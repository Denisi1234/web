<?php
/**
 * fastnetstays.com — Home Page — PRODUCTION GRADE
 * - Google Hotels pixel-perfect split 50/50, a11y, SEO, perf (preload, lazy, web-vitals), hardened empty/error states
 */
$this->assign('title', 'FastNet Stays — Find Hotels in Tanzania | Best Prices Guaranteed');
$this->assign('description', 'Search hotels in Dar es Salaam, Zanzibar & Tanzania. Real prices, verified stays, map view. Book direct and save.');
// preload first hotel image (LCP)
$firstImg = $properties[0]['image_url'] ?? ($properties[0]['primary_image_url'] ?? '');
if ($firstImg) $this->Html->meta(['rel'=>'preload','as'=>'image','href'=>$firstImg,'fetchpriority'=>'high'], null, ['block'=>true]);
?>
<?= $this->Html->css('/assets/css/google-travel-layout.css') ?>
<?= $this->Html->css('/assets/css/google-travel-cards.css') ?>
<?= $this->Html->css('/assets/css/hotel-card.css') ?>
<?= $this->Html->css('/assets/css/search-spacing.css') ?>
<?= $this->Html->css('/assets/css/google-travel-home.css') ?>
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

<!-- ── Mobile tabs — FastNet Stays (no Flights) ── -->
<div class="gh-m-tabs" role="tablist" aria-label="Travel types">
  <a href="/?explore=1" role="tab">Explore</a>
  <a href="/?homes=1" role="tab">Homes</a>
  <a href="/" role="tab" class="active" aria-selected="true">Hotels</a>
  <a href="/?destination=Vacation" role="tab">Vacation rentals</a>
</div>
<!-- ── Split styles moved to google-travel-home.css ── -->
<!-- Breadcrumb (ultra-compact) -->
<nav aria-label="Breadcrumb" class="container-fluid px-2 px-lg-2" style="max-width:100%;margin:0 auto;background:#f8f9fa;padding:0;">
  <ol class="breadcrumb mb-0 py-0" style="background:transparent;font-size:11px;line-height:1;padding:0;--bs-breadcrumb-divider:'›';" itemscope itemtype="https://schema.org/BreadcrumbList">
    <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a href="/" itemprop="item" style="color:#5f6368;text-decoration:none;"><span itemprop="name">Home</span></a><meta itemprop="position" content="1"></li>
    <li class="breadcrumb-item active" aria-current="page" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><span itemprop="name"><?= h($queryParams['city'] ?? $queryParams['destination'] ?? 'Tanzania') ?> Hotels</span><meta itemprop="position" content="2"></li>
  </ol>
