<?php
/** Public Contact Us page. */

require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Contact Us - ' . setting('store_name', 'PakStore');
require __DIR__ . '/includes/header.php';
?>

<main class="flex-1 bg-slate-50">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 py-12">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-10 text-center">
            <div class="w-14 h-14 rounded-2xl bg-emerald-100 text-[#00A651] flex items-center justify-center mx-auto mb-4">
                <i data-lucide="mail" class="w-7 h-7"></i>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Contact Us</h1>
            <p class="text-sm text-slate-500 mt-2 leading-relaxed">
                Have a question about your order or our products? Get in touch with us and
                we will be happy to help.
            </p>

            <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-md mx-auto">
                <a href="mailto:pakstore63@gmail.com" class="flex items-center justify-center gap-2 w-full py-3.5 bg-[#00A651] hover:bg-[#008f45] text-white font-bold text-sm rounded-xl shadow-sm transition-colors">
                    <i data-lucide="mail" class="w-5 h-5"></i>
                    Email Us
                </a>
                <a href="<?= e(url('track-order.php')) ?>" class="flex items-center justify-center gap-2 w-full py-3.5 bg-white hover:bg-emerald-50 text-slate-800 font-bold text-sm rounded-xl border border-slate-200 transition-colors">
                    <i data-lucide="package-check" class="w-5 h-5"></i>
                    Track Order
                </a>
            </div>

            <p class="text-xs text-slate-400 mt-8">Email: pakstore63@gmail.com</p>
        </div>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
