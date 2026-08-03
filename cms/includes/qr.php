<?php
declare(strict_types=1);

/**
 * Minimal dependency-free QR code encoder (byte mode only, EC level L, versions 1-6, single RS block).
 * That covers URLs up to ~130 bytes, which is all we need for a payment link. No image library,
 * no external QR service — just the matrix, rendered as inline SVG by the caller.
 */
final class MkQr
{
    /** @var array<int,int> */
    private static array $exp = [];
    /** @var array<int,int> */
    private static array $log = [];

    /**
     * [version => [dataCodewords, ecCodewords]] — EC level L, single RS block.
     * Capped at version 5 (verified byte-for-byte against a reference QR encoder); version 6+
     * had an unresolved placement bug during testing and was dropped rather than shipped unverified.
     * Version 5 alone covers ~105 bytes, comfortably more than an invoice-pay URL ever needs.
     */
    private const CAPACITY = [
        1 => [19, 7],
        2 => [34, 10],
        3 => [55, 15],
        4 => [80, 20],
        5 => [108, 26],
    ];

    private static function initGf(): void
    {
        if (self::$exp !== []) {
            return;
        }
        $x = 1;
        for ($i = 0; $i < 255; $i++) {
            self::$exp[$i] = $x;
            self::$log[$x] = $i;
            $x <<= 1;
            if ($x & 0x100) {
                $x ^= 0x11D;
            }
        }
        for ($i = 255; $i < 512; $i++) {
            self::$exp[$i] = self::$exp[$i - 255];
        }
    }

    private static function gfMul(int $a, int $b): int
    {
        if ($a === 0 || $b === 0) {
            return 0;
        }

        return self::$exp[self::$log[$a] + self::$log[$b]];
    }

    /** @param list<int> $a @param list<int> $b @return list<int> */
    private static function polyMul(array $a, array $b): array
    {
        $result = array_fill(0, count($a) + count($b) - 1, 0);
        foreach ($a as $i => $ac) {
            if ($ac === 0) {
                continue;
            }
            foreach ($b as $j => $bc) {
                if ($bc === 0) {
                    continue;
                }
                $result[$i + $j] ^= self::gfMul($ac, $bc);
            }
        }

        return $result;
    }

    /** @return list<int> */
    private static function generatorPoly(int $ecCount): array
    {
        self::initGf();
        $g = [1];
        for ($i = 0; $i < $ecCount; $i++) {
            $g = self::polyMul($g, [1, self::$exp[$i]]);
        }

        return $g;
    }

    /** @param list<int> $data @return list<int> */
    private static function computeEc(array $data, int $ecCount): array
    {
        self::initGf();
        $generator = self::generatorPoly($ecCount);
        $remainder = array_merge($data, array_fill(0, $ecCount, 0));
        $dataLen = count($data);
        for ($i = 0; $i < $dataLen; $i++) {
            $coef = $remainder[$i];
            if ($coef === 0) {
                continue;
            }
            for ($j = 0; $j <= $ecCount; $j++) {
                $remainder[$i + $j] ^= self::gfMul($generator[$j], $coef);
            }
        }

        return array_slice($remainder, $dataLen, $ecCount);
    }

    private static function pickVersion(int $byteLen): ?int
    {
        foreach (self::CAPACITY as $version => [$dataCw]) {
            $headerBits = 4 + 8; // mode + byte count (versions 1-9)
            $bitsNeeded = $headerBits + $byteLen * 8;
            $bytesNeeded = (int) ceil($bitsNeeded / 8);
            if ($bytesNeeded <= $dataCw) {
                return $version;
            }
        }

        return null;
    }

    /** @return list<int> bit array (0/1) */
    private static function buildBitStream(string $text, int $dataCw): array
    {
        $bits = [0, 1, 0, 0]; // byte mode indicator
        $len = strlen($text);
        for ($i = 7; $i >= 0; $i--) {
            $bits[] = ($len >> $i) & 1;
        }
        for ($i = 0; $i < $len; $i++) {
            $byte = ord($text[$i]);
            for ($b = 7; $b >= 0; $b--) {
                $bits[] = ($byte >> $b) & 1;
            }
        }
        $totalBits = $dataCw * 8;
        for ($i = 0; $i < 4 && count($bits) < $totalBits; $i++) {
            $bits[] = 0;
        }
        while (count($bits) % 8 !== 0) {
            $bits[] = 0;
        }
        $padBytes = [0xEC, 0x11];
        $p = 0;
        while (count($bits) < $totalBits) {
            $byte = $padBytes[$p % 2];
            for ($b = 7; $b >= 0; $b--) {
                $bits[] = ($byte >> $b) & 1;
            }
            $p++;
        }

        return $bits;
    }

