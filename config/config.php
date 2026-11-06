<?php
/**
 * PakStore - central configuration.
 *
 * InfinityFree setup:
 *   1) Copy config/config.local.example.php to config/config.local.php
 *   2) Fill in your InfinityFree MySQL host / database / user / password.
 *   3) Never commit config/config.local.php to git.
 *
 * You may also provide these values through environment variables
 * (PAKSTORE_DB_HOST, PAKSTORE_DB_NAME, ...). This is useful on hosts
 * that support environment variables.
 */

// Optional local override (not tracked by git).
$pakstoreLocalConfig = __DIR__ . '/config.local.php';
if (is_file($pakstoreLocalConfig)) {
    require $pakstoreLocalConfig;
}

/** Database host, e.g. sql301.infinityfree.com */
if (!defined('DB_HOST')) {
    define('DB_HOST', getenv('PAKSTORE_DB_HOST') ?: 'sqlXXX.infinityfree.com');
}

/** Database name, e.g. if0_12345678_pakstore */
if (!defined('DB_NAME')) {
    define('DB_NAME', getenv('PAKSTORE_DB_NAME') ?: 'if0_00000000_pakstore');
}

/** Database user, e.g. if0_12345678 */
if (!defined('DB_USER')) {
    define('DB_USER', getenv('PAKSTORE_DB_USER') ?: 'if0_00000000');
}

/** Database password */
if (!defined('DB_PASS')) {
    define('DB_PASS', getenv('PAKSTORE_DB_PASS') ?: 'CHANGE_ME');
}

/**
 * Base path of the application.
 * Leave empty when the site is installed directly inside htdocs.
 * If you upload it into a sub-folder, use e.g. '/pakstore'.
 */
if (!defined('APP_BASE')) {
    define('APP_BASE', getenv('PAKSTORE_APP_BASE') !== false ? getenv('PAKSTORE_APP_BASE') : '');
}

/** Store timezone used for all date/time output. */
if (!defined('APP_TIMEZONE')) {
    define('APP_TIMEZONE', 'Asia/Karachi');
}
date_default_timezone_set(APP_TIMEZONE);

/** Maximum product image upload size (bytes). */
if (!defined('MAX_UPLOAD_BYTES')) {
    define('MAX_UPLOAD_BYTES', 3 * 1024 * 1024);
}

/** Session name (kept separate from other apps on the same domain). */
if (!defined('SESSION_NAME')) {
    define('SESSION_NAME', 'PAKSTORESESSID');
}

/** Set to true only while debugging. Keep false in production. */
if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', getenv('PAKSTORE_DEBUG') === '1');
}

if (APP_DEBUG) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(E_ALL);
    ini_set('log_errors', '1');
}
