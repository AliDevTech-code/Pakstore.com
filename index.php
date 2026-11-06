<?php
/**
 * Storefront home + shop catalog.
 * All products and categories come from MySQL - there is no mock data.
 */

require_once __DIR__ . '/includes/functions.php';

$q              = get_string('q');
$activeSlug     = get_string('category');
$sort           = get_string('sort', 'featured');
$allowedSorts   = ['featured', 'price-low', 'price-high', 'newest'];
if (!in_array($sort, $allowedSorts, true)) {
    $sort = 'featured';
}

$activeCategoryRow = $activeSlug !== '' ? find_category_by_slug($activeSlug) : null;
if ($activeSlug !== '' && !$activeCategoryRow) {
    $activeSlug = '';
}

$categories = all_categories();

/* ---------------- Banner slides ---------------- */
$banners = [];
try {
    $banners = db()->query(
        'SELECT * FROM banners WHERE is_active = 1 ORDER BY sort_order ASC, id DESC'
    )->fetchAll();
} catch (Throwable $e) {
    error_log('Banner query failed: ' . $e->getMessage());
}

/* ---------------- Product query ---------------- */
$where = ['1 = 1'];
$params = [];

if ($activeSlug !== '') {
    $where[] = 'c.slug = ?';
    $params[] = $activeSlug;
}

if ($q !== '') {
    $like = '%' . $q . '%';
    $where[] = '(p.title LIKE ? OR p.description LIKE ? OR p.sku LIKE ? OR c.name LIKE ?)';
    array_push($params, $like, $like, $like, $like);
}

$orderBy = match ($sort) {
    'price-low'  => 'p.price ASC, p.id DESC',
    'price-high' => 'p.price DESC, p.id DESC',
    'newest'     => 'p.created_at DESC, p.id DESC',
    default      => "(p.badge = 'Bestseller') DESC, p.created_at DESC, p.id DESC",
};

$sql = "SELECT p.*, c.name AS category_name, c.slug AS category_slug
        FROM products p
        LEFT JOIN categories c ON c.id = p.category_id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY $orderBy
        LIMIT 120";

$stmt = db()->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$pageTitle = $activeCategoryRow
    ? ($activeCategoryRow['name'] . ' - ' . setting('store_name', 'PakStore'))
    : (setting('store_name', 'PakStore') . ' - Cash on Delivery Store Pakistan');
if ($q !== '') {
    $pageTitle = 'Search: ' . $q . ' - ' . setting('store_name', 'PakStore');
}

$currentListUrl = 'index.php?' . http_build_query(array_filter([
    'q'        => $q,
    'category' => $activeSlug,
    'sort'     => $sort,
]));

require __DIR__ . '/includes/header.php';
?>

