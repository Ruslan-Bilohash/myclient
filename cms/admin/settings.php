<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

[$t, $lang] = mk_boot('admin');
if (mk_admin_bootstrap_needed()) {
    mk_redirect(mk_base_url('admin/setup.php'));
}
mk_admin_require();

if (mk_is_post()) {
    mk_csrf_require();
    $section = mk_post_str('settings_section', 'company');
    if ($section === 'payments') {
        $payload = [
            'stripe_secret_key' => mk_post_str('stripe_secret_key'),
            'stripe_publishable_key' => mk_post_str('stripe_publishable_key'),
            'stripe_webhook_secret' => mk_post_str('stripe_webhook_secret'),
            'paypal_client_id' => mk_post_str('paypal_client_id'),
            'paypal_client_secret' => mk_post_str('paypal_client_secret'),
            'paypal_env' => mk_post_str('paypal_env', 'sandbox'),
        ];
        // Clear buttons
        if (mk_post_str('clear_stripe') === '1') {
            $payload['stripe_secret_key'] = '__clear__';
            $payload['stripe_publishable_key'] = '__clear__';
            $payload['stripe_webhook_secret'] = '__clear__';
        }
        if (mk_post_str('clear_paypal') === '1') {
            $payload['paypal_client_id'] = '__clear__';
            $payload['paypal_client_secret'] = '__clear__';
        }
        mk_settings_save($payload);
    } else {
        mk_settings_save($_POST);
    }
    mk_flash_set('ok', $t['settings_saved_ok'] ?? 'Settings saved.');
    mk_redirect(mk_base_url('admin/settings.php#' . rawurlencode($section)));
}

$settings = mk_settings_get();
$api = mk_payment_api_config();
$stripeOn = $api['stripe_secret_key'] !== '';
$paypalOn = $api['paypal_client_id'] !== '' && $api['paypal_client_secret'] !== '';
$webhookUrl = mk_base_url('webhook-stripe.php');

mk_layout_start($t['settings_title'] ?? 'Settings', $t, $lang, 'admin', 'settings');
?>
<div class="mk-card" id="company">
  <h2><?= mk_h($t['settings_company_section'] ?? 'Company details') ?></h2>
  <p class="mk-hint" style="margin-bottom:12px;"><?= mk_h($t['settings_hint'] ?? '') ?></p>
  <p class="mk-hint" style="margin-bottom:16px;opacity:.85;"><?= mk_h($t['settings_company_help'] ?? '') ?></p>
  <form method="post">
    <?= mk_csrf_field() ?>
    <input type="hidden" name="settings_section" value="company">
    <div class="mk-form-row">
      <div class="mk-field">
        <label><?= mk_h($t['settings_company_name'] ?? 'Company name') ?></label>
        <input class="mk-input" name="company_name" value="<?= mk_h($settings['company_name']) ?>">
        <span class="mk-field-help"><?= mk_h($t['settings_help_company_name'] ?? '') ?></span>
      </div>
      <div class="mk-field">
        <label><?= mk_h($t['settings_company_org_number'] ?? 'Registration / VAT number') ?></label>
        <input class="mk-input" name="company_org_number" value="<?= mk_h($settings['company_org_number']) ?>">
        <span class="mk-field-help"><?= mk_h($t['settings_help_company_org'] ?? '') ?></span>
      </div>
    </div>
    <div class="mk-field">
      <label><?= mk_h($t['settings_company_address'] ?? 'Address') ?></label>
      <textarea class="mk-textarea" name="company_address" rows="2"><?= mk_h($settings['company_address']) ?></textarea>
      <span class="mk-field-help"><?= mk_h($t['settings_help_company_address'] ?? '') ?></span>
    </div>
    <div class="mk-form-row">
      <div class="mk-field">
        <label><?= mk_h($t['settings_company_email'] ?? 'Contact email') ?></label>
        <input class="mk-input" type="email" name="company_email" value="<?= mk_h($settings['company_email']) ?>">
        <span class="mk-field-help"><?= mk_h($t['settings_help_company_email'] ?? '') ?></span>
      </div>
      <div class="mk-field">
        <label><?= mk_h($t['settings_company_phone'] ?? 'Phone') ?></label>
        <input class="mk-input" name="company_phone" value="<?= mk_h($settings['company_phone']) ?>">
        <span class="mk-field-help"><?= mk_h($t['settings_help_company_phone'] ?? '') ?></span>
      </div>
    </div>
    <div class="mk-field">
      <label><?= mk_h($t['settings_company_bank'] ?? 'Bank details') ?></label>
      <textarea class="mk-textarea" name="company_bank" rows="2"><?= mk_h($settings['company_bank']) ?></textarea>
      <span class="mk-field-help"><?= mk_h($t['settings_help_company_bank'] ?? '') ?></span>
    </div>
    <button type="submit" class="mk-btn mk-btn--primary"><?= mk_h($t['btn_save'] ?? 'Save') ?></button>
  </form>
