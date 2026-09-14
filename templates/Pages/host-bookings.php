<?php $this->assign('title','Host Bookings | FastNetStays'); ?>
<?= $this->element('navbar') ?>
<style>.host-card{border:1px solid #e8eaed;border-radius:16px;padding:16px;box-shadow:0 4px 12px rgba(0,0,0,.04);background:#fff}</style>
<div class="container py-4" style="max-width:1180px">
  <h1 style="font-size:20px;font-weight:800">Guest Bookings</h1><p style="font-size:12px;color:#5f6368">GET /admin/bookings (portal guest-list.php:10) + mobile host_bookings.dart — web + mobile share /api/bookings.</p>
  <?php if(empty($bookings)): ?><div class="host-card text-center" style="color:#5f6368">No bookings yet.</div>
  <?php else: foreach($bookings as $b): ?><div class="host-card mb-2 d-flex justify-content-between align-items-center flex-wrap gap-2"><span style="font-weight:700"><?= h($b['booking_code']??$b['id']??'—') ?></span><span style="font-size:12px;color:#5f6368"><?= h($b['guest_name']??$b['guest']['name']??'Guest') ?> · <?= h($b['property']['name']??'') ?></span><span style="background:#F0F3FF;color:#2563EB;border-radius:9999px;padding:4px 10px;font-size:11px;font-weight:700"><?= h($b['status']??$b['payment_status']??'pending') ?></span><span style="font-weight:800;color:#C2410C">TSh <?= number_format((float)($b['total_price']??$b['total_amount']??0)) ?></span></div><?php endforeach; endif; ?>
</div>
<?= $this->element('footer',['skin'=>'skin-light-footer']) ?>
