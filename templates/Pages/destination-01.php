<?php
$this->assign('title', 'Destination-01 Page');
?>

<?= $this->Html->css('/assets/css/google-travel-layout.css') ?>
<?= $this->Html->css('/assets/css/google-travel-home.css') ?>
<?= $this->Html->css('/assets/css/google-travel-cards.css') ?>

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
    <li class="breadcrumb-item active" aria-current="page" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><span itemprop="name">Destination 01</span><meta itemprop="position" content="2"></li>
  </ol>
</nav>
<main id="main-content" style="background:#f8f9fa;min-height:85vh;" role="main">

<!-- Hero Banner Start -->
<div class="position-relative" style="background:#fff;border-bottom:1px solid #e8eaed;padding:28px 0 22px;">
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
<section class="" style="background:#f8f9fa;">
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
</main>
<?= $this->element('footer', ['skin' => 'skin-light-footer']) ?>