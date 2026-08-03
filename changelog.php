<?php
/**
 * My Clients — public changelog page (version matches cms/VERSION).
 */
declare(strict_types=1);

$base = '/myclient';
$site_url = 'https://bilohash.com' . $base;
$cms = $base . '/cms';
$verFile = __DIR__ . '/cms/VERSION';
$version = is_readable($verFile) ? trim((string) file_get_contents($verFile)) : '1.0.0';
$clFile = __DIR__ . '/cms/CHANGELOG.md';
$raw = is_readable($clFile) ? (string) file_get_contents($clFile) : "# Changelog\n\nNo changelog file found.\n";

$lang = 'uk';
if (!empty($_GET['lang']) && in_array($_GET['lang'], ['uk', 'ru', 'en', 'no'], true)) {
    $lang = (string) $_GET['lang'];
}

$titles = [
    'uk' => 'Changelog — My Clients / Мої клієнти v' . $version,
    'ru' => 'Changelog — My Clients / Мои клиенты v' . $version,
    'en' => 'Changelog — My Clients v' . $version,
    'no' => 'Changelog — My Clients v' . $version,
];
$descs = [
    'uk' => 'Історія версій скрипта My Clients (Мої клієнти, послуги, Invoice, ПДВ/VAT). Поточна версія v' . $version . '.',
    'ru' => 'История версий скрипта My Clients (Мои клиенты, услуги, Invoice, НДС/VAT). Текущая версия v' . $version . '.',
    'en' => 'Version history for My Clients (services, Invoice, VAT/MVA). Current version v' . $version . '.',
    'no' => 'Versjonshistorikk for My Clients (Mine kunder, Invoice, MVA). Gjeldende versjon v' . $version . '.',
];
$back = [
    'uk' => '← На лендинг',
    'ru' => '← На лендинг',
    'en' => '← Product page',
    'no' => '← Produktside',
];
$demoNote = [
    'uk' => 'Це публічне портфоліо-демо. Не реальний білінг і не реальні платежі.',
    'ru' => 'Это публичное портфолио-демо. Не реальный биллинг и не реальные платежи.',
    'en' => 'This is a public portfolio demo. Not real billing and not real payments.',
    'no' => 'Dette er en offentlig portefølje-demo. Ikke ekte fakturering eller betalinger.',
];

// Minimal Markdown → HTML (headings, lists, bold, links, hr, code)
function mk_md_to_html(string $md): string
{
    $lines = preg_split('/\R/', $md) ?: [];
    $html = '';
    $inUl = false;
    $flushUl = static function () use (&$html, &$inUl): void {
        if ($inUl) {
            $html .= "</ul>\n";
            $inUl = false;
        }
    };
    $inline = static function (string $s): string {
        $s = htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
        $s = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $s) ?? $s;
        $s = preg_replace('/`([^`]+)`/', '<code>$1</code>', $s) ?? $s;
        $s = preg_replace('/\[([^\]]+)\]\((https?:\/\/[^)]+)\)/', '<a href="$2" rel="noopener">$1</a>', $s) ?? $s;
        return $s;
    };
    foreach ($lines as $line) {
        if (preg_match('/^###\s+(.+)/', $line, $m)) {
            $flushUl();
            $html .= '<h3>' . $inline($m[1]) . "</h3>\n";
            continue;
        }
        if (preg_match('/^##\s+(.+)/', $line, $m)) {
            $flushUl();
            $html .= '<h2>' . $inline($m[1]) . "</h2>\n";
            continue;
        }
        if (preg_match('/^#\s+(.+)/', $line, $m)) {
            $flushUl();
            $html .= '<h1>' . $inline($m[1]) . "</h1>\n";
            continue;
        }
        if (preg_match('/^---+$/', trim($line))) {
            $flushUl();
            $html .= "<hr>\n";
            continue;
        }
        if (preg_match('/^[-*]\s+(.+)/', $line, $m)) {
            if (!$inUl) {
                $html .= "<ul>\n";
                $inUl = true;
            }
            $html .= '<li>' . $inline($m[1]) . "</li>\n";
            continue;
        }
        if (trim($line) === '') {
            $flushUl();
            continue;
        }
        $flushUl();
        $html .= '<p>' . $inline($line) . "</p>\n";
    }
    $flushUl();
    return $html;
}

