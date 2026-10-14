<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$baseUrl = 'http://localhost:8000';
$courses = load_courses();

$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

$pages = [
    ['loc' => '/', 'priority' => '1.0', 'changefreq' => 'daily'],
    ['loc' => '/index.php', 'priority' => '1.0', 'changefreq' => 'daily'],
    ['loc' => '/courses.php', 'priority' => '0.9', 'changefreq' => 'daily'],
    ['loc' => '/halls.php', 'priority' => '0.8', 'changefreq' => 'weekly'],
    ['loc' => '/portal.php', 'priority' => '0.7', 'changefreq' => 'weekly'],
];

foreach ($pages as $p) {
    $xml .= "  <url>\n";
    $xml .= "    <loc>" . htmlspecialchars($baseUrl . $p['loc']) . "</loc>\n";
    $xml .= "    <lastmod>" . date('Y-m-d') . "</lastmod>\n";
    $xml .= "    <changefreq>" . $p['changefreq'] . "</changefreq>\n";
    $xml .= "    <priority>" . $p['priority'] . "</priority>\n";
    $xml .= "  </url>\n";
}

foreach ($courses as $c) {
    if (($c['status'] ?? 'active') === 'active') {
        $xml .= "  <url>\n";
        $xml .= "    <loc>" . htmlspecialchars($baseUrl . '/course.php?id=' . urlencode($c['id'])) . "</loc>\n";
        $xml .= "    <lastmod>" . date('Y-m-d') . "</lastmod>\n";
        $xml .= "    <changefreq>weekly</changefreq>\n";
        $xml .= "    <priority>0.85</priority>\n";
        $xml .= "  </url>\n";
    }
}

$xml .= '</urlset>';

file_put_contents(__DIR__ . '/sitemap.xml', $xml);
echo "Generated sitemap.xml successfully.\n";
