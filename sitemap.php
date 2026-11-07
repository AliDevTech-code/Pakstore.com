<?php
/** XML sitemap for products and categories. */

require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/xml; charset=UTF-8');

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$origin = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
$homeUrl = $origin . url('index.php');

$urls = [
    ['loc' => $homeUrl, 'lastmod' => date('Y-m-d'), 'priority' => '1.0'],
    ['loc' => $origin . url('track-order.php'), 'lastmod' => date('Y-m-d'), 'priority' => '0.4'],
];

foreach (all_categories() as $category) {
    $urls[] = [
        'loc'      => $origin . url('index.php?category=' . urlencode($category['slug'])),
        'lastmod'  => date('Y-m-d'),
        'priority' => '0.7',
    ];
}

try {
    $rows = db()->query('SELECT id, updated_at FROM products ORDER BY updated_at DESC')->fetchAll();
} catch (Throwable $e) {
    $rows = [];
}

foreach ($rows as $row) {
    $urls[] = [
        'loc'      => $origin . url('product.php?id=' . (int) $row['id']),
        'lastmod'  => date('Y-m-d', strtotime((string) $row['updated_at'])),
        'priority' => '0.8',
    ];
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as $item): ?>
    <url>
        <loc><?= e($item['loc']) ?></loc>
        <lastmod><?= e($item['lastmod']) ?></lastmod>
        <priority><?= e($item['priority']) ?></priority>
    </url>
<?php endforeach; ?>
</urlset>
