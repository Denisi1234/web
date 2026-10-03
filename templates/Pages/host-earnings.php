<?php
$this->assign('title', 'Earnings');
$this->assign('portal_title', 'Earnings');
$metrics = $finance['metrics'] ?? $finance;
$out = $metrics['outstanding'] ?? $finance['outstanding_balance'] ?? ($finance['total_earnings'] ?? 0);
$earned = $metrics['total_earnings'] ?? $finance['total_earnings'] ?? 0;
$gross = $metrics['gross_revenue'] ?? $earned;

/*
 * Available balance comes from GET /payouts/summary - the exact calculation
 * POST /payouts/request validates against, so the figure shown here is the
 * figure the backend will accept. It is not re-derived in the view.
 */
$summary = is_array($payoutSummary ?? null) ? $payoutSummary : [];
$available = (float)($summary['available_balance'] ?? $out ?? 0);
$canRequest = $available >= 1000;
?>
<div class="row g-3 mb-3">
  <div class="col-md-4"><div class="p-stat"><div class="v">TSh <?= number_format($available) ?></div><div class="l">Available to withdraw</div><div class="s">Earned TSh <?= number_format((float)$earned) ?></div></div></div>
  <div class="col-md-4"><div class="p-stat"><div class="v"><?= count($payouts ?? []) ?></div><div class="l">Payouts</div><div class="s">M-Pesa / Tigo · TZS</div></div></div>
  <?php /* Removed: a hardcoded "10% / host keeps 90%" tile. The commission
           rate is per-booking (bookings.commission_rate) and can differ, so
           this asserted a rate that was frequently wrong. */ ?>
  <div class="col-md-4"><div class="p-stat"><div class="v">TSh <?= number_format((float)$gross) ?></div><div class="l">Gross booking value</div><div class="s">Your earnings TSh <?= number_format((float)$earned) ?></div></div></div>
</div>

<div class="p-card">
  <h3>Request a payout</h3>
  <div class="sub">Withdraw your available balance to mobile money or a bank account.</div>

  <?php if (!$canRequest): ?>
    <div class="p-empty">
      <?= $available > 0
            ? 'The minimum withdrawable amount is TSh 1,000.'
            : 'Nothing is available to withdraw yet. Earnings appear here once a stay is completed.' ?>
    </div>
  <?php else: ?>
    <form method="post"
          action="<?= $this->Url->build('/host/earnings/request-payout') ?>"
          id="payoutRequestForm"
          class="mt-3"
          style="max-width:520px">
      <input type="hidden" name="_csrfToken" value="<?= h($this->getRequest()->getAttribute('csrfToken') ?? '') ?>">

      <div class="mb-3">
        <label class="form-label" for="payout_amount">Amount (TSh)</label>
        <input type="number"
               class="form-control"
               id="payout_amount"
               name="amount"
               min="1000"
               step="1000"
               max="<?= h((string)(int)floor($available)) ?>"
               value="<?= h((string)(int)floor($available)) ?>"
               required>
        <div class="form-text">Available: TSh <?= number_format($available) ?></div>
      </div>

      <div class="mb-3">
        <label class="form-label" for="payout_method">Method</label>
        <select class="form-select" id="payout_method" name="payment_method" required>
          <option value="Mobile Money">Mobile Money (M-Pesa / Tigo / Airtel / HaloPesa)</option>
          <option value="Bank Transfer">Bank Transfer</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label" for="payout_account">
          <span id="payout_account_label">Mobile money number</span>
        </label>
        <input type="text"
               class="form-control"
               id="payout_account"
               name="account_details"
               maxlength="255"
               placeholder="e.g. 0714 000 000"
               required>
        <div class="form-text" id="payout_account_help">
          The number or account the money should be sent to. Our team verifies this before releasing funds.
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label" for="payout_notes">Note <span class="text-muted">(optional)</span></label>
        <textarea class="form-control" id="payout_notes" name="notes" rows="2" maxlength="500"></textarea>
      </div>

      <button type="submit" class="p-btn" id="payoutSubmitBtn">Request payout</button>
      <div class="form-text mt-2">Minimum TSh 1,000. Requests are reviewed by our finance team.</div>
    </form>
  <?php endif; ?>
</div>

<script>
(function () {
  // Switch the account field between a mobile-money number and a bank account,
  // so the host is not asked for the wrong kind of detail.
  var method   = document.getElementById('payout_method');
  var label    = document.getElementById('payout_account_label');
  var help     = document.getElementById('payout_account_help');
  var account  = document.getElementById('payout_account');

  if (method && label && help && account) {
    var sync = function () {
      var bank = method.value === 'Bank Transfer';
      label.textContent = bank ? 'Bank account details' : 'Mobile money number';
      account.placeholder = bank
        ? 'Bank name, account number, bank name'
        : 'e.g. 0714 000 000';
      help.textContent = bank
        ? 'Bank name, account number and bank. Verified before funds are released.'
        : 'The mobile money number the money should be sent to. Verified before funds are released.';
    };
    method.addEventListener('change', sync);
    sync();
  }

  var form = document.getElementById('payoutRequestForm');
  var btn  = document.getElementById('payoutSubmitBtn');
  if (form && btn) {
    form.addEventListener('submit', function () {
      btn.disabled = true;
      btn.textContent = 'Submitting...';
      // Guarantee the button is restored if the request never completes.
      setTimeout(function () {
        btn.disabled = false;
        btn.textContent = 'Request payout';
      }, 15000);
    });
  }
})();
</script>

<div class="p-card">
  <h3>Recent payouts</h3>
  <div class="sub">Latest settlement records</div>
  <?php if (empty($payouts)): ?>
    <div class="p-empty">No payouts yet.</div>
  <?php else: ?>
    <div class="p-table-wrap mt-3">
      <table class="p-table">
        <thead><tr><th>Reference</th><th>Method</th><th style="text-align:right">Amount</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach (array_slice($payouts, 0, 8) as $p): ?>
          <tr>
            <td><strong><?= h($p['payout_reference'] ?? $p['id'] ?? '—') ?></strong></td>
            <td><?= h($p['payment_method'] ?? $p['method'] ?? $p['gateway'] ?? '—') ?></td>
            <td style="text-align:right;font-weight:600">TSh <?= number_format((float)($p['amount'] ?? 0)) ?></td>
            <td><span class="p-badge <?= in_array(strtoupper((string)($p['status'] ?? '')), ['PAID', 'COMPLETED'], true) ? 'green' : 'yellow' ?>"><?= h($p['status'] ?? '—') ?></span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>