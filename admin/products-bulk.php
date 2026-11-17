<?php
/** Bulk product export / import actions. */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../includes/bulk-products.php';

$action = trim((string) ($_POST['action'] ?? $_GET['action'] ?? ''));
$returnTo = 'admin/index.php?tab=bulk';

if ($action === 'export') {
    bulk_send_csv('pakstore-products-' . date('Y-m-d') . '.csv', bulk_export_columns(), bulk_export_rows());
}

if ($action === 'sample') {
    bulk_send_csv('pakstore-import-sample.csv', bulk_export_columns(), bulk_sample_rows());
}

require_post();
csrf_verify();

if ($action === 'preview') {
    [$parsed, $error] = bulk_parse_upload($_FILES['file'] ?? []);
    if ($error !== null) {
        flash('error', $error);
        redirect($returnTo);
    }

    $map = bulk_map_columns($parsed['header']);
    $missing = [];
    if (empty($map['name'])) {
        $missing[] = 'product name';
    }
    if (empty($map['price'])) {
        $missing[] = 'price';
    }
    if ($missing) {
        flash('error', 'Required column(s) missing: ' . implode(', ', $missing) . '. Download the sample file to see the expected format.');
        redirect($returnTo);
    }

    $defaultStock = max(0, (int) post_string('default_stock', (string) BULK_DEFAULT_STOCK));
    $grouped = bulk_is_variant_sheet($parsed['header'], $map);
    $products = bulk_prepare_products($parsed['rows'], $map, $parsed['header'], $defaultStock);
    $_SESSION[BULK_IMPORT_SESSION_KEY] = [
        'created_at'        => time(),
        'header'            => $parsed['header'],
        'map'               => $map,
        'products'          => $products,
        'create_categories' => !empty($_POST['create_categories']),
        'grouped'           => $grouped,
        'sheet_rows'        => count($parsed['rows']),
    ];

    $ready = 0;
    foreach ($products as $product) {
        if (!$product['errors']) {
            $ready++;
        }
    }
    flash('success', 'File parsed: ' . count($products) . ' row(s) found, ' . $ready . ' ready to import. Review below and then click Import.');
    redirect($returnTo);
}

if ($action === 'import') {
    $data = $_SESSION[BULK_IMPORT_SESSION_KEY] ?? null;
    if (!$data || empty($data['products'])) {
        flash('error', 'Import session expired. Please upload the file again.');
        redirect($returnTo);
    }
    if (time() - (int) ($data['created_at'] ?? 0) > 1800) {
        unset($_SESSION[BULK_IMPORT_SESSION_KEY]);
        flash('error', 'Preview expired after 30 minutes. Please upload the file again.');
        redirect($returnTo);
    }

    try {
        $stats = bulk_commit_products($data['products'], !empty($data['create_categories']));
        flash(
            'success',
            'Import complete: ' . $stats['created'] . ' new, ' . $stats['updated'] . ' updated, '
            . $stats['skipped'] . ' skipped.'
        );
    } catch (Throwable $e) {
        error_log('Bulk product import failed: ' . $e->getMessage());
        flash('error', 'Import failed: ' . $e->getMessage());
    }
    unset($_SESSION[BULK_IMPORT_SESSION_KEY]);
    redirect($returnTo);
}

if ($action === 'cancel') {
    unset($_SESSION[BULK_IMPORT_SESSION_KEY]);
    flash('info', 'Bulk import cancelled.');
    redirect($returnTo);
}

flash('error', 'Unknown bulk action.');
redirect($returnTo);
