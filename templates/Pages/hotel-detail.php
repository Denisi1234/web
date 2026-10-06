<?php
/**
 * fastnetstays.com — Agoda Hotel Detail — Exact match to screenshot reference
 */
$propTitle = \App\Utility\TextFormatter::formatTitle((string)($property['name'] ?? 'Hotel'));
$propCity = \App\Utility\TextFormatter::formatTitle((string)($property['city'] ?? 'Dar es Salaam'));
$propArea = \App\Utility\TextFormatter::formatTitle((string)($property['area'] ?? $propCity));
$actualReviews = !empty($reviews) && is_array($reviews) ? $reviews : [];
$reviewsCount = count($actualReviews) > 0 ? count($actualReviews) : (int)($property['reviews_count'] ?? $property['review_count'] ?? 0);
// Average only reviews that actually carry a rating. This substituted 5 for
// any missing rating, which inflated the displayed score.
$ratedReviews = array_values(array_filter(
    $actualReviews,
    fn($r) => isset($r['rating']) && is_numeric($r['rating']) && (float)$r['rating'] > 0
));
$propRating = !empty($property['rating'])
    ? (float)$property['rating']
    : (count($ratedReviews) > 0
        ? array_sum(array_map(fn($r) => (float)$r['rating'], $ratedReviews)) / count($ratedReviews)
        : 0.0);
$score10 = ($propRating <= 5.0) ? round($propRating * 2, 1) : round($propRating, 1);
$score10Fmt = number_format($score10, 1);
$ratingLabel = $score10 >= 9.0 ? 'Exceptional' : ($score10 >= 8.0 ? 'Excellent' : ($score10 >= 7.0 ? 'Very Good' : 'Good'));
$propBase = (float)($property['starting_price'] ?? ($property['price_per_night'] ?? ($property['price'] ?? 0)));
// "From" price = lowest AVAILABLE room price (customer rate). The property base rate is NOT
// bookable by itself, so it is only a fallback when no room carries a price.
$propPrice = 0.0;
if (!empty($rooms) && is_array($rooms)) {
    $pricePool = [];
    foreach ($rooms as $pr) {
        if (!is_array($pr)) continue;
        if (!array_key_exists('is_available', $pr) || (bool)$pr['is_available']) $pricePool[] = $pr;
    }
    if ($pricePool === []) $pricePool = array_values(array_filter($rooms, 'is_array'));
    foreach ($pricePool as $pr) {
        $rp = (float)($pr['customer_price'] ?? ($pr['price'] ?? 0));
        if ($rp > 0 && ($propPrice <= 0 || $rp < $propPrice)) $propPrice = $rp;
    }
}
if ($propPrice <= 0) $propPrice = $propBase;
// No invented fallback price. A property whose rooms carry no rate now shows
// "Price on request" instead of a fabricated 275.
$propPrice = $propPrice > 0 ? $propPrice : 0.0;
$firstRoomId = !empty($rooms[0]['id']) ? $rooms[0]['id'] : null;
$detailPropertyId = (int)($propertyId ?? 0);
$rawAddr = trim((string)($property['address'] ?? ''));
$propAddress = $rawAddr !== '' ? \App\Utility\TextFormatter::formatTitle($rawAddr) : \App\Utility\TextFormatter::formatLocation($propArea, $propCity);
if ($propAddress && $propCity && !str_contains(strtolower($propAddress), strtolower($propCity))) {
    $propAddress .= ', ' . $propCity;
}
$propDesc = $property['description'] ?? '';
$propertyAmenities = $property['amenities'] ?? [];
if (is_string($propertyAmenities)) {
    $d = json_decode($propertyAmenities, true);
    $propertyAmenities = is_array($d) ? $d : array_filter(array_map('trim', explode(',', $propertyAmenities)));
}
$hdNormUrl = function(string $url): string {
    $url = trim($url);
    if ($url === '') return '';
    if (str_starts_with($url, '/storage/') || str_contains($url, '127.0.0.1:8000/storage') || str_contains($url, 'localhost/storage')) {
        $apiBase = (string)\Cake\Core\Configure::read('App.backendApiUrl', \Cake\Core\env('BACKEND_API_URL', 'http://127.0.0.1:8000/api'));
        $backendHost = rtrim(preg_replace('#/api/?$#', '', $apiBase), '/');
        $storagePath = substr($url, strpos($url, '/storage/'));
        return $backendHost . $storagePath;
    }
    return $url;
};
$galleryImages = [];
foreach ([$property['image_url'] ?? null, $property['primary_image_url'] ?? null, $property['cover_image'] ?? null] as $singleImg) {
    if (empty($singleImg)) continue;
    $u = $hdNormUrl((string)$singleImg);
    if ($u !== '' && !in_array($u, $galleryImages)) $galleryImages[] = $u;
}
if (!empty($property['images']) && is_array($property['images'])) {
    foreach ($property['images'] as $img) { $u = is_array($img) ? ($img['url'] ?? $img['image_url'] ?? '') : $img; $u = $hdNormUrl((string)$u); if($u && !in_array($u,$galleryImages)) $galleryImages[]=$u; }
}
if (!empty($rooms) && is_array($rooms)) {
    foreach ($rooms as $r) {
        if (!is_array($r)) continue;
        $ph = $r['photos'] ?? ($r['images'] ?? []); if(is_string($ph)) $ph=json_decode($ph,true);
        if(is_array($ph)) foreach($ph as $p){ $u=is_array($p)?($p['url']??$p['image_url']??''):$p; $u = $hdNormUrl((string)$u); if($u && !in_array($u,$galleryImages)) $galleryImages[]=$u; }
    }
}
// A property with zero photos still gets the neutral house placeholder so
// the gallery section keeps its shape (never a stranger's photo).
if (empty($galleryImages)) $galleryImages = [$this->Url->build('/assets/img/hotel/hotel-1.jpg')];
$qp = $queryParams ?? [];
// Real dates only — hardcoded demo defaults rotted within weeks.
$checkInVal = $qp['checkin'] ?? $qp['checkIn'] ?? date('Y-m-d', strtotime('+7 days'));
$checkOutVal = $qp['checkout'] ?? $qp['checkOut'] ?? date('Y-m-d', strtotime('+13 days'));

