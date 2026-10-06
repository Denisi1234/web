/* FastNet host onboarding — split for the 300-line cap. Loads after fastnet-api.js; order: upload, map-search, map-util, map-main, rooms. */
(function () {
  var __ob = window.__ob || {};
  function toast(m) { if (__ob.toast) __ob.toast(m); }
  function getAuthToken() { return __ob.token ? __ob.token() : ''; }
  var BACKEND = (__ob.backend ? __ob.backend() : 'http://127.0.0.1:8000/api');
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
    // Failed optional photo → inline retry chip (keeps the File, one tap to
    // retry). Calm copy: rooms save fine without photos, so the message must
    // never read like the whole property failed.
    function catFailChip(box, idx, f, msg) {
      var chip = document.createElement('button');
      chip.type = 'button';
      chip.className = 'ph ph-retry';
      chip.setAttribute('aria-label', 'Retry uploading ' + (f.name || 'photo'));
      chip.innerHTML = '<span>Photo skipped — ' + msg + '</span><b>Tap to retry</b>';
      chip.onclick = function () {
        if (chip.parentNode) chip.parentNode.removeChild(chip);
        sendCatPhoto(f, box, idx);
      };
      box.appendChild(chip);
      toast(msg + ' Room still saves without it.');
    }
    function sendCatPhoto(f, box, idx) {
      if (!/^image\//.test(f.type)) { toast('Only image files please.'); return; }
      if (f.size > 10 * 1024 * 1024) { toast('Max 10 MB per photo.'); return; }
      roomUp++;
      var fd = new FormData();
      fd.append('file', f);

      var token = getAuthToken();
      var targetUrl = token ? (BACKEND + '/upload') : '/host/upload';

      function sendRoomReq(url, useAuth) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', url, true);
        xhr.setRequestHeader('Accept', 'application/json');
        if (useAuth && token) {
          xhr.setRequestHeader('Authorization', 'Bearer ' + token);
        }
        xhr.onload = function () {
          try {
            var j = JSON.parse(xhr.responseText);
            var photoUrl = j.url || j.photo_url || (j.data && (j.data.url || j.data.photo_url));
            if (xhr.status >= 200 && xhr.status < 300 && photoUrl) {
              roomUp--;
              thumb(box, idx, photoUrl);
              return;
            }
            if (url !== '/host/upload' && (xhr.status === 401 || xhr.status === 403 || xhr.status === 0)) {
              sendRoomReq('/host/upload', false);
              return;
            }
            roomUp--;
            catFailChip(box, idx, f, obFailMsg(xhr, j));
          } catch (e) {
            if (url !== '/host/upload') {
              sendRoomReq('/host/upload', false);
              return;
            }
            roomUp--;
            catFailChip(box, idx, f, obFailMsg(xhr, null));
          }
        };
        xhr.onerror = function () {
          if (url !== '/host/upload') {
            sendRoomReq('/host/upload', false);
            return;
          }
          roomUp--;
          catFailChip(box, idx, f, 'No connection to media server.');
        };
        xhr.send(fd);
      }

      sendRoomReq(targetUrl, Boolean(token));
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
        for (var k = 0; k < fs.length; k++) sendCatPhoto(fs[k], box, idx);
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
