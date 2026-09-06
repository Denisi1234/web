<?php
$this->assign('title', 'Help-Center Page');
?>

<!-- Include Navbar -->
<?= $this->element('navbar') ?>

<!-- Booking Title -->
<section class="bg-cover position-relative bg-primary" style="background:url(<?= $this->Url->build('/assets/img/bg2.png'); ?>)no-repeat;">
	<div class="container">
		<div class="row align-items-center justify-content-center">
			<div class="col-xl-12 col-lg-12 col-md-12">

				<div class="fpc-capstion text-center my-4">
					<div class="fpc-captions">
						<h1 class="fs-1 lh-base text-light">How Can We Help You?</h1>
						<form class="col-md-6 bg-body rounded mx-auto p-2 mb-3">
							<div class="input-group">
								<input class="form-control border-0 me-1" type="text" placeholder="Search question...">
								<button type="button" class="btn btn-dark rounded px-xl-5 mb-0">Search</button>
							</div>
						</form>
						<div class="row align-items-center mt-5 mb-2">
							<div class="col-xl-6 col-lg-8 col-md-9 mx-auto">
								<h6 class="text-white mb-3">Popular questions</h6>
								<!-- Questions List START -->
								<div class="list-group hstack gap-3 justify-content-center flex-wrap mb-0">
									<a class="btn-link text-white text-decoration-underline p-0 mb-0" href="#"> How can we help?</a>
									<a class="btn-link text-white text-decoration-underline p-0 mb-0" href="#"> How to upload data to the system? </a>
									<a class="btn-link text-white text-decoration-underline p-0 mb-0" href="#"> Installation Guide?</a>
									<a class="btn-link text-white text-decoration-underline p-0 mb-0" href="#"> How to view expired tickets? </a>
									<a class="btn-link text-white p-0 mb-0" href="#!">View all question</a>
								</div>
								<!-- Questions list END -->
							</div>
						</div>
					</div>
				</div>

			</div>
		</div>
	</div>
</section>
<!-- Booking Title -->

<!-- Help Box Section -->
<section class="pt-0">
	<div class="container">
		<div class="row justify-content-center g-4 mt-n6">

			<!-- templates/element/Pages/help-center/help-box.php -->
			<?= $this->element('Pages/help-center/help-box'); ?>

		</div>
	</div>
</section>
<!-- Help Box End -->

<!-- Help Ticket Start -->
<section class="p-0">
	<div class="container">
		<div class="row g-4">
			<!-- Action box item -->
			<div class="col-md-6 position-relative overflow-hidden">
				<div class="border rounded-3 h-100 p-4 py-5">
					<!-- Content -->
					<div class="d-flex">
						<!-- Icon -->
						<div class="square--80 circle bg-light-seegreen text-seegreen flex-shrink-0"><i class="fa-solid fa-headset fs-2"></i></div>
						<!-- Content -->
						<div class="ms-3">
							<h4 class="mb-1">Contact Support?</h4>
							<p class="mb-3">Not finding the help you need?</p>
							<a href="<?= $this->Url->build('/contact-v1'); ?>" class="btn btn-seegreen fw-semibold px-5 mb-0">Contact Us</a>
						</div>
					</div>
				</div>
			</div>

			<!-- Action box item -->
			<div class="col-md-6 position-relative overflow-hidden">
				<div class="border rounded-3 h-100 p-4 py-5">
					<!-- Content -->
					<div class="d-flex">
						<!-- Icon -->
						<div class="square--80 circle text-danger bg-light-danger flex-shrink-0"><i class="fa-solid fa-ticket fs-2"></i></div>
						<!-- Content -->
						<div class="ms-3">
							<h4 class="mb-1">Submit a Ticket</h4>
							<p class="mb-3">Prosperous impression had conviction For every delay</p>
							<a href="#" class="btn btn-danger fw-semibold px-5 mb-0">Submit ticket</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
<!-- Help Ticket End -->

