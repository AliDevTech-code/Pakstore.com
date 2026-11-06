<?php
/** Store settings, logo, admin password and staff accounts. */

require_once __DIR__ . '/includes/auth.php';
require_post();
csrf_verify();

$action = post_string('action');

/* ---------------- Store settings ---------------- */
if ($action === 'save_store') {
    $storeName = post_string('store_name');
    if ($storeName === '') {
        flash('error', 'Store name is required.');
        redirect('admin/index.php?tab=settings');
    }

    $logo = setting('logo_url', '');
    if (!empty($_FILES['logo_file']['name'])) {
        [$ok, $result] = upload_image($_FILES['logo_file'], 'logo', 'logo');
        if (!$ok) {
            flash('error', 'Logo upload failed: ' . $result);
            redirect('admin/index.php?tab=settings');
        }
        $logo = $result;
    }
    if (isset($_POST['remove_logo'])) {
        $logo = '';
    }

    update_setting('store_name', $storeName);
    update_setting('logo_url', $logo);
    update_setting('top_bar_text', post_string('top_bar_text'));
    update_setting('announcement', post_string('announcement'));
    update_setting('phone', post_string('phone'));
    update_setting('whatsapp', post_string('whatsapp'));
    update_setting('email', post_string('email'));
    update_setting('delivery_charges', (string) max(0, (float) post_string('delivery_charges')));

    flash('success', 'Store settings saved.');
    redirect('admin/index.php?tab=settings');
}

/* ---------------- Admin password ---------------- */
if ($action === 'change_password') {
    $current = (string) ($_POST['current_password'] ?? '');
    $new     = (string) ($_POST['new_password'] ?? '');
    $confirm = (string) ($_POST['confirm_password'] ?? '');

    if (!password_matches($current, $adminUser['password_hash'])) {
        flash('error', 'Your current admin password is incorrect.');
    } elseif (strlen($new) < 8) {
        flash('error', 'The new password must be at least 8 characters.');
    } elseif ($new !== $confirm) {
        flash('error', 'The new password and confirmation do not match.');
    } else {
        $stmt = db()->prepare('UPDATE admin_users SET password_hash = ? WHERE id = ?');
        $stmt->execute([password_hash($new, PASSWORD_DEFAULT), (int) $adminUser['id']]);
        flash('success', 'Admin password changed successfully.');
    }
    redirect('admin/index.php?tab=settings');
}

/* ---------------- Add staff account ---------------- */
if ($action === 'add_admin') {
    $email = strtolower(post_string('email'));
    $password = (string) ($_POST['password'] ?? '');
    $role = post_string('role', 'StoreManager');
    if (!in_array($role, ['SuperAdmin', 'StoreManager'], true)) {
        $role = 'StoreManager';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('error', 'Enter a valid admin email address.');
    } elseif (strlen($password) < 8) {
        flash('error', 'The new admin password must be at least 8 characters.');
    } else {
        $check = db()->prepare('SELECT id FROM admin_users WHERE email = ? LIMIT 1');
        $check->execute([$email]);
        if ($check->fetchColumn()) {
            flash('error', 'An admin with that email already exists.');
        } else {
            $stmt = db()->prepare('INSERT INTO admin_users (email, password_hash, role) VALUES (?, ?, ?)');
            $stmt->execute([$email, password_hash($password, PASSWORD_DEFAULT), $role]);
            flash('success', 'Staff account created.');
        }
    }
    redirect('admin/index.php?tab=settings');
}

/* ---------------- Delete staff account ---------------- */
if ($action === 'delete_admin') {
    $id = post_int('id');
    if ($id === (int) $adminUser['id']) {
        flash('error', 'You cannot delete the account you are currently signed in with.');
    } else {
        $stmt = db()->prepare('DELETE FROM admin_users WHERE id = ?');
        $stmt->execute([$id]);
        flash('success', 'Staff account deleted.');
    }
    redirect('admin/index.php?tab=settings');
}

flash('error', 'Unknown settings action.');
redirect('admin/index.php?tab=settings');
