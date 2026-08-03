<?php
declare(strict_types=1);

/**
 * Demo entry: one-click Admin or Client cabinet.
 */
require_once __DIR__ . '/config.php';

// Handle one-click demo logins before layout (may switch session scope).
// CSRF is relaxed only for demo_role one-click buttons in MK_DEMO_MODE (public portfolio).
if (mk_is_post() && mk_demo_mode()) {
    $role = mk_post_str('demo_role');

    if ($role === 'admin') {
        // Fresh admin session (different cookie name).
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        mk_session_start('admin');
        if (mk_admin_login('demo', 'demo') || mk_admin_login('admin', 'admin')) {
            mk_redirect(mk_base_url('admin/index.php'));
        }
        mk_flash_set('error', 'Admin demo login failed.');
        mk_redirect(mk_base_url('portal.php'));
    }

    if ($role === 'client') {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        mk_session_start('client');
        $clientEmail = 'anna.berg@demo.myclient';
        if (mk_client_login($clientEmail, 'demo')) {
            mk_redirect(mk_base_url('index.php'));
        }
        // Fallback: first active client
        foreach (mk_store_all(MK_CLIENT_STORE) as $c) {
            if (($c['status'] ?? '') === 'active' && !empty($c['email'])) {
                if (mk_client_login((string) $c['email'], 'demo')) {
                    mk_redirect(mk_base_url('index.php'));
                }
            }
        }
        mk_flash_set('error', 'Client demo login failed.');
        mk_redirect(mk_base_url('portal.php'));
    }
}

[$t, $lang] = mk_boot('portal');
$demo = mk_demo_mode();

// If already logged in somewhere, offer shortcuts.
$adminLogged = false;
$clientLogged = false;
if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}
// Peek admin session
mk_session_start('admin');
$adminLogged = mk_admin_current() !== null;
session_write_close();
mk_session_start('client');
$clientLogged = mk_client_current() !== null;
session_write_close();
// Portal session for CSRF on the form
mk_session_start('portal');

$siteName = $t['site_name'] ?? MK_SITE_NAME;
$ver = defined('MK_VERSION') ? MK_VERSION : '1.0.0';
mk_auth_layout_start($t['portal_title'] ?? 'My Clients Demo', $t, $lang);
?>
<h2 style="text-align:center;margin:0 0 8px;"><?= mk_h($t['portal_title'] ?? 'My Clients — demo') ?></h2>
<p class="mk-hint" style="text-align:center;margin:0 0 6px;"><?= mk_h($t['portal_subtitle'] ?? 'Choose how to enter the portfolio demo.') ?></p>
<p class="mk-version" style="text-align:center;margin:0 0 18px;">v<?= mk_h($ver) ?></p>

<?php if ($demo): ?>
<div class="mk-demo-btns">
  <form method="post" class="mk-demo-btn-form">
    <?= mk_csrf_field() ?>
    <input type="hidden" name="demo_role" value="admin">
    <button type="submit" class="mk-btn mk-btn--primary mk-btn--block mk-demo-btn">
      <span class="mk-demo-btn-icon">🛡️</span>
      <span class="mk-demo-btn-text">
        <strong><?= mk_h($t['portal_btn_admin'] ?? 'Log in as Admin') ?></strong>
        <small>demo / demo · admin / admin</small>
      </span>
    </button>
  </form>
  <form method="post" class="mk-demo-btn-form">
    <?= mk_csrf_field() ?>
    <input type="hidden" name="demo_role" value="client">
    <button type="submit" class="mk-btn mk-btn--block mk-demo-btn mk-demo-btn--client">
      <span class="mk-demo-btn-icon">👤</span>
      <span class="mk-demo-btn-text">
        <strong><?= mk_h($t['portal_btn_client'] ?? 'Log in as Client (demo)') ?></strong>
        <small>anna.berg@demo.myclient / demo</small>
      </span>
    </button>
  </form>
</div>
<p class="mk-hint" style="text-align:center;margin:16px 0 0;font-size:.85rem;">
  <?= mk_h($t['portal_or_manual'] ?? 'Or open login forms:') ?>
  <a href="<?= mk_h(mk_base_url('admin/login.php')) ?>"><?= mk_h($t['portal_link_admin'] ?? 'Admin form') ?></a>
  ·
  <a href="<?= mk_h(mk_base_url('login.php')) ?>"><?= mk_h($t['portal_link_client'] ?? 'Client form') ?></a>
</p>
<?php else: ?>
<div class="mk-demo-btns">
  <a class="mk-btn mk-btn--primary mk-btn--block" href="<?= mk_h(mk_base_url('admin/login.php')) ?>"><?= mk_h($t['portal_btn_admin'] ?? 'Admin login') ?></a>
  <a class="mk-btn mk-btn--block" href="<?= mk_h(mk_base_url('login.php')) ?>"><?= mk_h($t['portal_btn_client'] ?? 'Client login') ?></a>
</div>
<?php endif; ?>

<?php if ($adminLogged || $clientLogged): ?>
<p class="mk-hint" style="text-align:center;margin-top:18px;">
  <?php if ($adminLogged): ?><a href="<?= mk_h(mk_base_url('admin/index.php')) ?>"><?= mk_h($t['portal_continue_admin'] ?? 'Continue as admin') ?></a><?php endif; ?>
  <?php if ($adminLogged && $clientLogged): ?> · <?php endif; ?>
  <?php if ($clientLogged): ?><a href="<?= mk_h(mk_base_url('index.php')) ?>"><?= mk_h($t['portal_continue_client'] ?? 'Continue as client') ?></a><?php endif; ?>
</p>
<?php endif; ?>

<style>
.mk-demo-btns{display:flex;flex-direction:column;gap:12px;margin-top:8px}
.mk-demo-btn-form{margin:0}
.mk-demo-btn{
  display:flex!important;align-items:center;gap:14px;text-align:left;
  padding:16px 18px!important;border-radius:16px!important;min-height:72px;
  white-space:normal!important;line-height:1.25;
}
.mk-demo-btn-icon{font-size:1.6rem;line-height:1}
.mk-demo-btn-text{display:flex;flex-direction:column;gap:4px}
.mk-demo-btn-text strong{font-size:1.05rem}
.mk-demo-btn-text small{opacity:.8;font-size:.8rem;font-weight:500}
.mk-demo-btn--client{
  background:rgba(56,189,248,.12)!important;
  border:1px solid rgba(56,189,248,.4)!important;
  color:inherit!important;
}
.mk-demo-btn--client:hover{background:rgba(56,189,248,.2)!important}
</style>
<?php mk_auth_layout_end($lang); ?>
