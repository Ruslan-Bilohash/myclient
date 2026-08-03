<?php
declare(strict_types=1);

/** @return list<array<string,mixed>> */
function mk_clients_all(): array
{
    $items = mk_store_all(MK_CLIENT_STORE);
    usort($items, static fn(array $a, array $b): int => strcasecmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? '')));

    return $items;
}

function mk_client_find(string $id): ?array
{
    return mk_store_find(MK_CLIENT_STORE, $id);
}

/**
 * @param array{name:string,email:string,phone?:string,company?:string,lang?:string,status?:string,notes?:string,password?:string} $input
 * @return array{0: array<string,mixed>, 1: ?string} [$row, $plainPasswordIfGenerated]
 */
function mk_client_save(array $input, ?string $id = null): array
{
    $plainPassword = null;
    $row = [
        'name' => trim((string) ($input['name'] ?? '')),
        'email' => strtolower(trim((string) ($input['email'] ?? ''))),
        'phone' => trim((string) ($input['phone'] ?? '')),
        'company' => trim((string) ($input['company'] ?? '')),
        'site_url' => trim((string) ($input['site_url'] ?? '')),
        'domain_name' => trim((string) ($input['domain_name'] ?? '')),
        'domain_registrar' => trim((string) ($input['domain_registrar'] ?? '')),
        'domain_expires' => trim((string) ($input['domain_expires'] ?? '')),
        'lang' => in_array($input['lang'] ?? '', ['uk', 'no', 'en'], true) ? $input['lang'] : 'uk',
        'status' => ($input['status'] ?? 'active') === 'paused' ? 'paused' : 'active',
        'notes' => trim((string) ($input['notes'] ?? '')),
        'updated_at' => mk_now(),
    ];

    $newPassword = trim((string) ($input['password'] ?? ''));

    if ($id !== null) {
        if ($newPassword !== '') {
            $row['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
            $plainPassword = $newPassword;
        }
        mk_store_update(MK_CLIENT_STORE, $id, $row);

        return [mk_client_find($id) ?? $row, $plainPassword];
    }

    $plainPassword = $newPassword !== '' ? $newPassword : mk_generate_password();
    $row['password_hash'] = password_hash($plainPassword, PASSWORD_DEFAULT);
    $row['id'] = mk_new_id('cl');
    $row['created_at'] = mk_now();

    return [mk_store_insert(MK_CLIENT_STORE, $row), $plainPassword];
}

function mk_client_delete(string $id): bool
{
    return mk_store_delete(MK_CLIENT_STORE, $id);
}

/** Self-service profile update — never touches status/lang/password/email (those have their own dedicated flows). */
function mk_client_update_profile(string $id, array $input): bool
{
    return mk_store_update(MK_CLIENT_STORE, $id, [
        'name' => trim((string) ($input['name'] ?? '')),
        'phone' => trim((string) ($input['phone'] ?? '')),
        'company' => trim((string) ($input['company'] ?? '')),
        'domain_name' => trim((string) ($input['domain_name'] ?? '')),
        'domain_registrar' => trim((string) ($input['domain_registrar'] ?? '')),
        'domain_expires' => trim((string) ($input['domain_expires'] ?? '')),
        'updated_at' => mk_now(),
    ]);
}

function mk_client_verify_password(array $client, string $plain): bool
{
    return password_verify($plain, (string) ($client['password_hash'] ?? ''));
}

function mk_client_change_password(string $id, string $newPassword): bool
{
    return mk_store_update(MK_CLIENT_STORE, $id, [
        'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
        'updated_at' => mk_now(),
    ]);
}

/**
 * Starts an email change: stores the new address as pending plus a verification token, but does
 * NOT touch the login email yet. Returns the token to embed in the confirmation link, or null if
 * that address is already in use by another client.
 */
function mk_client_request_email_change(string $id, string $newEmail): ?string
{
    $newEmail = strtolower(trim($newEmail));
    $existing = mk_client_by_email($newEmail);
    if ($existing !== null && (string) $existing['id'] !== $id) {
        return null;
    }
    $token = bin2hex(random_bytes(24));
    mk_store_update(MK_CLIENT_STORE, $id, [
        'email_pending' => $newEmail,
        'email_verify_token' => $token,
        'email_verify_expires' => date('Y-m-d H:i:s', time() + 24 * 3600),
    ]);

    return $token;
}

/** @return array{ok: bool, client?: array<string,mixed>} */
function mk_client_confirm_email_change(string $token): array
{
    foreach (mk_store_all(MK_CLIENT_STORE) as $client) {
        if (($client['email_verify_token'] ?? '') !== $token || $token === '') {
            continue;
        }
        if ((string) ($client['email_verify_expires'] ?? '') < mk_now()) {
            return ['ok' => false];
        }
        $newEmail = (string) ($client['email_pending'] ?? '');
        if ($newEmail === '') {
            return ['ok' => false];
        }
        mk_store_update(MK_CLIENT_STORE, (string) $client['id'], [
            'email' => $newEmail,
            'email_pending' => '',
            'email_verify_token' => '',
            'email_verify_expires' => '',
            'updated_at' => mk_now(),
        ]);

        return ['ok' => true, 'client' => mk_client_find((string) $client['id']) ?? $client];
    }

    return ['ok' => false];
}

/** Services the client currently has an unexpired paid invoice for. */
function mk_client_active_services(string $clientId): array
{
    $today = new DateTimeImmutable('today');
    $active = [];
    foreach (mk_invoices_for_client($clientId) as $inv) {
        if (($inv['status'] ?? '') !== 'paid' || empty($inv['service_id']) || empty($inv['paid_at'])) {
            continue;
        }
        $months = (int) ($inv['period_months'] ?? 0) ?: mk_period_months((string) ($inv['period'] ?? 'm1'));
        $paidAt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', (string) $inv['paid_at']) ?: new DateTimeImmutable((string) $inv['paid_at']);
        $expires = $paidAt->modify('+' . $months . ' months');
        if ($expires < $today) {
            continue;
        }
        $serviceId = (string) $inv['service_id'];
        if (!isset($active[$serviceId]) || $expires > $active[$serviceId]['expires']) {
            $active[$serviceId] = ['service_id' => $serviceId, 'expires' => $expires, 'invoice' => $inv];
        }
    }

    return array_values($active);
}
