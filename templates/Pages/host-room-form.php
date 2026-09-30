<?php
$this->assign('title', ($isEdit ?? false) ? 'Edit Room' : 'Add Room');
$this->assign('portal_title', ($isEdit ?? false) ? 'Edit room' : 'Add room');
$this->assign('page_actions', '<a href="' . $this->Url->build('/host/rooms') . '" class="p-btn ghost">Back to rooms</a>');
$room = $room ?? [];
$propId = $room['property_id'] ?? ($properties[0]['id'] ?? '');
?>
<div class="p-card" style="max-width:720px">
  <h3><?= ($isEdit ?? false) ? 'Edit room' : 'New room' ?></h3>
  <div class="sub"><?= ($isEdit ?? false) ? 'Updates via PUT /rooms/{id}.' : 'Creates via POST /properties/{id}/rooms.' ?></div>
  <?= $this->Form->create(null, ['url' => $isEdit ? ['action' => 'editRoom', $room['id'] ?? ''] : ['action' => 'addRoom'], 'style' => 'display:grid;gap:12px;margin-top:16px']) ?>
    <div>
      <label style="font-size:12px;font-weight:600;color:var(--p-text-2)">Property *</label>
      <select name="property_id" class="form-select" style="min-height:40px" <?= ($isEdit ?? false) ? 'disabled' : '' ?>>
        <?php foreach (($properties ?? []) as $p): ?><option value="<?= h($p['id']) ?>" <?= (int)$propId === (int)$p['id'] ? 'selected' : '' ?>><?= h($p['name']) ?> — <?= h($p['city'] ?? '') ?></option><?php endforeach; ?>
      </select>
      <?php if ($isEdit): ?><input type="hidden" name="property_id" value="<?= h($propId) ?>"><?php endif; ?>
    </div>
    <div class="row g-2">
      <div class="col-6">
        <label style="font-size:12px;font-weight:600;color:var(--p-text-2)">Room number *</label>
        <input name="room_number" value="<?= h($room['room_number'] ?? $room['name'] ?? '') ?>" class="form-control" style="min-height:40px" placeholder="e.g. 101" required>
      </div>
      <div class="col-6">
        <label style="font-size:12px;font-weight:600;color:var(--p-text-2)">Room type</label>
        <select name="room_type" class="form-select" style="min-height:40px">
          <?php foreach (['Standard', 'Deluxe', 'Suite', 'Executive'] as $t): ?><option <?= ($room['room_type'] ?? 'Standard') === $t ? 'selected' : '' ?>><?= $t ?></option><?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="row g-2">
      <div class="col-12 col-sm-4"><label style="font-size:12px;font-weight:600;color:var(--p-text-2)">Floor</label><input name="floor" value="<?= h($room['floor'] ?? '') ?>" class="form-control" style="min-height:40px" placeholder="1"></div>
      <div class="col-12 col-sm-4"><label style="font-size:12px;font-weight:600;color:var(--p-text-2)">Price TSh *</label><input name="price" type="number" value="<?= h($room['price'] ?? $room['customer_price'] ?? '') ?>" class="form-control" style="min-height:40px" required></div>
      <div class="col-12 col-sm-4"><label style="font-size:12px;font-weight:600;color:var(--p-text-2)">Status</label><select name="status" class="form-select" style="min-height:40px"><option value="available" <?= strtolower($room['status'] ?? 'available') === 'available' ? 'selected' : '' ?>>Available</option><option value="maintenance" <?= strtolower($room['status'] ?? '') === 'maintenance' ? 'selected' : '' ?>>Maintenance</option></select></div>
    </div>
    <div class="row g-2">
      <div class="col-6 col-sm-4"><label style="font-size:12px;font-weight:600;color:var(--p-text-2)">Max adults</label><input name="max_adults" type="number" value="<?= h($room['max_adults'] ?? $room['capacity'] ?? 2) ?>" class="form-control" style="min-height:40px"></div>
      <div class="col-6 col-sm-4"><label style="font-size:12px;font-weight:600;color:var(--p-text-2)">Max children</label><input name="max_children" type="number" value="<?= h($room['max_children'] ?? 0) ?>" class="form-control" style="min-height:40px"></div>
      <div class="col-12 col-sm-4"><label style="font-size:12px;font-weight:600;color:var(--p-text-2)">Beds</label><input name="number_of_beds" type="number" value="<?= h($room['number_of_beds'] ?? 1) ?>" class="form-control" style="min-height:40px"></div>
    </div>
    <div class="row g-2">
      <div class="col-6"><label style="font-size:12px;font-weight:600;color:var(--p-text-2)">Bed config</label><input name="bed_configuration" value="<?= h($room['bed_configuration'] ?? '') ?>" class="form-control" style="min-height:40px" placeholder="King / Twin"></div>
      <div class="col-6"><label style="font-size:12px;font-weight:600;color:var(--p-text-2)">Room size</label><input name="room_size" value="<?= h($room['room_size'] ?? '') ?>" class="form-control" style="min-height:40px" placeholder="28 sqm"></div>
    </div>
    <div><label style="font-size:12px;font-weight:600;color:var(--p-text-2)">Description</label><textarea name="description" class="form-control" style="min-height:80px" placeholder="Room description"><?= h($room['description'] ?? '') ?></textarea></div>
    <div>
      <label style="font-size:12px;font-weight:600;color:var(--p-text-2)">Photos (one URL per line, ≥4 recommended)</label>
      <?php $photos = $room['photos'] ?? $room['images'] ?? []; if (is_string($photos)) $photos = [$photos]; $photoStr = is_array($photos) ? implode("\n", array_map(fn($p) => is_array($p) ? ($p['url'] ?? '') : (string)$p, $photos)) : ''; ?>
      <textarea name="photos_raw" id="photos_raw" class="form-control" style="min-height:90px" placeholder="https://…/img1.jpg&#10;https://…/img2.jpg"><?= h($photoStr) ?></textarea>
      <input type="hidden" name="photos" id="photos_hidden">
    </div>
    <button class="p-btn" style="justify-content:center"><?= ($isEdit ?? false) ? 'Update room' : 'Create room' ?></button>
  <?= $this->Form->end() ?>
</div>
<script>document.querySelector('form').addEventListener('submit', function(e){ const raw=document.getElementById('photos_raw').value; const arr=raw.split(/[\n,]+/).map(s=>s.trim()).filter(Boolean); const form=e.target; arr.forEach(v=>{ const i=document.createElement('input'); i.type='hidden'; i.name='photos[]'; i.value=v; form.appendChild(i); }); });</script>
