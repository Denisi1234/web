<?php
$this->assign('title', 'Delete Account - FastNet Stays');
?>

<!-- Include Navbar -->
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
    <li class="breadcrumb-item active" aria-current="page" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><span itemprop="name">Delete Account</span><meta itemprop="position" content="2"></li>
  </ol>
</nav>
<main id="main-content" style="background:#f8f9fa;min-height:85vh;" role="main">

<!-- templates/element/Listing/User-Dashboard/dashboard-menu.php -->
<?= $this->element('Listing/User-Dashboard/dashboard-menu'); ?>

<!-- Delete Account Page -->
<section class="pt-5 gray-simple position-relative">
	<div class="container">

		<div class="row align-items-center justify-content-center">
			<!-- templates/element/Listing/User-Dashboard/navigation.php -->
			<?= $this->element('Listing/User-Dashboard/navigation'); ?>
		</div>

		<div class="row align-items-start justify-content-between gx-xl-4">

			<!-- templates/element/Listing/User-Dashboard/side-info.php -->
			<?= $this->element('Listing/User-Dashboard/side-info'); ?>

			<div class="col-xl-8 col-lg-8 col-md-12">

				<!-- Delete Account Confirmation -->
				<div class="card mb-4 border border-danger/30 shadow-sm rounded-3 bg-white">
					<div class="card-header bg-red-50/50 border-bottom border-red-100 py-3">
						<h4 class="fs-6 fw-bold mb-0 text-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i>Delete FastNet Account</h4>
					</div>
					<div class="card-body p-4">
						<p class="text-slate-700 text-sm mb-3">
							Deleting your account is permanent. All your booking history, saved wishlists, and loyalty preferences will be removed.
						</p>

						<div class="p-3 bg-slate-50 rounded-2 border border-slate-200 mb-4">
							<h6 class="text-xs text-uppercase fw-bold text-slate-500 mb-1">Before you proceed:</h6>
							<ul class="text-xs text-slate-600 mb-0 ps-3">
								<li>Ensure all upcoming lodge bookings are completed or canceled.</li>
								<li>Any active AzamPay refunds will still process to your registered phone number.</li>
							</ul>
						</div>

						<div class="d-flex flex-wrap gap-2">
							<a href="<?= $this->Url->build('/settings'); ?>" class="btn btn-dark rounded-full px-4 fw-bold">
								<i class="fa-solid fa-arrow-left me-1"></i>Keep My Account
							</a>
							<button type="button" onclick="confirmDeleteAccount()" class="btn btn-outline-danger rounded-full px-4 fw-bold">
								<i class="fa-solid fa-trash-can me-1"></i>Delete Account Permanently
							</button>
						</div>
					</div>
				</div>

			</div>

		</div>
	</div>
</section>
<!-- End Delete Account Page -->

<script>
function confirmDeleteAccount() {
    if (confirm("Are you sure you want to delete your FastNet account? This action cannot be undone.")) {
        localStorage.removeItem('auth_token');
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        localStorage.removeItem('fastnet_wishlist');
        alert("Your account data has been cleared.");
        window.location.href = '<?= $this->Url->build('/'); ?>';
    }
}
</script>

<!-- Include Footer -->
</main>
<?= $this->element('footer', ['skin' => 'skin-light-footer']) ?>