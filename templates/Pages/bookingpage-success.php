<?php
$isPaid = !empty($isPaid);
$paymentStatus = strtolower((string)($paymentStatus ?? ($verifiedBooking['payment_status'] ?? '')));
$this->assign('title', 'Booking details - FastNet Stays');

$bookingId = $queryParams['booking_id'] ?? ($verifiedBooking['id'] ?? '');
$reference = $queryParams['reference'] ?? ($verifiedBooking['booking_code'] ?? ($verifiedBooking['reference'] ?? ''));
$guestName = $queryParams['guest_name'] ?? ($verifiedBooking['guest']['name'] ?? ($verifiedBooking['guest_name'] ?? 'Traveler'));
$guestEmail = $queryParams['guest_email'] ?? ($verifiedBooking['guest']['email'] ?? ($verifiedBooking['guest_email'] ?? ''));
$guestPhone = $queryParams['guest_phone'] ?? ($verifiedBooking['guest']['phone_number'] ?? ($verifiedBooking['guest_phone'] ?? ''));
$checkIn = $queryParams['check_in'] ?? ($verifiedBooking['check_in'] ?? '');
$checkOut = $queryParams['check_out'] ?? ($verifiedBooking['check_out'] ?? '');
$totalAmount = (float)($queryParams['total_amount'] ?? ($verifiedBooking['total_price'] ?? ($verifiedBooking['amount'] ?? 0)));

$rawPm = strtolower($queryParams['payment_method'] ?? ($verifiedBooking['payment_method'] ?? 'vodacom'));
$pmLabels = [
    'vodacom' => 'Vodacom M-Pesa',
    'mpesa' => 'M-Pesa',
    'tigo' => 'Tigo Pesa',
    'airtel' => 'Airtel Money',
    'halotel' => 'HaloPesa (Halotel)',
];
$paymentMethodLabel = $pmLabels[$rawPm] ?? (ucfirst($rawPm) ?: 'Mobile Money');
$paymentPhone = $queryParams['payment_phone'] ?? ($verifiedBooking['payment_phone'] ?? '');

$propName = $queryParams['property_name'] ?: ($property['name'] ?? ($verifiedBooking['room']['property']['name'] ?? ($verifiedBooking['property']['name'] ?? 'Lodge Stay')));
$propCity = $queryParams['property_city'] ?: ($property['city'] ?? ($verifiedBooking['room']['property']['city'] ?? ($verifiedBooking['property']['city'] ?? 'Dar es Salaam')));
$propArea = $property['area'] ?? ($verifiedBooking['room']['property']['area'] ?? ($verifiedBooking['property']['area'] ?? ''));
$propLocation = trim(trim((string)$propArea, " \t\n\r\0\x0B,") . ', ' . trim((string)$propCity, " \t\n\r\0\x0B,"), " \t\n\r\0\x0B,");
if ($propLocation === '' || $propLocation === ',') $propLocation = 'Dar es Salaam';

$propImg = '';
if (is_array($property ?? null)) {
    $propImg = (string)($property['image_url'] ?? ($property['main_image'] ?? ($property['primary_image_url'] ?? '')));
}
if ($propImg === '' && !empty($verifiedBooking['imageUrl'])) {
    $propImg = (string)$verifiedBooking['imageUrl'];
}
if ($propImg === '' && !empty($verifiedBooking['room']['property']['image_url'])) {
    $propImg = (string)$verifiedBooking['room']['property']['image_url'];
}
if (trim($propImg) === '') $propImg = '/assets/images/house3.webp';

$bkStatusRaw = strtolower((string)($bookingStatus ?? ($verifiedBooking['status'] ?? ($isPaid ? 'confirmed' : $paymentStatus))));
$statusLabel = match(true) {
    in_array($bkStatusRaw, ['cancelled', 'canceled'], true) => 'CANCELLED',
    $bkStatusRaw === 'completed' => 'COMPLETED',
    in_array($bkStatusRaw, ['checked in', 'checked_in', 'checked-in'], true) => 'CHECKED IN',
    $bkStatusRaw === 'pending' => 'PENDING',
    default => 'CONFIRMED',
};
$tagCls = match(true) {
    $statusLabel === 'CONFIRMED' => 'cds-t-confirmed',
    $statusLabel === 'PENDING' => 'cds-t-pending',
    $statusLabel === 'COMPLETED' => 'cds-t-completed',
    $statusLabel === 'CHECKED IN' => 'cds-t-checkin',
    $statusLabel === 'CANCELLED' => 'cds-t-cancelled',
    default => 'cds-t-confirmed',
};

// Dates & nights calculation (identical to mobile)
$ciOk = strtotime((string)$checkIn);
$coOk = strtotime((string)$checkOut);
$nights = 1;
if ($ciOk && $coOk && $coOk > $ciOk) {
    $nights = (int)round(($coOk - $ciOk) / 86400);
}
$arrivalDate = $ciOk ? date('M j, Y', $ciOk) : 'Today';
$departureDate = $coOk ? date('M j, Y', $coOk) : 'Next Day';

