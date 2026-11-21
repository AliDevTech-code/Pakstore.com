<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Privacy Policy - ' . setting('store_name', 'PakStore');
$metaDescription = 'Read the privacy policy of ' . setting('store_name', 'PakStore') . '.';
require __DIR__ . '/includes/header.php';
?>
<main class="flex-1 bg-[#f3f4f6] py-10">
  <div class="max-w-4xl mx-auto px-4">
    <div class="bg-white border border-gray-200 rounded-2xl p-6 sm:p-10 shadow-sm">
      <h1 class="pk-title">Privacy Policy</h1>
      <p class="pk-sub mt-2">Aapki privacy hamare liye important hai.</p>
      <div class="mt-8 space-y-6 text-sm leading-7 text-gray-700">
        <section><h2 class="text-lg font-bold text-[#166534]">1. Information We Collect</h2><p>Jab aap order place karte hain, hum aapka naam, phone number, address, city aur order details collect karte hain taake aapka order deliver kiya ja sake.</p></section>
        <section><h2 class="text-lg font-bold text-[#166534]">2. How We Use Information</h2><p>Aapki information sirf order processing, delivery, customer support aur order updates ke liye use hoti hai. Hum aapki personal information ko be-wajah share nahi karte.</p></section>
        <section><h2 class="text-lg font-bold text-[#166534]">3. Data Security</h2><p>Hum aapki information ko secure rakhne ki koshish karte hain. Payment Cash on Delivery hoti hai, is liye hum aapki card details store nahi karte.</p></section>
        <section><h2 class="text-lg font-bold text-[#166534]">4. Contact</h2><p>Privacy related sawalat ke liye <a href="<?= e(url('contact.php')) ?>" class="text-[#166534] font-bold">Contact Us</a> page par rabta karein.</p></section>
      </div>
    </div>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
