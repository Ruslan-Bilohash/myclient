<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

[$t, $lang] = mk_boot('client');
$client = mk_client_require();

$invoices = mk_invoices_for_client((string) $client['id']);
$pending = array_values(array_filter($invoices, static fn(array $i): bool => ($i['status'] ?? '') === 'pending'));

mk_layout_start($t['cab_invoices_title'] ?? 'My invoices', $t, $lang, 'client', 'invoices');
?>
<?php if (count($pending) > 1): $pendingIds = implode(',', array_map(static fn(array $i): string => (string) $i['id'], $pending)); $pendingTotal = array_sum(array_map(static fn(array $i): float => (float) $i['total'], $pending)); ?>
<div class="mk-card" style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
  <div>
    <p class="mk-hint" style="margin:0 0 4px;"><?= mk_h(count($pending) . ' ' . mb_strtolower($t['invoice_status_pending'] ?? 'pending')) ?></p>
    <p class="mk-pay-amount" style="margin:0;font-size:24px;"><?= mk_h(mk_money($pendingTotal, (string) ($pending[0]['currency'] ?? MK_CURRENCY))) ?></p>
  </div>
  <a class="mk-btn mk-btn--primary" href="<?= mk_h(mk_base_url('invoice-pay.php?ids=' . rawurlencode($pendingIds))) ?>"><?= mk_h($t['invoice_pay_all'] ?? 'Pay all invoices') ?></a>
</div>
<?php endif; ?>
<div class="mk-card">
  <?php if ($invoices === []): ?>
    <p class="mk-hint"><?= mk_h($t['cab_no_invoices'] ?? 'No invoices yet.') ?></p>
  <?php else: ?>
  <div class="mk-table-wrap">
    <table class="mk-table">
      <thead><tr>
        <th><?= mk_h($t['invoice_number'] ?? 'Number') ?></th>
        <th><?= mk_h($t['invoice_amount'] ?? 'Amount') ?></th>
        <th><?= mk_h($t['invoice_status'] ?? 'Status') ?></th>
        <th><?= mk_h($t['invoice_created_at'] ?? 'Created') ?></th>
        <th></th>
      </tr></thead>
      <tbody>
      <?php foreach ($invoices as $inv): ?>
        <tr>
          <td><?= mk_h((string) $inv['number']) ?></td>
          <td><?= mk_money_with_fx((float) $inv['total'], $lang, $t) ?></td>
          <td><span class="<?= mk_invoice_status_class($inv) ?>"><?= mk_h(mk_invoice_status_label($inv, $t)) ?></span></td>
          <td><?= mk_h((string) $inv['created_at']) ?></td>
          <td class="mk-table-actions">
            <a class="mk-btn mk-btn--sm mk-btn--ghost" href="<?= mk_h(mk_base_url('invoice-view.php?id=' . rawurlencode((string) $inv['id']))) ?>"><?= mk_h($t['btn_view'] ?? 'View') ?></a>
            <a class="mk-btn mk-btn--sm mk-btn--ghost" href="<?= mk_h(mk_base_url('invoice-pdf.php?id=' . rawurlencode((string) $inv['id']))) ?>" target="_blank">PDF</a>
            <?php if (($inv['status'] ?? '') === 'pending'): ?>
            <a class="mk-btn mk-btn--sm mk-btn--primary" href="<?= mk_h(mk_base_url('invoice-pay.php?id=' . rawurlencode((string) $inv['id']))) ?>"><?= mk_h($t['btn_pay'] ?? 'Pay') ?></a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
<?php mk_layout_end(); ?>
