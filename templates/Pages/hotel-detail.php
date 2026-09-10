<?php
/**
 * fastnetstays.com - Trivago Hotel Detail Experience
 * Fully matched to Trivago reference screenshot (Breadcrumb, Tabs, Deal Card, Mosaic Gallery, Review Summary, Mapbox)
 */

$propTitle = $property['name'] ?? 'Property';
$propCity = $property['city'] ?? '';
$propArea = $property['area'] ?? $propCity;
$propStars = !empty($property['star_rating']) ? max(1, min(5, (int)$property['star_rating'])) : 0;
$propRating = !empty($property['rating']) ? number_format((float)$property['rating'], 1) : null;
$reviewsCount = !empty($property['reviews_count']) ? (int)$property['reviews_count'] : count($reviews ?? []);
$propPrice = (float)($property['starting_price'] ?? ($property['price_per_night'] ?? ($property['price'] ?? 0)));
$propPrice = $propPrice > 0 ? $propPrice : (!empty($rooms[0]['price']) ? (float)$rooms[0]['price'] : 0);
$firstRoomId = !empty($rooms[0]['id']) ? $rooms[0]['id'] : null;
$detailPropertyId = (int)($propertyId ?? 0);
$bookingUrl = $this->Url->build('/booking-page?' . http_build_query(array_merge($queryParams ?? [], [
    'property_id' => $detailPropertyId,
    'room_id' => $firstRoomId,
    'price' => $propPrice
])));
$propAddress = trim((string)($property['address'] ?? ''));
$propDesc = !empty($property['description']) ? $property['description'] : '';
$propType = $property['property_type'] ?? ($property['type'] ?? '');
$propertyAmenities = $property['amenities'] ?? [];
if (is_string($propertyAmenities)) {
    $decodedAmenities = json_decode($propertyAmenities, true);
    $propertyAmenities = is_array($decodedAmenities)
        ? $decodedAmenities
        : array_filter(array_map('trim', explode(',', $propertyAmenities)));
}
$propertyAmenities = is_array($propertyAmenities) ? array_values(array_filter($propertyAmenities)) : [];

// Gallery images
$galleryImages = [];
if (!empty($property['image_url'])) $galleryImages[] = $property['image_url'];
if (!empty($property['primary_image_url'])) $galleryImages[] = $property['primary_image_url'];
if (!empty($property['cover_image'])) $galleryImages[] = $property['cover_image'];

if (!empty($property['images']) && is_array($property['images'])) {
        foreach ($property['images'] as $imgItem) {
        $imgUrl = is_array($imgItem) ? ($imgItem['url'] ?? ($imgItem['image_url'] ?? '')) : $imgItem;
        if (!empty($imgUrl) && !in_array($imgUrl, $galleryImages)) {
            $galleryImages[] = $imgUrl;
        }
    }
}

if (!empty($rooms) && is_array($rooms)) {
    foreach ($rooms as $rItem) {
        if (!empty($rItem['photos'])) {
            $photos = is_string($rItem['photos']) ? json_decode($rItem['photos'], true) : $rItem['photos'];
            if (is_array($photos)) {
                foreach ($photos as $pUrl) {
                    $pUrl = is_array($pUrl) ? ($pUrl['url'] ?? ($pUrl['image_url'] ?? '')) : $pUrl;
                    if (!empty($pUrl) && !in_array($pUrl, $galleryImages)) {
                        $galleryImages[] = $pUrl;
                    }
                }
            }
        }
    }
}

$this->assign('title', h($propTitle) . ' | fastnetstays.com');
?>

<?= $this->Html->css('/assets/css/hotel-detail.css?v=' . filemtime(WWW_ROOT . 'assets/css/hotel-detail.css')); ?>
<?= $this->Html->css('/assets/css/hotel-detail-spacing.css') ?>

<!-- Top fastnetstays Shared Navbar with Integrated Search Bar -->
<?= $this->element('navbar'); ?>

