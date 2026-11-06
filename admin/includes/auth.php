<?php
/** Admin guard + shared admin helpers. */

require_once __DIR__ . '/../../includes/functions.php';

require_admin();

$adminUser = current_admin();

/** Allowed dashboard tabs. */
function admin_tabs(): array
{
    return [
        'dashboard'  => ['label' => 'Dashboard', 'icon' => 'gauge', 'file' => 'dashboard.php'],
        'orders'     => ['label' => 'Orders', 'icon' => 'package', 'file' => 'orders.php'],
        'products'   => ['label' => 'Products', 'icon' => 'shopping-bag', 'file' => 'products.php'],
        'categories' => ['label' => 'Categories', 'icon' => 'tag', 'file' => 'categories.php'],
        'banners'    => ['label' => 'Banners', 'icon' => 'image', 'file' => 'banners.php'],
        'users'      => ['label' => 'Customers', 'icon' => 'users', 'file' => 'users.php'],
        'analytics'  => ['label' => 'Analytics', 'icon' => 'bar-chart-3', 'file' => 'analytics.php'],
        'settings'   => ['label' => 'Settings', 'icon' => 'settings', 'file' => 'settings.php'],
    ];
}

function admin_active_tab(): string
{
    $tab = get_string('tab', 'dashboard');
    return array_key_exists($tab, admin_tabs()) ? $tab : 'dashboard';
}

function admin_pending_orders(): int
{
    try {
        return (int) db()->query("SELECT COUNT(*) FROM orders WHERE status = 'Pending'")->fetchColumn();
    } catch (Throwable $e) {
        return 0;
    }
}
