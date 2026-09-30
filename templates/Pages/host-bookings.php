<?php
$this->assign('title', 'Guest Bookings');
$this->assign('portal_title', 'Bookings');
$tab = strtolower(trim((string)($this->getRequest()->getQuery('tab', 'all'))));
$tabs = ['all' => 'All', 'pending' => 'Pending', 'booked' => 'Booked', 'paid' => 'Paid', 'canceled' => 'Canceled', 'refunded' => 'Refunded'];
$filtered = $bookings ?? [];
if ($tab !== 'all') {
  $filtered = array_values(array_filter($bookings ?? [], fn($b) => strtolower((string)($b['payment_status'] ?? $b['status'] ?? '')) === $tab || ($tab === 'canceled' && in_array(strtolower((string)($b['status'] ?? '')), ['canceled', 'cancelled']))));
}
$this->assign('page_actions', '<span style="font-size:13px;color:var(--p-text-2)">' . count($filtered) . ' / ' . count($bookings ?? []) . '</span>');
?>
<div class="p-card mb-3">
  <div style="display:flex;gap:8px;flex-wrap:wrap">
    <?php foreach ($tabs as $k => $label):
      $cnt = $k === 'all' ? count($bookings ?? []) : count(array_filter($bookings ?? [], fn($b) => strtolower((string)($b['payment_status'] ?? $b['status'] ?? '')) === $k || ($k === 'canceled' && in_array(strtolower((string)($b['status'] ?? '')), ['canceled', 'cancelled']))));
    ?>
      <a href="?tab=<?= $k ?>" class="p-btn <?= $tab === $k ? '' : 'ghost' ?>" style="min-height:36px;font-size:13px"><?= h($label) ?> (<?= $cnt ?>)</a>
    <?php endforeach; ?>
  </div>
</div>

<?php if (empty($filtered)): ?>
  <div class="p-card"><div class="p-empty">No <?= h($tab) ?> bookings.</div></div>
<?php else: ?>
  <div class="p-table-wrap">
    <table class="p-table">
      <thead><tr><th>Reference</th><th>Guest</th><th>Property</th><th>Dates</th><th>Status</th><th style="text-align:right">Total</th></tr></thead>
      <tbody>
        <?php foreach ($filtered as $b):
          $st = strtolower((string)($b['payment_status'] ?? $b['status'] ?? 'pending'));
          $cls = $st === 'paid' || $st === 'completed' ? 'green' : ($st === 'pending' ? 'yellow' : (in_array($st, ['canceled', 'cancelled', 'refunded']) ? 'red' : 'blue'));
        ?>
        <tr>
          <td><strong>#<?= h($b['booking_code'] ?? $b['id'] ?? '—') ?></strong></td>
          <td><?= h($b['guest_name'] ?? $b['guest']['name'] ?? 'Guest') ?></td>
          <td><?= h($b['property']['name'] ?? ($b['room']['property']['name'] ?? '')) ?></td>
          <td style="white-space:nowrap;font-size:13px;color:var(--p-text-2)"><?= h(substr((string)($b['check_in'] ?? ''), 0, 10)) ?> → <?= h(substr((string)($b['check_out'] ?? ''), 0, 10)) ?></td>
          <td><span class="p-badge <?= $cls ?>"><?= h($b['payment_status'] ?? $b['status'] ?? 'pending') ?></span></td>
          <td style="text-align:right;font-weight:600;white-space:nowrap">TSh <?= number_format((float)($b['total_price'] ?? $b['total_amount'] ?? 0)) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
