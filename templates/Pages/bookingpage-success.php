<?php
$isPaid = !empty($isPaid);
$paymentStatus = strtolower((string)($paymentStatus ?? ($verifiedBooking['payment_status'] ?? '')));
$this->assign('title', 'Booking details - FastNet Stays');

$bookingId = $queryParams['booking_id'] ?? ($verifiedBooking['id'] ?? '');
$reference = $queryParams['reference'] ?? ($verifiedBooking['booking_code'] ?? ($verifiedBooking['reference'] ?? ''));
$guestName = $queryParams['guest_name'] ?? ($verifiedBooking['guest']['name'] ?? ($verifiedBooking['guest_name'] ?? ''));
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
$propId = $queryParams['property_id'] ?? ($property['id'] ?? ($verifiedBooking['property_id'] ?? ($verifiedBooking['room']['property']['id'] ?? null)));
$canCancel = !in_array($statusLabel, ['CANCELLED', 'COMPLETED'], true) && $reference !== '';
?>

<?= $this->element('navbar') ?>
<?= $this->Html->css('/assets/css/my-booking.css') ?>

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
            <button type="button" class="cds-btn cds-btn-ghost" style="flex:1;height:48px;font-size:13px;font-weight:700" onclick="downloadBookingReceipt('<?= h($reference) ?>')">
              <i class="fa-solid fa-receipt" aria-hidden="true"></i>
              <span>Receipt</span>
            </button>
            <a href="<?= $propId ? $this->Url->build('/hotel-detail-01', ['?' => ['id' => $propId]]) . '#reviews' : $this->Url->build('/hotel-list-01') ?>" class="cds-btn cds-btn-primary" style="flex:1;height:48px;font-size:13px;font-weight:700">
              <i class="fa-regular fa-star" aria-hidden="true"></i>
              <span>Review</span>
            </a>
          </div>
          <a href="<?= $this->Url->build('/help-center') ?>#helpTicket" class="cds-btn cds-btn-ghost" style="width:100%;height:48px;font-size:13px;font-weight:700">
            <i class="fa-regular fa-comment-dots" aria-hidden="true"></i>
            <span>Message support</span>
          </a>
        <?php else: ?>
          <!-- Active / Upcoming stays -->
          <div style="display:flex;gap:8px;width:100%">
            <?php if ($canCancel): ?>
            <button type="button" class="cds-btn cds-btn-danger" style="flex:1;height:48px;font-size:13px;font-weight:700" onclick="openCancelModal('<?= h($reference) ?>', '<?= h($guestEmail) ?>')">
              Cancel stay
            </button>
            <?php endif; ?>
            <button type="button" class="cds-btn cds-btn-ghost" style="flex:1;height:48px;font-size:13px;font-weight:700" onclick="downloadBookingReceipt('<?= h($reference) ?>')">
              <i class="fa-solid fa-receipt" aria-hidden="true"></i>
              <span>Receipt</span>
            </button>
          </div>
          <div style="display:flex;gap:8px;width:100%">
            <a href="<?= $this->Url->build('/help-center') ?>#helpTicket" class="cds-btn cds-btn-ghost" style="flex:1;height:48px;font-size:13px;font-weight:700">
              <i class="fa-regular fa-comment-dots" aria-hidden="true"></i>
              <span>Contact support</span>
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
