<?php
/** Admin dashboard router. */

require_once __DIR__ . '/includes/auth.php';

$activeTab = admin_active_tab();
$tabs = admin_tabs();
$pageTitle = $tabs[$activeTab]['label'] . ' - Admin';

require __DIR__ . '/includes/header.php';
require __DIR__ . '/tabs/' . $tabs[$activeTab]['file'];
require __DIR__ . '/includes/footer.php';
