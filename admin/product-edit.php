<?php
/** Add / edit product form (simple: name, description, stock, price, images). */

require_once __DIR__ . '/includes/auth.php';

$editingId = (int) get_string('id', '0');
$product = [
    'id' => 0,
    'title' => '',
    'category_id' => null,
    'price' => '',
    'original_price' => '',
    'stock' => '0',
    'image' => '',
    'gallery' => '',
    'badge' => '',
    'description' => '',
    'features' => '',
    'sku' => '',
    'in_stock' => 1,
];

if ($editingId > 0) {
    $stmt = db()->prepare('SELECT * FROM products WHERE id = ? LIMIT 1');
    $stmt->execute([$editingId]);
    $found = $stmt->fetch();
    if (!$found) {
        flash('error', 'Product not found.');
        redirect('admin/index.php?tab=products');
    }
    $product = $found;
}

$categories = all_categories();
$activeTab = 'products';
$pageTitle = ($editingId > 0 ? 'Edit Product' : 'Add Product') . ' - Admin';
require __DIR__ . '/includes/header.php';

$galleryValue = implode("\n", json_array($product['gallery']));
$featuresValue = implode("\n", json_array($product['features']));

$previewImages = json_array($product['gallery']);
if (trim((string) $product['image']) !== '' && !in_array($product['image'], $previewImages, true)) {
    array_unshift($previewImages, $product['image']);
}
?>

<div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between gap-3 mb-5">
        <div>
            <a href="<?= e(url('admin/index.php?tab=products')) ?>" class="text-[11px] font-bold text-slate-500 hover:text-[#00A651] flex items-center gap-1 mb-1">
                <i data-lucide="arrow-left" class="w-3 h-3"></i> Back to products
            </a>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight"><?= $editingId > 0 ? 'Edit Product' : 'Add New Product' ?></h1>
            <p class="text-xs text-slate-500 mt-1">Sirf zaroori details bharein — baqi automatic.</p>
        </div>
    </div>

    <form action="<?= e(url('admin/products.php')) ?>" method="post" enctype="multipart/form-data" class="space-y-4">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= (int) $product['id'] ?>">
        <input type="hidden" name="original_price" value="<?= e($product['original_price']) ?>">
        <input type="hidden" name="badge" value="<?= e($product['badge']) ?>">
        <input type="hidden" name="sku" value="<?= e($product['sku']) ?>">
        <textarea name="gallery" class="hidden"><?= e($galleryValue) ?></textarea>
        <textarea name="features" class="hidden"><?= e($featuresValue) ?></textarea>

        <div class="bg-white border border-slate-200 rounded-2xl p-5 space-y-4">
            <h2 class="text-sm font-bold text-slate-900 pb-2 border-b border-slate-100 flex items-center gap-1.5">
                <i data-lucide="package" class="w-4 h-4 text-[#00A651]"></i> Product Details
            </h2>

            <div>
                <label for="title" class="block text-xs font-semibold text-slate-700 mb-1">Product Name *</label>
                <input id="title" name="title" type="text" required maxlength="200" value="<?= e($product['title']) ?>" placeholder="e.g. Cotton Casual Shirt" class="w-full px-3.5 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651] font-medium">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="category_id" class="block text-xs font-semibold text-slate-700 mb-1">Category</label>
                    <select id="category_id" name="category_id" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg bg-white focus:outline-[#00A651]">
                        <option value="0">— Uncategorized —</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int) $cat['id'] ?>" <?= (int) $product['category_id'] === (int) $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="price" class="block text-xs font-semibold text-slate-700 mb-1">Price (Rs.) *</label>
                    <input id="price" name="price" type="number" min="0" step="0.01" required value="<?= e($product['price']) ?>" placeholder="1999" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg font-mono font-bold focus:outline-[#00A651]">
                </div>
                <div>
                    <label for="stock" class="block text-xs font-semibold text-slate-700 mb-1">Stock *</label>
                    <input id="stock" name="stock" type="number" min="0" step="1" required value="<?= e($product['stock']) ?>" placeholder="20" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg font-mono focus:outline-[#00A651]">
                    <span class="text-[10px] text-slate-400">0 = out of stock</span>
                </div>
            </div>

            <div>
                <label for="description" class="block text-xs font-semibold text-slate-700 mb-1">Product Description</label>
                <textarea id="description" name="description" rows="4" placeholder="Product ki tafseel likhein..." class="w-full px-3.5 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651]"><?= e($product['description']) ?></textarea>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-5 space-y-4">
            <h2 class="text-sm font-bold text-slate-900 pb-2 border-b border-slate-100 flex items-center gap-1.5">
                <i data-lucide="images" class="w-4 h-4 text-[#00A651]"></i> Product Images
            </h2>

            <div>
                <label for="image_files" class="block text-xs font-semibold text-slate-700 mb-1">Upload Images (JPG / PNG / WEBP, max 3 MB each)</label>
                <input id="image_files" name="image_files[]" type="file" accept="image/jpeg,image/png,image/webp,image/gif" multiple class="w-full text-xs border border-slate-300 rounded-lg p-1.5 bg-white file:mr-2 file:px-2.5 file:py-1 file:rounded file:border-0 file:bg-slate-900 file:text-white file:text-[11px] file:font-bold">
                <p class="text-[10px] text-slate-500 mt-1">Ek hi product ki alag alag colors upload karein (e.g. white shirt, black shirt). Pehli image main image banegi, baqi gallery mein chali jayengi.</p>
            </div>

            <?php if ($previewImages): ?>
                <div>
                    <span class="block text-[11px] font-semibold text-slate-600 mb-1.5">Current images (<?= count($previewImages) ?>)</span>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($previewImages as $img): ?>
                            <span class="w-16 h-16 rounded-lg border border-slate-200 overflow-hidden bg-slate-50 block">
                                <img src="<?= e(image_url($img)) ?>" alt="" class="w-full h-full object-cover">
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-end gap-2 pb-4">
            <a href="<?= e(url('admin/index.php?tab=products')) ?>" class="w-full sm:w-auto px-4 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-200 bg-slate-100 rounded-lg text-center">Cancel</a>
            <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-[#00A651] hover:bg-[#008f45] text-white font-bold text-xs rounded-lg flex items-center justify-center gap-1.5 shadow-sm">
                <i data-lucide="save" class="w-3.5 h-3.5"></i> <?= $editingId > 0 ? 'Save Changes' : 'Add Product' ?>
            </button>
        </div>
    </form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
