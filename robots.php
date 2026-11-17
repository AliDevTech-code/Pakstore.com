<?php
/** Dynamic robots.txt so the Sitemap URL always matches the live domain. */

require_once __DIR__ . '/includes/functions.php';

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$origin = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');

header('Content-Type: text/plain; charset=UTF-8');
?>
User-agent: *
Allow: /

Disallow: /admin/
Disallow: /config/
Disallow: /includes/
Disallow: /cart.php
Disallow: /checkout.php
Disallow: /account.php
Disallow: /order-success.php

Sitemap: <?= e($origin . url('sitemap.xml')) ?>
