<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

[$t, $lang] = mk_boot('client');
$client = mk_client_require();

$idsParam = mk_get_str('ids') ?: mk_post_str('ids');
$ids = $idsParam !== ''
    ? array_values(array_filter(array_map('trim', explode(',', $idsParam))))
    : array_values(array_filter([mk_get_str('id') ?: mk_post_str('id')]));

$invoices = [];
foreach ($ids as $id) {
    $inv = mk_invoice_find($id);
    if ($inv !== null && (string) $inv['client_id'] === (string) $client['id'] && ($inv['status'] ?? '') === 'pending') {
        $invoices[] = $inv;
    }
}
if ($invoices === []) {
    http_response_code(404);
    exit('Not found');
}

$total = array_sum(array_map(static fn(array $i): float => (float) $i['total'], $invoices));
$currency = (string) ($invoices[0]['currency'] ?? MK_CURRENCY);
$invoiceIds = array_map(static fn(array $i): string => (string) $i['id'], $invoices);

if (count($invoices) === 1) {
    $description = MK_SITE_NAME . ' — ' . (string) $invoices[0]['number'];
} else {
    $numbers = implode(', ', array_map(static fn(array $i): string => (string) $i['number'], $invoices));
    $description = MK_SITE_NAME . ' — ' . count($invoices) . ' ' . mb_strtolower($t['invoices_title'] ?? 'invoices') . ' (' . $numbers . ')';
}

$error = '';

if (mk_is_post()) {
    mk_csrf_require();
    $provider = mk_post_str('provider');

    if ($provider === 'stripe' && mk_stripe_enabled()) {
        $res = count($invoices) === 1
            ? mk_stripe_create_checkout_session($invoices[0], $client, $description)
            : mk_stripe_create_checkout_session_multi($invoiceIds, $total, $currency, $client, $description);
        if ($res['ok']) {
            foreach ($invoiceIds as $id) {
                mk_store_update(MK_INVOICE_STORE, $id, ['payment_provider' => 'stripe', 'payment_ref' => $res['session_id'] ?? '']);
            }
            mk_redirect((string) $res['url']);
        }
        $error = $t['invoice_pay_error'] ?? 'Payment error';
    } elseif ($provider === 'paypal' && mk_paypal_enabled()) {
        $res = count($invoices) === 1
            ? mk_paypal_create_order($invoices[0], $description)
            : mk_paypal_create_order_multi($invoiceIds, $total, $currency, $description);
        if ($res['ok']) {
            foreach ($invoiceIds as $id) {
                mk_store_update(MK_INVOICE_STORE, $id, ['payment_provider' => 'paypal', 'payment_ref' => $res['id'] ?? '']);
            }
            mk_redirect((string) $res['approve_url']);
        }
        $error = $t['invoice_pay_error'] ?? 'Payment error';
    } else {
        $error = $t['invoice_pay_error'] ?? 'Payment error';
    }
}

mk_layout_start($t['invoice_pay_title'] ?? 'Pay invoice', $t, $lang, 'client', 'invoices');
$idsCsv = implode(',', $invoiceIds);
?>
<div class="mk-card">
  <h2><?= mk_h($t['invoice_pay_title'] ?? 'Pay invoice') ?><?= count($invoices) === 1 ? ' — ' . mk_h((string) $invoices[0]['number']) : '' ?></h2>
  <?php if (count($invoices) > 1): ?>
  <p class="mk-hint" style="margin-bottom:6px;"><?= mk_h(implode(', ', array_map(static fn(array $i): string => (string) $i['number'], $invoices))) ?></p>
  <?php endif; ?>
  <p class="mk-pay-amount"><?= mk_money_with_fx($total, $lang, $t) ?></p>
  <?php if ($error !== ''): ?><div class="mk-alert mk-alert--error"><?= mk_h($error) ?></div><?php endif; ?>
  <p class="mk-hint" style="margin-bottom:14px;"><?= mk_h($t['invoice_pay_choose'] ?? 'Choose a payment method') ?></p>
  <div class="mk-pay-options">
    <?php if (mk_stripe_enabled()): ?>
    <form method="post">
      <?= mk_csrf_field() ?>
      <input type="hidden" name="ids" value="<?= mk_h($idsCsv) ?>">
      <input type="hidden" name="provider" value="stripe">
      <button type="submit" class="mk-pay-option">
        <span class="mk-pay-option-icon">💳</span> <?= mk_h($t['invoice_pay_stripe'] ?? 'Pay by card (Stripe)') ?>
        <span class="mk-pay-option-arrow">→</span>
      </button>
    </form>
    <?php endif; ?>
    <?php if (mk_paypal_enabled()): ?>
    <form method="post">
      <?= mk_csrf_field() ?>
      <input type="hidden" name="ids" value="<?= mk_h($idsCsv) ?>">
      <input type="hidden" name="provider" value="paypal">
      <button type="submit" class="mk-pay-option">
        <span class="mk-pay-option-icon">🅿️</span> <?= mk_h($t['invoice_pay_paypal'] ?? 'Pay with PayPal') ?>
        <span class="mk-pay-option-arrow">→</span>
      </button>
    </form>
    <?php endif; ?>
    <?php if (!mk_stripe_enabled() && !mk_paypal_enabled()): ?>
      <p class="mk-hint"><?= mk_h($t['invoice_pay_error'] ?? 'No payment method configured yet.') ?></p>
    <?php endif; ?>
  </div>
  <p class="mk-pay-secure">🔒 <?= mk_h($t['invoice_pay_button_hint'] ?? 'Secure payment.') ?></p>
</div>
<?php mk_layout_end(); ?>
