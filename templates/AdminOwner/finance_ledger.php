<?php
$this->assign('title', 'Ledger');
$this->assign('portal_title', 'Transaction ledger');
$this->assign('page_actions', '<a href="' . $this->Url->build('/admin/finance/ledger') . '" class="p-btn ghost">Clear filters</a>');
?>
<div class="p-card mb-3">
  <?= $this->Form->create(null, ['type' => 'get']) ?>
    <div class="row g-2">
      <div class="col-6 col-md-3"><input name="search" value="<?= h($params['search'] ?? '') ?>" placeholder="Search" class="form-control" style="min-height:40px"></div>
      <div class="col-6 col-md-2"><input name="transaction_id" value="<?= h($params['transaction_id'] ?? '') ?>" placeholder="Tx ID" class="form-control" style="min-height:40px"></div>
      <div class="col-6 col-md-2"><input name="booking_reference" value="<?= h($params['booking_reference'] ?? '') ?>" placeholder="Booking ref" class="form-control" style="min-height:40px"></div>
      <div class="col-6 col-md-2">
        <select name="payment_status" class="form-select" style="min-height:40px">
          <option value="">Payment status</option>
          <option value="paid" <?= ($params['payment_status'] ?? '') === 'paid' ? 'selected' : '' ?>>Paid</option>
          <option value="pending" <?= ($params['payment_status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
          <option value="refunded" <?= ($params['payment_status'] ?? '') === 'refunded' ? 'selected' : '' ?>>Refunded</option>
        </select>
      </div>
      <div class="col-6 col-md-2">
        <select name="payout_status" class="form-select" style="min-height:40px">
          <option value="">Payout</option>
          <option value="REQUESTED" <?= ($params['payout_status'] ?? '') === 'REQUESTED' ? 'selected' : '' ?>>Requested</option>
          <option value="PAID" <?= ($params['payout_status'] ?? '') === 'PAID' ? 'selected' : '' ?>>Paid</option>
          <option value="FAILED" <?= ($params['payout_status'] ?? '') === 'FAILED' ? 'selected' : '' ?>>Failed</option>
        </select>
      </div>
      <div class="col-6 col-md-2"><input type="date" name="date_from" value="<?= h($params['date_from'] ?? '') ?>" class="form-control" style="min-height:40px" aria-label="Date from"></div>
      <div class="col-6 col-md-2"><input type="date" name="date_to" value="<?= h($params['date_to'] ?? '') ?>" class="form-control" style="min-height:40px" aria-label="Date to"></div>
      <div class="col-6 col-md-2">
        <select name="sort_by" class="form-select" style="min-height:40px">
          <option value="">Sort</option>
          <option value="created_at_desc" <?= ($params['sort_by'] ?? '') === 'created_at_desc' ? 'selected' : '' ?>>Newest</option>
          <option value="amount_desc" <?= ($params['sort_by'] ?? '') === 'amount_desc' ? 'selected' : '' ?>>Amount ↓</option>
        </select>
      </div>
      <div class="col-12 col-md-2" style="display:flex;gap:8px"><button class="p-btn" style="flex:1;justify-content:center">Filter</button></div>
    </div>
  <?= $this->Form->end() ?>
</div>

<?php if (empty($transactions)): ?>
  <div class="p-card"><div class="p-empty">No transactions.</div></div>
<?php else: ?>
  <div class="p-table-wrap">
    <div class="table-responsive"><table class="p-table" style="font-size:13px">
      <thead><tr><th>Tx</th><th>Date</th><th>Owner / Lodge</th><th>Booking</th><th style="text-align:right">Gross</th><th style="text-align:right">Commission</th><th style="text-align:right">Owner net</th><th>Type</th><th>Pay</th><th>Payout</th></tr></thead>
      <tbody>
        <?php foreach ($transactions as $t): ?>
        <tr>
          <td style="font-weight:600;font-size:12px"><?= h($t['transaction_id'] ?? $t['id'] ?? '—') ?></td>
          <td style="font-size:12px"><?= h(substr((string)($t['created_at'] ?? $t['date'] ?? ''), 0, 10)) ?></td>
          <td><div style="font-weight:600"><?= h($t['owner_name'] ?? $t['owner']['name'] ?? '') ?></div><div style="font-size:11px;color:var(--p-text-2)"><?= h($t['property_name'] ?? $t['lodge_name'] ?? $t['property']['name'] ?? $t['lodge']['name'] ?? '') ?></div></td>
          <td style="font-size:12px"><?= h($t['booking_reference'] ?? $t['booking_code'] ?? '') ?></td>
          <td style="text-align:right;font-weight:600">TSh <?= number_format((float)($t['gross_amount'] ?? $t['amount'] ?? $t['gross'] ?? 0)) ?></td>
          <td style="text-align:right;font-weight:600">TSh <?= number_format((float)($t['platform_fee'] ?? $t['platform_fee_10'] ?? $t['platform_commission'] ?? 0)) ?></td>
          <td style="text-align:right;font-weight:600">TSh <?= number_format((float)($t['owner_amount'] ?? $t['owner_net_90'] ?? $t['owner_earnings'] ?? 0)) ?></td>
          <td><span class="p-badge blue"><?= h($t['transaction_type'] ?? $t['type'] ?? '') ?></span></td>
          <td><span class="p-badge green"><?= h($t['payment_status'] ?? '') ?></span></td>
          <td><span class="p-badge yellow"><?= h($t['payout_status'] ?? '') ?></span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table></div>
  </div>
  <div style="display:flex;justify-content:space-between;margin-top:12px">
    <a href="?<?= http_build_query(array_merge($params, ['page' => max(1, (int)($params['page'] ?? 1) - 1)])) ?>" class="p-btn ghost">Prev</a>
    <a href="?<?= http_build_query(array_merge($params, ['page' => (int)($params['page'] ?? 1) + 1])) ?>" class="p-btn ghost">Next</a>
  </div>
<?php endif; ?>
