<?php
/**
 * Open Graph preview image (1200x630 JPEG).
 *
 * WhatsApp / Facebook / LinkedIn only render a link preview when og:image is
 * a normal raster image (JPG/PNG) that is big enough. Small logos and WebP
 * uploads therefore show no preview, so this endpoint renders one fixed-size
 * JPEG card for the site and for each product.
 *
 *   og-image.php?type=site   -> store logo centered on a white card
 *   og-image.php?id=123      -> that product's photo on a white card
 *
 * Falls back to redirecting to the original image when GD is unavailable.
 */

require_once __DIR__ . '/includes/functions.php';

const OG_WIDTH  = 1200;
const OG_HEIGHT = 630;
const OG_PAD    = 60;

$type = get_string('type', 'site');
$id   = (int) get_string('id', '0');

$source = '';
if ($type === 'product' && $id > 0) {
    $stmt = db()->prepare('SELECT image FROM products WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $source = trim((string) ($stmt->fetchColumn() ?: ''));
}
$logo = trim((string) setting('logo_url', ''));

$rootDir  = realpath(__DIR__);
$cacheDir = __DIR__ . '/uploads/og';
if (!is_dir($cacheDir)) {
    @mkdir($cacheDir, 0755, true);
}

/** Resolve a stored path to a real local file inside the project. */
$resolve = static function (string $path) use ($rootDir): ?string {
    if ($path === '' || preg_match('~^https?://~i', $path) || str_starts_with($path, 'data:')) {
        return null;
    }
    $real = realpath(__DIR__ . '/' . ltrim($path, '/'));
    if ($real === false || !is_file($real) || $rootDir === false) {
        return null;
    }
    return str_starts_with($real, $rootDir) ? $real : null;
};

/** Download a remote product image once and keep it next to the cache. */
$remoteFile = static function (string $url) use ($cacheDir): ?string {
    if (!preg_match('~^https?://~i', $url) || !filter_var($url, FILTER_VALIDATE_URL)) {
        return null;
    }
    $local = $cacheDir . '/src-' . sha1($url) . '.img';
    if (is_file($local) && filesize($local) > 1024) {
        return $local;
    }
    $context = stream_context_create([
        'http' => ['timeout' => 8, 'user_agent' => 'PakStore-og/1.0'],
        'https' => ['timeout' => 8, 'user_agent' => 'PakStore-og/1.0'],
    ]);
    $bytes = @file_get_contents($url, false, $context, 0, 5 * 1024 * 1024);
    if (!is_string($bytes) || strlen($bytes) < 1024) {
        return null;
    }
    if (!is_dir($cacheDir) || !is_writable($cacheDir)) {
        return null;
    }
    return @file_put_contents($local, $bytes) !== false ? $local : null;
};

$sourceFile = $resolve($source);
if ($sourceFile === null && $source !== '') {
    $sourceFile = $remoteFile($source);
}
$isProductCard = $type === 'product' && $id > 0 && $sourceFile !== null;
if ($sourceFile === null) {
    $sourceFile = $resolve($logo);
}

/* Without GD (or without any usable image) just point at the best raw file. */
if (!function_exists('imagecreatetruecolor') || $sourceFile === null) {
    $raw = $source !== '' ? $source : $logo;
    if ($raw === '') {
        $raw = 'assets/images/hero_pakstore_banner_1793814622604.jpg';
    }
    header('Location: ' . image_url($raw), true, 302);
    exit;
}

$cacheName = 'og-' . ($isProductCard ? 'product-' . $id : 'site') . '-'
    . substr(sha1($sourceFile . '|' . filemtime($sourceFile)), 0, 12) . '.jpg';
$cachePath = $cacheDir . '/' . $cacheName;

$send = static function (string $file): void {
    header('Content-Type: image/jpeg');
    header('Content-Length: ' . (string) filesize($file));
    header('Cache-Control: public, max-age=604800');
    header('Access-Control-Allow-Origin: *');
    readfile($file);
    exit;
};

if (is_file($cachePath)) {
    $send($cachePath);
}

/* Load the source image. */
$info = @getimagesize($sourceFile);
$src  = false;
if ($info !== false) {
    switch ($info[2]) {
        case IMAGETYPE_JPEG:
            $src = @imagecreatefromjpeg($sourceFile);
            break;
        case IMAGETYPE_PNG:
            $src = @imagecreatefrompng($sourceFile);
            break;
        case IMAGETYPE_GIF:
            $src = @imagecreatefromgif($sourceFile);
            break;
        case IMAGETYPE_WEBP:
            if (function_exists('imagecreatefromwebp')) {
                $src = @imagecreatefromwebp($sourceFile);
            }
            break;
    }
}
if (!$src) {
    $raw = $isProductCard ? $source : $logo;
    header('Location: ' . image_url($raw !== '' ? $raw : 'assets/images/hero_pakstore_banner_1793814622604.jpg'), true, 302);
    exit;
}

/* Compose the 1200x630 card: white background, image scaled to fit. */
$canvas = imagecreatetruecolor(OG_WIDTH, OG_HEIGHT);
imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
imagealphablending($canvas, true);
if (function_exists('imagepalettetotruecolor')) {
    @imagepalettetotruecolor($src);
}

$srcW = imagesx($src);
$srcH = imagesy($src);
$boxW = OG_WIDTH - (OG_PAD * 2);
$boxH = OG_HEIGHT - (OG_PAD * 2);
$scale = min($boxW / $srcW, $boxH / $srcH);
if (!$isProductCard) {
    /* Logos are usually small: never blow them up more than 1.5x. */
    $scale = min($scale, 1.5);
}
$newW = (int) round($srcW * $scale);
$newH = (int) round($srcH * $scale);
$dstX = (int) round((OG_WIDTH - $newW) / 2);
$dstY = (int) round((OG_HEIGHT - $newH) / 2);

imagecopyresampled($canvas, $src, $dstX, $dstY, 0, 0, $newW, $newH, $srcW, $srcH);
imagedestroy($src);

if (is_dir($cacheDir) && is_writable($cacheDir)) {
    @imagejpeg($canvas, $cachePath, 84);
}

if (is_file($cachePath)) {
    imagedestroy($canvas);
    $send($cachePath);
}

/* Cache not writable: stream the generated image directly. */
header('Content-Type: image/jpeg');
header('Cache-Control: public, max-age=86400');
imagejpeg($canvas, null, 84);
imagedestroy($canvas);
