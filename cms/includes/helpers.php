<?php
declare(strict_types=1);

function mk_h(?string $s): string
{
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

function mk_new_id(string $prefix): string
{
    return $prefix . '_' . bin2hex(random_bytes(8));
}

function mk_now(): string
{
    return date('Y-m-d H:i:s');
}

/** Static asset URL with a cache-busting ?v= based on the file's mtime, so a deploy is never masked by a stale browser cache. */
function mk_asset_url(string $relPath): string
{
    $full = MK_ROOT . '/' . ltrim($relPath, '/');
    $v = is_file($full) ? (string) filemtime($full) : '0';

    return mk_base_url($relPath) . '?v=' . $v;
}

function mk_base_url(string $path = ''): string
{
    $base = rtrim(defined('MK_BASE_URL') ? MK_BASE_URL : '', '/');
    $path = ltrim($path, '/');

    return $path === '' ? $base . '/' : $base . '/' . $path;
}

function mk_redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function mk_money(float $amount, string $currency = MK_CURRENCY): string
{
    $symbol = match ($currency) {
        'EUR' => '€',
        'NOK' => 'kr',
        'USD' => '$',
        'UAH' => '₴',
        default => $currency . ' ',
    };
    $formatted = number_format($amount, 2, ',', ' ');
    $suffixCurrencies = ['NOK', 'UAH'];

    return in_array($currency, $suffixCurrencies, true) ? $formatted . ' ' . $symbol : $symbol . $formatted;
}

/** @return array<string,mixed> */
function mk_post(): array
{
    return $_POST;
}

function mk_post_str(string $key, string $default = ''): string
{
    return trim((string) ($_POST[$key] ?? $default));
}

function mk_get_str(string $key, string $default = ''): string
{
    return trim((string) ($_GET[$key] ?? $default));
}

function mk_is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function mk_flash_set(string $type, string $message): void
{
    $_SESSION['mk_flash'] = ['type' => $type, 'message' => $message];
}

/** @return array{type:string,message:string}|null */
function mk_flash_take(): ?array
{
    if (empty($_SESSION['mk_flash'])) {
        return null;
    }
    $flash = $_SESSION['mk_flash'];
    unset($_SESSION['mk_flash']);

    return is_array($flash) ? $flash : null;
}

function mk_period_label(string $period, array $t): string
{
    return match ($period) {
        'm1' => $t['period_m1'] ?? '1 month',
        'm3' => $t['period_m3'] ?? '3 months',
        'm12' => $t['period_m12'] ?? '12 months',
        default => $period,
    };
}

/** "Сьогодні вівторок, 22 серпня" / "I dag er det tirsdag 22. august" / "Today is Tuesday, August 22" — no PHP intl extension needed. */
function mk_today_phrase(array $t, string $lang): string
{
    $weekday = $t['weekdays'][(int) date('N')] ?? '';
    $month = $t['months_genitive'][(int) date('n')] ?? '';
    $day = (int) date('j');
    $prefix = $t['today_prefix'] ?? 'Today';
    if ($weekday === '' || $month === '') {
        return '';
    }

    return match ($lang) {
        'en' => "{$prefix} {$weekday}, {$month} {$day}",
        'no' => "{$prefix} {$weekday} {$day}. {$month}",
        default => "{$prefix} {$weekday}, {$day} {$month}",
    };
}

function mk_period_months(string $period): int
{
    return match ($period) {
        'm1' => 1,
        'm3' => 3,
        'm12' => 12,
        default => 1,
    };
}

/**
 * @param array{headers?: list<string>, body?: string, basic_user?: string, basic_pass?: string, timeout?: int} $options
 * @return array{ok: bool, status: int, body: string, error: string}
 */
function mk_http_request(string $method, string $url, array $options = []): array
{
    if (!function_exists('curl_init')) {
        return ['ok' => false, 'status' => 0, 'body' => '', 'error' => 'curl_unavailable'];
    }
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => strtoupper($method),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => (int) ($options['timeout'] ?? 20),
        CURLOPT_HTTPHEADER => $options['headers'] ?? [],
        CURLOPT_FOLLOWLOCATION => false,
    ]);
    if (isset($options['body'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $options['body']);
    }
    if (isset($options['basic_user'])) {
        curl_setopt($ch, CURLOPT_USERPWD, $options['basic_user'] . ':' . ($options['basic_pass'] ?? ''));
    }
    $body = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    return [
        'ok' => $body !== false && $error === '',
        'status' => $status,
        'body' => is_string($body) ? $body : '',
        'error' => $error,
    ];
}

/** application/x-www-form-urlencoded, including PHP nested-array notation (a[b]=c). */
function mk_http_build_form(array $data, string $prefix = ''): string
{
    $parts = [];
    foreach ($data as $key => $value) {
        $fullKey = $prefix === '' ? (string) $key : $prefix . '[' . $key . ']';
        if (is_array($value)) {
            $parts[] = mk_http_build_form($value, $fullKey);
        } else {
            $parts[] = rawurlencode($fullKey) . '=' . rawurlencode((string) $value);
        }
    }

    return implode('&', array_filter($parts, static fn(string $p): bool => $p !== ''));
}
