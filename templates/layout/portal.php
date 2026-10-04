<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $this->fetch('title') ? h($this->fetch('title')) . ' · FastNet Portal' : 'FastNet Portal' ?></title>
  <meta name="robots" content="noindex, nofollow">
  <meta name="csrfToken" content="<?= $this->request->getAttribute('csrfToken'); ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="preconnect" href="https://api.mapbox.com">
  <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&display=swap">
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
  <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&display=swap"></noscript>
  <style>
    /* Portal PJAX content swap. The progress bar itself is styled by
       loading.css (.fn-progress) - #pBar and its green/blue gradient were
       removed when the portal moved onto the shared indicator. */
    #main-content{transition:opacity .18s ease}
    #main-content.pjax-swap{animation:pFade .15s ease}
    /* Professional shift state: content softly dims while the next page
       loads (kicks in only if the fetch outlives ~120ms, so instant
       cache swaps never flicker). Non-blocking — no overlay, no spinner. */
    #main-content.pjax-pending{opacity:.55;pointer-events:none}
    /* Sidebar press feedback: instant tactile nudge on every nav tap */
    .p-side a{transition:background-color .12s ease,transform .12s ease}
    .p-side a:active{transform:translateX(2px)}
    @keyframes pFade{from{opacity:.4}to{opacity:1}}
    /* Button micro-dots loader lives in fastnet-dots.css (single source). */
    .p-btn[disabled]{opacity:.8;cursor:wait}
    @media (prefers-reduced-motion:reduce){
      #main-content,#main-content.pjax-swap{animation:none;transition:none}.p-side a{transition:none}.p-side a:active{transform:none}
    }
  </style>
  <?= $this->Html->css(['/assets/css/bootstrap.min.css', '/assets/css/fontawesome.css', '/assets/css/portal.css']) ?>
  <?= $this->Html->css('/assets/css/loading.css') ?>
  <?= $this->Html->css('/assets/css/app-loader.css') ?>
  <?= $this->Html->css('/assets/css/fastnet-dots.css') ?>
  <?= $this->Html->css('/assets/css/shimmer.css') ?>
  <?= $this->element('api_direct') ?>
  <?= $this->fetch('meta') ?>
  <?= $this->fetch('css') ?>
</head>
<body class="portal">
<!-- Canonical progress element. loading.js adopts this node and adds the
     fn-progress class, so the portal and the public site share one indicator.
     The inline display:none that used to sit here would have hidden the
     adopted bar entirely - visibility is driven by opacity in loading.css. -->
<div id="fastnet-top-progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-label="Loading"></div>
<div id="fastnet-bg-loader" role="status" aria-live="polite" aria-atomic="true"></div>
<div id="fns-confirm-overlay" role="dialog" aria-modal="true" aria-labelledby="fns-confirm-title" style="display:none">
    <div class="fns-confirm-card">
        <div class="fns-confirm-icon" aria-hidden="true">⚠</div>
        <p class="fns-confirm-title" id="fns-confirm-title">Are you sure?</p>
        <p class="fns-confirm-msg"   id="fns-confirm-msg">This action cannot be undone.</p>
        <div class="fns-confirm-actions">
            <button class="fns-confirm-cancel" id="fns-confirm-cancel" type="button">Cancel</button>
            <button class="fns-confirm-ok"     id="fns-confirm-ok"     type="button">Confirm</button>
        </div>
    </div>
</div>
<div class="p-shell" id="pShell">
  <?= $this->element('portal_sidebar', $this->viewVars) ?>
  <div class="p-scrim" id="pScrim"></div>
  <div class="p-main">
    <?= $this->element('portal_topbar', $this->viewVars) ?>
    <div class="p-flash"><?= $this->Flash->render() ?></div>
    <main class="p-content" id="main-content"><?= $this->fetch('content') ?></main>
  </div>
</div>
<?= $this->Html->script(['/assets/js/popper.min.js', '/assets/js/bootstrap.min.js'], ['defer' => true]) ?>
<?= $this->Html->script('/assets/js/app-loader.js?v=' . filemtime(WWW_ROOT . 'assets/js/app-loader.js')) ?>
<?= $this->Html->script('/assets/js/loading.js?v=' . filemtime(WWW_ROOT . 'assets/js/loading.js')) ?>
<?= $this->Html->script('/assets/js/fastnet-api.js?v=' . filemtime(WWW_ROOT . 'assets/js/fastnet-api.js')) ?>
<script>
(function () {
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
})();
</script>
<?= $this->fetch('script') ?>
</body>
</html>
