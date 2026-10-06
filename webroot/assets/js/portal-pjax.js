/* FastNet portal instant nav: shell, progress bar, PJAX swap, prefetch, navigation */
(function () {
  var shell = document.getElementById('pShell');
  if (!shell) return;
  var menu = document.getElementById('pMenuBtn');
  var scrim = document.getElementById('pScrim');
  function isMobile() { return window.matchMedia('(max-width: 991px)').matches; }
  if (menu) menu.addEventListener('click', function () {
    if (isMobile()) shell.classList.toggle('nav-open');
    else shell.classList.toggle('rail');
  });
  if (scrim) scrim.addEventListener('click', function () { shell.classList.remove('nav-open'); });

  /* ── Instant portal nav: hover-prefetch + content swap (no full reload) ──
     Sidebar shell, CSS, fonts and JS stay alive; only #main-content,
     flash and titles swap. Same-origin GET links only; forms do full POST.
     In-memory cache 60s. Falls back to full load on any error.
     No loading screens: the current page stays put until the swap. ── */
  var cache = new Map();
  var TTL = 60000;
  var MAX_STALE = 180000; // never serve memory older than 3 min: refetch instead
  var navSeq = 0;
  var dead = false; // set on first redirected (logged-out) response: no more warming
  var navBusy = false; // true while a click-navigation fetch is in flight

  function barStart() {
    if (window.FastnetLoading && FastnetLoading.bar && FastnetLoading.bar.start) {
      FastnetLoading.bar.start();
    }
  }
  function barDone() {
    navBusy = false;
    pendStop();
    if (window.FastnetLoading && FastnetLoading.bar && FastnetLoading.bar.done) {
      FastnetLoading.bar.done();
    }
  }

  // Pending shift indicator: dim the outgoing content only when loading
  // takes perceptible time — instant swaps stay flicker-free.
  var pendT = null;
  function pendStart() {
    pendStop();
    pendT = setTimeout(function () {
      var cur = document.getElementById('main-content');
      if (cur) cur.classList.add('pjax-pending');
    }, 120);
  }
  function pendStop() {
    if (pendT) { clearTimeout(pendT); pendT = null; }
    var cur = document.getElementById('main-content');
    if (cur) cur.classList.remove('pjax-pending');
  }

  function setActive(link) {
    if (!shell) return;
    shell.querySelectorAll('.p-link.active').forEach(function (el) { el.classList.remove('active'); el.removeAttribute('aria-current'); });
    if (link) { link.classList.add('active'); link.setAttribute('aria-current', 'page'); }
    if (isMobile()) shell.classList.remove('nav-open');
  }

  function markActiveByUrl(url) {
    if (!shell) return;
    var path = '';
    try { path = new URL(url, window.location.origin).pathname; } catch (e) { return; }
    var best = null;
    shell.querySelectorAll('.p-link').forEach(function (a) {
      var ap = '';
      try { ap = new URL(a.href, window.location.origin).pathname; } catch (e) { return; }
      if (path === ap || (ap.length > 1 && path.indexOf(ap) === 0)) {
        if (!best || ap.length > new URL(best.href, window.location.origin).pathname.length) best = a;
      }
    });
    setActive(best);
  }

  function runScripts(root) {
    if (!root) return;
    root.querySelectorAll('script').forEach(function (old) {
      var fresh = document.createElement('script');
      if (old.src) {
        if (document.querySelector('script[src="' + old.src + '"]')) return; // already loaded once
        fresh.src = old.src;
        if (old.defer) fresh.defer = true;
      } else {
        fresh.textContent = old.textContent;
      }
      document.body.appendChild(fresh);
      if (old.parentNode) old.parentNode.removeChild(old);
    });
  }

  function swapDoc(url, html, push) {
    var doc;
    try { doc = new DOMParser().parseFromString(html, 'text/html'); } catch (e) { return false; }
    var main = doc.getElementById('main-content');
    if (!main) return false;
    // Auth-loss guard: if the current page has an admin sidebar but the
    // fetched page does not (session expired between prefetch and render),
    // discard the cached entry and force a full navigation so the server
    // can redirect to /login properly instead of silently rendering a
    // logged-out page inside the admin shell.
    var curRole = document.querySelector('.p-role');
    var newRole = doc.querySelector('.p-role');
    if (curRole && newRole && curRole.textContent.trim() !== newRole.textContent.trim()) {
      cache.delete(url);
      return false;
    }
    // Also detect login-page content being swapped into the portal shell
    if (doc.querySelector('form[action*="/login"]') || doc.querySelector('.login-form')) {
      cache.delete(url);
      return false;
    }
    var cur = document.getElementById('main-content');
    if (!cur) return false;
    cur.innerHTML = main.innerHTML;
    pendStop();
    cur.classList.remove('pjax-swap');
    void cur.offsetWidth;
    cur.classList.add('pjax-swap');
    var flash = doc.querySelector('.p-flash');
    var curFlash = document.querySelector('.p-flash');
    if (flash && curFlash) curFlash.innerHTML = flash.innerHTML;
    if (doc.title) document.title = doc.title;
    var topTitle = doc.querySelector('.p-title');
    var curTitle = document.querySelector('.p-title');
    if (topTitle && curTitle) curTitle.textContent = topTitle.textContent;
    runScripts(cur);
    if (push !== false) { try { history.pushState({pjax: 1}, '', url); } catch (e) {} }
    var scroller = document.querySelector('.p-main') || window;
    if (scroller.scrollTo) scroller.scrollTo(0, 0); else window.scrollTo(0, 0);
    return true;
  }

  function prefetch(url) {
    if (dead || cache.has(url) || document.hidden) return;
    if (navigator.connection && (navigator.connection.saveData || /^(slow-2g|2g)$/.test(navigator.connection.effectiveType || ''))) return;
    try {
      fetch(url, {headers: {'X-Requested-With': 'XMLHttpRequest'}, credentials: 'same-origin'})
        .then(function (r) {
          if (!r.ok || r.redirected) { dead = true; return ''; }
          return r.text();
        })
        .then(function (html) {
          if (!html) return;
          var curRole = document.querySelector('.p-role');
          if (curRole && html.indexOf(curRole.textContent.trim()) === -1) { dead = true; return; }
          cache.set(url, {html: html, ts: Date.now()});
        })
        .catch(function () {});
    } catch (e) {}
  }

  function navigate(link, url) {
    var my = ++navSeq;
    setActive(link);
    // Serve from memory first — always instant, even if stale.
    // Stale entries revalidate silently in the background.
    // Older than MAX_STALE: never serve, go to network (kills zombie pages).
    var hit = cache.get(url);
    if (hit && Date.now() - hit.ts > MAX_STALE) { cache.delete(url); hit = null; }
    if (hit) {
      if (swapDoc(url, hit.html)) {
        barDone();
        if (Date.now() - hit.ts > TTL) refresh(url, my);
        return;
      }
      cache.delete(url);
    }
    barStart();
    pendStart();
    navBusy = true;
    fetch(url, {headers: {'X-Requested-With': 'XMLHttpRequest'}, credentials: 'same-origin'})
      .then(function (r) {
        if (!r.ok || r.redirected) throw 0;
        try { if (new URL(r.url, window.location.origin).pathname !== new URL(url, window.location.origin).pathname) throw 0; } catch (e) { throw 0; }
        return r.text();
      })
      .then(function (html) {
        if (my !== navSeq) return;
        cache.set(url, {html: html, ts: Date.now()});
        if (!swapDoc(url, html)) window.location.href = url;
        barDone();
      })
      .catch(function () { if (my === navSeq) window.location.href = url; });
  }

  function refresh(url, my) {
    if (dead || document.hidden) return;
    if (navigator.connection && (navigator.connection.saveData || /^(slow-2g|2g)$/.test(navigator.connection.effectiveType || ''))) return;
    try {
      fetch(url, {headers: {'X-Requested-With': 'XMLHttpRequest'}, credentials: 'same-origin'})
        .then(function (r) {
          if (!r.ok || r.redirected) { dead = true; return ''; }
          return r.text();
        })
        .then(function (html) {
          if (!html || my !== navSeq) return;
          var curRole = document.querySelector('.p-role');
          if (curRole && html.indexOf(curRole.textContent.trim()) === -1) { dead = true; return; }
          cache.set(url, {html: html, ts: Date.now()});
        })
        .catch(function () {});
    } catch (e) {}
  }

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
      try {
        navigate(a, a.href);
      } catch (navErr) {
        window.location.href = a.href;
      }
      return;
    }

    // App loading indicator for every other same-origin navigation
    if (a.dataset.loading === '1') return;
    a.dataset.loading = '1';
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

  // Warm cache on hover / press / keyboard focus
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

  // Seed cache with the current page
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
        if (navBusy) { setTimeout(next, 1500); return; }
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

  // Button loading state for full-POST forms
  document.addEventListener('submit', function (e) {
    var form = e.target;
    if (!form || form.tagName !== 'FORM' || form.dataset.loading === '1') return;
    if (form.getAttribute('data-api')) return; // handled by fastnet-api-submit.js
    var btn = form.querySelector('button[type="submit"], input[type="submit"], .p-btn');
    if (btn && !btn.disabled) {
      form.dataset.loading = '1';
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
      return;
    }
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
})();
