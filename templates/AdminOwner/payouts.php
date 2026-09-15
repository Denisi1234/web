<?php $this->assign('title', 'Payouts | FastNetStays'); ?>
<?= $this->element('navbar') ?>
<style>.host-card{border:1px solid #e8eaed;border-radius:16px;box-shadow:0 6px 16px rgba(0,0,0,.05);background:#fff}</style>
<div class="container py-4" style="max-width:1180px">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div><h1 style="font-size:20px;font-weight:800">Payout Control</h1><p style="font-size:12px;color:#5f6368">Port of chart-flot.php — GET /payouts?status + PATCH /payouts/{id}/status</p></div>
    <form method="get" class="d-flex gap-2 flex-wrap">
      <select name="status" class="form-select" style="border-radius:12px;height:44px;min-width:160px" onchange="this.form.submit()"><option value="">All</option><option value="REQUESTED" <?= ($status ?? '')==='REQUESTED'?'selected':'' ?>>Requested</option><option value="PROCESSING" <?= ($status ?? '')==='PROCESSING'?'selected':'' ?>>Processing</option><option value="PAID" <?= ($status ?? '')==='PAID'?'selected':'' ?>>Paid</option><option value="FAILED" <?= ($status ?? '')==='FAILED'?'selected':'' ?>>Failed</option></select>
    </form>
  </div>

  <?php $m=$finance['metrics']??$finance; $gross=(float)($m['gross_revenue']??$m['gross_booking_value']??0); $net=(float)($m['net_earnings']??$m['total_owner_earnings']??($gross*0.90)); $pending=(float)($m['pending_payouts']??0); $paid=(float)($m['completed_payouts']??0); ?>
  <div class="row g-3 mb-3">
    <div class="col-6 col-lg-3"><div class="host-card p-3 text-center"><div style="font-size:11px;color:#5f6368;font-weight:700">GROSS</div><div style="font-size:16px;font-weight:800">TSh <?= number_format($gross) ?></div></div></div>
    <div class="col-6 col-lg-3"><div class="host-card p-3 text-center"><div style="font-size:11px;color:#5f6368;font-weight:700">NET 90%</div><div style="font-size:16px;font-weight:800;color:#15803d">TSh <?= number_format($net) ?></div></div></div>
    <div class="col-6 col-lg-3"><div class="host-card p-3 text-center"><div style="font-size:11px;color:#5f6368;font-weight:700">PENDING</div><div style="font-size:16px;font-weight:800;color:#d97706">TSh <?= number_format($pending) ?></div></div></div>
    <div class="col-6 col-lg-3"><div class="host-card p-3 text-center"><div style="font-size:11px;color:#5f6368;font-weight:700">PAID</div><div style="font-size:16px;font-weight:800;color:#2563EB"><?= count($payouts ?? []) ?> records · TSh <?= number_format($paid) ?></div></div></div>
  </div>

  <?php if (empty($payouts)): ?><div class="host-card p-5 text-center" style="color:#5f6368">No payouts.</div>
  <?php else: ?>
  <div class="host-card p-0 overflow-hidden">
    <div class="table-responsive"><table class="table align-middle mb-0" style="font-size:13px"><thead style="background:#F8FAFC;font-size:11px;color:#5f6368"><tr><th>Ref</th><th>Amount</th><th>Method</th><th>Status</th><th style="width:260px">Process</th></tr></thead><tbody>
      <?php foreach ($payouts as $p):
        $pid = $p['id'] ?? $p['payout_id'] ?? '';
        $st = strtoupper((string)($p['status'] ?? ''));
      ?><tr>
        <td style="font-weight:700"><?= h($p['payout_reference'] ?? $pid) ?></td>
        <td style="color:#C2410C;font-weight:800">TSh <?= number_format((float)($p['amount'] ?? 0)) ?></td>
        <td style="font-size:12px"><?= h($p['payment_method'] ?? $p['method'] ?? $p['gateway'] ?? '') ?></td>
        <td><span style="background:<?= $st==='PAID'?'#dcfce7;color:#15803d':($st==='FAILED'?'#fee2e2;color:#dc2626':'#fef3c7;color:#d97706') ?>;border-radius:9999px;padding:4px 8px;font-size:11px;font-weight:700"><?= h($st) ?></span></td>
        <td>
          <div class="d-flex gap-1 flex-wrap">
            <?= $this->Form->create(null, ['url'=>['controller'=>'AdminOwner','action'=>'payouts']]) ?>
              <?= $this->Form->hidden('payout_id',['value'=>$pid]) ?><input type="hidden" name="status" value="PROCESSING"><button class="btn btn-sm" style="background:#2563EB;color:#fff;border-radius:9999px;font-size:11px" <?= $st==='PAID'?'disabled':'' ?>>Processing</button>
            <?= $this->Form->end() ?>
            <?= $this->Form->create(null, ['url'=>['controller'=>'AdminOwner','action'=>'payouts']]) ?>
              <?= $this->Form->hidden('payout_id',['value'=>$pid]) ?><input type="hidden" name="status" value="PAID"><button class="btn btn-sm" style="background:#15803d;color:#fff;border-radius:9999px;font-size:11px">Mark Paid</button>
            <?= $this->Form->end() ?>
            <?= $this->Form->create(null, ['url'=>['controller'=>'AdminOwner','action'=>'payouts']]) ?>
              <?= $this->Form->hidden('payout_id',['value'=>$pid]) ?><input type="hidden" name="status" value="FAILED"><button class="btn btn-sm" style="background:#fff;border:1px solid #fecaca;color:#dc2626;border-radius:9999px;font-size:11px">Fail</button>
            <?= $this->Form->end() ?>
          </div>
        </td>
      </tr><?php endforeach; ?>
    </tbody></table></div>
  </div>
  <?php endif; ?>

  <details class="host-card p-3 mt-3"><summary style="font-weight:700;cursor:pointer">Raw finance JSON</summary><pre style="background:#F8FAFC;border:1px solid #e8eaed;border-radius:12px;padding:12px;font-size:11px;overflow:auto;margin-top:10px"><?= h(json_encode($finance, JSON_PRETTY_PRINT)) ?></pre></details>
</div>
<?= $this->element('footer', ['skin'=>'skin-light-footer']) ?>
