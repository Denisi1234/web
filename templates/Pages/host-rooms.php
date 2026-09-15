<?php $this->assign('title', 'My Rooms | FastNetStays'); ?>
<?= $this->element('navbar') ?>
<style>.host-card{border:1px solid #e8eaed;border-radius:16px;box-shadow:0 6px 16px rgba(0,0,0,.05);background:#fff}.pill{border-radius:9999px;padding:4px 8px;font-size:11px;font-weight:700}</style>
<div class="container py-4" style="max-width:1180px">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div><h1 style="font-size:20px;font-weight:800">My Rooms</h1><p style="font-size:12px;color:#5f6368">Port of admin_owner_portal/room-list.php + elements/rooms-table.php — GET /rooms</p></div>
    <a href="<?= $this->Url->build('/host/rooms/add') ?>" class="btn" style="background:#2563EB;color:#fff;border-radius:9999px;padding:10px 18px;font-weight:700">Add Room</a>
  </div>

  <form method="get" class="d-flex gap-2 mb-3 flex-wrap">
    <input name="search" value="<?= h($search ?? '') ?>" placeholder="Search room/property/city" class="form-control" style="max-width:260px;border-radius:12px;height:44px">
    <select name="status" class="form-select" style="max-width:160px;border-radius:12px;height:44px" onchange="this.form.submit()">
      <option value="all" <?= ($status ?? 'all')==='all'?'selected':'' ?>>All</option><option value="available" <?= ($status ?? '')==='available'?'selected':'' ?>>Available</option><option value="maintenance" <?= ($status ?? '')==='maintenance'?'selected':'' ?>>Maintenance</option>
    </select>
    <button class="btn" style="background:#2563EB;color:#fff;border-radius:12px;height:44px">Search</button>
    <a href="<?= $this->Url->build('/host/rooms') ?>" class="btn" style="border:1px solid #e8eaed;border-radius:12px;height:44px">Clear</a>
  </form>

  <?php if (empty($rooms)): ?><div class="host-card p-5 text-center" style="color:#5f6368">No rooms yet. Create one for your property.</div>
  <?php else: ?>
  <div class="host-card p-0 overflow-hidden">
    <div class="table-responsive"><table class="table align-middle mb-0" style="font-size:13px"><thead style="background:#F8FAFC;font-size:11px;color:#5f6368"><tr><th>Room</th><th>Bed Type</th><th>Floor</th><th>Facilities</th><th>Rate</th><th>Status</th><th>Action</th></tr></thead><tbody>
      <?php foreach ($rooms as $r):
        $rid=$r['id'] ?? '';
        $rnum=h($r['room_number'] ?? $r['name'] ?? '—');
        $rtype=h($r['room_type'] ?? $r['type'] ?? 'Standard');
        $floor=h($r['floor'] ?? '—');
        $price=(float)($r['customer_price'] ?? $r['price'] ?? 0);
        $st=strtolower((string)($r['status'] ?? 'available'));
        $bed=h($r['bed_configuration'] ?? $r['bed_type'] ?? '—');
        $amenities = is_array($r['amenities'] ?? null) ? $r['amenities'] : (is_string($r['amenities'] ?? '') ? array_filter(array_map('trim', explode(',', $r['amenities']))) : []);
        $thumb = $r['primary_image_url'] ?? ($r['photos'][0] ?? $r['image_url'] ?? null);
        if (is_array($thumb)) $thumb = $thumb['url'] ?? null;
        if (empty($thumb)) $thumb = 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=200&h=150&fit=crop';
        // enrich property name
        $pname=''; $pid=$r['property_id'] ?? $r['property']['id'] ?? null;
        if ($pid) foreach (($properties ?? []) as $p) if ((int)$p['id']===(int)$pid) { $pname=$p['name'] ?? ''; break; }
      ?><tr>
        <td><div class="d-flex align-items-center gap-2"><img src="<?= h($thumb) ?>" style="width:44px;height:44px;border-radius:8px;object-fit:cover"><div><div style="font-weight:700"><?= $rnum ?> <span style="font-size:11px;color:#5f6368">#<?= h($rid) ?></span></div><div style="font-size:11px;color:#5f6368"><?= h($pname) ?></div></div></div></td>
        <td style="font-size:12px"><?= $bed ?></td>
        <td><?= $floor ?></td>
        <td style="font-size:11px;max-width:160px"><?= h(implode(', ', array_slice($amenities,0,3))) ?><?= count($amenities)>3?'…':'' ?></td>
        <td style="color:#C2410C;font-weight:800">TSh <?= number_format($price) ?></td>
        <td><span class="pill" style="background:<?= $st==='available'?'#dcfce7;color:#15803d':'#fee2e2;color:#dc2626' ?>"><?= h($st) ?></span></td>
        <td><a href="<?= $this->Url->build('/host/rooms/'.$rid) ?>" class="btn btn-sm" style="border:1px solid #e8eaed;border-radius:9999px;font-size:11px">Edit</a></td>
      </tr><?php endforeach; ?>
    </tbody></table></div>
  </div>
  <p style="font-size:11px;color:#9aa0a6" class="mt-2"><?= count($rooms) ?> rooms — filtered host_id via backend/Bearer.</p>
  <?php endif; ?>
</div>
<?= $this->element('footer', ['skin'=>'skin-light-footer']) ?>
