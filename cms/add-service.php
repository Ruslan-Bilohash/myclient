<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

[$t, $lang] = mk_boot('client');
$client = mk_client_require();

$services = mk_services_all(true);
$error = '';

if (mk_is_post()) {
    mk_csrf_require();
    $createdIds = [];
    foreach ($services as $service) {
        $sid = (string) $service['id'];
        if (empty($_POST['svc_' . $sid])) {
            continue;
        }
        $period = mk_service_billing_mode($service) === 'flexible'
            ? (in_array(mk_post_str('period_' . $sid), ['m1', 'm3', 'm12'], true) ? mk_post_str('period_' . $sid) : 'm1')
            : 'm1';
        $invoice = mk_invoice_create_for_service($client, $service, $period, $lang);
        mk_mail_invoice_created($client, $invoice, mk_load_lang($lang));
        $createdIds[] = (string) $invoice['id'];
    }
    if ($createdIds === []) {
        $error = $t['add_service_none_selected'] ?? 'Select at least one service.';
    } else {
        mk_redirect(mk_base_url('invoice-pay.php?ids=' . rawurlencode(implode(',', $createdIds))));
    }
}

mk_layout_start($t['add_service_title'] ?? 'Add a service', $t, $lang, 'client', 'invoices');
?>
<div class="mk-card">
  <h2><?= mk_h($t['add_service_title'] ?? 'Add a service') ?></h2>
  <p class="mk-hint" style="margin-bottom:16px;"><?= mk_h($t['add_service_hint'] ?? '') ?></p>
  <?php if ($error !== ''): ?><div class="mk-alert mk-alert--error"><?= mk_h($error) ?></div><?php endif; ?>
  <?php if ($services === []): ?>
    <p class="mk-hint"><?= mk_h($t['service_no_services'] ?? 'No services yet.') ?></p>
  <?php else: ?>
  <form method="post">
    <?= mk_csrf_field() ?>
    <?php foreach ($services as $s): $sid = (string) $s['id']; $mode = mk_service_billing_mode($s); ?>
    <div class="mk-service-chip" style="align-items:flex-start;">
      <span class="mk-service-chip-icon"><?= mk_h((string) $s['icon']) ?></span>
      <div style="flex:1;">
        <label class="mk-checkbox" style="margin-bottom:4px;">
          <input type="checkbox" name="svc_<?= mk_h($sid) ?>" value="1">
          <span class="mk-service-chip-name"><?= mk_h(mk_service_name($s, $lang)) ?></span>
        </label>
        <?php if (mk_service_desc($s, $lang) !== ''): ?><div class="mk-service-chip-meta"><?= mk_h(mk_service_desc($s, $lang)) ?></div><?php endif; ?>
        <?php if ($mode === 'flexible'): ?>
        <div class="mk-form-row" style="margin-top:8px;max-width:420px;">
          <div class="mk-field" style="margin-bottom:0;">
            <select class="mk-select" name="period_<?= mk_h($sid) ?>">
              <option value="m1"><?= mk_h($t['period_m1'] ?? '1 month') ?> — <?= mk_h(mk_money((float) $s['price_m1'])) ?></option>
              <option value="m3"><?= mk_h($t['period_m3'] ?? '3 months') ?> — <?= mk_h(mk_money((float) $s['price_m3'])) ?></option>
              <option value="m12"><?= mk_h($t['period_m12'] ?? '12 months') ?> — <?= mk_h(mk_money((float) $s['price_m12'])) ?></option>
            </select>
          </div>
        </div>
        <?php else: ?>
        <div class="mk-service-chip-meta" style="margin-top:4px;font-weight:700;color:var(--mk-text);">
          <?= mk_h(mk_service_price_summary($s, $t)) ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
    <button type="submit" class="mk-btn mk-btn--primary" style="margin-top:14px;"><?= mk_h($t['add_service_submit'] ?? 'Create invoice & pay') ?></button>
  </form>
  <?php endif; ?>
</div>
<?php mk_layout_end(); ?>
