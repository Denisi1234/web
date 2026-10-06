<?php
/**
 * fastnetstays.com — Agoda Booking Checkout — Customer Information (Step 1)
 * Pixel-perfect to agoda.com/book reference screenshot
 */
$propId = (int)($queryParams['property_id'] ?? ($property['id'] ?? 0));
$roomId = (int)($queryParams['room_id'] ?? ($room['id'] ?? 51));
$propTitle = \App\Utility\TextFormatter::formatTitle((string)($property['name'] ?? 'Hotel'));
$propCity = \App\Utility\TextFormatter::formatTitle((string)($property['city'] ?? 'Dar es Salaam'));
$propArea = \App\Utility\TextFormatter::formatTitle((string)($property['area'] ?? $propCity));
$propCountry = \App\Utility\TextFormatter::formatTitle((string)($property['country'] ?? 'Tanzania'));
$propStars = !empty($property['star_rating']) ? max(1, min(5, (int)$property['star_rating'])) : 0;
$rawAddr = trim((string)($property['address'] ?? ''));
$propAddressShort = $rawAddr !== '' ? \App\Utility\TextFormatter::formatTitle($rawAddr) : \App\Utility\TextFormatter::formatLocation($propArea, $propCity);
$propRatingRaw = $property['reviews_avg_rating'] ?? ($property['rating'] ?? null);
$propReviewsRaw = $property['reviews_count'] ?? ($property['review_count'] ?? null);
$hasRating = $propRatingRaw !== null && $propReviewsRaw !== null && (int)$propReviewsRaw > 0;
$propRating = $hasRating ? (float)$propRatingRaw : 0.0;
$propReviews = $hasRating ? (int)$propReviewsRaw : 0;

$roomTitle = \App\Utility\TextFormatter::formatTitle((string)($room['name'] ?? ($room['room_number'] ?? 'Standard Room')));
$roomSizeRaw = $room['size'] ?? ($room['area'] ?? ($room['room_size'] ?? ''));
$roomSize = $roomSizeRaw !== '' ? (is_numeric($roomSizeRaw) ? $roomSizeRaw . ' m²' : (string)$roomSizeRaw) : '';
$roomMax = (int)($room['max_occupancy'] ?? ($room['max_adults'] ?? ($room['capacity'] ?? 0)));
$bedRaw = trim((string)($room['bed_configuration'] ?? ($room['beds'] ?? '')));
$bed = $bedRaw !== '' ? \App\Utility\TextFormatter::formatTitle($bedRaw) : '';
if ($bed !== '' && !preg_match('/\b(bed|beds)\b/i', $bed)) $bed .= ' Bed';
// Real amenities: room + property merged, unique. Never hardcoded.
$realAmenities = [];
foreach ([$room['amenities'] ?? [], $property['amenities'] ?? []] as $amSrc) {
    if (is_string($amSrc)) { $d = json_decode($amSrc, true); $amSrc = is_array($d) ? $d : explode(',', $amSrc); }
    foreach ((array)$amSrc as $am) {
        $t = trim((string)(is_array($am) ? ($am['name'] ?? '') : $am));
        if ($t !== '' && !in_array($t, $realAmenities, true)) $realAmenities[] = $t;
    }
}
$adults = max(1, (int)($queryParams['adults'] ?? ($room['max_adults'] ?? 2)));
$children = (int)($queryParams['children'] ?? ($room['max_children'] ?? 0));
$guests = $adults + $children;
$rooms = max(1, (int)($queryParams['rooms'] ?? 1));

$checkIn = $queryParams['checkIn'] ?? date('Y-m-d', strtotime('+7 days'));
$checkOut = $queryParams['checkOut'] ?? date('Y-m-d', strtotime('+13 days'));
$nights = max(1, (int)round((strtotime($checkOut) - strtotime($checkIn)) / 86400));