<!-- Google Hotels Sticky Anchor Sub-Navigation -->
<div class="gh-detail-nav-wrapper">
    <div class="container" style="max-width: 1440px;">
        <ul class="gh-detail-nav-scroll">
            <li><a href="#overview-section" class="gh-detail-tab active" onclick="activateDetailTab(event, 'overview-section')">Overview</a></li>
            <li><a href="#rooms-section" class="gh-detail-tab" onclick="activateDetailTab(event, 'rooms-section')">Prices & Rooms</a></li>
            <li><a href="#about-section" class="gh-detail-tab" onclick="activateDetailTab(event, 'about-section')">About & Amenities</a></li>
            <li><a href="#location-section" class="gh-detail-tab" onclick="activateDetailTab(event, 'location-section')">Location & Neighborhood</a></li>
            <li><a href="#reviews-section" class="gh-detail-tab" onclick="activateDetailTab(event, 'reviews-section')">Reviews</a></li>
            <li><a href="#policies-section" class="gh-detail-tab" onclick="activateDetailTab(event, 'policies-section')">Policies</a></li>
        </ul>
    </div>
</div>

<!-- Main Hotel Detail Page Body -->
<main class="py-2 bg-white" style="min-height: 85vh;">
    <div class="container" style="max-width: 1440px;">
        
        <!-- Breadcrumb Row -->
        <nav class="trivago-detail-breadcrumb" aria-label="breadcrumb">
            <a href="<?= $this->Url->build('/'); ?>">Home</a>
            <span class="sep">&gt;</span>
            <a href="<?= $this->Url->build('/hotel-list-01?destination=' . urlencode($propCity)); ?>"><?= h($propCity) ?></a>
            <span class="sep">&gt;</span>
            <span class="text-slate-900 fw-semibold"><?= h($propTitle) ?></span>
        </nav>

        <!-- OVERVIEW SECTION: Header, Dealbox, Mosaic & Review Summary -->
        <section id="overview-section" class="mb-4">
            
            <!-- Title & Dealbox Row -->
            <div class="trivago-detail-header-row">
                <!-- Left: Title, Stars, Rating, Address -->
                <div class="trivago-detail-title-col">
                    <h1 class="trivago-detail-title">
                        <span><?= h($propTitle) ?></span>
                        <button type="button" class="border-0 bg-transparent text-slate-400 hover:text-danger p-0 ms-1 transition-all" data-property-id="<?= $detailPropertyId ?>" onclick="toggleWishlist(<?= $detailPropertyId ?>, this);" title="Save to Favourites">
                            <i class="fa-regular fa-heart fs-4"></i>
                        </button>
                    </h1>

                    <div class="trivago-detail-badge-row">
                        <!-- Stars -->
                        <span class="trivago-detail-stars">
                            <?= str_repeat('★', $propStars) ?>
                        </span>
                        <?php if ($propType !== ''): ?><span class="text-slate-700 fw-semibold"><?= h($propType) ?></span><?php endif; ?>
                        <span class="text-slate-300">·</span>

                        <!-- Score Badge & Word -->
                        <?php if ($propRating !== null): ?><span class="trivago-detail-score"><?= h($propRating) ?></span><span class="fw-bold text-slate-900"><?= (float)$propRating >= 9 ? 'Excellent' : ((float)$propRating >= 8 ? 'Very good' : 'Good') ?></span><?php endif; ?>
                        <?php if ($reviewsCount > 0): ?><span class="text-slate-500">(<?= number_format($reviewsCount) ?> ratings)</span><?php endif; ?>
                    </div>

                    <!-- Location Link -->
                    <?php if ($propAddress !== ''): ?><a href="javascript:void(0)" class="trivago-detail-address" onclick="openHotelMapModal()">
                        <i class="fa-solid fa-location-dot text-slate-500"></i>
                        <span><?= h($propAddress) ?></span>
                    </a><?php endif; ?>
                </div>

                <!-- Right: Trivago Dealbox (The Green Box) -->
                <?php if ($propPrice > 0 && $firstRoomId !== null): ?><div class="trivago-detail-dealcard">
                    <div class="trivago-dealcard-brand">
                        <span style="font-weight: 850; font-size: 13px;">
                            <span style="color: #d93025;">fast</span><span style="color: #1a73e8;">net</span><span style="color: #f29900;">stays</span>
                        </span>
                        <span class="fw-bold text-slate-800">Book &amp; Go</span>
                    </div>
                    <div class="trivago-dealcard-lowest">Our lowest price</div>

                    <div class="trivago-dealcard-action-row">
                        <div>
                            <div class="trivago-dealcard-price">TZS <?= number_format($propPrice) ?></div>
                            <div class="trivago-dealcard-sub">per night · taxes &amp; fees incl.</div>
                        </div>
                        <a href="#rooms-section" class="trivago-dealcard-btn">
                            <span>Book now</span>
                            <i class="fa-solid fa-chevron-right" style="font-size: 11px;"></i>
                        </a>
                    </div>
                </div><?php endif; ?>
            </div>

            <!-- 2-Column Hero: 5-Photo Mosaic (Left) + Review Summary (Right) -->
            <div class="trivago-hero-grid-row">
                
                <!-- Left: Mosaic Photo Grid -->
                <?php if (!empty($galleryImages)): ?><div class="trivago-mosaic-container" onclick="openPhotoLightbox(0)">
                    <!-- Big Main Featured Photo -->
                    <div class="trivago-mosaic-featured">
                        <img src="<?= h($galleryImages[0]) ?>" alt="<?= h($propTitle) ?>">
                    </div>

                    <?php if (count($galleryImages) > 1): ?><div class="trivago-mosaic-subgrid">
                        <?php foreach (array_slice($galleryImages, 1, 4) as $galleryIndex => $galleryImage): ?><div class="trivago-mosaic-subitem" onclick="event.stopPropagation(); openPhotoLightbox(<?= $galleryIndex + 1 ?>)">
                            <img src="<?= h($galleryImage) ?>" alt="<?= h($propTitle) ?> photo">
                        </div><?php endforeach; ?>
                    </div><?php endif; ?>
                    <button type="button" class="gh-mosaic-pill" onclick="event.stopPropagation(); openPhotoLightbox(0)" aria-label="View all photos">
                        <i class="fa-solid fa-camera" style="font-size: 12px;"></i> View all <?= count($galleryImages) ?> photos
                    </button>
                </div><?php else: ?><div class="trivago-mosaic-container trivago-mosaic-empty" role="status"><span>Photos unavailable</span></div><?php endif; ?>

                <!-- Right: Review Summary Card -->
                <div class="trivago-review-summary-card">
                    <div class="trivago-review-top-row">
                        <?php if ($propRating !== null): ?><div class="trivago-review-big-badge"><?= h($propRating) ?></div><?php endif; ?>
                        <div>
                            <div class="trivago-review-top-title"><?= $propRating !== null ? ((float)$propRating >= 9 ? 'Excellent' : ((float)$propRating >= 8 ? 'Very good' : 'Good')) : 'No rating yet' ?></div>
                            <div class="trivago-review-top-sub">
                                <?= $reviewsCount > 0 ? 'Based on ' . number_format($reviewsCount) . ' verified ratings' : 'Guest ratings are not available yet' ?>
                                <i class="fa-solid fa-circle-info text-slate-400 ms-1" title="Aggregated from real guest reviews"></i>
                            </div>
                        </div>
                    </div>

                    <div class="trivago-review-heading">
                        <span>Review summary</span>
                        <i class="fa-solid fa-wand-magic-sparkles text-primary" style="font-size: 13px;"></i>
                    </div>

                    <div class="trivago-review-body-text"><?= h(mb_strimwidth(strip_tags($propDesc), 0, 360, '...')) ?></div>

                    <a href="javascript:void(0)" class="trivago-review-show-more" onclick="openHotelReviewsModal()">Show more &gt;</a>
                </div>

            </div>

            <!-- Secondary Photo Strip & View Map Tile (Matching Trivago Screenshot) -->
            <?php if (count($galleryImages) > 5): ?><div class="trivago-secondary-gallery-row">
                <div class="trivago-subthumb" onclick="openPhotoLightbox(5)">
                    <img src="<?= h($galleryImages[5]) ?>" alt="<?= h($propTitle) ?> photo">
                </div>
                <div class="trivago-subthumb" onclick="openPhotoLightbox(6)">
                    <img src="<?= h($galleryImages[6] ?? $galleryImages[5]) ?>" alt="<?= h($propTitle) ?> photo">
                </div>
                <div class="trivago-subthumb" onclick="openPhotoLightbox(7)">
                    <img src="<?= h($galleryImages[7] ?? $galleryImages[5]) ?>" alt="<?= h($propTitle) ?> photo">
                </div>
                <div class="trivago-subthumb position-relative" onclick="openPhotoLightbox(8)">
                    <img src="<?= h($galleryImages[8] ?? $galleryImages[5]) ?>" alt="<?= h($propTitle) ?> photo">
                    <div class="trivago-subthumb-overlay">
                        <span>Show all photos</span>
                    </div>
                </div>
                <a href="javascript:void(0)" class="trivago-viewmap-tile" onclick="openHotelMapModal()">
                    <div class="trivago-viewmap-btn">
                        <i class="fa-solid fa-location-dot text-slate-900" style="font-size: 14px;"></i>
                        <span>View map</span>
                    </div>
                </a>
            </div><?php endif; ?>

            <!-- Key Amenities Highlights Bar (Matching Trivago Screenshot) -->
            <?php if (!empty($propertyAmenities)): ?><div class="trivago-key-amenities-card">
                <?php if (!empty($propertyAmenities)): ?><div class="trivago-amenities-row">
                    <?php foreach (array_slice($propertyAmenities, 0, 5) as $amenity): ?><div class="trivago-amenity-item"><i class="fa-solid fa-circle-check trivago-amenity-icon"></i><span><?= h(is_array($amenity) ? ($amenity['name'] ?? '') : $amenity) ?></span></div><?php endforeach; ?>
                </div><?php endif; ?>
                <?php if (!empty($propertyAmenities)): ?><div>
                    <a href="javascript:void(0)" class="trivago-amenity-show-more" onclick="openHotelAmenitiesModal()">Show more amenities</a>
                </div><?php endif; ?>
            </div><?php endif; ?>

        </section>

        <!-- PRICES & AVAILABLE ROOMS SECTION -->
        <section id="rooms-section" class="mb-5">
            <div class="d-flex align-items-center justify-content-between pb-2 border-bottom">
                <div>
                    <h2 class="fw-bold text-slate-900 mb-1 d-flex align-items-center gap-2" style="font-size: 22px;">
                        <i class="fa-solid fa-bed text-primary"></i>
                        <span>Select your room</span>
                    </h2>
                    <p class="text-slate-500 text-xs mb-0">Choose your preferred room type and rate options</p>
                </div>
            </div>
            <?= $this->element('Listing/Hotel/hotel-detail/rooms'); ?>
        </section>

        <!-- ABOUT & AMENITIES SECTION -->
        <section id="about-section" class="mb-5">
            <h2 class="fw-bold text-slate-900 mb-3" style="font-size: 20px; font-family: 'Google Sans', Roboto, sans-serif;">About & Amenities</h2>
            <div class="bg-white border rounded-3 p-4" style="border-color: #dadce0 !important; border-radius: 12px !important;">
                <?php if (!empty($propDesc)): ?><p class="text-slate-700" style="font-size: 14px; line-height: 1.6;"><?= h($propDesc) ?></p><?php endif; ?>
                <?= $this->element('Listing/Hotel/hotel-detail/amenities'); ?>
            </div>
        </section>

        <!-- LOCATION & NEIGHBORHOOD SECTION -->
        <section id="location-section" class="mb-5">
            <h2 class="fw-bold text-slate-900 mb-3" style="font-size: 20px; font-family: 'Google Sans', Roboto, sans-serif;">Location & Neighborhood</h2>
            <div class="bg-white border rounded-3 overflow-hidden mb-3" style="border-color: #dadce0 !important; border-radius: 12px !important;">
                <div id="hotel-detail-inline-map" style="height: 360px; width: 100%; background: #e5e7eb;"></div>
            </div>
            <?= $this->element('Listing/Hotel/hotel-detail/nearest'); ?>
        </section>

        <!-- REVIEWS SECTION -->
        <section id="reviews-section" class="mb-5">
            <h2 class="fw-bold text-slate-900 mb-3" style="font-size: 20px; font-family: 'Google Sans', Roboto, sans-serif;">Reviews</h2>
            <?= $this->element('Listing/Hotel/hotel-detail/guests-reviews'); ?>
        </section>

        <!-- POLICIES SECTION -->
        <section id="policies-section" class="mb-5">
            <h2 class="fw-bold text-slate-900 mb-3" style="font-size: 20px; font-family: 'Google Sans', Roboto, sans-serif;">Policies</h2>
            <div class="bg-white border rounded-3 p-4" style="border-color: #dadce0 !important; border-radius: 12px !important;">
                <div class="row g-4">
                    <div class="col-md-6">
                        <h6 class="fw-bold text-slate-900 mb-2"><i class="fa-solid fa-clock me-2 text-primary"></i>Check-in / Check-out</h6>
                        <p class="text-sm text-slate-600 mb-0">Check-in from 2:00 PM · Check-out until 11:00 AM<br>Early check-in and late check-out on request.</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold text-slate-900 mb-2"><i class="fa-solid fa-ban-smoking me-2 text-primary"></i>Cancellation</h6>
                        <p class="text-sm text-slate-600 mb-0">Free cancellation until 24h before check-in. Non-refundable rates available at discount.</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold text-slate-900 mb-2"><i class="fa-solid fa-paw me-2 text-primary"></i>Pets</h6>
                        <p class="text-sm text-slate-600 mb-0">Pet-friendly on request. Charges may apply.</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold text-slate-900 mb-2"><i class="fa-solid fa-credit-card me-2 text-primary"></i>Payment</h6>
                        <p class="text-sm text-slate-600 mb-0">Pay at property or online. Taxes and fees included where noted.</p>
                    </div>
                </div>
            </div>
        </section>

    </div>
