<?php
declare(strict_types=1);

const MK_SETTINGS_FILE = 'settings';

/** @return list<string> */
function mk_settings_fields(): array
{
    return [
        'company_name',
        'company_address',
        'company_org_number',
        'company_email',
        'company_phone',
        'company_bank',
        'fx_rate_uah',
        'fx_rate_usd',
        'tax_enabled',
        'tax_percent',
        'stripe_secret_key',
        'stripe_publishable_key',
        'stripe_webhook_secret',
        'paypal_client_id',
        'paypal_client_secret',
        'paypal_env',
    ];
}

/** @return array<string,string> */
function mk_settings_get(): array
{
    $data = mk_store_load(MK_SETTINGS_FILE);
    $row = $data['items'][0] ?? [];

    return [
        'company_name' => (string) ($row['company_name'] ?? MK_SITE_NAME),
        'company_address' => (string) ($row['company_address'] ?? ''),
        'company_org_number' => (string) ($row['company_org_number'] ?? ''),
        'company_email' => (string) ($row['company_email'] ?? ''),
        'company_phone' => (string) ($row['company_phone'] ?? ''),
        'company_bank' => (string) ($row['company_bank'] ?? ''),
        'fx_rate_uah' => (string) ($row['fx_rate_uah'] ?? ''),
        'fx_rate_usd' => (string) ($row['fx_rate_usd'] ?? ''),
        'tax_enabled' => (string) ($row['tax_enabled'] ?? ''),
        'tax_percent' => (string) ($row['tax_percent'] ?? '16'),
        'stripe_secret_key' => (string) ($row['stripe_secret_key'] ?? ''),
        'stripe_publishable_key' => (string) ($row['stripe_publishable_key'] ?? ''),
        'stripe_webhook_secret' => (string) ($row['stripe_webhook_secret'] ?? ''),
        'paypal_client_id' => (string) ($row['paypal_client_id'] ?? ''),
        'paypal_client_secret' => (string) ($row['paypal_client_secret'] ?? ''),
        'paypal_env' => (string) ($row['paypal_env'] ?? 'sandbox'),
    ];
}

/**
 * Payment API config: settings.json first, then config.local.php constants as fallback.
 * @return array{
 *   stripe_secret_key: string,
 *   stripe_publishable_key: string,
 *   stripe_webhook_secret: string,
 *   paypal_client_id: string,
 *   paypal_client_secret: string,
 *   paypal_env: string
 * }
 */
function mk_payment_api_config(): array
{
    $s = mk_settings_get();
    $pick = static function (string $fromSettings, string $constName): string {
        $v = trim($fromSettings);
        if ($v !== '') {
            return $v;
        }
        if (defined($constName)) {
            return trim((string) constant($constName));
        }

        return '';
    };

    $env = strtolower(trim($s['paypal_env'] !== '' ? $s['paypal_env'] : (defined('MK_PAYPAL_ENV') ? (string) MK_PAYPAL_ENV : 'sandbox')));
    if (!in_array($env, ['sandbox', 'live'], true)) {
        $env = 'sandbox';
    }

    return [
        'stripe_secret_key' => $pick($s['stripe_secret_key'], 'MK_STRIPE_SECRET_KEY'),
        'stripe_publishable_key' => $pick($s['stripe_publishable_key'], 'MK_STRIPE_PUBLISHABLE_KEY'),
        'stripe_webhook_secret' => $pick($s['stripe_webhook_secret'], 'MK_STRIPE_WEBHOOK_SECRET'),
        'paypal_client_id' => $pick($s['paypal_client_id'], 'MK_PAYPAL_CLIENT_ID'),
        'paypal_client_secret' => $pick($s['paypal_client_secret'], 'MK_PAYPAL_CLIENT_SECRET'),
        'paypal_env' => $env,
    ];
}

/** Mask secret for admin UI (show last 4 chars). */
function mk_mask_secret(string $value): string
{
    $value = trim($value);
    if ($value === '') {
        return '';
    }
    $len = strlen($value);
    if ($len <= 8) {
        return str_repeat('•', $len);
    }

    return str_repeat('•', max(0, $len - 4)) . substr($value, -4);
}

/** @return array{enabled: bool, percent: float} */
function mk_tax_settings(): array
{
    $s = mk_settings_get();

    return [
        'enabled' => $s['tax_enabled'] === '1',
        'percent' => (float) $s['tax_percent'],
    ];
}

/**
 * Merges into the existing record — each admin settings form only posts its own fields,
 * so a field absent from $input keeps its previously saved value instead of being blanked.
 * Secret fields: empty POST keeps previous value; value "__clear__" blanks the secret.
 * @param array<string,string> $input
 */
function mk_settings_save(array $input): void
{
    $current = mk_settings_get();
    $secretFields = [
        'stripe_secret_key',
        'stripe_publishable_key',
        'stripe_webhook_secret',
        'paypal_client_id',
        'paypal_client_secret',
    ];
    $row = [];
    foreach (mk_settings_fields() as $field) {
        if (!array_key_exists($field, $input)) {
            $row[$field] = $current[$field];
            continue;
        }
        $raw = trim((string) $input[$field]);
        if (in_array($field, $secretFields, true)) {
            if ($raw === '' || $raw === '••••keep••••') {
                $row[$field] = $current[$field];
            } elseif ($raw === '__clear__') {
                $row[$field] = '';
            } else {
                $row[$field] = $raw;
            }
            continue;
        }
        $row[$field] = $raw;
    }
    if (($row['paypal_env'] ?? '') !== 'live') {
        $row['paypal_env'] = 'sandbox';
    }
    $row['updated_at'] = mk_now();
    mk_store_save(MK_SETTINGS_FILE, ['items' => [$row]]);
}

/**
 * Reference-only conversion for display — the real charge is always in MK_CURRENCY (NOK).
 * Rates are admin-entered manually (no live FX API): "how many UAH/USD equal 1 NOK".
 * @return array{amount: float, currency: string}|null null when no rate is configured for this language.
 */
function mk_fx_display(float $nokAmount, string $lang): ?array
{
    $settings = mk_settings_get();
    $rate = match ($lang) {
        'uk' => (float) $settings['fx_rate_uah'],
        'en' => (float) $settings['fx_rate_usd'],
        default => 0.0,
    };
    if ($rate <= 0) {
        return null;
    }
    $currency = match ($lang) {
        'uk' => 'UAH',
        'en' => 'USD',
        default => MK_CURRENCY,
    };

    return ['amount' => round($nokAmount * $rate, 2), 'currency' => $currency];
}

/** mk_money() for the real (charged) amount, plus a "≈ ..." reference conversion when a rate is set for $lang. */
function mk_money_with_fx(float $nokAmount, string $lang, array $t): string
{
    $base = mk_money($nokAmount, MK_CURRENCY);
    $fx = mk_fx_display($nokAmount, $lang);
    if ($fx === null) {
        return $base;
    }

    return $base . ' <span class="mk-fx-approx">≈ ' . mk_h(mk_money($fx['amount'], $fx['currency'])) . '</span>';
}
