<?php
require_once __DIR__ . '/includes/functions.php';

$categories = all_categories();
try {
    $featured = db()->query("SELECT p.*, c.name AS category_name, c.slug AS category_slug FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE p.image <> '' ORDER BY (p.badge='Bestseller') DESC, p.created_at DESC, p.id DESC LIMIT 8")->fetchAll();
} catch (Throwable $e) { $featured = []; }
try {
    $best = db()->query("SELECT p.*, c.name AS category_name, c.slug AS category_slug FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE p.badge='Bestseller' OR p.badge='Hot' ORDER BY p.created_at DESC, p.id DESC LIMIT 10")->fetchAll();
    if (!$best) $best = array_slice($featured, 0, 8);
} catch (Throwable $e) { $best = array_slice($featured, 0, 8); }
try {
    $newArrivals = db()->query("SELECT p.*, c.name AS category_name, c.slug AS category_slug FROM products p LEFT JOIN categories c ON c.id=p.category_id ORDER BY p.created_at DESC, p.id DESC LIMIT 10")->fetchAll();
} catch (Throwable $e) { $newArrivals = []; }
try {
    $sale = db()->query("SELECT p.*, c.name AS category_name, c.slug AS category_slug FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE p.original_price IS NOT NULL AND p.original_price > p.price ORDER BY ((p.original_price-p.price)/p.original_price) DESC, p.id DESC LIMIT 10")->fetchAll();
} catch (Throwable $e) { $sale = []; }

$slides = array_slice($featured, 0, 5);
try { if(function_exists("ensure_banner_size_columns")) ensure_banner_size_columns(); $homeBanners = db()->query("SELECT * FROM banners WHERE is_active=1 ORDER BY sort_order ASC, id DESC LIMIT 10")->fetchAll(); } catch (Throwable $e) { $homeBanners = []; }
$pageTitle = setting('store_name', 'PakStore') . ' - Cash on Delivery Store Pakistan';
$metaDescription = 'Shop premium products in Pakistan with Cash on Delivery, free delivery offers, 7-day return, and fast nationwide shipping.';
require __DIR__ . '/includes/header.php';
if (!function_exists('pk_cat_icon')) { function pk_cat_icon(string $name, string $slug=''): string { $k=strtolower($slug.' '.$name); $map=['electronic'=>'smartphone','mobile'=>'smartphone','phone'=>'smartphone','watch'=>'watch','smart'=>'watch','fashion'=>'shirt','cloth'=>'shirt','wear'=>'shirt','shoe'=>'footprints','bag'=>'shopping-bag','beaut'=>'sparkles','cosmetic'=>'sparkles','skin'=>'sparkles','health'=>'heart-pulse','perfume'=>'spray-can','fragrance'=>'spray-can','home'=>'house','kitchen'=>'cooking-pot','decor'=>'lamp','furniture'=>'sofa','toy'=>'gamepad-2','kid'=>'baby','baby'=>'baby','sport'=>'dumbbell','fitness'=>'dumbbell','book'=>'book-open','station'=>'pencil-ruler','grocery'=>'shopping-basket','food'=>'utensils','computer'=>'laptop','laptop'=>'laptop','accessor'=>'headphones','audio'=>'headphones','earbud'=>'headphones','jewel'=>'gem','gift'=>'gift','auto'=>'car','car'=>'car']; foreach($map as $n=>$i){ if(str_contains($k,$n)) return $i; } return 'layout-grid'; } }

