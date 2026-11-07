<?php
/** Product create / update / delete actions. */

require_once __DIR__ . '/includes/auth.php';
require_post();
csrf_verify();

$action = post_string('action');

if ($action === 'delete') {
    $id = post_int('id');
    $stmt = db()->prepare('DELETE FROM products WHERE id = ?');
    $stmt->execute([$id]);
    flash('success', 'Product deleted.');
    redirect('admin/index.php?tab=products');
}

if ($action === 'save') {
    $id            = post_int('id');
    $title         = post_string('title');
    $categoryId    = post_int('category_id') ?: null;
    $price         = (float) post_string('price');
    $originalPrice = post_string('original_price') !== '' ? (float) post_string('original_price') : null;
    $stock         = max(0, post_int('stock'));
    $sku           = post_string('sku');
    $badge         = post_string('badge');
    $description   = post_string('description');
    $features      = post_string('features');
    $galleryText   = post_string('gallery');
    $imageUrl      = post_string('image_url');
    $inStock       = $stock > 0 ? 1 : 0;

    $errors = [];
    if (mb_strlen($title) < 3) {
        $errors[] = 'Product title is required (min 3 characters).';
    }
    if ($price < 0) {
        $errors[] = 'Price cannot be negative.';
    }
    if ($originalPrice !== null && $originalPrice < $price) {
        $errors[] = 'Original (compare-at) price must be greater than the selling price.';
    }
    if ($categoryId !== null) {
        $check = db()->prepare('SELECT id FROM categories WHERE id = ? LIMIT 1');
        $check->execute([$categoryId]);
        if (!$check->fetchColumn()) {
            $categoryId = null;
        }
    }

    if ($sku === '') {
        $sku = 'PAK-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
    }

    $dup = db()->prepare('SELECT id FROM products WHERE sku = ? AND id <> ? LIMIT 1');
    $dup->execute([$sku, $id]);
    if ($dup->fetchColumn()) {
        $errors[] = 'That SKU is already used by another product.';
    }

    if ($errors) {
        flash('error', implode(' ', $errors));
        redirect('admin/product-edit.php' . ($id > 0 ? '?id=' . $id : ''));
    }

    $existingImage = '';
    if ($id > 0) {
        $stmt = db()->prepare('SELECT image FROM products WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $existingImage = (string) ($stmt->fetchColumn() ?: '');
    }

    $image = $imageUrl !== '' ? $imageUrl : $existingImage;

    if (!empty($_FILES['image_file']['name'])) {
        [$ok, $result] = upload_image($_FILES['image_file'], 'products', 'product');
        if ($ok) {
            $image = $result;
        } else {
            flash('error', 'Image upload failed: ' . $result);
            redirect('admin/product-edit.php' . ($id > 0 ? '?id=' . $id : ''));
        }
    }

    $galleryLines = preg_split('/\r\n|\r|\n/', $galleryText) ?: [];
    $gallery = [];
    foreach ($galleryLines as $line) {
        $line = trim($line);
        if ($line !== '') {
            $gallery[] = $line;
        }
    }

    /* Multiple image uploads: first image becomes main, rest go to gallery (color variants). */
    $newUploads = [];
    if (!empty($_FILES['image_files']['name'][0])) {
        $fileCount = count($_FILES['image_files']['name']);
        for ($i = 0; $i < $fileCount; $i++) {
            if ((int) ($_FILES['image_files']['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $file = [
                'name'     => $_FILES['image_files']['name'][$i],
                'type'     => $_FILES['image_files']['type'][$i] ?? '',
                'tmp_name' => $_FILES['image_files']['tmp_name'][$i] ?? '',
                'error'    => $_FILES['image_files']['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                'size'     => $_FILES['image_files']['size'][$i] ?? 0,
            ];
            [$ok, $result] = upload_image($file, 'products', 'product');
            if (!$ok) {
                flash('error', 'Image upload failed: ' . $result);
                redirect('admin/product-edit.php' . ($id > 0 ? '?id=' . $id : ''));
            }
            $newUploads[] = $result;
        }
    }
    if ($newUploads) {
        if ($image === '') {
            $image = array_shift($newUploads);
        }
        foreach ($newUploads as $path) {
            $gallery[] = $path;
        }
    }

    $featureLines = preg_split('/\r\n|\r|\n/', $features) ?: [];
    $featureList = [];
    foreach ($featureLines as $line) {
        $line = trim($line);
        if ($line !== '') {
            $featureList[] = $line;
        }
    }

    $galleryJson  = $gallery ? json_encode($gallery, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null;
    $featuresJson = $featureList ? json_encode($featureList, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null;

    if ($id > 0) {
        $stmt = db()->prepare(
            'UPDATE products SET title = ?, category_id = ?, price = ?, original_price = ?, stock = ?,
                    image = ?, gallery = ?, badge = ?, description = ?, features = ?, sku = ?, in_stock = ?
             WHERE id = ?'
        );
        $stmt->execute([
            $title, $categoryId, $price, $originalPrice, $stock,
            $image, $galleryJson, $badge !== '' ? $badge : null, $description !== '' ? $description : null,
            $featuresJson, $sku, $inStock, $id,
        ]);
        flash('success', 'Product updated successfully.');
    } else {
        $stmt = db()->prepare(
            'INSERT INTO products (title, category_id, price, original_price, stock, image, gallery, badge,
                                   description, features, sku, in_stock)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $title, $categoryId, $price, $originalPrice, $stock,
            $image, $galleryJson, $badge !== '' ? $badge : null, $description !== '' ? $description : null,
            $featuresJson, $sku, $inStock,
        ]);
        flash('success', 'Product added to the catalog.');
    }

    redirect('admin/index.php?tab=products');
}

flash('error', 'Unknown product action.');
redirect('admin/index.php?tab=products');
