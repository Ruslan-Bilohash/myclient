<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

[$t, $lang] = mk_boot('admin');
if (mk_admin_bootstrap_needed()) {
    mk_redirect(mk_base_url('admin/setup.php'));
}
$admin = mk_admin_require();

$clients = mk_clients_all();
$invoices = mk_invoices_all();
$pending = array_values(array_filter($invoices, static fn(array $i): bool => ($i['status'] ?? '') === 'pending'));
$dueSoon = $pending;
usort($dueSoon, static fn(array $a, array $b): int => strcmp((string) ($a['due_at'] ?? ''), (string) ($b['due_at'] ?? '')));
$dueSoon = array_slice($dueSoon, 0, 8);
$today = date('Y-m-d');
$monthPrefix = date('Y-m');
$paidThisMonth = array_values(array_filter($invoices, static fn(array $i): bool =>
    ($i['status'] ?? '') === 'paid' && str_starts_with((string) ($i['paid_at'] ?? ''), $monthPrefix)
));
$revenueThisMonth = array_sum(array_map(static fn(array $i): float => (float) ($i['total'] ?? 0), $paidThisMonth));

$clientsById = [];
foreach ($clients as $c) {
    $clientsById[(string) $c['id']] = $c;
}

mk_layout_start($t['admin_dash_title'] ?? 'Dashboard', $t, $lang, 'admin', 'dashboard');
?>
<div class="mk-grid mk-grid--stats">
  <div class="mk-card mk-stat">
    <div class="mk-stat-value"><?= count($clients) ?></div>
    <div class="mk-stat-label"><?= mk_h($t['admin_dash_clients_total'] ?? 'Total clients') ?></div>
  </div>
  <div class="mk-card mk-stat">
    <div class="mk-stat-value"><?= count($pending) ?></div>
    <div class="mk-stat-label"><?= mk_h($t['admin_dash_invoices_pending'] ?? 'Unpaid invoices') ?></div>
  </div>
  <div class="mk-card mk-stat">
    <div class="mk-stat-value"><?= count($paidThisMonth) ?></div>
    <div class="mk-stat-label"><?= mk_h($t['admin_dash_invoices_paid_month'] ?? 'Paid this month') ?></div>
  </div>
  <div class="mk-card mk-stat">
    <div class="mk-stat-value"><?= mk_h(mk_money($revenueThisMonth)) ?></div>
    <div class="mk-stat-label"><?= mk_h($t['admin_dash_revenue_month'] ?? 'Revenue this month') ?></div>
  </div>
</div>


<div class="mk-card">
  <h2><?= mk_h($t['admin_dash_due_soon'] ?? 'Invoices due soon') ?></h2>
  <?php if ($dueSoon === []): ?>
    <p class="mk-hint"><?= mk_h($t['admin_dash_no_invoices'] ?? 'No invoices yet') ?></p>
  <?php else: ?>
  <div class="mk-table-wrap">
    <table class="mk-table">
      <thead><tr>
        <th><?= mk_h($t['invoice_number'] ?? 'Number') ?></th>
        <th><?= mk_h($t['invoice_client'] ?? 'Client') ?></th>
        <th><?= mk_h($t['invoice_amount'] ?? 'Amount') ?></th>
        <th><?= mk_h($t['invoice_due_at'] ?? 'Due date') ?></th>
        <th></th>
      </tr></thead>
      <tbody>
      <?php foreach ($dueSoon as $inv): $c = $clientsById[(string) $inv['client_id']] ?? null; $overdue = (string) ($inv['due_at'] ?? '') < $today; ?>
        <tr>
          <td><a href="<?= mk_h(mk_base_url('admin/invoices.php?view=' . rawurlencode((string) $inv['id']))) ?>"><?= mk_h((string) $inv['number']) ?></a></td>
          <td><?= mk_h($c['name'] ?? '—') ?></td>
          <td><?= mk_h(mk_money((float) $inv['total'], (string) ($inv['currency'] ?? MK_CURRENCY))) ?></td>
          <td>
            <?php if ($overdue): ?><span class="mk-badge mk-badge--warn"><?= mk_h($t['admin_dash_overdue'] ?? 'Overdue') ?> — <?php endif; ?><?= mk_h((string) $inv['due_at']) ?><?php if ($overdue): ?></span><?php endif; ?>
          </td>
          <td><a class="mk-btn mk-btn--sm mk-btn--ghost" href="<?= mk_h(mk_base_url('admin/invoices.php?view=' . rawurlencode((string) $inv['id']))) ?>"><?= mk_h($t['btn_view'] ?? 'View') ?></a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<div class="mk-card">
  <h2><?= mk_h($t['admin_dash_recent_invoices'] ?? 'Recent invoices') ?></h2>
  <?php if ($invoices === []): ?>
    <p class="mk-hint"><?= mk_h($t['admin_dash_no_invoices'] ?? 'No invoices yet') ?></p>
  <?php else: ?>
  <div class="mk-table-wrap">
    <table class="mk-table">
      <thead><tr>
        <th><?= mk_h($t['invoice_number'] ?? 'Number') ?></th>
        <th><?= mk_h($t['invoice_client'] ?? 'Client') ?></th>
        <th><?= mk_h($t['invoice_amount'] ?? 'Amount') ?></th>
        <th><?= mk_h($t['invoice_status'] ?? 'Status') ?></th>
        <th><?= mk_h($t['invoice_created_at'] ?? 'Created') ?></th>
      </tr></thead>
      <tbody>
      <?php foreach (array_slice($invoices, 0, 10) as $inv): $c = $clientsById[(string) $inv['client_id']] ?? null; ?>
        <tr>
          <td><a href="<?= mk_h(mk_base_url('admin/invoices.php?view=' . rawurlencode((string) $inv['id']))) ?>"><?= mk_h((string) $inv['number']) ?></a></td>
          <td><?= mk_h($c['name'] ?? '—') ?></td>
          <td><?= mk_h(mk_money((float) $inv['total'], (string) ($inv['currency'] ?? MK_CURRENCY))) ?></td>
          <td><span class="<?= mk_invoice_status_class($inv) ?>"><?= mk_h(mk_invoice_status_label($inv, $t)) ?></span></td>
          <td><?= mk_h((string) $inv['created_at']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
<?php mk_layout_end(); ?>
