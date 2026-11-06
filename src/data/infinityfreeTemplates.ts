export const SQL_SCHEMA = `-- ========================================================
-- PakStore E-Commerce Database Schema for MySQL / InfinityFree
-- Database Name: pakstore_db (or your InfinityFree db name e.g. if0_12345678_pakstore)
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Store Settings Table
CREATE TABLE IF NOT EXISTS \`settings\` (
  \`id\` INT(11) PRIMARY KEY DEFAULT 1,
  \`store_name\` VARCHAR(100) NOT NULL DEFAULT 'PakStore',
  \`logo_url\` TEXT DEFAULT NULL,
  \`top_bar_text\` VARCHAR(255) NOT NULL DEFAULT 'Cash on Delivery All Over Pakistan',
  \`phone\` VARCHAR(50) NOT NULL DEFAULT '0300-1234567',
  \`whatsapp\` VARCHAR(50) NOT NULL DEFAULT '923001234567',
  \`email\` VARCHAR(100) NOT NULL DEFAULT 'support@pakstore.com',
  \`delivery_charges\` INT(11) NOT NULL DEFAULT 199
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO \`settings\` (\`id\`, \`store_name\`, \`logo_url\`, \`top_bar_text\`, \`phone\`, \`whatsapp\`, \`email\`, \`delivery_charges\`)
VALUES (1, 'PakStore', '', 'Cash on Delivery All Over Pakistan', '0300-1234567', '923001234567', 'support@pakstore.com', 199)
ON DUPLICATE KEY UPDATE \`store_name\`=\`store_name\`;

-- 2. Categories Table
CREATE TABLE IF NOT EXISTS \`categories\` (
  \`id\` INT(11) AUTO_INCREMENT PRIMARY KEY,
  \`name\` VARCHAR(100) NOT NULL UNIQUE,
  \`slug\` VARCHAR(100) NOT NULL UNIQUE,
  \`created_at\` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO \`categories\` (\`name\`, \`slug\`) VALUES
('Mobiles', 'mobiles'),
('Men''s Fashion', 'mens-fashion'),
('Women''s Fashion', 'womens-fashion'),
('Shoes', 'shoes'),
('Watches', 'watches'),
('Electronics', 'electronics'),
('Home & Kitchen', 'home-kitchen'),
('Beauty', 'beauty')
ON DUPLICATE KEY UPDATE \`name\`=\`name\`;

-- 3. Hero Slider Banners Table
CREATE TABLE IF NOT EXISTS \`banners\` (
  \`id\` INT(11) AUTO_INCREMENT PRIMARY KEY,
  \`title\` VARCHAR(255) NOT NULL,
  \`subtitle\` TEXT DEFAULT NULL,
  \`image\` TEXT NOT NULL,
  \`category\` VARCHAR(100) DEFAULT NULL,
  \`badge\` VARCHAR(50) DEFAULT NULL,
  \`created_at\` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO \`banners\` (\`title\`, \`subtitle\`, \`image\`, \`category\`, \`badge\`) VALUES
('PakStore Mega Online Deals', 'Pay Cash at Doorstep Across Pakistan. 100% Inspected & Sealed Parcels.', 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=1400', 'Electronics', 'Cash on Delivery'),
('Smart Tech & Titanium Wearables', 'AMOLED Always-On Displays, Bluetooth Calling & Long Battery Life.', 'https://images.unsplash.com/photo-1508615039623-a25605d2b022?w=1400', 'Watches', 'Up to 35% Off'),
('Latest Fashion & Arabian Oud', 'Hand-crafted Pakistani cowhide leather goods and concentrated attar.', 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=1400', 'Men''s Fashion', 'New Season 2026');

-- 4. Products Table (Exact 8 Demo Items)
CREATE TABLE IF NOT EXISTS \`products\` (
  \`id\` VARCHAR(32) PRIMARY KEY,
  \`title\` VARCHAR(255) NOT NULL,
  \`category\` VARCHAR(100) NOT NULL,
  \`price\` INT(11) NOT NULL,
  \`original_price\` INT(11) DEFAULT NULL,
  \`stock\` INT(11) NOT NULL DEFAULT 10,
  \`image\` TEXT NOT NULL,
  \`badge\` VARCHAR(50) DEFAULT NULL,
  \`description\` TEXT NOT NULL,
  \`sku\` VARCHAR(50) NOT NULL,
  \`rating\` DECIMAL(2,1) DEFAULT 4.9,
  \`reviews_count\` INT(11) DEFAULT 42,
  \`in_stock\` TINYINT(1) NOT NULL DEFAULT 1,
  \`created_at\` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO \`products\` (\`id\`, \`title\`, \`category\`, \`price\`, \`original_price\`, \`stock\`, \`image\`, \`badge\`, \`description\`, \`sku\`, \`rating\`, \`reviews_count\`, \`in_stock\`) VALUES
('prd-101', 'Apex Chrono Ultra Titanium Smartwatch (Calling & AMOLED)', 'Watches', 6499, 9999, 25, 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=800', 'Bestseller', 'Aerospace titanium bezel with crystal-clear AMOLED display and calling.', 'PAK-WCH-01', 4.9, 142, 1),
('prd-102', 'TrueBass Pro Active Noise Cancelling Earbuds (32dB ANC)', 'Electronics', 4250, 5999, 32, 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=800', 'Trending', 'Deep punchy bass with 32dB active noise cancellation and 36h playback.', 'PAK-AUD-02', 4.8, 96, 1),
('prd-103', 'Royal Hunter Full-Grain Cowhide Leather Wallet (RFID Shield)', 'Men''s Fashion', 2899, 3800, 20, 'https://images.unsplash.com/photo-1627123424574-724758594e93?w=800', 'Top Rated', '100% Genuine Pakistani cowhide leather with RFID anti-theft blocking.', 'PAK-LTH-03', 4.9, 184, 1),
('prd-104', 'Oud Al-Malik Luxury Arabian Eau De Parfum (100ml Concentrated)', 'Beauty', 5499, 7500, 14, 'https://images.unsplash.com/photo-1594035910387-fea47794261f?w=800', 'Sale', 'Opulent Oriental fragrance with saffron, royal ambergris, and Cambodian oud.', 'PAK-FRG-04', 5.0, 78, 1),
('prd-105', 'AirFlex Ultra Breathable Running Sneakers (Lightweight Sports)', 'Shoes', 3850, 5500, 28, 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=800', 'New', 'Ultra-lightweight mesh upper with cushioned shock-absorbing air sole.', 'PAK-SHS-05', 4.7, 65, 1),
('prd-106', 'Galaxy Ultra 5G Dual SIM Mobile Phone (256GB / 12GB RAM)', 'Mobiles', 89999, 105000, 12, 'https://images.unsplash.com/photo-1592899677977-9c10ca588bbd?w=800', 'Bestseller', 'PTA approved flagship 120Hz AMOLED display with 200MP camera.', 'PAK-MOB-06', 4.9, 112, 1),
('prd-107', 'Heritage Pure Lawn Stitched 3-Piece Designer Suit (Embroidered)', 'Women''s Fashion', 4999, 7200, 22, 'https://images.unsplash.com/photo-1583391733956-3750e0ff4e8b?w=800', 'Trending', 'Fine lawn cotton with chikankari neck embroidery and chiffon dupatta.', 'PAK-WMN-07', 4.8, 89, 1),
('prd-108', 'Smart Touch Electric Rapid Kettle & Thermos (1.8L Stainless Steel)', 'Home & Kitchen', 3199, 4500, 35, 'https://images.unsplash.com/photo-1570222094114-d054a817e56b?w=800', 'Sale', '1500W rapid boiling double-wall insulated food grade 304 stainless steel.', 'PAK-KTN-08', 4.8, 54, 1)
ON DUPLICATE KEY UPDATE \`title\`=\`title\`;

-- 5. Orders Table (With Full Customer Address & Text City)
CREATE TABLE IF NOT EXISTS \`orders\` (
  \`id\` VARCHAR(32) PRIMARY KEY, -- Formats as PK-XXXX
  \`customer_name\` VARCHAR(150) NOT NULL,
  \`phone\` VARCHAR(50) NOT NULL,
  \`email\` VARCHAR(150) DEFAULT NULL,
  \`city\` VARCHAR(100) NOT NULL, -- User writes city as plain text
  \`address\` TEXT NOT NULL,      -- Full handwritten address
  \`order_notes\` TEXT DEFAULT NULL,
  \`payment_method\` VARCHAR(50) NOT NULL DEFAULT 'Cash on Delivery',
  \`subtotal\` INT(11) NOT NULL,
  \`shipping_fee\` INT(11) NOT NULL DEFAULT 199,
  \`total_amount\` INT(11) NOT NULL,
  \`status\` ENUM('Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled') NOT NULL DEFAULT 'Pending',
  \`tracking_courier\` VARCHAR(50) DEFAULT 'TCS',
  \`tracking_number\` VARCHAR(100) DEFAULT NULL,
  \`created_at\` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Order Items Table
CREATE TABLE IF NOT EXISTS \`order_items\` (
  \`id\` INT(11) AUTO_INCREMENT PRIMARY KEY,
  \`order_id\` VARCHAR(32) NOT NULL,
  \`product_id\` VARCHAR(32) NOT NULL,
  \`title\` VARCHAR(255) NOT NULL,
  \`price\` INT(11) NOT NULL,
  \`quantity\` INT(11) NOT NULL,
  \`image\` TEXT NOT NULL,
  CONSTRAINT \`fk_order\` FOREIGN KEY (\`order_id\`) REFERENCES \`orders\` (\`id\`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Registered Users Table
CREATE TABLE IF NOT EXISTS \`users\` (
  \`id\` INT(11) AUTO_INCREMENT PRIMARY KEY,
  \`full_name\` VARCHAR(150) NOT NULL,
  \`phone\` VARCHAR(50) NOT NULL UNIQUE,
  \`email\` VARCHAR(150) NOT NULL UNIQUE,
  \`password\` VARCHAR(255) NOT NULL,
  \`city\` VARCHAR(100) NOT NULL,
  \`address\` TEXT NOT NULL,
  \`created_at\` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Admin Users Table (admin@pakstore.com / admin123)
CREATE TABLE IF NOT EXISTS \`admin_users\` (
  \`id\` INT(11) AUTO_INCREMENT PRIMARY KEY,
  \`email\` VARCHAR(100) NOT NULL UNIQUE,
  \`password\` VARCHAR(255) NOT NULL,
  \`created_at\` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed Admin: admin@pakstore.com with hash for admin123
INSERT INTO \`admin_users\` (\`email\`, \`password\`)
VALUES ('admin@pakstore.com', '$2y$10$w6z/6tVv1UuC2D2O7lI5I.3TfxrWwWn60WjP.k9Cgq4i9Y5vR5cK.')
ON DUPLICATE KEY UPDATE \`email\`=\`email\`;

-- Seed Sample Order PK-7821
INSERT INTO \`orders\` (\`id\`, \`customer_name\`, \`phone\`, \`email\`, \`city\`, \`address\`, \`order_notes\`, \`subtotal\`, \`shipping_fee\`, \`total_amount\`, \`status\`) VALUES
('PK-7821', 'Muhammad Bilal Khan', '0300-4567891', 'bilal.khan@gmail.com', 'Lahore', 'House #42-B, Sector C, Commercial Area, Bahria Town', 'Call before arriving please', 6499, 199, 6698, 'Pending')
ON DUPLICATE KEY UPDATE \`customer_name\`=\`customer_name\`;

INSERT INTO \`order_items\` (\`order_id\`, \`product_id\`, \`title\`, \`price\`, \`quantity\`, \`image\`) VALUES
('PK-7821', 'prd-101', 'Apex Chrono Ultra Titanium Smartwatch (Calling & AMOLED)', 6499, 1, 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=800');
`;

