<?php
/** Add / edit product form. */

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
$suggestions = glob(dirname(__DIR__) . '/assets/images/*.{jpg,jpeg,png,webp}', GLOB_BRACE) ?: [];
?>

<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between gap-3 mb-5">
        <div>
            <a href="<?= e(url('admin/index.php?tab=products')) ?>" class="text-[11px] font-bold text-slate-500 hover:text-[#00A651] flex items-center gap-1 mb-1">
                <i data-lucide="arrow-left" class="w-3 h-3"></i> Back to products
            </a>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight"><?= $editingId > 0 ? 'Edit Product' : 'Add New Product' ?></h1>
            <p class="text-xs text-slate-500 mt-1"><?= $editingId > 0 ? 'Update the details below and save.' : 'Fill in the product details. It will appear on the storefront immediately.' ?></p>
        </div>
    </div>

    <form action="<?= e(url('admin/products.php')) ?>" method="post" enctype="multipart/form-data" class="space-y-4">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= (int) $product['id'] ?>">

        <div class="bg-white border border-slate-200 rounded-2xl p-5 space-y-4">
            <h2 class="text-sm font-bold text-slate-900 pb-2 border-b border-slate-100">Basic Information</h2>

            <div>
                <label for="title" class="block text-xs font-semibold text-slate-700 mb-1">Product Title *</label>
                <input id="title" name="title" type="text" required maxlength="200" value="<?= e($product['title']) ?>" placeholder="e.g. Wireless Pro ANC Earbuds" class="w-full px-3.5 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651] font-medium">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="category_id" class="block text-xs font-semibold text-slate-700 mb-1">Category</label>
                    <select id="category_id" name="category_id" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg bg-white focus:outline-[#00A651]">
                        <option value="0">— Uncategorized —</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int) $cat['id'] ?>" <?= (int) $product['category_id'] === (int) $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!$categories): ?>
                        <p class="text-[10px] text-amber-600 mt-1">No categories yet. <a href="<?= e(url('admin/index.php?tab=categories')) ?>" class="underline">Create one first</a>.</p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="sku" class="block text-xs font-semibold text-slate-700 mb-1">SKU (auto if blank)</label>
                    <input id="sku" name="sku" type="text" maxlength="80" value="<?= e($product['sku']) ?>" placeholder="e.g. PAK-AUD-01" class="w-full px-3.5 py-2 text-xs border border-slate-300 rounded-lg font-mono focus:outline-[#00A651]">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="price" class="block text-xs font-semibold text-slate-700 mb-1">Selling Price (Rs.) *</label>
                    <input id="price" name="price" type="number" min="0" step="0.01" required value="<?= e($product['price']) ?>" placeholder="3499" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg font-mono font-bold focus:outline-[#00A651]">
                </div>
                <div>
                    <label for="original_price" class="block text-xs font-semibold text-slate-700 mb-1">Compare-at Price (Rs.)</label>
                    <input id="original_price" name="original_price" type="number" min="0" step="0.01" value="<?= e($product['original_price']) ?>" placeholder="4999" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg font-mono text-slate-600 focus:outline-[#00A651]">
                </div>
                <div>
                    <label for="stock" class="block text-xs font-semibold text-slate-700 mb-1">Stock Quantity *</label>
                    <input id="stock" name="stock" type="number" min="0" step="1" required value="<?= e($product['stock']) ?>" placeholder="30" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg font-mono focus:outline-[#00A651]">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="badge" class="block text-xs font-semibold text-slate-700 mb-1">Badge (optional)</label>
                    <input id="badge" name="badge" type="text" maxlength="40" value="<?= e($product['badge']) ?>" placeholder="e.g. Bestseller, New Arrival" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651]">
                </div>
                <label class="flex items-center gap-2 mt-5 text-xs font-semibold text-slate-700 cursor-pointer">
                    <input type="checkbox" name="in_stock" value="1" <?= (int) $product['in_stock'] === 1 ? 'checked' : '' ?> class="w-4 h-4 rounded border-slate-300">
                    Available for purchase (in stock)
                </label>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-5 space-y-4">
            <h2 class="text-sm font-bold text-slate-900 pb-2 border-b border-slate-100">Images</h2>

            <div class="flex flex-col sm:flex-row gap-4">
                <div class="w-24 h-24 rounded-xl bg-slate-50 border border-slate-200 overflow-hidden shrink-0">
                    <img src="<?= e(image_url($product['image'])) ?>" alt="Current product image" class="w-full h-full object-cover">
                </div>
                <div class="flex-1 space-y-2">
                    <div>
                        <label for="image_file" class="block text-xs font-semibold text-slate-700 mb-1">Upload Image (JPG / PNG / WEBP, max 3 MB)</label>
                        <input id="image_file" name="image_file" type="file" accept="image/jpeg,image/png,image/webp,image/gif" class="w-full text-xs border border-slate-300 rounded-lg p-1.5 bg-white file:mr-2 file:px-2.5 file:py-1 file:rounded file:border-0 file:bg-slate-900 file:text-white file:text-[11px] file:font-bold">
                    </div>
                    <div>
                        <label for="image_url" class="block text-xs font-semibold text-slate-700 mb-1">Or Image URL / Path</label>
                        <input id="image_url" name="image_url" type="text" value="" placeholder="e.g. assets/images/product.jpg" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg font-mono focus:outline-[#00A651]">
                    </div>
                </div>
            </div>

            <?php if ($suggestions): ?>
                <div class="flex flex-wrap items-center gap-2 text-[11px]">
                    <span class="text-slate-500 font-medium">Quick pick:</span>
                    <?php foreach ($suggestions as $file): ?>
                        <?php $relative = 'assets/images/' . basename($file); ?>
                        <button type="button" onclick="document.getElementById('image_url').value='<?= e($relative) ?>'" class="px-2 py-1 rounded border border-slate-200 bg-slate-50 hover:bg-emerald-50 hover:border-emerald-200 hover:text-[#00A651] font-mono">
                            <?= e(basename($file)) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div>
                <label for="gallery" class="block text-xs font-semibold text-slate-700 mb-1">Gallery Image URLs (one per line, optional)</label>
                <textarea id="gallery" name="gallery" rows="3" placeholder="uploads/products/extra-1.jpg&#10;https://example.com/extra-2.jpg" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg font-mono focus:outline-[#00A651]"><?= e($galleryValue) ?></textarea>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-5 space-y-4">
            <h2 class="text-sm font-bold text-slate-900 pb-2 border-b border-slate-100">Description &amp; Features</h2>

            <div>
                <label for="description" class="block text-xs font-semibold text-slate-700 mb-1">Product Description</label>
                <textarea id="description" name="description" rows="4" placeholder="Explain product specifications, craftsmanship and benefits..." class="w-full px-3.5 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651]"><?= e($product['description']) ?></textarea>
            </div>

            <div>
                <label for="features" class="block text-xs font-semibold text-slate-700 mb-1">Key Features (one per line)</label>
                <textarea id="features" name="features" rows="4" placeholder="1 Year Official Replacement Warranty&#10;Bluetooth 5.3 with 40ms Low Latency" class="w-full px-3.5 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651]"><?= e($featuresValue) ?></textarea>
            </div>
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
