<?php
/**
 * Copy this file to config/config.local.php and fill in the real values
 * from InfinityFree Control Panel -> MySQL Databases.
 *
 * IMPORTANT: config/config.local.php is git-ignored, never commit it.
 */

define('DB_HOST', 'sqlXXX.infinityfree.com'); // e.g. sql301.infinityfree.com
define('DB_NAME', 'if0_00000000_pakstore');    // e.g. if0_12345678_pakstore
define('DB_USER', 'if0_00000000');             // e.g. if0_12345678
define('DB_PASS', 'YOUR_DATABASE_PASSWORD');

// Leave empty if the site is directly in htdocs.
// Use '/subfolder' if you uploaded the project into a sub-folder.
define('APP_BASE', '');

// Turn on only while debugging. Keep false on the live site.
define('APP_DEBUG', false);