export const PHP_CONFIG = `<?php
/**
 * PakStore - InfinityFree Database Configuration
 * Update credentials from your InfinityFree cPanel -> MySQL Databases
 */

define('DB_HOST', 'sql300.infinityfree.com'); // e.g. sqlXXX.epizy.com or sqlXXX.infinityfree.com
define('DB_USER', 'if0_12345678');           // InfinityFree vPanel username
define('DB_PASS', 'YOUR_VPANEL_PASSWORD');   // InfinityFree password
define('DB_NAME', 'if0_12345678_pakstore');   // Database name

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage() . "<br>Please check config.php with your InfinityFree MySQL details.");
}

// Format Pakistani Rupee (PKR)
function formatPKR($amount) {
    return 'Rs. ' . number_format($amount);
}

// Clean user input
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Fetch store settings from DB
function getStoreSettings($pdo) {
    $stmt = $pdo->query("SELECT * FROM settings WHERE id = 1");
    $settings = $stmt->fetch();
    if (!$settings) {
        return [
            'store_name' => 'PakStore',
            'logo_url' => '',
            'top_bar_text' => 'Cash on Delivery All Over Pakistan',
            'phone' => '0300-1234567',
            'whatsapp' => '923001234567',
            'email' => 'support@pakstore.com',
            'delivery_charges' => 199,
        ];
    }
    return $settings;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>`;

