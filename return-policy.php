<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Return / Exchange Policy - ' . setting('store_name', 'PakStore');
require __DIR__ . '/includes/header.php';
?>
<main class="flex-1 bg-[#f3f4f6] py-10">
  <div class="max-w-4xl mx-auto px-4">
    <div class="bg-white border border-gray-200 rounded-2xl p-6 sm:p-10 shadow-sm">
      <h1 class="pk-title">Return / Exchange Policy</h1>
      <p class="pk-sub mt-2">7-Day easy return/exchange for eligible products.</p>
      <div class="mt-8 space-y-6 text-sm leading-7 text-gray-700">
        <section><h2 class="text-lg font-bold text-[#166534]">7-Day Return Window</h2><p>Delivery ke 7 din ke andar aap eligible product return ya exchange kar sakte hain, agar product damaged, defective, ya wrong item ho.</p></section>
        <section><h2 class="text-lg font-bold text-[#166534]">Conditions</h2><p>Product unused, original condition aur original packaging mein hona chahiye. Kuch items hygiene/usage reasons ki wajah se returnable nahi ho sakte.</p></section>
        <section><h2 class="text-lg font-bold text-[#166534]">How to Request</h2><p>Apna order code aur issue details ke sath <a href="<?= e(url('contact.php')) ?>" class="text-[#166534] font-bold">Contact Us</a> par rabta karein. Hamari team aapko guide karegi.</p></section>
      </div>
    </div>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