</div>

<div class="mk-card" id="fx">
  <h2><?= mk_h($t['settings_fx_title'] ?? 'Reference exchange rate') ?></h2>
  <p class="mk-hint" style="margin-bottom:16px;"><?= mk_h($t['settings_fx_hint'] ?? '') ?></p>
  <form method="post">
    <?= mk_csrf_field() ?>
    <input type="hidden" name="settings_section" value="fx">
    <div class="mk-form-row">
      <div class="mk-field">
        <label><?= mk_h($t['settings_fx_uah'] ?? 'UAH per 1 NOK') ?></label>
        <input class="mk-input" type="number" step="0.0001" min="0" name="fx_rate_uah" value="<?= mk_h($settings['fx_rate_uah']) ?>" placeholder="0">
        <span class="mk-field-help"><?= mk_h($t['settings_help_fx_uah'] ?? '') ?></span>
      </div>
      <div class="mk-field">
        <label><?= mk_h($t['settings_fx_usd'] ?? 'USD per 1 NOK') ?></label>
        <input class="mk-input" type="number" step="0.0001" min="0" name="fx_rate_usd" value="<?= mk_h($settings['fx_rate_usd']) ?>" placeholder="0">
        <span class="mk-field-help"><?= mk_h($t['settings_help_fx_usd'] ?? '') ?></span>
      </div>
    </div>
    <button type="submit" class="mk-btn mk-btn--primary"><?= mk_h($t['btn_save'] ?? 'Save') ?></button>
  </form>
</div>

<div class="mk-card" id="tax">
  <h2><?= mk_h($t['settings_tax_title'] ?? 'Tax') ?></h2>
  <p class="mk-hint" style="margin-bottom:16px;"><?= mk_h($t['settings_tax_hint'] ?? '') ?></p>
  <form method="post">
    <?= mk_csrf_field() ?>
    <input type="hidden" name="settings_section" value="tax">
    <input type="hidden" name="tax_enabled" value="0">
    <label class="mk-check">
      <input type="checkbox" name="tax_enabled" value="1" <?= $settings['tax_enabled'] === '1' ? 'checked' : '' ?>>
      <?= mk_h($t['settings_tax_enabled'] ?? 'Add tax to all sums') ?>
    </label>
    <span class="mk-field-help" style="display:block;margin:6px 0 12px;"><?= mk_h($t['settings_help_tax_enabled'] ?? '') ?></span>
    <div class="mk-field" style="max-width:220px;margin-top:4px;">
      <label><?= mk_h($t['settings_tax_percent'] ?? 'Tax rate, %') ?></label>
      <input class="mk-input" type="number" step="0.01" min="0" max="100" name="tax_percent" value="<?= mk_h($settings['tax_percent']) ?>">
      <span class="mk-field-help"><?= mk_h($t['settings_help_tax_percent'] ?? '') ?></span>
    </div>
    <button type="submit" class="mk-btn mk-btn--primary" style="margin-top:12px;"><?= mk_h($t['btn_save'] ?? 'Save') ?></button>
  </form>
