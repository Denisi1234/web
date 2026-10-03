<?php
$this->assign('title', 'Dashboard');
$this->assign('portal_title', 'Overview');
$gross = (float)($stats['revenue'] ?? 0);
$fee = (float)($stats['platform_fee'] ?? 0);
$net = (float)($stats['owner_earnings'] ?? 0);
?>
<div class="row g-3 mb-4">
  <div class="col-6 col-xl-3"><div class="p-stat"><div class="v"><?= (int)($stats['properties'] ?? 0) ?></div><div class="l">Lodges</div><div class="s"><?= count($owners ?? []) ?> owners on platform</div></div></div>
  <div class="col-6 col-xl-3"><div class="p-stat"><div class="v"><?= (int)($stats['bookings'] ?? 0) ?></div><div class="l">Bookings</div><div class="s">All properties</div></div></div>
  <div class="col-6 col-xl-3"><div class="p-stat"><div class="v">TSh <?= number_format($gross) ?></div><div class="l">Gross booking value</div><div class="s">Platform commission · TSh <?= number_format($fee) ?></div></div></div>
  <div class="col-6 col-xl-3"><div class="p-stat"><div class="v">TSh <?= number_format($net) ?></div><div class="l">Owner earnings</div><div class="s">Owner share</div></div></div>
</div>

<?php /* Verification queue counts, from GET /admin/verification/summary.
         Previously the dashboard inferred these by scanning the full property,
         booking and user collections. */ ?>
<div class="row g-3 mb-3">
  <div class="col-6 col-xl-3"><div class="p-stat">
    <div class="v"><?= (int)($verificationCounts['pending_owners'] ?? 0) ?></div>
    <div class="l">Owners awaiting review</div>
    <div class="s"><?= (int)($verificationCounts['approved_owners'] ?? 0) ?> approved</div>
  </div></div>
  <div class="col-6 col-xl-3"><div class="p-stat">
    <div class="v"><?= (int)($verificationCounts['pending_lodges'] ?? 0) ?></div>
    <div class="l">Lodges awaiting review</div>
    <div class="s"><?= (int)($verificationCounts['approved_lodges'] ?? 0) ?> approved</div>
  </div></div>
</div>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="p-card">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div><h3>Recent lodges</h3><div class="sub">Latest properties awaiting or passed review</div></div>
        <a href="<?= $this->Url->build('/admin/lodges') ?>" class="p-btn ghost">View all</a>
      </div>
      <?php if (empty($recentProperties)): ?>
        <div class="p-empty">No lodges yet.</div>
      <?php else: ?>
        <div class="p-table-wrap"><table class="p-table">
          <thead><tr><th>Lodge</th><th>Owner</th><th>City</th><th>Price / night</th><th>Status</th></tr></thead>
          <tbody>
            <?php foreach ($recentProperties as $p): $st = strtolower((string)($p['status'] ?? 'active')); $h = $p['host'] ?? []; ?>
            <tr>
              <td><strong><?= h($p['name'] ?? '—') ?></strong></td>
              <td><div style="font-weight:600;font-size:13px"><?= h($h['name'] ?? '—') ?></div><div style="font-size:11px;color:var(--p-text-2)"><?= h($h['email'] ?? '') ?></div></td>
              <td><?= h($p['city'] ?? '') ?></td>
              <td>TSh <?= number_format((float)($p['price_per_night'] ?? 0)) ?></td>
              <td><span class="p-badge <?= $st === 'active' ? 'green' : ($st === 'pending' ? 'yellow' : 'blue') ?>"><?= h($p['status'] ?? 'Active') ?></span></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table></div>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="p-card">
      <h3>Quick actions</h3>
      <div class="sub">Common review and money tasks</div>
      <div class="d-grid gap-2 mt-3">
        <a href="<?= $this->Url->build('/admin/owners') ?>" class="p-btn">Verify owners</a>
        <a href="<?= $this->Url->build('/admin/lodges') ?>" class="p-btn ghost">Verify lodges</a>
        <a href="<?= $this->Url->build('/admin/finance/payouts') ?>" class="p-btn ghost">Process payouts</a>
        <a href="<?= $this->Url->build('/admin/finance/ledger') ?>" class="p-btn ghost">Open ledger</a>
        <a href="<?= $this->Url->build('/admin/support') ?>" class="p-btn ghost">Support tickets</a>
      </div>
    </div>
  </div>
</div>
