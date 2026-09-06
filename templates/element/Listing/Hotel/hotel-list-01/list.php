<?php
/**
 * fastnetstays.com - Hotel Comparison Card List
 * Desktop view: 3-column card (image, info with description, dealbox with lowest price).
 * Mobile view: Matches Trivago mobile screenshot (top red ribbon, full-width photo,
 * stars/type/highlight, score/location, and green Book & Go deal box).
 */

if (!function_exists('getTrivagoPropImage')) {
    function getTrivagoPropImage($prop): string {
        if (!empty($prop['image_url'])) return $prop['image_url'];
        if (!empty($prop['primary_image_url'])) return $prop['primary_image_url'];
        if (!empty($prop['cover_image'])) return $prop['cover_image'];
        return '';
    }
}
?>
<?= $this->Html->css('/assets/css/hotel-card.css?v=' . filemtime(WWW_ROOT . 'assets/css/hotel-card.css')); ?>

<!-- Shimmer Skeleton Loading Container (Active during search / filtering) -->
<div id="hotel-shimmer-container" style="display: none;">
    <?php for ($s = 0; $s < 4; $s++): ?>
        <div class="trivago-card trivago-shimmer-card">
            <div class="trivago-top-ribbon opacity-75">
                <i class="fa-solid fa-tag"></i> 25% less than similar stays
            </div>
            <div class="trivago-card-grid">
                <!-- Left media skeleton -->
                <div class="trivago-card-media trivago-shimmer-media"></div>
                <!-- Middle info skeleton -->
                <div class="trivago-card-body d-flex flex-column justify-content-between p-3">
                    <div>
                        <div class="trivago-shimmer-line title mb-2"></div>
                        <div class="trivago-shimmer-line score mb-2"></div>
                        <div class="trivago-shimmer-line location mb-3"></div>
                        <div class="trivago-shimmer-line mb-1" style="width: 92%;"></div>
                        <div class="trivago-shimmer-line mb-0" style="width: 65%;"></div>
                    </div>
                </div>
                <!-- Right dealbox skeleton -->
                <div class="trivago-card-dealbox p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="trivago-shimmer-line mb-2" style="width: 50%;"></div>
                        <div class="trivago-shimmer-line price mb-2"></div>
                        <div class="trivago-shimmer-line mb-2" style="width: 80%;"></div>
                    </div>
                    <div class="trivago-shimmer-btn"></div>
                </div>
            </div>
        </div>
    <?php endfor; ?>
</div>

