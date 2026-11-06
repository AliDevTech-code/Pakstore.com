<?php
/** Categories tab. */

$pdo = db();
$categories = $pdo->query(
    'SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS product_count
     FROM categories c ORDER BY c.name ASC'
)->fetchAll();

$editId = (int) get_string('edit', '0');
$editing = null;
if ($editId > 0) {
    foreach ($categories as $cat) {
        if ((int) $cat['id'] === $editId) {
            $editing = $cat;
            break;
        }
    }
}

$iconSuggestions = ['tag', 'smartphone', 'watch', 'headphones', 'shopping-bag', 'shirt', 'footprints', 'sparkles', 'wallet', 'home', 'laptop', 'gem'];
?>

<div class="flex flex-wrap items-end justify-between gap-3 mb-5">
    <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Categories</h1>
        <p class="text-xs text-slate-500 mt-1"><?= count($categories) ?> categor<?= count($categories) === 1 ? 'y' : 'ies' ?>. Used by the storefront menu and filters.</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start">
    <!-- Form -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 lg:sticky lg:top-36">
        <h2 class="text-sm font-bold text-slate-900 pb-2 border-b border-slate-100 mb-3 flex items-center gap-1.5">
            <i data-lucide="<?= $editing ? 'pencil' : 'plus' ?>" class="w-4 h-4 text-[#00A651]"></i>
            <?= $editing ? 'Edit Category' : 'Add Category' ?>
        </h2>

        <form action="<?= e(url('admin/categories.php')) ?>" method="post" class="space-y-3">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?= $editing ? (int) $editing['id'] : 0 ?>">

            <div>
                <label for="name" class="block text-xs font-semibold text-slate-700 mb-1">Category Name *</label>
                <input id="name" name="name" type="text" required maxlength="120" value="<?= e($editing['name'] ?? '') ?>" placeholder="e.g. Smart Watches" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651]">
            </div>

            <div>
                <label for="slug" class="block text-xs font-semibold text-slate-700 mb-1">Slug (optional)</label>
                <input id="slug" name="slug" type="text" maxlength="140" value="<?= e($editing['slug'] ?? '') ?>" placeholder="auto-generated" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg font-mono focus:outline-[#00A651]">
            </div>

            <div>
                <label for="icon" class="block text-xs font-semibold text-slate-700 mb-1">Lucide Icon</label>
                <input id="icon" name="icon" type="text" maxlength="60" value="<?= e($editing['icon'] ?? 'tag') ?>" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg font-mono focus:outline-[#00A651]">
                <div class="flex flex-wrap gap-1 mt-1.5">
                    <?php foreach ($iconSuggestions as $icon): ?>
                        <button type="button" onclick="document.getElementById('icon').value='<?= e($icon) ?>'" class="px-1.5 py-0.5 text-[10px] rounded bg-slate-100 hover:bg-emerald-50 hover:text-[#00A651] font-mono border border-slate-200"><?= e($icon) ?></button>
                    <?php endforeach; ?>
                </div>
            </div>

            <button type="submit" class="w-full py-2.5 bg-[#00A651] hover:bg-[#008f45] text-white rounded-lg text-xs font-bold flex items-center justify-center gap-1.5">
                <i data-lucide="save" class="w-3.5 h-3.5"></i> <?= $editing ? 'Save Changes' : 'Add Category' ?>
            </button>

            <?php if ($editing): ?>
                <a href="<?= e(url('admin/index.php?tab=categories')) ?>" class="block text-center text-[11px] font-semibold text-slate-500 hover:text-slate-800">Cancel edit</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- List -->
    <div class="lg:col-span-2 bg-white border border-slate-200 rounded-2xl overflow-hidden">
        <?php if (!$categories): ?>
            <div class="p-10 text-center">
                <i data-lucide="tag" class="w-8 h-8 text-slate-300 mx-auto mb-2"></i>
                <h2 class="text-sm font-bold text-slate-800">No categories yet</h2>
                <p class="text-xs text-slate-500 mt-1">Use the form to create your first category.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead class="bg-slate-50 text-slate-500 text-[10px] uppercase tracking-wider">
                        <tr>
                            <th class="text-left px-4 py-2.5 font-bold">Category</th>
                            <th class="text-left px-4 py-2.5 font-bold">Slug</th>
                            <th class="text-center px-4 py-2.5 font-bold">Products</th>
                            <th class="text-right px-4 py-2.5 font-bold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($categories as $cat): ?>
                            <tr class="hover:bg-slate-50/60">
                                <td class="px-4 py-2.5">
                                    <div class="flex items-center gap-2">
                                        <span class="w-7 h-7 rounded-lg bg-emerald-50 text-[#00A651] flex items-center justify-center"><i data-lucide="<?= e($cat['icon'] ?: 'tag') ?>" class="w-3.5 h-3.5"></i></span>
                                        <span class="font-bold text-slate-900"><?= e($cat['name']) ?></span>
                                    </div>
                                </td>
                                <td class="px-4 py-2.5 font-mono text-slate-500"><?= e($cat['slug']) ?></td>
                                <td class="px-4 py-2.5 text-center">
                                    <a href="<?= e(url('admin/index.php?tab=products&category=' . (int) $cat['id'])) ?>" class="font-mono font-bold text-slate-700 hover:text-[#00A651]"><?= (int) $cat['product_count'] ?></a>
                                </td>
                                <td class="px-4 py-2.5">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="<?= e(url('admin/index.php?tab=categories&edit=' . (int) $cat['id'])) ?>" class="p-1.5 text-slate-400 hover:text-[#00A651] hover:bg-emerald-50 rounded" title="Edit">
                                            <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                        </a>
                                        <form action="<?= e(url('admin/categories.php')) ?>" method="post" data-confirm="Delete category &quot;<?= e($cat['name']) ?>&quot;? Products will become uncategorized.">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= (int) $cat['id'] ?>">
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
</div>
