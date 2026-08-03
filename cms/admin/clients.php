<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

[$t, $lang] = mk_boot('admin');
if (mk_admin_bootstrap_needed()) {
    mk_redirect(mk_base_url('admin/setup.php'));
}
mk_admin_require();

if (mk_is_post()) {
    mk_csrf_require();
    $action = mk_post_str('action');
    if ($action === 'save') {
        $editId = mk_post_str('id') ?: null;
        $emailInput = strtolower(mk_post_str('email'));
        $existing = $emailInput !== '' ? mk_client_by_email($emailInput) : null;
        if ($existing !== null && (string) $existing['id'] !== (string) $editId) {
            mk_flash_set('error', $t['login_error'] ?? 'A client with this email already exists.');
            mk_redirect(mk_base_url('admin/clients.php' . ($editId !== null ? '?edit=' . rawurlencode($editId) : '')));
        }
        [$row, $plainPassword] = mk_client_save($_POST, $editId);
        if ($editId === null) {
            $welcomeLang = (string) ($row['lang'] ?? 'uk');
            $welcomeT = mk_load_lang($welcomeLang);
            mk_mail_send(
                (string) $row['email'],
                $welcomeT['client_login_link'] ?? 'Your login',
                mk_mail_template(
                    $welcomeT['client_login_link'] ?? 'Your login',
                    '<p style="font-size:15px;line-height:1.6;color:#333a4d;">'
                        . mk_h($welcomeT['login_email'] ?? 'Email') . ': <strong>' . mk_h((string) $row['email']) . '</strong><br>'
                        . mk_h($welcomeT['login_password'] ?? 'Password') . ': <strong>' . mk_h((string) $plainPassword) . '</strong></p>'
                        . mk_mail_button(mk_base_url('login.php'), $welcomeT['client_login_title'] ?? 'Log in'),
                    $welcomeT,
                    $welcomeLang
                )
            );
            mk_flash_set('ok', ($t['client_created_ok'] ?? 'Client created.') . ' ' . ($t['login_password'] ?? 'Password') . ': ' . $plainPassword);
        } else {
            mk_flash_set('ok', $t['client_updated_ok'] ?? 'Client updated.');
        }
        mk_redirect(mk_base_url('admin/clients.php'));
    }
    if ($action === 'delete') {
        mk_client_delete(mk_post_str('id'));
        mk_flash_set('ok', $t['client_deleted_ok'] ?? 'Deleted.');
        mk_redirect(mk_base_url('admin/clients.php'));
    }
}

$editId = mk_get_str('edit');
$editing = $editId !== '' ? mk_client_find($editId) : null;
$clients = mk_clients_all();

