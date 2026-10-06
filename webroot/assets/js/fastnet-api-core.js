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
  // Token: the server-rendered meta FIRST. It is the token of the PHP session
  // that just rendered (and, on /admin, role-verified) this page. localStorage
  // can hold a stale token or another account's token from an earlier login in
  // the same browser — preferring it made every admin action fail with 401
  // (bounce to login) or 403 "Admin role required". localStorage is only a
  // fallback for pages rendered without a session token.
  function token() {
    var m = document.querySelector('meta[name="api-token"]');
    var mt = m ? (m.getAttribute('content') || '') : '';
    if (mt) return mt;
    try {
      return window.localStorage.getItem('auth_token') || window.localStorage.getItem('token') || window.sessionStorage.getItem('auth_token') || '';
    } catch (e) {}
    return '';
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
    if (!r.ok) {
      var err = new Error(errText(r.status, json));
      err.status = r.status;
      err.payload = json;
      throw err;
    }
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

  return { req: req, toast: toast, btnDots: btnDots, base: base, token: token, collect: collect, errText: errText };
})();
