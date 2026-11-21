<?php
require_once __DIR__ . '/includes/functions.php';

$q = get_string('q');
$activeSlug = get_string('category');
$sort = get_string('sort', 'featured');
$minPrice = get_string('min_price');
$maxPrice = get_string('max_price');
$allowedSorts = ['featured', 'price-low', 'price-high', 'newest'];
if (!in_array($sort, $allowedSorts, true)) $sort = 'featured';

$categories = all_categories();
$activeCategoryRow = $activeSlug !== '' ? find_category_by_slug($activeSlug) : null;
if ($activeSlug !== '' && !$activeCategoryRow) $activeSlug = '';

$where = ['1=1'];
$params = [];
if ($activeSlug !== '') { $where[] = 'c.slug = ?'; $params[] = $activeSlug; }
if ($q !== '') { $like = '%' . $q . '%'; $where[] = '(p.title LIKE ? OR p.description LIKE ? OR p.sku LIKE ? OR c.name LIKE ?)'; array_push($params, $like, $like, $like, $like); }
if ($minPrice !== '' && is_numeric($minPrice)) { $where[] = 'p.price >= ?'; $params[] = (float)$minPrice; }
if ($maxPrice !== '' && is_numeric($maxPrice)) { $where[] = 'p.price <= ?'; $params[] = (float)$maxPrice; }

$orderBy = match ($sort) {
    'price-low' => 'p.price ASC, p.id DESC',
    'price-high' => 'p.price DESC, p.id DESC',
    'newest' => 'p.created_at DESC, p.id DESC',
    default => "(p.badge = 'Bestseller') DESC, p.created_at DESC, p.id DESC",
};

$stmt = db()->prepare("SELECT p.*, c.name AS category_name, c.slug AS category_slug FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE " . implode(' AND ', $where) . " ORDER BY $orderBy LIMIT 120");
$stmt->execute($params);
$products = $stmt->fetchAll();

