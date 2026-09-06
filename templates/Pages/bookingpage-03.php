<?php
$this->assign('title', 'Bookingpage-03 Page');
?>

<!-- Include Navbar -->
<?= $this->element('navbar') ?>

<!-- Booking Page -->
<section class="pt-4 gray-simple position-relative">
	<div class="container">

		<div class="row">
			<div class="col-xl-12 col-lg-12 col-md-12 mt-6 mt-md-0">
				<div id="stepper" class="bs-stepper stepper-outline mb-5">
					<div class="bs-stepper-header">
						<!-- Step 1 -->
						<div class="step completed" data-target="#step-1">
							<div class="text-center">
								<button type="button" class="step-trigger mb-0" id="steppertrigger1">
									<span class="bs-stepper-circle"><i class="fa-solid fa-check"></i></span>
								</button>
								<h6 class="bs-stepper-label d-none d-md-block">Tour Review</h6>
							</div>
						</div>
						<div class="line"></div>

						<!-- Step 2 -->
						<div class="step completed" data-target="#step-2">
							<div class="text-center">
								<button type="button" class="step-trigger mb-0" id="steppertrigger2">
									<span class="bs-stepper-circle">2</span>
								</button>
								<h6 class="bs-stepper-label d-none d-md-block">Traveler Info</h6>
							</div>
						</div>
						<div class="line"></div>

						<!-- Step 3 -->
						<div class="step active" data-target="#step-3">
							<div class="text-center">
								<button type="button" class="step-trigger mb-0" id="steppertrigger3">
									<span class="bs-stepper-circle">3</span>
								</button>
								<h6 class="bs-stepper-label d-none d-md-block">Make Payment</h6>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="row align-items-start">
			<div class="col-xl-12 col-lg-12 col-md-12">
				<div class="div-title d-flex align-items-center mb-3">
					<h4>Billing Details</h4>
				</div>
				<div class="row align-items-start">
					<div class="col-xl-8 col-lg-8 col-md-12">

						<div class="card mb-3">
							<div class="card-header">
								<h4>Basic Detail</h4>
							</div>
							<div class="card-body">
								<div class="row">

									<div class="col-xl-6 col-lg-6 col-md-6">
										<div class="form-group">
											<label class="form-label">Billing Name</label>
											<input type="text" class="form-control" placeholder="First Name">
										</div>
									</div>
									<div class="col-xl-6 col-lg-6 col-md-6">
										<div class="form-group">
											<label class="form-label">Email</label>
											<input type="text" class="form-control" placeholder="Last Name">
										</div>
									</div>
									<div class="col-xl-6 col-lg-6 col-md-6">
										<div class="form-group">
											<label class="form-label">Phone</label>
											<input type="text" class="form-control" placeholder="Phone Number">
										</div>
									</div>
									<div class="col-xl-6 col-lg-6 col-md-6">
										<div class="form-group">
											<label class="form-label">Address 01</label>
											<input type="text" class="form-control" placeholder="Passport Number">
										</div>
									</div>
									<div class="col-xl-6 col-lg-6 col-md-6">
										<div class="form-group">
											<label class="form-label">Address 02</label>
											<input type="text" class="form-control" placeholder="Passport Number">
										</div>
									</div>
									<div class="col-xl-6 col-lg-6 col-md-6">
										<div class="form-group">
											<label class="form-label">Country</label>
											<input type="text" class="form-control" placeholder="Passport Number">
										</div>
									</div>
									<div class="col-xl-6 col-lg-6 col-md-6">
										<div class="form-group">
											<label class="form-label">City\State</label>
											<input type="text" class="form-control" placeholder="Passport Number">
										</div>
									</div>
									<div class="col-xl-6 col-lg-6 col-md-6">
										<div class="form-group">
											<label class="form-label">Postal Code</label>
											<input type="text" class="form-control" placeholder="Passport Number">
										</div>
									</div>
									<div class="col-xl-12 col-lg-12 col-md-12">
										<div class="form-group">
											<label class="form-label">Special notes</label>
											<textarea class="form-control ht-200"></textarea>
										</div>
									</div>
								</div>
							</div>
						</div>

					</div>

					<div class="col-xl-4 col-lg-4 col-md-12">
						<div class="side-block card rounded-2 p-3">
							<div class="d-flex align-items-center justify-content-between mb-3">
								<h5 class="fw-semibold fs-6 mb-0">Local Mobile Payment</h5>
								<div class="d-flex align-items-start"><a href="<?= $this->Url->build('/payment-detail'); ?>" class="text-md fw-semibold text-primary">Payment Methods</a></div>
							</div>
							<div class="mid-block mb-2">
								<div class="paymntCardsoption-groups">
									<!-- Vodacom M-Pesa -->
									<div class="single-paymntCardsoption d-block position-relative mb-2">
										<div class="paymnt-line active d-flex align-items-center justify-content-start p-2 border rounded-2">
											<div class="position-relative text-center">
												<div class="form-check lg mb-0">
													<input class="form-check-input" type="radio" name="payment" id="vodacom" checked>
													<label class="form-check-label" for="vodacom"></label>
												</div>
											</div>
											<div class="paymnt-line-caps d-flex align-items-center justify-content-start">
												<div class="paymnt-caps-icons d-inline-flex px-2">
													<img src="<?= $this->Url->build('/assets/img/vodacom-logo.png'); ?>" alt="Vodacom M-Pesa" style="max-height: 28px; max-width: 60px; object-fit: contain;">
												</div>
												<div class="paymnt-caps-details ps-2">
													<span class="text-uppercase d-block fw-semibold text-md text-dark lh-2 mb-0">Vodacom M-Pesa</span>
													<span class="text-sm text-muted lh-2">Instant USSD Push</span>
												</div>
											</div>
										</div>
									</div>

									<!-- Tigo Pesa -->
									<div class="single-paymntCardsoption d-block position-relative mb-2">
										<div class="paymnt-line d-flex align-items-center justify-content-start p-2 border rounded-2">
											<div class="position-relative text-center">
												<div class="form-check lg mb-0">
													<input class="form-check-input" type="radio" name="payment" id="tigo">
													<label class="form-check-label" for="tigo"></label>
												</div>
											</div>
											<div class="paymnt-line-caps d-flex align-items-center justify-content-start">
												<div class="paymnt-caps-icons d-inline-flex px-2">
													<img src="<?= $this->Url->build('/assets/img/tigo-pesa-logo.jpg'); ?>" alt="Tigo Pesa" style="max-height: 28px; max-width: 60px; object-fit: contain;">
												</div>
												<div class="paymnt-caps-details ps-2">
													<span class="text-uppercase d-block fw-semibold text-md text-dark lh-2 mb-0">Tigo Pesa</span>
													<span class="text-sm text-muted lh-2">Instant USSD Push</span>
												</div>
											</div>
										</div>
									</div>

									<!-- Airtel Money -->
									<div class="single-paymntCardsoption d-block position-relative mb-2">
										<div class="paymnt-line d-flex align-items-center justify-content-start p-2 border rounded-2">
											<div class="position-relative text-center">
												<div class="form-check lg mb-0">
													<input class="form-check-input" type="radio" name="payment" id="airtel">
													<label class="form-check-label" for="airtel"></label>
												</div>
											</div>
											<div class="paymnt-line-caps d-flex align-items-center justify-content-start">
												<div class="paymnt-caps-icons d-inline-flex px-2">
													<img src="<?= $this->Url->build('/assets/img/airtel-logo.png'); ?>" alt="Airtel Money" style="max-height: 28px; max-width: 60px; object-fit: contain;">
												</div>
												<div class="paymnt-caps-details ps-2">
													<span class="text-uppercase d-block fw-semibold text-md text-dark lh-2 mb-0">Airtel Money</span>
													<span class="text-sm text-muted lh-2">Instant USSD Push</span>
												</div>
											</div>
										</div>
									</div>

									<!-- HaloPesa -->
									<div class="single-paymntCardsoption d-block position-relative mb-2">
										<div class="paymnt-line d-flex align-items-center justify-content-start p-2 border rounded-2">
											<div class="position-relative text-center">
												<div class="form-check lg mb-0">
													<input class="form-check-input" type="radio" name="payment" id="halotel">
													<label class="form-check-label" for="halotel"></label>
												</div>
											</div>
											<div class="paymnt-line-caps d-flex align-items-center justify-content-start">
												<div class="paymnt-caps-icons d-inline-flex px-2">
													<img src="<?= $this->Url->build('/assets/img/halotel-logo.jpg'); ?>" alt="HaloPesa" style="max-height: 28px; max-width: 60px; object-fit: contain;">
												</div>
												<div class="paymnt-caps-details ps-2">
													<span class="text-uppercase d-block fw-semibold text-md text-dark lh-2 mb-0">HaloPesa (Halotel)</span>
													<span class="text-sm text-muted lh-2">Instant USSD Push</span>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="summary-block d-block mb-3">
								<h5 class="fw-semibold fs-6">Summary</h5>
								<ul class="list-group list-group-borderless">
									<li class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-1">
										<span class="fw-medium text-sm text-muted mb-0">Payment:</span>
										<span class="fw-semibold text-md">TZS 150,000</span>
									</li>
									<li class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-1">
										<span class="fw-medium text-sm text-muted mb-0">Payment Method fee:</span>
										<span class="fw-semibold text-md">TZS 0</span>
									</li>
									<li class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-1">
										<span class="fw-medium text-sm text-muted mb-0">Total Price:</span>
										<span class="fw-semibold text-success text-md">TZS 150,000</span>
									</li>
								</ul>
							</div>
							<div class="bott-block mb-3">
								<div class="d-flex align-items-center justify-content-center py-2 px-3 rounded-2 bg-light-success mb-2">
									<div class="d-inline-flex text-success fs-2"><i class="fa-solid fa-shield-heart"></i></div>
									<div class="d-inline-flex flex-column ps-2">
										<span class="d-block text-md text-dark fw-semibold lh-2">100% Secure &amp; Protected</span>
										<span class="d-block text-sm text-muted-2 lh-2">Encrypted mobile money checkout</span>
									</div>
								</div>
								<a href="<?= $this->Url->build('/bookingpage-success'); ?>" class="btn fw-medium btn-primary full-width">Confirm and Pay TZS 150,000</a>
							</div>
							<div class="autopay-block-block">
								<div class="d-flex align-items-center justify-content-between">
									<div class="fluy-bkr"><a href="<?= $this->Url->build('/payment-detail'); ?>" class="fw-semibold text-md text-dark"><i class="fa-solid fa-circle-plus me-1"></i>Add Mobile Payment</a></div>
								</div>
							</div>
						</div>
					</div>

				</div>
			</div>

			<div class="col-xl-12 col-lg-12 col-md-12">
				<div class="text-center d-flex align-items-center justify-content-center mt-4">
					<a href="<?= $this->Url->build('/bookingpage-02'); ?>" class="btn btn-md btn-dark fw-semibold mx-2"><i class="fa-solid fa-arrow-left me-2"></i>Previous</a>
					<a href="<?= $this->Url->build('/bookingpage-success'); ?>" class="btn btn-md btn-primary fw-semibold mx-2">Submit & Confirm<i class="fa-solid fa-arrow-right ms-2"></i></a>
				</div>
			</div>
		</div>
	</div>
</section>
<!-- Booking End -->

<!-- templates/element/Home/index/newsletter.php -->
<?= $this->element('Home/index/newsletter'); ?>

<!-- templates/element/Home/index/log-in.php -->
<?= $this->element('Home/index/log-in'); ?>

<!-- templates/element/Home/index/countries.php -->
<?= $this->element('Home/index/countries'); ?>

<!-- Include Footer -->
<?= $this->element('footer', ['skin' => 'skin-dark-footer']) ?>