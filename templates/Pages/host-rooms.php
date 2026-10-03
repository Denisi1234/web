<?php
$this->assign('title', 'My Rooms');
$this->assign('portal_title', 'Rooms');
$this->assign('page_actions', '<a href="' . $this->Url->build('/host/rooms/add') . '" class="p-btn">Add room</a>');
?>
<?php if (!empty($properties)): ?>
<div class="p-card mb-3">
  <?= $this->Form->create(null, ['type' => 'get', 'url' => '/host/rooms/add', 'style' => 'display:flex;gap:8px;flex-wrap:wrap;align-items:end']) ?>
    <div style="flex:1;min-width:200px">
      <label for="add-room-prop" style="font-size:12px;font-weight:600;color:var(--p-text-2)">Add room — pick a property first *</label>
      <select id="add-room-prop" name="property_id" class="form-select" style="min-height:40px" required>
        <?php foreach (($properties ?? []) as $p): ?>
          <option value="<?= h($p['id']) ?>"><?= h($p['name']) ?> — <?= h($p['city'] ?? '') ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button class="p-btn" type="submit" style="min-height:40px">Add room →</button>
  <?= $this->Form->end() ?>
</div>
<?php else: ?>
<div class="p-card mb-3"><div class="p-empty">No property yet — <a href="<?= $this->Url->build('/host/onboarding') ?>">create a property first</a>, then add rooms to it.</div></div>
<?php endif; ?>
<div class="p-card mb-3">
  <?= $this->Form->create(null, ['type' => 'get', 'style' => 'display:flex;gap:8px;flex-wrap:wrap;align-items:end']) ?>
    <div style="flex:1;min-width:200px">
      <label for="room-search" style="font-size:12px;font-weight:600;color:var(--p-text-2)">Search</label>
      <input id="room-search" name="search" value="<?= h($search ?? '') ?>" placeholder="Room, property, city" class="form-control" style="min-height:40px">
    </div>
    <div style="min-width:160px">
      <label for="room-status" style="font-size:12px;font-weight:600;color:var(--p-text-2)">Status</label>
      <select id="room-status" name="status" class="form-select" style="min-height:40px" onchange="this.form.submit()">
        <option value="all" <?= ($status ?? 'all') === 'all' ? 'selected' : '' ?>>All</option>
        <option value="available" <?= ($status ?? '') === 'available' ? 'selected' : '' ?>>Available</option>
        <option value="maintenance" <?= ($status ?? '') === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
      </select>
    </div>
    <button class="p-btn" type="submit">Search</button>
    <a href="<?= $this->Url->build('/host/rooms') ?>" class="p-btn ghost">Clear</a>
  <?= $this->Form->end() ?>
</div>

<?php if (empty($rooms)): ?>
  <div class="p-card"><div class="p-empty">No rooms yet. Create one for your property.</div></div>
<?php else:
  // Group by category so Deluxe 45, 78 sit together
  $sorted = $rooms;
  usort($sorted, fn($a, $b) => [strtolower((string)($a['room_type'] ?? $a['type'] ?? 'Standard')), strtolower((string)($a['room_number'] ?? ''))] <=> [strtolower((string)($b['room_type'] ?? $b['type'] ?? 'Standard')), strtolower((string)($b['room_number'] ?? ''))]);
  $typeCounts = [];
  foreach ($sorted as $r) { $k = (string)($r['room_type'] ?? $r['type'] ?? 'Standard'); $typeCounts[$k] = ($typeCounts[$k] ?? 0) + 1; }
  $lastType = null;
?>
  <div class="p-table-wrap">
    <div class="table-responsive"><table class="p-table">
      <thead><tr><th>Room</th><th>Bed type</th><th>Floor</th><th>Facilities</th><th style="text-align:right">Rate</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($sorted as $r):
          $rtypeRaw = (string)($r['room_type'] ?? $r['type'] ?? 'Standard');
          if ($rtypeRaw !== $lastType):
            $lastType = $rtypeRaw;
        ?><tr style="background:var(--p-bg)"><td colspan="7" style="font-size:12px;font-weight:700;color:var(--p-text-2)"><span class="p-badge blue"><?= h($rtypeRaw) ?></span> &nbsp;<?= (int)($typeCounts[$rtypeRaw] ?? 0) ?> room<?= ((int)($typeCounts[$rtypeRaw] ?? 0) === 1 ? '' : 's') ?></td></tr>
        <?php endif;
          $rid = $r['id'] ?? '';
          $rnum = h($r['room_number'] ?? $r['name'] ?? '—');
          $rtype = h($r['room_type'] ?? $r['type'] ?? 'Standard');
          $floor = h($r['floor'] ?? '—');
          $price = (float)($r['customer_price'] ?? $r['price'] ?? 0);
          $st = strtolower((string)($r['status'] ?? 'available'));
          $bed = h($r['bed_configuration'] ?? $r['bed_type'] ?? '—');
          $amenities = is_array($r['amenities'] ?? null) ? $r['amenities'] : (is_string($r['amenities'] ?? '') ? array_filter(array_map('trim', explode(',', $r['amenities']))) : []);
          $thumb = $r['primary_image_url'] ?? ($r['photos'][0] ?? $r['image_url'] ?? null);
          if (is_array($thumb)) $thumb = $thumb['url'] ?? null;
          if (empty($thumb)) $thumb = 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=200&h=150&fit=crop';
          $pname = '';
          $pid = $r['property_id'] ?? $r['property']['id'] ?? null;
          if ($pid) foreach (($properties ?? []) as $p) if ((int)$p['id'] === (int)$pid) { $pname = $p['name'] ?? ''; break; }
        ?>
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:10px">
              <img src="<?= h($thumb) ?>" alt="" style="width:44px;height:44px;object-fit:cover;flex-shrink:0" loading="lazy">
              <div><div style="font-weight:600"><?= $rnum ?> <span style="font-size:11px;color:var(--p-text-2)">#<?= h($rid) ?></span></div><div style="font-size:12px;color:var(--p-text-2)"><?= h($pname) ?> · <?= $rtype ?></div></div>
            </div>
          </td>
          <td style="font-size:13px"><?= $bed ?></td>
          <td><?= $floor ?></td>
          <td style="font-size:12px;max-width:180px"><?= h(implode(', ', array_slice($amenities, 0, 3))) ?><?= count($amenities) > 3 ? '…' : '' ?></td>
          <td style="text-align:right;font-weight:600;white-space:nowrap">TSh <?= number_format($price) ?></td>
          <td><span class="p-badge <?= $st === 'available' ? 'green' : 'red' ?>"><?= h($st) ?></span></td>
          <td><a href="<?= $this->Url->build('/host/rooms/' . $rid) ?>" class="p-btn ghost" style="min-height:36px;font-size:13px">Edit</a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table></div>
  </div>
  <div style="font-size:12px;color:var(--p-text-2);margin-top:8px"><?= count($rooms) ?> rooms</div>
<?php endif; ?>
