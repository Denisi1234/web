<?php
/**
 * Shared wizard JS: cover-photo dropzone upload + Mapbox location picker +
 * room-category builder + review amenities split (host onboarding + lodge edit).
 * Extracted verbatim from host-onboarding.php — wires only element IDs that
 * exist on the page, so edit pages reuse it safely. Edit here, both follow.
 */
$backendUrl = rtrim((string)\Cake\Core\Configure::read('App.backendApiUrl', 'http://127.0.0.1:8000/api'), '/');
?>
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
    function hideStatic() {
      var st = document.getElementById('obStatic');
      if (st && st.parentNode) st.parentNode.removeChild(st);
    }
    function unlockManual() {
      mapEl.classList.remove('loading');
      latEl.readOnly = false; lngEl.readOnly = false;
      latEl.classList.remove('locked'); lngEl.classList.remove('locked');
      if (geoMsg) geoMsg.textContent = 'Map offline — you may enter coordinates manually.';
    }
    // mapbox-gl.js may still be downloading (deferred CDN) — poll briefly
    // instead of giving up on the first check (was a flaky no-map).
    function waitForGL(cb) {
      var tries = 0;
      (function poll() {
        if (typeof mapboxgl !== 'undefined') { cb(true); return; }
        if (++tries >= 30) { cb(false); return; }
        setTimeout(poll, 100);
      })();
    }
    function initMap(token) {
      mbToken = token;
      mapEl.classList.add('loading');
      var preStyle = (typeof window.MAPBOX_STYLE === 'string' && window.MAPBOX_STYLE) ? window.MAPBOX_STYLE : 'mapbox://styles/mapbox/streets-v12';
      waitForGL(function (ok) {
        if (!ok) { unlockManual(); return; }
        mapboxgl.accessToken = token;
        try {
          map = new mapboxgl.Map({ container: 'obMap', style: preStyle, center: [DAR.lng, DAR.lat], zoom: 12 });
        } catch (e) { unlockManual(); return; }
        function ready() {
          mapEl.classList.remove('loading');
          hideStatic();
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
      });
    }
    // Server inlines window.MAPBOX_TOKEN on step 2 — skip the extra
    // /api/map-config round trip and start the map immediately.
    (function bootMap() {
      var pre = (typeof window.MAPBOX_TOKEN === 'string' && window.MAPBOX_TOKEN.indexOf('pk.') === 0) ? window.MAPBOX_TOKEN : '';
      if (pre) { initMap(pre); return; }
      fetch('/api/map-config', { headers: { 'Accept': 'application/json' } })
        .then(function (r) { return r.ok ? r.json() : null; })
        .then(function (j) {
          var t = j && (j.mapbox_token || j.mapboxToken || j.token);
          if (t && t.indexOf('pk.') === 0) initMap(t);
          else unlockManual();
        })
        .catch(unlockManual);
    })();
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