$this->assign('title', $propTitle);
$this->assign('description', mb_strimwidth(strip_tags($propDesc),0,155,'...') . ' — Book on FastNet Stays');
?>
<?= $this->element('Hotel/hotel-detail-style') ?>
<?= $this->element('navbar') ?>

<!-- Detail loading feedback: thin top bar only (floating pill removed) -->
<?= $this->element('Home/home-loader') ?>

<?= $this->element('breadcrumb-schema', ['label' => $propTitle]) ?>
<!-- Gallery — full mosaic when 5+ photos, clean hero + thumb strip when sparse (never empty grey cells) -->
<?php $galleryThumbs = array_slice($galleryImages, 1); ?>
<?php if (count($galleryImages) >= 6): ?>
<div class="agoda-gallery" id="agoda_gallery">
  <div class="agoda-gallery-hero" onclick="openPhotoLightbox(0)" style="cursor:pointer">
    <img src="<?= h($galleryImages[0]) ?>" alt="<?= h($propTitle) ?>" loading="lazy" width="600" height="400" onerror="this.onerror=null;this.src='<?= $this->Url->build('/assets/img/hotel/hotel-1.jpg') ?>'">
    <button class="agoda-see-all" onclick="event.stopPropagation();openPhotoLightbox(0)"><i class="fa-solid fa-images"></i> See all photos</button>
  </div>
  <?php foreach(array_slice($galleryThumbs, 0, 5) as $i => $g): ?>
  <div class="agoda-gallery-item" onclick="openPhotoLightbox(<?= $i+1 ?>)" style="cursor:pointer">
    <img src="<?= h($g) ?>" alt="<?= h($propTitle) ?> photo <?= $i+2 ?>" loading="lazy" width="300" height="200" onerror="this.onerror=null;this.src='<?= $this->Url->build('/assets/img/hotel/hotel-1.jpg') ?>'">
  </div>
  <?php endforeach; ?>
  <!-- Real static map tile (Mapbox, token resolved in browser). Never a drawn
       fake: without a token the cell keeps its neutral placeholder. -->
  <div class="agoda-gallery-item agoda-gallery-map" onclick="openHotelMapModal()" style="cursor:pointer;background:#e8ecef;position:relative;overflow:hidden;display:flex;align-items:center;justify-content:center">
    <img id="gallery_map_img" src="" alt="Map" loading="lazy" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;display:none" data-lat="<?= h((string)($property['latitude'] ?? '')) ?>" data-lng="<?= h((string)($property['longitude'] ?? '')) ?>">
    <div style="position:absolute;left:50%;top:42%;transform:translate(-50%,-50%);background:#e53935;color:#fff;border-radius:50% 50% 50% 0;transform:translate(-50%,-50%) rotate(-45deg);width:28px;height:28px;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(0,0,0,.25);z-index:1"><i class="fa-solid fa-location-dot" style="transform:rotate(45deg);font-size:13px"></i></div>
    <span style="position:relative;z-index:1;background:#fff;border-radius:8px;padding:6px 12px;font-size:12px;font-weight:700;color:#202124;box-shadow:0 2px 6px rgba(0,0,0,.12);margin-top:18px">SEE MAP</span>
  </div>
