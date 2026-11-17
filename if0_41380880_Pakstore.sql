-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql100.byetcluster.com
-- Generation Time: Nov 17, 2026 at 07:11 AM
-- Server version: 11.4.13-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_41380880_Pakstore`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(10) UNSIGNED NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('SuperAdmin','StoreManager') NOT NULL DEFAULT 'SuperAdmin',
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `email`, `password_hash`, `role`, `last_login`, `created_at`) VALUES
(1, 'admin@pakstore.com', '$2y$12$0we0nthjjbcPKehiTy.VyuVzSkXPnFUcRLaE6.Z5ijQfY6Jz54Q/G', 'SuperAdmin', '2026-11-06 09:54:42', '2026-11-06 09:29:17'),
(2, 'alijaan122ah@gmail.com', '$2y$12$uHtUc1m3TA5DJ3d6DyfVRee/OIsZY.5oPEjff0p10sFPeFBv3ctwe', 'SuperAdmin', '2026-11-16 20:38:17', '2026-11-06 09:57:08');

-- --------------------------------------------------------

--
-- Table structure for table `banners`
--

CREATE TABLE `banners` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `subtitle` varchar(300) DEFAULT NULL,
  `image` varchar(500) NOT NULL DEFAULT '',
  `image_width` int(10) UNSIGNED DEFAULT NULL,
  `image_height` int(10) UNSIGNED DEFAULT NULL,
  `category` varchar(120) DEFAULT NULL,
  `badge` varchar(80) DEFAULT NULL,
  `link` varchar(300) DEFAULT '#catalog-section',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `banners`
--

INSERT INTO `banners` (`id`, `title`, `subtitle`, `image`, `image_width`, `image_height`, `category`, `badge`, `link`, `sort_order`, `is_active`, `created_at`) VALUES
(6, 'Sale 11 11', NULL, 'uploads/banners/banner-20261107093012-f56eb0bac0.jpg', NULL, NULL, NULL, NULL, '#catalog-section', 0, 1, '2026-11-06 20:30:13');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `slug` varchar(140) NOT NULL,
  `icon` varchar(60) NOT NULL DEFAULT 'tag',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `created_at`) VALUES
(1, 'Fashion', 'polo-shirt', 'shirt', '2026-11-06 10:50:40'),
(2, 'Electronics', 'smart-watch', 'headphones', '2026-11-15 01:36:48'),
(3, 'Beauty', 'beauty', 'sparkles', '2026-11-15 02:25:20');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_code` varchar(24) NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `customer_name` varchar(150) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `email` varchar(190) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `order_notes` text DEFAULT NULL,
  `payment_method` varchar(40) NOT NULL DEFAULT 'Cash on Delivery',
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `shipping_fee` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('Pending','Processing','Shipped','Delivered','Cancelled') NOT NULL DEFAULT 'Pending',
  `tracking_courier` varchar(60) DEFAULT 'TCS',
  `tracking_number` varchar(120) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `image` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `original_price` decimal(12,2) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `image` varchar(500) NOT NULL DEFAULT '',
  `gallery` text DEFAULT NULL,
  `badge` varchar(40) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `features` text DEFAULT NULL,
  `sku` varchar(80) NOT NULL DEFAULT '',
  `in_stock` tinyint(1) NOT NULL DEFAULT 1,
  `rating` decimal(2,1) NOT NULL DEFAULT 0.0,
  `reviews_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `title`, `category_id`, `price`, `original_price`, `stock`, `image`, `gallery`, `badge`, `description`, `features`, `sku`, `in_stock`, `rating`, `reviews_count`, `created_at`, `updated_at`) VALUES
(1, 'Men\'s Color Block Polo Shirt - Black Olive Summer', 1, '2499.00', '3499.00', 28, 'uploads/products/product-20261106235559-1dfff179f6.jpg', NULL, 'Sale', 'Stay cool and look sharp this summer with our Men\'s Color Block Polo Shirt in Black Olive. Designed for the modern Pakistani man who values both style and comfort.\r\n\r\nPerfect for daily wear, office, ya casual outings ke liye. Breathable fabric Pakistan ki garmi me bhi comfortable rakhta hai aur durable stitching long-lasting wear deti hai.\r\n\r\nFeatures:\r\n- Durable stitching for long-lasting wear\r\n- Stylish color-block design with olive green panel\r\n- Lightweight, breathable fabric perfect for summer\r\n- Classic polo collar with button closure\r\n- Front chest pocket with subtle branding\r\n- Ideal for office, casual, ya semi-formal settings\r\n\r\nProduct Details:\r\nSeason: Summer\r\nTarget: Men\r\nStyle: Fashionable\r\nCondition: Latest / New Collection\r\n\r\nCash on Delivery All Over Pakistan - Parcel khol ke check karein, phir payment karein.', NULL, 'MZCHYHAM2417', 1, '0.0', 0, '2026-11-06 10:56:00', '2026-11-15 02:33:00'),
(2, 'D20 Bluetooth Smart Watch Men Waterproof Sport Fitness', 2, '1999.00', NULL, 35, 'uploads/products/product-20261115143659-c81713cf07.jpg', NULL, NULL, 'Material: ABS Plastic\r\nConnectivity Tech: Bluetooth\r\nBluetooth Version: Bluetooth 4.2\r\nProduct Feature: Long Battery Life, Voice Assistant\r\nColor: Black, Multicolor\r\nPackage Includes: 1 x Smart Watch\r\n1 Model Number\r\n\r\n\r\nNote: Please ensure to follow the instructions provided in the user manual for proper usage and safety precautions.\r\n\r\nProduct Code: MZ2592200059KNBD', NULL, 'PAK-996D6329', 1, '0.0', 0, '2026-11-15 01:36:59', '2026-11-15 01:36:59'),
(3, 'SNBEI TWS Wireless Bluetooth Earbuds', 2, '1200.00', NULL, 55, 'uploads/products/product-20261115151325-43ae724d36.jpg', '[\"uploads/products/product-20261115151325-6fb8a6dabf.jpg\"]', NULL, 'Material: ABS Plastic\r\nConnectivity Tech: Bluetooth\r\nBluetooth Version: Bluetooth 5\r\nProduct Feature: Noise-Cancelling Earbuds, Dust & Water Proof\r\nColor: Green, White\r\nPackage Includes: 1 x Earbuds, 1 x Charger Cable\r\n5 Model Number\r\nBattery Capacity: 2000 mAh\r\nCall Time: 12 Hours\r\nStandby Time: 12 Hours\r\nVoltage: 5 Volts\r\nWatts: 5 Watt\r\nWeight: 29 Gram\r\nLength: 33 Inches\r\nWidth: 20 Inches\r\nHeight: 19 Inches\r\n\r\n\r\nNote: Please ensure to follow the instructions provided in the user manual for proper usage and safety precautions.\r\n\r\nProduct Code: MZ2674200204OKS', NULL, 'PAK-AC95F3CF', 1, '0.0', 0, '2026-11-15 02:13:26', '2026-11-15 02:13:26'),
(4, 'TWS Wireless Bluetooth Earbuds with Noise Isolating Design', 2, '1200.00', NULL, 45, 'uploads/products/product-20261115151713-e498b61cfa.jpg', '[\"uploads/products/product-20261115151713-0f69c72bbb.jpg\"]', NULL, 'Material: ABS Plastic\r\nConnectivity Tech: Bluetooth\r\nBluetooth Version: Bluetooth 5.1\r\nProduct Feature: Noise-Cancelling Earbuds, Dust & Water Proof\r\nColor: Black\r\nPackage Includes: 1 x Earbuds, 1 x Charger Cable\r\n2 Model Number\r\nBattery Capacity: 2200 mAh\r\nCall Time: 12 Hours\r\nStandby Time: 22 Hours\r\nVoltage: 5 Volts\r\nWatts: 5 Watt\r\nWeight: 33 Gram\r\nLength: 22 Inches\r\nWidth: 24 Inches\r\nHeight: 18 Inches\r\n\r\n\r\nNote: Please ensure to follow the instructions provided in the user manual for proper usage and safety precautions.\r\n\r\nProduct Code: MZ2674200201OKS', NULL, 'PAK-9ED83172', 1, '0.0', 0, '2026-11-15 02:17:14', '2026-11-15 02:17:14'),
(5, 'Kashee\'s Rice Face Wash 150ml Whitening Brightening', 3, '1100.00', NULL, 66, 'uploads/products/product-20261115152615-f05643eb43.jpg', '[\"uploads/products/product-20261115152615-bc7b2e431d.jpg\",\"uploads/products/product-20261115152615-977f789af6.jpg\",\"uploads/products/product-20261115152615-54548dc728.jpg\"]', NULL, 'Kashee’s Rice Skin Glowing Face Wash is your perfect solution for achieving radiant, healthy-looking skin without harsh chemicals. Designed for Pakistani weather and skin types, this premium quality face wash gently removes dirt, excess oil, and impurities while nourishing your skin with rice extract. It’s ideal for daily use, whether you’re heading to work or preparing for a special occasion.\r\n\r\nThis face wash is crafted with a creamy texture that lathers beautifully, leaving your skin feeling soft and refreshed. The rice-based formula is known for its moisturizing and anti-aging properties, helping to brighten your complexion over time. It’s durable in formulation and designed to work effectively without drying out your skin, making it suitable for all skin types. The stylish packaging and new arrival status make it a smart choice for your beauty routine.\r\n\r\n- Whitening and brightening properties for a glowing complexion\r\n- Deeply cleanses without drying out your skin\r\n- Moisturizing and anti-aging benefits from rice extract\r\n- Suitable for all skin types, including sensitive skin\r\n- 150ml size for long-lasting use\r\n- Premium quality formula with natural ingredients\r\n*Product Details*:Material: Cream\r\nProduct Feature: Whitening And Brightening\r\nPackage Includes: 1 x Face Wash\r\n\r\n\r\nNote: Before using any new cosmetic product, perform a patch test on a small skin area. Verify ingredients for allergies and follow package warnings and directions.\r\n\r\nProduct Code: MZ1347200490CNCS', NULL, 'PAK-1F3E8FE7', 1, '0.0', 0, '2026-11-15 02:26:16', '2026-11-15 02:26:27');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `author` varchar(150) NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL DEFAULT 5,
  `comment` text DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `setting_key` varchar(80) NOT NULL,
  `setting_value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('announcement', 'Cash on Delivery All Over Pakistan'),
