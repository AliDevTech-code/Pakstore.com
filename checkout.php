<?php
/**
 * Cash on Delivery checkout.
 * Works with the session cart OR a direct "buy now" product.
 */

require_once __DIR__ . '/includes/functions.php';

$errors = [];

$directId  = (int) get_string('product', '0');
$directQty = max(1, (int) get_string('qty', '1'));
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $directId  = post_int('direct_product_id', 0);
    $directQty = max(1, post_int('direct_qty', 1));
}
$isDirect = $directId > 0;

$directProduct = null;
if ($isDirect) {
    $stmt = db()->prepare(
        'SELECT p.*, c.name AS category_name FROM products p
         LEFT JOIN categories c ON c.id = p.category_id
         WHERE p.id = ? LIMIT 1'
    );
    $stmt->execute([$directId]);
    $directProduct = $stmt->fetch() ?: null;

    if (!$directProduct || !(int) $directProduct['in_stock'] || (int) $directProduct['stock'] <= 0) {
        flash('error', 'That product is not available for ordering right now.');
        redirect('index.php#catalog-section');
    }
    $directQty = min($directQty, max(1, (int) $directProduct['stock']));
}

$cartItems = $isDirect ? [] : cart_detailed();

if (!$isDirect && !$cartItems) {
    flash('info', 'Your shopping bag is empty. Add a product before checkout.');
    redirect('cart.php');
}

/* ---------------- Totals (always recalculated server side) ---------------- */
if ($isDirect) {
    $subtotal = (float) $directProduct['price'] * $directQty;
} else {
    $subtotal = 0.0;
    foreach ($cartItems as $ci) {
        $subtotal += (float) $ci['price'] * (int) $ci['qty'];
    }
}
$shipping = delivery_charges();
$total = $subtotal + $shipping;

$customer = current_customer();
$form = [
    'customer_name' => $customer['full_name'] ?? '',
    'phone'         => $customer['phone'] ?? '',
    'email'         => $customer['email'] ?? '',
    'city'          => $customer['city'] ?? '',
    'address'       => $customer['address'] ?? '',
    'order_notes'   => '',
];