export const PHP_INDEX = `<?php
require_once 'config.php';
$settings = getStoreSettings($pdo);

// Category filter
$selectedCat = $_GET['cat'] ?? 'All';
$search = $_GET['search'] ?? '';

$sql = "SELECT * FROM products WHERE in_stock = 1";
$params = [];

if ($selectedCat !== 'All' && !empty($selectedCat)) {
    $sql .= " AND category = ?";
    $params[] = $selectedCat;
}

if (!empty($search)) {
    $sql .= " AND (title LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Categories
$catsStmt = $pdo->query("SELECT * FROM categories ORDER BY id ASC");
$categories = $catsStmt->fetchAll();

// Banners
$bannersStmt = $pdo->query("SELECT * FROM banners ORDER BY id ASC");
$banners = $bannersStmt->fetchAll();

$cartCount = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($settings['store_name']) ?> - Pakistan E-Commerce & Cash on Delivery</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-900 pb-16 md:pb-0">

  <!-- Top Announcement Bar -->
  <div class="bg-[#003d1e] text-emerald-100 text-xs py-2 px-4 text-center font-semibold">
    <?= htmlspecialchars($settings['top_bar_text']) ?> | 100% Cash on Delivery
  </div>

  <!-- Header -->
  <header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-xs">
    <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-4">
      <!-- Logo -->
      <a href="index.php" class="flex items-center gap-1 shrink-0">
        <?php if (!empty($settings['logo_url'])): ?>
          <img src="<?= htmlspecialchars($settings['logo_url']) ?>" alt="Logo" class="h-9 max-w-[140px] object-contain">
        <?php else: ?>
          <span class="text-2xl font-black text-[#00A651]">Pak</span><span class="text-2xl font-black text-[#FF6B00]">Store</span>
        <?php endif; ?>
      </a>

      <!-- Search Bar -->
      <form action="index.php" method="GET" class="flex-1 max-w-lg hidden sm:flex">
        <div class="relative w-full">
          <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search products in Pakistan..." class="w-full pl-4 pr-10 py-2 text-xs bg-slate-100 border border-slate-300 rounded-lg focus:outline-none focus:border-[#00A651]">
          <button type="submit" class="absolute right-1 top-1 bottom-1 px-3 bg-[#FF6B00] text-white rounded text-xs">Search</button>
        </div>
      </form>

      <!-- Right Nav -->
      <div class="flex items-center gap-4 text-xs font-semibold">
        <a href="cart.php" class="text-slate-700 hover:text-[#00A651] relative">
          Cart (<?= $cartCount ?>)
        </a>
        <a href="track_order.php" class="text-slate-700 hover:text-[#00A651] hidden sm:inline">Track Order</a>
        <a href="admin/login.php" class="text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-200 hover:bg-emerald-100">Admin Portal</a>
      </div>
    </div>

    <!-- Category Bar -->
    <div class="bg-slate-50 border-t border-slate-200 overflow-x-auto">
      <div class="max-w-7xl mx-auto px-4 py-2 flex items-center gap-4 text-xs font-semibold">
        <a href="index.php" class="<?= $selectedCat === 'All' ? 'text-[#00A651] font-bold' : 'text-slate-600' ?>">All Categories</a>
        <?php foreach ($categories as $cat): ?>
          <a href="index.php?cat=<?= urlencode($cat['name']) ?>" class="<?= $selectedCat === $cat['name'] ? 'text-[#00A651] font-bold' : 'text-slate-600' ?> whitespace-nowrap">
            <?= htmlspecialchars($cat['name']) ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </header>

  <!-- Hero Slider -->
  <?php if (!empty($banners)): ?>
  <section class="max-w-7xl mx-auto px-4 py-6">
    <div class="relative rounded-2xl overflow-hidden bg-slate-900 aspect-16/9 md:aspect-21/9 text-white">
      <img src="<?= htmlspecialchars($banners[0]['image']) ?>" class="w-full h-full object-cover">
      <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/40 to-transparent p-6 md:p-12 flex flex-col justify-end">
        <span class="bg-[#FF6B00] text-white text-[11px] font-bold px-2 py-0.5 rounded w-fit mb-2"><?= htmlspecialchars($banners[0]['badge']) ?></span>
        <h2 class="text-2xl md:text-4xl font-black"><?= htmlspecialchars($banners[0]['title']) ?></h2>
        <p class="text-slate-300 text-xs md:text-sm mt-1 max-w-xl"><?= htmlspecialchars($banners[0]['subtitle']) ?></p>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- Products Grid (2 columns on mobile, 4 on desktop) -->
  <main class="max-w-7xl mx-auto px-4 pb-16">
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-xl font-bold text-slate-900"><?= htmlspecialchars($selectedCat) ?> Products</h3>
      <span class="text-xs text-slate-500"><?= count($products) ?> Items</span>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-6">
      <?php foreach ($products as $prod): ?>
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden hover:shadow-md transition flex flex-col justify-between">
          <div class="aspect-square bg-slate-100 relative">
            <img src="<?= htmlspecialchars($prod['image']) ?>" alt="<?= htmlspecialchars($prod['title']) ?>" class="w-full h-full object-cover">
            <?php if (!empty($prod['badge'])): ?>
              <span class="absolute top-2 left-2 bg-slate-900 text-white text-[9px] font-bold px-1.5 py-0.5 rounded">
                <?= htmlspecialchars($prod['badge']) ?>
              </span>
            <?php endif; ?>
          </div>
          <div class="p-3 flex-1 flex flex-col justify-between">
            <div>
              <span class="text-[10px] text-slate-400 uppercase font-bold"><?= htmlspecialchars($prod['category']) ?></span>
              <h4 class="font-bold text-slate-900 text-xs sm:text-sm mt-1 line-clamp-2"><?= htmlspecialchars($prod['title']) ?></h4>
              <div class="text-[11px] text-amber-500 font-bold mt-1">★ <?= $prod['rating'] ?> (<?= $prod['reviews_count'] ?>)</div>
            </div>
            <div class="mt-3 pt-2 border-t border-slate-100">
              <span class="text-sm sm:text-base font-black text-slate-900 font-mono"><?= formatPKR($prod['price']) ?></span>
              <div class="mt-2">
                <a href="checkout.php?buy_now=<?= urlencode($prod['id']) ?>" class="block w-full bg-[#00A651] hover:bg-[#008f45] text-white text-xs font-bold py-1.5 rounded-lg text-center shadow-xs">
                  Order COD (Cash on Delivery)
                </a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </main>

  <!-- Mobile Bottom Nav -->
  <div class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-slate-200 px-4 py-2 flex justify-between text-xs font-semibold text-slate-700">
    <a href="index.php" class="text-[#00A651]">Home</a>
    <a href="cart.php">Cart (<?= $cartCount ?>)</a>
    <a href="track_order.php">Track</a>
    <a href="admin/login.php">Admin</a>
  </div>

  <footer class="bg-white border-t border-slate-200 py-8 text-center text-xs text-slate-500">
    <p>© <?= date('Y') ?> <?= htmlspecialchars($settings['store_name']) ?> Pakistan. Cash on Delivery All Over Pakistan.</p>
  </footer>
</body>
</html>`;