</main>

<!-- Mobile Sticky Bottom Booking Pill (<992px) -->
<?php if ($propPrice > 0): ?><div class="gh-mobile-booking-bar" id="gh-mobile-booking-bar">
    <div style="display:flex;flex-direction:column;line-height:1.1;">
        <span style="font-size:11px;color:#5f6368;">From</span>
        <span style="font-size:16px;font-weight:700;color:#202124;">TZS <?= number_format($propPrice) ?> <span style="font-size:11px;font-weight:400;color:#5f6368;">/night</span></span>
    </div>
    <button type="button" onclick="document.getElementById('rooms-section')?.scrollIntoView({behavior:'smooth',block:'start'})" style="background:#1a73e8;color:#fff;border:none;border-radius:8px;padding:10px 18px;font-size:13px;font-weight:500;height:40px;display:inline-flex;align-items:center;gap:6px;font-family:'Google Sans',sans-serif;">Select Room</button>
</div><?php endif; ?>

<!-- Fullscreen Photo Gallery Lightbox Modal -->
<div class="trivago-lightbox-modal" id="photo_lightbox_modal" onclick="closePhotoLightbox()">
    <div class="trivago-lightbox-content" onclick="event.stopPropagation()">
        <button type="button" class="trivago-lightbox-close" onclick="closePhotoLightbox()" aria-label="Close photo viewer"><i class="fa-solid fa-xmark"></i></button>
        <?php if (!empty($galleryImages)): ?><img id="lightbox_main_img" src="<?= h($galleryImages[0]) ?>" class="img-fluid rounded-3 w-100 shadow-lg object-fit-contain" style="max-height: 75vh;" alt="Hotel Photo"><?php endif; ?>
        <div class="d-flex align-items-center justify-content-between mt-3 text-white">
            <span id="lightbox_counter" class="fw-bold">1 / <?= count($galleryImages) ?></span>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-light rounded-2 px-3" onclick="prevLightboxPhoto()"><i class="fa-solid fa-chevron-left me-1"></i>Previous</button>
                <button type="button" class="btn btn-sm btn-outline-light rounded-2 px-3" onclick="nextLightboxPhoto()">Next<i class="fa-solid fa-chevron-right ms-1"></i></button>
            </div>
        </div>
    </div>
