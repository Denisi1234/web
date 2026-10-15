<?php
$this->assign('title', 'Guest Reviews');
$this->assign('portal_title', 'Guest reviews');
$this->assign('page_actions', '<span style="font-size:13px;color:var(--p-text-2)">' . count($reviews ?? []) . ' total</span>');
?>
<?php if (!empty($backendError) && empty($reviews)): ?>
  <div class="p-card mb-3" style="border-left:3px solid #f1c21b">
    <div style="font-size:14px;font-weight:600">Couldn't reach the server</div>
    <div style="font-size:13px;color:var(--p-text-2)">Reviews couldn't be loaded right now — this is a connection issue, not an empty inbox.</div>
    <div style="margin-top:12px"><a href="<?= $this->Url->build('/host/reviews') ?>" class="p-btn">Retry</a></div>
  </div>
<?php elseif (empty($reviews)): ?>
  <div class="p-card"><div class="p-empty">No reviews yet. Reviews from guests with paid stays at your properties will appear here.</div></div>
<?php else: ?>
  <div class="p-table-wrap">
    <div class="table-responsive"><table class="p-table">
      <thead><tr><th>Date</th><th>Guest</th><th>Property</th><th>Rating</th><th>Comment</th></tr></thead>
      <tbody>
        <?php foreach ($reviews as $r): $stars = (int)($r['rating'] ?? $r['stars'] ?? 0); ?>
        <tr>
          <td style="font-size:12px;color:var(--p-text-2)"><?= h(substr((string)($r['created_at'] ?? $r['date'] ?? ''), 0, 10)) ?></td>
          <td><?= h($r['customer_name'] ?? $r['user_name'] ?? $r['user']['name'] ?? $r['name'] ?? 'Guest') ?></td>
          <td><?= h($r['property_name'] ?? $r['lodge_name'] ?? '') ?></td>
          <td style="white-space:nowrap" aria-label="<?= $stars ?> out of 5"><?php for ($i = 0; $i < 5; $i++): ?><i class="fa-solid fa-star" style="color:<?= $i < $stars ? '#f59e0b' : '#e0e0e0' ?>;font-size:12px"></i><?php endfor; ?></td>
          <td style="max-width:380px;white-space:normal"><?= h($r['comment'] ?? $r['review'] ?? $r['text'] ?? '') ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table></div>
  </div>
<?php endif; ?>
