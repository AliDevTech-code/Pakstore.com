<?php
/**
 * Bulk product import/export helpers.
 *
 * The export writes a UTF-8 CSV (Excel friendly) with one row per product.
 * Multiple images are exported in the "images" column as a comma separated
 * list of URLs (main image first), so they survive a round trip through Excel.
 *
 * The importer accepts CSV / TXT / XLSX files exported from other apps
 * (e.g. Markaz). Column names are matched by aliases, and image URLs may be
 * in one column or spread across several columns.
 */

require_once __DIR__ . '/functions.php';

const BULK_IMPORT_SESSION_KEY = 'pakstore_bulk_import';
const BULK_IMPORT_MAX_ROWS    = 2000;
const BULK_IMPORT_MAX_BYTES   = 12582912; // 12 MB
const BULK_EXPORT_IMAGE_SLOTS = 6;

/** Columns used by the CSV export (also the sample import file). */
function bulk_export_columns(): array
{
    $columns = [
        'id', 'name', 'sku', 'category', 'price', 'original_price',
        'stock', 'badge', 'description', 'features', 'image', 'images',
    ];
    for ($i = 1; $i <= BULK_EXPORT_IMAGE_SLOTS; $i++) {
        $columns[] = 'image_' . $i;
    }
    $columns[] = 'in_stock';
    return $columns;
}

/** Human friendly labels for the preview / help table. */
function bulk_column_labels(): array
{
    $labels = [
        'id'             => 'Product ID',
        'name'           => 'Product Name',
        'sku'            => 'SKU / Code',
        'category'       => 'Category',
        'price'          => 'Price (Rs)',
        'original_price' => 'Old Price',
        'stock'          => 'Stock',
        'badge'          => 'Badge',
        'description'    => 'Description',
        'features'       => 'Features',
        'image'          => 'Main Image URL',
        'images'         => 'All Image URLs (comma separated)',
    ];
    for ($i = 1; $i <= BULK_EXPORT_IMAGE_SLOTS; $i++) {
        $labels['image_' . $i] = 'Image URL ' . $i;
    }
    $labels['in_stock'] = 'In Stock (1/0)';
    return $labels;
}

/** Read every product from the database into export-ready rows. */
function bulk_export_rows(): array
{
    $stmt = db()->query(
        'SELECT p.*, c.name AS category_name
         FROM products p LEFT JOIN categories c ON c.id = p.category_id
         ORDER BY p.id ASC'
    );

    $rows = [];
    foreach ($stmt->fetchAll() as $product) {
        $main = trim((string) ($product['image'] ?? ''));
        $all = [];
        if ($main !== '') {
            $all[] = image_url($main);
        }
        foreach (json_array($product['gallery'] ?? null) as $path) {
            $url = image_url($path);
            if (!in_array($url, $all, true)) {
                $all[] = $url;
            }
        }

        $original = (float) ($product['original_price'] ?? 0);
        $row = [
            'id'             => (int) $product['id'],
            'name'           => (string) $product['title'],
            'sku'            => (string) ($product['sku'] ?? ''),
            'category'       => (string) ($product['category_name'] ?? ''),
            'price'          => number_format((float) $product['price'], 2, '.', ''),
            'original_price' => $original > 0 ? number_format($original, 2, '.', '') : '',
            'stock'          => (int) $product['stock'],
            'badge'          => (string) ($product['badge'] ?? ''),
            'description'    => (string) ($product['description'] ?? ''),
            'features'       => implode("\n", json_array($product['features'] ?? null)),
            'image'          => $main !== '' ? image_url($main) : '',
            'images'         => implode(', ', $all),
        ];
        for ($i = 1; $i <= BULK_EXPORT_IMAGE_SLOTS; $i++) {
            $row['image_' . $i] = $all[$i - 1] ?? '';
        }
        $row['in_stock'] = (int) ($product['in_stock'] ?? 0) === 1 ? 1 : 0;
        $rows[] = $row;
    }
    return $rows;
}

