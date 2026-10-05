<?php
$step = (int)($step ?? 1);
if ($step < 1 || $step > 4) $step = 1;
$propId = (int)($propId ?? $property['id'] ?? 0);
$this->assign('title', 'Edit Lodge');
$this->assign('portal_title', 'Edit lodge — ' . ($property['name'] ?? ''));
$this->assign('page_actions', '<a href="' . $this->Url->build('/hotel-detail/' . $propId) . '" target="_blank" rel="noopener" class="p-btn ghost">View live card</a> <a href="' . $this->Url->build('/host/listings') . '" class="p-btn ghost">Back to listings</a>');
$stepUrl = fn(int $s): string => $this->Url->build('/host/lodge/' . $propId . '/edit?step=' . $s);
$labels = [1 => 'Basics', 2 => 'Location', 3 => 'Photos', 4 => 'Review'];
// Done-state mirrors onboarding: a step counts once its data exists.
$hasName = trim((string)($property['name'] ?? '')) !== '';
$hasCity = trim((string)($property['city'] ?? '')) !== '';
$hasCover = trim((string)($property['image_url'] ?? ($property['primary_image_url'] ?? ''))) !== '';
$done = [1 => $hasName, 2 => $hasCity, 3 => $hasCover, 4 => false];
$pLat = $property['latitude'] ?? ($property['lat'] ?? '-6.7924');
$pLng = $property['longitude'] ?? ($property['lng'] ?? '39.2083');
$amenities = $property['amenities'] ?? [];
if (is_string($amenities)) $amenities = array_filter(array_map('trim', explode(',', $amenities)));
$amenities = array_values((array)$amenities);
// Rooms grouped by category (same as onboarding review).
$revCats = [];
foreach ((array)($rooms ?? []) as $rr) {
    $rr = is_array($rr) ? $rr : [];
    $t = trim((string)($rr['room_type'] ?? ($rr['type'] ?? 'Standard'))) ?: 'Standard';
    if (!isset($revCats[$t])) $revCats[$t] = ['type' => $t, 'price' => $rr['customer_price'] ?? ($rr['price'] ?? 0), 'rooms' => []];
    $revCats[$t]['rooms'][] = $rr;
}
?>
<?= $this->element('host_onboard_css') ?>
<?php if ($step === 2): ?>
<?= $this->Html->css('https://api.mapbox.com/mapbox-gl-js/v3.1.2/mapbox-gl.css') ?>
<?php endif; ?>

