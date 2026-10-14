<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/translations.php';
$current_page = 'courses';

$courses = load_courses();
$course = find_course($courses, $_GET['id'] ?? '');

$lang = current_lang();
if ($course) {
    $page_title = $course['name'][$lang] ?? '';
    $page_desc  = ($course['tagline'][$lang] ?? '') . ' — ' . mb_substr($course['desc'][$lang] ?? '', 0, 150) . '...';
}

require __DIR__ . '/includes/header.php';

if (!$course): ?>
  <div class="container section">
    <div class="empty-state">
      <span data-lucide="file-warning" style="width:44px;height:44px"></span>
      <h3><?= $lang === 'ar' ? 'الكورس غير موجود' : 'Course not found' ?></h3>
    </div>
    <div style="text-align:center;margin-top:20px">
      <a href="<?= BASE_URL ?>/courses.php" class="btn btn-secondary"><?= h($T['course']['backToCourses']) ?></a>
    </div>
  </div>
<?php else:
  $seatsLeft = course_seats_left($course);
  $pct = $course['seatsTotal'] > 0 ? min(100, round(($course['seatsBooked'] / $course['seatsTotal']) * 100)) : 0;
?>
<div class="container section">
  <a href="<?= BASE_URL ?>/courses.php" class="breadcrumb-btn">
    <span data-lucide="<?= $dir === 'rtl' ? 'arrow-right' : 'arrow-left' ?>" style="width:15px;height:15px"></span> <?= h($T['course']['backToCourses']) ?>
  </a>
  <div class="detail-grid">
    <div>
      <span class="course-code"><?= h($course['code']) ?></span>
      <div class="detail-header-icon" style="margin-top:16px"><span data-lucide="<?= h($course['icon']) ?>" style="width:26px;height:26px"></span></div>
      <h1 class="detail-title"><?= h($course['name'][$lang]) ?></h1>
      <div class="detail-tagline"><?= h($course['tagline'][$lang]) ?></div>
      <p class="detail-desc"><?= h($course['desc'][$lang]) ?></p>

      <div class="detail-meta-grid">
        <div class="meta-box"><div class="meta-box-label"><span data-lucide="star" style="width:13px;height:13px"></span><?= h($T['course']['level']) ?></div><div class="meta-box-value"><?= h($course['level'][$lang]) ?></div></div>
        <div class="meta-box"><div class="meta-box-label"><span data-lucide="clock" style="width:13px;height:13px"></span><?= h($T['course']['duration']) ?></div><div class="meta-box-value"><?= h($course['duration'][$lang]) ?></div></div>
        <div class="meta-box"><div class="meta-box-label"><span data-lucide="calendar" style="width:13px;height:13px"></span><?= h($T['course']['schedule']) ?></div><div class="meta-box-value"><?= h($course['schedule'][$lang]) ?></div></div>
        <div class="meta-box"><div class="meta-box-label"><span data-lucide="user-circle" style="width:13px;height:13px"></span><?= h($T['course']['instructor']) ?></div><div class="meta-box-value"><?= h($course['instructor'][$lang]) ?></div></div>
      </div>

      <div style="margin-top:36px">
        <h3 style="font-size:20px"><?= h($T['course']['curriculum']) ?></h3>
        <div class="curriculum-list">
          <?php foreach ($course['curriculum'][$lang] as $i => $item): ?>
            <div class="curriculum-item"><span class="curriculum-num"><?= $i + 1 ?></span><span><?= h($item) ?></span></div>
          <?php endforeach; ?>
        </div>
      </div>

      <div style="margin-top:28px">
        <h3 style="font-size:17px"><?= h($T['course']['prerequisites']) ?></h3>
        <p style="margin-top:8px;color:var(--text-soft);font-size:14.5px"><?= h($course['prerequisites'][$lang]) ?></p>
      </div>
    </div>

    <div class="book-panel card">
      <div class="book-panel-price"><?= h($course['price']) ?> <?= h($T['course']['egp']) ?><small><?= h($T['course']['perCourse']) ?></small></div>
      <div class="book-panel-row"><span><?= h($T['course']['duration']) ?></span><span><?= h($course['duration'][$lang]) ?></span></div>
      <div class="book-panel-row"><span><?= h($T['course']['schedule']) ?></span><span style="text-align:end;max-width:170px"><?= h($course['schedule'][$lang]) ?></span></div>
      <a href="<?= BASE_URL ?>/book.php?course=<?= urlencode($course['id']) ?>" class="btn btn-primary btn-block"><?= h($T['course']['bookThis']) ?></a>
    </div>
  </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