/** Two example rows for the "sample file" download. */
function bulk_sample_rows(): array
{
    return [
        [
            'id' => '', 'name' => 'Example Cotton Shirt', 'sku' => 'DEMO-SHIRT-001',
            'category' => 'Men', 'price' => '1999.00', 'original_price' => '2499.00',
            'stock' => 25, 'badge' => 'Sale',
            'description' => "Premium cotton shirt for daily wear.\nHouse #12, Main Street (example description line).",
            'features' => "Breathable fabric\nMachine washable",
            'image' => 'https://example.com/images/shirt-front.jpg',
            'images' => 'https://example.com/images/shirt-front.jpg, https://example.com/images/shirt-back.jpg, https://example.com/images/shirt-detail.jpg',
            'image_1' => 'https://example.com/images/shirt-front.jpg',
            'image_2' => 'https://example.com/images/shirt-back.jpg',
            'image_3' => 'https://example.com/images/shirt-detail.jpg',
            'image_4' => '', 'image_5' => '', 'image_6' => '',
            'in_stock' => 1,
        ],
        [
            'id' => '', 'name' => 'Example Smart Watch', 'sku' => 'DEMO-WATCH-002',
            'category' => 'Electronics', 'price' => '3500.00', 'original_price' => '',
            'stock' => 10, 'badge' => '',
            'description' => 'Bluetooth smart watch with fitness tracking.',
            'features' => 'Bluetooth 5.0\n7 day battery',
            'image' => 'https://example.com/images/watch-1.jpg',
            'images' => 'https://example.com/images/watch-1.jpg, https://example.com/images/watch-2.jpg',
            'image_1' => 'https://example.com/images/watch-1.jpg',
            'image_2' => 'https://example.com/images/watch-2.jpg',
            'image_3' => '', 'image_4' => '', 'image_5' => '', 'image_6' => '',
            'in_stock' => 1,
        ],
    ];
}

/** Neutralise spreadsheet formula injection in text cells. */
function bulk_csv_safe(string $value): string
{
    if (preg_match('/^[\s]*[=+@]/', $value) || preg_match('/^-(?![0-9])/', $value)) {
        return "'" . $value;
    }
    return $value;
}

/** Stream a CSV download and terminate the request. */
function bulk_send_csv(string $filename, array $headers, array $rows): void
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');

    $out = fopen('php://output', 'w');
    if ($out === false) {
        return;
    }

    // BOM makes Excel detect UTF-8 (Urdu / Unicode text stays intact).
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, $headers, ',', '"', '\\');

    $textColumns = ['name', 'sku', 'category', 'badge', 'description', 'features'];
    foreach ($rows as $row) {
        $line = [];
        foreach ($headers as $header) {
            $value = (string) ($row[$header] ?? '');
            $needsEscape = in_array($header, $textColumns, true) || str_starts_with($header, 'image');
            $line[] = $needsEscape ? bulk_csv_safe($value) : $value;
        }
        fputcsv($out, $line, ',', '"', '\\');
    }

    fclose($out);
    exit;
}

/** Friendly upload error messages. */
function bulk_upload_error(int $code): string
{
    return [
        UPLOAD_ERR_INI_SIZE   => 'The uploaded file is larger than the server upload limit.',
        UPLOAD_ERR_FORM_SIZE  => 'The uploaded file is too large.',
        UPLOAD_ERR_PARTIAL    => 'The file was only partially uploaded. Please try again.',
        UPLOAD_ERR_NO_FILE    => 'Please choose a file first.',
        UPLOAD_ERR_NO_TMP_DIR => 'Server temporary folder is missing.',
        UPLOAD_ERR_CANT_WRITE => 'Server could not store the uploaded file.',
        UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the upload.',
    ][$code] ?? 'File upload failed.';
}

