<?php
declare(strict_types=1);
header('Content-Type: application/xml; charset=utf-8');
$base = 'https://bilohash.com/myclient';
$today = gmdate('Y-m-d');
// Landing languages
$langMap = [
    'uk' => 'uk',
    'ru' => 'ru',
    'en' => 'en',
    'no' => 'nb-NO',
    'sv' => 'sv-SE',
    'pl' => 'pl-PL',
];
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xhtml="http://www.w3.org/1999/xhtml"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
  <url>
    <loc><?= htmlspecialchars($base . '/') ?></loc>
    <lastmod><?= $today ?></lastmod>
    <changefreq>weekly</changefreq>
    <priority>1.0</priority>
<?php foreach ($langMap as $code => $hl): ?>
    <xhtml:link rel="alternate" hreflang="<?= htmlspecialchars($hl) ?>" href="<?= htmlspecialchars($base . '/?lang=' . $code) ?>"/>
<?php endforeach; ?>
    <xhtml:link rel="alternate" hreflang="x-default" href="<?= htmlspecialchars($base . '/?lang=uk') ?>"/>
  </url>
<?php foreach (array_keys($langMap) as $code): ?>
  <url>
    <loc><?= htmlspecialchars($base . '/?lang=' . $code) ?></loc>
    <lastmod><?= $today ?></lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.95</priority>
  </url>
<?php endforeach; ?>
  <url>
    <loc><?= htmlspecialchars($base . '/cms/portal.php') ?></loc>
    <lastmod><?= $today ?></lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.9</priority>
  </url>
  <url>
    <loc><?= htmlspecialchars($base . '/cms/install.php') ?></loc>
    <lastmod><?= $today ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.85</priority>
  </url>
  <url>
    <loc><?= htmlspecialchars($base . '/cms/admin/login.php') ?></loc>
    <lastmod><?= $today ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.65</priority>
  </url>
  <url>
    <loc><?= htmlspecialchars($base . '/cms/login.php') ?></loc>
    <lastmod><?= $today ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.65</priority>
  </url>
  <url>
    <loc><?= htmlspecialchars($base . '/changelog.php') ?></loc>
    <lastmod><?= $today ?></lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.8</priority>
  </url>
  <url>
    <loc><?= htmlspecialchars($base . '/llms.txt') ?></loc>
    <lastmod><?= $today ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.5</priority>
  </url>
<?php
$shotDir = __DIR__ . '/screenshot';
if (is_dir($shotDir)) {
    foreach (scandir($shotDir) ?: [] as $f) {
        if (!preg_match('/\.webp$/i', $f) || str_contains($f, '-480')) {
            continue;
        }
        $loc = $base . '/screenshot/' . rawurlencode($f);
        echo "  <url>\n";
        echo '    <loc>' . htmlspecialchars($loc) . "</loc>\n";
        echo "    <lastmod>$today</lastmod>\n";
        echo "    <changefreq>monthly</changefreq>\n";
        echo "    <priority>0.4</priority>\n";
        echo "    <image:image><image:loc>" . htmlspecialchars($loc) . "</image:loc></image:image>\n";
        echo "  </url>\n";
    }
}
?>
</urlset>
