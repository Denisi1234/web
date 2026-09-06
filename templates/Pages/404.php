<?php
$this->assign('title', '404 Page');
?>

<!-- Include Navbar -->
<?= $this->element('navbar') ?>

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
						<p class="fs-6">Cicero famously orated against his political opponent.</p>
					</div>
				</div>

			</div>
		</div>
	</div>
</section>
<!-- 404 End -->

<!-- templates/element/Home/index/log-in.php -->
<?= $this->element('Home/index/log-in'); ?>

<!-- templates/element/Home/index/countries.php -->
<?= $this->element('Home/index/countries'); ?>

<!-- Include Footer -->
<?= $this->element('footer', ['skin' => 'skin-dark-footer']) ?>