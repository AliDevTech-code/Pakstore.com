<?php
/** Order confirmation page (reads the order placed in this session). */

require_once __DIR__ . '/includes/functions.php';

$orderId = (int) ($_SESSION['last_order_id'] ?? 0);
if ($orderId <= 0) {
    redirect('index.php');
}

$stmt = db()->prepare('SELECT * FROM orders WHERE id = ? LIMIT 1');
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
    unset($_SESSION['last_order_id'], $_SESSION['last_order_code']);
    redirect('index.php');
}

$order['items'] = order_items($orderId);
$waPhone = clean_phone_wa(setting('whatsapp', setting('phone', '')));
$waText  = rawurlencode('Assalam-o-Alaikum, I have placed order #' . $order['order_code'] . ' on ' . setting('store_name', 'PakStore') . '.');

$pageTitle = 'Order Confirmed #' . $order['order_code'] . ' - ' . setting('store_name', 'PakStore');
require __DIR__ . '/includes/header.php';
?>

<main class="flex-1 bg-slate-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-8 relative" data-success-burst>
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xl">
            <div class="bg-emerald-600 text-white p-6 text-center">
                <div class="w-14 h-14 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i data-lucide="check-circle" class="w-8 h-8 text-white"></i>
                </div>
                <span class="text-xs uppercase font-bold tracking-widest text-emerald-200 block">Order Successfully Placed</span>
                <h1 class="text-2xl font-bold tracking-tight mt-1">Shukriya, <?= e($order['customer_name']) ?>!</h1>
                <p class="text-xs text-emerald-100 mt-1 max-w-md mx-auto">Your Cash on Delivery order has been received. Our delivery agent will confirm it before dispatch.</p>
            </div>

            <div class="p-6 space-y-5">
                <div class="flex flex-wrap items-center justify-between gap-3 p-4 bg-slate-50 border border-slate-200 rounded-xl">
                    <div>
                        <span class="text-xs text-slate-500 font-medium block">Order Tracking ID:</span>
                        <span class="font-mono text-base font-black text-slate-900 tracking-wider">#<?= e($order['order_code']) ?></span>
                    </div>
                    <div class="text-right">
                        <span class="text-xs text-slate-500 font-medium block">Payment Method:</span>
                        <span class="text-xs font-bold text-emerald-700 bg-emerald-100/70 px-2.5 py-1 rounded-md inline-block">Cash on Delivery (COD)</span>
                    </div>
                </div>

                <div class="p-4 bg-amber-50/70 border border-amber-200 rounded-xl text-xs space-y-1.5">
                    <div class="flex items-center justify-between gap-2 font-bold text-slate-900">
                        <span class="text-amber-900 uppercase tracking-wider text-[11px]">Shipping &amp; Delivery Address</span>
                        <span class="text-emerald-700 font-mono"><?= e($order['phone']) ?></span>
                    </div>
                    <p class="font-medium text-slate-800"><?= e($order['customer_name']) ?> &middot; <?= e($order['city']) ?></p>
                    <p class="text-slate-700 font-normal bg-white p-2.5 rounded border border-amber-200 leading-relaxed"><?= nl2br(e($order['address'])) ?></p>
                    <?php if (!empty($order['order_notes'])): ?>
                        <p class="text-slate-500 text-[11px] pt-1"><strong>Delivery Note:</strong> <?= e($order['order_notes']) ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <span class="text-xs font-bold text-slate-700 block mb-2">Ordered Items:</span>
                    <div class="space-y-2 border border-slate-200 rounded-xl p-3 bg-white">
                        <?php foreach ($order['items'] as $item): ?>
                            <div class="flex items-center justify-between gap-3 text-xs py-1 border-b border-slate-100 last:border-b-0">
                                <div class="flex items-center gap-2 min-w-0">
                                    <img src="<?= e(image_url($item['image'])) ?>" alt="<?= e($item['title']) ?>" class="w-9 h-9 rounded object-cover border border-slate-100 shrink-0">
                                    <span class="text-slate-800 font-medium truncate"><?= e($item['title']) ?> &times; <?= (int) $item['quantity'] ?></span>
                                </div>
                                <span class="font-mono font-bold text-slate-900 shrink-0"><?= e(money((float) $item['price'] * (int) $item['quantity'])) ?></span>
                            </div>
                        <?php endforeach; ?>

                        <div class="pt-2 border-t border-slate-200 flex justify-between text-xs font-semibold text-slate-600">
                            <span>Shipping Charges</span>
                            <span><?= (float) $order['shipping_fee'] <= 0 ? 'FREE' : e(money($order['shipping_fee'])) ?></span>
                        </div>
                        <div class="flex justify-between text-sm font-bold text-slate-900 pt-1">
                            <span>Total Cash to Pay on Delivery</span>
                            <span class="font-mono text-emerald-700 text-base"><?= e(money($order['total_amount'])) ?></span>
                        </div>
                    </div>
                </div>

                <div class="p-3 bg-slate-100 rounded-xl text-xs text-slate-600 flex flex-wrap items-center justify-between gap-2 print-hide">
                    <span class="flex items-center gap-2">
                        <i data-lucide="shield" class="w-4 h-4 text-emerald-600"></i>
                        <span>This order is saved with your address in the admin panel.</span>
                    </span>
                    <a href="<?= e(url('track-order.php?order=' . urlencode($order['order_code']))) ?>" class="text-emerald-700 font-bold hover:underline">Track Order &rarr;</a>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-2 print-hide">
                    <button type="button" onclick="window.print()" class="py-2.5 px-4 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-semibold transition-colors flex items-center justify-center gap-2">
                        <i data-lucide="printer" class="w-4 h-4"></i> Print Receipt
                    </button>
                    <a href="<?= e(url('index.php')) ?>" class="py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold transition-colors flex items-center justify-center gap-2">
                        <span>Back to Store</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>


            </div>
        </div>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