$pricePerNight = (float)($room['price'] ?? ($room['customer_price'] ?? ($calculation['price_per_night'] ?? 0)));
if (!empty($calculation) && !empty($calculation['total_amount'])) {
    $grandTotal = (float)$calculation['total_amount'];
    $subtotal = !empty($calculation['subtotal']) ? (float)$calculation['subtotal'] : ($pricePerNight * $nights * $rooms);
    $taxFee = !empty($calculation['taxes']) ? (float)$calculation['taxes'] : ($grandTotal - $subtotal);
} else {
    $subtotal = $pricePerNight * $nights * $rooms;
    $taxFee = 0;
    $grandTotal = $subtotal + $taxFee;
}
$avgPerNight = $nights > 0 ? round($subtotal / max(1,$nights * $rooms)) : $pricePerNight;

$img = $property['image_url'] ?? '';
if (!empty($room['photos'])) {
    $photos = is_string($room['photos']) ? json_decode($room['photos'], true) : $room['photos'];
    if (is_array($photos) && !empty($photos[0])) {
        $img0 = is_array($photos[0]) ? ($photos[0]['url'] ?? $photos[0]['image_url'] ?? '') : $photos[0];
        if ($img0) $img = $img0;
    }
}
// No fake fallback photo: empty stays empty, template renders a neutral placeholder.
$roomImg = $img;
if (!empty($room['photos'])) {
    // try room specific
    $photos = is_string($room['photos']) ? json_decode($room['photos'], true) : $room['photos'];
    if (is_array($photos) && !empty($photos[0])) {
        $r = is_array($photos[0]) ? ($photos[0]['url'] ?? '') : $photos[0];
        if ($r) $roomImg = $r;
    }
}


// Autofill
$prefillFirstName = $userProfile['first_name'] ?? '';
$prefillLastName = $userProfile['last_name'] ?? '';
if (empty($prefillFirstName) && !empty($userProfile['name'])) {
    $parts = explode(' ', trim($userProfile['name']), 2);
    $prefillFirstName = $parts[0] ?? '';
    $prefillLastName = $parts[1] ?? '';
}
$prefillEmail = $userProfile['email'] ?? '';
$prefillPhone = $userProfile['phone'] ?? '';
$prefillFullName = trim($prefillFirstName . ' ' . $prefillLastName);
$hasPrefill = !empty($prefillFirstName) && !empty($prefillEmail) && !empty($prefillPhone);
$leadEditDisplay = $hasPrefill ? 'none' : 'block';

$this->assign('title', 'Customer information');
?>
<?= $this->element('Booking/booking-page-style') ?>
<?= $this->element('navbar') ?>

<!-- Checkout progress stepper — clean banner under navbar -->
<div class="agoda-checkout-stepper">
  <div class="agoda-stepper-inner">
    <div class="agoda-steps d-none d-md-flex">
      <div class="agoda-step active"><span class="num">1</span><span>Customer information</span></div>
      <div class="agoda-step-line filled"></div>
      <div class="agoda-step"><span class="num">2</span><span>Payment information</span></div>
      <div class="agoda-step-line"></div>
      <div class="agoda-step"><span class="num">3</span><span>Booking is confirmed!</span></div>
    </div>
    <div class="d-flex d-md-none align-items-center justify-content-between w-100">
      <div class="d-flex align-items-center gap-2">
        <span class="agoda-step-badge">Step 1 of 2</span>
        <span class="agoda-step-title">Customer information</span>
      </div>
      <span class="agoda-step-next">Next: Payment <i class="fa-solid fa-chevron-right ms-1"></i></span>
    </div>
  </div>
</div>

<?= $this->element('Booking/booking-timer') ?>

