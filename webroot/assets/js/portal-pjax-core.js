/* FastNet portal instant nav core: shell, progress bar, PJAX swap, prefetch. Part 1 of 2 — load before portal-pjax-nav.js. */
  var shell = document.getElementById('pShell');
  var menu = document.getElementById('pMenuBtn');
  var scrim = document.getElementById('pScrim');
  if (!shell) return;
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
  // Delegates to the canonical bar. This was a sixth loading implementation
  // with its own timings and a green/blue gradient that clashed with the rest
  // of the product. portal.php now loads loading.js and shares one indicator.
  function barStart() { FastnetLoading.bar.start(); }
  function barDone() { navBusy = false; pendStop(); FastnetLoading.bar.done(); }
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
    shell.querySelectorAll('.p-link.active').forEach(function (el) { el.classList.remove('active'); el.removeAttribute('aria-current'); });
    if (link) { link.classList.add('active'); link.setAttribute('aria-current', 'page'); }
    if (isMobile()) shell.classList.remove('nav-open');
  }
  function markActiveByUrl(url) {
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
          // Session dead (redirect to login): stop all background warming so
          // silent kicks don't pile extra flashes into the session
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
  var navBusy = false; // true while a click-navigation fetch is in flight
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
        // Session expired etc. → server redirects to /login: do a real
        // navigation so auth flow works instead of swapping wrong content
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
  // Silent background refresh for stale entries — next visit is fresh
  function refresh(url, my) {    if (dead || document.hidden) return;
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