$datesFormatted = '';
if ($ciOk && $coOk) {
    if (date('Y', $ciOk) === date('Y', $coOk) && date('M', $ciOk) === date('M', $coOk)) {
        $datesFormatted = date('M j', $ciOk) . ' – ' . date('j, Y', $coOk);
    } elseif (date('Y', $ciOk) === date('Y', $coOk)) {
        $datesFormatted = date('M j', $ciOk) . ' – ' . date('M j, Y', $coOk);
    } else {
        $datesFormatted = date('M j, Y', $ciOk) . ' – ' . date('M j, Y', $coOk);
    }
} elseif ($ciOk) {
    $datesFormatted = date('M j, Y', $ciOk);
}

$roomName = $queryParams['room_title'] ?? ($verifiedBooking['room']['name'] ?? ($verifiedBooking['room']['title'] ?? ($verifiedBooking['roomNumber'] ?? '')));
$roomNumber = '101';
if (preg_match('/Room\s*(\w+)/i', (string)$roomName, $rmMatches)) {
    $roomNumber = $rmMatches[1];
} elseif (!empty($verifiedBooking['roomNumber'])) {
    $roomNumber = (string)$verifiedBooking['roomNumber'];
} elseif (!empty($bookingRoom['room_number'])) {
    $roomNumber = (string)$bookingRoom['room_number'];
}

$propId = $queryParams['property_id'] ?? ($property['id'] ?? ($verifiedBooking['property_id'] ?? ($verifiedBooking['room']['property']['id'] ?? null)));
$hostId = $verifiedBooking['room']['property']['host_id'] ?? ($property['host_id'] ?? 1);
$msgPropertyUrl = $this->Url->build(['controller' => 'Account', 'action' => 'messages', '?' => [
    'host_id' => $hostId,
    'lodge_name' => $propName,
    'booking_code' => $reference,
    'property_id' => $propId
]]);
$canCancel = !in_array($statusLabel, ['CANCELLED', 'COMPLETED'], true) && $reference !== '';

// Receipt Voucher Details
$cleanCode = preg_replace('/[^a-zA-Z0-9]/', '', (string)$reference);
$refNo = '337038' . (strlen($cleanCode) >= 4 ? substr($cleanCode, 0, 4) : '0000');
$vatTotal = (int)round($totalAmount * 0.125);
$memberId = !empty($verifiedBooking['guest']['id']) ? (string)$verifiedBooking['guest']['id'] : '53370111';
$verifyUrl = !empty($verifiedBooking['verify_url']) ? $verifiedBooking['verify_url'] : $this->Url->build(['controller' => 'Bookings', 'action' => 'verifyReceipt', $reference], ['fullBase' => true]);
$qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=130x130&margin=0&data=' . urlencode($verifyUrl);
?>

<?= $this->element('navbar') ?>
<?= $this->Html->css('/assets/css/my-booking.css') ?>

