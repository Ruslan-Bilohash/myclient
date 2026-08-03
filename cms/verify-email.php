<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

[$t, $lang] = mk_boot('client');

$token = mk_get_str('token');
$res = $token !== '' ? mk_client_confirm_email_change($token) : ['ok' => false];
$ok = !empty($res['ok']);
$message = $ok ? ($t['email_confirm_success'] ?? 'Your email has been updated.') : ($t['email_confirm_error'] ?? 'This link is invalid or has expired.');

mk_auth_layout_start($t['settings_email_title'] ?? 'Confirm email', $t, $lang);
?>
<div style="text-align:center;">
  <div style="font-size:44px;margin-bottom:12px;"><?= $ok ? '✅' : '⚠️' ?></div>
  <h2 style="margin-bottom:18px;"><?= mk_h($message) ?></h2>
  <a class="mk-btn mk-btn--primary" href="<?= mk_h(mk_base_url('login.php')) ?>"><?= mk_h($t['login_submit'] ?? 'Log in') ?></a>
</div>
<?php mk_auth_layout_end($lang); ?>
