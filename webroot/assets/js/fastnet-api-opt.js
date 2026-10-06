/* FastNet direct-to-backend. Split for the 300-line cap — load core, opt-ui, submit in order. */
window.FastAPI = window.FastAPI || {};
window.FastAPIOpt = (function () {
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
      if (o && o.msg && Date.now() - (o.t || 0) < 120000) setTimeout(function () { (window.FastAPI.toast || function () {})(o.msg, o.kind || 'ok'); }, 350);
    } catch (e) {}
  })();

  // Named payload builders (data-api-build="property") for shapes that need
  // server-side defaults applied client-side. Mirrors controller code 1:1.
  var builders = {
    // Mirrors AdminOwnerController::verify() — the direct path must send the
    // exact payload the server proxy sends, or rows fail with 422 while the
    // UI already flipped the badge (the "can't approve/reject" trash look).
    // Duplicates reason across every key any backend build validates and
    // normalises lodge approve to the backend's capitalised Active.
    verify: function (d, form) {
      var out = {};
      Object.keys(d).forEach(function (k) {
        var v = typeof d[k] === 'string' ? d[k].trim() : d[k];
        if (v !== '' && v !== undefined) out[k] = v;
      });
      // Owner rows need lowercase approved/rejected/…; lodge rows need the
      // capitalised Active/Pending/Removed set. Detect from the target path
      // so one builder serves both without corrupting either.
      var api = form && form.getAttribute ? (form.getAttribute('data-api') || '') : '';
      var isLodge = api.indexOf('/lodge') !== -1 || api.indexOf('/property') !== -1;
      var st = String(out.status || '');
      var low = st.toLowerCase();
      if (isLodge) {
        if (low === 'approved' || low === 'approve' || low === 'active') out.status = 'Active';
        else if (low === 'rejected' || low === 'reject') out.status = 'rejected';
        else if (low === 'changes_requested') out.status = 'changes_requested';
        else if (low === 'pending') out.status = 'Pending';
        else if (low === 'removed' || low === 'suspended') out.status = 'Removed';
      } else {
        if (low === 'approved' || low === 'approve' || low === 'active') out.status = 'approved';
        else if (low === 'rejected' || low === 'reject') out.status = 'rejected';
        else if (low === 'changes_requested') out.status = 'changes_requested';
        else if (low === 'suspended' || low === 'removed') out.status = 'suspended';
        else if (low === 'pending') out.status = 'pending';
      }
      var reason = String(out.reason || out.admin_notes || out.notes || '').trim();
      if (reason) { out.reason = reason; out.admin_notes = reason; out.notes = reason; }
      else { delete out.reason; delete out.admin_notes; delete out.notes; }
      return out;
    },
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
  return { builders: builders, optScope: optScope, snapshot: snapshot, restore: restore, applyBadge: applyBadge, closeModal: closeModal, quietBust: quietBust, silentRefresh: silentRefresh, pendingNotice: pendingNotice };
})();
