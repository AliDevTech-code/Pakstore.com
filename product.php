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
$_pi = pk_display_prices($price, $product['original_price'] ?? 0, $product['id'] ?? 0); $original = $_pi['has_sale'] ? $_pi['original'] : 0.0;
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


$pageTitle = $product['title'] . ' - ' . money($price) . ' | ' . setting('store_name', 'PakStore');
$productDesc = trim((string) $product['description']);
$metaDescription = $product['title'] . ' for ' . money($price) . '. Cash on Delivery available all over Pakistan.';
if ($productDesc !== '') {
    $metaDescription = $product['title'] . ' for ' . money($price) . '. ' . mb_substr($productDesc, 0, 120);
}

$seoScheme = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http');
$seoOrigin = $seoScheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
$productImage = trim((string) $product['image']);
if ($productImage === '') {
    $productImage = 'assets/images/hero_pakstore_banner_1793814622604.jpg';
}
$seoImage = preg_match('~^https?://~i', $productImage)
    ? $productImage
    : rtrim($seoOrigin, '/') . image_url($productImage);
$ogImage = rtrim($seoOrigin, '/') . url('og-image.php?id=' . (int) $product['id'])
    . '&v=' . (int) strtotime((string) ($product['updated_at'] ?? 'now'));
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
$hasSale = $original > 0;
?>
<main class="flex-1 bg-[#f3f4f6]">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <nav class="text-xs text-gray-500 mb-4 flex flex-wrap items-center gap-2" aria-label="Breadcrumb">
      <a href="<?= e(url('index.php')) ?>" class="hover:text-[#166534]">Home</a><span>/</span>
      <a href="<?= e(url('shop.php')) ?>" class="hover:text-[#166534]">Shop</a><span>/</span>
      <?php if (!empty($product['category_slug'])): ?><a href="<?= e(url('shop.php?category=' . urlencode($product['category_slug']))) ?>" class="hover:text-[#166534]"><?= e($product['category_name']) ?></a><span>/</span><?php endif; ?>
      <span class="text-gray-800 font-semibold truncate max-w-[220px]"><?= e($product['title']) ?></span>
    </nav>

    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
      <div class="grid grid-cols-1 lg:grid-cols-2">
        <div class="p-4 sm:p-6 bg-gray-50 border-b lg:border-b-0 lg:border-r border-gray-200">
          <div class="pk-gallery-main">
            <img data-gallery-main src="<?= e(image_url($gallery[0])) ?>" alt="<?= e($product['title']) ?>" class="w-full h-full object-cover">
            <?php if ($hasSale && $discount > 0): ?><span class="pk-badge-sale">-<?= $discount ?>% OFF</span><?php elseif(!empty($product['badge'])): ?><span class="pk-badge-new"><?= e($product['badge']) ?></span><?php endif; ?>
          </div>
          <?php if (count($gallery) > 1): ?>
            <div class="mt-3 grid grid-cols-5 gap-2">
              <?php foreach ($gallery as $index => $image): ?>
                <button type="button" data-gallery-thumb="<?= e(image_url($image)) ?>" class="pk-thumb <?= $index === 0 ? 'is-active' : '' ?>" style="<?= $index===0?'border-color:#166534;opacity:1':'' ?>">
                  <img src="<?= e(image_url($image)) ?>" alt="<?= e($product['title']) ?> thumbnail <?= $index + 1 ?>">
                </button>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          <div class="mt-4 grid grid-cols-3 gap-2 text-[11px] text-gray-600 text-center">
            <div class="p-2 bg-white border border-gray-200 rounded-lg"><i data-lucide="truck" class="w-4 h-4 text-[#166534] mx-auto mb-1"></i>COD Nationwide</div>
            <div class="p-2 bg-white border border-gray-200 rounded-lg"><i data-lucide="shield-check" class="w-4 h-4 text-[#166534] mx-auto mb-1"></i>Quality Checked</div>
            <div class="p-2 bg-white border border-gray-200 rounded-lg"><i data-lucide="rotate-ccw" class="w-4 h-4 text-[#166534] mx-auto mb-1"></i>7-Day Return</div>
          </div>
        </div>

        <div class="p-4 sm:p-6 lg:p-8 flex flex-col">
          <div class="flex items-start justify-between gap-3">
            <div>
              <span class="text-xs font-bold uppercase tracking-wider text-gray-400"><?= e($product['category_name'] ?: 'Product') ?></span>
              <h1 class="text-2xl sm:text-3xl font-black text-[#166534] leading-tight mt-1"><?= e($product['title']) ?></h1>
            </div>
            <form action="<?= e(url('wishlist.php')) ?>" method="post" class="shrink-0">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="toggle">
              <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
              <input type="hidden" name="redirect" value="product.php?id=<?= (int) $product['id'] ?>">
              <button type="submit" class="p-2.5 rounded-full border transition-colors <?= wishlist_has((int) $product['id']) ? 'bg-red-500 border-red-500 text-gray-900' : 'bg-white border-gray-200 text-gray-400 hover:text-red-500' ?>" title="Wishlist">
                <i data-lucide="heart" class="w-5 h-5 <?= wishlist_has((int) $product['id']) ? 'fill-white' : '' ?>"></i>
              </button>
            </form>
          </div>

          <div class="mt-2 flex flex-wrap items-center gap-3 text-sm">
            <div class="pk-stars">★★★★★ <span class="text-gray-500 text-xs font-normal">(24 reviews)</span></div>
            <?php if ($inStock): ?><span class="text-[#166534] font-bold flex items-center gap-1"><i data-lucide="check-circle-2" class="w-4 h-4"></i> In Stock</span>
            <?php else: ?><span class="text-red-600 font-bold">Out of Stock</span><?php endif; ?>
          </div>

          <div class="mt-4 p-4 bg-green-50 border border-green-100 rounded-xl">
            <div class="flex flex-wrap items-baseline gap-2">
              <span class="text-3xl font-black text-[#166534] font-heading"><?= e(money($price)) ?></span>
              <?php if ($hasSale): ?>
                <span class="text-base text-gray-400 line-through"><?= e(money($original)) ?></span>
                <span class="text-xs font-bold text-[#f97316] bg-orange-50 border border-orange-200 px-2 py-1 rounded">Save <?= e(money($original - $price)) ?></span>
              <?php endif; ?>
            </div>
            <p class="text-xs text-gray-600 mt-1">Inclusive of all taxes • <strong class="text-[#166534]">Cash on Delivery</strong> available all over Pakistan.</p>
          </div>

          <?php if ($product['description']): ?>
            <?php $descText = trim((string)$product['description']); $descLong = mb_strlen($descText) > 180 || substr_count($descText, "\n") >= 3; ?>
            <div class="mt-4">
              <h2 class="text-sm font-bold text-gray-900 mb-1">Product Description</h2>
              <p id="product-description" class="text-sm text-gray-600 leading-7<?= $descLong ? ' overflow-hidden' : '' ?>"<?= $descLong ? ' style="max-height:6.5rem"' : '' ?>><?= nl2br(e($descText)) ?></p>
              <?php if ($descLong): ?><button type="button" data-desc-toggle="product-description" class="mt-1 text-xs font-bold text-[#166534]"><span data-desc-label>Read more</span> <i data-lucide="chevron-down" class="w-3.5 h-3.5 inline"></i></button><?php endif; ?>
            </div>
          <?php endif; ?>

          <?php if ($features): ?>
            <ul class="mt-4 space-y-2">
              <?php foreach ($features as $feature): ?>
                <li class="flex items-start gap-2 text-sm text-gray-700"><i data-lucide="check-circle" class="w-4 h-4 text-[#166534] shrink-0 mt-0.5"></i><span><?= e($feature) ?></span></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>

          <div class="mt-6 pt-5 border-t border-gray-200 space-y-3">
            <form action="<?= e(url('cart.php')) ?>" method="post" class="flex flex-wrap items-stretch gap-2" id="main-atc-form">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="add">
              <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
              <div class="flex items-center border border-gray-300 rounded-xl bg-white" data-qty>
                <button type="button" data-qty-step="-1" class="px-3 py-3 text-gray-600 hover:bg-gray-100 font-bold">-</button>
                <input type="number" name="quantity" value="1" min="1" max="<?= max(1, (int)$product['stock']) ?>" class="w-14 py-3 text-center font-bold border-x border-gray-200 focus:outline-none" aria-label="Quantity">
                <button type="button" data-qty-step="1" class="px-3 py-3 text-gray-600 hover:bg-gray-100 font-bold">+</button>
              </div>
              <button type="submit" class="flex-1 pk-btn pk-btn-orange" <?= !$inStock ? 'disabled style="opacity:.5;cursor:not-allowed"' : '' ?>><i data-lucide="shopping-cart" class="w-4 h-4"></i> Add to Cart</button>
              <button type="submit" formaction="<?= e(url('cart.php')) ?>" name="redirect" value="checkout.php?product=<?= (int)$product['id'] ?>&qty=1" class="flex-1 pk-btn pk-btn-green" <?= !$inStock ? 'disabled style="opacity:.5;cursor:not-allowed"' : '' ?>>Buy Now <i data-lucide="zap" class="w-4 h-4"></i></button>
            </form>
            <p class="text-xs text-gray-500">Cash on Delivery • 7-Day Return • TCS/Leopards Delivery</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Reviews -->
    <section class="mt-8 bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
      <h2 class="pk-title text-xl">Customer Reviews</h2>
      <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
        <?php foreach ([['Ali','Great quality, same as picture. Delivery fast thi.'],['Hina','Price reasonable hai aur COD bohat convenient hai.'],['Usman','Packaging achi thi, product original laga.']] as $rv): ?>
          <div class="border border-gray-200 rounded-xl p-4"><div class="pk-stars">★★★★★</div><p class="text-sm text-gray-600 mt-2"><?= e($rv[1]) ?></p><div class="text-xs font-bold text-[#166534] mt-3"><?= e($rv[0]) ?> — Verified Buyer</div></div>
        <?php endforeach; ?>
      </div>
    </section>

    <?php if ($related): ?>
    <section class="mt-10 pb-8">
      <div class="flex items-end justify-between gap-4 mb-5">
        <div><h2 class="pk-title">Related Products</h2><p class="pk-sub">You may also like</p></div>
        <a href="<?= e(url('shop.php')) ?>" class="text-sm font-bold text-[#166534]">View All →</a>
      </div>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-5">
        <?php foreach ($related as $item): 
          $_pi = pk_display_prices($item['price'] ?? 0, $item['original_price'] ?? 0, $item['id'] ?? 0); $rPrice=$_pi['price']; $rOrig=$_pi['original']; $rSale=$_pi['has_sale'];
          $rInStock=(int)$item['in_stock']===1 && (int)$item['stock']>0;
        ?>
          <div class="pk-card pk-product-card flex flex-col">
            <a href="<?= e(url('product.php?id='.(int)$item['id'])) ?>" class="img-wrap block">
              <img src="<?= e(image_url($item['image'])) ?>" alt="<?= e($item['title']) ?>" loading="lazy">
              <?php if($rSale): ?><span class="pk-badge-sale">Sale</span><?php endif; ?>
            </a>
            <div class="body flex flex-col flex-1">
              <div class="cat"><?= e($item['category_name'] ?: 'Product') ?></div>
              <a href="<?= e(url('product.php?id='.(int)$item['id'])) ?>" class="name"><?= e($item['title']) ?></a>
              <div class="mt-2 flex items-baseline gap-2"><span class="pk-price"><?= e(money($rPrice)) ?></span><?php if($rSale): ?><span class="pk-old"><?= e(money($rOrig)) ?></span><?php endif; ?></div>
              <div class="text-[11px] mt-1 font-semibold <?= $rInStock?'text-[#166534]':'text-red-500' ?>"><?= $rInStock?'In Stock':'Out of Stock' ?></div>
              <div class="mt-auto pt-3 flex items-center gap-2">
                <?php $wished = function_exists('wishlist_has') && wishlist_has((int)$item['id']); ?>
                <form action="<?= e(url('wishlist.php')) ?>" method="post" class="shrink-0">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="toggle">
                  <input type="hidden" name="product_id" value="<?= (int)$item['id'] ?>">
                  <input type="hidden" name="redirect" value="<?= e($_SERVER['REQUEST_URI'] ?? 'index.php') ?>">
                  <button type="submit" class="w-10 h-10 rounded-xl border flex items-center justify-center transition-colors <?= $wished ? 'bg-red-50 border-red-200 text-red-500' : 'bg-white border-gray-200 text-gray-500 hover:text-red-500 hover:border-red-200' ?>" title="<?= $wished ? 'Remove from Wishlist' : 'Add to Wishlist' ?>" aria-label="Wishlist">
                    <i data-lucide="heart" class="w-4 h-4 <?= $wished ? 'fill-red-500' : '' ?>"></i>
                  </button>
                </form>
                <?php if ($rInStock): ?>
                  <a href="<?= e(url('checkout.php?product='.(int)$item['id'].'&qty=1')) ?>" class="pk-btn pk-btn-orange flex-1 text-[11px] sm:text-xs py-2 px-2 sm:px-3 whitespace-nowrap">Buy Now</a>
                <?php else: ?>
                  <span class="pk-btn flex-1 text-xs py-2.5 bg-gray-100 text-gray-400 cursor-not-allowed">Out of Stock</span>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>
  </div>

  <!-- Sticky ATC mobile -->
  <?php if ($inStock): ?>
  <div class="pk-sticky-atc print-hide">
    <div class="flex-1 min-w-0">
      <div class="text-xs text-gray-500 truncate"><?= e($product['title']) ?></div>
      <div class="font-black text-[#166534]"><?= e(money($price)) ?> <?php if($hasSale): ?><span class="text-xs text-gray-400 line-through font-normal"><?= e(money($original)) ?></span><?php endif; ?></div>
    </div>
    <form action="<?= e(url('cart.php')) ?>" method="post" class="flex gap-2">
      <?= csrf_field() ?><input type="hidden" name="action" value="add"><input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>"><input type="hidden" name="quantity" value="1">
      <button type="submit" class="pk-btn pk-btn-orange text-xs py-2.5 px-3">Add to Cart</button>
      <button type="submit" name="redirect" value="checkout.php?product=<?= (int)$product['id'] ?>&qty=1" class="pk-btn pk-btn-green text-xs py-2.5 px-3">Buy Now</button>
    </form>
  </div>
  <script>document.body.classList.add('has-sticky-atc');</script>
  <?php endif; ?>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>