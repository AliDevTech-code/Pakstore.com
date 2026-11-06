<?php
/** Category create / update / delete actions. */

require_once __DIR__ . '/includes/auth.php';
require_post();
csrf_verify();

$action = post_string('action');

if ($action === 'delete') {
    $id = post_int('id');
    $stmt = db()->prepare('DELETE FROM categories WHERE id = ?');
    $stmt->execute([$id]);
    flash('success', 'Category deleted. Its products are now uncategorized.');
    redirect('admin/index.php?tab=categories');
}

if ($action === 'save') {
    $id   = post_int('id');
    $name = post_string('name');
    $icon = post_string('icon', 'tag');
    $slug = slugify(post_string('slug') !== '' ? post_string('slug') : $name);

    if (mb_strlen($name) < 2) {
        flash('error', 'Category name is required.');
        redirect('admin/index.php?tab=categories');
    }
    if ($icon === '') {
        $icon = 'tag';
    }

    // Ensure a unique slug.
    $base = $slug;
    $suffix = 1;
    $check = db()->prepare('SELECT id FROM categories WHERE slug = ? AND id <> ? LIMIT 1');
    while (true) {
        $check->execute([$slug, $id]);
        if (!$check->fetchColumn()) {
            break;
        }
        $suffix++;
        $slug = $base . '-' . $suffix;
    }

    $dupName = db()->prepare('SELECT id FROM categories WHERE name = ? AND id <> ? LIMIT 1');
    $dupName->execute([$name, $id]);
    if ($dupName->fetchColumn()) {
        flash('error', 'A category with that name already exists.');
        redirect('admin/index.php?tab=categories' . ($id > 0 ? '&edit=' . $id : ''));
    }

    if ($id > 0) {
        $stmt = db()->prepare('UPDATE categories SET name = ?, slug = ?, icon = ? WHERE id = ?');
        $stmt->execute([$name, $slug, $icon, $id]);
        flash('success', 'Category updated.');
    } else {
        $stmt = db()->prepare('INSERT INTO categories (name, slug, icon) VALUES (?, ?, ?)');
        $stmt->execute([$name, $slug, $icon]);
        flash('success', 'Category created.');
    }

    redirect('admin/index.php?tab=categories');
}

flash('error', 'Unknown category action.');
redirect('admin/index.php?tab=categories');
