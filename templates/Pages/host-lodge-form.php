<?php
$this->assign('title', 'Edit Lodge');
$this->assign('portal_title', 'Edit lodge — ' . ($property['name'] ?? ''));
$this->assign('page_actions', '<a href="' . $this->Url->build('/host/listings') . '" class="p-btn ghost">Back to listings</a>');
?>
<style>
.cds-chip{display:inline-flex;align-items:center;gap:6px;background:#edf5ff;border:1px solid #d0e2ff;color:#0043ce;font-size:12px;font-weight:600;padding:5px 10px}
.cds-chip a{color:#da1e28;text-decoration:none;font-weight:700}
</style>
<div class="p-card" style="max-width:780px">
  <h3><?= h($property['name'] ?? 'Lodge') ?></h3>
  <div class="sub">Updates via PUT /properties/{id}.</div>
  <?= $this->Form->create(null, ['url' => ['action' => 'editLodge', $property['id'] ?? ''], 'style' => 'display:grid;gap:12px;margin-top:16px']) ?>
    <div class="row g-2">
      <div class="col-md-8"><label style="font-size:12px;font-weight:600;color:var(--p-text-2)">Name *</label><input name="name" value="<?= h($property['name'] ?? '') ?>" class="form-control" style="min-height:40px" required></div>
      <div class="col-md-4"><label style="font-size:12px;font-weight:600;color:var(--p-text-2)">City</label><input name="city" value="<?= h($property['city'] ?? '') ?>" class="form-control" style="min-height:40px"></div>
    </div>
    <div class="row g-2">
      <div class="col-md-6"><label style="font-size:12px;font-weight:600;color:var(--p-text-2)">Area</label><input name="area" value="<?= h($property['area'] ?? '') ?>" class="form-control" style="min-height:40px"></div>
      <div class="col-md-6"><label style="font-size:12px;font-weight:600;color:var(--p-text-2)">Price / night TSh *</label><input name="price_per_night" type="number" value="<?= h($property['price_per_night'] ?? '') ?>" class="form-control" style="min-height:40px" required></div>
    </div>
    <div><label style="font-size:12px;font-weight:600;color:var(--p-text-2)">Address</label><input name="address" value="<?= h($property['address'] ?? '') ?>" class="form-control" style="min-height:40px"></div>
    <div><label style="font-size:12px;font-weight:600;color:var(--p-text-2)">Description</label><textarea name="description" class="form-control" style="min-height:90px"><?= h($property['description'] ?? '') ?></textarea></div>
    <div><label style="font-size:12px;font-weight:600;color:var(--p-text-2)">Cover image URL</label><input name="image_url" value="<?= h($property['image_url'] ?? $property['primary_image_url'] ?? '') ?>" class="form-control" style="min-height:40px" placeholder="https://…"></div>

    <div>
      <label style="font-size:12px;font-weight:600;color:var(--p-text-2)">Amenities</label>
      <div id="chipBox" style="display:flex;flex-wrap:wrap;gap:8px;margin:8px 0">
        <?php $amenities = $property['amenities'] ?? []; if (is_string($amenities)) $amenities = array_filter(array_map('trim', explode(',', $amenities))); foreach ((array)$amenities as $a): ?><span class="cds-chip"><?= h($a) ?> <a href="#" onclick="this.parentElement.remove();return false" aria-label="Remove">×</a><input type="hidden" name="amenities[]" value="<?= h($a) ?>"></span><?php endforeach; ?>
      </div>
      <div style="display:flex;gap:8px">
        <input id="chipInput" class="form-control" style="min-height:40px" placeholder="Add amenity (Enter)">
        <button type="button" class="p-btn ghost" onclick="addChip()">Add</button>
      </div>
      <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:8px"><?php foreach (['Wifi', 'Pool', 'Parking', 'Kitchen', 'AC', 'Gym', 'Spa', 'Beach'] as $q): ?><button type="button" class="p-btn ghost" style="min-height:32px;font-size:12px" onclick="addChipVal('<?= $q ?>')"><?= $q ?> +</button><?php endforeach; ?></div>
    </div>

    <div style="display:flex;gap:8px;flex-wrap:wrap">
      <button class="p-btn" style="flex:1;justify-content:center">Save lodge</button>
      <a href="<?= $this->Url->build('/host/calendar/' . $property['id']) ?>" class="p-btn ghost" style="flex:1;justify-content:center">Calendar</a>
    </div>
  <?= $this->Form->end() ?>
</div>

<?php if (!empty($rooms)): ?>
<div class="p-card mt-3" style="max-width:780px">
  <h3>Rooms in this lodge (<?= count($rooms) ?>)</h3>
  <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px"><?php foreach ($rooms as $r): ?><span class="cds-chip"><?= h($r['room_number'] ?? $r['name'] ?? 'Room') ?> — TSh <?= number_format((float)($r['price'] ?? 0)) ?></span><?php endforeach; ?></div>
</div>
<?php endif; ?>
<script>function addChipVal(v){document.getElementById('chipInput').value=v;addChip()}function addChip(){const i=document.getElementById('chipInput'),v=i.value.trim();if(!v)return;const b=document.getElementById('chipBox'),s=document.createElement('span');s.className='cds-chip';s.innerHTML=`${v} <a href="#" onclick="this.parentElement.remove();return false">×</a><input type="hidden" name="amenities[]" value="${v.replace(/"/g,'&quot;')}">`;b.appendChild(s);i.value=''}document.getElementById('chipInput').addEventListener('keydown',e=>{if(e.key==='Enter'){e.preventDefault();addChip()}})</script>
