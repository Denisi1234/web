<?php $this->assign('title', 'Verify Owners | FastNetStays'); ?>
<?= $this->element('navbar') ?>
<style>.host-card{border:1px solid #e8eaed;border-radius:16px;box-shadow:0 6px 16px rgba(0,0,0,.05);background:#fff}.pill{border-radius:9999px;padding:4px 8px;font-size:11px;font-weight:700}</style>
<div class="container py-4" style="max-width:1180px">
  <h1 style="font-size:20px;font-weight:800">Owner Verification</h1><p style="font-size:12px;color:#5f6368">Port of admin_owner_portal/ecom-customers.php owner queue — POST /admin/verification/owner/{id}</p>
  <form method="get" class="d-flex gap-2 mb-3 flex-wrap">
    <input name="search" value="<?= h($search ?? '') ?>" placeholder="Search name/email" class="form-control" style="max-width:280px;border-radius:12px;height:44px">
    <select name="status" class="form-select" style="max-width:180px;border-radius:12px;height:44px"><option value="">All status</option><option value="pending" <?= ($status ?? '')==='pending'?'selected':'' ?>>Pending</option><option value="approved" <?= ($status ?? '')==='approved'?'selected':'' ?>>Approved</option><option value="rejected" <?= ($status ?? '')==='rejected'?'selected':'' ?>>Rejected</option><option value="suspended" <?= ($status ?? '')==='suspended'?'selected':'' ?>>Suspended</option></select>
    <button class="btn" style="background:#2563EB;color:#fff;border-radius:12px;height:44px;padding:0 18px">Filter</button>
    <a href="<?= $this->Url->build('/admin/owners') ?>" class="btn" style="border:1px solid #e8eaed;border-radius:12px;height:44px">Clear</a>
  </form>

  <?php
    $list = $users;
    if (!empty($financial['data']) && is_array($financial['data'])) $list = $financial['data'];
    elseif (!empty($financial) && isset($financial[0])) $list = $financial;
    if (empty($list)) $list = $users;
  ?>
  <?php if (empty($list)): ?><div class="host-card p-5 text-center" style="color:#5f6368">No owners found.</div>
  <?php else: ?>
  <div class="host-card p-0 overflow-hidden">
    <div class="table-responsive"><table class="table align-middle mb-0" style="font-size:13px"><thead style="background:#F8FAFC;font-size:11px;color:#5f6368"><tr><th>Owner</th><th>Contact</th><th>Status</th><th>Gross / Net</th><th style="width:220px">Action</th></tr></thead><tbody>
      <?php foreach ($list as $o):
        $oid = $o['id'] ?? $o['owner_id'] ?? $o['user_id'] ?? null;
        $name = $o['name'] ?? $o['owner_name'] ?? '—';
        $email = $o['email'] ?? $o['owner_email'] ?? '';
        $st = strtolower((string)($o['status'] ?? $o['verification_status'] ?? 'pending'));
        $gross = $o['gross_revenue'] ?? $o['total_earnings'] ?? $o['gross'] ?? 0;
        $net = $o['net_earnings'] ?? $o['owner_earnings'] ?? ($gross * 0.9);
      ?><tr>
        <td><div style="font-weight:700"><?= h($name) ?> <span style="font-size:11px;color:#5f6368">#<?= h($oid) ?></span></div><div style="font-size:11px;color:#5f6368"><?= h($email) ?></div></td>
        <td style="font-size:12px"><?= h($o['phone'] ?? $o['phone_number'] ?? '') ?></td>
        <td><span class="pill" style="background:<?= $st==='approved'?'#dcfce7;color:#15803d':($st==='rejected'?'#fee2e2;color:#dc2626':'#fef3c7;color:#d97706') ?>"><?= h($st) ?></span></td>
        <td><div style="font-weight:700;color:#C2410C">TSh <?= number_format((float)$gross) ?></div><div style="font-size:11px;color:#5f6368">Net TSh <?= number_format((float)$net) ?></div></td>
        <td>
          <div class="d-flex gap-1 flex-wrap">
            <?= $this->Form->create(null, ['url'=>['controller'=>'AdminOwner','action'=>'verify','owner',$oid]]) ?>
              <?= $this->Form->hidden('status',['value'=>'approved']) ?><button class="btn btn-sm" style="background:#15803d;color:#fff;border-radius:9999px;font-size:11px">Approve</button>
            <?= $this->Form->end() ?>
            <?= $this->Form->create(null, ['url'=>['controller'=>'AdminOwner','action'=>'verify','owner',$oid]]) ?>
              <?= $this->Form->hidden('status',['value'=>'rejected']) ?><input type="hidden" name="reason" value="Rejected by admin"><button class="btn btn-sm" style="background:#fff;border:1px solid #fecaca;color:#dc2626;border-radius:9999px;font-size:11px">Reject</button>
            <?= $this->Form->end() ?>
            <button class="btn btn-sm" style="background:#fff;border:1px solid #e8eaed;border-radius:9999px;font-size:11px" onclick="document.getElementById('reason-<?= $oid ?>').classList.toggle('d-none')">Request changes</button>
          </div>
          <div id="reason-<?= $oid ?>" class="d-none mt-2">
            <?= $this->Form->create(null, ['url'=>['controller'=>'AdminOwner','action'=>'verify','owner',$oid]]) ?>
              <?= $this->Form->hidden('status',['value'=>'changes_requested']) ?>
              <div class="d-flex gap-1"><input name="reason" placeholder="Reason" class="form-control form-control-sm" style="border-radius:9999px"><button class="btn btn-sm" style="background:#2563EB;color:#fff;border-radius:9999px">Send</button></div>
            <?= $this->Form->end() ?>
          </div>
        </td>
      </tr><?php endforeach; ?>
    </tbody></table></div>
  </div>
  <div class="d-flex justify-content-between mt-3">
    <a href="?<?= http_build_query(array_merge(['page'=>max(1,($page ??1)-1)], array_filter(['search'=>$search ?? '','status'=>$status ?? '']))) ?>" class="btn" style="border:1px solid #e8eaed;border-radius:9999px">Prev</a>
    <a href="?<?= http_build_query(array_merge(['page'=>($page ??1)+1], array_filter(['search'=>$search ?? '','status'=>$status ?? '']))) ?>" class="btn" style="border:1px solid #e8eaed;border-radius:9999px">Next</a>
  </div>
  <?php endif; ?>
</div>
<?= $this->element('footer', ['skin'=>'skin-light-footer']) ?>