</div>

<!-- Interactive Mapbox Modal (Opened via 'View map' & Location Link) -->
<div class="trivago-lightbox-modal" id="hotel_map_modal" onclick="closeHotelMapModal()">
    <div class="trivago-lightbox-content bg-white p-3 rounded-3 shadow-lg position-relative" onclick="event.stopPropagation()" style="max-width: 860px;">
        <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
            <div>
                <h5 class="fw-bold text-slate-900 mb-0 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-map-location-dot text-danger"></i>
                    <span><?= h($propTitle) ?></span>
                </h5>
                <span class="text-slate-500 text-xs"><i class="fa-solid fa-location-dot me-1 text-slate-400"></i><?= h($propAddress) ?></span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="https://maps.google.com/?q=<?= urlencode($propAddress) ?>" target="_blank" class="btn btn-sm btn-outline-primary fw-bold text-xs">
                    Google Maps <i class="fa-solid fa-arrow-up-right-from-square text-xs ms-1"></i>
                </a>
            <button type="button" class="btn btn-sm btn-light rounded-circle p-1 lh-1" onclick="closeHotelMapModal()" aria-label="Close map" style="width: 32px; height: 32px; font-size: 16px;"><i class="fa-solid fa-xmark"></i></button>
            </div>
        </div>
        <div id="web1-hotel-detail-map" class="rounded-2 overflow-hidden border" style="height: 480px; width: 100%;"></div>
    </div>