</nav>
<!-- ── Split Screen: HALF — LEFT (search+chips+list scroll) + RIGHT (map full-height from header) ── -->
<main id="main-content" class="gh-split-container" style="background:#f8f9fa; min-height:85vh; margin-top:0;" role="main" aria-label="Hotel search results">
    <div class="container-fluid px-1 px-lg-2" style="max-width:100%; margin:0 auto; height:100%; padding-top:0;">
        <div class="row g-0 g-lg-0 position-relative" style="--bs-gutter-x:0; --bs-gutter-y:0; height:100%; margin-top:0; padding-top:0;">

            <!-- ══ LEFT PANE: HALF screen — search+chips fixed + list scrolls ══ -->
            <div class="col-xl-6 col-lg-6 col-md-12 pe-lg-1" id="gh_list_col">
                <section class="gh-left-fixed" aria-label="Search and filters">
                    <!-- Search bar now inside left half so map can start at header -->
                    <?= $this->element('Home/gh-search-bar') ?>
                    <?= $this->element('Home/gh-filter-chips') ?>
                </section>
                <section class="gh-left-scroll pt-1 pb-0 pe-1" aria-label="Stays list" aria-live="polite" aria-busy="false" id="gh_results_section">

                    <!-- Search warnings / notices (hardened) -->
                    <?php if (!empty($searchErrors)): ?>
                        <div class="alert alert-warning d-flex align-items-start gap-2 mb-3 rounded-3" role="alert" style="font-size:13.5px;">
                            <i class="fa-solid fa-circle-info mt-1 flex-shrink-0"></i>
                            <div>
                                <strong>Search updated</strong>
                                <ul class="mb-0 ps-3 mt-1">
                                    <?php foreach ($searchErrors as $err): ?>
                                        <li><?= h($err) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Results count header: "near Mikocheni, Dar es Salaam · 118 results" + info icon — EXACT screenshot -->
                    <?= $this->element('Home/gh-results-header') ?>

                    <!-- Google Hotels Cards Grid — EXACT: photo left + dots + bookmark, name+price, rating ★, amenity 3-col, View prices (blue) / View details (outline) -->
                    <div id="gh_cards_live" aria-live="polite">
                    <?= $this->element('Home/gh-hotel-cards') ?>
                    </div>
                    <noscript><div class="alert alert-info mt-3">Enable JavaScript for live filtering, map and instant price updates. <a href="/">Reload</a></div></noscript>
                    <!-- Shared Footer — same as other pages (full 5-column) inside left scroll so map stays fixed -->
                    <div class="gh-index-footer-wrap">
                        <?= $this->element('footer', ['skin' => 'skin-light-footer']) ?>
                    </div>
                </section>
            </div>

            <!-- ══ RIGHT PANE: HALF screen — map starts right after header, full height ── -->
            <div class="col-xl-6 col-lg-6 d-none d-lg-block ps-lg-1" id="gh_map_col" aria-hidden="true">
                <div class="gh-map-sticky">
                    <div class="gh-map-frame shadow-sm position-relative" style="background:#e8ecef; min-height:560px; height:100%; height:calc(100vh - 80px);">

                        <!-- Mapbox GL Container — real Mapbox from backend -->
                        <div id="gh-interactive-map" style="width:100%; height:100%; min-height:560px; background:#e8ecef;" role="application" aria-label="Map of hotels"></div>
                        <noscript><img src="https://api.mapbox.com/styles/v1/mapbox/streets-v12/static/-6.7725,39.245,11/600x800?access_token=placeholder" alt="Map of hotels" style="width:100%;height:100%;object-fit:cover;opacity:.6"></noscript>

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
                                style="width:36px; height:36px; font-size:18px; color:#5f6368; border-color:#dadce0 !important;"
                                onclick="if(window._ghMap) window._ghMap.zoomIn()" title="Zoom in" aria-label="Zoom in">+</button>
                            <button type="button" class="btn btn-white p-0 d-flex align-items-center justify-content-center bg-white border-0 border-bottom"
                                style="width:36px; height:36px; font-size:20px; color:#5f6368; border-color:#dadce0 !important;"
                                onclick="if(window._ghMap) window._ghMap.zoomOut()" title="Zoom out" aria-label="Zoom out">−</button>
                            <button type="button" class="btn btn-white p-0 d-flex align-items-center justify-content-center bg-white border-0 border-bottom"
                                style="width:36px; height:36px; font-size:14px; color:#5f6368; border-color:#dadce0 !important;"
                                onclick="if(window.ghRecenter) window.ghRecenter()" title="Recenter to all stays" aria-label="Recenter"><i class="fa-solid fa-crosshairs"></i></button>
                            <button type="button" class="btn btn-white p-0 d-flex align-items-center justify-content-center bg-white border-0"
                                style="width:36px; height:36px; font-size:14px; color:#5f6368;"
                                onclick="if(window.ghToggleFullscreen) window.ghToggleFullscreen()" title="Fullscreen" aria-label="Fullscreen"><i class="fa-solid fa-expand"></i></button>
                        </div>



                        <!-- Bottom Transit Toggle — UX: label + accessible range -->
                        <div class="position-absolute bottom-0 start-50 translate-middle-x mb-3 bg-white rounded-pill shadow-sm border px-2 py-1 d-flex align-items-center gap-2" style="z-index:20; border-color:#dadce0 !important;" role="group" aria-label="Transit mode">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-dark text-white" style="width:20px;height:20px;font-size:11px;" aria-hidden="true">✕</span>
                            <i class="fa-solid fa-person-walking" style="font-size:13px;color:#5f6368" aria-hidden="true"></i>
                            <span style="font-size:13px;font-weight:500;color:#202124" id="gh_transit_label">Off</span>
                            <i class="fa-solid fa-caret-down" style="font-size:10px;color:#5f6368" aria-hidden="true"></i>
                            <input type="range" min="0" max="100" value="30" style="width:70px; accent-color:#5f6368" aria-labelledby="gh_transit_label" aria-label="Transit distance">
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

