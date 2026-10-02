<?php
$isEdit = (bool)($isEdit ?? false);
$this->assign('title', $isEdit ? 'Edit Room' : 'Add Room');
$this->assign('portal_title', $isEdit ? 'Edit room' : 'Add room');
$this->assign('page_actions', '<a href="' . $this->Url->build('/host/rooms') . '" class="p-btn ghost">Back to rooms</a>');
$room = is_array($room ?? null) ? $room : [];
$properties = is_array($properties ?? null) ? $properties : [];
$propId = $room['property_id'] ?? ($room['property']['id'] ?? ($properties[0]['id'] ?? ''));
$roomTypes = ['Standard', 'Deluxe', 'Suite', 'Executive'];
$curType = trim((string)($room['room_type'] ?? $room['type'] ?? 'Standard')) ?: 'Standard';
// Display labels without the stutter ("Suite Room" + "Room 101" => "Suite · Room 101").
$typeLabel = trim((string)preg_replace('/\s+room$/i', '', $curType)) ?: 'Standard';
$roomNumRaw = trim((string)($room['room_number'] ?? $room['name'] ?? ''));
$roomNumLabel = $roomNumRaw !== '' ? (preg_match('/^room\b/i', $roomNumRaw) ? $roomNumRaw : 'Room ' . $roomNumRaw) : '';

// Amenities may arrive as array (API) or string — onboarding add uses comma-separated text.
$amenRaw = $room['amenities'] ?? '';
if (is_array($amenRaw)) $amenRaw = implode(', ', array_map('strval', $amenRaw));
$amenRaw = trim((string)$amenRaw);

$backendUrl = rtrim((string)\Cake\Core\Configure::read('App.backendApiUrl', 'http://127.0.0.1:8000/api'), '/');
$backendHost = rtrim((string)preg_replace('#/api/?$#', '', $backendUrl), '/');
// Same normalizer as the front-end room cards: bare /storage/ paths break <img> without the host.
$normRoomImg = function (string $url) use ($backendHost): string {
    $url = trim($url);
    if ($url === '') return '';
    if (str_starts_with($url, '/storage/') || str_contains($url, '127.0.0.1:8000/storage') || str_contains($url, 'localhost/storage')) {
        $pos = strpos($url, '/storage/');
        if ($pos !== false) return $backendHost . substr($url, $pos);
    }
    return $url;
};

// Photos may arrive as strings, ['url' => …] maps, or a single string.
$photosRaw = $room['photos'] ?? $room['images'] ?? [];
if (is_string($photosRaw)) $photosRaw = [$photosRaw];
$photoUrls = [];
foreach ((array)$photosRaw as $ph) {
    $u = is_array($ph) ? ($ph['url'] ?? ($ph['image_url'] ?? '')) : (string)$ph;
    $u = $normRoomImg($u);
    if ($u !== '' && !in_array($u, $photoUrls, true)) $photoUrls[] = $u;
}
$capacity = $room['capacity'] ?? $room['max_adults'] ?? 2;
$maxAdults = $room['max_adults'] ?? $room['capacity'] ?? 2;
$bedsVal = $room['number_of_beds'] ?? 1;
?>
<?= $this->element('host_onboard_css') ?>

