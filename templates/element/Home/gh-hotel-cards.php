<?php
/**
 * fastnetstays.com — Hotel result cards.
 * Photo + name/location/rating/price + View prices CTA.
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

        // Rating — "4.0 ★ Excellent (158)" format (5-point scale + word label, Booking-style)
        $ratingRaw    = !empty($prop['reviews_avg_rating']) ? (float)$prop['reviews_avg_rating'] : (!empty($prop['rating']) ? (float)$prop['rating'] : null);
        $ratingFmt    = $ratingRaw !== null ? number_format($ratingRaw, 1) : null;
        $reviewsCount = (int)($prop['reviews_count'] ?? 0);
        $ratingWord   = '';
        if ($ratingRaw !== null && $ratingRaw > 0) {
            if ($ratingRaw >= 4.5) $ratingWord = 'Exceptional';
            elseif ($ratingRaw >= 4.0) $ratingWord = 'Excellent';
            elseif ($ratingRaw >= 3.5) $ratingWord = 'Very good';
            elseif ($ratingRaw >= 3.0) $ratingWord = 'Good';
            else $ratingWord = 'Pleasant';
        }

        // Stars (1-5)
        $stars   = !empty($prop['star_rating']) ? max(1, min(5, (int)$prop['star_rating'])) : 0;
        $propType = !empty($prop['property_type']) ? ucfirst($prop['property_type']) : '';
        $starLabel = $stars > 0 ? $stars . '-star hotel' : ($propType ?: '');

        // Price — backend customer_price_per_night ALREADY includes the 1% AzamPay fee
        // (Property::getCustomerPricePerNightAttribute + fee_note "Includes payment processing fee", VAT 0%).
        // Stay total = customer nightly × nights × rooms. Never claim anything beyond the fee_note.
        $price      = (int)($prop['customer_price_per_night'] ?? ($prop['price_per_night'] ?? ($prop['price'] ?? 0)));
        // Prices are stored in TZS. This previously picked the currency from the
        // price magnitude, so any property priced under 500 rendered in dollars.
        $currency   = 'TSh ';
        $priceLabel = $price > 0 ? $currency . number_format($price) : '';
        // nights × rooms for stay total
        $ciTmp = $queryParams['checkin'] ?? $queryParams['checkIn'] ?? null;
        $coTmp = $queryParams['checkout'] ?? $queryParams['checkOut'] ?? null;
        $nightsForPrice = 1;
        if (!empty($ciTmp) && !empty($coTmp)) {
            $tsCi = strtotime((string)$ciTmp); $tsCo = strtotime((string)$coTmp);
            if ($tsCi && $tsCo) $nightsForPrice = max(1, (int)round(($tsCo - $tsCi) / 86400));
        }
        $roomsForPrice = max(1, (int)($queryParams['rooms'] ?? 1));
        $totalPrice = $price * $nightsForPrice * $roomsForPrice;

        // Location formatting
        $locText = \App\Utility\TextFormatter::formatLocation((string)($prop['area'] ?? ''), (string)($prop['city'] ?? ''));

        // Amenity detection — structured flags first, then CAREFUL keyword
        // matching (word boundaries + exclusions). Bare str_contains() lies:
        // "pool table" is not a pool, "smoking area" is not smoke-free.
        if (!function_exists('ghHasAmen')) {
            function ghHasAmen(string $haystack, array $needles, array $exclude = []): bool {
                foreach ($exclude as $no) {
                    if (preg_match('/\b' . preg_quote($no, '/') . '\b/i', $haystack)) return false;
                }
                foreach ($needles as $yes) {
                    if (preg_match('/\b' . preg_quote($yes, '/') . '\b/i', $haystack)) return true;
                }
                return false;
            }
        }
        $amenRaw = $prop['amenities'] ?? [];
        if (is_string($amenRaw)) {
            $dec = json_decode($amenRaw, true);
            $amenRaw = is_array($dec) ? $dec : explode(',', $amenRaw);
        }
        $amenStr = strtolower(implode(' ', (array)$amenRaw) . ' ' . ($prop['description'] ?? ''));

        $hasBreakfast    = !empty($prop['breakfast_included']) || ghHasAmen($amenStr, ['breakfast']);
        $hasAC           = ghHasAmen($amenStr, ['air conditioning', 'air-conditioning', 'aircon', 'a/c', 'climate control']);
        $hasParking      = !empty($prop['free_parking']) || ghHasAmen($amenStr, ['parking', 'garage']);
        $hasPool         = ghHasAmen($amenStr, ['swimming pool', 'pool'], ['pool table', 'snooker', 'billiard', 'table tennis']);
        $hasPet          = ghHasAmen($amenStr, ['pet-friendly', 'pet friendly', 'pets allowed', 'dog-friendly', 'dog friendly']);
        $hasKid          = ghHasAmen($amenStr, ['kid-friendly', 'kid friendly', 'kids club', 'family-friendly', 'family friendly', 'playground']);
        $hasShuttle      = ghHasAmen($amenStr, ['airport shuttle', 'shuttle', 'airport transfer']);
        $hasRoomService  = ghHasAmen($amenStr, ['room service']);
        $hasSmokeFree    = ghHasAmen($amenStr, ['smoke-free', 'smoke free', 'smokefree', 'non-smoking', 'non smoking']);
        $hasWifi         = ghHasAmen($amenStr, ['wi-fi', 'wifi', 'wireless internet']);
        $hasFitness      = ghHasAmen($amenStr, ['fitness', 'gym']);
        $hasHotTub       = ghHasAmen($amenStr, ['hot tub', 'jacuzzi']);
        $hasFreeCancel   = !empty($prop['free_cancellation']) || ghHasAmen(strtolower((string)($prop['cancellation_policy']??'')), ['free cancellation', 'cancel free', 'free cancel']);

        // Description snippet: fills the card honestly when the property
        // carries no structured amenities (never invented — backend text only).
        $descRaw = trim(strip_tags((string)($prop['description'] ?? '')));
        $descSnippet = $descRaw !== '' ? mb_strimwidth($descRaw, 0, 140, '…') : '';

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
    <article
        class="gh-card"
        id="gh-card-<?= $propId ?>"
        data-property-id="<?= $propId ?>"
        tabindex="0"
        role="link"
        aria-label="<?= h($title) ?><?= $locText !== '' ? ', ' . h($locText) : '' ?><?= $priceLabel !== '' ? ', ' . h($priceLabel) . ' per night' : '' ?>"
        data-href="<?= $detailUrl ?>"
        onclick="window.location='<?= $detailUrl ?>'"
        onkeydown="if((event.key==='Enter'||event.key===' ')&&event.target===this){event.preventDefault();window.location='<?= $detailUrl ?>';}"
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
                        <img src="<?= str_starts_with($u,'http') ? h($u) : $this->Url->build('/'.h($u)) ?>" alt="<?= h($title) ?> photo <?= $ci+1 ?>" loading="<?= $ci===0?'eager':'lazy' ?>"<?= ($idx===0 && $ci===0) ? ' fetchpriority="high" decoding="async"' : ' decoding="async"' ?> draggable="false" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800&h=600&fit=crop'">
                    <?php endforeach; ?>
                </div>
                <button type="button" class="gh-card-nav gh-card-prev" onclick="event.stopPropagation();ghSlide(<?= $propId ?>,-1)" aria-label="Previous image">‹</button>
                <button type="button" class="gh-card-nav gh-card-next" onclick="event.stopPropagation();ghSlide(<?= $propId ?>,1)" aria-label="Next image">›</button>
            <?php else: ?>
                <?php $img = $cardImgs[0] ?? ghPropImage2($prop); ?>
                <?php if ($img !== ''): ?>
                    <img src="<?= str_starts_with($img,'http') ? h($img) : $this->Url->build('/'.h($img)) ?>" alt="<?= h($title) ?>" loading="<?= $idx===0?'eager':'lazy' ?>"<?= $idx===0?' fetchpriority="high" decoding="async"':' decoding="async"' ?> onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800&h=600&fit=crop'">
                <?php else: ?>
                    <div class="gh-card-no-photo"><div><i class="fa-solid fa-image" style="font-size:30px;color:#bdbdbd;"></i><br>No photo</div></div>
                <?php endif; ?>
            <?php endif; ?>
            <!-- Bookmark -->
            <button type="button" class="gh-card-bm" onclick="event.stopPropagation();ghBookmark(<?= $propId ?>,this)" title="Save" aria-label="Save">
                <i class="fa-regular fa-bookmark" style="font-size:12px;"></i>
            </button>
            <!-- Locate on map: touch/click focuses the pin without navigating -->
            <button type="button" class="gh-card-locate" onclick="event.stopPropagation();if(window.ghFocusMarker)window.ghFocusMarker(<?= $propId ?>)" title="Show on map" aria-label="Show on map">
                <i class="fa-solid fa-location-dot" style="font-size:12px;"></i>
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
        </div>

        <!-- ── Info body ── -->
        <div class="gh-card-body">
            <!-- Row 1: Name + Price (Baymard: nightly + stay total + fee transparency) -->
            <div class="gh-name-price-row">
                <a href="<?= $detailUrl ?>" class="gh-hotel-name" onclick="event.stopPropagation()">
                    <?= h($title) ?>
                </a>
                <?php if ($priceLabel !== ''): ?>
                    <span style="text-align:right;line-height:1.2;flex-shrink:0;">
                        <span class="gh-hotel-price" data-tzs="<?= $price ?>"><?= h($priceLabel) ?><span class="gh-price-night">/night</span></span>
                        <?php if ($totalPrice > 0 && ($nightsForPrice > 1 || $roomsForPrice > 1)): ?>
                            <span style="display:block;font-size:11.5px;font-weight:500;color:#525252;"><span data-tzs="<?= $totalPrice ?>"><?= h($currency . number_format($totalPrice)) ?></span> total for <?= (int)$nightsForPrice ?> night<?= $nightsForPrice !== 1 ? 's' : '' ?><?= $roomsForPrice > 1 ? ' · ' . (int)$roomsForPrice . ' rooms' : '' ?></span>
                        <?php endif; ?>
                        <span style="display:block;font-size:10.5px;color:#6f6f6f;" title="<?= h($prop['fee_note'] ?? 'Includes payment processing fee') ?>"><?= h($prop['fee_note'] ?? 'Incl. payment processing fee') ?></span>
                    </span>
                <?php endif; ?>
            </div>

            <!-- Location + Rating "4.2 ★ Excellent (66)" + description fallback -->
            <?php if ($locText !== ''): ?>
                <div class="gh-card-loc"><i class="fa-solid fa-location-dot" aria-hidden="true"></i><span><?= h($locText) ?></span></div>
            <?php endif; ?>
            <?php if ($ratingFmt !== null || $starLabel !== ''): ?>
                <div class="gh-rating-inline">
                    <?php if ($ratingFmt !== null): ?>
                        <span class="gh-rating-num"><?= h($ratingFmt) ?></span>
                        <span class="gh-rating-star">★</span>
                        <?php if ($ratingWord !== ''): ?><span class="gh-rating-word"><?= h($ratingWord) ?></span><?php endif; ?>
                        <?php if ($reviewsCount > 0): ?>
                            <span class="gh-rating-count">(<?= number_format($reviewsCount) ?>)</span>
                        <?php else: ?>
                            <span class="gh-rating-new">New</span>
                        <?php endif; ?>
                        <?php if ($starLabel !== ''): ?><span style="color:#5f6368; margin:0 4px;">·</span><span style="color:#5f6368; font-size:12.5px;"><?= h($starLabel) ?></span><?php endif; ?>
                    <?php elseif ($starLabel !== ''): ?>
                        <span style="color:#5f6368; font-size:12.5px;"><?= h($starLabel) ?></span>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="gh-rating-inline"><span class="gh-rating-new">New property</span></div>
            <?php endif; ?>
            <?php if (empty($amenities) && $descSnippet !== ''): ?>
                <div class="gh-card-desc"><?= h($descSnippet) ?></div>
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
            <!-- Mobile: price + same View prices/details button as desktop -->
            <div class="gh-card-viewmap">
                <?php if ($priceLabel !== ''): ?>
                <div style="display:flex;flex-direction:column;line-height:1.2;min-width:0;">
                    <span class="gh-hotel-price" style="display:inline-block !important;font-size:15px;font-weight:700;color:#202124;" data-tzs="<?= $price ?>"><?= h($priceLabel) ?><span class="gh-price-night" style="font-size:11px;font-weight:400;color:#5f6368;">/night</span></span>
                </div>
                <?php endif; ?>
                <a href="<?= $detailUrl ?>" class="<?= !$hasPrice ? 'gh-btn-details' : 'gh-btn-prices' ?>" onclick="event.stopPropagation()" style="<?= $priceLabel!=='' ? 'flex:0 0 auto;' : 'flex:1;justify-content:center;' ?>min-height:44px;font-size:14px;font-weight:600;touch-action:manipulation;">
                    <?= !$hasPrice ? 'View details' : 'View prices' ?>
                </a>
            </div>
        </div>
    </article>
    <?php endforeach; ?>
<?php else: ?>
    <?php
    // Baymard: empty state must preserve search (city/dates/guests) while clearing filters AND stale map position
    $clearQP = [];
    foreach (['city','destination','checkin','checkout','checkIn','checkOut','adults','children','rooms'] as $k) {
        if (isset($queryParams[$k]) && $queryParams[$k] !== '' && $queryParams[$k] !== null) $clearQP[$k] = $queryParams[$k];
    }
    if (empty($clearQP['destination']) && !empty($clearQP['city'])) $clearQP['destination'] = $clearQP['city'];
    if (empty($clearQP['city']) && !empty($clearQP['destination'])) $clearQP['city'] = $clearQP['destination'];
    $emptyDest = trim((string)($queryParams['destination'] ?? $queryParams['city'] ?? $destination ?? ''));
    $emptyTitle = $emptyDest !== '' ? 'No stays found for "' . $emptyDest . '"' : 'No stays found in Tanzania';
    $popularEmpty = ['Arusha','Zanzibar','Dar es Salaam','Kilimanjaro','Serengeti','Mwanza'];
    ?>
    <div class="gh-empty cds-empty" role="status" aria-live="polite">
        <div class="cds-empty-icon" aria-hidden="true"><i class="fa-solid fa-magnifying-glass"></i></div>
        <h4><?= h($emptyTitle) ?></h4>
        <p>Try widening your map area, changing dates, or <a href="#" onclick="var m=document.getElementById('fnsFiltersModal'); if(m&&window.bootstrap) bootstrap.Modal.getOrCreateInstance(m).show();return false;" style="color:#0f62fe;text-decoration:none;">clearing filters</a>. Or explore popular Tanzanian destinations below.</p>
        <div class="cds-empty-actions">
            <a href="<?= $this->Url->build('/?' . http_build_query($clearQP)) ?>" class="cds-btn-secondary"><i class="fa-solid fa-filter-circle-xmark"></i> Clear filters</a>
            <a href="<?= $this->Url->build('/') ?>" class="cds-btn-primary"><i class="fa-solid fa-rotate"></i> Reset search</a>
        </div>
        <div class="cds-popular" aria-label="Popular destinations">
            <?php foreach ($popularEmpty as $pop): ?>
                <a href="<?= $this->Url->build('/?city=' . rawurlencode($pop)) ?>"><?= h($pop) ?></a>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>
</div>

<script>
function ghBookmark(propId, btn) {
    var icon = btn ? btn.querySelector('i') : null;
    var wasActive = !!(icon && icon.classList.contains('fa-solid'));
    if (icon) {
        icon.className = wasActive ? 'fa-regular fa-bookmark' : 'fa-solid fa-bookmark';
        icon.style.color = wasActive ? '' : '#fff';
        if(navigator.vibrate) try{ navigator.vibrate(10);}catch(e){}
    }
    // Mature UI: toggleWishlist is async + local-first — roll back optimistic icon if it rejects (e.g. storage blocked)
    if (typeof toggleWishlist === 'function') {
        try {
            var r = toggleWishlist(propId, btn);
            if (r && typeof r.catch === 'function') r.catch(function(){
                if (icon) { icon.className = wasActive ? 'fa-solid fa-bookmark' : 'fa-regular fa-bookmark'; icon.style.color = wasActive ? '#fff' : ''; }
                if (typeof window.fnsToast === 'function') window.fnsToast('Couldn’t save — please try again.');
            });
        } catch(e) {
            if (icon) { icon.className = wasActive ? 'fa-solid fa-bookmark' : 'fa-regular fa-bookmark'; icon.style.color = wasActive ? '#fff' : ''; }
        }
    }
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

  // The container swap delegates to FastnetLoading.skeleton, which is the same
  // code path the filter modal and fastnet-state.js use. These three copies of
  // show/hide were the reason the skeleton could get stuck: whichever definition
  // loaded last won, and one of them had no matching hide.
  var show=function(){
    var shim=document.getElementById('gh-shimmer-container');
    var sec=document.getElementById('gh_results_section');
    FastnetLoading.skeleton('gh-cards-container', true, shim);
    if(shim) shim.setAttribute('aria-hidden','false');
    if(sec) sec.setAttribute('aria-busy','true');
    var chips=document.getElementById('fns_shimmer_chips'); if(chips) chips.classList.add('show');
    ctaBusy(true);
  };
  var hide=function(){
    var shim=document.getElementById('gh-shimmer-container');
    var sec=document.getElementById('gh_results_section');
    FastnetLoading.skeleton('gh-cards-container', false, shim);
    if(shim) shim.setAttribute('aria-hidden','true');
    if(sec) sec.setAttribute('aria-busy','false');
    var chips=document.getElementById('fns_shimmer_chips'); if(chips) chips.classList.remove('show');
    ctaBusy(false);
  };

  // Only define these if nothing has claimed them yet - fastnet-state.js also
  // registers a pair, and whichever script evaluates last used to win.
  window.ghHideShimmer = window.ghHideShimmer || hide;
  window.ghTriggerShimmer = window.ghTriggerShimmer || show;
  window.fnsTriggerShimmer = window.fnsTriggerShimmer || show;

  // No skeleton on initial load: server already rendered real cards. Shimmer only
  // during AJAX transitions (fastnet:shimmer-show from FastNetState.hydrate).
  // fail-safe: hide after 8s if network hangs
  var t=null;
  window.addEventListener('fastnet:shimmer-show', function(){ clearTimeout(t); show(); t=setTimeout(hide,4000); });
  window.addEventListener('fastnet:shimmer-hide', function(){ clearTimeout(t); hide(); });
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
