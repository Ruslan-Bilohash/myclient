<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

[$t, $lang] = mk_boot('admin');
mk_admin_require();

$invoice = mk_invoice_find(mk_get_str('id'));
if ($invoice === null) {
    http_response_code(404);
    exit('Not found');
}
$client = mk_client_find((string) $invoice['client_id']) ?? ['name' => '', 'email' => ''];
$invoiceForPdf = $invoice;
$invoiceForPdf['lines'] = mk_invoice_display_lines($invoice, $lang);

$pdf = mk_pdf_invoice_bytes($invoiceForPdf, $client, $t);

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . preg_replace('/[^A-Za-z0-9_-]/', '-', (string) $invoice['number']) . '.pdf"');
header('Content-Length: ' . strlen($pdf));
echo $pdf;
