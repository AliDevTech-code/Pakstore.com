<?php
/** Storefront footer + Daraz-style mobile bottom navigation. */
$footerCategories = all_categories();
$footerCustomer   = current_customer();
$footerCount      = cart_count();
?>
<footer class="bg-slate-900 text-slate-300 border-t border-slate-800 pt-12 pb-8 print-hide mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 pb-12 border-b border-slate-800">
            <div class="space-y-3">
                <div class="flex items-center gap-2">
                    <span class="bg-emerald-600 text-white font-black text-xs px-2 py-0.5 rounded">PAK</span>
                    <span class="text-xl font-bold tracking-tight text-white"><?= e(setting('store_name', 'Pakstore')) ?></span>
                </div>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Pakistan&rsquo;s premier multi-category digital store. Offering 100% Cash on Delivery across Karachi, Lahore, Islamabad, Rawalpindi, Faisalabad, and all cities nationwide.
                </p>
                <div class="pt-2 text-xs text-emerald-400 font-semibold flex items-center gap-1.5">
                    <i data-lucide="shield-check" class="w-4 h-4"></i> 100% Verified Cash on Delivery
                </div>
            </div>

            <div class="space-y-3 text-xs">
                <h4 class="font-bold text-white uppercase tracking-wider text-[11px]">Categories</h4>
                <ul class="space-y-2 text-slate-400">
                    <?php if ($footerCategories): ?>
                        <?php foreach (array_slice($footerCategories, 0, 6) as $cat): ?>
                            <li>
                                <a href="<?= e(url('index.php?category=' . urlencode($cat['slug']) . '#catalog-section')) ?>" class="hover:text-white transition-colors">
                                    <?= e($cat['name']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li><a href="<?= e(url('index.php#catalog-section')) ?>" class="hover:text-white transition-colors">Browse the catalog</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="space-y-3 text-xs">
                <h4 class="font-bold text-white uppercase tracking-wider text-[11px]">Customer Support</h4>
                <ul class="space-y-2 text-slate-400">
                    <li><a href="<?= e(url('track-order.php')) ?>" class="hover:text-white transition-colors">Track Existing Order</a></li>
                    <li><a href="<?= e(url('account.php')) ?>" class="hover:text-white transition-colors">My Account &amp; Orders</a></li>
                    <li><span>Delivery via TCS &amp; Leopards (2-4 Days)</span></li>
                    <li><span>7 Days Return / Exchange Guarantee</span></li>
                    <li><span>100% Cash on Delivery</span></li>
                </ul>
            </div>

            <div class="space-y-3 text-xs">
                <h4 class="font-bold text-white uppercase tracking-wider text-[11px]">Direct Contact</h4>
                <div class="space-y-2 text-slate-400">
                    <a href="tel:<?= e(setting('phone')) ?>" class="flex items-center gap-2 hover:text-white transition-colors">
                        <i data-lucide="phone-call" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                        <span>Helpline: <?= e(setting('phone')) ?></span>
                    </a>
                    <div class="flex items-center gap-2">
                        <i data-lucide="mail" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                        <span>Email: <?= e(setting('email')) ?></span>
                    </div>
                    <?php if (setting('whatsapp') !== ''): ?>
                        <a href="https://wa.me/<?= e(clean_phone_wa(setting('whatsapp'))) ?>" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 hover:text-white transition-colors">
                            <i data-lucide="message-circle" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                            <span>WhatsApp Support</span>
                        </a>
                    <?php endif; ?>
                    <div class="flex items-center gap-2">
                        <i data-lucide="truck" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                        <span>Shipments: Dispatched Daily</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
            <p>&copy; <?= date('Y') ?> <?= e(setting('store_name', 'Pakstore')) ?> Pakistan. All rights reserved.</p>
            <div class="flex items-center gap-4 text-[11px] flex-wrap justify-center">
                <span>100% Cash on Delivery Nationwide</span>
                <span aria-hidden="true">&middot;</span>
                <span>Karachi &middot; Lahore &middot; Islamabad</span>
                <span aria-hidden="true">&middot;</span>
                <a href="<?= e(url('admin/login.php')) ?>" class="text-slate-500 hover:text-slate-300 transition-colors flex items-center gap-1" title="Staff Authorization Portal">
                    <span>&#128274; Staff Portal</span>
                </a>
            </div>
        </div>
    </div>
</footer>

<!-- Mobile bottom nav -->
<nav class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200 px-2 py-1.5 shadow-lg print-hide" aria-label="Mobile navigation">
    <div class="grid grid-cols-5 gap-1">
        <a href="<?= e(url('index.php')) ?>" class="flex flex-col items-center justify-center py-1 text-slate-600 hover:text-[#00A651] transition-colors">
            <i data-lucide="home" class="w-5 h-5"></i>
            <span class="text-[10px] font-semibold mt-0.5">Home</span>
        </a>
        <a href="<?= e(url('index.php#catalog-section')) ?>" class="flex flex-col items-center justify-center py-1 text-slate-600 hover:text-[#00A651] transition-colors">
            <i data-lucide="layout-grid" class="w-5 h-5"></i>
            <span class="text-[10px] font-semibold mt-0.5">Categories</span>
        </a>
        <a href="<?= e(url('track-order.php')) ?>" class="flex flex-col items-center justify-center py-1 text-slate-600 hover:text-[#00A651] transition-colors" title="Track Order Status">
            <i data-lucide="package-check" class="w-5 h-5"></i>
            <span class="text-[10px] font-semibold mt-0.5">Track</span>
        </a>
        <button type="button" data-open-drawer="cart-drawer" class="relative flex flex-col items-center justify-center py-1 text-slate-600 hover:text-[#00A651] transition-colors">
            <span class="relative">
                <i data-lucide="shopping-bag" class="w-5 h-5 text-[#00A651]"></i>
                <?php if ($footerCount > 0): ?>
                    <span class="absolute -top-1.5 -right-2 bg-[#FF6B00] text-white font-black text-[9px] w-4 h-4 rounded-full flex items-center justify-center"><?= $footerCount ?></span>
                <?php endif; ?>
            </span>
            <span class="text-[10px] font-semibold mt-0.5 text-[#00A651]">Cart</span>
        </button>
        <?php if ($footerCustomer): ?>
            <a href="<?= e(url('account.php')) ?>" class="flex flex-col items-center justify-center py-1 text-slate-600 hover:text-[#00A651] transition-colors">
                <i data-lucide="user" class="w-5 h-5"></i>
                <span class="text-[10px] font-semibold mt-0.5 truncate max-w-[50px]"><?= e(explode(' ', $footerCustomer['full_name'])[0]) ?></span>
            </a>
        <?php else: ?>
            <a href="<?= e(url('account.php')) ?>" class="flex flex-col items-center justify-center py-1 text-slate-600 hover:text-[#00A651] transition-colors">
                <i data-lucide="user" class="w-5 h-5"></i>
                <span class="text-[10px] font-semibold mt-0.5 truncate max-w-[50px]">Account</span>
            </a>
        <?php endif; ?>
    </div>
</nav>

<script src="<?= e(url('assets/js/main.js') . '?v=2') ?>"></script>
</body>
</html>
