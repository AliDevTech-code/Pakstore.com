<?php
/**
 * Shared helpers: output escaping, settings, sessions, CSRF, cart, auth,
 * uploads and small formatting utilities.
 */

require_once __DIR__ . '/../config/db.php';

/* ------------------------------------------------------------------
 * Session
 * ------------------------------------------------------------------ */
if (session_status() === PHP_SESSION_NONE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/* ------------------------------------------------------------------
 * Multibyte fallbacks (some shared hosts disable mbstring)
 * ------------------------------------------------------------------ */
if (!function_exists('mb_strlen')) {
    function mb_strlen($string, $encoding = null): int
    {
        return strlen((string) $string);
    }
}
if (!function_exists('mb_substr')) {
    function mb_substr($string, $start, $length = null, $encoding = null): string
    {
        return $length === null ? substr((string) $string, $start) : substr((string) $string, $start, $length);
    }
}

/* ------------------------------------------------------------------
 * Output / URL helpers
 * ------------------------------------------------------------------ */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    $base = rtrim(APP_BASE, '/');
    return ($base === '' ? '' : $base) . '/' . ltrim($path, '/');
}

function redirect(string $path): void
{
    $target = preg_match('~^https?://~i', $path) ? $path : url($path);
    header('Location: ' . $target);
    exit;
}

function money($amount): string
{
    return 'Rs. ' . number_format((float) $amount, 0, '.', ',');
}

function format_date(?string $datetime, bool $withTime = true): string
{
    if (!$datetime) {
        return '—';
    }
    $ts = strtotime($datetime);
    if ($ts === false) {
        return (string) $datetime;
    }
    return date($withTime ? 'd M Y, h:i A' : 'd M Y', $ts);
}

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim((string) $text, '-') ?: 'item';
}

function clean_phone_wa(string $phone): string
{
    $digits = preg_replace('/[^0-9]/', '', $phone);
    if (str_starts_with($digits, '03')) {
        return '92' . substr($digits, 1);
    }
    if (str_starts_with($digits, '92')) {
        return $digits;
    }
    return '92' . ltrim($digits, '0');
}

function json_array(?string $json): array
{
    if (!$json) {
        return [];
    }
    $decoded = json_decode($json, true);
    return is_array($decoded) ? array_values(array_filter($decoded, static fn($v) => $v !== '' && $v !== null)) : [];
}

function image_url(?string $path): string
{
    $path = trim((string) $path);
    if ($path === '') {
        return url('assets/images/placeholder.svg');
    }
    if (preg_match('~^https?://~i', $path) || str_starts_with($path, 'data:')) {
        return $path;
    }
    if (str_starts_with($path, '/')) {
        return $path;
    }
    return url($path);
}

/* ------------------------------------------------------------------
 * Flash messages
 * ------------------------------------------------------------------ */
function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function take_flashes(): array
{
    $items = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $items;
}

function render_flashes(): void
{
    foreach (take_flashes() as $item) {
        $type = $item['type'] ?? 'info';
        $classes = [
            'success' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
            'error'   => 'bg-rose-50 text-rose-800 border-rose-200',
            'info'    => 'bg-sky-50 text-sky-800 border-sky-200',
        ][$type] ?? 'bg-slate-50 text-slate-800 border-slate-200';

        echo '<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-3">'
            . '<div class="border rounded-xl px-4 py-2.5 text-xs font-semibold ' . $classes . '">'
            . e($item['message'])
            . '</div></div>';
    }
}

/* ------------------------------------------------------------------
 * CSRF
 * ------------------------------------------------------------------ */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_verify(): void
{
    $sent = $_POST['csrf_token'] ?? '';
    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        http_response_code(419);
        exit('Invalid security token. Please go back, refresh the page and try again.');
    }
}

function require_post(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        http_response_code(405);
        header('Allow: POST');
        exit('Method not allowed.');
    }
}

