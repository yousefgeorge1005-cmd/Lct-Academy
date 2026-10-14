<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/translations.php';
require_admin();

$lang = current_lang();
$T    = translations($lang);
$type = ($_GET['type'] ?? 'bookings') === 'customers' ? 'customers' : 'bookings';

require_once __DIR__ . '/../includes/xlsx_writer.php';

if (!class_exists('ZipArchive')) {
    // Fallback to CSV export if ZipArchive is missing on PHP server
    header('Location: export_csv.php?type=' . $type);
    exit;
}

$courses  = load_courses();  $courseById = []; foreach ($courses as $c) $courseById[$c['id']] = $c;
$halls    = load_halls();    $hallById   = []; foreach ($halls as $h) $hallById[$h['id']] = $h;
$branches = load_branches(); $branchById = []; foreach ($branches as $br) $branchById[$br['id']] = $br;

$writer = new SimpleXlsxWriter();
$writer->setRtl($lang === 'ar');

if ($type === 'customers') {
    $filename  = 'LCT-customers-' . date('Y-m-d') . '.xlsx';
    $customers = list_all_customers();

    $writer->setSheetName($lang === 'ar' ? 'سجل العملاء' : 'Customer List');
    $writer->setColumnWidths([6, 26, 16, 26, 18, 12, 16]);

    $title = $lang === 'ar' ? 'قائمة العملاء المسجلين — أكاديمية LCT' : 'Registered Customers — LCT Academy';
    $writer->addRow([['value' => $title, 'style' => SimpleXlsxWriter::STYLE_TITLE]], ['merge' => 7, 'height' => 30]);

    $headers = $lang === 'ar'
        ? ['#', 'اسم العميل', 'رقم الموبايل', 'البريد الإلكتروني', 'الرقم القومي', 'عدد الحجوزات', 'تاريخ التسجيل']
        : ['#', 'Customer Name', 'Mobile', 'Email', 'National ID', 'Bookings', 'Registered Date'];
    $writer->addRow(array_map(fn($h) => ['value' => $h, 'style' => SimpleXlsxWriter::STYLE_HEADER], $headers), ['height' => 32]);

    foreach ($customers as $idx => $c) {
        $bookingsCount = count($c['bookings'] ?? []);
        $writer->addRow([
            ['value' => $idx + 1, 'style' => SimpleXlsxWriter::STYLE_CELL],
            ['value' => $c['fullName'] ?? '', 'style' => SimpleXlsxWriter::STYLE_CELL],
            ['value' => $c['phone'] ?? '', 'style' => SimpleXlsxWriter::STYLE_CELL],
            ['value' => $c['email'] ?? '', 'style' => SimpleXlsxWriter::STYLE_CELL],
            ['value' => $c['nationalId'] ?? '', 'style' => SimpleXlsxWriter::STYLE_CELL],
            ['value' => $bookingsCount, 'style' => SimpleXlsxWriter::STYLE_CELL],
            ['value' => format_date_h($c['createdAt'] ?? '', $lang), 'style' => SimpleXlsxWriter::STYLE_CELL],
        ], ['height' => 24]);
    }
} else {
    // Bookings export
    $filename = 'LCT-bookings-' . date('Y-m-d') . '.xlsx';
    $bookings = all_bookings();

    $writer->setSheetName($lang === 'ar' ? 'سجل الحجوزات' : 'Bookings Roster');
    $writer->setColumnWidths([14, 10, 24, 16, 24, 18, 22, 14, 20, 12, 16]);

    $title = $lang === 'ar' ? 'سجل كافة الحجوزات — أكاديمية LCT' : 'All Bookings Report — LCT Academy';
    $writer->addRow([['value' => $title, 'style' => SimpleXlsxWriter::STYLE_TITLE]], ['merge' => 11, 'height' => 30]);

    $headers = $lang === 'ar'
        ? ['رقم المرجع', 'النوع', 'اسم العميل', 'رقم الموبايل', 'البريد الإلكتروني', 'الرقم القومي', 'الكورس/القاعة', 'الفرع', 'ميعاد القاعة', 'الحالة', 'تاريخ الطلب']
        : ['Ref Code', 'Type', 'Customer Name', 'Mobile', 'Email', 'National ID', 'Course/Hall', 'Branch', 'Hall Time Slot', 'Status', 'Date'];
    $writer->addRow(array_map(fn($h) => ['value' => $h, 'style' => SimpleXlsxWriter::STYLE_HEADER], $headers), ['height' => 32]);

    foreach ($bookings as $b) {
        $isHall     = ($b['type'] ?? 'course') === 'hall';
        $itemName   = $isHall ? ($hallById[$b['hallId']]['name'][$lang] ?? $b['hallId']) : ($courseById[$b['courseId']]['name'][$lang] ?? $b['courseId']);
        $branchName = $branchById[$b['branchId'] ?? '']['name'][$lang] ?? '';
        $slotInfo   = $isHall ? (($b['slotDate'] ?? '') . ' (' . ($b['slotStart'] ?? '') . ' - ' . ($b['slotEnd'] ?? '') . ')') : '-';
        $statusLabel= $T['status'][$b['status']] ?? $b['status'];

        $writer->addRow([
            ['value' => $b['ref'] ?? '', 'style' => SimpleXlsxWriter::STYLE_CELL],
            ['value' => $isHall ? ($T['admin']['bookings']['typeHall'] ?? 'قاعة') : ($T['admin']['bookings']['typeCourse'] ?? 'كورس'), 'style' => SimpleXlsxWriter::STYLE_CELL],
            ['value' => $b['fullName'] ?? '', 'style' => SimpleXlsxWriter::STYLE_CELL],
            ['value' => $b['phone'] ?? '', 'style' => SimpleXlsxWriter::STYLE_CELL],
            ['value' => $b['email'] ?? '', 'style' => SimpleXlsxWriter::STYLE_CELL],
            ['value' => $b['nationalId'] ?? '', 'style' => SimpleXlsxWriter::STYLE_CELL],
            ['value' => $itemName, 'style' => SimpleXlsxWriter::STYLE_CELL],
            ['value' => $branchName, 'style' => SimpleXlsxWriter::STYLE_CELL],
            ['value' => $slotInfo, 'style' => SimpleXlsxWriter::STYLE_CELL],
            ['value' => $statusLabel, 'style' => SimpleXlsxWriter::STYLE_CELL],
            ['value' => format_date_h($b['createdAt'] ?? '', $lang), 'style' => SimpleXlsxWriter::STYLE_CELL],
        ], ['height' => 24]);
    }
}

$tmpPath = sys_get_temp_dir() . '/' . $filename;
if ($writer->saveToFile($tmpPath)) {
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($tmpPath));
    readfile($tmpPath);
    @unlink($tmpPath);
    exit;
} else {
    // Fallback if save fails
    header('Location: export_csv.php?type=' . $type);
    exit;
}
