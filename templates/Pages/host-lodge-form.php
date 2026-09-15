<?php $this->assign('title', 'Edit Lodge | FastNetStays'); ?>
<?= $this->element('navbar') ?>
<style>.host-input{width:100%;height:44px;border:1px solid #e8eaed;border-radius:12px;padding:0 12px}.host-card{border:1px solid #e8eaed;border-radius:16px;box-shadow:0 6px 16px rgba(0,0,0,.05);background:#fff}.chip{display:inline-flex;align-items:center;gap:6px;background:#F0F3FF;border:1px solid #e8eaed;border-radius:9999px;padding:6px 10px;font-size:12px}</style>
<div class="container py-4" style="max-width:780px">
  <div class="d-flex justify-content-between align-items-center mb-3"><h1 style="font-size:20px;font-weight:800">Edit Lodge — <?= h($property['name'] ?? '—') ?></h1><a href="<?= $this->Url->build('/host/listings') ?>" class="btn" style="border:1px solid #e8eaed;border-radius:9999px">Back</a></div>
  <p style="font-size:12px;color:#5f6368">Port of admin_owner_portal/edit-lodge.php — PUT /properties/{id} + gallery + amenities chips</p>

  <?= $this->Form->create(null, ['url'=>['action'=>'editLodge', $property['id'] ?? '']]) ?>
  <div class="host-card p-3 d-grid gap-3">
    <div class="row g-2">
      <div class="col-md-8"><label style="font-size:11px;font-weight:700;color:#5f6368">Name *</label><input name="name" value="<?= h($property['name'] ?? '') ?>" class="host-input" required></div>
      <div class="col-md-4"><label style="font-size:11px;font-weight:700;color:#5f6368">City</label><input name="city" value="<?= h($property['city'] ?? '') ?>" class="host-input"></div>
    </div>
    <div class="row g-2">
      <div class="col-md-6"><label style="font-size:11px;font-weight:700;color:#5f6368">Area</label><input name="area" value="<?= h($property['area'] ?? '') ?>" class="host-input"></div>
      <div class="col-md-6"><label style="font-size:11px;font-weight:700;color:#5f6368">Price / night TSh *</label><input name="price_per_night" type="number" value="<?= h($property['price_per_night'] ?? '') ?>" class="host-input" required></div>
    </div>
    <div><label style="font-size:11px;font-weight:700;color:#5f6368">Address</label><input name="address" value="<?= h($property['address'] ?? '') ?>" class="host-input"></div>
    <div><label style="font-size:11px;font-weight:700;color:#5f6368">Description</label><textarea name="description" class="host-input" style="height:90px;padding:10px"><?= h($property['description'] ?? '') ?></textarea></div>
    <div><label style="font-size:11px;font-weight:700;color:#5f6368">Cover image URL</label><input name="image_url" value="<?= h($property['image_url'] ?? $property['primary_image_url'] ?? '') ?>" class="host-input" placeholder="https://..."></div>

    <div>
      <label style="font-size:11px;font-weight:700;color:#5f6368">Amenities</label>
      <div id="chipBox" class="d-flex flex-wrap gap-2 mb-2">
        <?php $amenities = $property['amenities'] ?? []; if (is_string($amenities)) $amenities = array_filter(array_map('trim', explode(',', $amenities))); foreach ((array)$amenities as $a): ?><span class="chip"><?= h($a) ?> <a href="#" onclick="this.parentElement.remove();return false" style="color:#dc2626;text-decoration:none">×</a><input type="hidden" name="amenities[]" value="<?= h($a) ?>"></span><?php endforeach; ?>
      </div>
      <div class="d-flex gap-2">
        <input id="chipInput" class="form-control" style="border-radius:12px;height:44px" placeholder="Add amenity (Enter)">
        <button type="button" class="btn" style="border:1px solid #e8eaed;border-radius:12px" onclick="addChip()">Add</button>
      </div>
      <div class="d-flex gap-1 flex-wrap mt-2"><?php foreach (['Wifi','Pool','Parking','Kitchen','AC','Gym','Spa','Beach'] as $q): ?><button type="button" class="btn btn-sm" style="border:1px solid #e8eaed;border-radius:9999px;background:#F8FAFC" onclick="addChipVal('<?= $q ?>')"><?= $q ?> +</button><?php endforeach; ?></div>
    </div>

    <div class="d-flex gap-2">
      <button class="btn" style="background:#2563EB;color:#fff;border-radius:9999px;padding:12px 18px;font-weight:800;flex:1">Save Lodge</button>
      <a href="<?= $this->Url->build('/host/calendar/'.$property['id']) ?>" class="btn" style="border:1px solid #e8eaed;border-radius:9999px;padding:12px 18px;flex:1;text-align:center">Calendar</a>
    </div>
  </div>
  <?= $this->Form->end() ?>

  <?php if (!empty($rooms)): ?><div class="host-card p-3 mt-3"><b style="font-size:13px">Rooms in this lodge (<?= count($rooms) ?>)</b><div class="d-flex gap-2 flex-wrap mt-2"><?php foreach ($rooms as $r): ?><span class="chip"><?= h($r['room_number'] ?? $r['name'] ?? 'Room') ?> — TSh <?= number_format((float)($r['price'] ?? 0)) ?></span><?php endforeach; ?></div></div><?php endif; ?>
</div>
<script>function addChipVal(v){document.getElementById('chipInput').value=v;addChip()}function addChip(){const i=document.getElementById('chipInput'),v=i.value.trim();if(!v)return;const b=document.getElementById('chipBox'),s=document.createElement('span');s.className='chip';s.innerHTML=`${v} <a href="#" onclick="this.parentElement.remove();return false" style="color:#dc2626;text-decoration:none">×</a><input type="hidden" name="amenities[]" value="${v.replace(/"/g,'&quot;')}">`;b.appendChild(s);i.value=''}document.getElementById('chipInput').addEventListener('keydown',e=>{if(e.key==='Enter'){e.preventDefault();addChip()}})</script>
<?= $this->element('footer', ['skin'=>'skin-light-footer']) ?>
