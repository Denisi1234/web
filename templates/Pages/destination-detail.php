<?php
$this->assign('title', 'Destination-Detail Page');
?>

<!-- Include Navbar -->
<?= $this->element('navbar') ?>

<!-- breadcrumbs  Start -->
<div class="py-2 gray-simple position-relative">
	<div class="container">
		<!-- Search Form -->
		<div class="row justify-content-center align-items-center mt-6 mt-md-0">
			<div class="col-xl-12 col-lg-12 col-md-12">
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb mb-0">
						<li class="breadcrumb-item"><a href="#" class="text-primary">Home</a></li>
						<li class="breadcrumb-item"><a href="#" class="text-primary">Hotel in Denver, USA</a></li>
						<li class="breadcrumb-item active" aria-current="page">Royal Plaza on Scotts</li>
					</ol>
				</nav>
			</div>
		</div>
		<!-- </row> -->
	</div>
</div>
<!-- Breadcrumbs End -->

<!-- Destination Detail Start -->
<section class="pt-3">
	<div class="container">
		<div class="row">

			<div class="col-xl-12 col-lg-12 col-md-12">
				<div class="card border-0 p-3 mb-4">

					<div class="crd-heaader d-md-flex align-items-center justify-content-between">
						<div class="crd-heaader-first">
							<div class="d-block">
								<h4 class="mb-0">
									<?php if (!empty($article['title'])): ?>
										<?= h($article['title']) ?>
									<?php else: ?>
										Swiss Paris Delight Group Departure Oman Air Special
									<?php endif; ?>
								</h4>
								<div class="exlops">
									<p class="detail ellipsis-container fw-medium">
										<span class="ellipsis-item__normal">4D/5N</span>
										<span class="separate ellipsis-item__normal"></span>
										<span class="ellipsis-item">2N Paris</span>
										<span class="separate ellipsis-item__normal"></span>
										<span class="ellipsis-item">2N Zurich</span>
										<span class="separate ellipsis-item__normal"></span>
										<span class="ellipsis-item">1N Engelberg</span>
										<span class="separate ellipsis-item__normal"></span>
										<span class="ellipsis-item label text-success bg-light-success">25 Group</span>
									</p>
								</div>
							</div>
						</div>
						<div class="crd-heaader-last my-md-0 my-2">
							<div class="drix-first d-flex align-items-center pe-2 text-end mb-2">
								<a href="#" class="bg-light-info text-info rounded-1 fw-medium text-sm px-3 py-2 lh-base"><i class="fa-solid fa-bookmark me-2"></i>Bookmark</a>
								<a href="#" class="bg-light-danger text-danger rounded-1 fw-medium text-sm px-3 py-2 lh-base ms-2"><i class="fa-solid fa-share-nodes me-2"></i>Share</a>
							</div>
						</div>
					</div>

					<div class="geotrip-gallery mb-lg-0 mb-3">
						<div class="left-img">
							<a href="<?= !empty($article['img']) ? $this->Url->build('/' . $article['img']) : $this->Url->build('/assets/img/destination/tr-1.jpg') ?>" data-lightbox="roadtrip"><img src="<?= !empty($article['img']) ? $this->Url->build('/' . $article['img']) : $this->Url->build('/assets/img/destination/tr-1.jpg') ?>" alt="image" class="img-fluid"></a>
						</div>

						<div class="right-grid position-relative">
							<a href="<?= $this->Url->build('/assets/img/destination/tr-2.jpg'); ?>" data-lightbox="roadtrip"><img src="<?= $this->Url->build('/assets/img/destination/tr-2.jpg'); ?>" alt="image" class="rounded-2 img-fluid"></a>
							<a href="<?= $this->Url->build('/assets/img/destination/tr-3.jpg'); ?>" data-lightbox="roadtrip"><img src="<?= $this->Url->build('/assets/img/destination/tr-3.jpg'); ?>" alt="image" class="rounded-4 img-fluid"></a>
							<a href="<?= $this->Url->build('/assets/img/destination/tr-2.jpg'); ?>" data-lightbox="roadtrip"><img src="<?= $this->Url->build('/assets/img/destination/tr-4.jpg'); ?>" alt="image" class="rounded-2 img-fluid"></a>
							<a href="<?= $this->Url->build('/assets/img/destination/tr-5.jpg'); ?>" data-lightbox="roadtrip"><img src="<?= $this->Url->build('/assets/img/destination/tr-5.jpg'); ?>" alt="image" class="rounded-2 img-fluid"></a>
							<div class="position-absolute end-0 bottom-0 mb-3 me-3">
								<a href="<?= $this->Url->build('/assets/img/destination/tr-3.jpg'); ?>" data-lightbox="roadtrip" class="btn btn-md btn-whites fw-medium text-dark"><i class="fa-solid fa-caret-right me-1"></i>16More Photos</a>
							</div>
						</div>
					</div>

				</div>
			</div>
			<div class="col-xl-12 col-lg-12 col-md-12 mb-5">
				<ul class="nav nav-pills primary nav-fill gap-2 p-2  bg-light-primary rounded-2" id="pillstour-tab" role="tablist">
					<li class="nav-item" role="presentation">
						<button class="nav-link rounded-2 active" id="pills-overview-tab" data-bs-toggle="pill" data-bs-target="#pills-overview" type="button" role="tab" aria-controls="pills-overview" aria-selected="true">Overview</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link rounded-2" id="pills-itinerary-tab" data-bs-toggle="pill" data-bs-target="#pills-itinerary" type="button" role="tab" aria-controls="pills-itinerary" aria-selected="false">Itinerary</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link rounded-2" id="pills-hotfly-tab" data-bs-toggle="pill" data-bs-target="#pills-hotfly" type="button" role="tab" aria-controls="pills-hotfly" aria-selected="false">Hotels & Transfers</button>
					</li>
				</ul>
			</div>

			<div class="col-xl-12 col-lg-12 col-md-12">
				<div class="row">

					<!-- Details -->
					<div class="col-xl-9 col-lg-9 col-md-12">
						<div class="tab-content" id="pillstour-tabContent">

							<!-- templates/element/Listing/Destination/destination-detail/tab-content.php -->
							<?= $this->element('Listing/Destination/destination-detail/tab-content'); ?>

						</div>
					</div>

					<!-- Sidebar -->
					<div class="col-xl-3 col-lg-3 col-md-12">
						<div class="sides-block">

							<!-- templates/element/Listing/Destination/destination-detail/destination-sidebar.php -->
							<?= $this->element('Listing/Destination/destination-detail/destination-sidebar'); ?>

						</div>
					</div>
				</div>
			</div>

		</div>
	</div>
