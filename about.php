<?php
/** Public About Us page. */

require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'About Us - ' . setting('store_name', 'PakStore');
require __DIR__ . '/includes/header.php';
?>

<main class="flex-1 bg-slate-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-10">
        <div class="text-center mb-8">
            <div class="w-14 h-14 rounded-2xl bg-emerald-100 text-[#00A651] flex items-center justify-center mx-auto mb-4">
                <i data-lucide="info" class="w-7 h-7"></i>
            </div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight">About <?= e(setting('store_name', 'PakStore')) ?></h1>
            <p class="text-sm text-slate-500 mt-2">Your trusted online store in Pakistan.</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 space-y-5 text-sm text-slate-700 leading-relaxed shadow-sm">
            <p>
                <?= e(setting('store_name', 'PakStore')) ?> is an online shopping store serving customers all over Pakistan.
                We offer a wide range of products at competitive prices with the convenience of <strong>Cash on Delivery</strong>.
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                <div class="bg-emerald-50 rounded-xl p-4 text-center">
                    <i data-lucide="truck" class="w-5 h-5 text-[#00A651] mx-auto mb-2"></i>
                    <p class="font-bold text-slate-900 text-xs">Free Delivery</p>
                    <p class="text-[11px] text-slate-500 mt-1">All over Pakistan</p>
                </div>
                <div class="bg-emerald-50 rounded-xl p-4 text-center">
                    <i data-lucide="wallet" class="w-5 h-5 text-[#00A651] mx-auto mb-2"></i>
                    <p class="font-bold text-slate-900 text-xs">Cash on Delivery</p>
                    <p class="text-[11px] text-slate-500 mt-1">Pay at your doorstep</p>
                </div>
                <div class="bg-emerald-50 rounded-xl p-4 text-center">
                    <i data-lucide="shield-check" class="w-5 h-5 text-[#00A651] mx-auto mb-2"></i>
                    <p class="font-bold text-slate-900 text-xs">Trusted Service</p>
                    <p class="text-[11px] text-slate-500 mt-1">Here to help you</p>
                </div>
            </div>
            <div class="pt-4 border-t border-slate-100 text-center">
                <a href="<?= e(url('index.php#catalog-section')) ?>" class="inline-block bg-[#00A651] hover:bg-[#008f45] text-white font-bold text-xs px-5 py-2.5 rounded-lg">Shop Now</a>
            </div>
        </div>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
