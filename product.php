<?php
/** Product detail page: gallery, pricing, stock, related products. */

require_once __DIR__ . '/includes/functions.php';

$productId = (int) get_string('id', '0');
if ($productId <= 0) {
    flash('error', 'Product not found.');
    redirect('index.php#catalog-section');
}

$stmt = db()->prepare(
    'SELECT p.*, c.name AS category_name, c.slug AS category_slug
     FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE p.id = ? LIMIT 1'
);
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    flash('error', 'That product no longer exists.');
    redirect('index.php#catalog-section');
}

$price    = (float) $product['price'];
$original = $product['original_price'] !== null && (float) $product['original_price'] > $price
    ? (float) $product['original_price'] : 0.0;
$discount = $original > 0 ? (int) round((($original - $price) / $original) * 100) : 0;
$inStock  = (int) $product['in_stock'] === 1 && (int) $product['stock'] > 0;
$features = json_array($product['features']);
$gallery  = json_array($product['gallery']);
if ($gallery === []) {
    $gallery = [$product['image']];
}
if (!in_array($product['image'], $gallery, true)) {
    array_unshift($gallery, $product['image']);
}

/* ---------------- Related products ---------------- */
$relStmt = db()->prepare(
    'SELECT p.*, c.name AS category_name, c.slug AS category_slug
     FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE p.id <> ? AND (p.category_id = ? OR ? IS NULL)
     ORDER BY (p.category_id = ?) DESC, p.created_at DESC
     LIMIT 4'
);
$relStmt->execute([$productId, $product['category_id'], $product['category_id'], $product['category_id']]);
$related = $relStmt->fetchAll();

if (count($related) < 4) {
    $fill = db()->prepare(
        'SELECT p.*, c.name AS category_name, c.slug AS category_slug
         FROM products p
         LEFT JOIN categories c ON c.id = p.category_id
         WHERE p.id <> ? ORDER BY p.created_at DESC LIMIT 8'
    );
    $fill->execute([$productId]);
    foreach ($fill->fetchAll() as $candidate) {
        if (count($related) >= 4) {
            break;
        }
        foreach ($related as $existing) {
            if ((int) $existing['id'] === (int) $candidate['id']) {
                continue 2;
            }
        }
        $related[] = $candidate;
    }
}

$shareText  = 'Check out "' . $product['title'] . '" on ' . setting('store_name', 'PakStore') . ' for ' . money($price) . ' with 100% Cash on Delivery!';
$whatsappShare = 'https://wa.me/?text=' . rawurlencode($shareText . ' ' . (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? ''));

$pageTitle = $product['title'] . ' - ' . setting('store_name', 'PakStore');
$metaDescription = mb_substr(trim((string) $product['description']), 0, 160);
if ($metaDescription === '') {
    $metaDescription = $product['title'] . ' - Cash on Delivery available all over Pakistan.';
}

$seoScheme = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http');
$seoOrigin = $seoScheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
$seoImage = preg_match('~^https?://~i', (string) $product['image'])
    ? $product['image']
    : $seoOrigin . image_url($product['image']);
