<?php
declare(strict_types=1);

const MK_ADMIN_STORE = 'admins';
const MK_CLIENT_STORE = 'clients';

function mk_session_start(string $scope): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        // Allow switching scopes (portal → admin/client demo login).
        $wanted = 'mk_' . $scope . '_sess';
        if (session_name() === $wanted) {
            return;
        }
        session_write_close();
    }
    session_name('mk_' . $scope . '_sess');
    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $cookiePath = '/';
    if (defined('MK_BASE_URL')) {
        $parts = parse_url((string) MK_BASE_URL);
        if (!empty($parts['path'])) {
            $cookiePath = rtrim((string) $parts['path'], '/') . '/';
            if ($cookiePath === '') {
                $cookiePath = '/';
            }
        }
    }
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => $cookiePath,
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// ---------- Admin ----------

function mk_admin_bootstrap_needed(): bool
{
    return mk_store_all(MK_ADMIN_STORE) === [];
}

function mk_admin_by_email(string $email): ?array
{
    $email = strtolower(trim($email));
    foreach (mk_store_all(MK_ADMIN_STORE) as $admin) {
        if (strtolower((string) ($admin['email'] ?? '')) === $email) {
            return $admin;
        }
    }

    return null;
}

/** Find admin by email or username (demo login supports either). */
function mk_admin_by_login(string $login): ?array
{
    $login = strtolower(trim($login));
    if ($login === '') {
        return null;
    }
    foreach (mk_store_all(MK_ADMIN_STORE) as $admin) {
        $email = strtolower((string) ($admin['email'] ?? ''));
        $user = strtolower((string) ($admin['username'] ?? ''));
        if ($login === $email || ($user !== '' && $login === $user)) {
            return $admin;
        }
    }

    return null;
}

function mk_admin_create(string $name, string $email, string $password, string $username = ''): array
{
    $row = [
        'id' => mk_new_id('adm'),
        'name' => $name,
        'username' => strtolower(trim($username)),
        'email' => strtolower(trim($email)),
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'created_at' => mk_now(),
    ];

    return mk_store_insert(MK_ADMIN_STORE, $row);
}

function mk_admin_login(string $email, string $password): bool
{
    $admin = mk_admin_by_login($email);
    if ($admin === null || !password_verify($password, (string) ($admin['password_hash'] ?? ''))) {
        return false;
    }
    $_SESSION['mk_admin_id'] = $admin['id'];

    return true;
}

function mk_demo_mode(): bool
{
    return defined('MK_DEMO_MODE') && MK_DEMO_MODE;
}

function mk_admin_logout(): void
{
    unset($_SESSION['mk_admin_id']);
}

function mk_admin_current(): ?array
{
    $id = (string) ($_SESSION['mk_admin_id'] ?? '');
    if ($id === '') {
        return null;
    }

    return mk_store_find(MK_ADMIN_STORE, $id);
}

function mk_admin_require(): array
{
    $admin = mk_admin_current();
    if ($admin === null) {
        mk_redirect(mk_base_url('admin/login.php'));
    }

    return $admin;
}

// ---------- Client ----------

function mk_client_by_email(string $email): ?array
{
    $email = strtolower(trim($email));
    foreach (mk_store_all(MK_CLIENT_STORE) as $client) {
        if (strtolower((string) ($client['email'] ?? '')) === $email) {
            return $client;
        }
    }

    return null;
}

function mk_client_login(string $email, string $password): bool
{
    $client = mk_client_by_email($email);
    if ($client === null || !password_verify($password, (string) ($client['password_hash'] ?? ''))) {
        return false;
    }
    if (($client['status'] ?? 'active') !== 'active') {
        return false;
    }
    $_SESSION['mk_client_id'] = $client['id'];

    return true;
}

function mk_client_logout(): void
{
    unset($_SESSION['mk_client_id']);
}

function mk_client_current(): ?array
{
    $id = (string) ($_SESSION['mk_client_id'] ?? '');
    if ($id === '') {
        return null;
    }

    return mk_store_find(MK_CLIENT_STORE, $id);
}

function mk_client_require(): array
{
    $client = mk_client_current();
    if ($client === null) {
        mk_redirect(mk_base_url('login.php'));
    }

    return $client;
}

function mk_generate_password(int $len = 10): string
{
    $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
    $out = '';
    for ($i = 0; $i < $len; $i++) {
        $out .= $alphabet[random_int(0, strlen($alphabet) - 1)];
    }

    return $out;
}