mk_layout_start($t['clients_title'] ?? 'Clients', $t, $lang, 'admin', 'clients');
?>
<div class="mk-card">
  <h2><?= mk_h($editing !== null ? ($t['client_edit_title'] ?? 'Edit client') : ($t['client_new_title'] ?? 'New client')) ?></h2>
  <form method="post">
    <?= mk_csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <?php if ($editing !== null): ?><input type="hidden" name="id" value="<?= mk_h((string) $editing['id']) ?>"><?php endif; ?>
    <div class="mk-form-row">
      <div class="mk-field"><label><?= mk_h($t['client_name'] ?? 'Name') ?></label><input class="mk-input" name="name" value="<?= mk_h($editing['name'] ?? '') ?>" required></div>
      <div class="mk-field"><label><?= mk_h($t['client_email'] ?? 'Email') ?></label><input class="mk-input" type="email" name="email" value="<?= mk_h($editing['email'] ?? '') ?>" required></div>
      <div class="mk-field"><label><?= mk_h($t['client_phone'] ?? 'Phone') ?></label><input class="mk-input" name="phone" value="<?= mk_h($editing['phone'] ?? '') ?>"></div>
    </div>
    <div class="mk-form-row">
      <div class="mk-field"><label><?= mk_h($t['client_company'] ?? 'Company') ?></label><input class="mk-input" name="company" value="<?= mk_h($editing['company'] ?? '') ?>"></div>
      <div class="mk-field"><label><?= mk_h($t['client_site_url'] ?? 'Primary site') ?></label><input class="mk-input" name="site_url" placeholder="example.com" value="<?= mk_h($editing['site_url'] ?? '') ?>"></div>
    </div>
    <div class="mk-form-row">
      <div class="mk-field">
        <label><?= mk_h($t['client_lang'] ?? 'Language') ?></label>
        <select class="mk-select" name="lang">
          <?php foreach (mk_langs() as $code => $meta): ?>
          <option value="<?= mk_h($code) ?>" <?= ($editing['lang'] ?? 'uk') === $code ? 'selected' : '' ?>><?= mk_h($meta['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mk-field">
        <label><?= mk_h($t['client_status'] ?? 'Status') ?></label>
        <select class="mk-select" name="status">
          <option value="active" <?= ($editing['status'] ?? 'active') === 'active' ? 'selected' : '' ?>><?= mk_h($t['client_status_active'] ?? 'Active') ?></option>
          <option value="paused" <?= ($editing['status'] ?? '') === 'paused' ? 'selected' : '' ?>><?= mk_h($t['client_status_paused'] ?? 'Paused') ?></option>
        </select>
      </div>
    </div>
    <div class="mk-field">
      <label><?= mk_h($editing !== null ? ($t['client_password_new'] ?? 'New password') : ($t['login_password'] ?? 'Password')) ?></label>
      <input class="mk-input" name="password" placeholder="<?= $editing !== null ? mk_h($t['client_password_hint'] ?? '') : '' ?>">
      <?php if ($editing !== null): ?><p class="mk-hint"><?= mk_h($t['client_password_hint'] ?? '') ?></p><?php endif; ?>
    </div>
    <div class="mk-field">
      <label><?= mk_h($t['client_notes'] ?? 'Notes') ?></label>
      <textarea class="mk-textarea" name="notes" rows="2"><?= mk_h($editing['notes'] ?? '') ?></textarea>
    </div>
    <button type="submit" class="mk-btn mk-btn--primary"><?= mk_h($t['btn_save'] ?? 'Save') ?></button>
    <?php if ($editing !== null): ?><a class="mk-btn mk-btn--ghost" href="<?= mk_h(mk_base_url('admin/clients.php')) ?>"><?= mk_h($t['btn_cancel'] ?? 'Cancel') ?></a><?php endif; ?>
  </form>
</div>

<?php if ($editing !== null): ?>
<div class="mk-card">
  <h2><?= mk_h($t['nav_invoices'] ?? 'Invoices') ?></h2>
  <?php $clientInvoices = mk_invoices_for_client((string) $editing['id']); ?>
  <?php if ($clientInvoices === []): ?>
    <p class="mk-hint"><?= mk_h($t['invoice_no_invoices'] ?? 'No invoices yet.') ?></p>
  <?php else: ?>
  <div class="mk-table-wrap">
    <table class="mk-table">
      <thead><tr><th><?= mk_h($t['invoice_number'] ?? 'Number') ?></th><th><?= mk_h($t['invoice_amount'] ?? 'Amount') ?></th><th><?= mk_h($t['invoice_status'] ?? 'Status') ?></th><th></th></tr></thead>
      <tbody>
      <?php foreach ($clientInvoices as $inv): ?>
        <tr>
          <td><?= mk_h((string) $inv['number']) ?></td>
          <td><?= mk_h(mk_money((float) $inv['total'], (string) ($inv['currency'] ?? MK_CURRENCY))) ?></td>
          <td><span class="<?= mk_invoice_status_class($inv) ?>"><?= mk_h(mk_invoice_status_label($inv, $t)) ?></span></td>
          <td><a class="mk-btn mk-btn--sm mk-btn--ghost" href="<?= mk_h(mk_base_url('admin/invoices.php?view=' . rawurlencode((string) $inv['id']))) ?>"><?= mk_h($t['btn_view'] ?? 'View') ?></a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>

<div class="mk-card">
  <h2><?= mk_h($t['clients_title'] ?? 'Clients') ?></h2>
  <?php if ($clients === []): ?>
    <p class="mk-hint"><?= mk_h($t['client_no_clients'] ?? 'No clients yet.') ?></p>
  <?php else: ?>
  <div class="mk-table-wrap">
    <table class="mk-table">
      <thead><tr>
        <th><?= mk_h($t['client_name'] ?? 'Name') ?></th>
        <th><?= mk_h($t['client_email'] ?? 'Email') ?></th>
        <th><?= mk_h($t['client_status'] ?? 'Status') ?></th>
        <th></th>
      </tr></thead>
      <tbody>
      <?php foreach ($clients as $c): ?>
        <tr>
          <td><?= mk_h((string) $c['name']) ?></td>
          <td><?= mk_h((string) $c['email']) ?></td>
          <td><?php if (($c['status'] ?? '') === 'active'): ?><span class="mk-badge mk-badge--ok"><?= mk_h($t['client_status_active'] ?? 'Active') ?></span><?php else: ?><span class="mk-badge mk-badge--muted"><?= mk_h($t['client_status_paused'] ?? 'Paused') ?></span><?php endif; ?></td>
          <td class="mk-table-actions">
            <a class="mk-btn mk-btn--sm mk-btn--ghost" href="<?= mk_h(mk_base_url('admin/clients.php?edit=' . rawurlencode((string) $c['id']))) ?>"><?= mk_h($t['btn_edit'] ?? 'Edit') ?></a>
            <form method="post" onsubmit="return confirm('<?= mk_h($t['confirm_delete'] ?? 'Are you sure?') ?>');" style="display:inline;">
              <?= mk_csrf_field() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= mk_h((string) $c['id']) ?>">
              <button type="submit" class="mk-btn mk-btn--sm mk-btn--danger"><?= mk_h($t['btn_delete'] ?? 'Delete') ?></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
<?php mk_layout_end(); ?>
