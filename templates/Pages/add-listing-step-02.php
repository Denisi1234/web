<?php
$this->assign('title', 'Add-Listing-Step-02 Page');
?>

<!-- Include Navbar -->
<?= $this->Html->css('/assets/css/google-travel-layout.css') ?>
<?= $this->Html->css('/assets/css/google-travel-home.css') ?>
<?= $this->element('navbar') ?>
<div class="gh-m-tabs" role="tablist" aria-label="Travel types">
  <a href="/?explore=1" role="tab">Explore</a>
  <a href="/?homes=1" role="tab">Homes</a>
  <a href="/" role="tab" class="active" aria-selected="true">Hotels</a>
  <a href="/?destination=Vacation" role="tab">Vacation rentals</a>
</div>
<nav aria-label="Breadcrumb" class="container-fluid px-2 px-lg-2" style="max-width:100%;margin:0 auto;background:#f8f9fa;">
  <ol class="breadcrumb mb-0 py-1" style="background:transparent;font-size:12px;line-height:1.2;--bs-breadcrumb-divider:'›';" itemscope itemtype="https://schema.org/BreadcrumbList">
    <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a href="/" itemprop="item" style="color:#5f6368;text-decoration:none;"><span itemprop="name">Home</span></a><meta itemprop="position" content="1"></li>
    <li class="breadcrumb-item active" aria-current="page" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><span itemprop="name">Add Listing Step 02</span><meta itemprop="position" content="2"></li>
  </ol>
</nav>
<main id="main-content" style="background:#f8f9fa;min-height:85vh;" role="main">

<!-- Title Start -->
<section class="bg-cover position-relative bg-primary" style="background: url(<?= $this->Url->build('/assets/img/bg2.png'); ?>)no-repeat;">
	<div class="container">
		<div class="row align-items-center justify-content-center">
			<div class="col-xl-7 col-lg-9 col-md-12">

				<div class="fpc-capstion text-center my-4">
					<div class="fpc-captions">
						<h1 class="fs-1 lh-base text-light">Add Your Listing</h1>
						<p class="text-light">Cicero famously orated against his political opponent Lucius Sergius Catilina. Occasionally the first Oration against Catiline is taken for type specimens</p>
					</div>
				</div>

			</div>
		</div>
	</div>
</section>
<!-- Title End -->

