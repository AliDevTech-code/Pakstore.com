<?php
/** Admin logout (POST only, CSRF protected). */

require_once __DIR__ . '/../includes/functions.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    redirect('admin/index.php');
}

csrf_verify();
unset($_SESSION['admin_id']);
session_regenerate_id(true);
flash('info', 'You have been signed out of the admin panel.');
redirect('admin/login.php');
