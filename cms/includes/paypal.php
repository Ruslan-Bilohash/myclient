<?php
declare(strict_types=1);

/** Raw PayPal REST v2 calls over cURL — no SDK. */

function mk_paypal_enabled(): bool
{
    $cfg = mk_payment_api_config();

    return $cfg['paypal_client_id'] !== '' && $cfg['paypal_client_secret'] !== '';
}

function mk_paypal_api_base(): string
{
    $env = mk_payment_api_config()['paypal_env'];

    return $env === 'sandbox' ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';
}

function mk_paypal_get_access_token(): ?string
{
    if (!mk_paypal_enabled()) {
        return null;
    }
    $cfg = mk_payment_api_config();
    $res = mk_http_request('POST', mk_paypal_api_base() . '/v1/oauth2/token', [
        'headers' => ['Content-Type: application/x-www-form-urlencoded', 'Accept: application/json'],
        'body' => 'grant_type=client_credentials',
        'basic_user' => $cfg['paypal_client_id'],
        'basic_pass' => $cfg['paypal_client_secret'],
    ]);
    if (!$res['ok'] || $res['status'] >= 300) {
        return null;
    }
    $json = json_decode($res['body'], true);

    return is_array($json) && !empty($json['access_token']) ? (string) $json['access_token'] : null;
}

/** @return array{ok: bool, id?: string, approve_url?: string, error?: string} */
function mk_paypal_create_order(array $invoice, string $description): array
{
    $token = mk_paypal_get_access_token();
    if ($token === null) {
        return ['ok' => false, 'error' => 'paypal_auth_failed'];
    }

    $amount = number_format((float) ($invoice['total'] ?? 0), 2, '.', '');
    $currency = strtoupper((string) ($invoice['currency'] ?? MK_CURRENCY));

    $body = [
        'intent' => 'CAPTURE',
        'purchase_units' => [
            0 => [
                'reference_id' => (string) ($invoice['id'] ?? ''),
                'description' => mb_substr($description, 0, 127),
                'amount' => ['currency_code' => $currency, 'value' => $amount],
            ],
        ],
        'application_context' => [
            'return_url' => mk_base_url('pay-return.php?provider=paypal&invoice=' . rawurlencode((string) $invoice['id']) . '&status=success'),
            'cancel_url' => mk_base_url('pay-return.php?provider=paypal&invoice=' . rawurlencode((string) $invoice['id']) . '&status=cancel'),
            'user_action' => 'PAY_NOW',
        ],
    ];

    $res = mk_http_request('POST', mk_paypal_api_base() . '/v2/checkout/orders', [
        'headers' => ['Content-Type: application/json', 'Authorization: Bearer ' . $token],
        'body' => json_encode($body, JSON_UNESCAPED_SLASHES),
    ]);
    if (!$res['ok'] || $res['status'] >= 300) {
        return ['ok' => false, 'error' => 'paypal_http_' . $res['status'] . ' ' . substr($res['body'], 0, 300)];
    }
    $json = json_decode($res['body'], true);
    if (!is_array($json) || empty($json['id'])) {
        return ['ok' => false, 'error' => 'paypal_invalid_response'];
    }
    $approveUrl = '';
    foreach ((array) ($json['links'] ?? []) as $link) {
        if (($link['rel'] ?? '') === 'approve') {
            $approveUrl = (string) $link['href'];
            break;
        }
    }
    if ($approveUrl === '') {
        return ['ok' => false, 'error' => 'paypal_no_approve_link'];
    }

    return ['ok' => true, 'id' => (string) $json['id'], 'approve_url' => $approveUrl];
}

/**
 * Same as mk_paypal_create_order() but for paying several invoices in one PayPal order.
 * @param list<string> $invoiceIds
 * @return array{ok: bool, id?: string, approve_url?: string, error?: string}
 */
function mk_paypal_create_order_multi(array $invoiceIds, float $total, string $currency, string $description): array
{
    $token = mk_paypal_get_access_token();
    if ($token === null) {
        return ['ok' => false, 'error' => 'paypal_auth_failed'];
    }

    $idsCsv = implode(',', $invoiceIds);
    $amount = number_format($total, 2, '.', '');

    $body = [
        'intent' => 'CAPTURE',
        'purchase_units' => [
            0 => [
                'reference_id' => mb_substr($idsCsv, 0, 250),
                'description' => mb_substr($description, 0, 127),
                'amount' => ['currency_code' => strtoupper($currency), 'value' => $amount],
            ],
        ],
        'application_context' => [
            'return_url' => mk_base_url('pay-return.php?provider=paypal&ids=' . rawurlencode($idsCsv) . '&status=success'),
            'cancel_url' => mk_base_url('pay-return.php?provider=paypal&ids=' . rawurlencode($idsCsv) . '&status=cancel'),
            'user_action' => 'PAY_NOW',
        ],
    ];

    $res = mk_http_request('POST', mk_paypal_api_base() . '/v2/checkout/orders', [
        'headers' => ['Content-Type: application/json', 'Authorization: Bearer ' . $token],
        'body' => json_encode($body, JSON_UNESCAPED_SLASHES),
    ]);
    if (!$res['ok'] || $res['status'] >= 300) {
        return ['ok' => false, 'error' => 'paypal_http_' . $res['status'] . ' ' . substr($res['body'], 0, 300)];
    }
    $json = json_decode($res['body'], true);
    if (!is_array($json) || empty($json['id'])) {
        return ['ok' => false, 'error' => 'paypal_invalid_response'];
    }
    $approveUrl = '';
    foreach ((array) ($json['links'] ?? []) as $link) {
        if (($link['rel'] ?? '') === 'approve') {
            $approveUrl = (string) $link['href'];
            break;
        }
    }
    if ($approveUrl === '') {
        return ['ok' => false, 'error' => 'paypal_no_approve_link'];
    }

    return ['ok' => true, 'id' => (string) $json['id'], 'approve_url' => $approveUrl];
}

/** @return array{ok: bool, status?: string, capture_id?: string, error?: string} */
function mk_paypal_capture_order(string $orderId): array
{
    $token = mk_paypal_get_access_token();
    if ($token === null) {
        return ['ok' => false, 'error' => 'paypal_auth_failed'];
    }
    $res = mk_http_request('POST', mk_paypal_api_base() . '/v2/checkout/orders/' . rawurlencode($orderId) . '/capture', [
        'headers' => ['Content-Type: application/json', 'Authorization: Bearer ' . $token],
        'body' => '{}',
    ]);
    if (!$res['ok'] || $res['status'] >= 300) {
        return ['ok' => false, 'error' => 'paypal_capture_http_' . $res['status'] . ' ' . substr($res['body'], 0, 300)];
    }
    $json = json_decode($res['body'], true);
    if (!is_array($json)) {
        return ['ok' => false, 'error' => 'paypal_invalid_response'];
    }
    $status = (string) ($json['status'] ?? '');
    $captureId = '';
    $captures = $json['purchase_units'][0]['payments']['captures'] ?? [];
    if (is_array($captures) && isset($captures[0]['id'])) {
        $captureId = (string) $captures[0]['id'];
    }

    return ['ok' => $status === 'COMPLETED', 'status' => $status, 'capture_id' => $captureId];
}