<!-- Booking Page -->
<section class="gray-simple position-relative">
	<div class="container">

		<div class="row">
			<div class="col-xl-12 col-lg-12 col-md-12">
				<div id="stepper" class="bs-stepper stepper-outline mb-5">
					<div class="bs-stepper-header">
						<!-- Step 1 -->
						<div class="step completed" data-target="#step-1">
							<div class="text-center">
								<button type="button" class="step-trigger mb-0" id="steppertrigger1">
									<span class="bs-stepper-circle"><i class="fa-solid fa-check"></i></span>
								</button>
								<h6 class="bs-stepper-label d-none d-md-block">Basic Information</h6>
							</div>
						</div>
						<div class="line"></div>

						<!-- Step 2 -->
						<div class="step active" data-target="#step-2">
							<div class="text-center">
								<button type="button" class="step-trigger mb-0" id="steppertrigger2">
									<span class="bs-stepper-circle">2</span>
								</button>
								<h6 class="bs-stepper-label d-none d-md-block">Listing Details</h6>
							</div>
						</div>
						<div class="line"></div>

						<!-- Step 3 -->
						<div class="step" data-target="#step-3">
							<div class="text-center">
								<button type="button" class="step-trigger mb-0" id="steppertrigger3">
									<span class="bs-stepper-circle">3</span>
								</button>
								<h6 class="bs-stepper-label d-none d-md-block">View & Confirm</h6>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-xl-12 col-lg-12 col-md-12">
				<h2>Listing Information</h2>

				<!-- Basic Information -->
				<div class="card rounded-3 mt-4 mb-4">
					<!-- Card header -->
					<div class="card-header border-bottom">
						<!-- Title -->
						<h5 class="mb-0"><i class="fa-brands fa-slack me-2"></i>Listing Information</h5>
					</div>

					<!-- Card body START -->
					<div class="card-body">
						<div class="row g-4">
							<!-- Choose type -->
							<div class="col-12">
								<label class="form-label">Select Amenities<span class="text-danger">*</span></label>
								<select class="form-control multiple-select" multiple="multiple">
									<option>Select Amenities</option>
									<option value="0">WiFi</option>
									<option value="1">Air Condition</option>
									<option value="2">Dry</option>
									<option value="3">Gym</option>
									<option value="4">Spa</option>
									<option value="5">Parking</option>
								</select>
							</div>

							<!-- Listing Description -->
							<div class="col-12">
								<label class="form-label">Listing Description<span class="text-danger">*</span></label>
								<textarea class="form-control ht-250"></textarea>
							</div>
						</div>
					</div>
					<!-- Card body END -->
				</div>

				<!-- Rooms Information -->
				<div class="card rounded-3 mb-4">
					<!-- Card header -->
					<div class="card-header border-bottom">
						<!-- Title -->
						<h5 class="mb-0"><i class="fa-solid fa-hotel me-2"></i>Your Listing Size</h5>
					</div>

					<!-- Card body START -->
					<div class="card-body">
						<div class="row g-4">

							<!-- Listing City -->
							<div class="col-xl-4 col-lg-4 col-12">
								<label class="form-label">Total Floor<span class="text-danger">*</span></label>
								<input type="text" class="form-control" placeholder="Enter Total Floor">
							</div>

							<!-- Listing City -->
							<div class="col-xl-4 col-lg-4 col-12">
								<label class="form-label">Total Rooms<span class="text-danger">*</span></label>
								<input type="text" class="form-control" placeholder="Enter Total Rooms">
							</div>

							<!-- Listing City -->
							<div class="col-xl-4 col-lg-4 col-12">
								<label class="form-label">Room Area<span class="text-danger">*</span></label>
								<input type="text" class="form-control" placeholder="Enter Area in sqft.">
							</div>
						</div>
					</div>
					<!-- Card body END -->
				</div>

				<!-- Room Options -->
				<div class="card rounded-3 mt-4 mb-4">
					<!-- Card header -->
					<div class="card-header border-bottom">
						<!-- Title -->
						<h5 class="mb-0"><i class="fa-regular fa-images me-2"></i>Room Options</h5>
					</div>

					<!-- Card body START -->
					<div class="card-body">
						<div class="row g-4">

							<!-- Room Name -->
							<div class="col-xl-6 col-lg-6 col-12">
								<label class="form-label">Room Name<span class="text-danger">*</span></label>
								<input type="text" class="form-control" placeholder="Enter Room Name">
							</div>

							<!-- Room Name -->
							<div class="col-xl-6 col-lg-6 col-12">
								<label class="form-label">Room Image<span class="text-danger">*</span></label>
								<input class="form-control" type="file" name="my-image" id="image" accept="image/gif, image/jpeg, image/png">
							</div>

							<!-- Room Base Price -->
							<div class="col-xl-6 col-lg-6 col-12">
								<label class="form-label">Room Base Price<span class="text-danger">*</span></label>
								<input type="text" class="form-control" placeholder="Enter Base Price">
							</div>

							<!-- Room Ratting -->
							<div class="col-xl-6 col-lg-6 col-12">
								<label class="form-label">Room Rating<span class="text-danger">*</span></label>
								<select class="form-control select">
									<option>Select Rating</option>
									<option value="0">05 Star</option>
									<option value="1">04 Star</option>
									<option value="2">03 Star</option>
									<option value="3">02 Star</option>
									<option value="4">01 Start</option>
								</select>
							</div>
							<!-- Bed Type -->
							<div class="col-xl-6 col-lg-6 col-12">
								<label class="form-label">Bed Type<span class="text-danger">*</span></label>
								<select class="form-control select">
									<option>Select Type</option>
									<option value="0">King Bed Size</option>
									<option value="1">Double Bed</option>
									<option value="2">King Double Bed</option>
									<option value="3">Single Bed</option>
								</select>
							</div>

							<!-- Discount -->
							<div class="col-xl-6 col-lg-6 col-12">
								<label class="form-label">Discount</label>
								<input type="text" class="form-control" placeholder="Enter Discount">
							</div>

							<!-- Refund Policy -->
							<div class="col-xl-6 col-lg-6 col-12">
								<label class="form-label">Refund Policy<span class="text-danger">*</span></label>
								<select class="form-control select">
									<option>Refund Type</option>
									<option value="0">Refundable</option>
									<option value="1">non-Refundable</option>
								</select>
							</div>

							<!-- Charges -->
							<div class="col-xl-6 col-lg-6 col-12">
								<label class="form-label">Charges<span class="text-danger">*</span></label>
								<input type="text" class="form-control" placeholder="Enter Charges">
							</div>

							<!-- Charges -->
							<div class="col-xl-6 col-lg-6 col-12">
								<button type="button" class="btn btn-light-primary btn-md px-5 fw-medium">Add More Rooms</button>
							</div>

						</div>
					</div>
					<!-- Card body END -->
				</div>

			</div>
		</div>

		<div class="row align-items-start">
			<div class="col-xl-12 col-lg-12 col-md-12">
				<div class="text-center d-flex align-items-center justify-content-center mt-4">
					<a href="<?= $this->Url->build('/add-listing'); ?>" class="btn btn-md btn-dark fw-semibold me-2"><i class="fa-solid fa-arrow-left me-2"></i>Previous</a>
					<a href="<?= $this->Url->build('/add-listing-step-03'); ?>" class="btn btn-md btn-primary fw-semibold ms-2">Submit<i class="fa-solid fa-arrow-right ms-2"></i></a>
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
</main>
<?= $this->element('footer', ['skin' => 'skin-light-footer']) ?>