<?php $this->assign('title', 'Onboard Lodge | FastNetStays'); ?>
<?= $this->element('navbar') ?>
<style>.host-input{width:100%;height:44px;border:1px solid #e8eaed;border-radius:12px;padding:0 12px}.host-card{border:1px solid #e8eaed;border-radius:16px;box-shadow:0 6px 16px rgba(0,0,0,.05);background:#fff}.step{width:36px;height:36px;border-radius:9999px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:13px}</style>
<div class="container py-4" style="max-width:780px">
  <h1 style="font-size:20px;font-weight:800">Onboard New Lodge</h1><p style="font-size:12px;color:#5f6368">Port of admin_owner_portal/onboarding.php 7-step wizard → POST /properties (Mapbox + rooms bulk later)</p>

  <div class="d-flex gap-2 mb-3 flex-wrap">
    <?php for($i=1;$i<=4;$i++): ?><span class="step" style="background:<?= $i===1?'#2563EB;color:#fff':'#F0F3FF;color:#5f6368;border:1px solid #e8eaed' ?>"><?= $i ?></span><?php endfor; ?>
    <span style="font-size:11px;color:#5f6368;align-self:center">Steps: 1 Info → 2 Location → 3 Media → 4 Rooms (simplified working flow)</span>
  </div>

  <?= $this->Form->create(null, ['url'=>['action'=>'onboarding']]) ?>
  <div class="host-card p-3 d-grid gap-3">
    <div class="row g-2">
      <div class="col-md-8"><label style="font-size:11px;font-weight:700;color:#5f6368">Property name *</label><input name="name" class="host-input" required placeholder="Sunrise Lodge"></div>
      <div class="col-md-4"><label style="font-size:11px;font-weight:700;color:#5f6368">Type</label><select name="type" class="form-select" style="border-radius:12px;height:44px"><option>Lodge</option><option>Hotel</option><option>Apartment</option></select></div>
    </div>
    <div class="row g-2">
      <div class="col-md-6"><label style="font-size:11px;font-weight:700;color:#5f6368">City *</label><input name="city" class="host-input" required placeholder="Arusha"></div>
      <div class="col-md-6"><label style="font-size:11px;font-weight:700;color:#5f6368">Area</label><input name="area" class="host-input" placeholder="Njiro"></div>
    </div>
    <div><label style="font-size:11px;font-weight:700;color:#5f6368">Address</label><input name="address" class="host-input" placeholder="Plot 123, Njiro Road"></div>
    <div><label style="font-size:11px;font-weight:700;color:#5f6368">Description</label><textarea name="description" class="host-input" style="height:80px;padding:10px" placeholder="Describe the lodge"></textarea></div>
    <div class="row g-2">
      <div class="col-md-4"><label style="font-size:11px;font-weight:700;color:#5f6368">Price / night TSh *</label><input name="price_per_night" type="number" class="host-input" required placeholder="150000"></div>
      <div class="col-md-4"><label style="font-size:11px;font-weight:700;color:#5f6368">Latitude</label><input name="latitude" class="host-input" value="-6.7924"></div>
      <div class="col-md-4"><label style="font-size:11px;font-weight:700;color:#5f6368">Longitude</label><input name="longitude" class="host-input" value="39.2083"></div>
    </div>
    <div><label style="font-size:11px;font-weight:700;color:#5f6368">Cover image URL</label><input name="image_url" class="host-input" placeholder="https://..."></div>
    <div><label style="font-size:11px;font-weight:700;color:#5f6368">Amenities (comma separated)</label><input name="amenities_raw" id="amen_raw" class="host-input" placeholder="Wifi, Pool, Parking"></div>
    <input type="hidden" name="amenities" id="amen_hidden">
    <button class="btn" style="background:#2563EB;color:#fff;border-radius:9999px;padding:12px;font-weight:800">Create & Go to Rooms</button>
  </div>
  <?= $this->Form->end() ?>
  <p style="font-size:11px;color:#9aa0a6" class="mt-2">After create → /host/rooms/add to bulk-add rooms. Full Mapbox picker = <code>MAPBOX_TOKEN</code> in layout default if needed.</p>
</div>
<script>document.querySelector('form').addEventListener('submit', function(e){ const v=document.getElementById('amen_raw').value; const arr=v.split(',').map(s=>s.trim()).filter(Boolean); const f=e.target; arr.forEach(val=>{ const i=document.createElement('input'); i.type='hidden'; i.name='amenities[]'; i.value=val; f.appendChild(i); });});</script>
<?= $this->element('footer', ['skin'=>'skin-light-footer']) ?>
