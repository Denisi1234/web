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
// canonical self for this filtered view — also emitted in layout, but set here for social share
$seoCanon = 'https://www.fastnetstays.com/' . ($seoCity !== '' ? '?city=' . rawurlencode($seoCity) : '');
?>
<?= $this->Html->css('/assets/css/google-travel-layout.css?v=' . filemtime(WWW_ROOT . 'assets/css/google-travel-layout.css')) ?>
<?= $this->Html->css('/assets/css/google-travel-cards.css?v=' . filemtime(WWW_ROOT . 'assets/css/google-travel-cards.css')) ?>
<?= $this->Html->css('/assets/css/hotel-card.css?v=' . filemtime(WWW_ROOT . 'assets/css/hotel-card.css')) ?>
<?= $this->Html->css('/assets/css/search-spacing.css?v=' . filemtime(WWW_ROOT . 'assets/css/search-spacing.css')) ?>
<?= $this->Html->css('/assets/css/google-travel-home.css?v=' . filemtime(WWW_ROOT . 'assets/css/google-travel-home.css')) ?>
<?php
// Hotel ItemList JSON-LD for SEO (production)
$hotelListLd = [
  '@context'=>'https://schema.org',
  '@type'=>'ItemList',
  'name'=>'Hotels in ' . ($queryParams['city'] ?? $queryParams['destination'] ?? 'Tanzania'),
  'numberOfItems'=> count($properties ?? []),
  'itemListElement'=> array_values(array_map(function($p,$i){
    return [
      '@type'=>'ListItem',
      'position'=>$i+1,
      'item'=>[
        '@type'=>'Hotel',
        'name'=> $p['name'] ?? 'Hotel',
        'image'=> $p['image_url'] ?? '',
        'address'=> ['@type'=>'PostalAddress','addressLocality'=> $p['city'] ?? 'Tanzania','addressCountry'=>'TZ'],
        'aggregateRating'=> isset($p['rating']) ? ['@type'=>'AggregateRating','ratingValue'=> (float)$p['rating'],'reviewCount'=> (int)($p['review_count'] ?? 0)] : null,
      ]
    ];
  }, array_slice($properties ?? [],0,10), array_keys(array_slice($properties ?? [],0,10))))
];
echo $this->Html->scriptBlock(json_encode($hotelListLd, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE), ['block'=>true]);
$this->Html->meta(['name'=>'format-detection','content'=>'telephone=no'], null, ['block'=>true]);
?>

<!-- ── Retained Header (do not modify) ── -->
<?= $this->element('navbar') ?>

<?php
// ── Mobile tabs — real working (Apartment / Home / Hotels / Lodge) ──
$tabCity = trim((string)($queryParams['city'] ?? $destination ?? ''));
$tabProp = trim((string)($queryParams['property_type'] ?? ''));
$isApartment = $tabProp === 'Apartment';
$isLodge = $tabProp === 'Safari Lodge';
$isHome = !$isApartment && !$isLodge;
$isHotels = !$isApartment && !$isLodge;
$buildTabUrl = function(array $overrides) use ($tabCity): string {
    $p = [];
    if ($tabCity !== '') $p['city'] = $tabCity;
    foreach ($overrides as $k=>$v) {
        if ($v === '' || $v === null) unset($p[$k]); else $p[$k] = $v;
    }
    return $p ? '/?' . http_build_query($p) : '/';
};
// Apartment (was Explore), Home same as Hotels (no filter), Lodge (was Vacation)
$apartmentUrl = $buildTabUrl(['property_type'=>'Apartment']);
$homeUrl = $buildTabUrl(['property_type'=>'']);
$hotelsUrl = $buildTabUrl(['property_type'=>'']);
$lodgeUrl = $buildTabUrl(['property_type'=>'Safari Lodge']);
?>
<!-- Mobile stack: header → breadcrumb (Home > Dar es Salaam Hotels) → tabs. Single wrapper guarantees visual order. -->
<div id="gh_mobile_stack">
  <div class="gh-m-tabs gh-m-tabs-in-stack" role="tablist" aria-label="Travel types">
    <a href="<?= h($apartmentUrl) ?>" role="tab" class="<?= $isApartment ? 'active' : '' ?>" <?= $isApartment ? 'aria-selected="true"' : '' ?>>Apartment</a>
    <a href="<?= h($homeUrl) ?>" role="tab" class="<?= $isHome ? 'active' : '' ?>" <?= $isHome ? 'aria-selected="true"' : '' ?>>Home</a>
    <a href="<?= h($hotelsUrl) ?>" role="tab" class="<?= $isHotels ? 'active' : '' ?>" <?= $isHotels ? 'aria-selected="true"' : '' ?>>Hotels</a>
    <a href="<?= h($lodgeUrl) ?>" role="tab" class="<?= $isLodge ? 'active' : '' ?>" <?= $isLodge ? 'aria-selected="true"' : '' ?>>Lodge</a>
  </div>