$htmlBody = mk_md_to_html($raw);
$title = $titles[$lang] ?? $titles['en'];
$desc = $descs[$lang] ?? $descs['en'];
?><!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang === 'no' ? 'nb' : $lang) ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($title) ?></title>
<meta name="description" content="<?= htmlspecialchars($desc) ?>">
<meta name="robots" content="index, follow">
<link rel="canonical" href="<?= htmlspecialchars($site_url . '/changelog.php?lang=' . $lang) ?>">
<meta property="og:type" content="article">
<meta property="og:title" content="<?= htmlspecialchars($title) ?>">
<meta property="og:description" content="<?= htmlspecialchars($desc) ?>">
<meta property="og:url" content="<?= htmlspecialchars($site_url . '/changelog.php?lang=' . $lang) ?>">
<meta property="og:site_name" content="My Clients · BILOHASH">
<meta name="twitter:card" content="summary">
<link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/site.css?v=<?= rawurlencode($version) ?>">
<style>
body{background:#faf9f7;margin:0;font-family:system-ui,Segoe UI,sans-serif;color:#1e293b}
.cl-wrap{max-width:760px;margin:0 auto;padding:28px 20px 60px}
.cl-top{display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between;margin-bottom:20px}
.cl-demo{background:#fef3c7;border:1px solid #fcd34d;color:#92400e;border-radius:12px;padding:12px 14px;margin-bottom:22px;font-size:.92rem;line-height:1.45}
.cl-body h1{font-size:1.6rem;margin:0 0 12px}
.cl-body h2{font-size:1.25rem;margin:28px 0 10px;color:#0f766e;border-bottom:1px solid #e2e8f0;padding-bottom:6px}
.cl-body h3{font-size:1.05rem;margin:18px 0 8px;color:#115e59}
.cl-body ul{padding-left:1.2rem;line-height:1.55}
.cl-body code{background:#f1f5f9;padding:1px 6px;border-radius:4px;font-size:.88em}
.cl-body a{color:#0d9488}
.cl-ver{display:inline-block;background:#ccfbf1;color:#0f766e;font-weight:800;padding:4px 10px;border-radius:999px;font-size:.85rem}
.cl-foot{margin-top:40px;padding-top:20px;border-top:1px solid #e2e8f0;font-size:.88rem;color:#64748b;text-align:center;line-height:1.7}
.cl-foot a{color:#0d9488;margin:0 6px}
.cl-lang a{margin-right:8px;font-weight:600;text-decoration:none;color:#64748b}
.cl-lang a.active{color:#0f766e}
</style>
</head>
<body>
<div class="cl-wrap">
  <div class="cl-top">
    <a href="<?= htmlspecialchars($base . '/?lang=' . $lang) ?>"><?= htmlspecialchars($back[$lang] ?? $back['en']) ?></a>
    <span class="cl-ver">v<?= htmlspecialchars($version) ?></span>
  </div>
  <div class="cl-lang">
    <?php foreach (['uk'=>'UA','ru'=>'RU','en'=>'EN','no'=>'NO'] as $c=>$lab): ?>
      <a class="<?= $c === $lang ? 'active' : '' ?>" href="?lang=<?= $c ?>"><?= $lab ?></a>
    <?php endforeach; ?>
  </div>
  <div class="cl-demo" role="note"><?= htmlspecialchars($demoNote[$lang] ?? $demoNote['en']) ?></div>
  <article class="cl-body">
    <?= $htmlBody ?>
  </article>
  <footer class="cl-foot">
    <p><strong>My Clients v<?= htmlspecialchars($version) ?></strong> · BILOHASH portfolio demo</p>
    <p>
      <a href="<?= htmlspecialchars($base . '/') ?>">Product</a>
      <a href="<?= htmlspecialchars($cms) ?>/portal.php">Demo</a>
      <a href="<?= htmlspecialchars($cms) ?>/install.php">Install</a>
      <a href="https://bilohash.com/website/privacy-policy.php">Privacy</a>
      <a href="https://bilohash.com/website/cookies.php">Cookies</a>
      <a href="https://bilohash.com/contact.php">Contact</a>
    </p>
  </footer>
</div>
</body>
</html>