<style>
/* Modal and Receipt Styling */
.receipt-modal-dialog {
  width: 100%;
  max-width: 680px;
  background: #ffffff;
  border-radius: 0;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2), 0 10px 10px -5px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  max-height: 92vh;
  display: flex;
  flex-direction: column;
}
.receipt-modal-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 18px;
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
}
.receipt-modal-body {
  overflow-y: auto;
  padding: 16px;
  background: #f1f5f9;
}
.receipt-wrapper {
  background: #ffffff;
  border: 2.5px solid #000000;
  box-shadow: 0 4px 12px rgba(0,0,0,0.06);
  padding: 12px;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
  color: #111827;
}
.receipt-header-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 12px;
  margin-bottom: 6px;
}
.receipt-logo-brand {
  display: flex;
  align-items: center;
  gap: 8px;
  text-decoration: none;
}
.receipt-logo-brand img {
  height: 28px;
  width: auto;
  object-fit: contain;
}
.receipt-brand-text {
  font-weight: 700;
  color: #202124;
  font-size: 17px;
  letter-spacing: -0.01em;
}
.receipt-title-box {
  text-align: right;
}
.receipt-main-title {
  font-size: 19px;
  font-weight: 900;
  line-height: 1.15;
  margin: 0;
}
.receipt-title-booking {
  color: #000000;
}
.receipt-title-conf {
  color: #D9251D;
}
.receipt-subtitle {
  font-size: 8.5px;
  color: #374151;
  margin-top: 2px;
  line-height: 1.25;
}
.receipt-watermark-band {
  width: 100%;
  background-color: #9ca3af;
  color: #ffffff;
  padding: 3px 6px;
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.04em;
  white-space: nowrap;
  overflow: hidden;
  margin-bottom: 12px;
}
.receipt-grid {
  display: flex;
  gap: 12px;
  margin-bottom: 10px;
}
.receipt-col-left {
  flex: 13;
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.receipt-col-right {
  flex: 11;
  background-color: #EFEFEF;
  padding: 8px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.receipt-row {
  display: flex;
  align-items: flex-start;
  font-size: 10px;
  line-height: 1.35;
}
.receipt-label {
  width: 125px;
  flex-shrink: 0;
  font-weight: 900;
  color: #000000;
}
.receipt-value {
  flex-grow: 1;
  font-weight: 700;
  color: #111827;
}
.receipt-value-boxed {
  background-color: #ffffff;
  border: 1px solid #9ca3af;
  padding: 3px 6px;
  font-weight: 900;
  font-size: 10px;
  display: block;
}
.receipt-form-row {
  display: flex;
  align-items: center;
  font-size: 9.5px;
  line-height: 1.3;
}
.receipt-form-label {
  width: 110px;
  flex-shrink: 0;
  font-weight: 900;
  color: #000000;
}
.receipt-form-val {
  flex-grow: 1;
  background-color: #ffffff;
  border: 1px solid #9ca3af;
  padding: 2px 4px;
  text-align: center;
  font-weight: 700;
  font-size: 9.5px;
  min-height: 18px;
}
.receipt-policy-box {
  background-color: #EFEFEF;
  padding: 6px 8px;
  font-size: 9.5px;
  line-height: 1.35;
  color: #1f2937;
  margin-bottom: 5px;
}
.receipt-benefits-box {
  background-color: #EFEFEF;
  padding: 6px 8px;
  font-size: 8.5px;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 8px;
}
.receipt-stay-payment-box {
  border: 1px solid #9ca3af;
  padding: 8px;
  margin-bottom: 10px;
}
.receipt-stay-row {
  display: flex;
  gap: 12px;
  margin-bottom: 8px;
}
.receipt-stay-half {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 6px;
}
.receipt-stay-badge {
  flex-grow: 1;
  background-color: #D9D9D9;
  padding: 4px 8px;
  text-align: center;
  font-weight: 900;
  font-size: 10px;
  color: #000000;
}
.receipt-pay-grid {
  display: flex;
  gap: 10px;
}
.receipt-pay-left {
  flex: 14;
}
.receipt-pay-note-box {
  background-color: #EFEFEF;
  padding: 6px;
  font-size: 8.5px;
  line-height: 1.35;
  margin-top: 4px;
}
.receipt-pay-right {
  flex: 9;
  border: 1px solid #9ca3af;
  background: #ffffff;
  padding: 6px 4px;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}
.receipt-qr-img {
  width: 70px;
  height: 70px;
  object-fit: contain;
  display: block;
  margin: 0 auto;
}
.receipt-stamp-text {
  font-size: 8px;
  font-weight: 900;
  color: #000000;
  margin-top: 4px;
  text-align: center;
  line-height: 1.15;
}
.receipt-remarks-title {
  font-size: 10px;
  font-weight: 900;
  color: #000000;
  margin-bottom: 2px;
}
.receipt-remarks-text {
  font-size: 9.5px;
  line-height: 1.35;
  color: #111827;
}
.receipt-remarks-footer {
  display: flex;
  justify-content: space-between;
  gap: 8px;
  font-size: 9px;
  color: #374151;
  margin-top: 4px;
  margin-bottom: 8px;
}
.receipt-notes-card {
  border: 1px solid #9ca3af;
  background-color: #FDFDFD;
  padding: 8px;
  margin-bottom: 8px;
}
.receipt-notes-title {
  font-size: 9.5px;
  font-weight: 900;
  color: #000000;
  margin-bottom: 4px;
}
.receipt-note-item {
  display: flex;
  align-items: flex-start;
  gap: 6px;
  font-size: 8px;
  line-height: 1.35;
  color: #111827;
  margin-bottom: 4px;
}
.receipt-note-num {
  font-weight: 900;
  color: #000000;
  width: 12px;
  flex-shrink: 0;
}
.receipt-thanks-banner {
  background-color: #EFEFEF;
  border: 1px solid #9ca3af;
  padding: 6px 8px;
  margin-top: 4px;
}
.receipt-thanks-title {
  font-size: 9.5px;
  font-weight: 900;
  color: #000000;
  margin-bottom: 2px;
}
.receipt-thanks-sub {
  font-size: 8.5px;
  color: #374151;
}

@media print {
  body * {
    visibility: hidden;
  }
  #receipt-printable-content, #receipt-printable-content * {
    visibility: visible;
  }
  #receipt-printable-content {
    position: absolute;
    left: 0;
    top: 0;
    width: 100% !important;
    max-width: 100% !important;
    padding: 0 !important;
    margin: 0 !important;
    background: #fff !important;
  }
  .receipt-wrapper {
    box-shadow: none !important;
    border: 2px solid #000 !important;
    margin: 0 !important;
  }
  nav, footer, .no-print, .receipt-modal-head, .cds-modal-overlay {
    display: none !important;
  }
}
</style>

