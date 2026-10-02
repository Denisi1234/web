<?php
$this->assign('title', 'Onboard Lodge');
$this->assign('portal_title', 'Add property');
$step = (int)($step ?? 1);
if ($step < 1 || $step > 5) $step = 1;
$d = is_array($draft ?? null) ? $draft : [];
$labels = [1 => 'Basics', 2 => 'Location', 3 => 'Photos', 4 => 'Rooms', 5 => 'Review'];
$roomTypes = ['Standard', 'Deluxe', 'Suite', 'Executive'];
$draftCats = (isset($d['roomCats']) && is_array($d['roomCats']) && !empty($d['roomCats'])) ? array_values($d['roomCats']) : [[]];
?>
<?= $this->Html->css('https://api.mapbox.com/mapbox-gl-js/v3.1.2/mapbox-gl.css') ?>
<?= $this->element('host_onboard_css') ?>

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
    <div id="obMap" aria-label="Property location map"><?php if (!empty($mapPreviewUrl)): ?><img id="obStatic" src="<?= h($mapPreviewUrl) ?>" alt="" aria-hidden="true" fetchpriority="high"><?php endif; ?></div>
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
      <button class="p-btn cds-btn-flex2" id="obLaunchBtn">Launch listing</button>
    </div>
  <?= $this->Form->end() ?>
  <script>
  /* Direct-API launch: property + rooms + verification straight to the backend.
   * Mirrors HostController::onboarding() step 5. Server draft is rendered
   * below (normal case). Network failure falls back to native CakePHP submit. */
  window.OB_DRAFT = <?= json_encode(is_array($d ?? null) ? $d : [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
  (function () {
    var form = document.getElementById('obReviewForm');
    if (!form || !window.FastAPI) return;
    function amenList(v) {
      return String(v || '').split(',').map(function (s) { return s.trim(); }).filter(Boolean);
    }
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      e.stopPropagation();
      var draft = window.OB_DRAFT || {};
      var amenRaw = document.getElementById('amen_raw');
      var amenities = amenList(amenRaw ? amenRaw.value : '') ;
      if (!amenities.length && Array.isArray(draft.amenities)) amenities = draft.amenities;
      var city = String(draft.city || 'Dar es Salaam').trim() || 'Dar es Salaam';
      var payload = {
        name: String(draft.name || '').trim(),
        description: String(draft.description || '').trim(),
        address: String(draft.address || '').trim(),
        city: city,
        area: String(draft.area || '').trim() || city,
        price_per_night: Number(draft.price_per_night) || 0,
        latitude: isFinite(Number(draft.latitude)) ? Number(draft.latitude) : -6.7924,
        longitude: isFinite(Number(draft.longitude)) ? Number(draft.longitude) : 39.2083,
        image_url: String(draft.image_url || '').trim(),
        amenities: amenities
      };
      if (!payload.name || !(payload.price_per_night > 0)) {
        window.FastAPI.toast('Basics are incomplete — back to step 1.');
        window.location.href = '/host/onboarding?step=1';
        return;
      }
      var btn = document.getElementById('obLaunchBtn');
      if (btn && window.FastAPI && FastAPI.btnDots) FastAPI.btnDots(btn, true);
      else if (btn) { btn.disabled = true; btn.setAttribute('aria-busy', 'true'); }
      var rooms = Array.isArray(draft.rooms) ? draft.rooms : [];
      var fails = [];
      window.FastAPI.req('POST', '/properties', payload).then(function (res) {
        var pid = 0;
        if (res) pid = parseInt((res.id || (res.data && res.data.id) || 0), 10) || 0;
        if (!(pid > 0)) throw new Error('Could not create listing.');
        var chain = Promise.resolve();
        rooms.forEach(function (rm) {
          chain = chain.then(function () {
            return window.FastAPI.req('POST', '/properties/' + pid + '/rooms', rm).catch(function () {
              fails.push(String((rm && rm.room_number) || '?'));
            });
          });
        });
        return chain.then(function () {
          return window.FastAPI.req('POST', '/verification/lodge/' + pid, {}).catch(function () {});
        }).then(function () {
          if (fails.length) window.FastAPI.toast('Rooms not created (' + fails.join(', ') + ') — add them under Rooms.');
          else window.FastAPI.toast('Property onboarded with ' + rooms.length + ' room(s).');
          setTimeout(function () {
            window.location.href = '/host/cache-bust?scope=rooms,properties&draft=1&go=' + encodeURIComponent('/host/rooms');
          }, 600);
        });
      }).catch(function (err) {
        if (err instanceof TypeError) { form.submit(); return; } // offline/CORS → server launch
        if (btn && window.FastAPI && FastAPI.btnDots) FastAPI.btnDots(btn, false);
        else if (btn) { btn.disabled = false; btn.removeAttribute('aria-busy'); }
        if (!err || err.message !== 'unauthorized') window.FastAPI.toast((err && err.message) || 'Could not create listing.');
      });
    }, true);
  })();
  </script>
  <?php endif; ?>
</div>

<?= $this->Html->script('https://api.mapbox.com/mapbox-gl-js/v3.1.2/mapbox-gl.js') ?>
<?php if (($step ?? 1) === 2 && !empty($mapToken)): ?>
<script>window.MAPBOX_TOKEN = <?= json_encode($mapToken) ?>;window.MAPBOX_STYLE = <?= json_encode($mapStyle ?? 'mapbox://styles/mapbox/streets-v12') ?>;</script>
<?php endif; ?>
<?= $this->element('host_onboard_js') ?>