<!-- Mobile Floating Map / List Toggle — peek sheet -->
<div class="d-lg-none position-fixed bottom-0 start-50 translate-middle-x mb-4" style="z-index:1040;">
    <button type="button" id="gh_mob_toggle" onclick="ghToggleMobileView()"
        class="btn btn-dark shadow-lg rounded-pill px-4 py-2 fw-bold d-inline-flex align-items-center gap-2 border border-2 border-white"
        style="font-size:14px; background:#202124;">
        <i class="fa-solid fa-map-location-dot" id="gh_mob_icon" style="color:#fbbc04;"></i>
        <span id="gh_mob_text">View Map</span>
    </button>
</div>
<!-- Mobile bottom sheet peek — shows list over map -->
<div class="gh-mobile-sheet d-lg-none" id="gh_mobile_sheet" aria-hidden="true">
    <div class="gh-sheet-handle" id="gh_sheet_handle"><span></span></div>
    <div class="gh-sheet-scroll" id="gh_sheet_scroll"></div>
</div>
<!-- Mobile horizontal carousel anchored bottom when viewing map -->
<div class="gh-mobile-carousel d-lg-none" id="gh_mobile_carousel" aria-hidden="true"></div>

<!-- Filters Modal -->
<?= $this->element('Home/gh-filters-modal') ?>

<?php
// Real Mapbox from backend — server-side injection avoids CORS/race and powers map on first paint
$serverMapboxToken = $mapboxToken ?? \Cake\Core\Configure::read('App.mapboxToken', env('MAPBOX_TOKEN', ''));
$serverMapboxStyle = $mapboxStyle ?? \Cake\Core\Configure::read('App.mapboxStyle', 'mapbox://styles/mapbox/streets-v12');
if (!is_string($serverMapboxToken)) $serverMapboxToken = '';
if (!is_string($serverMapboxStyle) || $serverMapboxStyle === '') $serverMapboxStyle = 'mapbox://styles/mapbox/streets-v12';
?>
<script>
// Server-injected Mapbox — real token from backend GET /api/map-config (with env fallback)
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

<!-- FastNetState — URL deep-linking engine (push/replaceState, popstate, AJAX hydration) -->
<?= $this->Html->script('/assets/js/fastnet-state.js?v=' . filemtime(WWW_ROOT . 'assets/js/fastnet-state.js')) ?>
<!-- Google Hotels Interactive Map Script — uses real Mapbox style/token from backend -->
<?= $this->Html->script('/assets/js/gh-home-map.js?v=' . filemtime(WWW_ROOT . 'assets/js/gh-home-map.js')) ?>

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
        // gh-home-map.js now handles OSM fallback automatically when token missing — just ensure style is set
        if (!window.MAPBOX_TOKEN && !window.MAPBOX_STYLE) {
            window.MAPBOX_STYLE = 'https://demotiles.maplibre.org/style.json';
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
<div id="fns_toast" role="status" aria-live="polite" aria-atomic="true" style="position:fixed;bottom:20px;bottom:calc(20px + env(safe-area-inset-bottom));left:50%;transform:translateX(-50%);background:#111827;color:#fff;padding:11px 18px;border-radius:9999px;font-size:13px;font-weight:500;display:none;z-index:4000;box-shadow:0 8px 30px rgba(0,0,0,.18),0 2px 8px rgba(0,0,0,.12);max-width:min(92vw,420px);text-align:center;pointer-events:none;font-family:'Inter',Roboto,sans-serif"></div>
