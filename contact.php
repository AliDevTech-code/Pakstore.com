<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Contact Us - ' . setting('store_name', 'PakStore');
$metaDescription = 'Contact ' . setting('store_name', 'PakStore') . ' for order support, delivery questions, or product inquiries.';
require __DIR__ . '/includes/header.php';
$addr = setting('address', 'Pakistan');
$email = setting('email', 'pakstore63@gmail.com');
$phone = setting('phone', '');
?>
<main class="flex-1 bg-[#f3f4f6]">
  <section class="bg-[#166534] text-white py-12"><div class="max-w-4xl mx-auto px-4 text-center"><h1 class="text-3xl sm:text-4xl font-black" style="color:#fff">Contact Us</h1><p class="mt-2 text-green-100">Hum aapki madad ke liye yahan hain</p></div></section>
  <div class="max-w-5xl mx-auto px-4 sm:px-6 py-10 grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="bg-white rounded-2xl border border-gray-200 p-6 sm:p-8 shadow-sm">
      <h2 class="text-xl font-extrabold text-[#166534] mb-4">Get in Touch</h2>
      <div class="space-y-4 text-sm text-gray-700">
        <div class="flex gap-3"><div class="w-10 h-10 rounded-lg bg-green-50 text-[#166534] flex items-center justify-center shrink-0"><i data-lucide="map-pin" class="w-5 h-5"></i></div><div><div class="font-bold text-gray-900">Address</div><div><?= e($addr) ?></div></div></div>
        <div class="flex gap-3"><div class="w-10 h-10 rounded-lg bg-green-50 text-[#166534] flex items-center justify-center shrink-0"><i data-lucide="mail" class="w-5 h-5"></i></div><div><div class="font-bold text-gray-900">Email</div><a href="mailto:<?= e($email) ?>" class="text-[#166534] font-semibold"><?= e($email) ?></a></div></div>
        <?php if ($phone !== ''): ?><div class="flex gap-3"><div class="w-10 h-10 rounded-lg bg-green-50 text-[#166534] flex items-center justify-center shrink-0"><i data-lucide="phone" class="w-5 h-5"></i></div><div><div class="font-bold text-gray-900">Phone</div><div><?= e($phone) ?></div></div></div><?php endif; ?>
      </div>
      <div class="mt-6 flex flex-wrap gap-3">
        <a href="mailto:<?= e($email) ?>" class="pk-btn pk-btn-green">Email Us</a>
        <a href="<?= e(url('track-order.php')) ?>" class="pk-btn pk-btn-outline">Track Order</a>
      </div>
    </div>
    <div class="bg-white rounded-2xl border border-gray-200 p-6 sm:p-8 shadow-sm">
      <h2 class="text-xl font-extrabold text-[#166534] mb-4">Business Hours & Support</h2>
      <p class="text-sm text-gray-600 leading-7">Order ya product ke baray mein koi sawal ho? Humari team aapki madad karegi. Cash on Delivery orders, delivery status, returns aur product inquiries ke liye rabta karein.</p>
      <div class="mt-5 p-4 rounded-xl bg-green-50 border border-green-100">
        <div class="font-bold text-[#166534]">Cash on Delivery Support</div>
        <div class="text-sm text-gray-600 mt-1">Order ID ya mobile number ke sath rabta karein taake hum aapki jaldi madad kar saken.</div>
      </div>
    </div>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>