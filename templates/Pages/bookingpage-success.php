<?php
$this->assign('title', 'Booking Confirmed - FastNet Stays');

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
?>

<!-- Include Navbar -->
<?= $this->element('navbar') ?>

<!-- Booking Success Page -->
<section class="py-5 gray-simple position-relative">
	<div class="container">

		<div class="row align-items-start justify-content-center">
			<div class="col-xl-10 col-lg-11 col-md-12">
				<div class="card mb-3 border border-slate-200 shadow-sm rounded-3">
					<div class="card-body px-xl-5 px-lg-4 py-lg-5 py-4 px-3">

						<div class="d-flex align-items-center justify-content-center mb-3">
							<div class="square--80 circle text-light bg-success d-flex align-items-center justify-content-center">
								<i class="fa-solid fa-check-double fs-1"></i>
							</div>
						</div>
						<div class="d-flex align-items-center justify-content-center flex-column text-center mb-4">
							<h2 class="mb-1 fw-bold text-slate-900">Your Booking Was Confirmed Successfully!</h2>
							<p class="text-md text-slate-600 mb-0">Booking Reference: <span class="text-orange-600 font-bold fs-5"><?= h($reference) ?></span></p>
							<p class="text-xs text-slate-500">A confirmation receipt has been generated for your stay.</p>
						</div>

						<div class="d-flex align-items-center justify-content-center flex-column mb-4">
							<div class="border br-dashed full-width rounded-3 p-4 bg-slate-50">
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
											<p class="text-success fw-bold mb-0"><i class="fa-solid fa-circle-check me-1"></i>Confirmed</p>
										</div>
									</li>
									<li class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-6">
										<div class="d-block">
											<p class="text-slate-500 text-xs text-uppercase fw-bold mb-0">Total Amount</p>
											<p class="text-orange-600 fw-bold mb-0">TZS <?= number_format($totalAmount) ?></p>
										</div>
									</li>
									<li class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
										<div class="d-block">
											<p class="text-slate-500 text-xs text-uppercase fw-bold mb-0">Property & Destination</p>
											<p class="text-slate-900 fw-bold mb-0"><?= h($propName) ?> (<?= h($propCity) ?>)</p>
										</div>
									</li>
									<li class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
										<div class="d-block">
											<p class="text-slate-500 text-xs text-uppercase fw-bold mb-0">Stay Dates</p>
											<p class="text-slate-900 fw-bold mb-0"><?= date('d M Y', strtotime($checkIn)) ?> - <?= date('d M Y', strtotime($checkOut)) ?></p>
										</div>
									</li>
									<li class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
										<div class="d-block">
											<p class="text-slate-500 text-xs text-uppercase fw-bold mb-0">Guest Name</p>
											<p class="text-slate-900 fw-medium mb-0"><?= h($guestName) ?></p>
										</div>
									</li>
									<li class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
										<div class="d-block">
											<p class="text-slate-500 text-xs text-uppercase fw-bold mb-0">Payment Method (Local)</p>
											<p class="text-slate-900 fw-bold mb-0">
												<i class="fa-solid fa-mobile-screen-button text-success me-1"></i>
												<?= h($paymentMethodLabel) ?> <span class="text-muted fw-normal text-xs">(<?= h($paymentPhone) ?>)</span>
											</p>
										</div>
									</li>
									<li class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
										<div class="d-block">
											<p class="text-slate-500 text-xs text-uppercase fw-bold mb-0">Guest Contact</p>
											<p class="text-slate-900 fw-medium mb-0"><?= h($guestPhone) ?> (<?= h($guestEmail) ?>)</p>
										</div>
									</li>
								</ul>
							</div>
						</div>

						<div class="text-center d-flex align-items-center justify-content-center flex-wrap gap-2">
							<a href="<?= $this->Url->build('/'); ?>" class="btn btn-md btn-light-seegreen fw-bold rounded-full px-4">Browse More Stays</a>
							<a href="<?= $this->Url->build('/my-booking'); ?>" class="btn btn-md btn-primary fw-bold rounded-full px-4">View My Bookings</a>
							<button type="button" data-bs-toggle="modal" data-bs-target="#invoice" class="btn btn-md btn-light-primary fw-bold rounded-full px-4">
								<i class="fa-solid fa-receipt me-1"></i>View Invoice Receipt
							</button>
						</div>

					</div>
				</div>

			</div>
		</div>

	</div>
</section>
<!-- Booking End -->

<!-- templates/element/Listing/booking-page/invoice.php -->
<?= $this->element('Listing/booking-page/invoice'); ?>

<!-- Include Footer -->
<?= $this->element('footer', ['skin' => 'skin-dark-footer']) ?>
