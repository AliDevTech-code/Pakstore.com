<?php
/**
 * PDO database connection (single shared instance).
 * All queries in this project use prepared statements.
 */

require_once __DIR__ . '/config.php';

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_STRINGIFY_FETCHES  => false,
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        error_log('PakStore DB connection failed: ' . $e->getMessage());

        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
        echo '<!doctype html><html lang="en"><head><meta charset="utf-8">'
            . '<meta name="viewport" content="width=device-width, initial-scale=1">'
            . '<title>Database connection error</title>'
            . '<style>body{font-family:system-ui,sans-serif;background:#f8fafc;color:#0f172a;display:grid;place-items:center;min-height:100vh;margin:0}'
            . '.box{max-width:560px;background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:28px;margin:16px}'
            . 'h1{font-size:18px;margin:0 0 8px}p{font-size:13px;line-height:1.6;color:#475569;margin:6px 0}'
            . 'code{background:#f1f5f9;padding:2px 6px;border-radius:6px;font-size:12px}</style></head><body><div class="box">'
            . '<h1>Database connection failed</h1>'
            . '<p>The store could not connect to MySQL. Please check your credentials in '
            . '<code>config/config.local.php</code> (or the environment variables).</p>'
            . '<p>InfinityFree database host, name and user are available in the Control Panel under '
            . '<strong>MySQL Databases</strong>.</p>';

        if (APP_DEBUG) {
            echo '<p><strong>Debug:</strong> ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
        }

        echo '</div></body></html>';
        exit;
    }

    return $pdo;
}
