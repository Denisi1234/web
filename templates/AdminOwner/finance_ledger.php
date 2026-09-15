<?php $this->assign('title', 'Finance Ledger | FastNetStays'); ?>
<?= $this->element('navbar') ?>
<style>.host-card{border:1px solid #e8eaed;border-radius:16px;box-shadow:0 6px 16px rgba(0,0,0,.05);background:#fff}</style>
<div class="container py-4" style="max-width:1280px">
  <h1 style="font-size:20px;font-weight:800">Transaction Ledger</h1><p style="font-size:12px;color:#5f6368">Port of chart-chartjs.php — GET /finance/ledger 13 filters</p>

  <form method="get" class="host-card p-3 mb-3">
    <div class="row g-2">
      <div class="col-6 col-md-3"><input name="search" value="<?= h($params['search'] ?? '') ?>" placeholder="Search" class="form-control" style="border-radius:12px;height:44px"></div>
      <div class="col-6 col-md-2"><input name="transaction_id" value="<?= h($params['transaction_id'] ?? '') ?>" placeholder="Tx ID" class="form-control" style="border-radius:12px;height:44px"></div>
      <div class="col-6 col-md-2"><input name="booking_reference" value="<?= h($params['booking_reference'] ?? '') ?>" placeholder="Booking ref" class="form-control" style="border-radius:12px;height:44px"></div>
      <div class="col-6 col-md-2"><select name="payment_status" class="form-select" style="border-radius:12px;height:44px"><option value="">Payment status</option><option value="paid" <?= ($params['payment_status'] ?? '')==='paid'?'selected':'' ?>>Paid</option><option value="pending" <?= ($params['payment_status'] ?? '')==='pending'?'selected':'' ?>>Pending</option><option value="refunded" <?= ($params['payment_status'] ?? '')==='refunded'?'selected':'' ?>>Refunded</option></select></div>
      <div class="col-6 col-md-2"><select name="payout_status" class="form-select" style="border-radius:12px;height:44px"><option value="">Payout</option><option value="REQUESTED" <?= ($params['payout_status'] ?? '')==='REQUESTED'?'selected':'' ?>>Requested</option><option value="PAID" <?= ($params['payout_status'] ?? '')==='PAID'?'selected':'' ?>>Paid</option><option value="FAILED" <?= ($params['payout_status'] ?? '')==='FAILED'?'selected':'' ?>>Failed</option></select></div>
      <div class="col-6 col-md-2"><input type="date" name="date_from" value="<?= h($params['date_from'] ?? '') ?>" class="form-control" style="border-radius:12px;height:44px"></div>
      <div class="col-6 col-md-2"><input type="date" name="date_to" value="<?= h($params['date_to'] ?? '') ?>" class="form-control" style="border-radius:12px;height:44px"></div>
      <div class="col-6 col-md-2"><select name="sort_by" class="form-select" style="border-radius:12px;height:44px"><option value="">Sort</option><option value="created_at_desc" <?= ($params['sort_by'] ?? '')==='created_at_desc'?'selected':'' ?>>Newest</option><option value="amount_desc" <?= ($params['sort_by'] ?? '')==='amount_desc'?'selected':'' ?>>Amount ↓</option></select></div>
      <div class="col-12 col-md-2 d-flex gap-2"><button class="btn" style="background:#2563EB;color:#fff;border-radius:12px;height:44px;flex:1">Filter</button><a href="<?= $this->Url->build('/admin/finance/ledger') ?>" class="btn" style="border:1px solid #e8eaed;border-radius:12px;height:44px;flex:1">Clear</a></div>
    </div>
  </form>

  <?php if (empty($transactions)): ?><div class="host-card p-5 text-center" style="color:#5f6368">No transactions.</div>
  <?php else: ?>
  <div class="host-card p-0 overflow-hidden">
    <div class="table-responsive"><table class="table table-hover align-middle mb-0" style="font-size:12px"><thead style="background:#F8FAFC;font-size:11px;color:#5f6368"><tr><th>Tx</th><th>Date</th><th>Owner/Lodge</th><th>Booking</th><th>Gross</th><th>10%</th><th>Net 90%</th><th>Type</th><th>Pay</th><th>Payout</th></tr></thead><tbody>
      <?php foreach ($transactions as $t): ?>
      <tr>
        <td style="font-weight:700;font-size:11px"><?= h($t['transaction_id'] ?? $t['id'] ?? '—') ?></td>
        <td style="font-size:11px"><?= h(substr((string)($t['created_at'] ?? $t['date'] ?? ''),0,10)) ?></td>
        <td><div style="font-weight:700"><?= h($t['owner_name'] ?? $t['owner']['name'] ?? '') ?></div><div style="font-size:11px;color:#5f6368"><?= h($t['property_name'] ?? $t['lodge_name'] ?? $t['property']['name'] ?? '') ?></div></td>
        <td style="font-size:11px"><?= h($t['booking_reference'] ?? $t['booking_code'] ?? '') ?></td>
        <td style="color:#1a1d25;font-weight:700">TSh <?= number_format((float)($t['gross_amount'] ?? $t['amount'] ?? $t['gross'] ?? 0)) ?></td>
        <td style="color:#2563EB;font-weight:700">TSh <?= number_format((float)($t['platform_fee'] ?? $t['platform_commission'] ?? (($t['gross_amount'] ?? $t['amount'] ?? 0)*0.10))) ?></td>
        <td style="color:#15803d;font-weight:700">TSh <?= number_format((float)($t['owner_amount'] ?? $t['owner_earnings'] ?? (($t['gross_amount'] ?? $t['amount'] ?? 0)*0.90))) ?></td>
        <td><span style="background:#F0F3FF;color:#2563EB;border-radius:9999px;padding:3px 6px;font-size:10px"><?= h($t['transaction_type'] ?? $t['type'] ?? '') ?></span></td>
        <td><span style="background:#dcfce7;color:#15803d;border-radius:9999px;padding:3px 6px;font-size:10px"><?= h($t['payment_status'] ?? '') ?></span></td>
        <td><span style="background:#fef3c7;color:#d97706;border-radius:9999px;padding:3px 6px;font-size:10px"><?= h($t['payout_status'] ?? '') ?></span></td>
      </tr>
      <?php endforeach; ?>
    </tbody></table></div>
  </div>
  <div class="d-flex justify-content-between mt-3">
    <a href="?<?= http_build_query(array_merge($params, ['page'=>max(1, (int)($params['page'] ?? 1)-1)])) ?>" class="btn" style="border:1px solid #e8eaed;border-radius:9999px">Prev</a>
    <a href="?<?= http_build_query(array_merge($params, ['page'=>(int)($params['page'] ?? 1)+1])) ?>" class="btn" style="border:1px solid #e8eaed;border-radius:9999px">Next</a>
  </div>
  <?php endif; ?>
</div>
<?= $this->element('footer', ['skin'=>'skin-light-footer']) ?>