</div>

<!-- Full Amenities Modal (Opened via 'Show more amenities' & Subnav Tab) -->
<div class="trivago-lightbox-modal" id="hotel_amenities_modal" onclick="closeHotelAmenitiesModal()">
    <div class="trivago-lightbox-content bg-white p-4 rounded-3 shadow-lg position-relative overflow-y-auto" onclick="event.stopPropagation()" style="max-width: 800px; max-height: 85vh;">
        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
            <h5 class="fw-bold text-slate-900 mb-0 d-flex align-items-center gap-2">
                <i class="fa-solid fa-bell-concierge text-primary"></i>
                <span>All Property Amenities &amp; Services</span>
            </h5>
            <button type="button" class="btn btn-sm btn-light rounded-circle p-1 lh-1" onclick="closeHotelAmenitiesModal()" aria-label="Close amenities" style="width: 32px; height: 32px; font-size: 16px;"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <?= $this->element('Listing/Hotel/hotel-detail/amenities'); ?>
    </div>
</div>

<!-- Full Reviews Modal (Opened via 'Reviews' Tab & 'Show more >') -->
<div class="trivago-lightbox-modal" id="hotel_reviews_modal" onclick="closeHotelReviewsModal()">
    <div class="trivago-lightbox-content bg-white p-4 rounded-3 shadow-lg position-relative overflow-y-auto" onclick="event.stopPropagation()" style="max-width: 800px; max-height: 85vh;">
        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
            <h5 class="fw-bold text-slate-900 mb-0 d-flex align-items-center gap-2">
                <i class="fa-solid fa-star text-warning"></i>
                <span>Guest Reviews &amp; Rating Breakdown</span>
            </h5>
            <button type="button" class="btn btn-sm btn-light rounded-circle p-1 lh-1" onclick="closeHotelReviewsModal()" aria-label="Close reviews" style="width: 32px; height: 32px; font-size: 16px;"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <?= $this->element('Listing/Hotel/hotel-detail/guests-reviews'); ?>
    </div>
