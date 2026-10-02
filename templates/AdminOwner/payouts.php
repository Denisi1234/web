<?php
$this->assign('title', 'Payouts');
$this->assign('portal_title', 'Payouts');
$m = $finance['metrics'] ?? $finance;
$gross = (float)($m['gross_revenue'] ?? $m['gross_booking_value'] ?? 0);
$net = (float)($m['net_earnings'] ?? $m['total_owner_earnings'] ?? ($gross * 0.90));
$pending = (float)($m['pending_payouts'] ?? 0);
$paid = (float)($m['completed_payouts'] ?? 0);
$this->assign('page_actions', '<span style="font-size:13px;color:var(--p-text-2)">' . count($payouts ?? []) . ' records</span>');
?>
<div class="row g-3 mb-3">
  <div class="col-6 col-lg-3"><div class="p-stat"><div class="v">TSh <?= number_format($gross) ?></div><div class="l">Gross</div><div class="s">Booking value</div></div></div>
  <div class="col-6 col-lg-3"><div class="p-stat"><div class="v">TSh <?= number_format($net) ?></div><div class="l">Net 90%</div><div class="s">Owner share</div></div></div>
  <div class="col-6 col-lg-3"><div class="p-stat"><div class="v">TSh <?= number_format($pending) ?></div><div class="l">Pending</div><div class="s">Awaiting payout</div></div></div>
  <div class="col-6 col-lg-3"><div class="p-stat"><div class="v">TSh <?= number_format($paid) ?></div><div class="l">Paid</div><div class="s"><?= count($payouts ?? []) ?> records</div></div></div>
</div>

<div class="p-card mb-3">
  <?= $this->Form->create(null, ['type' => 'get', 'style' => 'display:flex;gap:8px;align-items:end']) ?>
    <div style="min-width:180px">
      <label style="font-size:12px;font-weight:600;color:var(--p-text-2)">Status</label>
      <select name="status" class="form-select" style="min-height:40px" onchange="this.form.submit()">
        <?php foreach (['' => 'All', 'REQUESTED' => 'Requested', 'PROCESSING' => 'Processing', 'PAID' => 'Paid', 'FAILED' => 'Failed'] as $k => $v): ?>
          <option value="<?= $k ?>" <?= ($status ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  <?= $this->Form->end() ?>
</div>

<?php if (empty($payouts)): ?>
  <div class="p-card"><div class="p-empty">No payouts.</div></div>
<?php else: ?>
  <div class="p-table-wrap">
    <div class="table-responsive"><table class="p-table">
      <thead><tr><th>Ref</th><th style="text-align:right">Amount</th><th>Method</th><th>Status</th><th>Process</th></tr></thead>
      <tbody>
        <?php foreach ($payouts as $p):
          $pid = $p['id'] ?? $p['payout_id'] ?? '';
          $st = strtoupper((string)($p['status'] ?? ''));
          $badge = $st === 'PAID' ? 'green' : ($st === 'FAILED' ? 'red' : 'yellow');
        ?>
        <tr>
          <td><strong><?= h($p['payout_reference'] ?? $pid) ?></strong></td>
          <td style="text-align:right;font-weight:600">TSh <?= number_format((float)($p['amount'] ?? 0)) ?></td>
          <td style="font-size:13px"><?= h($p['payment_method'] ?? $p['method'] ?? $p['gateway'] ?? '') ?></td>
          <td><span class="p-badge <?= $badge ?>"><?= h($st) ?></span></td>
          <td>
            <div style="display:flex;gap:6px;flex-wrap:wrap">
              <?= $this->Form->create(null, ['url' => ['controller' => 'AdminOwner', 'action' => 'payouts'], 'style' => 'display:inline', 'data-api' => 'PATCH /payouts/' . $pid . '/status', 'data-api-strip' => 'payout_id', 'data-api-ok' => 'Payout updated.', 'data-opt' => 'patch', 'data-opt-badge' => '.p-badge', 'data-opt-badgesrc' => 'status', 'data-opt-badgemap' => 'payout', 'data-opt-badgetext' => 'raw', 'data-opt-bust' => '_payouts', 'data-api-go' => '/admin/cache-bust?scope=_payouts&go=' . urlencode('/admin/finance/payouts')]) ?>
                <?= $this->Form->hidden('payout_id', ['value' => $pid]) ?><input type="hidden" name="status" value="PROCESSING"><button class="p-btn ghost" style="min-height:32px;font-size:12px" <?= $st === 'PAID' ? 'disabled' : '' ?>>Processing</button>
              <?= $this->Form->end() ?>
              <?= $this->Form->create(null, ['url' => ['controller' => 'AdminOwner', 'action' => 'payouts'], 'style' => 'display:inline', 'data-api' => 'PATCH /payouts/' . $pid . '/status', 'data-api-strip' => 'payout_id', 'data-api-ok' => 'Payout updated.', 'data-opt' => 'patch', 'data-opt-badge' => '.p-badge', 'data-opt-badgesrc' => 'status', 'data-opt-badgemap' => 'payout', 'data-opt-badgetext' => 'raw', 'data-opt-bust' => '_payouts', 'data-api-go' => '/admin/cache-bust?scope=_payouts&go=' . urlencode('/admin/finance/payouts')]) ?>
                <?= $this->Form->hidden('payout_id', ['value' => $pid]) ?><input type="hidden" name="status" value="PAID"><button class="p-btn" style="min-height:32px;font-size:12px">Mark paid</button>
              <?= $this->Form->end() ?>
              <?= $this->Form->create(null, ['url' => ['controller' => 'AdminOwner', 'action' => 'payouts'], 'style' => 'display:inline', 'data-api' => 'PATCH /payouts/' . $pid . '/status', 'data-api-strip' => 'payout_id', 'data-api-ok' => 'Payout updated.', 'data-opt' => 'patch', 'data-opt-badge' => '.p-badge', 'data-opt-badgesrc' => 'status', 'data-opt-badgemap' => 'payout', 'data-opt-badgetext' => 'raw', 'data-opt-bust' => '_payouts', 'data-api-go' => '/admin/cache-bust?scope=_payouts&go=' . urlencode('/admin/finance/payouts')]) ?>
                <?= $this->Form->hidden('payout_id', ['value' => $pid]) ?><input type="hidden" name="status" value="FAILED"><button class="p-btn ghost" style="min-height:32px;font-size:12px">Fail</button>
              <?= $this->Form->end() ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table></div>
  </div>
<?php endif; ?>
