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
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    /* Instant-nav progress bar (Carbon blue-60) + swap transition */
    #pBar{position:fixed;top:0;left:0;height:3px;width:0;background:#0f62fe;z-index:2000;transition:width .25s ease,opacity .3s ease;opacity:0}
    #pBar.on{opacity:1}
    #main-content.pjax-swap{animation:pFade .18s ease}
    @keyframes pFade{from{opacity:.35}to{opacity:1}}
    /* ── Self-contained loaders: inline only, system fonts, zero external
       requests — they paint even when CDN/fonts/connection hang ── */
    #pBoot{position:fixed;inset:0;background:#f4f4f4;z-index:3000;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:14px;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif}
    #pBoot .mark{width:44px;height:44px;background:#0f62fe;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:20px}
    #pBoot .ring{width:28px;height:28px;border-radius:50%;border:3px solid #e0e0e0;border-top-color:#0f62fe;animation:pSpin .8s linear infinite}
    #pBoot .txt{font-size:13px;color:#525252}
    @keyframes pSpin{to{transform:rotate(360deg)}}
    .p-skel{display:grid;gap:12px;padding:4px 0}
    .p-skel .sk{border:1px solid #e0e0e0;background:#fff;padding:16px}
    .p-skel .ln{height:12px;background:linear-gradient(90deg,#e0e0e0 25%,#f4f4f4 50%,#e0e0e0 75%);background-size:200% 100%;animation:pShimmer 1.2s ease infinite}
    .p-skel .ln.t{width:38%;height:16px;margin-bottom:10px}
    .p-skel .ln.m{width:92%}.p-skel .ln.s{width:64%}
    .p-skel .row{display:flex;gap:8px;margin-top:12px}
    .p-skel .pill{height:32px;width:110px;background:linear-gradient(90deg,#e0e0e0 25%,#f4f4f4 50%,#e0e0e0 75%);background-size:200% 100%;animation:pShimmer 1.2s ease infinite}
    @keyframes pShimmer{from{background-position:200% 0}to{background-position:-200% 0}}
    .p-spin{display:inline-block;width:14px;height:14px;flex:none;border-radius:50%;border:2px solid rgba(255,255,255,.45);border-top-color:#fff;animation:pSpin .7s linear infinite;vertical-align:-2px;margin-right:8px}
    .p-btn.ghost .p-spin{border-color:rgba(15,98,254,.25);border-top-color:#0f62fe}
    .p-btn[disabled]{opacity:.75;cursor:wait}
    @media (prefers-reduced-motion:reduce){
      #pBar{transition:none}#main-content.pjax-swap{animation:none}
      #pBoot .ring,.p-skel .ln,.p-skel .pill,.p-spin{animation:none}
    }
    #pAuthBar{display:flex;align-items:center;gap:10px;flex-wrap:wrap;background:#fff1f1;border-bottom:1px solid #e0e0e0;border-left:3px solid #da1e28;padding:10px 32px;font-size:13px;color:#161616}
    #pAuthBar .p-btn{min-height:32px;font-size:13px}
    @media(max-width:991px){#pAuthBar{padding:10px 16px}}
  </style>
  <?= $this->Html->css(['/assets/css/bootstrap.min.css', '/assets/css/fontawesome.css', '/assets/css/portal.css']) ?>
  <?= $this->fetch('meta') ?>
  <?= $this->fetch('css') ?>
</head>
<body class="portal">
<div id="pBoot" role="status" aria-label="Loading portal"><div class="mark">F</div><div class="ring" aria-hidden="true"></div><div class="txt">Loading portal…</div></div>
<div id="pBar" aria-hidden="true"></div>
<div class="p-shell" id="pShell">
  <?= $this->element('portal_sidebar', $this->viewVars) ?>
  <div class="p-scrim" id="pScrim"></div>
  <div class="p-main">
    <?= $this->element('portal_topbar', $this->viewVars) ?>
    <?php if (empty($isLoggedIn)): ?>
    <div id="pAuthBar" role="note">
      <span><strong>You are signed out.</strong> Sign in to see your live properties, bookings and earnings.</span>
      <a class="p-btn" href="<?= $this->Url->build('/login') ?>">Sign in</a>
    </div>
    <?php endif; ?>
    <div class="p-flash"><?= $this->Flash->render() ?></div>
    <main class="p-content" id="main-content"><?= $this->fetch('content') ?></main>
  </div>
</div>
<?= $this->Html->script(['/assets/js/popper.min.js', '/assets/js/bootstrap.min.js'], ['defer' => true]) ?>
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

  /* ── Boot overlay: hide as soon as shell is interactive (never waits on
     external CSS/fonts/JS). Absolute failsafe hides it regardless. ── */
  function hideBoot() { var b = document.getElementById('pBoot'); if (b && b.parentNode) b.parentNode.removeChild(b); }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', hideBoot);
  else hideBoot();
  setTimeout(hideBoot, 2500);

  /* ── Instant portal nav: hover-prefetch + content swap (no full reload) ──
     Sidebar shell, CSS, fonts and JS stay alive; only #main-content,
     flash and titles swap. Same-origin GET links only; forms do full POST.
     In-memory cache 60s. Falls back to full load on any error. */
  var bar = document.getElementById('pBar');
  var cache = new Map();
  var TTL = 60000;
  var MAX_STALE = 180000; // never serve memory older than 3 min: refetch instead
  var navSeq = 0;
  var dead = false; // set on first redirected (logged-out) response: no more warming
  function barStart() { if (!bar) return; bar.classList.add('on'); bar.style.width = '15%'; requestAnimationFrame(function(){ bar.style.width = '65%'; }); }
  function barDone() { if (!bar) return; bar.style.width = '100%'; setTimeout(function(){ bar.classList.remove('on'); bar.style.width = '0'; }, 250); }
  // Skeleton placeholder for cold navigations: shows only if fetch outlives
  // 150ms, so warm swaps never flash. Pure inline CSS — no network needed.
  var skelT = null;
  function skelStart() {
    skelStop();
    skelT = setTimeout(function () {
      var cur = document.getElementById('main-content');
      if (!cur || cur.dataset.skel === '1') return;
      cur.dataset.skel = '1';
      cur.setAttribute('aria-busy', 'true');
      cur.innerHTML = '<div class="p-skel" aria-hidden="true">'
        + '<div class="sk"><div class="ln t"></div><div class="ln m"></div><div class="ln s" style="margin-top:8px"></div><div class="row"><div class="pill"></div><div class="pill"></div></div></div>'
        + '<div class="sk"><div class="ln t"></div><div class="ln m"></div><div class="ln s" style="margin-top:8px"></div></div>'
        + '<div class="sk"><div class="ln t"></div><div class="ln m"></div></div>'
        + '</div>';
    }, 150);
  }
  function skelStop() {
    if (skelT) { clearTimeout(skelT); skelT = null; }
    var cur = document.getElementById('main-content');
    if (cur) { delete cur.dataset.skel; cur.removeAttribute('aria-busy'); }
  }
  function setActive(link) {
    shell.querySelectorAll('.p-link.active').forEach(function (el) { el.classList.remove('active'); });
    if (link) link.classList.add('active');
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
    var cur = document.getElementById('main-content');
    skelStop();
    cur.innerHTML = main.innerHTML;
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
    if (dead || cache.has(url)) return;
    try {
      fetch(url, {headers: {'X-Requested-With': 'XMLHttpRequest'}, credentials: 'same-origin'})
        .then(function (r) {
          // Session dead (redirect to login): stop all background warming so
          // silent kicks don't pile extra flashes into the session
          if (!r.ok || r.redirected) { dead = true; return ''; }
          return r.text();
        })
        .then(function (html) { if (html) cache.set(url, {html: html, ts: Date.now()}); })
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
    skelStart();
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
      .catch(function () { skelStop(); if (my === navSeq) window.location.href = url; });
  }
  // Silent background refresh for stale entries — next visit is fresh
  function refresh(url, my) {
    if (dead) return;
    try {
      fetch(url, {headers: {'X-Requested-With': 'XMLHttpRequest'}, credentials: 'same-origin'})
        .then(function (r) {
          if (!r.ok || r.redirected) { dead = true; return ''; }
          return r.text();
        })
        .then(function (html) { if (html && my === navSeq) cache.set(url, {html: html, ts: Date.now()}); })
        .catch(function () {});
    } catch (e) {}
  }
  function isPjaxable(a) {
    if (!a || !a.href || a.target === '_blank' || a.hasAttribute('download')) return false;
    if (a.hostname !== window.location.hostname) return false;
    return true;
  }
  document.addEventListener('click', function (e) {
    if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
    var a = e.target.closest ? e.target.closest('.p-side a, .p-brand') : null;
    if (!a || !isPjaxable(a)) return;
    e.preventDefault();
    navigate(a, a.href);
  });
  // Warm cache on hover / keyboard focus — page is ready before the click
  var hoverT = null;
  document.addEventListener('mouseover', function (e) {
    var a = e.target.closest ? e.target.closest('.p-side a') : null;
    if (!a || !isPjaxable(a)) return;
    clearTimeout(hoverT);
    hoverT = setTimeout(function () { prefetch(a.href); }, 120);
  }, {passive: true});
  document.addEventListener('focusin', function (e) {
    var a = e.target.closest ? e.target.closest('.p-side a') : null;
    if (a && isPjaxable(a)) prefetch(a.href);
  });
  // Seed cache with the current page (free) + warm every sidebar target
  // on idle, 2 at a time. After warm-up each click swaps from memory (~1ms).
  // Session lock is released server-side during slow backend calls, so these
  // run in parallel instead of queuing.
  try {
    cache.set(window.location.href, {html: '<!doctype html>' + document.documentElement.outerHTML, ts: Date.now()});
  } catch (e) {}
  function warmAll() {
    try {
      if (dead) return;
      if (navigator.connection && navigator.connection.saveData) return;
      var seen = {};
      var urls = [];
      shell.querySelectorAll('.p-side a').forEach(function (a) {
        if (isPjaxable(a) && !seen[a.href] && a.href !== window.location.href) { seen[a.href] = 1; urls.push(a.href); }
      });
      var i = 0;
      var active = 0;
      function next() {
        while (active < 2 && i < urls.length) {
          (function (url) {
            active++;
            if (dead) { active--; next(); return; }
            fetch(url, {headers: {'X-Requested-With': 'XMLHttpRequest'}, credentials: 'same-origin'})
              .then(function (r) {
                if (!r.ok || r.redirected) { dead = true; return ''; }
                return r.text();
              })
              .then(function (html) { if (html) cache.set(url, {html: html, ts: Date.now()}); })
              .catch(function () {})
              .finally(function () { active--; next(); });
          })(urls[i++]);
        }
      }
      next();
    } catch (e) {}
  }
  if ('requestIdleCallback' in window) {
    requestIdleCallback(function () { setTimeout(warmAll, 400); }, {timeout: 3000});
  } else {
    setTimeout(warmAll, 1200);
  }
  // Button loading state for full-POST forms: instant CSS spinner + disable,
  // so slow submits (verify, payouts, room updates) never look dead and
  // never double-submit. Inline only — works on any connection.
  document.addEventListener('submit', function (e) {
    var form = e.target;
    if (!form || form.tagName !== 'FORM' || form.dataset.loading === '1') return;
    var btn = form.querySelector('button[type="submit"], input[type="submit"], .p-btn');
    if (btn && !btn.disabled) {
      form.dataset.loading = '1';
      btn.disabled = true;
      btn.setAttribute('aria-busy', 'true');
      if (btn.tagName === 'BUTTON' && !btn.querySelector('.p-spin')) {
        var s = document.createElement('span');
        s.className = 'p-spin';
        s.setAttribute('aria-hidden', 'true');
        btn.insertBefore(s, btn.firstChild);
      }
    }
    barStart();
  });
  window.addEventListener('popstate', function () {
    markActiveByUrl(window.location.href);
    var hit = cache.get(window.location.href);
    if (hit && swapDoc(window.location.href, hit.html, false)) {
      if (Date.now() - hit.ts > TTL) refresh(window.location.href, navSeq);
      return;
    }
    barStart();
    skelStart();
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
