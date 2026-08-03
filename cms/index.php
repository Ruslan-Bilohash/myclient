<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

[$t, $lang] = mk_boot('client');
$client = mk_client_require();

$activeServices = mk_client_active_services((string) $client['id']);
$invoices = mk_invoices_for_client((string) $client['id']);

mk_layout_start($t['cab_dashboard_title'] ?? 'My cabinet', $t, $lang, 'client', 'dashboard');
?>
<div class="mk-card">
  <h2><?= mk_h(($t['cab_welcome'] ?? 'Welcome') . ', ' . (string) $client['name']) ?> 👋</h2>
  <?php
  $greetings = is_array($t['cab_greetings'] ?? null) && $t['cab_greetings'] !== [] ? $t['cab_greetings'] : [$t['cab_welcome_subtitle'] ?? 'Your sites are all set.'];
  $todayPhrase = mk_today_phrase($t, $lang);
  $subtitle = ($todayPhrase !== '' ? $todayPhrase . '. ' : '') . $greetings[array_rand($greetings)];
  ?>
  <p class="mk-hint" style="margin:6px 0 0;"><?= mk_h($subtitle) ?></p>
  <?php if (!empty($client['site_url'])): $siteHref = preg_match('#^https?://#i', (string) $client['site_url']) ? (string) $client['site_url'] : 'https://' . $client['site_url']; ?>
  <p class="mk-hint" style="margin:4px 0 0;"><?= mk_h($t['cab_your_site'] ?? 'Your site') ?>: <a href="<?= mk_h($siteHref) ?>" target="_blank" rel="noopener"><?= mk_h((string) $client['site_url']) ?></a></p>
  <?php endif; ?>
</div>

<div class="mk-card">
  <h2><?= mk_h($t['cab_active_services'] ?? 'Your services') ?></h2>
  <?php if ($activeServices === []): ?>
    <p class="mk-hint"><?= mk_h($t['cab_no_services'] ?? 'No active services yet.') ?></p>
  <?php else: ?>
    <?php foreach ($activeServices as $as): $service = mk_service_find($as['service_id']); if ($service === null) { continue; } ?>
    <div class="mk-service-chip">
      <span class="mk-service-chip-icon"><?= mk_h((string) $service['icon']) ?></span>
      <div>
        <div class="mk-service-chip-name"><?= mk_h(mk_service_name($service, $lang)) ?></div>
        <div class="mk-service-chip-meta"><?= mk_h(($t['invoice_due_at'] ?? 'Until') . ': ' . $as['expires']->format('Y-m-d')) ?></div>
      </div>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<div class="mk-card">
  <h2><?= mk_h($t['cab_recent_invoices'] ?? 'Recent invoices') ?></h2>
  <?php if ($invoices === []): ?>
    <p class="mk-hint"><?= mk_h($t['cab_no_invoices'] ?? 'No invoices yet.') ?></p>
  <?php else: ?>
  <div class="mk-table-wrap">
    <table class="mk-table">
      <thead><tr>
        <th><?= mk_h($t['invoice_number'] ?? 'Number') ?></th>
        <th><?= mk_h($t['invoice_for'] ?? 'For') ?></th>
        <th><?= mk_h($t['invoice_amount'] ?? 'Amount') ?></th>
        <th><?= mk_h($t['invoice_status'] ?? 'Status') ?></th>
        <th></th>
      </tr></thead>
      <tbody>
      <?php foreach (array_slice($invoices, 0, 5) as $inv): ?>
        <tr>
          <td><?= mk_h((string) $inv['number']) ?></td>
          <td><?= mk_h(mk_invoice_line_summary($inv, $lang)) ?></td>
          <td><?= mk_money_with_fx((float) $inv['total'], $lang, $t) ?></td>
          <td><span class="<?= mk_invoice_status_class($inv) ?>"><?= mk_h(mk_invoice_status_label($inv, $t)) ?></span></td>
          <td>
            <?php if (($inv['status'] ?? '') === 'pending'): ?>
            <a class="mk-btn mk-btn--sm mk-btn--primary" href="<?= mk_h(mk_base_url('invoice-pay.php?id=' . rawurlencode((string) $inv['id']))) ?>"><?= mk_h($t['btn_pay'] ?? 'Pay') ?></a>
            <?php else: ?>
            <a class="mk-btn mk-btn--sm mk-btn--ghost" href="<?= mk_h(mk_base_url('invoice-view.php?id=' . rawurlencode((string) $inv['id']))) ?>"><?= mk_h($t['btn_view'] ?? 'View') ?></a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <p style="margin-top:12px;"><a href="<?= mk_h(mk_base_url('invoices.php')) ?>"><?= mk_h($t['cab_view_all_invoices'] ?? 'All invoices') ?> →</a></p>
  <?php endif; ?>
</div>
<?php mk_layout_end(); ?>