('delivery_charges', '0'),
('email', 'support@pakstore.com'),
('logo_url', 'uploads/logo/logo-20261106232004-3c329cf3fe.png'),
('phone', '0300-1234567'),
('store_name', 'PakStore'),
('top_bar_text', 'Cash on Delivery All Over Pakistan'),
('whatsapp', '923063159899');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL DEFAULT '',
  `address` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `phone`, `email`, `password_hash`, `city`, `address`, `created_at`) VALUES
(1, 'Riaz Hussain arain', '03221137341', 'mralijaan690@gmail.com', '$2y$12$PVBLJjkcRsHUQj6Goc4AQudNegCqa/9lDH1CqN3ixTIB5rSHIgjvC', 'Pano aqil', 'Sukkur Sindh PANO AQIL Pakistan\r\npano aqil', '2026-11-06 10:58:36'),
(2, 'Saeed Abbas', '03272923149', 'msae367sa@gmail.com', '$2y$12$IqzGdxwFsiAAgjttpSkWJ..CxqUHLrlhUt0IfVqG2t1ZnAR4HP18S', 'Khan pur', 'Khan pur', '2026-11-15 03:58:16');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_admin_email` (`email`);

--
-- Indexes for table `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_categories_slug` (`slug`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_orders_code` (`order_code`),
  ADD KEY `idx_orders_status` (`status`),
  ADD KEY `idx_orders_phone` (`phone`),
  ADD KEY `idx_orders_user` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_items_order` (`order_id`),
  ADD KEY `idx_order_items_product` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_products_sku` (`sku`),
  ADD KEY `idx_products_category` (`category_id`),
  ADD KEY `idx_products_created` (`created_at`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_reviews_product` (`product_id`),
  ADD KEY `fk_reviews_user` (`user_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_email` (`email`),
  ADD KEY `idx_users_phone` (`phone`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `banners`
--
ALTER TABLE `banners`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_reviews_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
