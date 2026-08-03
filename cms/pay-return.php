<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

[$t, $lang] = mk_boot('client');
$client = mk_client_require();

$provider = mk_get_str('provider');
$status = mk_get_str('status');

$idsParam = mk_get_str('ids');
$ids = $idsParam !== ''
    ? array_values(array_filter(array_map('trim', explode(',', $idsParam))))
    : array_values(array_filter([mk_get_str('invoice')]));

$invoices = [];
foreach ($ids as $id) {
    $inv = mk_invoice_find($id);
    if ($inv !== null && (string) $inv['client_id'] === (string) $client['id']) {
        $invoices[] = $inv;
    }
}
if ($invoices === []) {
    http_response_code(404);
    exit('Not found');
}

$message = '';
$ok = false;

function mk_pay_return_mark_all_paid(array $invoices, array $client, array $t, string $provider, string $ref): void
{
    foreach ($invoices as $inv) {
        if (($inv['status'] ?? '') !== 'paid') {
            mk_invoice_mark_paid((string) $inv['id'], $provider, $ref);
            mk_mail_invoice_paid($client, mk_invoice_find((string) $inv['id']) ?? $inv, $t);
        }
    }
}

if ($status === 'cancel') {
    $message = $t['invoice_pay_cancelled'] ?? 'Payment cancelled.';
} elseif ($provider === 'stripe') {
    $sessionId = mk_get_str('session_id');
    $res = $sessionId !== '' ? mk_stripe_retrieve_session($sessionId) : ['ok' => false];
    if (!empty($res['ok']) && ($res['payment_status'] ?? '') === 'paid') {
        mk_pay_return_mark_all_paid($invoices, $client, $t, 'stripe', $sessionId);
        $ok = true;
        $message = $t['invoice_pay_success'] ?? 'Payment successful. Thank you!';
    } else {
        $message = $t['invoice_pay_error'] ?? 'Could not confirm the payment.';
    }
} elseif ($provider === 'paypal') {
    $orderId = mk_get_str('token');
    $res = $orderId !== '' ? mk_paypal_capture_order($orderId) : ['ok' => false];
    if (!empty($res['ok'])) {
        mk_pay_return_mark_all_paid($invoices, $client, $t, 'paypal', (string) ($res['capture_id'] ?? $orderId));
        $ok = true;
        $message = $t['invoice_pay_success'] ?? 'Payment successful. Thank you!';
    } else {
        $message = $t['invoice_pay_error'] ?? 'Could not confirm the payment.';
    }
}

$viewUrl = count($invoices) === 1
    ? mk_base_url('invoice-view.php?id=' . rawurlencode((string) $invoices[0]['id']))
    : mk_base_url('invoices.php');

mk_layout_start($t['invoice_pay_title'] ?? 'Payment', $t, $lang, 'client', 'invoices');
?>
<div class="mk-card" style="text-align:center;padding:44px 24px;">
  <div style="font-size:44px;margin-bottom:12px;"><?= $ok ? '✅' : '⚠️' ?></div>
  <h2><?= mk_h($message) ?></h2>
  <p style="margin-top:18px;">
    <a class="mk-btn mk-btn--primary" href="<?= mk_h($viewUrl) ?>"><?= mk_h($t['btn_view'] ?? 'View invoice') ?></a>
  </p>
</div>
<?php mk_layout_end(); ?>
