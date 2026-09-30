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
          $st = strtolower((string)($o['status'] ?? $o['verification_status'] ?? 'pending'));
          $badge = $st === 'approved' ? 'green' : ($st === 'rejected' ? 'red' : ($st === 'suspended' ? 'red' : 'yellow'));
          $gross = $o['gross_revenue'] ?? $o['total_earnings'] ?? $o['gross'] ?? 0;
          $net = $o['net_earnings'] ?? $o['owner_earnings'] ?? ($gross * 0.9);
        ?>
        <tr>
          <td><div style="font-weight:600"><?= h($name) ?> <span style="font-size:11px;font-weight:400;color:var(--p-text-2)">#<?= h($oid) ?></span></div><div style="font-size:12px;color:var(--p-text-2)"><?= h($email) ?></div></td>
          <td style="font-size:13px"><?= h($o['phone'] ?? $o['phone_number'] ?? '') ?></td>
          <td><span class="p-badge <?= $badge ?>"><?= h($st) ?></span></td>
          <td style="text-align:right"><div style="font-weight:600">TSh <?= number_format((float)$gross) ?></div><div style="font-size:11px;color:var(--p-text-2)">Net TSh <?= number_format((float)$net) ?></div></td>
          <td>
            <div style="display:flex;gap:6px;flex-wrap:wrap">
              <?= $this->Form->create(null, ['url' => ['controller' => 'AdminOwner', 'action' => 'verify', 'owner', $oid], 'style' => 'display:inline']) ?>
                <?= $this->Form->hidden('status', ['value' => 'approved']) ?><button class="p-btn" style="min-height:32px;font-size:12px">Approve</button>
              <?= $this->Form->end() ?>
              <?= $this->Form->create(null, ['url' => ['controller' => 'AdminOwner', 'action' => 'verify', 'owner', $oid], 'style' => 'display:inline']) ?>
                <?= $this->Form->hidden('status', ['value' => 'rejected']) ?><input type="hidden" name="reason" value="Rejected by admin"><button class="p-btn ghost" style="min-height:32px;font-size:12px">Reject</button>
              <?= $this->Form->end() ?>
              <button class="p-btn ghost" style="min-height:32px;font-size:12px" onclick="document.getElementById('reason-<?= $oid ?>').classList.toggle('d-none')">Request changes</button>
            </div>
            <div id="reason-<?= $oid ?>" class="d-none mt-2">
              <?= $this->Form->create(null, ['url' => ['controller' => 'AdminOwner', 'action' => 'verify', 'owner', $oid], 'style' => 'display:flex;gap:6px']) ?>
                <?= $this->Form->hidden('status', ['value' => 'changes_requested']) ?>
                <input name="reason" placeholder="Reason" class="form-control form-control-sm" style="min-height:32px"><button class="p-btn" style="min-height:32px;font-size:12px">Send</button>
              <?= $this->Form->end() ?>
            </div>
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
