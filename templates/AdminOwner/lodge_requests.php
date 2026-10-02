<?php
$this->assign('title', 'Lodge Requests');
$this->assign('portal_title', 'Lodge requests');
?>
<div class="p-card mb-3">
  <?= $this->Form->create(null, ['type' => 'get', 'style' => 'display:flex;gap:8px;flex-wrap:wrap;align-items:end']) ?>
    <div style="flex:1;min-width:180px">
      <label style="font-size:12px;font-weight:600;color:var(--p-text-2)">Search</label>
      <input name="search" value="<?= h($search ?? '') ?>" placeholder="Room, type" class="form-control" style="min-height:40px">
    </div>
    <div style="min-width:150px">
      <label style="font-size:12px;font-weight:600;color:var(--p-text-2)">Status</label>
      <select name="status" class="form-select" style="min-height:40px" onchange="this.form.submit()">
        <?php foreach (['all' => 'All status', 'Pending' => 'Pending', 'In Progress' => 'In Progress', 'Completed' => 'Completed', 'Cancelled' => 'Cancelled'] as $k => $v): ?>
          <option value="<?= $k ?>" <?= ($status ?? 'all') === $k ? 'selected' : '' ?>><?= $v ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div style="min-width:150px">
      <label style="font-size:12px;font-weight:600;color:var(--p-text-2)">Type</label>
      <select name="type" class="form-select" style="min-height:40px" onchange="this.form.submit()">
        <?php foreach (($typeOptions ?? ['all']) as $opt): ?><option value="<?= h($opt) ?>" <?= ($type ?? 'all') === $opt ? 'selected' : '' ?>><?= h($opt) ?></option><?php endforeach; ?>
      </select>
    </div>
    <button class="p-btn" type="submit">Filter</button>
    <a href="<?= $this->Url->build('/admin/requests') ?>" class="p-btn ghost">Clear</a>
  <?= $this->Form->end() ?>
</div>

<?php if (empty($requests)): ?>
  <div class="p-card"><div class="p-empty">No lodge requests.</div></div>
<?php else: ?>
  <div class="p-table-wrap">
    <div class="table-responsive"><table class="p-table">
      <thead><tr><th>Request</th><th>Room</th><th>Type</th><th style="text-align:right">Price</th><th>Status</th><th>Created</th><th>Update</th></tr></thead>
      <tbody>
        <?php foreach ($requests as $r):
          $rid = $r['id'] ?? '';
          $st = strtolower((string)($r['status'] ?? 'pending'));
          $badge = $st === 'completed' ? 'green' : ($st === 'cancelled' ? 'red' : 'yellow');
          $price = (float)($r['price'] ?? $r['amount'] ?? 0);
        ?>
        <tr>
          <td><strong>#<?= h($rid) ?></strong></td>
          <td><?= h($r['room_number'] ?? $r['room'] ?? '—') ?></td>
          <td><span class="p-badge blue"><?= h($r['type'] ?? $r['room_type'] ?? '') ?></span></td>
          <td style="text-align:right;font-weight:600">TSh <?= number_format($price) ?></td>
          <td><span class="p-badge <?= $badge ?>"><?= h($r['status'] ?? '') ?></span></td>
          <td style="font-size:12px;color:var(--p-text-2)"><?= h(substr((string)($r['created_at'] ?? ''), 0, 10)) ?></td>
          <td>
            <?= $this->Form->create(null, ['url' => ['controller' => 'AdminOwner', 'action' => 'lodgeRequests'], 'style' => 'display:flex;gap:6px', 'data-api' => 'PATCH /lodge-requests/' . $rid . '/status', 'data-api-strip' => 'request_id', 'data-api-ok' => 'Request updated.', 'data-opt' => 'patch', 'data-opt-badge' => '.p-badge', 'data-opt-badge-idx' => '1', 'data-opt-badgesrc' => 'status', 'data-opt-badgemap' => 'request', 'data-opt-badgetext' => 'raw', 'data-opt-bust' => '_lodge_requests', 'data-api-go' => '/admin/cache-bust?scope=_lodge_requests&go=' . urlencode('/admin/requests')]) ?>
              <?= $this->Form->hidden('request_id', ['value' => $rid]) ?>
              <select name="status" class="form-select form-select-sm" style="min-height:32px;min-width:120px">
                <?php foreach (['Pending', 'In Progress', 'Completed', 'Cancelled'] as $opt): ?>
                  <option value="<?= $opt ?>" <?= $st === strtolower($opt) ? 'selected' : '' ?>><?= $opt ?></option>
                <?php endforeach; ?>
              </select>
              <button class="p-btn" style="min-height:32px;font-size:12px">Save</button>
            <?= $this->Form->end() ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table></div>
  </div>
<?php endif; ?>
