<?php
$this->assign('title', 'About Us | FastNetStays');
?>

<!-- Include Navbar -->
<?= $this->element('navbar') ?>

<!-- Hero Title Header -->
<section class="bg-cover position-relative" style="background:url(<?= $this->Url->build('/assets/img/bg.jpg'); ?>)no-repeat;" data-overlay="5">
	<div class="container">
		<div class="row align-items-center justify-content-center">
			<div class="col-xl-8 col-lg-10 col-md-12">
				<div class="fpc-capstion text-center my-5">
					<div class="fpc-captions">
						<h1 class="xl-heading text-light font-bold">About FastNetStays</h1>
						<p class="text-light opacity-90 fs-5">Tanzania’s premier accommodation & travel booking marketplace connecting travelers with authentic lodges, beachfront resorts, and city stays.</p>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="fpc-banner"></div>
</section>
<!-- Hero Title Header -->

<!-- Core Mission & Vision -->
<section class="py-5">
	<div class="container">
		<div class="row align-items-center justify-content-between g-5">

			<div class="col-xl-6 col-lg-6 col-md-12">
				<div class="pe-xl-3">
					<span class="label bg-light-danger text-danger font-semibold mb-2 d-inline-block">WHO WE ARE</span>
					<h2 class="lh-base fs-1 fw-bold text-slate-900 mb-4">Empowering Travel Across Tanzania & East Africa</h2>
					<p class="text-slate-600 fs-6 lh-lg mb-3">FastNetStays is a digital travel marketplace built specifically for Tanzania and the broader East African hospitality ecosystem. We bridge modern travelers with verified safari lodges, coastal beach resorts, boutique hotels, and budget guest lodges in key destinations such as Zanzibar, Dar es Salaam, Arusha, Serengeti, Kilimanjaro, and Dodoma.</p>
					<p class="text-slate-600 fs-6 lh-lg mb-4">We eliminate booking friction by offering instant room availability, transparent pricing with no hidden fees, local currency options (TZS / USD), and seamless mobile money payment integration.</p>
					
					<div class="row g-4 mt-2">
						<div class="col-sm-6">
							<div class="d-flex align-items-start gap-3">
								<div class="square--40 circle bg-light-primary text-primary flex-shrink-0"><i class="fa-solid fa-bolt fs-5"></i></div>
								<div>
									<h6 class="fw-bold mb-1">Instant Confirmations</h6>
									<p class="text-sm text-slate-500 mb-0">Direct integration with property managers for real-time room reservations.</p>
								</div>
							</div>
						</div>
						<div class="col-sm-6">
							<div class="d-flex align-items-start gap-3">
								<div class="square--40 circle bg-light-success text-success flex-shrink-0"><i class="fa-solid fa-mobile-screen fs-5"></i></div>
								<div>
									<h6 class="fw-bold mb-1">Local Mobile Money</h6>
									<p class="text-sm text-slate-500 mb-0">Pay effortlessly using M-Pesa, Airtel Money, Tigo Pesa, or Credit Cards.</p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-xl-5 col-lg-6 col-md-12">
				<div class="position-relative rounded-4 overflow-hidden shadow-lg">
					<img src="<?= $this->Url->build('/assets/img/side-3.png'); ?>" class="img-fluid w-100 object-fit-cover" alt="FastNetStays Platform">
				</div>
			</div>

		</div>
	</div>
</section>

<!-- Values & Pillars Section -->
<section class="py-5 gray-simple">
	<div class="container">
		<div class="row justify-content-center text-center mb-5">
			<div class="col-xl-8 col-lg-9 col-md-11">
				<span class="label bg-light-primary text-primary font-semibold mb-2 d-inline-block">OUR CORE PILLARS</span>
				<h2 class="fw-bold text-slate-900">Why Travelers & Property Owners Trust Us</h2>
				<p class="text-slate-600">Built on reliability, local partnership, and guest-first technology.</p>
			</div>
		</div>

		<div class="row g-4">
			<div class="col-xl-4 col-lg-4 col-md-6">
				<div class="card h-100 p-4 rounded-4 border-0 shadow-sm bg-white">
					<div class="square--50 rounded-3 bg-light-warning text-warning d-flex align-items-center justify-content-center fs-3 mb-4"><i class="fa-solid fa-shield-halved"></i></div>
					<h5 class="fw-bold text-slate-900 mb-2">Verified Stays & Cleanliness</h5>
					<p class="text-slate-600 text-sm mb-0">Every listed property undergoes host verification to ensure authentic photos, true room amenities, and reliable service quality.</p>
				</div>
			</div>

			<div class="col-xl-4 col-lg-4 col-md-6">
				<div class="card h-100 p-4 rounded-4 border-0 shadow-sm bg-white">
					<div class="square--50 rounded-3 bg-light-info text-info d-flex align-items-center justify-content-center fs-3 mb-4"><i class="fa-solid fa-handshake-angle"></i></div>
					<h5 class="fw-bold text-slate-900 mb-2">Empowering Local Hosts</h5>
					<p class="text-slate-600 text-sm mb-0">Our dedicated Owner Portal enables Tanzanian lodge owners and hotel managers to publish listings, adjust pricing, and receive direct guest bookings.</p>
				</div>
			</div>

			<div class="col-xl-4 col-lg-4 col-md-6">
				<div class="card h-100 p-4 rounded-4 border-0 shadow-sm bg-white">
					<div class="square--50 rounded-3 bg-light-purple text-purple d-flex align-items-center justify-content-center fs-3 mb-4"><i class="fa-solid fa-headset"></i></div>
					<h5 class="fw-bold text-slate-900 mb-2">24/7 Local Guest Support</h5>
					<p class="text-slate-600 text-sm mb-0">Our local customer assistance team is available around the clock to support booking changes, special requests, and travel inquiries.</p>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- templates/element/Home/index/newsletter.php -->
<?= $this->element('Home/index/newsletter'); ?>

<!-- templates/element/Home/index/log-in.php -->
<?= $this->element('Home/index/log-in'); ?>

<!-- templates/element/Home/index/countries.php -->
<?= $this->element('Home/index/countries'); ?>

<!-- Include Footer -->
<?= $this->element('footer', ['skin' => 'skin-dark-footer']) ?>