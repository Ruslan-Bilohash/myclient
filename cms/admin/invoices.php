<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

[$t, $lang] = mk_boot('admin');
if (mk_admin_bootstrap_needed()) {
    mk_redirect(mk_base_url('admin/setup.php'));
}
mk_admin_require();

$error = '';

if (mk_is_post()) {
    mk_csrf_require();
    $action = mk_post_str('action');

    if ($action === 'create') {
        $client = mk_client_find(mk_post_str('client_id'));
        if ($client === null) {
            $error = $t['clients_title'] ?? 'Client';
        } else {
            $serviceId = mk_post_str('service_id');
            $period = in_array(mk_post_str('period'), ['m1', 'm3', 'm12'], true) ? mk_post_str('period') : 'm1';
            $invLang = (string) ($client['lang'] ?? $lang);
            $invT = mk_load_lang($invLang);
            $lines = [];
            $extra = [];
            if ($serviceId !== '') {
                $service = mk_service_find($serviceId);
                if ($service !== null) {
                    $lines[] = [
                        'desc' => mk_service_name($service, $invLang) . ' — ' . mk_service_period_label($service, $period, $invT),
                        'qty' => 1,
                        'unit' => mk_service_price($service, $period),
                    ];
                    $extra['service_id'] = $serviceId;
                    $extra['period'] = $period;
                }
            }
            foreach (range(1, 4) as $i) {
                $desc = mk_post_str('line_desc_' . $i);
                $unit = (float) str_replace(',', '.', mk_post_str('line_unit_' . $i, '0'));
                $qty = (float) (mk_post_str('line_qty_' . $i, '1') ?: '1');
                if ($desc !== '' && $unit > 0) {
                    $lines[] = ['desc' => $desc, 'qty' => $qty ?: 1, 'unit' => $unit];
                }
            }
            if ($lines === []) {
                $error = $t['invoice_add_line'] ?? 'Add at least one line.';
            } else {
                $invoice = mk_invoice_create($client, $lines, $invLang, $extra);
                mk_mail_invoice_created($client, $invoice, $invT);
                mk_flash_set('ok', $t['invoice_created_ok'] ?? 'Invoice created.');
                mk_redirect(mk_base_url('admin/invoices.php?view=' . rawurlencode((string) $invoice['id'])));
            }
        }
    } elseif ($action === 'mark_paid') {
        $id = mk_post_str('id');
        if (mk_invoice_mark_paid($id, 'manual', 'admin')) {
            $inv = mk_invoice_find($id);
            $c = $inv !== null ? mk_client_find((string) $inv['client_id']) : null;
            if ($inv !== null && $c !== null) {
                mk_mail_invoice_paid($c, $inv, mk_load_lang((string) ($c['lang'] ?? $lang)));
            }
            mk_flash_set('ok', $t['invoice_marked_paid_ok'] ?? 'Marked as paid.');
        }
        mk_redirect(mk_base_url('admin/invoices.php?view=' . rawurlencode($id)));
    } elseif ($action === 'cancel') {
        $id = mk_post_str('id');
        mk_invoice_mark_cancelled($id);
        mk_flash_set('ok', $t['invoice_status_cancelled'] ?? 'Cancelled.');
        mk_redirect(mk_base_url('admin/invoices.php?view=' . rawurlencode($id)));
    } elseif ($action === 'delete') {
        $id = mk_post_str('id');
        mk_invoice_delete($id);
        mk_flash_set('ok', $t['invoice_deleted_ok'] ?? 'Deleted.');
        mk_redirect(mk_base_url('admin/invoices.php'));
    } elseif ($action === 'update') {
        $id = mk_post_str('id');
        $lines = [];
        foreach (range(1, 6) as $i) {
            $lines[] = [
                'desc' => mk_post_str('line_desc_' . $i),
                'qty' => (float) (mk_post_str('line_qty_' . $i, '1') ?: '1'),
                'unit' => (float) str_replace(',', '.', mk_post_str('line_unit_' . $i, '0')),
            ];
        }
        if (mk_invoice_update_lines($id, $lines)) {
            mk_flash_set('ok', $t['invoice_updated_ok'] ?? 'Invoice updated.');
        } else {
            $error = $t['invoice_add_line'] ?? 'Add at least one line.';
        }
        mk_redirect(mk_base_url('admin/invoices.php?view=' . rawurlencode($id)));
    } elseif ($action === 'resend') {
        $id = mk_post_str('id');
        $inv = mk_invoice_find($id);
        $c = $inv !== null ? mk_client_find((string) $inv['client_id']) : null;
        if ($inv !== null && $c !== null) {
            mk_mail_invoice_created($c, $inv, mk_load_lang((string) ($c['lang'] ?? $lang)));
            mk_flash_set('ok', $t['invoice_email_sent_ok'] ?? 'Email sent.');
        }
        mk_redirect(mk_base_url('admin/invoices.php?view=' . rawurlencode($id)));
    }
}

