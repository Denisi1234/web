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
<?= $this->Html->css('/assets/css/google-travel-layout.css') ?>
<?= $this->Html->css('/assets/css/google-travel-home.css') ?>
<style>
/* Phase 4 — receipt parity with mobile receipt_screen.dart + tokens r16 shadow F8FAFC */
.booking-success-card{border:1px solid #e8eaed !important;border-radius:16px !important;box-shadow:0 6px 16px rgba(0,0,0,0.05) !important;overflow:hidden}
.booking-success-watermark{background:#9ca3af;color:#fff;font-size:9px;font-weight:700;letter-spacing:0.08em;padding:4px 8px;text-align:center}
.booking-success-qr{width:96px;height:96px;background:#fff;border:1px solid #e8eaed;border-radius:10px;display:flex;align-items:center;justify-content:center}
@media(max-width:768px){.booking-success-card{border-radius:22px !important} .booking-success-watermark{font-size:8px}}
</style>
<?= $this->element('navbar') ?>
<nav aria-label="Breadcrumb" class="container-fluid px-2 px-lg-2" style="max-width:100%;margin:0 auto;background:#f8f9fa;">
  <ol class="breadcrumb mb-0 py-1" style="background:transparent;font-size:12px;line-height:1.2;--bs-breadcrumb-divider:'›';" itemscope itemtype="https://schema.org/BreadcrumbList">
    <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a href="/" itemprop="item" style="color:#5f6368;text-decoration:none;"><span itemprop="name">Home</span></a><meta itemprop="position" content="1"></li>
    <li class="breadcrumb-item active" aria-current="page" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><span itemprop="name">Booking Confirmation</span><meta itemprop="position" content="2"></li>
  </ol>
</nav>
<main id="main-content" style="background:#f8f9fa;min-height:85vh;" role="main">

<!-- Booking Success Page -->
<section class="py-5 gray-simple position-relative">
	<div class="container">

		<div class="row align-items-start justify-content-center">
			<div class="col-xl-10 col-lg-11 col-md-12">
				<div class="card mb-3 booking-success-card">
					<div class="card-body px-xl-5 px-lg-4 py-lg-5 py-4 px-3">

						<div class="d-flex align-items-center justify-content-center mb-3">
							<div style="width:72px;height:72px;border-radius:50%;background:#EBF5FF;border:1px solid #dbeafe;display:flex;align-items:center;justify-content:center">
								<i class="fa-solid fa-check fs-2" style="color:#2563EB"></i>
							</div>
						</div>
						<div class="d-flex align-items-center justify-content-center flex-column text-center mb-4">
							<h2 class="mb-1 fw-bold" style="color:#1a1d25;font-size:22px">Your Booking Was Confirmed Successfully!</h2>
							<p class="mb-0" style="color:#5f6368;font-size:14px">Booking Reference: <span style="color:#C2410C;font-weight:800;font-size:18px"><?= h($reference) ?></span></p>
							<p style="color:#9aa0a6;font-size:12px" class="mb-0">A confirmation receipt has been generated for your stay.</p>
						</div>
						<div class="booking-success-watermark mb-3">fastnetstays.com &nbsp; fastnetstays.com &nbsp; fastnetstays.com &nbsp; fastnetstays.com &nbsp; fastnetstays.com</div>

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
							<a href="<?= $this->Url->build('/'); ?>" class="btn fw-bold rounded-full px-4" style="background:#fff;border:1px solid #e8eaed;border-radius:30px;padding:10px 20px;color:#1a1d25">Browse More Stays</a>
							<a href="<?= $this->Url->build('/my-booking'); ?>" class="btn fw-bold rounded-full px-4" style="background:#2563EB;color:#fff;border-radius:30px;padding:10px 20px;box-shadow:0 4px 12px rgba(37,99,235,0.18)">View My Bookings</a>
							<button type="button" data-bs-toggle="modal" data-bs-target="#invoice" class="btn fw-bold rounded-full px-4" style="background:#F8FAFC;border:1px solid #e8eaed;border-radius:30px;padding:10px 20px;color:#2563EB">
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
</main>
<?= $this->element('footer', ['skin' => 'skin-light-footer']) ?>
