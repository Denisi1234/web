<?php $this->assign('title', 'Verify Lodges | FastNetStays'); ?>
<?= $this->element('navbar') ?>
<style>.host-card{border:1px solid #e8eaed;border-radius:16px;box-shadow:0 6px 16px rgba(0,0,0,.05);background:#fff}</style>
<div class="container py-4" style="max-width:1180px">
  <h1 style="font-size:20px;font-weight:800">Lodge Verification Queue</h1><p style="font-size:12px;color:#5f6368">Port of ecom-customers.php lodge queue — POST /admin/verification/lodge/{id}</p>
  <form method="get" class="d-flex gap-2 mb-3 flex-wrap">
    <input name="search" value="<?= h($search ?? '') ?>" placeholder="Search lodge/city" class="form-control" style="max-width:280px;border-radius:12px;height:44px">
    <select name="status" class="form-select" style="max-width:180px;border-radius:12px;height:44px"><option value="">All</option><option value="pending" <?= ($status ?? '')==='pending'?'selected':'' ?>>Pending</option><option value="active" <?= ($status ?? '')==='active'?'selected':'' ?>>Active</option><option value="rejected" <?= ($status ?? '')==='rejected'?'selected':'' ?>>Rejected</option></select>
    <button class="btn" style="background:#2563EB;color:#fff;border-radius:12px;height:44px">Filter</button>
  </form>

  <?php if (empty($properties)): ?><div class="host-card p-5 text-center" style="color:#5f6368">No lodges found.</div>
  <?php else: ?>
  <div class="row g-3">
    <?php foreach ($properties as $p):
      $pid = $p['id'] ?? null;
      $st = strtolower((string)($p['status'] ?? 'active'));
      $img = $p['image_url'] ?? $p['primary_image_url'] ?? 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=600&h=400&fit=crop';
    ?>
    <div class="col-md-6 col-lg-4">
      <div class="host-card overflow-hidden">
        <img src="<?= h($img) ?>" style="height:160px;width:100%;object-fit:cover">
        <div class="p-3">
          <div style="font-weight:800"><?= h($p['name'] ?? '—') ?> <span style="font-size:11px;color:#5f6368">#<?= h($pid) ?></span></div>
          <div style="font-size:12px;color:#5f6368"><?= h($p['city'] ?? '') ?> · TSh <?= number_format((float)($p['price_per_night'] ?? 0)) ?>/night</div>
          <div class="mt-1"><span style="background:#F0F3FF;color:#2563EB;border-radius:9999px;padding:3px 8px;font-size:11px;font-weight:700"><?= h($st) ?></span></div>
          <div class="d-flex gap-1 mt-3 flex-wrap">
            <?= $this->Form->create(null, ['url'=>['controller'=>'AdminOwner','action'=>'verify','lodge',$pid]]) ?>
              <?= $this->Form->hidden('status',['value'=>'approved']) ?><button class="btn btn-sm" style="background:#15803d;color:#fff;border-radius:9999px">Approve</button>
            <?= $this->Form->end() ?>
            <?= $this->Form->create(null, ['url'=>['controller'=>'AdminOwner','action'=>'verify','lodge',$pid]]) ?>
              <?= $this->Form->hidden('status',['value'=>'rejected']) ?><input type="hidden" name="reason" value="Rejected"><button class="btn btn-sm" style="border:1px solid #fecaca;color:#dc2626;background:#fff;border-radius:9999px">Reject</button>
            <?= $this->Form->end() ?>
            <?= $this->Form->create(null, ['url'=>['controller'=>'AdminOwner','action'=>'verify','lodge',$pid]]) ?>
              <?= $this->Form->hidden('status',['value'=>'changes_requested']) ?><input type="hidden" name="reason" value="Please update details"><button class="btn btn-sm" style="border:1px solid #e8eaed;background:#fff;border-radius:9999px">Request changes</button>
            <?= $this->Form->end() ?>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
<?= $this->element('footer', ['skin'=>'skin-light-footer']) ?>
