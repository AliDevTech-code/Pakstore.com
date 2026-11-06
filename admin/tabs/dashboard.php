<?php
/** Admin dashboard: KPIs, recent orders and low stock. */

$pdo = db();

$totalSales = (float) $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status <> 'Cancelled'")->fetchColumn();
$totalOrders = (int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$pendingOrdersCount = (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'Pending'")->fetchColumn();
$totalProducts = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$totalCustomers = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$outOfStock = (int) $pdo->query('SELECT COUNT(*) FROM products WHERE stock <= 0 OR in_stock = 0')->fetchColumn();

$recentOrders = $pdo->query('SELECT * FROM orders ORDER BY id DESC LIMIT 6')->fetchAll();
$lowStock = $pdo->query('SELECT id, title, stock, image FROM products ORDER BY stock ASC, id DESC LIMIT 5')->fetchAll();

$statusCounts = [];
foreach ($pdo->query('SELECT status, COUNT(*) AS c FROM orders GROUP BY status')->fetchAll() as $row) {
    $statusCounts[$row['status']] = (int) $row['c'];
}

/* 14-day revenue chart data (missing days filled with zero). */
$dailyRows = $pdo->query(
    "SELECT DATE(created_at) AS day, COALESCE(SUM(total_amount),0) AS revenue, COUNT(*) AS orders
     FROM orders
     WHERE status <> 'Cancelled' AND created_at >= (NOW() - INTERVAL 14 DAY)
     GROUP BY DATE(created_at) ORDER BY day ASC"
)->fetchAll();
$dailyMap = [];
foreach ($dailyRows as $row) {
    $dailyMap[(string) $row['day']] = $row;
}
$chartDays = [];
for ($i = 13; $i >= 0; $i--) {
    $day = date('Y-m-d', strtotime('-' . $i . ' days'));
    $chartDays[] = [
        'day'     => $day,
        'revenue' => (float) ($dailyMap[$day]['revenue'] ?? 0),
        'orders'  => (int) ($dailyMap[$day]['orders'] ?? 0),
    ];
}
$maxRevenue = 1.0;
$chartTotal = 0.0;
foreach ($chartDays as $day) {
    $maxRevenue = max($maxRevenue, $day['revenue']);
    $chartTotal += $day['revenue'];
}
$chartPoints = [];
$chartCount = count($chartDays);
foreach ($chartDays as $i => $day) {
    $x = $chartCount > 1 ? ($i / ($chartCount - 1)) * 640 : 320;
    $y = 150 - (($day['revenue'] / $maxRevenue) * 118);
    $chartPoints[] = round($x, 1) . ' ' . round($y, 1);
}
$chartLine = implode(' L', $chartPoints);
$chartArea = 'M0 150 L' . $chartLine . ' L640 150 Z';

/* Order status donut. */
$statusPalette = [
    'Pending'    => '#f59e0b',
    'Processing' => '#6366f1',
    'Shipped'    => '#3b82f6',
    'Delivered'  => '#00A651',
    'Cancelled'  => '#f43f5e',
];
$statusTotal = array_sum($statusCounts);
$donutSegments = [];
$donutOffset = 0;
foreach ($statusPalette as $status => $color) {
    $count = (int) ($statusCounts[$status] ?? 0);
    if ($count <= 0) {
        continue;
    }
    $from = $statusTotal > 0 ? ($donutOffset / $statusTotal) * 360 : 0;
    $donutOffset += $count;
    $to = $statusTotal > 0 ? ($donutOffset / $statusTotal) * 360 : 0;
    $donutSegments[] = $color . ' ' . round($from, 2) . 'deg ' . round($to, 2) . 'deg';
}
$donutBackground = $donutSegments
    ? 'conic-gradient(' . implode(', ', $donutSegments) . ')'
    : 'conic-gradient(#e2e8f0 0deg 360deg)';
?>

<div class="flex flex-wrap items-end justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Dashboard</h1>
        <p class="text-xs text-slate-500 mt-1">Live store overview — all figures come from your MySQL database.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="<?= e(url('index.php')) ?>" target="_blank" rel="noopener" class="px-3.5 py-2 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-700 hover:bg-slate-50 flex items-center gap-1.5">
            <i data-lucide="external-link" class="w-3.5 h-3.5"></i> View Store
        </a>
        <a href="<?= e(url('admin/product-edit.php')) ?>" class="px-3.5 py-2 bg-[#00A651] hover:bg-[#008f45] text-white rounded-lg text-xs font-bold flex items-center gap-1.5">
            <i data-lucide="plus" class="w-3.5 h-3.5"></i> Add Product
        </a>
    </div>
</div>

<!-- KPIs -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
    <div class="bg-white border border-slate-200 rounded-2xl p-4">
        <div class="flex items-center justify-between">
            <span class="text-[10px] uppercase tracking-wider font-bold text-slate-400">Total Sales</span>
            <span class="p-1.5 rounded-lg bg-emerald-50 text-[#00A651]"><i data-lucide="banknote" class="w-4 h-4"></i></span>
        </div>
        <p class="text-xl sm:text-2xl font-black text-slate-900 font-mono mt-2"><?= e(money($totalSales)) ?></p>
        <p class="text-[10px] text-slate-500 mt-0.5">Excluding cancelled orders</p>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl p-4">
        <div class="flex items-center justify-between">
            <span class="text-[10px] uppercase tracking-wider font-bold text-slate-400">Orders</span>
            <span class="p-1.5 rounded-lg bg-blue-50 text-blue-600"><i data-lucide="package" class="w-4 h-4"></i></span>
        </div>
        <p class="text-xl sm:text-2xl font-black text-slate-900 font-mono mt-2"><?= $totalOrders ?></p>
        <p class="text-[10px] text-slate-500 mt-0.5"><?= $pendingOrdersCount ?> pending action</p>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl p-4">
        <div class="flex items-center justify-between">
            <span class="text-[10px] uppercase tracking-wider font-bold text-slate-400">Products</span>
            <span class="p-1.5 rounded-lg bg-orange-50 text-[#FF6B00]"><i data-lucide="shopping-bag" class="w-4 h-4"></i></span>
        </div>
        <p class="text-xl sm:text-2xl font-black text-slate-900 font-mono mt-2"><?= $totalProducts ?></p>
        <p class="text-[10px] text-slate-500 mt-0.5"><?= $outOfStock ?> out of stock</p>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl p-4">
        <div class="flex items-center justify-between">
            <span class="text-[10px] uppercase tracking-wider font-bold text-slate-400">Customers</span>
            <span class="p-1.5 rounded-lg bg-indigo-50 text-indigo-600"><i data-lucide="users" class="w-4 h-4"></i></span>
        </div>
        <p class="text-xl sm:text-2xl font-black text-slate-900 font-mono mt-2"><?= $totalCustomers ?></p>
        <p class="text-[10px] text-slate-500 mt-0.5">Registered accounts</p>
    </div>
</div>

<!-- Charts -->
<div class="grid grid-cols-1 xl:grid-cols-3 gap-4 mb-4">
    <div class="xl:col-span-2 bg-white border border-slate-200 rounded-2xl p-5">
        <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
            <div>
                <h2 class="text-sm font-bold text-slate-900 flex items-center gap-1.5"><i data-lucide="trending-up" class="w-4 h-4 text-[#00A651]"></i> Sales Overview</h2>
                <p class="text-[11px] text-slate-500 mt-0.5">Revenue for the last 14 days</p>
            </div>
            <div class="text-right">
                <span class="block text-lg font-black font-mono text-slate-900"><?= e(money($chartTotal)) ?></span>
                <span class="text-[10px] uppercase tracking-wider font-bold text-slate-400">14-day total</span>
            </div>
        </div>

        <div class="relative">
            <svg viewBox="0 0 640 160" class="w-full h-40 sm:h-44" preserveAspectRatio="none" role="img" aria-label="Sales graph for the last 14 days">
                <defs>
                    <linearGradient id="salesFill" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#00A651" stop-opacity="0.28"></stop>
                        <stop offset="100%" stop-color="#00A651" stop-opacity="0"></stop>
                    </linearGradient>
                </defs>
                <line x1="0" y1="30" x2="640" y2="30" stroke="#f1f5f9" stroke-width="1"></line>
                <line x1="0" y1="90" x2="640" y2="90" stroke="#f1f5f9" stroke-width="1"></line>
                <line x1="0" y1="150" x2="640" y2="150" stroke="#e2e8f0" stroke-width="1"></line>
                <path d="<?= e($chartArea) ?>" fill="url(#salesFill)"></path>
                <path d="M<?= e($chartLine) ?>" fill="none" stroke="#00A651" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round"></path>
            </svg>
            <div class="flex justify-between text-[9px] sm:text-[10px] text-slate-400 font-medium mt-1">
                <?php foreach ([0, 4, 9, 13] as $index): ?>
                    <span><?= e(date('d M', strtotime($chartDays[$index]['day']))) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl p-5">
        <h2 class="text-sm font-bold text-slate-900 flex items-center gap-1.5 mb-4"><i data-lucide="pie-chart" class="w-4 h-4 text-[#00A651]"></i> Orders by Status</h2>

        <div class="relative w-36 h-36 mx-auto">
            <div class="w-full h-full rounded-full" style="background: <?= e($donutBackground) ?>"></div>
            <div class="absolute inset-[22px] bg-white rounded-full flex flex-col items-center justify-center shadow-inner">
                <span class="text-2xl font-black font-mono text-slate-900 leading-none"><?= $totalOrders ?></span>
                <span class="text-[9px] uppercase tracking-wider font-bold text-slate-400 mt-1">Total Orders</span>
            </div>
        </div>

        <div class="mt-5 space-y-2">
            <?php foreach ($statusPalette as $status => $color): ?>
                <div class="flex items-center justify-between text-[11px]">
                    <span class="flex items-center gap-2 text-slate-600">
                        <span class="w-2.5 h-2.5 rounded-full" style="background: <?= e($color) ?>"></span>
                        <?= e($status) ?>
                    </span>
                    <span class="font-mono font-bold text-slate-900"><?= (int) ($statusCounts[$status] ?? 0) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <!-- Recent orders -->
    <div class="lg:col-span-2 bg-white border border-slate-200 rounded-2xl overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-200 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900 flex items-center gap-1.5"><i data-lucide="clock" class="w-4 h-4 text-[#00A651]"></i> Recent Orders</h2>
            <a href="<?= e(url('admin/index.php?tab=orders')) ?>" class="text-[11px] font-bold text-[#00A651] hover:underline">View all</a>
        </div>

        <?php if (!$recentOrders): ?>
            <div class="p-10 text-center">
                <i data-lucide="inbox" class="w-8 h-8 text-slate-300 mx-auto mb-2"></i>
                <p class="text-xs text-slate-500">No orders yet. They will appear here automatically.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead class="bg-slate-50 text-slate-500 text-[10px] uppercase tracking-wider">
                        <tr>
                            <th class="text-left px-4 py-2 font-bold">Order</th>
                            <th class="text-left px-4 py-2 font-bold">Customer</th>
                            <th class="text-right px-4 py-2 font-bold">Total</th>
                            <th class="text-left px-4 py-2 font-bold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($recentOrders as $order): ?>
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-4 py-2.5">
                                    <a href="<?= e(url('admin/index.php?tab=orders&q=' . urlencode($order['order_code']))) ?>" class="font-mono font-bold text-slate-900 hover:text-[#00A651]">#<?= e($order['order_code']) ?></a>
                                    <span class="block text-[10px] text-slate-400"><?= e(format_date($order['created_at'], false)) ?></span>
                                </td>
                                <td class="px-4 py-2.5">
                                    <span class="font-semibold text-slate-800 block truncate max-w-[140px]"><?= e($order['customer_name']) ?></span>
                                    <span class="text-[10px] text-slate-500 font-mono"><?= e($order['phone']) ?></span>
                                </td>
                                <td class="px-4 py-2.5 text-right font-mono font-bold text-slate-900"><?= e(money($order['total_amount'])) ?></td>
                                <td class="px-4 py-2.5"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?= e(order_status_classes($order['status'])) ?>"><?= e($order['status']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Right column -->
    <div class="space-y-4">
        <div class="bg-white border border-slate-200 rounded-2xl p-4">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-bold text-slate-900 flex items-center gap-1.5"><i data-lucide="alert-triangle" class="w-4 h-4 text-[#FF6B00]"></i> Low Stock</h2>
                <a href="<?= e(url('admin/index.php?tab=products')) ?>" class="text-[10px] font-bold text-[#00A651] hover:underline">Manage</a>
            </div>
            <?php if (!$lowStock): ?>
                <p class="text-[11px] text-slate-500">No products yet.</p>
            <?php else: ?>
                <div class="space-y-2">
                    <?php foreach ($lowStock as $item): ?>
                        <div class="flex items-center gap-2">
                            <img src="<?= e(image_url($item['image'])) ?>" alt="" class="w-8 h-8 rounded object-cover border border-slate-200 shrink-0">
                            <span class="text-[11px] text-slate-700 truncate flex-1"><?= e($item['title']) ?></span>
                            <span class="text-[10px] font-mono font-bold <?= (int) $item['stock'] <= 0 ? 'text-rose-600' : 'text-amber-600' ?>"><?= (int) $item['stock'] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
