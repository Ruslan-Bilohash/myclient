<?php
declare(strict_types=1);

function mk_csrf_token(): string
{
    if (empty($_SESSION['mk_csrf'])) {
        $_SESSION['mk_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['mk_csrf'];
}

function mk_csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . mk_h(mk_csrf_token()) . '">';
}

function mk_csrf_check(): bool
{
    $sent = (string) ($_POST['csrf'] ?? '');
    $known = (string) ($_SESSION['mk_csrf'] ?? '');

    return $sent !== '' && $known !== '' && hash_equals($known, $sent);
}

function mk_csrf_require(): void
{
    if (!mk_csrf_check()) {
        http_response_code(400);
        exit('Invalid CSRF token.');
    }
}