</div>

<div class="mk-card" id="payments">
  <h2><?= mk_h($t['settings_payments_title'] ?? 'Payments API (Stripe & PayPal)') ?></h2>
  <p class="mk-hint" style="margin-bottom:10px;"><?= mk_h($t['settings_payments_hint'] ?? '') ?></p>
  <p class="mk-hint" style="margin-bottom:16px;opacity:.9;"><?= mk_h($t['settings_payments_help'] ?? '') ?></p>

  <div style="display:flex;flex-wrap:gap:10px;margin-bottom:18px;">
    <span class="mk-badge" style="padding:6px 10px;border-radius:999px;background:<?= $stripeOn ? 'rgba(16,185,129,.15)' : 'rgba(148,163,184,.12)' ?>;color:<?= $stripeOn ? '#34d399' : '#94a3b8' ?>;font-size:.85rem;">
      Stripe: <?= $stripeOn ? mk_h($t['settings_api_on'] ?? 'ON') : mk_h($t['settings_api_off'] ?? 'OFF') ?>
    </span>
    <span class="mk-badge" style="padding:6px 10px;border-radius:999px;background:<?= $paypalOn ? 'rgba(16,185,129,.15)' : 'rgba(148,163,184,.12)' ?>;color:<?= $paypalOn ? '#34d399' : '#94a3b8' ?>;font-size:.85rem;">
      PayPal: <?= $paypalOn ? mk_h($t['settings_api_on'] ?? 'ON') : mk_h($t['settings_api_off'] ?? 'OFF') ?>
    </span>
  </div>

  <form method="post">
    <?= mk_csrf_field() ?>
    <input type="hidden" name="settings_section" value="payments">

    <h3 style="font-size:1.05rem;margin:0 0 10px;"><?= mk_h($t['settings_stripe_title'] ?? 'Stripe') ?></h3>
    <p class="mk-hint" style="margin-bottom:12px;"><?= mk_h($t['settings_stripe_help'] ?? '') ?></p>

    <div class="mk-field">
      <label><?= mk_h($t['settings_stripe_secret'] ?? 'Secret key') ?> <code style="opacity:.7">sk_…</code></label>
      <input class="mk-input" type="password" name="stripe_secret_key" value="" autocomplete="off" placeholder="<?= $api['stripe_secret_key'] !== '' ? mk_h(mk_mask_secret($api['stripe_secret_key'])) : 'sk_test_… / sk_live_…' ?>">
      <span class="mk-field-help"><?= mk_h($t['settings_help_stripe_secret'] ?? '') ?></span>
    </div>
    <div class="mk-field">
      <label><?= mk_h($t['settings_stripe_publishable'] ?? 'Publishable key') ?> <code style="opacity:.7">pk_…</code></label>
      <input class="mk-input" type="text" name="stripe_publishable_key" value="" autocomplete="off" placeholder="<?= $api['stripe_publishable_key'] !== '' ? mk_h(mk_mask_secret($api['stripe_publishable_key'])) : 'pk_test_… / pk_live_…' ?>">
      <span class="mk-field-help"><?= mk_h($t['settings_help_stripe_publishable'] ?? '') ?></span>
    </div>
    <div class="mk-field">
      <label><?= mk_h($t['settings_stripe_webhook'] ?? 'Webhook signing secret') ?> <code style="opacity:.7">whsec_…</code></label>
      <input class="mk-input" type="password" name="stripe_webhook_secret" value="" autocomplete="off" placeholder="<?= $api['stripe_webhook_secret'] !== '' ? mk_h(mk_mask_secret($api['stripe_webhook_secret'])) : 'whsec_…' ?>">
      <span class="mk-field-help"><?= mk_h($t['settings_help_stripe_webhook'] ?? '') ?></span>
    </div>
    <div class="mk-field">
      <label><?= mk_h($t['settings_stripe_webhook_url'] ?? 'Webhook URL (copy to Stripe Dashboard)') ?></label>
      <input class="mk-input" type="text" readonly value="<?= mk_h($webhookUrl) ?>" onclick="this.select()">
      <span class="mk-field-help"><?= mk_h($t['settings_help_stripe_webhook_url'] ?? '') ?></span>
    </div>

    <h3 style="font-size:1.05rem;margin:22px 0 10px;"><?= mk_h($t['settings_paypal_title'] ?? 'PayPal') ?></h3>
    <p class="mk-hint" style="margin-bottom:12px;"><?= mk_h($t['settings_paypal_help'] ?? '') ?></p>

    <div class="mk-field">
      <label><?= mk_h($t['settings_paypal_env'] ?? 'Environment') ?></label>
      <select class="mk-input" name="paypal_env">
        <option value="sandbox" <?= ($api['paypal_env'] ?? 'sandbox') === 'sandbox' ? 'selected' : '' ?>><?= mk_h($t['settings_paypal_env_sandbox'] ?? 'Sandbox (test)') ?></option>
        <option value="live" <?= ($api['paypal_env'] ?? '') === 'live' ? 'selected' : '' ?>><?= mk_h($t['settings_paypal_env_live'] ?? 'Live (production)') ?></option>
      </select>
      <span class="mk-field-help"><?= mk_h($t['settings_help_paypal_env'] ?? '') ?></span>
    </div>
    <div class="mk-field">
      <label><?= mk_h($t['settings_paypal_client_id'] ?? 'Client ID') ?></label>
      <input class="mk-input" type="text" name="paypal_client_id" value="" autocomplete="off" placeholder="<?= $api['paypal_client_id'] !== '' ? mk_h(mk_mask_secret($api['paypal_client_id'])) : 'PayPal Client ID' ?>">
      <span class="mk-field-help"><?= mk_h($t['settings_help_paypal_client_id'] ?? '') ?></span>
    </div>
    <div class="mk-field">
      <label><?= mk_h($t['settings_paypal_client_secret'] ?? 'Client Secret') ?></label>
      <input class="mk-input" type="password" name="paypal_client_secret" value="" autocomplete="off" placeholder="<?= $api['paypal_client_secret'] !== '' ? mk_h(mk_mask_secret($api['paypal_client_secret'])) : 'PayPal Client Secret' ?>">
      <span class="mk-field-help"><?= mk_h($t['settings_help_paypal_client_secret'] ?? '') ?></span>
    </div>

    <p class="mk-hint" style="margin:14px 0;"><?= mk_h($t['settings_secrets_empty_keep'] ?? 'Leave secret fields empty to keep the current saved value. Use Clear to remove keys.') ?></p>

    <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;">
      <button type="submit" class="mk-btn mk-btn--primary"><?= mk_h($t['btn_save_payments'] ?? 'Save payment API') ?></button>
      <button type="submit" name="clear_stripe" value="1" class="mk-btn" onclick="return confirm('<?= mk_h($t['settings_clear_stripe_confirm'] ?? 'Clear all Stripe keys?') ?>');"><?= mk_h($t['settings_clear_stripe'] ?? 'Clear Stripe') ?></button>
      <button type="submit" name="clear_paypal" value="1" class="mk-btn" onclick="return confirm('<?= mk_h($t['settings_clear_paypal_confirm'] ?? 'Clear all PayPal keys?') ?>');"><?= mk_h($t['settings_clear_paypal'] ?? 'Clear PayPal') ?></button>
    </div>
  </form>
</div>

<style>
.mk-field-help{display:block;margin-top:6px;font-size:.82rem;line-height:1.45;color:var(--mk-muted,#94a3b8);opacity:.95}
.mk-field{margin-bottom:14px}
</style>
<?php mk_layout_end(); ?>
