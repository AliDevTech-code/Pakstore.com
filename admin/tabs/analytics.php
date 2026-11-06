<?php
/** Analytics tab: real sales insights from MySQL. */

$pdo = db();

$today = (float) $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status <> 'Cancelled' AND DATE(created_at) = CURDATE()")->fetchColumn();
$last7 = (float) $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status <> 'Cancelled' AND created_at >= (NOW() - INTERVAL 7 DAY)")->fetchColumn();
$last30 = (float) $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status <> 'Cancelled' AND created_at >= (NOW() - INTERVAL 30 DAY)")->fetchColumn();

$orderCount = (int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$delivered = (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'Delivered'")->fetchColumn();
$cancelled = (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'Cancelled'")->fetchColumn();
$avgOrder = $orderCount > 0 ? (float) $pdo->query("SELECT COALESCE(AVG(total_amount),0) FROM orders WHERE status <> 'Cancelled'")->fetchColumn() : 0.0;
$conversion = $orderCount > 0 ? round(($delivered / $orderCount) * 100, 1) : 0.0;

$topProducts = $pdo->query(
    "SELECT oi.product_id, oi.title,
            SUM(oi.quantity) AS units,
            SUM(oi.quantity * oi.price) AS revenue
     FROM order_items oi
     INNER JOIN orders o ON o.id = oi.order_id
     WHERE o.status <> 'Cancelled'
     GROUP BY oi.product_id, oi.title
     ORDER BY units DESC LIMIT 8"
)->fetchAll();

$byCity = $pdo->query(
    "SELECT city, COUNT(*) AS orders, SUM(total_amount) AS revenue
     FROM orders WHERE status <> 'Cancelled'
     GROUP BY city ORDER BY orders DESC LIMIT 8"
)->fetchAll();

$daily = $pdo->query(
    "SELECT DATE(created_at) AS day, COUNT(*) AS orders, SUM(total_amount) AS revenue
     FROM orders WHERE status <> 'Cancelled' AND created_at >= (NOW() - INTERVAL 14 DAY)
     GROUP BY DATE(created_at) ORDER BY day ASC"
)->fetchAll();

$maxDaily = 0.0;
foreach ($daily as $row) {
    $maxDaily = max($maxDaily, (float) $row['revenue']);
}
?>

<div class="flex flex-wrap items-end justify-between gap-3 mb-5">
    <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Analytics</h1>
        <p class="text-xs text-slate-500 mt-1">Sales performance based on your real orders. No demo figures.</p>
    </div>
</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-5">
    <div class="bg-white border border-slate-200 rounded-2xl p-4">
        <span class="text-[10px] uppercase tracking-wider font-bold text-slate-400">Sales Today</span>
        <p class="text-xl font-black text-slate-900 font-mono mt-1.5"><?= e(money($today)) ?></p>
    </div>
    <div class="bg-white border border-slate-200 rounded-2xl p-4">
        <span class="text-[10px] uppercase tracking-wider font-bold text-slate-400">Last 7 Days</span>
        <p class="text-xl font-black text-slate-900 font-mono mt-1.5"><?= e(money($last7)) ?></p>
    </div>
    <div class="bg-white border border-slate-200 rounded-2xl p-4">
        <span class="text-[10px] uppercase tracking-wider font-bold text-slate-400">Last 30 Days</span>
        <p class="text-xl font-black text-slate-900 font-mono mt-1.5"><?= e(money($last30)) ?></p>
    </div>
    <div class="bg-white border border-slate-200 rounded-2xl p-4">
        <span class="text-[10px] uppercase tracking-wider font-bold text-slate-400">Average Order</span>
        <p class="text-xl font-black text-slate-900 font-mono mt-1.5"><?= e(money($avgOrder)) ?></p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-5">
    <div class="lg:col-span-2 bg-white border border-slate-200 rounded-2xl p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-bold text-slate-900 flex items-center gap-1.5"><i data-lucide="trending-up" class="w-4 h-4 text-[#00A651]"></i> Revenue (Last 14 Days)</h2>
        </div>

        <?php if (!$daily): ?>
            <div class="h-40 flex items-center justify-center text-xs text-slate-400">No sales recorded yet.</div>
        <?php else: ?>
            <div class="flex items-end gap-1.5 h-40">
                <?php foreach ($daily as $day): ?>
                    <?php $height = $maxDaily > 0 ? max(4, round(((float) $day['revenue'] / $maxDaily) * 100)) : 4; ?>
                    <div class="flex-1 flex flex-col items-center justify-end h-full gap-1" title="<?= e(format_date($day['day'], false)) ?> — <?= e(money($day['revenue'])) ?> (<?= (int) $day['orders'] ?> orders)">
                        <span class="text-[8px] text-slate-400 font-mono hidden sm:block"><?= (int) $day['orders'] ?></span>
                        <div class="w-full bg-[#00A651]/80 hover:bg-[#00A651] rounded-t transition-colors" style="height: <?= $height ?>%"></div>
                        <span class="text-[8px] text-slate-400 whitespace-nowrap"><?= e(date('d M', strtotime((string) $day['day']))) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl p-5">
        <h2 class="text-sm font-bold text-slate-900 mb-3 flex items-center gap-1.5"><i data-lucide="target" class="w-4 h-4 text-[#00A651]"></i> Fulfilment</h2>
        <div class="space-y-3 text-xs">
            <div class="flex items-center justify-between"><span class="text-slate-600">Total orders</span><span class="font-mono font-bold text-slate-900"><?= $orderCount ?></span></div>
            <div class="flex items-center justify-between"><span class="text-slate-600">Delivered</span><span class="font-mono font-bold text-emerald-700"><?= $delivered ?></span></div>
            <div class="flex items-center justify-between"><span class="text-slate-600">Cancelled</span><span class="font-mono font-bold text-rose-600"><?= $cancelled ?></span></div>
            <div class="flex items-center justify-between border-t border-slate-100 pt-2"><span class="text-slate-600">Delivery success</span><span class="font-mono font-bold text-slate-900"><?= e(number_format($conversion, 1)) ?>%</span></div>
            <div class="h-2 bg-slate-100 rounded-full overflow-hidden mt-1">
                <div class="h-full bg-[#00A651] rounded-full" style="width: <?= min(100, max(0, $conversion)) ?>%"></div>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-200">
            <h2 class="text-sm font-bold text-slate-900 flex items-center gap-1.5"><i data-lucide="flame" class="w-4 h-4 text-[#FF6B00]"></i> Best Selling Products</h2>
        </div>
        <?php if (!$topProducts): ?>
            <div class="p-8 text-center text-xs text-slate-400">No product sales yet.</div>
        <?php else: ?>
            <table class="w-full text-xs">
                <thead class="bg-slate-50 text-slate-500 text-[10px] uppercase tracking-wider">
                    <tr><th class="text-left px-4 py-2 font-bold">Product</th><th class="text-center px-2 py-2 font-bold">Units</th><th class="text-right px-4 py-2 font-bold">Revenue</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($topProducts as $index => $product): ?>
                        <tr>
                            <td class="px-4 py-2.5">
                                <span class="font-mono text-[10px] text-slate-400 mr-1.5"><?= $index + 1 ?>.</span>
                                <span class="font-semibold text-slate-800"><?= e($product['title']) ?></span>
                            </td>
                            <td class="px-2 py-2.5 text-center font-mono font-bold text-slate-700"><?= (int) $product['units'] ?></td>
                            <td class="px-4 py-2.5 text-right font-mono font-bold text-slate-900"><?= e(money($product['revenue'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-200">
            <h2 class="text-sm font-bold text-slate-900 flex items-center gap-1.5"><i data-lucide="map-pin" class="w-4 h-4 text-[#00A651]"></i> Sales by City</h2>
        </div>
        <?php if (!$byCity): ?>
            <div class="p-8 text-center text-xs text-slate-400">No sales recorded yet.</div>
        <?php else: ?>
            <table class="w-full text-xs">
                <thead class="bg-slate-50 text-slate-500 text-[10px] uppercase tracking-wider">
                    <tr><th class="text-left px-4 py-2 font-bold">City</th><th class="text-center px-2 py-2 font-bold">Orders</th><th class="text-right px-4 py-2 font-bold">Revenue</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($byCity as $city): ?>
                        <tr>
                            <td class="px-4 py-2.5 font-semibold text-slate-800"><?= e($city['city']) ?></td>
                            <td class="px-2 py-2.5 text-center font-mono font-bold text-slate-700"><?= (int) $city['orders'] ?></td>
                            <td class="px-4 py-2.5 text-right font-mono font-bold text-slate-900"><?= e(money($city['revenue'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
