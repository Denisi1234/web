<?php $this->assign('title', 'Complete payment | fastnetstays.com'); ?>
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
    <li class="breadcrumb-item active" aria-current="page" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><span itemprop="name">Booking Payment</span><meta itemprop="position" content="2"></li>
  </ol>
</nav>
<main id="main-content" style="background:#f8f9fa;min-height:85vh;" role="main">
<link rel="stylesheet" href="<?= $this->Url->build('/assets/css/trivago-checkout.css') ?>">
<main class="trivago-checkout-body">
    <div class="container" style="max-width: 620px;">
        <section class="trivago-card text-center p-4 p-md-5" aria-labelledby="payment-title">
            <div class="mb-3" aria-hidden="true"><i class="fa-solid fa-mobile-screen-button text-primary" style="font-size: 42px;"></i></div>
            <h1 id="payment-title" class="trivago-card-title">Approve your mobile payment</h1>
            <p id="payment-status-text" class="trivago-card-subtitle">A payment request was sent to your phone. Approve it using your mobile-money PIN.</p>
            <div id="payment-status" class="alert alert-info" role="status" aria-live="polite">Waiting for payment approval...</div>
            <a href="<?= $this->Url->build('/hotel-list-01') ?>" class="btn btn-outline-secondary">Return to search</a>
        </section>
    </div>
</main>
<script>
(function () {
    const paymentId = <?= json_encode($paymentId) ?>;
    const statusUrl = <?= json_encode($this->Url->build('/booking-payment/status')) ?> + '?payment_id=' + encodeURIComponent(paymentId);
    const statusEl = document.getElementById('payment-status');
    const textEl = document.getElementById('payment-status-text');
    let attempts = 0;
    let timer = null;
    let finished = false;
    async function checkStatus() {
        if (finished) return;
        attempts++;
        try {
            const response = await fetch(statusUrl, { headers: { 'Accept': 'application/json' } });
            const data = await response.json();
            if (data.status === 'paid') {
                statusEl.className = 'alert alert-success';
                statusEl.textContent = 'Payment confirmed. Opening your booking...';
                finished = true;
                window.location.href = <?= json_encode($this->Url->build('/bookingpage-success')) ?> + '?booking_id=' + encodeURIComponent(data.booking_id);
                return;
            }
            if (data.status === 'failed' || data.status === 'expired' || attempts >= 40) {
                statusEl.className = 'alert alert-danger';
                statusEl.textContent = data.status === 'expired' || attempts >= 40 ? 'Payment timed out. Please start again.' : 'Payment was not approved.';
                textEl.textContent = 'Your room was not confirmed. No successful payment was recorded.';
                finished = true;
                return;
            }
        } catch (error) {
            if (attempts >= 40) {
                statusEl.className = 'alert alert-danger';
                statusEl.textContent = 'We could not verify the payment status. Please contact support.';
                finished = true;
                return;
            }
        }
        timer = window.setTimeout(checkStatus, 3000);
    }
    checkStatus();
})();
</script>
