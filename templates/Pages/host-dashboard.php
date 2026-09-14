<?php $this->assign('title', 'Host Dashboard | FastNetStays'); ?>
<?= $this->element('navbar') ?>
<style>.host-card{border:1px solid #e8eaed;border-radius:16px;box-shadow:0 6px 16px rgba(0,0,0,.05);background:#fff}.host-stat-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center}</style>
<div class="container py-4" style="max-width:1180px">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 style="font-size:22px;font-weight:800;color:#1a1d25">Lodge Management</h1>
    <a href="<?= $this->Url->build('/host/listings/add') ?>" class="btn" style="background:#2563EB;color:#fff;border-radius:30px;padding:10px 18px;font-weight:700">Add New Property</a>
  </div>
  <div class="row g-3 mb-4">
    <div class="col-6 col-lg-3"><div class="host-card p-3"><div class="host-stat-icon" style="background:#EBF5FF;color:#2563EB"><i class="fa-solid fa-wallet"></i></div><div style="font-size:20px;font-weight:800" class="mt-2">TSh <?= number_format((float)($stats['revenue'] ?? 0)) ?></div><div style="font-size:12px;color:#5f6368">Total Earnings</div></div></div>
    <div class="col-6 col-lg-3"><div class="host-card p-3"><div class="host-stat-icon" style="background:#F0F3FF;color:#2563EB"><i class="fa-solid fa-calendar-check"></i></div><div style="font-size:20px;font-weight:800" class="mt-2"><?= (int)($stats['bookings'] ?? 0) ?></div><div style="font-size:12px;color:#5f6368">Bookings</div></div></div>
    <div class="col-6 col-lg-3"><div class="host-card p-3"><div class="host-stat-icon" style="background:#fef3c7;color:#d97706"><i class="fa-solid fa-hotel"></i></div><div style="font-size:20px;font-weight:800" class="mt-2"><?= (int)($stats['properties'] ?? 0) ?></div><div style="font-size:12px;color:#5f6368">Properties</div></div></div>
    <div class="col-6 col-lg-3"><div class="host-card p-3"><div class="host-stat-icon" style="background:#fee2e2;color:#dc2626"><i class="fa-solid fa-triangle-exclamation"></i></div><div style="font-size:20px;font-weight:800" class="mt-2">—</div><div style="font-size:12px;color:#5f6368">Pending Action</div></div></div>
  </div>
  <h2 style="font-size:16px;font-weight:800;color:#1a1d25" class="mb-2">Management Tools</h2>
  <div class="list-group" style="border-radius:16px;overflow:hidden;border:1px solid #e8eaed">
    <?php $tools=[['My Properties','Manage listings','/host/listings','fa-list'],['Add New Property','Create listing','/host/listings/add','fa-plus'],['Guest Bookings','Reservations','/host/bookings','fa-users'],['Calendar','Availability','/host/calendar/1','fa-calendar'],['Financial Reports','Payouts','/host/earnings','fa-chart-line']]; foreach($tools as $t): ?>
    <a href="<?= $this->Url->build($t[2]) ?>" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3"><span class="host-stat-icon" style="background:#fef2f2;color:#dc2626"><i class="fa-solid <?= $t[3] ?>"></i></span><span style="font-weight:700;color:#1a1d25"><?= h($t[0]) ?></span><span style="font-size:12px;color:#5f6368" class="ms-1">— <?= h($t[1]) ?></span><i class="fa-solid fa-chevron-right ms-auto" style="font-size:12px;color:#9aa0a6"></i></a>
    <?php endforeach; ?>
  </div>
  <p style="font-size:12px;color:#9aa0a6" class="mt-3">Adapted from admin_owner_portal/index.php + mobile host_dashboard.dart — web + mobile share /api/properties & /api/bookings.</p>
</div>
<?= $this->element('footer', ['skin'=>'skin-light-footer']) ?>