</div>
<?php elseif (!empty($galleryImages)): ?>
<div class="agoda-gallery-sparse" id="agoda_gallery">
  <div class="agoda-gallery-hero" onclick="openPhotoLightbox(0)" style="cursor:pointer">
    <img src="<?= h($galleryImages[0]) ?>" alt="<?= h($propTitle) ?>" loading="lazy" width="900" height="420" onerror="this.onerror=null;this.src='<?= $this->Url->build('/assets/img/hotel/hotel-1.jpg') ?>'">
    <?php if (count($galleryImages) > 1): ?>
    <button class="agoda-see-all" onclick="event.stopPropagation();openPhotoLightbox(0)"><i class="fa-solid fa-images"></i> See all photos</button>
    <?php endif; ?>
  </div>
  <?php if (count($galleryThumbs) > 0): ?>
  <div class="agoda-gallery-strip">
    <?php foreach($galleryThumbs as $i => $g): ?>
    <div class="agoda-gallery-thumb" onclick="openPhotoLightbox(<?= $i+1 ?>)" style="cursor:pointer">
      <img src="<?= h($g) ?>" alt="<?= h($propTitle) ?> photo <?= $i+2 ?>" loading="lazy" width="200" height="130" onerror="this.onerror=null;this.src='<?= $this->Url->build('/assets/img/hotel/hotel-1.jpg') ?>'">
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>

<!-- Tabs — Overview active — matches screenshot -->
<div class="agoda-tabs-wrap" id="agoda_tabs">
  <button class="agoda-tab active" data-tab="overview">Overview</button>
  <button class="agoda-tab" data-tab="rooms">Rooms</button>
  <button class="agoda-tab" data-tab="trip">Trip recommendations</button>

  <div class="agoda-deal-cta">
    <span class="agoda-deal-price"><?php if ($propPrice > 0): ?>from <b>TSh <?= number_format($propPrice) ?></b><?php else: ?><b>Price on request</b><?php endif; ?></span>
    <button class="agoda-view-deal" onclick="document.getElementById('rooms-section')?.scrollIntoView({behavior:'smooth'})">VIEW THIS DEAL</button>
  </div>
</div>

