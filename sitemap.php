<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/xml; charset=utf-8');

$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');

$staticPages = [
    ['loc' => '/', 'changefreq' => 'daily', 'priority' => '1.0'],
    ['loc' => '/courses.php', 'changefreq' => 'daily', 'priority' => '0.9'],
    ['loc' => '/halls.php', 'changefreq' => 'weekly', 'priority' => '0.8'],
    ['loc' => '/portal.php', 'changefreq' => 'weekly', 'priority' => '0.7'],
];

$courses = load_courses();

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
<?php foreach ($staticPages as $p): ?>
  <url>
    <loc><?= htmlspecialchars($baseUrl . $p['loc']) ?></loc>
    <lastmod><?= date('Y-m-d') ?></lastmod>
    <changefreq><?= $p['changefreq'] ?></changefreq>
    <priority><?= $p['priority'] ?></priority>
  </url>
<?php endforeach; ?>

<?php foreach ($courses as $c): ?>
  <?php if (($c['status'] ?? '') === 'active'): ?>
  <url>
    <loc><?= htmlspecialchars($baseUrl . '/course.php?id=' . urlencode($c['id'])) ?></loc>
    <lastmod><?= date('Y-m-d') ?></lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.85</priority>
  </url>
  <?php endif; ?>
<?php endforeach; ?>
</urlset>
