<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/translations.php';
$current_page = 'courses';
$page_title = null;

$courses = load_courses();
$catFilter = isset($_GET['cat']) && in_array($_GET['cat'], ['language', 'computer'], true) ? $_GET['cat'] : 'all';
$search = trim($_GET['q'] ?? '');

$lang = current_lang();
$T = translations($lang);
$page_title = $T['coursesPage']['title'];
$page_desc  = $T['coursesPage']['sub'];

require __DIR__ . '/includes/header.php';
?>

<div class="page-hero-strip">
  <div class="container">
    <span class="eyebrow"><?= h($T['nav']['courses']) ?></span>
    <h1 class="section-title" style="margin-top:10px"><?= h($T['coursesPage']['title']) ?></h1>
    <p class="section-sub"><?= h($T['coursesPage']['sub']) ?></p>
    <form method="get" class="filter-bar">
      <div class="search-box">
        <span data-lucide="search" style="width:16px;height:16px"></span>
        <input type="text" name="q" value="<?= h($search) ?>" placeholder="<?= h($T['coursesPage']['searchPh']) ?>">
      </div>
      <input type="hidden" name="cat" value="<?= h($catFilter) ?>" id="catField">
      <div class="chip-row">
        <button type="button" class="chip <?= $catFilter === 'all' ? 'active' : '' ?>" onclick="document.getElementById('catField').value='all';this.closest('form').submit();"><?= h($T['coursesPage']['all']) ?></button>
        <button type="button" class="chip <?= $catFilter === 'language' ? 'active' : '' ?>" onclick="document.getElementById('catField').value='language';this.closest('form').submit();"><?= h($T['coursesPage']['language']) ?></button>
        <button type="button" class="chip <?= $catFilter === 'computer' ? 'active' : '' ?>" onclick="document.getElementById('catField').value='computer';this.closest('form').submit();"><?= h($T['coursesPage']['computer']) ?></button>
      </div>
    </form>
  </div>
</div>

<div class="container section-tight">
<?php
  $filtered = array_filter($courses, function ($c) use ($catFilter, $search, $lang) {
      if ($c['status'] !== 'active') return false;
      if ($catFilter !== 'all' && $c['category'] !== $catFilter) return false;
      if ($search !== '') {
          $q = mb_strtolower($search);
          $hay = mb_strtolower($c['name']['ar'] . ' ' . $c['name']['en'] . ' ' . $c['code']);
          if (mb_strpos($hay, $q) === false) return false;
      }
      return true;
  });
?>
<?php if (empty($filtered)): ?>
  <div class="empty-state">
    <span data-lucide="search" style="width:44px;height:44px"></span>
    <h3><?= h($T['coursesPage']['noResults']) ?></h3>
  </div>
<?php else: ?>
  <div class="courses-grid">
    <?php foreach ($filtered as $c): $seatsLeft = course_seats_left($c); ?>
      <a href="<?= BASE_URL ?>/course.php?id=<?= urlencode($c['id']) ?>" class="course-card">
        <div class="course-card-top">
          <span class="course-code"><?= h($c['code']) ?></span>
        </div>
        <div class="course-card-icon"><span data-lucide="<?= h($c['icon']) ?>" style="width:22px;height:22px"></span></div>
        <h3><?= h($c['name'][$lang]) ?></h3>
        <p class="course-card-tagline"><?= h($c['tagline'][$lang]) ?></p>
        <div class="course-card-meta">
          <span><span data-lucide="clock" style="width:13px;height:13px"></span><?= h($c['duration'][$lang]) ?></span>
          <span><span data-lucide="star" style="width:13px;height:13px"></span><?= h($c['level'][$lang]) ?></span>
        </div>
        <div class="course-card-bottom">
          <div class="course-price"><?= h($c['price']) ?> <small><?= h($T['course']['egp']) ?></small></div>
          <span class="btn btn-primary btn-sm"><?= $lang === 'ar' ? 'التفاصيل' : 'Details' ?></span>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