/** Validate, move and parse an uploaded CSV / TXT / XLSX file. */
function bulk_parse_upload(array $file): array
{
    if (!isset($file['error']) || is_array($file['error'])) {
        return [null, 'Invalid upload.'];
    }
    if ((int) $file['error'] !== UPLOAD_ERR_OK) {
        return [null, bulk_upload_error((int) $file['error'])];
    }
    if ((int) ($file['size'] ?? 0) > BULK_IMPORT_MAX_BYTES) {
        return [null, 'File is too large. Maximum size is 12 MB.'];
    }

    $extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
    if (!in_array($extension, ['csv', 'txt', 'xlsx'], true)) {
        return [null, 'Unsupported file type. Please upload a .csv, .txt or .xlsx file.'];
    }

    $tmp = (string) ($file['tmp_name'] ?? '');
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        return [null, 'Invalid upload. Please try again.'];
    }

    $local = tempnam(sys_get_temp_dir(), 'pakstore_bulk_');
    if ($local === false || !move_uploaded_file($tmp, $local)) {
        return [null, 'Could not read the uploaded file.'];
    }

    try {
        $rows = $extension === 'xlsx' ? bulk_read_xlsx($local) : bulk_read_csv($local);
    } catch (Throwable $e) {
        @unlink($local);
        return [null, 'Could not read the file: ' . $e->getMessage()];
    }
    @unlink($local);

    $rows = array_values(array_filter($rows, static function ($row): bool {
        foreach ($row as $cell) {
            if (trim((string) $cell) !== '') {
                return true;
            }
        }
        return false;
    }));

    if (count($rows) < 2) {
        return [null, 'The file needs a header row and at least one product row.'];
    }
    if (count($rows) - 1 > BULK_IMPORT_MAX_ROWS) {
        return [null, 'Too many rows. Import up to ' . BULK_IMPORT_MAX_ROWS . ' products at a time.'];
    }

    return [['header' => array_shift($rows), 'rows' => $rows], null];
}

/** Guess the CSV separator from the first line. */
function bulk_guess_delimiter(string $line): string
{
    $best = ',';
    $max = 0;
    foreach ([',', "\t", ';', '|'] as $delimiter) {
        $count = substr_count($line, $delimiter);
        if ($count > $max) {
            $max = $count;
            $best = $delimiter;
        }
    }
    return $best;
}

/** Read CSV (also semicolon / tab separated files saved by Excel). */
function bulk_read_csv(string $path): array
{
    $raw = (string) file_get_contents($path);
    if ($raw === '') {
        return [];
    }

    // Excel sometimes saves UTF-16 CSV; convert it to UTF-8 when possible.
    if (str_starts_with($raw, "\xFF\xFE") || str_starts_with($raw, "\xFE\xFF")) {
        if (function_exists('iconv')) {
            $converted = @iconv('UTF-16', 'UTF-8//IGNORE', $raw);
            if ($converted !== false) {
                $raw = $converted;
                file_put_contents($path, $raw);
            }
        }
    }

    $handle = fopen($path, 'rb');
    if ($handle === false) {
        throw new RuntimeException('cannot open CSV file');
    }

    $firstLine = (string) fgets($handle);
    rewind($handle);
    $delimiter = bulk_guess_delimiter($firstLine);

    $rows = [];
    while (($row = fgetcsv($handle, 0, $delimiter, '"', '\\')) !== false) {
        if (isset($row[0])) {
            $row[0] = preg_replace('/^\xEF\xBB\xBF/', '', $row[0]);
        }
        $rows[] = array_map(static fn($value) => $value === null ? '' : (string) $value, $row);
        if (count($rows) > BULK_IMPORT_MAX_ROWS + 5) {
            break;
        }
    }
    fclose($handle);

    return $rows;
}

/** Column index (0 based) from an Excel cell reference such as "AB12". */
function bulk_col_index(string $reference): int
{
    $letters = strtoupper((string) (preg_replace('/[^A-Z]/i', '', $reference) ?? ''));
    if ($letters === '') {
        return 0;
    }
    $index = 0;
    $length = strlen($letters);
    for ($i = 0; $i < $length; $i++) {
        $index = $index * 26 + (ord($letters[$i]) - 64);
    }
    return $index - 1;
}

