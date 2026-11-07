<?php
/**
 * Storefront header: announcement bar, navbar, category strip,
 * mobile bottom nav and the server-rendered cart drawer.
 *
 * Expects: includes/functions.php already loaded.
 */

require_once __DIR__ . '/functions.php';

$settings      = settings_all();
$storeName     = setting('store_name', 'PakStore');
$logoUrl       = setting('logo_url', '');
$logoVersion   = '';
if ($logoUrl !== '' && !preg_match('~^https?://~i', $logoUrl)) {
    $logoFile = dirname(__DIR__) . '/' . ltrim($logoUrl, '/');
    if (is_file($logoFile)) {
        $logoVersion = '?v=' . filemtime($logoFile);
    }
}
$topBarText    = setting('top_bar_text', 'Cash on Delivery All Over Pakistan');
$phone         = setting('phone', '');
$whatsapp      = setting('whatsapp', '');
$supportEmail  = setting('email', '');
$announcement  = setting('announcement', $topBarText);

$navCategories = all_categories();
$navCartCount  = cart_count();
$navCustomer   = current_customer();

$searchQuery    = get_string('q');
$activeCategory = get_string('category');
$currentPage    = basename($_SERVER['SCRIPT_NAME'] ?? '');

$pageTitle = $pageTitle ?? ($storeName . ' - Cash on Delivery Store Pakistan');
$metaDescription = $metaDescription ?? 'Shop online in Pakistan with 100% Cash on Delivery. Inspected and sealed parcels delivered nationwide.';

$cartItems = cart_detailed();
$cartTotals = cart_totals();

