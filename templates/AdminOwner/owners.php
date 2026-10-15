<?php
$this->assign('title', 'Owners');
$this->assign('portal_title', 'Owners');
$this->assign('page_actions', '<span style="font-size:13px;color:var(--p-text-2)">Verification queue</span>');
$list = $users;
if (!empty($financial['data']) && is_array($financial['data'])) $list = $financial['data'];
elseif (!empty($financial) && isset($financial[0])) $list = $financial;
if (empty($list)) $list = $users;
?>
<div class="p-card mb-3">
  <?= $this->Form->create(null, ['type' => 'get', 'style' => 'display:flex;gap:8px;flex-wrap:wrap;align-items:end']) ?>
    <div style="flex:1;min-width:200px">
      <label style="font-size:12px;font-weight:600;color:var(--p-text-2)">Search</label>
      <input name="search" value="<?= h($search ?? '') ?>" placeholder="Name, email" class="form-control" style="min-height:40px">
    </div>
    <div style="min-width:160px">
      <label style="font-size:12px;font-weight:600;color:var(--p-text-2)">Status</label>
      <select name="status" class="form-select" style="min-height:40px">
        <option value="">All status</option>
        <option value="pending" <?= ($status ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
        <option value="approved" <?= ($status ?? '') === 'approved' ? 'selected' : '' ?>>Approved</option>
        <option value="rejected" <?= ($status ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
        <option value="suspended" <?= ($status ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
      </select>
    </div>
    <button class="p-btn" type="submit">Filter</button>
    <a href="<?= $this->Url->build('/admin/owners') ?>" class="p-btn ghost">Clear</a>
  <?= $this->Form->end() ?>
</div>

<?php if (empty($list)): ?>
  <div class="p-card"><div class="p-empty">No owners found.</div></div>
<?php else: ?>
  <div class="p-table-wrap">
    <div class="table-responsive"><table class="p-table">
      <thead><tr><th>Owner</th><th>Contact</th><th>Status</th><th style="text-align:right">Gross / Net</th><th>Action</th></tr></thead>
      <tbody>
        <?php foreach ($list as $o):
          $oid = $o['id'] ?? $o['owner_id'] ?? $o['user_id'] ?? null;
          $name = $o['name'] ?? $o['owner_name'] ?? '—';
          $email = $o['email'] ?? $o['owner_email'] ?? '';
          $st = strtolower((string)($o['status'] ?? $o['account_status'] ?? $o['verification_status'] ?? 'pending'));
          // Account status Active means the owner is approved (backend sets
          // Active on approval), so it renders the same green Approved badge.
          $isApproved = in_array($st, ['active', 'approved'], true);
          $isPending = in_array($st, ['pending', 'pending verification'], true);
          $badge = $isApproved ? 'green' : ($st === 'rejected' || $st === 'suspended' ? 'red' : 'yellow');
          $badgeLabel = $isApproved ? 'approved' : ($isPending ? 'pending' : $st);
          // Backend financial-summary keys are gross_booking_value /
          // platform_commission / owner_earnings. The 0.9 fallback invented a
          // split when the backend omitted the row — it never omits it.
          $gross = $o['gross_revenue'] ?? $o['gross_booking_value'] ?? $o['total_earnings'] ?? $o['gross'] ?? 0;
          $fee = $o['platform_commission'] ?? 0;
          $net = $o['net_earnings'] ?? $o['owner_earnings'] ?? ($gross - $fee);
        ?>
        <tr>
          <td><div style="font-weight:600"><?= h($name) ?> <span style="font-size:11px;font-weight:400;color:var(--p-text-2)">#<?= h($oid) ?></span></div><div style="font-size:12px;color:var(--p-text-2)"><?= h($email) ?></div></td>
          <td style="font-size:13px"><?= h($o['phone'] ?? $o['phone_number'] ?? '') ?></td>
          <td><span class="p-badge <?= $badge ?>"><?= h($badgeLabel) ?></span></td>
          <td style="text-align:right"><div style="font-weight:600">TSh <?= number_format((float)$gross) ?></div><div style="font-size:11px;color:var(--p-text-2)">Net TSh <?= number_format((float)$net) ?></div></td>
          <td>
            <?php if (empty($oid)): ?>
            <span class="p-badge yellow">pending</span>
            <div style="font-size:11px;color:var(--p-text-2);margin-top:4px">Record has no id — cannot verify. Backend row is missing its key.</div>
            <?php else: ?>
            <div style="display:flex;gap:6px;flex-wrap:wrap">
              <button type="button" class="p-btn ghost" style="min-height:32px;font-size:12px" onclick="openOwnerDocs('<?= h($oid) ?>')">View docs</button>
              <?= $this->Form->create(null, ['url' => ['controller' => 'AdminOwner', 'action' => 'verify', 'owner', $oid], 'style' => 'display:inline', 'data-api' => 'POST /admin/verification/owner/' . $oid, 'data-api-build' => 'verify', 'data-api-omit-empty' => 'reason', 'data-api-ok' => 'Owner approved.', 'data-opt' => 'patch', 'data-opt-badge' => '.p-badge', 'data-opt-badgesrc' => 'status', 'data-opt-badgemap' => 'verify', 'data-opt-badgetext' => 'lower', 'data-opt-bust' => 'users,owners', 'data-api-go' => '/admin/cache-bust?scope=users,owners&go=' . urlencode('/admin/owners')]) ?>
                <?= $this->Form->hidden('status', ['value' => 'approved']) ?><button class="p-btn" style="min-height:32px;font-size:12px">Approve</button>
              <?= $this->Form->end() ?>
              <?= $this->Form->create(null, ['url' => ['controller' => 'AdminOwner', 'action' => 'verify', 'owner', $oid], 'style' => 'display:inline', 'data-api' => 'POST /admin/verification/owner/' . $oid, 'data-api-build' => 'verify', 'data-api-omit-empty' => 'reason', 'data-api-ok' => 'Owner rejected.', 'data-opt' => 'patch', 'data-opt-badge' => '.p-badge', 'data-opt-badgesrc' => 'status', 'data-opt-badgemap' => 'verify', 'data-opt-badgetext' => 'lower', 'data-opt-bust' => 'users,owners', 'data-api-go' => '/admin/cache-bust?scope=users,owners&go=' . urlencode('/admin/owners')]) ?>
                <?= $this->Form->hidden('status', ['value' => 'rejected']) ?><input type="hidden" name="reason" value="Rejected by admin"><input type="hidden" name="admin_notes" value="Rejected by admin"><button class="p-btn ghost" style="min-height:32px;font-size:12px">Reject</button>
              <?= $this->Form->end() ?>
              <button class="p-btn ghost" style="min-height:32px;font-size:12px" onclick="document.getElementById('reason-<?= h($oid) ?>').classList.toggle('d-none')">Request changes</button>
            </div>
            <div id="reason-<?= h($oid) ?>" class="d-none mt-2">
              <?= $this->Form->create(null, ['url' => ['controller' => 'AdminOwner', 'action' => 'verify', 'owner', $oid], 'style' => 'display:flex;gap:6px', 'data-api' => 'POST /admin/verification/owner/' . $oid, 'data-api-build' => 'verify', 'data-api-ok' => 'Changes requested.', 'data-opt' => 'patch', 'data-opt-badge' => '.p-badge', 'data-opt-badgesrc' => 'status', 'data-opt-badgemap' => 'verify', 'data-opt-badgetext' => 'lower', 'data-opt-bust' => 'users,owners', 'data-api-go' => '/admin/cache-bust?scope=users,owners&go=' . urlencode('/admin/owners')]) ?>
                <?= $this->Form->hidden('status', ['value' => 'changes_requested']) ?>
                <input name="reason" placeholder="Reason (required)" class="form-control form-control-sm" style="min-height:32px" required maxlength="500"><button class="p-btn" style="min-height:32px;font-size:12px">Send</button>
              <?= $this->Form->end() ?>
            </div>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table></div>
  </div>
  <div style="display:flex;justify-content:space-between;margin-top:12px">
    <a href="?<?= http_build_query(array_merge(['page' => max(1, ($page ?? 1) - 1)], array_filter(['search' => $search ?? '', 'status' => $status ?? '']))) ?>" class="p-btn ghost">Prev</a>
    <a href="?<?= http_build_query(array_merge(['page' => ($page ?? 1) + 1], array_filter(['search' => $search ?? '', 'status' => $status ?? '']))) ?>" class="p-btn ghost">Next</a>
  </div>
<?php endif; ?>

<!-- Owner KYC dossier modal: submitted identity fields, document images and audit history -->
<div class="modal fade" id="ownerDocsModal" tabindex="-1" aria-labelledby="ownerDocsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
    <div class="modal-content" style="border:1px solid #e0e0e0;border-radius:0">
      <div class="modal-header" style="border-bottom:1px solid #e0e0e0">
        <h5 class="modal-title fw-bold" id="ownerDocsModalLabel" style="font-size:16px">Verification documents</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="ownerDocsBody" style="font-size:13.5px">
        <div style="color:var(--p-text-2)">Loading…</div>
      </div>
    </div>
  </div>
</div>
<script>
function ownerDocsRow(label, value, isLink) {
  if (!value) return '';
  var v = String(value);
  var cell = isLink
    ? '<a href="' + v.replace(/"/g, '%22') + '" target="_blank" rel="noopener">Open document →</a>'
    : v.replace(/&/g, '&amp;').replace(/</g, '&lt;');
  return '<div style="display:flex;gap:12px;padding:8px 0;border-top:1px solid #e0e0e0"><span style="width:170px;flex:0 0 170px;font-size:12px;color:#6f6f6f">' + label + '</span><span style="flex:1;min-width:0;overflow-wrap:anywhere">' + cell + '</span></div>';
}
function openOwnerDocs(ownerId) {
  var body = document.getElementById('ownerDocsBody');
  var modalEl = document.getElementById('ownerDocsModal');
  if (!body || !modalEl) return;
  body.innerHTML = '<div style="color:var(--p-text-2)">Loading…</div>';
  if (window.bootstrap && bootstrap.Modal) bootstrap.Modal.getOrCreateInstance(modalEl).show();
  fetch('/admin/owner-docs/' + encodeURIComponent(ownerId), { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
    .then(function (r) { return r.json().catch(function () { return {}; }); })
    .then(function (data) {
      var v = (data && data.verification) || null;
      if (!data || data.status !== 'success' || !v) {
        body.innerHTML = '<div style="color:#b91c1c">No verification documents submitted yet for this owner.</div>';
        return;
      }
      var img = function (url, alt) {
        if (!url) return '';
        var u = String(url).replace(/"/g, '%22');
        if (/\.pdf($|\?)/i.test(u)) {
          return '<div style="margin:8px 0"><div style="font-size:12px;color:#6f6f6f;margin-bottom:4px">' + alt + ' (PDF)</div><a class="p-btn ghost" style="min-height:32px;font-size:12px" href="' + u + '" target="_blank" rel="noopener">Open PDF →</a></div>';
        }
        return '<div style="margin:8px 0"><div style="font-size:12px;color:#6f6f6f;margin-bottom:4px">' + alt + '</div><a href="' + u + '" target="_blank" rel="noopener"><img src="' + u + '" alt="' + alt + '" style="max-width:100%;max-height:320px;border:1px solid #e0e0e0" loading="lazy"></a></div>';
      };
      var hist = '';
      ((data && data.audit_history) || []).forEach(function (h) {
        hist += '<div style="font-size:12.5px;padding:6px 0;border-top:1px solid #e0e0e0">' + String(h.status || '') + ' · ' + String(h.created_at || '') + (h.admin_notes ? ' — ' + String(h.admin_notes) : '') + '</div>';
      });
      body.innerHTML =
        '<div style="font-weight:700;margin-bottom:4px">' + String(v.status || 'submitted') + '</div>' +
        ownerDocsRow('Full name', v.full_name) +
        ownerDocsRow('Phone', v.phone_number) +
        ownerDocsRow('ID number', v.id_number) +
        ownerDocsRow('Business reg.', v.business_registration_number) +
        ownerDocsRow('Bank', v.payout_bank_name) +
        ownerDocsRow('Account no.', v.payout_account_number) +
        ownerDocsRow('Account name', v.payout_account_name) +
        ownerDocsRow('Submitted', v.created_at) +
        img(v.id_document_url, 'National ID photo') +
        img(v.business_document_url, 'Business licence photo') +
        (hist !== '' ? '<div style="font-weight:700;margin:12px 0 4px">History</div>' + hist : '');
    })
    .catch(function () {
      body.innerHTML = '<div style="color:#b91c1c">Could not load documents. Please try again.</div>';
    });
}
</script>
