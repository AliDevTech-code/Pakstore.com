<?php
/** Order status / courier updates and deletion. */

require_once __DIR__ . '/includes/auth.php';
require_post();
csrf_verify();

$action = post_string('action');
$id = post_int('id');

if ($action === 'delete') {
    $stmt = db()->prepare('DELETE FROM orders WHERE id = ?');
    $stmt->execute([$id]);
    flash('success', 'Order deleted.');
    redirect('admin/index.php?tab=orders');
}

if ($action === 'update') {
    $status = post_string('status');
    $allowed = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
    if (!in_array($status, $allowed, true)) {
        flash('error', 'Invalid order status.');
        redirect('admin/index.php?tab=orders');
    }

    $courier = post_string('tracking_courier');
    $tracking = post_string('tracking_number');

    $stmt = db()->prepare('SELECT * FROM orders WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $order = $stmt->fetch();

    if (!$order) {
        flash('error', 'Order not found.');
        redirect('admin/index.php?tab=orders');
    }

    $oldStatus = $order['status'];
    $pdo = db();

    try {
        $pdo->beginTransaction();

        if ($status === 'Cancelled' && $oldStatus !== 'Cancelled') {
            // Return reserved stock to the catalog.
            $items = order_items($id);
            $restore = $pdo->prepare('UPDATE products SET stock = stock + ?, in_stock = 1 WHERE id = ?');
            foreach ($items as $item) {
                if (!empty($item['product_id'])) {
                    $restore->execute([(int) $item['quantity'], (int) $item['product_id']]);
                }
            }
        } elseif ($oldStatus === 'Cancelled' && $status !== 'Cancelled') {
            // Re-reserve stock when reviving a cancelled order.
            $items = order_items($id);
            $reserve = $pdo->prepare('UPDATE products SET in_stock = IF(stock - ? <= 0, 0, 1), stock = GREATEST(stock - ?, 0) WHERE id = ?');
            foreach ($items as $item) {
                if (!empty($item['product_id'])) {
                    $reserve->execute([(int) $item['quantity'], (int) $item['quantity'], (int) $item['product_id']]);
                }
            }
        }

        $update = $pdo->prepare(
            'UPDATE orders SET status = ?, tracking_courier = ?, tracking_number = ? WHERE id = ?'
        );
        $update->execute([
            $status,
            $courier !== '' ? $courier : null,
            $tracking !== '' ? $tracking : null,
            $id,
        ]);

        $pdo->commit();
        flash('success', 'Order #' . $order['order_code'] . ' updated to ' . $status . '.');
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Order update failed: ' . $e->getMessage());
        flash('error', 'Could not update this order. Please try again.');
    }

    redirect(local_redirect_target('admin/index.php?tab=orders'));
}

flash('error', 'Unknown order action.');
redirect('admin/index.php?tab=orders');
