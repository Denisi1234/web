<?php $this->assign('title', 'Lodge Requests | FastNetStays'); ?>
<?= $this->element('navbar') ?>
<style>.host-card{border:1px solid #e8eaed;border-radius:16px;box-shadow:0 6px 16px rgba(0,0,0,.05);background:#fff}.pill{border-radius:9999px;padding:4px 8px;font-size:11px;font-weight:700}</style>
<div class="container py-4" style="max-width:1280px">
  <h1 style="font-size:20px;font-weight:800">Lodge Requests</h1><p style="font-size:12px;color:#5f6368">Port of fastnet_admin_portal/lib/screens/lodge_requests_screen.dart — GET /lodge-requests</p>

  <form method="get" class="d-flex gap-2 mb-3 flex-wrap">
    <input name="search" value="<?= h($search ?? '') ?>" placeholder="Search room/type" class="form-control" style="max-width:260px;border-radius:12px;height:44px">
    <select name="status" class="form-select" style="max-width:160px;border-radius:12px;height:44px" onchange="this.form.submit()">
      <option value="all" <?= ($status ?? 'all')==='all'?'selected':'' ?>>All status</option><option value="Pending" <?= ($status ?? '')==='Pending'?'selected':'' ?>>Pending</option><option value="In Progress" <?= ($status ?? '')==='In Progress'?'selected':'' ?>>In Progress</option><option value="Completed" <?= ($status ?? '')==='Completed'?'selected':'' ?>>Completed</option><option value="Cancelled" <?= ($status ?? '')==='Cancelled'?'selected':'' ?>>Cancelled</option>
    </select>
    <select name="type" class="form-select" style="max-width:160px;border-radius:12px;height:44px" onchange="this.form.submit()">
      <?php foreach (($typeOptions ?? ['all']) as $opt): ?><option value="<?= h($opt) ?>" <?= ($type ?? 'all')===$opt?'selected':'' ?>><?= h($opt) ?></option><?php endforeach; ?>
    </select>
    <button class="btn" style="background:#2563EB;color:#fff;border-radius:12px;height:44px">Filter</button>
    <a href="<?= $this->Url->build('/admin/requests') ?>" class="btn" style="border:1px solid #e8eaed;border-radius:12px;height:44px">Clear</a>
  </form>

  <?php if (empty($requests)): ?><div class="host-card p-5 text-center" style="color:#5f6368">No lodge requests.</div>
  <?php else: ?>
  <div class="host-card p-0 overflow-hidden">
    <div class="table-responsive"><table class="table align-middle mb-0" style="font-size:13px"><thead style="background:#F8FAFC;font-size:11px;color:#5f6368"><tr><th>Request</th><th>Room</th><th>Type</th><th>Price</th><th>Status</th><th>Created</th><th>Update</th></tr></thead><tbody>
      <?php foreach ($requests as $r):
        $rid=$r['id'] ?? '';
        $st=strtolower((string)($r['status'] ?? 'pending'));
        $price=(float)($r['price'] ?? $r['amount'] ?? 0);
      ?><tr>
        <td style="font-weight:800">#<?= h($rid) ?></td>
        <td><?= h($r['room_number'] ?? $r['room'] ?? '—') ?></td>
        <td><span class="pill" style="background:#F0F3FF;color:#2563EB"><?= h($r['type'] ?? $r['room_type'] ?? '') ?></span></td>
        <td style="color:#C2410C;font-weight:800">TSh <?= number_format($price) ?></td>
        <td><span class="pill" style="background:<?= $st==='completed'?'#dcfce7;color:#15803d':($st==='cancelled'?'#fee2e2;color:#dc2626':'#fef3c7;color:#d97706') ?>"><?= h($r['status'] ?? '') ?></span></td>
        <td style="font-size:12px;color:#5f6368"><?= h(substr((string)($r['created_at'] ?? ''),0,10)) ?></td>
        <td>
          <?= $this->Form->create(null, ['url'=>['controller'=>'AdminOwner','action'=>'lodgeRequests']]) ?>
            <?= $this->Form->hidden('request_id',['value'=>$rid]) ?>
            <div class="d-flex gap-1">
              <select name="status" class="form-select form-select-sm" style="border-radius:9999px;min-width:130px">
                <option value="Pending" <?= $st==='pending'?'selected':'' ?>>Pending</option>
                <option value="In Progress" <?= $st==='in progress'?'selected':'' ?>>In Progress</option>
                <option value="Completed" <?= $st==='completed'?'selected':'' ?>>Completed</option>
                <option value="Cancelled" <?= $st==='cancelled'?'selected':'' ?>>Cancelled</option>
              </select>
              <button class="btn btn-sm" style="background:#2563EB;color:#fff;border-radius:9999px">Save</button>
            </div>
          <?= $this->Form->end() ?>
        </td>
      </tr><?php endforeach; ?>
    </tbody></table></div>
  </div>
  <?php endif; ?>
</div>
<?= $this->element('footer', ['skin'=>'skin-light-footer']) ?>