$ogImage = $seoImage;
$ogType = 'product';
$structuredData = '<script type="application/ld+json">' . json_encode([
    '@context'    => 'https://schema.org',
    '@type'       => 'Product',
    'name'        => $product['title'],
    'description' => trim(strip_tags((string) ($product['description'] ?? ''))),
    'sku'         => (string) $product['sku'],
    'image'       => [$seoImage],
    'offers'      => [
        '@type'         => 'Offer',
        'price'         => number_format((float) $product['price'], 2, '.', ''),
        'priceCurrency' => 'PKR',
        'availability'  => $inStock ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
        'url'           => $seoOrigin . url('product.php?id=' . (int) $product['id']),
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) . '</script>';

require __DIR__ . '/includes/header.php';
?>

<main class="flex-1 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <nav class="text-xs text-slate-500 mb-4 flex flex-wrap items-center gap-2" aria-label="Breadcrumb">
            <a href="<?= e(url('index.php')) ?>" class="hover:text-[#00A651]">Home</a>
            <span>/</span>
            <?php if (!empty($product['category_slug'])): ?>
                <a href="<?= e(url('index.php?category=' . urlencode($product['category_slug']) . '#catalog-section')) ?>" class="hover:text-[#00A651]"><?= e($product['category_name']) ?></a>
                <span>/</span>
            <?php endif; ?>
            <span class="text-slate-800 font-semibold truncate max-w-[220px]"><?= e($product['title']) ?></span>
        </nav>

        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="grid grid-cols-1 lg:grid-cols-2">
                <!-- Gallery -->
                <div class="p-4 sm:p-6 bg-slate-50/60 border-b lg:border-b-0 lg:border-r border-slate-200">
                    <div class="relative rounded-xl overflow-hidden bg-white border border-slate-200 aspect-square">
                        <img data-gallery-main src="<?= e(image_url($gallery[0])) ?>" alt="<?= e($product['title']) ?>" class="w-full h-full object-cover">
                        <?php if (!empty($product['badge'])): ?>
                            <span class="absolute top-3 left-3 bg-slate-900 text-white text-[10px] font-bold px-2.5 py-1 rounded shadow-sm"><?= e($product['badge']) ?></span>
                        <?php endif; ?>
                        <?php if ($discount > 0): ?>
                            <span class="absolute top-3 right-3 bg-[#FF6B00] text-white text-xs font-black px-2.5 py-1 rounded shadow-sm">-<?= $discount ?>% OFF</span>
                        <?php endif; ?>
                    </div>

                    <?php if (count($gallery) > 1): ?>
                        <div class="mt-3 grid grid-cols-5 gap-2">
                            <?php foreach ($gallery as $index => $image): ?>
                                <button type="button"
                                        data-gallery-thumb="<?= e(image_url($image)) ?>"
                                        class="aspect-square rounded-lg overflow-hidden border-2 bg-white transition-all <?= $index === 0 ? 'border-[#00A651] ring-2 ring-emerald-500/30' : 'border-slate-200 opacity-75 hover:opacity-100' ?>">
                                    <img src="<?= e(image_url($image)) ?>" alt="<?= e($product['title']) ?> thumbnail <?= $index + 1 ?>" class="w-full h-full object-cover">
                                </button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="mt-4 grid grid-cols-3 gap-2 text-[10px] text-slate-600 text-center">
                        <div class="p-2 bg-white border border-slate-200 rounded-lg"><i data-lucide="truck" class="w-4 h-4 text-[#00A651] mx-auto mb-1"></i>COD Nationwide</div>
                        <div class="p-2 bg-white border border-slate-200 rounded-lg"><i data-lucide="shield-check" class="w-4 h-4 text-[#00A651] mx-auto mb-1"></i>Inspected</div>
                        <div class="p-2 bg-white border border-slate-200 rounded-lg"><i data-lucide="refresh-cw" class="w-4 h-4 text-[#00A651] mx-auto mb-1"></i>7-Day Return</div>
                    </div>
                </div>

                <!-- Info -->
                <div class="p-4 sm:p-6 flex flex-col">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400"><?= e($product['category_name'] ?: 'Uncategorized') ?></span>
                            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight leading-snug mt-1"><?= e($product['title']) ?></h1>
                        </div>
                        <form action="<?= e(url('wishlist.php')) ?>" method="post" class="shrink-0">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                            <input type="hidden" name="redirect" value="product.php?id=<?= (int) $product['id'] ?>">
                            <button type="submit" class="p-2 rounded-full border transition-colors <?= wishlist_has((int) $product['id']) ? 'bg-rose-500 border-rose-500 text-white' : 'bg-white border-slate-200 text-slate-400 hover:text-rose-500' ?>" title="<?= wishlist_has((int) $product['id']) ? 'Remove from Wishlist' : 'Add to Wishlist' ?>">
                                <i data-lucide="heart" class="w-5 h-5 <?= wishlist_has((int) $product['id']) ? 'fill-white' : '' ?>"></i>
                            </button>
                        </form>
                    </div>

                    <div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
                        <?php if ($inStock): ?>
                            <span class="text-[#00A651] font-bold flex items-center gap-1"><i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i> In Stock (<?= (int) $product['stock'] ?>)</span>
                        <?php else: ?>
                            <span class="text-rose-600 font-bold">Out of Stock</span>
                        <?php endif; ?>
                        <?php if (!empty($product['sku'])): ?>
                            <span class="text-slate-300">&middot;</span>
                            <span class="text-slate-400 font-mono">SKU: <?= e($product['sku']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="mt-4 p-4 bg-slate-50 border border-slate-200 rounded-xl">
                        <div class="flex flex-wrap items-baseline gap-2">
                            <span class="text-2xl sm:text-3xl font-black text-slate-900 font-mono"><?= e(money($price)) ?></span>
                            <?php if ($original > 0): ?>
                                <span class="text-sm text-slate-400 line-through font-mono"><?= e(money($original)) ?></span>
                                <span class="text-[11px] font-bold text-[#FF6B00] bg-orange-50 border border-orange-200 px-2 py-0.5 rounded">Save <?= e(money($original - $price)) ?></span>
                            <?php endif; ?>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1">Inclusive of all taxes &middot; <strong class="text-emerald-700">Cash on Delivery</strong> available all over Pakistan.</p>
                    </div>

                    <?php if ($product['description']): ?>
                        <?php
                        $descText = trim((string) $product['description']);
                        $descLong = mb_strlen($descText) > 150 || substr_count($descText, "\n") >= 3;
                        ?>
                        <div class="mt-4">
                            <p id="product-description" class="text-xs sm:text-sm text-slate-600 leading-relaxed<?= $descLong ? ' overflow-hidden' : '' ?>"<?= $descLong ? ' style="max-height:6.5rem"' : '' ?>><?= nl2br(e($descText)) ?></p>
                            <?php if ($descLong): ?>
                                <button type="button" data-desc-toggle="product-description" class="mt-1.5 text-[11px] font-bold text-[#00A651] hover:text-[#008f45] flex items-center gap-1">
                                    <span data-desc-label>Read more</span>
                                    <i data-lucide="arrow-down" class="w-3.5 h-3.5 transition-transform"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($features): ?>
                        <ul class="mt-4 space-y-1.5">
                            <?php foreach ($features as $feature): ?>
                                <li class="flex items-start gap-2 text-xs text-slate-700">
                                    <i data-lucide="check" class="w-4 h-4 text-[#00A651] shrink-0 mt-0.5"></i>
                                    <span><?= e($feature) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <!-- Purchasing -->
                    <div class="mt-5 pt-4 border-t border-slate-200 space-y-3">
                        <form action="<?= e(url('cart.php')) ?>" method="post" class="flex items-stretch gap-2">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                            <input type="hidden" name="redirect" value="cart.php">
                            <div class="flex items-center border border-slate-300 rounded-lg bg-slate-50" data-qty>
                                <button type="button" data-qty-step="-1" class="px-3 py-2.5 text-slate-600 hover:bg-slate-200 font-bold" aria-label="Decrease quantity">-</button>
                                <input type="number" name="quantity" value="1" min="1" max="<?= max(1, (int) $product['stock']) ?>" class="w-12 py-2.5 text-center text-sm font-mono font-bold bg-white border-x border-slate-200 focus:outline-none" aria-label="Quantity">
                                <button type="button" data-qty-step="1" class="px-3 py-2.5 text-slate-600 hover:bg-slate-200 font-bold" aria-label="Increase quantity">+</button>
                            </div>
                            <button type="submit" class="flex-1 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-lg text-xs flex items-center justify-center gap-2 transition-colors <?= $inStock ? '' : 'opacity-50 pointer-events-none' ?>" <?= $inStock ? '' : 'disabled' ?>>
                                <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                                <span>Add to Bag</span>
                            </button>
                        </form>

                        <a href="<?= e(url('checkout.php?product=' . (int) $product['id'] . '&qty=1')) ?>"
                           class="w-full py-3 bg-[#00A651] hover:bg-[#008f45] text-white font-bold rounded-xl text-sm transition-colors shadow-sm flex items-center justify-center gap-2 <?= $inStock ? '' : 'opacity-50 pointer-events-none' ?>">
                            <i data-lucide="zap" class="w-4 h-4 fill-current"></i>
                            <span>Order Now</span>
                        </a>

                        <div class="flex items-center gap-2">
                            <a href="<?= e($whatsappShare) ?>" target="_blank" rel="noopener noreferrer" class="flex-1 py-2 border border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg text-[11px] font-bold flex items-center justify-center gap-1.5 transition-colors">
                                <i data-lucide="message-circle" class="w-3.5 h-3.5"></i> Share on WhatsApp
                            </a>
                            <button type="button" data-copy-link="" class="flex-1 py-2 border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 rounded-lg text-[11px] font-bold flex items-center justify-center gap-1.5 transition-colors">
                                <i data-lucide="copy" class="w-3.5 h-3.5"></i> <span data-copy-label>Copy Link</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related -->
        <?php if ($related): ?>
            <section class="mt-8">
                <div class="flex items-end justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-black text-slate-900 tracking-tight">You may also like</h2>
                        <p class="text-xs text-slate-500">More products from the same collection.</p>
                    </div>
                    <a href="<?= e(url('index.php#catalog-section')) ?>" class="text-xs font-bold text-[#00A651] hover:underline">View all</a>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-5">
                    <?php foreach ($related as $item): ?>
                        <?php
                        $rPrice = (float) $item['price'];
                        $rOriginal = $item['original_price'] !== null && (float) $item['original_price'] > $rPrice ? (float) $item['original_price'] : 0.0;
                        $rInStock = (int) $item['in_stock'] === 1 && (int) $item['stock'] > 0;
                        ?>
                        <div class="group bg-white rounded-xl border border-slate-200 overflow-hidden flex flex-col hover:shadow-lg transition-shadow">
                            <a href="<?= e(url('product.php?id=' . (int) $item['id'])) ?>" class="block aspect-square bg-slate-50 overflow-hidden">
                                <img src="<?= e(image_url($item['image'])) ?>" alt="<?= e($item['title']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
                            </a>
                            <div class="p-3 flex-1 flex flex-col justify-between">
                                <div>
                                    <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400"><?= e($item['category_name'] ?: 'Uncategorized') ?></span>
                                    <a href="<?= e(url('product.php?id=' . (int) $item['id'])) ?>" class="block text-xs font-bold text-slate-900 clamp-2 min-h-[32px] hover:text-[#00A651] mt-0.5"><?= e($item['title']) ?></a>
                                </div>
                                <div class="mt-2 pt-2 border-t border-slate-100">
                                    <div class="flex items-baseline gap-1.5">
                                        <span class="text-xs font-black text-slate-900 font-mono"><?= e(money($rPrice)) ?></span>
                                        <?php if ($rOriginal > 0): ?><span class="text-[10px] text-slate-400 line-through font-mono"><?= e(money($rOriginal)) ?></span><?php endif; ?>
                                    </div>
                                    <span class="text-[10px] <?= $rInStock ? 'text-[#00A651]' : 'text-rose-500' ?> font-semibold"><?= $rInStock ? 'In Stock' : 'Out of Stock' ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
