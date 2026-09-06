<?php
$count = $totalCount ?? count($properties ?? []);
$destName = !empty($queryParams['destination']) ? $queryParams['destination'] : (!empty($queryParams['q']) ? $queryParams['q'] : 'Tanzania');

$session = $this->getRequest()->getSession();
$authUser = $session->read('Auth.User') ?? $session->read('user');
$userName = !empty($authUser['name']) ? $authUser['name'] : (!empty($authUser['username']) ? $authUser['username'] : null);
?>

<!-- Desktop Results Subheader (Original Desktop View Preserved) -->
<div class="d-none d-lg-flex align-items-center justify-content-between mb-3 text-sm flex-wrap gap-2">
    <div style="font-size: 14px; color: #1e293b;">
        We found <strong class="fw-bold text-slate-900"><?= $count ?></strong> available <?= $count === 1 ? 'stay' : 'stays' ?> in <strong class="fw-bold text-primary"><?= h(ucwords($destName)) ?></strong>
        <?php if (!empty($queryParams['amenities'])): ?>
            <span class="badge bg-light text-primary border border-primary ms-1" style="font-size: 11.5px;">Filtered</span>
        <?php endif; ?>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="javascript:void(0);" class="text-slate-500 hover:text-slate-800 text-decoration-none d-inline-flex align-items-center gap-1" style="font-size: 12px;">
            <span>How payments to us affect ranking</span>
            <i class="fa-solid fa-circle-info text-slate-400" style="font-size: 11px;"></i>
        </a>
    </div>
</div>

<!-- Mobile Greeting Header (Matches Trivago Mobile Screenshot) -->
<div class="d-block d-lg-none mb-3">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-1">
        <h2 class="h5 fw-bold text-slate-900 mb-0 d-inline-flex align-items-center gap-1.5" style="font-size: 18px;">
            <span>Our top choices for you<span id="mobile_top_choices_name"><?= !empty($userName) ? ', ' . h($userName) : '' ?></span></span>
            <i class="fa-solid fa-circle-info text-slate-500" style="font-size: 13px;"></i>
        </h2>
    </div>
    <div class="mt-1">
        <a href="javascript:void(0);" class="text-slate-500 hover:text-slate-800 text-decoration-none d-inline-flex align-items-center gap-1" style="font-size: 12px;">
            <i class="fa-solid fa-circle-info text-slate-400" style="font-size: 11px;"></i>
            <span>How payments to us affect ranking</span>
        </a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const el = document.getElementById('mobile_top_choices_name');
    if (el && !el.innerText.trim()) {
        try {
            const u = JSON.parse(localStorage.getItem('user'));
            if (u && (u.name || u.username)) {
                el.innerText = ', ' + (u.name || u.username);
            }
        } catch(e) {}
    }
});
</script>