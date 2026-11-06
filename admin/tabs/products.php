<?php
/** Product list tab. */

$pdo = db();
$q = get_string('q');
$categoryFilter = (int) get_string('category', '0');

$where = ['1 = 1'];
$params = [];
if ($q !== '') {
    $where[] = '(p.title LIKE ? OR p.sku LIKE ?)';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
}
if ($categoryFilter > 0) {
    $where[] = 'p.category_id = ?';
    $params[] = $categoryFilter;
}

$stmt = $pdo->prepare(
    'SELECT p.*, c.name AS category_name
     FROM products p LEFT JOIN categories c ON c.id = p.category_id
     WHERE ' . implode(' AND ', $where) . '
     ORDER BY p.id DESC LIMIT 300'
);
$stmt->execute($params);
$products = $stmt->fetchAll();
$categories = all_categories();
?>

<div class="flex flex-wrap items-end justify-between gap-3 mb-5">
    <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Products</h1>
        <p class="text-xs text-slate-500 mt-1"><?= count($products) ?> product<?= count($products) === 1 ? '' : 's' ?> in the catalog.</p>
    </div>
    <a href="<?= e(url('admin/product-edit.php')) ?>" class="px-3.5 py-2 bg-[#00A651] hover:bg-[#008f45] text-white rounded-lg text-xs font-bold flex items-center gap-1.5">
        <i data-lucide="plus" class="w-3.5 h-3.5"></i> Add New Product
    </a>
</div>

<form method="get" action="<?= e(url('admin/index.php')) ?>" class="bg-white border border-slate-200 rounded-xl p-3 mb-4 flex flex-col sm:flex-row gap-2">
    <input type="hidden" name="tab" value="products">
    <div class="relative flex-1">
        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search by title or SKU..." class="w-full pl-9 pr-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651]">
    </div>
    <select name="category" class="px-3 py-2 text-xs border border-slate-300 rounded-lg bg-white focus:outline-[#00A651]" onchange="this.form.submit()">
        <option value="0">All categories</option>
        <?php foreach ($categories as $cat): ?>
            <option value="<?= (int) $cat['id'] ?>" <?= $categoryFilter === (int) $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-lg text-xs font-bold">Filter</button>
</form>

<div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
    <?php if (!$products): ?>
        <div class="p-10 text-center">
            <i data-lucide="shopping-bag" class="w-8 h-8 text-slate-300 mx-auto mb-2"></i>
            <h2 class="text-sm font-bold text-slate-800">No products found</h2>
            <p class="text-xs text-slate-500 mt-1">Add your first product to start selling.</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-slate-50 text-slate-500 text-[10px] uppercase tracking-wider">
                    <tr>
                        <th class="text-left px-4 py-2.5 font-bold">Product</th>
                        <th class="text-left px-4 py-2.5 font-bold">Category</th>
                        <th class="text-right px-4 py-2.5 font-bold">Price</th>
                        <th class="text-center px-4 py-2.5 font-bold">Stock</th>
                        <th class="text-center px-4 py-2.5 font-bold">Status</th>
                        <th class="text-right px-4 py-2.5 font-bold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($products as $product): ?>
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2.5">
                                    <img src="<?= e(image_url($product['image'])) ?>" alt="" class="w-10 h-10 rounded-lg object-cover border border-slate-200 shrink-0">
                                    <div class="min-w-0">
                                        <span class="block font-bold text-slate-900 truncate max-w-[220px]"><?= e($product['title']) ?></span>
                                        <span class="block text-[10px] text-slate-400 font-mono"><?= e($product['sku']) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-slate-600"><?= e($product['category_name'] ?: '—') ?></td>
                            <td class="px-4 py-3 text-right">
                                <span class="font-mono font-bold text-slate-900 block"><?= e(money($product['price'])) ?></span>
                                <?php if ((float) $product['original_price'] > (float) $product['price']): ?>
                                    <span class="font-mono text-[10px] text-slate-400 line-through"><?= e(money($product['original_price'])) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-center font-mono font-bold <?= (int) $product['stock'] <= 0 ? 'text-rose-600' : ((int) $product['stock'] <= 5 ? 'text-amber-600' : 'text-slate-700') ?>"><?= (int) $product['stock'] ?></td>
                            <td class="px-4 py-3 text-center">
                                <?php if ((int) $product['in_stock'] === 1 && (int) $product['stock'] > 0): ?>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">In Stock</span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700">Out</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="<?= e(url('product.php?id=' . (int) $product['id'])) ?>" target="_blank" rel="noopener" class="p-1.5 text-slate-400 hover:text-slate-900 hover:bg-slate-100 rounded" title="View">
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    </a>
                                    <a href="<?= e(url('admin/product-edit.php?id=' . (int) $product['id'])) ?>" class="p-1.5 text-slate-400 hover:text-[#00A651] hover:bg-emerald-50 rounded" title="Edit">
                                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                    </a>
                                    <form action="<?= e(url('admin/products.php')) ?>" method="post" data-confirm="Delete &quot;<?= e($product['title']) ?>&quot;? This cannot be undone.">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int) $product['id'] ?>">
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded" title="Delete">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