    /** @return list<int> bytes */
    private static function bitsToBytes(array $bits): array
    {
        $bytes = [];
        for ($i = 0; $i < count($bits); $i += 8) {
            $byte = 0;
            for ($b = 0; $b < 8; $b++) {
                $byte = ($byte << 1) | ($bits[$i + $b] ?? 0);
            }
            $bytes[] = $byte;
        }

        return $bytes;
    }

    private static function alignmentCenter(int $version): ?int
    {
        return $version === 1 ? null : (4 * $version + 10);
    }

    /** @return array{0:list<list<int>>,1:list<list<bool>>} [matrix(0/1/-1 reserved marker via separate reserved grid), reserved] */
    private static function emptyMatrix(int $size): array
    {
        $matrix = array_fill(0, $size, array_fill(0, $size, 0));
        $reserved = array_fill(0, $size, array_fill(0, $size, false));

        return [$matrix, $reserved];
    }

    private static function placeFinder(array &$matrix, array &$reserved, int $row, int $col): void
    {
        for ($r = -1; $r <= 7; $r++) {
            for ($c = -1; $c <= 7; $c++) {
                $rr = $row + $r;
                $cc = $col + $c;
                if ($rr < 0 || $cc < 0 || $rr >= count($matrix) || $cc >= count($matrix)) {
                    continue;
                }
                $isFinder = $r >= 0 && $r <= 6 && $c >= 0 && $c <= 6
                    && ($r === 0 || $r === 6 || $c === 0 || $c === 6
                        || ($r >= 2 && $r <= 4 && $c >= 2 && $c <= 4));
                $matrix[$rr][$cc] = $isFinder ? 1 : 0;
                $reserved[$rr][$cc] = true;
            }
        }
    }

    private static function placeAlignment(array &$matrix, array &$reserved, int $row, int $col): void
    {
        for ($r = -2; $r <= 2; $r++) {
            for ($c = -2; $c <= 2; $c++) {
                $dark = (max(abs($r), abs($c)) !== 1);
                $matrix[$row + $r][$col + $c] = $dark ? 1 : 0;
                $reserved[$row + $r][$col + $c] = true;
            }
        }
    }

    public static function encodeMatrix(string $text): ?array
    {
        self::initGf();
        $byteLen = strlen($text);
        $version = self::pickVersion($byteLen);
        if ($version === null) {
            return null;
        }
        [$dataCw, $ecCw] = self::CAPACITY[$version];

        $bits = self::buildBitStream($text, $dataCw);
        $dataBytes = self::bitsToBytes($bits);
        $ecBytes = self::computeEc($dataBytes, $ecCw);
        $allCodewords = array_merge($dataBytes, $ecBytes);

        $size = 17 + 4 * $version;
        [$matrix, $reserved] = self::emptyMatrix($size);

        self::placeFinder($matrix, $reserved, 0, 0);
        self::placeFinder($matrix, $reserved, 0, $size - 7);
        self::placeFinder($matrix, $reserved, $size - 7, 0);

        for ($i = 0; $i < $size; $i++) {
            $dark = $i % 2 === 0 ? 1 : 0;
            if (!$reserved[6][$i]) {
                $matrix[6][$i] = $dark;
                $reserved[6][$i] = true;
            }
            if (!$reserved[$i][6]) {
                $matrix[$i][6] = $dark;
                $reserved[$i][6] = true;
            }
        }

        $center = self::alignmentCenter($version);
        if ($center !== null) {
            self::placeAlignment($matrix, $reserved, $center, $center);
        }

        $matrix[$size - 8][8] = 1;
        $reserved[$size - 8][8] = true;

        for ($i = 0; $i <= 8; $i++) {
            if ($i !== 6) {
                $reserved[8][$i] = true;
                $reserved[$i][8] = true;
            }
        }
        for ($i = 0; $i < 8; $i++) {
            $reserved[8][$size - 1 - $i] = true;
        }
        for ($i = 0; $i < 7; $i++) {
            $reserved[$size - 1 - $i][8] = true;
        }

        $bitStream = [];
        foreach ($allCodewords as $byte) {
            for ($b = 7; $b >= 0; $b--) {
                $bitStream[] = ($byte >> $b) & 1;
            }
        }

        $bitIndex = 0;
        $bitCount = count($bitStream);
        $upward = true;
        for ($colPair = $size - 1; $colPair > 0; $colPair -= 2) {
            if ($colPair === 6) {
                $colPair--;
            }
            $rows = $upward ? range($size - 1, 0, -1) : range(0, $size - 1);
            foreach ($rows as $row) {
                foreach ([$colPair, $colPair - 1] as $col) {
                    if ($reserved[$row][$col]) {
                        continue;
                    }
                    $bit = $bitIndex < $bitCount ? $bitStream[$bitIndex] : 0;
                    $bitIndex++;
                    $maskBit = (($row + $col) % 2 === 0) ? 1 : 0;
                    $matrix[$row][$col] = $bit ^ $maskBit;
                }
            }
            $upward = !$upward;
        }

        self::placeFormatInfo($matrix, $size, 0);

        return $matrix;
    }

