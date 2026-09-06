<?php
$this->assign('title', 'Faq Page');
?>

<!-- Include Navbar -->
<?= $this->element('navbar') ?>

<!-- Booking Title -->
<section class="bg-cover position-relative" style="background:url(<?= $this->Url->build('/assets/img/bg-title.jpg'); ?>)no-repeat;" data-overlay="5">
	<div class="container">
		<div class="row align-items-center justify-content-center">
			<div class="col-xl-7 col-lg-9 col-md-12">

				<div class="fpc-capstion text-center my-4">
					<div class="fpc-captions">
						<h1 class="xl-heading text-light">Frequently Asked Questions</h1>
						<p class="text-light">Find instant answers to common questions about booking stays, payment options, cancellations, and host policies on Fastnetstays.com.</p>
					</div>
				</div>

			</div>
		</div>
	</div>
	<div class="fpc-banner"></div>
</section>
<!-- Booking Title -->

<!-- FAQ's Section -->
<section>
	<div class="container">
		<div class="row align-items-start g-4">

			<!-- templates/element/Pages/faq/office.php -->
			<?= $this->element('Pages/faq/office'); ?>

		</div>

		<div class="row align-items-start">
			<div class="col-xl-12 col-lg-12 col-md-12 mt-4">

				<!-- templates/element/Pages/faq/faqs.php -->
				<?= $this->element('Pages/faq/faqs'); ?>

			</div>
		</div>

	</div>
</section>
<!-- FAQ's Section End -->

<!-- templates/element/Home/index/newsletter.php -->
<?= $this->element('Home/index/newsletter'); ?>

<!-- templates/element/Home/index/log-in.php -->
<?= $this->element('Home/index/log-in'); ?>

<!-- templates/element/Home/index/countries.php -->
<?= $this->element('Home/index/countries'); ?>

<!-- Include Footer -->
<?= $this->element('footer', ['skin' => 'skin-dark-footer']) ?>