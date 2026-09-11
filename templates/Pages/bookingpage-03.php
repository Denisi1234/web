<?php
$this->assign('title', 'Payment information | fastnetstays.com');
$propTitle = $property['name'] ?? 'Divi Village Golf and Beach Resort';
$propCity = $property['city'] ?? 'Oranjestad';
$propAddress = $property['address'] ?? 'J.E. Irausquin Blvd 93, Oranjestad, Aruba';
$propStars = !empty($property['star_rating']) ? max(1,min(5,(int)$property['star_rating'])) : 4;
$checkIn = $queryParams['checkIn'] ?? $queryParams['check_in'] ?? date('Y-m-d', strtotime('+7 days'));
$checkOut = $queryParams['checkOut'] ?? $queryParams['check_out'] ?? date('Y-m-d', strtotime('+13 days'));
$nights = max(1, (int)round((strtotime($checkOut)-strtotime($checkIn))/86400));
if ($nights <1) $nights=6;
$roomTitle = $room['name'] ?? 'Golf Villa One Bedroom Suite';
$origPrice = $calculation['original_price'] ?? 5041.00;
$roomPrice = $calculation['subtotal'] ?? $calculation['room_price'] ?? 1662.49;
$taxes = $calculation['taxes'] ?? 515.75;
$bookingFees = $calculation['booking_fees'] ?? 0;
$total = $calculation['total_amount'] ?? 2178.24;
$quoteId = $quote['quote_id'] ?? $queryParams['quote_id'] ?? '';
$propertyId = $property['id'] ?? $queryParams['property_id'] ?? 0;
$roomId = $room['id'] ?? $queryParams['room_id'] ?? 0;
$guestEmail = $queryParams['guest_email'] ?? $queryParams['email'] ?? 'dm328432@gmail.com';
if ($guestEmail==='') $guestEmail='dm328432@gmail.com';
$holderName = trim(($queryParams['first_name'] ?? 'Mudrick') . ' ' . ($queryParams['last_name'] ?? 'Mahenge'));
if (trim($holderName)==='') $holderName='Mudrick  Mahenge';
?>
<style>
/* Agoda Payment — step 2 — matches screenshot */
.agoda-pay-header{background:#fff;border-bottom:1px solid #e8eaed;height:64px;display:flex;align-items:center;position:sticky;top:0;z-index:100}
.agoda-pay-header-inner{max-width:1180px;margin:0 auto;padding:0 16px;width:100%;display:flex;align-items:center;justify-content:space-between;gap:16px}
.agoda-logo{font-size:22px;font-weight:900;letter-spacing:-0.02em;display:flex;align-items:center;gap:4px;text-decoration:none!important;color:#202124}
.agoda-logo .dot{width:10px;height:10px;border-radius:50%;display:inline-block}
.agoda-steps{display:flex;align-items:center;gap:0;flex:1;max-width:620px;margin:0 24px}
.agoda-step{display:flex;align-items:center;gap:8px;font-size:13px;font-weight:600;white-space:nowrap}
.agoda-step .num{width:22px;height:22px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;border:1px solid #dadce0;background:#fff;color:#5f6368}
.agoda-step.done .num{background:#1a73e8;color:#fff;border-color:#1a73e8}
.agoda-step.active .num{background:#1a73e8;color:#fff;border-color:#1a73e8}
.agoda-step span{color:#5f6368}
.agoda-step.active span{color:#1a73e8}
.agoda-step.done span{color:#1a73e8}
.agoda-step-line{flex:1;height:2px;background:#e8eaed;margin:0 8px;border-radius:1px}
.agoda-step-line.filled{background:#1a73e8}
.agoda-timer-bar{background:#fef3e8;border-bottom:1px solid #fde8cc;padding:10px 16px;text-align:center;font-size:13px;color:#202124;display:flex;align-items:center;justify-content:center;gap:8px}
.agoda-timer-bar b{color:#e53935;font-weight:700;display:flex;align-items:center;gap:6px}
.agoda-pay-wrap{max-width:1180px;margin:14px auto;padding:0 16px;display:grid;grid-template-columns:1fr 360px;gap:16px;align-items:start}
.agoda-card{background:#fff;border:1px solid #e0e6ef;border-radius:10px;overflow:hidden}
.agoda-card-pad{padding:16px}
.agoda-pay-title{font-size:18px;font-weight:800;color:#202124;margin:0}
.agoda-pay-sub{font-size:12px;color:#1a73e8;display:flex;align-items:center;gap:4px;margin-top:4px}
.agoda-pay-radio{display:flex;align-items:center;gap:8px;font-size:14px;font-weight:700;color:#1a73e8}
.agoda-pay-radio input{width:18px;height:18px;accent-color:#1a73e8}
.agoda-card-icons{display:flex;gap:6px;align-items:center;flex-wrap:wrap;justify-content:flex-end}
.agoda-card-icons img{height:24px;border:1px solid #e8eaed;border-radius:4px;background:#fff;padding:2px 4px;object-fit:contain}
.agoda-pay-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:16px 16px 12px;border-bottom:none}
.agoda-green-tip{background:#e6f4ea;color:#137333;font-size:12px;font-weight:600;text-align:center;padding:8px 12px;border-radius:0;position:relative}
.agoda-green-tip:after{content:"";position:absolute;left:50%;bottom:-6px;transform:translateX(-50%);width:0;height:0;border-left:6px solid transparent;border-right:6px solid transparent;border-top:6px solid #e6f4ea}
.agoda-form-grid{display:grid;grid-template-columns:1fr 260px;gap:16px;padding:16px}
.agoda-field{position:relative;margin-bottom:14px}
.agoda-field label{position:absolute;top:-8px;left:10px;background:#fff;padding:0 6px;font-size:11px;color:#137333;font-weight:600}
.agoda-field label.req{color:#137333}
.agoda-input{width:100%;height:42px;border:1px solid #137333;border-radius:8px;padding:0 12px;font-size:13px;color:#202124;background:#fff;outline:none}
.agoda-input::placeholder{color:#9aa0a6}
.agoda-input-card{border-color:#dadce0}
.agoda-input-card:focus{border-color:#1a73e8;box-shadow:0 0 0 2px rgba(26,115,232,0.15)}
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
.agoda-checkbox input{width:18px;height:18px;accent-color:#1a73e8;flex:0 0 18px;margin-top:2px}
.agoda-terms{padding:0 16px 16px;font-size:12px;color:#5f6368}
.agoda-terms a{color:#3264ff;font-weight:600;text-decoration:underline}
.agoda-hurry-red{font-size:12px;color:#c0392b;text-align:right;margin:10px 0 6px}
.agoda-email-note{font-size:13px;color:#202124;display:flex;align-items:center;gap:6px}
.agoda-email-note b{color:#202124}
.agoda-book-btn{background:#1a73e8;color:#fff;border:none;border-radius:24px;padding:12px 24px;font-size:14px;font-weight:800;width:100%;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px}
.agoda-book-btn:hover{background:#1557b0}
.agoda-flexi{font-size:12px;color:#0f7a2b;text-align:center;font-weight:600;margin-top:6px}
/* Right */
.agoda-side-card{background:#fff;border:1px solid #e0e6ef;border-radius:10px;overflow:hidden;margin-bottom:12px}
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
.agoda-price-total{display:flex;justify-content:space-between;align-items:center;padding:12px 0 6px;border-top:1px solid #e8eaed;margin-top:6px}
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
  .agoda-pay-header{height:auto;padding:10px 0}
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
}
@media(max-width:768px){
  html,body{max-width:100%;overflow-x:hidden}
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
<header class="agoda-pay-header">
  <div class="agoda-pay-header-inner" style="justify-content:center">
    <div class="agoda-steps" style="margin:0 auto">
      <div class="agoda-step done"><span class="num"><i class="fa-solid fa-check" style="font-size:10px"></i></span><span>Customer information</span></div>
      <div class="agoda-step-line filled"></div>
      <div class="agoda-step active"><span class="num">2</span><span>Payment information</span></div>
      <div class="agoda-step-line"></div>
      <div class="agoda-step"><span class="num">3</span><span>Booking is confirmed!</span></div>
    </div>
    <div class="agoda-user" style="margin-left:auto;display:flex;align-items:center;gap:8px;white-space:nowrap;font-size:13px;color:#202124"><span class="avatar" style="width:32px;height:32px;border-radius:50%;background:#7c6af0;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px">M</span> Mudrick M. <i class="fa-solid fa-caret-down" style="font-size:10px;color:#5f6368"></i></div>
  </div>
</header>
<div class="agoda-timer-bar">This price is guaranteed for... <b><i class="fa-regular fa-clock"></i> <span id="agodaPayCountdown">00:15:32</span></b></div>

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

        <!-- Unified payment methods — all in one section -->
        <div style="padding:12px 16px">
          <div style="font-size:12px;font-weight:800;color:#202124;margin-bottom:10px">Select payment method</div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
            <label class="agoda-pay-option" data-method="card" style="border:2px solid #1a73e8;background:#eef3ff;border-radius:8px;padding:10px 10px;display:flex;align-items:center;gap:8px;cursor:pointer">
              <input type="radio" name="pay_method" value="card" checked style="accent-color:#1a73e8"><span style="font-size:13px;font-weight:700;color:#1a73e8">Credit/debit card</span>
              <span style="margin-left:auto;display:flex;gap:4px;align-items:center">
                <img src="<?= $this->Url->build('/assets/img/visa-logo.png') ?>" alt="VISA" style="height:18px;border:1px solid #e8eaed;border-radius:3px;background:#fff;padding:1px 3px">
                <img src="<?= $this->Url->build('/assets/img/mastercard-logo.png') ?>" alt="Mcard" style="height:18px;border:1px solid #e8eaed;border-radius:3px;background:#fff;padding:1px 3px">
              </span>
            </label>
            <label class="agoda-pay-option" data-method="vodacom" style="border:1px solid #e8eaed;background:#fff;border-radius:8px;padding:10px 10px;display:flex;align-items:center;gap:8px;cursor:pointer">
              <input type="radio" name="pay_method" value="vodacom" style="accent-color:#e60000"><img src="<?= $this->Url->build('/assets/img/vodacom-logo.png') ?>" alt="M-Pesa" style="height:18px;max-width:48px;object-fit:contain"><span style="font-size:12px;font-weight:700;color:#202124">M-Pesa</span>
            </label>
            <label class="agoda-pay-option" data-method="tigo" style="border:1px solid #e8eaed;background:#fff;border-radius:8px;padding:10px 10px;display:flex;align-items:center;gap:8px;cursor:pointer">
              <input type="radio" name="pay_method" value="tigo" style="accent-color:#0033a0"><img src="<?= $this->Url->build('/assets/img/tigo-pesa-logo.jpg') ?>" alt="Tigo" style="height:18px;max-width:48px;object-fit:contain"><span style="font-size:12px;font-weight:700;color:#202124">Tigo Pesa</span>
            </label>
            <label class="agoda-pay-option" data-method="airtel" style="border:1px solid #e8eaed;background:#fff;border-radius:8px;padding:10px 10px;display:flex;align-items:center;gap:8px;cursor:pointer">
              <input type="radio" name="pay_method" value="airtel" style="accent-color:#ff0000"><img src="<?= $this->Url->build('/assets/img/airtel-logo.png') ?>" alt="Airtel" style="height:18px;max-width:48px;object-fit:contain"><span style="font-size:12px;font-weight:700;color:#202124">Airtel Money</span>
            </label>
            <label class="agoda-pay-option" data-method="halotel" style="border:1px solid #e8eaed;background:#fff;border-radius:8px;padding:10px 10px;display:flex;align-items:center;gap:8px;cursor:pointer;grid-column:span 2;justify-content:center">
              <input type="radio" name="pay_method" value="halotel" style="accent-color:#ff6600"><img src="<?= $this->Url->build('/assets/img/halotel-logo.jpg') ?>" alt="Halo" style="height:18px;max-width:48px;object-fit:contain"><span style="font-size:12px;font-weight:700;color:#202124">HaloPesa</span>
            </label>
          </div>
        </div>

        <div class="agoda-green-tip">Last step! You're almost done.</div>

        <!-- Card fields — appears only when card selected -->
        <div id="cardFormSection" class="agoda-form-grid">
          <div>
            <div class="agoda-field">
              <label class="req">Card holder name *</label>
              <input class="agoda-input agoda-input-holder" name="card_holder" value="<?= h($holderName) ?>" required>
            </div>
            <div class="agoda-field">
              <label>Credit/debit card number *</label>
              <input class="agoda-input agoda-input-card" name="card_number" placeholder="Card Number" inputmode="numeric" autocomplete="cc-number" required>
              <span class="agoda-input-icon"><i class="fa-solid fa-lock"></i></span>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
              <div class="agoda-field">
                <label>Expiry date *</label>
                <input class="agoda-input agoda-input-card" name="expiry" placeholder="MM/YY" inputmode="numeric" autocomplete="cc-exp" required>
              </div>
              <div class="agoda-field">
                <label>CVC/CVV *</label>
                <input class="agoda-input agoda-input-card" name="cvc" placeholder="CVC/CVV" inputmode="numeric" autocomplete="cc-csc" required>
              </div>
            </div>
          </div>
          <div>
            <div class="agoda-card-preview">
              <div class="chip"><i class="fa-solid fa-credit-card" style="color:#b0b3b8"></i></div>
              <div class="numbers">**** &nbsp; **** &nbsp; **** &nbsp; ****</div>
              <div class="holder"><span><?= h(strtoupper($holderName)) ?></span><span>**/****</span></div>
            </div>
            <div class="agoda-secure"><i class="fa-solid fa-shield-halved"></i> All card information is fully encrypted and secured</div>
          </div>
        </div>

        <!-- Local mobile money fields — appears when any momo selected -->
        <div id="momoFormSection" style="display:none;padding:16px">
          <div style="background:#f8f9fa;border:1px solid #e8eaed;border-radius:8px;padding:14px">
            <div style="font-size:13px;font-weight:700;color:#202124;margin-bottom:8px"><span id="momoTitle">Mobile money number</span></div>
            <div style="display:flex;align-items:center">
              <span style="background:#fff;border:1px solid #dadce0;border-right:none;border-radius:6px 0 0 6px;padding:0 10px;height:42px;display:flex;align-items:center;font-size:13px;color:#5f6368;white-space:nowrap"><span id="momoPrefix">+255</span> <img id="momoLogo" src="" alt="" style="height:16px;margin-left:6px;display:none"></span>
              <input class="agoda-input" name="payment_phone" placeholder="712 345 678" style="border-radius:0 6px 6px 0;flex:1" inputmode="tel">
            </div>
            <div style="font-size:11px;color:#5f6368;margin-top:6px">You'll receive a USSD push on your phone to approve the payment. <span id="momoHint" style="color:#1a73e8;font-weight:600"></span></div>
          </div>
        </div>
        <div style="display:none"><button type="submit" id="hiddenSubmit">submit</button></div>
      </form>

      <div class="agoda-divider"></div>
      <label class="agoda-checkbox"><input type="checkbox" checked> I agree to receive updates and promotions about Agoda and its affiliates or business partners via various channels, including WhatsApp. Opt out anytime. Read more in the Privacy Policy.</label>
      <div class="agoda-terms">By proceeding with this booking, I agree to Agoda's <a href="#">Terms of Use</a> and <a href="#">Privacy Policy</a>.</div>
    </div>

    <div class="agoda-hurry-red">Hurry! Our last room for your dates at this price</div>
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

    <div class="agoda-pink"><i class="fa-solid fa-bell" style="color:#c0392b"></i> Hurry! Our last room for your dates at this price</div>

    <div class="agoda-green">
      <div style="display:flex;gap:6px;align-items:center"><b>We price match.</b> Find it for less, and we'll match it! <i class="fa-regular fa-circle-question" style="color:#137333"></i></div>
      <div style="margin-top:6px;color:#137333;font-weight:700">You saved USD 3,378.51 on this booking!</div>
    </div>

    <div class="agoda-side-card">
      <div class="agoda-price-card">
        <span class="agoda-off-badge">67% OFF TODAY</span>
        <div style="clear:both"></div>
        <div class="agoda-price-row strike"><span>Original price (1 room x <?= h($nights) ?> nights)</span><span>USD <?= number_format($origPrice,2) ?></span></div>
        <div class="agoda-price-row"><span>Room price (1 room x <?= h($nights) ?> nights)</span><span>USD <?= number_format($roomPrice,2) ?></span></div>
        <div class="agoda-price-row"><span>Taxes and fees</span><span>USD <?= number_format($taxes,2) ?></span></div>
        <div class="agoda-price-row"><span style="color:#0f7a2b">Booking fees</span><span style="color:#0f7a2b">FREE</span></div>
        <div class="agoda-price-total"><span style="font-size:13px;color:#202124;display:flex;align-items:center;gap:4px">Price <i class="fa-regular fa-circle-question" style="font-size:11px;color:#5f6368"></i></span><b>USD <?= number_format($total,2) ?></b></div>
        <div class="agoda-included">Included in price: Tax USD 497.75, Hotel tax and service fees USD 18.00</div>
      </div>
    </div>

    <div class="agoda-side-card">
      <div class="agoda-cancel-card">
        <div class="agoda-cancel-title">How much will it cost to cancel?</div>
        <div class="agoda-cancel-text"><span style="color:#0f7a2b">Stay flexible!</span> Cancel for free before 12 Sep 2026. Quickly edit your booking online - no added cost! <a href="#" style="color:#3264ff;font-weight:700">See more details</a></div>
        <div class="agoda-timeline">
          <div class="fill"></div>
          <div class="agoda-dot active"></div>
          <div class="agoda-dot next"></div>
          <div class="agoda-dot next"></div>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:11px;color:#5f6368;margin-top:4px"><span>Today</span><span>12 Sep</span><span>Arrival</span></div>
      </div>
    </div>
  </div>
</div>

<script>
let paySeconds = 15*60+32;
function tickPay(){
  const el=document.getElementById('agodaPayCountdown');
  if(!el) return;
  let s=paySeconds--;
  if(s<0){el.textContent='00:00:00';return}
  const h=String(Math.floor(s/3600)).padStart(2,'0');
  const m=String(Math.floor((s%3600)/60)).padStart(2,'0');
  const sec=String(s%60).padStart(2,'0');
  el.textContent=h+':'+m+':'+sec;
  setTimeout(tickPay,1000);
}
tickPay();
function updatePayUI(){
  const sel=document.querySelector('[name=pay_method]:checked')?.value || 'card';
  const cardSec=document.getElementById('cardFormSection');
  const momoSec=document.getElementById('momoFormSection');
  const isCard=sel==='card';
  if(cardSec) cardSec.style.display=isCard?'grid':'none';
  if(momoSec) momoSec.style.display=isCard?'none':'block';
  document.querySelectorAll('[name=card_holder],[name=card_number],[name=expiry],[name=cvc]').forEach(i=>{i.required=isCard});
  const phoneInput=document.querySelector('[name=payment_phone]');
  if(phoneInput) phoneInput.required=!isCard;
  // highlight selected option
  document.querySelectorAll('.agoda-pay-option').forEach(l=>{
    const isSel=l.querySelector('input')?.value===sel;
    l.style.border=isSel?'2px solid #1a73e8':'1px solid #e8eaed';
    l.style.background=isSel?'#eef3ff':'#fff';
  });
  // update momo hint/logo
  if(!isCard){
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
}
document.querySelectorAll('[name=pay_method]').forEach(r=>{r.addEventListener('change',updatePayUI)});
document.addEventListener('DOMContentLoaded',updatePayUI);
document.getElementById('agodaPaymentForm')?.addEventListener('submit',function(e){
  const payMethod=this.querySelector('[name=pay_method]:checked')?.value || 'card';
  if(payMethod==='card'){
    const holder=this.querySelector('[name=card_holder]');
    const num=this.querySelector('[name=card_number]');
    const exp=this.querySelector('[name=expiry]');
    const cvc=this.querySelector('[name=cvc]');
    if(!holder.value.trim()||!num.value.trim()||!exp.value.trim()||!cvc.value.trim()){
      e.preventDefault();
      alert('Please fill card details');
      return;
    }
  } else {
    const phone=this.querySelector('[name=payment_phone]');
    if(!phone.value.trim()){
      e.preventDefault();
      alert('Please enter your mobile money number for ' + payMethod);
      phone.focus();
      return;
    }
    // For backend mobile money, ensure card fields not sent as empty required
    // payment_method already is vodacom/tigo/etc via radio value
  }
});
</script>