/** Find the first worksheet path inside an XLSX archive. */
function bulk_xlsx_first_sheet(ZipArchive $zip): string
{
    $fallback = 'xl/worksheets/sheet1.xml';
    $workbookXml = $zip->getFromName('xl/workbook.xml');
    $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');
    if ($workbookXml === false || $relsXml === false) {
        return $fallback;
    }

    $workbook = @simplexml_load_string($workbookXml);
    $rels = @simplexml_load_string($relsXml);
    if ($workbook === false || $rels === false) {
        return $fallback;
    }

    $workbook->registerXPathNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
    $rels->registerXPathNamespace('r', 'http://schemas.openxmlformats.org/package/2006/relationships');
    $sheets = $workbook->xpath('//m:sheets/m:sheet');
    if (!$sheets) {
        return $fallback;
    }

    $attributes = $sheets[0]->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships');
    $relationId = $attributes ? (string) $attributes['id'] : '';
    if ($relationId === '') {
        return $fallback;
    }

    foreach ($rels->xpath('//r:Relationship') as $relation) {
        if ((string) $relation['Id'] !== $relationId) {
            continue;
        }
        $target = ltrim((string) $relation['Target'], '/');
        return str_starts_with($target, 'xl/') ? $target : 'xl/' . $target;
    }
    return $fallback;
}

/** Minimal XLSX reader (no external libraries needed). */
function bulk_read_xlsx(string $path): array
{
    if (!class_exists('ZipArchive')) {
        throw new RuntimeException('XLSX needs the PHP zip extension. Please save the file as CSV and upload that instead.');
    }
    if (!function_exists('simplexml_load_string')) {
        throw new RuntimeException('XLSX needs the PHP SimpleXML extension. Please save the file as CSV instead.');
    }

    $zip = new ZipArchive();
    if ($zip->open($path) !== true) {
        throw new RuntimeException('the file is not a valid XLSX workbook');
    }

    $shared = [];
    $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
    if ($sharedXml !== false) {
        $xml = @simplexml_load_string($sharedXml);
        if ($xml !== false) {
            $xml->registerXPathNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            foreach ($xml->xpath('//m:si') as $item) {
                $text = '';
                foreach ($item->xpath('.//m:t') as $node) {
                    $text .= (string) $node;
                }
                $shared[] = $text;
            }
        }
    }

    $sheetXml = $zip->getFromName(bulk_xlsx_first_sheet($zip));
    if ($sheetXml === false) {
        $zip->close();
        throw new RuntimeException('worksheet not found in the workbook');
    }

    $sheet = @simplexml_load_string($sheetXml);
    $zip->close();
    if ($sheet === false) {
        throw new RuntimeException('worksheet could not be parsed');
    }

    $sheet->registerXPathNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
    $rows = [];
    foreach ($sheet->xpath('//m:sheetData/m:row') as $rowNode) {
        $cells = [];
        foreach ($rowNode->xpath('./m:c') as $cell) {
            $column = bulk_col_index((string) $cell['r']);
            $type = (string) $cell['t'];
            $value = '';

            if ($type === 'inlineStr') {
                foreach ($cell->xpath('.//m:t') as $node) {
                    $value .= (string) $node;
                }
            } else {
                $valueNodes = $cell->xpath('./m:v');
                if ($valueNodes) {
                    $value = (string) $valueNodes[0];
                }
                if ($type === 's' && isset($shared[(int) $value])) {
                    $value = $shared[(int) $value];
                } elseif ($type === 'b') {
                    $value = $value === '1' ? 'TRUE' : 'FALSE';
                }
            }
            $cells[$column] = $value;
        }

        if (!$cells) {
            continue;
        }
        ksort($cells);
        $last = max(array_keys($cells));
        $row = [];
        for ($i = 0; $i <= $last; $i++) {
            $row[] = $cells[$i] ?? '';
        }
        $rows[] = $row;
    }

    return $rows;
}

