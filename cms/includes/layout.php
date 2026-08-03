<?php
declare(strict_types=1);

/**
 * @param 'admin'|'client' $scope
 */
function mk_layout_nav(string $scope, array $t): array
{
    if ($scope === 'admin') {
        return [
            ['href' => 'index.php', 'icon' => '📊', 'label' => $t['nav_dashboard'] ?? 'Dashboard', 'key' => 'dashboard'],
            ['href' => 'clients.php', 'icon' => '👥', 'label' => $t['nav_clients'] ?? 'Clients', 'key' => 'clients'],
            ['href' => 'services.php', 'icon' => '🧩', 'label' => $t['nav_services'] ?? 'Services', 'key' => 'services'],
            ['href' => 'invoices.php', 'icon' => '🧾', 'label' => $t['nav_invoices'] ?? 'Invoices', 'key' => 'invoices'],
            ['href' => 'settings.php', 'icon' => '⚙️', 'label' => $t['nav_settings'] ?? 'Settings', 'key' => 'settings'],
        ];
    }

    return [
        ['href' => 'index.php', 'icon' => '🏠', 'label' => $t['cab_dashboard_title'] ?? 'My cabinet', 'key' => 'dashboard'],
        ['href' => 'invoices.php', 'icon' => '🧾', 'label' => $t['nav_invoices'] ?? 'Invoices', 'key' => 'invoices'],
        ['href' => 'settings.php', 'icon' => '⚙️', 'label' => $t['nav_settings'] ?? 'Settings', 'key' => 'settings'],
    ];
}

/** Inline, runs before first paint so the page never flashes the wrong theme. */
function mk_theme_boot_script(): string
{
    return '<script>(function(){try{var s=localStorage.getItem("mk_theme");if(s==="light"||s==="dark"){document.documentElement.setAttribute("data-theme",s);}}catch(e){}})();</script>';
}

function mk_theme_toggle_html(): string
{
    return '<div class="mk-theme-toggle" data-mk-theme-toggle>'
        . '<button type="button" data-mk-theme="light" aria-label="Light theme">☀️</button>'
        . '<button type="button" data-mk-theme="dark" aria-label="Dark theme">🌙</button>'
        . '</div>';
}

/** Accessible dropdown — button shows current language, menu lists the rest. Closes on outside click / Escape. */
function mk_lang_dropdown_html(string $lang): string
{
    $meta = mk_langs()[$lang] ?? mk_langs()['uk'];
    $html = '<div class="mk-lang-drop" data-mk-lang-drop>'
        . '<button type="button" class="mk-lang-drop-btn" data-mk-lang-btn aria-haspopup="listbox" aria-expanded="false">'
        . '<span>' . mk_h($meta['flag']) . '</span><span>' . mk_h($meta['label']) . '</span>'
        . '<span class="mk-lang-drop-caret">▾</span></button>'
        . '<div class="mk-lang-drop-menu" data-mk-lang-menu hidden role="listbox">';
    foreach (mk_langs() as $code => $item) {
        $html .= '<a href="' . mk_h(mk_lang_url($code)) . '" class="' . ($code === $lang ? 'active' : '') . '" role="option">'
            . '<span>' . mk_h($item['flag']) . '</span> ' . mk_h($item['name']) . '</a>';
    }

    return $html . '</div></div>';
}

function mk_lang_dropdown_script(): string
{
    return <<<'JS'
<script>
(function(){
  document.querySelectorAll('[data-mk-lang-drop]').forEach(function(drop){
    var btn = drop.querySelector('[data-mk-lang-btn]');
    var menu = drop.querySelector('[data-mk-lang-menu]');
    if (!btn || !menu) return;
    function close(){ menu.hidden = true; btn.setAttribute('aria-expanded', 'false'); }
    function open(){ menu.hidden = false; btn.setAttribute('aria-expanded', 'true'); }
    btn.addEventListener('click', function(e){
      e.stopPropagation();
      menu.hidden ? open() : close();
    });
    document.addEventListener('click', function(e){
      if (!drop.contains(e.target)) close();
    });
    document.addEventListener('keydown', function(e){
      if (e.key === 'Escape') close();
    });
  });
})();
</script>
JS;
}

function mk_copy_button_script(): string
{
    return <<<'JS'
<script>
(function(){
  document.querySelectorAll('[data-mk-copy]').forEach(function(btn){
    var input = document.getElementById(btn.getAttribute('data-mk-copy'));
    if (!input) return;
    var original = btn.textContent;
    btn.addEventListener('click', function(){
      input.select();
      (navigator.clipboard ? navigator.clipboard.writeText(input.value) : Promise.reject())
        .catch(function(){ document.execCommand('copy'); })
        .finally(function(){
          btn.textContent = btn.getAttribute('data-mk-copied') || 'Copied!';
          setTimeout(function(){ btn.textContent = original; }, 1500);
        });
    });
  });
})();
</script>
JS;
}

