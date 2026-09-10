<?php
$this->assign('title', 'Add-Listing-Step-03 Page');
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
    <li class="breadcrumb-item active" aria-current="page" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><span itemprop="name">Add Listing Step 03</span><meta itemprop="position" content="2"></li>
  </ol>
</nav>
<main id="main-content" style="background:#f8f9fa;min-height:85vh;" role="main">

<!-- Title Start -->
<section class="bg-cover position-relative bg-primary" style="background:#b22118 url(<?= $this->Url->build('/assets/img/bg2.png'); ?>)no-repeat;">
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
						<div class="step completed" data-target="#step-2">
							<div class="text-center">
								<button type="button" class="step-trigger mb-0" id="steppertrigger2">
									<span class="bs-stepper-circle">2</span>
								</button>
								<h6 class="bs-stepper-label d-none d-md-block">Listing Details</h6>
							</div>
						</div>
						<div class="line"></div>

						<!-- Step 3 -->
						<div class="step active" data-target="#step-3">
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

		<div class="row align-items-start">
			<div class="col-xl-12 col-lg-12 col-md-12">
				<div class="card mb-3">
					<div class="car-body px-xl-5 px-lg-4 py-lg-5 py-4">

						<div class="d-flex align-items-center justify-content-center mb-3">
							<div class="square--80 circle text-light bg-success"><i class="fa-solid fa-check-double fs-1"></i></div>
						</div>
						<div class="d-flex align-items-center justify-content-center flex-column text-center mb-5">
							<h3 class="mb-0">Your Listing was added successfully!</h3>
							<p class="text-md mb-0">Listing detail send to: <span class="text-primary">paysupport@shreethemes.in</span></p>
						</div>
						<div class="d-flex align-items-center justify-content-center flex-column mb-4">
							<div class="border br-dashed full-width rounded-2 p-3 pt-0">
								<ul class="row align-items-center justify-content-start g-3 m-0 p-0">
									<li class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-6">
										<div class="d-block">
											<p class="text-dark fw-medium lh-2 mb-0">Order Invoice</p>
											<p class="text-muted mb-0 lh-2">#26545</p>
										</div>
									</li>
									<li class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-6">
										<div class="d-block">
											<p class="text-dark fw-medium lh-2 mb-0">Date</p>
											<p class="text-muted mb-0 lh-2">24 Aug 2026</p>
										</div>
									</li>
									<li class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-6">
										<div class="d-block">
											<p class="text-dark fw-medium lh-2 mb-0">Total Amount</p>
											<p class="text-muted mb-0 lh-2">$772.40</p>
										</div>
									</li>
									<li class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-6">
										<div class="d-block">
											<p class="text-dark fw-medium lh-2 mb-0">Payment Mode</p>
											<p class="text-muted mb-0 lh-2">Visa Card</p>
										</div>
									</li>
									<li class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-6">
										<div class="d-block">
											<p class="text-dark fw-medium lh-2 mb-0">First Name</p>
											<p class="text-muted mb-0 lh-2">Harry</p>
										</div>
									</li>
									<li class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-6">
										<div class="d-block">
											<p class="text-dark fw-medium lh-2 mb-0">Last Name</p>
											<p class="text-muted mb-0 lh-2">Verma</p>
										</div>
									</li>
									<li class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-6">
										<div class="d-block">
											<p class="text-dark fw-medium lh-2 mb-0">Phone</p>
											<p class="text-muted mb-0 lh-2">9584563625</p>
										</div>
									</li>
									<li class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-6">
										<div class="d-block">
											<p class="text-dark fw-medium lh-2 mb-0">Email</p>
											<p class="text-muted mb-0 lh-2">paysupport@shreethemes.in</p>
										</div>
									</li>
								</ul>
							</div>
						</div>

						<div class="text-center d-flex align-items-center justify-content-center">
							<a href="<?= $this->Url->build('/hotel-detail'); ?>" class="btn btn-md btn-light-seegreen fw-semibold mx-2">Preview Your Listing</a>
							<a href="<?= $this->Url->build('/add-listing'); ?>" class="btn btn-md btn-light-primary fw-semibold mx-2">Add New Listing</a>
						</div>

					</div>
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