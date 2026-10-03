<?php
$this->assign('title', 'Dashboard');
$this->assign('portal_title', 'Overview');
$this->assign('page_actions', '<a href="' . $this->Url->build('/host/onboarding') . '" class="p-btn">Add property</a>');
$gross = (float)($stats['revenue'] ?? 0);
// Use the backend's stored commission_rate / platform_fee / owner_payout.
// This re-derived a flat 10/90 from gross, so the numbers could not reconcile
// with the payout ledger when a booking had a different commission rate.
$fee = (float)($stats['platform_fee'] ?? 0);
$net = (float)($stats['owner_earnings'] ?? 0);
$roomsCount = 0;
foreach (($properties ?? []) as $p) { $roomsCount += (int)($p['room_count'] ?? (isset($p['rooms']) ? count($p['rooms']) : 0)); }
$recentBookings = array_slice($bookings ?? [], 0, 5);
?>
<?php if (!empty($backendError) && empty($properties) && empty($bookings)): ?>
<div class="p-card mb-3" style="border-left:3px solid #f1c21b">
  <div style="font-size:14px;font-weight:600">Couldn't reach the server</div>
  <div style="font-size:13px;color:var(--p-text-2)">Live figures couldn't be loaded — zeros below mean connection trouble, not an empty account.</div>
  <div style="margin-top:12px"><a href="<?= $this->Url->build('/host/dashboard') ?>" class="p-btn">Retry</a></div>
</div>
<?php endif; ?>
<div class="row g-3 mb-4">
  <div class="col-6 col-xl-3"><div class="p-stat"><div class="v"><?= (int)($stats['properties'] ?? 0) ?></div><div class="l">Properties</div><div class="s"><?= $roomsCount ?> rooms total</div></div></div>
  <div class="col-6 col-xl-3"><div class="p-stat"><div class="v"><?= (int)($stats['bookings'] ?? 0) ?></div><div class="l">Bookings</div><div class="s">All your listings</div></div></div>
  <div class="col-6 col-xl-3"><div class="p-stat"><div class="v">TSh <?= number_format($gross) ?></div><div class="l">Gross revenue</div><div class="s">Your share · TSh <?= number_format($net) ?></div></div></div>
  <div class="col-6 col-xl-3"><div class="p-stat"><div class="v">TSh <?= number_format($fee) ?></div><div class="l">Platform fee</div><div class="s"><?= $gross > 0 ? round($fee / $gross * 100, 1) . '% of gross' : 'Share of bookings' ?></div></div></div>
</div>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="p-card">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div><h3>Latest bookings</h3><div class="sub">Most recent reservations across your properties</div></div>
        <a href="<?= $this->Url->build('/host/bookings') ?>" class="p-btn ghost">View all</a>
      </div>
      <?php if (empty($recentBookings)): ?>
        <div class="p-empty">No bookings yet. New reservations will appear here.</div>
      <?php else: ?>
        <div class="p-table-wrap"><table class="p-table">
          <thead><tr><th>Reference</th><th>Guest</th><th>Property</th><th>Total</th><th>Status</th></tr></thead>
          <tbody>
            <?php foreach ($recentBookings as $b): $st = strtolower((string)($b['status'] ?? $b['payment_status'] ?? 'pending')); ?>
            <tr>
              <td><strong>#<?= h($b['id'] ?? $b['booking_code'] ?? '') ?></strong></td>
              <td><?= h($b['guest_name'] ?? $b['guest']['name'] ?? 'Guest') ?></td>
              <td><?= h($b['property_name'] ?? $b['property']['name'] ?? '') ?></td>
              <td>TSh <?= number_format((float)($b['total_price'] ?? $b['total_amount'] ?? 0)) ?></td>
              <td><span class="p-badge <?= $st === 'confirmed' ? 'blue' : ($st === 'completed' ? 'green' : ($st === 'cancelled' ? 'red' : 'yellow')) ?>"><?= h($b['status'] ?? $b['payment_status'] ?? '—') ?></span></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table></div>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="p-card">
      <h3>Manage</h3>
      <div class="sub">Day-to-day hosting tasks</div>
      <div class="d-grid gap-2 mt-3">
        <a href="<?= $this->Url->build('/host/listings') ?>" class="p-btn">My properties</a>
        <a href="<?= $this->Url->build('/host/rooms') ?>" class="p-btn ghost">Rooms</a>
        <a href="<?= $this->Url->build('/host/bookings') ?>" class="p-btn ghost">Bookings</a>
        <a href="<?= $this->Url->build('/host/earnings') ?>" class="p-btn ghost">Earnings</a>
        <a href="<?= $this->Url->build('/host/onboarding') ?>" class="p-btn ghost">Add property</a>
      </div>
    </div>
  </div>
</div>
