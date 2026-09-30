<?php
$this->assign('title', 'Onboard Lodge');
$this->assign('portal_title', 'Add property');
$backendUrl = rtrim((string)\Cake\Core\Configure::read('App.backendApiUrl', 'http://127.0.0.1:8000/api'), '/');
$step = (int)($step ?? 1);
if ($step < 1 || $step > 5) $step = 1;
$d = is_array($draft ?? null) ? $draft : [];
$labels = [1 => 'Basics', 2 => 'Location', 3 => 'Photos', 4 => 'Rooms', 5 => 'Review'];
$roomTypes = ['Standard', 'Deluxe', 'Suite', 'Executive'];
$draftCats = (isset($d['roomCats']) && is_array($d['roomCats']) && !empty($d['roomCats'])) ? array_values($d['roomCats']) : [[]];
?>
<?= $this->Html->css('https://api.mapbox.com/mapbox-gl-js/v3.1.2/mapbox-gl.css') ?>
<style>
/* ═══════════════════════════════════════════════════════════════════
   IBM Carbon v11 · White theme · Host onboarding
   Tokens → components → nothing inline. Productive type, spacing scale.
   ═══════════════════════════════════════════════════════════════════ */
.obx{
  --cds-blue-60:#0f62fe; --cds-blue-70:#0043ce; --cds-blue-10:#edf5ff; --cds-blue-20:#d0e2ff;
  --cds-gray-100:#161616; --cds-gray-70:#525252; --cds-gray-60:#6f6f6f;
  --cds-gray-30:#c6c6c6; --cds-gray-20:#e0e0e0; --cds-gray-10:#f4f4f4; --cds-white:#ffffff;
  --cds-red-60:#da1e28; --cds-green-50:#0e6027; --cds-green-10:#defbe6; --cds-yellow-10:#fcf4d6;
  --cds-space-03:.5rem; --cds-space-04:.75rem; --cds-space-05:1rem; --cds-space-06:1.5rem;
  --cds-field-h:2.5rem; --cds-field-h-lg:2.75rem;
  font-family:'IBM Plex Sans','Inter',Roboto,Arial,sans-serif; color:var(--cds-gray-100);
  max-width:780px;
}
/* progress */
.ob-steps{display:flex;margin:var(--cds-space-05) 0;border:1px solid var(--cds-gray-20);background:var(--cds-white)}
.ob-steps a{flex:1;display:flex;align-items:center;gap:var(--cds-space-03);padding:.625rem .75rem;font-size:.75rem;font-weight:600;color:var(--cds-gray-70);text-decoration:none;border-right:1px solid var(--cds-gray-20)}
.ob-steps a:last-child{border-right:none}
.ob-steps a.cur{background:var(--cds-blue-10);color:var(--cds-blue-70)}
.ob-steps a.done{color:var(--cds-gray-100)}
.ob-steps .n{width:1.5rem;height:1.5rem;flex:none;display:inline-flex;align-items:center;justify-content:center;font-size:.75rem;background:var(--cds-gray-10);border:1px solid var(--cds-gray-20)}
.ob-steps a.cur .n{background:var(--cds-blue-60);border-color:var(--cds-blue-60);color:#fff}
.ob-steps a.done .n{background:var(--cds-green-10);border-color:#a7e8b7;color:var(--cds-green-50)}
@media(max-width:640px){.ob-steps a span.t{display:none}.ob-steps a{justify-content:center}}
/* form primitives */
.cds-form{display:grid;gap:var(--cds-space-04)}
.cds-label{display:block;font-size:.75rem;font-weight:600;letter-spacing:.02em;color:var(--cds-gray-70);margin-bottom:6px}
.cds-label.sm{font-size:11px}
.cds-hint{font-size:.75rem;color:var(--cds-gray-70);margin-bottom:10px}
.cds-field{min-height:var(--cds-field-h)}
.cds-field-lg{min-height:var(--cds-field-h-lg)}
.cds-area{min-height:90px;padding:10px 14px}
.cds-num{max-width:200px}
.obx .form-control:focus,.obx .form-select:focus{border-color:var(--cds-blue-60);box-shadow:0 0 0 2px var(--cds-white),0 0 0 4px var(--cds-blue-60);outline:none}
.cds-geo{display:flex;gap:var(--cds-space-03);flex-wrap:wrap;align-items:center}
.cds-geo-msg{font-size:.75rem;color:var(--cds-gray-70)}
.cds-err{font-size:.75rem;color:var(--cds-red-60);margin-top:4px;display:none}
input[readonly].locked{background:var(--cds-gray-10);color:var(--cds-gray-70)}
/* photo dropzone */
#obDrop{border:1.5px dashed var(--cds-gray-20);background:var(--cds-gray-10);padding:var(--cds-space-06);text-align:center;cursor:pointer;transition:border-color .15s ease,background .15s ease}
#obDrop.over{border-color:var(--cds-blue-60);background:var(--cds-blue-10)}
#obDrop input{display:none}
.ob-drop-ic{width:2.5rem;height:2.5rem;margin:0 auto var(--cds-space-03);display:flex;align-items:center;justify-content:center;border:1px solid var(--cds-gray-20);background:var(--cds-white)}
.ob-drop-t{font-size:.875rem;font-weight:600}
.ob-drop-s{font-size:.75rem;color:var(--cds-gray-70)}
#obPrev{display:flex;gap:var(--cds-space-03);flex-wrap:wrap;margin-top:12px}
#obPrev .ph{position:relative;width:160px;height:120px;overflow:hidden;border:1px solid var(--cds-gray-20);background:var(--cds-white)}
#obPrev img{width:100%;height:100%;object-fit:cover;display:block}
#obPrev .rm{position:absolute;top:4px;right:4px;width:24px;height:24px;border:none;border-radius:50%;background:var(--cds-gray-100);color:#fff;font-size:14px;line-height:1;cursor:pointer}
#obPrev .cov{position:absolute;left:4px;bottom:4px;background:var(--cds-blue-60);color:#fff;font-size:10px;font-weight:700;padding:2px 6px}
#obUpBar{display:none;height:4px;background:var(--cds-gray-20);margin-top:12px}
#obUpBar i{display:block;height:100%;width:0;background:var(--cds-blue-60);transition:width .2s ease}
/* map */
#obMap{height:260px;border:1px solid var(--cds-gray-20);background:var(--cds-gray-10)}
@media(min-width:992px){#obMap{height:320px}}
#obMap.loading{background:linear-gradient(90deg,#e0e0e0 25%,#f4f4f4 50%,#e0e0e0 75%);background-size:200% 100%;animation:pShimmer 1.2s ease infinite}
@keyframes pShimmer{from{background-position:200% 0}to{background-position:-200% 0}}
#obMapWrap{position:relative}
#obSearchList{position:absolute;top:100%;left:0;right:0;z-index:20;background:var(--cds-white);border:1px solid var(--cds-gray-20);border-top:none;display:none;max-height:220px;overflow:auto}
#obSearchList button{display:block;width:100%;text-align:left;background:none;border:none;padding:10px 12px;font-size:13px;cursor:pointer;border-bottom:1px solid var(--cds-gray-10)}
#obSearchList button:hover{background:var(--cds-blue-10)}
#obSearchList button small{display:block;color:var(--cds-gray-70);font-size:11px}
/* category cards + review */
.ob-sec-t{font-size:13px;font-weight:600;margin-bottom:4px}
.ob-sec-s{font-size:12px;color:var(--cds-gray-70);margin-bottom:12px}
.ob-room{border:1px solid var(--cds-gray-20);border-left:3px solid var(--cds-blue-60);background:var(--cds-white);padding:14px;margin-bottom:12px}
.ob-room-h{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;gap:8px;flex-wrap:wrap}
.ob-room-h b{font-size:14px}
.ob-room-rm{background:none;border:1px solid var(--cds-gray-20);font-size:12px;padding:4px 10px;cursor:pointer;color:#a2191f}
.ob-numline{display:flex;gap:8px;margin-bottom:8px}
.ob-rphotos{display:flex;gap:8px;flex-wrap:wrap;margin-top:8px}
.ob-rphotos .ph{position:relative;width:96px;height:72px;overflow:hidden;border:1px solid var(--cds-gray-20)}
.ob-rphotos img{width:100%;height:100%;object-fit:cover;display:block}
.ob-rphotos .rm{position:absolute;top:2px;right:2px;width:20px;height:20px;border:none;border-radius:50%;background:var(--cds-gray-100);color:#fff;font-size:12px;line-height:1;cursor:pointer}
.ob-review{display:grid;gap:0;border:1px solid var(--cds-gray-20);background:var(--cds-white)}
.ob-review>div{display:flex;gap:12px;padding:10px 14px;border-bottom:1px solid var(--cds-gray-10);font-size:13px}
.ob-review>div:last-child{border-bottom:none}
.ob-review dt{width:130px;flex:none;color:var(--cds-gray-70);font-weight:600;font-size:12px}
.ob-review dd{margin:0;font-weight:600}
.ob-review img{width:120px;height:80px;object-fit:cover;border:1px solid var(--cds-gray-20)}
.ob-sec-h{font-size:13px;font-weight:600;margin:4px 0 8px}
.ob-muted{font-size:12px;color:var(--cds-gray-70)}
.ob-nav{display:flex;gap:8px;margin-top:16px;flex-wrap:wrap}
.cds-btn-flex{flex:1;justify-content:center}
.cds-btn-flex2{flex:2;justify-content:center}
.cds-btn-sm{min-height:36px;font-size:12px}
.cds-btn-ic{min-height:40px}
.cds-hint{font-size:.75rem;color:var(--cds-gray-70);margin-bottom:10px}
.cds-geo-msg{font-size:.75rem;color:var(--cds-gray-70)}
.ob-rooms-line{font-size:13px}
.ob-more{font-size:11px;color:var(--cds-gray-70)}
.ob-dim{font-size:12px;color:var(--cds-gray-70)}
.ob-dim-nm{font-weight:400}
.ob-tight{margin-bottom:10px}
.cds-err{font-size:.75rem;color:var(--cds-red-60);margin-top:4px;display:none}
.field-err{font-size:.75rem;color:var(--cds-red-60);margin-top:4px;display:none}
.cds-ta{min-height:90px;padding:10px 14px}
@media(max-width:640px){.ob-room{padding:12px}.ob-nav .p-btn{flex:1 1 100%;justify-content:center}}
@media (prefers-reduced-motion:reduce){.obx *{animation:none!important;transition:none!important}}
</style>

<div class="p-card obx">
  <h3>Onboard new lodge</h3>
  <div class="sub">Step <?= $step ?> of 5 — <?= h($labels[$step]) ?>. Your progress saves automatically.</div>

  <nav class="ob-steps" aria-label="Onboarding progress">
    <?php for ($i = 1; $i <= 5; $i++): ?>
      <a href="<?= $this->Url->build('/host/onboarding?step=' . $i) ?>" class="<?= $i === $step ? 'cur' : ($i < $step ? 'done' : '') ?>" <?= $i === $step ? 'aria-current="step"' : '' ?>><span class="n"><?= $i < $step ? '✓' : $i ?></span><span class="t"><?= h($labels[$i]) ?></span></a>
    <?php endfor; ?>
  </nav>

  <?php if ($step === 1): ?>
  <?= $this->Form->create(null, ['url' => ['action' => 'onboarding', '?' => ['step' => 1]], 'class' => 'cds-form']) ?>
    <?= $this->Form->hidden('wizard_step', ['value' => 1]) ?>
    <div>
      <label class="cds-label">Property name *</label>
      <input name="name" class="form-control cds-field" required placeholder="Sunrise Lodge" value="<?= h($d['name'] ?? '') ?>">
    </div>
    <div class="row g-2">
      <div class="col-md-6"><label class="cds-label">Type</label>
        <select name="type" class="form-select cds-field">
          <?php foreach (['Lodge', 'Hotel', 'Apartment'] as $t): ?><option <?= ($d['type'] ?? 'Lodge') === $t ? 'selected' : '' ?>><?= $t ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6"><label class="cds-label">Price / night TSh *</label><input name="price_per_night" type="number" min="1" class="form-control cds-field" required placeholder="150000" value="<?= h($d['price_per_night'] ?? '') ?>"></div>
    </div>
    <div><label class="cds-label">Description</label><textarea name="description" class="form-control cds-ta" placeholder="What makes this place special?"><?= h($d['description'] ?? '') ?></textarea></div>
    <div class="ob-nav"><button class="p-btn cds-btn-flex">Continue → Location</button></div>
  <?= $this->Form->end() ?>

  <?php elseif ($step === 2): ?>
  <?= $this->Form->create(null, ['url' => ['action' => 'onboarding', '?' => ['step' => 2]], 'class' => 'cds-form']) ?>
    <?= $this->Form->hidden('wizard_step', ['value' => 2]) ?>
    <div id="obMapWrap">
      <label class="cds-label">Find it like in Bolt</label>
      <input id="obSearch" class="form-control cds-field-lg" placeholder="Type address, area or landmark…" autocomplete="off" aria-label="Search location">
      <div id="obSearchList" role="listbox"></div>
    </div>
    <div id="obMap" aria-label="Property location map"></div>
    <div class="cds-geo">
      <button type="button" class="p-btn ghost cds-btn-sm" id="obGeoBtn">Use my location</button>
      <span id="obGeoMsg" class="cds-geo-msg"></span>
    </div>
    <div class="row g-2">
      <div class="col-md-6"><label class="cds-label">City *</label><input name="city" id="obCity" class="form-control cds-field" required placeholder="Arusha" value="<?= h($d['city'] ?? '') ?>"></div>
      <div class="col-md-6"><label class="cds-label">Area</label><input name="area" id="obArea" class="form-control cds-field" placeholder="Njiro" value="<?= h($d['area'] ?? '') ?>"></div>
    </div>
    <div><label class="cds-label">Street address</label><input name="address" id="obAddress" class="form-control cds-field" placeholder="Plot 123, Njiro Road" value="<?= h($d['address'] ?? '') ?>"></div>
    <div class="row g-2">
      <div class="col-6"><label class="cds-label">Latitude (auto)</label><input name="latitude" id="obLat" class="form-control locked cds-field" value="<?= h($d['latitude'] ?? '-6.7924') ?>" readonly></div>
      <div class="col-6"><label class="cds-label">Longitude (auto)</label><input name="longitude" id="obLng" class="form-control locked cds-field" value="<?= h($d['longitude'] ?? '39.2083') ?>" readonly></div>
    </div>
    <div class="ob-nav">
      <a href="<?= $this->Url->build('/host/onboarding?step=1') ?>" class="p-btn ghost cds-btn-flex">← Back</a>
      <button class="p-btn cds-btn-flex2">Continue → Photos</button>
    </div>
  <?= $this->Form->end() ?>

  <?php elseif ($step === 3): ?>
  <?= $this->Form->create(null, ['url' => ['action' => 'onboarding', '?' => ['step' => 3]], 'id' => 'obPhotoForm', 'class' => 'cds-form']) ?>
    <?= $this->Form->hidden('wizard_step', ['value' => 3]) ?>
    <div>
      <label class="cds-label">Cover photo * — drag &amp; drop or click</label>
      <div id="obDrop" role="button" tabindex="0" aria-label="Upload cover photo">
        <div class="ob-drop-ic" aria-hidden="true"><svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M10 13.5v-9m0 0L6.5 8M10 4.5L13.5 8" stroke="#0f62fe" stroke-width="1.8" stroke-linecap="square"/><path d="M3.5 12.5v3a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-3" stroke="#161616" stroke-width="1.8"/></svg></div>
        <div class="ob-drop-t">Drop photo here or click to browse</div>
        <div class="ob-drop-s">JPG / PNG / WebP · max 10 MB · stored immediately</div>
        <input type="file" id="obFile" accept="image/jpeg,image/png,image/webp,image/gif">
      </div>
      <div id="obUpBar"><i></i></div>
      <div id="obPrev"></div>
      <input type="hidden" name="image_url" id="obCover" value="<?= h($d['image_url'] ?? '') ?>">
      <div class="field-err" id="obPhotoErr">Please upload a cover photo before continuing.</div>
    </div>
    <div class="ob-nav">
      <a href="<?= $this->Url->build('/host/onboarding?step=2') ?>" class="p-btn ghost cds-btn-flex">← Back</a>
      <button class="p-btn cds-btn-flex2">Continue → Rooms</button>
    </div>
  <?= $this->Form->end() ?>

  <?php elseif ($step === 4): ?>
  <?= $this->Form->create(null, ['url' => ['action' => 'onboarding', '?' => ['step' => 4]], 'id' => 'obRoomsForm', 'class' => 'cds-form']) ?>
    <?= $this->Form->hidden('wizard_step', ['value' => 4]) ?>
    <div>
      <label class="cds-label">Room categories — e.g. Deluxe with rooms 45, 78</label>
      <div class="cds-hint">Define each category once (price, pictures, amenities), then list every room number in it. Each number becomes its own bookable room.</div>
      <div id="obCats">
        <?php foreach ($draftCats as $ci => $cat):
          $cat = is_array($cat) ? $cat : [];
          $cph = isset($cat['photos']) && is_array($cat['photos']) ? array_values(array_filter(array_map('trim', array_map('strval', $cat['photos'])))) : [];
          $cnums = isset($cat['numbers']) && is_array($cat['numbers']) ? array_values(array_filter(array_map('trim', array_map('strval', $cat['numbers'])))) : [];
        ?>
        <div class="ob-room" data-i="<?= $ci ?>">
          <div class="ob-room-h"><b><span class="p-badge blue"><?= h($cat['room_type'] ?? 'Standard') ?></span> <span class="ob-rn">Category <?= $ci + 1 ?></span></b><button type="button" class="ob-room-rm">Remove</button></div>
          <div class="row g-2">
            <div class="col-6 col-md-4"><label class="cds-label sm">Category</label><select name="cats[<?= $ci ?>][room_type]" class="form-select ob-cat-type cds-field"><?php foreach ($roomTypes as $t): ?><option <?= ($cat['room_type'] ?? 'Standard') === $t ? 'selected' : '' ?>><?= $t ?></option><?php endforeach; ?></select></div>
            <div class="col-6 col-md-4"><label class="cds-label sm">Price TSh / night *</label><input name="cats[<?= $ci ?>][price]" type="number" min="1" class="form-control cds-field" placeholder="85000" value="<?= h($cat['price'] ?? '') ?>"></div>
            <div class="col-6 col-md-4"><label class="cds-label sm">Sleeps (capacity)</label><input name="cats[<?= $ci ?>][capacity]" type="number" min="1" class="form-control cds-field" value="<?= h($cat['capacity'] ?? 2) ?>"></div>
          </div>
          <div class="mt-2">
            <label class="cds-label sm">Room numbers in this category * <span class="ob-dim">(e.g. 45, 78)</span></label>
            <div class="ob-nums" data-nums>
              <?php $numList = !empty($cnums) ? $cnums : ['']; foreach ($numList as $nn): ?>
              <div class="d-flex gap-2 mb-2"><input name="cats[<?= $ci ?>][numbers][]" class="form-control ob-num cds-field cds-num" placeholder="45" value="<?= h($nn) ?>"><button type="button" class="ob-num-rm p-btn ghost cds-btn-ic">×</button></div>
              <?php endforeach; ?>
            </div>
            <button type="button" class="ob-num-add p-btn ghost cds-btn-sm">+ Add room number</button>
          </div>
          <div class="row g-2 mt-1">
            <div class="col-6"><label class="cds-label sm">Bed setup</label><input name="cats[<?= $ci ?>][bed_configuration]" class="form-control cds-field" placeholder="1 King Bed" value="<?= h($cat['bed_configuration'] ?? '') ?>"></div>
            <div class="col-6"><label class="cds-label sm">Status</label><select name="cats[<?= $ci ?>][status]" class="form-select cds-field"><option value="available" <?= ($cat['status'] ?? 'available') === 'available' ? 'selected' : '' ?>>Available</option><option value="maintenance" <?= ($cat['status'] ?? '') === 'maintenance' ? 'selected' : '' ?>>Maintenance</option></select></div>
          </div>
          <div class="mt-2"><label class="cds-label sm">Amenities (comma separated)</label><input name="cats[<?= $ci ?>][amenities]" class="form-control cds-field" placeholder="Wifi, AC, Mini bar" value="<?= h(is_array($cat['amenities'] ?? null) ? implode(', ', $cat['amenities']) : ($cat['amenities'] ?? '')) ?>"></div>
          <div class="mt-2">
            <label class="cds-label sm">Category pictures (shared by all its rooms)</label>
            <input type="file" class="ob-rfile form-control cds-field" accept="image/jpeg,image/png,image/webp,image/gif" multiple>
            <div class="ob-rphotos" data-photos>
              <?php foreach ($cph as $ph): ?><div class="ph"><img src="<?= h($ph) ?>" alt="Category photo"><button type="button" class="rm" aria-label="Remove photo">×</button><input type="hidden" name="cats[<?= $ci ?>][photos][]" value="<?= h($ph) ?>"></div><?php endforeach; ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <button type="button" class="p-btn ghost cds-btn-flex" id="obAddRoom">+ Add another category</button>
      <div class="field-err" id="obRoomsErr">Uploads still running — wait a moment.</div>
    </div>
    <div class="ob-nav">
      <a href="<?= $this->Url->build('/host/onboarding?step=3') ?>" class="p-btn ghost cds-btn-flex">← Back</a>
      <button class="p-btn cds-btn-flex2">Continue → Review</button>
    </div>
  <?= $this->Form->end() ?>

  <?php else: ?>
  <?= $this->Form->create(null, ['url' => ['action' => 'onboarding', '?' => ['step' => 5]], 'id' => 'obReviewForm', 'class' => 'cds-form']) ?>
    <?= $this->Form->hidden('wizard_step', ['value' => 5]) ?>
    <dl class="ob-review">
      <div><dt>Property</dt><dd><?= h(($d['name'] ?? '—') . ' (' . ($d['type'] ?? 'Lodge') . ')') ?></dd></div>
      <div><dt>Location</dt><dd><?= h(trim(($d['address'] ?? '') . ', ' . ($d['area'] ?? '') . ', ' . ($d['city'] ?? ''), ', ')) ?><br><span class="ob-dim"><?= h(($d['latitude'] ?? '') . ', ' . ($d['longitude'] ?? '')) ?></span></dd></div>
      <div><dt>Price</dt><dd>TSh <?= number_format((float)($d['price_per_night'] ?? 0)) ?> / night</dd></div>
      <?php if (!empty($d['image_url'])): ?><div><dt>Cover</dt><dd><img src="<?= h($d['image_url']) ?>" alt="Cover photo"></dd></div><?php endif; ?>
      <?php if (!empty($d['description'])): ?><div><dt>About</dt><dd class="ob-dim-nm"><?= h($d['description']) ?></dd></div><?php endif; ?>
    </dl>
    <?php $revRooms = (isset($d['rooms']) && is_array($d['rooms'])) ? array_values($d['rooms']) : [];
    $revCats = [];
    foreach ($revRooms as $rr) {
      $rr = is_array($rr) ? $rr : [];
      $t = $rr['room_type'] ?? 'Standard';
      if (!isset($revCats[$t])) $revCats[$t] = ['type' => $t, 'price' => $rr['price'] ?? 0, 'rooms' => [], 'photos' => []];
      $revCats[$t]['rooms'][] = $rr['room_number'] ?? '—';
      foreach ((array)($rr['photos'] ?? []) as $ph) { if (!in_array($ph, $revCats[$t]['photos'], true)) $revCats[$t]['photos'][] = $ph; }
    }
    ?>
    <div class="ob-sec-h">Rooms (<?= count($revRooms) ?>) — each belongs to this lodge</div>
    <?php if (empty($revRooms)): ?>
      <div class="p-card"><div class="p-empty">No rooms added — you can add them later under Rooms.</div></div>
    <?php else: ?>
      <?php foreach ($revCats as $rc): ?>
      <div class="ob-room ob-tight">
        <div class="ob-room-h"><span class="p-badge blue"><?= h($rc['type']) ?></span><b>TSh <?= number_format((float)$rc['price']) ?> / night</b></div>
        <div class="ob-rooms-line">Rooms: <strong><?= h(implode(', ', $rc['rooms'])) ?></strong></div>
        <?php if (!empty($rc['photos'])): ?><div class="ob-rphotos"><?php foreach (array_slice($rc['photos'], 0, 4) as $ph): ?><div class="ph"><img src="<?= h($ph) ?>" alt="Category photo"></div><?php endforeach; ?><?php if (count($rc['photos']) > 4): ?><span class="ob-more">+<?= count($rc['photos']) - 4 ?></span><?php endif; ?></div><?php endif; ?>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
    <div><label class="cds-label">Amenities (comma separated)</label><input name="amenities_raw" id="amen_raw" class="form-control cds-field" placeholder="Wifi, Pool, Parking" value="<?= h(is_array($d['amenities'] ?? null) ? implode(', ', $d['amenities']) : ($d['amenities'] ?? '')) ?>"></div>
    <div class="ob-nav">
      <a href="<?= $this->Url->build('/host/onboarding?step=4') ?>" class="p-btn ghost cds-btn-flex">← Back</a>
      <button class="p-btn cds-btn-flex2">Launch listing</button>
    </div>
  <?= $this->Form->end() ?>
  <?php endif; ?>
</div>

<?= $this->Html->script('https://api.mapbox.com/mapbox-gl-js/v3.1.2/mapbox-gl.js') ?>
<script>
(function () {
  var BACKEND = <?= json_encode($backendUrl) ?>;
  var DAR = { lng: 39.2083, lat: -6.7924 };

  function toast(msg) {
    var t = document.createElement('div');
    t.textContent = msg;
    t.style.cssText = 'position:fixed;bottom:20px;left:50%;transform:translateX(-50%);background:#161616;color:#fff;padding:10px 18px;font-size:13px;z-index:3000';
    document.body.appendChild(t);
    setTimeout(function(){ t.parentNode && t.parentNode.removeChild(t); }, 2600);
  }

  /* ---------- step 3 · cover photo drag & drop (only when present) ---------- */
  var drop = document.getElementById('obDrop');
  if (drop) {
    var fileInput = document.getElementById('obFile');
    var prev = document.getElementById('obPrev');
    var bar = document.getElementById('obUpBar');
    var barFill = bar ? bar.querySelector('i') : null;
    var coverInput = document.getElementById('obCover');
    var photoErr = document.getElementById('obPhotoErr');
    var uploading = 0;
    window.__obUploading = function () { return uploading; };
    (function renderPrev() {
      var url = coverInput.value;
      prev.innerHTML = '';
      if (!url) return;
      var d = document.createElement('div');
      d.className = 'ph';
      d.innerHTML = '<img alt="Cover photo"><span class="cov">COVER</span>';
      d.querySelector('img').src = url;
      var rm = document.createElement('button');
      rm.type = 'button'; rm.className = 'rm'; rm.textContent = '×'; rm.setAttribute('aria-label', 'Remove photo');
      rm.onclick = function () { coverInput.value = ''; renderPrev(); };
      d.appendChild(rm);
      prev.appendChild(d);
      window.__obRenderPrev = renderPrev;
    })();
    function uploadFile(f) {
      if (!f || !/^image\//.test(f.type)) { toast('Only image files please.'); return; }
      if (f.size > 10 * 1024 * 1024) { toast('Max 10 MB per photo.'); return; }
      uploading++;
      if (bar) { bar.style.display = 'block'; if (barFill) barFill.style.width = '30%'; }
      var fd = new FormData();
      fd.append('file', f);
      var xhr = new XMLHttpRequest();
      xhr.open('POST', BACKEND + '/upload', true);
      xhr.setRequestHeader('Accept', 'application/json');
      xhr.upload.onprogress = function (e) {
        if (e.lengthComputable && barFill) barFill.style.width = Math.round(e.loaded / e.total * 100) + '%';
      };
      xhr.onload = function () {
        uploading--;
        if (bar) { if (barFill) barFill.style.width = '100%'; setTimeout(function(){ bar.style.display = 'none'; if (barFill) barFill.style.width = '0'; }, 400); }
        try {
          var j = JSON.parse(xhr.responseText);
          if (xhr.status >= 200 && xhr.status < 300 && j.url) { coverInput.value = j.url; if (photoErr) photoErr.style.display = 'none'; window.__obRenderPrev(); }
          else toast((j && j.message) || 'Upload failed. Try again.');
        } catch (e) { toast('Upload failed. Try again.'); }
      };
      xhr.onerror = function () { uploading--; if (bar) bar.style.display = 'none'; toast('No connection to media server.'); };
      xhr.send(fd);
    }
    drop.addEventListener('click', function () { fileInput.click(); });
    drop.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); fileInput.click(); } });
    ['dragenter', 'dragover'].forEach(function (ev) { drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.add('over'); }); });
    ['dragleave', 'drop'].forEach(function (ev) { drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.remove('over'); }); });
    drop.addEventListener('drop', function (e) {
      var fs = e.dataTransfer && e.dataTransfer.files;
      if (fs && fs[0]) uploadFile(fs[0]);
    });
    fileInput.addEventListener('change', function () { if (fileInput.files[0]) uploadFile(fileInput.files[0]); fileInput.value = ''; });
    var pf = document.getElementById('obPhotoForm');
    if (pf) pf.addEventListener('submit', function (e) {
      if (window.__obUploading() > 0) { e.preventDefault(); toast('Wait for the photo upload to finish.'); return; }
      if (!coverInput.value) {
        e.preventDefault();
        if (photoErr) photoErr.style.display = 'block';
        drop.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    });
  }

  /* ---------- step 2 · smart location (only when present) ---------- */
  var mapEl = document.getElementById('obMap');
  if (mapEl) {
    var latEl = document.getElementById('obLat');
    var lngEl = document.getElementById('obLng');
    var cityEl = document.getElementById('obCity');
    var areaEl = document.getElementById('obArea');
    var addrEl = document.getElementById('obAddress');
    var searchEl = document.getElementById('obSearch');
    var listEl = document.getElementById('obSearchList');
    var geoBtn = document.getElementById('obGeoBtn');
    var geoMsg = document.getElementById('obGeoMsg');
    var mapWrap = document.getElementById('obMapWrap');
    var map = null, marker = null, mbToken = '';

    (function seed() {
      var la = parseFloat(latEl.value), ln = parseFloat(lngEl.value);
      if (isFinite(la) && isFinite(ln) && (la !== DAR.lat || ln !== DAR.lng)) DAR = { lng: ln, lat: la };
    })();

    function setPoint(lng, lat) {
      latEl.value = (+lat).toFixed(6);
      lngEl.value = (+lng).toFixed(6);
      if (marker) marker.setLngLat([lng, lat]);
    }
    function ctxVal(ctx, keys) {
      if (!ctx) return '';
      for (var i = 0; i < ctx.length; i++) {
        var id = ctx[i].id || '';
        for (var k = 0; k < keys.length; k++) {
          if (id.indexOf(keys[k]) === 0) return ctx[i].text || '';
        }
      }
      return '';
    }
    function applyPlace(f) {
      var c = f.center || [];
      if (c.length === 2) {
        setPoint(c[0], c[1]);
        if (map) map.flyTo({ center: c, zoom: 14 });
      }
      if (f.place_name) addrEl.value = f.place_name;
      var city = ctxVal(f.context, ['place']);
      var area = ctxVal(f.context, ['neighborhood', 'locality']);
      if (city) cityEl.value = city;
      if (area) areaEl.value = area;
      if (geoMsg) geoMsg.textContent = 'Pinned: ' + (f.text || f.place_name || 'location');
      listEl.style.display = 'none';
    }
    function geoSearch(q, cb) {
      if (!mbToken || q.length < 2) { cb([]); return; }
      fetch('https://api.mapbox.com/geocoding/v5/mapbox.places/' + encodeURIComponent(q) + '.json?access_token=' + mbToken + '&country=tz&limit=5&types=address,place,locality,neighborhood,poi')
        .then(function (r) { return r.ok ? r.json() : null; })
        .then(function (j) { cb((j && j.features) || []); })
        .catch(function () { cb([]); });
    }
    function reverse(lng, lat) {
      if (!mbToken) return;
      fetch('https://api.mapbox.com/geocoding/v5/mapbox.places/' + lng + ',' + lat + '.json?access_token=' + mbToken + '&country=tz&limit=1&types=address,place,locality,neighborhood,poi')
        .then(function (r) { return r.ok ? r.json() : null; })
        .then(function (j) {
          var f = j && j.features && j.features[0];
          if (!f) return;
          addrEl.value = f.place_name || addrEl.value;
          var city = ctxVal(f.context, ['place']);
          var area = ctxVal(f.context, ['neighborhood', 'locality']);
          if (city) cityEl.value = city;
          if (area) areaEl.value = area;
          if (geoMsg) geoMsg.textContent = 'Pinned: ' + (f.text || 'location');
        })
        .catch(function () {});
    }
    var deb = null;
    searchEl.addEventListener('input', function () {
      clearTimeout(deb);
      var q = searchEl.value.trim();
      if (q.length < 2) { listEl.style.display = 'none'; return; }
      deb = setTimeout(function () {
        geoSearch(q, function (feats) {
          listEl.innerHTML = '';
          if (!feats.length) { listEl.style.display = 'none'; return; }
          feats.forEach(function (f) {
            var b = document.createElement('button');
            b.type = 'button';
            b.innerHTML = (f.text || '').replace(/</g, '&lt;') + '<small>' + (f.place_name || '').replace(/</g, '&lt;') + '</small>';
            b.onclick = function () { searchEl.value = f.text || ''; applyPlace(f); };
            listEl.appendChild(b);
          });
          listEl.style.display = 'block';
        });
      }, 300);
    });
    document.addEventListener('click', function (e) {
      if (mapWrap && !mapWrap.contains(e.target)) listEl.style.display = 'none';
    });
    /* fields → map: typing city/area/address moves the pin */
    var fldDeb = null;
    [addrEl, areaEl, cityEl].forEach(function (el) {
      el.addEventListener('input', function () {
        clearTimeout(fldDeb);
        fldDeb = setTimeout(function () {
          if (!mbToken || !map || !marker) return;
          var q = [addrEl.value.trim(), areaEl.value.trim(), cityEl.value.trim()].filter(Boolean).join(', ');
          if (q.length < 3) return;
          geoSearch(q, function (feats) {
            var f = feats && feats[0];
            if (!f || !f.center) return;
            setPoint(f.center[0], f.center[1]);
            try { map.easeTo({ center: f.center, zoom: Math.max(map.getZoom(), 13), duration: 600 }); } catch (e) {}
            if (geoMsg) geoMsg.textContent = 'Pinned: ' + (f.text || 'location');
          });
        }, 700);
      });
    });
    function unlockManual() {
      mapEl.classList.remove('loading');
      latEl.readOnly = false; lngEl.readOnly = false;
      latEl.classList.remove('locked'); lngEl.classList.remove('locked');
      if (geoMsg) geoMsg.textContent = 'Map offline — you may enter coordinates manually.';
    }
    function initMap(token) {
      mbToken = token;
      mapEl.classList.add('loading');
      if (typeof mapboxgl === 'undefined') { unlockManual(); return; }
      mapboxgl.accessToken = token;
      try {
        map = new mapboxgl.Map({ container: 'obMap', style: 'mapbox://styles/mapbox/streets-v12', center: [DAR.lng, DAR.lat], zoom: 12 });
      } catch (e) { unlockManual(); return; }
      function ready() {
        mapEl.classList.remove('loading');
        try { map.resize(); } catch (e) {}
      }
      map.on('load', ready);
      setTimeout(ready, 2500);
      setTimeout(function () { try { map.resize(); } catch (e) {} }, 400);
      map.on('error', function () { mapEl.classList.remove('loading'); });
      marker = new mapboxgl.Marker({ draggable: true }).setLngLat([DAR.lng, DAR.lat]).addTo(map);
      marker.on('dragend', function () {
        var p = marker.getLngLat();
        setPoint(p.lng, p.lat);
        reverse(p.lng.toFixed(6), p.lat.toFixed(6));
      });
      map.on('click', function (e) {
        setPoint(e.lngLat.lng, e.lngLat.lat);
        reverse(e.lngLat.lng.toFixed(6), e.lngLat.lat.toFixed(6));
      });
    }
    fetch('/api/map-config', { headers: { 'Accept': 'application/json' } })
      .then(function (r) { return r.ok ? r.json() : null; })
      .then(function (j) {
        var t = j && (j.mapbox_token || j.mapboxToken || j.token);
        if (t && t.indexOf('pk.') === 0) initMap(t);
        else unlockManual();
      })
      .catch(unlockManual);
    if (geoBtn) {
      geoBtn.addEventListener('click', function () {
        if (!navigator.geolocation) { if (geoMsg) geoMsg.textContent = 'Geolocation not supported here.'; return; }
        if (geoMsg) geoMsg.textContent = 'Locating…';
        navigator.geolocation.getCurrentPosition(function (pos) {
          var lng = +pos.coords.longitude.toFixed(6), lat = +pos.coords.latitude.toFixed(6);
          setPoint(lng, lat);
          if (map) map.flyTo({ center: [lng, lat], zoom: 14 });
          reverse(lng, lat);
          if (geoMsg) geoMsg.textContent = 'Pinned to your location.';
        }, function () {
          if (geoMsg) geoMsg.textContent = 'Location blocked — search or tap the map instead.';
        }, { timeout: 10000 });
      });
    }
  }

  /* ---------- step 4 · categories with many numbered rooms (only when present) ---------- */
  var roomsBox = document.getElementById('obCats');
  if (roomsBox) {
    var catIdx = roomsBox.querySelectorAll('.ob-room').length;
    var roomUp = 0;
    var roomsErr = document.getElementById('obRoomsErr');
    function thumb(box, idx, url) {
      var d = document.createElement('div');
      d.className = 'ph';
      var img = document.createElement('img');
      img.alt = 'Category photo'; img.src = url;
      var rm = document.createElement('button');
      rm.type = 'button'; rm.className = 'rm'; rm.textContent = '×'; rm.setAttribute('aria-label', 'Remove photo');
      rm.onclick = function () { d.parentNode.removeChild(d); };
      var hid = document.createElement('input');
      hid.type = 'hidden'; hid.name = 'cats[' + idx + '][photos][]'; hid.value = url;
      d.appendChild(img); d.appendChild(rm); d.appendChild(hid);
      box.appendChild(d);
    }
    function syncBadge(row) {
      var t = row.querySelector('.ob-cat-type');
      var b = row.querySelector('.p-badge');
      if (t && b) b.textContent = t.value;
    }
    function wireCat(row) {
      var idx = row.getAttribute('data-i');
      var file = row.querySelector('.ob-rfile');
      var box = row.querySelector('[data-photos]');
      box.querySelectorAll('.rm').forEach(function (b) {
        b.onclick = function () { var p = b.closest('.ph'); if (p) p.parentNode.removeChild(p); };
      });
      var typeSel = row.querySelector('.ob-cat-type');
      if (typeSel) typeSel.addEventListener('change', function () { syncBadge(row); });
      if (file) file.addEventListener('change', function () {
        var fs = file.files;
        for (var k = 0; k < fs.length; k++) {
          (function (f) {
            if (!/^image\//.test(f.type)) { toast('Only image files please.'); return; }
            if (f.size > 10 * 1024 * 1024) { toast('Max 10 MB per photo.'); return; }
            roomUp++;
            var fd = new FormData();
            fd.append('file', f);
            var xhr = new XMLHttpRequest();
            xhr.open('POST', BACKEND + '/upload', true);
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.onload = function () {
              roomUp--;
              try {
                var j = JSON.parse(xhr.responseText);
                if (xhr.status >= 200 && xhr.status < 300 && j.url) thumb(box, idx, j.url);
                else toast((j && j.message) || 'Photo upload failed.');
              } catch (e) { toast('Photo upload failed.'); }
            };
            xhr.onerror = function () { roomUp--; toast('No connection to media server.'); };
            xhr.send(fd);
          })(fs[k]);
        }
        file.value = '';
      });
      // numbers: add / remove inputs
      var nums = row.querySelector('[data-nums]');
      function wireNumRm(scope) {
        scope.querySelectorAll('.ob-num-rm').forEach(function (b) {
          b.onclick = function () {
            var rows = nums.querySelectorAll('.ob-num');
            if (rows.length <= 1) { var inp = rows[0]; if (inp) inp.value = ''; return; }
            var line = b.closest('div.d-flex') || b.parentNode;
            if (line && line.parentNode === nums) nums.removeChild(line);
          };
        });
      }
      wireNumRm(row);
      var addNum = row.querySelector('.ob-num-add');
      if (addNum) addNum.onclick = function () {
        var line = document.createElement('div');
        line.className = 'd-flex gap-2 mb-2';
        line.innerHTML = '<input name="cats[' + idx + '][numbers][]" class="form-control ob-num cds-field cds-num" placeholder="79">';
        var rb = document.createElement('button');
        rb.type = 'button'; rb.className = 'ob-num-rm p-btn ghost'; rb.style.minHeight = '40px'; rb.textContent = '×';
        rb.onclick = function () { nums.removeChild(line); };
        line.appendChild(rb);
        nums.appendChild(line);
        line.querySelector('input').focus();
      };
      var rm = row.querySelector('.ob-room-rm');
      if (rm) rm.onclick = function () {
        if (roomsBox.querySelectorAll('.ob-room').length <= 1) { toast('Keep at least one category — empty ones are skipped.'); return; }
        row.parentNode.removeChild(row);
        renumber();
      };
    }
    function renumber() {
      roomsBox.querySelectorAll('.ob-room').forEach(function (row, n) {
        row.setAttribute('data-i', n);
        row.querySelector('.ob-rn').textContent = 'Category ' + (n + 1);
        row.querySelectorAll('[name]').forEach(function (el) {
          el.name = el.name.replace(/cats\[\d+\]/, 'cats[' + n + ']');
        });
        wireCatRefresh(row);
      });
      catIdx = roomsBox.querySelectorAll('.ob-room').length;
    }
    function wireCatRefresh(row) {
      // rebind thumb helper index after renumber: photos keep working since
      // new uploads use the refreshed data-i
      var file = row.querySelector('.ob-rfile');
      if (file) {
        var fresh = file.cloneNode(false);
        file.parentNode.replaceChild(fresh, file);
      }
      wireCat(row);
    }
    roomsBox.querySelectorAll('.ob-room').forEach(wireCat);
    var addBtn = document.getElementById('obAddRoom');
    if (addBtn) addBtn.addEventListener('click', function () {
      var n = catIdx++;
      var row = document.createElement('div');
      row.className = 'ob-room';
      row.setAttribute('data-i', n);
      row.innerHTML =
        '<div class="ob-room-h"><b><span class="p-badge blue">Standard</span> <span class="ob-rn">Category ' + (n + 1) + '</span></b><button type="button" class="ob-room-rm">Remove</button></div>' +
        '<div class="row g-2">' +
        '<div class="col-6 col-md-4"><label class="cds-label sm">Category</label><select name="cats[' + n + '][room_type]" class="form-select ob-cat-type cds-field"><option>Standard</option><option>Deluxe</option><option>Suite</option><option>Executive</option></select></div>' +
        '<div class="col-6 col-md-4"><label class="cds-label sm">Price TSh / night *</label><input name="cats[' + n + '][price]" type="number" min="1" class="form-control cds-field" placeholder="85000"></div>' +
        '<div class="col-6 col-md-4"><label class="cds-label sm">Sleeps (capacity)</label><input name="cats[' + n + '][capacity]" type="number" min="1" class="form-control cds-field" value="2"></div>' +
        '</div>' +
        '<div class="mt-2"><label class="cds-label sm">Room numbers in this category *</label>' +
        '<div class="ob-nums" data-nums><div class="d-flex gap-2 mb-2"><input name="cats[' + n + '][numbers][]" class="form-control ob-num cds-field cds-num" placeholder="45"><button type="button" class="ob-num-rm p-btn ghost cds-btn-ic">×</button></div></div>' +
        '<button type="button" class="ob-num-add p-btn ghost cds-btn-sm">+ Add room number</button></div>' +
        '<div class="row g-2 mt-1">' +
        '<div class="col-6"><label class="cds-label sm">Bed setup</label><input name="cats[' + n + '][bed_configuration]" class="form-control cds-field" placeholder="1 King Bed"></div>' +
        '<div class="col-6"><label class="cds-label sm">Status</label><select name="cats[' + n + '][status]" class="form-select cds-field"><option value="available">Available</option><option value="maintenance">Maintenance</option></select></div>' +
        '</div>' +
        '<div class="mt-2"><label class="cds-label sm">Amenities (comma separated)</label><input name="cats[' + n + '][amenities]" class="form-control cds-field" placeholder="Wifi, AC, Mini bar"></div>' +
        '<div class="mt-2"><label class="cds-label sm">Category pictures (shared by all its rooms)</label>' +
        '<input type="file" class="ob-rfile form-control cds-field" accept="image/jpeg,image/png,image/webp,image/gif" multiple>' +
        '<div class="ob-rphotos" data-photos></div></div>';
      roomsBox.appendChild(row);
      wireCat(row);
      row.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
    var roomsForm = document.getElementById('obRoomsForm');
    if (roomsForm) roomsForm.addEventListener('submit', function (e) {
      if (roomUp > 0) {
        e.preventDefault();
        if (roomsErr) roomsErr.style.display = 'block';
        toast('Wait for photos to finish uploading.');
      }
    });
  }

  /* ---------- step 5 · amenities split ---------- */
  var rf = document.getElementById('obReviewForm');
  if (rf) rf.addEventListener('submit', function () {
    var v = document.getElementById('amen_raw').value;
    v.split(',').map(function (s) { return s.trim(); }).filter(Boolean).forEach(function (val) {
      var i = document.createElement('input');
      i.type = 'hidden'; i.name = 'amenities[]'; i.value = val;
      rf.appendChild(i);
    });
  });
})();
</script>
