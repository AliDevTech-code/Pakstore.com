<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'FAQs - ' . setting('store_name', 'PakStore');
require __DIR__ . '/includes/header.php';
$faqs = [
  ['q'=>'Cash on Delivery available hai?','a'=>'Ji haan, hum poore Pakistan mein Cash on Delivery offer karte hain.'],
  ['q'=>'Delivery kitne din mein hoti hai?','a'=>'Aam tor par 2-5 working days mein delivery ho jati hai, location aur courier par depend karta hai.'],
  ['q'=>'Kya main order return kar sakta hoon?','a'=>'Ji haan, eligible products 7 din ke andar return/exchange ho sakte hain. Hamari Return/Exchange Policy dekhein.'],
  ['q'=>'Order track kaise karun?','a'=>'Track Order page par apna order code ya phone number enter kar ke order status dekh sakte hain.'],
  ['q'=>'Delivery charges kitne hain?','a'=>'Delivery charges checkout par clear show hotay hain. Free delivery offers product/page par mention hoti hain.'],
];
?>
<main class="flex-1 bg-[#f3f4f6] py-10">
  <div class="max-w-4xl mx-auto px-4">
    <div class="text-center mb-8"><h1 class="pk-title">Frequently Asked Questions</h1><p class="pk-sub">Aapke aam sawalat ke jawab</p></div>
    <div class="space-y-4">
      <?php foreach($faqs as $f): ?>
        <details class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
          <summary class="font-bold text-gray-900 cursor-pointer"><?= e($f['q']) ?></summary>
          <p class="mt-3 text-sm text-gray-600 leading-7"><?= e($f['a']) ?></p>
        </details>
      <?php endforeach; ?>
    </div>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
