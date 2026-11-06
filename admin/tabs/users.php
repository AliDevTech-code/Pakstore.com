<?php
/** Customers tab. */

$pdo = db();
$q = get_string('q');

$where = ['1 = 1'];
$params = [];
if ($q !== '') {
    $where[] = '(u.full_name LIKE ? OR u.phone LIKE ? OR u.email LIKE ? OR u.city LIKE ?)';
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like, $like);
}

$stmt = $pdo->prepare(
    'SELECT u.*,
            (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id) AS order_count,
            (SELECT COALESCE(SUM(o.total_amount),0) FROM orders o WHERE o.user_id = u.id AND o.status <> \'Cancelled\') AS total_spent
     FROM users u WHERE ' . implode(' AND ', $where) . ' ORDER BY u.id DESC LIMIT 300'
);
$stmt->execute($params);
$customers = $stmt->fetchAll();
?>

<div class="flex flex-wrap items-end justify-between gap-3 mb-5">
    <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Customers</h1>
        <p class="text-xs text-slate-500 mt-1"><?= count($customers) ?> registered customer<?= count($customers) === 1 ? '' : 's' ?>. Guest checkout orders are visible under Orders.</p>
    </div>
</div>

<form method="get" action="<?= e(url('admin/index.php')) ?>" class="bg-white border border-slate-200 rounded-xl p-3 mb-4 flex gap-2">
    <input type="hidden" name="tab" value="users">
    <div class="relative flex-1">
        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search by name, phone, email or city..." class="w-full pl-9 pr-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651]">
    </div>
    <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-lg text-xs font-bold">Search</button>
</form>

<div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
    <?php if (!$customers): ?>
        <div class="p-10 text-center">
            <i data-lucide="users" class="w-8 h-8 text-slate-300 mx-auto mb-2"></i>
            <h2 class="text-sm font-bold text-slate-800">No customers found</h2>
            <p class="text-xs text-slate-500 mt-1">Customers appear when they create an account on the storefront.</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-slate-50 text-slate-500 text-[10px] uppercase tracking-wider">
                    <tr>
                        <th class="text-left px-4 py-2.5 font-bold">Customer</th>
                        <th class="text-left px-4 py-2.5 font-bold">Contact</th>
                        <th class="text-left px-4 py-2.5 font-bold">City</th>
                        <th class="text-center px-4 py-2.5 font-bold">Orders</th>
                        <th class="text-right px-4 py-2.5 font-bold">Spent</th>
                        <th class="text-left px-4 py-2.5 font-bold">Joined</th>
                        <th class="text-right px-4 py-2.5 font-bold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($customers as $customer): ?>
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="w-8 h-8 rounded-full bg-emerald-50 text-[#00A651] font-black text-xs flex items-center justify-center shrink-0"><?= e(strtoupper(mb_substr($customer['full_name'], 0, 1))) ?></span>
                                    <span class="font-bold text-slate-900 truncate max-w-[160px]"><?= e($customer['full_name']) ?></span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="block font-mono text-slate-700"><?= e($customer['phone']) ?></span>
                                <span class="block text-[10px] text-slate-400 break-all"><?= e($customer['email']) ?></span>
                            </td>
                            <td class="px-4 py-3 text-slate-600"><?= e($customer['city'] ?: '—') ?></td>
                            <td class="px-4 py-3 text-center font-mono font-bold text-slate-700"><?= (int) $customer['order_count'] ?></td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-slate-900"><?= e(money($customer['total_spent'])) ?></td>
                            <td class="px-4 py-3 text-[11px] text-slate-500"><?= e(format_date($customer['created_at'], false)) ?></td>
                            <td class="px-4 py-3 text-right">
                                <form action="<?= e(url('admin/users.php')) ?>" method="post" data-confirm="Delete customer &quot;<?= e($customer['full_name']) ?>&quot;? Their orders will remain.">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int) $customer['id'] ?>">
                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded" title="Delete customer">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