<main id="main-content" style="background:#f4f4f4;min-height:85vh;padding:24px 0 60px;" role="main">
  <div class="container" style="max-width:680px">

    <!-- Top App Bar Navigation -->
    <div style="margin-bottom:16px;display:flex;align-items:center;justify-content:space-between">
      <a href="<?= $this->Url->build('/my-booking') ?>" style="display:inline-flex;align-items:center;gap:6px;font-size:14px;font-weight:700;color:#161616;text-decoration:none">
        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
        <span>Back to My bookings</span>
      </a>
      <span style="font-size:16px;font-weight:800;color:#161616">Booking details</span>
    </div>

    <!-- Main Booking Details Stack (Exact Mobile Mirror) -->
    <div style="display:flex;flex-direction:column;gap:12px">

      <!-- 1. Hero Photo with Status Badge -->
      <div style="position:relative;width:100%;height:220px;background:#e0e0e0;overflow:hidden;border:1px solid #e0e0e0;border-bottom:none">
        <img src="<?= h($propImg) ?>" alt="<?= h($propName) ?>" style="width:100%;height:100%;object-fit:cover;display:block" loading="lazy" onerror="this.onerror=null;this.src='/assets/images/house3.webp'">
        <div style="position:absolute;top:12px;left:12px">
          <span class="cds-tag <?= $tagCls ?>" style="font-size:11px;padding:4px 10px;letter-spacing:0.04em"><?= h($statusLabel) ?></span>
        </div>
      </div>

      <!-- 2. Property & Stay Facts Card -->
      <div style="background:#ffffff;border:1px solid #e0e0e0;padding:20px 20px 16px;">
        <h1 style="font-size:19px;font-weight:800;color:#161616;line-height:1.25;margin:0 0 6px"><?= h($propName) ?></h1>
        <div style="display:flex;align-items:center;gap:5px;font-size:13px;color:#525252;margin-bottom:16px">
          <i class="fa-solid fa-location-dot" style="font-size:13px;color:#525252" aria-hidden="true"></i>
          <span><?= h($propLocation) ?></span>
        </div>

        <div style="height:1px;background:#e0e0e0;margin:0 0 14px"></div>

        <!-- Facts List -->
        <div style="display:flex;flex-direction:column;gap:10px;font-size:13.5px">
          <div style="display:flex;justify-content:space-between;align-items:center">
            <span style="color:#525252">Booking code</span>
            <span style="font-family:monospace;font-weight:700;color:#161616;font-size:14px;letter-spacing:0.04em"><?= h($reference) ?></span>
          </div>
          <div style="display:flex;justify-content:space-between;align-items:center">
            <span style="color:#525252">Dates</span>
            <span style="font-weight:600;color:#161616"><?= h($datesFormatted ?: '—') ?></span>
          </div>
          <div style="display:flex;justify-content:space-between;align-items:center">
            <span style="color:#525252">Nights</span>
            <span style="font-weight:600;color:#161616"><?= $nights ?> night<?= $nights === 1 ? '' : 's' ?></span>
          </div>
          <?php if ($roomName !== ''): ?>
          <div style="display:flex;justify-content:space-between;align-items:center">
            <span style="color:#525252">Room</span>
            <span style="font-weight:600;color:#161616"><?= h($roomName) ?></span>
          </div>
          <?php endif; ?>
          <div style="display:flex;justify-content:space-between;align-items:center">
            <span style="color:#525252">Payment</span>
            <span style="font-weight:600;color:#161616"><?= h(ucfirst($paymentStatus ?: ($isPaid ? 'paid' : 'pending'))) ?><?= $paymentMethodLabel ? ' · ' . h($paymentMethodLabel) : '' ?></span>
          </div>
          <?php if ($bookingId !== ''): ?>
          <div style="display:flex;justify-content:space-between;align-items:center">
            <span style="color:#525252">Booking ID</span>
            <span style="font-weight:600;color:#161616">#<?= h($bookingId) ?></span>
          </div>
          <?php endif; ?>
          <?php if ($guestName !== ''): ?>
          <div style="display:flex;justify-content:space-between;align-items:center">
            <span style="color:#525252">Guest name</span>
            <span style="font-weight:600;color:#161616"><?= h($guestName) ?></span>
          </div>
          <?php endif; ?>
        </div>

        <div style="height:1px;background:#e0e0e0;margin:14px 0"></div>

        <!-- Total Row -->
        <div style="display:flex;justify-content:space-between;align-items:center">
          <span style="font-size:13.5px;font-weight:700;color:#525252"><?= $isPaid ? 'Total paid' : 'Total price' ?></span>
          <span style="font-size:18px;font-weight:800;color:#161616">TSh <?= number_format($totalAmount) ?></span>
        </div>
      </div>

      <!-- 3. Blue Info Callout Banner -->
      <div style="background:#edf5ff;border:1px solid #d0e2ff;padding:12px 14px;display:flex;align-items:flex-start;gap:10px">
        <i class="fa-solid fa-circle-info" style="color:#0f62fe;font-size:16px;margin-top:2px;flex-shrink:0" aria-hidden="true"></i>
        <div style="font-size:12.5px;color:#161616;line-height:1.45">
          Show this booking code at check-in along with your payment confirmation.
        </div>
      </div>

      <!-- 4. Check-in Guide Card (When Upcoming) -->
      <?php if ($statusLabel !== 'CANCELLED'):
        $todayTs = strtotime('today');
        $guideDone = in_array($statusLabel, ['CHECKED IN', 'COMPLETED'], true);
        $guideOpen = !$guideDone && $ciOk && $ciOk <= $todayTs;
        $guideDays = (!$guideDone && !$guideOpen && $ciOk && $ciOk > $todayTs) ? (int)round(($ciOk - $todayTs) / 86400) : null;
        $guideAccent = $guideDone ? '#0e6027' : ($guideOpen ? '#0f62fe' : '#8e6a00');
        $guideTint = $guideDone ? '#defbe6' : ($guideOpen ? '#edf5ff' : '#fcf4d6');
        $guideIcon = $guideDone ? 'fa-circle-check' : ($guideOpen ? 'fa-plane-arrival' : 'fa-calendar-days');
        $guideTitle = $guideDone ? 'Checked in — enjoy your stay' : ($guideOpen ? 'Check-in is open' : ($guideDays !== null ? 'Check-in opens ' . date('M j, Y', $ciOk) : 'Check-in guide'));
        $guideSub = $guideDone
          ? 'Your arrival has been confirmed. For anything during your stay, contact the property or support below.'
          : ($guideOpen
            ? 'You can arrive from today. Show your booking code and photo ID at the front desk.'
            : ($guideDays !== null
              ? 'Your stay starts ' . ($guideDays <= 1 ? 'tomorrow' : 'in ' . $guideDays . ' days') . '. Arrive with your booking code and ID.'
              : 'Your check-in details will appear here once dates are confirmed.'));
      ?>
      <div style="background:#ffffff;border:1px solid #e0e0e0;padding:16px 18px">
        <div style="display:flex;align-items:flex-start;gap:12px">
          <div style="width:40px;height:40px;flex:0 0 40px;border-radius:50%;background:<?= $guideTint ?>;display:flex;align-items:center;justify-content:center">
            <i class="fa-solid <?= $guideIcon ?>" style="color:<?= $guideAccent ?>;font-size:18px" aria-hidden="true"></i>
          </div>
          <div>
            <div style="font-size:15.5px;font-weight:800;color:#161616;margin-bottom:3px"><?= h($guideTitle) ?></div>
            <div style="font-size:12.5px;color:#525252;margin-bottom:10px;line-height:1.45"><?= h($guideSub) ?></div>
            <ol style="font-size:12.5px;color:#161616;margin:0;padding-left:18px;line-height:1.6">
              <li>On arrival day, present yourself at the lodge reception desk.</li>
              <li>Show the booking code (<strong><?= h($reference) ?></strong>) and a valid ID.</li>
              <li>The host confirms your check-in — your stay is all set.</li>
            </ol>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- 5. Action Buttons (Exact Mobile Layout) -->
      <div style="display:flex;flex-direction:column;gap:8px;margin-top:4px">
        <?php if ($statusLabel === 'CANCELLED'): ?>
          <a href="<?= $this->Url->build('/hotel-list-01') ?>" class="cds-btn cds-btn-primary" style="width:100%;height:48px;font-size:13.5px;font-weight:700">
            <i class="fa-solid fa-rotate-right" aria-hidden="true"></i>
            <span>Book again</span>
          </a>
        <?php elseif ($statusLabel === 'COMPLETED'): ?>
          <div style="display:flex;gap:8px;width:100%">
            <button type="button" class="cds-btn cds-btn-ghost" style="flex:1;height:48px;font-size:13px;font-weight:700" onclick="openReceiptModal()">
              <i class="fa-solid fa-receipt" aria-hidden="true"></i>
              <span>Receipt</span>
            </button>
            <a href="<?= $propId ? $this->Url->build('/hotel-detail-01', ['?' => ['id' => $propId]]) . '#reviews' : $this->Url->build('/hotel-list-01') ?>" class="cds-btn cds-btn-primary" style="flex:1;height:48px;font-size:13px;font-weight:700">
              <i class="fa-regular fa-star" aria-hidden="true"></i>
              <span>Review</span>
            </a>
          </div>
          <a href="<?= $msgPropertyUrl ?>" class="cds-btn cds-btn-ghost" style="width:100%;height:48px;font-size:13px;font-weight:700">
            <i class="fa-regular fa-comment-dots" aria-hidden="true"></i>
            <span>Message property</span>
          </a>
        <?php else: ?>
          <!-- Active / Upcoming stays -->
          <div style="display:flex;gap:8px;width:100%">
            <?php if ($canCancel): ?>
            <button type="button" class="cds-btn cds-btn-danger" style="flex:1;height:48px;font-size:13px;font-weight:700" onclick="openCancelModal('<?= h($reference) ?>', '<?= h($guestEmail) ?>')">
              Cancel stay
            </button>
            <?php endif; ?>
            <button type="button" class="cds-btn cds-btn-ghost" style="flex:1;height:48px;font-size:13px;font-weight:700" onclick="openReceiptModal()">
              <i class="fa-solid fa-receipt" aria-hidden="true"></i>
              <span>Receipt</span>
            </button>
          </div>
          <div style="display:flex;gap:8px;width:100%">
            <a href="<?= $msgPropertyUrl ?>" class="cds-btn cds-btn-ghost" style="flex:1;height:48px;font-size:13px;font-weight:700">
              <i class="fa-regular fa-comment-dots" aria-hidden="true"></i>
              <span>Message property</span>
            </a>
            <button type="button" class="cds-btn cds-btn-ghost" style="flex:1;height:48px;font-size:13px;font-weight:700" onclick="openReschedule('<?= h($reference) ?>', '<?= h($datesFormatted) ?>')">
              <i class="fa-regular fa-calendar" aria-hidden="true"></i>
              <span>Change dates</span>
            </button>
          </div>
        <?php endif; ?>
      </div>

      <!-- 6. Need Help Link (Footer) -->
      <div style="text-align:center;margin-top:16px;font-size:12.5px;color:#525252">
        Need help with this stay?
        <a href="<?= $this->Url->build('/help-center') ?>" style="color:#0f62fe;font-weight:700;text-decoration:underline;margin-left:3px">Visit the help center</a>
      </div>

    </div>
  </div>
