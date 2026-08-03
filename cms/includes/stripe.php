<?php
declare(strict_types=1);

/** Raw Stripe API calls over cURL — no SDK. */

function mk_stripe_secret_key(): string
{
    return mk_payment_api_config()['stripe_secret_key'];
}

function mk_stripe_webhook_secret(): string
{
    return mk_payment_api_config()['stripe_webhook_secret'];
}

function mk_stripe_enabled(): bool
{
    return mk_stripe_secret_key() !== '';
}

/**
 * @return array{ok: bool, url?: string, session_id?: string, error?: string}
 */
function mk_stripe_create_checkout_session(array $invoice, array $client, string $description): array
{
    if (!mk_stripe_enabled()) {
        return ['ok' => false, 'error' => 'stripe_disabled'];
    }

    $currency = strtolower((string) ($invoice['currency'] ?? MK_CURRENCY));
    $amountMinor = (int) round(((float) ($invoice['total'] ?? 0)) * 100);

    $form = [
        'mode' => 'payment',
        'success_url' => mk_base_url('pay-return.php?provider=stripe&invoice=' . rawurlencode((string) $invoice['id']) . '&status=success&session_id={CHECKOUT_SESSION_ID}'),
        'cancel_url' => mk_base_url('pay-return.php?provider=stripe&invoice=' . rawurlencode((string) $invoice['id']) . '&status=cancel'),
        'customer_email' => (string) ($client['email'] ?? ''),
        'client_reference_id' => (string) ($invoice['id'] ?? ''),
        'line_items' => [
            0 => [
                'quantity' => 1,
                'price_data' => [
                    'currency' => $currency,
                    'unit_amount' => $amountMinor,
                    'product_data' => [
                        'name' => $description,
                    ],
                ],
            ],
        ],
        'metadata' => [
            'invoice_id' => (string) ($invoice['id'] ?? ''),
            'invoice_number' => (string) ($invoice['number'] ?? ''),
        ],
    ];

    $res = mk_http_request('POST', 'https://api.stripe.com/v1/checkout/sessions', [
        'headers' => ['Content-Type: application/x-www-form-urlencoded'],
        'body' => mk_http_build_form($form),
        'basic_user' => mk_stripe_secret_key(),
        'basic_pass' => '',
    ]);

    if (!$res['ok'] || $res['status'] >= 300) {
        return ['ok' => false, 'error' => 'stripe_http_' . $res['status'] . ' ' . substr($res['body'], 0, 300)];
    }

    $json = json_decode($res['body'], true);
    if (!is_array($json) || empty($json['url'])) {
        return ['ok' => false, 'error' => 'stripe_invalid_response'];
    }

    return ['ok' => true, 'url' => (string) $json['url'], 'session_id' => (string) ($json['id'] ?? '')];
}

/**
 * Same as mk_stripe_create_checkout_session() but for paying several invoices in one Stripe session.
 * @param list<string> $invoiceIds
 * @return array{ok: bool, url?: string, session_id?: string, error?: string}
 */
function mk_stripe_create_checkout_session_multi(array $invoiceIds, float $total, string $currency, array $client, string $description): array
{
    if (!mk_stripe_enabled()) {
        return ['ok' => false, 'error' => 'stripe_disabled'];
    }

    $idsCsv = implode(',', $invoiceIds);
    $amountMinor = (int) round($total * 100);

    $form = [
        'mode' => 'payment',
        'success_url' => mk_base_url('pay-return.php?provider=stripe&ids=' . rawurlencode($idsCsv) . '&status=success&session_id={CHECKOUT_SESSION_ID}'),
        'cancel_url' => mk_base_url('pay-return.php?provider=stripe&ids=' . rawurlencode($idsCsv) . '&status=cancel'),
        'customer_email' => (string) ($client['email'] ?? ''),
        'line_items' => [
            0 => [
                'quantity' => 1,
                'price_data' => [
                    'currency' => strtolower($currency),
                    'unit_amount' => $amountMinor,
                    'product_data' => [
                        'name' => $description,
                    ],
                ],
            ],
        ],
        'metadata' => [
            'invoice_ids' => $idsCsv,
        ],
    ];

    $res = mk_http_request('POST', 'https://api.stripe.com/v1/checkout/sessions', [
        'headers' => ['Content-Type: application/x-www-form-urlencoded'],
        'body' => mk_http_build_form($form),
        'basic_user' => mk_stripe_secret_key(),
        'basic_pass' => '',
    ]);

    if (!$res['ok'] || $res['status'] >= 300) {
        return ['ok' => false, 'error' => 'stripe_http_' . $res['status'] . ' ' . substr($res['body'], 0, 300)];
    }

    $json = json_decode($res['body'], true);
    if (!is_array($json) || empty($json['url'])) {
        return ['ok' => false, 'error' => 'stripe_invalid_response'];
    }

    return ['ok' => true, 'url' => (string) $json['url'], 'session_id' => (string) ($json['id'] ?? '')];
}

/** @return array{ok: bool, status?: string, payment_status?: string, invoice_id?: string, error?: string} */
function mk_stripe_retrieve_session(string $sessionId): array
{
    if (!mk_stripe_enabled()) {
        return ['ok' => false, 'error' => 'stripe_disabled'];
    }
    $res = mk_http_request('GET', 'https://api.stripe.com/v1/checkout/sessions/' . rawurlencode($sessionId), [
        'basic_user' => mk_stripe_secret_key(),
        'basic_pass' => '',
    ]);
    if (!$res['ok'] || $res['status'] >= 300) {
        return ['ok' => false, 'error' => 'stripe_http_' . $res['status']];
    }
    $json = json_decode($res['body'], true);
    if (!is_array($json)) {
        return ['ok' => false, 'error' => 'stripe_invalid_response'];
    }

    return [
        'ok' => true,
        'status' => (string) ($json['status'] ?? ''),
        'payment_status' => (string) ($json['payment_status'] ?? ''),
        'invoice_id' => (string) ($json['metadata']['invoice_id'] ?? ($json['client_reference_id'] ?? '')),
    ];
}

/**
 * Verify the Stripe-Signature header per Stripe's documented scheme:
 * header = "t=<timestamp>,v1=<hex hmac>[,v1=<hex hmac>...]"; signed payload = "<timestamp>.<raw body>".
 */
function mk_stripe_verify_webhook(string $payload, string $sigHeader, string $secret, int $toleranceSeconds = 300): bool
{
    if ($secret === '' || $sigHeader === '') {
        return false;
    }
    $timestamp = null;
    $signatures = [];
    foreach (explode(',', $sigHeader) as $part) {
        [$k, $v] = array_pad(explode('=', trim($part), 2), 2, '');
        if ($k === 't') {
            $timestamp = $v;
        } elseif ($k === 'v1') {
            $signatures[] = $v;
        }
    }
    if ($timestamp === null || $signatures === []) {
        return false;
    }
    if (abs(time() - (int) $timestamp) > $toleranceSeconds) {
        return false;
    }
    $expected = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);
    foreach ($signatures as $sig) {
        if (hash_equals($expected, $sig)) {
            return true;
        }
    }

    return false;
}
