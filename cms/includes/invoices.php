<?php
declare(strict_types=1);

const MK_INVOICE_STORE = 'invoices';

function mk_invoice_next_number(): string
{
    $seq = mk_store_next_seq(MK_INVOICE_STORE);

    return 'MK-' . date('Y') . '-' . str_pad((string) $seq, 5, '0', STR_PAD_LEFT);
}

/** @param list<array{desc:string,qty:float,unit:float}> $lines */
function mk_invoice_totals(array $lines): float
{
    $total = 0.0;
    foreach ($lines as $line) {
        $total += ((float) ($line['qty'] ?? 1)) * ((float) ($line['unit'] ?? 0));
    }

    return round($total, 2);
}

/**
 * @param array<string,mixed> $client
 * @param list<array{desc:string,qty:float,unit:float}> $lines
 * @param array<string,mixed> $extra
 */
function mk_invoice_create(array $client, array $lines, string $lang, array $extra = []): array
{
    $lines = array_values(array_map(static function (array $l): array {
        $qty = (float) ($l['qty'] ?? 1);
        $unit = (float) ($l['unit'] ?? 0);

        return [
            'desc' => (string) ($l['desc'] ?? ''),
            'qty' => $qty,
            'unit' => round($unit, 2),
            'total' => round($qty * $unit, 2),
        ];
    }, $lines));

    $subtotal = mk_invoice_totals($lines);
    $tax = mk_tax_settings();
    $taxPercent = $tax['enabled'] ? $tax['percent'] : 0.0;
    $taxAmount = round($subtotal * $taxPercent / 100, 2);
    $total = round($subtotal + $taxAmount, 2);

    $row = array_replace([
        'id' => mk_new_id('inv'),
        'number' => mk_invoice_next_number(),
        'client_id' => (string) ($client['id'] ?? ''),
        'service_id' => null,
        'period' => null,
        'lines' => $lines,
        'currency' => MK_CURRENCY,
        'subtotal' => $subtotal,
        'tax_percent' => $taxPercent,
        'tax_amount' => $taxAmount,
        'total' => $total,
        'status' => 'pending',
        'lang' => $lang,
        'notes' => '',
        'created_at' => mk_now(),
        'due_at' => date('Y-m-d', strtotime('+14 days')),
        'paid_at' => null,
        'payment_provider' => null,
        'payment_ref' => null,
    ], $extra);

    return mk_store_insert(MK_INVOICE_STORE, $row);
}

function mk_invoice_create_for_service(array $client, array $service, string $period, string $lang): array
{
    $price = mk_service_price($service, $period);
    $name = mk_service_name($service, $lang);
    $label = $name . ' — ' . mk_period_label($period, mk_load_lang($lang));

    return mk_invoice_create($client, [
        ['desc' => $label, 'qty' => 1, 'unit' => $price],
    ], $lang, [
        'service_id' => (string) ($service['id'] ?? ''),
        'period' => $period,
    ]);
}

/**
 * For 'month'/'year' billing-mode services where the client picks how many months/years to buy
 * at once (e.g. 6 months of hosting, or 2 years of the API add-on), rather than a single fixed unit.
 */
function mk_invoice_create_for_qty(array $client, array $service, int $qty, string $qtyUnit, string $lang): array
{
    $qty = max(1, $qty);
    $t = mk_load_lang($lang);
    $unitPrice = $qtyUnit === 'year' ? mk_service_price($service, 'm12') : mk_service_price($service, 'm1');
    $months = $qtyUnit === 'year' ? $qty * 12 : $qty;
    $unitLabel = $qtyUnit === 'year' ? ($t['unit_years_short'] ?? 'yr') : ($t['unit_months_short'] ?? 'mo');
    $name = mk_service_name($service, $lang);
    $label = "{$name} — {$qty} {$unitLabel}";

    return mk_invoice_create($client, [
        ['desc' => $label, 'qty' => $qty, 'unit' => $unitPrice],
    ], $lang, [
        'service_id' => (string) ($service['id'] ?? ''),
        'period' => $qtyUnit === 'year' ? 'm12' : 'm1',
        'period_months' => $months,
        'qty_unit' => $qtyUnit,
    ]);
}

/**
 * Line items for display, with service-based descriptions regenerated in $lang instead of the
 * text frozen at creation time — so switching the interface language also retranslates the
 * invoice. Free-text custom lines (no service_id, or more than one line) are shown as stored,
 * since there is nothing to regenerate them from.
 * @return list<array<string,mixed>>
 */