</main>

<!-- EXACT MOBILE-MATCHING RECEIPT VOUCHER MODAL DIALOG -->
<div id="bkReceiptModal" class="cds-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="bkReceiptTitle" style="display:none;padding:16px">
  <div class="receipt-modal-dialog">
    <div class="receipt-modal-head no-print">
      <div style="display:flex;align-items:center;gap:8px">
        <i class="fa-solid fa-receipt" style="color:#0f62fe;font-size:16px"></i>
        <h3 id="bkReceiptTitle" style="font-size:15px;font-weight:800;color:#0f172a;margin:0">Booking Confirmation Voucher</h3>
      </div>
      <div style="display:flex;align-items:center;gap:8px">
        <button type="button" class="cds-btn cds-btn-primary" style="height:36px;padding:0 14px;font-size:13px;font-weight:700;display:inline-flex;align-items:center;gap:6px" onclick="window.print()">
          <i class="fa-solid fa-print" aria-hidden="true"></i>
          <span>Print / Save PDF</span>
        </button>
        <button type="button" class="cds-modal-btn-cancel" style="height:36px;padding:0 12px;font-size:13px;border-radius:4px" onclick="closeReceiptModal()">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
    </div>
    <div class="receipt-modal-body">
      <div id="receipt-printable-content">
        <!-- VOUCHER BOX (Exact Mobile ReceiptScreen Match) -->
        <div class="receipt-wrapper">
          
          <!-- 1. Top Header Row -->
          <div class="receipt-header-row">
            <div class="receipt-logo-brand">
              <img src="/assets/images/fastnet_logo_icon.png" alt="FastNet" onerror="this.onerror=null;this.src='/assets/images/favicon.ico'">
              <span class="receipt-brand-text">fastnetstays.com</span>
            </div>
            <div class="receipt-title-box">
              <h1 class="receipt-main-title">
                <span class="receipt-title-booking">Booking </span><span class="receipt-title-conf">Confirmation</span>
              </h1>
              <div class="receipt-subtitle">Please present either an electronic or paper copy of your booking confirmation upon check-in.</div>
            </div>
          </div>

          <!-- Full-width Grey Watermark Band -->
          <div class="receipt-watermark-band">
            fastnetstays.com&nbsp;&nbsp;&nbsp;&nbsp;fastnetstays.com&nbsp;&nbsp;&nbsp;&nbsp;fastnetstays.com&nbsp;&nbsp;&nbsp;&nbsp;fastnetstays.com&nbsp;&nbsp;&nbsp;&nbsp;fastnetstays.com
          </div>

          <!-- 2. Main 2-Column Grid -->
          <div class="receipt-grid">
            <!-- Left Column -->
            <div class="receipt-col-left">
              <div class="receipt-row">
                <div class="receipt-label">Booking ID :</div>
                <div class="receipt-value" style="font-family:monospace;letter-spacing:0.03em"><?= h($reference) ?></div>
              </div>
              <div class="receipt-row">
                <div class="receipt-label">Booking Reference No :</div>
                <div class="receipt-value"><?= h($refNo) ?></div>
              </div>
              <div class="receipt-row">
                <div class="receipt-label">Client :</div>
                <div class="receipt-value" style="text-transform:uppercase"><?= h($guestName) ?></div>
              </div>
              <div class="receipt-row">
                <div class="receipt-label">Member ID :</div>
                <div class="receipt-value"><?= h($memberId) ?></div>
              </div>
              <div class="receipt-row">
                <div class="receipt-label">Country of Residence :</div>
                <div class="receipt-value">Tanzania</div>
              </div>
              <div class="receipt-row">
                <div class="receipt-label">Property Contact :</div>
                <div class="receipt-value"><?= h($guestPhone ?: '+255 700 000 000') ?></div>
              </div>
              <div class="receipt-row" style="margin-top:2px">
                <div class="receipt-label">Property :</div>
                <div class="receipt-value">
                  <span class="receipt-value-boxed"><?= h($propName) ?></span>
                </div>
              </div>
              <div class="receipt-row">
                <div class="receipt-label">Room Assigned :</div>
                <div class="receipt-value">
                  <span class="receipt-value-boxed">Room <?= h($roomNumber) ?></span>
                </div>
              </div>
              <div class="receipt-row">
                <div class="receipt-label">Address :</div>
                <div class="receipt-value">
                  <span class="receipt-value-boxed"><?= h($propLocation) ?>, Tanzania</span>
                </div>
              </div>
            </div>

            <!-- Right Column (Grey Background Box) -->
            <div class="receipt-col-right">
              <div class="receipt-form-row">
                <div class="receipt-form-label">Number of Rooms :</div>
                <div class="receipt-form-val">1</div>
              </div>
              <div class="receipt-form-row">
                <div class="receipt-form-label">Number of Extra Beds :</div>
                <div class="receipt-form-val">0</div>
              </div>
              <div class="receipt-form-row">
                <div class="receipt-form-label">Number of Adults :</div>
                <div class="receipt-form-val">2</div>
              </div>
              <div class="receipt-form-row">
                <div class="receipt-form-label">Number of Children :</div>
                <div class="receipt-form-val">0</div>
              </div>
              <div class="receipt-form-row">
                <div class="receipt-form-label">Room Type :</div>
                <div class="receipt-form-val"><?= h($roomName ?: 'Standard King Room') ?></div>
              </div>
              <div class="receipt-form-row">
                <div class="receipt-form-label">Room Number :</div>
                <div class="receipt-form-val">Room <?= h($roomNumber) ?></div>
              </div>
              <div class="receipt-form-row">
                <div class="receipt-form-label">Promotion :</div>
                <div class="receipt-form-val"></div>
              </div>
              <div style="font-size:8.5px;color:#374151;margin-top:2px;line-height:1.25">
                For Full Promotion details and conditions see confirmation email
              </div>
            </div>
          </div>

          <!-- 3. Cancellation Policy Box -->
          <div class="receipt-policy-box">
            <strong>Cancellation Policy:</strong> Any cancellation received will incur a charge of 34% of the booking value. Failure to arrive at your hotel or property will be treated as a No-Show and will incur a charge of 100% of the booking value (Hotel policy).
          </div>

          <!-- 4. Benefits Included Box -->
          <div class="receipt-benefits-box">
            Benefits Included: -
          </div>

          <!-- 5. Arrival / Departure & Payment Details Box -->
          <div class="receipt-stay-payment-box">
            <div class="receipt-stay-row">
              <div class="receipt-stay-half">
                <span style="font-size:10px;font-weight:900;color:#000000">Arrival :</span>
                <span class="receipt-stay-badge"><?= h($arrivalDate) ?></span>
              </div>
              <div class="receipt-stay-half">
                <span style="font-size:10px;font-weight:900;color:#000000">Departure :</span>
                <span class="receipt-stay-badge"><?= h($departureDate) ?></span>
              </div>
            </div>

            <div class="receipt-pay-grid">
              <div class="receipt-pay-left">
                <div style="font-size:10px;font-weight:900;color:#000000">Payment Details :</div>
                <div class="receipt-pay-note-box">
                  <div style="margin-bottom:3px">
                    <strong style="color:#D9251D">Please note: </strong>
                    <span>Payment for this booking has been processed via FastNetStays. Payment confirmation is verified by property.</span>
                  </div>
                  <div>
                    <strong style="color:#D9251D">Note to property: </strong>
                    <span>Reservation was made under FastNetStays booking ID <?= h($reference) ?></span>
                  </div>
                </div>
              </div>
              <div class="receipt-pay-right">
                <img class="receipt-qr-img" src="<?= h($qrCodeUrl) ?>" alt="QR Code" loading="lazy">
                <div class="receipt-stamp-text">Authorized Stamp &amp; Signature</div>
              </div>
            </div>
          </div>

          <!-- 6. Remarks -->
          <div class="receipt-remarks-title">Remarks :</div>
          <div class="receipt-remarks-text">
            <strong>Included : Taxes and fees TSh <?= number_format($vatTotal) ?></strong>
          </div>
          <div class="receipt-remarks-text">NonSmoke</div>
          <div class="receipt-remarks-footer">
            <div>All special requests are subject to availability upon arrival</div>
            <div>
              For any issues or questions, please visit <a href="<?= $this->Url->build('/help-center') ?>" style="color:#1a73e8;font-weight:700;text-decoration:underline">support.fastnetstays.com</a>.
            </div>
          </div>

          <!-- 7. Notes Box -->
          <div class="receipt-notes-card">
            <div class="receipt-notes-title">Notes</div>
            <div class="receipt-note-item">
              <span class="receipt-note-num">1.</span>
              <div><strong style="color:#D9251D">IMPORTANT: </strong>At check-in, you must present a valid photo ID with your address confirming the same name as the lead guest on the booking. For bookings paid with a credit card, you may also need to present the card used to make the payment. Failure to do so may result in the hotel requesting additional payment or your reservation not being honored.</div>
            </div>
            <div class="receipt-note-item">
              <span class="receipt-note-num">2.</span>
              <div>All rooms are guaranteed on the day of arrival. In the case of a no-show, your room(s) will be released and you will be subject to the terms and conditions of the Cancellation/No-Show Policy specified at the time you made the booking as well as noted in the Confirmation Email.</div>
            </div>
            <div class="receipt-note-item">
              <span class="receipt-note-num">3.</span>
              <div>The total price for this booking does not include mini-bar items, telephone usage, laundry service, etc. The property will bill you directly.</div>
            </div>
            <div class="receipt-note-item">
              <span class="receipt-note-num">4.</span>
              <div>In cases where Breakfast is included with the room rate, please note that certain properties may charge extra for children travelling with their parents. If applicable, the property will bill you directly. Upon arrival, if you have any questions, please verify with the property.</div>
            </div>
          </div>

          <!-- 8. Calm & Minimal Thank You Banner Box -->
          <div class="receipt-thanks-banner">
            <div class="receipt-thanks-title">Thank you for choosing FastNetStays.com!</div>
            <div class="receipt-thanks-sub">We wish you a pleasant and comfortable stay.</div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<!-- Mobile-Style Confirmation Dialog -->
