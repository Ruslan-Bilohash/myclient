<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

[$t, $lang] = mk_boot('admin');

if (!mk_admin_bootstrap_needed()) {
    mk_redirect(mk_base_url('admin/login.php'));
}

$error = '';
if (mk_is_post()) {
    mk_csrf_require();
    $name = mk_post_str('name');
    $email = mk_post_str('email');
    $password = mk_post_str('password');
    if ($name === '' || $email === '' || strlen($password) < 6) {
        $error = $t['login_error'] ?? 'Please fill in all fields (password min. 6 characters).';
    } else {
        mk_admin_create($name, $email, $password);
        mk_admin_login($email, $password);
        mk_redirect(mk_base_url('admin/index.php'));
    }
}

mk_auth_layout_start($t['setup_title'] ?? 'Setup', $t, $lang);
?>
<h2 style="text-align:center;margin-bottom:20px;"><?= mk_h($t['setup_title'] ?? 'First-time setup') ?></h2>
<?php if ($error !== ''): ?><div class="mk-alert mk-alert--error"><?= mk_h($error) ?></div><?php endif; ?>
<form method="post">
  <?= mk_csrf_field() ?>
  <div class="mk-field">
    <label><?= mk_h($t['setup_name'] ?? 'Name') ?></label>
    <input class="mk-input" type="text" name="name" required autofocus>
  </div>
  <div class="mk-field">
    <label><?= mk_h($t['setup_email'] ?? 'Email') ?></label>
    <input class="mk-input" type="email" name="email" required>
  </div>
  <div class="mk-field">
    <label><?= mk_h($t['setup_password'] ?? 'Password') ?></label>
    <input class="mk-input" type="password" name="password" minlength="6" required>
  </div>
  <button type="submit" class="mk-btn mk-btn--primary mk-btn--block"><?= mk_h($t['setup_submit'] ?? 'Create account') ?></button>
</form>
<?php mk_auth_layout_end($lang); ?>
