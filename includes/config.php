<?php
/**
 * LCT Academy — core configuration.
 */

define('SITE_ROOT', dirname(__DIR__));
define('DATA_DIR', SITE_ROOT . '/data');
define('CUSTOMERS_DIR', DATA_DIR . '/customers');
define('UPLOADS_DIR', DATA_DIR . '/uploads/id_photos');
define('COURSES_FILE', DATA_DIR . '/courses.json');
define('HALLS_FILE', DATA_DIR . '/halls.json');
define('ADMIN_FILE', DATA_DIR . '/admin.json');
define('SETTINGS_FILE', DATA_DIR . '/settings.json');
define('BLOCKED_NUMBERS_FILE', DATA_DIR . '/blocked_numbers.json');
define('CLOUDINARY_CREDENTIALS_FILE', DATA_DIR . '/cloudinary_credentials.json');

$script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
$isAdmin = strpos($script, '/admin/') !== false;
define('BASE_URL', $isAdmin ? '..' : '.');
define('IS_ADMIN_AREA', $isAdmin);

// Load .env if present
if (file_exists(SITE_ROOT . '/.env')) {
    $lines = @file(SITE_ROOT . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        if (strpos($line, '=') !== false) {
            [$k, $v] = explode('=', $line, 2);
            $k = trim($k);
            $v = trim($v, " \t\n\r\0\x0B\"'");
            if (!getenv($k)) {
                putenv("$k=$v");
                $_ENV[$k] = $v;
            }
        }
    }
}
require_once __DIR__ . '/upstash.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

foreach ([DATA_DIR, CUSTOMERS_DIR, UPLOADS_DIR] as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
}