$siteOrigin = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
$canonicalUrl = $siteOrigin . ($_SERVER['REQUEST_URI'] ?? '/');
$ogType = $ogType ?? 'website';
$shareImage = $ogImage ?? setting('logo_url', '');
if ($shareImage !== '' && !preg_match('~^https?://~i', $shareImage)) {
    $shareImage = $siteOrigin . image_url($shareImage);
}
if (empty($structuredData)) {
    $structuredData = '<script type="application/ld+json">' . json_encode([
        '@context' => 'https://schema.org',
        '@type'    => 'WebSite',
        'name'     => $storeName,
        'url'      => $siteOrigin . url(''),
        'potentialAction' => [
            '@type'       => 'SearchAction',
            'target'      => $siteOrigin . url('index.php') . '?q={search_term_string}',
            'query-input' => 'required name=search_term_string',
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) . '</script>';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($metaDescription) ?>">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($metaDescription) ?>">
    <meta property="og:type" content="<?= e($ogType) ?>">
    <meta property="og:url" content="<?= e($canonicalUrl) ?>">
    <meta property="og:site_name" content="<?= e($storeName) ?>">
    <?php if ($shareImage !== ''): ?>
        <meta property="og:image" content="<?= e($shareImage) ?>">
        <meta name="twitter:image" content="<?= e($shareImage) ?>">
    <?php endif; ?>
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($pageTitle) ?>">
    <meta name="twitter:description" content="<?= e($metaDescription) ?>">
    <link rel="canonical" href="<?= e($canonicalUrl) ?>">
    <meta name="theme-color" content="#00A651">
    <?= $structuredData ?>

    <link rel="preload" href="<?= e(url('assets/fonts/plus-jakarta-sans-latin.woff2')) ?>" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?= e(url('assets/fonts/outfit-latin.woff2')) ?>" as="font" type="font/woff2" crossorigin>

    <link rel="stylesheet" href="<?= e(url('assets/css/tailwind.css')) ?>">
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css') . '?v=3') ?>">
    <script src="<?= e(url('assets/js/lucide.min.js')) ?>"></script>
    <style>
        /* Keep drawers closed by default even if the external stylesheet fails to load. */
        [data-drawer][hidden] { display: none !important; }
    </style>
</head>
<body class="min-h-screen flex flex-col bg-slate-50 text-slate-900 selection:bg-[#00A651] selection:text-white">

<!-- Announcement bar -->
<div class="bg-[#003d1e] text-emerald-100 text-xs py-1.5 px-4 border-b border-emerald-900/40 print-hide">
    <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-2">
        <div class="flex items-center gap-2 font-semibold">
            <i data-lucide="truck" class="w-3.5 h-3.5 text-[#FF6B00] shrink-0"></i>
            <span class="tracking-wide"><?= e($topBarText) ?></span>
        </div>
        <div class="hidden md:flex items-center gap-4 text-emerald-200 text-[11px]">
            <span class="flex items-center gap-1.5 font-medium">
                <i data-lucide="shield-check" class="w-3.5 h-3.5 text-[#FF6B00]"></i> 100% Cash on Delivery
            </span>
            <span aria-hidden="true" class="text-emerald-700">&middot;</span>
            <a href="tel:<?= e($phone) ?>" class="flex items-center gap-1.5 hover:text-white transition-colors">
                <i data-lucide="phone-call" class="w-3 h-3 text-[#FF6B00]"></i> Helpline: <?= e($phone) ?>
            </a>
        </div>
    </div>
</div>

<!-- Navbar -->
<header class="sticky top-0 z-30 bg-white border-b border-slate-200 text-slate-800 shadow-sm print-hide">
    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-2 sm:gap-4 h-[64px] sm:h-[72px]">
            <div class="flex items-center gap-1.5 sm:gap-3 shrink-0">
                <a href="<?= e(url('index.php')) ?>" class="flex items-center gap-1.5 text-left group cursor-pointer shrink-0" aria-label="<?= e($storeName) ?> home">
                    <span id="store-text-logo" class="<?= $logoUrl !== '' ? 'hidden ' : 'flex ' ?>items-end">
                        <span class="text-xl sm:text-3xl font-black tracking-tight text-[#00A651]">Pak</span>
                        <span class="text-xl sm:text-3xl font-black tracking-tight text-[#FF6B00]">Store</span>
                        <span class="ml-1 text-[10px] font-bold uppercase tracking-widest text-slate-400 self-end mb-1 hidden sm:inline">.pk</span>
                    </span>
                    <?php if ($logoUrl !== ''): ?>
                        <img src="<?= e(image_url($logoUrl) . $logoVersion) ?>" alt="<?= e($storeName) ?>" class="h-9 sm:h-10 w-auto max-w-[110px] sm:max-w-[170px] object-contain rounded" onerror="this.style.display='none';var f=document.getElementById('store-text-logo');if(f){f.classList.remove('hidden');f.classList.add('flex');}">
                    <?php endif; ?>
                </a>

                <button type="button" data-open-drawer="category-drawer" class="p-2 rounded-lg text-slate-700 hover:text-[#00A651] hover:bg-emerald-50 border border-slate-200 transition-colors cursor-pointer flex items-center gap-1.5" title="Categories Menu" aria-label="Open Categories Menu">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                    <span class="hidden sm:inline text-xs font-bold">Categories</span>
                </button>
            </div>

            <div class="flex-1 min-w-0 max-w-2xl">
                <form action="<?= e(url('index.php')) ?>" method="get" class="relative flex items-center" role="search">
                    <input type="search" name="q" value="<?= e($searchQuery) ?>" placeholder="Search products..." class="w-full pl-3 sm:pl-4 pr-10 sm:pr-12 py-2 sm:py-2.5 text-xs bg-slate-100/90 border border-slate-300 rounded-lg focus:outline-none focus:border-[#00A651] focus:bg-white text-slate-900 placeholder-slate-400 transition-all shadow-inner" aria-label="Search products">
                    <button type="submit" class="absolute right-1 top-1 bottom-1 px-2.5 sm:px-3.5 bg-[#FF6B00] hover:bg-[#e05e00] text-white rounded-md flex items-center justify-center transition-colors cursor-pointer" title="Search Products">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>

            <div class="flex items-center gap-1 sm:gap-2 shrink-0">
                <details class="relative group">
                    <summary class="list-none flex items-center gap-1.5 px-2.5 sm:px-3 py-2 rounded-lg bg-[#00A651] hover:bg-[#008f45] border border-[#00A651] text-xs font-bold text-white transition-colors cursor-pointer shadow-sm">
                        <i data-lucide="user" class="w-4 h-4"></i>
                        <span class="hidden sm:inline max-w-[90px] truncate"><?= $navCustomer ? e(explode(' ', $navCustomer['full_name'])[0]) : 'Login' ?></span>
                        <?php if ($navCartCount > 0): ?>
                            <span class="bg-white text-[#00A651] text-[10px] font-black w-4 h-4 rounded-full flex items-center justify-center leading-none"><?= $navCartCount ?></span>
                        <?php endif; ?>
                        <i data-lucide="chevron-down" class="w-3 h-3 text-white/80"></i>
                    </summary>
                    <div class="absolute right-0 mt-2 w-56 bg-white border border-slate-200 rounded-xl shadow-xl py-2 z-50 text-xs">
                        <?php if ($navCustomer): ?>
                            <div class="px-3.5 py-2 border-b border-slate-100">
                                <span class="font-bold text-slate-900 block truncate"><?= e($navCustomer['full_name']) ?></span>
                                <span class="text-[10px] text-slate-500 font-mono"><?= e($navCustomer['phone']) ?></span>
                            </div>
                            <button type="button" data-open-drawer="cart-drawer" onclick="this.closest('details').open=false" class="w-full px-3.5 py-2 text-left hover:bg-emerald-50 flex items-center gap-2 text-slate-700 font-medium cursor-pointer">
                                <i data-lucide="shopping-bag" class="w-4 h-4 text-[#00A651]"></i>
                                <span class="flex-1">Shopping Cart</span>
                                <span class="bg-[#FF6B00] text-white text-[10px] font-black px-1.5 py-0.5 rounded-full leading-none"><?= (int) $navCartCount ?></span>
                            </button>
                            <a href="<?= e(url('account.php')) ?>" class="w-full px-3.5 py-2 text-left hover:bg-emerald-50 flex items-center gap-2 text-slate-700 font-medium">
                                <i data-lucide="package" class="w-4 h-4 text-[#00A651]"></i>
                                <span>My Orders &amp; Delivery</span>
                            </a>
                            <a href="<?= e(url('wishlist.php')) ?>" class="w-full px-3.5 py-2 text-left hover:bg-emerald-50 flex items-center gap-2 text-slate-700 font-medium">
                                <i data-lucide="heart" class="w-4 h-4 text-[#00A651]"></i>
                                <span>My Wishlist</span>
                            </a>
                            <form action="<?= e(url('account.php')) ?>" method="post">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="logout">
                                <button type="submit" class="w-full px-3.5 py-2 text-left hover:bg-rose-50 flex items-center gap-2 text-rose-600 font-medium cursor-pointer">
                                    <i data-lucide="log-out" class="w-4 h-4"></i>
                                    <span>Sign Out</span>
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="px-3.5 py-2 border-b border-slate-100">
                                <span class="font-bold text-slate-900 block">Welcome to <?= e($storeName) ?></span>
                                <span class="text-[10px] text-slate-500">Login or create an account</span>
                            </div>
                            <button type="button" data-open-drawer="cart-drawer" onclick="this.closest('details').open=false" class="w-full px-3.5 py-2 text-left hover:bg-emerald-50 flex items-center gap-2 text-slate-700 font-medium cursor-pointer">
                                <i data-lucide="shopping-bag" class="w-4 h-4 text-[#00A651]"></i>
                                <span class="flex-1">Shopping Cart</span>
                                <span class="bg-[#FF6B00] text-white text-[10px] font-black px-1.5 py-0.5 rounded-full leading-none"><?= (int) $navCartCount ?></span>
                            </button>
                            <a href="<?= e(url('account.php')) ?>" class="w-full px-3.5 py-2 text-left hover:bg-emerald-50 flex items-center gap-2 text-slate-700 font-medium">
                                <i data-lucide="log-in" class="w-4 h-4 text-[#00A651]"></i>
                                <span>Login</span>
                            </a>
                            <a href="<?= e(url('account.php?mode=register')) ?>" class="w-full px-3.5 py-2 text-left hover:bg-emerald-50 flex items-center gap-2 text-slate-700 font-medium">
                                <i data-lucide="user-plus" class="w-4 h-4 text-[#00A651]"></i>
                                <span>Create Account</span>
                            </a>
                            <a href="<?= e(url('track-order.php')) ?>" class="w-full px-3.5 py-2 text-left hover:bg-emerald-50 flex items-center gap-2 text-slate-700 font-medium">
                                <i data-lucide="package-check" class="w-4 h-4 text-[#00A651]"></i>
                                <span>Track Order</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </details>
            </div>
        </div>
    </div>

    <!-- Category quick strip -->
    <div class="bg-slate-50 border-t border-slate-200 overflow-x-auto scrollbar-none">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center gap-4 py-2 text-xs font-semibold text-slate-700">
            <button type="button" data-open-drawer="category-drawer" class="flex items-center gap-1.5 text-[#00A651] hover:underline cursor-pointer font-bold shrink-0">
                <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i>
                <span>All Categories &#9776;</span>
            </button>
            <span class="text-slate-300">|</span>
            <?php foreach ($navCategories as $cat): ?>
                <a href="<?= e(url('index.php?category=' . urlencode($cat['slug']) . '#catalog-section')) ?>"
                   class="whitespace-nowrap transition-colors hover:text-[#00A651] <?= $activeCategory === $cat['slug'] ? 'text-[#00A651] font-bold border-b-2 border-[#00A651]' : '' ?>">
                    <?= e($cat['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</header>

<?php render_flashes(); ?>

<!-- ============ Category drawer ============ -->
<div id="category-drawer" data-drawer hidden class="fixed inset-0 z-50 overflow-hidden bg-slate-950/60 backdrop-blur-sm flex print-hide">
    <div class="w-full max-w-xs sm:max-w-sm bg-white text-slate-900 h-full p-5 shadow-2xl flex flex-col justify-between animate-slide-left overflow-y-auto">
        <div>
            <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                <div class="flex items-center gap-2">
                    <span class="bg-[#00A651] text-white font-black text-xs px-2 py-0.5 rounded">PAK</span>
                    <h3 class="font-bold text-sm text-slate-900">All Store Categories</h3>
                </div>
                <button type="button" data-close-drawer="category-drawer" class="p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="mt-4 space-y-1">
                <a href="<?= e(url('index.php#catalog-section')) ?>" class="w-full text-left px-3.5 py-2.5 rounded-xl font-semibold text-xs flex items-center justify-between transition-colors <?= $activeCategory === '' ? 'bg-emerald-50 text-[#00A651] font-bold' : 'hover:bg-slate-100 text-slate-800' ?>">
                    <span>All Categories</span>
                    <span class="text-[#00A651] text-xs">&rarr;</span>
                </a>
                <?php foreach ($navCategories as $cat): ?>
                    <a href="<?= e(url('index.php?category=' . urlencode($cat['slug']) . '#catalog-section')) ?>" class="w-full text-left px-3.5 py-2.5 rounded-xl font-semibold text-xs flex items-center justify-between transition-colors <?= $activeCategory === $cat['slug'] ? 'bg-emerald-50 text-[#00A651] font-bold' : 'hover:bg-slate-100 text-slate-800' ?>">
                        <span><?= e($cat['name']) ?></span>
                        <span class="text-[#FF6B00] text-xs">&rarr;</span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="pt-4 mt-4 border-t border-slate-200 bg-slate-50 p-3 rounded-xl text-center text-xs text-slate-600">
            <span class="font-bold text-[#00A651] block">Cash on Delivery All Over Pakistan</span>
            <span class="text-[11px] text-slate-500">Pay cash upon parcel inspection at doorstep.</span>
        </div>
    </div>
    <div class="flex-1 cursor-pointer" data-close-drawer="category-drawer"></div>
</div>

<!-- ============ Cart drawer ============ -->
<div id="cart-drawer" data-drawer hidden class="fixed inset-0 z-50 overflow-hidden bg-slate-950/50 backdrop-blur-sm flex justify-end print-hide">
    <div class="w-full max-w-md bg-white h-full shadow-2xl flex flex-col justify-between border-l border-slate-200 animate-slide-right">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="shopping-bag" class="w-5 h-5 text-[#00A651]"></i>
                <h2 class="font-bold text-slate-900 text-base">Shopping Bag (<?= count($cartItems) ?>)</h2>
            </div>
            <button type="button" data-close-drawer="cart-drawer" class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors" aria-label="Close cart">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <div class="bg-emerald-50 px-6 py-2.5 border-b border-emerald-100 text-xs flex items-center justify-between text-slate-700 font-medium">
            <div class="flex items-center gap-1.5">
                <i data-lucide="truck" class="w-4 h-4 text-[#00A651]"></i>
                <span>Delivery: <strong class="text-slate-900"><?= $cartTotals['shipping'] <= 0 ? 'FREE' : e(money($cartTotals['shipping'])) ?></strong> nationwide</span>
            </div>
            <span class="text-[10px] font-bold uppercase bg-[#FF6B00] text-white px-2 py-0.5 rounded">COD</span>
        </div>

        <div class="flex-1 overflow-y-auto p-6 space-y-4">
            <?php if ($cartItems): ?>
                <?php foreach ($cartItems as $item): ?>
                    <div class="flex items-center gap-4 pb-4 border-b border-slate-100 last:border-b-0">
                        <img src="<?= e(image_url($item['image'])) ?>" alt="<?= e($item['title']) ?>" class="w-16 h-16 rounded-lg object-cover bg-slate-100 shrink-0 border border-slate-200">
                        <div class="flex-1 min-w-0">
                            <h4 class="text-xs font-semibold text-slate-900 truncate"><?= e($item['title']) ?></h4>
                            <p class="text-xs font-mono font-bold text-slate-900 mt-0.5"><?= e(money($item['price'])) ?></p>

                            <div class="flex items-center justify-between mt-2 gap-2">
                                <form action="<?= e(url('cart.php')) ?>" method="post" class="flex items-center border border-slate-200 rounded-md bg-slate-50 text-xs" data-qty>
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="product_id" value="<?= (int) $item['id'] ?>">
                                    <button type="submit" name="quantity" value="<?= max(0, (int) $item['qty'] - 1) ?>" class="px-2 py-0.5 text-slate-600 hover:bg-slate-200 font-bold" aria-label="Decrease quantity">-</button>
                                    <span class="px-2.5 py-0.5 font-mono font-bold text-slate-900 bg-white"><?= (int) $item['qty'] ?></span>
                                    <button type="submit" name="quantity" value="<?= (int) $item['qty'] + 1 ?>" class="px-2 py-0.5 text-slate-600 hover:bg-slate-200 font-bold" aria-label="Increase quantity">+</button>
                                </form>

                                <form action="<?= e(url('cart.php')) ?>" method="post">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="product_id" value="<?= (int) $item['id'] ?>">
                                    <button type="submit" class="text-slate-400 hover:text-rose-600 p-1 transition-colors" title="Remove item">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="h-full flex flex-col items-center justify-center text-center py-12">
                    <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mb-4">
                        <i data-lucide="shopping-bag" class="w-8 h-8"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-800">Your bag is empty</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-xs">Explore our catalog and choose your favorite items with Cash on Delivery.</p>
                    <a href="<?= e(url('index.php#catalog-section')) ?>" class="mt-4 px-4 py-2 bg-slate-900 text-white rounded-lg text-xs font-semibold hover:bg-slate-800">Start Shopping</a>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($cartItems): ?>
            <div class="p-6 bg-slate-50 border-t border-slate-200 space-y-3">
                <div class="space-y-1.5 text-xs">
                    <div class="flex justify-between text-slate-600">
                        <span>Subtotal</span>
                        <span class="font-mono font-bold text-slate-900"><?= e(money($cartTotals['subtotal'])) ?></span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Delivery Charges (Pakistan)</span>
                        <span class="font-mono font-bold">
                            <?php if ($cartTotals['shipping'] <= 0): ?>
                                <span class="text-emerald-600 uppercase font-bold text-[11px]">Free</span>
                            <?php else: ?>
                                <?= e(money($cartTotals['shipping'])) ?>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="flex justify-between text-sm font-bold text-slate-900 pt-2 border-t border-slate-200">
                        <span>Total Amount to Pay</span>
                        <span class="font-mono text-emerald-700 text-base"><?= e(money($cartTotals['total'])) ?></span>
                    </div>
                    <p class="text-[11px] text-slate-500 text-center pt-1">&#128181; 100% Cash on Delivery (Pay only when courier arrives)</p>
                </div>

                <a href="<?= e(url('checkout.php')) ?>" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-sm transition-colors flex items-center justify-center gap-2 cursor-pointer shadow-sm">
                    <span>Proceed to COD Checkout</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>
        <?php endif; ?>
    </div>
    <div class="flex-1 cursor-pointer" data-close-drawer="cart-drawer"></div>
</div>
