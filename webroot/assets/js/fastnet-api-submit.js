/* FastNet direct submit + optimistic UI driver. Loads after fastnet-api-core.js and fastnet-api-opt.js. */
(function () {
  var FastAPI = window.FastAPI || {};
  var Opt = window.FastAPIOpt || {};
  function setBusy(form, on) {
    (FastAPI.btnDots || function () {})(form.querySelector('button[type="submit"], input[type="submit"]'), on);
  }
  async function submitDirect(form) {
    var spec = (form.getAttribute('data-api') || '').trim().split(/\s+/);
    var method = (spec[0] || 'POST').toUpperCase();
    var path = spec.slice(1).join(' ') || '/';
    var data = FastAPI.collect(form);
    var build = form.getAttribute('data-api-build');
    if (build && builders[build]) data = builders[build](data, form) || data;
    path = path.replace(/\{(\w+)\}/g, function (m, k) { return encodeURIComponent(data[k] == null ? '' : data[k]); });
    var strip = (form.getAttribute('data-api-strip') || '').split(',').map(function (s) { return s.trim(); });
    strip.forEach(function (k) { if (k) delete data[k]; });
    if (form.dataset.uploading === '1') {
      FastAPI.toast('Wait for photos to finish uploading.');
      return;
    }
    // Restore native required-field validation (bypassed by direct submit).
    var reqs = form.querySelectorAll('[required]');
    for (var ri = 0; ri < reqs.length; ri++) {
      var rEl = reqs[ri];
      if (rEl.disabled || rEl.type === 'hidden') continue;
      if (!String(rEl.value == null ? '' : rEl.value).trim()) {
        FastAPI.toast('Please fill in the required fields.', 'err');
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
      scope = Opt.optScope(form);
      snap = Opt.snapshot(scope);
      if (opt === 'remove') { if (scope) scope.remove(); }
      else Opt.applyBadge(form, data);
      setBusy(form, true);
    } else if (opt === 'refresh') {
      Opt.closeModal(form);
      FastAPI.toast('Saving…');
      setBusy(form, true);
    } else if (opt === 'go') {
      setBusy(form, true);
      FastAPI.toast('Saving…');
    } else {
      setBusy(form, true);
    }
    // Verify goes to the one real route (POST /admin/verification/{type}/{id}).
    // The old "shape matrix" retried 404s against /verification/lodge/{id} —
    // that is the HOST submission endpoint, so a missing lodge could be
    // re-submitted as pending instead of reporting "not found".
    try {
      await FastAPI.req(method, path, method === 'GET' || method === 'DELETE' ? undefined : data);
      if (opt === 'patch' || opt === 'remove') {
        setBusy(form, false);
        FastAPI.toast(okMsg, 'ok');
        // Converge to backend truth: the badge already flipped optimistically,
        // but the list must re-render from the server (which just cleared its
        // cache via quietBust). Without this a failed write looks successful
        // until reload — the "trash" feel. Failure path below restores anyway.
        Opt.quietBust(bust).then(silentRefresh);
      } else if (opt === 'refresh') {
        setBusy(form, false);
        FastAPI.toast(okMsg, 'ok');
        Opt.quietBust(bust).then(silentRefresh);
      } else if (opt === 'go') {
        Opt.pendingNotice(okMsg);
        window.location.href = go || window.location.href;
      } else {
        // Instant redirect — show toast on next page via pendingNotice (no artificial delay)
        Opt.pendingNotice(okMsg);
        if (go) window.location.href = go;
        else window.location.reload();
      }
    } catch (e) {
      if ((opt === 'patch' || opt === 'remove') && scope && snap) Opt.restore(scope, snap);
      setBusy(form, false);
      if (e instanceof TypeError) {
        // Network/CORS/offline → fall back to native CakePHP submit (proxy path).
        form.dataset.fapi = '1';
        form.submit();
        return;
      }
      if (e && e.message === 'unauthorized') return; // FastAPI.req() already redirected to login
      var msg = (e && e.message) || 'Request failed. Please try again.';
      if (e && e.status >= 500) {
        // Backend itself failing — say NOT-saved plainly or admins keep
        // clicking a dead button thinking the portal is trash.
        msg = 'Backend error (' + e.status + ') — NOT saved. ' + msg;
      }
      FastAPI.toast(msg, 'err');
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
})();
