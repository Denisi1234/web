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
if (!function_exists('ghPropImage2')) {
    function ghPropImage2(array $p): string {
        foreach (['image_url','primary_image_url','cover_image','thumbnail'] as $k) {
            if (!empty($p[$k])) return $p[$k];
        }
        return '';
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
        $propId = $prop['id'] ?? 0;
        $title  = $prop['name'] ?? '';

        // Rating — "4.0 ★ (158)" format
        $ratingRaw    = !empty($prop['reviews_avg_rating']) ? (float)$prop['reviews_avg_rating'] : (!empty($prop['rating']) ? (float)$prop['rating'] : null);
        $ratingFmt    = $ratingRaw !== null ? number_format($ratingRaw, 1) : null;
        $reviewsCount = (int)($prop['reviews_count'] ?? 0);

        // Stars (1-5)
        $stars   = !empty($prop['star_rating']) ? max(1, min(5, (int)$prop['star_rating'])) : 0;
        $propType = !empty($prop['property_type']) ? ucfirst($prop['property_type']) : '';
        $starLabel = $stars > 0 ? $stars . '-star hotel' : ($propType ?: '');

        // Price
        $price      = (int)($prop['customer_price_per_night'] ?? ($prop['price_per_night'] ?? ($prop['price'] ?? 0)));
        $currency   = ($price > 500) ? 'TSh ' : '$';
        $priceLabel = $price > 0 ? $currency . number_format($price) : '';

        // Location
        $area    = $prop['area'] ?? ($prop['address'] ?? '');
        $city    = $prop['city'] ?? '';
        $locText = trim(implode(', ', array_filter([ucwords($area), ucwords($city)])));

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
        class="gh-card"
        id="gh-card-<?= $propId ?>"
        data-property-id="<?= $propId ?>"
        onclick="window.location='<?= $detailUrl ?>'"
        onmouseenter="if(window.ghHighlightMarker)window.ghHighlightMarker(<?= $propId ?>,true)"
        onmouseleave="if(window.ghHighlightMarker)window.ghHighlightMarker(<?= $propId ?>,false)"
    >
        <!-- ── Top media: Photo + Mobile mini-map (mobile only split) ── -->
        <div class="gh-card-media-row">
        <div class="gh-card-photo">
            <?php $img = ghPropImage2($prop); ?>
            <?php if ($img !== ''): ?>
                <img
                    src="<?= str_starts_with($img,'http') ? h($img) : $this->Url->build('/'.h($img)) ?>"
                    alt="<?= h($title) ?>"
                    loading="lazy"
                >
            <?php else: ?>
                <div class="gh-card-no-photo">
                    <div><i class="fa-solid fa-image" style="font-size:30px;color:#bdbdbd;"></i><br>No photo</div>
                </div>
            <?php endif; ?>
            <!-- Bookmark -->
            <button type="button" class="gh-card-bm" onclick="event.stopPropagation();ghBookmark(<?= $propId ?>,this)" title="Save" aria-label="Save">
                <i class="fa-regular fa-bookmark" style="font-size:12px;"></i>
            </button>
            <!-- Carousel dots -->
            <div class="gh-card-dots">
                <div class="gh-card-dot a"></div>
                <div class="gh-card-dot"></div>
                <div class="gh-card-dot"></div>
                <div class="gh-card-dot"></div>
                <div class="gh-card-dot"></div>
            </div>
        </div>
        <!-- Mobile mini-map with blue price pill (visible only ≤767px) -->
        <div class="gh-card-map-mobile" aria-hidden="true">
            <?php
                // Use static map placeholder; if lat/lng available use Mapbox static
                $mlat = (float)($prop['latitude'] ?? ($prop['lat'] ?? -6.7725));
                $mlng = (float)($prop['longitude'] ?? ($prop['lng'] ?? 39.245));
                $mapImg = "https://api.mapbox.com/styles/v1/mapbox/streets-v12/static/pin-s+1a73e8($mlng,$mlat)/$mlng,$mlat,13,0/300x300@2x?access_token=pk.placeholder";
            ?>
            <img src="<?= h($mapImg) ?>" alt="" loading="lazy" onerror="this.style.background='#e8ecef'; this.src='data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27%3E%3C/svg%3E'">
            <span class="gh-card-map-price"><?= h($priceLabel) ?></span>
        </div>
        </div>

        <!-- ── Info body ── -->
        <div class="gh-card-body">
            <!-- Row 1: Name + Price -->
            <div class="gh-name-price-row">
                <a href="<?= $detailUrl ?>" class="gh-hotel-name" onclick="event.stopPropagation()">
                    <?= h($title) ?>
                </a>
                <?php if ($priceLabel !== ''): ?>
                    <span class="gh-hotel-price"><?= h($priceLabel) ?><span class="gh-price-night">/night</span></span>
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
            <!-- Mobile View map bar (visible ≤767px) — screenshot: View map | bookmark -->
            <div class="gh-card-viewmap">
                <a href="<?= $detailUrl ?>" class="gh-viewmap-btn" onclick="event.stopPropagation()">
                    <i class="fa-solid fa-map-location-dot" style="font-size:15px;"></i> View map
                </a>
                <span class="gh-viewmap-divider" aria-hidden="true"></span>
                <button type="button" class="gh-viewmap-bm" onclick="event.stopPropagation();ghBookmark(<?= $propId ?>,this)" aria-label="Save"><i class="fa-regular fa-bookmark"></i></button>
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
    window.dispatchEvent(new CustomEvent('fastnet:shimmer-show'));
  };
  var hide=function(){
    var cards=document.getElementById('gh-cards-container');
    var shim=document.getElementById('gh-shimmer-container');
    var sec=document.getElementById('gh_results_section');
    if(cards&&shim){ cards.style.display=''; shim.style.display='none'; shim.setAttribute('aria-hidden','true'); if(sec) sec.setAttribute('aria-busy','false'); }
    ctaBusy(false);
    window.dispatchEvent(new CustomEvent('fastnet:shimmer-hide'));
  };
  window.ghTriggerShimmer=show;
  window.fnsTriggerShimmer=show;
  window.ghHideShimmer=hide;
  // demo on first paint for any-device QA (2s)
  document.addEventListener('DOMContentLoaded', function(){
    // only demo if not already hydrated
    if(!sessionStorage.getItem('fns_shimmer_demo')){
      show();
      setTimeout(function(){ hide(); sessionStorage.setItem('fns_shimmer_demo','1'); }, 900);
    }
  });
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