/* ---------------- Place order ---------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post();
    csrf_verify();

    $form['customer_name'] = post_string('customer_name');
    $form['phone']         = post_string('phone');
    $form['email']         = post_string('email');
    $form['city']          = post_string('city');
    $form['address']       = post_string('address');
    $form['order_notes']   = post_string('order_notes');

    if (mb_strlen($form['customer_name']) < 3) {
        $errors[] = 'Please enter your full name.';
    }
    if (strlen(preg_replace('/[^0-9]/', '', $form['phone'])) < 10) {
        $errors[] = 'Please enter a valid Pakistani mobile number (e.g. 0300-1234567).';
    }
    if ($form['email'] !== '' && !filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'The email address is not valid.';
    }
    if (mb_strlen($form['city']) < 2) {
        $errors[] = 'Please write your city name.';
    }
    if (mb_strlen($form['address']) < 8) {
        $errors[] = 'Please enter your complete street address (house, street, area).';
    }

    if (!$errors) {
        $pdo = db();
        try {
            $pdo->beginTransaction();

            // Re-read the ordered products inside the transaction and lock them.
            $orderLines = [];

            if ($isDirect) {
                $stmt = $pdo->prepare('SELECT id, title, price, stock, in_stock, image FROM products WHERE id = ? FOR UPDATE');
                $stmt->execute([(int) $directProduct['id']]);
                $row = $stmt->fetch();
                if (!$row || !(int) $row['in_stock'] || (int) $row['stock'] < $directQty) {
                    throw new RuntimeException('One of the products is no longer available in the requested quantity.');
                }
                $orderLines[] = [
                    'id'       => (int) $row['id'],
                    'title'    => $row['title'],
                    'price'    => (float) $row['price'],
                    'quantity' => $directQty,
                    'image'    => $row['image'],
                ];
            } else {
                $ids = array_map(static fn($row) => (int) $row['id'], $cartItems);
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $stmt = $pdo->prepare("SELECT id, title, price, stock, in_stock, image FROM products WHERE id IN ($placeholders) FOR UPDATE");
                $stmt->execute($ids);
                $fresh = [];
                foreach ($stmt->fetchAll() as $row) {
                    $fresh[(int) $row['id']] = $row;
                }

                foreach ($cartItems as $ci) {
                    $row = $fresh[(int) $ci['id']] ?? null;
                    $qty = (int) $ci['qty'];
                    if (!$row || !(int) $row['in_stock'] || (int) $row['stock'] < $qty) {
                        throw new RuntimeException('"' . $ci['title'] . '" is no longer available in the requested quantity.');
                    }
                    $orderLines[] = [
                        'id'       => (int) $row['id'],
                        'title'    => $row['title'],
                        'price'    => (float) $row['price'],
                        'quantity' => $qty,
                        'image'    => $row['image'],
                    ];
                }
            }

            $subtotal = 0.0;
            foreach ($orderLines as $line) {
                $subtotal += $line['price'] * $line['quantity'];
            }
            $shipping = delivery_charges();
            $total = $subtotal + $shipping;

            $orderCode = generate_order_code();

            $stmt = $pdo->prepare(
                'INSERT INTO orders
                 (order_code, user_id, customer_name, phone, email, city, address, order_notes,
                  payment_method, subtotal, shipping_fee, total_amount, status, tracking_courier)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $orderCode,
                $customer['id'] ?? null,
                $form['customer_name'],
                $form['phone'],
                $form['email'] !== '' ? $form['email'] : null,
                $form['city'],
                $form['address'],
                $form['order_notes'] !== '' ? $form['order_notes'] : null,
                'Cash on Delivery',
                $subtotal,
                $shipping,
                $total,
                'Pending',
                'TCS',
            ]);

            $orderId = (int) $pdo->lastInsertId();

            $itemStmt = $pdo->prepare(
                'INSERT INTO order_items (order_id, product_id, title, price, quantity, image)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stockStmt = $pdo->prepare(
                'UPDATE products SET in_stock = IF(stock - ? <= 0, 0, 1), stock = stock - ? WHERE id = ?'
            );

            foreach ($orderLines as $line) {
                $itemStmt->execute([
                    $orderId,
                    $line['id'],
                    $line['title'],
                    $line['price'],
                    $line['quantity'],
                    $line['image'],
                ]);
                $stockStmt->execute([$line['quantity'], $line['quantity'], $line['id']]);
            }

            $pdo->commit();

            if (!$isDirect) {
                cart_clear();
            }

            $_SESSION['last_order_id'] = $orderId;
            $_SESSION['last_order_code'] = $orderCode;
            redirect('order-success.php');
        } catch (Throwable $e) {
            if (db()->inTransaction()) {
                db()->rollBack();
            }
            error_log('Order placement failed: ' . $e->getMessage());
            $errors[] = $e instanceof RuntimeException
                ? $e->getMessage()
                : 'Could not place your order right now. Please try again.';
        }
    }
}

$pageTitle = 'Checkout (Cash on Delivery) - ' . setting('store_name', 'PakStore');
require __DIR__ . '/includes/header.php';
?>

<main class="flex-1 bg-slate-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
                <div>
                    <span class="text-[#00A651] font-bold text-xs uppercase tracking-wider block">100% Cash on Delivery</span>
                    <h1 class="text-lg font-bold tracking-tight">Complete Your Order</h1>
                </div>
                <a href="<?= e($isDirect ? url('product.php?id=' . (int) $directProduct['id']) : url('cart.php')) ?>" class="p-1.5 text-slate-400 hover:text-white rounded-lg transition-colors" aria-label="Back">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </a>
            </div>

            <form method="post" action="<?= e(url('checkout.php')) ?>" class="p-6 space-y-5">
                <?= csrf_field() ?>
                <?php if ($isDirect): ?>
                    <input type="hidden" name="direct_product_id" value="<?= (int) $directProduct['id'] ?>">
                    <input type="hidden" name="direct_qty" value="<?= (int) $directQty ?>">
                <?php endif; ?>

                <?php if ($errors): ?>
                    <div class="p-3 text-xs bg-rose-50 text-rose-700 border border-rose-200 rounded-lg space-y-1">
                        <?php foreach ($errors as $error): ?>
                            <p><?= e($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Summary -->
                <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                    <span class="text-xs font-bold text-slate-700 block mb-2">Order Summary:</span>

                    <?php if ($isDirect): ?>
                        <div class="flex items-center justify-between gap-3 text-xs">
                            <div class="flex items-center gap-2 min-w-0">
                                <img src="<?= e(image_url($directProduct['image'])) ?>" alt="<?= e($directProduct['title']) ?>" class="w-10 h-10 rounded object-cover border border-slate-200 shrink-0">
                                <div class="min-w-0">
                                    <span class="font-semibold text-slate-900 block truncate"><?= e($directProduct['title']) ?></span>
                                    <span class="text-slate-500">Qty: <?= (int) $directQty ?> &times; <?= e(money($directProduct['price'])) ?></span>
                                </div>
                            </div>
                            <span class="font-mono font-bold text-slate-900 shrink-0"><?= e(money($subtotal)) ?></span>
                        </div>
                    <?php else: ?>
                        <div class="space-y-1.5 max-h-36 overflow-y-auto text-xs">
                            <?php foreach ($cartItems as $item): ?>
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-slate-700 truncate"><?= e($item['title']) ?> &times; <?= (int) $item['qty'] ?></span>
                                    <span class="font-mono font-bold text-slate-900 shrink-0"><?= e(money((float) $item['price'] * (int) $item['qty'])) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="mt-3 pt-2 border-t border-slate-200 flex flex-wrap items-center justify-between gap-2 text-xs">
                        <span class="text-slate-600">Delivery Charges: <strong class="text-slate-900 font-mono"><?= e(money($shipping)) ?></strong></span>
                        <span class="text-sm font-bold text-slate-900">Total Payable (COD): <span class="text-[#00A651] font-mono"><?= e(money($total)) ?></span></span>
                    </div>
                </div>

                <!-- Delivery form -->
                <div class="space-y-4">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-1.5">
                        <i data-lucide="map-pin" class="w-4 h-4 text-[#00A651]"></i>
                        <span>Customer &amp; Delivery Details (Pakistan)</span>
                    </h2>

                    <div>
                        <label for="customer_name" class="block text-xs font-semibold text-slate-700 mb-1">Full Name *</label>
                        <div class="relative">
                            <i data-lucide="user" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                            <input id="customer_name" name="customer_name" type="text" required maxlength="150" placeholder="e.g. Muhammad Bilal Khan" value="<?= e($form['customer_name']) ?>" class="w-full pl-9 pr-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651] focus:border-[#00A651]">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="phone" class="block text-xs font-semibold text-slate-700 mb-1">Mobile / WhatsApp Number *</label>
                            <div class="relative">
                                <i data-lucide="phone" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                                <input id="phone" name="phone" type="tel" required maxlength="30" placeholder="0300-1234567" value="<?= e($form['phone']) ?>" class="w-full pl-9 pr-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651] focus:border-[#00A651] font-mono">
                            </div>
                            <span class="text-[10px] text-slate-500 mt-0.5 block">The courier rider will call you on this number.</span>
                        </div>

                        <div>
                            <label for="city" class="block text-xs font-semibold text-slate-700 mb-1">City (Write City Name) *</label>
                            <div class="relative">
                                <i data-lucide="map-pin" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                                <input id="city" name="city" type="text" required maxlength="100" placeholder="e.g. Lahore, Karachi, Islamabad..." value="<?= e($form['city']) ?>" class="w-full pl-9 pr-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651] focus:border-[#00A651]">
                            </div>
                            <span class="text-[10px] text-slate-500 mt-0.5 block">Write your city; the rider will deliver there.</span>
                        </div>
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-semibold text-slate-700 mb-1">Email (optional)</label>
                        <input id="email" name="email" type="email" maxlength="190" placeholder="you@example.com" value="<?= e($form['email']) ?>" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651] focus:border-[#00A651]">
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1 gap-2">
                            <label for="address" class="text-xs font-semibold text-slate-700">Full Street Address (House / Flat #, Street, Mohalla / Area) *</label>
                            <span class="text-[10px] text-[#00A651] font-bold bg-emerald-50 px-1.5 py-0.5 rounded whitespace-nowrap">Shown in admin panel</span>
                        </div>
                        <textarea id="address" name="address" required rows="3" maxlength="1000" placeholder="e.g. House #42-B, Street #7, Sector C, Near Grand Jamia Mosque, Bahria Town..." class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651] focus:border-[#00A651]"><?= e($form['address']) ?></textarea>
                        <span class="text-[10px] text-slate-500 mt-0.5 block">This exact address is saved with the order for courier booking.</span>
                    </div>

                    <div>
                        <label for="order_notes" class="block text-xs font-semibold text-slate-700 mb-1">Order Notes / Landmark / Instructions (optional)</label>
                        <div class="relative">
                            <i data-lucide="file-text" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                            <input id="order_notes" name="order_notes" type="text" maxlength="500" placeholder="e.g. Deliver between 2 PM to 5 PM, or call on an alternate number" value="<?= e($form['order_notes']) ?>" class="w-full pl-9 pr-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651]">
                        </div>
                    </div>
                </div>

                <!-- Payment -->
                <div class="p-3.5 bg-emerald-50/80 border border-emerald-200 rounded-xl flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-[#00A651] text-white flex items-center justify-center font-bold text-xs shrink-0">Rs.</div>
                        <div>
                            <span class="text-xs font-bold text-slate-900 block">Payment Method: Cash on Delivery (COD)</span>
                            <span class="text-[11px] text-slate-600">Take the parcel and pay cash to the courier rider. No advance online payment.</span>
                        </div>
                    </div>
                    <i data-lucide="check-circle-2" class="w-5 h-5 text-[#00A651] shrink-0"></i>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3.5 bg-[#00A651] hover:bg-[#008f45] text-white font-bold rounded-xl text-sm transition-colors shadow-md flex items-center justify-center gap-2">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                        <span>Confirm Order (<?= e(money($total)) ?> - Cash on Delivery)</span>
                    </button>
                    <p class="text-[11px] text-slate-400 text-center mt-2">After confirmation an Order ID like PK-1234 is generated.</p>
                </div>
            </form>
        </div>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