    /** Places the 15-bit format info (EC level + mask), in its two redundant copies, per the QR spec. */
    private static function placeFormatInfo(array &$matrix, int $size, int $maskPattern): void
    {
        $ecBits = 0b01; // EC level L
        $data = ($ecBits << 3) | $maskPattern;
        $bch = self::bchFormat($data);
        $formatBits = ($data << 10) | $bch;
        $formatBits ^= 0b101010000010010;

        // $f[$k] = bit f_k per the QR spec's placement diagrams (f0 is the first bit placed,
        // which corresponds to the MOST significant bit of the 15-bit format value).
        $f = [];
        for ($k = 0; $k <= 14; $k++) {
            $f[$k] = ($formatBits >> (14 - $k)) & 1;
        }

        // Copy A — around the top-left finder pattern.
        for ($k = 0; $k <= 5; $k++) {
            $matrix[8][$k] = $f[$k];
        }
        $matrix[8][7] = $f[6];
        $matrix[8][8] = $f[7];
        $matrix[7][8] = $f[8];
        for ($k = 9; $k <= 14; $k++) {
            $matrix[14 - $k][8] = $f[$k];
        }

        // Copy B — split across the top-right and bottom-left finder patterns (redundant copy).
        for ($k = 0; $k <= 6; $k++) {
            $matrix[$size - 1 - $k][8] = $f[$k];
        }
        for ($k = 7; $k <= 14; $k++) {
            $matrix[8][$size - 15 + $k] = $f[$k];
        }
    }

    private static function bchFormat(int $data): int
    {
        $g = 0b10100110111;
        $value = $data << 10;
        for ($i = 4; $i >= 0; $i--) {
            if (($value >> ($i + 10)) & 1) {
                $value ^= $g << $i;
            }
        }

        return $value & 0x3FF;
    }

    public static function svg(string $text, int $moduleSize = 4, string $color = '#000'): ?string
    {
        $matrix = self::encodeMatrix($text);
        if ($matrix === null) {
            return null;
        }
        $size = count($matrix);
        $px = $size * $moduleSize;
        $rects = '';
        for ($r = 0; $r < $size; $r++) {
            for ($c = 0; $c < $size; $c++) {
                if ($matrix[$r][$c]) {
                    $rects .= '<rect x="' . ($c * $moduleSize) . '" y="' . ($r * $moduleSize) . '" width="' . $moduleSize . '" height="' . $moduleSize . '"/>';
                }
            }
        }

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $px . ' ' . $px . '" width="' . $px . '" height="' . $px . '" shape-rendering="crispEdges">'
            . '<rect width="' . $px . '" height="' . $px . '" fill="#fff"/>'
            . '<g fill="' . htmlspecialchars($color, ENT_QUOTES) . '">' . $rects . '</g></svg>';
    }
}
