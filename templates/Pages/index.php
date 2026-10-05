<?php
/**
 * fastnetstays.com — Home Page — PRODUCTION GRADE
 * - Google Hotels pixel-perfect split 50/50, a11y, SEO, perf (preload, lazy, web-vitals), hardened empty/error states
 */
// ── Dynamic SEO per city — helps fast ranking for "Hotels in Dar / Zanzibar / Arusha" queries ──
$seoCity = trim((string)($queryParams['city'] ?? $destination ?? ''));
$seoCount = (int)($totalCount ?? count($properties ?? []));
if ($seoCity !== '' && strtolower($seoCity) !== 'tanzania') {
    $seoTitle = 'Hotels in ' . $seoCity . ' — ' . ($seoCount ? $seoCount . ' Stays | ' : '') . 'FastNet Stays | Best Prices Guaranteed';
    $seoDesc  = 'Find ' . ($seoCount ? $seoCount . ' ' : '') . 'hotels in ' . $seoCity . ', Tanzania — real prices, verified stays, map view, free cancellation. Compare & book direct on FastNet Stays.';
} else {
    $seoTitle = 'FastNet Stays — Find Hotels in Tanzania | Best Prices Guaranteed';
    $seoDesc  = 'Search hotels in Dar es Salaam, Zanzibar & Arusha, Tanzania. Real prices, verified stays, map view. Book direct and save.';
}
$this->assign('title', $seoTitle);
$this->assign('description', $seoDesc);
// preload first hotel image (LCP)
$firstImg = $properties[0]['image_url'] ?? ($properties[0]['primary_image_url'] ?? '');
if ($firstImg) $this->Html->meta(['rel'=>'preload','as'=>'image','href'=>$firstImg,'fetchpriority'=>'high'], null, ['block'=>true]);
// canonical is emitted once in layout/default.php (city-only); no second copy here.
?>
<?= $this->Html->css('/assets/css/google-travel-layout.css?v=' . filemtime(WWW_ROOT . 'assets/css/google-travel-layout.css')) ?>
<?= $this->Html->css('/assets/css/google-travel-cards.css?v=' . filemtime(WWW_ROOT . 'assets/css/google-travel-cards.css')) ?>
<?= $this->Html->css('/assets/css/hotel-card.css?v=' . filemtime(WWW_ROOT . 'assets/css/hotel-card.css')) ?>
<?= $this->Html->css('/assets/css/google-travel-home.css?v=' . filemtime(WWW_ROOT . 'assets/css/google-travel-home.css')) ?>
<?php
// Hotel ItemList JSON-LD for SEO (production). Nulls omitted: emitting
// "aggregateRating": null or "image": "" fails schema validation.
$hotelListSlice = array_slice($properties ?? [], 0, 10);
$hotelListLd = [
  '@context'=>'https://schema.org',
  '@type'=>'ItemList',
  'name'=>'Hotels in ' . ($queryParams['city'] ?? $queryParams['destination'] ?? 'Tanzania'),
  'numberOfItems'=> count($hotelListSlice),
  'itemListElement'=> array_values(array_map(function($p,$i){
    $hotel = [
      '@type'=>'Hotel',
      'name'=> $p['name'] ?? 'Hotel',
      'address'=> ['@type'=>'PostalAddress','addressLocality'=> $p['city'] ?? 'Tanzania','addressCountry'=>'TZ'],
    ];
    $img = trim((string)($p['image_url'] ?? ($p['primary_image_url'] ?? '')));
    if ($img !== '') $hotel['image'] = $img;
    $rating = isset($p['rating']) ? (float)$p['rating'] : 0.0;
    $rc = (int)($p['review_count'] ?? ($p['reviews_count'] ?? 0));
    if ($rating > 0 && $rc > 0) $hotel['aggregateRating'] = ['@type'=>'AggregateRating','ratingValue'=>$rating,'reviewCount'=>$rc];
    return ['@type'=>'ListItem','position'=>$i+1,'item'=>$hotel];
  }, $hotelListSlice, array_keys($hotelListSlice)))
];
// Inline with explicit type: scriptBlock defaults to text/javascript, which
// makes browsers EXECUTE the payload (SyntaxError) and crawlers miss it.
$hotelListJson = json_encode($hotelListLd, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP);
echo '<script type="application/ld+json">' . $hotelListJson . '</script>';
?>

<!-- ── Retained Header (do not modify) ── -->
<?= $this->element('navbar') ?>

