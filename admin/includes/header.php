<?php
/** Admin layout header. Expects $adminUser and $activeTab. */
$adminStoreName = setting('store_name', 'PakStore');
$adminTabs = admin_tabs();
$pendingOrders = admin_pending_orders();
$pageTitle = $pageTitle ?? ('Admin - ' . $adminStoreName);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Outfit:wght@500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { brand: '#00A651', accent: '#FF6B00' } } } };
    </script>
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
    <script src="https://unpkg.com/lucide@0.546.0/dist/umd/lucide.min.js"></script>
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">

<header class="bg-slate-900 text-white sticky top-0 z-40 border-b border-slate-800 shadow-md print-hide">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 gap-4">
            <div class="flex items-center gap-3 shrink-0">
                <a href="<?= e(url('index.php')) ?>" class="p-1.5 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-colors flex items-center gap-1.5 text-xs font-semibold" title="Return to storefront">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">Storefront</span>
                </a>
                <div class="h-5 w-px bg-slate-800" aria-hidden="true"></div>
                <a href="<?= e(url('admin/index.php')) ?>" class="flex items-center gap-1.5">
                    <span class="bg-[#00A651] text-white font-black text-xs px-2 py-0.5 rounded">ADMIN</span>
                    <span class="font-bold text-sm sm:text-base tracking-tight text-white"><?= e($adminStoreName) ?> Control</span>
                </a>
            </div>

            <nav class="hidden lg:flex items-center gap-1 overflow-x-auto py-2" aria-label="Admin sections">
                <?php foreach ($adminTabs as $key => $tab): ?>
                    <a href="<?= e(url('admin/index.php?tab=' . $key)) ?>"
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition-colors whitespace-nowrap shrink-0 <?= $activeTab === $key ? 'bg-[#00A651] text-white shadow-sm' : 'text-slate-300 hover:text-white hover:bg-slate-800' ?>">
                        <i data-lucide="<?= e($tab['icon']) ?>" class="w-3.5 h-3.5"></i>
                        <span><?= e($tab['label']) ?></span>
                        <?php if ($key === 'orders' && $pendingOrders > 0): ?>
                            <span class="bg-[#FF6B00] text-white text-[10px] font-black px-1.5 rounded-full"><?= $pendingOrders ?></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="flex items-center gap-2 shrink-0">
                <div class="hidden sm:block text-right">
                    <span class="block text-[11px] font-bold text-white leading-tight"><?= e($adminUser['email']) ?></span>
                    <span class="block text-[10px] text-slate-400"><?= e($adminUser['role']) ?></span>
                </div>
                <form action="<?= e(url('admin/logout.php')) ?>" method="post">
                    <?= csrf_field() ?>
                    <button type="submit" class="p-2 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-colors" title="Sign out">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>
        </div>

        <!-- Mobile tabs -->
        <nav class="lg:hidden flex items-center gap-1 overflow-x-auto py-2 scrollbar-none" aria-label="Admin sections">
            <?php foreach ($adminTabs as $key => $tab): ?>
                <a href="<?= e(url('admin/index.php?tab=' . $key)) ?>"
                   class="px-2.5 py-1.5 rounded-lg text-[11px] font-semibold flex items-center gap-1 whitespace-nowrap shrink-0 <?= $activeTab === $key ? 'bg-[#00A651] text-white' : 'text-slate-300 hover:text-white hover:bg-slate-800' ?>">
                    <i data-lucide="<?= e($tab['icon']) ?>" class="w-3 h-3"></i>
                    <span><?= e($tab['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>
</header>

<?php render_flashes(); ?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 pb-16">
