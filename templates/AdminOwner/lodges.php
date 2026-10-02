<?php
$this->assign('title', 'Lodges');
$this->assign('portal_title', 'Lodges');
$this->assign('page_actions', '<span style="font-size:13px;color:var(--p-text-2)">' . count($properties ?? []) . ' total</span>');
?>
<div class="p-card mb-3">
  <?= $this->Form->create(null, ['type' => 'get', 'style' => 'display:flex;gap:8px;flex-wrap:wrap;align-items:end']) ?>
    <div style="flex:1;min-width:200px">
      <label style="font-size:12px;font-weight:600;color:var(--p-text-2)">Search</label>
      <input name="search" value="<?= h($search ?? '') ?>" placeholder="Lodge, city" class="form-control" style="min-height:40px">
    </div>
    <div style="min-width:160px">
      <label style="font-size:12px;font-weight:600;color:var(--p-text-2)">Status</label>
      <select name="status" class="form-select" style="min-height:40px">
        <option value="">All</option>
        <option value="pending" <?= ($status ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
        <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
        <option value="rejected" <?= ($status ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
      </select>
    </div>
    <button class="p-btn" type="submit">Filter</button>
    <a href="<?= $this->Url->build('/admin/lodges') ?>" class="p-btn ghost">Clear</a>
  <?= $this->Form->end() ?>
</div>

<?php if (empty($properties)): ?>
  <div class="p-card"><div class="p-empty">No lodges found.</div></div>
<?php else: ?>
  <div class="row g-3">
    <?php foreach ($properties as $p):
      $pid = $p['id'] ?? null;
      $st = strtolower((string)($p['status'] ?? 'active'));
      $badge = $st === 'active' ? 'green' : ($st === 'pending' ? 'yellow' : ($st === 'rejected' ? 'red' : 'blue'));
      $img = $p['image_url'] ?? $p['primary_image_url'] ?? 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=600&h=400&fit=crop';
      $host = $p['host'] ?? [];
      $hostName = $host['name'] ?? '';
      $hostEmail = $host['email'] ?? '';
      $hostPhone = $host['phone_number'] ?? $host['phone'] ?? '';
    ?>
    <div class="col-md-6 col-lg-4 d-flex">
      <div class="p-card d-flex flex-column w-100" style="padding:0;overflow:hidden">
        <div style="aspect-ratio:16/9;background:var(--p-bg);overflow:hidden">
          <img src="<?= h($img) ?>" alt="<?= h($p['name'] ?? 'Lodge') ?>" style="width:100%;height:100%;object-fit:cover;display:block" loading="lazy">
        </div>
        <div style="padding:16px 16px 0">
          <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px">
            <div style="font-size:15px;font-weight:600"><?= h($p['name'] ?? '—') ?> <span style="font-size:11px;font-weight:400;color:var(--p-text-2)">#<?= h($pid) ?></span></div>
            <span class="p-badge <?= $badge ?>"><?= h($st) ?></span>
          </div>
          <div style="font-size:13px;color:var(--p-text-2);margin-top:2px"><?= h($p['city'] ?? '') ?> · TSh <?= number_format((float)($p['price_per_night'] ?? 0)) ?>/night</div>
          <?php if ($hostName !== '' || $hostEmail !== ''): ?>
          <div style="display:flex;align-items:center;gap:8px;margin-top:10px;padding-top:10px;border-top:1px solid var(--p-border)">
            <span style="width:28px;height:28px;border-radius:50%;background:var(--p-blue);color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:600;font-size:12px;flex:none"><?= h(strtoupper(substr(trim($hostName !== '' ? $hostName : $hostEmail), 0, 1))) ?></span>
            <div style="min-width:0">
              <div style="font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= h($hostName !== '' ? $hostName : $hostEmail) ?></div>
              <div style="font-size:11px;color:var(--p-text-2);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= h(trim($hostEmail . ($hostPhone !== '' ? ' · ' . $hostPhone : ''), ' ·')) ?></div>
            </div>
          </div>
          <?php endif; ?>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;padding:16px;margin-top:auto">
          <?= $this->Form->create(null, ['url' => ['controller' => 'AdminOwner', 'action' => 'verify', 'lodge', $pid], 'style' => 'display:inline', 'data-api' => 'POST /admin/verification/lodge/' . $pid, 'data-api-omit-empty' => 'reason', 'data-api-ok' => 'Verification updated.', 'data-opt' => 'patch', 'data-opt-scope' => 'closest:div.p-card', 'data-opt-badge' => '.p-badge', 'data-opt-badgesrc' => 'status', 'data-opt-badgemap' => 'lodge', 'data-opt-badgetext' => 'lower', 'data-opt-bust' => 'properties', 'data-api-go' => '/admin/cache-bust?scope=properties&go=' . urlencode('/admin/lodges')]) ?>
            <?= $this->Form->hidden('status', ['value' => 'approved']) ?><button class="p-btn" style="min-height:36px;font-size:13px">Approve</button>
          <?= $this->Form->end() ?>
          <?= $this->Form->create(null, ['url' => ['controller' => 'AdminOwner', 'action' => 'verify', 'lodge', $pid], 'style' => 'display:inline', 'data-api' => 'POST /admin/verification/lodge/' . $pid, 'data-api-omit-empty' => 'reason', 'data-api-ok' => 'Verification updated.', 'data-opt' => 'patch', 'data-opt-scope' => 'closest:div.p-card', 'data-opt-badge' => '.p-badge', 'data-opt-badgesrc' => 'status', 'data-opt-badgemap' => 'lodge', 'data-opt-badgetext' => 'lower', 'data-opt-bust' => 'properties', 'data-api-go' => '/admin/cache-bust?scope=properties&go=' . urlencode('/admin/lodges')]) ?>
            <?= $this->Form->hidden('status', ['value' => 'rejected']) ?><input type="hidden" name="reason" value="Rejected"><button class="p-btn ghost" style="min-height:36px;font-size:13px">Reject</button>
          <?= $this->Form->end() ?>
          <?= $this->Form->create(null, ['url' => ['controller' => 'AdminOwner', 'action' => 'verify', 'lodge', $pid], 'style' => 'display:inline', 'data-api' => 'POST /admin/verification/lodge/' . $pid, 'data-api-omit-empty' => 'reason', 'data-api-ok' => 'Verification updated.', 'data-opt' => 'patch', 'data-opt-scope' => 'closest:div.p-card', 'data-opt-badge' => '.p-badge', 'data-opt-badgesrc' => 'status', 'data-opt-badgemap' => 'lodge', 'data-opt-badgetext' => 'lower', 'data-opt-bust' => 'properties', 'data-api-go' => '/admin/cache-bust?scope=properties&go=' . urlencode('/admin/lodges')]) ?>
            <?= $this->Form->hidden('status', ['value' => 'changes_requested']) ?><input type="hidden" name="reason" value="Please update details"><button class="p-btn ghost" style="min-height:36px;font-size:13px">Request changes</button>
          <?= $this->Form->end() ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
