<?php
$this->assign('title', '404 Page');
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
    <li class="breadcrumb-item active" aria-current="page" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><span itemprop="name">404</span><meta itemprop="position" content="2"></li>
  </ol>
</nav>
<main id="main-content" style="background:#f8f9fa;min-height:85vh;" role="main">

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
</main>
<?= $this->element('footer', ['skin' => 'skin-light-footer']) ?>