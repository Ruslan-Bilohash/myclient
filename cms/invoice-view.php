<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

[$t, $lang] = mk_boot('client');
$client = mk_client_require();

$invoice = mk_invoice_find(mk_get_str('id'));
if ($invoice === null || (string) $invoice['client_id'] !== (string) $client['id']) {
    http_response_code(404);
    exit('Not found');
}

$settings = mk_settings_get();

mk_layout_start(($t['invoice_view_title'] ?? 'Invoice') . ' ' . (string) $invoice['number'], $t, $lang, 'client', 'invoices');
?>
<div class="mk-card mk-invoice-doc">
  <div class="mk-invoice-head">
    <div>
      <h2 style="margin-bottom:2px;"><?= mk_h($settings['company_name'] !== '' ? $settings['company_name'] : MK_SITE_NAME) ?></h2>
      <p class="mk-hint"><?= mk_h($t['invoice_view_title'] ?? 'Invoice') ?> <?= mk_h((string) $invoice['number']) ?></p>
    </div>
    <span class="<?= mk_invoice_status_class($invoice) ?>"><?= mk_h(mk_invoice_status_label($invoice, $t)) ?></span>
  </div>
  <div class="mk-invoice-parties">
    <div>
      <h4><?= mk_h($t['invoice_from'] ?? 'From') ?></h4>
      <p>
        <?php
        $fromLines = array_filter([$settings['company_name'], $settings['company_address'], $settings['company_org_number'], $settings['company_email'], $settings['company_phone']], static fn(string $v): bool => $v !== '');
        foreach (array_values($fromLines) as $i => $fl) {
            echo ($i > 0 ? '<br>' : '') . nl2br(mk_h($fl));
        }
        ?>
      </p>
    </div>
    <div>
      <h4><?= mk_h($t['invoice_to'] ?? 'Client') ?></h4>
      <p><?= mk_h((string) $client['name']) ?><?php if (!empty($client['company'])): ?><br><?= mk_h((string) $client['company']) ?><?php endif; ?><br><?= mk_h((string) $client['email']) ?></p>
    </div>
  </div>
  <p class="mk-hint" style="margin:-10px 0 20px;"><?= mk_h($t['invoice_created_at'] ?? 'Created') ?>: <?= mk_h((string) $invoice['created_at']) ?> · <?= mk_h($t['invoice_due_at'] ?? 'Due date') ?>: <?= mk_h((string) $invoice['due_at']) ?></p>
  <div class="mk-table-wrap">
    <table class="mk-table">
      <thead><tr><th><?= mk_h($t['invoice_line_desc'] ?? 'Description') ?></th><th><?= mk_h($t['invoice_line_qty'] ?? 'Qty') ?></th><th><?= mk_h($t['invoice_line_unit'] ?? 'Unit') ?></th><th><?= mk_h($t['invoice_line_total'] ?? 'Total') ?></th></tr></thead>
      <tbody>
      <?php foreach (mk_invoice_display_lines($invoice, $lang) as $line): ?>
        <tr><td><?= mk_h((string) $line['desc']) ?></td><td><?= mk_h((string) $line['qty']) ?></td><td><?= mk_h(mk_money((float) $line['unit'], (string) $invoice['currency'])) ?></td><td><?= mk_h(mk_money((float) $line['total'], (string) $invoice['currency'])) ?></td></tr>
      <?php endforeach; ?>
      <?php if ((float) ($invoice['tax_percent'] ?? 0) > 0): ?>
      <tr><td colspan="3" style="text-align:right;"><?= mk_h($t['invoice_subtotal'] ?? 'Subtotal') ?></td><td><?= mk_h(mk_money((float) $invoice['subtotal'], (string) $invoice['currency'])) ?></td></tr>
      <tr><td colspan="3" style="text-align:right;"><?= mk_h($t['invoice_tax'] ?? 'Tax') ?> (<?= mk_h(rtrim(rtrim(number_format((float) $invoice['tax_percent'], 2, '.', ''), '0'), '.')) ?>%)</td><td><?= mk_h(mk_money((float) $invoice['tax_amount'], (string) $invoice['currency'])) ?></td></tr>
      <?php endif; ?>
      <tr class="mk-invoice-total-row"><td colspan="3" style="text-align:right;"><?= mk_h($t['invoice_total'] ?? 'Total due') ?></td><td><?= mk_money_with_fx((float) $invoice['total'], $lang, $t) ?></td></tr>
      </tbody>
    </table>
  </div>
  <?php if ($settings['company_bank'] !== '' && ($invoice['status'] ?? '') === 'pending'): ?>
  <p class="mk-hint" style="margin-top:18px;"><strong><?= mk_h($t['settings_company_bank'] ?? 'Bank details') ?>:</strong><br><?= nl2br(mk_h($settings['company_bank'])) ?></p>
  <?php endif; ?>
  <?php if (($invoice['status'] ?? '') === 'pending'): $payUrl = mk_invoice_pay_url($invoice); $qr = MkQr::svg($payUrl, 4); ?>
  <div class="mk-pay-qr-block mk-no-print">
    <?php if ($qr !== null): ?><?= $qr ?><?php endif; ?>
    <div class="mk-pay-link-col">
      <p class="mk-hint" style="margin:0 0 6px;"><?= mk_h($t['invoice_pay_qr_hint'] ?? 'Scan to pay') ?></p>
      <div class="mk-pay-link-row">
        <input id="mkPayLink" type="text" readonly value="<?= mk_h($payUrl) ?>" onclick="this.select()">
        <button type="button" class="mk-btn mk-btn--sm mk-btn--ghost" data-mk-copy="mkPayLink" data-mk-copied="<?= mk_h($t['btn_copied'] ?? 'Copied!') ?>"><?= mk_h($t['btn_copy_link'] ?? 'Copy') ?></button>
      </div>
    </div>
  </div>
  <?php endif; ?>
  <div class="mk-table-actions mk-no-print" style="margin-top:18px;">
    <a class="mk-btn mk-btn--ghost" href="<?= mk_h(mk_base_url('invoice-pdf.php?id=' . rawurlencode((string) $invoice['id']))) ?>" target="_blank"><?= mk_h($t['btn_download_pdf'] ?? 'Download PDF') ?></a>
    <?php if (($invoice['status'] ?? '') === 'pending'): ?>
    <a class="mk-btn mk-btn--primary" href="<?= mk_h(mk_base_url('invoice-pay.php?id=' . rawurlencode((string) $invoice['id']))) ?>"><?= mk_h($t['btn_pay'] ?? 'Pay') ?></a>
    <?php endif; ?>
  </div>
</div>
<?php mk_layout_end(); ?>
