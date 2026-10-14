<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/translations.php';
require_admin();
$lang = current_lang(); $theme = current_theme(); $T = translations($lang); $dir = $T['dir'];
$rt = $T['admin']['roster'];

$courses = load_courses();
$course  = find_course($courses, $_GET['course'] ?? '');

if (!$course) {
    header('Location: courses.php');
    exit;
}

// Roster = anyone actually accepted into the course (confirmed, or already marked completed).
$roster = array_values(array_filter(all_bookings(), function ($b) use ($course) {
    return ($b['type'] ?? 'course') === 'course'
        && ($b['courseId'] ?? '') === $course['id']
        && in_array($b['status'], ['confirmed', 'completed'], true);
}));
usort($roster, fn($a, $b) => strcmp($a['fullName'] ?? '', $b['fullName'] ?? ''));

/* ===================== POST Actions: Add student directly to course ===================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add_student') {
        $phone      = trim($_POST['phone'] ?? '');
        $fullName   = trim($_POST['full_name'] ?? '');
        $nationalId = trim($_POST['national_id'] ?? '');
        $paidAmount = (float)($_POST['paid_amount'] ?? 0);

        if (!empty($phone) && !empty($fullName)) {
            enroll_student_in_course($phone, $fullName, $nationalId, $course['id'], '', $paidAmount);
            header('Location: roster.php?course=' . urlencode($course['id']) . '&added=1');
            exit;
        }
    }
}

$sessions = max(1, min(60, (int)($_GET['sessions'] ?? 8)));

/* ===================== PDF / Print Attendance Sheet mode ===================== */
if (isset($_GET['export_pdf'])) {
    $courseName = $course['name'][$lang];
    $todayLabel = format_date_h(date('c'), $lang);
    ?>
    <!DOCTYPE html>
    <html lang="<?= $lang ?>" dir="<?= $dir ?>">
    <head>
    <meta charset="UTF-8">
    <title>كشف حضور وغياب — <?= h($courseName) ?></title>
    <style>
      :root { font-family: system-ui, -apple-system, sans-serif; }
      body { margin: 20px; color: #111; direction: <?= $dir ?>; background: #fff; }
      .sheet-header { border-bottom: 2px solid #000; padding-bottom: 12px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: flex-end; }
      .sheet-title { font-size: 22px; font-weight: bold; margin: 0 0 4px 0; }
      .sheet-meta { font-size: 13px; color: #444; }
      .print-bar { margin-bottom: 20px; display: flex; gap: 10px; }
      .btn-print { background: #0284c7; color: #fff; border: none; padding: 10px 20px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 14px; }
      @media print { .print-bar { display: none; } body { margin: 0; } }
      table.roster-grid { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 12px; }
      table.roster-grid th, table.roster-grid td { border: 1.5px solid #000; padding: 8px 6px; text-align: center; vertical-align: middle; }
      table.roster-grid th { background-color: #f1f5f9; font-weight: bold; }
      .name-col { text-align: <?= $lang === 'ar' ? 'right' : 'left' ?> !important; padding-right: 10px; }
      .check-box { width: 26px; height: 26px; border: 1px dashed #666; margin: 0 auto; border-radius: 3px; }
      .sheet-footer { margin-top: 35px; display: flex; justify-content: space-between; font-size: 13px; border-top: 1px solid #aaa; padding-top: 12px; }
    </style>
    </head>
    <body>

    <div class="print-bar">
      <button onclick="window.print()" class="btn-print">🖨️ <?= $lang === 'ar' ? 'طباعة كشف الحضور والغياب (PDF)' : 'Print Attendance Sheet (PDF)' ?></button>
      <button onclick="window.close()" class="btn-print" style="background:#64748b;"><?= $lang === 'ar' ? 'إغلاق Window' : 'Close' ?></button>
    </div>

    <div class="sheet-header">
      <div>
        <h1 class="sheet-title">📋 <?= $lang === 'ar' ? 'كشف حضور وغياب الطلاب' : 'Student Attendance Sheet' ?></h1>
        <div class="sheet-meta">
          <strong>الكورس:</strong> <?= h($courseName) ?> (<?= h($course['code'] ?? '') ?>) | 
          <strong>المحاضر:</strong> <?= h($course['instructor'][$lang] ?? '—') ?> | 
          <strong>عدد المحاضرات:</strong> <?= $sessions ?>
        </div>
      </div>
      <div style="text-align:<?= $lang === 'ar' ? 'left' : 'right' ?>;" class="sheet-meta">
        <div><strong>أكاديمية LCT</strong></div>
        <div><?= $lang === 'ar' ? 'التاريخ: ' . date('Y/m/d') : 'Date: ' . date('Y-m-d') ?></div>
        <div><?= $lang === 'ar' ? 'إجمالي المقاعد: ' : 'Seats: ' ?><?= count($roster) ?> / <?= $course['seatsTotal'] ?></div>
      </div>
    </div>

    <table class="roster-grid">
      <thead>
        <tr>
          <th style="width: 35px;">#</th>
          <th class="name-col" style="min-width: 180px;"><?= $lang === 'ar' ? 'اسم الطالب بالكامل' : 'Student Name' ?></th>
          <th style="width: 100px;"><?= $lang === 'ar' ? 'الموبايل' : 'Mobile' ?></th>
          <th style="width: 110px;"><?= $lang === 'ar' ? 'الرقم القومي' : 'National ID' ?></th>
          <?php for ($s = 1; $s <= $sessions; $s++): ?>
            <th style="width: 45px;"><?= $lang === 'ar' ? 'مـ ' . $s : 'L' . $s ?></th>
          <?php endfor; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($roster as $idx => $student): ?>
          <tr>
            <td><?= $idx + 1 ?></td>
            <td class="name-col"><strong><?= h($student['fullName'] ?? '') ?></strong></td>
            <td dir="ltr"><?= h($student['phone'] ?? '') ?></td>
            <td dir="ltr"><?= h($student['nationalId'] ?? '') ?></td>
            <?php for ($s = 1; $s <= $sessions; $s++): ?>
              <td><div class="check-box"></div></td>
            <?php endfor; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="sheet-footer">
      <div><?= $lang === 'ar' ? 'ملاحظات المحاضر: ........................................................................' : 'Notes: ........................................................................' ?></div>
      <div><?= $lang === 'ar' ? 'توقيع المحاضر: ....................................' : 'Instructor Signature: ....................................' ?></div>
    </div>

    </body>
    </html>
    <?php
    exit;
}

/* ===================== Export mode (Excel / CSV) ===================== */
if (isset($_GET['export'])) {
    $courseName = $course['name'][$lang];
    $todayLabel = format_date_h(date('c'), $lang);

    $headers = array_merge(
        [$rt['colNum'], $rt['colName'], $rt['colPhone'], $rt['colNationalId']],
        array_map(fn($i) => $rt['colLecture'] . ' ' . $i, range(1, $sessions))
    );

    require_once __DIR__ . '/../includes/xlsx_writer.php';
    $canXlsx = class_exists('ZipArchive');

    if ($canXlsx) {
        $w = new SimpleXlsxWriter();
        $w->setSheetName($rt['sheetName'] . ' - ' . $courseName);
        $w->setRtl($lang === 'ar');
        $w->setColumnWidths(array_merge([5, 26, 15, 15], array_fill(0, $sessions, 11)));

        $w->addRow([['value' => $rt['sheetName'] . ' — ' . $courseName . '  (' . $todayLabel . ')', 'style' => SimpleXlsxWriter::STYLE_TITLE]],
            ['merge' => count($headers), 'height' => 30]);

        $w->addRow(array_map(fn($h) => ['value' => $h, 'style' => SimpleXlsxWriter::STYLE_HEADER], $headers), ['height' => 34]);

        foreach ($roster as $i => $student) {
            $row = [
                ['value' => $i + 1, 'style' => SimpleXlsxWriter::STYLE_CELL],
                ['value' => $student['fullName'] ?? '', 'style' => SimpleXlsxWriter::STYLE_CELL],
                ['value' => $student['phone'] ?? '', 'style' => SimpleXlsxWriter::STYLE_CELL],
                ['value' => $student['nationalId'] ?? '', 'style' => SimpleXlsxWriter::STYLE_CELL],
            ];
            for ($k = 0; $k < $sessions; $k++) $row[] = ['value' => '', 'style' => SimpleXlsxWriter::STYLE_BOX];
            $w->addRow($row, ['height' => 22]);
        }

        $tmpPath = sys_get_temp_dir() . '/lct_roster_' . uniqid() . '.xlsx';
        if ($w->saveToFile($tmpPath)) {
            $downloadName = 'attendance_' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $course['code'] ?? $course['id']) . '.xlsx';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $downloadName . '"');
            header('Content-Length: ' . filesize($tmpPath));
            readfile($tmpPath);
            @unlink($tmpPath);
            exit;
        }
    }

    // CSV fallback
    $downloadName = 'attendance_' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $course['code'] ?? $course['id']) . '.csv';
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $downloadName . '"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, $headers);
    foreach ($roster as $i => $student) {
        $phoneCell = !empty($student['phone']) ? '="' . $student['phone'] . '"' : '';
        $nidCell   = !empty($student['nationalId']) ? '="' . $student['nationalId'] . '"' : '';
        $row = [$i + 1, $student['fullName'] ?? '', $phoneCell, $nidCell];
        for ($k = 0; $k < $sessions; $k++) $row[] = '';
        fputcsv($out, $row);
    }
    fclose($out);
    exit;
}

/* ===================== View mode ===================== */
$active_tab = 'courses';
require __DIR__ . '/../includes/admin_shell_start.php';
?>
<div class="admin-content-head"><h2><?= h($rt['title']) ?>: <?= h($course['name'][$lang]) ?></h2></div>

<?php if (isset($_GET['added'])): ?>
  <div class="toast-banner success"><span data-lucide="check-circle-2" style="width:16px;height:16px"></span><?= $lang === 'ar' ? 'تمت إضافة الطالب وتأكيد مقعده في الكورس بنجاح' : 'Student added and confirmed in course successfully' ?></div>
<?php endif; ?>

<!-- Quick Enroll Student Card -->
<details style="margin-bottom:24px; border:1px solid var(--border); border-radius:12px; padding:18px; background:var(--surface);">
  <summary style="cursor:pointer; font-size:15px; font-weight:700; color:var(--primary); display:flex; align-items:center; gap:8px;">
    <span data-lucide="user-plus" style="width:18px;height:18px"></span>
    <?= $lang === 'ar' ? '➕ إضافة طالب مباشرة في كشف هذا الكورس' : '➕ Add Student Directly to this Course' ?>
  </summary>

  <form method="post" style="margin-top:16px; padding-top:14px; border-top:1px solid var(--border);">
    <input type="hidden" name="action" value="add_student">
    <div class="form-row-2">
      <div class="form-group">
        <label class="form-label"><?= $lang === 'ar' ? 'اسم الطالب بالكامل' : 'Student Full Name' ?> <span style="color:#ef4444">*</span></label>
        <input type="text" name="full_name" class="form-input" required placeholder="<?= $lang === 'ar' ? 'مثال: أحمد محمد علي' : 'e.g. John Doe' ?>">
      </div>
      <div class="form-group">
        <label class="form-label"><?= $lang === 'ar' ? 'رقم الموبايل' : 'Mobile Phone' ?> <span style="color:#ef4444">*</span></label>
        <input type="text" name="phone" class="form-input" required dir="ltr" placeholder="010XXXXXXXX">
      </div>
    </div>

    <div class="form-row-2">
      <div class="form-group">
        <label class="form-label"><?= $lang === 'ar' ? 'الرقم القومي (اختياري)' : 'National ID (Optional)' ?></label>
        <input type="text" name="national_id" class="form-input" dir="ltr" maxlength="14" placeholder="14 أرقام">
      </div>
      <div class="form-group">
        <label class="form-label"><?= $lang === 'ar' ? 'المبلغ المدفوع كـ مقدم/عربون (جنيه)' : 'Paid Amount / Deposit (EGP)' ?></label>
        <input type="number" step="any" name="paid_amount" class="form-input" value="0" placeholder="0">
      </div>
    </div>

    <button type="submit" class="btn btn-primary btn-sm" style="font-weight:bold;">
      <span data-lucide="user-check" style="width:15px;height:15px"></span>
      <?= $lang === 'ar' ? 'تأكيد وإضافة الطالب للكورس' : 'Enroll Student Now' ?>
    </button>
  </form>
</details>

<div class="card" style="padding:22px;margin-bottom:24px">
  <div style="display:flex;gap:24px;flex-wrap:wrap;margin-bottom:18px">
    <div><div class="cell-muted" style="font-size:12px"><?= h($rt['confirmedCount']) ?></div><div style="font-size:20px;font-weight:700"><?= count($roster) ?></div></div>
    <div><div class="cell-muted" style="font-size:12px"><?= h($T['course']['seatsLabel'] ?? ($lang === 'ar' ? 'المقاعد' : 'Seats')) ?></div><div style="font-size:20px;font-weight:700"><?= h($course['seatsBooked']) ?>/<?= h($course['seatsTotal']) ?></div></div>
  </div>

  <?php if (empty($roster)): ?>
    <div class="toast-banner" style="background:var(--warn-bg);color:var(--warn)">
      <span data-lucide="info" style="width:16px;height:16px"></span><?= h($rt['noConfirmed']) ?>
    </div>
  <?php endif; ?>

  <form method="get" style="margin-top:10px;display:flex;gap:14px;align-items:flex-end;flex-wrap:wrap">
    <input type="hidden" name="course" value="<?= h($course['id']) ?>">
    <div class="form-group" style="margin-bottom:0;max-width:180px">
      <label class="form-label"><?= h($rt['sessionsLabel']) ?></label>
      <input class="form-input" type="number" name="sessions" min="1" max="60" value="<?= $sessions ?>" dir="ltr">
    </div>
    <button type="submit" name="export" value="1" class="btn btn-outline" <?= empty($roster) ? 'disabled' : '' ?>>
      <span data-lucide="file-spreadsheet" style="width:16px;height:16px"></span><?= h($rt['exportBtn']) ?>
    </button>
    <button type="submit" name="export_pdf" value="1" formtarget="_blank" class="btn btn-primary" <?= empty($roster) ? 'disabled' : '' ?>>
      <span data-lucide="printer" style="width:16px;height:16px"></span><?= $lang === 'ar' ? '🖨️ طباعة كشف غياب (PDF)' : '🖨️ Print Attendance PDF' ?>
    </button>
  </form>
  <?php if (!class_exists('ZipArchive')): ?>
    <p class="cell-muted" style="font-size:12px;margin-top:10px"><?= h($rt['csvFallbackNote']) ?></p>
  <?php endif; ?>
</div>

<?php if (!empty($roster)): ?>
<div class="table-wrap">
  <table class="data-table">
    <thead><tr><th>#</th><th><?= h($rt['colName']) ?></th><th><?= h($rt['colPhone']) ?></th><th><?= h($rt['colNationalId']) ?></th></tr></thead>
    <tbody>
      <?php foreach ($roster as $i => $student): ?>
        <tr>
          <td class="cell-muted"><?= $i + 1 ?></td>
          <td class="cell-strong"><?= h($student['fullName'] ?? '') ?></td>
          <td class="cell-muted" dir="ltr"><?= h($student['phone'] ?? '') ?></td>
          <td class="cell-muted" dir="ltr"><?= h($student['nationalId'] ?? '') ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>
<?php require __DIR__ . '/../includes/admin_shell_end.php'; ?>
