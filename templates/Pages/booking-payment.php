<?php $this->assign('title', 'Complete payment | fastnetstays.com'); ?>
<?= $this->Html->css('/assets/css/google-travel-layout.css') ?>
<?= $this->Html->css('/assets/css/google-travel-home.css') ?>
<?= $this->element('navbar') ?>
<nav aria-label="Breadcrumb" class="container-fluid px-2 px-lg-2" style="max-width:100%;margin:0 auto;background:#f8f9fa;">
  <ol class="breadcrumb mb-0 py-1" style="background:transparent;font-size:12px;line-height:1.2;--bs-breadcrumb-divider:'›';" itemscope itemtype="https://schema.org/BreadcrumbList">
    <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a href="/" itemprop="item" style="color:#5f6368;text-decoration:none;"><span itemprop="name">Home</span></a><meta itemprop="position" content="1"></li>
    <li class="breadcrumb-item active" aria-current="page" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><span itemprop="name">Booking Payment</span><meta itemprop="position" content="2"></li>
  </ol>
</nav>
<main id="main-content" style="background:#f8f9fa;min-height:85vh;" role="main">
<link rel="stylesheet" href="<?= $this->Url->build('/assets/css/trivago-checkout.css') ?>">
<style>
/* Phase 4 — payment pending parity with mobile USSD dialog (#EBF5FF circle, r16, r30) */
.trivago-card{border:1px solid #e8eaed !important;border-radius:16px !important;box-shadow:0 6px 16px rgba(0,0,0,0.05) !important;background:#fff}
.payment-icon-circle{width:60px;height:60px;border-radius:50%;background:#EBF5FF;border:1px solid #dbeafe;display:flex;align-items:center;justify-content:center;margin:0 auto 14px}
.trivago-card-title{font-size:20px;font-weight:800;color:#1a1d25}
.trivago-card-subtitle{font-size:13px;color:#5f6368}
#payment-status{border-radius:12px;border:1px solid #e8eaed}
#payment-status.alert-info{background:#F0F3FF;color:#1a1d25;border-color:#dbeafe}
#payment-status.alert-success{background:#e6f4ea;border-color:#c8e6c9;color:#137333}
@media(max-width:768px){.trivago-card{border-radius:22px !important}}
</style>
<div class="trivago-checkout-body" style="background:#F8FAFC;padding:16px 0">
    <div class="container" style="max-width: 620px;">
        <section class="trivago-card text-center p-4 p-md-5" aria-labelledby="payment-title">
            <div class="payment-icon-circle" aria-hidden="true">
                <i id="payment-icon" class="fa-solid fa-mobile-screen-button" style="font-size:26px;color:#2563EB"></i>
            </div>
            <h1 id="payment-title" class="trivago-card-title">Approve your mobile payment</h1>
            <p id="payment-status-text" class="trivago-card-subtitle">Sending payment request to your phone...</p>
            <div id="payment-status" class="alert alert-info" role="status" aria-live="polite">
                <span class="spinner-border spinner-border-sm me-2" role="status"></span> Sending USSD push to your phone...
            </div>
            <p style="font-size:12px;color:#9aa0a6;margin-top:10px">You'll receive a USSD push — enter your wallet PIN to authorize. Matches mobile <code style="background:#F8FAFC;padding:2px 6px;border-radius:6px">booking_checkout.dart:312</code> flow.</p>
            <a href="<?= $this->Url->build('/hotel-list-01') ?>" class="btn mt-3" style="background:#fff;border:1px solid #e8eaed;border-radius:30px;padding:10px 20px;color:#1a1d25">Return to search</a>
        </section>
    </div>
</div>
</main>
<?= $this->element('footer', ['skin' => 'skin-light-footer']) ?>
<script>
(function () {
    const paymentId  = <?= json_encode($paymentId) ?>;
    const dispatchUrl = <?= json_encode($this->Url->build('/booking-payment/dispatch')) ?>;
    const statusUrl  = <?= json_encode($this->Url->build('/booking-payment/status')) ?> + '?payment_id=' + encodeURIComponent(paymentId);
    const statusEl   = document.getElementById('payment-status');
    const textEl     = document.getElementById('payment-status-text');
    const csrfToken  = document.querySelector('meta[name="csrfToken"]')?.content || '';
    let attempts = 0;
    let finished = false;

    // ── Step 1: Fire USSD push via AJAX immediately on page load ──────────────
    async function dispatchPush() {
        try {
            await fetch(dispatchUrl, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
                body: JSON.stringify({ payment_id: paymentId })
            });
        } catch(e) { /* push fires best-effort; status polling is authoritative */ }
        // After dispatch (success or not), start polling
        textEl.textContent = 'Approve the payment request using your mobile-money PIN.';
        statusEl.className = 'alert alert-info';
        statusEl.innerHTML = 'Waiting for payment approval...';
        setTimeout(checkStatus, 3000);
    }

    // ── Step 2: Poll for payment status ───────────────────────────────────────
    async function checkStatus() {
        if (finished) return;
        attempts++;
        try {
            const response = await fetch(statusUrl, { headers: { 'Accept': 'application/json', 'X-CSRF-Token': csrfToken } });
            if (response.status === 429) {
                statusEl.className = 'alert alert-warning';
                statusEl.textContent = 'Too many checks — please wait a moment.';
            } else {
                const data = await response.json();
                if (data.status === 'paid') {
                    statusEl.className = 'alert alert-success';
                    statusEl.textContent = '✅ Payment confirmed! Opening your booking...';
                    finished = true;
                    window.location.href = <?= json_encode($this->Url->build('/bookingpage-success')) ?> + '?booking_id=' + encodeURIComponent(data.booking_id);
                    return;
                }
                if (data.status === 'failed' || data.status === 'expired' || attempts >= 40) {
                    statusEl.className = 'alert alert-danger';
                    statusEl.textContent = data.status === 'expired' || attempts >= 40
                        ? 'Payment timed out. Please start again.'
                        : 'Payment was not approved.';
                    textEl.textContent = 'Your room was not confirmed. No successful payment was recorded.';
                    finished = true;
                    return;
                }
            }
        } catch (error) {
            if (attempts >= 40) {
                statusEl.className = 'alert alert-danger';
                statusEl.textContent = 'We could not verify the payment status. Please contact support.';
                finished = true;
                return;
            }
        }
        // Exponential backoff 3s → 8s max
        const delay = Math.min(3000 * Math.pow(1.12, attempts), 8000);
        setTimeout(checkStatus, delay);
    }

    // Kick off immediately
    dispatchPush();
})();
</script>
