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

  <!-- HERO SWIPER -->
  <section data-hero-slider class="relative bg-[#14532d] overflow-hidden" style="min-height:420px;">
    <?php if ($slides): foreach ($slides as $i => $s): 
      $price=(float)$s['price']; $orig=(float)($s['original_price']??0); $hasSale=$orig>$price;
    ?>
      <div class="hero-slide <?= $i===0?'is-active':'' ?>">
        <div class="absolute inset-0 bg-gradient-to-r from-[#14532d] via-[#166534]/95 to-[#14532d]/70"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 lg:py-20 min-h-[420px] flex items-center">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center w-full">
            <div class="text-gray-900">
              <div class="inline-flex items-center gap-2 bg-white/10 border border-white/20 px-3 py-1.5 rounded-full text-xs font-bold text-green-100 mb-4">
                <span class="w-2 h-2 rounded-full bg-[#f97316] animate-pulse"></span> Cash on Delivery • Free Delivery
              </div>
              <p class="text-green-200 font-semibold text-sm uppercase tracking-wider"><?= e($s['category_name'] ?: 'PakStore') ?></p>
              <h1 class="text-3xl sm:text-5xl font-black leading-tight mt-2" style="color:#111827"><?= e($s['title']) ?></h1>
              <p class="mt-4 text-green-50/90 text-sm sm:text-base max-w-xl"><?= e(mb_substr(trim(strip_tags((string)($s['description'] ?? 'Premium quality product with Cash on Delivery all over Pakistan.'))), 0, 140)) ?></p>
              <div class="mt-5 flex items-baseline gap-3">
                <span class="text-3xl sm:text-4xl font-black text-gray-900 font-heading"><?= e(money($price)) ?></span>
                <?php if ($hasSale): ?><span class="text-lg text-green-200/70 line-through"><?= e(money($orig)) ?></span><?php endif; ?>
              </div>
              <div class="mt-6 flex flex-wrap gap-3">
                <a href="<?= e(url('product.php?id='.(int)$s['id'])) ?>" class="pk-btn pk-btn-orange">Shop Now <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
                <a href="<?= e(url('shop.php')) ?>" class="pk-btn" style="background:rgba(255,255,255,.12);color:#111827;border-color:rgba(255,255,255,.25)">View All Products</a>
              </div>
            </div>
            <div class="flex justify-center mt-6 lg:mt-0">
              <a href="<?= e(url('product.php?id='.(int)$s['id'])) ?>" class="block bg-white/10 p-3 sm:p-4 rounded-3xl border border-white/10 backdrop-blur-sm w-full max-w-md">
                <img src="<?= e(image_url($s['image'])) ?>" alt="<?= e($s['title']) ?>" class="w-full h-[220px] sm:h-[280px] lg:h-[320px] object-contain rounded-2xl bg-white">
              </a>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; else: ?>
      <div class="hero-slide is-active">
        <div class="max-w-7xl mx-auto px-4 py-20 text-center text-gray-900">
          <h1 class="text-4xl font-black" style="color:#111827">Welcome to PakStore</h1>
          <p class="mt-3 text-green-100">Quality products with Cash on Delivery across Pakistan.</p>
          <a href="<?= e(url('shop.php')) ?>" class="pk-btn pk-btn-orange mt-6">Shop Now</a>
        </div>
      </div>
    <?php endif; ?>

    <?php if (count($slides) > 1): ?>
      <button type="button" data-hero-prev class="absolute left-3 top-1/2 -translate-y-1/2 z-20 w-10 h-10 rounded-full bg-white/90 text-[#166534] hover:bg-[#f97316] hover:text-gray-900 flex items-center justify-center shadow"><i data-lucide="chevron-left" class="w-5 h-5"></i></button>
      <button type="button" data-hero-next class="absolute right-3 top-1/2 -translate-y-1/2 z-20 w-10 h-10 rounded-full bg-white/90 text-[#166534] hover:bg-[#f97316] hover:text-gray-900 flex items-center justify-center shadow"><i data-lucide="chevron-right" class="w-5 h-5"></i></button>
      <div class="absolute bottom-4 left-0 right-0 z-20 flex justify-center gap-2">
        <?php foreach ($slides as $i => $_): ?><button type="button" data-hero-dot="<?= $i ?>" class="hero-dot rounded-full h-2.5 <?= $i===0?'w-7':'w-2.5' ?>" style="background:<?= $i===0?'#f97316':'rgba(255,255,255,.5)' ?>"></button><?php endforeach; ?>
      </div>
    <?php endif; ?>
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