export const PHP_CHECKOUT = `<?php
require_once 'config.php';
$settings = getStoreSettings($pdo);

$productId = $_GET['buy_now'] ?? null;
$product = null;

if ($productId) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
}

$orderPlaced = false;
$orderId = null;
$error = null;

// Handle Form Submission (Cash on Delivery)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $name = sanitize($_POST['customer_name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    // City is free text box written by customer - NO dropdown
    $city = sanitize($_POST['city'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $notes = sanitize($_POST['order_notes'] ?? '');
    $pId = sanitize($_POST['product_id'] ?? '');

    if (empty($name) || empty($phone) || empty($city) || empty($address)) {
        $error = "Please fill in all required fields (Name, Phone, City, and Full Address).";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$pId]);
        $prod = $stmt->fetch();

        if ($prod) {
            // Strictly PK-XXXX format (4 digits)
            $orderId = 'PK-' . rand(1000, 9999);
            $subtotal = $prod['price'];
            $shipping = intval($settings['delivery_charges']);
            $total = $subtotal + $shipping;

            $insertOrder = $pdo->prepare("
                INSERT INTO orders (id, customer_name, phone, city, address, order_notes, payment_method, subtotal, shipping_fee, total_amount, status)
                VALUES (?, ?, ?, ?, ?, ?, 'Cash on Delivery', ?, ?, ?, 'Pending')
            ");
            $insertOrder->execute([$orderId, $name, $phone, $city, $address, $notes, $subtotal, $shipping, $total]);

            $insertItem = $pdo->prepare("
                INSERT INTO order_items (order_id, product_id, title, price, quantity, image)
                VALUES (?, ?, ?, ?, 1, ?)
            ");
            $insertItem->execute([$orderId, $prod['id'], $prod['title'], $prod['price'], $prod['image']]);

            $orderPlaced = true;
        } else {
            $error = "Selected product is invalid or unavailable.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Cash on Delivery Checkout - <?= htmlspecialchars($settings['store_name']) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen py-10 px-4">

<div class="max-w-2xl mx-auto">
  <div class="mb-6 text-center">
    <a href="index.php" class="text-2xl font-bold tracking-tight text-slate-900">
      <span class="text-[#00A651]">Pak</span><span class="text-[#FF6B00]">Store</span>
    </a>
  </div>

  <?php if ($orderPlaced): ?>
    <div class="bg-white border border-emerald-200 rounded-2xl p-8 text-center shadow-sm">
      <div class="w-16 h-16 bg-emerald-100 text-[#00A651] rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-bold">✓</div>
      <h2 class="text-2xl font-bold text-slate-900 mb-1">Order Confirmed!</h2>
      <p class="text-sm text-slate-600 mb-4">Shukriya! Aapka Cash on Delivery order receive ho chuka hai.</p>
      
      <div class="bg-slate-50 rounded-xl p-4 text-left border border-slate-200 mb-6 text-sm">
        <p><strong>Order Tracking ID:</strong> <span class="text-[#00A651] font-mono font-bold"><?= $orderId ?></span></p>
        <p class="mt-1"><strong>Payment:</strong> Cash on Delivery (Pay cash to courier upon arrival)</p>
        <p class="mt-1"><strong>City:</strong> <?= htmlspecialchars($city) ?></p>
        <p class="mt-1"><strong>Delivery Address:</strong> <?= htmlspecialchars($address) ?></p>
      </div>

      <a href="index.php" class="bg-slate-900 hover:bg-slate-800 text-white font-semibold px-6 py-2.5 rounded-xl text-sm inline-block">Continue Shopping</a>
    </div>
  <?php else: ?>
    <div class="bg-white border border-slate-200 rounded-2xl p-6 md:p-8 shadow-sm">
      <h2 class="text-xl font-bold text-slate-900 mb-1">Cash on Delivery (COD) Checkout</h2>
      <p class="text-xs text-slate-500 mb-6">Koi online payment nahi karni. Parcel milne par courier ko cash ada karein.</p>

      <?php if ($error): ?>
        <div class="bg-rose-50 text-rose-700 border border-rose-200 p-3 rounded-lg text-sm mb-4"><?= $error ?></div>
      <?php endif; ?>

      <?php if ($product): ?>
        <div class="flex items-center gap-4 bg-slate-50 p-4 rounded-xl border border-slate-100 mb-6">
          <img src="<?= htmlspecialchars($product['image']) ?>" class="w-16 h-16 object-cover rounded-lg">
          <div>
            <h4 class="font-bold text-sm text-slate-900"><?= htmlspecialchars($product['title']) ?></h4>
            <p class="text-xs text-[#00A651] font-bold"><?= formatPKR($product['price']) ?></p>
          </div>
        </div>
      <?php endif; ?>

      <form method="POST" action="checkout.php<?= $productId ? '?buy_now=' . urlencode($productId) : '' ?>" class="space-y-4">
        <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['id'] ?? '') ?>">

        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1">Customer Full Name *</label>
          <input type="text" name="customer_name" required placeholder="e.g. Muhammad Bilal Khan" class="w-full px-4 py-2 text-sm border border-slate-300 rounded-lg focus:outline-[#00A651]">
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1">WhatsApp / Mobile Number *</label>
          <input type="tel" name="phone" required placeholder="0300-1234567" class="w-full px-4 py-2 text-sm border border-slate-300 rounded-lg focus:outline-[#00A651]">
        </div>

        <!-- City Text Box (NO Dropdown) -->
        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1">City (Write Your City Name) *</label>
          <input type="text" name="city" required placeholder="e.g. Lahore, Karachi, Islamabad, Multan..." class="w-full px-4 py-2 text-sm border border-slate-300 rounded-lg focus:outline-[#00A651]">
          <span class="text-[10px] text-slate-500">Apna city likhein, koi dropdown nahi hai.</span>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1">Full Delivery Address (House #, Street, Area) *</label>
          <textarea name="address" required rows="3" placeholder="House #, Street #, Mohalla / Sector, Famous Landmark..." class="w-full px-4 py-2 text-sm border border-slate-300 rounded-lg focus:outline-[#00A651]"></textarea>
          <span class="text-[11px] text-slate-500">Yehi address admin panel me courier booking k liye show hoga.</span>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1">Order Notes (Optional)</label>
          <input type="text" name="order_notes" placeholder="e.g. Call before delivery" class="w-full px-4 py-2 text-sm border border-slate-300 rounded-lg focus:outline-[#00A651]">
        </div>

        <button type="submit" name="place_order" class="w-full bg-[#00A651] hover:bg-[#008f45] text-white font-bold py-3 rounded-xl transition text-sm">
          Confirm Order (Cash on Delivery)
        </button>
      </form>
    </div>
  <?php endif; ?>
</div>

</body>
</html>`;