$clients = mk_clients_all();
$clientsById = [];
foreach ($clients as $c) {
    $clientsById[(string) $c['id']] = $c;
}
$services = mk_services_all(true);

$viewId = mk_get_str('view');
$viewing = $viewId !== '' ? mk_invoice_find($viewId) : null;
$invoices = mk_invoices_all();
$editingInvoice = $viewing !== null && mk_get_str('edit') === '1' && ($viewing['status'] ?? '') === 'pending';

mk_layout_start($t['invoices_title'] ?? 'Invoices', $t, $lang, 'admin', 'invoices');
?>
<?php if ($error !== ''): ?><div class="mk-alert mk-alert--error"><?= mk_h($error) ?></div><?php endif; ?>

<?php if ($viewing !== null): $vc = $clientsById[(string) $viewing['client_id']] ?? null; ?>
<div class="mk-card">
  <div class="mk-invoice-head">
    <h2><?= mk_h($t['invoice_view_title'] ?? 'Invoice') ?> <?= mk_h((string) $viewing['number']) ?></h2>
    <span class="<?= mk_invoice_status_class($viewing) ?>"><?= mk_h(mk_invoice_status_label($viewing, $t)) ?></span>
  </div>
  <p><strong><?= mk_h($t['invoice_to'] ?? 'Client') ?>:</strong> <?= mk_h($vc['name'] ?? '—') ?> (<?= mk_h($vc['email'] ?? '') ?>)</p>

  <?php if ($editingInvoice): ?>
  <form method="post">
    <?= mk_csrf_field() ?>
    <input type="hidden" name="action" value="update">
    <input type="hidden" name="id" value="<?= mk_h((string) $viewing['id']) ?>">
    <?php $existingLines = (array) $viewing['lines']; for ($i = 1; $i <= 6; $i++): $el = $existingLines[$i - 1] ?? null; ?>
    <div class="mk-form-row">
      <div class="mk-field"><input class="mk-input" name="line_desc_<?= $i ?>" placeholder="<?= mk_h($t['invoice_line_desc'] ?? 'Description') ?>" value="<?= mk_h((string) ($el['desc'] ?? '')) ?>"></div>
      <div class="mk-field"><input class="mk-input" type="number" step="0.01" min="0" name="line_qty_<?= $i ?>" placeholder="<?= mk_h($t['invoice_line_qty'] ?? 'Qty') ?>" value="<?= mk_h((string) ($el['qty'] ?? ($i === 1 ? '1' : ''))) ?>"></div>
      <div class="mk-field"><input class="mk-input" type="number" step="0.01" min="0" name="line_unit_<?= $i ?>" placeholder="<?= mk_h($t['invoice_line_unit'] ?? 'Unit price') ?>" value="<?= mk_h((string) ($el['unit'] ?? '')) ?>"></div>
    </div>
    <?php endfor; ?>
    <button type="submit" class="mk-btn mk-btn--primary"><?= mk_h($t['btn_save'] ?? 'Save') ?></button>
    <a class="mk-btn mk-btn--ghost" href="<?= mk_h(mk_base_url('admin/invoices.php?view=' . rawurlencode((string) $viewing['id']))) ?>"><?= mk_h($t['btn_cancel'] ?? 'Cancel') ?></a>
  </form>
  <?php else: ?>
  <div class="mk-table-wrap">
    <table class="mk-table">
      <thead><tr><th><?= mk_h($t['invoice_line_desc'] ?? 'Description') ?></th><th><?= mk_h($t['invoice_line_qty'] ?? 'Qty') ?></th><th><?= mk_h($t['invoice_line_unit'] ?? 'Unit') ?></th><th><?= mk_h($t['invoice_line_total'] ?? 'Total') ?></th></tr></thead>
      <tbody>
      <?php foreach (mk_invoice_display_lines($viewing, $lang) as $line): ?>
        <tr><td><?= mk_h((string) $line['desc']) ?></td><td><?= mk_h((string) $line['qty']) ?></td><td><?= mk_h(mk_money((float) $line['unit'], (string) $viewing['currency'])) ?></td><td><?= mk_h(mk_money((float) $line['total'], (string) $viewing['currency'])) ?></td></tr>
      <?php endforeach; ?>
      <?php if ((float) ($viewing['tax_percent'] ?? 0) > 0): ?>
      <tr><td colspan="3" style="text-align:right;"><?= mk_h($t['invoice_subtotal'] ?? 'Subtotal') ?></td><td><?= mk_h(mk_money((float) $viewing['subtotal'], (string) $viewing['currency'])) ?></td></tr>
      <tr><td colspan="3" style="text-align:right;"><?= mk_h($t['invoice_tax'] ?? 'Tax') ?> (<?= mk_h(rtrim(rtrim(number_format((float) $viewing['tax_percent'], 2, '.', ''), '0'), '.')) ?>%)</td><td><?= mk_h(mk_money((float) $viewing['tax_amount'], (string) $viewing['currency'])) ?></td></tr>
      <?php endif; ?>
      <tr class="mk-invoice-total-row"><td colspan="3" style="text-align:right;"><?= mk_h($t['invoice_total'] ?? 'Total') ?></td><td><?= mk_h(mk_money((float) $viewing['total'], (string) $viewing['currency'])) ?></td></tr>
      </tbody>
    </table>
  </div>
  <p class="mk-hint"><?= mk_h($t['invoice_created_at'] ?? 'Created') ?>: <?= mk_h((string) $viewing['created_at']) ?> · <?= mk_h($t['invoice_due_at'] ?? 'Due') ?>: <?= mk_h((string) $viewing['due_at']) ?><?php if (!empty($viewing['paid_at'])): ?> · <?= mk_h($t['invoice_paid_at'] ?? 'Paid') ?>: <?= mk_h((string) $viewing['paid_at']) ?> (<?= mk_h((string) $viewing['payment_provider']) ?>)<?php endif; ?></p>
  <?php endif; ?>

  <?php if (!$editingInvoice): ?>
  <?php if (($viewing['status'] ?? '') === 'pending'): $payUrl = mk_invoice_pay_url($viewing); $qr = MkQr::svg($payUrl, 4); ?>
  <div class="mk-pay-qr-block">
    <?php if ($qr !== null): ?><?= $qr ?><?php endif; ?>
    <div class="mk-pay-link-col">
      <p class="mk-hint" style="margin:0 0 6px;"><?= mk_h($t['invoice_pay_link'] ?? 'Payment link') ?></p>
      <div class="mk-pay-link-row">
        <input id="mkPayLink" type="text" readonly value="<?= mk_h($payUrl) ?>" onclick="this.select()">
        <button type="button" class="mk-btn mk-btn--sm mk-btn--ghost" data-mk-copy="mkPayLink" data-mk-copied="<?= mk_h($t['btn_copied'] ?? 'Copied!') ?>"><?= mk_h($t['btn_copy_link'] ?? 'Copy') ?></button>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <div class="mk-table-actions">
    <a class="mk-btn mk-btn--ghost" href="<?= mk_h(mk_base_url('admin/invoice-pdf.php?id=' . rawurlencode((string) $viewing['id']))) ?>" target="_blank"><?= mk_h($t['btn_download_pdf'] ?? 'Download PDF') ?></a>
    <?php if (($viewing['status'] ?? '') === 'pending'): ?>
    <a class="mk-btn mk-btn--ghost" href="<?= mk_h(mk_base_url('admin/invoices.php?view=' . rawurlencode((string) $viewing['id']) . '&edit=1')) ?>"><?= mk_h($t['btn_edit'] ?? 'Edit') ?></a>
    <form method="post" style="display:inline;"><?= mk_csrf_field() ?><input type="hidden" name="action" value="mark_paid"><input type="hidden" name="id" value="<?= mk_h((string) $viewing['id']) ?>"><button type="submit" class="mk-btn mk-btn--primary"><?= mk_h($t['invoice_mark_paid'] ?? 'Mark as paid') ?></button></form>
    <form method="post" style="display:inline;"><?= mk_csrf_field() ?><input type="hidden" name="action" value="resend"><input type="hidden" name="id" value="<?= mk_h((string) $viewing['id']) ?>"><button type="submit" class="mk-btn mk-btn--ghost"><?= mk_h($t['invoice_send_email'] ?? 'Send by email') ?></button></form>
    <form method="post" style="display:inline;"><?= mk_csrf_field() ?><input type="hidden" name="action" value="cancel"><input type="hidden" name="id" value="<?= mk_h((string) $viewing['id']) ?>"><button type="submit" class="mk-btn mk-btn--danger"><?= mk_h($t['invoice_status_cancelled'] ?? 'Cancel') ?></button></form>
    <?php endif; ?>
    <form method="post" style="display:inline;" onsubmit="return confirm('<?= mk_h($t['confirm_delete'] ?? 'Are you sure?') ?>');"><?= mk_csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= mk_h((string) $viewing['id']) ?>"><button type="submit" class="mk-btn mk-btn--danger"><?= mk_h($t['btn_delete'] ?? 'Delete') ?></button></form>
    <a class="mk-btn mk-btn--ghost" href="<?= mk_h(mk_base_url('admin/invoices.php')) ?>"><?= mk_h($t['btn_back'] ?? 'Back') ?></a>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>

