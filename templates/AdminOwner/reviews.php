<?php
$this->assign('title', 'Reviews');
$this->assign('portal_title', 'Reviews');
$this->assign('page_actions', '<span style="font-size:13px;color:var(--p-text-2)">' . count($reviews ?? []) . ' total</span>');
?>
<?php if (empty($reviews)): ?>
  <div class="p-card"><div class="p-empty">No reviews yet.</div></div>
<?php else: ?>
  <div class="p-table-wrap">
    <div class="table-responsive"><table class="p-table">
      <thead><tr><th>Order</th><th>Date</th><th>Customer</th><th>Rating</th><th>Comment</th></tr></thead>
      <tbody>
        <?php foreach ($reviews as $r): $stars = (int)($r['rating'] ?? $r['stars'] ?? 0); ?>
        <tr>
          <td><strong><?= h($r['order_id'] ?? $r['booking_reference'] ?? $r['id'] ?? '—') ?></strong></td>
          <td style="font-size:12px;color:var(--p-text-2)"><?= h(substr((string)($r['created_at'] ?? $r['date'] ?? ''), 0, 10)) ?></td>
          <td><?= h($r['customer_name'] ?? $r['user']['name'] ?? $r['name'] ?? 'Guest') ?></td>
          <td style="white-space:nowrap" aria-label="<?= $stars ?> out of 5"><?php for ($i = 0; $i < 5; $i++): ?><i class="fa-solid fa-star" style="color:<?= $i < $stars ? '#f59e0b' : '#e0e0e0' ?>;font-size:12px"></i><?php endfor; ?></td>
          <td style="max-width:380px;white-space:normal"><?= h($r['comment'] ?? $r['review'] ?? $r['text'] ?? '') ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table></div>
  </div>
<?php endif; ?>
