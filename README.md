# PakStore - PHP + MySQL (InfinityFree Ready)

PakStore is a complete Cash on Delivery e-commerce store built with **pure PHP, MySQL, HTML, CSS and vanilla JavaScript**. There is no React, no Node.js, no npm and no build step. Upload the files to InfinityFree `htdocs`, import the SQL file, set your database credentials, and the store is live.

The store starts **empty** - no demo products, categories, banners or orders. Only the store settings and one admin account are seeded. Products, categories and orders appear only when you add them from the admin panel or customers place real orders.

---

## 1. Requirements

- PHP **8.0 or newer** (InfinityFree supports PHP 8.x)
- MySQL / MariaDB (InfinityFree includes MySQL)
- Apache with `.htaccess` support (InfinityFree provides this)
- No Composer, Node.js or build tools required

---

## 2. Quick Install on InfinityFree

### Step 1 - Create the MySQL database

1. Log in to the **InfinityFree Control Panel**.
2. Open **MySQL Databases**.
3. Create a database, for example `pakstore`.
4. Note down the four values shown by InfinityFree:
   - **MySQL Host** (e.g. `sql301.infinityfree.com`)
   - **Database Name** (e.g. `if0_12345678_pakstore`)
   - **Database User** (e.g. `if0_12345678`)
   - **Database Password** (the password you set)

### Step 2 - Import the database

1. In the Control Panel open **phpMyAdmin** for your new database.
2. Select the database on the left.
3. Click the **Import** tab.
4. Choose the `database.sql` file from this project and click **Go**.
5. This creates these tables:
   - `users` (customers)
   - `products`
   - `categories`
   - `orders`
   - `order_items`
   - `settings`
   - `admin_users`
   - `banners`
   - `reviews`

### Step 3 - Configure the database connection

1. Open `config/config.local.example.php`.
2. Copy/rename it to `config/config.local.php`.
3. Fill in your real InfinityFree values:

```php
<?php
define('DB_HOST', 'sql301.infinityfree.com');
define('DB_NAME', 'if0_12345678_pakstore');
define('DB_USER', 'if0_12345678');
define('DB_PASS', 'YOUR_REAL_DATABASE_PASSWORD');

// Leave empty when the site is directly in htdocs.
define('APP_BASE', '');

define('APP_DEBUG', false);
```

> If you upload the project into a sub-folder (for example `htdocs/shop`), set `APP_BASE` to `'/shop'`.

### Step 4 - Upload the files

1. Open **File Manager** in the InfinityFree Control Panel (or use FTP).
2. Go to the `htdocs` folder.
3. Upload **all project files** so that `index.php` is directly inside `htdocs`.
4. Make sure these folders exist and are writable (**755**):
   - `uploads/`
   - `uploads/products/`
   - `uploads/logo/`
   - `uploads/banners/`

The `assets/` folder and `config/` folder should also be uploaded. Do **not** upload `database.sql` if you prefer, but it is harmless because `.htaccess` blocks direct access to it.

### Step 5 - Open the store

- Storefront: `https://your-domain.com/`
- Admin panel: `https://your-domain.com/admin/`

---

## 3. Default Admin Login

| Field | Value |
|---|---|
| URL | `/admin/login.php` |
| Email | `admin@pakstore.com` |
| Password | `Admin@12345` |

**Change this password immediately** after your first login:

`Admin -> Settings -> Change My Password`

The seeded password hash is SHA-256. On the first successful login it is automatically upgraded to a secure **bcrypt** hash. All new passwords use `password_hash()` with `PASSWORD_DEFAULT`.

---

## 4. Features

