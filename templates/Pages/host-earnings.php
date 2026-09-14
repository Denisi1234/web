<?php $this->assign('title','Earnings | FastNetStays'); ?>
<?= $this->element('navbar') ?>
<style>.earn-card{border:1px solid #e8eaed;border-radius:16px;box-shadow:0 6px 16px rgba(0,0,0,.05);background:#fff;padding:20px}.earn-gradient{background:linear-gradient(135deg,#ec4899 0%,#dc2626 100%);border-radius:20px;color:#fff}</style>
<div class="container py-4" style="max-width:980px">
  <h1 style="font-size:20px;font-weight:800;color:#1a1d25">Financial Reports</h1><p style="font-size:12px;color:#5f6368">Portal chart-chartist.php:262 / chart-flot.php:251 + mobile financial_reports.dart — GET /finance/overview or /admin/dashboard-stats fallback.</p>
  <?php $metrics=$finance['metrics']??$finance; $out=$metrics['outstanding']??$finance['outstanding_balance']??($finance['total_earnings']??0); $earned=$metrics['total_earnings']??$finance['total_earnings']??0; $gross=$metrics['gross_revenue']??$earned; ?>
  <div class="earn-gradient p-4 mb-3">
    <div style="font-size:11px;letter-spacing:.08em;font-weight:700;color:rgba(255,255,255,.75)">OUTSTANDING BALANCE</div>
    <div style="font-size:32px;font-weight:800" class="mt-1">TSh <?= number_format((float)$out) ?></div>
    <div class="d-flex gap-2 mt-3 flex-wrap"><span style="background:rgba(255,255,255,.15);border-radius:9999px;padding:6px 10px;font-size:11px">Earned: TSh <?= number_format((float)$earned) ?></span><span style="background:rgba(255,255,255,.2);border-radius:9999px;padding:6px 10px;font-size:11px">Gross: TSh <?= number_format((float)$gross) ?></span></div>
  </div>
  <div class="row g-3 mb-3">
    <div class="col-md-4"><div class="earn-card text-center"><div style="font-size:12px;color:#5f6368;font-weight:700">Payouts</div><div style="font-size:18px;font-weight:800"><?= count($payouts??[]) ?></div><div style="font-size:11px;color:#5f6368">records</div></div></div>
    <div class="col-md-4"><div class="earn-card text-center"><div style="font-size:12px;color:#5f6368;font-weight:700">Platform Fee</div><div style="font-size:18px;font-weight:800">10%</div><div style="font-size:11px;color:#5f6368">host 90% net</div></div></div>
    <div class="col-md-4"><div class="earn-card text-center"><div style="font-size:12px;color:#5f6368;font-weight:700">Currency</div><div style="font-size:18px;font-weight:800">TZS</div><div style="font-size:11px;color:#5f6368">M-Pesa/Tigo payout</div></div></div>
  </div>
  <?php if(!empty($payouts)): ?><div class="earn-card"><b style="font-size:14px">Recent Payouts</b><div class="mt-2 d-grid gap-2"><?php foreach(array_slice($payouts,0,5) as $p): ?><div style="border:1px solid #e8eaed;border-radius:12px;padding:12px;display:flex;justify-content:space-between;align-items:center"><span style="font-weight:700"><?= h($p['id']??$p['payout_id']??'—') ?> · <span style="font-size:11px;color:#5f6368"><?= h($p['method']??$p['gateway']??'') ?></span></span><span style="color:#C2410C;font-weight:800">TSh <?= number_format((float)($p['amount']??0)) ?></span><span style="font-size:11px;font-weight:700;color:<?= ($p['status']??'')==='completed'?'#15803d':'#d97706' ?>"><?= h($p['status']??'pending') ?></span></div><?php endforeach; ?></div></div><?php endif; ?>
  <details class="earn-card mt-3"><summary style="font-weight:700;cursor:pointer">Raw finance JSON</summary><pre style="background:#F8FAFC;border:1px solid #e8eaed;border-radius:12px;padding:12px;font-size:11px;overflow:auto;margin-top:10px"><?= h(json_encode($finance??[],JSON_PRETTY_PRINT)) ?></pre></details>
  <div class="text-center mt-3"><a href="<?= $this->Url->build('/host/dashboard') ?>" class="btn" style="background:#2563EB;color:#fff;border-radius:30px;padding:10px 18px">Back</a></div>
</div>
<?= $this->element('footer',['skin'=>'skin-light-footer']) ?>