function pk_product_card(array $p, string $badgeType = '') {
  $price = (float)$p['price']; $orig = (float)($p['original_price'] ?? 0); $hasSale = $orig > $price;
  $disc = $hasSale ? (int)round((($orig-$price)/$orig)*100) : 0;
  $inStock = (int)($p['in_stock'] ?? 1)===1 && (int)($p['stock'] ?? 0)>0;
  ?>
  <div class="pk-card pk-product-card flex flex-col h-full">
    <a href="<?= e(url('product.php?id='.(int)$p['id'])) ?>" class="img-wrap block">
      <img src="<?= e(image_url($p['image'])) ?>" alt="<?= e($p['title']) ?>" loading="lazy">
      <?php if ($hasSale): ?><span class="pk-badge-sale">-<?= $disc ?>% OFF</span>
      <?php elseif ($badgeType==='new'): ?><span class="pk-badge-new">New</span>
      <?php elseif (!empty($p['badge'])): ?><span class="pk-badge-new"><?= e($p['badge']) ?></span><?php endif; ?>
    </a>
    <div class="body flex flex-col flex-1">
      <div class="cat"><?= e($p['category_name'] ?: 'Product') ?></div>
      <a href="<?= e(url('product.php?id='.(int)$p['id'])) ?>" class="name"><?= e($p['title']) ?></a>
      <div class="pk-stars mt-1">★★★★★ <span class="text-gray-400 text-[11px] font-normal">(4.8)</span></div>
      <div class="mt-2 flex items-baseline gap-2 flex-wrap">
        <span class="pk-price"><?= e(money($price)) ?></span>
        <?php if ($hasSale): ?><span class="pk-old"><?= e(money($orig)) ?></span><?php endif; ?>
      </div>
      <div class="text-[11px] mt-1 font-semibold <?= $inStock ? 'text-[#166534]' : 'text-red-500' ?>"><?= $inStock ? 'In Stock • Ready to Ship' : 'Out of Stock' ?></div>
      <div class="mt-auto pt-3 flex items-center gap-2">
        <?php $wished = function_exists('wishlist_has') && wishlist_has((int)$p['id']); ?>
        <form action="<?= e(url('wishlist.php')) ?>" method="post" class="shrink-0">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="toggle">
          <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
          <input type="hidden" name="redirect" value="<?= e($_SERVER['REQUEST_URI'] ?? 'index.php') ?>">
          <button type="submit" class="w-10 h-10 rounded-xl border flex items-center justify-center transition-colors <?= $wished ? 'bg-red-50 border-red-200 text-red-500' : 'bg-white border-gray-200 text-gray-500 hover:text-red-500 hover:border-red-200' ?>" title="<?= $wished ? 'Remove from Wishlist' : 'Add to Wishlist' ?>" aria-label="Wishlist">
            <i data-lucide="heart" class="w-4 h-4 <?= $wished ? 'fill-red-500' : '' ?>"></i>
          </button>
        </form>
        <?php if ($inStock): ?>
          <a href="<?= e(url('checkout.php?product='.(int)$p['id'].'&qty=1')) ?>" class="pk-btn pk-btn-orange flex-1 text-xs py-2.5">Buy Now <i data-lucide="chevron-right" class="w-4 h-4"></i></a>
        <?php else: ?>
          <span class="pk-btn flex-1 text-xs py-2.5 bg-gray-100 text-gray-400 cursor-not-allowed">Out of Stock</span>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php
}
?>
<main class="flex-1">

    <!-- HERO -->
  <section class="bg-white text-gray-900 border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20 text-center">
      <p class="text-sm font-bold uppercase tracking-wider text-[#f97316]">Pakistan's Trusted Online Store</p>
      <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black mt-3 leading-tight" style="color:#111827">Premium Shopping, <span style="color:#f97316">Cash on Delivery</span></h1>
      <p class="mt-4 text-base sm:text-lg text-gray-700 max-w-2xl mx-auto">Quality products, best prices, aur poore Pakistan mein fast delivery. Abhi shop karein.</p>
      <div class="mt-7 flex flex-wrap justify-center gap-3">
        <a href="<?= e(url('shop.php')) ?>" class="pk-btn pk-btn-orange text-base px-7 py-3.5">Shop Now <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
        <a href="<?= e(url('categories.php')) ?>" class="pk-btn pk-btn-outline bg-white text-base px-7 py-3.5">Browse Categories</a>
      </div>
    </div>
  </section>

  <!-- CATEGORIES -->
  <section class="pk-section pk-section-surface">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-end justify-between gap-4 mb-6">
        <div><h2 class="pk-title">Shop by Category</h2><p class="pk-sub">Apni favourite category explore karein</p></div>
        <a href="<?= e(url('categories.php')) ?>" class="text-sm font-bold text-[#166534] hover:text-[#f97316]">View All →</a>
      </div>
      <div class="flex gap-5 sm:gap-8 overflow-x-auto scrollbar-none pb-2">
        <?php foreach (array_slice($categories, 0, 10) as $cat): $img = trim((string)($cat['image'] ?? '')); ?>
          <a href="<?= e(url('shop.php?category=' . urlencode($cat['slug']))) ?>" class="pk-cat-circle shrink-0 w-[100px]">
            <div class="circle">
              <?php if ($img !== ''): ?><img src="<?= e(image_url($img)) ?>" alt="<?= e($cat['name']) ?>" loading="lazy">
              <?php else: ?><i data-lucide="<?= e(pk_cat_icon($cat['name'], $cat['slug'] ?? '')) ?>" class="w-8 h-8 text-[#166534]"></i><?php endif; ?>
            </div>
            <span class="label"><?= e($cat['name']) ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

    <!-- AUTOPLAY PRODUCT SLIDER -->
  <section data-hero-slider class="relative bg-[#f3f4f6] border-y border-gray-200 overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
      <div class="flex items-end justify-between gap-3 mb-5">
        <div>
          <p class="text-xs font-bold uppercase tracking-wider text-[#f97316]">Featured Picks</p>
          <h2 class="pk-title text-2xl sm:text-3xl">Trending Offers</h2>
        </div>
        <a href="<?= e(url('shop.php')) ?>" class="text-sm font-bold text-[#166534] hover:text-[#f97316]">View All →</a>
      </div>

      <div class="relative rounded-2xl overflow-hidden bg-white border border-gray-200 shadow-sm">
        <?php if (!empty($homeBanners)): foreach ($homeBanners as $i => $b):
          $desk = trim((string)($b['image'] ?? ''));
          $mob = trim((string)($b['image_mobile'] ?? ''));
          if ($mob === '') $mob = $desk;
          $link = trim((string)($b['link'] ?? ''));
          if ($link === '') $link = url('shop.php');
        ?>
          <div class="hero-slide <?= $i===0?'is-active':'' ?>">
            <a href="<?= e(str_starts_with($link,'http') ? $link : url(ltrim($link,'/'))) ?>" class="block w-full">
              <img src="<?= e(image_url($mob)) ?>" alt="<?= e($b['title'] ?? 'Banner') ?>" class="block md:hidden w-full h-auto object-cover" loading="<?= $i===0?'eager':'lazy' ?>">
              <img src="<?= e(image_url($desk)) ?>" alt="<?= e($b['title'] ?? 'Banner') ?>" class="hidden md:block w-full h-auto object-cover" loading="<?= $i===0?'eager':'lazy' ?>">
            </a>
          </div>
        <?php endforeach; elseif (!empty($slides)): foreach ($slides as $i => $s):
          $price=(float)$s['price']; $orig=(float)($s['original_price']??0); $hasSale=$orig>$price;
        ?>
          <div class="hero-slide <?= $i===0?'is-active':'' ?>">
            <div class="grid grid-cols-1 md:grid-cols-2 items-center">
              <a href="<?= e(url('product.php?id='.(int)$s['id'])) ?>" class="block bg-gray-50 p-6 sm:p-8 flex items-center justify-center min-h-[260px] sm:min-h-[360px]">
                <img src="<?= e(image_url($s['image'])) ?>" alt="<?= e($s['title']) ?>" class="max-h-[240px] sm:max-h-[320px] w-auto max-w-full object-contain mx-auto">
              </a>
              <div class="p-6 sm:p-8">
                <span class="inline-block text-xs font-bold uppercase tracking-wider text-[#166534] bg-green-50 px-3 py-1 rounded-full mb-3"><?= e($s['category_name'] ?: 'Featured') ?></span>
                <h3 class="text-2xl sm:text-3xl font-black text-gray-900 leading-tight"><?= e($s['title']) ?></h3>
                <p class="mt-3 text-gray-600 text-sm leading-7"><?= e(mb_substr(trim(strip_tags((string)($s['description'] ?? 'Quality product with Cash on Delivery all over Pakistan.'))), 0, 140)) ?></p>
                <div class="mt-4 flex items-baseline gap-3">
                  <span class="text-3xl font-black text-[#166534] font-heading"><?= e(money($price)) ?></span>
                  <?php if ($hasSale): ?><span class="text-lg text-gray-400 line-through"><?= e(money($orig)) ?></span><?php endif; ?>
                </div>
                <div class="mt-5 flex flex-wrap gap-3">
                  <a href="<?= e(url('product.php?id='.(int)$s['id'])) ?>" class="pk-btn pk-btn-orange">Shop Now</a>
                  <a href="<?= e(url('checkout.php?product='.(int)$s['id'].'&qty=1')) ?>" class="pk-btn pk-btn-green">Buy Now</a>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; else: ?>
          <div class="hero-slide is-active"><div class="p-10 text-center text-gray-600">Banners yahan admin panel se add honge.</div></div>
        <?php endif; ?>

        <?php $dotsCount = !empty($homeBanners) ? count($homeBanners) : count($slides); if ($dotsCount > 1): ?>
          <div class="absolute bottom-3 left-0 right-0 z-20 flex justify-center gap-2">
            <?php for($i=0;$i<$dotsCount;$i++): ?><button type="button" data-hero-dot="<?= $i ?>" class="hero-dot rounded-full h-2.5 <?= $i===0?'w-7':'w-2.5' ?>" style="background:<?= $i===0?'#f97316':'rgba(255,255,255,.85)' ?>;border:1px solid rgba(0,0,0,.08)"></button><?php endfor; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>