<div class="p-card obx">
  <h3><?= h($property['name'] ?? 'Lodge') ?></h3>
  <div class="sub">Step <?= $step ?> of 4 — <?= h($labels[$step]) ?>. Saving a step moves you forward.</div>

  <nav class="ob-steps" aria-label="Edit progress">
    <?php for ($i = 1; $i <= 4; $i++): ?>
      <a href="<?= $stepUrl($i) ?>" class="<?= $i === $step ? 'cur' : ($done[$i] ? 'done' : '') ?>" <?= $i === $step ? 'aria-current="step"' : '' ?>><span class="n"><?= ($done[$i] && $i !== $step) ? '✓' : $i ?></span><span class="t"><?= h($labels[$i]) ?></span></a>
    <?php endfor; ?>
  </nav>

  <?php if ($step === 1): ?>
  <?= $this->Form->create(null, ['url' => '/host/lodge/' . $propId . '/edit?step=1', 'class' => 'cds-form', 'data-api' => 'PUT /properties/' . $propId, 'data-api-ok' => 'Saved — continue to the next step.', 'data-api-go' => '/host/cache-bust?scope=properties&go=' . urlencode('/host/lodge/' . $propId . '/edit?step=2')]) ?>
    <input type="hidden" name="address" value="<?= h($property['address'] ?? '') ?>">
    <input type="hidden" name="city" value="<?= h($property['city'] ?? '') ?>">
    <input type="hidden" name="area" value="<?= h($property['area'] ?? '') ?>">
    <input type="hidden" name="image_url" value="<?= h($property['image_url'] ?? ($property['primary_image_url'] ?? '')) ?>">
    <?php foreach ($amenities as $am): ?><input type="hidden" name="amenities[]" value="<?= h($am) ?>"><?php endforeach; ?>
    <div>
      <label class="cds-label">Property name *</label>
      <input name="name" class="form-control cds-field" required placeholder="Sunrise Lodge" value="<?= h($property['name'] ?? '') ?>">
    </div>
    <div><label class="cds-label">Price / night TSh *</label><input name="price_per_night" type="number" min="1" class="form-control cds-field" required placeholder="150000" value="<?= h($property['price_per_night'] ?? '') ?>"></div>
    <div>
      <label class="cds-label">Description</label>
      <textarea name="description" id="lodgeDescription" class="form-control cds-ta" placeholder="What makes this place special?"><?= h($property['description'] ?? '') ?></textarea>
      <button type="button" class="p-btn ghost" id="generateDescriptionBtn"
              data-property-id="<?= h((string)$propId) ?>"
              style="margin-top:8px;font-size:13px">
        <i class="fa-solid fa-wand-magic-sparkles me-1"></i>Draft from my rooms &amp; amenities
      </button>
      <div class="ob-dim-nm" id="generateDescriptionHint" style="font-size:11px;margin-top:6px">
        Fills the box from the room types, amenities and ratings already saved for this lodge. Review before saving.
      </div>
    </div>
    <div class="ob-nav"><button class="p-btn cds-btn-flex2">Save &amp; continue → Location</button></div>
  <?= $this->Form->end() ?>

  <?php elseif ($step === 2): ?>
  <?= $this->Form->create(null, ['url' => '/host/lodge/' . $propId . '/edit?step=2', 'class' => 'cds-form', 'data-api' => 'PUT /properties/' . $propId, 'data-api-num' => 'price_per_night', 'data-api-ok' => 'Saved — continue to the next step.', 'data-api-go' => '/host/cache-bust?scope=properties&go=' . urlencode('/host/lodge/' . $propId . '/edit?step=3')]) ?>
    <input type="hidden" name="name" value="<?= h($property['name'] ?? '') ?>">
    <input type="hidden" name="price_per_night" value="<?= h($property['price_per_night'] ?? '') ?>">
    <input type="hidden" name="description" value="<?= h($property['description'] ?? '') ?>">
    <input type="hidden" name="image_url" value="<?= h($property['image_url'] ?? ($property['primary_image_url'] ?? '')) ?>">
    <?php foreach ($amenities as $am): ?><input type="hidden" name="amenities[]" value="<?= h($am) ?>"><?php endforeach; ?>
    <div id="obMapWrap">
      <label class="cds-label">Find it like in Bolt</label>
      <input id="obSearch" class="form-control cds-field-lg" placeholder="Type address, area or landmark…" autocomplete="off" aria-label="Search location">
      <div id="obSearchList" role="listbox"></div>
    </div>
    <div id="obMap" aria-label="Property location map"><?php if (!empty($mapPreviewUrl)): ?><img id="obStatic" src="<?= h($mapPreviewUrl) ?>" alt="" aria-hidden="true" fetchpriority="high"><?php endif; ?></div>
    <div class="cds-geo">
      <button type="button" class="p-btn ghost cds-btn-sm" id="obGeoBtn">Use my location</button>
      <span id="obGeoMsg" class="cds-geo-msg"></span>
    </div>
    <div class="row g-2">
      <div class="col-md-6"><label class="cds-label">City *</label><input name="city" id="obCity" class="form-control cds-field" required placeholder="Arusha" value="<?= h($property['city'] ?? '') ?>"></div>
      <div class="col-md-6"><label class="cds-label">Area</label><input name="area" id="obArea" class="form-control cds-field" placeholder="Njiro" value="<?= h($property['area'] ?? '') ?>"></div>
    </div>
    <div><label class="cds-label">Street address</label><input name="address" id="obAddress" class="form-control cds-field" placeholder="Plot 123, Njiro Road" value="<?= h($property['address'] ?? '') ?>"></div>
    <div class="row g-2">
      <div class="col-6"><label class="cds-label">Latitude (auto)</label><input name="latitude" id="obLat" class="form-control locked cds-field" value="<?= h($pLat) ?>" readonly></div>
      <div class="col-6"><label class="cds-label">Longitude (auto)</label><input name="longitude" id="obLng" class="form-control locked cds-field" value="<?= h($pLng) ?>" readonly></div>
    </div>
    <div class="ob-nav">
      <a href="<?= $stepUrl(1) ?>" class="p-btn ghost cds-btn-flex">← Back</a>
      <button class="p-btn cds-btn-flex2">Save &amp; continue → Photos</button>
    </div>
  <?= $this->Form->end() ?>

  <?php elseif ($step === 3): ?>
  <?= $this->Form->create(null, ['url' => '/host/lodge/' . $propId . '/edit?step=3', 'id' => 'obPhotoForm', 'class' => 'cds-form', 'data-api' => 'PUT /properties/' . $propId, 'data-api-num' => 'price_per_night,latitude,longitude', 'data-api-ok' => 'Saved — continue to the next step.', 'data-api-go' => '/host/cache-bust?scope=properties&go=' . urlencode('/host/lodge/' . $propId . '/edit?step=4')]) ?>
    <input type="hidden" name="name" value="<?= h($property['name'] ?? '') ?>">
    <input type="hidden" name="price_per_night" value="<?= h($property['price_per_night'] ?? '') ?>">
    <input type="hidden" name="description" value="<?= h($property['description'] ?? '') ?>">
    <input type="hidden" name="city" value="<?= h($property['city'] ?? '') ?>">
    <input type="hidden" name="area" value="<?= h($property['area'] ?? '') ?>">
    <input type="hidden" name="address" value="<?= h($property['address'] ?? '') ?>">
    <input type="hidden" name="latitude" value="<?= h($pLat) ?>">
    <input type="hidden" name="longitude" value="<?= h($pLng) ?>">
    <?php foreach ($amenities as $am): ?><input type="hidden" name="amenities[]" value="<?= h($am) ?>"><?php endforeach; ?>
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
      <input type="hidden" name="image_url" id="obCover" value="<?= h($property['image_url'] ?? ($property['primary_image_url'] ?? '')) ?>">
      <div class="field-err" id="obPhotoErr">Please upload a cover photo before continuing.</div>
    </div>
    <div class="ob-nav">
      <a href="<?= $stepUrl(2) ?>" class="p-btn ghost cds-btn-flex">← Back</a>
      <button class="p-btn cds-btn-flex2">Save &amp; continue → Review</button>
    </div>
  <?= $this->Form->end() ?>

  <?php else: ?>
  <?= $this->Form->create(null, ['url' => '/host/lodge/' . $propId . '/edit?step=4', 'id' => 'obReviewForm', 'class' => 'cds-form', 'data-api' => 'PUT /properties/' . $propId, 'data-api-num' => 'price_per_night,latitude,longitude', 'data-api-ok' => 'Lodge updated.', 'data-api-go' => '/host/cache-bust?scope=properties&go=' . urlencode('/host/listings')]) ?>
    <input type="hidden" name="name" value="<?= h($property['name'] ?? '') ?>">
    <input type="hidden" name="price_per_night" value="<?= h($property['price_per_night'] ?? '') ?>">
    <input type="hidden" name="description" value="<?= h($property['description'] ?? '') ?>">
    <input type="hidden" name="city" value="<?= h($property['city'] ?? '') ?>">
    <input type="hidden" name="area" value="<?= h($property['area'] ?? '') ?>">
    <input type="hidden" name="address" value="<?= h($property['address'] ?? '') ?>">
    <input type="hidden" name="latitude" value="<?= h($pLat) ?>">
    <input type="hidden" name="longitude" value="<?= h($pLng) ?>">
    <?php $coverInit = $property['image_url'] ?? ($property['primary_image_url'] ?? ''); ?>
    <input type="hidden" name="image_url" id="obCoverStatic" value="<?= h($coverInit) ?>">
    <dl class="ob-review">
      <div><dt>Property</dt><dd><?= h(($property['name'] ?? '—') . ' (' . ($property['property_type'] ?? $property['type'] ?? 'Lodge') . ')') ?></dd></div>
      <div><dt>Location</dt><dd><?= h(trim(($property['address'] ?? '') . ', ' . ($property['area'] ?? '') . ', ' . ($property['city'] ?? ''), ', ')) ?><br><span class="ob-dim"><?= h($pLat . ', ' . $pLng) ?></span></dd></div>
      <div><dt>Price</dt><dd>TSh <?= number_format((float)($property['price_per_night'] ?? 0)) ?> / night</dd></div>
      <?php $cover = $property['image_url'] ?? ($property['primary_image_url'] ?? ''); if ($cover !== ''): ?><div><dt>Cover</dt><dd><img src="<?= h($cover) ?>" alt="Cover photo"></dd></div><?php endif; ?>
      <?php if (!empty($property['description'])): ?><div><dt>About</dt><dd class="ob-dim-nm"><?= h($property['description']) ?></dd></div><?php endif; ?>
    </dl>
    <div class="ob-sec-h">Rooms (<?= count((array)($rooms ?? [])) ?>) — select to edit</div>
    <?php if (empty($rooms)): ?>
      <div class="p-card"><div class="p-empty">No rooms yet — <a href="<?= $this->Url->build('/host/rooms/add?property_id=' . $propId) ?>">add the first room</a>.</div></div>
    <?php else: ?>
      <?php foreach ($revCats as $rc): ?>
      <div class="ob-room ob-tight">
        <div class="ob-room-h"><span class="p-badge blue"><?= h($rc['type']) ?></span><b>TSh <?= number_format((float)$rc['price']) ?> / night</b></div>
        <div class="ob-rooms-line">Rooms:
          <?php foreach ($rc['rooms'] as $rr): ?><?php $rid = (int)($rr['id'] ?? 0); ?><?php if ($rid > 0): ?><a href="<?= $this->Url->build('/host/rooms/' . $rid) ?>"><strong><?= h($rr['room_number'] ?? $rr['name'] ?? '—') ?></strong></a><?php else: ?><strong><?= h($rr['room_number'] ?? $rr['name'] ?? '—') ?></strong><?php endif; ?> <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
    <div><label class="cds-label">Amenities (comma separated)</label><input name="amenities_raw" id="amen_raw" class="form-control cds-field" placeholder="Wifi, Pool, Parking" value="<?= h(implode(', ', $amenities)) ?>"></div>
    <div class="ob-nav">
      <a href="<?= $stepUrl(3) ?>" class="p-btn ghost cds-btn-flex">← Back</a>
      <button class="p-btn cds-btn-flex2">Save &amp; finish</button>
    </div>
  <?= $this->Form->end() ?>
  <?php endif; ?>
