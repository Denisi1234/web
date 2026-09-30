<?php
$this->assign('title', 'Earnings');
$this->assign('portal_title', 'Earnings');
$metrics = $finance['metrics'] ?? $finance;
$out = $metrics['outstanding'] ?? $finance['outstanding_balance'] ?? ($finance['total_earnings'] ?? 0);
$earned = $metrics['total_earnings'] ?? $finance['total_earnings'] ?? 0;
$gross = $metrics['gross_revenue'] ?? $earned;
?>
<div class="row g-3 mb-3">
  <div class="col-md-4"><div class="p-stat"><div class="v">TSh <?= number_format((float)$out) ?></div><div class="l">Outstanding balance</div><div class="s">Earned TSh <?= number_format((float)$earned) ?></div></div></div>
  <div class="col-md-4"><div class="p-stat"><div class="v"><?= count($payouts ?? []) ?></div><div class="l">Payouts</div><div class="s">M-Pesa / Tigo · TZS</div></div></div>
  <div class="col-md-4"><div class="p-stat"><div class="v">10%</div><div class="l">Platform fee</div><div class="s">Host keeps 90% · Gross TSh <?= number_format((float)$gross) ?></div></div></div>
</div>

<div class="p-card">
  <h3>Recent payouts</h3>
  <div class="sub">Latest settlement records</div>
  <?php if (empty($payouts)): ?>
    <div class="p-empty">No payouts yet.</div>
  <?php else: ?>
    <div class="p-table-wrap mt-3">
      <table class="p-table">
        <thead><tr><th>ID</th><th>Method</th><th style="text-align:right">Amount</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach (array_slice($payouts, 0, 8) as $p): ?>
          <tr>
            <td><strong><?= h($p['id'] ?? $p['payout_id'] ?? '—') ?></strong></td>
            <td><?= h($p['method'] ?? $p['gateway'] ?? '—') ?></td>
            <td style="text-align:right;font-weight:600">TSh <?= number_format((float)($p['amount'] ?? 0)) ?></td>
            <td><span class="p-badge <?= ($p['status'] ?? '') === 'completed' ? 'green' : 'yellow' ?>"><?= h($p['status'] ?? 'pending') ?></span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