<div class="p-card obx">
  <h3><?= $isEdit ? 'Edit room' : 'New room' ?></h3>
  <div class="sub">
    <?php if ($isEdit): ?>
      Room #<?= h($room['id'] ?? '') ?><?= $roomNumLabel !== '' ? ' · ' . h($roomNumLabel) : '' ?>
    <?php else: ?>
      Creates one bookable room.
    <?php endif; ?>
  </div>

  <?php if (empty($properties)): ?>
    <div class="p-card"><div class="p-empty">No property yet — <a href="<?= $this->Url->build('/host/listings/add') ?>">create a property first</a>.</div></div>
  <?php else: ?>
  <?= $this->Form->create(null, [
    'url' => $isEdit ? ['action' => 'editRoom', $room['id'] ?? ''] : ['action' => 'addRoom'],
    'class' => 'cds-form',
    'id' => 'roomForm',
    'data-api' => $isEdit ? ('PUT /rooms/' . (int)($room['id'] ?? 0)) : 'POST /properties/{property_id}/rooms',
    'data-api-strip' => 'property_id',
    'data-api-ok' => $isEdit ? 'Room updated.' : 'Room created.', 'data-opt' => 'go',
    'data-api-go' => '/host/cache-bust?scope=rooms,properties&go=' . urlencode('/host/rooms'),
  ]) ?>
    <div>
      <label class="cds-label" for="rf-prop">Property *</label>
      <select id="rf-prop" name="property_id" class="form-select cds-field" <?= $isEdit ? 'disabled' : '' ?> required>
        <?php foreach ($properties as $p): ?>
          <option value="<?= h($p['id']) ?>" <?= (int)$propId === (int)($p['id'] ?? 0) ? 'selected' : '' ?>><?= h($p['name']) ?> — <?= h($p['city'] ?? '') ?></option>
        <?php endforeach; ?>
      </select>
      <?php if ($isEdit): ?><input type="hidden" name="property_id" value="<?= h($propId) ?>"><?php endif; ?>
      <div class="cds-hint">Rooms belong to a property — this can't be moved after creation.</div>
    </div>

    <div class="ob-room" data-i="0">
      <div class="ob-room-h"><b><span class="p-badge blue" id="rf-type-badge"><?= h($typeLabel) ?></span> <span class="ob-rn"><?= $isEdit ? h($roomNumLabel !== '' ? $roomNumLabel : 'Room details') : 'Room details' ?></span></b></div>
      <div class="row g-2">
        <div class="col-6 col-md-4"><label class="cds-label sm" for="rf-type">Category</label><select id="rf-type" name="room_type" class="form-select ob-cat-type cds-field"><?php foreach ($roomTypes as $t): ?><option <?= $typeLabel === $t || $curType === $t ? 'selected' : '' ?>><?= $t ?></option><?php endforeach; ?></select></div>
        <div class="col-6 col-md-4"><label class="cds-label sm" for="rf-price">Price TSh / night *</label><input id="rf-price" name="price" type="number" min="1" step="any" class="form-control cds-field" placeholder="85000" required value="<?= h($room['price'] ?? $room['customer_price'] ?? '') ?>"></div>
        <div class="col-6 col-md-4"><label class="cds-label sm" for="rf-cap">Sleeps (capacity)</label><input id="rf-cap" name="capacity" type="number" min="1" class="form-control cds-field" value="<?= h($capacity) ?>"></div>
      </div>
      <div class="mt-2">
        <label class="cds-label sm" for="rf-num">Room number *</label>
        <div class="ob-nums" data-nums>
          <div class="d-flex gap-2 mb-2"><input id="rf-num" name="room_number" class="form-control ob-num cds-field cds-num" placeholder="101" required value="<?= h($roomNumRaw) ?>"></div>
        </div>
        <div class="cds-hint">e.g. 101</div>
      </div>
      <div class="row g-2 mt-1">
        <div class="col-6"><label class="cds-label sm" for="rf-bed">Bed setup</label><input id="rf-bed" name="bed_configuration" class="form-control cds-field" placeholder="1 King Bed" value="<?= h($room['bed_configuration'] ?? $room['bed_type'] ?? '') ?>"></div>
        <div class="col-6"><label class="cds-label sm" for="rf-status">Status</label><select id="rf-status" name="status" class="form-select cds-field"><option value="available" <?= strtolower((string)($room['status'] ?? 'available')) === 'available' ? 'selected' : '' ?>>Available</option><option value="maintenance" <?= strtolower((string)($room['status'] ?? '')) === 'maintenance' ? 'selected' : '' ?>>Maintenance</option></select></div>
      </div>
      <div class="mt-2"><label class="cds-label sm" for="rf-amen">Amenities (comma separated)</label><input id="rf-amen" name="amenities_raw" class="form-control cds-field" placeholder="Wifi, AC, Mini bar" value="<?= h($amenRaw) ?>"></div>
      <div class="mt-2">
        <label class="cds-label sm" id="rf-photos-label">Room photos (<?= count($photoUrls) ?>)</label>
        <div class="cds-hint">First photo is the cover. JPG / PNG / WebP · max 10 MB.</div>
        <input type="file" id="rf-file" class="ob-rfile form-control cds-field" accept="image/jpeg,image/png,image/webp,image/gif" multiple>
        <?php if (empty($photoUrls)): ?>
          <div class="p-empty" style="margin-top:8px">No photos yet — upload below.</div>
        <?php endif; ?>
        <div class="ob-rphotos" id="rf-prev" data-photos>
          <?php foreach ($photoUrls as $i => $ph): ?>
            <div class="ph"><img src="<?= h($ph) ?>" alt="Photo <?= $i + 1 ?>" loading="lazy" onerror="this.style.display='none'"><button type="button" class="rm" aria-label="Remove photo <?= $i + 1 ?>">×</button><input type="hidden" name="photos[]" value="<?= h($ph) ?>"></div>
          <?php endforeach; ?>
        </div>
        <div class="field-err" id="rf-up-err">Uploads still running — wait a moment.</div>
      </div>
      <div class="mt-2">
        <label class="cds-label sm">Details</label>
        <div class="row g-2">
          <div class="col-6 col-md-4"><label class="cds-label sm" for="rf-floor">Floor</label><input id="rf-floor" name="floor" class="form-control cds-field" placeholder="1" value="<?= h($room['floor'] ?? '') ?>"></div>
          <div class="col-6 col-md-4"><label class="cds-label sm" for="rf-size">Room size</label><input id="rf-size" name="room_size" class="form-control cds-field" placeholder="28 sqm" value="<?= h($room['room_size'] ?? '') ?>"></div>
          <div class="col-6 col-md-4"><label class="cds-label sm" for="rf-beds">Beds</label><input id="rf-beds" name="number_of_beds" type="number" min="1" class="form-control cds-field" value="<?= h($bedsVal) ?>"></div>
          <div class="col-6 col-md-6"><label class="cds-label sm" for="rf-ad">Max adults</label><input id="rf-ad" name="max_adults" type="number" min="1" class="form-control cds-field" value="<?= h($maxAdults) ?>"></div>
          <div class="col-6 col-md-6"><label class="cds-label sm" for="rf-ch">Max children</label><input id="rf-ch" name="max_children" type="number" min="0" class="form-control cds-field" value="<?= h($room['max_children'] ?? 0) ?>"></div>
        </div>
      </div>
    </div>

    <div class="ob-nav">
      <a href="<?= $this->Url->build('/host/rooms') ?>" class="p-btn ghost cds-btn-flex">← Back</a>
      <button class="p-btn cds-btn-flex2" id="rf-save"><?= $isEdit ? 'Save changes' : 'Create room' ?></button>
    </div>
  <?= $this->Form->end() ?>
  <?php endif; ?>