<div id="bkCancelModal" class="cds-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="bkCancelTitle">
  <div class="cds-modal-box">
    <h3 id="bkCancelTitle" class="cds-modal-title">Cancel this stay?</h3>
    <p id="bkCancelDesc" class="cds-modal-body">Booking will be cancelled. This action cannot be undone.</p>
    <div class="cds-modal-actions">
      <button type="button" class="cds-modal-btn-cancel" onclick="closeCancelModal()">Keep stay</button>
      <button type="button" id="bkConfirmCancelBtn" class="cds-modal-btn-confirm">Cancel stay</button>
    </div>
  </div>
</div>

<!-- Reschedule Modal Dialog -->
<div id="bkRescheduleModal" class="cds-modal-overlay" role="dialog" aria-modal="true" style="display:none">
  <div class="cds-modal-box" style="max-width:460px">
    <h3 class="cds-modal-title">Change stay dates</h3>
    <p style="font-size:13px;color:#525252;margin-bottom:14px">Select your new check-in and check-out dates below:</p>
    <div style="display:flex;gap:10px;margin-bottom:16px">
      <div style="flex:1">
        <label style="font-size:12px;font-weight:700;color:#161616;display:block;margin-bottom:4px">Check-in</label>
        <input type="date" id="rescheduleCheckIn" min="<?= date('Y-m-d') ?>" style="width:100%;height:44px;border:1px solid #e0e0e0;padding:0 10px;font-size:13.5px">
      </div>
      <div style="flex:1">
        <label style="font-size:12px;font-weight:700;color:#161616;display:block;margin-bottom:4px">Check-out</label>
        <input type="date" id="rescheduleCheckOut" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" style="width:100%;height:44px;border:1px solid #e0e0e0;padding:0 10px;font-size:13.5px">
      </div>
    </div>
    <div id="rescheduleQuoteBox" style="display:none;background:#edf5ff;border:1px solid #d0e2ff;padding:10px 12px;margin-bottom:16px;font-size:13px;color:#161616"></div>
    <div class="cds-modal-actions">
      <button type="button" class="cds-modal-btn-cancel" onclick="closeReschedule()">Cancel</button>
      <button type="button" id="confirmRescheduleBtn" class="cds-btn cds-btn-primary" style="height:42px;padding:0 18px;font-size:13px;font-weight:700">Confirm change</button>
    </div>
  </div>
