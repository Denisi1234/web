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
            <?= $this->Form->hidden('status', ['value' => 'Active']) ?><button class="p-btn" style="min-height:36px;font-size:13px">Approve</button>
          <?= $this->Form->end() ?>
          <?= $this->Form->create(null, ['url' => ['controller' => 'AdminOwner', 'action' => 'verify', 'lodge', $pid], 'style' => 'display:inline', 'data-api' => 'POST /admin/verification/lodge/' . $pid, 'data-api-omit-empty' => 'reason', 'data-api-ok' => 'Verification updated.', 'data-opt' => 'patch', 'data-opt-scope' => 'closest:div.p-card', 'data-opt-badge' => '.p-badge', 'data-opt-badgesrc' => 'status', 'data-opt-badgemap' => 'lodge', 'data-opt-badgetext' => 'lower', 'data-opt-bust' => 'properties', 'data-api-go' => '/admin/cache-bust?scope=properties&go=' . urlencode('/admin/lodges')]) ?>
            <?= $this->Form->hidden('status', ['value' => 'rejected']) ?><input type="hidden" name="reason" value=""><button class="p-btn ghost" style="min-height:36px;font-size:13px" data-reason-prompt="Why is this lodge being rejected? The host will see this.">Reject</button>
          <?= $this->Form->end() ?>
          <?= $this->Form->create(null, ['url' => ['controller' => 'AdminOwner', 'action' => 'verify', 'lodge', $pid], 'style' => 'display:inline', 'data-api' => 'POST /admin/verification/lodge/' . $pid, 'data-api-omit-empty' => 'reason', 'data-api-ok' => 'Verification updated.', 'data-opt' => 'patch', 'data-opt-scope' => 'closest:div.p-card', 'data-opt-badge' => '.p-badge', 'data-opt-badgesrc' => 'status', 'data-opt-badgemap' => 'lodge', 'data-opt-badgetext' => 'lower', 'data-opt-bust' => 'properties', 'data-api-go' => '/admin/cache-bust?scope=properties&go=' . urlencode('/admin/lodges')]) ?>
            <?= $this->Form->hidden('status', ['value' => 'changes_requested']) ?><input type="hidden" name="reason" value=""><button class="p-btn ghost" style="min-height:36px;font-size:13px" data-reason-prompt="What must the host change? The host will see this.">Request changes</button>
          <?= $this->Form->end() ?>
        </div>
        <!-- Inline reason box. Rejecting or requesting changes without saying
             why leaves a useless audit trail, so the reason is collected here
             on the card — typed, visible, cancellable — instead of a native
             prompt(). -->
        <div class="p-reason" hidden style="padding:0 16px 16px">
          <label style="font-size:12px;font-weight:600;color:var(--p-text-2)" class="p-reason-label">Reason for the host</label>
          <textarea class="form-control p-reason-text" rows="2" maxlength="500" placeholder="Tell the host exactly what is wrong…" style="min-height:40px;font-size:13px"></textarea>
          <div class="p-reason-err" style="font-size:12px;color:#b91c1c;margin-top:4px" hidden>A reason is required — the host needs to know what to fix.</div>
          <div style="display:flex;gap:8px;margin-top:8px">
            <button type="button" class="p-btn p-reason-go" style="min-height:36px;font-size:13px">Confirm</button>
            <button type="button" class="p-btn ghost p-reason-cancel" style="min-height:36px;font-size:13px">Cancel</button>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<script>
// Review reasons: Reject / Request-changes must carry a typed reason for the
// audit trail. The button click (capture, before the data-api submit handler)
// opens the inline reason box on that card instead of submitting; Confirm fills
// the hidden reason and submits through requestSubmit() so the normal
// data-api flow — optimistic badge, toast, cache-bust — still runs.
(function () {
  var pendingForm = null;
  var pendingBox = null;

  function closeBox() {
    if (pendingBox) pendingBox.hidden = true;
    pendingForm = null;
    pendingBox = null;
  }

  document.addEventListener('click', function (e) {
    var btn = e.target && e.target.closest ? e.target.closest('[data-reason-prompt]') : null;

    // Confirm inside an open reason box.
    if (e.target && e.target.closest && e.target.closest('.p-reason-go')) {
      var box = e.target.closest('.p-reason');
      var text = box ? box.querySelector('.p-reason-text') : null;
      var err = box ? box.querySelector('.p-reason-err') : null;
      var value = text ? text.value.trim() : '';
      if (value === '') {
        if (err) err.hidden = false;
        if (text) text.focus();
        return;
      }
      if (pendingForm) {
        var reason = pendingForm.querySelector('input[name="reason"]');
        if (reason) reason.value = value;
        var f = pendingForm;
        closeBox();
        f.requestSubmit();
      }
      return;
    }

    // Cancel inside an open reason box.
    if (e.target && e.target.closest && e.target.closest('.p-reason-cancel')) {
      closeBox();
      return;
    }

    if (!btn) return;
    var form = btn.closest('form');
    if (!form) return;

    // Hold the submission and ask for the reason first.
    e.preventDefault();
    e.stopPropagation();
    closeBox();
    var card = btn.closest('div.p-card');
    var panel = card ? card.querySelector('.p-reason') : null;
    if (!panel) {
      form.requestSubmit();
      return;
    }
    var label = panel.querySelector('.p-reason-label');
    if (label) label.textContent = btn.getAttribute('data-reason-prompt') || 'Reason for the host';
    var area = panel.querySelector('.p-reason-text');
    var go = panel.querySelector('.p-reason-go');
    if (area) {
      area.value = '';
      var errBox = panel.querySelector('.p-reason-err');
      if (errBox) errBox.hidden = true;
    }
    if (go) go.textContent = btn.textContent.trim() || 'Confirm';
    pendingForm = form;
    pendingBox = panel;
    panel.hidden = false;
    if (area) area.focus();
    panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }, true);
})();
</script>
