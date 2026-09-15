<?php $this->assign('title', 'Finance Intelligence | FastNetStays'); ?>
<?= $this->element('navbar') ?>
<style>.host-card{border:1px solid #e8eaed;border-radius:16px;box-shadow:0 6px 16px rgba(0,0,0,.05);background:#fff}</style>
<div class="container py-4" style="max-width:1180px">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div><h1 style="font-size:20px;font-weight:800">Financial Intelligence</h1><p style="font-size:12px;color:#5f6368">Port of chart-chartist.php — GET /finance/overview?date_range</p></div>
    <form method="get" class="d-flex gap-2 flex-wrap">
      <select name="date_range" class="form-select" style="border-radius:12px;height:44px;min-width:160px" onchange="this.form.submit()">
        <?php $opts=['today'=>'Today','7_days'=>'7 days','30_days'=>'30 days','this_month'=>'This month','last_month'=>'Last month','custom'=>'Custom']; foreach($opts as $k=>$v): ?><option value="<?= $k ?>" <?= ($dateRange ?? '30_days')===$k?'selected':'' ?>><?= $v ?></option><?php endforeach; ?>
      </select>
    </form>
  </div>

  <?php $m=$finance['metrics']??$finance; $gross=(float)($m['gross_revenue']??$m['gross_booking_value']??$m['total_earnings']??0); $fee=(float)($m['platform_fee']??$m['platform_commission']??($gross*0.10)); $net=(float)($m['net_earnings']??$m['total_owner_earnings']??($gross*0.90)); $pending=(float)($m['pending_payouts']??$m['outstanding_balance']??0); ?>
  <div class="row g-3 mb-3">
    <div class="col-6 col-lg-3"><div class="host-card p-3 text-center"><div style="font-size:11px;color:#5f6368;font-weight:700">GROSS</div><div style="font-size:18px;font-weight:800;color:#1a1d25">TSh <?= number_format($gross) ?></div></div></div>
    <div class="col-6 col-lg-3"><div class="host-card p-3 text-center"><div style="font-size:11px;color:#5f6368;font-weight:700">PLATFORM 10%</div><div style="font-size:18px;font-weight:800;color:#2563EB">TSh <?= number_format($fee) ?></div></div></div>
    <div class="col-6 col-lg-3"><div class="host-card p-3 text-center"><div style="font-size:11px;color:#5f6368;font-weight:700">OWNER 90%</div><div style="font-size:18px;font-weight:800;color:#15803d">TSh <?= number_format($net) ?></div></div></div>
    <div class="col-6 col-lg-3"><div class="host-card p-3 text-center"><div style="font-size:11px;color:#5f6368;font-weight:700">PENDING</div><div style="font-size:18px;font-weight:800;color:#d97706">TSh <?= number_format($pending) ?></div></div></div>
  </div>

  <div class="host-card p-3 mb-3">
    <b style="font-size:14px">Chart — Revenue vs Payouts</b>
    <canvas id="financeChart" height="120"></canvas>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
      const series = <?= json_encode($finance['chart_series'] ?? $finance['series'] ?? []) ?>;
      const labels = series.map(s=> s.period ?? s.date ?? '');
      const rev = series.map(s=> Number(s.revenue ?? s.gross ?? 0));
      const own = series.map(s=> Number(s.owner_earnings ?? s.net ?? 0));
      if(labels.length){
        new Chart(document.getElementById('financeChart'), {type:'line', data:{labels, datasets:[{label:'Gross',data:rev,borderColor:'#2563EB',tension:.3},{label:'Owner 90%',data:own,borderColor:'#15803d',tension:.3}]}, options:{responsive:true, plugins:{legend:{position:'bottom'}}}});
      } else {
        document.getElementById('financeChart').outerHTML = '<div class="text-center py-4" style="color:#5f6368;font-size:13px">No chart data — finance overview empty.</div>';
      }
    </script>
  </div>

  <details class="host-card p-3"><summary style="font-weight:700;cursor:pointer">Raw JSON</summary><pre style="background:#F8FAFC;border:1px solid #e8eaed;border-radius:12px;padding:12px;font-size:11px;overflow:auto;margin-top:10px"><?= h(json_encode($finance, JSON_PRETTY_PRINT)) ?></pre></details>
</div>
<?= $this->element('footer', ['skin'=>'skin-light-footer']) ?>
