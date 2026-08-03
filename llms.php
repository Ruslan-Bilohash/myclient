<?php
/**
 * UTF-8 safe serve of llms.txt (charset always declared).
 * Prefer https://bilohash.com/myclient/llms.txt (via .htaccess charset).
 */
declare(strict_types=1);

$path = __DIR__ . '/llms.txt';
if (!is_readable($path)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo "llms.txt not found\n";
    exit;
}

header('Content-Type: text/plain; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: public, max-age=3600');
readfile($path);