<!-- FAQ's Section -->
<section>
	<div class="container">

		<div class="row mb-4">
			<div class="col-12 text-center">
				<h2>Frequently Asked Questions</h2>
				<p class="mb-0">Perceived end knowledge certainly day sweetness why cordially</p>
			</div>
		</div>

		<div class="row align-items-start">
			<div class="col-xl-12 col-lg-12 col-md-12 mt-4">

				<div class="accordion accordion-flush" id="accordionFlushExample">
					<div class="accordion-item border">
						<h2 class="accordion-header rounded-2">
							<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">
								How To Book A resort with Booer.com?
							</button>
						</h2>
						<div id="flush-collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
							<div class="accordion-body">In a professional context it often happens that private or corporate clients corder a publication to be made and presented with the actual content still not being ready. Think of a news blog that's filled with content hourly on the day of going live. However, reviewers tend to be distracted by comprehensible content, say, a random text copied from a newspaper or the internet. The are likely to focus on the text, disregarding the layout and its elements.</div>
						</div>
					</div>
					<div class="accordion-item border rounded-2">
						<h2 class="accordion-header">
							<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseTwo" aria-expanded="false" aria-controls="flush-collapseTwo">
								Can We Pay After Check-out?
							</button>
						</h2>
						<div id="flush-collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
							<div class="accordion-body">In a professional context it often happens that private or corporate clients corder a publication to be made and presented with the actual content still not being ready. Think of a news blog that's filled with content hourly on the day of going live. However, reviewers tend to be distracted by comprehensible content, say, a random text copied from a newspaper or the internet. The are likely to focus on the text, disregarding the layout and its elements.</div>
						</div>
					</div>
					<div class="accordion-item border rounded-2">
						<h2 class="accordion-header">
							<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseThree" aria-expanded="false" aria-controls="flush-collapseThree">
								Is This Collaborate with Oyo?
							</button>
						</h2>
						<div id="flush-collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
							<div class="accordion-body">In a professional context it often happens that private or corporate clients corder a publication to be made and presented with the actual content still not being ready. Think of a news blog that's filled with content hourly on the day of going live. However, reviewers tend to be distracted by comprehensible content, say, a random text copied from a newspaper or the internet. The are likely to focus on the text, disregarding the layout and its elements.</div>
						</div>
					</div>
					<div class="accordion-item border rounded-2">
						<h2 class="accordion-header">
							<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseFour" aria-expanded="false" aria-controls="flush-collapseFour">
								Can We get Any Transport For Walk?
							</button>
						</h2>
						<div id="flush-collapseFour" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
							<div class="accordion-body">In a professional context it often happens that private or corporate clients corder a publication to be made and presented with the actual content still not being ready. Think of a news blog that's filled with content hourly on the day of going live. However, reviewers tend to be distracted by comprehensible content, say, a random text copied from a newspaper or the internet. The are likely to focus on the text, disregarding the layout and its elements.</div>
						</div>
					</div>
					<div class="accordion-item border rounded-2 mb-0">
						<h2 class="accordion-header">
							<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseFive" aria-expanded="false" aria-controls="flush-collapseFive">
								Can We Get Any Extra Services?
							</button>
						</h2>
						<div id="flush-collapseFive" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
							<div class="accordion-body">In a professional context it often happens that private or corporate clients corder a publication to be made and presented with the actual content still not being ready. Think of a news blog that's filled with content hourly on the day of going live. However, reviewers tend to be distracted by comprehensible content, say, a random text copied from a newspaper or the internet. The are likely to focus on the text, disregarding the layout and its elements.</div>
						</div>
					</div>
				</div>

			</div>
		</div>

	</div>
</section>
<!-- FAQ's Section End -->

<!-- Article Section Start -->
<section class="pt-0">
	<div class="container">

		<div class="row align-items-center justify-content-center">
			<div class="col-xl-8 col-lg-9 col-md-11 col-sm-12">
				<div class="secHeading-wrap text-center mb-5">
					<h2>Trending & Popular Articles</h2>
					<p>Cicero famously orated against his political opponent Lucius Sergius Catilina.</p>
				</div>
			</div>
		</div>

		<div class="row justify-content-center g-4">

			<!-- templates/element/Home/index/blog.php -->
      		<?= $this->element('Home/index/blog'); ?>

		</div>
	</div>
</section>
<!-- Article Section Start -->

<!-- templates/element/Home/index/newsletter.php -->
<?= $this->element('Home/index/newsletter'); ?>

<!-- templates/element/Home/index/log-in.php -->
<?= $this->element('Home/index/log-in'); ?>

<!-- templates/element/Home/index/countries.php -->
<?= $this->element('Home/index/countries'); ?>

<!-- Include Footer -->
<?= $this->element('footer', ['skin' => 'skin-dark-footer']) ?>