</div>

<script>
(function () {
  var form = document.getElementById('roomForm');
  if (!form) return;
  var BACKEND = <?= json_encode($backendUrl) ?>;
  var typeSel = document.getElementById('rf-type');
  var badge = document.getElementById('rf-type-badge');
  if (typeSel && badge) typeSel.addEventListener('change', function () { badge.textContent = typeSel.value.replace(/\s+room$/i, ''); });

  function toast(msg) {
    var t = document.createElement('div');
    t.textContent = msg;
    t.style.cssText = 'position:fixed;bottom:20px;left:50%;transform:translateX(-50%);background:#161616;color:#fff;padding:10px 18px;font-size:13px;z-index:3000';
    document.body.appendChild(t);
    setTimeout(function () { t.parentNode && t.parentNode.removeChild(t); }, 2600);
  }

  /* photos: preview grid + file upload (same /upload endpoint as onboarding) */
  var box = document.getElementById('rf-prev');
  var file = document.getElementById('rf-file');
  var upErr = document.getElementById('rf-up-err');
  var uploading = 0;
  // Signal for direct-API submit (fastnet-api.js holds submit while '1').
  function syncUp() { try { form.dataset.uploading = uploading > 0 ? '1' : ''; } catch (e) {} }

  function thumb(url) {
    var n = box.querySelectorAll('.ph').length + 1;
    var d = document.createElement('div');
    d.className = 'ph';
    var img = document.createElement('img');
    img.alt = 'Photo ' + n; img.src = url; img.loading = 'lazy';
    img.onerror = function () { img.style.display = 'none'; };
    var rm = document.createElement('button');
    rm.type = 'button'; rm.className = 'rm'; rm.textContent = '×'; rm.setAttribute('aria-label', 'Remove photo ' + n);
    rm.onclick = function () { d.parentNode && d.parentNode.removeChild(d); refreshCount(); };
    var hid = document.createElement('input');
    hid.type = 'hidden'; hid.name = 'photos[]'; hid.value = url;
    d.appendChild(img); d.appendChild(rm); d.appendChild(hid);
    box.appendChild(d);
    refreshCount();
  }
  function refreshCount() {
    var lbl = document.getElementById('rf-photos-label');
    if (lbl) lbl.textContent = 'Room photos (' + box.querySelectorAll('.ph').length + ')';
  }
  function hasUrl(u) {
    return Array.prototype.some.call(box.querySelectorAll('input[name="photos[]"]'), function (el) { return el.value === u; });
  }
  box.querySelectorAll('.rm').forEach(function (b) {
    b.onclick = function () { var p = b.closest('.ph'); if (p) p.parentNode.removeChild(p); refreshCount(); };
  });
  if (file) file.addEventListener('change', function () {
    var fs = file.files;
    for (var k = 0; k < fs.length; k++) {
      (function (f) {
        if (!/^image\//.test(f.type)) { toast('Only image files please.'); return; }
        if (f.size > 10 * 1024 * 1024) { toast('Max 10 MB per photo.'); return; }
        uploading++;
        syncUp();
        var fd = new FormData();
        fd.append('file', f);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', BACKEND + '/upload', true);
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.onload = function () {
          uploading--;
          syncUp();
          try {
            var j = JSON.parse(xhr.responseText);
            if (xhr.status >= 200 && xhr.status < 300 && j.url) { if (!hasUrl(j.url)) thumb(j.url); }
            else toast((j && j.message) || 'Photo upload failed.');
          } catch (e) { toast('Photo upload failed.'); }
        };
        xhr.onerror = function () { uploading--; syncUp(); toast('No connection to media server.'); };
        xhr.send(fd);
      })(fs[k]);
    }
    file.value = '';
  });

  /* submit: block while uploading; split amenities text like onboarding review */
  form.addEventListener('submit', function (e) {
    if (uploading > 0) {
      e.preventDefault();
      if (upErr) upErr.style.display = 'block';
      toast('Wait for photos to finish uploading.');
      return;
    }
    form.querySelectorAll('input.js-gen').forEach(function (el) { el.parentNode.removeChild(el); });
    // Empty gallery = explicit clear so PUT sends photos: [] instead of omitting the key.
    if (!box.querySelector('input[name="photos[]"]')) {
      var clr = document.createElement('input');
      clr.type = 'hidden'; clr.className = 'js-gen'; clr.name = 'photos[]'; clr.value = '';
      form.appendChild(clr);
    }
    var raw = document.getElementById('rf-amen');
    (raw.value || '').split(',').map(function (s) { return s.trim(); }).filter(Boolean).forEach(function (val) {
      var i = document.createElement('input');
      i.type = 'hidden'; i.className = 'js-gen'; i.name = 'amenities[]'; i.value = val;
      form.appendChild(i);
    });
  });
})();
</script>
