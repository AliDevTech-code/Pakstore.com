<?php
/** Printable courier slip / packing label. */

require_once __DIR__ . '/includes/auth.php';

$id = (int) get_string('id', '0');
$stmt = db()->prepare('SELECT * FROM orders WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
    flash('error', 'Order not found.');
    redirect('admin/index.php?tab=orders');
}
$order['items'] = order_items($id);
$storeName = setting('store_name', 'PakStore');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Courier Slip #<?= e($order['order_code']) ?> - <?= e($storeName) ?></title>
    <link rel="stylesheet" href="<?= e(url('assets/css/tailwind.css') . '?v=4') ?>">
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css') . '?v=4') ?>">
    <style>
        @page { size: A5; margin: 8mm; }
        @media print { .no-print { display: none !important; } body { background: #fff; } }
    </style>
</head>
<body class="bg-slate-100 p-4 text-slate-900">

<div class="no-print max-w-2xl mx-auto mb-4 flex items-center justify-between gap-2">
    <a href="<?= e(url('admin/index.php?tab=orders')) ?>" class="px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-700">&larr; Back to orders</a>
    <button type="button" onclick="window.print()" class="px-4 py-2 bg-[#00A651] hover:bg-[#008f45] text-white rounded-lg text-xs font-bold">Print Slip</button>
</div>

<div class="print-area max-w-2xl mx-auto bg-white border border-slate-300 rounded-lg overflow-hidden">
    <div class="px-5 py-4 border-b-2 border-slate-900 flex items-start justify-between gap-4">
        <div>
            <div class="flex items-end">
                <span class="text-2xl font-black text-[#00A651]">Pak</span>
                <span class="text-2xl font-black text-[#FF6B00]">Store</span>
            </div>
            <p class="text-[10px] text-slate-500 mt-0.5"><?= e($storeName) ?> &middot; Cash on Delivery</p>
            <p class="text-[10px] text-slate-600 mt-1">Helpline: <?= e(setting('phone')) ?> &middot; <?= e(setting('email')) ?></p>
        </div>
        <div class="text-right">
            <span class="block text-[10px] uppercase tracking-widest font-bold text-slate-500">Order ID</span>
            <span class="block font-mono text-xl font-black">#<?= e($order['order_code']) ?></span>
            <span class="block text-[10px] text-slate-500 mt-1"><?= e(format_date($order['created_at'])) ?></span>
        </div>
    </div>

    <div class="grid grid-cols-2 border-b border-slate-300">
        <div class="p-4 border-r border-slate-300">
            <span class="block text-[9px] uppercase tracking-widest font-bold text-slate-400 mb-1">Deliver To</span>
            <p class="text-sm font-black"><?= e($order['customer_name']) ?></p>
            <p class="text-xs font-mono font-bold mt-0.5"><?= e($order['phone']) ?></p>
            <p class="text-[11px] leading-relaxed mt-1"><?= nl2br(e($order['address'])) ?></p>
            <p class="text-[11px] font-bold mt-1"><?= e($order['city']) ?></p>
        </div>
        <div class="p-4">
            <span class="block text-[9px] uppercase tracking-widest font-bold text-slate-400 mb-1">Shipment</span>
            <p class="text-xs">Courier: <strong><?= e($order['tracking_courier'] ?: 'TCS') ?></strong></p>
            <p class="text-xs">Tracking #: <strong class="font-mono"><?= e($order['tracking_number'] ?: '—') ?></strong></p>
            <p class="text-xs">Payment: <strong><?= e($order['payment_method']) ?></strong></p>
            <p class="text-xs">Status: <strong><?= e($order['status']) ?></strong></p>
            <div class="mt-3 p-2 bg-slate-900 text-white rounded text-center">
                <span class="block text-[9px] uppercase tracking-widest">Collect Cash</span>
                <span class="block font-mono text-lg font-black"><?= e(money($order['total_amount'])) ?></span>
            </div>
        </div>
    </div>

    <table class="w-full text-[11px]">
        <thead class="bg-slate-100 text-slate-600">
            <tr>
                <th class="text-left px-4 py-2 font-bold">Item</th>
                <th class="text-center px-2 py-2 font-bold">Qty</th>
                <th class="text-right px-4 py-2 font-bold">Amount</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            <?php foreach ($order['items'] as $item): ?>
                <tr>
                    <td class="px-4 py-2"><?= e($item['title']) ?></td>
                    <td class="px-2 py-2 text-center font-mono"><?= (int) $item['quantity'] ?></td>
                    <td class="px-4 py-2 text-right font-mono"><?= e(money((float) $item['price'] * (int) $item['quantity'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot class="border-t border-slate-300">
            <tr><td colspan="2" class="px-4 py-1.5 text-right text-slate-500">Subtotal</td><td class="px-4 py-1.5 text-right font-mono"><?= e(money($order['subtotal'])) ?></td></tr>
            <tr><td colspan="2" class="px-4 py-1.5 text-right text-slate-500">Shipping</td><td class="px-4 py-1.5 text-right font-mono"><?= e(money($order['shipping_fee'])) ?></td></tr>
            <tr class="bg-slate-50"><td colspan="2" class="px-4 py-2 text-right font-black">Total COD</td><td class="px-4 py-2 text-right font-mono font-black"><?= e(money($order['total_amount'])) ?></td></tr>
        </tfoot>
    </table>

    <?php if (!empty($order['order_notes'])): ?>
        <div class="px-4 py-2 border-t border-slate-300 text-[10px]"><strong>Note:</strong> <?= e($order['order_notes']) ?></div>
    <?php endif; ?>

    <div class="px-4 py-3 border-t border-slate-300 flex items-center justify-between text-[9px] text-slate-500">
        <span>Parcel inspected &amp; sealed. Please check before paying.</span>
        <span>Signature: ____________________</span>
    </div>
</div>

</body>
</html>
