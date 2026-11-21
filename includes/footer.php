<?php
/** Storefront footer. */
$footerCategories = all_categories();
$storeName = setting('store_name', 'PakStore');
$footerEmail = setting('email', 'pakstore63@gmail.com');
$footerPhone = setting('phone', '');
$footerAddress = setting('address', 'Pakistan');
$footerFacebook = setting('facebook_url', '#');
$footerInstagram = setting('instagram_url', '#');
$footerTiktok = setting('tiktok_url', '#');
?>
<footer class="pk-footer print-hide mt-auto">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
      <div>
        <div class="pk-brand text-3xl mb-3"><span class="pk" style="color:#fff">Pak</span><span class="store">Store</span></div>
        <p class="text-sm leading-relaxed" style="color:#bbf7d0">Pakistan ka trusted online store. Quality products, best prices, aur Cash on Delivery all over Pakistan.</p>
        <div class="mt-4 space-y-2 text-sm" style="color:#dcfce7">
          <div class="flex gap-2"><i data-lucide="map-pin" class="w-4 h-4 mt-0.5 shrink-0" style="color:#f97316"></i><span><strong style="color:#fff">Address:</strong> <?= e($footerAddress) ?></span></div>
          <?php if ($footerPhone !== ''): ?><div class="flex gap-2"><i data-lucide="phone" class="w-4 h-4 mt-0.5 shrink-0" style="color:#f97316"></i><span><strong style="color:#fff">Phone:</strong> <?= e($footerPhone) ?></span></div><?php endif; ?>
          <div class="flex gap-2"><i data-lucide="mail" class="w-4 h-4 mt-0.5 shrink-0" style="color:#f97316"></i><span><strong style="color:#fff">Email:</strong> <?= e($footerEmail) ?></span></div>
        </div>
      </div>

      <div>
        <h4>Quick Links</h4>
        <ul class="space-y-2 text-sm">
          <li><a href="<?= e(url('index.php')) ?>">Home</a></li>
          <li><a href="<?= e(url('shop.php')) ?>">Shop</a></li>
          <li><a href="<?= e(url('categories.php')) ?>">Categories</a></li>
          <li><a href="<?= e(url('about.php')) ?>">About Us</a></li>
          <li><a href="<?= e(url('contact.php')) ?>">Contact Us</a></li>
          <li><a href="<?= e(url('blog.php')) ?>">Blog</a></li>
          <li><a href="<?= e(url('faqs.php')) ?>">FAQs</a></li>
        </ul>
      </div>

      <div>
        <h4>Customer Care</h4>
        <ul class="space-y-2 text-sm">
          <li><a href="<?= e(url('track-order.php')) ?>">Track Order</a></li>
          <li><a href="<?= e(url('account.php')) ?>">My Account</a></li>
          <li><a href="<?= e(url('shipping-policy.php')) ?>">Shipping / COD Policy</a></li>
          <li><a href="<?= e(url('return-policy.php')) ?>">Return / Exchange Policy</a></li>
          <li><a href="<?= e(url('privacy-policy.php')) ?>">Privacy Policy</a></li>
        </ul>
      </div>

      <div>
        <h4>Follow Us</h4>
        <div class="flex flex-wrap gap-2 mb-4">
          <a href="<?= e($footerFacebook) ?>" class="social" target="_blank" rel="noopener"><i data-lucide="facebook" class="w-4 h-4"></i>Facebook</a>
          <a href="<?= e($footerInstagram) ?>" class="social" target="_blank" rel="noopener"><i data-lucide="instagram" class="w-4 h-4"></i>Instagram</a>
          <a href="<?= e($footerTiktok) ?>" class="social" target="_blank" rel="noopener"><i data-lucide="music-2" class="w-4 h-4"></i>TikTok</a>
        </div>
        <div class="p-4 rounded-xl" style="background:rgba(255,255,255,.08)">
          <div class="text-sm font-bold text-white mb-1">100% Cash on Delivery</div>
          <div class="text-xs" style="color:#bbf7d0">TCS / Leopards ke zariye safe delivery all over Pakistan.</div>
        </div>
      </div>
    </div>

    <div class="mt-10 pt-6 border-t flex flex-col sm:flex-row items-center justify-between gap-3 text-xs" style="border-color:rgba(255,255,255,.12);color:#bbf7d0">
      <p>&copy; <?= date('Y') ?> <?= e($storeName) ?>. All rights reserved.</p>
      <p>COD Nationwide | 7-Day Return | Secure Shopping</p>
    </div>
  </div>
</footer>

<script src="<?= e(url('assets/js/main.js') . '?v=20') ?>"></script>
</body>
</html>