</div>

<div id="trivago-toast" style="position:fixed;bottom:24px;right:24px;background:#161616;color:#fff;padding:12px 20px;font-size:13.5px;z-index:9999;display:none;opacity:0;transition:opacity .25s ease;border-radius:0"></div>

<?= $this->element('Booking/my-booking-script') ?>
<?= $this->element('footer', ['skin' => 'skin-light-footer']) ?>

<script>
function openReceiptModal() {
  const modal = document.getElementById('bkReceiptModal');
  if (modal) {
    modal.classList.add('is-open');
    modal.style.display = 'flex';
  }
}

function closeReceiptModal() {
  const modal = document.getElementById('bkReceiptModal');
  if (modal) {
    modal.classList.remove('is-open');
    modal.style.display = 'none';
  }
}

// Auto open receipt if URL has receipt parameter or hash
document.addEventListener('DOMContentLoaded', function() {
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get('view') === 'receipt' || urlParams.get('receipt') === '1' || window.location.hash === '#receipt') {
    openReceiptModal();
  }
});

let pendingRescheduleCode = '';

function openReschedule(bookingCode, currentDates) {
  pendingRescheduleCode = bookingCode;
  const modal = document.getElementById('bkRescheduleModal');
  if (modal) {
    modal.classList.add('is-open');
    modal.style.display = 'flex';
  }
}

