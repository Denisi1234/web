<?php
$this->assign('title', 'Page Not Found (404) | FastNet Stays');
?>

<?= $this->Html->css('/assets/css/google-travel-layout.css') ?>
<?= $this->Html->css('/assets/css/google-travel-home.css') ?>
<?= $this->Html->css('/assets/css/google-travel-cards.css') ?>

<?= $this->element('navbar') ?>
<?= $this->element('breadcrumb-schema', ['label' => 'Page not found']) ?>
<main id="main-content" style="background:var(--cds-gray-10);min-height:85vh;" role="main">

<!-- 404 Start -->
<section class="position-relative">
	<div class="container">
		<div class="row align-items-center justify-content-center">
			<div class="col-xl-7 col-lg-9 col-md-12">

				<div class="404-capstion text-center my-4">
					<div class="404-captions">
						<img src="<?= $this->Url->build('/assets/img/404.png'); ?>" class="img-fluid mb-3" alt="">
						<h1 class="display-1 fw-bold mb-0">404</h1>
						<h2>Ohhh ho, something went wrong!</h2>
						<p class="fs-6 text-muted">We can’t find that page — it may have moved. Try searching stays or go back home.</p>
						<div class="d-flex gap-2 justify-content-center mt-3">
							<a href="/" class="btn btn-primary rounded-pill px-4">Back to Stays</a>
							<a href="/?city=Zanzibar" class="btn btn-outline-primary rounded-pill px-4">Search Hotels</a>
						</div>
					</div>
				</div>

			</div>
		</div>
	</div>
</section>
<!-- 404 End -->


<!-- templates/element/Home/index/countries.php -->
<?= $this->element('Home/index/countries'); ?>

<!-- Include Footer -->
</main>
<?= $this->element('footer', ['skin' => 'skin-light-footer']) ?>