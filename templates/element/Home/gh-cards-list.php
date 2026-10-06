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
                        <img src="<?= str_starts_with($u,'http') ? h($u) : $this->Url->build('/'.h($u)) ?>" alt="<?= h($title) ?> photo <?= $ci+1 ?>" loading="<?= $ci===0?'eager':'lazy' ?>"<?= ($idx===0 && $ci===0) ? ' fetchpriority="high" decoding="async"' : ' decoding="async"' ?> draggable="false" onerror="this.onerror=null;this.src='<?= $this->Url->build('/assets/img/hotel/hotel-1.jpg') ?>'">
                    <?php endforeach; ?>
                </div>
                <button type="button" class="gh-card-nav gh-card-prev" onclick="event.stopPropagation();ghSlide(<?= $propId ?>,-1)" aria-label="Previous image">‹</button>
                <button type="button" class="gh-card-nav gh-card-next" onclick="event.stopPropagation();ghSlide(<?= $propId ?>,1)" aria-label="Next image">›</button>
            <?php else: ?>
                <?php $img = $cardImgs[0] ?? ghPropImage2($prop); ?>
                <?php if ($img !== ''): ?>
                    <img src="<?= str_starts_with($img,'http') ? h($img) : $this->Url->build('/'.h($img)) ?>" alt="<?= h($title) ?>" loading="<?= $idx===0?'eager':'lazy' ?>"<?= $idx===0?' fetchpriority="high" decoding="async"':' decoding="async"' ?> onerror="this.onerror=null;this.src='<?= $this->Url->build('/assets/img/hotel/hotel-1.jpg') ?>'">
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
