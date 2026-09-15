<?php $this->assign('title', 'Reviews | FastNetStays'); ?>
<?= $this->element('navbar') ?>
<style>.host-card{border:1px solid #e8eaed;border-radius:16px;box-shadow:0 6px 16px rgba(0,0,0,.05);background:#fff}</style>
<div class="container py-4" style="max-width:1180px">
  <h1 style="font-size:20px;font-weight:800">Platform Reviews</h1><p style="font-size:12px;color:#5f6368">Port of reviews.php — GET /admin/reviews</p>

  <?php if (empty($reviews)): ?><div class="host-card p-5 text-center" style="color:#5f6368">No reviews yet.</div>
  <?php else: ?>
  <div class="host-card p-0 overflow-hidden">
    <div class="table-responsive"><table class="table align-middle mb-0" style="font-size:13px"><thead style="background:#F8FAFC;font-size:11px;color:#5f6368"><tr><th>Order</th><th>Date</th><th>Customer</th><th>Rating</th><th>Comment</th></tr></thead><tbody>
      <?php foreach ($reviews as $r): $stars=(int)($r['rating'] ?? $r['stars'] ?? 0); ?>
      <tr>
        <td style="font-weight:700"><?= h($r['order_id'] ?? $r['booking_reference'] ?? $r['id'] ?? '—') ?></td>
        <td style="font-size:12px;color:#5f6368"><?= h(substr((string)($r['created_at'] ?? $r['date'] ?? ''),0,10)) ?></td>
        <td><?= h($r['customer_name'] ?? $r['user']['name'] ?? $r['name'] ?? 'Guest') ?></td>
        <td><?php for($i=0;$i<5;$i++): ?><i class="fa-solid fa-star" style="color:<?= $i<$stars?'#f59e0b':'#e8eaed' ?>;font-size:12px"></i><?php endfor; ?></td>
        <td style="max-width:360px;white-space:normal"><?= h($r['comment'] ?? $r['review'] ?? $r['text'] ?? '') ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody></table></div>
  </div>
  <?php endif; ?>
</div>
<?= $this->element('footer', ['skin'=>'skin-light-footer']) ?>
