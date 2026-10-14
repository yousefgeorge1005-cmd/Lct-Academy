<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$bookingId = $_GET['booking'] ?? '';
$photoType = $_GET['type'] ?? 'id';
$phone = find_booking_owner($bookingId);
$path = null;
if ($phone) {
    $customer = load_customer($phone);
    foreach (($customer['bookings'] ?? []) as $b) {
        if ($b['id'] === $bookingId) {
            if ($photoType === 'payment') {
                $path = $b['paymentPhotoPath'] ?? $b['idPhotoPath'] ?? null;
            } else {
                $path = $b['idPhotoPath'] ?? null;
            }
            break;
        }
    }
}

if (!$path) {
    http_response_code(404);
    exit('Not found');
}

$fullPath = SITE_ROOT . '/' . $path;
$realBase = realpath(UPLOADS_DIR);
$realPath = realpath($fullPath);
// Defensive: the resolved file must actually live inside the uploads folder.
if (!$realPath || strpos($realPath, $realBase) !== 0) {
    http_response_code(404);
    exit('Not found');
}

$ext = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
$mime = $ext === 'png' ? 'image/png' : 'image/jpeg';
header('Content-Type: ' . $mime);
header('Cache-Control: private, no-store');
header('X-Content-Type-Options: nosniff');
readfile($realPath);
