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
$propStars = !empty($property['star_rating']) ? max(1, min(5, (int)$property['star_rating'])) : 4;
$rawAddr = trim((string)($property['address'] ?? ''));
$propAddressShort = $rawAddr !== '' ? \App\Utility\TextFormatter::formatTitle($rawAddr) : \App\Utility\TextFormatter::formatLocation($propArea, $propCity);
$propRating = $property['rating'] ?? 8.4;
$propReviews = $property['review_count'] ?? $property['reviews_count'] ?? 737;

$roomTitle = \App\Utility\TextFormatter::formatTitle((string)($room['name'] ?? ($room['room_number'] ?? 'Standard Room')));
$roomSize = $room['size'] ?? $room['area'] ?? $room['room_size'] ?? '78 m²';
$roomMax = $room['max_occupancy'] ?? $room['max_adults'] ?? 2;
$bed = \App\Utility\TextFormatter::formatTitle((string)($room['bed_configuration'] ?? '1 King Bed'));
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
if (empty($img)) $img = 'https://images.unsplash.com/photo-1571896349842-89672768eec3?w=400&h=300&fit=crop'; // fallback resort pool
$roomImg = $img;
if (!empty($room['photos'])) {
    // try room specific
    $photos = is_string($room['photos']) ? json_decode($room['photos'], true) : $room['photos'];
    if (is_array($photos) && !empty($photos[0])) {
        $r = is_array($photos[0]) ? ($photos[0]['url'] ?? '') : $photos[0];
        if ($r) $roomImg = $r;
    }
}
if ($roomImg === $img) $roomImg = 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=300&h=200&fit=crop';

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

