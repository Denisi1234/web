<?php
$this->assign('title', 'Payment information');
$propTitle = $property['name'] ?? 'Divi Village Golf and Beach Resort';
$propCity = $property['city'] ?? 'Dar es Salaam';
$propAddress = $property['address'] ?? 'Msasani Peninsula, Dar es Salaam, Tanzania';
$propStars = !empty($property['star_rating']) ? max(1,min(5,(int)$property['star_rating'])) : 4;
$checkIn = $queryParams['checkIn'] ?? $queryParams['check_in'] ?? date('Y-m-d', strtotime('+7 days'));
$checkOut = $queryParams['checkOut'] ?? $queryParams['check_out'] ?? date('Y-m-d', strtotime('+13 days'));
$nights = max(1, (int)round((strtotime($checkOut)-strtotime($checkIn))/86400));
if ($nights <1) $nights=6;
$roomTitle = $room['name'] ?? 'Golf Villa One Bedroom Suite';
$origPrice = (float)($calculation['original_price'] ?? $calculation['subtotal'] ?? 0);
$roomPrice = (float)($calculation['subtotal'] ?? $calculation['room_price'] ?? 0);
$taxes = (float)($calculation['taxes'] ?? 0);
$azampayFee = (float)($calculation['azampay_fee'] ?? 0);
// Quote 'taxes' already bundles backend VAT (0%) + AzamPay 1% — split honestly for display
if ($azampayFee <= 0 && $taxes > 0) $azampayFee = $taxes;
$baseTaxes = max(0, $taxes - $azampayFee);
$roomsCount = max(1, (int)($queryParams['rooms'] ?? $quote['rooms'] ?? 1));
$bookingFees = (float)($calculation['booking_fees'] ?? 0);
$total = (float)($calculation['total_amount'] ?? 0);
$saved = max(0, $origPrice - $total);
$offPercent = $origPrice > 0 ? (int)round(($saved / $origPrice) * 100) : 0;
$quoteId = $quote['quote_id'] ?? $queryParams['quote_id'] ?? '';
$propertyId = $property['id'] ?? $queryParams['property_id'] ?? 0;
$roomId = $room['id'] ?? $queryParams['room_id'] ?? 0;
$guestEmail = $queryParams['guest_email'] ?? $queryParams['email'] ?? '';
?>
<style>
/* Agoda Payment — step 2 — matches screenshot */
.agoda-pay-header{background:#fff;border-bottom:1px solid #e8eaed;min-height:64px;display:flex;align-items:center;position:sticky;top:0;z-index:100;margin-top:16px;padding:14px 0}
.agoda-pay-header-inner{max-width:1180px;margin:0 auto;padding:0 16px;width:100%;display:flex;align-items:center;justify-content:space-between;gap:16px}
.agoda-logo{font-size:22px;font-weight:900;letter-spacing:-0.02em;display:flex;align-items:center;gap:4px;text-decoration:none!important;color:#202124}
.agoda-logo .dot{width:10px;height:10px;border-radius:50%;display:inline-block}
.agoda-steps{display:flex;align-items:center;gap:0;flex:1;max-width:620px;margin:0 24px}
.agoda-step{display:flex;align-items:center;gap:8px;font-size:13px;font-weight:600;white-space:nowrap}
.agoda-step .num{width:22px;height:22px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;border:1px solid #dadce0;background:#fff;color:#5f6368}
.agoda-step.done .num{background:#0f62fe;color:#fff;border-color:#0f62fe}
.agoda-step.active .num{background:#0f62fe;color:#fff;border-color:#0f62fe}
.agoda-step span{color:#5f6368}
.agoda-step.active span{color:#0f62fe}
.agoda-step.done span{color:#0f62fe}
.agoda-step-line{flex:1;height:2px;background:#e8eaed;margin:0 8px;border-radius:1px}
.agoda-step-line.filled{background:#0f62fe}
.agoda-timer-bar{background:#fef3e8;border-bottom:1px solid #fde8cc;padding:10px 16px;text-align:center;font-size:13px;color:#202124;display:flex;align-items:center;justify-content:center;gap:8px}
.agoda-timer-bar b{color:#e53935;font-weight:700;display:flex;align-items:center;gap:6px}
.agoda-pay-wrap{max-width:1180px;margin:14px auto;padding:0 16px;display:grid;grid-template-columns:1fr 360px;gap:16px;align-items:start}
.agoda-card{background:#fff;border:1px solid #e8eaed;border-radius:16px;overflow:hidden;box-shadow:0 6px 16px rgba(0,0,0,0.05)}
.agoda-card-pad{padding:16px}
.agoda-pay-title{font-size:18px;font-weight:800;color:#202124;margin:0}
.agoda-pay-sub{font-size:12px;color:#0f62fe;display:flex;align-items:center;gap:4px;margin-top:4px}
.agoda-pay-radio{display:flex;align-items:center;gap:8px;font-size:14px;font-weight:700;color:#0f62fe}
.agoda-pay-radio input{width:18px;height:18px;accent-color:#0f62fe}
.agoda-card-icons{display:flex;gap:6px;align-items:center;flex-wrap:wrap;justify-content:flex-end}
.agoda-card-icons img{height:24px;border:1px solid #e8eaed;border-radius:4px;background:#fff;padding:2px 4px;object-fit:contain}
.agoda-pay-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:16px 16px 12px;border-bottom:none}
.agoda-green-tip{background:#e6f4ea;color:#137333;font-size:12px;font-weight:600;text-align:center;padding:8px 12px;border-radius:0;position:relative}
.agoda-green-tip:after{content:"";position:absolute;left:50%;bottom:-6px;transform:translateX(-50%);width:0;height:0;border-left:6px solid transparent;border-right:6px solid transparent;border-top:6px solid #e6f4ea}
.agoda-form-grid{display:grid;grid-template-columns:1fr 260px;gap:16px;padding:16px}
.agoda-field{position:relative;margin-bottom:14px}
.agoda-field label{position:absolute;top:-8px;left:10px;background:#fff;padding:0 6px;font-size:11px;color:#137333;font-weight:600}
.agoda-field label.req{color:#137333}
.agoda-input{width:100%;height:44px;border:1px solid #137333;border-radius:12px;padding:0 12px;font-size:13px;color:#202124;background:#fff;outline:none;transition:border-color 150ms ease,box-shadow 150ms ease}
.agoda-input::placeholder{color:#9aa0a6}
.agoda-input-card{border-color:#dadce0}
.agoda-input-card:focus{border-color:#0f62fe;box-shadow:0 0 0 2px rgba(15,98,254,0.15)}
.agoda-input-holder{border-color:#137333}
.agoda-input-holder:focus{border-color:#137333}
.agoda-input-icon{position:absolute;right:10px;top:50%;transform:translateY(-50%);color:#5f6368;font-size:13px}
.agoda-card-preview{background:#dddfe2;border-radius:10px;padding:16px;height:150px;display:flex;flex-direction:column;justify-content:space-between;color:#fff;position:relative}
.agoda-card-preview .chip{width:48px;height:32px;background:#c4c6c9;border-radius:6px;display:flex;align-items:center;justify-content:center}
.agoda-card-preview .numbers{font-size:13px;letter-spacing:2px;color:#fff;text-align:center;margin-top:8px}
.agoda-card-preview .holder{font-size:11px;letter-spacing:1px;color:#fff;display:flex;justify-content:space-between}
.agoda-secure{font-size:11px;color:#0f7a2b;display:flex;align-items:center;gap:6px;margin-top:8px}
.agoda-divider{border-top:1px solid #e8eaed}
.agoda-digital{display:flex;align-items:center;justify-content:space-between;padding:12px 16px}
.agoda-digital label{display:flex;align-items:center;gap:8px;font-size:13px;font-weight:700;color:#202124}
.agoda-digital input{width:18px;height:18px;accent-color:#5f6368}
.agoda-checkbox{padding:12px 16px;font-size:12px;color:#5f6368;line-height:1.5;display:flex;gap:8px;align-items:flex-start}
.agoda-checkbox input{width:18px;height:18px;accent-color:#0f62fe;flex:0 0 18px;margin-top:2px}
.agoda-terms{padding:0 16px 16px;font-size:12px;color:#5f6368}
.agoda-terms a{color:#0f62fe;font-weight:600;text-decoration:underline}
.agoda-hurry-red{font-size:12px;color:#c0392b;text-align:right;margin:10px 0 6px}
/* Carbon selectable-tile payment methods (White theme tokens) */
.cds-pay-field{border:none;padding:0;margin:0}
.cds-pay-legend{font-size:12px;font-weight:600;color:var(--cds-gray-100);margin:0 0 8px;padding:0}
.cds-pay-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.cds-pay-tile{position:relative;display:flex;align-items:center;gap:12px;background:var(--cds-white);border:1px solid var(--cds-border-subtle);border-radius:8px;padding:12px;cursor:pointer;transition:border-color 150ms ease,background 150ms ease,box-shadow 150ms ease;margin:0}
.cds-pay-tile:hover{border-color:var(--cds-gray-60)}
.cds-pay-tile input{position:absolute;opacity:0;pointer-events:none;margin:0}
.cds-pay-tile:focus-within{outline:2px solid var(--cds-focus);outline-offset:2px}
.cds-pay-tile:has(input:checked),.cds-pay-tile.is-selected{border-color:var(--cds-blue-60);background:var(--cds-blue-10-highlight);box-shadow:inset 0 0 0 1px var(--cds-blue-60)}
.cds-pay-check{width:20px;height:20px;border-radius:50%;border:1px solid var(--cds-gray-60);flex:0 0 20px;display:inline-flex;align-items:center;justify-content:center;color:transparent;font-size:10px;transition:background 150ms ease,border-color 150ms ease,color 150ms ease}
.cds-pay-tile:has(input:checked) .cds-pay-check,.cds-pay-tile.is-selected .cds-pay-check{background:var(--cds-blue-60);border-color:var(--cds-blue-60);color:#fff}
.cds-pay-logo{width:44px;height:28px;object-fit:contain;flex:0 0 44px}
.cds-pay-name{font-size:13px;font-weight:600;color:var(--cds-gray-100);line-height:1.25;display:block}
.cds-pay-sub{font-size:11px;color:var(--cds-gray-70);display:block;margin-top:1px}
.cds-momo-box{background:var(--cds-gray-10);border:1px solid var(--cds-border-subtle);border-radius:8px;padding:14px}
.cds-momo-title{font-size:13px;font-weight:600;color:var(--cds-gray-100);margin-bottom:8px}
.cds-momo-row{display:flex;align-items:stretch}
.cds-momo-prefix{background:var(--cds-white);border:1px solid var(--cds-gray-30);border-right:none;border-radius:8px 0 0 8px;padding:0 10px;min-height:44px;display:inline-flex;align-items:center;font-size:13px;color:var(--cds-gray-70);white-space:nowrap}
.cds-momo-prefix img{height:16px;margin-left:6px}
.cds-momo-input{border-radius:0 8px 8px 0!important;flex:1}
.cds-momo-hint{font-size:11px;color:var(--cds-gray-70);margin-top:6px}
.cds-momo-hint span{color:var(--cds-blue-60);font-weight:600}
@media(max-width:480px){.cds-pay-grid{grid-template-columns:1fr 1fr;gap:6px}.cds-pay-tile{padding:10px;gap:8px}.cds-pay-logo{width:36px;height:24px;flex-basis:36px}.cds-pay-name{font-size:12px}}
.agoda-email-note{font-size:13px;color:#202124;display:flex;align-items:center;gap:6px}
.agoda-email-note b{color:#202124}
.agoda-book-btn{background:#0f62fe;color:#fff;border:none;border-radius:30px;padding:14px 24px;font-size:15px;font-weight:800;width:100%;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;box-shadow:0 4px 12px rgba(15,98,254,0.18)}
.agoda-book-btn:hover{background:#0353e9}
.agoda-flexi{font-size:12px;color:#0f7a2b;text-align:center;font-weight:600;margin-top:6px}
/* Right */
.agoda-side-card{background:#fff;border:1px solid #e8eaed;border-radius:16px;overflow:hidden;margin-bottom:12px;box-shadow:0 4px 12px rgba(0,0,0,0.04)}
.agoda-side-head{padding:14px 16px;display:flex;justify-content:space-between;gap:8px}
.agoda-side-title{font-size:15px;font-weight:800;color:#202124;line-height:1.3}
.agoda-side-sub{font-size:12px;color:#5f6368;margin-top:4px}
.agoda-pink{ background:#fdecea;border:1px solid #f5c6cb;border-radius:8px;padding:10px 12px;font-size:12px;color:#7d2e2e;display:flex;gap:8px;align-items:center;margin:0 0 12px}
.agoda-green{ background:#e6f4ea;border:1px solid #c8e6c9;border-radius:8px;padding:10px 12px;font-size:12px;color:#202124}
.agoda-green b{color:#137333}
.agoda-price-card{padding:14px 16px}
.agoda-off-badge{background:#c0392b;color:#fff;font-size:11px;font-weight:800;padding:4px 8px;border-radius:4px;float:right}
.agoda-price-row{display:flex;justify-content:space-between;font-size:13px;color:#202124;padding:6px 0}
.agoda-price-row.strike span:last-child{text-decoration:line-through;color:#5f6368}
.agoda-price-total{display:flex;justify-content:space-between;align-items:center;padding:var(--cds-spacing-04) 0 var(--cds-spacing-03);border-top:1px solid #e8eaed;margin-top:6px}
.agoda-price-total b{font-size:16px;color:#202124}
.agoda-included{font-size:11px;color:#5f6368;border-top:1px dashed #e8eaed;padding-top:8px;margin-top:6px}
.agoda-cancel-card{padding:14px 16px}
.agoda-cancel-title{font-size:14px;font-weight:800;color:#202124;margin-bottom:8px}
.agoda-cancel-text{font-size:12px;color:#202124;line-height:1.5}
.agoda-timeline{display:flex;align-items:center;justify-content:space-between;margin-top:10px;position:relative}
.agoda-timeline:before{content:"";position:absolute;left:6px;right:6px;top:6px;height:4px;background:#e8eaed;border-radius:2px}
.agoda-timeline .fill{position:absolute;left:6px;top:6px;height:4px;background:#0f7a2b;border-radius:2px;width:45%}
.agoda-dot{width:12px;height:12px;border-radius:50%;background:#e8eaed;position:relative;z-index:1}
.agoda-dot.active{background:#0f7a2b;border:2px solid #0f7a2b}
.agoda-dot.next{background:#fff;border:2px solid #cbd5e1}
@media(max-width:992px){
  .agoda-pay-header{height:auto;padding:10px 0;margin-top:8px}
  .agoda-pay-header-inner{flex-wrap:wrap}
  .agoda-steps{order:3;max-width:none;width:100%;margin:0;justify-content:space-between}
  .agoda-pay-wrap{grid-template-columns:1fr;gap:12px;padding:0 12px}
}
@media(max-width:600px){
  .agoda-pay-header-inner{padding:0 12px}
  .agoda-steps{gap:4px}
  .agoda-step{font-size:11px;gap:4px}
  .agoda-step .num{width:18px;height:18px;font-size:10px}
  .agoda-timer-bar{font-size:12px}
  .agoda-form-grid{grid-template-columns:1fr;gap:12px}
  .agoda-card-preview{height:130px}
  .agoda-pay-head{flex-direction:column;align-items:flex-start}
  .agoda-user{display:none!important}
}
@media(max-width:768px){
  html,body{max-width:100%;overflow-x:hidden}
  .agoda-user{display:none!important}
  .agoda-pay-wrap{padding:0 8px;gap:12px}
  .agoda-card{border-radius:10px}
  .agoda-timer-bar{flex-wrap:wrap;gap:4px}
  .agoda-pay-methods{grid-template-columns:1fr!important}
  .agoda-pay-option{padding:8px 10px}
  .agoda-book-btn{padding:14px 24px;font-size:15px}
}
@media(max-width:480px){
  .agoda-pay-header-inner{padding:0 8px;gap:8px}
  .agoda-steps{gap:3px}
  .agoda-step{font-size:10px;gap:3px}
  .agoda-step .num{width:16px;height:16px;font-size:9px}
  .agoda-pay-wrap{padding:0 8px}
  .agoda-card{padding:10px}
  .agoda-card-pad{padding:12px}
  .agoda-pay-title{font-size:16px}
  .agoda-pay-methods{gap:6px}
  .agoda-pay-option{padding:8px}
  .agoda-form-grid{padding:12px}
  .agoda-field{margin-bottom:12px}
  .agoda-card-preview{height:120px;padding:12px}
  .agoda-price-card{padding:12px}
  .agoda-side-head{padding:12px}
  .agoda-pink,.agoda-green{font-size:11px;padding:8px 10px}
}
@media(max-width:375px){
  .agoda-pay-header{padding:6px 0}
  .agoda-steps{gap:2px}
  .agoda-step{font-size:9.5px}
  .agoda-timer-bar{font-size:11px;padding:6px 8px}
  .agoda-card{padding:8px}
  .agoda-book-btn{font-size:14px;padding:12px 16px}
  .agoda-input{height:38px;font-size:13px}
  .agoda-pay-option{font-size:12px}
}
</style>
<?= $this->element('navbar') ?>
<div class="agoda-checkout-stepper" style="background:#fff;border-bottom:1px solid #e8eaed;padding:14px 0;margin-top:16px;">
  <div style="max-width:1180px;margin:0 auto;padding:0 16px;display:flex;align-items:center;justify-content:center;">
    <div class="agoda-steps" style="margin:0 auto;max-width:620px;flex:1;">
      <div class="agoda-step done"><span class="num"><i class="fa-solid fa-check" style="font-size:10px"></i></span><span>Customer information</span></div>
      <div class="agoda-step-line filled"></div>
      <div class="agoda-step active"><span class="num">2</span><span>Payment information</span></div>
      <div class="agoda-step-line"></div>
      <div class="agoda-step"><span class="num">3</span><span>Booking is confirmed!</span></div>
    </div>
  </div>
</div>
<?php
// Same server-owned guarantee as step 1 — the payment step shares the quote expiry.
$payRemainingSrv = isset($quoteRemaining) ? max(0, (int)$quoteRemaining)
    : (isset($quote['expires_at']) ? max(0, (int)$quote['expires_at'] - time()) : 0);
$payHasGuarantee = !empty($quote['expires_at']) || isset($quoteRemaining);
$payFmt = sprintf('%02d:%02d:%02d', (int)($payRemainingSrv / 3600), (int)(($payRemainingSrv % 3600) / 60), $payRemainingSrv % 60);
?>
<?php if ($payHasGuarantee): ?>
<div class="agoda-timer-bar" id="agodaPayTimerBar" data-remaining="<?= (int)$payRemainingSrv ?>">
  <span id="agodaPayTimerLabel">This price is guaranteed for... <b><i class="fa-regular fa-clock"></i> <span id="agodaPayCountdown"><?= h($payFmt) ?></span></b></span>
  <span id="agodaPayTimerExpired" style="display:none">Price guarantee expired — <a href="<?= $this->Url->build('/booking-page', ['?' => array_filter(['property_id' => $propertyId, 'room_id' => $roomId, 'checkIn' => $checkIn, 'checkOut' => $checkOut, 'repriced' => '1'])]) ?>" class="agoda-link" style="font-weight:800">go back to refresh the live price</a></span>
</div>
<?php endif; ?>

<div class="agoda-pay-wrap">
  <!-- LEFT -->
  <div style="display:flex;flex-direction:column;gap:12px">
    <div class="agoda-card">
      <div class="agoda-card-pad" style="padding-bottom:0">
        <div class="agoda-pay-title">Payment method</div>
        <div class="agoda-pay-sub"><i class="fa-solid fa-shield-halved"></i> All payment data is encrypted and secure</div>
      </div>
      <form id="agodaPaymentForm" action="<?= $this->Url->build('/bookingpage-02') ?>" method="POST" style="display:block">
        <input type="hidden" name="_csrfToken" value="<?= $this->request->getAttribute('csrfToken') ?>">
        <input type="hidden" name="property_id" value="<?= h($propertyId) ?>">
        <input type="hidden" name="room_id" value="<?= h($roomId) ?>">
        <input type="hidden" name="quote_id" value="<?= h($quoteId) ?>">
        <input type="hidden" name="check_in" value="<?= h($checkIn) ?>">
        <input type="hidden" name="check_out" value="<?= h($checkOut) ?>">
        <!-- Guest info forwarded from Customer Information step -->
        <input type="hidden" name="first_name" value="<?= h($queryParams['first_name'] ?? '') ?>">
        <input type="hidden" name="last_name" value="<?= h($queryParams['last_name'] ?? '') ?>">
        <input type="hidden" name="email" value="<?= h($queryParams['email'] ?? $guestEmail) ?>">
        <input type="hidden" name="phone" value="<?= h($queryParams['phone'] ?? '') ?>">
        <input type="hidden" name="special_requests" value="<?= h($queryParams['special_notes'] ?? $queryParams['special_requests'] ?? '') ?>">
        <input type="hidden" name="room_preference" value="<?= h($queryParams['pref_room_type'] ?? '') ?>">
        <input type="hidden" name="bed_preference" value="<?= h($queryParams['pref_bed'] ?? '') ?>">

        <!-- Unified payment methods — Carbon selectable tiles, one clean label each.
             Card payments are not offered: there is no card gateway, so a card
             option would collect PAN/CVC details that go nowhere. -->
        <fieldset class="cds-pay-field" style="padding:12px 16px">
          <legend class="cds-pay-legend">Select payment method</legend>
          <div class="cds-pay-grid">
            <label class="cds-pay-tile agoda-pay-option" data-method="vodacom">
              <input type="radio" name="payment_method" value="vodacom" checked>
              <span class="cds-pay-check" aria-hidden="true"><i class="fa-solid fa-check"></i></span>
              <img class="cds-pay-logo" src="<?= $this->Url->build('/assets/img/vodacom-logo.png') ?>" alt="" onerror="this.style.display='none'">
              <span><span class="cds-pay-name">M-Pesa</span><span class="cds-pay-sub">Vodacom</span></span>
            </label>
            <label class="cds-pay-tile agoda-pay-option" data-method="tigo">
              <input type="radio" name="payment_method" value="tigo">
              <span class="cds-pay-check" aria-hidden="true"><i class="fa-solid fa-check"></i></span>
              <img class="cds-pay-logo" src="<?= $this->Url->build('/assets/img/tigo-pesa-logo.jpg') ?>" alt="" onerror="this.style.display='none'">
              <span><span class="cds-pay-name">Tigo Pesa</span><span class="cds-pay-sub">Tigo</span></span>
            </label>
            <label class="cds-pay-tile agoda-pay-option" data-method="airtel">
              <input type="radio" name="payment_method" value="airtel">
              <span class="cds-pay-check" aria-hidden="true"><i class="fa-solid fa-check"></i></span>
              <img class="cds-pay-logo" src="<?= $this->Url->build('/assets/img/airtel-logo.png') ?>" alt="" onerror="this.style.display='none'">
              <span><span class="cds-pay-name">Airtel Money</span><span class="cds-pay-sub">Airtel</span></span>
            </label>
            <label class="cds-pay-tile agoda-pay-option" data-method="halotel">
              <input type="radio" name="payment_method" value="halotel">
              <span class="cds-pay-check" aria-hidden="true"><i class="fa-solid fa-check"></i></span>
              <img class="cds-pay-logo" src="<?= $this->Url->build('/assets/img/halotel-logo.jpg') ?>" alt="" onerror="this.style.display='none'">
              <span><span class="cds-pay-name">HaloPesa</span><span class="cds-pay-sub">Halotel</span></span>
            </label>
          </div>
        </fieldset>

        <div class="agoda-green-tip">Last step! You're almost done.</div>

        <!-- Local mobile money fields -->
        <div id="momoFormSection" style="padding:16px">
          <div class="cds-momo-box">
            <div class="cds-momo-title"><span id="momoTitle">Mobile money number</span></div>
            <div class="cds-momo-row">
              <span class="cds-momo-prefix"><span id="momoPrefix">+255</span> <img id="momoLogo" src="" alt="" style="display:none" onerror="this.style.display='none'"></span>
              <input class="agoda-input cds-momo-input" name="payment_phone" placeholder="712 345 678" inputmode="tel" autocomplete="tel">
            </div>
            <div class="cds-momo-hint">You'll receive a USSD push on your phone to approve the payment. <span id="momoHint"></span></div>
          </div>
        </div>
        <div style="display:none"><button type="submit" id="hiddenSubmit">submit</button></div>
      </form>

      <div class="agoda-divider"></div>
      <label class="agoda-checkbox"><input type="checkbox" checked> I agree to receive updates and promotions about FastNet Stays via various channels, including WhatsApp. Opt out anytime. Read more in the Privacy Policy.</label>
      <div class="agoda-terms">By proceeding with this booking, I agree to FastNet Stays' <a href="/terms-of-service">Terms of Use</a> and <a href="/privacy-policy">Privacy Policy</a>.</div>
    </div>

    <div class="agoda-email-note"><i class="fa-solid fa-envelope" style="color:#5f6368"></i> We'll send confirmation of your booking to <b><?= h($guestEmail) ?></b></div>

    <div class="agoda-card" style="padding:14px">
      <button type="button" class="agoda-book-btn" onclick="document.getElementById('agodaPaymentForm').requestSubmit()"><i class="fa-solid fa-lock"></i> BOOK NOW!</button>
      <div class="agoda-flexi">Stay flexible! Cancel for free</div>
    </div>
  </div>

  <!-- RIGHT -->
  <div style="display:flex;flex-direction:column;gap:12px">
    <div class="agoda-side-card">
      <div class="agoda-side-head" style="cursor:pointer" onclick="this.nextElementSibling.style.display=this.nextElementSibling.style.display==='none'?'block':'none'">
        <div>
          <div class="agoda-side-title"><?= h($propTitle) ?></div>
          <div class="agoda-side-sub">Mon, Oct 12 - Sun, Oct 18 • <?= h($nights) ?> nights • 1 x <?= h($roomTitle) ?></div>
        </div>
        <i class="fa-solid fa-chevron-down" style="font-size:12px;color:#5f6368"></i>
      </div>
    </div>

    <?php if ($saved > 0): ?>
    <div class="agoda-green">
      <div style="display:flex;gap:6px;align-items:center"><i class="fa-solid fa-circle-check" style="color:#137333"></i><span>You saved <b>TSh <?= number_format($saved) ?></b> on this booking!</span></div>
    </div>
    <?php endif; ?>

    <div class="agoda-side-card">
      <div class="agoda-price-card">
        <?php if ($offPercent > 0): ?><span class="agoda-off-badge"><?= $offPercent ?>% OFF TODAY</span><?php endif; ?>
        <div style="clear:both"></div>
        <?php if ($saved > 0): ?><div class="agoda-price-row strike"><span>Original price (<?= h($roomsCount) ?> room<?= $roomsCount!==1?'s':'' ?> x <?= h($nights) ?> nights)</span><span>TSh <?= number_format($origPrice) ?></span></div><?php endif; ?>
        <div class="agoda-price-row"><span>Room price (<?= h($roomsCount) ?> room<?= $roomsCount!==1?'s':'' ?> x <?= h($nights) ?> nights)</span><span>TSh <?= number_format($roomPrice) ?></span></div>
        <div class="agoda-price-row"><span>Mobile-money fee (1%)</span><span>TSh <?= number_format($azampayFee) ?></span></div>
        <div class="agoda-price-row"><span>Taxes</span><span>TSh <?= number_format($baseTaxes) ?></span></div>
        <div class="agoda-price-row"><span style="color:#0f7a2b">Booking fees</span><span style="color:#0f7a2b">FREE</span></div>
        <div class="agoda-price-total"><span style="font-size:13px;color:#202124;display:flex;align-items:center;gap:4px">Price <i class="fa-regular fa-circle-question" style="font-size:11px;color:#5f6368"></i></span><b>TSh <?= number_format($total) ?></b></div>
        <div class="agoda-included">Includes 1% AzamPay processing fee · No VAT charged</div>
      </div>
    </div>

     <div class="agoda-side-card">
      <div class="agoda-cancel-card">
        <div class="agoda-cancel-title">How much will it cost to cancel?</div>
        <div class="agoda-cancel-text"><span style="color:#0f7a2b">Stay flexible!</span> <?= h($calculation['cancellation_policy'] ?? $quote['calculation']['cancellation_policy'] ?? 'Cancel for free before ' . date('j M Y', strtotime($checkIn))) ?>. Quickly edit your booking online - no added cost! <a href="#" style="color:#0f62fe;font-weight:700">See more details</a></div>
        <div class="agoda-timeline">
          <div class="fill"></div>
          <div class="agoda-dot active"></div>
          <div class="agoda-dot next"></div>
          <div class="agoda-dot next"></div>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:11px;color:#5f6368;margin-top:4px"><span>Today</span><span><?= h(date('j M', strtotime($checkIn))) ?></span><span>Arrival</span></div>
      </div>
    </div>
  </div>
</div>

<script>
// Real countdown, same guarantee as step 1. On expiry BOOK NOW is blocked and the
// guest is sent back to step 1 for an honest re-price (backend enforces this too).
(function(){
  const bar = document.getElementById('agodaPayTimerBar');
  if (!bar) return;
  const initial = Math.max(0, parseInt(bar.dataset.remaining || '0', 10));
  const loadedAt = Date.now();
  const el = document.getElementById('agodaPayCountdown');
  const label = document.getElementById('agodaPayTimerLabel');
  const expired = document.getElementById('agodaPayTimerExpired');
  let payExpired = initial <= 0;
  window.agodaPayExpired = () => payExpired;
  function fmt(s){
    s = Math.max(0, s);
    return String(Math.floor(s/3600)).padStart(2,'0')+':'+String(Math.floor((s%3600)/60)).padStart(2,'0')+':'+String(s%60).padStart(2,'0');
  }
  function onExpire(){
    payExpired = true;
    if (el) el.textContent = '00:00:00';
    if (label) label.style.display = 'none';
    if (expired) expired.style.display = 'inline';
    bar.style.background = '#fdecea';
    bar.style.borderBottomColor = '#f5c6cb';
    document.querySelectorAll('.agoda-book-btn').forEach(b => {
      b.disabled = true; b.style.opacity = '0.6'; b.style.cursor = 'not-allowed';
      b.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> PRICE EXPIRED';
    });
  }
  function tick(){
    const left = initial - Math.floor((Date.now() - loadedAt) / 1000);
    if (left <= 0) { onExpire(); return; }
    if (el) el.textContent = fmt(left);
    setTimeout(tick, 1000);
  }
  if (initial <= 0) { onExpire(); return; }
  tick();
})();
function updatePayUI(){
  const sel=document.querySelector('[name=payment_method]:checked')?.value || 'vodacom';
  const momoSec=document.getElementById('momoFormSection');
  if(momoSec) momoSec.style.display='block';
  const phoneInput=document.querySelector('[name=payment_phone]');
  if(phoneInput) phoneInput.required=true;
  // highlight selected option (class toggle; :has() in CSS covers no-JS,
  // the class covers older browsers without :has support)
  document.querySelectorAll('.cds-pay-tile').forEach(l=>{
    l.classList.toggle('is-selected', l.querySelector('input')?.value===sel);
  });
  // update momo hint/logo
  const titles={vodacom:'M-Pesa (Vodacom)',tigo:'Tigo Pesa',airtel:'Airtel Money',halotel:'HaloPesa'};
    const logos={
      vodacom:'<?= $this->Url->build('/assets/img/vodacom-logo.png') ?>',
      tigo:'<?= $this->Url->build('/assets/img/tigo-pesa-logo.jpg') ?>',
      airtel:'<?= $this->Url->build('/assets/img/airtel-logo.png') ?>',
      halotel:'<?= $this->Url->build('/assets/img/halotel-logo.jpg') ?>'
    };
    const t=document.getElementById('momoTitle');
    const h=document.getElementById('momoHint');
    const lg=document.getElementById('momoLogo');
    if(t) t.textContent='Pay with ' + (titles[sel]||'Mobile money');
    if(h) h.textContent='You selected ' + (titles[sel]||sel) + ' — you will receive a USSD push.';
    if(lg && logos[sel]){lg.src=logos[sel]; lg.style.display='inline-block';}
}
document.querySelectorAll('[name=payment_method]').forEach(r=>{r.addEventListener('change',updatePayUI)});
document.addEventListener('DOMContentLoaded',updatePayUI);
document.getElementById('agodaPaymentForm')?.addEventListener('submit',function(e){
  if(window.agodaPayExpired && window.agodaPayExpired()){
    e.preventDefault();
    alert('This price guarantee has expired. Please go back to refresh the live price.');
    return;
  }
  const payMethod=this.querySelector('[name=payment_method]:checked')?.value || 'vodacom';
  const phone=this.querySelector('[name=payment_phone]');
  if(!phone.value.trim()){
    e.preventDefault();
    alert('Please enter your mobile money number for ' + payMethod);
    phone.focus();
    return;
  }
});
</script>
