/* FastNet host onboarding — split for the 300-line cap. Loads after fastnet-api.js; order: upload, map-search, map-util, map-main, rooms. */
(function () {
  var BACKEND = (function () { var m = document.querySelector('meta[name="api-base"]'); var b = m ? (m.getAttribute('content') || '') : ''; return (b || 'http://127.0.0.1:8000/api').replace(/\/+$/, ''); })();

  function toast(msg) {
    var t = document.createElement('div');
    t.textContent = msg;
    t.style.cssText = 'position:fixed;bottom:20px;left:50%;transform:translateX(-50%);background:#161616;color:#fff;padding:10px 18px;font-size:13px;z-index:3000';
    document.body.appendChild(t);
    setTimeout(function(){ t.parentNode && t.parentNode.removeChild(t); }, 2600);
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

  // Status-aware upload failure message — never a bare "Upload failed" when
  // the server actually told us why. 413s arrive as HTML (unparseable), so
  // the status code itself is the message there.
  function obFailMsg(xhr, j) {
    if (xhr && xhr.status === 413) return 'Photo too large for the server (max 10 MB). Try a smaller photo.';
    if (xhr && (xhr.status === 401 || xhr.status === 403)) return 'Session expired — please sign in again.';
    if (j && j.message) return j.message;
    if (xhr && xhr.status >= 500) return 'Media server error — please try again in a moment.';
    return 'Upload failed. Try again.';
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
    // Persistent inline upload error (stays until the next attempt or a
    // success). A 2.6s toast alone confused hosts: the property saved fine
    // afterwards and the scary message made no sense. One clear signal here.
    var upErr = document.getElementById('obUpErr');
    if (!upErr && bar && bar.parentNode) {
      upErr = document.createElement('div');
      upErr.id = 'obUpErr';
      upErr.className = 'field-err';
      upErr.style.display = 'none';
      bar.parentNode.insertBefore(upErr, bar.nextSibling);
    }
    function showUpErr(msg) {
      if (!upErr) { toast(msg); return; }
      upErr.textContent = msg + ' — tap the box to try again.';
      upErr.style.display = 'block';
    }
    function hideUpErr() { if (upErr) upErr.style.display = 'none'; }
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
      hideUpErr();
      if (bar) { bar.style.display = 'block'; if (barFill) barFill.style.width = '30%'; }
      var fd = new FormData();
      fd.append('file', f);

      var token = getAuthToken();
      var targetUrl = token ? (BACKEND + '/upload') : '/host/upload';

      function sendCoverXhr(url, useAuth) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', url, true);
        xhr.setRequestHeader('Accept', 'application/json');
        if (useAuth && token) {
          xhr.setRequestHeader('Authorization', 'Bearer ' + token);
        }
        xhr.upload.onprogress = function (e) {
          if (e.lengthComputable && barFill) barFill.style.width = Math.round(e.loaded / e.total * 100) + '%';
        };
        xhr.onload = function () {
          try {
            var j = JSON.parse(xhr.responseText);
            var photoUrl = j.url || j.photo_url || (j.data && (j.data.url || j.data.photo_url));
            if (xhr.status >= 200 && xhr.status < 300 && photoUrl) {
              uploading--;
              if (bar) { if (barFill) barFill.style.width = '100%'; setTimeout(function(){ bar.style.display = 'none'; if (barFill) barFill.style.width = '0'; }, 400); }
              coverInput.value = photoUrl;
              if (photoErr) photoErr.style.display = 'none';
              hideUpErr();
              window.__obRenderPrev();
              return;
            }
            if (url !== '/host/upload' && (xhr.status === 401 || xhr.status === 403 || xhr.status === 0)) {
              sendCoverXhr('/host/upload', false);
              return;
            }
            uploading--;
            if (bar) bar.style.display = 'none';
            showUpErr(obFailMsg(xhr, j));
          } catch (e) {
            if (url !== '/host/upload') {
              sendCoverXhr('/host/upload', false);
              return;
            }
            uploading--;
            if (bar) bar.style.display = 'none';
            showUpErr(obFailMsg(xhr, null));
          }
        };
        xhr.onerror = function () {
          if (url !== '/host/upload') {
            sendCoverXhr('/host/upload', false);
            return;
          }
          uploading--;
          if (bar) bar.style.display = 'none';
          showUpErr('No connection to media server.');
        };
        xhr.send(fd);
      }

      sendCoverXhr(targetUrl, Boolean(token));
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
  window.__ob = { toast: toast, token: getAuthToken, backend: function () { return BACKEND; } };
})();