</div>

<!-- Include Footer -->
<?= $this->element('footer', ['skin' => 'skin-dark-footer']); ?>

<script>
let currentLightboxIdx = 0;
const galleryImagesList = <?= json_encode($galleryImages) ?>;
let detailMapInstance = null;

function openPhotoLightbox(idx) {
    currentLightboxIdx = idx;
    updateLightboxImg();
    const modal = document.getElementById('photo_lightbox_modal');
    if (modal) modal.classList.add('show');
}

function closePhotoLightbox() {
    const modal = document.getElementById('photo_lightbox_modal');
    if (modal) modal.classList.remove('show');
}

function updateLightboxImg() {
    if (currentLightboxIdx < 0) currentLightboxIdx = galleryImagesList.length - 1;
    if (currentLightboxIdx >= galleryImagesList.length) currentLightboxIdx = 0;
    const img = document.getElementById('lightbox_main_img');
    const counter = document.getElementById('lightbox_counter');
    if (img) img.src = galleryImagesList[currentLightboxIdx];
    if (counter) counter.innerText = (currentLightboxIdx + 1) + ' / ' + galleryImagesList.length;
}

function nextLightboxPhoto() {
    currentLightboxIdx++;
    updateLightboxImg();
}

function prevLightboxPhoto() {
    currentLightboxIdx--;
    updateLightboxImg();
}

