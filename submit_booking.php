<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/translations.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$type = ($_POST['type'] ?? '') === 'hall' ? 'hall' : 'course';
$lang = ($_POST['lang'] ?? current_lang()) === 'en' ? 'en' : 'ar';
$T    = translations($lang);

$fullName   = trim($_POST['full_name'] ?? '');
$phone      = trim($_POST['phone'] ?? '');
$email      = trim($_POST['email'] ?? '');
$nationalId = trim($_POST['national_id'] ?? '');
$notes      = trim($_POST['notes'] ?? '');
$agree      = !empty($_POST['agree']);
$branchId   = trim($_POST['branch_id'] ?? '');

function back_url($type, $target, $lang, $errorCode, $fields) {
    $base   = $type === 'hall' ? 'book-hall.php' : 'book.php';
    $params = $type === 'hall'
        ? ['hall' => $target['hall']]
        : ['course' => $target['course']];
    $params['error']     = $errorCode;
    $params['lang_hint'] = $lang;
    foreach ($fields as $k => $v) $params[$k] = $v;
    return $base . '?' . http_build_query($params);
}

$carryFields = [
    'full_name' => $fullName, 'phone' => $phone,
    'email' => $email, 'national_id' => $nationalId, 'notes' => $notes,
];

if ($type === 'course') {
    $courseId = $_POST['course_id'] ?? '';
    $courses  = load_courses();
    $course   = find_course($courses, $courseId);
    $target   = ['course' => $courseId];

    // Branch required for courses
    if ($branchId === '') {
        header('Location: ' . back_url($type, $target, $lang, 'validation', $carryFields));
        exit;
    }
} else {
    $hallId      = $_POST['hall_id'] ?? '';
    $bookingDate = trim($_POST['booking_date'] ?? '');
    $startTime   = trim($_POST['start_time'] ?? '');
    $endTime     = trim($_POST['end_time'] ?? '');
    $personCount = (int)($_POST['person_count'] ?? 1);

    $halls = load_halls();
    $hall  = find_hall($halls, $hallId);
    $target = ['hall' => $hallId];

    $carryFields['booking_date'] = $bookingDate;
    $carryFields['start_time']   = $startTime;
    $carryFields['end_time']     = $endTime;
}

/* ---------- Validation ---------- */
$valid = true;
if ($fullName === '' || $phone === '') $valid = false;
if (!validate_phone_eg($phone)) $valid = false;
if (!validate_email_addr($email)) $valid = false;
if (!validate_national_id($nationalId)) $valid = false;
if (!$agree) $valid = false;

if ($type === 'course' && !$course) $valid = false;
if ($type === 'hall') {
    if (!$hall || $bookingDate === '' || $startTime === '' || $endTime === '') $valid = false;
    if ($bookingDate !== '' && $bookingDate < date('Y-m-d', strtotime('+1 day'))) {
        header('Location: ' . back_url($type, $target, $lang, 'dateError', $carryFields)); exit;
    }
    if ($startTime !== '' && $endTime !== '' && $endTime <= $startTime) {
        header('Location: ' . back_url($type, $target, $lang, 'timeError', $carryFields)); exit;
    }
}

if (!$valid) {
    header('Location: ' . back_url($type, $target, $lang, 'validation', $carryFields));
    exit;
}

if (is_phone_blocked($phone)) {
    header('Location: ' . back_url($type, $target, $lang, 'blocked', $carryFields));
    exit;
}

if ($type === 'hall') {
    if (hall_slot_conflicts($hallId, $bookingDate, $startTime, $endTime)) {
        header('Location: ' . back_url($type, $target, $lang, 'conflict', $carryFields));
        exit;
    }
}

// Duplicate check (course)
if ($type === 'course' && has_duplicate_booking($phone, 'course', $courseId)) {
    header('Location: ' . back_url($type, $target, $lang, 'duplicate', $carryFields));
    exit;
}

/* ---------- File Uploads: Payment receipt photo & ID photo ---------- */
$ref = gen_booking_ref();

// 1. Payment photo (required)
$payUpload = handle_id_photo_upload('payment_photo', $ref . '_pay');
if (!$payUpload['ok']) {
    $code = $payUpload['error'] === 'bad_type' ? 'filetype' : ($payUpload['error'] === 'too_large' ? 'filesize' : 'payment');
    header('Location: ' . back_url($type, $target, $lang, $code, $carryFields));
    exit;
}

// 2. ID photo (required or fallback)
$idUpload = handle_id_photo_upload('id_photo', $ref . '_id');
if (!$idUpload['ok']) {
    $code = $idUpload['error'] === 'bad_type' ? 'filetype' : ($idUpload['error'] === 'too_large' ? 'filesize' : 'idcard');
    header('Location: ' . back_url($type, $target, $lang, $code, $carryFields));
    exit;
}

/* ---------- Build + persist the booking ---------- */
$booking = [
    'id'                     => gen_uid('bk'),
    'ref'                    => $ref,
    'type'                   => $type,
    'status'                 => 'pending',
    'notes'                  => $notes,
    'paymentPhotoPath'       => $payUpload['path'],
    'paymentPhotoCloudinary' => $payUpload['isCloudinary'] ?? false,
    'idPhotoPath'            => $idUpload['path'],
    'idPhotoCloudinary'      => $idUpload['isCloudinary'] ?? false,
    'branchId'               => $branchId,
    'createdAt'              => date('c'),
];

if ($type === 'course') {
    $booking['courseId'] = $courseId;
} else {
    $slotId = gen_uid('slot');
    $booking['hallId']    = $hallId;
    $booking['slotId']    = $slotId;
    $booking['slotDate']  = $bookingDate;
    $booking['slotStart'] = $startTime;
    $booking['slotEnd']   = $endTime;
    if ($branchId === '') $booking['branchId'] = $hall['branchId'] ?? '';
}

$profile = ['phone' => $phone, 'fullName' => $fullName, 'email' => $email, 'nationalId' => $nationalId];
add_booking_for_customer($phone, $profile, $booking);

// Note: seatsBooked is intentionally NOT incremented here. A pending course
// request doesn't occupy a seat until the admin actually confirms it (see
// admin/bookings.php) — this keeps seatsBooked meaning "confirmed enrollments".
if ($type === 'hall') {
    // Create a dynamic slot entry in halls.json
    $slotEntry = [
        'id'     => $slotId,
        'date'   => $bookingDate,
        'start'  => $startTime,
        'end'    => $endTime,
        'status' => 'pending',
        'note'   => ['ar' => $ref, 'en' => $ref],
        'bookingId' => $booking['id'],
    ];
    update_json_file(HALLS_FILE, function ($data) use ($hallId, $slotEntry) {
        foreach ($data as &$h) {
            if ($h['id'] === $hallId) $h['slots'][] = $slotEntry;
        }
        unset($h);
        return $data;
    });
}

setcookie('lct_lang', $lang, time() + 60 * 60 * 24 * 365, '/');
header('Location: booking-success.php?ref=' . urlencode($ref) . '&phone=' . urlencode($phone));
exit;
