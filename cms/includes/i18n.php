<?php
declare(strict_types=1);

define('MK_LANG_COOKIE', 'mk_lang');

/** @return array<string,array{label:string,name:string,flag:string}> */
function mk_langs(): array
{
    return [
        'uk' => ['label' => 'UA', 'name' => 'Українська', 'flag' => '🇺🇦'],
        'no' => ['label' => 'NO', 'name' => 'Norsk', 'flag' => '🇳🇴'],
        'en' => ['label' => 'EN', 'name' => 'English', 'flag' => '🇬🇧'],
    ];
}

function mk_detect_lang(): string
{
    $codes = array_keys(mk_langs());
    if (!empty($_GET['lang']) && in_array($_GET['lang'], $codes, true)) {
        $chosen = (string) $_GET['lang'];
        setcookie(MK_LANG_COOKIE, $chosen, [
            'expires' => time() + 365 * 86400,
            'path' => '/',
            'httponly' => false,
            'samesite' => 'Lax',
        ]);

        return $chosen;
    }
    if (!empty($_COOKIE[MK_LANG_COOKIE]) && in_array($_COOKIE[MK_LANG_COOKIE], $codes, true)) {
        return (string) $_COOKIE[MK_LANG_COOKIE];
    }

    return 'uk';
}

/** @return array<string,string> */
function mk_load_lang(string $lang): array
{
    $codes = array_keys(mk_langs());
    if (!in_array($lang, $codes, true)) {
        $lang = 'uk';
    }
    $file = MK_ROOT . '/lang/' . $lang . '.php';
    $t = is_file($file) ? require $file : [];
    if ($lang !== 'uk') {
        $base = require MK_ROOT . '/lang/uk.php';
        $t = array_replace($base, $t);
    }

    return $t;
}

function mk_lang_url(string $code): string
{
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $parts = parse_url($uri);
    parse_str($parts['query'] ?? '', $q);
    $q['lang'] = $code;

    return ($parts['path'] ?? '/') . '?' . http_build_query($q);
}