</div>

<?php if (($step ?? 1) === 2): ?>
<script src="https://api.mapbox.com/mapbox-gl-js/v3.1.2/mapbox-gl.js" defer></script>
<?php endif; ?>
<?php if (($step ?? 1) === 2 && !empty($mapToken)): ?>
<script>window.MAPBOX_TOKEN = <?= json_encode($mapToken) ?>;window.MAPBOX_STYLE = <?= json_encode($mapStyle ?? 'mapbox://styles/mapbox/streets-v12') ?>;</script>
<?php endif; ?>
<?= $this->element('host_onboard_js') ?>

<script>
// Draft a lodge description from real saved data -> POST /properties/{id}/generate-description
(function () {
  var btn  = document.getElementById('generateDescriptionBtn');
  var ta   = document.getElementById('lodgeDescription');
  if (!btn || !ta) return;

  btn.addEventListener('click', function () {
    var id = btn.getAttribute('data-property-id');
    if (!id) return;

    btn.disabled = true;
    var original = btn.innerHTML;
    btn.innerHTML = 'Drafting\u2026';

    fetch('/api/properties/' + encodeURIComponent(id) + '/generate-description', {
      method: 'POST',
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin'
    })
      .then(function (r) { return r.json().catch(function () { return {}; }); })
      .then(function (data) {
        if (data && data.description) {
          // Never clobber text the host already wrote.
          if (ta.value.trim()) {
            if (!window.confirm('Replace the description you already wrote with the drafted version?')) return;
          }
          ta.value = data.description;
        } else {
          window.alert((data && (data.message || data.error)) || 'Could not draft a description yet. Add some rooms and amenities first.');
        }
      })
      .catch(function () { window.alert('Could not reach the server. Please try again.'); })
      .finally(function () {
        btn.disabled = false;
        btn.innerHTML = original;
      });
  });
})();
</script>
