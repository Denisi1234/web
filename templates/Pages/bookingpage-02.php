<
<?= $this->Html->css('/assets/css/google-travel-layout.css') ?>
<?= $this->Html->css('/assets/css/google-travel-home.css') ?>
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
    <li class="breadcrumb-item active" aria-current="page" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><span itemprop="name">Bookingpage 02</span><meta itemprop="position" content="2"></li>
  </ol>
</nav>
<main id="main-content" style="background:#f8f9fa;min-height:85vh;" role="main">?php
/**
 * fastnetstays.com - Unified Trivago Book&Go Checkout
 */
include __DIR__ . '/booking-page.php';
</main>
<?= $this->element('footer', ['skin' => 'skin-light-footer']) ?>
