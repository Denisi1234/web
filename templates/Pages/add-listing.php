<?php
header('Location: /fastnet/admin_owner_portal/page-login.php');
exit;
$this->assign('title', 'Owner Portal Redirect');
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
    <li class="breadcrumb-item active" aria-current="page" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><span itemprop="name">Add Listing</span><meta itemprop="position" content="2"></li>
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
						<div class="step active" data-target="#step-1">
							<div class="text-center">
								<button type="button" class="step-trigger mb-0" id="steppertrigger1">
									<span class="bs-stepper-circle"><i class="fa-solid fa-check"></i></span>
								</button>
								<h6 class="bs-stepper-label d-none d-md-block">Basic Information</h6>
							</div>
						</div>
						<div class="line"></div>

						<!-- Step 2 -->
						<div class="step" data-target="#step-2">
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
				<h2>Basic Information</h2>

				<!-- Basic Information -->
				<div class="card rounded-3 mt-4 mb-4">
					<!-- Card header -->
					<div class="card-header border-bottom">
						<!-- Title -->
						<h5 class="mb-0"><i class="fa-brands fa-slack me-2"></i>Choose Listing Category</h5>
					</div>

					<!-- Card body START -->
					<div class="card-body">
						<div class="row g-4">
							<!-- Choose type -->
							<div class="col-12">
								<label class="form-label">Choose listing Type<span class="text-danger">*</span></label>
								<select class="form-control select">
									<option>Select Type</option>
									<option value="0">Hotel</option>
									<option value="1">Villa</option>
									<option value="2">Property</option>
									<option value="3">Cabs</option>
									<option value="4">House</option>
									<option value="5">Destination</option>
								</select>
							</div>

							<!-- Listing Name -->
							<div class="col-12">
								<label class="form-label">Listing Name<span class="text-danger">*</span></label>
								<input class="form-control" type="text" placeholder="Enter place name">
								<small>A catchy name usually includes: House name - Room name - A tourist destination</small>
							</div>

							<!-- listing type -->
							<div class="col-12">
								<label class="form-label">Is your listing set as a personal or guest use *</label>
								<div class="d-sm-flex">
									<!-- Radio -->
									<div class="form-check radio-bg-light me-4">
										<input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault1" checked="">
										<label class="form-check-label" for="flexRadioDefault1">
											Entire Place
										</label>
									</div>
									<!-- Radio -->
									<div class="form-check radio-bg-light me-4">
										<input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault2">
										<label class="form-check-label" for="flexRadioDefault2">
											For Guest
										</label>
									</div>
									<!-- Radio -->
									<div class="form-check radio-bg-light">
										<input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault3">
										<label class="form-check-label" for="flexRadioDefault3">
											For Personal
										</label>
									</div>
								</div>
							</div>

							<!-- Short description -->
							<div class="col-12">
								<label class="form-label">Short description<span class="text-danger">*</span></label>
								<textarea class="form-control ht-200" placeholder="Enter keywords"></textarea>
							</div>
						</div>
					</div>
					<!-- Card body END -->
				</div>

				<!-- Listing Address Information -->
				<div class="card rounded-3 mb-4">
					<!-- Card header -->
					<div class="card-header border-bottom">
						<!-- Title -->
						<h5 class="mb-0"><i class="fa-solid fa-location-dot me-2"></i>Listing Locations</h5>
					</div>

					<!-- Card body START -->
					<div class="card-body">
						<div class="row g-4">

							<!-- Choose Country -->
							<div class="col-xl-6 col-lg-6 col-12">
								<label class="form-label">Country/Region<span class="text-danger">*</span></label>
								<select class="form-control select">
									<option>Select Country</option>
									<option value="0">India</option>
									<option value="1">United State</option>
									<option value="2">United Kingdom</option>
									<option value="3">Australia</option>
									<option value="4">Japan</option>
									<option value="5">China</option>
								</select>
							</div>

							<!-- Choose State -->
							<div class="col-xl-6 col-lg-6 col-12">
								<label class="form-label">State<span class="text-danger">*</span></label>
								<select class="form-control select">
									<option>Select State</option>
									<option value="0">Denver</option>
									<option value="1">New York</option>
									<option value="2">California</option>
									<option value="3">Housten</option>
									<option value="4">Liverpool</option>
									<option value="5">Canada</option>
								</select>
							</div>

							<!-- Listing City -->
							<div class="col-xl-6 col-lg-6 col-12">
								<label class="form-label">City/Town<span class="text-danger">*</span></label>
								<select class="form-control select">
									<option>Select City</option>
									<option value="0">Denver</option>
									<option value="1">New York</option>
									<option value="2">California</option>
									<option value="3">Housten</option>
									<option value="4">Liverpool</option>
									<option value="5">Canada</option>
								</select>
							</div>

							<!-- Listing City -->
							<div class="col-xl-6 col-lg-6 col-12">
								<label class="form-label">Zip Code<span class="text-danger">*</span></label>
								<input type="text" class="form-control" placeholder="zipcode">
							</div>

							<!-- Listing City -->
							<div class="col-xl-12 col-lg-12 col-12">
								<label class="form-label">Home/Street<span class="text-danger">*</span></label>
								<input type="text" class="form-control" placeholder="Street">
							</div>

							<!-- Listing City -->
							<div class="col-xl-6 col-lg-6 col-12">
								<label class="form-label">Latitude<span class="text-danger">*</span></label>
								<input type="text" class="form-control" placeholder="Enter Latitude">
							</div>

							<!-- Listing City -->
							<div class="col-xl-6 col-lg-6 col-12">
								<label class="form-label">Longitude<span class="text-danger">*</span></label>
								<input type="text" class="form-control" placeholder="Enter Longitude">
							</div>

							<!-- Short description -->
							<div class="col-12">
								<iframe class="full-width ht-400 grayscale rounded" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3022.9663095343008!2d-74.00425878428698!3d40.74076684379132!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c259bf5c1654f3%3A0xc80f9cfce5383d5d!2sGoogle!5e0!3m2!1sen!2sin!4v1586000412513!5m2!1sen!2sin" height="500" style="border:0;" aria-hidden="false" tabindex="0"></iframe>
							</div>
						</div>
					</div>
					<!-- Card body END -->
				</div>

				<!-- Listing Address Information -->
				<div class="card rounded-3 mt-4 mb-4">
					<!-- Card header -->
					<div class="card-header border-bottom">
						<!-- Title -->
						<h5 class="mb-0"><i class="fa-regular fa-images me-2"></i>Upload Gallery</h5>
					</div>

					<!-- Card body START -->
					<div class="card-body">
						<div class="row g-4">

							<!-- Thumbnail -->
							<div class="col-12">
								<label class="form-label">Upload thumbnail image<span class="text-danger">*</span></label>
								<input class="form-control" type="file" name="my-image" id="image" accept="image/gif, image/jpeg, image/png">
								<p class="small mb-0 mt-2"><b>Note:</b> Upload only jpg, png, gif only with 500x500px width adn height.</p>
							</div>

							<!-- Gallery -->
							<div class="col-12">
								<label class="form-label">Upload Gallery<span class="text-danger">*</span></label>
								<form action="/target" class="dropzone rounded-3 border br-dashed border-2">
									<div class="mx-auto mb-4 z-0">
										<div class="square--60 circle text-primary bg-light-primary mx-auto my-3"><i class="fa-solid fa-arrow-up-from-bracket"></i></div>
									</div>
								</form>
								<p class="small mb-0 mt-2"><b>Note:</b> Upload only jpg, png, gif only with 1920x1000px width adn height.</p>
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
					<a href="<?= $this->Url->build('/add-listing-step-02'); ?>" class="btn btn-md btn-primary fw-semibold">Next<i class="fa-solid fa-arrow-right ms-2"></i></a>
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

<?php $this->start('script'); ?>
	<script>
		Dropzone.autoDiscover = false;

		const myDropzone = new Dropzone(".dropzone", {
			url: "/file/post",
			timeout: 0,
		});
	</script>
<?php $this->end(); ?>