/** Normalise a header cell for alias matching. */
function bulk_header_key(string $header): string
{
    $key = strtolower(trim($header));
    $key = preg_replace('/^\xEF\xBB\xBF/', '', $key) ?? '';
    $key = str_replace(['_', '-', '.', '/', '\\'], ' ', $key);
    return (string) preg_replace('/\s+/', ' ', $key);
}

/** Accepted names for each product field (Markaz-style exports included). */
function bulk_field_aliases(): array
{
    return [
        'id'             => ['id', 'product id', 'productid'],
        'name'           => ['name', 'title', 'product name', 'product title', 'product', 'item name', 'item', 'product name english', 'name english'],
        'description'    => ['description', 'desc', 'details', 'detail', 'product description', 'product details', 'about product', 'long description', 'description english'],
        'price'          => ['price', 'sale price', 'selling price', 'sell price', 'product price', 'rate', 'amount', 'discounted price', 'special price', 'offer price'],
        'original_price' => ['original price', 'mrp', 'old price', 'regular price', 'compare at price', 'compare price', 'list price', 'retail price'],
        'stock'          => ['stock', 'quantity', 'qty', 'inventory', 'available', 'available stock', 'stock quantity'],
        'sku'            => ['sku', 'code', 'product code', 'productcode', 'barcode', 'item code'],
        'category'       => ['category', 'category name', 'cat', 'product category', 'categoryname', 'type'],
        'images'         => ['images', 'image', 'image url', 'image urls', 'imageurl', 'imageurls', 'gallery', 'gallery images', 'photos', 'photo', 'pictures', 'picture', 'product images', 'product image', 'image link', 'image links', 'main image', 'image 1', 'image1'],
        'badge'          => ['badge', 'label', 'tag', 'ribbon'],
        'features'       => ['features', 'key features', 'specifications', 'specs', 'highlights'],
        'in_stock'       => ['in stock', 'instock', 'availability'],
    ];
}

/** Map sheet columns to product fields. Every field may have several columns. */
function bulk_map_columns(array $header): array
{
    $aliases = bulk_field_aliases();
    $map = [];

    foreach ($header as $index => $cell) {
        $key = bulk_header_key((string) $cell);
        if ($key === '') {
            continue;
        }

        $matched = '';
        foreach ($aliases as $field => $names) {
            if (in_array($key, $names, true)) {
                $matched = $field;
                break;
            }
        }

        if ($matched === '') {
            // Loose fallback for differently worded exports.
            if (str_contains($key, 'image') || str_contains($key, 'photo') || str_contains($key, 'picture') || str_contains($key, 'gallery')) {
                $matched = 'images';
            } elseif (str_contains($key, 'description') || str_contains($key, 'detail')) {
                $matched = 'description';
            } elseif (str_contains($key, 'price') || str_contains($key, 'mrp')) {
                $matched = 'price';
            } elseif (str_contains($key, 'category')) {
                $matched = 'category';
            } elseif (str_contains($key, 'sku') || str_contains($key, 'code')) {
                $matched = 'sku';
            } elseif (str_contains($key, 'stock') || str_contains($key, 'quantity')) {
                $matched = 'stock';
            } elseif (str_contains($key, 'name') || str_contains($key, 'title')) {
                $matched = 'name';
            }
        }

        if ($matched !== '') {
            $map[$matched][] = $index;
        }
    }

    return $map;
}

/** Parse values like "Rs. 1,999.00" or "PKR 3500" into a number. */
function bulk_number(string $value): float
{
    $clean = preg_replace('/[^\d.\-]/', '', trim($value)) ?? '';
    if (substr_count($clean, '-') > 1 || substr_count($clean, '.') > 1) {
        return 0.0;
    }
    return is_numeric($clean) ? (float) $clean : 0.0;
}