function openHotelMapModal() {
    const modal = document.getElementById('hotel_map_modal');
    if (modal) modal.classList.add('show');
    if (!detailMapInstance) {
        initDetailMap();
    } else {
        setTimeout(() => { detailMapInstance.resize(); }, 200);
    }
}

function closeHotelMapModal() {
    const modal = document.getElementById('hotel_map_modal');
    if (modal) modal.classList.remove('show');
}

function openHotelAmenitiesModal() {
    const modal = document.getElementById('hotel_amenities_modal');
    if (modal) modal.classList.add('show');
}

function closeHotelAmenitiesModal() {
    const modal = document.getElementById('hotel_amenities_modal');
    if (modal) modal.classList.remove('show');
}

function openHotelReviewsModal() {
    const modal = document.getElementById('hotel_reviews_modal');
    if (modal) modal.classList.add('show');
}

function closeHotelReviewsModal() {
    const modal = document.getElementById('hotel_reviews_modal');
    if (modal) modal.classList.remove('show');
}

function activateDetailTab(e, sectionId) {
    if (e) e.preventDefault();
    document.querySelectorAll('.gh-detail-tab, .trivago-detail-tab').forEach(t => t.classList.remove('active'));
    if (e && e.currentTarget) e.currentTarget.classList.add('active');

    const target = document.getElementById(sectionId);
    if (target) {
        const offset = 120;
        const bodyRect = document.body.getBoundingClientRect().top;
        const elementRect = target.getBoundingClientRect().top;
        const elementPosition = elementRect - bodyRect;
        const offsetPosition = elementPosition - offset;

        window.scrollTo({
            top: offsetPosition,
            behavior: 'smooth'
        });
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const nav = document.querySelector('.trivago-detail-nav-wrapper');
    const header = document.querySelector('.agoda-top-header, .shared-header, header');
    const updateHeaderOffset = function () {
        if (nav && header) nav.style.setProperty('--detail-header-offset', header.offsetHeight + 'px');
    };
    updateHeaderOffset();
    window.addEventListener('resize', updateHeaderOffset, { passive: true });
    window.addEventListener('scroll', function () {
        if (header) header.classList.toggle('is-scrolled', window.scrollY > 8);
    }, { passive: true });

    const sections = ['overview-section', 'rooms-section','about-section','location-section','reviews-section','policies-section'].map(id => document.getElementById(id)).filter(Boolean);
    const tabs = Array.from(document.querySelectorAll('.gh-detail-tab'));
    if ('IntersectionObserver' in window && sections.length) {
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;
                tabs.forEach(tab => tab.classList.toggle('active', tab.getAttribute('href') === '#' + entry.target.id));
            });
        }, { rootMargin: '-140px 0px -55% 0px', threshold: 0 });
        sections.forEach(section => observer.observe(section));
    }
    // inline location map
    const inlineMapEl = document.getElementById('hotel-detail-inline-map');
    if (inlineMapEl && typeof mapboxgl !== 'undefined') {
        setTimeout(() => {
            try {
                const lat2 = <?= (!empty($property['latitude']) && is_numeric($property['latitude'])) ? (float)$property['latitude'] : -6.1659 ?>;
                const lng2 = <?= (!empty($property['longitude']) && is_numeric($property['longitude'])) ? (float)$property['longitude'] : 39.2026 ?>;
                mapboxgl.accessToken = window.MAPBOX_TOKEN || window.DEFAULT_MAPBOX_TOKEN || '';
                const imap = new mapboxgl.Map({ container: 'hotel-detail-inline-map', style: 'mapbox://styles/mapbox/streets-v12', center: [lng2, lat2], zoom: 14 });
                imap.addControl(new mapboxgl.NavigationControl(), 'top-right');
                new mapboxgl.Marker({ color: '#1a73e8' }).setLngLat([lng2, lat2]).addTo(imap);
            } catch(e) { console.warn('inline map', e); }
        }, 800);
    }
});

