<?php
require_once __DIR__ . '/includes/functions.php';
$categories = all_categories();
$pageTitle = 'Categories - ' . setting('store_name', 'PakStore');
$metaDescription = 'Browse all product categories at ' . setting('store_name', 'PakStore') . '. Cash on Delivery available across Pakistan.';
require __DIR__ . '/includes/header.php';
if (!function_exists('pk_cat_icon')) { function pk_cat_icon(string $name, string $slug=''): string { $k=strtolower($slug.' '.$name); $map=['electronic'=>'smartphone','mobile'=>'smartphone','phone'=>'smartphone','watch'=>'watch','smart'=>'watch','fashion'=>'shirt','cloth'=>'shirt','wear'=>'shirt','shoe'=>'footprints','bag'=>'shopping-bag','beaut'=>'sparkles','cosmetic'=>'sparkles','skin'=>'sparkles','health'=>'heart-pulse','perfume'=>'spray-can','fragrance'=>'spray-can','home'=>'house','kitchen'=>'cooking-pot','decor'=>'lamp','furniture'=>'sofa','toy'=>'gamepad-2','kid'=>'baby','baby'=>'baby','sport'=>'dumbbell','fitness'=>'dumbbell','book'=>'book-open','station'=>'pencil-ruler','grocery'=>'shopping-basket','food'=>'utensils','computer'=>'laptop','laptop'=>'laptop','accessor'=>'headphones','audio'=>'headphones','earbud'=>'headphones','jewel'=>'gem','gift'=>'gift','auto'=>'car','car'=>'car']; foreach($map as $n=>$i){ if(str_contains($k,$n)) return $i; } return 'layout-grid'; } }

// Count products per category
$counts = [];
try {
  $rows = db()->query("SELECT category_id, COUNT(*) c FROM products GROUP BY category_id")->fetchAll();
  foreach ($rows as $r) $counts[(int)$r['category_id']] = (int)$r['c'];
} catch (Throwable $e) {}
?>
<main class="flex-1 bg-[#f3f4f6]">
  <section class="bg-[#166534] text-white py-10 sm:py-14">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
      <p class="text-sm font-semibold text-green-200">Browse by Category</p>
      <h1 class="text-3xl sm:text-4xl font-black mt-1" style="color:#fff">Product Categories</h1>
      <p class="text-green-100 mt-2 text-sm">Apni pasand ki category choose karein — Cash on Delivery available.</p>
    </div>
  </section>

  <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <?php if ($categories): ?>
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5">
        <?php foreach ($categories as $cat): 
          $img = trim((string)($cat['image'] ?? ''));
          $count = $counts[(int)$cat['id']] ?? 0;
        ?>
          <a href="<?= e(url('shop.php?category=' . urlencode($cat['slug']))) ?>" class="pk-card p-5 text-center hover:border-[#166534] transition-all group">
            <div class="w-24 h-24 mx-auto rounded-full bg-green-50 border-2 border-green-100 overflow-hidden flex items-center justify-center group-hover:border-[#166534] transition-all">
              <?php if ($img !== ''): ?>
                <img src="<?= e(image_url($img)) ?>" alt="<?= e($cat['name']) ?>" class="w-full h-full object-cover" loading="lazy">
              <?php else: ?>
                <i data-lucide="<?= e(pk_cat_icon($cat['name'], $cat['slug'] ?? '')) ?>" class="w-10 h-10 text-[#166534]"></i>
              <?php endif; ?>
            </div>
            <h2 class="mt-4 font-extrabold text-gray-900 group-hover:text-[#166534]"><?= e($cat['name']) ?></h2>
            <p class="text-xs text-gray-500 mt-1"><?= $count ?> product<?= $count===1?'':'s' ?></p>
          </a>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="bg-white border border-gray-200 rounded-2xl p-10 text-center text-gray-500">No categories found.</div>
    <?php endif; ?>
  </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
