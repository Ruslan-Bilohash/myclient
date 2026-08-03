<?php
declare(strict_types=1);

const MK_SERVICE_STORE = 'services';

/** @return list<array<string,mixed>> */
function mk_services_all(bool $onlyActive = false): array
{
    $items = mk_store_all(MK_SERVICE_STORE);
    if ($onlyActive) {
        $items = array_values(array_filter($items, static fn(array $s): bool => !empty($s['active'])));
    }
    usort($items, static fn(array $a, array $b): int => ((int) ($a['sort'] ?? 0)) <=> ((int) ($b['sort'] ?? 0)));

    return $items;
}

function mk_service_find(string $id): ?array
{
    return mk_store_find(MK_SERVICE_STORE, $id);
}

function mk_service_name(array $service, string $lang): string
{
    $names = is_array($service['name'] ?? null) ? $service['name'] : [];
    $val = $names[$lang] ?? $names['en'] ?? $names['uk'] ?? '';
    if ($val === '' && $names !== []) {
        $val = (string) reset($names);
    }

    return (string) $val;
}

function mk_service_desc(array $service, string $lang): string
{
    $descs = is_array($service['desc'] ?? null) ? $service['desc'] : [];

    return (string) ($descs[$lang] ?? $descs['en'] ?? $descs['uk'] ?? '');
}

/** 'flexible' (client picks 1/3/12 months) | 'month' (single recurring monthly price) | 'year' (single yearly price). */
function mk_service_billing_mode(array $service): string
{
    $mode = (string) ($service['billing_mode'] ?? 'flexible');

    return in_array($mode, ['month', 'year'], true) ? $mode : 'flexible';
}

/** For 'month'/'year' services the period is fixed by the service itself — the $period argument is ignored. */
function mk_service_price(array $service, string $period): float
{
    $mode = mk_service_billing_mode($service);
    $effectivePeriod = match ($mode) {
        'month' => 'm1',
        'year' => 'm12',
        default => $period,
    };

    return (float) ($service['price_' . $effectivePeriod] ?? 0);
}

/** Billing-period label for an invoice line: respects the service's fixed mode, ignoring a mismatched $period pick. */
function mk_service_period_label(array $service, string $period, array $t): string
{
    return match (mk_service_billing_mode($service)) {
        'month' => $t['period_m1'] ?? '1 month',
        'year' => $t['period_year'] ?? '1 year',
        default => mk_period_label($period, $t),
    };
}

/**
 * @param array{name_uk:string,name_no:string,name_en:string,desc_uk:string,desc_no:string,desc_en:string,
 *   icon:string,price_m1:string,price_m3:string,price_m12:string,active:string,sort:string} $input
 */
function mk_service_save(array $input, ?string $id = null): array
{
    $row = [
        'name' => [
            'uk' => trim((string) ($input['name_uk'] ?? '')),
            'no' => trim((string) ($input['name_no'] ?? '')),
            'en' => trim((string) ($input['name_en'] ?? '')),
        ],
        'desc' => [
            'uk' => trim((string) ($input['desc_uk'] ?? '')),
            'no' => trim((string) ($input['desc_no'] ?? '')),
            'en' => trim((string) ($input['desc_en'] ?? '')),
        ],
        'icon' => trim((string) ($input['icon'] ?? '')) ?: '💼',
        'billing_mode' => in_array($input['billing_mode'] ?? '', ['month', 'year'], true) ? $input['billing_mode'] : 'flexible',
        'price_m1' => round((float) ($input['price_m1'] ?? 0), 2),
        'price_m3' => round((float) ($input['price_m3'] ?? 0), 2),
        'price_m12' => round((float) ($input['price_m12'] ?? 0), 2),
        'active' => !empty($input['active']),
        'sort' => (int) ($input['sort'] ?? 0),
        'updated_at' => mk_now(),
    ];

    if ($id !== null) {
        mk_store_update(MK_SERVICE_STORE, $id, $row);
        $row = mk_service_find($id) ?? $row;

        return $row;
    }

    $row['id'] = mk_new_id('svc');
    $row['created_at'] = mk_now();

    return mk_store_insert(MK_SERVICE_STORE, $row);
}

/** Human-readable price summary for admin lists — one line, respecting the service's billing mode. */
function mk_service_price_summary(array $service, array $t): string
{
    $currency = MK_CURRENCY;
    return match (mk_service_billing_mode($service)) {
        'month' => mk_money((float) $service['price_m1'], $currency) . ' / ' . ($t['period_m1'] ?? '1 month'),
        'year' => mk_money((float) $service['price_m12'], $currency) . ' / ' . ($t['period_year'] ?? '1 year'),
        default => mk_money((float) $service['price_m1'], $currency) . ' / ' . ($t['period_m1'] ?? '1mo')
            . ' · ' . mk_money((float) $service['price_m3'], $currency) . ' / ' . ($t['period_m3'] ?? '3mo')
            . ' · ' . mk_money((float) $service['price_m12'], $currency) . ' / ' . ($t['period_m12'] ?? '12mo'),
    };
}

function mk_service_delete(string $id): bool
{
    return mk_store_delete(MK_SERVICE_STORE, $id);
}