<!-- Live Properties Container -->
<div id="hotel-cards-container">
<?php if (!empty($properties)): ?>
    <?php foreach ($properties as $index => $prop): 
        $propId = $prop['id'] ?? 0;
        $title = $prop['name'] ?? '';
        $stars = !empty($prop['star_rating']) ? max(1, min(5, (int)$prop['star_rating'])) : 0;
        $propType = !empty($prop['property_type']) ? ucfirst($prop['property_type']) : '';

        $rating = !empty($prop['reviews_avg_rating']) ? number_format((float)$prop['reviews_avg_rating'], 1) : (!empty($prop['rating']) ? number_format((float)$prop['rating'], 1) : null);
        $ratingWord = $rating !== null ? (($rating >= 9.0) ? 'Excellent' : (($rating >= 8.0) ? 'Very good' : 'Good')) : '';
        $reviewsCount = (int)($prop['reviews_count'] ?? 0);
        $hasBreakfast = !empty($prop['breakfast_included']) || (!empty($prop['amenities']) && stripos((string)$prop['amenities'], 'breakfast') !== false);
        $hasFreeCancellation = !empty($prop['free_cancellation']) || (!empty($prop['cancellation_policy']) && stripos((string)$prop['cancellation_policy'], 'free') !== false);
        $discountPercent = isset($prop['discount_percent']) && is_numeric($prop['discount_percent']) ? (int)$prop['discount_percent'] : 0;
        $isVerifiedProvider = !empty($prop['verified']) || !empty($prop['provider_verified']);
        
        $price = (int)($prop['customer_price_per_night'] ?? ($prop['price_per_night'] ?? ($prop['price'] ?? 0)));
        $nights = max(1, (int)round((strtotime($queryParams['checkOut'] ?? '+5 days') - strtotime($queryParams['checkIn'] ?? '+1 day')) / 86400));
        $totalPrice = $price * $nights;
        $currency = ($price > 500) ? 'TSh ' : '$';
        $provider = $prop['provider'] ?? '';

        $area = $prop['area'] ?? ($prop['address'] ?? '');
        $city = $prop['city'] ?? '';
        $locationText = trim(implode(', ', array_filter([ucwords($area), ucwords($city)])));

        // Concise lodge highlight extracted from real description (for mobile view)
        $rawDesc = !empty($prop['description']) ? trim(strip_tags($prop['description'])) : '';
        if (!empty($rawDesc)) {
            $sentences = preg_split('/(?<=[.?!])\s+/', $rawDesc, 2);
            $shortHighlight = !empty($sentences[0]) ? mb_strimwidth($sentences[0], 0, 52, '...') : '';
        } else {
            $shortHighlight = '';
        }

        $img = getTrivagoPropImage($prop);
        $detailUrl = $this->Url->build('/hotel-detail/' . $propId . (!empty($queryParams) ? '?' . http_build_query($queryParams) : ''));
    ?>
        <div class="trivago-card" id="hotel-card-<?= $propId ?>" data-property-id="<?= $propId ?>" onmouseenter="if(window.highlightMapMarker) window.highlightMapMarker(<?= $propId ?>, true);" onmouseleave="if(window.highlightMapMarker) window.highlightMapMarker(<?= $propId ?>, false);">
            <!-- Top Crimson Ribbon Tag - Mobile ONLY (Matches Trivago Mobile Screenshot) -->
            <?php if ($discountPercent > 0): ?>
                <div class="trivago-top-ribbon">
                    <i class="fa-solid fa-tag"></i>
                    <span><?= h($discountPercent) ?>% less than the previous price</span>
                </div>
            <?php endif; ?>

            <div class="trivago-card-grid">
                
                <!-- 1. Left: Image Media -->
                <div class="trivago-card-media">
                    <a href="<?= $detailUrl ?>" class="d-block w-100 h-100">
                        <?php if ($img !== ''): ?><img src="<?= (str_starts_with($img, 'http')) ? h($img) : $this->Url->build('/' . h($img)) ?>" alt="<?= h($title) ?>"><?php else: ?><div class="hotel-list-image-empty" role="status">Photo unavailable</div><?php endif; ?>
                    </a>

                    <!-- Heart Wishlist Toggle -->
                    <button type="button" class="trivago-fav-btn" onclick="event.preventDefault(); toggleWishlist(<?= $propId ?>, this);" title="Save">
                        <i class="fa-regular fa-heart"></i>
                    </button>

                    <!-- Dot indicators -->
                    <div class="trivago-dot-pagination">
                        <span class="trivago-dot active"></span>
                        <span class="trivago-dot"></span>
                        <span class="trivago-dot"></span>
                        <span class="trivago-dot"></span>
                    </div>
                </div>

                <!-- 2. Middle: Hotel Information -->
                <div class="trivago-card-body">
                    <!-- Title -->
                    <div class="d-flex align-items-baseline gap-2 flex-wrap">
                        <a href="<?= $detailUrl ?>" class="trivago-hotel-title">
                            <?= h($title) ?>
                        </a>
                        <!-- Desktop Stars -->
                        <div class="trivago-stars d-none d-lg-block">
                            <?= str_repeat('★', $stars) ?>
                        </div>
                    </div>

                    <!-- DESKTOP Line 2: Score Badge & Reviews -->
                    <div class="d-none d-lg-flex align-items-center gap-1.5 mt-1 flex-nowrap">
                        <?php if ($rating !== null): ?><span class="trivago-score-badge"><?= h($rating) ?></span><span class="trivago-score-text"><?= h($ratingWord) ?></span><?php endif; ?>
                        <?php if ($reviewsCount > 0): ?><span class="trivago-score-reviews text-nowrap">(<?= h($reviewsCount) ?> ratings)</span><?php endif; ?>
                    </div>

                    <!-- DESKTOP Line 3: Location -->
                    <div class="trivago-location-text d-none d-lg-block">
                        <i class="fa-solid fa-location-dot text-slate-500" style="font-size: 11px;"></i>
                        <span><?= h($locationText) ?></span>
                        <button type="button" class="btn btn-link btn-sm p-0 ms-2 text-primary fw-semibold text-decoration-none" onclick="event.preventDefault(); if(window.focusMapMarker) window.focusMapMarker(<?= $propId ?>);" title="Show on map" style="font-size: 11.5px;">
                            <i class="fa-solid fa-map-location-dot me-0.5"></i> Show on map
                        </button>
                    </div>

                    <!-- DESKTOP Line 4: Lodge Description -->
                    <div class="trivago-lodge-desc d-none d-lg-block" title="<?= h($rawDesc) ?>">
                        <i class="fa-solid fa-feather-pointed text-primary me-1" style="font-size: 11px;"></i>
                        <?= h(mb_strimwidth($rawDesc, 0, 130, '...')) ?>
                    </div>

                    <!-- MOBILE Line 2: Stars + Type + Highlight (Matches Trivago Mobile Screenshot) -->
                    <div class="d-flex d-lg-none align-items-center gap-1.5 flex-wrap my-1" style="font-size: 13px;">
                        <?php if ($stars > 0): ?><span class="trivago-stars-gold"><?= str_repeat('★', $stars) ?></span><?php endif; ?>
                        <?php if ($propType !== ''): ?><span class="fw-semibold text-slate-700"><?= h($propType) ?></span><?php endif; ?>
                        <?php if ($shortHighlight !== ''): ?><span class="text-slate-300">|</span><span class="text-slate-700 d-inline-flex align-items-center gap-1"><span><?= h($shortHighlight) ?></span></span><?php endif; ?>
                    </div>

                    <!-- MOBILE Line 3: Score + Location (Matches Trivago Mobile Screenshot) -->
                    <div class="d-flex d-lg-none align-items-center gap-1.5 flex-wrap my-1" style="font-size: 12.5px;">
                        <?php if ($rating !== null): ?><span class="trivago-score-badge"><?= h($rating) ?></span><span class="trivago-score-text"><?= h($ratingWord) ?></span><?php endif; ?>
                        <?php if ($reviewsCount > 0): ?><span class="trivago-score-reviews">(<?= h($reviewsCount) ?> ratings)</span><?php endif; ?>
                        <?php if ($locationText !== ''): ?><span class="text-slate-300">|</span><span class="text-slate-600 d-inline-flex align-items-center gap-1"><i class="fa-solid fa-location-dot text-slate-400" style="font-size: 11px;"></i><span><?= h($locationText) ?></span></span><button type="button" class="btn btn-link btn-sm p-0 ms-1 text-primary fw-semibold text-decoration-none" onclick="event.preventDefault(); if(window.focusMapMarker) window.focusMapMarker(<?= $propId ?>);" style="font-size: 11px;"><i class="fa-solid fa-map-location-dot"></i> Map</button><?php endif; ?>
                    </div>
                </div>

                <!-- 3. Right: Price Comparison Deal Box -->
                <div class="trivago-card-dealbox">
                    <div>
                        <!-- Desktop Header: Member / Discount Badge -->
                        <div class="d-none d-lg-block">
                            <?php if ($index === 0): ?>
                                <div class="trivago-member-badge">FastNet Member Rate</div>
                            <?php else: ?>
                                <div class="trivago-discount-badge">Direct Host Pricing</div>
                            <?php endif; ?>
                            <div class="trivago-provider-name"><?= h($provider) ?></div>
                        </div>

                        <!-- Mobile Header: Book & Go + Breakfast Included (Matches Screenshot) -->
                        <div class="d-flex d-lg-none align-items-center justify-content-between mb-1 pb-1 border-bottom">
                            <div class="trivago-provider-name fw-bold" style="color: #0f172a; font-size: 12px;">
                                <i class="fa-solid fa-bolt text-warning me-1"></i>fastnetstays Book & Go
                            </div>
                            <?php if ($hasBreakfast): ?>
                                <div class="text-success fw-bold text-xs d-inline-flex align-items-center gap-1">
                                    <i class="fa-solid fa-check"></i> Breakfast included
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="trivago-lowest-pill">Our lowest price</div>

                        <div class="trivago-price-row">
                            <div class="trivago-price-amount">
                                <?= $currency ?><?= number_format($price) ?>
                                <i class="fa-solid fa-lock text-slate-800" style="font-size: 12px; margin-left: 2px;"></i>
                            </div>
                            <a href="<?= $detailUrl ?>" class="trivago-deal-btn">
                                <span>View Deal</span>
                                <i class="fa-solid fa-chevron-right" style="font-size: 10px;"></i>
                            </a>
                        </div>

                        <div class="trivago-nights-subtext"><?= $nights ?> <?= $nights === 1 ? 'night' : 'nights' ?> for <?= $currency ?><?= number_format($totalPrice) ?></div>
                        <div class="trivago-fees-note">Includes all taxes & fees</div>
                    </div>

                    <!-- Instant Confirmation & Free Cancellation -->
                    <div class="trivago-comparison-accordion">
                        <div class="d-flex align-items-center justify-content-between text-xs text-slate-600">
                            <?php if ($isVerifiedProvider): ?><span class="d-inline-flex align-items-center gap-1">
                                <i class="fa-solid fa-shield-check text-success"></i> Verified provider
                            </span><?php endif; ?>
                            <?php if ($hasFreeCancellation): ?><span class="text-success fw-semibold">Free Cancellation</span><?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="card p-5 text-center border-0 shadow-sm rounded-3">
        <i class="fa-solid fa-hotel text-slate-300 display-4 mb-3"></i>
        <h4 class="fw-bold text-slate-800">No stays found matching your filters</h4>
        <p class="text-slate-500 mb-3">Try adjusting your dates or clearing your amenity filters to view available lodges.</p>
        <div>
            <a href="<?= $this->Url->build('/hotel-list-01'); ?>" class="btn btn-primary fw-bold px-4 py-2 rounded-2">
                Reset All Filters
            </a>
        </div>
    </div>
<?php endif; ?>
</div>
