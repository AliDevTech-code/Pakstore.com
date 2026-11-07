-- ============================================================
--  PakStore - MySQL schema for InfinityFree
--  Import this file once from phpMyAdmin (Control Panel -> MySQL).
--  The store starts EMPTY (no demo products / categories / orders).
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- Admin accounts
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(190) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('SuperAdmin','StoreManager') NOT NULL DEFAULT 'SuperAdmin',
  `last_login` DATETIME NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admin_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Customer accounts
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(30) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `city` VARCHAR(100) NOT NULL DEFAULT '',
  `address` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`),
  KEY `idx_users_phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Categories
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `slug` VARCHAR(140) NOT NULL,
  `icon` VARCHAR(60) NOT NULL DEFAULT 'tag',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_categories_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Products
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(200) NOT NULL,
  `category_id` INT UNSIGNED NULL DEFAULT NULL,
  `price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `original_price` DECIMAL(12,2) NULL DEFAULT NULL,
  `stock` INT NOT NULL DEFAULT 0,
  `image` VARCHAR(500) NOT NULL DEFAULT '',
  `gallery` TEXT NULL,
  `badge` VARCHAR(40) NULL DEFAULT NULL,
  `description` TEXT NULL,
  `features` TEXT NULL,
  `sku` VARCHAR(80) NOT NULL DEFAULT '',
  `in_stock` TINYINT(1) NOT NULL DEFAULT 1,
  `rating` DECIMAL(2,1) NOT NULL DEFAULT 0.0,
  `reviews_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_products_sku` (`sku`),
  KEY `idx_products_category` (`category_id`),
  KEY `idx_products_created` (`created_at`),
  CONSTRAINT `fk_products_category`
    FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Orders
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_code` VARCHAR(24) NOT NULL,
  `user_id` INT UNSIGNED NULL DEFAULT NULL,
  `customer_name` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(30) NOT NULL,
  `email` VARCHAR(190) NULL DEFAULT NULL,
  `city` VARCHAR(100) NOT NULL,
  `address` TEXT NOT NULL,
  `order_notes` TEXT NULL,
  `payment_method` VARCHAR(40) NOT NULL DEFAULT 'Cash on Delivery',
  `subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `shipping_fee` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `total_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `status` ENUM('Pending','Processing','Shipped','Delivered','Cancelled') NOT NULL DEFAULT 'Pending',
  `tracking_courier` VARCHAR(60) NULL DEFAULT 'TCS',
  `tracking_number` VARCHAR(120) NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_orders_code` (`order_code`),
  KEY `idx_orders_status` (`status`),
  KEY `idx_orders_phone` (`phone`),
  KEY `idx_orders_user` (`user_id`),
  CONSTRAINT `fk_orders_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Order items
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NULL DEFAULT NULL,
  `title` VARCHAR(200) NOT NULL,
  `price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `quantity` INT UNSIGNED NOT NULL DEFAULT 1,
  `image` VARCHAR(500) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_order_items_order` (`order_id`),
  KEY `idx_order_items_product` (`product_id`),
  CONSTRAINT `fk_order_items_order`
    FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_order_items_product`
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Store settings (key/value)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
  `setting_key` VARCHAR(80) NOT NULL,
  `setting_value` TEXT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Hero banners
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `banners` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(200) NOT NULL,
  `subtitle` VARCHAR(300) NULL DEFAULT NULL,
  `image` VARCHAR(500) NOT NULL DEFAULT '',
  `image_width` INT UNSIGNED NULL DEFAULT NULL,
  `image_height` INT UNSIGNED NULL DEFAULT NULL,
  `category` VARCHAR(120) NULL DEFAULT NULL,
  `badge` VARCHAR(80) NULL DEFAULT NULL,
  `link` VARCHAR(300) NULL DEFAULT '#catalog-section',
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Product reviews / star ratings (optional real data)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NULL DEFAULT NULL,
  `author` VARCHAR(150) NOT NULL,
  `city` VARCHAR(100) NULL DEFAULT NULL,
  `rating` TINYINT UNSIGNED NOT NULL DEFAULT 5,
  `comment` TEXT NULL,
  `is_verified` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_reviews_product` (`product_id`),
  CONSTRAINT `fk_reviews_product`
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_reviews_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
--  Default settings (no products / categories / orders)
-- ============================================================
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('store_name', 'PakStore'),
('logo_url', ''),
('top_bar_text', 'Cash on Delivery All Over Pakistan'),
('phone', '0300-1234567'),
('whatsapp', '923001234567'),
('email', 'support@pakstore.com'),
('delivery_charges', '199'),
('announcement', 'Cash on Delivery All Over Pakistan')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);

-- ============================================================
--  Default admin account
--  Email:    admin@pakstore.com
--  Password: Admin@12345
--
--  IMPORTANT: change this password immediately after the first login
--  (Admin -> Logo & Settings -> Admin Login & Access Control).
--  The hash below is SHA-256 and is automatically upgraded to a
--  bcrypt hash the first time you sign in.
-- ============================================================
INSERT INTO `admin_users` (`email`, `password_hash`, `role`) VALUES
('admin@pakstore.com', '6f2cb9dd8f4b65e24e1c3f3fa5bc57982349237f11abceacd45bbcb74d621c25', 'SuperAdmin')
ON DUPLICATE KEY UPDATE `email` = `email`;
