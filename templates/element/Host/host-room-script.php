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

  function getAuthToken() {
    var meta = document.querySelector('meta[name="api-token"]');
    if (meta) {
      var val = (meta.getAttribute('content') || '').trim();
      if (val) return val;
    }
    try {
      var s = window.localStorage.getItem('auth_token') || window.localStorage.getItem('token') || window.sessionStorage.getItem('auth_token');
      if (s && s.trim()) return s.trim();
    } catch (e) {}
    try {
      var m = document.cookie.match(/fn_token=([^;]+)/);
      if (m && m[1]) return decodeURIComponent(m[1]).trim();
    } catch (e) {}
    return '';
  }

  function rfFailMsg(xhr, j) {
    if (xhr && xhr.status === 413) return 'Photo too large for the server (max 10 MB). Try a smaller photo.';
    if (xhr && (xhr.status === 401 || xhr.status === 403)) return 'Session expired — please sign in again.';
    if (j && j.message) return j.message;
    if (xhr && xhr.status >= 500) return 'Media server error — please try again in a moment.';
    return 'Photo upload failed.';
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
  // Failed optional photo → inline retry chip (keeps the File). Calm copy:
  // the room saves fine without photos, so it must not read like a failure.
  function rfFailChip(f, msg) {
    var chip = document.createElement('button');
    chip.type = 'button';
    chip.className = 'ph ph-retry';
    chip.setAttribute('aria-label', 'Retry uploading ' + (f.name || 'photo'));
    chip.innerHTML = '<span>Photo skipped — ' + msg + '</span><b>Tap to retry</b>';
    chip.onclick = function () {
      if (chip.parentNode) chip.parentNode.removeChild(chip);
      sendRfPhoto(f);
    };
    box.appendChild(chip);
    refreshCount();
    toast(msg + ' Room still saves without it.');
  }
  function sendRfPhoto(f) {
        if (!/^image\//.test(f.type)) { toast('Only image files please.'); return; }
        if (f.size > 10 * 1024 * 1024) { toast('Max 10 MB per photo.'); return; }
        uploading++;
        syncUp();
        var fd = new FormData();
        fd.append('file', f);

        var token = getAuthToken();
        var targetUrl = token ? (BACKEND + '/upload') : '/host/upload';

        function sendRoomPhoto(url, useAuth) {
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
                uploading--;
                syncUp();
                if (!hasUrl(photoUrl)) thumb(photoUrl);
                return;
              }
              if (url !== '/host/upload' && (xhr.status === 401 || xhr.status === 403 || xhr.status === 0)) {
                sendRoomPhoto('/host/upload', false);
                return;
              }
              uploading--;
              syncUp();
              rfFailChip(f, rfFailMsg(xhr, j));
            } catch (e) {
              if (url !== '/host/upload') {
                sendRoomPhoto('/host/upload', false);
                return;
              }
              uploading--;
              syncUp();
              rfFailChip(f, rfFailMsg(xhr, null));
            }
          };
          xhr.onerror = function () {
            if (url !== '/host/upload') {
              sendRoomPhoto('/host/upload', false);
              return;
            }
            uploading--;
            syncUp();
            rfFailChip(f, 'No connection to media server.');
          };
          xhr.send(fd);
        }

        sendRoomPhoto(targetUrl, Boolean(token));
  }
  if (file) file.addEventListener('change', function () {
    var fs = file.files;
    for (var k = 0; k < fs.length; k++) sendRfPhoto(fs[k]);
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