function mk_theme_toggle_script(): string
{
    return <<<'JS'
<script>
(function(){
  function apply(theme){
    document.documentElement.setAttribute('data-theme', theme);
    try { localStorage.setItem('mk_theme', theme); } catch(e){}
    document.querySelectorAll('[data-mk-theme-toggle]').forEach(function(box){
      box.querySelectorAll('button').forEach(function(btn){
        btn.classList.toggle('active', btn.getAttribute('data-mk-theme') === theme);
      });
    });
  }
  var current = document.documentElement.getAttribute('data-theme');
  if (!current) {
    current = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  }
  apply(current);
  document.querySelectorAll('[data-mk-theme-toggle] button').forEach(function(btn){
    btn.addEventListener('click', function(){ apply(btn.getAttribute('data-mk-theme')); });
  });
})();
</script>
JS;
}

/**
 * @param 'admin'|'client' $scope
 */
function mk_layout_start(string $title, array $t, string $lang, string $scope, string $activeKey): void
{
    $base = $scope === 'admin' ? mk_base_url('admin/') : mk_base_url('');
    $siteName = mk_h($scope === 'client' ? ($t['site_name_client'] ?? $t['site_name'] ?? MK_SITE_NAME) : ($t['site_name'] ?? MK_SITE_NAME));
    ?>
<!DOCTYPE html>
<html lang="<?= mk_h($lang) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title><?= mk_h($title) ?> — <?= $siteName ?></title>
<link rel="stylesheet" href="<?= mk_h(mk_asset_url('assets/css/app.css')) ?>">
<?= mk_theme_boot_script() ?>
</head>
<body>
<div class="mk-app">
  <aside class="mk-sidebar" id="mkSidebar">
    <div class="mk-brand"><span class="mk-brand-mark">🗂️</span> <span><?= $siteName ?></span></div>
    <nav class="mk-nav">
      <?php foreach (mk_layout_nav($scope, $t) as $item): ?>
      <a href="<?= mk_h($base . $item['href']) ?>" class="<?= $activeKey === $item['key'] ? 'active' : '' ?>">
        <span class="mk-nav-icon"><?= $item['icon'] ?></span> <?= mk_h($item['label']) ?>
      </a>
      <?php endforeach; ?>
    </nav>
    <div class="mk-sidebar-footer">
      <?= mk_theme_toggle_html() ?>
      <?= mk_lang_dropdown_html($lang) ?>
      <a href="<?= mk_h($base . 'logout.php') ?>">↩ <?= mk_h($t['nav_logout'] ?? 'Log out') ?></a>
      <div class="mk-version" title="My Clients CMS">
        v<?= mk_h(defined('MK_VERSION') ? MK_VERSION : '1.0.0') ?>
        <?php if (function_exists('mk_demo_mode') && mk_demo_mode()): ?>
          <span class="mk-demo-tag">DEMO</span>
        <?php endif; ?>
      </div>
      <?php if (function_exists('mk_demo_mode') && mk_demo_mode()): ?>
      <p class="mk-demo-side-note">Portfolio demo · not real billing</p>
      <?php endif; ?>
    </div>
  </aside>
  <div class="mk-sidebar-overlay" id="mkSidebarOverlay" style="display:none;position:fixed;inset:0;z-index:30;background:rgba(10,10,20,.45);"></div>
  <main class="mk-main">
    <div class="mk-topbar">
      <button type="button" class="mk-menu-toggle" id="mkMenuToggle" aria-label="Menu">☰</button>
      <h1><?= mk_h($title) ?></h1>
      <span></span>
    </div>
    <?php $flash = mk_flash_take(); if ($flash !== null): ?>
    <div class="mk-alert mk-alert--<?= $flash['type'] === 'error' ? 'error' : 'ok' ?>"><?= mk_h($flash['message']) ?></div>
    <?php endif; ?>
    <?php
}

function mk_layout_end(): void
{
    ?>
  </main>
</div>
<?= mk_theme_toggle_script() ?>
<?= mk_lang_dropdown_script() ?>
<?= mk_copy_button_script() ?>
<script>
(function(){
  var toggle = document.getElementById('mkMenuToggle');
  var sidebar = document.getElementById('mkSidebar');
  var overlay = document.getElementById('mkSidebarOverlay');
  if (!toggle || !sidebar) return;
  function close(){ sidebar.classList.remove('mk-sidebar--open'); overlay.style.display = 'none'; }
  toggle.addEventListener('click', function(){
    sidebar.classList.toggle('mk-sidebar--open');
    overlay.style.display = sidebar.classList.contains('mk-sidebar--open') ? 'block' : 'none';
  });
  overlay.addEventListener('click', close);
})();
</script>
</body>
</html>
    <?php
}

function mk_auth_layout_start(string $title, array $t, string $lang): void
{
    $siteName = mk_h($t['site_name'] ?? MK_SITE_NAME);
    ?>
<!DOCTYPE html>
<html lang="<?= mk_h($lang) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title><?= mk_h($title) ?> — <?= $siteName ?></title>
<link rel="stylesheet" href="<?= mk_h(mk_asset_url('assets/css/app.css')) ?>">
<?= mk_theme_boot_script() ?>
</head>
<body>
<div class="mk-auth-wrap">
  <div class="mk-auth-card">
    <div class="mk-brand"><span class="mk-brand-mark">🗂️</span> <span><?= $siteName ?></span></div>
    <?php $flash = mk_flash_take(); if ($flash !== null): ?>
    <div class="mk-alert mk-alert--<?= $flash['type'] === 'error' ? 'error' : 'ok' ?>"><?= mk_h($flash['message']) ?></div>
    <?php endif; ?>
    <?php
}

