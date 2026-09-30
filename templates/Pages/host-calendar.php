<?php
$this->assign('title', 'Calendar — ' . ($property['name'] ?? 'Property'));
$this->assign('portal_title', ($property['name'] ?? 'Property') . ' — Calendar');
$this->assign('page_actions', '<a href="' . $this->Url->build('/host/listings') . '" class="p-btn ghost">Back to listings</a>');
?>
<style>
.cds-day-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:8px}
.cds-day{aspect-ratio:1;display:flex;flex-direction:column;align-items:center;justify-content:center;font-weight:600;border:1px solid var(--p-border);font-size:13px}
.cds-day small{font-size:10px;font-weight:400;color:inherit;opacity:.8}
.cds-day.ok{background:#defbe6;color:#0e6027;border-color:#a7e8b7}
.cds-day.off{background:#fdecea;color:#a2191f;border-color:#f4b0b1}
@media(max-width:480px){.cds-day-grid{gap:6px}.cds-day{font-size:11px}}
</style>
<?php if (empty($rooms)): ?>
  <div class="p-card"><div class="p-empty">No rooms for this property yet. Add rooms first.</div></div>
<?php else: ?>
  <?php foreach ($rooms as $rm):
    $rid = (int)($rm['id'] ?? 0);
    $rname = h($rm['name'] ?? $rm['room_number'] ?? 'Room');
    $rprice = (float)($rm['customer_price'] ?? $rm['price'] ?? 0);
    $rstatus = trim((string)($rm['status'] ?? 'available'));
    $isBlocked = in_array(strtolower($rstatus), ['maintenance', 'blocked'], true);
  ?>
  <div class="p-card mb-3">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:12px">
      <div style="font-size:15px;font-weight:600"><?= $rname ?> <span style="font-size:12px;font-weight:400;color:var(--p-text-2)">#<?= $rid ?></span></div>
      <span class="p-badge <?= $isBlocked ? 'red' : 'green' ?>"><?= $isBlocked ? 'Blocked' : 'Available' ?></span>
    </div>
    <div class="cds-day-grid mb-3" role="grid" aria-label="Availability for <?= $rname ?>">
      <?php for ($d = 1; $d <= 31; $d++): $blocked = ($d % 7 === 0); ?>
        <div class="cds-day <?= $blocked ? 'off' : 'ok' ?>" title="Day <?= $d ?> — <?= $blocked ? 'Blocked' : 'Available' ?>"><span><?= $d ?></span><small>TSh <?= number_format($rprice / 1000, 1) ?>k</small></div>
      <?php endfor; ?>
    </div>
    <?= $this->Form->create(null, ['url' => ['action' => 'calendar', $property['id'] ?? null], 'style' => 'display:flex;gap:8px;flex-wrap:wrap;align-items:end']) ?>
      <?= $this->Form->hidden('room_id', ['value' => $rid]) ?>
      <div style="flex:1;min-width:160px">
        <label style="font-size:12px;font-weight:600;color:var(--p-text-2)">Price TSh</label>
        <input name="price" type="number" value="<?= h($rprice) ?>" class="form-control" style="min-height:40px">
      </div>
      <div style="min-width:180px">
        <label style="font-size:12px;font-weight:600;color:var(--p-text-2)">Status</label>
        <select name="status" class="form-select" style="min-height:40px">
          <option value="available" <?= $isBlocked ? '' : 'selected' ?>>Available</option>
          <option value="maintenance" <?= $isBlocked ? 'selected' : '' ?>>Blocked / Maintenance</option>
        </select>
      </div>
      <button class="p-btn" style="min-height:40px">Update</button>
    <?= $this->Form->end() ?>
  </div>
  <?php endforeach; ?>
<?php endif; ?>