<style>
/* Legacy theme (style.css) puts 80px padding on every <section> — reset for the split layout */
.gh-split-container section.gh-left-fixed{padding-top:0 !important;padding-bottom:0 !important}
.gh-split-container section.gh-left-scroll{padding-top:0 !important}
</style>
<!-- ── Split view: LEFT search+chips+list · RIGHT map ── -->
<main id="main-content" class="gh-split-container" style="background:var(--cds-gray-10); min-height:85vh; margin-top:0;" role="main" aria-label="Hotel search results">
    <!-- 4px gutters — close to zero as requested -->
    <div class="container-fluid px-0" style="max-width:100%; margin:0 auto; height:100%; padding-top:0; padding-left:4px !important; padding-right:4px !important;">
        <!-- 4px close to zero — g-0 + explicit gutter to avoid Bootstrap 16px -->
        <div class="row g-0 position-relative" style="height:100%; margin-top:0; padding-top:0; --bs-gutter-x:4px; --bs-gutter-y:4px;">

            <!-- ══ LEFT PANE: HALF screen — search+chips fixed + list scrolls ══ -->
            <div class="col-xl-6 col-lg-6 col-md-12 pe-lg-1" id="gh_list_col">
                <section class="gh-left-fixed" aria-label="Search and filters">
                    <!-- Search bar now inside left half so map can start at header -->
                    <?= $this->element('Home/gh-search-bar') ?>
                    <?= $this->element('Home/gh-filter-chips') ?>
                </section>
                <!-- Removed Bootstrap pt-1 pb-0 pe-1: gh-left-scroll CSS fully manages its own padding -->
                <section class="gh-left-scroll" aria-label="Stays list" aria-live="polite" aria-busy="false" id="gh_results_section">
                    <h1 class="sr-only">Hotels in <?= h($seoCity !== '' ? $seoCity : 'Tanzania') ?> — book direct on FastNet Stays</h1>

                    <?= $this->element('Home/gh-results-header') ?>

                    <div id="gh_cards_live" aria-live="polite">
                    <?= $this->element('Home/gh-hotel-cards') ?>
                    </div>
                    <noscript><div class="alert alert-info mt-3">Enable JavaScript for live filtering, map and instant price updates. <a href="/">Reload</a></div></noscript>
                    <!-- Shared Footer — desktop-only to eliminate mobile vertical clutter; hidden <992px so list ends at pagination -->
                    <div class="gh-index-footer-wrap d-none d-lg-block">
                        <?= $this->element('footer', ['skin' => 'skin-light-footer']) ?>
                    </div>
                </section>
            </div>

            <!-- ══ RIGHT PANE: HALF screen — map starts right after header, full height ── -->
            <div class="col-xl-6 col-lg-6 d-none d-lg-block ps-lg-1" id="gh_map_col" role="complementary" aria-label="Hotel map">
                <div class="gh-map-sticky">
                    <div class="gh-map-frame shadow-sm position-relative" style="background:#e8ecef; min-height:560px; height:100%; height:calc(100vh - 80px);">

                        <!-- Mapbox GL Container — real Mapbox from backend -->
                        <div id="gh-interactive-map" style="width:100%; height:100%; min-height:560px; background:#e8ecef;" role="application" aria-label="Map of hotels"></div>
                        <noscript><div style="width:100%;height:100%;min-height:560px;display:flex;align-items:center;justify-content:center;background:var(--cds-gray-10);color:#525252;font-size:14px;">Enable JavaScript to view the interactive map.</div></noscript>

                        <!-- "Update list when map moves" — PC screenshot: checkbox + text pill -->
                        <div class="position-absolute top-0 start-50 translate-middle-x mt-3" style="z-index:20;">
                            <button type="button" id="gh_update_list_btn"
                                onclick="ghUpdateListFromMap()"
                                class="btn btn-white shadow-sm border rounded-pill px-3 py-1.5 fw-normal d-inline-flex align-items-center gap-2 bg-white"
                                style="font-size:12.5px; color:#202124; border-color:#dadce0 !important; font-family:'Google Sans',Roboto,sans-serif;">
                                <span style="width:14px; height:14px; border:1.5px solid #5f6368; border-radius:2px; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0; background:#fff;"></span>
                                <span>Update list when map moves</span>
                            </button>
                        </div>

                        <!-- Zoom Controls — UX: 44px targets, aria-labels + Recenter/Fullscreen (FastNet own) -->
                        <div class="position-absolute top-0 end-0 m-3 d-flex flex-column" style="z-index:20; border-radius:4px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,0.18);">
                            <button type="button" class="btn btn-white p-0 d-flex align-items-center justify-content-center bg-white border-0 border-bottom"
                                style="width:44px; height:44px; font-size:18px; color:#5f6368; border-color:#dadce0 !important;"
                                onclick="if(window._ghMap) window._ghMap.zoomIn()" title="Zoom in" aria-label="Zoom in">+</button>
                            <button type="button" class="btn btn-white p-0 d-flex align-items-center justify-content-center bg-white border-0 border-bottom"
                                style="width:44px; height:44px; font-size:20px; color:#5f6368; border-color:#dadce0 !important;"
                                onclick="if(window._ghMap) window._ghMap.zoomOut()" title="Zoom out" aria-label="Zoom out">−</button>
                            <button type="button" class="btn btn-white p-0 d-flex align-items-center justify-content-center bg-white border-0 border-bottom"
                                style="width:44px; height:44px; font-size:14px; color:#5f6368; border-color:#dadce0 !important;"
                                onclick="if(window.ghRecenter) window.ghRecenter()" title="Recenter to all stays" aria-label="Recenter"><i class="fa-solid fa-crosshairs"></i></button>
                            <button type="button" class="btn btn-white p-0 d-flex align-items-center justify-content-center bg-white border-0"
                                style="width:44px; height:44px; font-size:14px; color:#5f6368;"
                                onclick="if(window.ghToggleFullscreen) window.ghToggleFullscreen()" title="Fullscreen" aria-label="Fullscreen"><i class="fa-solid fa-expand"></i></button>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

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
<?= $this->Html->script('/assets/js/fastnet-state.js?v=' . filemtime(WWW_ROOT . 'assets/js/fastnet-state.js'), ['defer' => true]) ?>
<!-- Google Hotels Interactive Map Script — deferred, uses real Mapbox style/token from backend -->
<?= $this->Html->script('/assets/js/gh-home-map.js?v=' . filemtime(WWW_ROOT . 'assets/js/gh-home-map.js'), ['defer' => true]) ?>

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
        focusBbox: <?= json_encode($queryParams['bbox'] ?? '') ?>,
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
