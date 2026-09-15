<?php $this->assign('title', 'Staff | FastNetStays'); ?>
<?= $this->element('navbar') ?>
<style>.host-card{border:1px solid #e8eaed;border-radius:16px;box-shadow:0 6px 16px rgba(0,0,0,.05);background:#fff}.pill{border-radius:9999px;padding:4px 8px;font-size:11px;font-weight:700}</style>
<div class="container py-4" style="max-width:1180px">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div><h1 style="font-size:20px;font-weight:800">Staff Management</h1><p style="font-size:12px;color:#5f6368">Port of fastnet_admin_portal/lib/screens/staff_screen.dart — GET/POST/PATCH/DELETE /staff</p></div>
    <button class="btn" style="background:#2563EB;color:#fff;border-radius:9999px" data-bs-toggle="modal" data-bs-target="#addStaffModal">Add Staff</button>
  </div>

  <!-- Add Modal -->
  <div class="modal fade" id="addStaffModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content" style="border-radius:16px">
    <div class="modal-header"><h5 style="font-weight:800">Add Staff</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <?= $this->Form->create(null, ['url'=>['controller'=>'AdminOwner','action'=>'staff']]) ?>
    <div class="modal-body d-grid gap-2">
      <?= $this->Form->hidden('action',['value'=>'add']) ?>
      <input name="name" placeholder="Name *" class="form-control" style="border-radius:12px;height:44px" required>
      <select name="role" class="form-select" style="border-radius:12px;height:44px"><option>Manager</option><option>Receptionist</option><option>Cleaner</option><option>Security</option><option>Driver</option></select>
      <input name="phone" placeholder="Phone *" class="form-control" style="border-radius:12px;height:44px" required>
    </div>
    <div class="modal-footer"><button type="button" class="btn" style="border:1px solid #e8eaed;border-radius:9999px" data-bs-dismiss="modal">Cancel</button><button class="btn" style="background:#2563EB;color:#fff;border-radius:9999px">Save</button></div>
    <?= $this->Form->end() ?>
  </div></div></div>

  <?php if (empty($staff)): ?><div class="host-card p-5 text-center" style="color:#5f6368">No staff yet. Add first staff member.</div>
  <?php else: ?>
  <div class="host-card p-0 overflow-hidden">
    <div class="table-responsive"><table class="table align-middle mb-0" style="font-size:13px"><thead style="background:#F8FAFC;font-size:11px;color:#5f6368"><tr><th>Staff</th><th>Role</th><th>Phone</th><th>Added</th><th>Actions</th></tr></thead><tbody>
      <?php foreach ($staff as $s):
        $sid=$s['id'] ?? '';
        $role=trim((string)($s['role'] ?? 'Receptionist'));
        $roleColor = match(strtolower($role)) {
          'manager'=>'#2563EB', 'receptionist'=>'#1A56DB', 'cleaner'=>'#15803d', 'security'=>'#d97706', 'driver'=>'#7C3AED', default=>'#5f6368'
        };
        $initials = strtoupper(substr(trim((string)($s['name'] ?? 'S')),0,2));
      ?><tr>
        <td><div class="d-flex align-items-center gap-2"><span style="width:36px;height:36px;border-radius:9999px;background:<?= h($roleColor) ?>20;color:<?= h($roleColor) ?>;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:11px"><?= h($initials) ?></span><b><?= h($s['name'] ?? '—') ?></b></div></td>
        <td><span class="pill" style="background:<?= h($roleColor) ?>20;color:<?= h($roleColor) ?>"><?= h($role) ?></span></td>
        <td><?= h($s['phone'] ?? '') ?></td>
        <td style="font-size:12px;color:#5f6368"><?= h(substr((string)($s['created_at'] ?? ''),0,10)) ?></td>
        <td>
          <div class="d-flex gap-1 flex-wrap">
            <button class="btn btn-sm" style="border:1px solid #e8eaed;border-radius:9999px" data-bs-toggle="modal" data-bs-target="#editStaff<?= h($sid) ?>">Edit</button>
            <?= $this->Form->create(null, ['url'=>['controller'=>'AdminOwner','action'=>'staff']]) ?>
              <?= $this->Form->hidden('action',['value'=>'delete']) ?><?= $this->Form->hidden('staff_id',['value'=>$sid]) ?><button class="btn btn-sm" style="background:#fff;border:1px solid #fecaca;color:#dc2626;border-radius:9999px" onclick="return confirm('Delete staff?')">Delete</button>
            <?= $this->Form->end() ?>
          </div>
          <div class="modal fade" id="editStaff<?= h($sid) ?>" tabindex="-1"><div class="modal-dialog"><div class="modal-content" style="border-radius:16px">
            <div class="modal-header"><h5 style="font-weight:800">Edit Staff #<?= h($sid) ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <?= $this->Form->create(null, ['url'=>['controller'=>'AdminOwner','action'=>'staff']]) ?>
            <div class="modal-body d-grid gap-2">
              <?= $this->Form->hidden('action',['value'=>'update']) ?><?= $this->Form->hidden('staff_id',['value'=>$sid]) ?>
              <input name="name" value="<?= h($s['name'] ?? '') ?>" class="form-control" style="border-radius:12px;height:44px" required>
              <select name="role" class="form-select" style="border-radius:12px;height:44px"><option <?= $role==='Manager'?'selected':'' ?>>Manager</option><option <?= $role==='Receptionist'?'selected':'' ?>>Receptionist</option><option <?= $role==='Cleaner'?'selected':'' ?>>Cleaner</option><option <?= $role==='Security'?'selected':'' ?>>Security</option><option <?= $role==='Driver'?'selected':'' ?>>Driver</option></select>
              <input name="phone" value="<?= h($s['phone'] ?? '') ?>" class="form-control" style="border-radius:12px;height:44px" required>
            </div>
            <div class="modal-footer"><button type="button" class="btn" style="border:1px solid #e8eaed;border-radius:9999px" data-bs-dismiss="modal">Cancel</button><button class="btn" style="background:#2563EB;color:#fff;border-radius:9999px">Update</button></div>
            <?= $this->Form->end() ?>
          </div></div></div>
        </td>
      </tr><?php endforeach; ?>
    </tbody></table></div>
  </div>
  <?php endif; ?>
</div>
<?= $this->element('footer', ['skin'=>'skin-light-footer']) ?>