/* ------------------------------------------------------------------
 * Settings
 * ------------------------------------------------------------------ */
function settings_all(bool $refresh = false): array
{
    static $cache = null;
    if ($cache === null || $refresh) {
        $cache = [];
        try {
            $rows = db()->query('SELECT setting_key, setting_value FROM settings')->fetchAll();
            foreach ($rows as $row) {
                $cache[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Throwable $e) {
            error_log('Settings load failed: ' . $e->getMessage());
        }
    }
    return $cache;
}

function setting(string $key, string $default = ''): string
{
    $all = settings_all();
    if (!array_key_exists($key, $all)) {
        return $default;
    }
    $value = trim((string) $all[$key]);
    return $value === '' ? $default : $value;
}

function update_setting(string $key, string $value): void
{
    $stmt = db()->prepare(
        'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    );
    $stmt->execute([$key, $value]);
    settings_all(true);
}

function delivery_charges(): float
{
    return (float) setting('delivery_charges', '0');
}

/* ------------------------------------------------------------------
 * Categories
 * ------------------------------------------------------------------ */
function all_categories(): array
{
    static $cache = null;
    if ($cache === null) {
        $cache = db()->query('SELECT id, name, slug, icon FROM categories ORDER BY name ASC')->fetchAll();
    }
    return $cache;
}

function find_category_by_slug(string $slug): ?array
{
    $stmt = db()->prepare('SELECT * FROM categories WHERE slug = ? LIMIT 1');
    $stmt->execute([$slug]);
    $row = $stmt->fetch();
    return $row ?: null;
}

/* ------------------------------------------------------------------
 * Cart (session based)
 * ------------------------------------------------------------------ */
function cart_raw(): array
{
    $cart = $_SESSION['cart'] ?? [];
    return is_array($cart) ? $cart : [];
}

function cart_add(int $productId, int $quantity = 1): void
{
    if ($productId <= 0) {
        return;
    }
    $quantity = max(1, $quantity);
    $cart = cart_raw();
    $cart[$productId] = ($cart[$productId] ?? 0) + $quantity;
    $_SESSION['cart'] = $cart;
}

function cart_update(int $productId, int $quantity): void
{
    $cart = cart_raw();
    if ($quantity <= 0 || !isset($cart[$productId])) {
        unset($cart[$productId]);
    } else {
        $cart[$productId] = $quantity;
    }
    $_SESSION['cart'] = $cart;
}

function cart_remove(int $productId): void
{
    $cart = cart_raw();
    unset($cart[$productId]);
    $_SESSION['cart'] = $cart;
}

function cart_clear(): void
{
    unset($_SESSION['cart']);
}

function cart_detailed(): array
{
    $cart = cart_raw();
    if (!$cart) {
        return [];
    }

    $ids = array_map('intval', array_keys($cart));
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $stmt = db()->prepare(
        "SELECT p.*, c.name AS category_name, c.slug AS category_slug
         FROM products p
         LEFT JOIN categories c ON c.id = p.category_id
         WHERE p.id IN ($placeholders)"
    );
    $stmt->execute($ids);
    $products = $stmt->fetchAll();

    $items = [];
    foreach ($products as $product) {
        $pid = (int) $product['id'];
        $qty = max(1, (int) ($cart[$pid] ?? 1));
        if ((int) $product['stock'] > 0 && $qty > (int) $product['stock']) {
            $qty = (int) $product['stock'];
        }
        if (!(int) $product['in_stock'] || (int) $product['stock'] <= 0) {
            unset($_SESSION['cart'][$pid]);
            continue;
        }
        $product['qty'] = $qty;
        $product['line_total'] = $qty * (float) $product['price'];
        $items[] = $product;
    }
    return $items;
}

function cart_count(): int
{
    $count = 0;
    foreach (cart_raw() as $qty) {
        $count += (int) $qty;
    }
    return $count;
}

function cart_subtotal(): float
{
    $total = 0.0;
    foreach (cart_detailed() as $item) {
        $total += (float) $item['line_total'];
    }
    return $total;
}

function cart_totals(): array
{
    $subtotal = cart_subtotal();
    $shipping = $subtotal > 0 ? delivery_charges() : 0.0;
    return [
        'subtotal' => $subtotal,
        'shipping' => $shipping,
        'total'    => $subtotal + $shipping,
    ];
}

/* ------------------------------------------------------------------
 * Wishlist (session based)
 * ------------------------------------------------------------------ */
function wishlist_ids(): array
{
    $ids = $_SESSION['wishlist'] ?? [];
    if (!is_array($ids)) {
        return [];
    }
    $ids = array_map('intval', $ids);
    return array_values(array_unique(array_filter($ids)));
}

function wishlist_has(int $productId): bool
{
    return in_array($productId, wishlist_ids(), true);
}

function wishlist_toggle(int $productId): void
{
    $ids = wishlist_ids();
    if (in_array($productId, $ids, true)) {
        $ids = array_values(array_diff($ids, [$productId]));
    } else {
        $ids[] = $productId;
    }
    $_SESSION['wishlist'] = $ids;
}

/* ------------------------------------------------------------------
 * Customer auth
 * ------------------------------------------------------------------ */
function current_customer(): ?array
{
    static $loaded = false;
    static $customer = null;

    if ($loaded) {
        return $customer;
    }
    $loaded = true;

    $id = (int) ($_SESSION['customer_id'] ?? 0);
    if ($id <= 0) {
        return null;
    }
    $stmt = db()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) {
        unset($_SESSION['customer_id']);
        return null;
    }
    $customer = $row;
    return $customer;
}

function customer_logout(): void
{
    unset($_SESSION['customer_id']);
}

/* ------------------------------------------------------------------
 * Admin auth
 * ------------------------------------------------------------------ */
function current_admin(): ?array
{
    static $loaded = false;
    static $admin = null;

    if ($loaded) {
        return $admin;
    }
    $loaded = true;

    $id = (int) ($_SESSION['admin_id'] ?? 0);
    if ($id <= 0) {
        return null;
    }
    $stmt = db()->prepare('SELECT * FROM admin_users WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) {
        unset($_SESSION['admin_id']);
        return null;
    }
    $admin = $row;
    return $admin;
}

function require_admin(): void
{
    if (!current_admin()) {
        flash('error', 'Please sign in to access the admin panel.');
        redirect('admin/login.php');
    }
}

function password_matches(string $plain, string $storedHash): bool
{
    if (str_starts_with($storedHash, '$2y$') || str_starts_with($storedHash, '$2a$') || str_starts_with($storedHash, '$argon2')) {
        return password_verify($plain, $storedHash);
    }
    // Legacy / SQL seed hash (SHA-256). Upgraded to bcrypt on first login.
    if (strlen($storedHash) === 64 && ctype_xdigit($storedHash)) {
        return hash_equals(strtolower($storedHash), hash('sha256', $plain));
    }
    return false;
}

/* ------------------------------------------------------------------
 * Orders
 * ------------------------------------------------------------------ */
function generate_order_code(): string
{
    $pdo = db();
    $stmt = $pdo->prepare('SELECT 1 FROM orders WHERE order_code = ? LIMIT 1');
    for ($i = 0; $i < 20; $i++) {
        $code = 'PK-' . random_int(1000, 9999);
        $stmt->execute([$code]);
        if (!$stmt->fetchColumn()) {
            return $code;
        }
    }
    return 'PK-' . substr((string) time(), -6);
}

function order_items(int $orderId): array
{
    $stmt = db()->prepare('SELECT * FROM order_items WHERE order_id = ? ORDER BY id ASC');
    $stmt->execute([$orderId]);
    return $stmt->fetchAll();
}

function find_order(string $codeOrPhone): ?array
{
    $clean = trim($codeOrPhone);
    if ($clean === '') {
        return null;
    }
    $digits = preg_replace('/[^0-9]/', '', $clean);

    $stmt = db()->prepare(
        "SELECT * FROM orders
         WHERE order_code = ? OR phone = ? OR REPLACE(REPLACE(phone, '-', ''), ' ', '') = ?
         ORDER BY id DESC LIMIT 1"
    );
    $stmt->execute([strtoupper($clean), $clean, $digits]);
    $order = $stmt->fetch();
    if (!$order) {
        return null;
    }
    $order['items'] = order_items((int) $order['id']);
    return $order;
}

function order_status_classes(string $status): string
{
    return [
        'Delivered'  => 'bg-emerald-100 text-emerald-800',
        'Shipped'    => 'bg-blue-100 text-blue-800',
        'Processing' => 'bg-indigo-100 text-indigo-800',
        'Cancelled'  => 'bg-rose-100 text-rose-800',
        'Pending'    => 'bg-amber-100 text-amber-800',
    ][$status] ?? 'bg-slate-100 text-slate-700';
}

function order_status_step(string $status): int
{
    return [
        'Pending'    => 1,
        'Processing' => 2,
        'Shipped'    => 3,
        'Delivered'  => 4,
        'Cancelled'  => 0,
    ][$status] ?? 1;
}

/* ------------------------------------------------------------------
 * Image uploads
 * ------------------------------------------------------------------ */
function upload_image(array $file, string $subdir, string $prefix = 'img'): array
{
    if (!isset($file['error']) || is_array($file['error'])) {
        return [false, 'No valid file was uploaded.'];
    }
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return [false, 'No file selected.'];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [false, 'Upload failed (error code ' . (int) $file['error'] . ').'];
    }
    if ((int) $file['size'] > MAX_UPLOAD_BYTES) {
        return [false, 'Image is larger than 3 MB.'];
    }

    $mime = '';
    if (class_exists('finfo')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = (string) $finfo->file($file['tmp_name']);
    } else {
        $info = @getimagesize($file['tmp_name']);
        $mime = is_array($info) && !empty($info['mime']) ? (string) $info['mime'] : '';
    }
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];
    if (!isset($allowed[$mime])) {
        return [false, 'Only JPG, PNG, WEBP or GIF images are allowed.'];
    }
    if (@getimagesize($file['tmp_name']) === false) {
        return [false, 'The uploaded file is not a valid image.'];
    }

    $subdir = trim($subdir, '/');
    $dir = dirname(__DIR__) . '/uploads/' . $subdir;
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        return [false, 'Upload folder could not be created. Check folder permissions.'];
    }

    $name = $prefix . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(5)) . '.' . $allowed[$mime];
    $target = $dir . '/' . $name;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        return [false, 'Could not move the uploaded file. Check uploads/ permissions (755).'];
    }

    return [true, 'uploads/' . $subdir . '/' . $name];
}

/* ------------------------------------------------------------------
 * Misc
 * ------------------------------------------------------------------ */
function post_int(string $key, int $default = 0): int
{
    return isset($_POST[$key]) ? (int) $_POST[$key] : $default;
}

function post_string(string $key, string $default = ''): string
{
    $value = $_POST[$key] ?? $default;
    return is_string($value) ? trim($value) : $default;
}

function get_string(string $key, string $default = ''): string
{
    $value = $_GET[$key] ?? $default;
    return is_string($value) ? trim($value) : $default;
}

/** Only allow local redirect targets (prevents open redirects). */
function local_redirect_target(string $fallback = 'index.php'): string
{
    $target = post_string('redirect', '');
    if ($target === '') {
        return $fallback;
    }
    if (
        preg_match('~^[a-z][a-z0-9+.-]*:~i', $target)
        || str_starts_with($target, '//')
        || str_contains($target, '\\')
        || str_contains($target, "\n")
        || str_contains($target, "\r")
    ) {
        return $fallback;
    }
    return ltrim($target, '/');
}