<!-- Main grid — LEFT + RIGHT as screenshot -->
<div class="agoda-detail-grid">
  <!-- LEFT COLUMN -->
  <div style="display:flex;flex-direction:column;gap:14px">
    <!-- Title card -->
    <div class="agoda-card" id="overview-section" style="position:relative;padding-top:14px;">
      <?php /* Best-seller badge only when the record says so (never unconditional). */ ?>
      <?php if (!empty($property['is_best_seller'])): ?>
      <div style="margin-bottom:6px"><span class="agoda-badge-bestseller">Best seller</span></div>
      <?php endif; ?>
      <button style="position:absolute;top:12px;right:12px;background:none;border:1px solid #e8eaed;border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;cursor:pointer" onclick="toggleWishlist(<?= $detailPropertyId ?>,this)" aria-label="Save"><i class="fa-regular fa-heart" style="color:#5f6368;font-size:15px"></i></button>
      <div class="agoda-title" style="padding-right:48px;"><?= h($propTitle) ?> <span class="agoda-stars"><?= str_repeat('★', $propStars) ?><?= $propStars<5 ? str_repeat('☆',5-$propStars) : '' ?></span></div>
      <div class="agoda-address"><?= h($propAddress) ?></div>
      <div style="display:flex;flex-wrap:wrap;gap:6px 16px;margin-top:6px;font-size:13px;align-items:center">
        <?php if ($reviewsCount > 0): ?>
        <span style="display:inline-flex;align-items:center;gap:6px;font-weight:700;color:#1a1d25"><i class="fa-solid fa-star" style="color:#f59e0b;font-size:13px"></i> <?= h($score10Fmt) ?> <?= h($ratingLabel) ?> <span style="font-weight:500;color:#5f6368">· <?= number_format($reviewsCount) ?> verified review<?= $reviewsCount !== 1 ? 's' : '' ?></span></span>
        <?php else: ?>
        <span style="display:inline-flex;align-items:center;gap:6px;font-weight:700;color:#1a1d25"><span style="background:#f1f5f9;color:#475569;font-size:11px;font-weight:700;border-radius:6px;padding:3px 8px">New</span> <span style="font-weight:500;color:#5f6368">No reviews yet</span></span>
        <?php endif; ?>
      </div>
      <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap">
        <button type="button" onclick="openHotelMapModal()" style="background:#fff;border:1px solid #dadce0;border-radius:9999px;padding:7px 14px;font-size:13px;font-weight:600;color:#0f62fe;display:inline-flex;align-items:center;gap:6px;cursor:pointer"><i class="fa-solid fa-location-dot"></i> SEE MAP</button>
        <button type="button" class="agoda-view-deal agoda-title-rooms-cta" onclick="document.getElementById('rooms-section')?.scrollIntoView({behavior:'smooth'})" style="padding:7px 16px;font-size:13px">View Rooms</button>
        <button type="button" data-bs-toggle="modal" data-bs-target="#propertyDetailsModal" style="background:#fff;border:1px solid #dadce0;border-radius:9999px;padding:7px 14px;font-size:13px;font-weight:600;color:#1a1d25;display:inline-flex;align-items:center;gap:6px;cursor:pointer"><i class="fa-solid fa-circle-info"></i> Details</button>
      </div>
    </div>

    <!-- Select your room header -->
    <div class="agoda-rooms-header" id="rooms-section">
      <h2>Select your room</h2>
      <?php /* Removed: an unconditional "We price match!" promise on every
               property, which is a commercial claim we never actually honour. */ ?>
      <?php if (!empty($property['price_match'])): ?>
      <span class="agoda-price-match"><i class="fa-solid fa-badge-check" style="font-size:16px"></i> We price match!</span>
      <?php endif; ?>
    </div>
    <div class="agoda-card" style="padding:14px">
      <?= $this->element('Listing/Hotel/hotel-detail/rooms') ?>
    </div>

  </div>

  <!-- RIGHT COLUMN — rating + map + landmarks as screenshot -->
  <div style="display:flex;flex-direction:column;gap:14px">
    <!-- Rating card -->
    <div class="agoda-rating-card">
      <?php if ($reviewsCount > 0): ?>
      <div class="agoda-rating-big">
        <div class="agoda-rating-score"><?= h($score10Fmt) ?></div>
        <div class="agoda-rating-label"><b><?= h($ratingLabel) ?></b><span><?= number_format($reviewsCount) ?> verified reviews</span></div>
      </div>
      <!-- Per-category score bars removed.

           They were computed arithmetically from the single overall score
           (Cleanliness = score+0.1, Service = score, Facilities = score-0.1,
           Value = score-0.3) and floored at 7.0, so nothing could ever score
           badly. The reviews table stores one rating and no category scores,
           so these bars had no data behind them. They should return only once
           categories are genuinely collected. -->
      <div style="position:relative;margin-top:14px">
        <div class="agoda-review-scroll" id="agoda_review_scroll">
          <?php foreach (array_slice($actualReviews, 0, 4) as $snip):
            // No invented author or comment text. Previously an unnamed
            // reviewer became "Verified Guest" and an empty comment became
            // "Wonderful experience.", both indistinguishable from real content.
            $sAuthor = trim((string)($snip['user_name'] ?? $snip['guest_name'] ?? ''));
            $sComment = trim((string)($snip['comment'] ?? ''));
            if ($sAuthor === '' && $sComment === '') {
                continue; // nothing real to show for this review
            }
            if (mb_strlen($sComment) > 110) {
                $sComment = mb_substr($sComment, 0, 107) . '...';
            }
          ?>
          <div class="agoda-review-snippet">
            <?php if ($sComment !== ''): ?>
              &ldquo;<?= h($sComment) ?>&rdquo;
            <?php endif; ?>
            <?php /* No hardcoded flag and no unconditional "Verified Guest"
                     badge. Verification status is a property of the record, so
                     it is only shown when the backend actually marks the
                     reviewer verified. */ ?>
            <div class="agoda-review-author">
              <?php if ($sAuthor !== ''): ?><b><?= h($sAuthor) ?></b><?php endif; ?>
              <?php if (!empty($snip['is_verified'])): ?>
                <span style="color:#9aa0a6">|</span> Verified stay
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <button class="agoda-review-arrow" onclick="document.getElementById('agoda_review_scroll').scrollBy({left:280,behavior:'smooth'})"><i class="fa-solid fa-chevron-right" style="font-size:12px;color:#202124"></i></button>
      </div>
      <?php else: ?>
      <div class="agoda-rating-big">
        <div class="agoda-rating-score" style="font-size:14px;background:#f1f5f9;color:#475569;width:auto;padding:4px 10px;border-radius:6px;font-weight:700">New</div>
        <div class="agoda-rating-label"><b>No reviews yet</b><span>0 verified reviews</span></div>
      </div>
      <div style="font-size:12.5px;color:#5f6368;margin-top:10px;line-height:1.4">
        Be the first verified guest to stay here and leave a review.
      </div>
      <?php endif; ?>
    </div>

  </div>
</div>
<?= $this->element('Hotel/hotel-detail-modals', ['galleryImages' => $galleryImages ?? [], 'propTitle' => $propTitle ?? '', 'propArea' => $propArea ?? '', 'propCity' => $propCity ?? '', 'propPrice' => $propPrice ?? 0, 'ratingLabel' => $ratingLabel ?? '', 'reviewsCount' => $reviewsCount ?? 0, 'score10' => $score10 ?? 0, 'detailPropertyId' => $detailPropertyId ?? 0, 'propDesc' => $propDesc ?? '', 'score10Fmt' => $score10Fmt ?? '']) ?>
<div class="d-none d-lg-block">
<?= $this->element('footer', ['skin' => 'skin-light-footer']) ?>
</div>
