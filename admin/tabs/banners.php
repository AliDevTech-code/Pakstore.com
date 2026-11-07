<?php
/** Hero banners tab. */

$pdo = db();
$banners = $pdo->query('SELECT * FROM banners ORDER BY sort_order ASC, id DESC')->fetchAll();

$editId = (int) get_string('edit', '0');
$editing = null;
if ($editId > 0) {
    foreach ($banners as $banner) {
        if ((int) $banner['id'] === $editId) {
            $editing = $banner;
            break;
        }
    }
}
?>

<div class="flex flex-wrap items-end justify-between gap-3 mb-5">
    <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Hero Banners</h1>
        <p class="text-xs text-slate-500 mt-1"><?= count($banners) ?> banner<?= count($banners) === 1 ? '' : 's' ?>. Slides are shown on the homepage hero slider.</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start">
    <div class="bg-white border border-slate-200 rounded-2xl p-5 lg:sticky lg:top-36">
        <h2 class="text-sm font-bold text-slate-900 pb-2 border-b border-slate-100 mb-3 flex items-center gap-1.5">
            <i data-lucide="<?= $editing ? 'pencil' : 'plus' ?>" class="w-4 h-4 text-[#00A651]"></i>
            <?= $editing ? 'Edit Banner' : 'Add Banner' ?>
        </h2>

        <form action="<?= e(url('admin/banners.php')) ?>" method="post" enctype="multipart/form-data" class="space-y-3">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?= $editing ? (int) $editing['id'] : 0 ?>">
            <input type="hidden" name="subtitle" value="<?= e($editing['subtitle'] ?? '') ?>">
            <input type="hidden" name="badge" value="<?= e($editing['badge'] ?? '') ?>">
            <input type="hidden" name="category" value="<?= e($editing['category'] ?? '') ?>">
            <input type="hidden" name="sort_order" value="<?= e($editing['sort_order'] ?? '0') ?>">
            <?php if (!$editing || (int) $editing['is_active'] === 1): ?><input type="hidden" name="is_active" value="1"><?php endif; ?>

            <div>
                <label for="title" class="block text-xs font-semibold text-slate-700 mb-1">Banner Title *</label>
                <input id="title" name="title" type="text" required maxlength="200" value="<?= e($editing['title'] ?? '') ?>" placeholder="e.g. Mega Winter Sale" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651]">
            </div>

            <div>
                <label for="image_file" class="block text-xs font-semibold text-slate-700 mb-1">Banner Image (max 3 MB)</label>
                <input id="image_file" name="image_file" type="file" accept="image/jpeg,image/png,image/webp,image/gif" class="w-full text-xs border border-slate-300 rounded-lg p-1.5 bg-white file:mr-2 file:px-2.5 file:py-1 file:rounded file:border-0 file:bg-slate-900 file:text-white file:text-[11px] file:font-bold">
                <?php if (!empty($editing['image'])): ?>
                    <img src="<?= e(image_url($editing['image'])) ?>" alt="" class="mt-2 h-20 w-full object-cover rounded-lg border border-slate-200">
                <?php endif; ?>
            </div>

            <div>
                <label for="link" class="block text-xs font-semibold text-slate-700 mb-1">Button Link (optional)</label>
                <input id="link" name="link" type="text" maxlength="300" value="<?= e($editing['link'] ?? '#catalog-section') ?>" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg font-mono focus:outline-[#00A651]">
            </div>

            <button type="submit" class="w-full py-2.5 bg-[#00A651] hover:bg-[#008f45] text-white rounded-lg text-xs font-bold flex items-center justify-center gap-1.5">
                <i data-lucide="save" class="w-3.5 h-3.5"></i> <?= $editing ? 'Save Banner' : 'Add Banner' ?>
            </button>

            <?php if ($editing): ?>
                <a href="<?= e(url('admin/index.php?tab=banners')) ?>" class="block text-center text-[11px] font-semibold text-slate-500 hover:text-slate-800">Cancel edit</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="lg:col-span-2 space-y-3">
        <?php if (!$banners): ?>
            <div class="bg-white border border-slate-200 rounded-2xl p-10 text-center">
                <i data-lucide="image" class="w-8 h-8 text-slate-300 mx-auto mb-2"></i>
                <h2 class="text-sm font-bold text-slate-800">No banners yet</h2>
                <p class="text-xs text-slate-500 mt-1">The homepage will show a neutral gradient hero until you add one.</p>
            </div>
        <?php else: ?>
            <?php foreach ($banners as $banner): ?>
                <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden flex flex-col sm:flex-row">
                    <div class="sm:w-48 h-28 sm:h-auto bg-slate-100 shrink-0">
                        <img src="<?= e(image_url($banner['image'])) ?>" alt="<?= e($banner['title']) ?>" class="w-full h-full object-cover">
                    </div>
                    <div class="p-4 flex-1 flex flex-col justify-between gap-2">
                        <div>
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="text-sm font-bold text-slate-900"><?= e($banner['title']) ?></h3>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?= (int) $banner['is_active'] === 1 ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' ?>"><?= (int) $banner['is_active'] === 1 ? 'Active' : 'Hidden' ?></span>
                            </div>
                            <?php if (!empty($banner['subtitle'])): ?><p class="text-[11px] text-slate-500 mt-0.5"><?= e($banner['subtitle']) ?></p><?php endif; ?>
                            <p class="text-[10px] text-slate-400 mt-1 font-mono">Sort: <?= (int) $banner['sort_order'] ?> &middot; Link: <?= e($banner['link']) ?></p>
                        </div>
                        <div class="flex items-center justify-end gap-1">
                            <a href="<?= e(url('admin/index.php?tab=banners&edit=' . (int) $banner['id'])) ?>" class="px-2.5 py-1.5 text-[11px] font-bold text-slate-600 hover:text-[#00A651] hover:bg-emerald-50 rounded-lg flex items-center gap-1">
                                <i data-lucide="pencil" class="w-3.5 h-3.5"></i> Edit
                            </a>
                            <form action="<?= e(url('admin/banners.php')) ?>" method="post" data-confirm="Delete banner &quot;<?= e($banner['title']) ?>&quot;?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int) $banner['id'] ?>">
                                <button type="submit" class="px-2.5 py-1.5 text-[11px] font-bold text-rose-600 hover:bg-rose-50 rounded-lg flex items-center gap-1">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