<div class="agoda-checkout-wrap">
  <!-- LEFT COLUMN: Guest Details & Preferences -->
  <div style="display:flex;flex-direction:column;gap:16px">
    <?php if ($prefillFirstName !== ''): ?>
    <div class="agoda-card" style="padding:12px 16px">
      <div class="agoda-welcome">
        <span class="icon"><i class="fa-regular fa-user"></i></span>
        <span>Booking as <b><?= h($prefillFirstName) ?></b> (<a href="<?= $this->Url->build('/logout') ?>" class="agoda-link">Sign out</a>)</span>
      </div>
    </div>
    <?php endif; ?>

    <form id="agodaCheckoutForm" action="<?= $this->Url->build('/bookingpage-03') ?>" method="GET" style="display:flex;flex-direction:column;gap:16px" novalidate>
      <input type="hidden" name="property_id" value="<?= h($propId) ?>">
      <input type="hidden" name="room_id" value="<?= h($roomId) ?>">
      <input type="hidden" name="checkIn" value="<?= h($checkIn) ?>">
      <input type="hidden" name="checkOut" value="<?= h($checkOut) ?>">
      <input type="hidden" name="adults" value="<?= h($adults) ?>">
      <input type="hidden" name="children" value="<?= h($children) ?>">
      <input type="hidden" name="rooms" value="<?= h($rooms) ?>">
      <input type="hidden" name="quote_id" value="<?= h($quote['quote_id'] ?? '') ?>">
      <input type="hidden" name="city" value="<?= h($queryParams['city'] ?? $queryParams['destination'] ?? '') ?>">
      <input type="hidden" name="price" value="<?= h($queryParams['price'] ?? $pricePerNight) ?>">

      <!-- Guest Information Card -->
      <div class="agoda-card">
        <div class="agoda-card-title">
          <span>Who's the lead guest?</span>
          <span style="color:#e53935;font-size:12px;font-weight:600">* Required</span>
        </div>

        <div id="leadEditFields">
          <div class="agoda-input-grid">
            <div class="agoda-field-group">
              <label class="agoda-field-label" for="inputFirstName">First name <span style="color:#e53935">*</span></label>
              <input class="agoda-input" id="inputFirstName" name="first_name" value="<?= h($prefillFirstName) ?>" required autocomplete="given-name" placeholder="e.g. John">
              <div class="field-error" data-for="first_name" style="font-size:11.5px;color:#c0392b;display:none;margin-top:3px">First name is required</div>
            </div>
            <div class="agoda-field-group">
              <label class="agoda-field-label" for="inputLastName">Last name <span style="color:#e53935">*</span></label>
              <input class="agoda-input" id="inputLastName" name="last_name" value="<?= h($prefillLastName) ?>" required autocomplete="family-name" placeholder="e.g. Doe">
              <div class="field-error" data-for="last_name" style="font-size:11.5px;color:#c0392b;display:none;margin-top:3px">Last name is required</div>
            </div>
          </div>

          <div class="agoda-input-grid" style="margin-top:12px">
            <div class="agoda-field-group">
              <label class="agoda-field-label" for="inputEmail">Email address <span style="color:#e53935">*</span></label>
              <input class="agoda-input" id="inputEmail" type="email" name="email" value="<?= h($prefillEmail) ?>" required autocomplete="email" placeholder="you@example.com">
              <div class="field-error" data-for="email" style="font-size:11.5px;color:#c0392b;display:none;margin-top:3px">Valid email address is required</div>
            </div>
            <div class="agoda-field-group">
              <label class="agoda-field-label" for="inputPhone">Phone number (Tanzania +255) <span style="color:#e53935">*</span></label>
              <input class="agoda-input" id="inputPhone" name="phone" type="tel" value="<?= h($prefillPhone) ?>" required autocomplete="tel" placeholder="0712 345 678" pattern="[\d\s\+\-]{7,20}">
              <div class="field-error" data-for="phone" style="font-size:11.5px;color:#c0392b;display:none;margin-top:3px">Phone number is required</div>
            </div>
          </div>
        </div>
        <div id="customerFormError" style="display:none;background:#fdecea;border:1px solid #f5c6cb;color:#7d2e2e;padding:10px 12px;border-radius:8px;font-size:12.5px;margin-top:12px"></div>
      </div>

      <!-- Next Button CTA Card -->
      <div class="agoda-card" style="padding:16px">
        <button type="submit" class="agoda-next-btn">Continue to payment <i class="fa-solid fa-arrow-right ms-2"></i></button>
        <div class="agoda-not-charged"><i class="fa-solid fa-lock" style="font-size:11px"></i> Instant confirmation · You won't be charged yet</div>
      </div>
    </form>
  </div>

  <!-- RIGHT COLUMN: Stay & Price Summary -->
  <div class="agoda-side-card-wrap" style="display:flex;flex-direction:column;gap:16px">
    <div class="agoda-side-card">
      <!-- Hotel Header -->
      <div class="agoda-hotel-row">
        <?php if ($img !== ''): ?>
          <img class="agoda-hotel-thumb" src="<?= h($img) ?>" alt="<?= h($propTitle) ?>">
        <?php else: ?>
          <span class="agoda-hotel-thumb d-flex align-items-center justify-content-center" style="background:#e2e8f0;color:#94a3b8"><i class="fa-solid fa-hotel" style="font-size:24px"></i></span>
        <?php endif; ?>
        <div style="flex:1;min-width:0">
          <div class="agoda-hotel-title"><?= h($propTitle) ?></div>
          <?php if ($propStars > 0): ?><div class="agoda-stars"><?= str_repeat('★', $propStars) ?></div><?php endif; ?>
          <?php if ($hasRating): ?>
            <div class="agoda-rating" style="font-size:12px;margin-top:2px"><b><?= h(number_format($propRating,1)) ?> Excellent</b> · <?= h($propReviews) ?> reviews</div>
          <?php endif; ?>
          <div style="font-size:11.5px;color:#64748b;margin-top:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= h($propAddressShort) ?></div>
        </div>
      </div>

      <!-- Dates Row -->
      <div class="agoda-dates">
        <div>
          <span style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:.03em">Check-in</span>
          <b><?= date('D, M j, Y', strtotime($checkIn)) ?></b>
        </div>
        <div style="color:#94a3b8;font-size:16px">→</div>
        <div>
          <span style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:.03em">Check-out</span>
          <b><?= date('D, M j, Y', strtotime($checkOut)) ?></b>
        </div>
        <div style="text-align:right">
          <b><?= h($nights) ?> <?= $nights === 1 ? 'Night' : 'Nights' ?></b>
          <span style="font-size:11px;color:#64748b"><?= h($rooms) ?> <?= $rooms === 1 ? 'Room' : 'Rooms' ?>, <?= h($guests) ?> Guests</span>
        </div>
      </div>

      <!-- Room Row -->
      <div class="agoda-room-box">
        <div class="agoda-room-title">1 × <?= h($roomTitle) ?></div>
        <div class="agoda-room-meta">
          <?php
            $metaBits = [];
            if ($roomSize !== '') $metaBits[] = h($roomSize);
            if ($roomMax > 0) $metaBits[] = 'Max ' . h($roomMax) . ' adults';
            if ($bed !== '') $metaBits[] = h($bed);
            echo implode(' · ', $metaBits);
          ?>
        </div>
      </div>

      <!-- Price Breakdown -->
      <div class="agoda-price-breakdown">
        <div class="agoda-price-row">
          <span><?= h($rooms) ?> × Room (<?= h($nights) ?> <?= $nights === 1 ? 'night' : 'nights' ?>)</span>
          <span data-tzs="<?= (int)$subtotal ?>">TSh <?= number_format($subtotal) ?></span>
        </div>
        <div class="agoda-price-row">
          <span>Taxes & Service Fees</span>
          <?php if ($taxFee > 0): ?>
            <span data-tzs="<?= (int)$taxFee ?>">TSh <?= number_format($taxFee) ?></span>
          <?php else: ?>
            <span style="color:#059669;font-weight:600">Included</span>
          <?php endif; ?>
        </div>
        <div class="agoda-price-total">
          <span>Total Price</span>
          <span class="total-amount" data-tzs="<?= (int)$grandTotal ?>">TSh <?= number_format($grandTotal) ?></span>
        </div>
      </div>
    </div>
  </div>
</div>

<?= $this->Html->script('/assets/js/booking-page.js?v=' . filemtime(WWW_ROOT . 'assets/js/booking-page.js')) ?>
