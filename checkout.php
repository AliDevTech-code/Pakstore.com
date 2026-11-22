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

/* Orders are only accepted from signed-in customers. */
if (!$customer) {
    $returnTo = 'checkout.php';
    if ($isDirect) {
        $returnTo .= '?product=' . $directId . '&qty=' . $directQty;
    }
    flash('info', 'Please sign in or create an account to place your order. Your cart is saved.');
    redirect('account.php?mode=login&redirect=' . urlencode($returnTo));
}
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
<main class="flex-1 bg-[#f3f4f6]">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6"><h1 class="pk-title">Checkout</h1><p class="pk-sub">Cash on Delivery — poore Pakistan mein</p></div>
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
      <div class="px-6 py-4 bg-[#166534] text-white flex items-center justify-between">
        <div><span class="text-green-200 font-bold text-xs uppercase tracking-wider block">100% Cash on Delivery</span><h2 class="text-lg font-bold" style="color:#111827">Complete Your Order</h2></div>
        <a href="<?= e($isDirect ? url('product.php?id=' . (int)$directProduct['id']) : url('cart.php')) ?>" class="text-gray-900/80 hover:text-gray-900"><i data-lucide="x" class="w-5 h-5"></i></a>
      </div>

      <form method="post" action="<?= e(url('checkout.php')) ?>" class="p-6 space-y-5">
        <?= csrf_field() ?>
        <?php if ($isDirect): ?>
          <input type="hidden" name="direct_product_id" value="<?= (int)$directProduct['id'] ?>">
          <input type="hidden" name="direct_qty" value="<?= (int)$directQty ?>">
        <?php endif; ?>

        <?php if ($errors): ?>
          <div class="p-3 text-sm bg-red-50 text-red-700 border border-red-200 rounded-xl space-y-1"><?php foreach ($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div>
        <?php endif; ?>

        <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
          <span class="text-sm font-bold text-gray-900 block mb-2">Order Summary</span>
          <?php if ($isDirect): ?>
            <div class="flex items-center justify-between gap-3 text-sm">
              <div class="flex items-center gap-3 min-w-0">
                <img src="<?= e(image_url($directProduct['image'])) ?>" alt="<?= e($directProduct['title']) ?>" class="w-12 h-12 rounded-lg object-cover border border-gray-200">
                <div class="min-w-0">
                  <div class="font-semibold text-gray-900 truncate"><?= e($directProduct['title']) ?></div>
                  <div class="text-gray-500 text-xs"><?= e(money($directProduct['price'])) ?> each</div>
                </div>
              </div>
              <div class="shrink-0 text-right">
                <label class="block text-[11px] font-bold text-gray-600 mb-1">Quantity</label>
                <input type="number" name="direct_qty" value="<?= (int)$directQty ?>" min="1" max="<?= max(1,(int)($directProduct['stock']??99)) ?>" class="w-20 px-2 py-2 border border-gray-300 rounded-lg text-center font-bold text-sm">
              </div>
            </div>
          <?php else: ?>
            <div class="space-y-2 text-sm max-h-40 overflow-y-auto">
              <?php foreach ($cartItems as $item): ?>
                <div class="flex justify-between gap-3"><span class="text-gray-700 truncate"><?= e($item['title']) ?> × <?= (int)$item['qty'] ?></span><span class="font-bold text-gray-900 shrink-0"><?= e(money((float)$item['price']*(int)$item['qty'])) ?></span></div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          <div class="mt-3 pt-3 border-t border-gray-200 flex flex-wrap justify-between gap-2 text-sm">
            <span class="text-gray-600">Delivery: <strong class="text-gray-900"><?= $shipping <= 0 ? 'FREE' : e(money($shipping)) ?></strong></span>
            <span class="font-black text-gray-900">Total (COD): <span class="text-[#166534] text-lg"><?= e(money($total)) ?></span></span>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="md:col-span-2"><label for="customer_name" class="pk-label">Full Name *</label><input id="customer_name" name="customer_name" type="text" required maxlength="150" value="<?= e($form['customer_name']) ?>" class="pk-input" placeholder="e.g. Muhammad Bilal Khan"></div>
          <div><label for="phone" class="pk-label">Mobile Number *</label><input id="phone" name="phone" type="tel" required maxlength="30" value="<?= e($form['phone']) ?>" class="pk-input" placeholder="0300-1234567"><span class="text-[11px] text-gray-500 mt-1 block">Courier rider will call you on this number.</span></div>
          <div><label for="city" class="pk-label">City *</label><input id="city" name="city" type="text" required maxlength="100" value="<?= e($form['city']) ?>" class="pk-input" placeholder="e.g. Lahore, Karachi, Islamabad"></div>
          <div class="md:col-span-2"><label for="email" class="pk-label">Email (optional)</label><input id="email" name="email" type="email" maxlength="190" value="<?= e($form['email']) ?>" class="pk-input" placeholder="you@example.com"></div>
          <div class="md:col-span-2"><label for="address" class="pk-label">Full Street Address *</label><textarea id="address" name="address" required rows="3" maxlength="1000" class="pk-textarea" placeholder="House #, Street, Area, Landmark..."><?= e($form['address']) ?></textarea></div>
          <div class="md:col-span-2"><label for="order_notes" class="pk-label">Order Notes (optional)</label><input id="order_notes" name="order_notes" type="text" maxlength="500" value="<?= e($form['order_notes']) ?>" class="pk-input" placeholder="Delivery instructions or landmark"></div>
        </div>

        <div class="p-4 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-[#166534] text-white flex items-center justify-center font-bold shrink-0">Rs</div>
          <div><div class="font-bold text-gray-900 text-sm">Payment Method: Cash on Delivery (COD)</div><div class="text-xs text-gray-600">Parcel receive karen aur cash courier rider ko dein.</div></div>
        </div>

        <button type="submit" class="pk-btn pk-btn-orange w-full text-base py-4"><i data-lucide="shield-check" class="w-5 h-5"></i> Confirm Order (<?= e(money($total)) ?> - COD)</button>
        <p class="text-xs text-gray-500 text-center">After confirmation an Order ID like PK-1234 is generated.</p>
      </form>
    </div>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>