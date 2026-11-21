<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Blog - ' . setting('store_name', 'PakStore');
require __DIR__ . '/includes/header.php';
$posts = [
  ['title'=>'How to Shop Safely Online in Pakistan','date'=>'Nov 2026','excerpt'=>'Cash on Delivery, verified stores, aur product details check karna — online shopping ko safe banata hai.','img'=>'assets/images/hero_pakstore_banner_1793814622604.jpg'],
  ['title'=>'COD Delivery Tips for Fast Receiving','date'=>'Nov 2026','excerpt'=>'Apna address aur phone number sahi likhein, taake courier aapko asani se deliver kar sake.','img'=>'assets/images/product_smartwatch_ultra_1793814621511.jpg'],
  ['title'=>'Why Price + Sale Price Matters','date'=>'Nov 2026','excerpt'=>'Clear pricing customers ko trust deti hai. Original price aur sale price dono dikhana best practice hai.','img'=>'assets/images/product_wireless_earbuds_1793814621939.jpg'],
];
?>
<main class="flex-1 bg-[#f3f4f6] py-10">
  <div class="max-w-7xl mx-auto px-4">
    <div class="text-center mb-8"><h1 class="pk-title">PakStore Blog</h1><p class="pk-sub">Shopping tips, updates aur guides</p></div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <?php foreach($posts as $p): ?>
        <article class="pk-card">
          <img src="<?= e(image_url($p['img'])) ?>" alt="<?= e($p['title']) ?>" class="w-full h-52 object-cover bg-gray-100">
          <div class="p-5">
            <div class="text-xs font-bold text-[#f97316] uppercase"><?= e($p['date']) ?></div>
            <h2 class="text-lg font-extrabold text-[#166534] mt-2 leading-snug"><?= e($p['title']) ?></h2>
            <p class="text-sm text-gray-600 mt-3 leading-7"><?= e($p['excerpt']) ?></p>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
