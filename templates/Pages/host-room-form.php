<?php $this->assign('title', ($isEdit ?? false) ? 'Edit Room | FastNetStays' : 'Add Room | FastNetStays'); ?>
<?= $this->element('navbar') ?>
<style>.host-input{width:100%;height:44px;border:1px solid #e8eaed;border-radius:12px;padding:0 12px}.host-card{border:1px solid #e8eaed;border-radius:16px;box-shadow:0 6px 16px rgba(0,0,0,.05);background:#fff}</style>
<div class="container py-4" style="max-width:720px">
  <div class="d-flex justify-content-between align-items-center mb-3"><h1 style="font-size:20px;font-weight:800"><?= ($isEdit ?? false) ? 'Edit Room' : 'Add Room' ?></h1><a href="<?= $this->Url->build('/host/rooms') ?>" class="btn" style="border:1px solid #e8eaed;border-radius:9999px">Back</a></div>
  <p style="font-size:12px;color:#5f6368">Port of admin_owner_portal/add-room.php + room-editor.js — <?= ($isEdit ?? false) ? 'PUT /rooms/{id}' : 'POST /properties/{id}/rooms' ?> (min 4 photos via dropzone pattern).</p>

  <?php $room = $room ?? []; $propId = $room['property_id'] ?? ($properties[0]['id'] ?? ''); ?>
  <?= $this->Form->create(null, ['url'=> $isEdit ? ['action'=>'editRoom', $room['id'] ?? ''] : ['action'=>'addRoom']]) ?>
  <div class="host-card p-3 d-grid gap-3">
    <div><label style="font-size:11px;font-weight:700;color:#5f6368">Property *</label><select name="property_id" class="form-select" style="border-radius:12px;height:44px" <?= ($isEdit ?? false) ? 'disabled' : '' ?>><?php foreach (($properties ?? []) as $p): ?><option value="<?= h($p['id']) ?>" <?= (int)$propId===(int)$p['id']?'selected':'' ?>><?= h($p['name']) ?> — <?= h($p['city'] ?? '') ?></option><?php endforeach; ?></select><?php if ($isEdit): ?><input type="hidden" name="property_id" value="<?= h($propId) ?>"><?php endif; ?></div>
    <div class="row g-2">
      <div class="col-6"><label style="font-size:11px;font-weight:700;color:#5f6368">Room number *</label><input name="room_number" value="<?= h($room['room_number'] ?? $room['name'] ?? '') ?>" class="host-input" placeholder="e.g. 101" required></div>
      <div class="col-6"><label style="font-size:11px;font-weight:700;color:#5f6368">Room type</label><select name="room_type" class="form-select" style="border-radius:12px;height:44px"><option <?= ($room['room_type'] ?? '')==='Deluxe'?'selected':'' ?>>Deluxe</option><option <?= ($room['room_type'] ?? 'Standard')==='Standard'?'selected':'' ?>>Standard</option><option <?= ($room['room_type'] ?? '')==='Suite'?'selected':'' ?>>Suite</option><option <?= ($room['room_type'] ?? '')==='Executive'?'selected':'' ?>>Executive</option></select></div>
    </div>
    <div class="row g-2">
      <div class="col-12 col-sm-4"><label style="font-size:11px;font-weight:700;color:#5f6368">Floor</label><input name="floor" value="<?= h($room['floor'] ?? '') ?>" class="host-input" placeholder="1"></div>
      <div class="col-12 col-sm-4"><label style="font-size:11px;font-weight:700;color:#5f6368">Price TSh *</label><input name="price" type="number" value="<?= h($room['price'] ?? $room['customer_price'] ?? '') ?>" class="host-input" required></div>
      <div class="col-12 col-sm-4"><label style="font-size:11px;font-weight:700;color:#5f6368">Status</label><select name="status" class="form-select" style="border-radius:12px;height:44px"><option value="available" <?= strtolower($room['status'] ?? 'available')==='available'?'selected':'' ?>>Available</option><option value="maintenance" <?= strtolower($room['status'] ?? '')==='maintenance'?'selected':'' ?>>Maintenance</option></select></div>
    </div>
    <div class="row g-2">
      <div class="col-6 col-sm-4"><label style="font-size:11px;font-weight:700;color:#5f6368">Max adults</label><input name="max_adults" type="number" value="<?= h($room['max_adults'] ?? $room['capacity'] ?? 2) ?>" class="host-input"></div>
      <div class="col-6 col-sm-4"><label style="font-size:11px;font-weight:700;color:#5f6368">Max children</label><input name="max_children" type="number" value="<?= h($room['max_children'] ?? 0) ?>" class="host-input"></div>
      <div class="col-12 col-sm-4"><label style="font-size:11px;font-weight:700;color:#5f6368">Beds</label><input name="number_of_beds" type="number" value="<?= h($room['number_of_beds'] ?? 1) ?>" class="host-input"></div>
    </div>
    <div class="row g-2">
      <div class="col-6"><label style="font-size:11px;font-weight:700;color:#5f6368">Bed config</label><input name="bed_configuration" value="<?= h($room['bed_configuration'] ?? '') ?>" class="host-input" placeholder="King / Twin"></div>
      <div class="col-6"><label style="font-size:11px;font-weight:700;color:#5f6368">Room size</label><input name="room_size" value="<?= h($room['room_size'] ?? '') ?>" class="host-input" placeholder="28 sqm"></div>
    </div>
    <div><label style="font-size:11px;font-weight:700;color:#5f6368">Description</label><textarea name="description" class="host-input" style="height:80px;padding:10px" placeholder="Room description"><?= h($room['description'] ?? '') ?></textarea></div>
    <div><label style="font-size:11px;font-weight:700;color:#5f6368">Amenities (comma separated)</label><input name="amenities[0]" value="<?= h(implode(', ', (array)($room['amenities'] ?? []))) ?>" class="host-input" placeholder="Wifi, AC, TV, Mini bar" oninput="this.name='amenities'; this.value.split(',').length && (this._ph=true)"><small style="font-size:11px;color:#5f6368">Portal had 5 fixed checkboxes — free text now, backend accepts array.</small></div>
    <div>
      <label style="font-size:11px;font-weight:700;color:#5f6368">Photos (URLs, ≥4 for portal parity — paste or use upload)</label>
      <?php $photos = $room['photos'] ?? $room['images'] ?? []; if (is_string($photos)) $photos = [$photos]; $photoStr = is_array($photos) ? implode("\n", array_map(fn($p)=> is_array($p)?($p['url']??''): (string)$p, $photos)) : ''; ?>
      <textarea name="photos_raw" id="photos_raw" class="host-input" style="height:90px;padding:10px" placeholder="https://.../img1.jpg&#10;https://.../img2.jpg"><?= h($photoStr) ?></textarea>
      <input type="hidden" name="photos" id="photos_hidden">
      <small style="font-size:11px;color:#9aa0a6">On submit, split by newline into <code>photos[]</code>. Use /api/upload separately if needed.</small>
    </div>
    <button class="btn" style="background:#2563EB;color:#fff;border-radius:9999px;padding:12px;font-weight:800"><?= ($isEdit ?? false) ? 'Update Room' : 'Create Room' ?></button>
  </div>
  <?= $this->Form->end() ?>
</div>
<script>document.querySelector('form').addEventListener('submit', function(e){ const raw=document.getElementById('photos_raw').value; const arr=raw.split(/[\n,]+/).map(s=>s.trim()).filter(Boolean); const h=document.getElementById('photos_hidden'); // replace with multiple inputs const form=e.target; arr.forEach(v=>{ const i=document.createElement('input'); i.type='hidden'; i.name='photos[]'; i.value=v; form.appendChild(i); }); });</script>
<?= $this->element('footer', ['skin'=>'skin-light-footer']) ?>
