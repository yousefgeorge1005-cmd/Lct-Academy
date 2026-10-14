<?php
$lang  = current_lang();
$theme = current_theme();
$T     = translations($lang);
$dir   = $T['dir'];

$seo_title     = isset($page_title) ? $page_title . ' — ' . $T['brand']['name'] : $T['brand']['name'] . ' | ' . $T['brand']['tag'];
$seo_desc      = isset($page_desc) ? $page_desc : $T['hero']['subtitle'];
$seo_keywords  = $lang === 'ar' 
    ? 'أكاديمية LCT, كورسات لغات, دورات كمبيوتر, الإسماعيلية, كورس انجليزي, IELTS, دورات تصميم, حجز قاعات تدريب, لغات وكمبيوتر' 
    : 'LCT Academy, language courses, computer courses, Ismailia, English courses, IELTS prep, hall rentals';
$seo_image     = (isset($page_image) && $page_image) ? $page_image : BASE_URL . '/assets/logo.png';
$seo_canonical = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '');
?><!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $dir ?>" data-theme="<?= $theme ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($seo_title) ?></title>
<meta name="description" content="<?= h($seo_desc) ?>">
<meta name="keywords" content="<?= h($seo_keywords) ?>">
<meta name="robots" content="index, follow, max-image-preview:large">
<meta name="author" content="LCT Academy">
<link rel="canonical" href="<?= h($seo_canonical) ?>">

<!-- Open Graph / Facebook / WhatsApp -->
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= h($T['brand']['name']) ?> — <?= h($T['brand']['tag']) ?>">
<meta property="og:title" content="<?= h($seo_title) ?>">
<meta property="og:description" content="<?= h($seo_desc) ?>">
<meta property="og:image" content="<?= h($seo_image) ?>">
<meta property="og:url" content="<?= h($seo_canonical) ?>">
<meta property="og:locale" content="<?= $lang === 'ar' ? 'ar_EG' : 'en_US' ?>">

<!-- Twitter Cards -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= h($seo_title) ?>">
<meta name="twitter:description" content="<?= h($seo_desc) ?>">
<meta name="twitter:image" content="<?= h($seo_image) ?>">

<!-- Favicon -->
<link rel="icon" href="<?= BASE_URL ?>/assets/logo.png" type="image/png">
<link rel="apple-touch-icon" href="<?= BASE_URL ?>/assets/logo.png">

<!-- CSS Styles -->
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css?v=<?= filemtime(__DIR__ . '/../assets/style.css') ?>">

<!-- Structured Data (JSON-LD) for Google Rich Snippets -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "EducationalOrganization",
  "name": "LCT Academy",
  "alternateName": "أكاديمية LCT لتدريب اللغات والكمبيوتر",
  "url": "<?= h($seo_canonical) ?>",
  "logo": "<?= h($seo_image) ?>",
  "description": "<?= h($seo_desc) ?>",
  "telephone": "+201005373384",
  "address": {
    "@type": "PostalAddress",
    "addressLocality": "Ismailia",
    "addressCountry": "EG"
  }
}
</script>

<script src="https://unpkg.com/lucide@0.383.0/dist/umd/lucide.js" defer></script>
<script src="<?= BASE_URL ?>/assets/script.js" defer></script>
</head>
<body>
<header class="site-header">
  <div class="container header-inner">
    <a href="<?= BASE_URL ?>/index.php" class="brand">
      <span class="logo-badge"><img src="<?= BASE_URL ?>/assets/logo.png" alt="<?= h($T['brand']['name']) ?>"></span>
      <span class="brand-text">
        <span class="brand-name"><?= h($T['brand']['name']) ?></span>
        <span class="brand-tag"><?= h($T['brand']['tag']) ?></span>
      </span>
    </a>

    <nav class="main-nav">
      <a href="<?= BASE_URL ?>/index.php" class="nav-link <?= ($current_page ?? '') === 'home' ? 'active' : '' ?>"><?= h($T['nav']['home']) ?></a>
      <a href="<?= BASE_URL ?>/courses.php" class="nav-link <?= ($current_page ?? '') === 'courses' ? 'active' : '' ?>"><?= h($T['nav']['courses']) ?></a>
      <a href="<?= BASE_URL ?>/halls.php" class="nav-link <?= ($current_page ?? '') === 'halls' ? 'active' : '' ?>"><?= h($T['nav']['halls']) ?></a>
      <a href="<?= BASE_URL ?>/portal.php" class="nav-link <?= ($current_page ?? '') === 'portal' ? 'active' : '' ?>"><?= h($T['nav']['myPage']) ?></a>
    </nav>

    <div class="header-actions">
      <button type="button" class="icon-btn lang-btn" data-action="toggle-lang"><span data-lucide="globe" style="width:15px;height:15px"></span> <?= h($T['common']['language']) ?></button>
      <button type="button" class="icon-btn" data-action="toggle-theme" aria-label="theme"><span data-lucide="<?= $theme === 'light' ? 'moon' : 'sun' ?>" style="width:17px;height:17px"></span></button>
      <button type="button" class="icon-btn mobile-menu-btn" data-action="toggle-mobile-menu" aria-label="menu"><span data-lucide="menu" style="width:18px;height:18px"></span></button>
    </div>
  </div>
  <div class="mobile-drawer">
    <a href="<?= BASE_URL ?>/index.php" class="nav-link"><?= h($T['nav']['home']) ?></a>
    <a href="<?= BASE_URL ?>/courses.php" class="nav-link"><?= h($T['nav']['courses']) ?></a>
    <a href="<?= BASE_URL ?>/halls.php" class="nav-link"><?= h($T['nav']['halls']) ?></a>
    <a href="<?= BASE_URL ?>/portal.php" class="nav-link"><?= h($T['nav']['myPage']) ?></a>
  </div>
</header>