</section>
<!-- Destination Detail End -->

<!-- Similar Destination Start -->
<section class="gray-simple py-5">
	<div class="container">

		<div class="row align-items-center justify-content-between mb-3">
			<div class="col-8">
				<div class="upside-heading">
					<h5 class="fw-bold fs-6 m-0">Similar Destination</h5>
				</div>
			</div>
			<div class="col-4">
				<div class="text-end grpx-btn overflow-hidden">
					<a href="#" class="btn btn-light-primary btn-md fw-medium">More<i class="fa-solid fa-arrow-trend-up ms-2"></i></a>
				</div>
			</div>
		</div>

		<div class="row justify-content-center">
			<div class="col-xl-12 col-lg-12 col-md-12 p-0">
				<div class="main-carousel arrow-hide cols-3">

					<!-- templates/element/Listing/Destination/destination-detail/similar-destination.php -->
					<?= $this->element('Listing/Destination/destination-detail/similar-destination'); ?>

				</div>
			</div>
		</div>
	</div>
</section>
<!-- Similar Destination End -->

<!-- FAQ About Tour Detail Start -->
<section>
	<div class="container">
		<div class="row align-items-start justify-content-between gx-3">
			<div class="col-xl-3 col-lg-4 col-md-4">
				<div class="position-relative mb-4">
					<h4 class="lh-base">FAQ Regarding The Royal Plaza Scout</h4>
				</div>
				<div class="position-relative mb-4">
					<button class="btn btn-md btn-primary fw-medium" type="button">Submit Request</button>
				</div>
			</div>
			<div class="col-xl-9 col-lg-8 col-md-8">
				<div class="accordion accordion-flush" id="accordionFlushExample">
					<div class="accordion-item border rounded-3">
						<h2 class="accordion-header">
							<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">
								How To Book A resort with Booer.com?
							</button>
						</h2>
						<div id="flush-collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
							<div class="accordion-body">In a professional context it often happens that private or corporate clients corder a publication to be made and presented with the actual content still not being ready. Think of a news blog that's filled with content hourly on the day of going live. However, reviewers tend to be distracted by comprehensible content, say, a random text copied from a newspaper or the internet. The are likely to focus on the text, disregarding the layout and its elements.</div>
						</div>
					</div>
					<div class="accordion-item border rounded-3">
						<h2 class="accordion-header">
							<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseTwo" aria-expanded="false" aria-controls="flush-collapseTwo">
								Can We Pay After Check-out?
							</button>
						</h2>
						<div id="flush-collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
							<div class="accordion-body">In a professional context it often happens that private or corporate clients corder a publication to be made and presented with the actual content still not being ready. Think of a news blog that's filled with content hourly on the day of going live. However, reviewers tend to be distracted by comprehensible content, say, a random text copied from a newspaper or the internet. The are likely to focus on the text, disregarding the layout and its elements.</div>
						</div>
					</div>
					<div class="accordion-item border rounded-3">
						<h2 class="accordion-header">
							<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseThree" aria-expanded="false" aria-controls="flush-collapseThree">
								Is This Collaborate with Oyo?
							</button>
						</h2>
						<div id="flush-collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
							<div class="accordion-body">In a professional context it often happens that private or corporate clients corder a publication to be made and presented with the actual content still not being ready. Think of a news blog that's filled with content hourly on the day of going live. However, reviewers tend to be distracted by comprehensible content, say, a random text copied from a newspaper or the internet. The are likely to focus on the text, disregarding the layout and its elements.</div>
						</div>
					</div>
					<div class="accordion-item border rounded-3">
						<h2 class="accordion-header">
							<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseFour" aria-expanded="false" aria-controls="flush-collapseFour">
								Can We get Any Transport For Walk?
							</button>
						</h2>
						<div id="flush-collapseFour" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
							<div class="accordion-body">In a professional context it often happens that private or corporate clients corder a publication to be made and presented with the actual content still not being ready. Think of a news blog that's filled with content hourly on the day of going live. However, reviewers tend to be distracted by comprehensible content, say, a random text copied from a newspaper or the internet. The are likely to focus on the text, disregarding the layout and its elements.</div>
						</div>
					</div>
					<div class="accordion-item border rounded-3">
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
<!-- FAQ About Tour Detail END -->

<!-- templates/element/Home/index/newsletter.php -->
<?= $this->element('Home/index/newsletter'); ?>

<!-- templates/element/Home/index/log-in.php -->
<?= $this->element('Home/index/log-in'); ?>

<!-- templates/element/Home/index/countries.php -->
<?= $this->element('Home/index/countries'); ?>

<!-- Include Footer -->
<?= $this->element('footer') ?>

<?php $this->start('script'); ?>
	<script>
		lightbox.option({
			'resizeDuration': 200,
			'wrapAround': true
		})
	</script>
<?php $this->end(); ?>