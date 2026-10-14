<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/translations.php';
require_admin();

$lang = current_lang();
$dir  = $lang === 'ar' ? 'rtl' : 'ltr';
$T    = translations($lang);
$type = ($_GET['type'] ?? 'bookings') === 'customers' ? 'customers' : 'bookings';

$courses  = load_courses();  $courseById = []; foreach ($courses as $c) $courseById[$c['id']] = $c;
$halls    = load_halls();    $hallById   = []; foreach ($halls as $h) $hallById[$h['id']] = $h;
$branches = load_branches(); $branchById = []; foreach ($branches as $br) $branchById[$br['id']] = $br;

$title = $type === 'customers'
    ? ($lang === 'ar' ? 'تقرير بيانات العملاء المسجلين' : 'Registered Customers Report')
    : ($lang === 'ar' ? 'تقرير سجل الحجوزات الشامل' : 'Full Bookings Roster Report');
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $dir ?>">
<head>
<meta charset="UTF-8">
<title><?= h($title) ?> — LCT Academy</title>
<style>
  :root { font-family: system-ui, -apple-system, sans-serif; }
  body { margin: 20px; background: #fff; color: #111; direction: <?= $dir ?>; }
  .header { display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #000; padding-bottom: 15px; margin-bottom: 20px; }
  .header img { height: 50px; }
  .header-info { text-align: <?= $lang === 'ar' ? 'left' : 'right' ?>; font-size: 13px; color: #555; }
  h1 { font-size: 20px; margin: 0 0 5px 0; }
  .print-btn { background: #0284c7; color: #fff; border: none; padding: 10px 18px; border-radius: 6px; cursor: pointer; font-weight: bold; margin-bottom: 20px; font-size: 14px; }
  .print-btn:hover { background: #0369a1; }
  @media print {
    .print-btn { display: none; }
    body { margin: 0; }
  }
  table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 12px; }
  th, td { border: 1px solid #ccc; padding: 8px 10px; text-align: <?= $lang === 'ar' ? 'right' : 'left' ?>; }
  th { background-color: #f1f5f9; font-weight: bold; }
  tr:nth-child(even) { background-color: #f9fafb; }
  .footer { margin-top: 40px; display: flex; justify-content: space-between; font-size: 13px; color: #444; border-top: 1px solid #ddd; padding-top: 15px; }
</style>
</head>
<body>

<button onclick="window.print()" class="print-btn">🖨️ <?= $lang === 'ar' ? 'طباعة / حفظ كـ PDF' : 'Print / Save as PDF' ?></button>

<div class="header">
  <div>
    <h1><?= h($title) ?></h1>
    <div style="font-size: 13px; color: #666;"><?= h($T['brand']['name']) ?> — <?= date('Y-m-d H:i') ?></div>
  </div>
  <div class="header-info">
    <div><strong>LCT Academy</strong></div>
    <div><?= $lang === 'ar' ? 'تاريخ التقرير: ' . date('Y/m/d') : 'Date: ' . date('Y-m-d') ?></div>
  </div>
</div>

<?php if ($type === 'customers'): 
  $customers = list_all_customers();
?>
  <table>
    <thead>
      <tr>
        <th>#</th>
        <th><?= $lang === 'ar' ? 'الاسم بالكامل' : 'Full Name' ?></th>
        <th><?= $lang === 'ar' ? 'رقم الموبايل' : 'Mobile' ?></th>
        <th><?= $lang === 'ar' ? 'البريد الإلكتروني' : 'Email' ?></th>
        <th><?= $lang === 'ar' ? 'الرقم القومي' : 'National ID' ?></th>
        <th><?= $lang === 'ar' ? 'عدد الحجوزات' : 'Bookings' ?></th>
        <th><?= $lang === 'ar' ? 'تاريخ التسجيل' : 'Date Registered' ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($customers as $idx => $c): ?>
        <tr>
          <td><?= $idx + 1 ?></td>
          <td><strong><?= h($c['fullName'] ?? '') ?></strong></td>
          <td><?= h($c['phone'] ?? '') ?></td>
          <td><?= h($c['email'] ?? '') ?></td>
          <td><?= h($c['nationalId'] ?? '') ?></td>
          <td><?= count($c['bookings'] ?? []) ?></td>
          <td><?= h(format_date_h($c['createdAt'] ?? '', $lang)) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php else:
  $bookings = all_bookings();
?>
  <table>
    <thead>
      <tr>
        <th><?= $lang === 'ar' ? 'المرجع' : 'Ref' ?></th>
        <th><?= $lang === 'ar' ? 'النوع' : 'Type' ?></th>
        <th><?= $lang === 'ar' ? 'الاسم' : 'Name' ?></th>
        <th><?= $lang === 'ar' ? 'الموبايل' : 'Mobile' ?></th>
        <th><?= $lang === 'ar' ? 'الكورس / القاعة' : 'Course / Hall' ?></th>
        <th><?= $lang === 'ar' ? 'الفرع' : 'Branch' ?></th>
        <th><?= $lang === 'ar' ? 'الميعاد' : 'Slot' ?></th>
        <th><?= $lang === 'ar' ? 'الحالة' : 'Status' ?></th>
        <th><?= $lang === 'ar' ? 'التاريخ' : 'Date' ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($bookings as $b):
        $isHall     = ($b['type'] ?? 'course') === 'hall';
        $itemName   = $isHall ? ($hallById[$b['hallId']]['name'][$lang] ?? $b['hallId']) : ($courseById[$b['courseId']]['name'][$lang] ?? $b['courseId']);
        $branchName = $branchById[$b['branchId'] ?? '']['name'][$lang] ?? '';
        $slotInfo   = $isHall ? (($b['slotDate'] ?? '') . ' ' . ($b['slotStart'] ?? '') . '-' . ($b['slotEnd'] ?? '')) : '-';
      ?>
        <tr>
          <td><code><?= h($b['ref'] ?? '') ?></code></td>
          <td><?= $isHall ? ($T['admin']['bookings']['typeHall'] ?? 'قاعة') : ($T['admin']['bookings']['typeCourse'] ?? 'كورس') ?></td>
          <td><strong><?= h($b['fullName'] ?? '') ?></strong></td>
          <td><?= h($b['phone'] ?? '') ?></td>
          <td><?= h($itemName) ?></td>
          <td><?= h($branchName) ?></td>
          <td><?= h($slotInfo) ?></td>
          <td><?= h($T['status'][$b['status']] ?? $b['status']) ?></td>
          <td><?= h(format_date_h($b['createdAt'] ?? '', $lang)) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

<div class="footer">
  <div><?= $lang === 'ar' ? 'تم استخراج هذا التقرير تلقائياً من لوحة تحكم أكاديمية LCT' : 'Generated automatically from LCT Academy Admin Dashboard' ?></div>
  <div><?= $lang === 'ar' ? 'توقيع الإدارة: ........................' : 'Manager Signature: ........................' ?></div>
</div>

</body>
</html>
