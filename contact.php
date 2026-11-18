<?php
/** Public Contact Us page - simple two-button layout. */

require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Contact Us - ' . setting('store_name', 'PakStore');
require __DIR__ . '/includes/header.php';
?>

<main class="flex-1 bg-slate-50">
    <div class="max-w-md mx-auto px-4 sm:px-6 py-14">
        <div class="grid grid-cols-1 gap-4">
            <a href="mailto:pakstore63@gmail.com" class="flex items-center justify-center gap-2 w-full py-4 bg-[#00A651] hover:bg-[#008f45] text-white font-bold text-sm rounded-xl shadow-sm transition-colors">
                <i data-lucide="mail" class="w-5 h-5"></i>
                Email Us
            </a>
            <a href="<?= e(url('track-order.php')) ?>" class="flex items-center justify-center gap-2 w-full py-4 bg-white hover:bg-emerald-50 text-slate-800 font-bold text-sm rounded-xl border border-slate-200 transition-colors">
                <i data-lucide="package-check" class="w-5 h-5"></i>
                Track Order
            </a>
        </div>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
