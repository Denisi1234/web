<?php
$this->assign('title', 'Payments & Refunds | FastNet Stays');
$this->assign('description', 'Manage saved mobile-money numbers, review billing history and track refunds on FastNet Stays.');
?>
<?= $this->element('navbar') ?>
<main id="main-content" role="main" style="background:var(--cds-gray-10);min-height:85vh">
<style>
.cds-pay-head{padding:28px 0 4px}
.cds-eyebrow{font-size:12px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:var(--cds-blue-60)}
.cds-h1{font-size:30px;font-weight:300;color:#161616;margin:8px 0 6px}
.cds-lede{font-size:14px;color:#525252;margin:0;max-width:640px}
.cds-pay-body{max-width:1180px;margin:0 auto;padding:20px 20px 56px}
.cds-panel{background:#fff;border:1px solid #e0e0e0;margin-bottom:20px}
.cds-panel-h{padding:16px 20px;border-bottom:1px solid #e0e0e0}
.cds-panel-h h2{font-size:17px;font-weight:600;color:#161616;margin:0}
.cds-panel-h p{font-size:12.5px;color:#6f6f6f;margin:2px 0 0}
.cds-panel-b{padding:8px 20px 16px}
.cds-pm-row{display:flex;align-items:center;gap:14px;padding:14px 0;border-top:1px solid #e0e0e0}
.cds-pm-row:first-child{border-top:none}
.cds-pm-logo{width:70px;height:40px;flex:0 0 70px;background:#f4f4f4;display:flex;align-items:center;justify-content:center;padding:4px 8px}
.cds-pm-logo img{max-height:28px;max-width:54px;object-fit:contain}
.cds-pm-name{font-size:14px;font-weight:600;color:#161616}
.cds-pm-num{font-size:12.5px;color:#6f6f6f;font-variant-numeric:tabular-nums}
.cds-pm-del{margin-left:auto;background:none;border:1px solid #e0e0e0;color:#da1e28;width:34px;height:34px;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;flex:0 0 auto}
.cds-pm-del:hover{background:#fff1f1;border-color:#da1e28}
.cds-pm-add{display:inline-flex;align-items:center;gap:8px;margin-top:12px;background:transparent;border:1px solid var(--cds-blue-60);color:var(--cds-blue-60);padding:10px 18px;font-size:13.5px;font-weight:600;cursor:pointer;border-radius:0}
.cds-pm-add:hover{background:var(--cds-blue-60);color:#fff}
.cds-empty{padding:28px 12px;text-align:center;color:#525252}
.cds-empty i{font-size:26px;color:#8d8d8d;display:block;margin-bottom:10px}
.cds-empty b{display:block;font-size:14px;color:#161616;margin-bottom:4px}
.cds-empty span{font-size:13px}
.cds-table{width:100%;border-collapse:collapse;font-size:13.5px}
.cds-table th{text-align:left;font-size:11.5px;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:#6f6f6f;padding:10px 8px;border-bottom:1px solid #161616}
.cds-table td{padding:12px 8px;border-top:1px solid #e0e0e0;color:#161616;vertical-align:middle}
.cds-table tr:first-child td{border-top:none}
.cds-badge{display:inline-block;font-size:11.5px;font-weight:600;padding:3px 10px;text-transform:capitalize}
.cds-b-paid{background:#defbe6;color:#0e6027}
.cds-b-pending{background:#fcf4d6;color:#8e6a00}
.cds-b-failed,.cds-b-cancelled{background:#fff1f1;color:#b81922}
.cds-b-refunded{background:#d0e2ff;color:#0043ce}
.cds-b-review{background:#e8daff;color:#491d8b}
.cds-refund-note{display:flex;gap:12px;background:#fff;border:1px solid #e0e0e0;border-left:4px solid var(--cds-blue-60);padding:14px 16px;font-size:13.5px;line-height:1.6;color:#393939}
.cds-refund-note i{color:var(--cds-blue-60);font-size:16px;margin-top:2px}
.cds-refund-note ol{margin:6px 0 0;padding-left:18px}
/* Carbon square modal fields (beat the rounded global theme) */
#addcard .modal-content{border-radius:0;border:1px solid #e0e0e0}
#addcard .modal-header{border-bottom:1px solid #e0e0e0;padding:16px 20px}
#addcard .modal-title{font-size:16px;font-weight:600;color:#161616}
#addcard .modal-body{padding:20px}
#addcard label{font-size:12px;color:#525252;margin-bottom:8px;display:block}
#addcard select,#addcard input{width:100%!important;background:#f4f4f4!important;border:none!important;border-bottom:1px solid #8d8d8d!important;border-radius:0!important;height:44px!important;padding:0 12px!important;font-size:14px!important;color:#161616!important}
#addcard select:focus,#addcard input:focus{outline:2px solid var(--cds-focus)!important;outline-offset:-2px!important}
#addcard .cds-prefix{display:flex;align-items:stretch}
#addcard .cds-prefix span{background:#f4f4f4;border-bottom:1px solid #8d8d8d;padding:0 10px;display:inline-flex;align-items:center;font-size:13px;color:#525252;font-weight:600}
#addcard .cds-prefix input{flex:1}
#pm-add-btn{width:100%;background:var(--cds-blue-60);color:#fff;border:none;padding:12px;font-size:14px;border-radius:0;min-height:48px;cursor:pointer}
#pm-add-btn:hover{background:#0353e9}
#pm-add-btn:disabled{opacity:.6;cursor:not-allowed}
.cds-alert{padding:10px 14px;font-size:13px;margin-bottom:12px}
.cds-alert-ok{background:#defbe6;border:1px solid #a7e8bd;color:#0e6027}
.cds-alert-err{background:#fff1f1;border:1px solid #f5c6cb;color:#7d2e2e}
@media(max-width:768px){.cds-table th:nth-child(3),.cds-table td:nth-child(3){display:none}}
</style>

<div class="container" style="max-width:1180px">
  <div class="cds-pay-head">
    <div class="cds-eyebrow">Account / Payments</div>
    <h1 class="cds-h1">Payments &amp; refunds</h1>
    <p class="cds-lede">Saved mobile-money numbers, every charge on your bookings, and refund tracking — all from live records.</p>
  </div>
</div>

<div class="cds-pay-body">
  <div class="row">
    <?= $this->element('profile_sidebar', ['active' => 'payment-detail']); ?>
    <div class="col-lg-9 ps-lg-4">

      <div class="cds-panel" aria-label="Saved payment methods">
        <div class="cds-panel-h"><h2><i class="fa-solid fa-wallet me-2" style="color:var(--cds-blue-60)"></i>Saved numbers</h2>
        <p>Numbers you pay with at checkout. Only the network and a masked number are shown.</p></div>
        <div class="cds-panel-b">
          <div id="payment-alert" style="display:none"></div>
          <div id="payment-methods-list">
            <div class="cds-empty" id="pm-loading"><span class="p-dots" aria-hidden="true"><span class="p-dot"></span><span class="p-dot"></span><span class="p-dot"></span></span></div>
          </div>
        </div>
      </div>

      <div class="cds-panel" aria-label="Billing history">
        <div class="cds-panel-h"><h2><i class="fa-solid fa-file-invoice-dollar me-2" style="color:var(--cds-blue-60)"></i>Billing history</h2>
        <p>Charges per booking, newest first.</p></div>
        <div class="cds-panel-b" style="overflow-x:auto">
          <table class="cds-table">
            <thead><tr><th>#</th><th>Booking ref</th><th class="hide-sm">Date</th><th>Payment</th><th style="text-align:right">Amount</th></tr></thead>
            <tbody id="billing-history-rows">
              <tr><td colspan="5"><div class="cds-empty"><span class="p-dots" aria-hidden="true"><span class="p-dot"></span><span class="p-dot"></span><span class="p-dot"></span></span></div></td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="cds-panel" aria-label="Refunds">
        <div class="cds-panel-h"><h2><i class="fa-solid fa-rotate-left me-2" style="color:var(--cds-blue-60)"></i>Refunds</h2>
        <p>Money returned to your mobile-money number after cancellation.</p></div>
        <div class="cds-panel-b" style="overflow-x:auto">
          <table class="cds-table">
            <thead><tr><th>#</th><th>Booking ref</th><th class="hide-sm">Date</th><th>Status</th><th style="text-align:right">Amount</th></tr></thead>
            <tbody id="refund-history-rows">
              <tr><td colspan="5"><div class="cds-empty"><span class="p-dots" aria-hidden="true"><span class="p-dot"></span><span class="p-dot"></span><span class="p-dot"></span></span></div></td></tr>
            </tbody>
          </table>
          <div class="cds-refund-note" style="margin-top:12px">
            <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
            <div>How refunds work:
              <ol>
                <li>Cancel an eligible stay from <a href="<?= $this->Url->build('/my-booking') ?>" style="color:var(--cds-blue-60);font-weight:600">My bookings</a> — the room is released immediately.</li>
                <li>Our team reviews the cancellation against the property’s policy.</li>
                <li>Approved refunds go back to the mobile-money number you paid with and appear above as <strong>Refunded</strong>.</li>
              </ol>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<div class="modal fade" id="addcard" tabindex="-1" role="dialog" aria-labelledby="addcardmodal" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content" id="addcardmodal">
      <div class="modal-header">
        <h4 class="modal-title" style="font-size:16px">Add mobile-money number</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="add-payment-form" novalidate>
          <div class="mb-3">
            <label for="pm-type">Network</label>
            <select id="pm-type">
              <option value="vodacom" selected>Vodacom M-Pesa (074, 075, 076)</option>
              <option value="tigo">Tigo Pesa (065, 067, 071)</option>
              <option value="airtel">Airtel Money (068, 069, 078)</option>
              <option value="halotel">HaloPesa — Halotel (061, 062)</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="mobile-phone">Phone number</label>
            <div class="cds-prefix">
              <span>+255</span>
              <input type="tel" id="mobile-phone" placeholder="712 345 678" inputmode="tel" autocomplete="tel" required>
            </div>
          </div>
          <div class="mb-3">
            <label for="mobile-name">Registered account name</label>
            <input type="text" id="mobile-name" placeholder="Full name as registered on SIM" autocomplete="cc-name">
          </div>
          <button type="submit" id="pm-add-btn">Save number</button>
        </form>
      </div>
    </div>
  </div>
</div>
</main>
<?= $this->element('footer', ['skin' => 'skin-light-footer']) ?>

<?php $this->start('script'); ?>
<?= $this->Html->script('/assets/js/payment-detail.js?v=' . filemtime(WWW_ROOT . 'assets/js/payment-detail.js')); ?>
<?php $this->end(); ?>
