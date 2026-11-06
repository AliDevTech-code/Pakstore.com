<?php
/**
 * Copy this file to config/config.local.php and fill in the real values
 * from InfinityFree Control Panel -> MySQL Databases.
 *
 * IMPORTANT: config/config.local.php is git-ignored, never commit it.
 */

define('DB_HOST', 'sql100.infinityfree.com'); // e.g. sql301.infinityfree.com
define('DB_NAME', 'if0_41380880_Pakstore');    // e.g. if0_12345678_pakstore
define('DB_USER', 'if0_41380880');             // e.g. if0_12345678
define('DB_PASS', 'bxiCWKPcJHxmE');

// Leave empty if the site is directly in htdocs.
// Use '/subfolder' if you uploaded the project into a sub-folder.
define('APP_BASE', '');

// Turn on only while debugging. Keep false on the live site.
define('APP_DEBUG', false);
