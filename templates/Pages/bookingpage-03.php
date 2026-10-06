<?php
$this->assign('title', 'Payment information');
// No invented fallbacks: missing backend data renders neutral labels or hides
// the section — never a real-sounding hotel, address, or night count.
$propTitle = trim((string)($property['name'] ?? '')) !== '' ? $property['name'] : 'Your stay';
$propCity = $property['city'] ?? '';
$propAddress = trim((string)($property['address'] ?? ''));
$propStars = !empty($property['star_rating']) ? max(1,min(5,(int)$property['star_rating'])) : 0;
$checkIn = $queryParams['checkIn'] ?? $queryParams['check_in'] ?? '';
$checkOut = $queryParams['checkOut'] ?? $queryParams['check_out'] ?? '';
$ciTs = strtotime((string)$checkIn); $coTs = strtotime((string)$checkOut);
$hasDates = $ciTs && $coTs && $coTs > $ciTs;
$nights = $hasDates ? max(1, (int)round(($coTs - $ciTs) / 86400)) : 1;
$roomTitle = trim((string)($room['name'] ?? '')) !== '' ? $room['name'] : 'Selected room';
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
<?= $this->Html->css('/assets/css/bookingpage-03.css') ?>
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
      <div class="agoda-terms">By paying, you agree to FastNet Stays' <a href="/terms-of-service">Terms of Use</a> and <a href="/privacy-policy">Privacy Policy</a>.</div>
    </div>

    <div class="agoda-email-note"><i class="fa-solid fa-envelope" style="color:#5f6368"></i> We'll send confirmation of your booking to <b><?= h($guestEmail) ?></b></div>

    <?php $cancelPolicy03 = trim((string)($calculation['cancellation_policy'] ?? ($quote['calculation']['cancellation_policy'] ?? ''))); ?>
    <div class="agoda-card" style="padding:14px">
      <div id="payFormError" style="display:none;background:#fdecea;border:1px solid #f5c6cb;color:#7d2e2e;padding:8px 10px;border-radius:6px;font-size:12px;margin-bottom:10px"></div>
      <button type="button" class="agoda-book-btn" onclick="document.getElementById('agodaPaymentForm').requestSubmit()"><i class="fa-solid fa-lock"></i> <?= $total > 0 ? 'Pay TSh ' . number_format($total) : 'Pay now' ?></button>
      <?php if ($cancelPolicy03 !== ''): ?>
      <div class="agoda-flexi"><?= h($cancelPolicy03) ?></div>
      <?php endif; ?>
    </div>
  </div>

  <!-- RIGHT -->
  <div style="display:flex;flex-direction:column;gap:12px">
    <div class="agoda-side-card">
      <div class="agoda-side-head" style="cursor:pointer" onclick="this.nextElementSibling.style.display=this.nextElementSibling.style.display==='none'?'block':'none'">
        <div>
          <div class="agoda-side-title"><?= h($propTitle) ?></div>
          <div class="agoda-side-sub"><?php if ($hasDates): ?><?= h(date('D, M j', $ciTs)) ?> - <?= h(date('D, M j', $coTs)) ?> • <?php endif; ?><?= h($nights) ?> night<?= $nights !== 1 ? 's' : '' ?> • <?= h($roomTitle) ?></div>
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

     <?php $cancelCard03 = trim((string)($calculation['cancellation_policy'] ?? ($quote['calculation']['cancellation_policy'] ?? ''))); ?>
     <?php if ($cancelCard03 !== ''): ?>
     <div class="agoda-side-card">
      <div class="agoda-cancel-card">
        <div class="agoda-cancel-title">Cancellation</div>
        <div class="agoda-cancel-text"><?= h($cancelCard03) ?></div>
      </div>
    </div>
     <?php endif; ?>
  </div>
</div>

<script>
// Real countdown, same guarantee as step 1. On expiry the pay button is blocked and
// the guest is sent back to step 1 for an honest re-price (backend enforces this too).
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
  const errBox=document.getElementById('payFormError');
  const fail=function(msg, focusEl){
    e.preventDefault();
    if(errBox){ errBox.style.display='block'; errBox.textContent=msg; }
    if(focusEl && focusEl.focus) focusEl.focus();
    errBox?.scrollIntoView({behavior:'smooth', block:'center'});
  };
  if(window.agodaPayExpired && window.agodaPayExpired()){
    fail('This price guarantee has expired. Please go back to refresh the live price.');
    return;
  }
  const payMethod=this.querySelector('[name=payment_method]:checked')?.value || 'vodacom';
  const phone=this.querySelector('[name=payment_phone]');
  if(!phone.value.trim()){
    fail('Enter your mobile money number to receive the payment prompt.', phone);
    return;
  }
});
</script>