$pageTitle = ($activeCategoryRow ? $activeCategoryRow['name'] . ' - ' : ($q !== '' ? 'Search: ' . $q . ' - ' : 'Shop - ')) . setting('store_name', 'PakStore');
$metaDescription = 'Shop quality products in Pakistan with Cash on Delivery. Browse by category, price and latest arrivals.';
require __DIR__ . '/includes/header.php';
?>
<main class="flex-1 bg-[#f3f4f6]">
  <section class="bg-[#166534] text-white py-10 sm:py-14">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <p class="text-sm font-semibold text-green-200">PakStore Collection</p>
      <h1 class="text-3xl sm:text-4xl font-black mt-1" style="color:#111827"><?= e($activeCategoryRow ? $activeCategoryRow['name'] : 'Shop All Products') ?></h1>
      <p class="text-green-100 mt-2 text-sm">Quality products • Best prices • Cash on Delivery all over Pakistan</p>
    </div>
  </section>

  <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
      <aside class="lg:col-span-1">
        <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm sticky top-24">
          <div class="flex items-center justify-between mb-4">
            <h2 class="font-extrabold text-[#166534] text-lg">Filters</h2>
            <a href="<?= e(url('shop.php')) ?>" class="text-xs font-bold text-[#f97316]">Clear</a>
          </div>
          <form method="get" action="<?= e(url('shop.php')) ?>" class="space-y-5">
            <div>
              <label class="pk-label">Search</label>
              <input type="search" name="q" value="<?= e($q) ?>" class="pk-input" placeholder="Search products...">
            </div>

            <?php if ($activeSlug !== ''): ?><input type="hidden" name="category" value="<?= e($activeSlug) ?>"><?php endif; ?>

            <div>
              <div class="pk-label mb-2">Price Range</div>
              <div class="grid grid-cols-2 gap-3">
                <div><input type="number" name="min_price" value="<?= e($minPrice) ?>" class="pk-input" min="0" placeholder="Min"></div>
                <div><input type="number" name="max_price" value="<?= e($maxPrice) ?>" class="pk-input" min="0" placeholder="Max"></div>
              </div>
            </div>

            <div>
              <div class="pk-label mb-2">Sort By</div>
              <div class="space-y-2">
                <?php $sortOpts = ['featured'=>'Featured','price-low'=>'Price: Low to High','price-high'=>'Price: High to Low','newest'=>'Newest']; foreach ($sortOpts as $val=>$label): ?>
                  <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                    <input type="radio" name="sort" value="<?= e($val) ?>" <?= $sort===$val?'checked':'' ?> class="accent-[#166534]">
                    <span><?= e($label) ?></span>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>

            <button type="submit" class="pk-btn pk-btn-green w-full">Apply Filters</button>
          </form>
        </div>
      </aside>

      <div class="lg:col-span-3">
        <div class="mb-4">
          <div class="flex items-center gap-2 overflow-x-auto scrollbar-none whitespace-nowrap pb-1 text-sm">
            <?php $catBaseTop = array_filter(['q'=>$q,'min_price'=>$minPrice,'max_price'=>$maxPrice,'sort'=>$sort]); ?>
            <a href="<?= e(url('shop.php?' . http_build_query($catBaseTop))) ?>" class="<?= $activeSlug==='' ? 'text-[#166534] font-bold' : 'text-gray-500 hover:text-[#166534] font-semibold' ?>">All</a>
            <?php foreach ($categories as $cat): $pms = $catBaseTop; $pms['category']=$cat['slug']; ?>
              <span class="text-gray-300">&gt;</span>
              <a href="<?= e(url('shop.php?' . http_build_query($pms))) ?>" class="<?= $activeSlug===$cat['slug'] ? 'text-[#166534] font-bold' : 'text-gray-500 hover:text-[#166534] font-semibold' ?>"><?= e($cat['name']) ?></a>
            <?php endforeach; ?>
          </div>
          <p class="text-sm text-gray-600 mt-2"><strong class="text-gray-900"><?= count($products) ?></strong> product<?= count($products)===1?'':'s' ?> found</p>
        </div>
        <?php if ($products): ?>
          <div class="grid grid-cols-2 md:grid-cols-3 gap-4 sm:gap-5">
            <?php foreach ($products as $p): 
              $_pi = pk_display_prices($p['price'] ?? 0, $p['original_price'] ?? 0, $p['id'] ?? 0); $price = $_pi['price']; $orig = $_pi['original']; $hasSale = $_pi['has_sale'];
              $disc = $hasSale ? (int)round((($orig-$price)/$orig)*100) : 0;
              $inStock = (int)$p['in_stock']===1 && (int)$p['stock']>0;
            ?>
              <div class="pk-card pk-product-card flex flex-col">
                <a href="<?= e(url('product.php?id='.(int)$p['id'])) ?>" class="img-wrap block">
                  <img src="<?= e(image_url($p['image'])) ?>" alt="<?= e($p['title']) ?>" loading="lazy">
                  <?php if ($hasSale): ?><span class="pk-badge-sale">-<?= $disc ?>%</span><?php elseif(!empty($p['badge'])): ?><span class="pk-badge-new"><?= e($p['badge']) ?></span><?php endif; ?>
                </a>
                <div class="body flex flex-col flex-1">
                  <div class="cat"><?= e($p['category_name'] ?: 'Product') ?></div>
                  <a href="<?= e(url('product.php?id='.(int)$p['id'])) ?>" class="name"><?= e($p['title']) ?></a>
                  <div class="pk-stars mt-1">★★★★★ <span class="text-gray-400 text-[11px]">(4.8)</span></div>
                  <div class="mt-2 flex items-baseline gap-2">
                    <span class="pk-price"><?= e(money($price)) ?></span>
                    <?php if ($hasSale): ?><span class="pk-old"><?= e(money($orig)) ?></span><?php endif; ?>
                  </div>
                  <div class="text-[11px] mt-1 <?= $inStock ? 'text-[#166534]' : 'text-red-500' ?> font-semibold"><?= $inStock ? 'In Stock' : 'Out of Stock' ?></div>
                  <div class="mt-auto pt-3 flex items-center gap-2">
                    <?php $wished = function_exists('wishlist_has') && wishlist_has((int)$p['id']); ?>
                    <form action="<?= e(url('wishlist.php')) ?>" method="post" class="shrink-0">
                      <?= csrf_field() ?>
                      <input type="hidden" name="action" value="toggle">
                      <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                      <input type="hidden" name="redirect" value="<?= e($_SERVER['REQUEST_URI'] ?? 'shop.php') ?>">
                      <button type="submit" class="w-9 h-9 rounded-lg border flex items-center justify-center transition-colors shrink-0 <?= $wished ? 'bg-red-50 border-red-200 text-red-500' : 'bg-white border-gray-200 text-gray-500 hover:text-red-500 hover:border-red-200' ?>" title="<?= $wished ? 'Remove from Wishlist' : 'Add to Wishlist' ?>" aria-label="Wishlist">
                        <i data-lucide="heart" class="w-4 h-4 <?= $wished ? 'fill-red-500' : '' ?>"></i>
                      </button>
                    </form>
                    <?php if ($inStock): ?>
                      <a href="<?= e(url('checkout.php?product='.(int)$p['id'].'&qty=1')) ?>" class="pk-btn pk-btn-orange flex-1 text-[10px] sm:text-[11px] py-2 px-2 leading-none whitespace-nowrap">Buy Now</a>
                    <?php else: ?>
                      <span class="pk-btn flex-1 text-xs py-2.5 bg-gray-100 text-gray-400 cursor-not-allowed">Out of Stock</span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="bg-white border border-gray-200 rounded-2xl p-10 text-center text-gray-500">
            <div class="text-lg font-bold text-gray-800">No products found</div>
            <p class="text-sm mt-1">Try changing filters or search term.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
