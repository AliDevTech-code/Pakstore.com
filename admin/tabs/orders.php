<?php
/** Admin orders tab: filters + full order management. */

$pdo = db();
$q = get_string('q');
$statusFilter = get_string('status');
$allowedStatuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];

$where = ['1 = 1'];
$params = [];
if ($q !== '') {
    $where[] = '(o.order_code LIKE ? OR o.customer_name LIKE ? OR o.phone LIKE ? OR o.city LIKE ?)';
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like, $like);
}
if (in_array($statusFilter, $allowedStatuses, true)) {
    $where[] = 'o.status = ?';
    $params[] = $statusFilter;
}

$stmt = $pdo->prepare(
    'SELECT o.* FROM orders o WHERE ' . implode(' AND ', $where) . ' ORDER BY o.id DESC LIMIT 200'
);
$stmt->execute($params);
$orders = $stmt->fetchAll();

foreach ($orders as &$order) {
    $order['items'] = order_items((int) $order['id']);
}
unset($order);

$counts = [];
foreach ($pdo->query('SELECT status, COUNT(*) c FROM orders GROUP BY status')->fetchAll() as $row) {
    $counts[$row['status']] = (int) $row['c'];
}
$filterBase = 'admin/index.php?tab=orders';
?>

<div class="flex flex-wrap items-end justify-between gap-3 mb-5">
    <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Orders</h1>
        <p class="text-xs text-slate-500 mt-1"><?= count($orders) ?> order<?= count($orders) === 1 ? '' : 's' ?> shown. Update status, add courier tracking, or print a slip.</p>
    </div>
    <a href="<?= e(url('admin/index.php?tab=orders')) ?>" class="px-3.5 py-2 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-700 hover:bg-slate-50 flex items-center gap-1.5">
        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i> Reset filters
    </a>
</div>

<!-- Status quick filters -->
<div class="flex flex-wrap items-center gap-2 mb-4">
    <a href="<?= e(url($filterBase)) ?>" class="px-3 py-1.5 rounded-lg text-[11px] font-bold border <?= $statusFilter === '' ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-300' ?>">All (<?= array_sum($counts) ?>)</a>
    <?php foreach ($allowedStatuses as $status): ?>
        <a href="<?= e(url($filterBase . '&status=' . urlencode($status) . ($q !== '' ? '&q=' . urlencode($q) : ''))) ?>"
           class="px-3 py-1.5 rounded-lg text-[11px] font-bold border <?= $statusFilter === $status ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-300' ?>">
            <?= e($status) ?> (<?= (int) ($counts[$status] ?? 0) ?>)
        </a>
    <?php endforeach; ?>
</div>

<form method="get" action="<?= e(url('admin/index.php')) ?>" class="bg-white border border-slate-200 rounded-xl p-3 mb-5 flex flex-col sm:flex-row gap-2">
    <input type="hidden" name="tab" value="orders">
    <?php if ($statusFilter !== ''): ?><input type="hidden" name="status" value="<?= e($statusFilter) ?>"><?php endif; ?>
    <div class="relative flex-1">
        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search order ID, customer name, phone or city..." class="w-full pl-9 pr-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651]">
    </div>
    <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-lg text-xs font-bold">Search</button>
</form>

<?php if (!$orders): ?>
    <div class="bg-white border border-slate-200 rounded-2xl p-12 text-center">
        <i data-lucide="package-open" class="w-9 h-9 text-slate-300 mx-auto mb-2"></i>
        <h2 class="text-sm font-bold text-slate-800">No orders found</h2>
        <p class="text-xs text-slate-500 mt-1"><?= $q !== '' || $statusFilter !== '' ? 'Try a different filter or search term.' : 'New COD orders from the storefront will appear here.' ?></p>
    </div>
