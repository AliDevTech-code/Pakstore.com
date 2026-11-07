<?php
/** Admin layout header: left sidebar, top bar and page shell. */
$adminStoreName = setting('store_name', 'PakStore');
$adminLogo = setting('logo_url', '');
$adminTabs = admin_tabs();
$pendingOrders = admin_pending_orders();
$pageTitle = $pageTitle ?? ('Admin - ' . $adminStoreName);

$adminNavGroups = [
    'Overview' => ['dashboard', 'analytics'],
    'Store'    => ['orders', 'products', 'categories', 'banners'],
    'People'   => ['users'],
    'System'   => ['settings'],
];

$adminInitial = strtoupper(substr((string) ($adminUser['email'] ?? 'A'), 0, 1));
$activeLabel = $adminTabs[$activeTab]['label'] ?? 'Dashboard';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($pageTitle) ?></title>
    <link rel="preload" href="<?= e(url('assets/fonts/plus-jakarta-sans-latin.woff2')) ?>" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?= e(url('assets/fonts/outfit-latin.woff2')) ?>" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="<?= e(url('assets/css/tailwind.css') . '?v=4') ?>">
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css') . '?v=4') ?>">
    <script src="<?= e(url('assets/js/lucide.min.js')) ?>"></script>
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">

<!-- Mobile overlay -->
<div id="admin-sidebar-overlay" class="fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-sm hidden lg:hidden print-hide" data-admin-close></div>

<!-- Sidebar -->
<aside id="admin-sidebar" class="fixed inset-y-0 left-0 z-50 w-72 -translate-x-full lg:translate-x-0 transition-transform duration-200 bg-slate-950 text-slate-300 flex flex-col print-hide" aria-label="Admin navigation">
    <div class="h-16 px-5 flex items-center justify-between border-b border-slate-800/80 shrink-0">
        <a href="<?= e(url('admin/index.php')) ?>" class="flex items-center gap-2.5 min-w-0">
            <?php if ($adminLogo !== ''): ?>
                <span class="w-9 h-9 rounded-lg bg-white p-1 flex items-center justify-center shrink-0">
                    <img src="<?= e(image_url($adminLogo)) ?>" alt="" class="w-full h-full object-contain">
                </span>
            <?php else: ?>
                <span class="w-9 h-9 rounded-lg bg-gradient-to-br from-[#00A651] to-emerald-700 text-white font-black text-sm flex items-center justify-center shrink-0">P</span>
            <?php endif; ?>
            <span class="min-w-0">
                <span class="block text-sm font-black tracking-tight text-white truncate"><?= e($adminStoreName) ?></span>
                <span class="block text-[10px] uppercase tracking-widest text-slate-500 font-bold">Admin Panel</span>
            </span>
        </a>
        <button type="button" class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800" data-admin-close aria-label="Close menu">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-5 space-y-6 scrollbar-none">
        <?php foreach ($adminNavGroups as $groupLabel => $tabKeys): ?>
            <?php $visibleTabs = array_filter($tabKeys, static fn($key) => isset($adminTabs[$key])); ?>
            <?php if (!$visibleTabs): ?><?php continue; ?><?php endif; ?>
            <div>
                <span class="block px-3 mb-2 text-[10px] font-black uppercase tracking-[0.16em] text-slate-600"><?= e($groupLabel) ?></span>
                <div class="space-y-0.5">
                    <?php foreach ($visibleTabs as $key): ?>
                        <?php $tab = $adminTabs[$key]; ?>
                        <a href="<?= e(url('admin/index.php?tab=' . $key)) ?>"
                           class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold transition-colors <?= $activeTab === $key ? 'bg-[#00A651] text-white shadow-lg shadow-emerald-950/40' : 'text-slate-400 hover:text-white hover:bg-slate-900' ?>">
                            <i data-lucide="<?= e($tab['icon']) ?>" class="w-4 h-4 <?= $activeTab === $key ? 'text-white' : 'text-slate-500 group-hover:text-[#00A651]' ?>"></i>
                            <span class="flex-1"><?= e($tab['label']) ?></span>
                            <?php if ($key === 'orders' && $pendingOrders > 0): ?>
                                <span class="bg-[#FF6B00] text-white text-[10px] font-black px-1.5 py-0.5 rounded-full leading-none"><?= $pendingOrders ?></span>
                            <?php elseif ($key === 'orders'): ?>
                                <span class="text-[10px] font-mono text-slate-600 group-hover:text-slate-400">0</span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="pt-2 border-t border-slate-800/80">
            <a href="<?= e(url('index.php')) ?>" target="_blank" rel="noopener" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold text-slate-400 hover:text-white hover:bg-slate-900 transition-colors">
                <i data-lucide="external-link" class="w-4 h-4 text-slate-500"></i>
                <span class="flex-1">View Storefront</span>
            </a>
            <a href="<?= e(url('admin/product-edit.php')) ?>" class="mt-1 flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold text-emerald-300 bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/20 transition-colors">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span class="flex-1">Add New Product</span>
            </a>
        </div>
    </nav>

    <div class="border-t border-slate-800/80 p-3 shrink-0">
        <div class="flex items-center gap-2.5 rounded-lg bg-slate-900/80 border border-slate-800 p-2.5">
            <span class="w-8 h-8 rounded-full bg-[#00A651] text-white font-black text-xs flex items-center justify-center shrink-0"><?= e($adminInitial) ?></span>
            <span class="min-w-0 flex-1">
                <span class="block text-[11px] font-bold text-white truncate"><?= e($adminUser['email']) ?></span>
                <span class="block text-[10px] text-slate-500"><?= e($adminUser['role']) ?></span>
            </span>
            <form action="<?= e(url('admin/logout.php')) ?>" method="post">
                <?= csrf_field() ?>
                <button type="submit" class="p-1.5 text-slate-500 hover:text-white hover:bg-slate-800 rounded-lg transition-colors" title="Sign out">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                </button>
            </form>
        </div>
    </div>
</aside>

<!-- Content shell -->
<div class="lg:pl-72 min-h-screen flex flex-col">
    <header class="sticky top-0 z-30 h-16 bg-white/95 backdrop-blur border-b border-slate-200 print-hide">
        <div class="h-full px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <button type="button" class="lg:hidden p-2 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50" data-admin-open aria-label="Open admin menu">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>
                <div class="min-w-0">
                    <span class="block text-[10px] uppercase tracking-wider font-bold text-slate-400">Admin</span>
                    <h1 class="text-sm font-black text-slate-900 truncate"><?= e($activeLabel) ?></h1>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a href="<?= e(url('index.php')) ?>" target="_blank" rel="noopener" class="hidden sm:flex items-center gap-1.5 px-3 py-2 rounded-lg border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50">
                    <i data-lucide="external-link" class="w-3.5 h-3.5 text-[#00A651]"></i>
                    <span>Storefront</span>
                </a>
                <span class="hidden md:flex items-center gap-2 px-2.5 py-1.5 rounded-lg bg-slate-100 border border-slate-200 text-xs">
                    <span class="w-6 h-6 rounded-full bg-slate-900 text-white text-[10px] font-black flex items-center justify-center"><?= e($adminInitial) ?></span>
                    <span class="max-w-[150px] truncate font-semibold text-slate-700"><?= e($adminUser['email']) ?></span>
                </span>
                <form action="<?= e(url('admin/logout.php')) ?>" method="post">
                    <?= csrf_field() ?>
                    <button type="submit" class="p-2 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg border border-slate-200 transition-colors" title="Sign out">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <?php render_flashes(); ?>

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 pb-16">
