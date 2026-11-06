<?php
/** Customer delete action. */

require_once __DIR__ . '/includes/auth.php';
require_post();
csrf_verify();

$action = post_string('action');

if ($action === 'delete') {
    $id = post_int('id');
    $stmt = db()->prepare('DELETE FROM users WHERE id = ?');
    $stmt->execute([$id]);
    flash('success', 'Customer account deleted. Their past orders remain for records.');
    redirect('admin/index.php?tab=users');
}

flash('error', 'Unknown customer action.');
redirect('admin/index.php?tab=users');