### Storefront
- Homepage with database-driven hero banner slider (falls back to a clean gradient when no banners exist)
- Shop catalog with category filter, search and sorting (featured, price, newest)
- Product detail page with image gallery, discount %, stock status, feature list, real reviews (if any), share buttons and related products
- Shopping cart stored in **PHP sessions** (prices and stock are always re-read from MySQL)
- Cash on Delivery checkout with server-side validation
- Automatic order code generation (e.g. `PK-4821`), stock reduction and transaction safety
- Order confirmation page with printable receipt and optional WhatsApp message
- Order tracking by order code or phone number with a status timeline
- Customer registration, login, saved delivery address, order history and password change
- Session wishlist
- Fully responsive layout, mobile bottom navigation, Tailwind CSS via CDN and Lucide icons
- Brand colors preserved: green `#00A651`, orange `#FF6B00`

### Admin panel
- Secure admin login with CSRF protection and session regeneration
- Dashboard with total sales, orders, products, customers and pending orders
- Orders management: search, filter by status, update **Pending / Processing / Shipped / Delivered / Cancelled**, courier name, tracking number, delete
- Cancelling an order automatically returns its items to stock
- Printable A5 courier slip / packing label
- Products: add, edit, delete, image upload (JPG/PNG/WEBP/GIF, max 3 MB), gallery, features, SKU, stock, badge, compare-at price
- Categories: add, edit, delete; products become uncategorized when a category is deleted
- Hero banners: upload image, subtitle, badge, link, sort order, active/hidden
- Customers: list, search, order count, total spent, delete
- Analytics: sales today / 7 days / 30 days, average order, fulfilment rate, best sellers, sales by city and a 14-day revenue chart
- Settings: store name, logo upload, phone, WhatsApp, email, delivery charges, announcement bar
- Admin account management and password change

---

## 5. Project Structure

```
/
├── index.php                  Homepage + shop catalog + search + category filter
├── product.php                Product detail page
├── cart.php                   Session cart page + cart actions
├── checkout.php               Cash on Delivery checkout
├── order-success.php          Order confirmation + printable receipt
├── track-order.php            Public order tracking
├── account.php                Customer login / signup / profile / order history
├── wishlist.php               Session wishlist
├── database.sql               Full MySQL schema + settings/admin seed
├── .htaccess                  Apache hardening
├── config/
│   ├── config.php             App constants
│   ├── config.local.example.php  Copy to config.local.php and edit
│   └── db.php                 PDO connection (prepared statements)
├── includes/
│   ├── functions.php          Helpers, auth, cart, CSRF, uploads, orders
│   ├── header.php             Storefront header, drawers, navbar
│   └── footer.php             Footer + mobile navigation
├── admin/
│   ├── login.php              Admin sign-in
│   ├── logout.php             Admin sign-out (POST + CSRF)
│   ├── index.php              Dashboard router (?tab=...)
│   ├── product-edit.php       Add / edit product form
│   ├── products.php           Product save/delete action
│   ├── categories.php         Category action
│   ├── orders.php             Order update/delete action
│   ├── banners.php            Banner action
│   ├── users.php              Customer delete action
│   ├── settings.php           Settings + admin account action
│   ├── order-slip.php         Printable courier slip
│   ├── includes/              Admin auth, header, footer
│   └── tabs/                  Dashboard, Orders, Products, Categories,
│                              Banners, Customers, Analytics, Settings
├── assets/
│   ├── css/style.css          Small additions on top of Tailwind
│   ├── js/main.js             Drawers, slider, gallery, quantity steppers
│   └── images/                Existing store images + placeholder
└── uploads/
    ├── products/              Uploaded product images
    ├── logo/                  Uploaded store logo
    └── banners/               Uploaded hero banners
```

---

## 6. How Data and Security Work

- **No fake data**: every product, category, banner and order is read from MySQL. When the products table is empty the storefront shows **"No products found"**.
- **SQL injection protection**: all dynamic queries use PDO prepared statements.
- **XSS protection**: all output is escaped with `htmlspecialchars()` through the `e()` helper.
- **CSRF protection**: every POST form includes a token and calls `csrf_verify()`.
- **Passwords**: `password_hash()` / `password_verify()`; seeded SHA-256 admin hash upgrades to bcrypt on first login.
- **Sessions**: HttpOnly, SameSite=Lax cookies; session ID regenerated on login.
- **Uploads**: MIME type + image validation, 3 MB limit, random file names, PHP execution blocked inside `uploads/`.
- **Stock safety**: checkout uses a database transaction and `SELECT ... FOR UPDATE`; stock is decremented atomically.
- **Order totals**: recalculated on the server from database prices - never trusted from the browser.

