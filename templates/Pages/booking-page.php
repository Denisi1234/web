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

<!-- Checkout progress stepper — clean banner under navbar (no duplicate avatar) -->
<div class="agoda-checkout-stepper" style="background:#fff;border-bottom:1px solid #e8eaed;padding:14px 0;margin-top:16px;">
  <div style="max-width:1180px;margin:0 auto;padding:0 16px;display:flex;align-items:center;justify-content:center;gap:16px;">
    <div class="agoda-steps" style="margin:0 auto;max-width:620px;flex:1;">
      <div class="agoda-step active"><span class="num">1</span><span>Customer information</span></div>
      <div class="agoda-step-line filled"></div>
      <div class="agoda-step"><span class="num">2</span><span>Payment information</span></div>
      <div class="agoda-step-line"></div>
      <div class="agoda-step"><span class="num">3</span><span>Booking is confirmed!</span></div>
    </div>
  </div>
</div>
<?= $this->element('Booking/booking-timer') ?>
<div class="agoda-checkout-wrap">
  <!-- LEFT -->
  <div style="display:flex;flex-direction:column;gap:12px">
    <?php if ($prefillFirstName !== ''): ?>
    <div class="agoda-card" style="padding:12px 16px">
      <div class="agoda-welcome"><span class="icon"><i class="fa-regular fa-user"></i></span> <span>Booking as <?= h($prefillFirstName) ?> (<a href="<?= $this->Url->build('/logout') ?>" class="agoda-link">Sign out</a>)</span></div>
    </div>
    <?php endif; ?>

    <form id="agodaCheckoutForm" action="<?= $this->Url->build('/bookingpage-03') ?>" method="GET" style="display:flex;flex-direction:column;gap:12px" novalidate>
      <input type="hidden" name="property_id" value="<?= h($propId) ?>">
      <input type="hidden" name="room_id" value="<?= h($roomId) ?>">
      <input type="hidden" name="checkIn" value="<?= h($checkIn) ?>">
      <input type="hidden" name="checkOut" value="<?= h($checkOut) ?>">
      <input type="hidden" name="adults" value="<?= h($adults) ?>">
      <input type="hidden" name="children" value="<?= h($children) ?>">
      <input type="hidden" name="rooms" value="<?= h($rooms) ?>">
      <input type="hidden" name="quote_id" value="<?= h($quote['quote_id'] ?? '') ?>">
      <!-- Preserve city/destination for fallback reconstruction -->
      <input type="hidden" name="city" value="<?= h($queryParams['city'] ?? $queryParams['destination'] ?? '') ?>">
      <input type="hidden" name="price" value="<?= h($queryParams['price'] ?? $pricePerNight) ?>">

      <div class="agoda-card">
        <div class="agoda-card-title">Who's the lead guest? <span style="color:#e53935;font-size:12px;font-weight:600">* required</span></div>
        <div class="agoda-lead-card" id="leadSummaryCard" style="<?= $hasPrefill ? '' : 'opacity:0.6' ?>">
          <div>
            <div class="agoda-lead-name"><i class="fa-solid fa-circle-user" style="color:#5f6368"></i> <span id="leadDisplayName"><?= h($prefillFullName ?: 'Please enter your details below') ?></span></div>
            <div class="agoda-lead-meta" id="leadDisplayEmail" style="margin-top:6px"><?= h($prefillEmail ?: 'Email required') ?></div>
          </div>
          <div class="agoda-lead-meta" id="leadDisplayPhone">Tanzania <?= h($prefillPhone ?: 'Phone required') ?></div>
          <a href="javascript:void(0)" class="agoda-edit" onclick="toggleLeadEdit()"><i class="fa-regular fa-pen-to-square"></i> <?= $hasPrefill ? 'Edit' : 'Enter details' ?></a>
        </div>
        <?php if (!$hasPrefill): ?>
        <div style="background:#fef3e8;border:1px solid #fde8cc;border-radius:6px;padding:8px 10px;font-size:12px;color:#664d03;margin-top:10px;display:flex;gap:6px;align-items:center"><i class="fa-solid fa-triangle-exclamation"></i> Please fill in customer information below to continue.</div>
        <?php endif; ?>
        <div id="leadEditFields" style="display:<?= $leadEditDisplay ?>;margin-top:14px">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
            <div><label style="font-size:12px;font-weight:600;color:#475569">First name <span style="color:#e53935">*</span></label><input class="agoda-input" id="inputFirstName" name="first_name" value="<?= h($prefillFirstName) ?>" required autocomplete="given-name" placeholder="e.g. John"><div class="field-error" data-for="first_name" style="font-size:11px;color:#c0392b;display:none;margin-top:4px">First name is required</div></div>
            <div><label style="font-size:12px;font-weight:600;color:#475569">Last name <span style="color:#e53935">*</span></label><input class="agoda-input" id="inputLastName" name="last_name" value="<?= h($prefillLastName) ?>" required autocomplete="family-name" placeholder="e.g. Doe"><div class="field-error" data-for="last_name" style="font-size:11px;color:#c0392b;display:none;margin-top:4px">Last name is required</div></div>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:10px">
            <div><label style="font-size:12px;font-weight:600;color:#475569">Email <span style="color:#e53935">*</span></label><input class="agoda-input" id="inputEmail" type="email" name="email" value="<?= h($prefillEmail) ?>" required autocomplete="email" placeholder="you@example.com"><div class="field-error" data-for="email" style="font-size:11px;color:#c0392b;display:none;margin-top:4px">Valid email is required</div></div>
            <div><label style="font-size:12px;font-weight:600;color:#475569">Phone <span style="color:#e53935">*</span></label><input class="agoda-input" id="inputPhone" name="phone" type="tel" value="<?= h($prefillPhone) ?>" required autocomplete="tel" placeholder="255 712 345 678" pattern="[\d\s\+\-]{7,20}"><div class="field-error" data-for="phone" style="font-size:11px;color:#c0392b;display:none;margin-top:4px">Phone is required</div></div>
          </div>
        </div>
        <div id="customerFormError" style="display:none;background:#fdecea;border:1px solid #f5c6cb;color:#7d2e2e;padding:8px 10px;border-radius:6px;font-size:12px;margin-top:10px"></div>
      </div>

      <div class="agoda-card">
        <div class="agoda-card-title">Special requests</div>
        <p style="font-size:12px;color:#5f6368;margin:0 0 12px">Subject to availability. Your selections will be sent to the property right after you book.</p>
        <div class="agoda-pref-grid">
          <div class="agoda-pref-col">
            <b>Which type of room would you prefer?</b>
            <label class="agoda-radio"><input type="radio" name="pref_room_type" value="non-smoking" checked> <i class="fa-solid fa-ban-smoking"></i> Non-smoking</label>
            <label class="agoda-radio"><input type="radio" name="pref_room_type" value="smoking"> <i class="fa-solid fa-smoking"></i> Smoking</label>
          </div>
          <div class="agoda-pref-col">
            <b>Which bed setup would you prefer?</b>
            <label class="agoda-radio"><input type="radio" name="pref_bed" value="large" checked> <i class="fa-solid fa-bed"></i> I'd like a large bed</label>
            <label class="agoda-radio"><input type="radio" name="pref_bed" value="twin"> <i class="fa-solid fa-bed"></i> I'd like twin beds</label>
          </div>
        </div>
        <div style="margin-top:12px"><a href="javascript:void(0)" class="agoda-link" onclick="toggleExtraPrefs(event)">Show additional preferences <i class="fa-solid fa-chevron-down" style="font-size:11px"></i></a></div>
        <div id="extraPrefs" style="display:none;margin-top:12px">
          <textarea class="agoda-input" style="height:80px;padding:8px" name="special_notes" placeholder="Any other requests?"></textarea>
        </div>
      </div>

      <?php $cancelPolicy01 = trim((string)($calculation['cancellation_policy'] ?? ($quote['calculation']['cancellation_policy'] ?? ''))); ?>
      <?php if ($cancelPolicy01 !== ''): ?>
      <div class="agoda-card">
        <div class="agoda-card-title">Cancellation</div>
        <div class="agoda-benefit">
          <i class="fa-solid fa-calendar-check" style="color:#0f62fe;font-size:28px"></i>
          <div style="flex:1">
            <div style="font-size:12px;color:#5f6368"><?= h($cancelPolicy01) ?></div>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <div class="agoda-card" style="padding:14px">
        <button type="submit" class="agoda-next-btn">Continue to payment</button>
        <div class="agoda-not-charged">You won't be charged yet.</div>
      </div>
    </form>
  </div>

  <!-- RIGHT -->
  <div style="display:flex;flex-direction:column;gap:12px">
    <div class="agoda-side-card">
      <div class="agoda-dates">
        <div><span style="font-size:11px;color:#5f6368">Check-in</span><b style="display:block"><?= date('D, M j', strtotime($checkIn)) ?></b></div>
        <div style="color:#5f6368">→</div>
        <div><span style="font-size:11px;color:#5f6368">Check-out</span><b style="display:block"><?= date('D, M j', strtotime($checkOut)) ?></b></div>
        <div style="text-align:right"><b><?= h($nights) ?></b><span style="font-size:11px;color:#5f6368;display:block">nights</span></div>
      </div>
    </div>

    <div class="agoda-side-card">
      <div class="agoda-side-card-inner">
        <div class="agoda-hotel-row">
          <?php if ($img !== ''): ?><img class="agoda-hotel-thumb" src="<?= h($img) ?>" alt="<?= h($propTitle) ?>"><?php else: ?><span class="agoda-hotel-thumb" style="display:inline-flex;align-items:center;justify-content:center;background:var(--cds-gray-10);color:#8d8d8d;" aria-hidden="true"><i class="fa-solid fa-image"></i></span><?php endif; ?>
          <div>
            <div class="agoda-hotel-title"><?= h($propTitle) ?></div>
            <?php if ($propStars > 0): ?><div class="agoda-stars"><?= str_repeat('★', $propStars) ?></div><?php endif; ?>
            <?php if ($hasRating): ?><div class="agoda-rating"><b><?= h(number_format($propRating,1)) ?> Excellent</b> <span style="color:#5f6368;font-size:12px"><?= h($propReviews) ?> reviews</span></div><?php else: ?><div class="agoda-rating"><span style="color:#5f6368;font-size:12px">New property — no reviews yet</span></div><?php endif; ?>
            <div style="font-size:11px;color:#5f6368;margin-top:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:200px"><?= h($propAddressShort) ?></div>

          </div>
        </div>
        <?php $cancelLine = trim((string)($calculation['cancellation_policy'] ?? ($quote['calculation']['cancellation_policy'] ?? ''))); ?>
        <?php if ($cancelLine !== ''): ?><div style="margin-top:10px;font-size:11px;color:#0f7a2b;display:flex;gap:6px;align-items:center"><i class="fa-solid fa-shield-check"></i> <?= h($cancelLine) ?>.</div><?php endif; ?>
      </div>
    </div>

    <div class="agoda-side-card">
      <div class="agoda-side-card-inner">
        <div class="agoda-room-box">
          <?php if ($roomImg !== ''): ?><img class="agoda-room-thumb" src="<?= h($roomImg) ?>" alt="<?= h($roomTitle) ?>"><?php else: ?><span class="agoda-room-thumb" style="display:inline-flex;align-items:center;justify-content:center;background:var(--cds-gray-10);color:#8d8d8d;" aria-hidden="true"><i class="fa-solid fa-bed"></i></span><?php endif; ?>
          <div>
            <div class="agoda-room-title">1 x <?= h($roomTitle) ?></div>
            <div class="agoda-room-meta"><?php
              $metaBits = [];
              if ($roomSize !== '') $metaBits[] = 'Size: ' . h($roomSize);
              if ($roomMax > 0) $metaBits[] = 'Max: ' . h($roomMax) . ' adults';
              if ($bed !== '') $metaBits[] = h($bed);
              echo implode('<br>', $metaBits);
            ?></div>
          </div>
        </div>
        <?php if (!empty($realAmenities)): ?>
        <div style="margin-top:10px" class="agoda-amenities">
          <div style="margin-top:8px;display:grid;grid-template-columns:1fr 1fr;gap:4px 8px">
            <?php foreach (array_slice($realAmenities, 0, 6) as $amItem): ?>
            <span><i class="fa-solid fa-check" style="font-size:10px"></i> <?= h($amItem) ?></span>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div>
<?= $this->Html->script('/assets/js/booking-page.js?v=' . filemtime(WWW_ROOT . 'assets/js/booking-page.js')) ?>
