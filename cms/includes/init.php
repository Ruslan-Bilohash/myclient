<?php
declare(strict_types=1);

/**
 * @return array{0: array<string,string>, 1: string} [$t, $lang]
 */
function mk_boot(string $scope): array
{
    mk_session_start($scope);
    $lang = mk_detect_lang();
    $t = mk_load_lang($lang);

    return [$t, $lang];
}