export const PHP_ADMIN_ORDERS = `<?php
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = sanitize($_POST['order_id']);
    $newStatus = sanitize($_POST['status']);
    $trackingNum = sanitize($_POST['tracking_number'] ?? '');
    
    $updateStmt = $pdo->prepare("UPDATE orders SET status = ?, tracking_number = ? WHERE id = ?");
    $updateStmt->execute([$newStatus, $trackingNum, $orderId]);
}

$ordersStmt = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC");
$orders = $ordersStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin - Live Orders & Addresses | PakStore</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-100 text-slate-900 min-h-screen">

  <nav class="bg-slate-900 text-white px-6 py-4 flex items-center justify-between">
    <div class="flex items-center gap-3">
      <span class="bg-[#00A651] px-2 py-0.5 rounded text-xs font-black">ADMIN</span>
      <h1 class="text-lg font-bold">PakStore Control Panel</h1>
    </div>
    <div class="flex items-center gap-4 text-xs font-medium">
      <a href="orders.php" class="text-white border-b-2 border-[#00A651] pb-1">Orders & Addresses</a>
      <a href="products.php" class="text-slate-300 hover:text-white">Products</a>
      <a href="categories.php" class="text-slate-300 hover:text-white">Categories</a>
      <a href="banners.php" class="text-slate-300 hover:text-white">Banners</a>
      <a href="settings.php" class="text-slate-300 hover:text-white">Settings</a>
      <a href="logout.php" class="text-rose-400 hover:text-rose-300">Logout</a>
    </div>
  </nav>

  <main class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h2 class="text-2xl font-bold text-slate-900">Orders & Handwritten Customer Addresses</h2>
        <p class="text-xs text-slate-500">Customer ka handwritten city aur full street address courier dispatch k liye.</p>
      </div>
      <span class="text-xs bg-white border border-slate-300 px-3 py-1.5 rounded-lg font-medium">Total: <?= count($orders) ?> Orders</span>
    </div>

    <div class="space-y-6">
      <?php foreach ($orders as $order): ?>
        <?php
          $itemsStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
          $itemsStmt->execute([$order['id']]);
          $items = $itemsStmt->fetchAll();
          
          $cleanPhone = preg_replace('/[^0-9]/', '', $order['phone']);
          if (strpos($cleanPhone, '03') === 0) {
              $cleanPhone = '92' . substr($cleanPhone, 1);
          }
        ?>
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
          <div class="flex flex-wrap items-center justify-between gap-4 pb-4 border-b border-slate-100">
            <div>
              <span class="font-mono font-bold text-[#00A651] text-sm">#<?= htmlspecialchars($order['id']) ?></span>
              <span class="text-xs text-slate-400 ml-2"><?= htmlspecialchars($order['created_at']) ?></span>
              <span class="ml-2 px-2 py-0.5 rounded text-xs font-semibold
                <?= $order['status'] === 'Delivered' ? 'bg-emerald-100 text-emerald-800' :
                   ($order['status'] === 'Shipped' ? 'bg-blue-100 text-blue-800' :
                   ($order['status'] === 'Processing' ? 'bg-indigo-100 text-indigo-800' : 'bg-amber-100 text-amber-800')) ?>">
                <?= htmlspecialchars($order['status']) ?>
              </span>
            </div>

            <form method="POST" class="flex items-center gap-2">
              <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['id']) ?>">
              <select name="status" class="text-xs border border-slate-300 rounded px-2 py-1 bg-white">
                <option value="Pending" <?= $order['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                <option value="Processing" <?= $order['status'] === 'Processing' ? 'selected' : '' ?>>Processing</option>
                <option value="Shipped" <?= $order['status'] === 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                <option value="Delivered" <?= $order['status'] === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                <option value="Cancelled" <?= $order['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
              </select>
              <button type="submit" name="update_status" class="bg-slate-900 text-white text-xs px-3 py-1 rounded hover:bg-slate-800">
                Update
              </button>
            </form>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4">
            <div class="bg-emerald-50/60 border border-emerald-200 p-4 rounded-xl">
              <span class="text-[11px] font-bold uppercase text-[#00A651] block mb-2">Customer & Delivery Address</span>
              <p class="font-bold text-slate-900 text-sm"><?= htmlspecialchars($order['customer_name']) ?></p>
              
              <div class="mt-2 flex items-center gap-2">
                <span class="text-xs font-medium text-slate-700"><?= htmlspecialchars($order['phone']) ?></span>
                <a href="https://wa.me/<?= $cleanPhone ?>" target="_blank" class="text-[11px] bg-[#00A651] hover:bg-[#008f45] text-white px-2 py-0.5 rounded font-bold">
                  WhatsApp
                </a>
              </div>

              <div class="mt-3 pt-2 border-t border-emerald-200 text-xs">
                <p class="text-slate-500 font-semibold">City: <span class="text-slate-900 font-bold"><?= htmlspecialchars($order['city']) ?></span></p>
                <p class="text-slate-500 font-semibold mt-1">Full Street Address:</p>
                <p class="text-slate-900 font-medium bg-white p-2 rounded border border-emerald-200 mt-1">
                  <?= nl2br(htmlspecialchars($order['address'])) ?>
                </p>
              </div>
            </div>

            <div class="md:col-span-2 flex flex-col justify-between">
              <div>
                <span class="text-[11px] font-bold uppercase text-slate-400 block mb-2">Order Items</span>
                <div class="space-y-2">
                  <?php foreach ($items as $item): ?>
                    <div class="flex items-center justify-between text-xs py-1.5 border-b border-slate-100">
                      <div class="flex items-center gap-2">
                        <img src="<?= htmlspecialchars($item['image']) ?>" class="w-8 h-8 rounded object-cover">
                        <span class="font-medium text-slate-800"><?= htmlspecialchars($item['title']) ?></span>
                        <span class="text-slate-400">× <?= $item['quantity'] ?></span>
                      </div>
                      <span class="font-bold text-slate-900"><?= formatPKR($item['price'] * $item['quantity']) ?></span>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>

              <div class="mt-4 pt-3 border-t border-slate-200 flex items-center justify-between text-sm font-bold">
                <span class="text-slate-600">Total COD Cash to Collect:</span>
                <span class="text-[#00A651] text-base font-black"><?= formatPKR($order['total_amount']) ?></span>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </main>
</html>`;

