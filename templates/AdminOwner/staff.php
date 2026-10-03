<?php
$this->assign('title', 'Staff');
$this->assign('portal_title', 'Staff');
$this->assign('page_actions', '<button class="p-btn" data-bs-toggle="modal" data-bs-target="#addStaffModal">Add staff</button>');
?>
<div class="modal fade" id="addStaffModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
  <div class="modal-header"><h5 style="font-weight:600">Add staff</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
  <?= $this->Form->create(null, ['url' => ['controller' => 'AdminOwner', 'action' => 'staff'], 'data-api' => 'POST /staff', 'data-api-strip' => 'action', 'data-api-ok' => 'Staff added.', 'data-opt' => 'go', 'data-api-go' => '/admin/cache-bust?scope=_staff&go=' . urlencode('/admin/staff')]) ?>
  <div class="modal-body" style="display:grid;gap:8px">
    <?= $this->Form->hidden('action', ['value' => 'add']) ?>
    <input name="name" placeholder="Name *" class="form-control" style="min-height:40px" required>
    <select name="role" class="form-select" style="min-height:40px"><option>Manager</option><option>Receptionist</option><option>Housekeeper</option><option>Maintenance</option></select>
    <input name="phone" placeholder="Phone *" class="form-control" style="min-height:40px" required>
  </div>
  <div class="modal-footer"><button type="button" class="p-btn ghost" data-bs-dismiss="modal">Cancel</button><button class="p-btn">Save</button></div>
  <?= $this->Form->end() ?>
</div></div></div>

<?php if (empty($staff)): ?>
  <div class="p-card"><div class="p-empty">No staff yet. Add the first staff member.</div></div>
<?php else: ?>
  <div class="p-table-wrap">
    <div class="table-responsive"><table class="p-table">
      <thead><tr><th>Staff</th><th>Role</th><th>Phone</th><th>Added</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($staff as $s):
          $sid = $s['id'] ?? '';
          $role = trim((string)($s['role'] ?? 'Receptionist'));
          $initials = strtoupper(substr(trim((string)($s['name'] ?? 'S')), 0, 2));
        ?>
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:10px">
              <span style="width:32px;height:32px;border-radius:50%;background:var(--p-blue);color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:600;font-size:12px"><?= h($initials) ?></span>
              <strong><?= h($s['name'] ?? '—') ?></strong>
            </div>
          </td>
          <td><span class="p-badge blue"><?= h($role) ?></span></td>
          <td><?= h($s['phone'] ?? '') ?></td>
          <td style="font-size:12px;color:var(--p-text-2)"><?= h(substr((string)($s['created_at'] ?? ''), 0, 10)) ?></td>
          <td>
            <div style="display:flex;gap:6px;flex-wrap:wrap">
              <button class="p-btn ghost" style="min-height:32px;font-size:12px" data-bs-toggle="modal" data-bs-target="#editStaff<?= h($sid) ?>">Edit</button>
              <?= $this->Form->create(null, ['url' => ['controller' => 'AdminOwner', 'action' => 'staff'], 'style' => 'display:inline', 'data-api' => 'DELETE /staff/' . $sid, 'data-api-strip' => 'action,staff_id', 'data-api-ok' => 'Staff deleted.', 'data-api-confirm' => 'Delete staff?', 'data-opt' => 'remove', 'data-opt-bust' => '_staff', 'data-api-go' => '/admin/cache-bust?scope=_staff&go=' . urlencode('/admin/staff')]) ?>
                <?= $this->Form->hidden('action', ['value' => 'delete']) ?><?= $this->Form->hidden('staff_id', ['value' => $sid]) ?><button class="p-btn ghost" style="min-height:32px;font-size:12px">Delete</button>
              <?= $this->Form->end() ?>
            </div>
            <div class="modal fade" id="editStaff<?= h($sid) ?>" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
              <div class="modal-header"><h5 style="font-weight:600">Edit staff #<?= h($sid) ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
              <?= $this->Form->create(null, ['url' => ['controller' => 'AdminOwner', 'action' => 'staff'], 'data-api' => 'PATCH /staff/' . $sid, 'data-api-strip' => 'action,staff_id', 'data-api-ok' => 'Staff updated.', 'data-opt' => 'refresh', 'data-opt-close' => 'closest:.modal', 'data-opt-bust' => '_staff', 'data-api-go' => '/admin/cache-bust?scope=_staff&go=' . urlencode('/admin/staff')]) ?>
              <div class="modal-body" style="display:grid;gap:8px">
                <?= $this->Form->hidden('action', ['value' => 'update']) ?><?= $this->Form->hidden('staff_id', ['value' => $sid]) ?>
                <input name="name" value="<?= h($s['name'] ?? '') ?>" class="form-control" style="min-height:40px" required>
                <select name="role" class="form-select" style="min-height:40px">
                  <?php foreach (['Manager', 'Receptionist', 'Housekeeper', 'Maintenance'] as $opt): ?><option <?= $role === $opt ? 'selected' : '' ?>><?= $opt ?></option><?php endforeach; ?>
                </select>
                <input name="phone" value="<?= h($s['phone'] ?? '') ?>" class="form-control" style="min-height:40px" required>
              </div>
              <div class="modal-footer"><button type="button" class="p-btn ghost" data-bs-dismiss="modal">Cancel</button><button class="p-btn">Update</button></div>
              <?= $this->Form->end() ?>
            </div></div></div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table></div>
  </div>
<?php endif; ?>
