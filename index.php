<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/translations.php';
$current_page = 'home';
require __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <div class="container hero-grid">
    <div>
      <span class="eyebrow"><span data-lucide="shield-check" style="width:13px;height:13px"></span><?= h($T['hero']['eyebrow']) ?></span>
      <h1 class="hero-title"><?= h($T['hero']['title1']) ?> <span class="accent"><?= h($T['hero']['title2']) ?></span></h1>
      <p class="hero-sub"><?= h($T['hero']['subtitle']) ?></p>
      <div class="hero-cta-row">
        <a href="<?= BASE_URL ?>/courses.php" class="btn btn-gold"><?= h($T['hero']['ctaCourses']) ?> <span data-lucide="<?= $dir === 'rtl' ? 'arrow-left' : 'arrow-right' ?>" style="width:16px;height:16px"></span></a>
        <a href="<?= BASE_URL ?>/portal.php" class="btn btn-secondary" style="background:rgba(255,255,255,.12);color:#fff;border-color:rgba(255,255,255,.32)"><?= h($T['hero']['ctaLookup']) ?></a>
      </div>
      <div class="hero-trust">
        <span class="hero-trust-item"><span data-lucide="shield-check" style="width:15px;height:15px"></span><?= h($T['hero']['trust1']) ?></span>
        <span class="hero-trust-item"><span data-lucide="eye" style="width:15px;height:15px"></span><?= h($T['hero']['trust2']) ?></span>
        <span class="hero-trust-item"><span data-lucide="user-circle" style="width:15px;height:15px"></span><?= h($T['hero']['trust3']) ?></span>
      </div>
      <div class="hero-stats">
        <div><div class="hero-stat-num"><?= h($T['hero']['stat1n']) ?></div><div class="hero-stat-label"><?= h($T['hero']['stat1l']) ?></div></div>
        <div><div class="hero-stat-num"><?= h($T['hero']['stat2n']) ?></div><div class="hero-stat-label"><?= h($T['hero']['stat2l']) ?></div></div>
        <div><div class="hero-stat-num"><?= h($T['hero']['stat3n']) ?></div><div class="hero-stat-label"><?= h($T['hero']['stat3l']) ?></div></div>
      </div>
    </div>
    <div class="hero-emblem-wrap">
      <div class="hero-emblem">
        <div class="hero-emblem-ring"></div>
        <img src="<?= BASE_URL ?>/assets/logo.png" alt="<?= h($T['brand']['name']) ?>">
      </div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-head center">
      <span class="eyebrow"><?= h($T['depts']['eyebrow']) ?></span>
      <h2 class="section-title"><?= h($T['depts']['title']) ?></h2>
      <p class="section-sub"><?= h($T['depts']['sub']) ?></p>
    </div>
    <div class="dept-grid">
      <a href="<?= BASE_URL ?>/courses.php?cat=language" class="dept-card lang">
        <span class="dept-card-icon"><span data-lucide="languages" style="width:24px;height:24px"></span></span>
        <span class="dept-card-code">DIV. A</span>
        <h3><?= h($T['depts']['langTitle']) ?></h3>
        <p><?= h($T['depts']['langDesc']) ?></p>
        <span class="dept-card-link"><?= h($T['depts']['viewAll']) ?> <span data-lucide="<?= $dir === 'rtl' ? 'arrow-left' : 'arrow-right' ?>" style="width:15px;height:15px"></span></span>
      </a>
      <a href="<?= BASE_URL ?>/courses.php?cat=computer" class="dept-card comp">
        <span class="dept-card-icon"><span data-lucide="monitor" style="width:24px;height:24px"></span></span>
        <span class="dept-card-code">DIV. B</span>
        <h3><?= h($T['depts']['compTitle']) ?></h3>
        <p><?= h($T['depts']['compDesc']) ?></p>
        <span class="dept-card-link"><?= h($T['depts']['viewAll']) ?> <span data-lucide="<?= $dir === 'rtl' ? 'arrow-left' : 'arrow-right' ?>" style="width:15px;height:15px"></span></span>
      </a>
    </div>
  </div>
</section>

<section class="section section-tight" style="background:var(--surface-2)">
  <div class="container">
    <div class="section-head center">
      <span class="eyebrow"><?= h($T['why']['eyebrow']) ?></span>
      <h2 class="section-title"><?= h($T['why']['title']) ?></h2>
      <p class="section-sub"><?= h($T['why']['sub']) ?></p>
    </div>
    <div class="why-grid">
      <div class="card why-card"><span class="why-icon"><span data-lucide="shield-check" style="width:21px;height:21px"></span></span><h4><?= h($T['why']['f1t']) ?></h4><p><?= h($T['why']['f1d']) ?></p></div>
      <div class="card why-card"><span class="why-icon"><span data-lucide="users" style="width:21px;height:21px"></span></span><h4><?= h($T['why']['f2t']) ?></h4><p><?= h($T['why']['f2d']) ?></p></div>
      <div class="card why-card"><span class="why-icon"><span data-lucide="user-circle" style="width:21px;height:21px"></span></span><h4><?= h($T['why']['f3t']) ?></h4><p><?= h($T['why']['f3d']) ?></p></div>
      <div class="card why-card"><span class="why-icon"><span data-lucide="layout-dashboard" style="width:21px;height:21px"></span></span><h4><?= h($T['why']['f4t']) ?></h4><p><?= h($T['why']['f4d']) ?></p></div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="cta-band">
      <h2><?= h($T['ctaBand']['title']) ?></h2>
      <p><?= h($T['ctaBand']['sub']) ?></p>
      <a href="<?= BASE_URL ?>/courses.php" class="btn btn-gold"><?= h($T['ctaBand']['btn']) ?> <span data-lucide="<?= $dir === 'rtl' ? 'arrow-left' : 'arrow-right' ?>" style="width:16px;height:16px"></span></a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
