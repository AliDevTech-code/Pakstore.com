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
$shareImage = trim((string) ($ogImage ?? ''));
if ($shareImage !== '' && !preg_match('~^https?://~i', $shareImage)) {
    $localImagePath = dirname(__DIR__) . '/' . ltrim($shareImage, '/');
    $shareImage = is_file($localImagePath)
        ? rtrim($siteOrigin, '/') . image_url($shareImage)
        : '';
}
if ($shareImage === '') {
    /* Always give crawlers a real 1200x630 JPG: small logos and WebP
       uploads are ignored by social platforms, so the card is rendered
       by og-image.php instead of linking the raw file. */
    $siteCard = url('og-image.php?type=site');
    if ($logoVersion !== '') {
        $siteCard .= '&v=' . rawurlencode(trim(substr($logoVersion, 3)));
    }
    $shareImage = rtrim($siteOrigin, '/') . $siteCard;
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
    <meta name="google-site-verification" content="ByRh6Xdd-AWXEIN41Nqmz-gGEdc7mCFRaK4RiSkRrEc">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($metaDescription) ?>">
    <meta name="robots" content="index,follow,max-image-preview:large">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($metaDescription) ?>">
    <meta property="og:type" content="<?= e($ogType) ?>">
    <meta property="og:url" content="<?= e($canonicalUrl) ?>">
    <meta property="og:site_name" content="<?= e($storeName) ?>">
    <?php if ($shareImage !== ''): ?>
        <meta property="og:image" content="<?= e($shareImage) ?>">
        <meta property="og:image:type" content="image/jpeg">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta name="twitter:image" content="<?= e($shareImage) ?>">
    <?php endif; ?>
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($pageTitle) ?>">
    <meta name="twitter:description" content="<?= e($metaDescription) ?>">
    <link rel="canonical" href="<?= e($canonicalUrl) ?>">
    <meta name="theme-color" content="#166534">
    <?= $structuredData ?>

    <link rel="preload" href="<?= e(url('assets/fonts/plus-jakarta-sans-latin.woff2')) ?>" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?= e(url('assets/fonts/outfit-latin.woff2')) ?>" as="font" type="font/woff2" crossorigin>

    <link rel="stylesheet" href="<?= e(url('assets/css/tailwind.css') . '?v=4') ?>">
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css') . '?v=28') ?>">
    <script src="<?= e(url('assets/js/lucide.min.js')) ?>"></script>
    <style>
        /* Keep drawers closed by default even if the external stylesheet fails to load. */
        [data-drawer][hidden] { display: none !important; }
    </style>
</head>
<body class="min-h-screen flex flex-col bg-white text-gray-800">

<div class="pk-announcement print-hide">
  <div class="max-w-7xl mx-auto px-4 py-2 flex items-center justify-center gap-3 sm:gap-6 text-center flex-wrap">
    <span class="inline-flex items-center gap-1.5"><i data-lucide="truck" class="w-3.5 h-3.5" style="color:#f97316"></i> Free Delivery All Over Pakistan</span>
    <span class="hidden sm:inline opacity-40">|</span>
    <span class="inline-flex items-center gap-1.5"><i data-lucide="banknote" class="w-3.5 h-3.5" style="color:#f97316"></i> Cash on Delivery Available</span>
    <span class="hidden sm:inline opacity-40">|</span>
    <span class="inline-flex items-center gap-1.5"><i data-lucide="shield-check" class="w-3.5 h-3.5" style="color:#f97316"></i> 7-Day Easy Return</span>
  </div>
</div>

<header class="pk-header print-hide">
  <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between gap-2 sm:gap-4 h-[68px] sm:h-[76px]">
      <div class="nav-left flex items-center gap-2 shrink-0">
        <a href="<?= e(url('index.php')) ?>" class="flex items-center gap-1.5 shrink-0" aria-label="<?= e($storeName) ?> home">
          <span id="store-text-logo" class="<?= $logoUrl !== '' ? 'hidden ' : 'flex ' ?>items-end pk-brand text-2xl sm:text-3xl">
            <span class="pk">Pak</span><span class="store">Store</span>
          </span>
          <?php if ($logoUrl !== ''): ?>
            <img src="<?= e(image_url($logoUrl) . $logoVersion) ?>" alt="<?= e($storeName) ?>" class="h-9 sm:h-11 w-auto max-w-[120px] sm:max-w-[170px] object-contain" onerror="this.style.display='none';var f=document.getElementById('store-text-logo');if(f){f.classList.remove('hidden');f.classList.add('flex');}">
          <?php endif; ?>
        </a>
        <button type="button" data-open-drawer="category-drawer" class="mobile-menu-btn menu-icon-btn border border-gray-200 text-gray-700 hover:text-[#166534] hover:bg-green-50" title="Menu" aria-label="Open Menu"><i data-lucide="menu" class="w-5 h-5"></i></button>
      </div>

      <nav class="desktop-nav pk-nav">
        <a href="<?= e(url('index.php')) ?>" class="<?= $currentPage === 'index.php' ? 'is-active' : '' ?>">Home</a>
        <a href="<?= e(url('shop.php')) ?>" class="<?= $currentPage === 'shop.php' ? 'is-active' : '' ?>">Shop</a>
        <a href="<?= e(url('categories.php')) ?>" class="<?= $currentPage === 'categories.php' ? 'is-active' : '' ?>">Categories</a>
        <a href="<?= e(url('track-order.php')) ?>" class="<?= $currentPage === 'track-order.php' ? 'is-active' : '' ?>">Track Order</a>
      </nav>

      <div class="flex-1 min-w-0 max-w-xl desktop-search">
        <form action="<?= e(url('shop.php')) ?>" method="get" class="pk-search" role="search">
          <input type="search" name="q" value="<?= e($searchQuery) ?>" placeholder="Search products... / پروڈکٹ تلاش کریں" aria-label="Search products">
          <button type="submit" aria-label="Search"><i data-lucide="search" class="w-4 h-4"></i></button>
        </form>
      </div>

      <div class="mobile-inline-search flex-1 min-w-0">
        <form action="<?= e(url('shop.php')) ?>" method="get" class="pk-search" role="search">
          <input type="search" name="q" value="<?= e($searchQuery) ?>" placeholder="Search products..." aria-label="Search products">
          <button type="submit" aria-label="Search"><i data-lucide="search" class="w-4 h-4"></i></button>
        </form>
      </div>

      <div class="flex items-center gap-2 sm:gap-3 shrink-0 ml-auto">
        <button type="button" data-search-toggle class="mobile-menu-btn menu-icon-btn border border-gray-200 text-gray-700 hover:text-[#166534] hover:bg-green-50" title="Search" aria-label="Open search"><i data-lucide="search" class="w-5 h-5"></i></button>
        <button type="button" data-search-close class="search-close-btn p-2 rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-50" title="Close search" aria-label="Close search"><i data-lucide="x" class="w-5 h-5"></i></button>
        <a href="<?= e(url('account.php')) ?>" class="inline-flex pk-icon-btn" title="Account" aria-label="Account"><i data-lucide="user" class="w-5 h-5"></i></a>
        <button type="button" data-open-drawer="cart-drawer" class="relative inline-flex items-center gap-2 px-3 sm:px-4 py-2.5 rounded-xl bg-[#166534] text-white font-bold text-xs hover:bg-[#14532d]">
          <i data-lucide="shopping-bag" class="w-4 h-4"></i>
          <span class="hidden sm:inline">Cart</span>
          <span class="min-w-[20px] h-5 px-1 rounded-full bg-[#f97316] text-gray-900 text-[10px] font-black inline-flex items-center justify-center"><?= (int)$navCartCount ?></span>
        </button>
      </div>
    </div>
  </div>

  <div class="mobile-categories border-t border-gray-100 bg-white overflow-x-auto scrollbar-none">
    <div class="max-w-7xl mx-auto px-4 flex items-center gap-4 py-2.5 text-xs font-semibold text-gray-600">
      <a href="<?= e(url('shop.php')) ?>" class="text-[#166534] font-bold whitespace-nowrap">All Products</a>
      <?php foreach ($navCategories as $cat): ?>
        <a href="<?= e(url('shop.php?category=' . urlencode($cat['slug']))) ?>" class="whitespace-nowrap hover:text-[#166534] <?= $activeCategory === $cat['slug'] ? 'text-[#166534] font-bold' : '' ?>"><?= e($cat['name']) ?></a>
      <?php endforeach; ?>
    </div>
  </div>

  <script>
  (function () {
    var header = document.currentScript ? document.currentScript.closest('header') : null;
    function setSearch(open) {
      header = header || document.querySelector('header.pk-header');
      if (!header) return;
      header.classList.toggle('search-open', open);
      var btn = header.querySelector('[data-search-toggle]');
      if (btn) btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (open) { var i = header.querySelector('.mobile-inline-search input[type=search]'); if (i) { try { i.focus(); } catch (x) {} } }
    }
    window.setSearch = setSearch;
    function bind() {
      header = header || document.querySelector('header.pk-header');
      if (!header || header.dataset.searchBound) return;
      header.dataset.searchBound = '1';
      var openBtn = header.querySelector('[data-search-toggle]');
      var closeBtn = header.querySelector('[data-search-close]');
      if (openBtn) openBtn.addEventListener('click', function (e) { e.preventDefault(); e.stopPropagation(); setSearch(true); });
      if (closeBtn) closeBtn.addEventListener('click', function (e) { e.preventDefault(); e.stopPropagation(); setSearch(false); });
    }
    bind(); document.addEventListener('DOMContentLoaded', bind);
  })();
  </script>
</header>

<?php render_flashes(); ?>

<div id="category-drawer" data-drawer hidden class="fixed inset-0 z-50 overflow-hidden bg-black/50 backdrop-blur-sm flex print-hide">
  <div class="menu-drawer-panel h-full shadow-2xl flex flex-col animate-slide-left overflow-y-auto">
    <div class="flex items-center justify-between pb-4 border-b border-gray-200">
      <div class="pk-brand text-2xl"><span class="pk">Pak</span><span class="store">Store</span></div>
      <button type="button" data-close-drawer="category-drawer" class="pk-icon-btn"><i data-lucide="x" class="w-5 h-5"></i></button>
    </div>
    <nav class="mt-4 space-y-1 text-sm font-bold text-gray-700">
      <a href="<?= e(url('index.php')) ?>" class="flex items-center gap-3 px-3 py-3 rounded-xl hover:bg-green-50 hover:text-[#166534]"><i data-lucide="home" class="w-4 h-4 text-[#f97316]"></i>Home</a>
      <a href="<?= e(url('shop.php')) ?>" class="flex items-center gap-3 px-3 py-3 rounded-xl hover:bg-green-50 hover:text-[#166534]"><i data-lucide="store" class="w-4 h-4 text-[#f97316]"></i>Shop</a>
      <a href="<?= e(url('categories.php')) ?>" class="flex items-center gap-3 px-3 py-3 rounded-xl hover:bg-green-50 hover:text-[#166534]"><i data-lucide="layout-grid" class="w-4 h-4 text-[#f97316]"></i>Categories</a>
      <a href="<?= e(url('track-order.php')) ?>" class="flex items-center gap-3 px-3 py-3 rounded-xl hover:bg-green-50 hover:text-[#166534]"><i data-lucide="package-search" class="w-4 h-4 text-[#f97316]"></i>Track Order</a>
      <a href="<?= e(url('about.php')) ?>" class="flex items-center gap-3 px-3 py-3 rounded-xl hover:bg-green-50 hover:text-[#166534]"><i data-lucide="info" class="w-4 h-4 text-[#f97316]"></i>About Us</a>
      <a href="<?= e(url('contact.php')) ?>" class="flex items-center gap-3 px-3 py-3 rounded-xl hover:bg-green-50 hover:text-[#166534]"><i data-lucide="mail" class="w-4 h-4 text-[#f97316]"></i>Contact Us</a>
      <a href="<?= e(url('account.php')) ?>" class="flex items-center gap-3 px-3 py-3 rounded-xl hover:bg-green-50 hover:text-[#166534]"><i data-lucide="user" class="w-4 h-4 text-[#f97316]"></i>My Account</a>
    </nav>
    <div class="mt-auto pt-4 border-t border-gray-200">
      <div class="p-4 rounded-xl bg-green-50 text-sm"><div class="font-bold text-[#166534]">Cash on Delivery</div><div class="text-gray-600 text-xs mt-1">All over Pakistan with easy return.</div></div>
    </div>
  </div>
  <div class="flex-1 cursor-pointer" data-close-drawer="category-drawer"></div>
</div>

<div id="cart-drawer" data-drawer hidden class="fixed inset-0 z-50 overflow-hidden bg-black/50 backdrop-blur-sm flex justify-end print-hide">
  <div class="w-full max-w-md bg-white h-full shadow-2xl flex flex-col border-l border-gray-200 animate-slide-right">
    <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
      <div class="flex items-center gap-2 font-bold text-gray-900"><i data-lucide="shopping-bag" class="w-5 h-5 text-[#166534]"></i>Shopping Cart (<?= count($cartItems) ?>)</div>
      <button type="button" data-close-drawer="cart-drawer" class="pk-icon-btn"><i data-lucide="x" class="w-5 h-5"></i></button>
    </div>
    <div class="flex-1 overflow-y-auto p-5 space-y-4">
      <?php if ($cartItems): ?>
        <?php foreach ($cartItems as $item): ?>
          <div class="flex gap-3 pb-4 border-b border-gray-100">
            <img src="<?= e(image_url($item['image'])) ?>" alt="<?= e($item['title']) ?>" class="w-16 h-16 rounded-lg object-cover bg-gray-100 border border-gray-200">
            <div class="flex-1 min-w-0">
              <div class="text-sm font-bold text-gray-900 truncate"><?= e($item['title']) ?></div>
              <div class="text-sm font-extrabold text-[#166534] mt-0.5"><?= e(money($item['price'])) ?></div>
              <div class="flex items-center justify-between mt-2">
                <span class="text-xs text-gray-500">Qty: <?= (int)$item['qty'] ?></span>
                <form action="<?= e(url('cart.php')) ?>" method="post"><?= csrf_field() ?><input type="hidden" name="action" value="remove"><input type="hidden" name="product_id" value="<?= (int)$item['id'] ?>"><button type="submit" class="text-gray-400 hover:text-red-600"><i data-lucide="trash-2" class="w-4 h-4"></i></button></form>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="text-center py-12 text-gray-500"><i data-lucide="shopping-bag" class="w-12 h-12 mx-auto mb-3 text-gray-300"></i><div class="font-bold text-gray-800">Your cart is empty</div><p class="text-sm mt-1">Add products to start shopping.</p></div>
      <?php endif; ?>
    </div>
    <?php if ($cartItems): ?>
    <div class="p-5 bg-gray-50 border-t border-gray-200 space-y-3">
      <div class="flex justify-between text-sm"><span class="text-gray-600">Subtotal</span><span class="font-bold"><?= e(money($cartTotals['subtotal'])) ?></span></div>
      <div class="flex justify-between text-sm"><span class="text-gray-600">Delivery</span><span class="font-bold"><?= $cartTotals['shipping'] <= 0 ? '<span class="text-[#166534]">FREE</span>' : e(money($cartTotals['shipping'])) ?></span></div>
      <div class="flex justify-between text-base font-black text-gray-900 pt-2 border-t border-gray-200"><span>Total</span><span class="text-[#166534]"><?= e(money($cartTotals['total'])) ?></span></div>
      <a href="<?= e(url('checkout.php')) ?>" class="pk-btn pk-btn-orange w-full">Proceed to Checkout</a>
      <a href="<?= e(url('cart.php')) ?>" class="pk-btn pk-btn-outline w-full">View Cart</a>
    </div>
    <?php endif; ?>
  </div>
  <div class="flex-1 cursor-pointer" data-close-drawer="cart-drawer"></div>
</div>