$this->assign('title', 'Customer information | fastnetstays.com');
?>
<style>
/* Agoda Checkout — exact to screenshot */
.agoda-checkout-header{background:#fff;border-bottom:1px solid #e8eaed;min-height:64px;display:flex;align-items:center;position:sticky;top:0;z-index:100;padding:14px 0}
.agoda-checkout-header-inner{max-width:1180px;margin:0 auto;padding:0 16px;width:100%;display:flex;align-items:center;justify-content:space-between;gap:16px}
.agoda-logo{font-size:22px;font-weight:800;letter-spacing:-0.02em;display:flex;align-items:center;gap:4px;text-decoration:none!important}
.agoda-logo .dot{width:10px;height:10px;border-radius:50%;display:inline-block}
.agoda-logo b{font-weight:800;color:#202124}
.agoda-steps{display:flex;align-items:center;gap:0;flex:1;max-width:560px;margin:0 24px}
.agoda-step{display:flex;align-items:center;gap:8px;font-size:13px;font-weight:600;white-space:nowrap}
.agoda-step .num{width:22px;height:22px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;border:1px solid #dadce0;background:#fff;color:#5f6368}
.agoda-step.active .num{background:#2563EB;color:#fff;border-color:#2563EB}
.agoda-step.done .num{background:#2563EB;color:#fff;border-color:#2563EB}
.agoda-step span{color:#5f6368}
.agoda-step.active span{color:#2563EB}
.agoda-step-line{flex:1;height:2px;background:#e8eaed;margin:0 8px;border-radius:1px}
.agoda-step-line.filled{background:#2563EB}
.agoda-user{font-size:13px;color:#202124;display:flex;align-items:center;gap:8px;white-space:nowrap}
.agoda-user .avatar{width:32px;height:32px;border-radius:50%;background:#7c6af0;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px}
.agoda-timer-bar{background:#fef3e8;border-bottom:1px solid #fde8cc;padding:10px 16px;text-align:center;font-size:13px;color:#202124;display:flex;align-items:center;justify-content:center;gap:8px}
.agoda-timer-bar b{color:#e53935;font-weight:700;display:flex;align-items:center;gap:6px}
.agoda-checkout-wrap{max-width:1180px;margin:14px auto;padding:0 16px;display:grid;grid-template-columns:1fr 360px;gap:16px;align-items:start}
.agoda-card{background:#fff;border:1px solid #e8eaed;border-radius:16px;padding:16px;box-shadow:0 6px 16px rgba(0,0,0,0.05)}
.agoda-card-title{font-size:16px;font-weight:800;color:#202124;margin:0 0 10px}
.agoda-welcome{display:flex;align-items:center;gap:12px;font-size:13px;color:#202124}
.agoda-welcome .icon{width:44px;height:32px;background:#3576f6;color:#fff;display:flex;align-items:center;justify-content:center;border-radius:2px}
.agoda-lead-card{background:#F8FAFC;border:1px solid #e8eaed;border-radius:14px;padding:14px 16px;display:grid;grid-template-columns:1fr auto;gap:8px 16px}
.agoda-lead-name{font-size:14px;font-weight:700;color:#202124;display:flex;align-items:center;gap:6px}
.agoda-lead-meta{font-size:13px;color:#202124}
.agoda-edit{color:#3264ff;font-size:13px;font-weight:700;text-decoration:none;display:flex;align-items:center;gap:4px;align-self:end}
.agoda-pref-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;background:#F8FAFC;border:1px solid #e8eaed;border-radius:14px;padding:14px 16px}
.agoda-pref-col b{font-size:13px;color:#202124;display:block;margin-bottom:8px}
.agoda-radio{display:flex;align-items:center;gap:8px;font-size:13px;color:#202124;margin:8px 0}
.agoda-radio input{width:18px;height:18px;accent-color:#1a73e8}
.agoda-link{color:#3264ff;font-size:13px;font-weight:600;text-decoration:none}
.agoda-link:hover{text-decoration:underline}
.agoda-benefit{display:flex;align-items:center;gap:12px;background:#f8f9fa;border-radius:8px;padding:14px}
.agoda-benefit .free-badge{background:#0f7a2b;color:#fff;font-size:11px;font-weight:800;padding:4px 8px;border-radius:4px}
.agoda-quote{font-size:13px;color:#5f6368;background:#f8f9fa;border-radius:8px;padding:10px 12px;margin-top:10px}
.agoda-urgent{font-size:12px;color:#c0392b;text-align:center;margin-top:8px}
.agoda-next-btn{background:#2563EB;color:#fff;border:none;border-radius:30px;padding:14px 24px;font-size:15px;font-weight:800;width:100%;cursor:pointer;letter-spacing:0.02em;box-shadow:0 4px 12px rgba(37,99,235,0.18);transition:background 150ms ease}
.agoda-next-btn:hover{background:#1d4ed8}
.agoda-not-charged{font-size:12px;color:#0f7a2b;text-align:center;font-weight:600;margin-top:6px}
.agoda-side-card{background:#fff;border:1px solid #e8eaed;border-radius:16px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.04)}
.agoda-side-card-inner{padding:14px}
.agoda-dates{display:flex;align-items:center;justify-content:space-between;font-size:13px;color:#202124;padding:12px 14px;border-bottom:1px solid #f1f3f4}
.agoda-dates b{font-size:14px;color:#202124}
.agoda-hotel-row{display:flex;gap:12px}
.agoda-hotel-thumb{width:72px;height:72px;border-radius:8px;object-fit:cover;flex:0 0 72px}
.agoda-hotel-title{font-size:14px;font-weight:800;color:#202124;line-height:1.3}
.agoda-stars{color:#e67e22;font-size:11px}
.agoda-rating{font-size:13px;color:#202124}
.agoda-rating b{color:#0d4a7a}
.agoda-room-box{background:#F8FAFC;border:1px solid #e8eaed;border-radius:14px;padding:12px;display:flex;gap:12px}
.agoda-room-thumb{width:72px;height:72px;border-radius:8px;object-fit:cover;flex:0 0 72px}
.agoda-room-title{font-size:13px;font-weight:700;color:#202124}
.agoda-room-meta{font-size:12px;color:#202124;line-height:1.6}
.agoda-amenities{font-size:12px;color:#0f7a2b;line-height:1.8}
.agoda-amenities span{margin-right:10px;white-space:nowrap}
.agoda-green-banner{background:#e6f4ea;border:1px solid #c8e6c9;border-radius:8px;padding:10px 12px;font-size:12px;color:#202124;display:flex;gap:8px;align-items:center}
.agoda-green-banner b{color:#137333}
.agoda-pink-banner{background:#fdecea;border:1px solid #f5c6cb;border-radius:8px;padding:10px 12px;font-size:12px;color:#7d2e2e;display:flex;gap:8px;align-items:center}
.agoda-input{width:100%;height:44px;border:1px solid #dadce0;border-radius:12px;padding:0 12px;font-size:13px;transition:border-color 150ms ease,box-shadow 150ms ease}
.agoda-input:focus{outline:none;border-color:#2563EB;box-shadow:0 0 0 2px rgba(37,99,235,0.15)}
@media(max-width:992px){
  .agoda-checkout-header{height:auto;padding:10px 0}
  .agoda-checkout-header-inner{flex-wrap:wrap;gap:10px}
  .agoda-steps{order:3;max-width:none;width:100%;margin:0;justify-content:space-between}
  .agoda-checkout-wrap{grid-template-columns:1fr;gap:12px;padding:0 12px}
  .agoda-side-card{order:-1}
}
@media(max-width:600px){
  .agoda-checkout-header-inner{padding:0 12px}
  .agoda-steps{gap:4px}
  .agoda-step{font-size:11px;gap:4px}
  .agoda-step .num{width:18px;height:18px;font-size:10px}
  .agoda-timer-bar{font-size:12px;padding:8px 12px}
  .agoda-card{padding:12px}
  .agoda-pref-grid{grid-template-columns:1fr;gap:12px}
  .agoda-hotel-title{font-size:13px}
  .agoda-dates{padding:10px 12px;font-size:12px}
  .agoda-dates b{font-size:13px}
  .agoda-user{display:none!important}
}
@media(max-width:768px){
  html,body{max-width:100%;overflow-x:hidden}
  .agoda-user{display:none!important}
  .agoda-checkout-wrap{padding:0 8px;gap:12px}
  .agoda-card{border-radius:10px}
  .agoda-timer-bar{flex-wrap:wrap;gap:4px}
  .agoda-lead-card{grid-template-columns:1fr;gap:6px}
  .agoda-hotel-row{flex-direction:row;gap:10px}
  .agoda-room-box{flex-direction:row}
  .agoda-next-btn{padding:14px 24px;font-size:15px}
}
@media(max-width:480px){
  .agoda-checkout-header-inner{padding:0 8px;gap:8px}
  .agoda-steps{gap:3px}
  .agoda-step{font-size:10px;gap:3px}
  .agoda-step .num{width:16px;height:16px;font-size:9px}
  .agoda-checkout-wrap{padding:0 8px}
  .agoda-card{padding:10px}
  .agoda-welcome{font-size:12px}
  .agoda-lead-name{font-size:13px}
  .agoda-lead-meta{font-size:12px}
  .agoda-pref-grid{padding:12px}
  .agoda-benefit{padding:12px;gap:10px}
  .agoda-benefit i{font-size:24px!important}
  .agoda-quote{font-size:12px}
  .agoda-dates{flex-wrap:wrap;gap:8px}
  .agoda-hotel-thumb,.agoda-room-thumb{width:60px;height:60px;flex:0 0 60px}
  .agoda-amenities{font-size:11px}
  .agoda-green-banner,.agoda-pink-banner{font-size:11px;padding:8px 10px}
}
@media(max-width:375px){
  .agoda-checkout-header{padding:6px 0}
  .agoda-steps{gap:2px}
  .agoda-step{font-size:9.5px}
  .agoda-timer-bar{font-size:11px;padding:6px 8px}
  .agoda-card{padding:8px}
  .agoda-next-btn{font-size:14px;padding:12px 16px}
  .agoda-input{height:38px;font-size:13px}
}
</style>
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
<div class="agoda-timer-bar">This price is guaranteed for... <b><i class="fa-regular fa-clock"></i> <span id="agodaCountdown">00:18:35</span></b></div>

<?php if (!empty($quoteError) && empty($quote['is_fallback'] ?? false) || !empty($quote['_fallback'] ?? false) && !empty($quoteError)): ?>
  <div style="max-width:1180px;margin:14px auto 0;padding:0 16px">
    <div style="background:#fff3cd;border:1px solid #ffe69c;color:#664d03;padding:10px 14px;border-radius:8px;font-size:13px;display:flex;gap:8px;align-items:center">
      <i class="fa-solid fa-circle-info"></i>
      <span>
        <?php if (!empty($quote['_fallback'])): ?>
          Booking quote generated locally. Live price will be confirmed on payment.
        <?php else: ?>
          <?= h($quoteError) ?>
        <?php endif; ?>
      </span>
    </div>
  </div>
  <?php endif; ?>
<div class="agoda-checkout-wrap">
  <!-- LEFT -->
  <div style="display:flex;flex-direction:column;gap:12px">
    <div class="agoda-card" style="padding:12px 16px">
      <div class="agoda-welcome"><span class="icon"><i class="fa-regular fa-user"></i></span> <span>Welcome, <?= h($prefillFirstName ?: 'Guest') ?> ! (Not <?= h($prefillFirstName ?: 'Guest') ?> ? <a href="#" class="agoda-link">Sign out</a>)</span></div>
    </div>

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

      <div class="agoda-card">
        <div class="agoda-card-title" style="color:#0f7a2b">Free room benefits</div>
        <div class="agoda-benefit">
          <i class="fa-solid fa-calendar-check" style="color:#1a73e8;font-size:28px"></i>
          <div style="flex:1">
            <div style="font-size:13px;font-weight:700;color:#202124">Fully refundable</div>
            <div style="font-size:12px;color:#5f6368"><?= h($calculation['cancellation_policy'] ?? $quote['calculation']['cancellation_policy'] ?? 'Free cancellation before ' . date('j F Y', strtotime($checkIn))) ?></div>
          </div>
          <span class="free-badge">FREE</span>
        </div>
      </div>

      <div class="agoda-quote">"The beautiful beach was a favorite and it was never crowded." <span style="display:flex;align-items:center;gap:6px;margin-top:6px;font-size:12px;color:#5f6368"><span>🇺🇸</span> Julie · Jun 2024</span></div>
      <div class="agoda-urgent">Hurry! Our last room for your dates at this price</div>

      <div class="agoda-card" style="padding:14px">
        <button type="submit" class="agoda-next-btn">NEXT: FINAL STEP</button>
        <div class="agoda-not-charged">You won't be charged yet.</div>
      </div>
    </form>
  </div>

  <!-- RIGHT -->
  <div style="display:flex;flex-direction:column;gap:12px">
    <div class="agoda-side-card">
      <div class="agoda-dates">
        <div><span style="font-size:11px;color:#5f6368">Check-in</span><b style="display:block"><?= date('D, M j', strtotime($checkIn)) ?></b><span style="font-size:11px;color:#5f6368">16:00</span></div>
        <div style="color:#5f6368">→</div>
        <div><span style="font-size:11px;color:#5f6368">Check-out</span><b style="display:block"><?= date('D, M j', strtotime($checkOut)) ?></b><span style="font-size:11px;color:#5f6368">10:00</span></div>
        <div style="text-align:right"><b><?= h($nights) ?></b><span style="font-size:11px;color:#5f6368;display:block">nights</span></div>
      </div>
    </div>

    <div class="agoda-side-card">
      <div class="agoda-side-card-inner">
        <div class="agoda-hotel-row">
          <img class="agoda-hotel-thumb" src="<?= h($img) ?>" alt="<?= h($propTitle) ?>" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1566073771259-6a8506099945?w=200&h=200&fit=crop'">
          <div>
            <div class="agoda-hotel-title"><?= h($propTitle) ?></div>
            <div class="agoda-stars"><?= str_repeat('★', $propStars) ?></div>
            <div class="agoda-rating"><b><?= h(number_format((float)$propRating,1)) ?> Excellent</b> <span style="color:#5f6368;font-size:12px"><?= h($propReviews) ?> reviews</span></div>
            <div style="font-size:11px;color:#5f6368;margin-top:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:200px"><?= h($propAddressShort) ?>...</div>
            <a href="javascript:void(0)" class="agoda-link" style="font-size:12px">What's nearby?</a>
          </div>
        </div>
        <div style="margin-top:10px;font-size:11px;color:#0f7a2b;display:flex;gap:6px;align-items:center"><i class="fa-solid fa-shield-check"></i> <?= h($calculation['cancellation_policy'] ?? $quote['calculation']['cancellation_policy'] ?? 'Stay flexible! Free cancellation') ?>.</div>
      </div>
    </div>

    <div class="agoda-side-card">
      <div class="agoda-side-card-inner">
        <div class="agoda-room-box">
          <img class="agoda-room-thumb" src="<?= h($roomImg) ?>" alt="<?= h($roomTitle) ?>" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=200&h=200&fit=crop'">
          <div>
            <div class="agoda-room-title">1 x <?= h($roomTitle) ?></div>
            <div class="agoda-room-meta">Size:<?= h($roomSize) ?><br>Max:<?= h($roomMax) ?> adults<br><?= h($bed) ?></div>
          </div>
        </div>
        <div style="margin-top:10px" class="agoda-amenities">
          <div style="color:#0f7a2b"><i class="fa-solid fa-suitcase"></i> Luggage storage available<br><i class="fa-solid fa-clock"></i> 24 hours check-in<br><i class="fa-solid fa-check"></i> Book and pay now<br><span style="color:#c45a00"><i class="fa-solid fa-fire"></i> Hurry! Our last room for your dates at this price</span></div>
          <div style="margin-top:8px;display:grid;grid-template-columns:1fr 1fr;gap:4px 8px">
            <span><i class="fa-solid fa-check" style="font-size:10px"></i> Separate shower/bathtub</span><span><i class="fa-solid fa-check" style="font-size:10px"></i> Blackout curtains</span>
            <span><i class="fa-solid fa-check" style="font-size:10px"></i> Non-smoking</span><span><i class="fa-solid fa-check" style="font-size:10px"></i> Private bathroom</span>
            <span><i class="fa-solid fa-check" style="font-size:10px"></i> Wi-Fi [free]</span><span><i class="fa-solid fa-check" style="font-size:10px"></i> Individual air conditioning</span>
            <span><i class="fa-solid fa-check" style="font-size:10px"></i> Coffee/tea maker</span><span><i class="fa-solid fa-check" style="font-size:10px"></i> Refrigerator</span>
            <span><i class="fa-solid fa-check" style="font-size:10px"></i> Hair dryer</span><span><a href="#" class="agoda-link">+28 more</a></span>
          </div>
        </div>
      </div>
    </div>

    <div class="agoda-green-banner"><i class="fa-solid fa-thumbs-up" style="color:#0f7a2b"></i> <span><b>Great choice of property</b> – with an average guest rating of <b>8.3</b></span></div>
    <div class="agoda-pink-banner"><i class="fa-solid fa-bell" style="color:#c0392b"></i> Hurry! Our last room for your dates at this price</div>
  </div>
</div>

<script>
let agodaSeconds = <?= isset($quote['expires_at']) ? max(0, (int)$quote['expires_at'] - time()) : 18*60+35 ?>;
function tickAgoda(){
  const el=document.getElementById('agodaCountdown');
  if(!el) return;
  let s=agodaSeconds--;
  if(s<0){el.textContent='00:00:00';return}
  const h=String(Math.floor(s/3600)).padStart(2,'0');
  const m=String(Math.floor((s%3600)/60)).padStart(2,'0');
  const sec=String(s%60).padStart(2,'0');
  el.textContent=h+':'+m+':'+sec;
  setTimeout(tickAgoda,1000);
}
function toggleLeadEdit(){const e=document.getElementById('leadEditFields');e.style.display=(e.style.display==='none'||e.style.display==='')?'block':'none'}
function toggleExtraPrefs(ev){ev.preventDefault();const e=document.getElementById('extraPrefs');e.style.display=e.style.display==='none'?'block':'none'}
function validateCustomerInfo(){
  const getVal = (sel)=> document.querySelector(sel)?.value.trim() || '';
  const fn=getVal('#inputFirstName'), ln=getVal('#inputLastName'), em=getVal('#inputEmail'), ph=getVal('#inputPhone');
  let valid=true;
  const errors=[];
  const setErr = (key, show)=>{
    const el=document.querySelector('.field-error[data-for="'+key+'"]');
    if(el) el.style.display=show?'block':'none';
    const inp=document.querySelector('[name="'+key+'"]');
    if(inp) inp.style.borderColor=show?'#c0392b':'#dadce0';
  };
  // First name
  if(!fn){ setErr('first_name', true); valid=false; errors.push('First name is required'); } else setErr('first_name', false);
  if(!ln){ setErr('last_name', true); valid=false; errors.push('Last name is required'); } else setErr('last_name', false);
  const emailOk=/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(em);
  if(!em || !emailOk){ setErr('email', true); valid=false; if(!em) errors.push('Email is required'); else errors.push('Valid email is required'); } else setErr('email', false);
  if(!ph){ setErr('phone', true); valid=false; errors.push('Phone is required'); } else setErr('phone', false);
  const errBox=document.getElementById('customerFormError');
  if(!valid){
    if(errBox){ errBox.style.display='block'; errBox.textContent=errors.join(' • '); }
    // Ensure edit section visible so user can fix
    const edit=document.getElementById('leadEditFields');
    if(edit && (edit.style.display==='none'||edit.style.display==='')) edit.style.display='block';
  } else {
    if(errBox){ errBox.style.display='none'; }
  }
  return valid;
}
function updateLeadPreview(){
  const fn=document.getElementById('inputFirstName')?.value.trim()||'';
  const ln=document.getElementById('inputLastName')?.value.trim()||'';
  const em=document.getElementById('inputEmail')?.value.trim()||'';
  const ph=document.getElementById('inputPhone')?.value.trim()||'';
  const nameEl=document.getElementById('leadDisplayName');
  const emailEl=document.getElementById('leadDisplayEmail');
  const phoneEl=document.getElementById('leadDisplayPhone');
  if(nameEl) nameEl.textContent=(fn+' '+ln).trim() || 'Please enter your details below';
  if(emailEl) emailEl.textContent=em || 'Email required';
  if(phoneEl) phoneEl.textContent= ph ? ('Tanzania '+ph) : 'Phone required';
}
document.addEventListener('DOMContentLoaded',()=>{
  const form=document.getElementById('agodaCheckoutForm');
  if(form){
    // Live preview update
    ['#inputFirstName','#inputLastName','#inputEmail','#inputPhone'].forEach(sel=>{
      const el=document.querySelector(sel);
      if(el) el.addEventListener('input', updateLeadPreview);
    });
    updateLeadPreview();
    form.addEventListener('submit', (ev)=>{
      if(!validateCustomerInfo()){
        ev.preventDefault();
        ev.stopPropagation();
        // scroll to error
        document.getElementById('leadEditFields')?.scrollIntoView({behavior:'smooth', block:'center'});
        return false;
      }
      // Disable button to prevent double click
      const btn=form.querySelector('.agoda-next-btn');
      if(btn){ btn.disabled=true; btn.textContent='Processing…'; btn.style.opacity='0.7'; }
    });
    const nextBtn=form.querySelector('.agoda-next-btn');
    if(nextBtn){
      // Remove old fallback that forced navigation on invalid; we now block properly
      nextBtn.addEventListener('click', (e)=>{
        // Trigger form submit validation; if invalid, prevent
        // No auto-force navigation — handled by submit handler
      });
    }
  }
  // Restore visual edit open if any field invalid on load
  const anyEmpty = !document.getElementById('inputFirstName')?.value.trim() || !document.getElementById('inputEmail')?.value.trim();
  if(anyEmpty){
    const edit=document.getElementById('leadEditFields');
    if(edit) edit.style.display='block';
  }
});
document.addEventListener('DOMContentLoaded',()=>{
  try{
    const u=JSON.parse(localStorage.getItem('user')||localStorage.getItem('fastnet_user')||'null');
    if(u){
      let changed=false;
      if(u.name && !document.querySelector('[name=first_name]')?.value){
        const parts=u.name.split(' ');
        const fn=document.querySelector('[name=first_name]'); if(fn){ fn.value=parts[0]||''; changed=true; }
        const ln=document.querySelector('[name=last_name]'); if(ln){ ln.value=parts.slice(1).join(' ')||''; changed=true; }
      }
      if(u.email && !document.querySelector('[name=email]')?.value){
        const e=document.querySelector('[name=email]'); if(e){ e.value=u.email; changed=true; }
      }
      if(u.phone && !document.querySelector('[name=phone]')?.value){
        const p=document.querySelector('[name=phone]'); if(p){ p.value=u.phone; changed=true; }
      }
      if(changed) updateLeadPreview();
    }
  }catch(e){}
});
</script>