<?php else: ?>
    <div class="space-y-3">
        <?php foreach ($orders as $order): ?>
            <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
                <div class="px-4 py-3 bg-slate-50 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
                        <div>
                            <span class="text-[10px] uppercase tracking-wider text-slate-400 font-bold block">Order ID</span>
                            <span class="font-mono text-sm font-black text-slate-900">#<?= e($order['order_code']) ?></span>
                        </div>
                        <div class="hidden sm:block">
                            <span class="text-[10px] uppercase tracking-wider text-slate-400 font-bold block">Placed</span>
                            <span class="text-[11px] text-slate-700 font-semibold"><?= e(format_date($order['created_at'])) ?></span>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase tracking-wider text-slate-400 font-bold block">Total (COD)</span>
                            <span class="font-mono text-sm font-black text-emerald-700"><?= e(money($order['total_amount'])) ?></span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold <?= e(order_status_classes($order['status'])) ?>"><?= e($order['status']) ?></span>
                        <a href="<?= e(url('admin/order-slip.php?id=' . (int) $order['id'])) ?>" target="_blank" rel="noopener" class="px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg text-[11px] font-bold text-slate-700 hover:bg-slate-50 flex items-center gap-1">
                            <i data-lucide="printer" class="w-3.5 h-3.5"></i> Slip
                        </a>
                        <form action="<?= e(url('admin/orders.php')) ?>" method="post" data-confirm="Delete order #<?= e($order['order_code']) ?>? This cannot be undone.">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int) $order['id'] ?>">
                            <button type="submit" class="p-1.5 bg-white border border-rose-200 text-rose-600 hover:bg-rose-50 rounded-lg" title="Delete order">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="p-4 grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <!-- Customer -->
                    <div class="text-xs space-y-1">
                        <span class="text-[10px] uppercase tracking-wider text-slate-400 font-bold block mb-1">Customer</span>
                        <p class="font-bold text-slate-900"><?= e($order['customer_name']) ?></p>
                        <p class="font-mono text-slate-600"><?= e($order['phone']) ?></p>
                        <?php if (!empty($order['email'])): ?><p class="text-slate-500 break-all"><?= e($order['email']) ?></p><?php endif; ?>
                        <p class="text-slate-600 leading-relaxed pt-1"><?= nl2br(e($order['address'])) ?>, <?= e($order['city']) ?></p>
                        <?php if (!empty($order['order_notes'])): ?>
                            <p class="text-[11px] text-amber-700 bg-amber-50 border border-amber-200 rounded p-1.5 mt-1"><strong>Note:</strong> <?= e($order['order_notes']) ?></p>
                        <?php endif; ?>
                        <div class="pt-1.5 flex flex-wrap gap-2">
                            <a href="tel:<?= e($order['phone']) ?>" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded text-[10px] font-bold text-slate-700 flex items-center gap-1"><i data-lucide="phone" class="w-3 h-3"></i> Call</a>
                            <?php if (setting('whatsapp') !== '' || $order['phone']): ?>
                                <a href="https://wa.me/<?= e(clean_phone_wa($order['phone'])) ?>" target="_blank" rel="noopener" class="px-2 py-1 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded text-[10px] font-bold text-emerald-700 flex items-center gap-1"><i data-lucide="message-circle" class="w-3 h-3"></i> WhatsApp</a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Items -->
                    <div class="text-xs">
                        <span class="text-[10px] uppercase tracking-wider text-slate-400 font-bold block mb-1">Items (<?= count($order['items']) ?>)</span>
                        <div class="space-y-1.5 max-h-40 overflow-y-auto pr-1">
                            <?php foreach ($order['items'] as $item): ?>
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-1.5 min-w-0">
                                        <img src="<?= e(image_url($item['image'])) ?>" alt="" class="w-6 h-6 rounded object-cover border border-slate-100 shrink-0">
                                        <span class="truncate text-slate-700"><?= e($item['title']) ?> <span class="text-slate-400">&times;<?= (int) $item['quantity'] ?></span></span>
                                    </div>
                                    <span class="font-mono font-bold text-slate-900 shrink-0"><?= e(money((float) $item['price'] * (int) $item['quantity'])) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="pt-2 mt-2 border-t border-slate-100 text-[11px] text-slate-500 space-y-0.5">
                            <div class="flex justify-between"><span>Subtotal</span><span class="font-mono"><?= e(money($order['subtotal'])) ?></span></div>
                            <div class="flex justify-between"><span>Shipping</span><span class="font-mono"><?= e(money($order['shipping_fee'])) ?></span></div>
                            <div class="flex justify-between font-bold text-slate-900"><span>Total</span><span class="font-mono"><?= e(money($order['total_amount'])) ?></span></div>
                        </div>
                    </div>

                    <!-- Update -->
                    <div>
                        <span class="text-[10px] uppercase tracking-wider text-slate-400 font-bold block mb-1">Update Order</span>
                        <form action="<?= e(url('admin/orders.php')) ?>" method="post" class="space-y-2">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" value="<?= (int) $order['id'] ?>">
                            <input type="hidden" name="redirect" value="admin/index.php?tab=orders<?= $statusFilter !== '' ? '&status=' . urlencode($statusFilter) : '' ?>">

                            <select name="status" class="w-full px-2.5 py-1.5 text-xs border border-slate-300 rounded-lg bg-white focus:outline-[#00A651]">
                                <?php foreach ($allowedStatuses as $status): ?>
                                    <option value="<?= e($status) ?>" <?= $order['status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                                <?php endforeach; ?>
                            </select>

                            <div class="grid grid-cols-2 gap-1.5">
                                <input type="text" name="tracking_courier" maxlength="60" value="<?= e($order['tracking_courier'] ?: 'TCS') ?>" placeholder="Courier" class="w-full px-2.5 py-1.5 text-[11px] border border-slate-300 rounded-lg focus:outline-[#00A651]">
                                <input type="text" name="tracking_number" maxlength="120" value="<?= e($order['tracking_number']) ?>" placeholder="Tracking #" class="w-full px-2.5 py-1.5 text-[11px] border border-slate-300 rounded-lg font-mono focus:outline-[#00A651]">
                            </div>

                            <div class="flex items-center gap-1.5">
                                <button type="submit" class="flex-1 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-[11px] font-bold flex items-center justify-center gap-1">
                                    <i data-lucide="save" class="w-3 h-3"></i> Save
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
