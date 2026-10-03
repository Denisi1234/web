<?php
/**
 * Add-property onboarding wizard.
 *
 * Four client-side steps (Property → Location → Rooms → Review) that post once,
 * so a host finishes in a single sitting. HostController::create() creates the
 * property and then every room, one at a time, so one bad room row cannot lose
 * the rest; failures come back keyed by row index and are shown inline.
 *
 * Deliberately NO data-api attribute: fastnet-api.js intercepts those forms in
 * the capture phase and posts straight to the backend, which bypassed this
 * controller entirely — meaning lodge verification was never submitted even
 * though the page claimed it was.
 */
$roomErrors = is_array($roomErrors ?? null) ? $roomErrors : [];
// A non-zero id means the property already exists from a previous submit, so
// the wizard must only retry the rooms.
$propertyExists = (int)($createdPropertyId ?? 0) > 0;

$this->assign('title', $propertyExists ? 'Add rooms' : 'Add property');
$this->assign('portal_title', $propertyExists ? 'Add rooms' : 'Add property');
$this->assign('page_actions', '<a href="' . $this->Url->build('/host/listings') . '" class="p-btn ghost">Back to listings</a>');

$roomTypes = ['Standard', 'Deluxe', 'Superior', 'Suite', 'Family', 'Single', 'Twin', 'Double', 'Studio', 'Villa'];
$draft = is_array($draft ?? null) ? $draft : [];
?>
<?= $this->element('host_property_css') ?>
<style>
  .wz { max-width: 980px; }
  .wz-steps { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-bottom: 22px; }
  @media (max-width: 720px) { .wz-steps { grid-template-columns: repeat(2, 1fr); } }
  .wz-step {
    display: flex; align-items: center; gap: 10px; padding: 12px 14px;
    background: var(--cds-gray-10, #f4f4f4);
    border: 1px solid var(--cds-border-subtle, #e0e0e0);
    border-bottom: 3px solid transparent;
  }
  .wz-step[aria-current='step'] { background: #fff; border-bottom-color: var(--cds-blue-60, #0f62fe); }
  .wz-step[data-done='1'] .wz-num { background: #0e6027; color: #fff; }
  .wz-num {
    flex: 0 0 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center;
    background: var(--cds-gray-30, #c6c6c6); color: var(--cds-gray-100, #161616);
    font-size: 12px; font-weight: 600;
  }
  .wz-step[aria-current='step'] .wz-num { background: var(--cds-blue-60, #0f62fe); color: #fff; }
  .wz-step-t { font-size: 13px; font-weight: 600; line-height: 1.25; }
  .wz-step-s { font-size: 11px; color: var(--cds-gray-60, #6f6f6f); }
  .wz-pane { display: none; }
  .wz-pane.is-on { display: block; }
  .wz-nav { display: flex; gap: 10px; margin-top: 24px; }
  .wz-nav .p-btn { flex: 0 0 auto; }
  .wz-nav .spacer { flex: 1 1 auto; }

  /* Room rows */
  .wz-room { border: 1px solid var(--cds-border-subtle, #e0e0e0); padding: 16px; margin-bottom: 12px; background: #fff; }
  .wz-room.has-err { border-color: var(--cds-red-60, #da1e28); border-left: 3px solid var(--cds-red-60, #da1e28); }
  .wz-room-h { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
  .wz-room-h strong { font-size: 13px; }
  .wz-err { color: var(--cds-red-60, #da1e28); font-size: 12px; margin-top: 8px; }
  .wz-err:empty { display: none; }
  .wz-noscript {
    background: var(--cds-yellow-10, #fcf4d6); border: 1px solid #f1c21b;
    padding: 12px 14px; margin-bottom: 16px; font-size: 13px;
  }

  /* Cover image upload */
  .wz-file { position: relative; }
  .wz-file-input { position: absolute; inset: 0; opacity: 0; width: 100%; height: 100%; cursor: pointer; }
  .wz-file-lbl {
    display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px;
    min-height: 132px; padding: 18px; text-align: center; cursor: pointer;
    background: var(--cds-gray-10, #f4f4f4);
    border: 1px dashed var(--cds-gray-40, #8d8d8d);
    font-size: 14px; font-weight: 600; color: var(--cds-gray-100, #161616);
  }
  .wz-file-input:hover + .wz-file-lbl,
  .wz-file-input:focus-visible + .wz-file-lbl { border-color: var(--cds-blue-60, #0f62fe); background: #fff; }
  .wz-file-input:focus-visible + .wz-file-lbl { outline: 2px solid var(--cds-focus, #0f62fe); outline-offset: -2px; }
  .wz-file-input:disabled + .wz-file-lbl { opacity: .6; cursor: progress; }
  .wz-file-lbl i { font-size: 22px; color: var(--cds-blue-60, #0f62fe); }
  .wz-file-sub { font-size: 12px; font-weight: 400; color: var(--cds-gray-60, #6f6f6f); }
  .wz-file-preview { display: flex; gap: 14px; align-items: center; margin-top: 12px; }
  .wz-file-preview img { width: 132px; height: 92px; object-fit: cover; border: 1px solid var(--cds-gray-30, #c6c6c6); }
  .wz-file-name { font-size: 13px; font-weight: 600; margin-bottom: 8px; word-break: break-all; }

  /* Review summary */
  .wz-rev { display: grid; gap: 2px; }
  .wz-rev-row { display: grid; grid-template-columns: 190px 1fr; gap: 12px; padding: 9px 0; border-bottom: 1px solid var(--cds-gray-20, #e0e0e0); font-size: 14px; }
  .wz-rev-row dt { color: var(--cds-gray-60, #6f6f6f); font-size: 13px; }
  .wz-rev-row dd { margin: 0; font-weight: 500; }
  @media (max-width: 560px) { .wz-rev-row { grid-template-columns: 1fr; gap: 2px; } }

  /* Square corners to match /login, /signup and /join-us. This page is not
     inside .cx-auth, so the rule there does not reach it. */
  .wz .form-control, .wz .form-select,
  .wz .form-control:focus, .wz .form-control:hover,
  .wz .form-control.is-invalid, .wz .form-select:focus {
    border-radius: 0 !important;
  }
  .wz .form-control:focus, .wz .form-select:focus {
    outline: 2px solid var(--cds-focus, #0f62fe); outline-offset: -2px; border-color: var(--cds-blue-60, #0f62fe);
  }
  .wz .form-control.is-invalid { border-color: var(--cds-red-60, #da1e28); }
</style>

<div class="p-card wz">
  <?php if ($propertyExists): ?>
    <h3>Add rooms to your property</h3>
    <div class="sub">The property was created. Fix any highlighted rooms below and submit again — we will not create it twice.</div>
  <?php else: ?>
    <h3>New property</h3>
    <div class="sub">Four short steps. You can review everything before anything is saved.</div>
  <?php endif; ?>

  <noscript>
    <div class="wz-noscript">
      This wizard needs JavaScript for the step navigation. Without it, use
      <a href="<?= $this->Url->build('/host/onboarding') ?>">the property form</a> and
      <a href="<?= $this->Url->build('/host/rooms/add') ?>">add rooms</a> afterwards.
    </div>
  </noscript>

  <div class="wz-steps" role="list">
    <div class="wz-step" role="listitem" data-step="1" aria-current="step">
      <span class="wz-num">1</span><span><span class="wz-step-t">Property</span><br><span class="wz-step-s">Name &amp; rate</span></span>
    </div>
    <div class="wz-step" role="listitem" data-step="2">
      <span class="wz-num">2</span><span><span class="wz-step-t">Location</span><br><span class="wz-step-s">Where it is</span></span>
    </div>
    <div class="wz-step" role="listitem" data-step="3">
      <span class="wz-num">3</span><span><span class="wz-step-t">Rooms</span><br><span class="wz-step-s">Inventory</span></span>
    </div>
    <div class="wz-step" role="listitem" data-step="4">
      <span class="wz-num">4</span><span><span class="wz-step-t">Review</span><br><span class="wz-step-s">Confirm &amp; submit</span></span>
    </div>
  </div>

  <?= $this->Form->create(null, ['url' => ['action' => 'create'], 'class' => 'cds-pform wz-form', 'id' => 'wzForm']) ?>
    <div class="wz-pane is-on" data-pane="1">
      <div class="cds-field">
        <label for="prop-name">Property name <span class="req">*</span></label>
        <input id="prop-name" name="name" class="form-control cds-input" placeholder="Sunrise Lodge" required>
      </div>
      <div class="cds-row">
        <div class="cds-field">
          <label for="prop-city">City <span class="req">*</span></label>
          <input id="prop-city" name="city" class="form-control cds-input" placeholder="Dar es Salaam" required>
        </div>
        <div class="cds-field">
          <label for="prop-area">Area / neighbourhood <span class="req">*</span></label>
          <input id="prop-area" name="area" class="form-control cds-input" placeholder="Oyster Bay">
          <span class="cds-helper">Used for search filtering. Defaults to the city.</span>
        </div>
      </div>
      <div class="cds-field">
        <label for="prop-address">Street address</label>
        <input id="prop-address" name="address" class="form-control cds-input" placeholder="Plot 123, Njiro Road">
      </div>
      <div class="cds-row">
        <div class="cds-field">
          <label for="prop-price">From price per night (TSh) <span class="req">*</span></label>
          <input id="prop-price" name="price_per_night" class="form-control cds-input" placeholder="150000" type="number" min="1" step="any" required>
          <span class="cds-helper">Your cheapest room sets the listing price.</span>
        </div>
        <div class="cds-field">
          <label for="prop-amen">Property amenities</label>
          <input id="prop-amen" name="property_amenities" class="form-control cds-input" placeholder="Pool, WiFi, Airport shuttle">
          <span class="cds-helper">Comma separated.</span>
        </div>
      </div>
      <div class="cds-field">
        <label for="prop-desc">Description</label>
        <textarea id="prop-desc" name="description" class="form-control cds-input" rows="4" placeholder="What makes this property worth staying at?"></textarea>
      </div>
      <div class="cds-field">
        <label for="prop-img">Cover image</label>
        <!-- Real upload -> POST /api/upload. The returned public URL is carried
             into the single wizard submit through this hidden field, so the
             property still needs only one POST to create. -->
        <input type="hidden" name="image_url" id="prop-img-url" value="<?= h((string)($draft['image_url'] ?? '')) ?>">
        <div class="wz-file" id="wzFile">
          <input type="file" id="prop-img" accept="image/jpeg,image/png,image/webp" class="wz-file-input">
          <label class="wz-file-lbl" for="prop-img">
            <i class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i>
            <span id="wzFileText">Choose an image or drop it here</span>
            <span class="wz-file-sub">JPG, PNG or WebP · up to 10 MB · this is the photo guests see first</span>
          </label>
        </div>
        <div class="wz-file-preview" id="wzPreview" hidden>
          <img id="wzPreviewImg" alt="Cover image preview">
          <div>
            <div class="wz-file-name" id="wzFileName"></div>
            <button type="button" class="p-btn ghost" id="wzFileClear">Remove image</button>
          </div>
        </div>
        <div class="wz-err" id="wzFileErr"></div>
      </div>
    </div>

    <div class="wz-pane" data-pane="2">
      <div class="cds-row">
        <div class="cds-field">
          <label for="prop-lat">Latitude</label>
          <input id="prop-lat" name="latitude" class="form-control cds-input" placeholder="-6.7924" type="number" step="any" min="-90" max="90">
        </div>
        <div class="cds-field">
          <label for="prop-lng">Longitude</label>
          <input id="prop-lng" name="longitude" class="form-control cds-input" placeholder="39.2083" type="number" step="any" min="-180" max="180">
        </div>
      </div>
      <span class="cds-helper">
        Drives the map pin and "near me" search. Leave blank to use the default
        (Dar es Salaam, -6.7924 / 39.2083). Drag the pin on a map to get exact values.
      </span>
    </div>

    <div class="wz-pane" data-pane="3">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div class="cds-helper" style="margin:0">Add at least one room. You can add more later.</div>
        <button type="button" class="p-btn ghost" id="wzAddRoom">Add another room</button>
      </div>
      <div id="wzRooms"></div>
      <template id="wzRoomTpl">
        <div class="wz-room">
          <div class="wz-room-h">
            <strong class="wz-room-label">Room</strong>
            <button type="button" class="p-btn ghost wz-room-rm">Remove</button>
          </div>
          <div class="cds-row">
            <div class="cds-field">
              <label>Room number / name <span class="req">*</span></label>
              <input name="rooms[__I__][room_number]" class="form-control cds-input" placeholder="101">
            </div>
            <div class="cds-field">
              <label>Category</label>
              <select name="rooms[__I__][room_type]" class="form-select cds-input"><?php foreach ($roomTypes as $t): ?><option><?= h($t) ?></option><?php endforeach; ?></select>
            </div>
          </div>
          <div class="cds-row">
            <div class="cds-field">
              <label>Price per night (TSh) <span class="req">*</span></label>
              <input name="rooms[__I__][price]" class="form-control cds-input" type="number" min="1" step="any" placeholder="85000">
            </div>
            <div class="cds-field">
              <label>Sleeps</label>
              <input name="rooms[__I__][capacity]" class="form-control cds-input" type="number" min="1" value="2">
            </div>
          </div>
          <div class="cds-row">
            <div class="cds-field">
              <label>Bed setup</label>
              <input name="rooms[__I__][bed_configuration]" class="form-control cds-input" placeholder="1 King Bed">
            </div>
            <div class="cds-field">
              <label>Size</label>
              <input name="rooms[__I__][room_size]" class="form-control cds-input" placeholder="28 sqm">
            </div>
          </div>
          <div class="cds-row">
            <div class="cds-field">
              <label>Amenities</label>
              <input name="rooms[__I__][amenities]" class="form-control cds-input" placeholder="Wifi, AC, Mini bar">
            </div>
            <div class="cds-field">
              <label>Floor</label>
              <input name="rooms[__I__][floor]" class="form-control cds-input" placeholder="1">
            </div>
          </div>
          <div class="wz-err"></div>
        </div>
      </template>
    </div>

    <div class="wz-pane" data-pane="4">
      <h4 style="font-size:15px;font-weight:600;margin-bottom:10px">Check this looks right</h4>
      <dl class="wz-rev" id="wzReview"></dl>
    </div>

    <div class="wz-nav">
      <button type="button" class="p-btn ghost" id="wzBack" style="display:none">Back</button>
      <span class="spacer"></span>
      <button type="button" class="p-btn ghost" id="wzNext">Next</button>
      <button type="submit" class="p-btn" id="wzSubmit">Create property</button>
    </div>
  <?= $this->Form->end() ?>
</div>

<script>
(function () {
  'use strict';

  var form   = document.getElementById('wzForm');
  if (!form) return;

  // Steps 2-4 are display:none, and a required input inside a hidden pane is
  // not focusable — Chrome then refuses to submit with "An invalid form control
  // ... is not focusable". Validation is handled per-pane in JS instead.
  form.setAttribute('novalidate', 'novalidate');

  var panes    = Array.prototype.slice.call(form.querySelectorAll('.wz-pane'));
  // The step rail is rendered BEFORE the form element, so it must be queried
  // from the document. Scoping this to `form` silently yields an empty list and
  // leaves the first step highlighted for the whole wizard.
  var stepEls  = Array.prototype.slice.call(document.querySelectorAll('.wz-step'));
  var backBtn  = document.getElementById('wzBack');
  var nextBtn  = document.getElementById('wzNext');
  var submitBtn= document.getElementById('wzSubmit');
  var roomsBox = document.getElementById('wzRooms');
  var tpl      = document.getElementById('wzRoomTpl');
  var addBtn   = document.getElementById('wzAddRoom');
  var review   = document.getElementById('wzReview');

  // Errors keyed by row index from HostController::createRooms().
  var serverRoomErrors = <?= json_encode($roomErrors === [] ? new stdClass() : $roomErrors) ?>;
  var retryRoomsOnly   = <?= $propertyExists ? 'true' : 'false' ?>;

  var step = 1;
  var LAST = 4;

  function esc(v) {
    return String(v === undefined || v === null ? '' : v)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  function rows() {
    return Array.prototype.slice.call(roomsBox.querySelectorAll('.wz-room'));
  }

  function renumber() {
    rows().forEach(function (row, i) {
      row.querySelector('.wz-room-label').textContent = 'Room ' + (i + 1);
      row.dataset.index = String(i);
      Array.prototype.forEach.call(row.querySelectorAll('[name]'), function (el) {
        el.name = el.name.replace(/rooms\[\d+\]/, 'rooms[' + i + ']');
      });
      var rm = row.querySelector('.wz-room-rm');
      if (rm) rm.disabled = rows().length <= 1;
    });
  }

  function addRow(values) {
    var html = tpl.innerHTML.replace(/__I__/g, String(rows().length));
    var wrap = document.createElement('div');
    wrap.innerHTML = html;
    var row = wrap.firstElementChild;
    roomsBox.appendChild(row);

    if (values) {
      Object.keys(values).forEach(function (k) {
        var el = row.querySelector('[name*="[' + k + ']"]');
        if (el) el.value = values[k];
      });
    }

    // A failed row from a previous submit keeps its message.
    var idx = rows().length - 1;
    if (serverRoomErrors && serverRoomErrors[idx]) {
      row.classList.add('has-err');
      row.querySelector('.wz-err').textContent = serverRoomErrors[idx];
    }

    row.querySelector('.wz-room-rm').addEventListener('click', function () {
      row.remove();
      renumber();
    });

    renumber();
    return row;
  }

  function show(n) {
    // Clamp: an out-of-range step would hide every pane and every nav button,
    // leaving a dead form with no way forward.
    n = Math.min(Math.max(parseInt(n, 10) || 1, 1), LAST);
    step = n;
    panes.forEach(function (p) {
      p.classList.toggle('is-on', p.getAttribute('data-pane') === String(n));
    });
    stepEls.forEach(function (s) {
      var i = parseInt(s.getAttribute('data-step'), 10);
      if (i === n) { s.setAttribute('aria-current', 'step'); s.dataset.done = '0'; }
      else { s.removeAttribute('aria-current'); s.dataset.done = i < n ? '1' : '0'; }
    });
    backBtn.style.display = n > 1 ? '' : 'none';
    nextBtn.style.display = n < LAST ? '' : 'none';
    submitBtn.style.display = n === LAST ? '' : 'none';
    if (n === LAST) { renderReview(); }
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  /** Validate only the visible pane, so we never block on hidden fields. */
  function validatePane(n) {
    var pane = panes.filter(function (p) { return p.getAttribute('data-pane') === String(n); })[0];
    if (!pane) return true;
    var bad = null;

    Array.prototype.forEach.call(pane.querySelectorAll('input,select,textarea'), function (el) {
      if (el.disabled || el.closest('[hidden]')) return;
      var missing = el.hasAttribute('required') && String(el.value).trim() === '';
      // A room row only counts once it has been started.
      var inRoom = el.closest('.wz-room');
      var rowStarted = inRoom && (inRoom.querySelector('[name*="[room_number]"]').value.trim() !== ''
                                   || inRoom.querySelector('[name*="[price]"]').value !== '');
      if (inRoom && !rowStarted) return;
      if (missing) { el.classList.add('is-invalid'); bad = bad || el; }
      else { el.classList.remove('is-invalid'); }
    });

    if (bad) { bad.focus(); return false; }
    return true;
  }

  function val(sel) {
    var el = form.querySelector(sel);
    return el ? String(el.value).trim() : '';
  }

  function renderReview() {
    var html = '';
    function line(k, v) { html += '<div class="wz-rev-row"><dt>' + esc(k) + '</dt><dd>' + esc(v || '—') + '</dd></div>'; }

    if (!retryRoomsOnly) {
      line('Property', val('[name="name"]'));
      line('City', val('[name="city"]'));
      line('Area', val('[name="area"]'));
      line('Address', val('[name="address"]'));
      line('From price', val('[name="price_per_night"]') ? 'TSh ' + val('[name="price_per_night"]') : '');
      line('Amenities', val('[name="property_amenities"]'));
      line('Description', val('[name="description"]'));
      line('Cover image', fileUrl.value ? 'Uploaded (' + fileUrl.value.split('/').pop() + ')' : '');
      line('Coordinates', (val('[name="latitude"]') || '—') + ', ' + (val('[name="longitude"]') || '—'));
    }

    var list = rows().map(function (row, i) {
      var get = function (n) { var el = row.querySelector('[name*="[' + n + ']"]'); return el ? el.value.trim() : ''; };
      var num = get('room_number');
      var price = get('price');
      if (!num && !price) return '';
      var bits = [num || 'Room ' + (i + 1)];
      if (get('room_type')) bits.push(get('room_type'));
      if (price) bits.push('TSh ' + price);
      if (get('capacity')) bits.push('sleeps ' + get('capacity'));
      if (get('bed_configuration')) bits.push(get('bed_configuration'));
      return bits.join(' · ');
    }).filter(Boolean);

    html += '<div class="wz-rev-row"><dt>Rooms (' + list.length + ')</dt><dd>'
         + (list.length ? esc(list.join('  |  ')) : 'None yet — the property will be created without rooms.')
         + '</dd></div>';

    review.innerHTML = html;
  }

  // ---- cover image upload ----------------------------------------
  // Uploads go straight to the backend's POST /api/upload (multipart), then the
  // returned public URL is carried into the single wizard POST via a hidden
  // field. The endpoint already requires auth, validates image/* up to 10 MB and
  // stores under a random filename, so nothing here trusts the client file.
  var fileInput  = document.getElementById('prop-img');
  var fileUrl    = document.getElementById('prop-img-url');
  var fileErr    = document.getElementById('wzFileErr');
  var fileText   = document.getElementById('wzFileText');
  var fileName   = document.getElementById('wzFileName');
  var preview    = document.getElementById('wzPreview');
  var previewImg = document.getElementById('wzPreviewImg');
  var clearBtn   = document.getElementById('wzFileClear');
  var MAX_BYTES  = 10 * 1024 * 1024;
  var uploading  = false;

  function apiBase() {
    if (window.FastAPI && typeof FastAPI.base === 'function') return FastAPI.base();
    return '';
  }
  function authToken() {
    if (window.FastAPI && typeof FastAPI.token === 'function') return FastAPI.token();
    try { return localStorage.getItem('auth_token') || localStorage.getItem('token') || ''; } catch (e) { return ''; }
  }

  function setFileError(msg) {
    fileErr.textContent = msg || '';
    if (msg) { fileInput.classList.add('is-invalid'); } else { fileInput.classList.remove('is-invalid'); }
  }

  function showPreview(url, name) {
    previewImg.src = url;
    fileName.textContent = name;
    preview.hidden = false;
  }

  function resetFile() {
    fileUrl.value = '';
    fileInput.value = '';
    preview.hidden = true;
    previewImg.removeAttribute('src');
    fileText.textContent = 'Choose an image or drop it here';
    setFileError('');
  }

  clearBtn.addEventListener('click', function () {
    resetFile();
    fileInput.focus();
  });

  fileInput.addEventListener('change', function () {
    var f = fileInput.files && fileInput.files[0];
    if (!f) return;

    setFileError('');
    if (!/^image\/(jpeg|png|webp)$/.test(f.type)) {
      resetFile();
      setFileError('Cover image must be a JPG, PNG or WebP file.');
      return;
    }
    if (f.size > MAX_BYTES) {
      resetFile();
      setFileError('That image is ' + Math.round(f.size / 1048576) + ' MB. The limit is 10 MB.');
      return;
    }

    uploading = true;
    fileInput.disabled = true;
    fileText.textContent = 'Uploading…';

    var fd = new FormData();
    fd.append('file', f);
    var headers = { 'Accept': 'application/json' };
    var tok = authToken();
    if (tok) headers['Authorization'] = 'Bearer ' + tok;

    fetch(apiBase() + '/upload', { method: 'POST', body: fd, headers: headers })
      .then(function (r) {
        return r.json().catch(function () { return {}; }).then(function (j) { return { ok: r.ok, status: r.status, body: j }; });
      })
      .then(function (res) {
        uploading = false;
        fileInput.disabled = false;
        if (res.ok && res.body && res.body.url) {
          fileUrl.value = res.body.url;
          fileText.textContent = 'Image ready';
          showPreview(res.body.url, f.name);
          return;
        }
        resetFile();
        setFileError((res.body && (res.body.message || res.body.error))
          || (res.status === 401 ? 'Your session expired. Sign in again and retry.' : 'Upload failed. Please try again.'));
      })
      .catch(function () {
        uploading = false;
        fileInput.disabled = false;
        resetFile();
        setFileError('Could not reach the upload service. Check your connection.');
      });
  });

  // Drag & drop
  ['dragenter', 'dragover'].forEach(function (ev) {
    fileInput.addEventListener(ev, function (e) { e.preventDefault(); });
  });
  fileInput.addEventListener('drop', function (e) {
    e.preventDefault();
    if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0]) {
      fileInput.files = e.dataTransfer.files;
      fileInput.dispatchEvent(new Event('change'));
    }
  });

  // ---- seed rooms -------------------------------------------------
  var posted = <?= json_encode((array)$this->getRequest()->getData()) ?>;
  var seeded = posted && typeof posted.rooms === 'object' && posted.rooms !== null ? posted.rooms : [];
  if (seeded && typeof seeded === 'object') {
    Object.keys(seeded).forEach(function (k) { addRow(seeded[k]); });
  }
  if (rows().length === 0) addRow({ capacity: 2 });

  addBtn.addEventListener('click', function () {
    addRow({ capacity: 2 });
    var last = rows()[rows().length - 1];
    last.scrollIntoView({ behavior: 'smooth', block: 'center' });
  });

  nextBtn.addEventListener('click', function () {
    if (!validatePane(step)) return;
    show(step + 1);
  });
  backBtn.addEventListener('click', function () { show(step - 1); });

  // Land on the first pane that still needs work.
  show(<?= $propertyExists && $roomErrors !== [] ? 3 : 1 ?>);

  form.addEventListener('submit', function (e) {
    if (uploading) {
      setFileError('Wait for the image to finish uploading.');
      e.preventDefault();
      return;
    }
    // Validate every pane, not just the visible one.
    for (var n = 1; n <= LAST; n++) {
      if (!validatePane(n)) { show(n); e.preventDefault(); return; }
    }
    submitBtn.disabled = true;
    submitBtn.textContent = 'Creating…';
  });
})();
</script>