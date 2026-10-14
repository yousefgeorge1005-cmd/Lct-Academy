<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/translations.php';
require_admin();

$lang = current_lang();
$T    = translations($lang);
$type = ($_GET['type'] ?? 'bookings') === 'customers' ? 'customers' : 'bookings';

if ($type === 'customers') {
    $filename  = 'LCT-customers-' . date('Y-m-d') . '.csv';
    $customers = list_all_customers();

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM

    $headers = $lang === 'ar'
        ? ['#', 'الاسم بالكامل', 'رقم الموبايل', 'البريد الإلكتروني', 'الرقم القومي', 'عدد الحجوزات', 'تاريخ التسجيل']
        : ['#', 'Full Name', 'Mobile', 'Email', 'National ID', 'Bookings Count', 'Registered Date'];
    fputcsv($out, $headers);

    foreach ($customers as $idx => $c) {
        $phoneCell = !empty($c['phone']) ? '="' . $c['phone'] . '"' : '';
        $nidCell   = !empty($c['nationalId']) ? '="' . $c['nationalId'] . '"' : '';
        fputcsv($out, [
            $idx + 1,
            $c['fullName'] ?? '',
            $phoneCell,
            $c['email'] ?? '',
            $nidCell,
            count($c['bookings'] ?? []),
            format_date_h($c['createdAt'] ?? '', $lang),
        ]);
    }
    fclose($out);
    exit;
}

// Default: Bookings CSV
$courses   = load_courses();  $courseById = []; foreach ($courses as $c) $courseById[$c['id']] = $c;
$halls     = load_halls();    $hallById   = []; foreach ($halls as $h) $hallById[$h['id']] = $h;
$branches  = load_branches(); $branchById = []; foreach ($branches as $br) $branchById[$br['id']] = $br;
$bookings  = all_bookings();

$filename = 'LCT-bookings-' . date('Y-m-d') . '.csv';
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF");

$headers = $lang === 'ar'
    ? ['رقم المرجع', 'النوع', 'الاسم بالكامل', 'رقم الموبايل', 'البريد الإلكتروني', 'الرقم القومي', 'الكورس/القاعة', 'الكود', 'الفرع', 'ميعاد القاعة', 'الحالة', 'تاريخ التقديم', 'ملاحظات']
    : ['Reference', 'Type', 'Full Name', 'Mobile', 'Email', 'National ID', 'Course/Hall', 'Code', 'Branch', 'Hall Slot', 'Status', 'Submitted On', 'Notes'];
fputcsv($out, $headers);

foreach ($bookings as $b) {
    $isHall     = ($b['type'] ?? 'course') === 'hall';
    $itemName   = $isHall ? ($hallById[$b['hallId']]['name'][$lang] ?? $b['hallId']) : ($courseById[$b['courseId']]['name'][$lang] ?? $b['courseId']);
    $code       = $isHall ? '' : ($courseById[$b['courseId']]['code'] ?? '');
    $branchName = $branchById[$b['branchId'] ?? '']['name'][$lang] ?? '';
    $slotInfo   = $isHall ? (($b['slotDate'] ?? '') . ' ' . ($b['slotStart'] ?? '') . '-' . ($b['slotEnd'] ?? '')) : '';
    $phoneCell  = !empty($b['phone']) ? '="' . $b['phone'] . '"' : '';
    $nidCell    = !empty($b['nationalId']) ? '="' . $b['nationalId'] . '"' : '';

    fputcsv($out, [
        $b['ref'] ?? '',
        $isHall ? ($T['admin']['bookings']['typeHall'] ?? 'قاعة') : ($T['admin']['bookings']['typeCourse'] ?? 'كورس'),
        $b['fullName'] ?? '',
        $phoneCell,
        $b['email'] ?? '',
        $nidCell,
        $itemName,
        $code,
        $branchName,
        $slotInfo,
        $T['status'][$b['status']] ?? $b['status'],
        format_datetime_h($b['createdAt'] ?? '', $lang),
        $b['notes'] ?? '',
    ]);
}
fclose($out);
exit;
