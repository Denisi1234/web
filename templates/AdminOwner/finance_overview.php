<?php
$this->assign('title', 'Finance');
$this->assign('portal_title', 'Finance overview');
$m = $finance['metrics'] ?? $finance;
// Both backends nest the cards one level down: finance/overview uses
// summary_cards.*, admin/dashboard-stats uses financials.*
$cards = $m['summary_cards'] ?? $m['financials'] ?? $m;
$gross = (float)($cards['gross_revenue'] ?? $cards['gross_booking_value'] ?? $cards['total_earnings'] ?? 0);
$fee = (float)($cards['platform_fee'] ?? $cards['platform_commission'] ?? 0);
$net = (float)($cards['net_earnings'] ?? $cards['total_owner_earnings'] ?? $cards['owner_earnings'] ?? 0);
$pending = (float)($cards['pending_payouts'] ?? $cards['outstanding_balance'] ?? 0);
?>
<div style="display:flex;justify-content:flex-end;margin-bottom:12px">
  <form method="get">
    <select name="date_range" class="form-select form-select-sm" style="min-height:36px;min-width:160px" onchange="this.form.submit()" aria-label="Date range">
      <?php $opts = ['today' => 'Today', '7_days' => '7 days', '30_days' => '30 days', 'this_month' => 'This month', 'last_month' => 'Last month', 'custom' => 'Custom']; foreach ($opts as $k => $v): ?><option value="<?= $k ?>" <?= ($dateRange ?? '30_days') === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?>
    </select>
  </form>
</div>

<div class="row g-3 mb-3">
  <div class="col-6 col-lg-3"><div class="p-stat"><div class="v">TSh <?= number_format($gross) ?></div><div class="l">Gross revenue</div><div class="s">All bookings</div></div></div>
  <div class="col-6 col-lg-3"><div class="p-stat"><div class="v">TSh <?= number_format($fee) ?></div><div class="l">Platform commission</div><div class="s">Commission</div></div></div>
  <div class="col-6 col-lg-3"><div class="p-stat"><div class="v">TSh <?= number_format($net) ?></div><div class="l">Owner 90%</div><div class="s">Net earnings</div></div></div>
  <div class="col-6 col-lg-3"><div class="p-stat"><div class="v">TSh <?= number_format($pending) ?></div><div class="l">Pending</div><div class="s">Outstanding</div></div></div>
</div>

<div class="p-card mb-3">
  <h3>Revenue vs payouts</h3>
  <div class="sub">Gross against owner share</div>
  <div class="mt-3"><canvas id="financeChart" height="120"></canvas></div>
  <script src="https://cdn.jsdelivr.net/npm/chart.js" defer></script>
  <script>
    /* Runs immediately (not on DOMContentLoaded) so the chart also renders
       after instant portal navigation, where DOMContentLoaded never refires. */
    (function () {
      var series = <?= json_encode($finance['chart_series'] ?? $finance['series'] ?? []) ?>;
      var labels = series.map(function (s) { return s.period ?? s.date ?? ''; });
      var rev = series.map(function (s) { return Number(s.revenue ?? s.gross ?? 0); });
      var own = series.map(function (s) { return Number(s.owner_earnings ?? s.net ?? 0); });
      var tries = 0;
      function draw() {
        var el = document.getElementById('financeChart');
        if (!el) return;
        if (!labels.length) {
          el.outerHTML = '<div class="p-empty">No chart data — finance overview empty.</div>';
          return;
        }
        if (!window.Chart) {
          // chart.js CDN is deferred — retry briefly, then give up silently
          if (++tries < 20) setTimeout(draw, 250);
          return;
        }
        new Chart(el, { type: 'line', data: { labels: labels, datasets: [{ label: 'Gross', data: rev, borderColor: '#0f62fe', tension: .3 }, { label: 'Owner 90%', data: own, borderColor: '#0e6027', tension: .3 }] }, options: { responsive: true, plugins: { legend: { position: 'bottom' } } } });
      }
      draw();
    })();
  </script>
</div>
