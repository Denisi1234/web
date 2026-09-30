<?php
$this->assign('title', 'Bookings');
$this->assign('portal_title', 'Bookings');
$this->assign('page_actions', '<span style="font-size:13px;color:var(--p-text-2)">' . count($bookings ?? []) . ' total</span>');
?>
<div class="p-card mb-3">
  <?= $this->Form->create(null, ['type' => 'get', 'style' => 'display:flex;gap:8px;flex-wrap:wrap;align-items:end']) ?>
    <div style="flex:1;min-width:200px">
      <label style="font-size:12px;font-weight:600;color:var(--p-text-2)">Search</label>
      <input name="search" value="<?= h($search ?? '') ?>" placeholder="Guest, property, id" class="form-control" style="min-height:40px">
    </div>
    <div style="min-width:170px">
      <label style="font-size:12px;font-weight:600;color:var(--p-text-2)">Status</label>
      <select name="status" class="form-select" style="min-height:40px" onchange="this.form.submit()">
        <?php foreach (['all' => 'All', 'Pending' => 'Pending', 'Confirmed' => 'Confirmed', 'Checked In' => 'Checked In', 'Completed' => 'Completed', 'Cancelled' => 'Cancelled'] as $k => $v): ?>
          <option value="<?= $k ?>" <?= ($status ?? 'all') === $k ? 'selected' : '' ?>><?= $v ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button class="p-btn" type="submit">Filter</button>
    <a href="<?= $this->Url->build('/admin/bookings') ?>" class="p-btn ghost">Clear</a>
  <?= $this->Form->end() ?>
</div>

<?php if (empty($bookings)): ?>
  <div class="p-card"><div class="p-empty">No bookings.</div></div>
<?php else: ?>
  <div class="p-table-wrap">
    <div class="table-responsive"><table class="p-table">
      <thead><tr><th>Booking</th><th>Guest</th><th>Property</th><th>Dates</th><th style="text-align:right">Total</th><th>Status</th><th>Update</th></tr></thead>
      <tbody>
        <?php foreach ($bookings as $b):
          $bid = $b['id'] ?? $b['booking_code'] ?? '';
          $st = strtolower((string)($b['status'] ?? $b['payment_status'] ?? 'pending'));
          $badge = $st === 'confirmed' ? 'blue' : ($st === 'completed' ? 'green' : (in_array($st, ['cancelled', 'canceled'], true) ? 'red' : 'yellow'));
          $price = (float)($b['total_price'] ?? $b['total_amount'] ?? 0);
          $ci = substr((string)($b['check_in'] ?? ''), 0, 10);
          $co = substr((string)($b['check_out'] ?? ''), 0, 10);
          $nights = 1;
          try { if ($ci && $co) $nights = max(1, (new DateTime($co))->diff(new DateTime($ci))->days); } catch (Throwable $e) {}
        ?>
        <tr>
          <td><strong>#<?= h($bid) ?></strong></td>
          <td><?= h($b['guest_name'] ?? $b['guest']['name'] ?? 'Guest') ?></td>
          <td style="max-width:170px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= h($b['property_name'] ?? $b['property']['name'] ?? '') ?></td>
          <td style="font-size:12px;white-space:nowrap"><?= h($ci) ?> → <?= h($co) ?> (<?= $nights ?>n)</td>
          <td style="text-align:right;font-weight:600;white-space:nowrap">TSh <?= number_format($price) ?></td>
          <td><span class="p-badge <?= $badge ?>"><?= h($b['status'] ?? $b['payment_status'] ?? '—') ?></span></td>
          <td>
            <?= $this->Form->create(null, ['url' => ['controller' => 'AdminOwner', 'action' => 'bookings'], 'style' => 'display:flex;gap:6px']) ?>
              <?= $this->Form->hidden('booking_id', ['value' => $bid]) ?>
              <select name="status" class="form-select form-select-sm" style="min-height:32px;min-width:120px">
                <?php foreach (['Pending', 'Confirmed', 'Checked In', 'Completed', 'Cancelled'] as $opt): ?>
                  <option value="<?= $opt ?>" <?= $st === strtolower($opt) ? 'selected' : '' ?>><?= $opt ?></option>
                <?php endforeach; ?>
              </select>
              <button class="p-btn" style="min-height:32px;font-size:12px">Save</button>
            <?= $this->Form->end() ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table></div>
  </div>
<?php endif; ?>
