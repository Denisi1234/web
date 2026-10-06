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
<?= $this->Html->css('/assets/css/hotel-card-01.css?v=' . filemtime(WWW_ROOT . 'assets/css/hotel-card-01.css')) ?>
<?= $this->Html->css('/assets/css/hotel-card-02.css?v=' . filemtime(WWW_ROOT . 'assets/css/hotel-card-02.css')) ?>
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

                    <?php if (!empty($isSampleData)): ?>
                    <div style="background:#fffbeb;border:1px dashed #f59e0b;border-radius:12px;padding:10px 14px;font-size:12.5px;font-weight:600;color:#92400e;margin:0 0 12px">Sample stays — localhost preview only, never shown in production.</div>
                    <?php endif; ?>
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
<?= $this->element('Home/gh-index-foot') ?>
