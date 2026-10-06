/* FastNet portal instant nav: clicks, hover-warm, form fast-save, history. Part 2 of 2 — load after portal-pjax-core.js; shares its globals. */
  function isPjaxable(a) {
    if (!a || !a.href || a.target === '_blank' || a.hasAttribute('download')) return false;
    if (a.hostname !== window.location.hostname) return false;
    var path = '';
    try { path = new URL(a.href, window.location.origin).pathname; } catch (e) { return false; }
    if (path === '/logout' || path.indexOf('/logout') === 0) return false;
    if (a.hasAttribute('data-no-pjax') || a.getAttribute('role') === 'button') return false;
    return true;
  }
  document.addEventListener('click', function (e) {
    if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
    var a = e.target.closest ? e.target.closest('a[href]') : null;
    if (!a || !isPjaxable(a)) return;
    if (a.target && a.target !== '_self') return;
    var href = a.getAttribute('href') || '';
    if (href.charAt(0) === '#' || href.indexOf('tel:') === 0 || href.indexOf('mailto:') === 0 || href.indexOf('javascript:') === 0) return;

    var path = '';
    try { path = new URL(a.href, window.location.origin).pathname; } catch (err) { path = ''; }
    var isPortalNav = (path.indexOf('/host') === 0 || path.indexOf('/admin') === 0);

    if (isPortalNav && (a.closest('.p-side, .p-top, #main-content') || a.classList.contains('p-brand'))) {
      e.preventDefault();
      navigate(a, a.href);
      return;
    }

    // App loading indicator for every other same-origin navigation
    // (external links, full downloads): progress bar + button dots.
    if (a.dataset.loading === '1') return;
    a.dataset.loading = '1';
    // Shared dot loader (fastnet-api.js) with inline fallback.
    if (window.FastAPI && FastAPI.btnDots) FastAPI.btnDots(a, true);
    else if (!a.querySelector('.p-dots')) {
      var d = document.createElement('span');
      d.className = 'p-dots';
      d.setAttribute('aria-hidden', 'true');
      d.innerHTML = '<span class="p-dot"></span><span class="p-dot"></span><span class="p-dot"></span>';
      a.insertBefore(d, a.firstChild);
    }
    barStart();
  });
  // Warm cache on hover / press / keyboard focus — the fetch starts before
  // the click completes, so the swap lands the moment you release.
  var hoverT = null;
  document.addEventListener('mouseover', function (e) {
    var a = e.target.closest ? e.target.closest('a[href]') : null;
    if (!a || !isPjaxable(a)) return;
    var path = '';
    try { path = new URL(a.href, window.location.origin).pathname; } catch (err) { return; }
    if (path.indexOf('/host') !== 0 && path.indexOf('/admin') !== 0) return;
    clearTimeout(hoverT);
    hoverT = setTimeout(function () { prefetch(a.href); }, 60);
  }, {passive: true});
  document.addEventListener('pointerdown', function (e) {
    if (e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
    var a = e.target.closest ? e.target.closest('a[href]') : null;
    if (!a || !isPjaxable(a)) return;
    var path = '';
    try { path = new URL(a.href, window.location.origin).pathname; } catch (err) { return; }
    if (path.indexOf('/host') === 0 || path.indexOf('/admin') === 0) prefetch(a.href);
  }, {passive: true});
  document.addEventListener('focusin', function (e) {
    var a = e.target.closest ? e.target.closest('a[href]') : null;
    if (!a || !isPjaxable(a)) return;
    var path = '';
    try { path = new URL(a.href, window.location.origin).pathname; } catch (err) { return; }
    if (path.indexOf('/host') === 0 || path.indexOf('/admin') === 0) prefetch(a.href);
  });
  // Seed cache with the current page (free) + warm top sidebar targets
  // on idle, 2 at a time. Hover-prefetch already covers user intent, so
  // idle warming is capped, delayed, and paused while a real navigation
  // is in flight — previously this fired a full-page render (with backend
  // calls) per sidebar link and serialized on the session lock, making
  // every portal page feel slow. After warm-up each click swaps from
  // memory (~1ms). Session lock is released server-side during slow
  // backend calls, so these run in parallel instead of queuing.
  try {
    cache.set(window.location.href, {html: '<!doctype html>' + document.documentElement.outerHTML, ts: Date.now()});
  } catch (e) {}
  var WARM_MAX = 2;
  function warmAll() {
    try {
      if (dead || navBusy || document.hidden) return;
      if (navigator.connection && (navigator.connection.saveData || /^(slow-2g|2g)$/.test(navigator.connection.effectiveType || ''))) return;
      var seen = {};
      var urls = [];
      shell.querySelectorAll('.p-side a').forEach(function (a) {
        if (urls.length >= WARM_MAX) return;
        if (isPjaxable(a) && !seen[a.href] && a.href !== window.location.href) { seen[a.href] = 1; urls.push(a.href); }
      });
      var i = 0;
      var active = 0;
      function next() {
        if (navBusy) { setTimeout(next, 1500); return; } // don't stampede during a real navigation
        while (active < 1 && i < urls.length) {
          (function (url) {
            active++;
            if (dead) { active--; next(); return; }
            fetch(url, {headers: {'X-Requested-With': 'XMLHttpRequest'}, credentials: 'same-origin'})
              .then(function (r) {
                if (!r.ok || r.redirected) { dead = true; return ''; }
                return r.text();
              })
              .then(function (html) {
                if (!html) return;
                // Don't cache pages where auth state has changed (e.g.
                // session expired mid-warmup → server rendered logged-out page)
                var curRole = document.querySelector('.p-role');
                if (curRole && html.indexOf(curRole.textContent.trim()) === -1) { dead = true; return; }
                cache.set(url, {html: html, ts: Date.now()});
              })
              .catch(function () {})
              .finally(function () { active--; next(); });
          })(urls[i++]);
        }
      }
      next();
    } catch (e) {}
  }
  if ('requestIdleCallback' in window) {
    requestIdleCallback(function () { setTimeout(warmAll, 3000); }, {timeout: 8000});
  } else {
    setTimeout(warmAll, 4000);
  }
  // Button loading state for full-POST forms: instant CSS dot cycle + disable,
  // so slow submits (verify, payouts, room updates) never look dead and
  // never double-submit. Inline only — works on any connection.
  document.addEventListener('submit', function (e) {
    var form = e.target;
    if (!form || form.tagName !== 'FORM' || form.dataset.loading === '1') return;
    var btn = form.querySelector('button[type="submit"], input[type="submit"], .p-btn');
    if (btn && !btn.disabled) {
      form.dataset.loading = '1';
      // Shared dot loader (fastnet-api.js) with inline fallback.
      if (window.FastAPI && FastAPI.btnDots) FastAPI.btnDots(btn, true);
      else {
        btn.disabled = true;
        btn.setAttribute('aria-busy', 'true');
        if (btn.tagName === 'BUTTON' && !btn.querySelector('.p-dots')) {
          var d = document.createElement('span');
          d.className = 'p-dots';
          d.setAttribute('aria-hidden', 'true');
          d.innerHTML = '<span class="p-dot"></span><span class="p-dot"></span><span class="p-dot"></span>';
          btn.insertBefore(d, btn.firstChild);
        }
      }
    }
    barStart();
    if (form.getAttribute('data-pjax') === 'false' || form.enctype === 'multipart/form-data') {
      return; // native submit proceeds; progress bar only, no overlay
    }
    // Intercept with fetch for fast, seamless submission
    e.preventDefault();
    var fd = new FormData(form);
    var url = form.action || window.location.href;
    var method = (form.method || 'GET').toUpperCase();
    
    if (method === 'GET') {
      var params = new URLSearchParams(fd).toString();
      var sep = url.indexOf('?') === -1 ? '?' : '&';
      navigate(form, url + sep + params);
      return;
    }
    
    fetch(url, {
      method: method,
      body: fd,
      headers: {'X-Requested-With': 'XMLHttpRequest'},
      redirect: 'follow'
    }).then(function(r) {
      if (!r.ok && r.status !== 400 && r.status !== 422 && r.status !== 500 && !r.redirected) {
        window.location.reload();
        return;
      }
      var finalUrl = r.url;
      return r.text().then(function(html) {
        // Clean up Bootstrap modals
        document.querySelectorAll('.modal-backdrop').forEach(function(b) { b.remove(); });
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
        
        if (swapDoc(finalUrl, html, true)) {
          barDone();
        } else {
          window.location.href = finalUrl;
        }
      });
    }).catch(function() {
      window.location.reload();
    });
  });
  window.addEventListener('popstate', function () {
    markActiveByUrl(window.location.href);
    var hit = cache.get(window.location.href);
    if (hit && swapDoc(window.location.href, hit.html, false)) {
      if (Date.now() - hit.ts > TTL) refresh(window.location.href, navSeq);
      return;
    }
    barStart();
    pendStart();
    fetch(window.location.href, {headers: {'X-Requested-With': 'XMLHttpRequest'}, credentials: 'same-origin'})
      .then(function (r) { return r.ok ? r.text() : ''; })
      .then(function (html) {
        if (html) { cache.set(window.location.href, {html: html, ts: Date.now()}); swapDoc(window.location.href, html, false); }
        else window.location.reload();
        barDone();
      })
      .catch(function () { window.location.reload(); });
  });