/** Tidy a single image value. */
function bulk_clean_url(string $url): string
{
    $url = trim($url, " \t\n\r\0\x0B\"'");
    $url = rtrim($url, ',;|)');
    if (str_starts_with($url, 'www.')) {
        $url = 'https://' . $url;
    }
    return $url;
}

/**
 * Collect image URLs from one or more sheet cells.
 * Supports a single URL, comma/pipe/newline separated URLs, JSON arrays,
 * Markdown links and relative paths such as uploads/products/foo.jpg.
 */
function bulk_extract_images(array $values): array
{
    $found = [];

    foreach ($values as $value) {
        $value = trim((string) $value);
        if ($value === '') {
            continue;
        }

        $decoded = json_decode($value, true);
        if (is_array($decoded)) {
            $found = array_merge($found, bulk_extract_images(array_map('strval', $decoded)));
            continue;
        }

        $value = (string) (preg_replace('/\[[^\]]*\]\((https?:\/\/[^\s)]+)\)/i', '$1', $value) ?? $value);

        if (preg_match_all('~https?://[^\s<>"\'\],;|]+~i', $value, $matches)) {
            foreach ($matches[0] as $url) {
                $found[] = bulk_clean_url($url);
            }
        } elseif (preg_match_all('~(?:uploads|images|media)/[^\s<>"\'\],;|]+~i', $value, $matches)) {
            foreach ($matches[0] as $url) {
                $found[] = bulk_clean_url($url);
            }
        } else {
            foreach (preg_split('/[\r\n,;|]+/', $value) ?: [] as $part) {
                if (trim($part) !== '') {
                    $found[] = bulk_clean_url($part);
                }
            }
        }
    }

    $images = [];
    foreach ($found as $url) {
        if ($url === '' || in_array($url, $images, true)) {
            continue;
        }
        $validUrl = (bool) filter_var($url, FILTER_VALIDATE_URL);
        $validPath = str_starts_with($url, '/') || (bool) preg_match('~^[\w./-]+\.(jpg|jpeg|png|webp|gif)$~i', $url);
        if (!$validUrl && !$validPath) {
            continue;
        }
        $images[] = $url;
        if (count($images) >= 20) {
            break;
        }
    }

    return $images;
}

/** Convert parsed sheet rows into normalised product arrays. */
function bulk_normalize_rows(array $rows, array $map): array
{
    $products = [];

    foreach ($rows as $index => $row) {
        $get = static function (string $field) use ($row, $map): string {
            $column = $map[$field][0] ?? null;
            return $column === null ? '' : trim((string) ($row[$column] ?? ''));
        };

        $nameRaw = $get('name');
        $priceRaw = $get('price');
        $name = $nameRaw;
        $price = bulk_number($priceRaw);
        $original = bulk_number($get('original_price'));
        $stock = (int) bulk_number($get('stock'));
        $errors = [];

        if ($name === '') {
            $errors[] = 'name missing';
        }
        if ($priceRaw !== '' && $price <= 0) {
            $errors[] = 'invalid price';
        }
        if ($original > 0 && $original < $price) {
            $original = 0.0;
        }

        $imageValues = [];
        foreach ($map['images'] ?? [] as $column) {
            $imageValues[] = (string) ($row[$column] ?? '');
        }

        $products[] = [
            'source_row'     => $index + 2, // +1 for zero based, +1 for header
            'id'             => (int) bulk_number($get('id')),
            'name'           => $name,
            'sku'            => $get('sku'),
            'category'       => $get('category'),
            'price'          => $price,
            'original_price' => $original > 0 ? $original : null,
            'stock'          => max(0, $stock),
            'badge'          => $get('badge'),
            'description'    => $get('description'),
            'features'       => $get('features'),
            'images'         => bulk_extract_images($imageValues),
            'errors'         => $errors,
        ];
    }

    return $products;
}

