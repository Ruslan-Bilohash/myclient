<?php
declare(strict_types=1);

/**
 * Dependency-free PDF writer (PDF 1.4). Prefers a real embedded Unicode font (DejaVu Sans,
 * assets/fonts/) via Type0/CIDFontType2 so Ukrainian/Norwegian text renders as real glyphs —
 * not transliterated. Falls back to core Helvetica + Latin transliteration only if the font
 * files are missing from the deployment.
 */

require_once __DIR__ . '/ttf.php';

/** @return array{regular: ?MkTtfFont, bold: ?MkTtfFont} */
function mk_pdf_fonts(): array
{
    static $fonts = null;
    if ($fonts !== null) {
        return $fonts;
    }
    $fonts = [
        'regular' => MkTtfFont::load(MK_ROOT . '/assets/fonts/DejaVuSans.ttf'),
        'bold' => MkTtfFont::load(MK_ROOT . '/assets/fonts/DejaVuSans-Bold.ttf'),
    ];

    return $fonts;
}

function mk_pdf_escape(string $s): string
{
    return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $s);
}

function mk_pdf_translit_uk(string $s): string
{
    static $map = null;
    if ($map === null) {
        $map = [
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'h', 'ґ' => 'g', 'д' => 'd', 'е' => 'e', 'є' => 'ie',
            'ж' => 'zh', 'з' => 'z', 'и' => 'y', 'і' => 'i', 'ї' => 'i', 'й' => 'i', 'к' => 'k', 'л' => 'l',
            'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u',
            'ф' => 'f', 'х' => 'kh', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch', 'ь' => '', 'ю' => 'iu',
            'я' => 'ia', '№' => 'No.',
        ];
        $upper = [];
        foreach ($map as $k => $v) {
            $upper[mb_strtoupper($k, 'UTF-8')] = ucfirst($v);
        }
        $map = $map + $upper;
    }

    return strtr($s, $map);
}

function mk_pdf_text_latin1(string $s): string
{
    if (preg_match('/[\x{0400}-\x{04FF}]/u', $s) === 1) {
        $s = mk_pdf_translit_uk($s);
    }
    $converted = @iconv('UTF-8', 'CP1252//TRANSLIT//IGNORE', $s);

    return $converted !== false ? $converted : preg_replace('/[^\x20-\x7E]/', '?', $s);
}

/**
 * @param list<array{text:string,x:float,y:float,size?:int,bold?:bool}> $lines
 */
function mk_pdf_document(array $lines): string
{
    $fonts = mk_pdf_fonts();
    if ($fonts['regular'] !== null && $fonts['bold'] !== null) {
        return mk_pdf_document_unicode($lines, $fonts['regular'], $fonts['bold']);
    }

    return mk_pdf_document_latin1($lines);
}

