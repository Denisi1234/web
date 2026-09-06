<?php
$this->assign('title', 'fastnetstays.com - Hotel Deals, Lodges & Beach Escapes');
?>
<?= $this->Html->css('/assets/css/home.css') ?>
<?= $this->Html->css('/assets/css/home-spacing.css') ?>

<!-- Sleek fastnetstays.com Top Loader & Mobile Pill Indicator -->
<?= $this->element('Home/home-loader') ?>

<!-- Include Navbar -->
<?= $this->element('navbar') ?>

<div id="home-app-root" class="home-ready">

<!-- Hero Banner -->
<?= $this->element('hero') ?>

<!-- Recently Viewed Section (Trivago-style combo card) -->
<?= $this->element('Home/recently-viewed') ?>

<!-- Popular Stays & Resorts Section -->
<?= $this->element('Home/resorts-section') ?>

<?= $this->element('Home/how-it-works') ?>

<?= $this->element('Home/price-comparison') ?>

<?= $this->element('Home/popular-searches') ?>

</div><!-- #home-app-root -->

<!-- Member Log-in Banner -->
<?= $this->element('Home/index/log-in') ?>

<!-- Countries Directory -->
<?= $this->element('Home/index/countries') ?>

<!-- Include Footer -->
<?= $this->element('footer') ?>

<!-- FastNetStays Home Carousel & State Controller -->
<?= $this->Html->script('/assets/js/home-carousel.js') ?>
