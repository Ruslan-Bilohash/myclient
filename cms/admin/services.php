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
        mk_service_save($_POST, $editId);
        mk_flash_set('ok', $t['service_saved_ok'] ?? 'Saved.');
        mk_redirect(mk_base_url('admin/services.php'));
    }
    if ($action === 'delete') {
        mk_service_delete(mk_post_str('id'));
        mk_flash_set('ok', $t['service_deleted_ok'] ?? 'Deleted.');
        mk_redirect(mk_base_url('admin/services.php'));
    }
}

$editId = mk_get_str('edit');
$editing = $editId !== '' ? mk_service_find($editId) : null;
$editName = is_array($editing['name'] ?? null) ? $editing['name'] : [];
$editDesc = is_array($editing['desc'] ?? null) ? $editing['desc'] : [];
$services = mk_services_all();

mk_layout_start($t['services_title'] ?? 'Services', $t, $lang, 'admin', 'services');
?>
<div class="mk-card">
  <h2><?= mk_h($editing !== null ? ($t['service_edit_title'] ?? 'Edit service') : ($t['service_new_title'] ?? 'New service')) ?></h2>
  <form method="post">
    <?= mk_csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <?php if ($editing !== null): ?><input type="hidden" name="id" value="<?= mk_h((string) $editing['id']) ?>"><?php endif; ?>
    <div class="mk-form-row">
      <div class="mk-field"><label><?= mk_h($t['service_name'] ?? 'Name') ?> (UA)</label><input class="mk-input" name="name_uk" value="<?= mk_h($editName['uk'] ?? '') ?>" required></div>
      <div class="mk-field"><label><?= mk_h($t['service_name'] ?? 'Name') ?> (NO)</label><input class="mk-input" name="name_no" value="<?= mk_h($editName['no'] ?? '') ?>"></div>
      <div class="mk-field"><label><?= mk_h($t['service_name'] ?? 'Name') ?> (EN)</label><input class="mk-input" name="name_en" value="<?= mk_h($editName['en'] ?? '') ?>"></div>
    </div>
    <div class="mk-form-row">
      <div class="mk-field"><label><?= mk_h($t['service_desc'] ?? 'Description') ?> (UA)</label><input class="mk-input" name="desc_uk" value="<?= mk_h($editDesc['uk'] ?? '') ?>"></div>
      <div class="mk-field"><label><?= mk_h($t['service_desc'] ?? 'Description') ?> (NO)</label><input class="mk-input" name="desc_no" value="<?= mk_h($editDesc['no'] ?? '') ?>"></div>
      <div class="mk-field"><label><?= mk_h($t['service_desc'] ?? 'Description') ?> (EN)</label><input class="mk-input" name="desc_en" value="<?= mk_h($editDesc['en'] ?? '') ?>"></div>
    </div>
    <div class="mk-form-row">
      <div class="mk-field">
        <label><?= mk_h($t['service_icon'] ?? 'Icon') ?></label>
        <div style="display:flex;gap:8px;align-items:center;">
          <input class="mk-input" id="mkIconInput" name="icon" value="<?= mk_h($editing['icon'] ?? '💼') ?>" maxlength="4" style="max-width:80px;font-size:18px;text-align:center;">
          <button type="button" class="mk-btn mk-btn--ghost" id="mkIconPickBtn"><?= mk_h($t['service_icon_pick'] ?? 'Choose icon') ?></button>
        </div>
      </div>
      <div class="mk-field">
        <label><?= mk_h($t['service_billing_mode'] ?? 'Billing type') ?></label>
        <?php $billingMode = $editing !== null ? mk_service_billing_mode($editing) : 'flexible'; ?>
        <select class="mk-select" name="billing_mode" id="mkBillingMode">
          <option value="flexible" <?= $billingMode === 'flexible' ? 'selected' : '' ?>><?= mk_h($t['billing_mode_flexible'] ?? 'Flexible (1/3/12 months)') ?></option>
          <option value="month" <?= $billingMode === 'month' ? 'selected' : '' ?>><?= mk_h($t['billing_mode_month'] ?? 'Monthly (single price)') ?></option>
          <option value="year" <?= $billingMode === 'year' ? 'selected' : '' ?>><?= mk_h($t['billing_mode_year'] ?? 'Yearly (single price)') ?></option>
        </select>
      </div>
    </div>
    <div class="mk-form-row">
      <div class="mk-field" data-mk-price-for="flexible month"><label><?= mk_h(($t['service_price_m1'] ?? 'Price / 1 mo') . ' (' . MK_CURRENCY . ')') ?></label><input class="mk-input" type="number" step="0.01" min="0" name="price_m1" value="<?= mk_h((string) ($editing['price_m1'] ?? '0')) ?>"></div>
      <div class="mk-field" data-mk-price-for="flexible"><label><?= mk_h(($t['service_price_m3'] ?? 'Price / 3 mo') . ' (' . MK_CURRENCY . ')') ?></label><input class="mk-input" type="number" step="0.01" min="0" name="price_m3" value="<?= mk_h((string) ($editing['price_m3'] ?? '0')) ?>"></div>
      <div class="mk-field" data-mk-price-for="flexible year"><label><?= mk_h(($t['service_price_m12'] ?? 'Price / 12 mo') . ' (' . MK_CURRENCY . ')') ?></label><input class="mk-input" type="number" step="0.01" min="0" name="price_m12" value="<?= mk_h((string) ($editing['price_m12'] ?? '0')) ?>"></div>
    </div>
    <div class="mk-form-row">
      <div class="mk-field"><label>Sort</label><input class="mk-input" type="number" name="sort" value="<?= mk_h((string) ($editing['sort'] ?? '0')) ?>"></div>
      <div class="mk-field" style="align-self:end;">
        <label class="mk-checkbox"><input type="checkbox" name="active" value="1" <?= !empty($editing['active']) || $editing === null ? 'checked' : '' ?>> <?= mk_h($t['service_active'] ?? 'Active') ?></label>
      </div>
    </div>
    <button type="submit" class="mk-btn mk-btn--primary"><?= mk_h($t['btn_save'] ?? 'Save') ?></button>
    <?php if ($editing !== null): ?><a class="mk-btn mk-btn--ghost" href="<?= mk_h(mk_base_url('admin/services.php')) ?>"><?= mk_h($t['btn_cancel'] ?? 'Cancel') ?></a><?php endif; ?>
  </form>
