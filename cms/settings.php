<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

[$t, $lang] = mk_boot('client');
$client = mk_client_require();

$errors = ['profile' => '', 'password' => '', 'email' => ''];

if (mk_is_post()) {
    mk_csrf_require();
    $action = mk_post_str('action');

    if ($action === 'profile') {
        mk_client_update_profile((string) $client['id'], $_POST);
        mk_flash_set('ok', $t['settings_profile_saved_ok'] ?? 'Saved.');
        mk_redirect(mk_base_url('settings.php'));
    } elseif ($action === 'password') {
        $current = mk_post_str('current_password');
        $new = mk_post_str('new_password');
        $confirm = mk_post_str('confirm_password');
        if (!mk_client_verify_password($client, $current)) {
            $errors['password'] = $t['settings_password_wrong'] ?? 'Wrong current password.';
        } elseif ($new === '' || $new !== $confirm) {
            $errors['password'] = $t['settings_password_mismatch'] ?? 'Passwords do not match.';
        } else {
            mk_client_change_password((string) $client['id'], $new);
            mk_flash_set('ok', $t['settings_password_saved_ok'] ?? 'Password changed.');
            mk_redirect(mk_base_url('settings.php'));
        }
    } elseif ($action === 'email') {
        $newEmail = strtolower(mk_post_str('new_email'));
        $token = mk_client_request_email_change((string) $client['id'], $newEmail);
        if ($token === null) {
            $errors['email'] = $t['settings_email_taken'] ?? 'This email is already in use.';
        } else {
            $confirmUrl = mk_base_url('verify-email.php?token=' . rawurlencode($token));
            $subject = $t['mail_email_confirm_subject'] ?? 'Confirm your new email address';
            $body = '<p style="font-size:15px;line-height:1.6;color:#333a4d;">' . mk_h($t['mail_email_confirm_body'] ?? '') . '</p>'
                . mk_mail_button($confirmUrl, $t['mail_email_confirm_btn'] ?? 'Confirm email');
            mk_mail_send($newEmail, $subject, mk_mail_template($subject, $body, $t, $lang));
            mk_flash_set('ok', $t['settings_email_sent'] ?? 'Confirmation email sent.');
            mk_redirect(mk_base_url('settings.php'));
        }
    }
}

mk_layout_start($t['nav_settings'] ?? 'Settings', $t, $lang, 'client', 'settings');
?>
<div class="mk-card">
  <h2><?= mk_h($t['settings_profile_title'] ?? 'Contact details') ?></h2>
  <?php if ($errors['profile'] !== ''): ?><div class="mk-alert mk-alert--error"><?= mk_h($errors['profile']) ?></div><?php endif; ?>
  <form method="post">
    <?= mk_csrf_field() ?>
    <input type="hidden" name="action" value="profile">
    <div class="mk-form-row">
      <div class="mk-field"><label><?= mk_h($t['client_name'] ?? 'Name') ?></label><input class="mk-input" name="name" value="<?= mk_h((string) $client['name']) ?>"></div>
      <div class="mk-field"><label><?= mk_h($t['client_phone'] ?? 'Phone') ?></label><input class="mk-input" name="phone" value="<?= mk_h((string) ($client['phone'] ?? '')) ?>"></div>
      <div class="mk-field"><label><?= mk_h($t['client_company'] ?? 'Company') ?></label><input class="mk-input" name="company" value="<?= mk_h((string) ($client['company'] ?? '')) ?>"></div>
    </div>
    <div class="mk-form-row">
      <div class="mk-field"><label><?= mk_h($t['client_domain_name'] ?? 'Domain') ?></label><input class="mk-input" name="domain_name" placeholder="example.com" value="<?= mk_h((string) ($client['domain_name'] ?? '')) ?>"></div>
      <div class="mk-field"><label><?= mk_h($t['client_domain_registrar'] ?? 'Registrar') ?></label><input class="mk-input" name="domain_registrar" value="<?= mk_h((string) ($client['domain_registrar'] ?? '')) ?>"></div>
      <div class="mk-field"><label><?= mk_h($t['client_domain_expires'] ?? 'Expires') ?></label><input class="mk-input" type="date" name="domain_expires" value="<?= mk_h((string) ($client['domain_expires'] ?? '')) ?>"></div>
    </div>
    <button type="submit" class="mk-btn mk-btn--primary"><?= mk_h($t['btn_save'] ?? 'Save') ?></button>
  </form>
</div>

<div class="mk-card">
  <h2><?= mk_h($t['settings_password_title'] ?? 'Change password') ?></h2>
  <?php if ($errors['password'] !== ''): ?><div class="mk-alert mk-alert--error"><?= mk_h($errors['password']) ?></div><?php endif; ?>
  <form method="post">
    <?= mk_csrf_field() ?>
    <input type="hidden" name="action" value="password">
    <div class="mk-form-row">
      <div class="mk-field"><label><?= mk_h($t['settings_password_current'] ?? 'Current password') ?></label><input class="mk-input" type="password" name="current_password" required></div>
      <div class="mk-field"><label><?= mk_h($t['settings_password_new'] ?? 'New password') ?></label><input class="mk-input" type="password" name="new_password" minlength="6" required></div>
      <div class="mk-field"><label><?= mk_h($t['settings_password_confirm'] ?? 'Confirm new password') ?></label><input class="mk-input" type="password" name="confirm_password" minlength="6" required></div>
    </div>
    <button type="submit" class="mk-btn mk-btn--primary"><?= mk_h($t['btn_save'] ?? 'Save') ?></button>
  </form>
</div>

<div class="mk-card">
  <h2><?= mk_h($t['settings_email_title'] ?? 'Change email') ?></h2>
  <p class="mk-hint" style="margin-bottom:12px;"><?= mk_h((string) $client['email']) ?><?php if (!empty($client['email_pending'])): ?> · <span class="mk-badge mk-badge--warn"><?= mk_h($t['settings_email_pending_hint'] ?? 'Awaiting confirmation') ?>: <?= mk_h((string) $client['email_pending']) ?></span><?php endif; ?></p>
  <?php if ($errors['email'] !== ''): ?><div class="mk-alert mk-alert--error"><?= mk_h($errors['email']) ?></div><?php endif; ?>
  <form method="post">
    <?= mk_csrf_field() ?>
    <input type="hidden" name="action" value="email">
    <div class="mk-field"><label><?= mk_h($t['settings_email_new'] ?? 'New email address') ?></label><input class="mk-input" type="email" name="new_email" required></div>
    <button type="submit" class="mk-btn mk-btn--primary"><?= mk_h($t['btn_save'] ?? 'Save') ?></button>
  </form>
</div>
<?php mk_layout_end(); ?>
