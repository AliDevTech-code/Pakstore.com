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

$pageTitle = 'Track Your Order - ' . setting('store_name', 'PakStore');
require __DIR__ . '/includes/header.php';
?>
<main class="flex-1 bg-[#f3f4f6]">
  <div class="max-w-3xl mx-auto px-4 sm:px-6 py-8">
    <div class="text-center mb-6">
      <div class="w-14 h-14 rounded-2xl bg-green-100 text-[#166534] flex items-center justify-center mx-auto mb-3"><i data-lucide="package-search" class="w-7 h-7"></i></div>
      <h1 class="pk-title">Track Your Order</h1>
      <p class="pk-sub">Order ID (PK-1234) ya mobile number enter karein.</p>
    </div>

    <form method="get" action="<?= e(url('track-order.php')) ?>" class="bg-white border border-gray-200 rounded-2xl p-4 shadow-sm flex flex-col sm:flex-row gap-2">
      <div class="relative flex-1"><i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-3.5"></i><input type="text" name="order" value="<?= e($query) ?>" required placeholder="PK-1234 or 0300-1234567" class="pk-input pl-9 font-mono" aria-label="Order ID or phone number"></div>
      <button type="submit" class="pk-btn pk-btn-green">Track Order</button>
    </form>

    <?php if ($searched && !$order): ?>
      <div class="mt-5 p-6 bg-white border border-red-200 rounded-2xl text-center">
        <i data-lucide="search-x" class="w-8 h-8 text-red-400 mx-auto mb-2"></i>
        <h2 class="font-bold text-gray-900">No order found</h2>
        <p class="text-sm text-gray-500 mt-1">"<?= e($query) ?>" ke liye koi order nahi mila. Order ID ya phone number check karein.</p>
      </div>
    <?php endif; ?>

    <?php if ($order): ?>
      <div class="mt-5 bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm">
        <div class="px-5 py-4 bg-[#166534] text-white flex flex-wrap items-center justify-between gap-3">
          <div><span class="text-[10px] uppercase tracking-widest text-green-200 font-bold block">Order ID</span><span class="font-mono text-base font-black">#<?= e($order['order_code']) ?></span></div>
          <span class="px-2.5 py-1 rounded-full text-xs font-bold <?= e(order_status_classes($order['status'])) ?>"><?= e($order['status']) ?></span>
        </div>

        <?php if ($order['status'] !== 'Cancelled'): ?>
          <div class="px-5 pt-6 pb-2">
            <div class="grid grid-cols-4 gap-1 relative">
              <div class="absolute top-5 left-[12.5%] right-[12.5%] h-0.5 bg-gray-200"></div>
              <div class="absolute top-5 left-[12.5%] h-0.5 bg-[#166534]" style="width: <?= max(0, min(75, ($currentStep - 1) * 25)) ?>%"></div>
              <?php foreach ($steps as $i => $step): $stepNo=$i+1; $done=$stepNo<=$currentStep; $active=$stepNo===$currentStep; ?>
                <div class="relative flex flex-col items-center text-center">
                  <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 z-10 <?= $done ? 'bg-[#166534] border-[#166534] text-white' : 'bg-white border-gray-200 text-gray-400' ?> <?= $active ? 'ring-4 ring-green-100' : '' ?>"><i data-lucide="<?= e($step['icon']) ?>" class="w-4 h-4"></i></div>
                  <span class="mt-2 text-[10px] sm:text-[11px] font-bold <?= $done ? 'text-gray-900' : 'text-gray-400' ?>"><?= e($step['label']) ?></span>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php else: ?>
          <div class="m-5 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-800 font-semibold">This order was cancelled.</div>
        <?php endif; ?>

        <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4 border-t border-gray-100 mt-4 text-sm">
          <div><div class="text-[11px] uppercase tracking-wider text-gray-400 font-bold mb-1">Delivery Details</div><p class="font-bold text-gray-900"><?= e($order['customer_name']) ?></p><p class="text-gray-600 font-mono text-xs"><?= e($order['phone']) ?></p><p class="text-gray-600 text-xs mt-1"><?= nl2br(e($order['address'])) ?></p><p class="text-gray-600 text-xs"><?= e($order['city']) ?></p></div>
          <div><div class="text-[11px] uppercase tracking-wider text-gray-400 font-bold mb-1">Shipment Info</div><p class="text-xs text-gray-600">Placed: <strong class="text-gray-900"><?= e(format_date($order['created_at'])) ?></strong></p><p class="text-xs text-gray-600">Courier: <strong class="text-gray-900"><?= e($order['tracking_courier'] ?: 'TCS') ?></strong></p><?php if (!empty($order['tracking_number'])): ?><p class="text-xs text-gray-600">Tracking #: <strong class="font-mono text-gray-900"><?= e($order['tracking_number']) ?></strong></p><?php endif; ?><p class="text-xs text-gray-600">Payment: <strong class="text-[#166534]"><?= e($order['payment_method']) ?></strong></p></div>
        </div>

        <div class="px-5 pb-5">
          <div class="text-[11px] uppercase tracking-wider text-gray-400 font-bold mb-2">Order Items</div>
          <div class="border border-gray-200 rounded-xl divide-y divide-gray-100">
            <?php foreach ($order['items'] as $item): ?>
              <div class="flex items-center justify-between gap-3 p-3 text-sm">
                <div class="flex items-center gap-2 min-w-0"><img src="<?= e(image_url($item['image'])) ?>" alt="<?= e($item['title']) ?>" class="w-10 h-10 rounded object-cover border border-gray-100 shrink-0"><span class="text-gray-800 font-medium truncate"><?= e($item['title']) ?> × <?= (int)$item['quantity'] ?></span></div>
                <span class="font-bold text-gray-900 shrink-0"><?= e(money((float)$item['price'] * (int)$item['quantity'])) ?></span>
              </div>
            <?php endforeach; ?>
            <div class="flex justify-between p-3 text-sm text-gray-600"><span>Delivery</span><span class="font-semibold"><?= (float)$order['shipping_fee'] <= 0 ? 'FREE' : e(money($order['shipping_fee'])) ?></span></div>
            <div class="flex justify-between p-3 font-black text-gray-900 bg-gray-50"><span>Total (COD)</span><span class="text-[#166534]"><?= e(money($order['total_amount'])) ?></span></div>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>