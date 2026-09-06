<?php
$this->assign('title', 'Contact Us | FastNetStays');
?>

<!-- Include Navbar -->
<?= $this->element('navbar') ?>

<!-- Hero Title Header -->
<section class="bg-cover position-relative" style="background:url(<?= $this->Url->build('/assets/img/bg-title.jpg'); ?>)no-repeat;" data-overlay="5">
	<div class="container">
		<div class="row align-items-center justify-content-center">
			<div class="col-xl-7 col-lg-9 col-md-12">
				<div class="fpc-capstion text-center my-4">
					<div class="fpc-captions">
						<h1 class="xl-heading text-light font-bold">Contact FastNetStays</h1>
						<p class="text-light opacity-90">Have questions about your booking or listing your property? Reach out to our local team in Dar es Salaam & Arusha.</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
<!-- Hero Title Header -->

<!-- Form Section -->
<section class="gray-simple">
	<div class="container">

		<div class="row align-items-stretch justify-content-between g-4">

			<div class="col-xl-7 col-lg-7 col-md-12">
				<div class="contactForm bg-white p-4 p-md-5 rounded-4 border border-slate-200 shadow-sm h-100">

					<?= $this->Form->create(null, [
						'url' => ['controller' => 'Contact', 'action' => 'submit'],
						'id' => 'myForm',
						'class' => "mt-2"
					]) ?>
						<div class="row align-items-center">

							<div class="col-xl-12 col-lg-12 col-md-12">
								<div class="touch-block d-flex flex-column mb-4">
									<h2 class="fw-bold text-slate-900 fs-3">Send Us a Message</h2>
									<p class="text-slate-500 mb-0">Fill out the form below and our Tanzania customer support team will reply within 24 hours.</p>
								</div>
							</div>

							<p class="mb-0" id="error-msg"></p>
							<div id="simple-msg"></div>

							<div class="col-xl-6 col-lg-6 col-md-6">
								<div class="form-group mb-3">
									<label class="form-label font-semibold text-sm text-slate-700" for="name">Your Name</label>
									<input name="name" id="name" type="text" class="form-control" placeholder="Enter Your Full Name" required>
								</div>
							</div>

							<div class="col-xl-6 col-lg-6 col-md-6">
								<div class="form-group mb-3">
									<label class="form-label font-semibold text-sm text-slate-700" for="email">Email Address</label>
									<input name="email" id="email" type="email" class="form-control" placeholder="Enter Your Email Address" required>
								</div>
							</div>

							<div class="col-xl-6 col-lg-6 col-md-6">
								<div class="form-group mb-3">
									<label class="form-label font-semibold text-sm text-slate-700" for="number">Phone Number</label>
									<input name="number" id="number" type="tel" class="form-control" placeholder="+255 700 000 000">
								</div>
							</div>

							<div class="col-xl-6 col-lg-6 col-md-6">
								<div class="form-group mb-3">
									<label class="form-label font-semibold text-sm text-slate-700" for="subject">Subject</label>
									<input name="subject" id="subject" type="text" class="form-control" placeholder="Booking Inquiry / Host Partnership" required>
								</div>
							</div>

							<div class="col-xl-12 col-lg-12 col-md-12">
								<div class="form-group mb-3">
									<label class="form-label font-semibold text-sm text-slate-700" for="comments">Message Details</label>
									<textarea name="comments" id="comments" class="form-control ht-120" placeholder="How can we help you?" required></textarea>
								</div>
							</div>

							<div class="col-xl-12 col-lg-12 col-md-12">
								<div class="form-group mb-0">
									<button type="submit" id="submit" name="send" class="btn fw-semibold btn-primary px-4 py-2.5">
										Send Message<i class="fa-solid fa-paper-plane ms-2"></i>
									</button>
								</div>
							</div>

						</div>
					<?= $this->Form->end() ?>
					
				</div>
			</div>

			<!-- Office Details Cards -->
			<div class="col-xl-4 col-lg-4 col-md-12">
				<div class="card p-4 rounded-4 border br-dashed text-center mb-4">
					<div class="crds-icons d-inline-flex mx-auto mb-3 text-primary fs-2"><i class="fa-solid fa-envelope"></i></div>
					<div class="crds-desc">
						<h5>Drop Us a Mail</h5>
						<p class="text-md lh-2 mb-0">support@fastnetstays.com<br>arusha@fastnetstays.com</p>
					</div>
				</div>

				<div class="card p-4 rounded-4 border br-dashed text-center">
					<div class="crds-icons d-inline-flex mx-auto mb-3 text-primary fs-2"><i class="fa-solid fa-headset"></i></div>
					<div class="crds-desc">
						<h5>Call Us (24/7)</h5>
						<p class="text-md lh-2 mb-0">+255 22 212 3456 (DSM)<br>Toll Free: +255 800 750 000</p>
					</div>
				</div>
			</div>

		</div>

		<!-- Real Tanzania Map Section (Mapbox OpenStreetMap centered on Dar es Salaam & Arusha HQ) -->
		<div class="row mt-5">
			<div class="col-12">
				<div class="card rounded-4 border border-slate-200 overflow-hidden shadow-sm">
					<div class="card-header bg-white py-3 px-4 border-bottom border-slate-100 flex items-center justify-between">
						<h5 class="fw-bold text-slate-900 mb-0"><i class="fa-solid fa-location-dot text-danger me-2"></i>FastNetStays Headquarters Location (Dar es Salaam, Tanzania)</h5>
						<span class="text-xs font-semibold text-slate-500">Lat: -6.7924, Long: 39.2083</span>
					</div>
					<iframe class="full-width ht-450 rounded-bottom" 
							src="https://www.openstreetmap.org/export/embed.html?bbox=39.1800%2C-6.8200%2C39.3000%2C-6.7500&amp;layer=mapnik&amp;marker=-6.7924%2C39.2083" 
							height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
				</div>
			</div>
		</div>

	</div>
</section>
<!-- Form Section End -->

<!-- templates/element/Home/index/newsletter.php -->
<?= $this->element('Home/index/newsletter'); ?>

<!-- templates/element/Home/index/log-in.php -->
<?= $this->element('Home/index/log-in'); ?>

<!-- templates/element/Home/index/countries.php -->
<?= $this->element('Home/index/countries'); ?>

<!-- Include Footer -->
<?= $this->element('footer', ['skin' => 'skin-dark-footer']) ?>