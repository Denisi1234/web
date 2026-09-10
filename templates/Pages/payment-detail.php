<?php
$this->assign('title', 'Payment Details');
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
    <li class="breadcrumb-item active" aria-current="page" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><span itemprop="name">Payment Detail</span><meta itemprop="position" content="2"></li>
  </ol>
</nav>
<main id="main-content" style="background:#f8f9fa;min-height:85vh;" role="main">

<!-- Dashboard Menu -->
<?= $this->element('Listing/User-Dashboard/dashboard-menu'); ?>

<!-- Payment Page -->
<section class="pt-5 gray-simple position-relative">
	<div class="container">

		<div class="row align-items-center justify-content-center">
			<?= $this->element('Listing/User-Dashboard/navigation'); ?>
		</div>

		<div class="row align-items-start justify-content-between gx-xl-4">

			<!-- Left user sidebar info -->
			<?= $this->element('Listing/User-Dashboard/side-info'); ?>

			<div class="col-xl-8 col-lg-8 col-md-12">

				<!-- Saved Payment Methods -->
				<div class="card mb-4">
					<div class="card-header">
						<h4><i class="fa-solid fa-wallet me-2"></i>Payment Methods</h4>
					</div>
					<div class="card-body gap-4">
						<div id="payment-alert" class="mb-3" style="display:none;"></div>
						<h4 class="fs-5 fw-semibold mb-3">Saved Payment Methods</h4>

						<div class="row justify-content-start g-3" id="payment-methods-list">
							<!-- Spinner -->
							<div class="col-12 text-center py-4" id="pm-loading">
								<div class="spinner-border text-primary spinner-border-sm" role="status"></div>
							</div>
						</div>
					</div>
				</div>

				<!-- Billing History / Bookings List Transactions -->
				<div class="card mb-4">
					<div class="card-header">
						<h4><i class="fa-solid fa-file-invoice-dollar me-2"></i>Billing History</h4>
					</div>
					<div class="card-body">
						<div class="table-responsive">
							<table class="table">
								<thead>
									<tr>
										<th scope="col">#</th>
										<th scope="col">Booking Ref</th>
										<th scope="col">Date</th>
										<th scope="col">Status</th>
										<th scope="col">Amount</th>
									</tr>
								</thead>
								<tbody id="billing-history-rows">
									<tr>
										<td colspan="5" class="text-center py-4">
											<div class="spinner-border text-primary spinner-border-sm" role="status"></div>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>

			</div>

		</div>
	</div>
</section>

<!-- Add card modal -->
<div class="modal fade" id="addcard" tabindex="-1" role="dialog" aria-labelledby="addcardmodal" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered addcard-pop-form" role="document">
		<div class="modal-content" id="addcardmodal">
			<div class="modal-header">
				<h4 class="modal-title fs-6 fw-bold">Add Mobile Payment Method</h4>
				<a href="#" class="text-muted fs-4" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-square-xmark"></i></a>
			</div>
			<div class="modal-body">
				<div class="modal-addcard-form pb-4 pt-0">
					<!-- Type selector -->
					<div class="mb-3">
						<label class="form-label">Payment Network (Tanzania)</label>
						<select id="pm-type" class="form-control">
							<option value="vodacom" selected>Vodacom M-Pesa (074, 075, 076)</option>
							<option value="tigo">Tigo Pesa (065, 067, 071)</option>
							<option value="airtel">Airtel Money (068, 069, 078)</option>
							<option value="halotel">HaloPesa (Halotel) (061, 062)</option>
						</select>
					</div>

					<form id="add-payment-form" class="row align-items-start g-3">
						<!-- Mobile Money fields -->
						<div id="mobile-fields-group" class="row g-3 p-0 m-0">
							<div class="col-12 p-0">
								<div class="form-group">
									<label class="form-label">Phone Number (Tanzania)</label>
									<div class="input-group">
										<span class="input-group-text bg-light border-end-0 fw-bold">🇹🇿 +255</span>
										<input type="text" id="mobile-phone" class="form-control" placeholder="0712345678" required>
									</div>
								</div>
							</div>
							<div class="col-12 p-0 mt-3">
								<div class="form-group">
									<label class="form-label">Registered Account Name</label>
									<input type="text" id="mobile-name" class="form-control" placeholder="Full name as registered on SIM" required>
								</div>
							</div>
						</div>

						<div class="col-12 mt-4">
							<button type="submit" id="pm-add-btn" class="btn btn-md btn-primary full-width fw-medium">Save Mobile Payment Method</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<?= $this->element('Home/index/newsletter'); ?>
<?= $this->element('Home/index/log-in'); ?>
<?= $this->element('Home/index/countries'); ?>
</main>
<?= $this->element('footer', ['skin' => 'skin-light-footer']) ?>

<?php $this->start('script'); ?>
<?= $this->Html->script('/assets/js/payment-detail.js'); ?>
<?php $this->end(); ?>