<main class="flex-1">
    <!-- ================= HERO ================= -->
    <section data-hero-slider class="relative overflow-hidden bg-slate-950 text-white border-b border-slate-800 min-h-[460px] sm:min-h-[520px] flex flex-col justify-between">
        <div class="absolute inset-0 z-0 overflow-hidden">
            <?php if ($banners): ?>
                <?php foreach ($banners as $i => $banner): ?>
                    <div class="hero-slide <?= $i === 0 ? 'is-active' : '' ?>">
                        <img src="<?= e(image_url($banner['image'])) ?>" alt="<?= e($banner['title']) ?>" loading="<?= $i === 0 ? 'eager' : 'lazy' ?>">
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="hero-slide is-active bg-gradient-to-br from-slate-950 via-emerald-950 to-slate-900"></div>
            <?php endif; ?>
            <div class="absolute inset-0 bg-gradient-to-r from-slate-950/95 via-slate-950/85 to-slate-950/70"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,transparent_0%,rgba(2,6,23,0.65)_65%,rgba(2,6,23,0.95)_100%)]"></div>
        </div>

        <?php if (count($banners) > 1): ?>
            <div class="absolute right-4 top-1/2 -translate-y-1/2 z-20 hidden md:flex flex-col gap-2">
                <button type="button" data-hero-prev class="p-2.5 rounded-full bg-slate-900/80 hover:bg-[#00A651] text-white border border-slate-700/80 transition-colors backdrop-blur-md" aria-label="Previous slide">
                    <i data-lucide="chevron-left" class="w-4 h-4"></i>
                </button>
                <button type="button" data-hero-next class="p-2.5 rounded-full bg-slate-900/80 hover:bg-[#00A651] text-white border border-slate-700/80 transition-colors backdrop-blur-md" aria-label="Next slide">
                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </button>
            </div>
        <?php endif; ?>

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-12 sm:pt-12 sm:pb-16 flex-1 flex flex-col justify-center items-center w-full">
            <div class="max-w-3xl mx-auto text-center flex flex-col items-center space-y-4 sm:space-y-6">
                <div class="flex flex-wrap items-center justify-center gap-2">
                    <div class="inline-flex items-center gap-2 text-xs font-bold text-emerald-300 bg-emerald-950/90 border border-emerald-500/40 px-3.5 py-1.5 rounded-full backdrop-blur-md">
                        <span class="w-2 h-2 rounded-full bg-[#00A651] animate-pulse"></span>
                        <span>&#127477;&#127472; 100% Cash on Delivery All Over Pakistan</span>
                    </div>
                </div>

                <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-white tracking-tight leading-[1.1]">
                    Premium Lifestyle &amp; Audio. <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#00A651] via-emerald-300 to-[#FF6B00]">Pay Cash at Doorstep.</span>
                </h1>

                <p class="text-xs sm:text-sm md:text-base text-slate-200 leading-relaxed max-w-2xl mx-auto">
                    Authentic smartwatches, active noise-cancelling acoustics, genuine leather accessories, and royal fragrances. Check the parcel and pay cash to the rider upon arrival.
                </p>

                <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
                    <a href="#catalog-section" class="px-6 py-3 bg-[#00A651] hover:bg-[#008f45] text-white font-bold rounded-xl text-xs sm:text-sm transition-all shadow-lg flex items-center gap-2">
                        <span>Explore Catalog</span>
                        <i data-lucide="arrow-down" class="w-4 h-4"></i>
                    </a>
                    <?php if ($products): ?>
                        <a href="<?= e(url('checkout.php?product=' . (int) $products[0]['id'] . '&qty=1')) ?>" class="px-5 py-3 bg-[#FF6B00] hover:bg-[#e05e00] text-white font-bold rounded-xl text-xs sm:text-sm transition-all shadow-md flex items-center gap-2">
                            <i data-lucide="zap" class="w-4 h-4 fill-current"></i>
                            <span>Quick COD Order</span>
                        </a>
                    <?php endif; ?>
                </div>

                <?php if (count($banners) > 1): ?>
                    <div class="flex items-center justify-center gap-1.5 pt-2">
                        <?php foreach ($banners as $idx => $banner): ?>
                            <button type="button" data-hero-dot="<?= $idx ?>" class="hero-dot rounded-full h-1.5 <?= $idx === 0 ? 'w-7 bg-[#FF6B00]' : 'w-2 bg-white/40 hover:bg-white/70' ?>" aria-label="Go to slide <?= $idx + 1 ?>"></button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Trust bar -->
        <div class="relative z-10 bg-slate-900/90 backdrop-blur-md border-t border-slate-800 py-3.5 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto grid grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 rounded-lg bg-emerald-500/10 text-[#00A651] shrink-0"><i data-lucide="truck" class="w-4 h-4"></i></div>
                    <div><h4 class="font-bold text-white text-xs">Nationwide COD</h4><p class="text-[10px] text-slate-400">Pay cash upon delivery</p></div>
                </div>
                <div class="flex items-center gap-2.5">
                    <div class="p-2 rounded-lg bg-amber-500/10 text-[#FF6B00] shrink-0"><i data-lucide="shield-check" class="w-4 h-4"></i></div>
                    <div><h4 class="font-bold text-white text-xs">Inspected &amp; Sealed</h4><p class="text-[10px] text-slate-400">100% genuine tested</p></div>
                </div>
                <div class="flex items-center gap-2.5">
                    <div class="p-2 rounded-lg bg-emerald-500/10 text-[#00A651] shrink-0"><i data-lucide="refresh-cw" class="w-4 h-4"></i></div>
                    <div><h4 class="font-bold text-white text-xs">7-Day Replacement</h4><p class="text-[10px] text-slate-400">Hassle-free warranty</p></div>
                </div>
                <div class="flex items-center gap-2.5">
                    <div class="p-2 rounded-lg bg-amber-500/10 text-[#FF6B00] shrink-0"><i data-lucide="headphones" class="w-4 h-4"></i></div>
                    <div><h4 class="font-bold text-white text-xs">24/7 Helpline</h4><p class="text-[10px] text-slate-400">Customer order support</p></div>
                </div>
            </div>
        </div>
    </section>

    <!-- ================= CATALOG ================= -->
    <section id="catalog-section" class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 overflow-hidden">
        <div class="space-y-4 pb-5 border-b border-slate-200 w-full min-w-0 max-w-full">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                        <?= e($activeCategoryRow ? $activeCategoryRow['name'] : 'Curated Collection') ?>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        <?= count($products) ?> item<?= count($products) === 1 ? '' : 's' ?> available &middot; Guaranteed Cash on Delivery across Pakistan
                    </p>
                </div>

                <form method="get" action="<?= e(url('index.php')) ?>" class="relative shrink-0 flex items-center border border-slate-200 rounded-lg px-2.5 py-1.5 bg-white text-xs shadow-sm self-start sm:self-auto">
                    <?php if ($q !== ''): ?><input type="hidden" name="q" value="<?= e($q) ?>"><?php endif; ?>
                    <?php if ($activeSlug !== ''): ?><input type="hidden" name="category" value="<?= e($activeSlug) ?>"><?php endif; ?>
                    <i data-lucide="arrow-up-down" class="w-3.5 h-3.5 text-slate-400 mr-1.5"></i>
                    <label for="sort" class="sr-only">Sort products</label>
                    <select id="sort" name="sort" onchange="this.form.submit()" class="bg-transparent text-slate-700 font-medium focus:outline-none cursor-pointer">
                        <option value="featured" <?= $sort === 'featured' ? 'selected' : '' ?>>Featured First</option>
                        <option value="price-low" <?= $sort === 'price-low' ? 'selected' : '' ?>>Price: Low to High</option>
                        <option value="price-high" <?= $sort === 'price-high' ? 'selected' : '' ?>>Price: High to Low</option>
                        <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest Arrivals</option>
                    </select>
                    <noscript><button type="submit" class="ml-2 text-[#00A651] font-bold">Apply</button></noscript>
                </form>
            </div>

            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none w-full max-w-full min-w-0">
                <a href="<?= e(url('index.php' . ($q !== '' ? '?q=' . urlencode($q) : '') . '#catalog-section')) ?>" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg transition-colors whitespace-nowrap shrink-0 <?= $activeSlug === '' ? 'bg-[#00A651] text-white shadow-sm' : 'bg-white text-slate-600 hover:text-slate-900 border border-slate-200 hover:border-slate-300' ?>">All Products</a>
                <?php foreach ($categories as $cat): ?>
                    <a href="<?= e(url('index.php?' . http_build_query(array_filter(['q' => $q, 'category' => $cat['slug']])) . '#catalog-section')) ?>"
                       class="px-3.5 py-1.5 text-xs font-semibold rounded-lg transition-colors whitespace-nowrap shrink-0 <?= $activeSlug === $cat['slug'] ? 'bg-[#00A651] text-white shadow-sm' : 'bg-white text-slate-600 hover:text-slate-900 border border-slate-200 hover:border-slate-300' ?>">
                        <?= e($cat['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if ($q !== ''): ?>
            <div class="mt-4 p-3 bg-slate-100 rounded-lg flex items-center justify-between text-xs text-slate-700">
                <span>Search results for <strong class="text-slate-900">&ldquo;<?= e($q) ?>&rdquo;</strong> (<?= count($products) ?> items)</span>
                <a href="<?= e(url('index.php#catalog-section')) ?>" class="text-emerald-700 font-semibold hover:underline">Clear Search</a>
            </div>
        <?php endif; ?>

        <?php if ($products): ?>
            <div class="mt-6 sm:mt-8 grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-5 lg:gap-6">
                <?php foreach ($products as $p): ?>
                    <?php
                    $price = (float) $p['price'];
                    $original = $p['original_price'] !== null && (float) $p['original_price'] > $price ? (float) $p['original_price'] : 0.0;
                    $discount = $original > 0 ? (int) round((($original - $price) / $original) * 100) : 0;
                    $inStock = (int) $p['in_stock'] === 1 && (int) $p['stock'] > 0;
                    $productUrl = url('product.php?id=' . (int) $p['id']);
                    ?>
                    <div class="group bg-white rounded-xl sm:rounded-2xl border border-slate-200/90 overflow-hidden flex flex-col justify-between hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 shadow-sm relative">
                        <div class="relative aspect-square bg-slate-50 overflow-hidden">
                            <a href="<?= e($productUrl) ?>" class="block w-full h-full">
                                <img src="<?= e(image_url($p['image'])) ?>" alt="<?= e($p['title']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
                            </a>

                            <?php if (!empty($p['badge'])): ?>
                                <span class="absolute top-2 left-2 bg-slate-900 text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded shadow-sm"><?= e($p['badge']) ?></span>
                            <?php endif; ?>

                            <?php if ($discount > 0): ?>
                                <span class="absolute top-2 right-10 sm:right-11 bg-[#FF6B00] text-white text-[9px] sm:text-[10px] font-black px-1.5 py-0.5 rounded shadow-sm">-<?= $discount ?>%</span>
                            <?php endif; ?>

                            <form action="<?= e(url('wishlist.php')) ?>" method="post" class="absolute top-2 right-2">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
                                <input type="hidden" name="redirect" value="<?= e($currentListUrl . '#catalog-section') ?>">
                                <button type="submit" class="p-1.5 rounded-full backdrop-blur-md transition-all shadow-sm <?= wishlist_has((int) $p['id']) ? 'bg-rose-500 text-white' : 'bg-white/90 text-slate-500 hover:text-rose-500 hover:bg-white' ?>" title="<?= wishlist_has((int) $p['id']) ? 'Remove from Wishlist' : 'Add to Wishlist' ?>">
                                    <i data-lucide="heart" class="w-3.5 h-3.5 <?= wishlist_has((int) $p['id']) ? 'fill-white' : '' ?>"></i>
                                </button>
                            </form>

                            <div class="absolute inset-0 bg-slate-950/20 opacity-0 group-hover:opacity-100 transition-opacity hidden sm:flex items-center justify-center pointer-events-none">
                                <span class="bg-white/95 text-slate-900 text-xs font-semibold px-3 py-1.5 rounded-lg shadow-sm flex items-center gap-1.5">
                                    <i data-lucide="eye" class="w-3.5 h-3.5 text-[#00A651]"></i> View Details &amp; Reviews
                                </span>
                            </div>
                        </div>

                        <div class="p-2.5 sm:p-3.5 flex-1 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between gap-2 text-[10px] sm:text-xs text-slate-500 mb-1">
                                    <span class="font-bold uppercase tracking-wider text-slate-400 truncate max-w-[90px]"><?= e($p['category_name'] ?: 'Uncategorized') ?></span>
                                    <span class="<?= $inStock ? 'text-[#00A651] font-semibold text-[10px]' : 'text-rose-500 font-semibold' ?>"><?= $inStock ? 'In Stock' : 'Out' ?></span>
                                </div>

                                <a href="<?= e($productUrl) ?>" class="text-xs sm:text-sm font-bold text-slate-900 clamp-2 min-h-[32px] sm:min-h-[38px] hover:text-[#00A651] transition-colors leading-snug"><?= e($p['title']) ?></a>

                                <div class="mt-1 flex items-center gap-1">
                                    <?php if ((int) $p['reviews_count'] > 0): ?>
                                        <i data-lucide="star" class="w-3 h-3 fill-amber-400 text-amber-400"></i>
                                        <span class="text-[10px] font-bold text-slate-700 font-mono"><?= e(number_format((float) $p['rating'], 1)) ?></span>
                                        <span class="text-[10px] text-slate-400">(<?= (int) $p['reviews_count'] ?>)</span>
                                    <?php else: ?>
                                        <span class="text-[10px] text-slate-400">No ratings yet</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="mt-2.5 pt-2 border-t border-slate-100">
                                <div class="flex flex-wrap items-baseline gap-1.5 mb-2">
                                    <span class="text-xs sm:text-sm font-black text-slate-900 font-mono tabular-nums"><?= e(money($price)) ?></span>
                                    <?php if ($original > 0): ?>
                                        <span class="text-[10px] text-slate-400 line-through font-mono tabular-nums"><?= e(money($original)) ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="flex items-center gap-1.5">
                                    <a href="<?= e(url('checkout.php?product=' . (int) $p['id'] . '&qty=1')) ?>"
                                       class="flex-1 py-1.5 sm:py-2 px-1.5 text-[11px] font-bold rounded-lg bg-[#00A651] hover:bg-[#008f45] text-white transition-colors flex items-center justify-center gap-1 whitespace-nowrap shadow-sm <?= $inStock ? '' : 'opacity-50 pointer-events-none' ?>"
                                       title="Instant Cash on Delivery Checkout">
                                        <i data-lucide="zap" class="w-3 h-3 fill-current shrink-0"></i>
                                        <span>Order COD</span>
                                    </a>

                                    <form action="<?= e(url('cart.php')) ?>" method="post">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="add">
                                        <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <input type="hidden" name="redirect" value="<?= e($currentListUrl . '#catalog-section') ?>">
                                        <button type="submit" class="p-1.5 sm:p-2 rounded-lg border border-slate-200 text-slate-700 hover:text-slate-900 hover:bg-slate-50 transition-colors <?= $inStock ? '' : 'opacity-50 pointer-events-none' ?>" title="Add to shopping cart" <?= $inStock ? '' : 'disabled' ?>>
                                            <i data-lucide="shopping-bag" class="w-3.5 h-3.5 text-slate-700"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="py-16 text-center bg-white rounded-2xl border border-slate-200 mt-8">
                <i data-lucide="search-x" class="w-10 h-10 text-slate-400 mx-auto mb-3"></i>
                <h3 class="text-base font-bold text-slate-800">No products found</h3>
                <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1">
                    <?= $q !== '' ? 'Try a different search term or browse another category.' : 'The catalog is empty right now. New products will appear here as soon as the admin adds them.' ?>
                </p>
                <?php if ($q !== '' || $activeSlug !== ''): ?>
                    <a href="<?= e(url('index.php#catalog-section')) ?>" class="inline-block mt-4 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-semibold">Reset Filters</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
