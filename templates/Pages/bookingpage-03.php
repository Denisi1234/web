<?php
$this->assign('title', 'Payment information');

$propTitle = trim((string)($property['name'] ?? '')) !== '' ? $property['name'] : 'Your stay';
$propCity = $property['city'] ?? '';
$propAddress = trim((string)($property['address'] ?? ''));
$propAddressShort = $propAddress !== '' ? $propAddress : ($propCity !== '' ? $propCity . ', Tanzania' : '');
$propStars = !empty($property['star_rating']) ? max(1,min(5,(int)$property['star_rating'])) : 0;
$propRating = (float)($property['rating'] ?? 0);
$propReviews = (int)($property['reviews_count'] ?? 0);
$hasRating = $propRating > 0;

$checkIn = $queryParams['checkIn'] ?? $queryParams['check_in'] ?? '';
$checkOut = $queryParams['checkOut'] ?? $queryParams['check_out'] ?? '';
$ciTs = strtotime((string)$checkIn); $coTs = strtotime((string)$checkOut);
$hasDates = $ciTs && $coTs && $coTs > $ciTs;
$nights = $hasDates ? max(1, (int)round(($coTs - $ciTs) / 86400)) : 1;
$guests = (int)($queryParams['adults'] ?? 2) + (int)($queryParams['children'] ?? 0);

$roomTitle = trim((string)($room['name'] ?? '')) !== '' ? $room['name'] : 'Selected room';
$roomSize = !empty($room['room_size']) ? (string)$room['room_size'] : (!empty($room['size']) ? (is_numeric($room['size']) ? $room['size'] . ' m²' : (string)$room['size']) : '');
$roomMax = max(1, (int)($room['max_adults'] ?? ($room['capacity'] ?? 2)));
$bed = \App\Utility\TextFormatter::formatTitle(trim((string)($room['bed_configuration'] ?? ($room['beds'] ?? ''))));

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
$guestPhone = $queryParams['payment_phone'] ?? $queryParams['phone'] ?? '';

$img = '';
if (!empty($property['primary_image_url'])) $img = $property['primary_image_url'];
elseif (!empty($property['image_url'])) $img = $property['image_url'];
elseif (!empty($property['images'][0])) $img = is_array($property['images'][0]) ? ($property['images'][0]['url'] ?? '') : (string)$property['images'][0];
elseif (!empty($room['photos'][0])) $img = is_array($room['photos'][0]) ? ($room['photos'][0]['url'] ?? '') : (string)$room['photos'][0];
?>
<?= $this->Html->css('/assets/css/bookingpage-03.css?v=' . filemtime(WWW_ROOT . 'assets/css/bookingpage-03.css')) ?>
<?= $this->element('navbar') ?>

<div class="agoda-checkout-stepper">
  <div class="agoda-stepper-inner">
    <!-- Desktop Stepper -->
    <div class="agoda-steps d-none d-md-flex">
      <div class="agoda-step done"><span class="num"><i class="fa-solid fa-check" style="font-size:10px"></i></span><span>Customer information</span></div>
      <div class="agoda-step-line filled"></div>
      <div class="agoda-step active"><span class="num">2</span><span>Payment information</span></div>
      <div class="agoda-step-line"></div>
      <div class="agoda-step"><span class="num">3</span><span>Booking is confirmed!</span></div>
    </div>
    <!-- Mobile Compact Stepper -->
    <div class="d-flex d-md-none align-items-center justify-content-between w-100 px-1">
      <div class="d-flex align-items-center gap-2">
        <span class="agoda-step-badge">Step 2 of 3</span>
        <span class="agoda-step-title">Payment information</span>
      </div>
      <span class="agoda-step-next"><i class="fa-solid fa-shield-check text-success me-1"></i>Secure</span>
    </div>
  </div>
</div>

<?php
$payRemainingSrv = isset($quoteRemaining) ? max(0, (int)$quoteRemaining)
    : (isset($quote['expires_at']) ? max(0, (int)$quote['expires_at'] - time()) : 0);
$payHasGuarantee = !empty($quote['expires_at']) || isset($quoteRemaining);
$payFmt = sprintf('%02d:%02d:%02d', (int)($payRemainingSrv / 3600), (int)(($payRemainingSrv % 3600) / 60), $payRemainingSrv % 60);
?>
<?php if ($payHasGuarantee): ?>
<div class="agoda-timer-bar" id="agodaPayTimerBar" data-remaining="<?= (int)$payRemainingSrv ?>">
  <span id="agodaPayTimerLabel">This price is guaranteed for... <b><i class="fa-regular fa-clock"></i> <span id="agodaPayCountdown"><?= h($payFmt) ?></span></b></span>
  <span id="agodaPayTimerExpired" style="display:none">Price guarantee expired — <a href="<?= $this->Url->build('/booking-page', ['?' => array_filter(['property_id' => $propertyId, 'room_id' => $roomId, 'checkIn' => $checkIn, 'checkOut' => $checkOut, 'repriced' => '1'])]) ?>" style="font-weight:700;color:#0f62fe">go back to refresh the live price</a></span>