<div class="mk-card">
  <h2><?= mk_h($t['invoice_new_title'] ?? 'New invoice') ?></h2>
  <form method="post">
    <?= mk_csrf_field() ?>
    <input type="hidden" name="action" value="create">
    <div class="mk-form-row">
      <div class="mk-field">
        <label><?= mk_h($t['invoice_client'] ?? 'Client') ?></label>
        <select class="mk-select" name="client_id" required>
          <option value="">—</option>
          <?php foreach ($clients as $c): ?>
          <option value="<?= mk_h((string) $c['id']) ?>"><?= mk_h((string) $c['name']) ?> (<?= mk_h((string) $c['email']) ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mk-field">
        <label><?= mk_h($t['invoice_service'] ?? 'Service') ?></label>
        <select class="mk-select" name="service_id">
          <option value="">—</option>
          <?php foreach ($services as $s): ?>
          <option value="<?= mk_h((string) $s['id']) ?>"><?= mk_h($s['icon'] . ' ' . mk_service_name($s, $lang)) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mk-field">
        <label><?= mk_h($t['invoice_period'] ?? 'Period') ?></label>
        <select class="mk-select" name="period">
          <option value="m1"><?= mk_h($t['period_m1'] ?? '1 month') ?></option>
          <option value="m3"><?= mk_h($t['period_m3'] ?? '3 months') ?></option>
          <option value="m12"><?= mk_h($t['period_m12'] ?? '12 months') ?></option>
        </select>
      </div>
    </div>

    <p class="mk-hint" style="margin:14px 0 6px;"><?= mk_h($t['invoice_custom_title'] ?? 'Or custom line items') ?></p>
    <?php for ($i = 1; $i <= 4; $i++): ?>
    <div class="mk-form-row">
      <div class="mk-field"><input class="mk-input" name="line_desc_<?= $i ?>" placeholder="<?= mk_h($t['invoice_line_desc'] ?? 'Description') ?>"></div>
      <div class="mk-field"><input class="mk-input" type="number" step="0.01" min="0" name="line_qty_<?= $i ?>" placeholder="<?= mk_h($t['invoice_line_qty'] ?? 'Qty') ?>" value="1"></div>
      <div class="mk-field"><input class="mk-input" type="number" step="0.01" min="0" name="line_unit_<?= $i ?>" placeholder="<?= mk_h($t['invoice_line_unit'] ?? 'Unit price') ?>"></div>
    </div>
    <?php endfor; ?>

    <button type="submit" class="mk-btn mk-btn--primary"><?= mk_h($t['btn_create'] ?? 'Create') ?></button>
  </form>
</div>


<?php
$unpaidInvoices = array_values(array_filter($invoices, static fn(array $i): bool => ($i['status'] ?? '') === 'pending'));
$unpaidTotalsByCurrency = [];
foreach ($unpaidInvoices as $ui) {
    $cur = (string) ($ui['currency'] ?? MK_CURRENCY);
    $unpaidTotalsByCurrency[$cur] = ($unpaidTotalsByCurrency[$cur] ?? 0) + (float) $ui['total'];
}
?>
<div class="mk-card">
  <h2><?= mk_h($t['invoices_title'] ?? 'Invoices') ?></h2>
  <?php if ($unpaidInvoices !== []): ?>
  <p class="mk-hint" style="margin-bottom:14px;">
    <?= mk_h($t['invoice_unpaid_total'] ?? 'Unpaid total') ?>:
    <strong><?php foreach ($unpaidTotalsByCurrency as $cur => $sum): ?><?= mk_h(mk_money($sum, $cur)) ?> <?php endforeach; ?></strong>
    (<?= count($unpaidInvoices) ?>)
  </p>
  <?php endif; ?>
  <?php if ($invoices === []): ?>
    <p class="mk-hint"><?= mk_h($t['invoice_no_invoices'] ?? 'No invoices yet.') ?></p>
  <?php else: ?>
  <div class="mk-table-wrap">
    <table class="mk-table">
      <thead><tr>
        <th><?= mk_h($t['invoice_number'] ?? 'Number') ?></th>
        <th><?= mk_h($t['invoice_client'] ?? 'Client') ?></th>
        <th><?= mk_h($t['invoice_amount'] ?? 'Amount') ?></th>
        <th><?= mk_h($t['invoice_status'] ?? 'Status') ?></th>
        <th><?= mk_h($t['invoice_created_at'] ?? 'Created') ?></th>
        <th></th>
      </tr></thead>
      <tbody>
      <?php foreach ($invoices as $inv): $ic = $clientsById[(string) $inv['client_id']] ?? null; ?>
        <tr>
          <td><?= mk_h((string) $inv['number']) ?></td>
          <td><?= mk_h($ic['name'] ?? '—') ?></td>
          <td><?= mk_h(mk_money((float) $inv['total'], (string) ($inv['currency'] ?? MK_CURRENCY))) ?></td>
          <td><span class="<?= mk_invoice_status_class($inv) ?>"><?= mk_h(mk_invoice_status_label($inv, $t)) ?></span></td>
          <td><?= mk_h((string) $inv['created_at']) ?></td>
          <td><a class="mk-btn mk-btn--sm mk-btn--ghost" href="<?= mk_h(mk_base_url('admin/invoices.php?view=' . rawurlencode((string) $inv['id']))) ?>"><?= mk_h($t['btn_view'] ?? 'View') ?></a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
<?php mk_layout_end(); ?>
