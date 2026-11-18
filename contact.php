<?php
/** Public Contact Us page. */

require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Contact Us - ' . setting('store_name', 'PakStore');
require __DIR__ . '/includes/header.php';
?>

<main class="flex-1 bg-slate-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-10">
        <div class="text-center mb-8">
            <div class="w-14 h-14 rounded-2xl bg-emerald-100 text-[#00A651] flex items-center justify-center mx-auto mb-4">
                <i data-lucide="mail" class="w-7 h-7"></i>
            </div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight">Contact Us</h1>
            <p class="text-sm text-slate-500 mt-2">We are here to help — feel free to reach out.</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm">
            <div class="space-y-4">
                <a href="mailto:pakstore63@gmail.com" class="flex items-center gap-4 p-4 rounded-xl border border-slate-200 hover:border-[#00A651] hover:bg-emerald-50 transition-colors">
                    <span class="w-10 h-10 rounded-full bg-emerald-100 text-[#00A651] flex items-center justify-center shrink-0">
                        <i data-lucide="mail" class="w-5 h-5"></i>
                    </span>
                    <span>
                        <span class="block text-[11px] text-slate-500 font-semibold uppercase tracking-wide">Email</span>
                        <span class="block text-sm font-bold text-slate-900">pakstore63@gmail.com</span>
                    </span>
                </a>

                <?php if (setting('phone', '') !== ''): ?>
                <a href="tel:<?= e(setting('phone', '')) ?>" class="flex items-center gap-4 p-4 rounded-xl border border-slate-200 hover:border-[#00A651] hover:bg-emerald-50 transition-colors">
                    <span class="w-10 h-10 rounded-full bg-emerald-100 text-[#00A651] flex items-center justify-center shrink-0">
                        <i data-lucide="phone" class="w-5 h-5"></i>
                    </span>
                    <span>
                        <span class="block text-[11px] text-slate-500 font-semibold uppercase tracking-wide">Phone</span>
                        <span class="block text-sm font-bold text-slate-900"><?= e(setting('phone', '')) ?></span>
                    </span>
                </a>
                <?php endif; ?>

                <a href="<?= e(url('track-order.php')) ?>" class="flex items-center gap-4 p-4 rounded-xl border border-slate-200 hover:border-[#00A651] hover:bg-emerald-50 transition-colors">
                    <span class="w-10 h-10 rounded-full bg-emerald-100 text-[#00A651] flex items-center justify-center shrink-0">
                        <i data-lucide="package-check" class="w-5 h-5"></i>
                    </span>
                    <span>
                        <span class="block text-[11px] text-slate-500 font-semibold uppercase tracking-wide">Orders</span>
                        <span class="block text-sm font-bold text-slate-900">Track your order</span>
                    </span>
                </a>
            </div>
        </div>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