/** @param array<int,int> $usedGlyphs gid => unicode codepoint, filled as we go */
function mk_pdf_encode_run(MkTtfFont $font, string $utf8Text, array &$usedGlyphs): string
{
    $hex = '';
    foreach (preg_split('//u', $utf8Text, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $ch) {
        $cp = mb_ord($ch, 'UTF-8');
        if ($cp === false) {
            continue;
        }
        $gid = $font->glyphId($cp);
        if ($gid === 0 && $cp !== 0x20) {
            $gid = $font->glyphId(0x3F); // '?' fallback for glyphs missing from the font
        }
        if (!isset($usedGlyphs[$gid])) {
            $usedGlyphs[$gid] = $cp;
        }
        $hex .= sprintf('%04X', $gid);
    }

    return $hex;
}

function mk_pdf_document_unicode(array $lines, MkTtfFont $regular, MkTtfFont $bold): string
{
    $pageHeight = 842.0;
    $usedRegular = [];
    $usedBold = [];
    $content = "BT\n";
    $lastFontKey = '';
    $lastSize = 0;

    foreach ($lines as $line) {
        $size = (int) ($line['size'] ?? 10);
        $isBold = !empty($line['bold']);
        $fontKey = $isBold ? 'bold' : 'regular';
        if ($fontKey !== $lastFontKey || $size !== $lastSize) {
            $content .= ($isBold ? '/F2 ' : '/F1 ') . $size . " Tf\n";
            $lastFontKey = $fontKey;
            $lastSize = $size;
        }
        $font = $isBold ? $bold : $regular;
        $used = &$usedRegular;
        if ($isBold) {
            $used = &$usedBold;
        }
        $hex = mk_pdf_encode_run($font, (string) $line['text'], $used);
        unset($used);
        $x = number_format((float) $line['x'], 2, '.', '');
        $y = number_format($pageHeight - (float) $line['y'], 2, '.', '');
        $content .= "1 0 0 1 {$x} {$y} Tm <{$hex}> Tj\n";
    }
    $content .= "ET\n";

    $objects = [];
    $objects[1] = "<< /Type /Catalog /Pages 2 0 R >>";
    $objects[2] = "<< /Type /Pages /Kids [3 0 R] /Count 1 >>";
    $objects[3] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R /F2 9 0 R >> >> /Contents 14 0 R >>";

    mk_pdf_add_composite_font($objects, 4, 'DejaVuSans', $regular, $usedRegular);
    mk_pdf_add_composite_font($objects, 9, 'DejaVuSans-Bold', $bold, $usedBold);

    $objects[14] = ['dict' => '<< /Length ' . strlen($content) . ' >>', 'stream' => $content];

    return mk_pdf_assemble($objects);
}

/**
 * Registers objects $base (Type0), $base+1 (CIDFontType2), $base+2 (FontDescriptor),
 * $base+3 (FontFile2 stream), $base+4 (ToUnicode stream) into $objects.
 * @param array<int,int> $usedGlyphs gid => unicode codepoint
 */
function mk_pdf_add_composite_font(array &$objects, int $base, string $psName, MkTtfFont $font, array $usedGlyphs): void
{
    ksort($usedGlyphs);
    $wParts = [];
    foreach ($usedGlyphs as $gid => $cp) {
        $w = $font->unitsToPdf($font->glyphWidth($gid));
        $wParts[] = "{$gid} [{$w}]";
    }
    $wArray = implode(' ', $wParts);

    $objects[$base] = "<< /Type /Font /Subtype /Type0 /BaseFont /{$psName} /Encoding /Identity-H "
        . "/DescendantFonts [{$base}1 0 R] /ToUnicode {$base}4 0 R >>";

    $objects[$base . '1'] = "<< /Type /Font /Subtype /CIDFontType2 /BaseFont /{$psName} "
        . "/CIDSystemInfo << /Registry (Adobe) /Ordering (Identity) /Supplement 0 >> "
        . "/FontDescriptor {$base}2 0 R /DW 600 /W [{$wArray}] /CIDToGIDMap /Identity >>";

    $ascent = $font->unitsToPdf($font->ascent);
    $descent = $font->unitsToPdf($font->descent);
    $objects[$base . '2'] = "<< /Type /FontDescriptor /FontName /{$psName} /Flags 32 "
        . "/FontBBox [-1000 {$descent} 2000 {$ascent}] /ItalicAngle 0 /Ascent {$ascent} /Descent {$descent} "
        . "/CapHeight {$ascent} /StemV 80 /FontFile2 {$base}3 0 R >>";

    $raw = $font->bytes;
    $compressed = gzcompress($raw, 6);
    $objects[$base . '3'] = [
        'dict' => '<< /Length ' . strlen($compressed) . ' /Length1 ' . strlen($raw) . ' /Filter /FlateDecode >>',
        'stream' => $compressed,
    ];

    $bfLines = [];
    foreach ($usedGlyphs as $gid => $cp) {
        $bfLines[] = sprintf('<%04X> <%04X>', $gid, $cp);
    }
    $bfBody = implode("\n", $bfLines);
    $cmap = "/CIDInit /ProcSet findresource begin\n12 dict begin\nbegincmap\n"
        . "/CIDSystemInfo << /Registry (Adobe) /Ordering (UCS) /Supplement 0 >> def\n"
        . "/CMapName /Adobe-Identity-UCS def\n/CMapType 2 def\n"
        . "1 begincodespacerange\n<0000> <FFFF>\nendcodespacerange\n"
        . count($usedGlyphs) . " beginbfchar\n{$bfBody}\nendbfchar\nendcmap\n"
        . "CMapName currentdict /CMap defineresource pop\nend\nend";
    $objects[$base . '4'] = ['dict' => '<< /Length ' . strlen($cmap) . ' >>', 'stream' => $cmap];
}

/**
 * @param array<int|string, string|array{dict:string,stream:string}> $objects object number
 *   (int, or "int+suffix" string id for composite font sub-objects) => dict-only body,
 *   or ['dict'=>..., 'stream'=>...] for objects carrying a binary/opaque stream payload.
 *   Reference renumbering only ever touches 'dict' text (or plain-string bodies) — never a
 *   stream payload — so compressed font bytes can never be corrupted by an incidental
 *   byte sequence that happens to look like "N 0 R".
 */
function mk_pdf_assemble(array $objects): string
{
    $numbered = [];
    foreach ($objects as $key => $body) {
        $numbered[(string) $key] = $body;
    }

    // Emit in ascending numeric-ish order for readability; PDF only cares that xref offsets are correct.
    uksort($numbered, static fn(string $a, string $b): int => strnatcmp($a, $b));
    $idMap = [];
    $n = 1;
    foreach ($numbered as $key => $body) {
        $idMap[$key] = $n++;
    }

    $rewrite = static fn(string $dict): string => preg_replace_callback(
        '/(\d+) 0 R/',
        static fn(array $m): string => ($idMap[$m[1]] ?? $m[1]) . ' 0 R',
        $dict
    );

    $pdf = "%PDF-1.4\n";
    $offsets = [];
    foreach ($numbered as $key => $body) {
        $num = $idMap[$key];
        $offsets[$num] = strlen($pdf);
        if (is_array($body)) {
            $dict = $rewrite($body['dict']);
            $pdf .= "{$num} 0 obj\n{$dict}\nstream\n" . $body['stream'] . "\nendstream\nendobj\n";
        } else {
            $pdf .= "{$num} 0 obj\n" . $rewrite($body) . "\nendobj\n";
        }
    }
    $xrefStart = strlen($pdf);
    $count = count($numbered) + 1;
    $pdf .= "xref\n0 {$count}\n0000000000 65535 f \n";
    ksort($offsets);
    foreach ($offsets as $off) {
        $pdf .= str_pad((string) $off, 10, '0', STR_PAD_LEFT) . " 00000 n \n";
    }
    $rootNum = $idMap['1'];
    $pdf .= "trailer\n<< /Size {$count} /Root {$rootNum} 0 R >>\nstartxref\n{$xrefStart}\n%%EOF";

    return $pdf;
}

/** Legacy fallback path: core Helvetica + Latin transliteration (used only if fonts/*.ttf are missing). */
function mk_pdf_document_latin1(array $lines): string
{
    $pageHeight = 842.0;
    $content = "BT\n";
    $lastSize = 0;
    foreach ($lines as $line) {
        $size = (int) ($line['size'] ?? 10);
        $font = !empty($line['bold']) ? '/F2' : '/F1';
        if ($size !== $lastSize) {
            $content .= "{$font} {$size} Tf\n";
            $lastSize = $size;
        } else {
            $content .= "{$font} {$size} Tf\n";
        }
        $x = number_format((float) $line['x'], 2, '.', '');
        $y = number_format($pageHeight - (float) $line['y'], 2, '.', '');
        $text = mk_pdf_escape(mk_pdf_text_latin1((string) $line['text']));
        $content .= "1 0 0 1 {$x} {$y} Tm ({$text}) Tj\n";
    }
    $content .= "ET\n";

    $objects = [];
    $objects[1] = "<< /Type /Catalog /Pages 2 0 R >>";
    $objects[2] = "<< /Type /Pages /Kids [3 0 R] /Count 1 >>";
    $objects[3] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R /F2 5 0 R >> >> /Contents 6 0 R >>";
    $objects[4] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>";
    $objects[5] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>";
    $objects[6] = "<< /Length " . strlen($content) . " >>\nstream\n{$content}endstream";

    $pdf = "%PDF-1.4\n";
    $offsets = [];
    foreach ($objects as $num => $body) {
        $offsets[$num] = strlen($pdf);
        $pdf .= "{$num} 0 obj\n{$body}\nendobj\n";
    }
    $xrefStart = strlen($pdf);
    $count = count($objects) + 1;
    $pdf .= "xref\n0 {$count}\n0000000000 65535 f \n";
    for ($i = 1; $i <= count($objects); $i++) {
        $pdf .= str_pad((string) $offsets[$i], 10, '0', STR_PAD_LEFT) . " 00000 n \n";
    }
    $pdf .= "trailer\n<< /Size {$count} /Root 1 0 R >>\nstartxref\n{$xrefStart}\n%%EOF";

    return $pdf;
}

function mk_pdf_invoice_bytes(array $invoice, array $client, array $t): string
{
    $settings = mk_settings_get();
    $lines = [];
    $y = 60;
    $lines[] = ['text' => $settings['company_name'] !== '' ? $settings['company_name'] : MK_SITE_NAME, 'x' => 50, 'y' => $y, 'size' => 18, 'bold' => true];
    $y += 30;
    $lines[] = ['text' => ($t['invoice_view_title'] ?? 'Invoice') . ' ' . (string) ($invoice['number'] ?? ''), 'x' => 50, 'y' => $y, 'size' => 13, 'bold' => true];
    $y += 22;
    $lines[] = ['text' => ($t['invoice_created_at'] ?? 'Created') . ': ' . (string) ($invoice['created_at'] ?? ''), 'x' => 50, 'y' => $y, 'size' => 10];
    $y += 16;
    $lines[] = ['text' => ($t['invoice_due_at'] ?? 'Due date') . ': ' . (string) ($invoice['due_at'] ?? ''), 'x' => 50, 'y' => $y, 'size' => 10];
    $y += 30;

    $colFromY = $y;
    $colToY = $y;
    $lines[] = ['text' => ($t['invoice_from'] ?? 'From') . ':', 'x' => 50, 'y' => $colFromY, 'size' => 10, 'bold' => true];
    $colFromY += 16;
    foreach ([$settings['company_name'], $settings['company_address'], $settings['company_org_number'], $settings['company_email'], $settings['company_phone']] as $fromLine) {
        if ($fromLine === '') {
            continue;
        }
        foreach (explode("\n", $fromLine) as $wrapped) {
            $lines[] = ['text' => $wrapped, 'x' => 50, 'y' => $colFromY, 'size' => 10];
            $colFromY += 15;
        }
    }

    $lines[] = ['text' => ($t['invoice_to'] ?? 'Client') . ':', 'x' => 320, 'y' => $colToY, 'size' => 10, 'bold' => true];
    $colToY += 16;
    $lines[] = ['text' => (string) ($client['name'] ?? ''), 'x' => 320, 'y' => $colToY, 'size' => 10];
    $colToY += 15;
    if (!empty($client['company'])) {
        $lines[] = ['text' => (string) $client['company'], 'x' => 320, 'y' => $colToY, 'size' => 10];
        $colToY += 15;
    }
    $lines[] = ['text' => (string) ($client['email'] ?? ''), 'x' => 320, 'y' => $colToY, 'size' => 10];
    $colToY += 15;

    $y = max($colFromY, $colToY) + 20;

    $lines[] = ['text' => $t['invoice_line_desc'] ?? 'Description', 'x' => 50, 'y' => $y, 'size' => 10, 'bold' => true];
    $lines[] = ['text' => $t['invoice_line_qty'] ?? 'Qty', 'x' => 340, 'y' => $y, 'size' => 10, 'bold' => true];
    $lines[] = ['text' => $t['invoice_line_unit'] ?? 'Unit', 'x' => 400, 'y' => $y, 'size' => 10, 'bold' => true];
    $lines[] = ['text' => $t['invoice_line_total'] ?? 'Total', 'x' => 480, 'y' => $y, 'size' => 10, 'bold' => true];
    $y += 8;
    $lines[] = ['text' => str_repeat('-', 96), 'x' => 50, 'y' => $y, 'size' => 9];
    $y += 16;

    $currency = (string) ($invoice['currency'] ?? MK_CURRENCY);
    foreach ((array) ($invoice['lines'] ?? []) as $line) {
        $lines[] = ['text' => (string) ($line['desc'] ?? ''), 'x' => 50, 'y' => $y, 'size' => 10];
        $lines[] = ['text' => (string) ($line['qty'] ?? 1), 'x' => 340, 'y' => $y, 'size' => 10];
        $lines[] = ['text' => mk_money((float) ($line['unit'] ?? 0), $currency), 'x' => 400, 'y' => $y, 'size' => 10];
        $lines[] = ['text' => mk_money((float) ($line['total'] ?? 0), $currency), 'x' => 480, 'y' => $y, 'size' => 10];
        $y += 18;
    }

    $y += 10;
    $lines[] = ['text' => str_repeat('-', 96), 'x' => 50, 'y' => $y, 'size' => 9];
    $y += 20;

    $taxPercent = (float) ($invoice['tax_percent'] ?? 0);
    if ($taxPercent > 0) {
        $lines[] = ['text' => ($t['invoice_subtotal'] ?? 'Subtotal') . ':', 'x' => 380, 'y' => $y, 'size' => 10];
        $lines[] = ['text' => mk_money((float) ($invoice['subtotal'] ?? 0), $currency), 'x' => 480, 'y' => $y, 'size' => 10];
        $y += 16;
        $taxLabel = ($t['invoice_tax'] ?? 'Tax') . ' (' . rtrim(rtrim(number_format($taxPercent, 2, '.', ''), '0'), '.') . '%):';
        $lines[] = ['text' => $taxLabel, 'x' => 380, 'y' => $y, 'size' => 10];
        $lines[] = ['text' => mk_money((float) ($invoice['tax_amount'] ?? 0), $currency), 'x' => 480, 'y' => $y, 'size' => 10];
        $y += 18;
    }

    $lines[] = ['text' => ($t['invoice_total'] ?? 'Total due') . ':', 'x' => 380, 'y' => $y, 'size' => 12, 'bold' => true];
    $lines[] = ['text' => mk_money((float) ($invoice['total'] ?? 0), $currency), 'x' => 480, 'y' => $y, 'size' => 12, 'bold' => true];
    $y += 24;
    $lines[] = ['text' => ($t['invoice_status'] ?? 'Status') . ': ' . mk_invoice_status_label($invoice, $t), 'x' => 50, 'y' => $y, 'size' => 10];
    if (!empty($invoice['paid_at'])) {
        $y += 15;
        $lines[] = ['text' => ($t['invoice_paid_at'] ?? 'Paid at') . ': ' . (string) $invoice['paid_at'], 'x' => 50, 'y' => $y, 'size' => 10];
    }
    if ($settings['company_bank'] !== '' && ($invoice['status'] ?? '') === 'pending') {
        $y += 24;
        $lines[] = ['text' => $t['settings_company_bank'] ?? 'Bank details', 'x' => 50, 'y' => $y, 'size' => 10, 'bold' => true];
        foreach (explode("\n", $settings['company_bank']) as $bankLine) {
            $y += 15;
            $lines[] = ['text' => $bankLine, 'x' => 50, 'y' => $y, 'size' => 10];
        }
    }

    return mk_pdf_document($lines);
}