<!-- BEST SELLERS -->
  <section class="pk-section pk-section-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-end justify-between gap-4 mb-6">
        <div><h2 class="pk-title">Best Sellers</h2><p class="pk-sub">Most loved by customers across Pakistan</p></div>
        <div class="flex gap-2">
          <button type="button" class="pk-carousel-btn" data-carousel-prev data-target="best-track"><i data-lucide="chevron-left" class="w-4 h-4"></i></button>
          <button type="button" class="pk-carousel-btn" data-carousel-next data-target="best-track"><i data-lucide="chevron-right" class="w-4 h-4"></i></button>
        </div>
      </div>
      <div class="pk-carousel" data-carousel>
        <div class="pk-carousel-track scrollbar-none" id="best-track" data-carousel-track>
          <?php foreach ($best as $p) { echo '<div>'; pk_product_card($p); echo '</div>'; } ?>
        </div>
      </div>
    </div>
  </section>

  <!-- NEW ARRIVALS -->
  <section class="pk-section pk-section-surface">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-end justify-between gap-4 mb-6">
        <div><h2 class="pk-title">New Arrivals</h2><p class="pk-sub">Fresh products just added</p></div>
        <a href="<?= e(url('shop.php?sort=newest')) ?>" class="text-sm font-bold text-[#166534] hover:text-[#f97316]">View All →</a>
      </div>
      <div class="pk-carousel" data-carousel>
        <div class="pk-carousel-track scrollbar-none" data-carousel-track>
          <?php foreach ($newArrivals as $p) { echo '<div>'; pk_product_card($p, 'new'); echo '</div>'; } ?>
        </div>
      </div>
    </div>
  </section>

  <!-- SALE -->
  <?php if ($sale): ?>
  <section class="pk-section pk-section-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-end justify-between gap-4 mb-6">
        <div><h2 class="pk-title">Sale / Special Offers</h2><p class="pk-sub">Best discounts — limited time deals</p></div>
        <a href="<?= e(url('shop.php')) ?>" class="text-sm font-bold text-[#166534] hover:text-[#f97316]">Shop All →</a>
      </div>
      <div class="pk-carousel" data-carousel>
        <div class="pk-carousel-track scrollbar-none" data-carousel-track>
          <?php foreach ($sale as $p) { echo '<div>'; pk_product_card($p); echo '</div>'; } ?>
        </div>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- TRUST BADGES -->
  <section class="py-10 bg-white border-t border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="pk-trust"><div class="icon"><i data-lucide="banknote" class="w-6 h-6"></i></div><h3>Cash on Delivery</h3><p>Poore Pakistan mein COD available</p></div>
      <div class="pk-trust"><div class="icon"><i data-lucide="rotate-ccw" class="w-6 h-6"></i></div><h3>7-Day Easy Return</h3><p>Asaan return/exchange policy</p></div>
      <div class="pk-trust"><div class="icon"><i data-lucide="truck" class="w-6 h-6"></i></div><h3>TCS / Leopards Delivery</h3><p>Fast & reliable nationwide shipping</p></div>
      <div class="pk-trust"><div class="icon"><i data-lucide="shield-check" class="w-6 h-6"></i></div><h3>Secure Shopping</h3><p>Trusted store & quality products</p></div>
    </div>
  </section>
  <!-- CTA -->
  <section class="py-14 bg-[#166534] text-gray-900">
    <div class="max-w-4xl mx-auto px-4 text-center">
      <h2 class="text-3xl sm:text-4xl font-black" style="color:#111827">Ready to Shop?</h2>
      <p class="mt-3 text-green-100">Quality products, best prices, aur Cash on Delivery — all over Pakistan.</p>
      <div class="mt-6 flex flex-wrap justify-center gap-3">
        <a href="<?= e(url('shop.php')) ?>" class="pk-btn pk-btn-orange">Shop Now</a>
        <a href="<?= e(url('contact.php')) ?>" class="pk-btn" style="background:#fff;color:#166534">Contact Us</a>
      </div>
    </div>
  </section>

</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
