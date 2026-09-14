<?php
/**
 * fastnetstays.com — Google Hotels Exact Card Layout
 * EXACT match of screenshot:
 * - Photo left (180px) with dots + bookmark
 * - Right: [Name .............. TSh XX,XXX]
 *           [4.0 ★ (N)]
 *           [icon amenity] [icon amenity] [icon amenity]  ← 3 column grid
 *           [icon amenity] [icon amenity] [icon amenity]
 *                                         [View prices ▶]
 */
if (!function_exists('ghNormalizeImgUrl')) {
    function ghNormalizeImgUrl(string $url): string {
        $url = trim($url);
        if ($url === '') return '';
        // If image points to local or relative storage in production, resolve with configured backend host
        if (str_starts_with($url, '/storage/') || str_contains($url, '127.0.0.1:8000/storage') || str_contains($url, 'localhost/storage')) {
            $apiBase = (string)\Cake\Core\Configure::read('App.backendApiUrl', \Cake\Core\env('BACKEND_API_URL', 'http://127.0.0.1:8000/api'));
            $backendHost = rtrim(preg_replace('#/api/?$#', '', $apiBase), '/');
            $storagePath = substr($url, strpos($url, '/storage/'));
            return $backendHost . $storagePath;
        }
        return $url;
    }
}
if (!function_exists('ghPropImage2')) {
    function ghPropImage2(array $p): string {
        foreach (['image_url','primary_image_url','cover_image','thumbnail'] as $k) {
            if (!empty($p[$k])) return ghNormalizeImgUrl((string)$p[$k]);
        }
        return '';
    }
}
if (!function_exists('ghPropImages')) {
    function ghPropImages(array $p): array {
        $out = [];
        // Primary images array (real deploy will have $p['images'] as JSON or array of urls/objects)
        $raw = $p['images'] ?? $p['gallery'] ?? $p['photos'] ?? null;
        if (is_string($raw)) { $d = json_decode($raw, true); if (is_array($d)) $raw = $d; else $raw = null; }
        if (is_array($raw)) {
            foreach ($raw as $it) {
                $u = is_array($it) ? ($it['url'] ?? $it['image_url'] ?? $it['src'] ?? '') : (string)$it;
                $u = ghNormalizeImgUrl($u);
                if ($u !== '' && !in_array($u, $out, true)) $out[] = $u;
                if (count($out) >= 8) break;
            }
        }
        // fallback single fields if no gallery
        if (empty($out)) {
            foreach (['primary_image_url','image_url','cover_image','thumbnail'] as $k) {
                if (!empty($p[$k])) {
                    $u = ghNormalizeImgUrl((string)$p[$k]);
                    if ($u !== '' && !in_array($u, $out, true)) $out[] = $u;
                }
            }
        }
        return $out;
    }
}
?>


<!-- Shimmer skeletons — any-device (320→2560), dvh-safe, reduced-motion -->
<div id="gh-shimmer-container" style="display:none;" aria-hidden="true" aria-live="polite" aria-busy="true">
    <div class="fns-shimmer-chips show" id="fns_shimmer_chips" aria-hidden="true">
        <?php for($i=0;$i<6;$i++): ?><div class="gh-shim-block fns-shimmer-chip" style="width:<?= 84+($i%3)*18 ?>px"></div><?php endfor; ?>
    </div>
    <?php for ($s=0;$s<4;$s++): ?>
    <div class="gh-shimmer" role="presentation">
        <div class="gh-shim-block" style="flex:0 0 185px;height:180px;" aria-hidden="true"></div>
        <div style="flex:1;padding:12px 16px;display:flex;flex-direction:column;gap:8px;min-width:0">
            <div style="display:flex;justify-content:space-between;gap:8px;align-items:center">
                <div class="gh-shim-block" style="height:16px;flex:0 0 55%;max-width:55%"></div>
                <div class="gh-shim-block" style="height:16px;width:88px;flex-shrink:0"></div>
            </div>
            <div class="gh-shim-block" style="height:12px;width:120px;max-width:60%"></div>
            <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px;">
                <?php for($i=0;$i<9;$i++): ?><div class="gh-shim-block" style="height:12px;"></div><?php endfor; ?>
            </div>
            <div style="display:flex;justify-content:flex-end;">
                <div class="gh-shim-block" style="height:36px;width:128px;border-radius:9999px;"></div>
            </div>
        </div>
    </div>
    <?php endfor; ?>
</div>

