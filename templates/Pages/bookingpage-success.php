<?php
$isPaid = !empty($isPaid);
$paymentStatus = strtolower((string)($paymentStatus ?? ($verifiedBooking['payment_status'] ?? '')));
$this->assign('title', $isPaid ? 'Booking Confirmed - FastNet Stays' : 'Booking Details - FastNet Stays');

$bookingId = $queryParams['booking_id'] ?? '';
$reference = $queryParams['reference'] ?? '';
$guestName = $queryParams['guest_name'] ?? '';
$guestEmail = $queryParams['guest_email'] ?? '';
$guestPhone = $queryParams['guest_phone'] ?? '';
$checkIn = $queryParams['check_in'] ?? '';
$checkOut = $queryParams['check_out'] ?? '';
$totalAmount = (float)($queryParams['total_amount'] ?? 0);

$rawPm = strtolower($queryParams['payment_method'] ?? 'vodacom');
$pmLabels = [
    'vodacom' => 'Vodacom M-Pesa',
    'mpesa' => 'M-Pesa',
    'tigo' => 'Tigo Pesa',
    'airtel' => 'Airtel Money',
    'halotel' => 'HaloPesa (Halotel)',
];
$paymentMethodLabel = $pmLabels[$rawPm] ?? 'Mobile Money';
$paymentPhone = $queryParams['payment_phone'] ?? '';

$propName = $queryParams['property_name'] ?: ($property['name'] ?? '');
$propCity = $queryParams['property_city'] ?: ($property['city'] ?? '');
$propImg = '';
if (is_array($property ?? null)) {
    $propImg = (string)($property['image_url'] ?? ($property['main_image'] ?? ($property['primary_image_url'] ?? '')));
}
if (trim($propImg) === '') $propImg = '/assets/img/hotel/hotel-1.jpg';
$bkStatusRaw = strtolower((string)($bookingStatus ?? ($verifiedBooking['status'] ?? ($isPaid ? 'confirmed' : $paymentStatus))));
$bkPillCls = match(true) {
    $bkStatusRaw === 'confirmed' => 'cds-t-confirmed',
    $bkStatusRaw === 'pending' => 'cds-t-pending',
    $bkStatusRaw === 'completed' => 'cds-t-completed',
    in_array($bkStatusRaw, ['checked in', 'checked-in']) => 'cds-t-checkin',
    in_array($bkStatusRaw, ['cancelled', 'canceled']) => 'cds-t-cancelled',
    default => 'cds-t-confirmed',
};
$bkPillLabel = $bkStatusRaw !== '' ? ucfirst($bkStatusRaw) : ($isPaid ? 'Confirmed' : 'Pending');
?>