</div>
<style>
#gh_mobile_stack{display:none}
#gh_mobile_breadcrumb{background:var(--cds-gray-10);border-bottom:1px solid #e8eaed;padding:8px 16px 6px;padding-left:calc(16px + env(safe-area-inset-left,0px));font-size:11px;line-height:1.2}
#gh_mobile_breadcrumb .breadcrumb{background:transparent;font-size:11px;line-height:1;padding:0;--bs-breadcrumb-divider:'›';margin:0;flex-wrap:nowrap;white-space:nowrap;overflow:hidden}
#gh_mobile_breadcrumb .breadcrumb-item a{color:#5f6368;text-decoration:none}
#gh_mobile_breadcrumb .breadcrumb-item.active{color:#5f6368;overflow:hidden;text-overflow:ellipsis}
@media(max-width:991px){
  /* Single static stack: header → breadcrumb → tabs scroll away together; only filter chips stay sticky */
  /* No top margin here: body{padding-top} already clears the fixed header (double offset = dead gap) */
  #gh_mobile_stack{display:block !important;position:static !important;top:auto !important;margin-top:0 !important;z-index:auto !important}
  #gh_mobile_stack #gh_mobile_breadcrumb{display:block !important;position:static !important;top:auto !important;z-index:auto !important}
  #gh_mobile_stack .gh-m-tabs{display:flex !important;position:static !important;top:auto !important;z-index:auto !important}
  #gh_desktop_breadcrumb{display:none !important}
  /* Legacy theme (style.css) puts 80px padding on every <section> — reset for the split layout */
  .gh-split-container section.gh-left-fixed{padding-top:0 !important;padding-bottom:0 !important}
  .gh-split-container section.gh-left-scroll{padding-top:0 !important}
}
@media(min-width:992px){
  #gh_mobile_stack{display:none !important}
  /* Desktop: same legacy-theme reset as mobile — prevents style.css 80px section gap leaking into split layout */
  .gh-split-container section.gh-left-fixed{padding-top:0 !important;padding-bottom:0 !important}
  .gh-split-container section.gh-left-scroll{padding-top:0 !important}
}
</style>
<!-- ── Split styles moved to google-travel-home.css ── -->
<!-- Breadcrumb (ultra-compact) desktop only — mobile uses #gh_mobile_breadcrumb AFTER header, BEFORE tabs -->
<!-- ── Split Screen: HALF — LEFT (search+chips+list scroll) + RIGHT (map full-height from header) ── -->
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

                    <!-- Search warnings / notices (hardened) — auto-dismiss to avoid blocking results -->
                    <?php if (!empty($searchErrors)): ?>
                        <div class="alert alert-warning d-flex align-items-start gap-2 mb-3 rounded-3" role="alert" style="font-size:13.5px;position:relative;" id="fns_search_alert">
                            <i class="fa-solid fa-circle-info mt-1 flex-shrink-0"></i>
                            <div style="flex:1;">
                                <strong>Search updated</strong>
                                <ul class="mb-0 ps-3 mt-1">
                                    <?php foreach ($searchErrors as $err): ?>
                                        <li><?= h($err) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <button type="button" class="btn-close" style="font-size:11px;flex-shrink:0;" aria-label="Dismiss" onclick="this.closest('#fns_search_alert').style.display='none'"></button>
                        </div>
                        <script>try{setTimeout(function(){var el=document.getElementById('fns_search_alert'); if(el) el.style.display='none';}, 6000);}catch(e){}</script>
                    <?php endif; ?>

                    <!-- Results count header: "near Mikocheni, Dar es Salaam · 118 results" + info icon — EXACT screenshot -->
                    <?= $this->element('Home/gh-results-header') ?>

                    <!-- Google Hotels Cards Grid — EXACT: photo left + dots + bookmark, name+price, rating ★, amenity 3-col, View prices (blue) / View details (outline) -->
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

<!-- Mobile loading indicator (bar + pill) + controller -->
<?= $this->element('Home/home-loader') ?>
<?= $this->Html->script('/assets/js/home-carousel.js?v=' . filemtime(WWW_ROOT . 'assets/js/home-carousel.js'), ['defer' => true]) ?>
<script>
/* State-aware mobile loader: destination in the pill, hide on settle */
(function () {
  function dest() {
    var d = document.getElementById('gh_dest');
    var m = document.getElementById('fns_m_input');
    return ((m && m.value) || (d && d.value) || 'Tanzania').trim() || 'Tanzania';
  }
  function show() { if (typeof window.showHomeLoader === 'function') window.showHomeLoader('Searching ' + dest() + '…'); }
  function hide() { if (typeof window.hideHomeLoader === 'function') window.hideHomeLoader(); }
  var f = document.getElementById('gh_search_form');
  if (f) f.addEventListener('submit', show);
  // every detail entry (card, View prices/details, Show details, name) → spinner while system loads.
  // anchors: paint one frame so the loader shows before unload (was a flat 140ms hold).
  document.addEventListener('click', function (e) {
    if (e.defaultPrevented || (e.button !== undefined && e.button !== 0) || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
    var a = e.target.closest ? e.target.closest('a[href*="/hotel-detail/"]') : null;
    if (!a) {
      var card = e.target.closest ? e.target.closest('.gh-card[data-property-id]') : null;
      if (card && typeof window.showHomeLoader === 'function') window.showHomeLoader('Loading stay details…');
      return;
    }
    if (a.target && a.target !== '_self') return;
    if (typeof window.showHomeLoader === 'function') {
      e.preventDefault();
      window.showHomeLoader('Loading stay details…');
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
        'label' => 'TSH ' . number_format($price),
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
        // perf: web-vitals beacon (production) + CLS guard
        try{
          const po=new PerformanceObserver((l)=>{ l.getEntries().forEach(e=>{ if(e.entryType==='largest-contentful-paint') console.debug('LCP',e.startTime); }); });
          po.observe({type:'largest-contentful-paint', buffered:true});
        }catch(e){}
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
          // wrap FastNetState setState hydrate already handles markers — also keep token fresh
          const origFetchUrl = window.FastNetState.hydrate;
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