<!-- Live property cards -->
<div id="gh-cards-container">
<?php if (!empty($properties)): ?>
    <?php foreach ($properties as $idx => $prop):
        if (!is_array($prop)) continue;
        $propId = $prop['id'] ?? 0;
        $title  = \App\Utility\TextFormatter::formatTitle((string)($prop['name'] ?? 'Hotel'));

        // Rating — "4.0 ★ (158)" format
        $ratingRaw    = !empty($prop['reviews_avg_rating']) ? (float)$prop['reviews_avg_rating'] : (!empty($prop['rating']) ? (float)$prop['rating'] : null);
        $ratingFmt    = $ratingRaw !== null ? number_format($ratingRaw, 1) : null;
        $reviewsCount = (int)($prop['reviews_count'] ?? 0);

        // Stars (1-5)
        $stars   = !empty($prop['star_rating']) ? max(1, min(5, (int)$prop['star_rating'])) : 0;
        $propType = !empty($prop['property_type']) ? ucfirst($prop['property_type']) : '';
        $starLabel = $stars > 0 ? $stars . '-star hotel' : ($propType ?: '');

        // Price — nightly vs stay total (real logic: total = nightly * nights)
        $price      = (int)($prop['customer_price_per_night'] ?? ($prop['price_per_night'] ?? ($prop['price'] ?? 0)));
        $currency   = ($price > 500) ? 'TSh ' : '$';
        $priceLabel = $price > 0 ? $currency . number_format($price) : '';
        // nights for stay total
        $ciTmp = $queryParams['checkin'] ?? $queryParams['checkIn'] ?? null;
        $coTmp = $queryParams['checkout'] ?? $queryParams['checkOut'] ?? null;
        $nightsForPrice = 1;
        if (!empty($ciTmp) && !empty($coTmp)) {
            $tsCi = strtotime((string)$ciTmp); $tsCo = strtotime((string)$coTmp);
            if ($tsCi && $tsCo) $nightsForPrice = max(1, (int)round(($tsCo - $tsCi) / 86400));
        }
        $totalPrice = $price * $nightsForPrice;
        $totalLabel = $price > 0 ? $currency . number_format($totalPrice) : '';

        // Real coords? Coord-less cards must not render the Dar fallback tile (identical maps look broken).
        $rawLat = $prop['latitude'] ?? ($prop['lat'] ?? null);
        $rawLng = $prop['longitude'] ?? ($prop['lng'] ?? null);
        $hasRealCoords = is_numeric($rawLat) && is_numeric($rawLng) && ((float)$rawLat != 0.0 || (float)$rawLng != 0.0);

        // Location formatting
        $locText = \App\Utility\TextFormatter::formatLocation((string)($prop['area'] ?? ''), (string)($prop['city'] ?? ''));

        // Amenity detection (from raw amenities string/array + specific fields)
        $amenRaw = $prop['amenities'] ?? [];
        if (is_string($amenRaw)) {
            $dec = json_decode($amenRaw, true);
            $amenRaw = is_array($dec) ? $dec : explode(',', $amenRaw);
        }
        $amenStr = strtolower(implode(' ', (array)$amenRaw) . ' ' . ($prop['description'] ?? ''));

        $hasBreakfast    = !empty($prop['breakfast_included']) || str_contains($amenStr, 'breakfast');
        $hasAC           = str_contains($amenStr, 'air conditioning') || str_contains($amenStr, 'air-condition');
        $hasParking      = !empty($prop['free_parking']) || str_contains($amenStr, 'parking');
        $hasPool         = str_contains($amenStr, 'pool');
        $hasPet          = str_contains($amenStr, 'pet');
        $hasKid          = str_contains($amenStr, 'kid') || str_contains($amenStr, 'child');
        $hasShuttle      = str_contains($amenStr, 'shuttle') || str_contains($amenStr, 'airport transfer');
        $hasRoomService  = str_contains($amenStr, 'room service');
        $hasSmokeFree    = str_contains($amenStr, 'smoke');
        $hasWifi         = str_contains($amenStr, 'wi-fi') || str_contains($amenStr, 'wifi');
        $hasFitness      = str_contains($amenStr, 'fitness') || str_contains($amenStr, 'gym');
        $hasHotTub       = str_contains($amenStr, 'hot tub') || str_contains($amenStr, 'jacuzzi');
        $hasFreeCancel   = !empty($prop['free_cancellation']) || str_contains(strtolower((string)($prop['cancellation_policy']??'')), 'free');

        // Build amenity list (max 9 items for the 3×3 grid)
        $amenities = [];
        if ($starLabel)       $amenities[] = ['fa-hotel',          $starLabel];
        if ($hasAC)           $amenities[] = ['fa-snowflake',      'Air conditioning'];
        if ($hasShuttle)      $amenities[] = ['fa-van-shuttle',    'Airport shuttle'];
        if ($hasBreakfast)    $amenities[] = ['fa-mug-saucer',     'Breakfast'];
        if ($hasPet)          $amenities[] = ['fa-paw',            'Pet-friendly'];
        if ($hasKid)          $amenities[] = ['fa-child',          'Kid-friendly'];
        if ($hasParking)      $amenities[] = ['fa-square-parking', 'Free parking'];
        if ($hasRoomService)  $amenities[] = ['fa-bell-concierge', 'Room service'];
        if ($hasSmokeFree)    $amenities[] = ['fa-ban-smoking',    'Smoke-free property'];
        if ($hasPool)         $amenities[] = ['fa-person-swimming','Pool'];
        if ($hasWifi)         $amenities[] = ['fa-wifi',           'Free Wi-Fi'];
        if ($hasHotTub)       $amenities[] = ['fa-hot-tub-person', 'Hot tub'];
        if ($hasFitness)      $amenities[] = ['fa-dumbbell',       'Fitness center'];

        // Button type: first card shown if no price → "View details" (outline). Otherwise "View prices" (solid).
        $hasPrice   = $price > 0;
        $cleanQP = array_filter($queryParams ?? [], function($v){ return $v!=='' && $v!==null; });
        // dedupe amenities
        if(!empty($cleanQP['amenities'])){
            $parts = array_unique(array_filter(array_map('trim', explode(',', $cleanQP['amenities']))));
            $cleanQP['amenities'] = implode(',', $parts);
            if($cleanQP['amenities']==='') unset($cleanQP['amenities']);
        }
        if(isset($cleanQP['sort']) && ($cleanQP['sort']==='recommended' || $cleanQP['sort']==='')) unset($cleanQP['sort']);
        $detailUrl  = $this->Url->build('/hotel-detail/' . $propId . (!empty($cleanQP) ? '?' . http_build_query($cleanQP) : ''));
    ?>
    <div
        class="gh-card<?= $hasRealCoords ? '' : ' gh-no-map' ?>"
        id="gh-card-<?= $propId ?>"
        data-property-id="<?= $propId ?>"
        onclick="window.location='<?= $detailUrl ?>'"
        onmouseenter="if(window.ghHighlightMarker)window.ghHighlightMarker(<?= $propId ?>,true)"
        onmouseleave="if(window.ghHighlightMarker)window.ghHighlightMarker(<?= $propId ?>,false)"
    >
        <!-- ── Top media: Photo + Mobile mini-map (mobile only split) ── -->
        <div class="gh-card-media-row">
        <div class="gh-card-photo gh-card-photo--slider" data-prop-id="<?= $propId ?>">
            <?php $cardImgs = ghPropImages($prop); $isSlider = count($cardImgs) > 1; ?>
            <?php if ($isSlider): ?>
                <div class="gh-card-track" id="gh-track-<?= $propId ?>">
                    <?php foreach ($cardImgs as $ci => $u): ?>
                        <img src="<?= str_starts_with($u,'http') ? h($u) : $this->Url->build('/'.h($u)) ?>" alt="<?= h($title) ?> photo <?= $ci+1 ?>" loading="<?= $ci===0?'eager':'lazy' ?>" draggable="false" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800&h=600&fit=crop'">
                    <?php endforeach; ?>
                </div>
                <button type="button" class="gh-card-nav gh-card-prev" onclick="event.stopPropagation();ghSlide(<?= $propId ?>,-1)" aria-label="Previous image">‹</button>
                <button type="button" class="gh-card-nav gh-card-next" onclick="event.stopPropagation();ghSlide(<?= $propId ?>,1)" aria-label="Next image">›</button>
            <?php else: ?>
                <?php $img = $cardImgs[0] ?? ghPropImage2($prop); ?>
                <?php if ($img !== ''): ?>
                    <img src="<?= str_starts_with($img,'http') ? h($img) : $this->Url->build('/'.h($img)) ?>" alt="<?= h($title) ?>" loading="lazy" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800&h=600&fit=crop'">
                <?php else: ?>
                    <div class="gh-card-no-photo"><div><i class="fa-solid fa-image" style="font-size:30px;color:#bdbdbd;"></i><br>No photo</div></div>
                <?php endif; ?>
            <?php endif; ?>
            <!-- Bookmark -->
            <button type="button" class="gh-card-bm" onclick="event.stopPropagation();ghBookmark(<?= $propId ?>,this)" title="Save" aria-label="Save">
                <i class="fa-regular fa-bookmark" style="font-size:12px;"></i>
            </button>
            <!-- Carousel dots / counter -->
            <?php if ($isSlider): ?>
                <div class="gh-card-dots" id="gh-dots-<?= $propId ?>">
                    <?php foreach ($cardImgs as $di => $_): ?>
                        <button type="button" class="gh-card-dot <?= $di===0?'a active':'' ?>" data-idx="<?= $di ?>" onclick="event.stopPropagation();ghGoSlide(<?= $propId ?>,<?= $di ?>)" aria-label="Go to image <?= $di+1 ?>"></button>
                    <?php endforeach; ?>
                </div>
                <span class="gh-card-count" id="gh-count-<?= $propId ?>">1 / <?= count($cardImgs) ?></span>
            <?php else: ?>
                <div class="gh-card-dots"><div class="gh-card-dot a"></div><div class="gh-card-dot"></div><div class="gh-card-dot"></div><div class="gh-card-dot"></div><div class="gh-card-dot"></div></div>
            <?php endif; ?>
        </div>
        <!-- Mobile mini-map with blue price pill (visible only ≤767px) — only when real coordinates exist -->
        <?php
            $mlat = (float)($prop['latitude'] ?? ($prop['lat'] ?? 0));
            $mlng = (float)($prop['longitude'] ?? ($prop['lng'] ?? 0));
            // Only render map tile if property has real coordinates (not 0,0 or the generic fallback)
            $hasRealCoords = ($mlat !== 0.0 || $mlng !== 0.0)
                && !($mlat === -6.7725 && $mlng === 39.245); // skip generic Dar fallback
        ?>
        <?php if ($hasRealCoords): ?>
        <div class="gh-card-map-mobile" aria-hidden="true">
            <?php
                $mapService = new \App\Service\MapService();
                $mapImg = $mapService->getStaticMapUrl($mlat, $mlng, 13, 300, 300);
                $svgFallback = $mapService->getSvgFallbackUrl(300, 300);
            ?>
            <img src="<?= h($mapImg) ?>" alt="Map location" loading="lazy" onerror="this.onerror=null;this.src='<?= $svgFallback ?>';">
            <span class="gh-card-map-price" data-nightly="<?= h($priceLabel) ?>" data-total="<?= h($totalLabel) ?>"><?= h($priceLabel) ?></span>
        </div>
        <?php endif; ?>
        </div>

        <!-- ── Info body ── -->
        <div class="gh-card-body">
            <!-- Row 1: Name + Price -->
            <div class="gh-name-price-row">
                <a href="<?= $detailUrl ?>" class="gh-hotel-name" onclick="event.stopPropagation()">
                    <?= h($title) ?>
                </a>
                <?php if ($priceLabel !== ''): ?>
                    <span class="gh-hotel-price" data-nightly="<?= h($priceLabel) ?>" data-total="<?= h($totalLabel) ?>" data-nights="<?= (int)$nightsForPrice ?>"><?= h($priceLabel) ?><span class="gh-price-night">/night</span></span>
                <?php endif; ?>
            </div>

            <!-- Row 2: Rating "4.x ★ (66) · 3-star hotel" — screenshot style -->
            <?php if ($ratingFmt !== null || $starLabel !== ''): ?>
                <div class="gh-rating-inline">
                    <?php if ($ratingFmt !== null): ?>
                        <span class="gh-rating-num"><?= h($ratingFmt) ?></span>
                        <span class="gh-rating-star">★</span>
                        <?php if ($reviewsCount > 0): ?>
                            <span class="gh-rating-count">(<?= number_format($reviewsCount) ?>)</span>
                        <?php endif; ?>
                        <?php if ($starLabel !== ''): ?><span style="color:#5f6368; margin:0 4px;">·</span><span style="color:#5f6368; font-size:12.5px;"><?= h($starLabel) ?></span><?php endif; ?>
                    <?php elseif ($starLabel !== ''): ?>
                        <span style="color:#5f6368; font-size:12.5px;"><?= h($starLabel) ?></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Amenity 3-column grid -->
            <?php if (!empty($amenities)): ?>
                <div class="gh-amenity-grid">
                    <?php foreach (array_slice($amenities, 0, 9) as [$icon, $label]): ?>
                        <div class="gh-am-item">
                            <i class="fa-solid <?= $icon ?>"></i>
                            <span><?= h($label) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Bottom: Button right-aligned (desktop) -->
            <div class="gh-card-bottom">
                <?php if (!$hasPrice): ?>
                    <a href="<?= $detailUrl ?>" class="gh-btn-details" onclick="event.stopPropagation()">View details</a>
                <?php else: ?>
                    <a href="<?= $detailUrl ?>" class="gh-btn-prices" onclick="event.stopPropagation()">View prices</a>
                <?php endif; ?>
            </div>
            <!-- Mobile: price + Show details (price visible so users know cost before tapping) -->
            <div class="gh-card-viewmap">
                <?php if ($priceLabel !== ''): ?>
                <div style="display:flex;flex-direction:column;line-height:1.2;min-width:0;">
                    <span class="gh-hotel-price" style="display:inline-block !important;font-size:15px;font-weight:700;color:#202124;" data-nightly="<?= h($priceLabel) ?>" data-total="<?= h($totalLabel) ?>" data-nights="<?= (int)$nightsForPrice ?>"><?= h($priceLabel) ?><span class="gh-price-night" style="font-size:11px;font-weight:400;color:#5f6368;">/night</span></span>
                </div>
                <?php endif; ?>
                <a href="<?= $detailUrl ?>" class="gh-btn-prices" onclick="event.stopPropagation()" style="<?= $priceLabel!=='' ? 'flex:0 0 auto;' : 'flex:1;justify-content:center;' ?>min-height:44px;font-size:14px;font-weight:600;touch-action:manipulation;">
                    Show details
                </a>
                <button type="button" class="gh-viewmap-bm" onclick="event.stopPropagation();ghBookmark(<?= $propId ?>,this)" aria-label="Save" style="width:44px;height:44px;border:1px solid #dadce0;border-radius:50%;background:#fff;touch-action:manipulation;"><i class="fa-regular fa-bookmark"></i></button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="gh-empty">
        <div class="gh-empty-icon"><i class="fa-solid fa-magnifying-glass"></i></div>
        <h4>No stays found for "<?= h($queryParams['destination'] ?? $destination ?? 'your search') ?>"</h4>
        <p>Try widening your map area, changing dates, or <a href="#" onclick="document.getElementById('trivagoFiltersModal')&&bootstrap.Modal.getOrCreateInstance(document.getElementById('trivagoFiltersModal')).show();return false;" style="color:#1a73e8;text-decoration:none;">clearing filters</a>. We found stays nearby for similar dates.</p>
        <div class="gh-empty-actions">
            <a href="<?= $this->Url->build(['?' => ['destination' => $queryParams['destination'] ?? 'Dar es Salaam']]) ?>" class="gh-empty-btn secondary"><i class="fa-solid fa-filter-circle-xmark"></i> Clear filters</a>
            <a href="<?= $this->Url->build('/') ?>" class="gh-empty-btn primary"><i class="fa-solid fa-rotate"></i> Reset search</a>
        </div>
    </div>