function mk_auth_layout_end(string $lang = ''): void
{
    ?>
    <div class="mk-auth-footer">
      <?= mk_theme_toggle_html() ?>
      <?php if ($lang !== ''): ?><?= mk_lang_dropdown_html($lang) ?><?php endif; ?>
      <div class="mk-version">My Clients v<?= mk_h(defined('MK_VERSION') ? MK_VERSION : '1.0.0') ?></div>
    </div>
  </div>
</div>
<?= mk_theme_toggle_script() ?>
<?= mk_lang_dropdown_script() ?>
</body>
</html>
    <?php
}

/** Two-panel hero login screen for the client cabinet (brand story + form) — distinct from the plain admin login. */
function mk_client_login_layout_start(string $title, array $t, string $lang): void
{
    $siteName = mk_h($t['site_name_client'] ?? $t['site_name'] ?? MK_SITE_NAME);
    $points = [];
    foreach (['client_login_point_1', 'client_login_point_2', 'client_login_point_3'] as $key) {
        if (!empty($t[$key])) {
            $points[] = $t[$key];
        }
    }
    ?>
<!DOCTYPE html>
<html lang="<?= mk_h($lang) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title><?= mk_h($title) ?> — <?= $siteName ?></title>
<link rel="stylesheet" href="<?= mk_h(mk_asset_url('assets/css/app.css')) ?>">
<?= mk_theme_boot_script() ?>
</head>
<body class="mk-split-body">
<div class="mk-split-auth">
  <div class="mk-split-brand">
    <div class="mk-split-brand-glow" aria-hidden="true"></div>
    <div class="mk-split-brand-inner">
      <div class="mk-brand"><span class="mk-brand-mark">🗂️</span> <span><?= $siteName ?></span></div>
      <h1 class="mk-split-headline"><?= mk_h($t['client_login_headline'] ?? 'Everything you pay for, in one place') ?></h1>
      <?php if ($points !== []): ?>
      <ul class="mk-split-points">
        <?php foreach ($points as $p): ?><li><span class="mk-split-point-icon">✓</span> <?= mk_h($p) ?></li><?php endforeach; ?>
      </ul>
      <?php endif; ?>
      <svg class="mk-split-deco" viewBox="0 0 320 220" fill="none" aria-hidden="true">
        <rect x="18" y="26" width="180" height="118" rx="14" fill="rgba(255,255,255,.07)" stroke="rgba(255,255,255,.16)"/>
        <rect x="40" y="50" width="90" height="10" rx="5" fill="rgba(255,255,255,.28)"/>
        <rect x="40" y="70" width="130" height="8" rx="4" fill="rgba(255,255,255,.16)"/>
        <rect x="40" y="86" width="110" height="8" rx="4" fill="rgba(255,255,255,.16)"/>
        <rect x="40" y="112" width="60" height="20" rx="6" fill="rgba(255,255,255,.22)"/>
        <circle cx="248" cy="70" r="46" fill="rgba(255,255,255,.06)"/>
        <circle cx="248" cy="70" r="46" stroke="rgba(255,255,255,.18)"/>
        <path d="M228 70l14 14 28-28" stroke="#fff" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" opacity=".85"/>
        <rect x="60" y="150" width="200" height="46" rx="12" fill="rgba(255,255,255,.08)" stroke="rgba(255,255,255,.14)"/>
        <circle cx="84" cy="173" r="10" fill="rgba(255,255,255,.3)"/>
        <rect x="104" y="166" width="120" height="7" rx="3.5" fill="rgba(255,255,255,.22)"/>
        <rect x="104" y="178" width="80" height="6" rx="3" fill="rgba(255,255,255,.14)"/>
      </svg>
    </div>
  </div>
  <div class="mk-split-form">
    <div class="mk-split-form-inner">
    <?php $flash = mk_flash_take(); if ($flash !== null): ?>
    <div class="mk-alert mk-alert--<?= $flash['type'] === 'error' ? 'error' : 'ok' ?>"><?= mk_h($flash['message']) ?></div>
    <?php endif; ?>
    <?php
}

function mk_client_login_layout_end(string $lang): void
{
    ?>
    <div class="mk-auth-footer">
      <?= mk_theme_toggle_html() ?>
      <?= mk_lang_dropdown_html($lang) ?>
      <div class="mk-version">My Clients v<?= mk_h(defined('MK_VERSION') ? MK_VERSION : '1.0.0') ?></div>
    </div>
    </div>
  </div>
</div>
<?= mk_theme_toggle_script() ?>
<?= mk_lang_dropdown_script() ?>
</body>
</html>
    <?php
}