---

## 7. First-Time Setup Checklist

1. Import `database.sql` in phpMyAdmin.
2. Create `config/config.local.php` with real DB credentials.
3. Upload everything to `htdocs`.
4. Set `uploads/` folders to `755`.
5. Open `/admin/login.php` and sign in with `admin@pakstore.com` / `Admin@12345`.
6. Change the admin password.
7. Open **Settings** and update store name, phone, WhatsApp, email and delivery charges.
8. Add categories first, then products, then optional hero banners.
9. Place a test COD order from the storefront and update its status from the admin panel.

---

## 8. InfinityFree Notes

- **No SSH/SFTP on free hosting**: use the File Manager or FTP (FileZilla) to upload files.
- **Database host** is not `localhost` on InfinityFree. Use the exact host shown in MySQL Databases, e.g. `sql301.infinityfree.com`.
- **Database name and username** usually start with `if0_`.
- **Free hosting limits**: InfinityFree may show a JavaScript challenge or daily hit limits on free accounts. This is a hosting limitation, not a store error.
- **HTTPS**: enable the free SSL certificate in the Control Panel, then use the HTTPS URL.
- **Permissions**: if image uploads fail, set `uploads/` and its sub-folders to `755`. Some hosts require `775`; never use `777`.
- **Emails**: InfinityFree free hosting does not reliably send email. This store does not depend on email - order updates are shown in the admin panel and via WhatsApp links.
- **Error display**: keep `APP_DEBUG` set to `false` in production. Errors are written to the PHP error log instead of the page.

---

## 9. Troubleshooting

### "Database connection failed"
- Check `config/config.local.php` exists.
- Confirm the host is the InfinityFree MySQL host, not `localhost`.
- Confirm database name, user and password exactly match the Control Panel.
- Make sure the database has been created and `database.sql` imported.

### Admin login does not work
- Confirm `database.sql` was imported, especially the `admin_users` row.
- Email: `admin@pakstore.com`, Password: `Admin@12345`.
- If you changed the password and forgot it, run this in phpMyAdmin to reset it:

```sql
UPDATE admin_users
SET password_hash = '6f2cb9dd8f4b65e24e1c3f3fa5bc57982349237f11abceacd45bbcb74d621c25'
WHERE email = 'admin@pakstore.com';
```

Then log in with `Admin@12345` and change it again.

### Images do not upload
- Check `uploads/products/` exists and is writable (`755`).
- Check the file is under 3 MB and is JPG, PNG, WEBP or GIF.
- On InfinityFree, the PHP upload limit may be lower than 3 MB. Compress large images.

### CSS or icons missing
- The design uses Tailwind CSS and Lucide icons from public CDNs. An active internet connection is required in the browser.
- If your region blocks the CDN, download Tailwind/Lucide locally and update `includes/header.php` and `admin/includes/header.php`.

### Wrong links after uploading to a sub-folder
- Set `APP_BASE` in `config/config.local.php` to your sub-folder, for example `define('APP_BASE', '/shop');`.

---

## 10. Customisation

- **Brand colors**: search for `#00A651` and `#FF6B00` in the PHP files, `assets/css/style.css` and the Tailwind config block.
- **Delivery charges**: Admin -> Settings -> Delivery Charges (set `0` for free delivery).
- **Store logo**: Admin -> Settings -> Upload Logo. If no logo is set, the text logo `PakStore` is used.
- **Hero banners**: Admin -> Banners. Banners are optional; the homepage shows a gradient hero when none are active.
- **Products**: Admin -> Products -> Add New Product. Categories must be created first, or products can stay uncategorized.

---

## 11. License

This project is provided for the PakStore store owner. You may modify and deploy it on your own hosting.
