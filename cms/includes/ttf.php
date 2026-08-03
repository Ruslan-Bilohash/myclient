<?php
declare(strict_types=1);

/**
 * Minimal TrueType font reader — just enough to embed a font in a PDF as a Type0/CIDFontType2
 * composite font: unitsPerEm, per-glyph advance widths, and a Unicode (BMP) cmap.
 * Loads no external tools; only reads tables out of the font's own binary.
 */
final class MkTtfFont
{
    public string $bytes;
    public int $unitsPerEm = 1000;
    public int $ascent = 800;
    public int $descent = -200;
    /** @var array<int,int> codepoint => glyphId */
    private array $cmap = [];
    /** @var array<int,int> glyphId => advance width (font units) */
    private array $widths = [];
    private int $defaultWidth = 600;

    public static function load(string $path): ?self
    {
        if (!is_file($path)) {
            return null;
        }
        $bytes = (string) file_get_contents($path);
        if ($bytes === '') {
            return null;
        }
        $font = new self();
        $font->bytes = $bytes;

        try {
            $tables = $font->readTableDirectory($bytes);
            if (!isset($tables['head'], $tables['maxp'], $tables['hhea'], $tables['hmtx'], $tables['cmap'])) {
                return null;
            }
            $font->readHead($bytes, $tables['head']);
            $numGlyphs = $font->readMaxp($bytes, $tables['maxp']);
            $numHMetrics = $font->readHheaNumHMetrics($bytes, $tables['hhea']);
            $font->readHmtx($bytes, $tables['hmtx'], $numHMetrics, $numGlyphs);
            $font->readCmap($bytes, $tables['cmap']);
        } catch (Throwable $e) {
            return null;
        }

        return $font->cmap === [] ? null : $font;
    }

    /** @return array<string, array{offset:int,length:int}> */
    private function readTableDirectory(string $b): array
    {
        $numTables = self::u16($b, 4);
        $tables = [];
        for ($i = 0; $i < $numTables; $i++) {
            $rec = 12 + $i * 16;
            $tag = substr($b, $rec, 4);
            $offset = self::u32($b, $rec + 8);
            $length = self::u32($b, $rec + 12);
            $tables[$tag] = ['offset' => $offset, 'length' => $length];
        }

        return $tables;
    }

    private function readHead(string $b, array $t): void
    {
        $this->unitsPerEm = self::u16($b, $t['offset'] + 18) ?: 1000;
    }

    private function readMaxp(string $b, array $t): int
    {
        return self::u16($b, $t['offset'] + 4);
    }

    private function readHheaNumHMetrics(string $b, array $t): int
    {
        $this->ascent = self::i16($b, $t['offset'] + 4);
        $this->descent = self::i16($b, $t['offset'] + 6);

        return self::u16($b, $t['offset'] + 34);
    }

    private function readHmtx(string $b, array $t, int $numHMetrics, int $numGlyphs): void
    {
        $off = $t['offset'];
        $lastWidth = $this->defaultWidth;
        for ($g = 0; $g < $numHMetrics; $g++) {
            $lastWidth = self::u16($b, $off + $g * 4);
            $this->widths[$g] = $lastWidth;
        }
        for ($g = $numHMetrics; $g < $numGlyphs; $g++) {
            $this->widths[$g] = $lastWidth;
        }
        $this->defaultWidth = $lastWidth;
    }

    private function readCmap(string $b, array $t): void
    {
        $base = $t['offset'];
        $numTables = self::u16($b, $base + 2);
        $best = null;
        $bestScore = -1;
        for ($i = 0; $i < $numTables; $i++) {
            $rec = $base + 4 + $i * 8;
            $platformId = self::u16($b, $rec);
            $encodingId = self::u16($b, $rec + 2);
            $subOffset = self::u32($b, $rec + 4);
            $score = match (true) {
                $platformId === 3 && $encodingId === 1 => 3,
                $platformId === 0 => 2,
                $platformId === 3 && $encodingId === 0 => 1,
                default => 0,
            };
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $base + $subOffset;
            }
        }
        if ($best === null) {
            return;
        }
        $format = self::u16($b, $best);
        if ($format === 4) {
            $this->readCmapFormat4($b, $best);
        } elseif ($format === 12) {
            $this->readCmapFormat12($b, $best);
        }
    }

    private function readCmapFormat4(string $b, int $off): void
    {
        $segCountX2 = self::u16($b, $off + 6);
        $segCount = intdiv($segCountX2, 2);
        $endCodeOff = $off + 14;
        $startCodeOff = $endCodeOff + $segCountX2 + 2; // +2 skips reservedPad
        $idDeltaOff = $startCodeOff + $segCountX2;
        $idRangeOff = $idDeltaOff + $segCountX2;

        for ($s = 0; $s < $segCount; $s++) {
            $endCode = self::u16($b, $endCodeOff + $s * 2);
            $startCode = self::u16($b, $startCodeOff + $s * 2);
            $idDelta = self::i16($b, $idDeltaOff + $s * 2);
            $idRangeOffset = self::u16($b, $idRangeOff + $s * 2);
            if ($startCode === 0xFFFF && $endCode === 0xFFFF) {
                continue;
            }
            for ($c = $startCode; $c <= $endCode && $c !== 0xFFFF; $c++) {
                if ($idRangeOffset === 0) {
                    $gid = ($c + $idDelta) & 0xFFFF;
                } else {
                    $addr = $idRangeOff + $s * 2 + $idRangeOffset + ($c - $startCode) * 2;
                    if ($addr + 2 > strlen($b)) {
                        continue;
                    }
                    $g = self::u16($b, $addr);
                    $gid = $g === 0 ? 0 : ($g + $idDelta) & 0xFFFF;
                }
                if ($gid !== 0) {
                    $this->cmap[$c] = $gid;
                }
            }
        }
    }

    private function readCmapFormat12(string $b, int $off): void
    {
        $numGroups = self::u32($b, $off + 12);
        for ($g = 0; $g < $numGroups; $g++) {
            $rec = $off + 16 + $g * 12;
            $startChar = self::u32($b, $rec);
            $endChar = self::u32($b, $rec + 4);
            $startGlyph = self::u32($b, $rec + 8);
            if ($endChar - $startChar > 20000) {
                continue; // guard against corrupt/oversized groups
            }
            for ($c = $startChar; $c <= $endChar; $c++) {
                $this->cmap[$c] = $startGlyph + ($c - $startChar);
            }
        }
    }

    public function glyphId(int $codepoint): int
    {
        return $this->cmap[$codepoint] ?? 0;
    }

    public function glyphWidth(int $glyphId): int
    {
        return $this->widths[$glyphId] ?? $this->defaultWidth;
    }

    public function unitsToPdf(int $unitsValue): float
    {
        return round($unitsValue * 1000 / $this->unitsPerEm, 2);
    }

    private static function u16(string $b, int $pos): int
    {
        return unpack('n', substr($b, $pos, 2))[1] ?? 0;
    }

    private static function i16(string $b, int $pos): int
    {
        $v = self::u16($b, $pos);

        return $v >= 0x8000 ? $v - 0x10000 : $v;
    }

    private static function u32(string $b, int $pos): int
    {
        return unpack('N', substr($b, $pos, 4))[1] ?? 0;
    }
}
