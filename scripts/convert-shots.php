<?php
/**
 * Convert /myclient/screenshot/*.jpg|png → .webp + optional -480 width.
 * Run on Hostinger: php convert-shots.php
 */
declare(strict_types=1);

$dir = $argv[1] ?? dirname(__DIR__) . '/screenshot';
if (!is_dir($dir)) {
    fwrite(STDERR, "Missing dir: $dir\n");
    exit(1);
}
if (!extension_loaded('gd')) {
    fwrite(STDERR, "GD not available\n");
    exit(1);
}

$files = scandir($dir) ?: [];
$n = 0;
foreach ($files as $f) {
    if (!preg_match('/\.(jpe?g|png)$/i', $f)) {
        continue;
    }
    $src = $dir . '/' . $f;
    $base = pathinfo($f, PATHINFO_FILENAME);
    $webp = $dir . '/' . $base . '.webp';
    $webp480 = $dir . '/' . $base . '-480.webp';

    $info = @getimagesize($src);
    if ($info === false) {
        echo "skip bad $f\n";
        continue;
    }
    $im = match ($info[2]) {
        IMAGETYPE_JPEG => @imagecreatefromjpeg($src),
        IMAGETYPE_PNG => @imagecreatefrompng($src),
        default => false,
    };
    if ($im === false) {
        echo "skip load $f\n";
        continue;
    }
    if ($info[2] === IMAGETYPE_PNG) {
        imagepalettetotruecolor($im);
        imagealphablending($im, true);
        imagesavealpha($im, true);
    }

    imagewebp($im, $webp, 82);
    $w = imagesx($im);
    $h = imagesy($im);
    if ($w > 480) {
        $nw = 480;
        $nh = (int) round($h * ($nw / $w));
        $small = imagecreatetruecolor($nw, $nh);
        imagecopyresampled($small, $im, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagewebp($small, $webp480, 80);
        imagedestroy($small);
    } else {
        copy($webp, $webp480);
    }
    imagedestroy($im);
    $n++;
    echo "ok $f → {$base}.webp\n";
}
echo "done $n files\n";
