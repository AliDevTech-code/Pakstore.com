<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Shipping / COD Policy - ' . setting('store_name', 'PakStore');
require __DIR__ . '/includes/header.php';
?>
<main class="flex-1 bg-[#f3f4f6] py-10">
  <div class="max-w-4xl mx-auto px-4">
    <div class="bg-white border border-gray-200 rounded-2xl p-6 sm:p-10 shadow-sm">
      <h1 class="pk-title">Shipping / COD Policy</h1>
      <p class="pk-sub mt-2">Nationwide delivery with Cash on Delivery.</p>
      <div class="mt-8 space-y-6 text-sm leading-7 text-gray-700">
        <section><h2 class="text-lg font-bold text-[#166534]">Cash on Delivery</h2><p>Hum Pakistan bhar mein Cash on Delivery offer karte hain. Aap parcel receive karne par payment kar sakte hain.</p></section>
        <section><h2 class="text-lg font-bold text-[#166534]">Delivery Time</h2><p>Usually delivery 2-5 working days mein ho jati hai, city aur courier service ke mutabiq. Dispatch TCS/Leopards ya available courier partners ke through hota hai.</p></section>
        <section><h2 class="text-lg font-bold text-[#166534]">Delivery Charges</h2><p>Delivery charges checkout par clearly show kiye jate hain. Agar free delivery offer available ho to wo product/page par mention hogi.</p></section>
      </div>
    </div>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
