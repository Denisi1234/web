<?php $this->assign('title', 'Admin Bookings | FastNetStays'); ?>
<?= $this->element('navbar') ?>
<style>.host-card{border:1px solid #e8eaed;border-radius:16px;box-shadow:0 6px 16px rgba(0,0,0,.05);background:#fff}.pill{border-radius:9999px;padding:4px 8px;font-size:11px;font-weight:700}.tab-pill{border-radius:9999px;padding:6px 12px;font-size:12px;font-weight:700;border:1px solid #e8eaed;background:#fff;color:#5f6368}.tab-pill.active{background:#2563EB;color:#fff;border-color:#2563EB}</style>
<div class="container py-4" style="max-width:1280px">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div><h1 style="font-size:20px;font-weight:800">Admin Bookings</h1><p style="font-size:12px;color:#5f6368">Port of fastnet_admin_portal/lib/screens/bookings_screen.dart — GET /admin/bookings + PATCH /admin/bookings/{id}/status</p></div>
    <span style="background:#F0F3FF;color:#2563EB;border-radius:9999px;padding:6px 12px;font-size:11px;font-weight:700"><?= count($bookings ?? []) ?> total</span>
  </div>

  <form method="get" class="d-flex gap-2 mb-3 flex-wrap">
    <input name="search" value="<?= h($search ?? '') ?>" placeholder="Search guest/property/id" class="form-control" style="max-width:260px;border-radius:12px;height:44px">
    <select name="status" class="form-select" style="max-width:180px;border-radius:12px;height:44px" onchange="this.form.submit()">
      <option value="all" <?= ($status ?? 'all')==='all'?'selected':'' ?>>All</option>
      <option value="Pending" <?= ($status ?? '')==='Pending'?'selected':'' ?>>Pending</option>
      <option value="Confirmed" <?= ($status ?? '')==='Confirmed'?'selected':'' ?>>Confirmed</option>
      <option value="Checked In" <?= ($status ?? '')==='Checked In'?'selected':'' ?>>Checked In</option>
      <option value="Completed" <?= ($status ?? '')==='Completed'?'selected':'' ?>>Completed</option>
      <option value="Cancelled" <?= ($status ?? '')==='Cancelled'?'selected':'' ?>>Cancelled</option>
    </select>
    <button class="btn" style="background:#2563EB;color:#fff;border-radius:12px;height:44px">Filter</button>
    <a href="<?= $this->Url->build('/admin/bookings') ?>" class="btn" style="border:1px solid #e8eaed;border-radius:12px;height:44px">Clear</a>
  </form>

  <?php if (empty($bookings)): ?><div class="host-card p-5 text-center" style="color:#5f6368">No bookings.</div>
  <?php else: ?>
  <div class="host-card p-0 overflow-hidden">
    <div class="table-responsive"><table class="table align-middle mb-0" style="font-size:13px"><thead style="background:#F8FAFC;font-size:11px;color:#5f6368"><tr><th>Booking</th><th>Guest</th><th>Property</th><th>Check-in → Check-out (nights)</th><th>Total</th><th>Status</th><th>Update</th></tr></thead><tbody>
      <?php foreach ($bookings as $b):
        $bid=$b['id'] ?? $b['booking_code'] ?? '';
        $st= strtolower((string)($b['status'] ?? $b['payment_status'] ?? 'pending'));
        $price=(float)($b['total_price'] ?? $b['total_amount'] ?? 0);
        $ci=substr((string)($b['check_in'] ?? ''),0,10);
        $co=substr((string)($b['check_out'] ?? ''),0,10);
        $nights = 1;
        try { if($ci && $co) $nights = max(1, (new DateTime($co))->diff(new DateTime($ci))->days); } catch (Throwable $e) {}
      ?><tr>
        <td style="font-weight:800;color:#2563EB">#<?= h($bid) ?></td>
        <td><?= h($b['guest_name'] ?? $b['guest']['name'] ?? 'Guest') ?></td>
        <td style="max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= h($b['property_name'] ?? $b['property']['name'] ?? '') ?></td>
        <td style="font-size:12px"><?= h($ci) ?> → <?= h($co) ?> (<?= $nights ?> nights)</td>
        <td style="color:#C2410C;font-weight:800">TSh <?= number_format($price) ?></td>
        <td><span class="pill" style="background:<?= $st==='confirmed'?'#EBF5FF;color:#1A56DB':($st==='completed'?'#dcfce7;color:#15803d':($st==='cancelled'?'#fee2e2;color:#dc2626':($st==='checked in'?'#dcfce7;color:#15803d':'#fef3c7;color:#d97706'))) ?>"><?= h($b['status'] ?? $b['payment_status'] ?? '—') ?></span></td>
        <td>
          <?= $this->Form->create(null, ['url'=>['controller'=>'AdminOwner','action'=>'bookings']]) ?>
            <?= $this->Form->hidden('booking_id',['value'=>$bid]) ?>
            <div class="d-flex gap-1">
              <select name="status" class="form-select form-select-sm" style="border-radius:9999px;min-width:130px">
                <option value="Pending" <?= $st==='pending'?'selected':'' ?>>Pending</option>
                <option value="Confirmed" <?= $st==='confirmed'?'selected':'' ?>>Confirmed</option>
                <option value="Checked In" <?= $st==='checked in'?'selected':'' ?>>Checked In</option>
                <option value="Completed" <?= $st==='completed'?'selected':'' ?>>Completed</option>
                <option value="Cancelled" <?= $st==='cancelled' || $st==='canceled'?'selected':'' ?>>Cancelled</option>
              </select>
              <button class="btn btn-sm" style="background:#2563EB;color:#fff;border-radius:9999px">Save</button>
            </div>
          <?= $this->Form->end() ?>
        </td>
      </tr><?php endforeach; ?>
    </tbody></table></div>
  </div>
  <?php endif; ?>
</div>
<?= $this->element('footer', ['skin'=>'skin-light-footer']) ?>