export const PHP_ADMIN_ADD_PRODUCT = `<?php
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$success = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $title = sanitize($_POST['title']);
    $category = sanitize($_POST['category']);
    $price = intval($_POST['price']);
    $originalPrice = !empty($_POST['original_price']) ? intval($_POST['original_price']) : null;
    $stock = intval($_POST['stock']);
    $sku = sanitize($_POST['sku']);
    $image = sanitize($_POST['image']);
    $badge = sanitize($_POST['badge']);
    $desc = sanitize($_POST['description']);
    $id = 'prd-' . rand(100, 999);

    if (empty($title) || $price <= 0 || empty($image)) {
        $error = "Title, valid price, and image URL are required!";
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO products (id, title, category, price, original_price, stock, image, badge, description, sku, in_stock)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
        ");
        $stmt->execute([$id, $title, $category, $price, $originalPrice, $stock, $image, $badge, $desc, $sku]);
        $success = "Product added successfully to MySQL database!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Product - PakStore Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 text-slate-900 min-h-screen p-6">
  <div class="max-w-xl mx-auto bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
    <h2 class="text-xl font-bold mb-4">Add Product</h2>
    <?php if ($success) echo "<p class='text-emerald-700 text-xs mb-2'>$success</p>"; ?>
    <form method="POST" class="space-y-3 text-xs">
      <div><label>Title *</label><input type="text" name="title" required class="w-full border p-2 rounded"></div>
      <div><label>Category *</label><input type="text" name="category" required class="w-full border p-2 rounded"></div>
      <div><label>Price (PKR) *</label><input type="number" name="price" required class="w-full border p-2 rounded"></div>
      <div><label>Image URL *</label><input type="url" name="image" required class="w-full border p-2 rounded"></div>
      <button type="submit" name="add_product" class="bg-[#00A651] text-white py-2 px-4 rounded font-bold">Save</button>
    </form>
  </div>
</body>
</html>`;

