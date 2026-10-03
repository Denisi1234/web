<?php
$this->assign('title', 'Frequently Asked Questions | FastNet Stays');
?>

<?= $this->Html->css('/assets/css/google-travel-layout.css') ?>
<?= $this->Html->css('/assets/css/google-travel-home.css') ?>
<?= $this->Html->css('/assets/css/google-travel-cards.css') ?>

<?= $this->element('navbar') ?>
<?= $this->element('breadcrumb-schema', ['label' => 'Frequently asked questions']) ?>
<main id="main-content" style="background:var(--cds-gray-10);min-height:85vh;" role="main">

<!-- Booking Title -->
<section class="position-relative" style="background:#fff;border-bottom:1px solid #e8eaed;padding:var(--cds-spacing-07) 0 var(--cds-spacing-06);">
	<div class="container">
		<div class="row align-items-center justify-content-center">
			<div class="col-xl-7 col-lg-9 col-md-12">

				<div class="fpc-capstion text-center my-4">
					<div class="fpc-captions">
						<h1 class="xl-heading " style="color:#202124;">Frequently Asked Questions</h1>
						<p class="" style="color:#202124;">Find instant answers to common questions about booking stays, payment options, cancellations, and host policies on Fastnetstays.com.</p>
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


<!-- templates/element/Home/index/countries.php -->
<?= $this->element('Home/index/countries'); ?>

<!-- Include Footer -->
</main>
<?= $this->element('footer', ['skin' => 'skin-light-footer']) ?>