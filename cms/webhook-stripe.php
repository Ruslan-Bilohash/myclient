<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

$payload = (string) file_get_contents('php://input');
$sigHeader = (string) ($_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '');

if (!mk_stripe_verify_webhook($payload, $sigHeader, mk_stripe_webhook_secret())) {
    http_response_code(400);
    exit('Invalid signature');
}

$event = json_decode($payload, true);
if (!is_array($event)) {
    http_response_code(400);
    exit('Invalid payload');
}

$type = (string) ($event['type'] ?? '');
if ($type === 'checkout.session.completed') {
    $session = $event['data']['object'] ?? [];
    $paymentStatus = (string) ($session['payment_status'] ?? '');
    $sessionId = (string) ($session['id'] ?? '');
    $idsMeta = (string) ($session['metadata']['invoice_ids'] ?? '');
    $invoiceIds = $idsMeta !== ''
        ? array_values(array_filter(array_map('trim', explode(',', $idsMeta))))
        : array_values(array_filter([(string) ($session['metadata']['invoice_id'] ?? ($session['client_reference_id'] ?? ''))]));

    if ($paymentStatus === 'paid') {
        foreach ($invoiceIds as $invoiceId) {
            $invoice = mk_invoice_find($invoiceId);
            if ($invoice !== null && ($invoice['status'] ?? '') !== 'paid') {
                mk_invoice_mark_paid($invoiceId, 'stripe', $sessionId);
                $client = mk_client_find((string) $invoice['client_id']);
                if ($client !== null) {
                    mk_mail_invoice_paid($client, mk_invoice_find($invoiceId) ?? $invoice, mk_load_lang((string) ($client['lang'] ?? 'uk')));
                }
            }
        }
    }
}

http_response_code(200);
echo 'ok';
