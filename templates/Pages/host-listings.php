<?php
$this->assign('title', 'My Properties');
$this->assign('portal_title', 'My properties');
$this->assign('page_actions', '<a href="' . $this->Url->build('/host/onboarding') . '" class="p-btn">Add property</a>');
?>
<style>
/* Host lodge cards — mirrors home Google-Hotels card language, portal Carbon skin */
.hp-list{display:grid;gap:12px}
.hp-card{display:flex;background:#fff;border:1px solid var(--p-border);overflow:hidden;text-decoration:none;color:inherit}
.hp-card:hover{border-color:#8d8d8d}
.hp-photo{position:relative;flex:0 0 220px;min-height:170px;background:var(--p-bg);overflow:hidden}
.hp-photo img{width:100%;height:100%;object-fit:cover;display:block;position:absolute;inset:0}
.hp-count{position:absolute;left:8px;bottom:8px;background:rgba(22,22,22,.78);color:#fff;font-size:11px;font-weight:600;padding:3px 8px}
.hp-body{flex:1;min-width:0;padding:14px 16px;display:flex;flex-direction:column;gap:6px}
.hp-top{display:flex;justify-content:space-between;gap:10px;align-items:flex-start}
.hp-name{font-size:16px;font-weight:600;color:var(--p-text);line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.hp-price{font-size:16px;font-weight:600;color:var(--p-text);white-space:nowrap}
.hp-price small{font-size:12px;font-weight:400;color:var(--p-text-2)}
.hp-rate{font-size:13px;color:var(--p-text-2)}
.hp-rate b{color:var(--p-text)}
.hp-loc{font-size:13px;color:var(--p-text-2);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.hp-rooms{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:6px;margin-top:2px}
.hp-rooms span{font-size:12px;color:var(--p-text-2);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.hp-rooms i{color:var(--p-blue);margin-right:5px;font-size:11px}
.hp-foot{display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap;margin-top:auto;padding-top:8px}
.hp-actions{display:flex;gap:8px;flex-wrap:wrap}
@media(max-width:640px){
  .hp-card{flex-direction:column}
  .hp-photo{flex:none;height:180px;min-height:0}
  .hp-rooms{grid-template-columns:repeat(2,minmax(0,1fr))}
}
@media (prefers-reduced-motion:reduce){.hp-card{transition:none}}
</style>
<?php if (!empty($backendError) && empty($properties)): ?>
  <div class="p-card mb-3" style="border-left:3px solid #f1c21b">
    <div style="font-size:14px;font-weight:600">Couldn't reach the server</div>
    <div style="font-size:13px;color:var(--p-text-2)">Your listings couldn't be loaded right now — this is a connection issue, not an empty account.</div>
    <div style="margin-top:12px"><a href="<?= $this->Url->build('/host/listings') ?>" class="p-btn">Retry</a></div>
  </div>
<?php elseif (empty($properties)): ?>
  <div class="p-card">
    <div class="p-empty">
      <div style="font-size:16px;font-weight:600;color:var(--p-text);margin-bottom:4px">No listings yet</div>
      <div style="margin-bottom:16px">Create your first property to start receiving bookings.</div>
      <a href="<?= $this->Url->build('/host/onboarding') ?>" class="p-btn">Add property</a>
    </div>
  </div>
<?php else: ?>
  <div style="font-size:13px;color:var(--p-text-2);margin-bottom:12px"><?= count($properties) ?> <?= count($properties) === 1 ? 'property' : 'properties' ?></div>
  <div class="hp-list">
    <?php foreach ($properties as $p):
      $pid = $p['id'] ?? '';
      $img = $p['image_url'] ?? $p['primary_image_url'] ?? $this->Url->build('/assets/img/hotel/hotel-1.jpg');
      $name = $p['name'] ?? 'Property';
      $loc = trim(trim((string)($p['area'] ?? '')) . ', ' . trim((string)($p['city'] ?? '')), ', ');
      $price = (float)($p['customer_price_per_night'] ?? $p['price_per_night'] ?? 0);
      $rating = isset($p['reviews_avg_rating']) && $p['reviews_avg_rating'] !== null ? number_format((float)$p['reviews_avg_rating'], 1) : null;
      $rcount = (int)($p['reviews_count'] ?? 0);
      $status = strtolower((string)($p['status'] ?? ''));
      if ($status === '') $status = 'pending review';
      $badge = $status === 'active' ? 'green' : (in_array($status, ['pending', 'pending review'], true) ? 'yellow' : 'blue');
      $rooms = (isset($p['rooms']) && is_array($p['rooms'])) ? $p['rooms'] : [];
      $typeCounts = [];
      foreach ($rooms as $rm) {
        $t = trim((string)($rm['room_type'] ?? $rm['room_type_id'] ?? $rm['type'] ?? 'Standard'));
        if ($t === '') $t = 'Standard';
        $typeCounts[$t] = ($typeCounts[$t] ?? 0) + 1;
      }
    ?>
    <article class="hp-card">
      <div class="hp-photo">
        <img src="<?= h($img) ?>" alt="<?= h($name) ?>" loading="lazy" decoding="async">
        <span class="hp-count"><?= count($rooms) ?> room<?= count($rooms) === 1 ? '' : 's' ?></span>
      </div>
      <div class="hp-body">
        <div class="hp-top">
          <div class="hp-name"><?= h($name) ?></div>
          <div class="hp-price">TSh <?= number_format($price) ?> <small>/ night</small></div>
        </div>
        <div class="hp-rate">
          <?php if ($rating !== null): ?><b><?= h($rating) ?></b> ★ (<?= $rcount ?>)&nbsp;·&nbsp;<?php endif; ?><span class="p-badge <?= $badge ?>"><?= h($status) ?></span>
        </div>
        <?php if ($loc !== ''): ?><div class="hp-loc"><?= h($loc) ?></div><?php endif; ?>
        <?php if (!empty($typeCounts)): ?>
        <div class="hp-rooms">
          <?php $shown = 0; foreach ($typeCounts as $t => $c): if ($shown++ >= 6) break; ?>
          <span><i class="fa-solid fa-bed"></i><?= h($t) ?> × <?= $c ?></span>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <div class="hp-foot">
          <span style="font-size:11px;color:var(--p-text-2)">#<?= h($pid) ?></span>
          <div class="hp-actions">
            <?php if ($pid !== ''): ?>
              <a href="<?= $this->Url->build('/host/calendar/' . $pid) ?>" class="p-btn" style="min-height:36px;font-size:13px">Calendar</a>
              <a href="<?= $this->Url->build('/host/lodge/' . $pid . '/edit') ?>" class="p-btn ghost" style="min-height:36px;font-size:13px">Edit</a>
            <?php endif; ?>
            <a href="<?= $this->Url->build('/host/rooms') ?>" class="p-btn ghost" style="min-height:36px;font-size:13px">Rooms</a>
          </div>
        </div>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