<!-- Include Navbar -->
<?= $this->Html->css('/assets/css/google-travel-layout.css') ?>
<?= $this->Html->css('/assets/css/google-travel-home.css') ?>
<style>
/* Booking details mirror the mobile Carbon details screen: sharp, hairline, flat. */
.booking-success-card{border:1px solid #e0e0e0 !important;border-radius:0 !important;box-shadow:none !important;overflow:hidden;background:#fff}
.booking-success-emblem{width:72px;height:72px;background:#fff;border:1px solid #e0e0e0;display:flex;align-items:center;justify-content:center}
.booking-success-photo{position:relative;display:block;background:#f4f4f4}
.booking-success-photo img{width:100%;height:220px;object-fit:cover;display:block}
.booking-success-pill{position:absolute;top:12px;left:12px}
.booking-success-facts{border:1px solid #e0e0e0;background:#fff;padding:20px}
.booking-success-actions .btn{min-height:48px;border-radius:0 !important;font-size:14px}
.booking-success-actions .btn-primary{background:#0f62fe;border-color:#0f62fe;box-shadow:none !important}
.booking-success-actions .btn-outline{background:#fff;border:1px solid #e0e0e0;color:#161616;box-shadow:none !important}
@media(max-width:768px){.booking-success-photo img{height:180px}}
</style>
<?= $this->element('navbar') ?>
<?= $this->element('breadcrumb-schema', ['label' => $isPaid ? 'Booking confirmed' : 'Booking details']) ?>
<main id="main-content" style="background:var(--cds-gray-10);min-height:85vh;" role="main">

<!-- Booking Success Page -->
<section class="py-5 gray-simple position-relative">
	<div class="container">

		<div class="row align-items-start justify-content-center">
			<div class="col-xl-10 col-lg-11 col-md-12">
				<div class="card mb-3 booking-success-card">
					<div class="card-body px-xl-5 px-lg-4 py-lg-5 py-4 px-3">

						<div class="d-flex align-items-center justify-content-center mb-3">
							<div class="booking-success-emblem">
								<i class="fa-solid <?= $isPaid ? 'fa-check' : 'fa-hourglass-half' ?> fs-2" style="color:<?= $isPaid ? '#0f62fe' : '#8e6a00' ?>"></i>
							</div>
						</div>
						<div class="d-flex align-items-center justify-content-center flex-column text-center mb-4">
							<h2 class="mb-1 fw-bold" style="color:#161616;font-size:22px"><?= $isPaid ? 'Your Booking Was Confirmed Successfully!' : 'Booking Details' ?></h2>
							<p class="mb-0" style="color:#525252;font-size:14px">Booking Reference: <span style="color:#161616;font-weight:800;font-size:18px"><?= h($reference) ?></span></p>
							<?php if ($isPaid): ?>
							<p style="color:#6f6f6f;font-size:12px" class="mb-0">A confirmation receipt has been generated for your stay.</p>
							<?php else: ?>
							<p style="color:#8e6a00;font-size:13px;font-weight:600" class="mb-0">Payment <?= h($paymentStatus !== '' ? $paymentStatus : 'pending') ?> — this stay is not confirmed yet.</p>
							<p style="color:#6f6f6f;font-size:12px" class="mb-0">Complete the payment from <a href="<?= $this->Url->build('/my-booking') ?>" style="color:#0f62fe;font-weight:700">My bookings</a> to confirm it.</p>
							<?php endif; ?>
						</div>
						<div class="booking-success-photo mb-4">
							<img src="<?= h($propImg) ?>" alt="<?= h($propName !== '' ? $propName : 'Property photo') ?>" loading="lazy" onerror="this.onerror=null;this.src='/assets/img/hotel/hotel-1.jpg'">
							<span class="cds-tag <?= $bkPillCls ?> booking-success-pill"><?= h($bkPillLabel) ?></span>
						</div>
						<div class="d-flex align-items-center justify-content-center flex-column mb-4">
							<div class="booking-success-facts full-width">
								<ul class="row align-items-center justify-content-start g-3 m-0 p-0 list-unstyled">
									<li class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-6">
										<div class="d-block">
											<p class="text-slate-500 text-xs text-uppercase fw-bold mb-0">Booking ID</p>
											<p class="text-slate-900 fw-bold mb-0">#<?= h($bookingId) ?></p>
										</div>
									</li>
									<li class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-6">
										<div class="d-block">
											<p class="text-slate-500 text-xs text-uppercase fw-bold mb-0">Date Issued</p>
											<p class="text-slate-900 fw-bold mb-0"><?= date('d M Y') ?></p>
										</div>
									</li>
									<li class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-6">
										<div class="d-block">
											<p class="text-slate-500 text-xs text-uppercase fw-bold mb-0">Booking Status</p>
											<?php if ($isPaid): ?>
											<p class="text-success fw-bold mb-0"><i class="fa-solid fa-circle-check me-1"></i>Confirmed</p>
											<?php else: ?>
											<p class="fw-bold mb-0" style="color:#b45309"><i class="fa-solid fa-circle-exclamation me-1"></i><?= h(ucfirst($paymentStatus !== '' ? $paymentStatus : 'pending')) ?></p>
											<?php endif; ?>
										</div>
									</li>
									<li class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-6">
										<div class="d-block">
											<p class="text-slate-500 text-xs text-uppercase fw-bold mb-0">Total Amount</p>
											<p class="fw-bold mb-0" style="color:#161616;font-size:18px">TZS <?= number_format($totalAmount) ?></p>
										</div>
									</li>
									<li class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
										<div class="d-block">
											<p class="text-slate-500 text-xs text-uppercase fw-bold mb-0">Property & Destination</p>
											<p class="text-slate-900 fw-bold mb-0"><?= h($propName) ?> (<?= h($propCity) ?>)</p>
										</div>
									</li>
									<?php $ciOk = strtotime((string)$checkIn); $coOk = strtotime((string)$checkOut); ?>
								<?php if ($ciOk && $coOk): ?>
									<li class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
										<div class="d-block">
											<p class="text-slate-500 text-xs text-uppercase fw-bold mb-0">Stay Dates</p>
											<p class="text-slate-900 fw-bold mb-0"><?= date('d M Y', $ciOk) ?> - <?= date('d M Y', $coOk) ?></p>
										</div>
									</li>
								<?php endif; ?>
									<?php if (trim((string)$guestName) !== ''): ?>
									<li class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
										<div class="d-block">
											<p class="text-slate-500 text-xs text-uppercase fw-bold mb-0">Guest Name</p>
											<p class="text-slate-900 fw-medium mb-0"><?= h($guestName) ?></p>
										</div>
									</li>
								<?php endif; ?>
									<li class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
										<div class="d-block">
											<p class="text-slate-500 text-xs text-uppercase fw-bold mb-0">Payment Method (Local)</p>
											<p class="text-slate-900 fw-bold mb-0">
												<i class="fa-solid fa-mobile-screen-button text-success me-1"></i>
												<?= h($paymentMethodLabel) ?> <span class="text-muted fw-normal text-xs">(<?= h($paymentPhone) ?>)</span>
											</p>
										</div>
									</li>
									<?php $contactBits = array_filter([trim((string)$guestPhone), trim((string)$guestEmail)]); ?>
								<?php if (!empty($contactBits)): ?>
									<li class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
										<div class="d-block">
											<p class="text-slate-500 text-xs text-uppercase fw-bold mb-0">Guest Contact</p>
											<p class="text-slate-900 fw-medium mb-0"><?= h(implode(' · ', $contactBits)) ?></p>
										</div>
									</li>
								<?php endif; ?>
								</ul>
							</div>
						</div>

						<?php
						$ciTs = strtotime((string)$checkIn);
						$coTs = strtotime((string)$checkOut);
						$todayTs = strtotime('today');
						$guideDone = in_array(strtolower((string)($bookingStatus ?? '')), ['checked in', 'checked-in', 'completed'], true);
						$guideOpen = !$guideDone && $ciTs && $ciTs <= $todayTs;
						$guideDays = (!$guideDone && !$guideOpen && $ciTs && $ciTs > $todayTs) ? (int)round(($ciTs - $todayTs) / 86400) : null;
						$guideAccent = $guideDone ? '#0e6027' : ($guideOpen ? '#0f62fe' : '#8e6a00');
						$guideTint = $guideDone ? '#defbe6' : ($guideOpen ? '#edf5ff' : '#fcf4d6');
						$guideIcon = $guideDone ? 'fa-circle-check' : ($guideOpen ? 'fa-plane-arrival' : 'fa-calendar-clock');
						$guideTitle = $guideDone ? 'Checked in — enjoy your stay' : ($guideOpen ? 'Check-in is open' : ($guideDays !== null ? 'Check-in opens ' . date('d M Y', $ciTs) : 'Check-in'));
						$guideSub = $guideDone
							? 'Your arrival is confirmed. For anything during your stay, contact support below.'
							: ($guideOpen
								? 'You can arrive from today. Show your receipt QR code and a photo ID at the front desk.'
								: ($guideDays !== null
									? 'Your stay starts ' . ($guideDays <= 1 ? 'tomorrow' : 'in ' . $guideDays . ' days') . '. Come back on the day with your receipt.'
									: 'Your check-in details will appear here once dates are confirmed.'));
						?>
						<div class="mb-4" style="background:#fff;border:1px solid #e0e0e0;padding:20px">
							<div class="d-flex align-items-start gap-3">
								<div style="width:44px;height:44px;flex:0 0 44px;border-radius:50%;background:<?= $guideTint ?>;display:flex;align-items:center;justify-content:center">
									<i class="fa-solid <?= $guideIcon ?>" style="color:<?= $guideAccent ?>;font-size:20px"></i>
								</div>
								<div>
									<h3 style="font-size:16px;font-weight:800;color:#161616;margin:0 0 4px"><?= h($guideTitle) ?></h3>
									<p style="font-size:13px;color:#525252;margin:0 0 10px;line-height:1.5"><?= h($guideSub) ?></p>
									<ol style="font-size:13px;color:#161616;margin:0 0 4px;padding-left:20px;line-height:1.7">
										<li>On arrival day, notify the property through your booking.</li>
										<li>Show the receipt QR code and a photo ID at the front desk.</li>
										<li>The host confirms — the stay flips to Checked In.</li>
									</ol>
									<a href="<?= $this->Url->build('/help-center') ?>#helpTicket" style="font-size:13px;font-weight:700;color:#0f62fe">Contact support →</a>
								</div>
							</div>
						</div>

						<div class="booking-success-actions text-center d-flex align-items-center justify-content-center flex-wrap gap-2">
							<a href="<?= $this->Url->build('/'); ?>" class="btn fw-bold px-4 btn-outline" style="padding:12px 20px;color:#161616">Browse More Stays</a>
							<a href="<?= $this->Url->build('/my-booking'); ?>" class="btn fw-bold px-4 btn-primary" style="padding:12px 20px">View My Bookings</a>
							<?php
							$detailsMovable = $reference !== '' && !in_array(strtolower((string)($bookingStatus ?? '')), ['cancelled', 'canceled', 'completed'], true);
							$detailsDatesLabel = (($ciOk ? date('d M Y', $ciOk) : '') !== '' && ($coOk ? date('d M Y', $coOk) : '') !== '') ? (date('d M Y', $ciOk) . ' → ' . date('d M Y', $coOk)) : '';
							?>
							<?php if ($detailsMovable): ?>
							<button type="button" class="btn fw-bold px-4 btn-outline" style="padding:12px 20px;color:#da1e28;border-color:#f4c7c7" onclick="cancelBookingAction('<?= h($reference) ?>')">Cancel stay</button>
							<button type="button" class="btn fw-bold px-4 btn-outline" style="padding:12px 20px" onclick="openReschedule('<?= h($reference) ?>', '<?= h($detailsDatesLabel) ?>')">Change dates</button>
							<?php endif; ?>
							<a href="<?= $this->Url->build('/help-center') ?>#helpTicket" class="btn fw-bold px-4 btn-outline" style="padding:12px 20px">Support</a>
							<?php if ($isPaid && !empty($verifiedBooking) && (float)($verifiedBooking['total_price'] ?? 0) > 0): ?>
							<button type="button" data-bs-toggle="modal" data-bs-target="#invoice" class="btn fw-bold px-4 btn-outline" style="padding:12px 20px;color:#0f62fe;border-color:#0f62fe">
								<i class="fa-solid fa-receipt me-1"></i>View Invoice Receipt
							</button>
							<?php endif; ?>
							<?php if (!empty($verifiedBooking) && ($verifiedBooking['payment_status'] ?? '') === 'paid'): ?>
							<button type="button" id="downloadReceiptBtn"
							        class="btn fw-bold px-4 btn-primary"
							        style="padding:12px 20px"
							        data-booking-id="<?= h((string)($verifiedBooking['id'] ?? '')) ?>">
								<i class="fa-solid fa-file-arrow-down me-1"></i>Download receipt
							</button>
							<?php endif; ?>
						</div>

					</div>
				</div>

			</div>
		</div>

	</div>
</section>
<!-- Booking End -->

<?php if ($isPaid): ?>
<!-- templates/element/Listing/booking-page/invoice.php -->
<?= $this->element('Listing/booking-page/invoice'); ?>
<?php endif; ?>

<!-- Include Footer -->
</main>
<div id="trivago-toast" style="position:fixed;bottom:24px;right:24px;background:#161616;color:#fff;padding:12px 20px;font-size:13.5px;z-index:9999;display:none;opacity:0;transition:opacity .25s ease"></div>
<?= $this->element('Booking/my-booking-script') ?>
<?= $this->element('footer', ['skin' => 'skin-light-footer']) ?>

<script>
// E-receipt download -> POST /booking-receipt -> POST /receipts/generate
(function () {
  var btn = document.getElementById('downloadReceiptBtn');
  if (!btn) return;
  var label = btn.innerHTML;

  btn.addEventListener('click', function () {
    var bookingId = btn.getAttribute('data-booking-id');
    if (!bookingId) return;

    btn.disabled = true;
    btn.innerHTML = 'Preparing receipt\u2026';

    var body = new URLSearchParams();
    body.set('booking_id', bookingId);

    fetch('/booking-receipt', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: body.toString(),
      credentials: 'same-origin'
    })
      .then(function (r) { return r.json().catch(function () { return {}; }); })
      .then(function (data) {
        if (data && data.status === 'success' && data.receipt_url) {
          window.open(data.receipt_url, '_blank', 'noopener');
        } else {
          if (typeof window.fnsToast === 'function') window.fnsToast((data && data.message) || 'Could not generate your receipt.');
        }
      })
      .catch(function () { if (typeof window.fnsToast === 'function') window.fnsToast('Could not reach the server. Please try again.'); })
      .finally(function () {
        btn.disabled = false;
        btn.innerHTML = label;
      });
  });
})();
</script>
