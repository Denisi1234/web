<?php
$this->assign('title', 'Destination-01 Page');
?>

<!-- Include Navbar -->
<?= $this->element('navbar') ?>

<!-- Hero Banner Start -->
<div class="py-5 bg-primary position-relative">
	<div class="container">

		<!-- Search Form -->
		<div class="row justify-content-center align-items-center mt-6 mt-md-0">
			<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
				<div class="search-wrap position-relative">
					<div class="row align-items-end gy-3 gx-md-3 gx-sm-2">

						<!-- templates/element/Listing/Destination/destination-01/destination-search.php -->
						<?= $this->element('Listing/Destination/destination-01/destination-search'); ?>

					</div>
				</div>
			</div>
		</div>
		<!-- </row> -->

	</div>
</div>
<!-- Hero Banner End -->

<!-- Searching Destination Lists Start -->
<section class="gray-simple">
	<div class="container">
		<div class="row justify-content-between gy-4 gx-xl-4 gx-lg-3 gx-md-3 gx-4">

			<!-- templates/element/Listing/Hotel/hotel-list-01/sidebar.php -->
			<?= $this->element('Listing/Hotel/hotel-list-01/sidebar'); ?>

			<!-- All List -->
			<div class="col-xl-9 col-lg-8 col-md-12">
					
				<!-- templates/element/Listing/Hotel/hotel-list-01/showing.php -->
				<?= $this->element('Listing/Hotel/hotel-list-01/showing'); ?>

				<div class="row align-items-center g-4 mt-2">

					<!-- templates/element/Listing/Destination/destination-01/destination-list.php -->
					<?= $this->element('Listing/Destination/destination-01/destination-list'); ?>

					<!-- templates/element/Listing/Hotel/hotel-list-01/pagination.php -->
					<?= $this->element('Listing/Hotel/hotel-list-01/pagination'); ?>

				</div>

			</div>

		</div>
	</div>
</section>
<!-- Destination Searches Lists End -->

<!-- templates/element/Home/index/newsletter.php -->
<?= $this->element('Home/index/newsletter'); ?>

<!-- templates/element/Home/index/log-in.php -->
<?= $this->element('Home/index/log-in'); ?>

<!-- templates/element/Home/index/countries.php -->
<?= $this->element('Home/index/countries'); ?>

<!-- Include Footer -->
<?= $this->element('footer', ['skin' => 'skin-dark-footer']) ?>