<!-- Filters Modal -->
<?= $this->element('Home/gh-filters-modal') ?>

<!-- Loading: single-source top bar + skeletons (floating pill removed) -->
<?= $this->element('Home/home-loader') ?>
<?= $this->Html->script('/assets/js/home-carousel.js?v=' . filemtime(WWW_ROOT . 'assets/js/home-carousel.js'), ['defer' => true]) ?>
<script>
/* Thin top bar only: start on search submit, stop when results settle.
   Detail navigation uses native paint (double rAF) so the bar shows before unload. */
(function () {
  function show() { if (typeof window.showHomeLoader === 'function') window.showHomeLoader(); }
  function hide() { if (typeof window.hideHomeLoader === 'function') window.hideHomeLoader(); }
  var f = document.getElementById('gh_search_form');
  if (f) f.addEventListener('submit', show);
  document.addEventListener('click', function (e) {
    if (e.defaultPrevented || (e.button !== undefined && e.button !== 0) || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
    var a = e.target.closest ? e.target.closest('a[href*="/hotel-detail/"]') : null;
    if (!a) {
      var card = e.target.closest ? e.target.closest('.gh-card[data-property-id]') : null;
      if (card && typeof window.showHomeLoader === 'function') window.showHomeLoader();
      return;
    }
    if (a.target && a.target !== '_self') return;
    if (typeof window.showHomeLoader === 'function') {
      e.preventDefault();
      window.showHomeLoader();
      var href = a.href;
      requestAnimationFrame(function () {
        requestAnimationFrame(function () { window.location.href = href; });
      });
    }
  });
  // mobile sheet search is SPA (pushState + hydrate): wrap to show until results land
  var tries = 0;
  (function hookMobile() {
    if (typeof window.fnsMobileSearch === 'function') {
      var orig = window.fnsMobileSearch;
      window.fnsMobileSearch = function () { show(); try { return orig.apply(this, arguments); } catch (e) { hide(); } };
    } else if (++tries < 40) setTimeout(hookMobile, 250);
  })();
  window.addEventListener('fastnet:markers-update', hide);
  window.addEventListener('fastnet:shimmer-hide', hide);
  window.addEventListener('pageshow', hide);
  if (document.readyState !== 'loading') hide(); else document.addEventListener('DOMContentLoaded', hide);
})();
</script>

<?php
// Real Mapbox — only public pk.* echoed (never secret). Backend GET /api/map-config is cached.
$serverMapboxToken = $mapboxToken ?? \Cake\Core\Configure::read('App.mapboxToken', env('MAPBOX_TOKEN', ''));
$serverMapboxStyle = $mapboxStyle ?? \Cake\Core\Configure::read('App.mapboxStyle', 'mapbox://styles/mapbox/streets-v12');
if (!is_string($serverMapboxToken) || !str_starts_with($serverMapboxToken, 'pk.')) $serverMapboxToken = '';
if (!is_string($serverMapboxStyle) || $serverMapboxStyle === '') $serverMapboxStyle = 'mapbox://styles/mapbox/streets-v12';
?>
<script>
// Server-injected Mapbox — only pk.* public token echoed (secret never in HTML). Restrict by Referrer in Mapbox dashboard.
window.MAPBOX_TOKEN = <?= json_encode($serverMapboxToken) ?>;
window.MAPBOX_STYLE = <?= json_encode($serverMapboxStyle) ?>;
window.DEFAULT_MAPBOX_TOKEN = window.MAPBOX_TOKEN || window.DEFAULT_MAPBOX_TOKEN || '';
if (window.MAPBOX_TOKEN && typeof mapboxgl !== 'undefined') {
    mapboxgl.accessToken = window.MAPBOX_TOKEN;
}
if (window.MAPBOX_TOKEN) {
    // dispatch immediately so gh-home-map can init without waiting for layout's async fetch
    setTimeout(function(){ window.dispatchEvent(new CustomEvent('fastnet:mapbox-ready')); }, 0);
}
</script>

<!-- Markers JSON Payload for Mapbox — real backend lat/lng or mock Dar/ZNZ/Arusha -->
<script id="gh_markers_json" type="application/json">
<?php
$markers = [];
foreach ($properties as $p) {
    $lat = (float)($p['latitude'] ?? ($p['lat'] ?? 0));
    $lng = (float)($p['longitude'] ?? ($p['lng'] ?? 0));
    if ($lat == 0 && $lng == 0) continue;
    $price = (int)($p['customer_price_per_night'] ?? ($p['price_per_night'] ?? ($p['price'] ?? 0)));
    $markers[] = [
        'id'    => (int)($p['id'] ?? 0),
        'lat'   => $lat,
        'lng'   => $lng,
        'price' => $price, // numeric nightly TZS base — map labels render from this (view + currency aware)
        'label' => 'TSh ' . number_format($price),
        'title' => $p['name'] ?? '',
    ];
}
echo json_encode($markers, JSON_UNESCAPED_UNICODE);
?>
</script>

<!-- FastNetState — URL deep-linking engine (push/replaceState, popstate, AJAX hydration). Defer: not needed before first paint; inline init guards until DOMContentLoaded -->
<?= $this->Html->script('/assets/js/fastnet-state-url.js?v=' . filemtime(WWW_ROOT . 'assets/js/fastnet-state-url.js'), ['defer' => true]) ?>
<?= $this->Html->script('/assets/js/fastnet-state-ui.js?v=' . filemtime(WWW_ROOT . 'assets/js/fastnet-state-ui.js'), ['defer' => true]) ?>
<?= $this->Html->script('/assets/js/fastnet-state-state.js?v=' . filemtime(WWW_ROOT . 'assets/js/fastnet-state-state.js'), ['defer' => true]) ?>
<!-- Google Hotels Interactive Map Script — deferred, uses real Mapbox style/token from backend -->
<?= $this->Html->script('/assets/js/gh-home-map-core.js?v=' . filemtime(WWW_ROOT . 'assets/js/gh-home-map-core.js'), ['defer' => true]) ?>
<?= $this->Html->script('/assets/js/gh-home-map-markers.js?v=' . filemtime(WWW_ROOT . 'assets/js/gh-home-map-markers.js'), ['defer' => true]) ?>
<?= $this->Html->script('/assets/js/gh-home-map-ui.js?v=' . filemtime(WWW_ROOT . 'assets/js/gh-home-map-ui.js'), ['defer' => true]) ?>

<script>
(function () {
    const rawData = document.getElementById('gh_markers_json')?.textContent;
    let markers=[];
    try{ markers = rawData ? JSON.parse(rawData) : []; }catch(e){ console.warn('markers parse',e); }
    const cfg = {
        markers: markers,
        defaultLat: <?= !empty($queryParams['lat']) ? (float)$queryParams['lat'] : -6.7725 ?>,
        defaultLng: <?= !empty($queryParams['lng']) ? (float)$queryParams['lng'] : 39.2450 ?>,
        defaultZoom: 13,
        focusBbox: <?= json_encode($queryParams['bbox'] ?? $queryParams['bounds'] ?? '') ?>,
        style: window.MAPBOX_STYLE || 'mapbox://styles/mapbox/streets-v12',
    };
    window._ghCfg = cfg;
    let _mapRetry=0;
    function showMapFallback(reason){
        const el=document.getElementById('gh-interactive-map');
        if(!el) return;
        // don't overwrite a successfully initialized map (mapboxgl-map class)
        if(el.classList.contains('mapboxgl-map')) return;
        if(el.dataset.fallbackShown) return;
        el.dataset.fallbackShown='1';
        console.warn('[FastNet] Map fallback:', reason);
        // only show placeholder if mapboxgl itself failed to load after retries
        if(typeof mapboxgl === 'undefined' && _mapRetry >= 10){
            el.style.display='flex';
            el.style.alignItems='center';
            el.style.justifyContent='center';
            el.style.background='#e8ecef';
            el.innerHTML='<div style="text-align:center;padding:24px;font-family:Google Sans,Roboto,sans-serif;color:#5f6368;">'
              +'<div style="font-size:28px;margin-bottom:8px;">🗺️</div>'
              +'<div style="font-weight:500;color:#202124;margin-bottom:4px;">Map loading failed</div>'
              +'<div style="font-size:12px;">Check network / Mapbox config</div></div>';
        }
    }
    function startMap() {
        if (typeof mapboxgl === 'undefined') {
            if(_mapRetry < 12){
                _mapRetry++;
                setTimeout(startMap, 300);
            } else {
                showMapFallback('mapboxgl not loaded after retry');
            }
            return;
        }
        // clear any previous fallback overlay if map now succeeds
        const el=document.getElementById('gh-interactive-map');
        if(el && el.dataset.fallbackShown && el.classList.contains('mapboxgl-map')){
            // map initialized after fallback — remove overlay text but keep canvas
            el.querySelectorAll('div[style*="Map loading failed"]').forEach(n=>n.remove());
            el.dataset.fallbackShown='';
            el.style.display=''; el.style.background='';
        }
        // gh-home-map.js now handles Carto fallback automatically when token missing — just ensure style is set
        if (!window.MAPBOX_TOKEN && !window.MAPBOX_STYLE) {
            window.MAPBOX_STYLE = 'https://basemaps.cartocdn.com/gl/positron-gl-style/style.json';
        }
        if (typeof initGhHomeMap === 'function') {
            try{
                if (window.MAPBOX_STYLE) cfg.style = window.MAPBOX_STYLE;
                if (window.MAPBOX_TOKEN) mapboxgl.accessToken = window.MAPBOX_TOKEN;
                initGhHomeMap(cfg);
            }catch(e){ console.warn('map init',e); showMapFallback(e.message); }
        }
    }
    window.addEventListener('fastnet:mapbox-ready', function(){
        if(window.MAPBOX_TOKEN && typeof mapboxgl!=='undefined') mapboxgl.accessToken = window.MAPBOX_TOKEN;
        startMap();
    });
    document.addEventListener('DOMContentLoaded', function () {
        // aria-busy toggle on hydrate — also sync real Mapbox token from JSON hydration payload
        const sec=document.getElementById('gh_results_section');
        if(sec && window.FastNetState){
          const origHydrate=window.FastNetState.hydrate;
          window.FastNetState.hydrate=async function(u){
              sec.setAttribute('aria-busy','true');
              const r=await origHydrate.call(this,u);
              // if hydration response contained fresh mapbox token, apply it
              try{
                  if(r && r.mapboxToken && r.mapboxToken !== window.MAPBOX_TOKEN){
                      window.MAPBOX_TOKEN = r.mapboxToken;
                      window.DEFAULT_MAPBOX_TOKEN = r.mapboxToken;
                      if(typeof mapboxgl!=='undefined') mapboxgl.accessToken = r.mapboxToken;
                      if(r.mapboxStyle) window.MAPBOX_STYLE = r.mapboxStyle;
                      window.dispatchEvent(new CustomEvent('fastnet:mapbox-ready'));
                  }
                  if(r && r.markers) cfg.markers = r.markers;
              }catch(e){}
              sec.setAttribute('aria-busy','false');
              return r;
          };
        }
        // If token already injected server-side, start immediately; otherwise wait for backend fetch (layout) or 800ms
        if(window.MAPBOX_TOKEN) setTimeout(startMap, 150);
        else setTimeout(startMap, 900);
        // offline + slow-network toast (any-device)
        const toast=document.getElementById('fns_toast');
        const showToast=(m)=>{ if(!toast) return; toast.textContent=m; toast.style.display='block'; clearTimeout(toast._t); toast._t=setTimeout(()=>toast.style.display='none', 2800); };
        window.addEventListener('offline', ()=>showToast('You’re offline — showing cached stays'));
        window.addEventListener('online', ()=>showToast('Back online — refreshing'));
        // slow 3G hint
        const conn=navigator.connection; if(conn && conn.effectiveType && conn.effectiveType.includes('2g')) showToast('Slow connection — loading lighter view');
    });
    startMap();
    // global error guard (production) — log only, no intrusive toast on refresh
    window.addEventListener('error', function(e){ console.warn('index error', e.message); });
    window.addEventListener('unhandledrejection', function(e){ console.warn('promise', e.reason); });
})();
</script>
<?php // #fns_toast and window.fnsToast are defined once in layout/default.php.
      // This page used to carry a byte-identical second copy that silently
      // overwrote the guarded layout definition. ?>
