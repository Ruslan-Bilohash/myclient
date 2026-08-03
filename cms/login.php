<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

[$t, $lang] = mk_boot('client');

if (mk_client_current() !== null) {
    mk_redirect(mk_base_url('index.php'));
}

$error = '';
$demo = mk_demo_mode();
$prefillEmail = $demo ? 'anna.berg@demo.myclient' : '';
$prefillPass = $demo ? 'demo' : '';

// One-click client demo (CSRF not required in demo mode for the big buttons).
if (mk_is_post() && mk_post_str('demo_role') === 'client' && $demo) {
    if (mk_client_login('anna.berg@demo.myclient', 'demo')) {
        mk_redirect(mk_base_url('index.php'));
    }
    $error = $t['login_error'] ?? 'Wrong email or password';
} elseif (mk_is_post() && mk_post_str('demo_role') === '') {
    mk_csrf_require();
    if (mk_client_login(mk_post_str('email'), mk_post_str('password'))) {
        mk_redirect(mk_base_url('index.php'));
    }
    $error = $t['login_error'] ?? 'Wrong email or password';
}

mk_client_login_layout_start($t['client_login_title'] ?? 'Client cabinet', $t, $lang);
?>
<h2 style="margin-bottom:16px;"><?= mk_h($t['client_login_title'] ?? 'Client cabinet') ?></h2>

<?php if ($demo): ?>
<div class="mk-demo-btns" style="margin-bottom:18px;">
  <form method="post" action="<?= mk_h(mk_base_url('portal.php')) ?>">
    <?= mk_csrf_field() ?>
    <input type="hidden" name="demo_role" value="admin">
    <button type="submit" class="mk-btn mk-btn--primary mk-btn--block mk-demo-btn">
      <span class="mk-demo-btn-icon">🛡️</span>
      <span class="mk-demo-btn-text">
        <strong><?= mk_h($t['portal_btn_admin'] ?? 'Log in as Admin') ?></strong>
        <small>demo / demo</small>
      </span>
    </button>
  </form>
  <form method="post">
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
<p class="mk-hint" style="margin-bottom:14px;font-size:.85rem;"><?= mk_h($t['portal_manual_form'] ?? 'Or sign in manually:') ?></p>
<?php endif; ?>

<?php if ($error !== ''): ?><div class="mk-alert mk-alert--error"><?= mk_h($error) ?></div><?php endif; ?>
<form method="post" autocomplete="on">
  <?= mk_csrf_field() ?>
  <div class="mk-field">
    <label><?= mk_h($t['login_email'] ?? 'Email') ?></label>
    <input class="mk-input" type="email" name="email" value="<?= mk_h($prefillEmail) ?>" required autofocus autocomplete="username">
  </div>
  <div class="mk-field">
    <label><?= mk_h($t['login_password'] ?? 'Password') ?></label>
    <input class="mk-input" type="password" name="password" value="<?= mk_h($prefillPass) ?>" required autocomplete="current-password">
  </div>
  <button type="submit" class="mk-btn mk-btn--primary mk-btn--block"><?= mk_h($t['login_submit'] ?? 'Log in') ?></button>
</form>
<style>
.mk-demo-btns{display:flex;flex-direction:column;gap:12px}
.mk-demo-btn{
  display:flex!important;align-items:center;gap:14px;text-align:left;
  padding:14px 16px!important;border-radius:16px!important;min-height:64px;
  white-space:normal!important;line-height:1.25;
}
.mk-demo-btn-icon{font-size:1.5rem}
.mk-demo-btn-text{display:flex;flex-direction:column;gap:3px}
.mk-demo-btn-text strong{font-size:1rem}
.mk-demo-btn-text small{opacity:.8;font-size:.78rem}
.mk-demo-btn--client{
  background:rgba(56,189,248,.12)!important;
  border:1px solid rgba(56,189,248,.4)!important;
  color:inherit!important;
}
</style>
<?php mk_client_login_layout_end($lang); ?>
