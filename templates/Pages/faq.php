<?php
$this->assign('title', 'Faq Page');
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
    <li class="breadcrumb-item active" aria-current="page" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><span itemprop="name">Faq</span><meta itemprop="position" content="2"></li>
  </ol>
</nav>
<main id="main-content" style="background:#f8f9fa;min-height:85vh;" role="main">

<!-- Booking Title -->
<section class="position-relative" style="background:#fff;border-bottom:1px solid #e8eaed;padding:28px 0 22px;">
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

<!-- templates/element/Home/index/log-in.php -->
<?= $this->element('Home/index/log-in'); ?>

<!-- templates/element/Home/index/countries.php -->
<?= $this->element('Home/index/countries'); ?>

<!-- Include Footer -->
</main>
<?= $this->element('footer', ['skin' => 'skin-light-footer']) ?>