export const PHP_ADMIN_LOGIN = `<?php
require_once '../config.php';

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Strict MySQL query against admin_users table
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_email'] = $admin['email'];
        header("Location: orders.php");
        exit;
    } else {
        $error = "Access Denied: Ghalat Admin Email ya Password. Database me set kiya hua credential darj karein.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Login | PakStore Pakistan</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-900 text-white min-h-screen flex items-center justify-center p-4">
  <div class="bg-slate-800 border border-slate-700 rounded-2xl max-w-sm w-full p-6 shadow-2xl">
    <div class="text-center mb-6">
      <span class="bg-[#00A651] text-white text-xs font-black px-2.5 py-0.5 rounded">PAKSTORE ADMIN</span>
      <h2 class="text-xl font-bold mt-2 text-white">Admin Control Portal</h2>
      <p class="text-xs text-slate-400 mt-1">Apna MySQL database me set kiya hua Admin Email aur Password enter karein.</p>
    </div>

    <?php if ($error): ?>
      <div class="p-3 mb-4 text-xs bg-rose-950 text-rose-300 border border-rose-800 rounded-lg">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" class="space-y-4 text-xs">
      <div>
        <label class="block font-semibold mb-1 text-slate-300">Admin Email *</label>
        <input type="email" name="email" required placeholder="admin@pakstore.com" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white font-mono focus:outline-[#00A651]">
      </div>
      <div>
        <label class="block font-semibold mb-1 text-slate-300">Admin Password *</label>
        <input type="password" name="password" required placeholder="Enter password" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white focus:outline-[#00A651]">
      </div>
      <button type="submit" name="login" class="w-full bg-[#00A651] hover:bg-[#008f45] text-white font-bold py-2.5 rounded-xl transition text-xs shadow-md cursor-pointer">
        Secure Login to Dashboard
      </button>
    </form>
  </div>
</body>
</html>`;

