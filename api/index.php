<?php
/**
 * LCT Academy — Vercel Serverless Master Router.
 * Directs all incoming HTTP requests to their respective PHP handlers.
 */

putenv('VERCEL=1');
$_ENV['VERCEL'] = '1';
$_SERVER['VERCEL'] = '1';

$root = dirname(__DIR__);
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri = urldecode($uri);
$path = ltrim($uri, '/');

// Static assets should have been served by Vercel routes, but as a safety check:
if (strpos($path, 'assets/') === 0 && file_exists($root . '/' . $path)) {
    $mime = 'text/plain';
    if (substr($path, -4) === '.css') $mime = 'text/css';
    elseif (substr($path, -3) === '.js') $mime = 'application/javascript';
    elseif (substr($path, -4) === '.png') $mime = 'image/png';
    elseif (substr($path, -4) === '.jpg' || substr($path, -5) === '.jpeg') $mime = 'image/jpeg';
    elseif (substr($path, -4) === '.svg') $mime = 'image/svg+xml';
    header("Content-Type: $mime");
    readfile($root . '/' . $path);
    exit;
}

// Common route aliases to prevent 404
if ($path === 'login.php' || $path === 'login') {
    header('Location: /admin/login.php', true, 301);
    exit;
}
if (strpos($path, 'api/admin') === 0) {
    header('Location: /' . substr($path, 4), true, 301);
    exit;
}

// Resolve target PHP file
if ($path === '' || $path === '/') {
    $target = $root . '/index.php';
} elseif (is_dir($root . '/' . rtrim($path, '/'))) {
    if (substr($uri, -1) !== '/') {
        header('Location: ' . $uri . '/', true, 301);
        exit;
    }
    $target = $root . '/' . rtrim($path, '/') . '/index.php';
} elseif (file_exists($root . '/' . $path) && substr($path, -4) === '.php') {
    $target = $root . '/' . $path;
} elseif (file_exists($root . '/' . $path . '.php')) {
    $target = $root . '/' . $path . '.php';
} else {
    http_response_code(404);
    echo "Page not found";
    exit;
}

// Setup environment for the requested script
$rel = '/' . ltrim(str_replace([$root, '\\'], ['', '/'], $target), '/');
$_SERVER['SCRIPT_NAME'] = $rel;
$_SERVER['SCRIPT_FILENAME'] = $target;

// Set current working directory to the target file's folder so relative includes work
chdir(dirname($target));

require $target;
