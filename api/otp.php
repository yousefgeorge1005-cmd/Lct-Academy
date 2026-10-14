<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=UTF-8');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$phone  = trim($_POST['phone'] ?? $_GET['phone'] ?? '');

if ($phone === '') {
    echo json_encode(['success' => false, 'message' => 'رقم الهاتف مطلوب']);
    exit;
}

if (!validate_phone_eg($phone)) {
    echo json_encode(['success' => false, 'message' => 'رقم الهاتف غير صحيح']);
    exit;
}

if ($action === 'send') {
    $code = generate_otp_code($phone);
    // In production, this code would be sent via SMS gateway (e.g. Twilio or SMS.eg)
    echo json_encode([
        'success' => true,
        'message' => 'تم إرسال كود التأكيد بنجاح',
        'otp_code' => $code, // Sent to frontend for interactive demo toast
    ]);
    exit;
}

if ($action === 'verify') {
    $inputCode = trim($_POST['code'] ?? '');
    if (verify_otp_code($phone, $inputCode)) {
        echo json_encode(['success' => true, 'message' => 'تم التحقق بنجاح']);
    } else {
        echo json_encode(['success' => false, 'message' => 'كود التأكيد غير صحيح أو انتهت صلاحيته']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'إجراء غير معروف']);
exit;
