<?php
/** Public order tracking by order code or phone number. */

require_once __DIR__ . '/includes/functions.php';

$query = get_string('order');
if ($query === '') {
    $query = get_string('q');
}

$order  = null;
$searched = $query !== '';
if ($searched) {
    $order = find_order($query);
}

$steps = [
    ['key' => 'Pending',    'label' => 'Order Placed',    'icon' => 'clipboard-check'],
    ['key' => 'Processing', 'label' => 'Processing',      'icon' => 'package'],
    ['key' => 'Shipped',    'label' => 'Shipped',         'icon' => 'truck'],
    ['key' => 'Delivered',  'label' => 'Delivered',       'icon' => 'home'],
];

$currentStep = $order ? order_status_step($order['status']) : 0;
$waPhone = clean_phone_wa(setting('whatsapp', setting('phone', '')));

$pageTitle = 'Track Your Order - ' . setting('store_name', 'PakStore');
require __DIR__ . '/includes/header.php';
?>

<main class="flex-1 bg-slate-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-8">
        <div class="text-center mb-6">
            <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-[#00A651] flex items-center justify-center mx-auto mb-3">
                <i data-lucide="package-check" class="w-6 h-6"></i>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Track Your Order</h1>
            <p class="text-xs text-slate-500 mt-1">Enter your Order ID (e.g. PK-1234) or the mobile number used at checkout.</p>
        </div>

        <form method="get" action="<?= e(url('track-order.php')) ?>" class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm flex flex-col sm:flex-row gap-2">
            <div class="relative flex-1">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                <input type="text" name="order" value="<?= e($query) ?>" required placeholder="PK-1234 or 0300-1234567" class="w-full pl-9 pr-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651] focus:border-[#00A651] font-mono" aria-label="Order ID or phone number">
            </div>
            <button type="submit" class="px-5 py-2.5 bg-[#00A651] hover:bg-[#008f45] text-white rounded-lg text-xs font-bold flex items-center justify-center gap-2 transition-colors">
                <i data-lucide="package-search" class="w-4 h-4"></i> Track Order
            </button>
        </form>

        <?php if ($searched && !$order): ?>
            <div class="mt-5 p-6 bg-white border border-rose-200 rounded-2xl text-center">
                <i data-lucide="search-x" class="w-8 h-8 text-rose-400 mx-auto mb-2"></i>
                <h2 class="text-sm font-bold text-slate-900">No order found</h2>
                <p class="text-xs text-slate-500 mt-1">We could not find an order matching &ldquo;<?= e($query) ?>&rdquo;. Please check the Order ID or phone number and try again.</p>
                <?php if (setting('whatsapp') !== ''): ?>
                    <a href="https://wa.me/<?= e($waPhone) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 mt-4 px-3.5 py-2 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg text-xs font-bold">
                        <i data-lucide="message-circle" class="w-3.5 h-3.5"></i> Contact support on WhatsApp
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($order): ?>
            <div class="mt-5 bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
                <div class="px-5 py-4 bg-slate-900 text-white flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <span class="text-[10px] uppercase tracking-widest text-slate-400 font-bold block">Order ID</span>
                        <span class="font-mono text-base font-black tracking-wider">#<?= e($order['order_code']) ?></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] uppercase tracking-widest text-slate-400 font-bold">Status</span>
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold <?= e(order_status_classes($order['status'])) ?>"><?= e($order['status']) ?></span>
                    </div>
                </div>

                <?php if ($order['status'] === 'Cancelled'): ?>
                    <div class="m-5 p-4 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800">
                        <p class="font-bold flex items-center gap-1.5"><i data-lucide="x-circle" class="w-4 h-4"></i> This order was cancelled.</p>
                        <p class="mt-1 text-rose-600">If this was unexpected, please contact support with your Order ID.</p>
                    </div>
                <?php else: ?>
                    <div class="px-5 pt-6 pb-2">
                        <div class="grid grid-cols-4 gap-1 relative">
                            <div class="absolute top-5 left-[12.5%] right-[12.5%] h-0.5 bg-slate-200" aria-hidden="true"></div>
                            <div class="absolute top-5 left-[12.5%] h-0.5 bg-[#00A651] transition-all" style="width: <?= max(0, min(75, ($currentStep - 1) * 25)) ?>%" aria-hidden="true"></div>

                            <?php foreach ($steps as $i => $step): ?>
                                <?php $stepNo = $i + 1; $done = $stepNo <= $currentStep; $active = $stepNo === $currentStep; ?>
                                <div class="relative flex flex-col items-center text-center">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 z-10 transition-colors <?= $done ? 'bg-[#00A651] border-[#00A651] text-white' : 'bg-white border-slate-200 text-slate-400' ?> <?= $active ? 'ring-4 ring-emerald-100' : '' ?>">
                                        <i data-lucide="<?= e($step['icon']) ?>" class="w-4 h-4"></i>
                                    </div>
                                    <span class="mt-2 text-[10px] sm:text-[11px] font-bold <?= $done ? 'text-slate-900' : 'text-slate-400' ?>"><?= e($step['label']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4 border-t border-slate-100 mt-4">
                    <div>
                        <span class="text-[10px] uppercase tracking-wider text-slate-400 font-bold block mb-1">Delivery Details</span>
                        <p class="text-xs font-bold text-slate-900"><?= e($order['customer_name']) ?></p>
                        <p class="text-[11px] text-slate-600 font-mono"><?= e($order['phone']) ?></p>
                        <p class="text-[11px] text-slate-600 leading-relaxed mt-1"><?= nl2br(e($order['address'])) ?></p>
                        <p class="text-[11px] text-slate-600"><?= e($order['city']) ?></p>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase tracking-wider text-slate-400 font-bold block mb-1">Shipment Info</span>
                        <p class="text-[11px] text-slate-600">Placed: <strong class="text-slate-900"><?= e(format_date($order['created_at'])) ?></strong></p>
                        <p class="text-[11px] text-slate-600">Courier: <strong class="text-slate-900"><?= e($order['tracking_courier'] ?: 'TCS') ?></strong></p>
                        <?php if (!empty($order['tracking_number'])): ?>
                            <p class="text-[11px] text-slate-600">Tracking #: <strong class="text-slate-900 font-mono"><?= e($order['tracking_number']) ?></strong></p>
                        <?php endif; ?>
                        <p class="text-[11px] text-slate-600">Payment: <strong class="text-emerald-700"><?= e($order['payment_method']) ?></strong></p>
                        <?php if (!empty($order['order_notes'])): ?>
                            <p class="text-[11px] text-slate-500 mt-1"><strong>Note:</strong> <?= e($order['order_notes']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="px-5 pb-5">
                    <span class="text-[10px] uppercase tracking-wider text-slate-400 font-bold block mb-2">Order Items</span>
                    <div class="border border-slate-200 rounded-xl divide-y divide-slate-100">
                        <?php foreach ($order['items'] as $item): ?>
                            <div class="flex items-center justify-between gap-3 p-3 text-xs">
                                <div class="flex items-center gap-2 min-w-0">
                                    <img src="<?= e(image_url($item['image'])) ?>" alt="<?= e($item['title']) ?>" class="w-9 h-9 rounded object-cover border border-slate-100 shrink-0">
                                    <span class="text-slate-800 font-medium truncate"><?= e($item['title']) ?> &times; <?= (int) $item['quantity'] ?></span>
                                </div>
                                <span class="font-mono font-bold text-slate-900 shrink-0"><?= e(money((float) $item['price'] * (int) $item['quantity'])) ?></span>
                            </div>
                        <?php endforeach; ?>
                        <div class="flex justify-between p-3 text-xs font-semibold text-slate-600 bg-slate-50">
                            <span>Shipping</span>
                            <span><?= (float) $order['shipping_fee'] <= 0 ? 'FREE' : e(money($order['shipping_fee'])) ?></span>
                        </div>
                        <div class="flex justify-between p-3 text-sm font-bold text-slate-900 bg-slate-50">
                            <span>Total (COD)</span>
                            <span class="font-mono text-emerald-700"><?= e(money($order['total_amount'])) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
