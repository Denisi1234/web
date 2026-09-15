<?php $this->assign('title', 'Admin Dashboard | FastNetStays'); ?>
<?= $this->element('navbar') ?>
<style>.host-card{border:1px solid #e8eaed;border-radius:16px;box-shadow:0 6px 16px rgba(0,0,0,.05);background:#fff}.host-stat-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center}</style>
<div class="container py-4" style="max-width:1180px">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div><h1 style="font-size:22px;font-weight:800;color:#1a1d25">Admin Dashboard</h1><p style="font-size:12px;color:#5f6368" class="mb-0">Mirrors admin_owner_portal/index.php — admin sees all.</p></div>
    <span style="background:#EBF5FF;color:#2563EB;border-radius:9999px;padding:6px 12px;font-size:12px;font-weight:700"><?= count($owners ?? []) ?> owners · <?= count($properties ?? []) ?> lodges</span>
  </div>
  <div class="row g-3 mb-4">
    <div class="col-6 col-lg-3"><div class="host-card p-3"><div class="host-stat-icon" style="background:#EBF5FF;color:#2563EB"><i class="fa-solid fa-hotel"></i></div><div style="font-size:20px;font-weight:800" class="mt-2"><?= (int)($stats['properties'] ?? 0) ?></div><div style="font-size:12px;color:#5f6368">Lodges</div></div></div>
    <div class="col-6 col-lg-3"><div class="host-card p-3"><div class="host-stat-icon" style="background:#F0F3FF;color:#2563EB"><i class="fa-solid fa-calendar-check"></i></div><div style="font-size:20px;font-weight:800" class="mt-2"><?= (int)($stats['bookings'] ?? 0) ?></div><div style="font-size:12px;color:#5f6368">Bookings</div></div></div>
    <div class="col-6 col-lg-3"><div class="host-card p-3"><div class="host-stat-icon" style="background:#fef3c7;color:#d97706"><i class="fa-solid fa-users"></i></div><div style="font-size:20px;font-weight:800" class="mt-2"><?= (int)($stats['owners'] ?? 0) ?></div><div style="font-size:12px;color:#5f6368">Owners</div></div></div>
    <div class="col-6 col-lg-3"><div class="host-card p-3"><div class="host-stat-icon" style="background:#dcfce7;color:#15803d"><i class="fa-solid fa-wallet"></i></div><div style="font-size:20px;font-weight:800" class="mt-2">TSh <?= number_format((float)($stats['revenue'] ?? 0)) ?></div><div style="font-size:11px;color:#5f6368">Gross · Fee 10% TSh <?= number_format((float)($stats['platform_fee'] ?? 0)) ?> · Net TSh <?= number_format((float)($stats['owner_earnings'] ?? 0)) ?></div></div></div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-lg-8">
      <div class="host-card p-3">
        <div class="d-flex justify-content-between align-items-center mb-2"><b style="font-size:14px">Recent Lodges</b><a href="<?= $this->Url->build('/admin/lodges') ?>" style="font-size:12px;color:#2563EB;font-weight:700">View all</a></div>
        <?php if (empty($recentProperties)): ?><div style="font-size:13px;color:#5f6368" class="py-3 text-center">No lodges yet.</div>
        <?php else: ?>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0" style="font-size:13px"><thead style="font-size:11px;color:#5f6368"><tr><th>Lodge</th><th>City</th><th>Price</th><th>Status</th></tr></thead><tbody>
          <?php foreach ($recentProperties as $p): ?><tr><td style="font-weight:700"><?= h($p['name'] ?? '—') ?></td><td><?= h($p['city'] ?? '') ?></td><td style="color:#C2410C;font-weight:800">TSh <?= number_format((float)($p['price_per_night'] ?? 0)) ?></td><td><span style="background:#F0F3FF;color:#2563EB;border-radius:9999px;padding:3px 8px;font-size:11px;font-weight:700"><?= h($p['status'] ?? 'Active') ?></span></td></tr><?php endforeach; ?>
        </tbody></table></div>
        <?php endif; ?>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="host-card p-3">
        <b style="font-size:14px">Admin Tools</b>
        <div class="d-grid gap-2 mt-3">
          <a href="<?= $this->Url->build('/admin/owners') ?>" class="btn" style="background:#2563EB;color:#fff;border-radius:12px;font-weight:700">Verify Owners</a>
          <a href="<?= $this->Url->build('/admin/lodges') ?>" class="btn" style="background:#fff;border:1px solid #e8eaed;border-radius:12px;font-weight:700">Verify Lodges</a>
          <a href="<?= $this->Url->build('/admin/finance/payouts') ?>" class="btn" style="background:#fff;border:1px solid #e8eaed;border-radius:12px;font-weight:700">Payouts</a>
          <a href="<?= $this->Url->build('/admin/finance/ledger') ?>" class="btn" style="background:#fff;border:1px solid #e8eaed;border-radius:12px;font-weight:700">Ledger</a>
          <a href="<?= $this->Url->build('/admin/support') ?>" class="btn" style="background:#fff;border:1px solid #e8eaed;border-radius:12px;font-weight:700">Support Tickets</a>
        </div>
      </div>
    </div>
  </div>
</div>
<?= $this->element('footer', ['skin'=>'skin-light-footer']) ?>