/** Insert / update products. Existing rows are matched by ID first, then SKU. */
function bulk_commit_products(array $products, bool $createCategories): array
{
    $pdo = db();
    $stats = ['created' => 0, 'updated' => 0, 'skipped' => 0];

    $categorySelect = $pdo->prepare('SELECT id FROM categories WHERE LOWER(name) = LOWER(?) LIMIT 1');
    $categoryInsert = $pdo->prepare('INSERT INTO categories (name, slug, icon) VALUES (?, ?, ?)');
    $findById = $pdo->prepare('SELECT id FROM products WHERE id = ? LIMIT 1');
    $findBySku = $pdo->prepare('SELECT id FROM products WHERE sku = ? LIMIT 1');
    $insert = $pdo->prepare(
        'INSERT INTO products (title, category_id, price, original_price, stock, image, gallery,
                               badge, description, features, sku, in_stock)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $update = $pdo->prepare(
        'UPDATE products SET title = ?, category_id = ?, price = ?, original_price = ?, stock = ?,
                             image = ?, gallery = ?, badge = ?, description = ?, features = ?,
                             sku = ?, in_stock = ?
         WHERE id = ?'
    );

    $pdo->beginTransaction();

    foreach ($products as $product) {
        if (!empty($product['errors'])) {
            $stats['skipped']++;
            continue;
        }

        $categoryId = null;
        $categoryName = trim((string) $product['category']);
        if ($categoryName !== '') {
            $categorySelect->execute([$categoryName]);
            $categoryId = (int) ($categorySelect->fetchColumn() ?: 0) ?: null;

            if ($categoryId === null && $createCategories) {
                $slug = slugify($categoryName);
                if ($slug === '') {
                    $slug = 'cat-' . substr(md5($categoryName), 0, 8);
                }
                try {
                    $categoryInsert->execute([$categoryName, $slug, 'package']);
                    $categoryId = (int) $pdo->lastInsertId();
                } catch (Throwable $e) {
                    // Another category with the same slug may exist with different casing.
                    $categorySelect->execute([$categoryName]);
                    $categoryId = (int) ($categorySelect->fetchColumn() ?: 0) ?: null;
                }
            }
        }

        $sku = trim((string) $product['sku']);
        if ($sku === '') {
            $sku = 'PAK-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        }

        $images = $product['images'];
        $mainImage = array_shift($images) ?? '';
        $galleryJson = $images
            ? json_encode(array_values($images), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            : null;

        $featureList = [];
        $featuresText = trim((string) $product['features']);
        if ($featuresText !== '') {
            $decoded = json_decode($featuresText, true);
            if (is_array($decoded)) {
                foreach ($decoded as $feature) {
                    if (trim((string) $feature) !== '') {
                        $featureList[] = trim((string) $feature);
                    }
                }
            } else {
                foreach (preg_split('/\r\n|\r|\n|[,|]/', $featuresText) ?: [] as $feature) {
                    if (trim($feature) !== '') {
                        $featureList[] = trim($feature);
                    }
                }
            }
        }
        $featuresJson = $featureList
            ? json_encode($featureList, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            : null;

        $existingId = 0;
        if ((int) $product['id'] > 0) {
            $findById->execute([(int) $product['id']]);
            $existingId = (int) ($findById->fetchColumn() ?: 0);
        }
        if ($existingId === 0) {
            $findBySku->execute([$sku]);
            $existingId = (int) ($findBySku->fetchColumn() ?: 0);
        }

        $values = [
            $product['name'],
            $categoryId,
            $product['price'],
            $product['original_price'],
            $product['stock'],
            $mainImage,
            $galleryJson,
            $product['badge'] !== '' ? $product['badge'] : null,
            $product['description'] !== '' ? $product['description'] : null,
            $featuresJson,
            $sku,
            $product['stock'] > 0 ? 1 : 0,
        ];

        if ($existingId > 0) {
            $update->execute([...$values, $existingId]);
            $stats['updated']++;
        } else {
            $insert->execute($values);
            $stats['created']++;
        }
    }

    $pdo->commit();
    return $stats;
}
