/* FastAPI — direct-to-backend submitter.
 *
 * Lets any <form data-api="METHOD /path"> send its data straight to the
 * backend API (e.g. https://api.fastnetstays.com/properties) with the
 * Bearer token from <meta name="api-token">, instead of proxying through
 * CakePHP. Controllers stay untouched as a fallback: on network/CORS
 * failure the form submits natively to CakePHP, which proxies as before.
 *
 * Attributes:
 *   data-api="POST /properties/{property_id}/rooms"  (required; {field} filled from values)
 *   data-api-strip="property_id,action"               (routing leftovers dropped from body)
 *   data-api-ok="Room created."                       (success toast; default generic)
 *   data-api-go="/host/rooms"                         (redirect on success; default reload)
 *   data-api-confirm="Delete staff?"                 (optional confirm)
 *
 * Collection rules: type=number → Number (empty dropped), checkbox → bool,
 * repeated names (photos[]) → arrays, amenities_raw → amenities[] split,
 * _csrfToken/_Token/_method dropped. Forms can signal an in-progress
 * upload via form.dataset.uploading === '1' (submit is held with a toast).
 */
window.FastAPI = (function () {
  // Base includes /api (matches App.backendApiUrl). Falls back to the
  // site-wide window.FASTNET_API_URL convention, then hostname sniffing.
  function base() {
    var m = document.querySelector('meta[name="api-base"]');
    var b = m ? (m.getAttribute('content') || '') : '';
    if (b) return b.replace(/\/+$/, '');
    if (typeof window.FASTNET_API_URL === 'string' && window.FASTNET_API_URL) {
      return window.FASTNET_API_URL.replace(/\/+$/, '') + '/api';
    }
    var h = window.location.hostname || '';
    return (h === 'localhost' || h === '127.0.0.1' || h === '') ? 'http://127.0.0.1:8000/api' : 'https://api.fastnetstays.com/api';
  }
  // Token: localStorage first (written at login, always freshest), then the
  // server-rendered meta (covers portal sessions without localStorage).
  function token() {
    try {
      var t = window.localStorage.getItem('auth_token') || window.localStorage.getItem('token') || window.sessionStorage.getItem('auth_token');
      if (t) return t;
    } catch (e) {}
    var m = document.querySelector('meta[name="api-token"]');
    return m ? (m.getAttribute('content') || '') : '';
  }
  // Professional toast: white card, colored accent + glyph by kind.
  function toast(msg, kind) {
    var styles = {
      ok:   { bar: '#24a148', glyph: '✓' },
      err:  { bar: '#da1e28', glyph: '!' },
      info: { bar: '#0f62fe', glyph: 'i' }
    };
    var st = styles[kind] || styles.info;
    var t = document.createElement('div');
    t.setAttribute('role', 'status');
    t.style.cssText = 'position:fixed;bottom:20px;left:50%;transform:translateX(-50%);background:#fff;color:#161616;border:1px solid #e0e0e0;border-left:4px solid ' + st.bar + ';border-radius:8px;box-shadow:0 6px 20px rgba(0,0,0,.12);padding:10px 16px;font-size:13px;font-weight:500;z-index:4000;max-width:92vw;display:flex;align-items:center;gap:8px;font-family:inherit';
    var g = document.createElement('span');
    g.textContent = st.glyph;
    g.style.cssText = 'flex:none;width:18px;height:18px;border-radius:50%;background:' + st.bar + ';color:#fff;font-size:11px;font-weight:700;display:inline-flex;align-items:center;justify-content:center';
    var s = document.createElement('span');
    s.textContent = msg;
    t.appendChild(g);
    t.appendChild(s);
    document.body.appendChild(t);
    setTimeout(function () { t.parentNode && t.parentNode.removeChild(t); }, 3200);
  }
  // Dot loader on any button. Delegates to the canonical implementation so the
  // markup, motion and aria handling match every other loading indicator.
  // (The previous version hand-rolled <span class="p-dots"> and set opacity,
  // which is why ~20 call sites had drifted into copy-pasted variants.)
  function btnDots(btn, on) {
    if (!btn) return;
    FastnetLoading.button(btn, !!on, { small: true });
  }
  function errText(status, json) {
    if (json) {
      if (typeof json.message === 'string' && json.message) return json.message;
      if (json.errors && typeof json.errors === 'object') {
        var flat = [];
        Object.keys(json.errors).forEach(function (k) {
          (Array.isArray(json.errors[k]) ? json.errors[k] : [json.errors[k]]).forEach(function (e) { flat.push(e); });
        });
        if (flat.length) return flat.slice(0, 3).join(' ');
      }
    }
    if (status === 401) return 'Session expired — please sign in again.';
    if (status === 422) return 'Please check the highlighted fields.';
    return 'Request failed. Please try again.';
  }

  async function req(method, path, body) {
    var url = base() + path;
    var headers = { 'Accept': 'application/json', 'Content-Type': 'application/json' };
    var tk = token();
    if (tk) headers['Authorization'] = 'Bearer ' + tk;
    var r = await fetch(url, {
      method: method,
      headers: headers,
      body: body === undefined ? undefined : JSON.stringify(body)
    });
    var json = null;
    try { json = await r.json(); } catch (e) { json = null; }
    if (r.status === 401) {
      var back = window.location.pathname + window.location.search;
      window.location.href = '/login?redirect=' + encodeURIComponent(back);
      throw new Error('unauthorized');
    }
    if (!r.ok) throw new Error(errText(r.status, json));
    return json;
  }

  function collect(form) {
    var out = {};
    var els = form.elements;
    for (var i = 0; i < els.length; i++) {
      var el = els[i];
      var name = el.name;
      if (!name || el.disabled) continue;
      if (name === '_csrfToken' || name === '_Token' || name === '_method') continue;
      if (name === 'amenities_raw') continue; // handled below
      if ((el.type === 'checkbox' || el.type === 'radio') && !el.checked) continue;
      var isMulti = name.slice(-2) === '[]';
      var key = isMulti ? name.slice(0, -2) : name;
      var val;
      if (el.type === 'checkbox') val = true;
      else if (el.type === 'number') {
        if (el.value === '') continue;
        val = Number(el.value);
        if (!isFinite(val)) continue;
      } else if (el.tagName === 'SELECT' && el.multiple) {
        val = Array.prototype.map.call(el.selectedOptions, function (o) { return o.value; });
      } else {
        val = el.value;
      }
      if (isMulti) {
        if (!Array.isArray(out[key])) out[key] = [];
        if (Array.isArray(val)) out[key] = out[key].concat(val);
        else if (val !== '') out[key].push(val);
      } else {
        out[key] = val;
      }
    }
    // Explicit numeric coercion for hidden inputs (type=hidden has no number type).
    var numKeys = (form.getAttribute('data-api-num') || '').split(',').map(function (s) { return s.trim(); });
    numKeys.forEach(function (k) {
      if (k && out[k] !== undefined && out[k] !== '') {
        var n = Number(out[k]);
        if (isFinite(n)) out[k] = n;
      }
    });
    // Drop listed keys when empty (optional fields like verify reason).
    var omitKeys = (form.getAttribute('data-api-omit-empty') || '').split(',').map(function (s) { return s.trim(); });
    omitKeys.forEach(function (k) {
      if (k && (out[k] === '' || out[k] === undefined)) delete out[k];
    });
    var amen = form.querySelector('[name="amenities_raw"]');
    if (amen) {
      // Always send (possibly empty) so clearing the field clears it server-side too.
      out.amenities = amen.value.split(',').map(function (s) { return s.trim(); }).filter(Boolean);
    }
    if (form.querySelector('[data-photos]') && !('photos' in out)) {
      // Gallery UI present but empty = explicit clear (backend keeps old photos otherwise).
      out.photos = [];
    }
    return out;
  }

  function setBusy(form, on) {
    btnDots(form.querySelector('button[type="submit"], input[type="submit"]'), on);
  }

  /* ---------- optimistic saves (data-opt): zero-loading ---------- */
  var BADGE_MAPS = {
    payout:  { def: 'yellow', m: { processing: 'yellow', paid: 'green', failed: 'red' } },
    booking: { def: 'yellow', m: { confirmed: 'blue', completed: 'green', cancelled: 'red', canceled: 'red' } },
    verify:  { def: 'yellow', m: { approved: 'green', rejected: 'red', suspended: 'red', changes_requested: 'blue' } },
    lodge:   { def: 'blue',   m: { approved: 'green', rejected: 'red', changes_requested: 'blue', active: 'green', pending: 'yellow' } },
    ticket:  { def: 'blue',   m: { open: 'yellow', resolved: 'green' } },
    request: { def: 'yellow', m: { completed: 'green', cancelled: 'red' } }
  };
  function optScope(form) {
    var s = form.getAttribute('data-opt-scope') || 'closest:tr';
    if (s.indexOf('closest:') === 0 && form.closest) return form.closest(s.slice(8));
    try { return document.querySelector(s); } catch (e) { return null; }
  }
  function snapshot(scope) { return scope ? scope.outerHTML : null; }
  function restore(scope, html) {
    if (!scope || html == null) return;
    try {
      var t = document.createElement('template');
      t.innerHTML = String(html).trim();
      var fresh = t.content.firstChild;
      if (fresh) scope.replaceWith(fresh);
    } catch (e) {}
  }
  // data-opt-badge=".p-badge" + data-opt-badgesrc="status" (+idx) +
  // data-opt-badgemap="payout" + data-opt-badgetext="raw|lower"
  function applyBadge(form, data) {
    var sel = form.getAttribute('data-opt-badge');
    if (!sel) return;
    var scope = optScope(form);
    var list = scope && scope.querySelectorAll ? scope.querySelectorAll(sel) : [];
    var idx = parseInt(form.getAttribute('data-opt-badge-idx') || '0', 10) || 0;
    var node = list[idx] || null;
    if (!node && scope && scope.matches && scope.matches(sel)) node = scope;
    if (!node) return;
    var src = form.getAttribute('data-opt-badgesrc') || 'status';
    var v = data[src];
    if (v === undefined) {
      var el = form.querySelector('[name="' + src + '"]');
      v = el ? el.value : '';
    }
    v = String(v == null ? '' : v);
    node.textContent = (form.getAttribute('data-opt-badgetext') || 'raw') === 'lower' ? v.toLowerCase() : v;
    var map = BADGE_MAPS[form.getAttribute('data-opt-badgemap')] || BADGE_MAPS.verify;
    node.className = 'p-badge ' + (map.m[v.toLowerCase()] || map.def);
  }
  function closeModal(form) {
    var s = form.getAttribute('data-opt-close');
    if (!s) return;
    var m = (s.indexOf('closest:') === 0 && form.closest) ? form.closest(s.slice(8)) : null;
    if (!m) { try { m = document.querySelector(s); } catch (e) { m = null; } }
    if (!m) return;
    try {
      if (window.bootstrap && window.bootstrap.Modal) {
        var inst = window.bootstrap.Modal.getInstance(m);
        if (inst) { inst.hide(); return; }
      }
    } catch (e) {}
    m.style.display = 'none';
    document.querySelectorAll('.modal-backdrop').forEach(function (b) { b.remove(); });
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
  }
  function bustBase() {
    return window.location.pathname.indexOf('/admin') === 0 ? '/admin/cache-bust' : '/host/cache-bust';
  }
  function quietBust(csv) {
    if (!csv) return Promise.resolve();
    return fetch(bustBase() + '?scope=' + encodeURIComponent(csv) + '&quiet=1', { credentials: 'same-origin' }).then(function () {}, function () {});
  }
  function runScripts(root) {
    root.querySelectorAll('script').forEach(function (old) {
      try {
        if (old.src) {
          if (document.querySelector('script[src="' + old.src + '"]')) { old.remove(); return; }
          var ext = document.createElement('script');
          ext.src = old.src;
          if (old.defer) ext.defer = true;
          old.replaceWith(ext);
        } else if (old.textContent.trim()) {
          var inline = document.createElement('script');
          inline.textContent = old.textContent;
          old.replaceWith(inline);
        }
      } catch (e) {}
    });
  }
  // Silent content refresh (no loader): re-fetch page, swap main + flash.
  function silentRefresh() {
    return fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
      .then(function (r) { return r.ok ? r.text() : ''; })
      .then(function (html) {
        if (!html) return;
        var doc;
        try { doc = new DOMParser().parseFromString(html, 'text/html'); } catch (e) { return; }
        var main = doc.getElementById('main-content'), cur = document.getElementById('main-content');
        if (main && cur) { cur.innerHTML = main.innerHTML; runScripts(cur); }
        var fl = doc.querySelector('.p-flash'), cf = document.querySelector('.p-flash');
        if (fl && cf) cf.innerHTML = fl.innerHTML;
      }).catch(function () {});
  }
  function pendingNotice(msg) {
    try { window.localStorage.setItem('fapi_notice', JSON.stringify({ msg: msg, kind: 'ok', t: Date.now() })); } catch (e) {}
  }
  (function bootNotice() {
    try {
      var raw = window.localStorage.getItem('fapi_notice');
      if (!raw) return;
      window.localStorage.removeItem('fapi_notice');
      var o = JSON.parse(raw);
      if (o && o.msg && Date.now() - (o.t || 0) < 120000) setTimeout(function () { toast(o.msg, o.kind || 'ok'); }, 350);
    } catch (e) {}
  })();

  // Named payload builders (data-api-build="property") for shapes that need
  // server-side defaults applied client-side. Mirrors controller code 1:1.
  var builders = {
    // Mirrors HostController::profile() — drops empties, duplicates phone.
    profile: function (d) {
      var out = {};
      Object.keys(d).forEach(function (k) {
        var v = typeof d[k] === 'string' ? d[k].trim() : d[k];
        if (v !== '' && v !== undefined) out[k] = v;
      });
      var phone = out.phone || out.phone_number || '';
      if (phone) { out.phone = phone; out.phone_number = phone; }
      return out;
    },
    property: function (d) {      d.city = (d.city || 'Dar es Salaam').toString().trim() || 'Dar es Salaam';
      d.area = (d.area || '').toString().trim() || d.city;
      d.price_per_night = Number(d.price_per_night) || 0;
      d.latitude = isFinite(Number(d.latitude)) ? Number(d.latitude) : -6.7924;
      d.longitude = isFinite(Number(d.longitude)) ? Number(d.longitude) : 39.2083;
      ['name', 'description', 'address', 'image_url'].forEach(function (k) {
        d[k] = d[k] == null ? '' : String(d[k]).trim();
      });
      return d;
    }
  };

  async function submitDirect(form) {
    var spec = (form.getAttribute('data-api') || '').trim().split(/\s+/);
    var method = (spec[0] || 'POST').toUpperCase();
    var path = spec.slice(1).join(' ') || '/';
    var data = collect(form);
    var build = form.getAttribute('data-api-build');
    if (build && builders[build]) data = builders[build](data, form) || data;
    path = path.replace(/\{(\w+)\}/g, function (m, k) { return encodeURIComponent(data[k] == null ? '' : data[k]); });
    var strip = (form.getAttribute('data-api-strip') || '').split(',').map(function (s) { return s.trim(); });
    strip.forEach(function (k) { if (k) delete data[k]; });
    if (form.dataset.uploading === '1') {
      toast('Wait for photos to finish uploading.');
      return;
    }
    // Restore native required-field validation (bypassed by direct submit).
    var reqs = form.querySelectorAll('[required]');
    for (var ri = 0; ri < reqs.length; ri++) {
      var rEl = reqs[ri];
      if (rEl.disabled || rEl.type === 'hidden') continue;
      if (!String(rEl.value == null ? '' : rEl.value).trim()) {
        toast('Please fill in the required fields.', 'err');
        try { rEl.focus(); } catch (e2) {}
        return;
      }
    }
    var go = form.getAttribute('data-api-go');
    var okMsg = form.getAttribute('data-api-ok') || 'Saved.';
    // data-opt modes: patch (flip row instantly, stay), remove (drop row
    // instantly, stay), refresh (stay + silent refresh), go (instant
    // feedback then navigate once saved). All save in the background.
    var opt = form.getAttribute('data-opt');
    var bust = form.getAttribute('data-opt-bust');
    var scope = null, snap = null;
    if (opt === 'patch' || opt === 'remove') {
      scope = optScope(form);
      snap = snapshot(scope);
      if (opt === 'remove') { if (scope) scope.remove(); }
      else applyBadge(form, data);
      setBusy(form, true);
    } else if (opt === 'refresh') {
      closeModal(form);
      toast('Saving…');
      setBusy(form, true);
    } else if (opt === 'go') {
      setBusy(form, true);
      toast('Saving…');
    } else {
      setBusy(form, true);
    }
    try {
      await req(method, path, method === 'GET' || method === 'DELETE' ? undefined : data);
      if (opt === 'patch' || opt === 'remove') {
        setBusy(form, false);
        toast(okMsg, 'ok');
        quietBust(bust);
      } else if (opt === 'refresh') {
        setBusy(form, false);
        toast(okMsg, 'ok');
        quietBust(bust).then(silentRefresh);
      } else if (opt === 'go') {
        pendingNotice(okMsg);
        window.location.href = go || window.location.href;
      } else {
        // Instant redirect — show toast on next page via pendingNotice (no artificial delay)
        pendingNotice(okMsg);
        if (go) window.location.href = go;
        else window.location.reload();
      }
    } catch (e) {
      if ((opt === 'patch' || opt === 'remove') && scope && snap) restore(scope, snap);
      setBusy(form, false);
      if (e instanceof TypeError) {
        // Network/CORS/offline → fall back to native CakePHP submit (proxy path).
        form.dataset.fapi = '1';
        form.submit();
        return;
      }
      if (!e || e.message !== 'unauthorized') toast((e && e.message) || 'Request failed. Please try again.', 'err');
    }
  }

  document.addEventListener('submit', function (e) {
    var form = e.target && e.target.tagName === 'FORM' ? e.target : null;
    if (!form || !form.getAttribute('data-api')) return;
    if (form.dataset.fapi === '1') return; // fallback native submit in progress
    e.preventDefault();
    e.stopPropagation(); // take precedence over portal Fast Save bubble handler
    var c = form.getAttribute('data-api-confirm');
    if (c) {
      // Use professional confirm modal; fnsConfirm resolves async
      var confirmFn = (typeof window.fnsConfirm === 'function') ? window.fnsConfirm : function (msg) {
        return Promise.resolve(window.confirm(msg));
      };
      confirmFn(c).then(function (ok) { if (ok) submitDirect(form); });
      return;
    }
    submitDirect(form);
  }, true);

  return { req: req, toast: toast, btnDots: btnDots, base: base, token: token, collect: collect };
})();