<?php endif; ?>
</div>

<script>
function ghBookmark(propId, btn) {
    var icon = btn ? btn.querySelector('i') : null;
    if (icon) {
        var isActive = icon.classList.contains('fa-solid');
        icon.className = isActive ? 'fa-regular fa-bookmark' : 'fa-solid fa-bookmark';
        icon.style.color = isActive ? '' : '#fff';
        if(navigator.vibrate) try{ navigator.vibrate(10);}catch(e){}
    }
    if (typeof toggleWishlist === 'function') toggleWishlist(propId, btn);
}
window.ghScrollToCard = function(propId) {
    var c = document.getElementById('gh-card-' + propId);
    if (c) c.scrollIntoView({ behavior:'smooth', block:'nearest' });
};
// ── Card slider: sliding images for demo + real deploy (same logic: $prop['images'] array) ──
window._ghSlideIdx = window._ghSlideIdx || {};
window.ghGoSlide = function(propId, idx){
    var track = document.getElementById('gh-track-' + propId);
    var dots = document.getElementById('gh-dots-' + propId);
    var count = document.getElementById('gh-count-' + propId);
    if(!track) return;
    var total = track.children.length;
    if(idx < 0) idx = total - 1;
    if(idx >= total) idx = 0;
    window._ghSlideIdx[propId] = idx;
    track.style.transform = 'translateX(' + (-idx * 100) + '%)';
    if(dots){ Array.prototype.forEach.call(dots.children, function(d,i){ d.classList.toggle('a', i===idx); d.classList.toggle('active', i===idx); }); }
    if(count) count.textContent = (idx+1) + ' / ' + total;
};
window.ghSlide = function(propId, dir){ var cur = window._ghSlideIdx[propId] || 0; window.ghGoSlide(propId, cur + dir); };
// touch swipe for cards
(function(){
    document.addEventListener('DOMContentLoaded', function(){
        document.querySelectorAll('.gh-card-photo--slider').forEach(function(el){
            var pid = el.getAttribute('data-prop-id');
            if(!pid) return;
            var startX = 0, dx = 0, dragging = false;
            el.addEventListener('touchstart', function(e){ startX = e.touches[0].clientX; dragging = true; }, {passive:true});
            el.addEventListener('touchmove', function(e){ if(!dragging) return; dx = e.touches[0].clientX - startX; }, {passive:true});
            el.addEventListener('touchend', function(){ if(!dragging) return; dragging = false; if(Math.abs(dx) > 36){ window.ghSlide(parseInt(pid,10), dx < 0 ? 1 : -1); } dx = 0; });
            // also mouse drag for desktop
            el.addEventListener('mousedown', function(e){ startX = e.clientX; dragging = true; e.preventDefault(); });
            window.addEventListener('mouseup', function(e){ if(!dragging) return; dragging = false; var diff = e.clientX - startX; if(Math.abs(diff) > 36){ window.ghSlide(parseInt(pid,10), diff < 0 ? 1 : -1); } });
        });
    });
})();
// any-device shimmer: instant on any filter/search, auto-hide on hydrate + 8s fail-safe + CTA spinner
(function(){
  var ctaBusy=function(on){
    var btn=document.getElementById('fns_search_btn');
    var mBtn=document.querySelector('#fns_mobile_sheet .fns-btn-apply');
    if(btn) btn.setAttribute('aria-busy', on?'true':'false');
    if(mBtn) mBtn.setAttribute('aria-busy', on?'true':'false');
    var inputs=document.querySelectorAll('#gh_dest,#fns_m_input'); inputs.forEach(function(i){ i.setAttribute('aria-busy', on?'true':'false'); });
  };
  var show=function(){
    var cards=document.getElementById('gh-cards-container');
    var shim=document.getElementById('gh-shimmer-container');
    var sec=document.getElementById('gh_results_section');
    if(cards&&shim){ cards.style.display='none'; shim.style.display='block'; shim.setAttribute('aria-hidden','false'); if(sec) sec.setAttribute('aria-busy','true'); }
    var chips=document.getElementById('fns_shimmer_chips'); if(chips) chips.classList.add('show');
    ctaBusy(true);
  };
  var hide=function(){
    var cards=document.getElementById('gh-cards-container');
    var shim=document.getElementById('gh-shimmer-container');
    var sec=document.getElementById('gh_results_section');
    if(cards&&shim){ cards.style.display=''; shim.style.display='none'; shim.setAttribute('aria-hidden','true'); if(sec) sec.setAttribute('aria-busy','false'); }
    ctaBusy(false);
  };
  window.ghTriggerShimmer=show;
  window.fnsTriggerShimmer=show;
  window.ghHideShimmer=hide;
  // No skeleton on initial load: server already rendered real cards. Shimmer only
  // during AJAX transitions (fastnet:shimmer-show from FastNetState.hydrate).
  // fail-safe: hide after 8s if network hangs
  var t=null;
  window.addEventListener('fastnet:shimmer-show', function(){ clearTimeout(t); show(); t=setTimeout(hide,8000); });
  window.addEventListener('fastnet:shimmer-hide', hide);
  // hook FastNetState hydrate
  var w=0;
  document.addEventListener('DOMContentLoaded', function(){
    var check=setInterval(function(){
      if(window.FastNetState && !w){
        w=1; clearInterval(check);
        var orig=window.FastNetState.hydrate;
        window.FastNetState.hydrate=async function(u){
          window.dispatchEvent(new CustomEvent('fastnet:shimmer-show'));
          try{ return await orig.call(this,u); } finally{ window.dispatchEvent(new CustomEvent('fastnet:shimmer-hide')); }
        };
      }
    },200);
    setTimeout(function(){ clearInterval(check); },5000);
  });
})();
</script>