export const INFINITYFREE_GUIDE = `# Complete InfinityFree PHP & MySQL Setup Guide for PakStore

Follow these steps to host your PakStore website on your InfinityFree domain:

## Step 1: Create MySQL Database on InfinityFree
1. Log in to your InfinityFree Account Dashboard (app.infinityfree.net).
2. Open your account and enter **vPanel (Control Panel)**.
3. Click **MySQL Databases** under Databases.
4. Enter database name: \`pakstore\` and click **Create Database**.
5. Note your details:
   - MySQL Hostname (e.g. \`sql300.infinityfree.com\`)
   - MySQL Username (e.g. \`if0_38123456\`)
   - MySQL Password (your vPanel password)
   - MySQL Database Name (e.g. \`if0_38123456_pakstore\`)

## Step 2: Import \`pakstore_db.sql\` into phpMyAdmin
1. In vPanel, click **phpMyAdmin** and click **Connect**.
2. Select your \`pakstore\` database from the left menu.
3. Click on the **Import** tab.
4. Click **Choose File** and select \`pakstore_db.sql\`.
5. Click **Go** at the bottom.

## Step 3: Configure \`config.php\`
Open \`config.php\` and put your database credentials:
\`\`\`php
define('DB_HOST', 'sqlXXX.infinityfree.com');
define('DB_USER', 'if0_XXXXXXXX');
define('DB_PASS', 'YOUR_VPANEL_PASSWORD');
define('DB_NAME', 'if0_XXXXXXXX_pakstore');
\`\`\`

## Step 4: Upload to InfinityFree \`htdocs\`
1. In vPanel, open **Online File Manager** (Monsta FTP).
2. Double click \`htdocs\`.
3. Upload \`config.php\`, \`index.php\`, \`checkout.php\`, \`cart.php\`, \`track_order.php\`.
4. Create folder \`admin\` and upload \`admin/orders.php\`, \`admin/products.php\`, \`admin/categories.php\`, \`admin/banners.php\`, \`admin/settings.php\`, \`admin/login.php\`.

## Step 5: Admin Login Credentials
- Admin URL: \`https://yourdomain.infinityfreeapp.com/admin/login.php\`
- Email: **admin@pakstore.com**
- Password: **admin123**
`;