function closeReschedule() {
  pendingRescheduleCode = '';
  const modal = document.getElementById('bkRescheduleModal');
  if (modal) {
    modal.classList.remove('is-open');
    modal.style.display = 'none';
  }
}

const confirmRescheduleBtn = document.getElementById('confirmRescheduleBtn');
if (confirmRescheduleBtn) {
  confirmRescheduleBtn.addEventListener('click', async () => {
    const checkIn = document.getElementById('rescheduleCheckIn')?.value;
    const checkOut = document.getElementById('rescheduleCheckOut')?.value;
    if (!checkIn || !checkOut) {
      showBookingToast('Please select both check-in and check-out dates.');
      return;
    }
    showBookingToast('Applying new dates…');
    confirmRescheduleBtn.disabled = true;
    try {
      const csrf = document.querySelector('meta[name="csrfToken"]')?.content || '';
      const res = await fetch('/account/reschedule-apply', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-Token': csrf
        },
        body: JSON.stringify({
          booking_id: pendingRescheduleCode,
          check_in: checkIn,
          check_out: checkOut
        })
      });
      const data = await res.json().catch(() => ({}));
      if (res.ok && data.status !== 'error') {
        showBookingToast('Dates updated successfully.');
        closeReschedule();
        setTimeout(() => { window.location.reload(); }, 1000);
      } else {
        showBookingToast(data.message || 'Could not change dates. Please try again.');
      }
    } catch (e) {
      showBookingToast('Network error while updating dates.');
    } finally {
      confirmRescheduleBtn.disabled = false;
    }
  });
}
</script>


