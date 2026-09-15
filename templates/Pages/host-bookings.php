<?php $this->assign('title','Host Bookings | FastNetStays'); 
$tab = strtolower(trim((string)($this->getRequest()->getQuery('tab','all'))));
$tabs = ['all'=>'All','pending'=>'Pending','booked'=>'Booked','paid'=>'Paid','canceled'=>'Canceled','refunded'=>'Refunded'];
$filtered = $bookings ?? [];
if ($tab !== 'all') {
  $filtered = array_values(array_filter($bookings ?? [], fn($b)=> strtolower((string)($b['payment_status'] ?? $b['status'] ?? '')) === $tab || ($tab==='canceled' && in_array(strtolower((string)($b['status'] ?? '')), ['canceled','cancelled'])) ));
}
?>
<?= $this->element('navbar') ?>
<style>.host-card{border:1px solid #e8eaed;border-radius:16px;padding:16px;box-shadow:0 4px 12px rgba(0,0,0,.04);background:#fff}.tab-pill{border-radius:9999px;padding:6px 12px;font-size:12px;font-weight:700;border:1px solid #e8eaed;background:#fff;color:#5f6368}.tab-pill.active{background:#2563EB;color:#fff;border-color:#2563EB}</style>
<div class="container py-4" style="max-width:1180px">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div><h1 style="font-size:20px;font-weight:800">Guest Bookings</h1><p style="font-size:12px;color:#5f6368">Port of guest-list.php 5 tabs — GET /admin/bookings filtered by payment_status</p></div>
    <span style="background:#F0F3FF;color:#2563EB;border-radius:9999px;padding:6px 12px;font-size:11px;font-weight:700"><?= count($filtered) ?> / <?= count($bookings ?? []) ?></span>
  </div>

  <div class="d-flex gap-2 mb-3 flex-wrap">
    <?php foreach ($tabs as $k=>$label): $cnt = $k==='all' ? count($bookings ?? []) : count(array_filter($bookings ?? [], fn($b)=> strtolower((string)($b['payment_status'] ?? $b['status'] ?? '')) === $k || ($k==='canceled' && in_array(strtolower((string)($b['status'] ?? '')), ['canceled','cancelled'])))); ?>
    <a href="?tab=<?= $k ?>" class="tab-pill <?= $tab===$k?'active':'' ?>"><?= $label ?> (<?= $cnt ?>)</a>
    <?php endforeach; ?>
  </div>

  <?php if(empty($filtered)): ?><div class="host-card text-center" style="color:#5f6368">No <?= h($tab) ?> bookings.</div>
  <?php else: foreach($filtered as $b): ?><div class="host-card mb-2 d-flex justify-content-between align-items-center flex-wrap gap-2"><span style="font-weight:700"><?= h($b['booking_code']??$b['id']??'—') ?></span><span style="font-size:12px;color:#5f6368"><?= h($b['guest_name']??$b['guest']['name']??'Guest') ?> · <?= h($b['property']['name']??($b['room']['property']['name'] ?? '')) ?> · <?= h(substr((string)($b['check_in'] ?? ''),0,10)) ?>→<?= h(substr((string)($b['check_out'] ?? ''),0,10)) ?></span><span style="background:<?= strtolower($b['payment_status'] ?? '')==='paid'?'#dcfce7;color:#15803d':(strtolower($b['payment_status'] ?? '')==='pending'?'#fef3c7;color:#d97706':'#F0F3FF;color:#2563EB') ?>;border-radius:9999px;padding:4px 10px;font-size:11px;font-weight:700"><?= h($b['payment_status']??$b['status']??'pending') ?></span><span style="font-weight:800;color:#C2410C">TSh <?= number_format((float)($b['total_price']??$b['total_amount']??0)) ?></span></div><?php endforeach; endif; ?>
</div>
<?= $this->element('footer',['skin'=>'skin-light-footer']) ?>