</div>
<script>
(function(){
  var select = document.getElementById('mkBillingMode');
  if (!select) return;
  function apply(){
    var mode = select.value;
    document.querySelectorAll('[data-mk-price-for]').forEach(function(field){
      var modes = field.getAttribute('data-mk-price-for').split(' ');
      field.style.display = modes.indexOf(mode) !== -1 ? '' : 'none';
    });
  }
  select.addEventListener('change', apply);
  apply();
})();
</script>

<div class="mk-modal-overlay mk-no-print" id="mkIconModal" hidden>
  <div class="mk-modal">
    <div class="mk-modal-head">
      <h3><?= mk_h($t['service_icon_pick'] ?? 'Choose icon') ?></h3>
      <button type="button" class="mk-modal-close" id="mkIconModalClose" aria-label="Close">&times;</button>
    </div>
    <div class="mk-icon-grid" id="mkIconGrid">
      <?php foreach ([
          '💼','🖥️','🌐','📦','🔒','🔐','📧','✉️','💳','🅿️','🚀','⚙️','📊','📈','📉','📱','💾','🔧','🛠️','🎯',
          '🛡️','📞','☎️','🏢','🏠','🖧','🗂️','📁','📂','📋','🗄️','💻','⌨️','🖱️','🖨️','📡','☁️','🔗','🧾','💰',
          '💵','💶','💷','🪙','🧮','📅','⏰','⏱️','✅','⭐','🔔','🔑','🧩','🧰','🔍','📌','🚩','🎨','🧪','🧑‍💻',
          '🛒','🚚','📮','🌍','🗺️','🧭','🏷️','📞','🖇️','📎',
      ] as $emoji): ?>
      <button type="button" class="mk-icon-opt" data-icon="<?= mk_h($emoji) ?>"><?= mk_h($emoji) ?></button>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<script>
(function(){
  var input = document.getElementById('mkIconInput');
  var btn = document.getElementById('mkIconPickBtn');
  var modal = document.getElementById('mkIconModal');
  var closeBtn = document.getElementById('mkIconModalClose');
  var grid = document.getElementById('mkIconGrid');
  if (!input || !btn || !modal) return;

  function open(){ modal.hidden = false; }
  function close(){ modal.hidden = true; }

  btn.addEventListener('click', open);
  closeBtn.addEventListener('click', close);
  modal.addEventListener('click', function(e){ if (e.target === modal) close(); });
  document.addEventListener('keydown', function(e){ if (e.key === 'Escape' && !modal.hidden) close(); });
  grid.addEventListener('click', function(e){
    var opt = e.target.closest('.mk-icon-opt');
    if (!opt) return;
    input.value = opt.getAttribute('data-icon');
    close();
  });
})();
</script>

<div class="mk-card">
  <h2><?= mk_h($t['services_title'] ?? 'Services') ?></h2>
  <?php if ($services === []): ?>
    <p class="mk-hint"><?= mk_h($t['service_no_services'] ?? 'No services yet.') ?></p>
  <?php else: ?>
  <div class="mk-table-wrap">
    <table class="mk-table">
      <thead><tr>
        <th></th><th><?= mk_h($t['service_name'] ?? 'Name') ?></th>
        <th><?= mk_h($t['invoice_amount'] ?? 'Price') ?></th>
        <th><?= mk_h($t['service_active'] ?? 'Active') ?></th>
        <th></th>
      </tr></thead>
      <tbody>
      <?php foreach ($services as $s): ?>
        <tr>
          <td style="font-size:20px;"><?= mk_h((string) $s['icon']) ?></td>
          <td><?= mk_h(mk_service_name($s, $lang)) ?></td>
          <td><?= mk_h(mk_service_price_summary($s, $t)) ?></td>
          <td><?php if (!empty($s['active'])): ?><span class="mk-badge mk-badge--ok">✓</span><?php else: ?><span class="mk-badge mk-badge--muted">—</span><?php endif; ?></td>
          <td class="mk-table-actions">
            <a class="mk-btn mk-btn--sm mk-btn--ghost" href="<?= mk_h(mk_base_url('admin/services.php?edit=' . rawurlencode((string) $s['id']))) ?>"><?= mk_h($t['btn_edit'] ?? 'Edit') ?></a>
            <form method="post" onsubmit="return confirm('<?= mk_h($t['confirm_delete'] ?? 'Are you sure?') ?>');" style="display:inline;">
              <?= mk_csrf_field() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= mk_h((string) $s['id']) ?>">
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
