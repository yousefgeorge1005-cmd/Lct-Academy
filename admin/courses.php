<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/translations.php';
require_admin();
$lang = current_lang(); $theme = current_theme(); $T = translations($lang); $dir = $T['dir'];
$f = $T['admin']['courses'];
$notice = '';

/* ---------- handle POST (create / update / delete) ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'save';
    if ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        update_json_file(COURSES_FILE, function ($data) use ($id) {
            return array_values(array_filter($data, function ($c) use ($id) { return $c['id'] !== $id; }));
        });
        header('Location: courses.php?deleted=1');
        exit;
    }

    $courseId = trim($_POST['course_id'] ?? '');
    $curriculumAr = array_values(array_filter(array_map('trim', explode("\n", $_POST['curriculum_ar'] ?? ''))));
    $curriculumEn = array_values(array_filter(array_map('trim', explode("\n", $_POST['curriculum_en'] ?? ''))));
    $data = [
        'code' => trim($_POST['code'] ?? ''),
        'category' => ($_POST['category'] ?? 'language') === 'computer' ? 'computer' : 'language',
        'icon' => $_POST['icon'] ?? 'book-open',
        'status' => ($_POST['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active',
        'name' => ['ar' => trim($_POST['name_ar'] ?? ''), 'en' => trim($_POST['name_en'] ?? '')],
        'tagline' => ['ar' => trim($_POST['tagline_ar'] ?? ''), 'en' => trim($_POST['tagline_en'] ?? '')],
        'desc' => ['ar' => trim($_POST['desc_ar'] ?? ''), 'en' => trim($_POST['desc_en'] ?? '')],
        'level' => ['ar' => trim($_POST['level_ar'] ?? ''), 'en' => trim($_POST['level_en'] ?? '')],
        'duration' => ['ar' => trim($_POST['duration_ar'] ?? ''), 'en' => trim($_POST['duration_en'] ?? '')],
        'price' => (int)($_POST['price'] ?? 0),
        'instructor' => ['ar' => trim($_POST['instructor_ar'] ?? ''), 'en' => trim($_POST['instructor_en'] ?? '')],
        'schedule' => ['ar' => trim($_POST['schedule_ar'] ?? ''), 'en' => trim($_POST['schedule_en'] ?? '')],
        'seatsTotal' => (int)($_POST['seats_total'] ?? 0),
        'seatsBooked' => (int)($_POST['seats_booked'] ?? 0),
        'curriculum' => ['ar' => $curriculumAr, 'en' => $curriculumEn],
        'prerequisites' => ['ar' => trim($_POST['prereq_ar'] ?? ''), 'en' => trim($_POST['prereq_en'] ?? '')],
    ];

    if ($data['name']['ar'] === '' || $data['name']['en'] === '' || $data['code'] === '') {
        $notice = 'error:' . ($lang === 'ar' ? 'الاسم والكود مطلوبين' : 'Name and code are required');
    } else {
        update_json_file(COURSES_FILE, function ($all) use ($courseId, $data) {
            if ($courseId !== '') {
                foreach ($all as &$c) { if ($c['id'] === $courseId) { $c = array_merge($c, $data); } }
                unset($c);
                return $all;
            }
            $data['id'] = gen_uid('course');
            $all[] = $data;
            return $all;
        });
        header('Location: courses.php?saved=1');
        exit;
    }
}

$courses = load_courses();
$editCourse = isset($_GET['edit']) ? find_course($courses, $_GET['edit']) : null;
$active_tab = 'courses';
require __DIR__ . '/../includes/admin_shell_start.php';

$iconOptions = ['book-open', 'languages', 'monitor', 'award', 'image', 'layers', 'sparkles', 'graduation-cap'];
?>
<div class="admin-content-head"><h2><?= h($f['title']) ?></h2>
  <a href="courses.php" class="btn btn-primary"><span data-lucide="plus" style="width:16px;height:16px"></span><?= h($f['add']) ?></a>
</div>

<?php if (isset($_GET['saved'])): ?><div class="toast-banner success"><span data-lucide="check-circle-2" style="width:16px;height:16px"></span><?= h($T['common']['saved']) ?></div><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><div class="toast-banner success"><span data-lucide="check-circle-2" style="width:16px;height:16px"></span><?= $lang === 'ar' ? 'تم الحذف' : 'Deleted' ?></div><?php endif; ?>
<?php if ($notice): [$type, $msg] = explode(':', $notice, 2); ?><div class="toast-banner error"><span data-lucide="alert-triangle" style="width:16px;height:16px"></span><?= h($msg) ?></div><?php endif; ?>

<div class="card" style="padding:26px;margin-bottom:30px">
  <h3 style="font-size:17px;margin-bottom:18px"><?= $editCourse ? h($f['edit']) . ': ' . h($editCourse['name'][$lang]) : h($f['add']) ?></h3>
  <form method="post">
    <input type="hidden" name="course_id" value="<?= h($editCourse['id'] ?? '') ?>">
    <div class="form-row-2">
      <div class="form-group"><label class="form-label"><?= h($f['fields']['nameAr']) ?></label><input class="form-input" name="name_ar" value="<?= h($editCourse['name']['ar'] ?? '') ?>"></div>
      <div class="form-group"><label class="form-label"><?= h($f['fields']['nameEn']) ?></label><input class="form-input" name="name_en" value="<?= h($editCourse['name']['en'] ?? '') ?>" dir="ltr"></div>
    </div>
    <div class="form-row-2">
      <div class="form-group"><label class="form-label"><?= h($f['fields']['code']) ?></label><input class="form-input" name="code" value="<?= h($editCourse['code'] ?? '') ?>" dir="ltr" placeholder="LANG-EN-01"></div>
      <div class="form-group"><label class="form-label"><?= h($f['fields']['category']) ?></label>
        <select class="form-select" name="category">
          <option value="language" <?= ($editCourse['category'] ?? '') === 'language' ? 'selected' : '' ?>><?= h($T['coursesPage']['language']) ?></option>
          <option value="computer" <?= ($editCourse['category'] ?? '') === 'computer' ? 'selected' : '' ?>><?= h($T['coursesPage']['computer']) ?></option>
        </select>
      </div>
    </div>
    <div class="form-row-2">
      <div class="form-group"><label class="form-label"><?= $lang === 'ar' ? 'الأيقونة' : 'Icon' ?></label>
        <select class="form-select" name="icon">
          <?php foreach ($iconOptions as $ic): ?><option value="<?= h($ic) ?>" <?= ($editCourse['icon'] ?? '') === $ic ? 'selected' : '' ?>><?= h($ic) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="form-group"><label class="form-label"><?= h($f['fields']['status']) ?></label>
        <select class="form-select" name="status">
          <option value="active" <?= ($editCourse['status'] ?? 'active') === 'active' ? 'selected' : '' ?>><?= h($f['active']) ?></option>
          <option value="inactive" <?= ($editCourse['status'] ?? '') === 'inactive' ? 'selected' : '' ?>><?= h($f['inactive']) ?></option>
        </select>
      </div>
    </div>
    <div class="form-row-2">
      <div class="form-group"><label class="form-label"><?= $lang === 'ar' ? 'الجملة التعريفية (عربي)' : 'Tagline (Arabic)' ?></label><input class="form-input" name="tagline_ar" value="<?= h($editCourse['tagline']['ar'] ?? '') ?>"></div>
      <div class="form-group"><label class="form-label"><?= $lang === 'ar' ? 'الجملة التعريفية (إنجليزي)' : 'Tagline (English)' ?></label><input class="form-input" name="tagline_en" value="<?= h($editCourse['tagline']['en'] ?? '') ?>" dir="ltr"></div>
    </div>
    <div class="form-row-2">
      <div class="form-group"><label class="form-label"><?= h($f['fields']['descAr']) ?></label><textarea class="form-textarea" name="desc_ar"><?= h($editCourse['desc']['ar'] ?? '') ?></textarea></div>
      <div class="form-group"><label class="form-label"><?= h($f['fields']['descEn']) ?></label><textarea class="form-textarea" name="desc_en" dir="ltr"><?= h($editCourse['desc']['en'] ?? '') ?></textarea></div>
    </div>
    <div class="form-row-2">
      <div class="form-group"><label class="form-label"><?= h($f['fields']['levelAr']) ?></label><input class="form-input" name="level_ar" value="<?= h($editCourse['level']['ar'] ?? '') ?>"></div>
      <div class="form-group"><label class="form-label"><?= h($f['fields']['levelEn']) ?></label><input class="form-input" name="level_en" value="<?= h($editCourse['level']['en'] ?? '') ?>" dir="ltr"></div>
    </div>
    <div class="form-row-2">
      <div class="form-group"><label class="form-label"><?= h($f['fields']['duration']) ?> (AR)</label><input class="form-input" name="duration_ar" value="<?= h($editCourse['duration']['ar'] ?? '') ?>"></div>
      <div class="form-group"><label class="form-label"><?= h($f['fields']['duration']) ?> (EN)</label><input class="form-input" name="duration_en" value="<?= h($editCourse['duration']['en'] ?? '') ?>" dir="ltr"></div>
    </div>
    <div class="form-row-2">
      <div class="form-group"><label class="form-label"><?= h($f['fields']['price']) ?></label><input class="form-input" type="number" name="price" value="<?= h($editCourse['price'] ?? 1000) ?>" dir="ltr"></div>
      <div></div>
    </div>
    <div class="form-row-2">
      <div class="form-group"><label class="form-label"><?= h($f['fields']['instructorAr']) ?></label><input class="form-input" name="instructor_ar" value="<?= h($editCourse['instructor']['ar'] ?? '') ?>"></div>
      <div class="form-group"><label class="form-label"><?= h($f['fields']['instructorEn']) ?></label><input class="form-input" name="instructor_en" value="<?= h($editCourse['instructor']['en'] ?? '') ?>" dir="ltr"></div>
    </div>
    <div class="form-row-2">
      <div class="form-group"><label class="form-label"><?= h($f['fields']['scheduleAr'] ?? '') ?></label><input class="form-input" name="schedule_ar" value="<?= h($editCourse['schedule']['ar'] ?? '') ?>"></div>
      <div class="form-group"><label class="form-label"><?= $lang === 'ar' ? 'المواعيد (إنجليزي)' : 'Schedule (English)' ?></label><input class="form-input" name="schedule_en" value="<?= h($editCourse['schedule']['en'] ?? '') ?>" dir="ltr"></div>
    </div>
    <div class="form-row-2">
      <div class="form-group"><label class="form-label"><?= h($f['fields']['seatsTotal']) ?></label><input class="form-input" type="number" name="seats_total" value="<?= h($editCourse['seatsTotal'] ?? 16) ?>" dir="ltr"></div>
      <div class="form-group"><label class="form-label"><?= h($f['fields']['seatsBooked']) ?></label><input class="form-input" type="number" name="seats_booked" value="<?= h($editCourse['seatsBooked'] ?? 0) ?>" dir="ltr"></div>
    </div>
    <div class="form-row-2">
      <div class="form-group"><label class="form-label"><?= $lang === 'ar' ? 'محاور الكورس (سطر لكل نقطة، عربي)' : 'Curriculum (one per line, Arabic)' ?></label><textarea class="form-textarea" name="curriculum_ar" rows="5"><?= h(implode("\n", $editCourse['curriculum']['ar'] ?? [])) ?></textarea></div>
      <div class="form-group"><label class="form-label"><?= $lang === 'ar' ? 'محاور الكورس (إنجليزي)' : 'Curriculum (English)' ?></label><textarea class="form-textarea" name="curriculum_en" dir="ltr" rows="5"><?= h(implode("\n", $editCourse['curriculum']['en'] ?? [])) ?></textarea></div>
    </div>
    <div class="form-row-2">
      <div class="form-group"><label class="form-label"><?= h($T['course']['prerequisites']) ?> (AR)</label><input class="form-input" name="prereq_ar" value="<?= h($editCourse['prerequisites']['ar'] ?? '') ?>"></div>
      <div class="form-group"><label class="form-label"><?= h($T['course']['prerequisites']) ?> (EN)</label><input class="form-input" name="prereq_en" value="<?= h($editCourse['prerequisites']['en'] ?? '') ?>" dir="ltr"></div>
    </div>
    <div style="display:flex;gap:10px">
      <button type="submit" class="btn btn-primary"><span data-lucide="save" style="width:16px;height:16px"></span><?= h($T['common']['save']) ?></button>
      <?php if ($editCourse): ?><a href="courses.php" class="btn btn-ghost"><?= h($T['common']['cancel']) ?></a><?php endif; ?>
    </div>
  </form>
</div>

<div class="table-wrap">
  <table class="data-table">
    <thead><tr><th><?= h($f['table']['code']) ?></th><th><?= h($f['table']['name']) ?></th><th><?= h($f['table']['category']) ?></th><th><?= h($f['table']['price']) ?></th><th><?= h($f['table']['seats']) ?></th><th><?= h($f['table']['status']) ?></th><th><?= h($f['table']['actions']) ?></th></tr></thead>
    <tbody>
      <?php foreach ($courses as $c): ?>
        <tr>
          <td class="cell-muted" dir="ltr"><?= h($c['code']) ?></td>
          <td class="cell-strong"><?= h($c['name'][$lang]) ?></td>
          <td><?= $c['category'] === 'language' ? h($T['coursesPage']['language']) : h($T['coursesPage']['computer']) ?></td>
          <td><?= h($c['price']) ?> <?= h($T['course']['egp']) ?></td>
          <td>
            <?= h($c['seatsBooked']) ?>/<?= h($c['seatsTotal']) ?>
            <?php if ((int)$c['seatsBooked'] >= (int)$c['seatsTotal']): ?>
              <span class="badge badge-confirmed" style="font-size:10px; padding:2px 6px; margin-inline-start:4px; background:#10b981; color:#fff;"><?= $lang === 'ar' ? 'مكتمل' : 'Full' ?></span>
            <?php endif; ?>
          </td>
          <td><span class="badge <?= $c['status'] === 'active' ? 'badge-confirmed' : 'badge-neutral' ?>"><?= $c['status'] === 'active' ? h($f['active']) : h($f['inactive']) ?></span></td>
          <td>
            <div class="row-actions">
              <a href="roster.php?course=<?= urlencode($c['id']) ?>" class="icon-btn" aria-label="roster" title="<?= $lang === 'ar' ? 'إضافة طلاب وإدارة الكشف' : 'Enroll Students & Roster' ?>" style="color:#10b981;"><span data-lucide="user-plus" style="width:15px;height:15px"></span></a>
              <a href="roster.php?course=<?= urlencode($c['id']) ?>" class="icon-btn" aria-label="roster" title="<?= h($f['rosterLink']) ?>"><span data-lucide="file-spreadsheet" style="width:15px;height:15px"></span></a>
              <a href="roster.php?course=<?= urlencode($c['id']) ?>&export_pdf=1" target="_blank" class="icon-btn" aria-label="pdf" title="<?= $lang === 'ar' ? 'طباعة كشف غياب PDF' : 'Print Attendance PDF' ?>" style="color:#0284c7;"><span data-lucide="printer" style="width:15px;height:15px"></span></a>
              <a href="courses.php?edit=<?= urlencode($c['id']) ?>" class="icon-btn" aria-label="edit"><span data-lucide="pencil" style="width:15px;height:15px"></span></a>
              <form method="post" data-confirm="<?= h($f['deleteConfirm']) ?>" style="display:inline">
                <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= h($c['id']) ?>">
                <button type="submit" class="icon-btn" aria-label="delete"><span data-lucide="trash-2" style="width:15px;height:15px"></span></button>
              </form>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php require __DIR__ . '/../includes/admin_shell_end.php'; ?>