function mk_invoice_display_lines(array $invoice, string $lang): array
{
    $lines = (array) ($invoice['lines'] ?? []);
    if (empty($invoice['service_id']) || count($lines) !== 1) {
        return $lines;
    }
    $service = mk_service_find((string) $invoice['service_id']);
    if ($service === null) {
        return $lines;
    }
    $t = mk_load_lang($lang);
    $qtyUnit = (string) ($invoice['qty_unit'] ?? '');
    $qty = (int) ($lines[0]['qty'] ?? 1);
    if ($qtyUnit === 'month' || $qtyUnit === 'year') {
        $unitLabel = $qtyUnit === 'year' ? ($t['unit_years_short'] ?? 'yr') : ($t['unit_months_short'] ?? 'mo');
        $label = "{$qty} {$unitLabel}";
    } else {
        $period = (string) ($invoice['period'] ?? 'm1');
        $label = mk_service_period_label($service, $period, $t);
    }
    $lines[0]['desc'] = mk_service_name($service, $lang) . ' — ' . $label;

    return $lines;
}

/** @return list<array<string,mixed>> */
function mk_invoices_all(): array
{
    $items = mk_store_all(MK_INVOICE_STORE);
    usort($items, static fn(array $a, array $b): int => strcmp((string) ($b['created_at'] ?? ''), (string) ($a['created_at'] ?? '')));

    return $items;
}

function mk_invoice_find(string $id): ?array
{
    return mk_store_find(MK_INVOICE_STORE, $id);
}

/** @return list<array<string,mixed>> */
function mk_invoices_for_client(string $clientId): array
{
    $items = array_values(array_filter(
        mk_store_all(MK_INVOICE_STORE),
        static fn(array $inv): bool => ($inv['client_id'] ?? '') === $clientId
    ));
    usort($items, static fn(array $a, array $b): int => strcmp((string) ($b['created_at'] ?? ''), (string) ($a['created_at'] ?? '')));

    return $items;
}

function mk_invoice_mark_paid(string $id, string $provider, string $ref): bool
{
    $inv = mk_invoice_find($id);
    if ($inv === null || ($inv['status'] ?? '') === 'paid') {
        return false;
    }

    return mk_store_update(MK_INVOICE_STORE, $id, [
        'status' => 'paid',
        'paid_at' => mk_now(),
        'payment_provider' => $provider,
        'payment_ref' => $ref,
    ]);
}

function mk_invoice_mark_cancelled(string $id): bool
{
    return mk_store_update(MK_INVOICE_STORE, $id, ['status' => 'cancelled']);
}

/** Admin-only action (never exposed to clients) — deletion is allowed regardless of status. */
function mk_invoice_delete(string $id): bool
{
    return mk_store_delete(MK_INVOICE_STORE, $id);
}

/**
 * @param list<array{desc:string,qty:float,unit:float}> $lines
 */
function mk_invoice_update_lines(string $id, array $lines): bool
{
    $lines = array_values(array_filter(array_map(static function (array $l): array {
        $qty = (float) ($l['qty'] ?? 1);
        $unit = (float) ($l['unit'] ?? 0);

        return [
            'desc' => trim((string) ($l['desc'] ?? '')),
            'qty' => $qty ?: 1,
            'unit' => round($unit, 2),
            'total' => round(($qty ?: 1) * $unit, 2),
        ];
    }, $lines), static fn(array $l): bool => $l['desc'] !== '' && $l['unit'] > 0));

    if ($lines === []) {
        return false;
    }
    $subtotal = mk_invoice_totals($lines);
    $inv = mk_invoice_find($id);
    $taxPercent = (float) ($inv['tax_percent'] ?? 0);
    $taxAmount = round($subtotal * $taxPercent / 100, 2);
    $total = round($subtotal + $taxAmount, 2);

    return mk_store_update(MK_INVOICE_STORE, $id, ['lines' => $lines, 'subtotal' => $subtotal, 'tax_amount' => $taxAmount, 'total' => $total]);
}

/** Joined line-item descriptions — what the invoice is actually for, shown in list views. */
function mk_invoice_line_summary(array $invoice, ?string $lang = null): string
{
    $lines = $lang !== null ? mk_invoice_display_lines($invoice, $lang) : (array) ($invoice['lines'] ?? []);
    $descs = array_map(
        static fn(array $l): string => (string) ($l['desc'] ?? ''),
        $lines
    );

    return implode(', ', array_filter($descs, static fn(string $d): bool => $d !== ''));
}

function mk_invoice_pay_url(array $invoice): string
{
    return mk_base_url('invoice-pay.php?id=' . rawurlencode((string) $invoice['id']));
}

function mk_invoice_status_label(array $invoice, array $t): string
{
    return match ($invoice['status'] ?? 'pending') {
        'paid' => $t['invoice_status_paid'] ?? 'Paid',
        'cancelled' => $t['invoice_status_cancelled'] ?? 'Cancelled',
        default => $t['invoice_status_pending'] ?? 'Pending',
    };
}

function mk_invoice_status_class(array $invoice): string
{
    return match ($invoice['status'] ?? 'pending') {
        'paid' => 'mk-badge mk-badge--ok',
        'cancelled' => 'mk-badge mk-badge--muted',
        default => 'mk-badge mk-badge--warn',
    };
}