// Mapbox Initialization
function initDetailMap() {
    const lat = <?= (!empty($property['latitude']) && is_numeric($property['latitude'])) ? (float)$property['latitude'] : -6.1659 ?>;
    const lng = <?= (!empty($property['longitude']) && is_numeric($property['longitude'])) ? (float)$property['longitude'] : 39.2026 ?>;
    const propName = <?= json_encode($propTitle) ?>;
    const propAddr = <?= json_encode($propAddress) ?>;

    const container = document.getElementById('web1-hotel-detail-map');
    if (typeof mapboxgl === 'undefined' || !container) return;
    
    const token = window.MAPBOX_TOKEN || window.DEFAULT_MAPBOX_TOKEN || '';
    mapboxgl.accessToken = token;
    
    try {
        detailMapInstance = new mapboxgl.Map({
            container: 'web1-hotel-detail-map',
            style: 'mapbox://styles/mapbox/streets-v12',
            center: [lng, lat],
            zoom: 14.5
        });

        detailMapInstance.addControl(new mapboxgl.NavigationControl(), 'top-right');

        const popup = new mapboxgl.Popup({ offset: 25 }).setHTML(`
            <div style="font-family: Inter, sans-serif; padding: 4px;">
                <h6 style="font-weight: 700; margin: 0 0 4px; color: #0f172a; font-size: 14px;">${propName}</h6>
                <p style="font-size: 12px; margin: 0; color: #475569;">${propAddr}</p>
            </div>
        `);

        new mapboxgl.Marker({ color: '#008009' })
            .setLngLat([lng, lat])
            .setPopup(popup)
            .addTo(detailMapInstance);

        setTimeout(() => { detailMapInstance.resize(); }, 350);
    } catch(err) {
        console.error("Mapbox init error:", err);
    }
}
</script>