</div>
<?php endif; ?>

<div class="agoda-pay-wrap">
  <!-- LEFT COLUMN: Payment form -->
  <div style="display:flex;flex-direction:column;gap:16px">
    <div class="agoda-card">
      <div class="agoda-card-pad">
        <div class="agoda-pay-title">Payment method</div>
        <div class="agoda-pay-sub"><i class="fa-solid fa-shield-halved"></i> 256-bit SSL encrypted & secure mobile payment</div>
      </div>
      <form id="agodaPaymentForm" action="<?= $this->Url->build('/bookingpage-02') ?>" method="POST" style="display:block">
        <input type="hidden" name="_csrfToken" value="<?= $this->request->getAttribute('csrfToken') ?>">
        <input type="hidden" name="property_id" value="<?= h($propertyId) ?>">
        <input type="hidden" name="room_id" value="<?= h($roomId) ?>">
        <input type="hidden" name="quote_id" value="<?= h($quoteId) ?>">
        <input type="hidden" name="check_in" value="<?= h($checkIn) ?>">
        <input type="hidden" name="check_out" value="<?= h($checkOut) ?>">
        <input type="hidden" name="first_name" value="<?= h($queryParams['first_name'] ?? '') ?>">
        <input type="hidden" name="last_name" value="<?= h($queryParams['last_name'] ?? '') ?>">
        <input type="hidden" name="email" value="<?= h($queryParams['email'] ?? $guestEmail) ?>">
        <input type="hidden" name="phone" value="<?= h($queryParams['phone'] ?? $guestPhone) ?>">

        <!-- Select payment method -->
        <fieldset class="cds-pay-field">
          <legend class="cds-pay-legend">Select your mobile network</legend>
          <div class="cds-pay-grid">
            <label class="cds-pay-tile agoda-pay-option" data-method="vodacom">
              <input type="radio" name="payment_method" value="vodacom" checked>
              <span class="cds-pay-check" aria-hidden="true"><i class="fa-solid fa-check"></i></span>
              <img class="cds-pay-logo" src="<?= $this->Url->build('/assets/img/vodacom-logo.png') ?>" alt="M-Pesa" onerror="this.style.display='none'">
              <span><span class="cds-pay-name">M-Pesa</span><span class="cds-pay-sub">Vodacom</span></span>
            </label>
            <label class="cds-pay-tile agoda-pay-option" data-method="tigo">
              <input type="radio" name="payment_method" value="tigo">
              <span class="cds-pay-check" aria-hidden="true"><i class="fa-solid fa-check"></i></span>
              <img class="cds-pay-logo" src="<?= $this->Url->build('/assets/img/tigo-pesa-logo.jpg') ?>" alt="Tigo Pesa" onerror="this.style.display='none'">
              <span><span class="cds-pay-name">Tigo Pesa</span><span class="cds-pay-sub">Tigo</span></span>
            </label>
            <label class="cds-pay-tile agoda-pay-option" data-method="airtel">
              <input type="radio" name="payment_method" value="airtel">
              <span class="cds-pay-check" aria-hidden="true"><i class="fa-solid fa-check"></i></span>
              <img class="cds-pay-logo" src="<?= $this->Url->build('/assets/img/airtel-logo.png') ?>" alt="Airtel Money" onerror="this.style.display='none'">
              <span><span class="cds-pay-name">Airtel Money</span><span class="cds-pay-sub">Airtel</span></span>
            </label>
            <label class="cds-pay-tile agoda-pay-option" data-method="halotel">
              <input type="radio" name="payment_method" value="halotel">
              <span class="cds-pay-check" aria-hidden="true"><i class="fa-solid fa-check"></i></span>
              <img class="cds-pay-logo" src="<?= $this->Url->build('/assets/img/halotel-logo.jpg') ?>" alt="HaloPesa" onerror="this.style.display='none'">
              <span><span class="cds-pay-name">HaloPesa</span><span class="cds-pay-sub">Halotel</span></span>
            </label>
          </div>
        </fieldset>

        <!-- Local mobile money fields -->
        <div id="momoFormSection">
          <div class="cds-momo-box">
            <div class="cds-momo-title"><span id="momoTitle">Mobile money number</span></div>
            <div class="cds-momo-row">
              <span class="cds-momo-prefix"><span id="momoPrefix">+255</span> <img id="momoLogo" src="" alt="" style="display:none" onerror="this.style.display='none'"></span>
              <input class="cds-momo-input" name="payment_phone" value="<?= h($guestPhone) ?>" placeholder="712 345 678" inputmode="tel" autocomplete="tel" required>
            </div>
            <div class="cds-momo-hint"><i class="fa-solid fa-circle-info text-primary mt-1"></i> <span>You will receive a USSD prompt on your phone to authorize payment. <span id="momoHint"></span></span></div>
          </div>
        </div>
        <div style="display:none"><button type="submit" id="hiddenSubmit">submit</button></div>
      </form>

      <div class="agoda-divider"></div>
      <div class="agoda-terms">By confirming, you agree to FastNet Stays' <a href="/terms-of-service" target="_blank">Terms of Use</a> and <a href="/privacy-policy" target="_blank">Privacy Policy</a>.</div>
    </div>

    <?php if ($guestEmail !== ''): ?>
    <div class="agoda-email-note">
      <i class="fa-solid fa-envelope" style="color:#0f62fe;font-size:16px"></i>
      <div>We'll send booking confirmation and digital receipt to <b><?= h($guestEmail) ?></b></div>
    </div>
    <?php endif; ?>

    <div class="agoda-card" style="padding:16px">
      <div id="payFormError" style="display:none;background:#fdecea;border:1px solid #f5c6cb;color:#7d2e2e;padding:10px 12px;border-radius:8px;font-size:12.5px;margin-bottom:12px"></div>
      <button type="button" class="agoda-book-btn" onclick="document.getElementById('agodaPaymentForm').requestSubmit()"><i class="fa-solid fa-lock"></i> <?= $total > 0 ? 'Pay TSh ' . number_format($total) : 'Pay now' ?></button>
      <div class="agoda-flexi"><i class="fa-solid fa-bolt me-1"></i> Instant payment processing via AzamPay</div>
    </div>
  </div>

  <!-- RIGHT COLUMN: Stay & Price Summary -->
  <div class="agoda-side-card-wrap">
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
            <div style="font-size:12px;color:#334155;margin-top:2px"><b><?= h(number_format($propRating,1)) ?> Excellent</b> · <?= h($propReviews) ?> reviews</div>
          <?php endif; ?>
          <div style="font-size:11.5px;color:#64748b;margin-top:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= h($propAddressShort) ?></div>
        </div>
      </div>

      <!-- Dates Row -->
      <div class="agoda-dates">
        <div>
          <span style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:.03em">Check-in</span>
          <b><?= $hasDates ? date('D, M j, Y', $ciTs) : h($checkIn) ?></b>
        </div>
        <div style="color:#94a3b8;font-size:16px">→</div>
        <div>
          <span style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:.03em">Check-out</span>
          <b><?= $hasDates ? date('D, M j, Y', $coTs) : h($checkOut) ?></b>
        </div>
        <div style="text-align:right">
          <b><?= h($nights) ?> <?= $nights === 1 ? 'Night' : 'Nights' ?></b>
          <span style="font-size:11px;color:#64748b"><?= h($roomsCount) ?> <?= $roomsCount === 1 ? 'Room' : 'Rooms' ?>, <?= h($guests) ?> Guests</span>
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
          <span><?= h($roomsCount) ?> × Room (<?= h($nights) ?> <?= $nights === 1 ? 'night' : 'nights' ?>)</span>
          <span>TSh <?= number_format($roomPrice) ?></span>
        </div>
        <div class="agoda-price-row">
          <span>Mobile-money processing (1%)</span>
          <span>TSh <?= number_format($azampayFee) ?></span>
        </div>
        <div class="agoda-price-row">
          <span>Taxes & fees</span>
          <span style="color:#059669;font-weight:600">Included</span>
        </div>
        <div class="agoda-price-total">
          <span>Total Price</span>
          <span class="total-amount">TSh <?= number_format($total) ?></span>
        </div>
        <div class="agoda-included">Includes 1% AzamPay mobile fee · Instant confirmation</div>
      </div>
    </div>
  </div>
</div>

<script>
// Real countdown guarantee
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
  document.querySelectorAll('.cds-pay-tile').forEach(l=>{
    l.classList.toggle('is-selected', l.querySelector('input')?.value===sel);
  });
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
  if(h) h.textContent='selected: ' + (titles[sel]||sel) + '.';
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
  const phone=this.querySelector('[name=payment_phone]');
  if(!phone || !phone.value.trim()){
    fail('Enter your mobile money phone number to receive the payment prompt.', phone);
    return;
  }
});
</script>
