<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'About Us - ' . setting('store_name', 'PakStore');
$metaDescription = 'Learn about ' . setting('store_name', 'PakStore') . ' - your trusted online store in Pakistan with Cash on Delivery.';
require __DIR__ . '/includes/header.php';
?>
<main class="flex-1 bg-[#f3f4f6]">
  <section class="bg-[#166534] text-white py-12"><div class="max-w-4xl mx-auto px-4 text-center"><h1 class="text-3xl sm:text-4xl font-black" style="color:#fff">About <?= e(setting('store_name', 'PakStore')) ?></h1><p class="mt-2 text-green-100">Pakistan ka trusted online shopping store</p></div></section>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 py-10">
    <div class="bg-white rounded-2xl border border-gray-200 p-6 sm:p-10 shadow-sm space-y-6 text-gray-700 leading-8">
      <p><?= e(setting('store_name', 'PakStore')) ?> Pakistan mein customers ko quality products, best prices aur <strong class="text-[#166534]">Cash on Delivery</strong> ki sahulat provide karta hai. Hamara mission hai online shopping ko easy, safe aur reliable banana.</p>
      <p>Hum har order ko carefully process karte hain aur TCS/Leopards jaise trusted courier partners ke through nationwide delivery karte hain.</p>
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
        <div class="pk-trust"><div class="icon"><i data-lucide="truck" class="w-6 h-6"></i></div><h3>Nationwide Delivery</h3><p>All over Pakistan</p></div>
        <div class="pk-trust"><div class="icon"><i data-lucide="banknote" class="w-6 h-6"></i></div><h3>Cash on Delivery</h3><p>Pay at your doorstep</p></div>
        <div class="pk-trust"><div class="icon"><i data-lucide="shield-check" class="w-6 h-6"></i></div><h3>Trusted Service</h3><p>Quality & support</p></div>
      </div>
      <div class="text-center pt-4"><a href="<?= e(url('shop.php')) ?>" class="pk-btn pk-btn-orange">Shop Now</a></div>
    </div>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>