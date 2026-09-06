<?php
$checkIn = $queryParams['checkIn'] ?? date('Y-m-d', strtotime('+1 day'));
$checkOut = $queryParams['checkOut'] ?? date('Y-m-d', strtotime('+4 days'));
$pricePerNight = (float)($queryParams['price'] ?? ($calculation['total_price'] ?? 70000));
$nights = max(1, (strtotime($checkOut) - strtotime($checkIn)) / 86400);

// Use backend calculation values if available, else calculate standard breakdown
if (!empty($calculation) && !empty($calculation['total_amount'])) {
    $grandTotal = (float)$calculation['total_amount'];
    $subtotal = !empty($calculation['subtotal']) ? (float)$calculation['subtotal'] : ($pricePerNight * $nights);
    $taxFee = !empty($calculation['taxes']) ? (float)$calculation['taxes'] : ($grandTotal - $subtotal);
} else {
    $subtotal = $pricePerNight * $nights;
    $taxFee = $subtotal * 0.08;
    $grandTotal = $subtotal + $taxFee;
}

$isStep2 = ($this->request->getParam('action') === 'bookingpage02');
?>
<div class="col-xl-4 col-lg-4 col-md-12">
    <div class="side-block card rounded-3 p-3 border border-slate-200 shadow-sm bg-white">
        <h5 class="fw-bold fs-6 text-slate-900 mb-3">Reservation Summary</h5>
        <div class="mid-block rounded-2 border br-dashed p-3 mb-3 bg-slate-50">
            <div class="row align-items-center justify-content-between g-2 mb-3">
                <div class="col-6">
                    <div class="bg-white rounded-2 p-2 border border-slate-100">
                        <span class="d-block text-muted-3 text-xs fw-bold text-uppercase mb-1">Check-In</span>
                        <p class="text-dark fw-bold text-sm mb-0"><?= date('D, d M Y', strtotime($checkIn)) ?></p>
                        <span class="text-muted text-xs">From 14:00</span>
                    </div>
                </div>
                <div class="col-6">
                    <div class="bg-white rounded-2 p-2 border border-slate-100">
                        <span class="d-block text-muted-3 text-xs fw-bold text-uppercase mb-1">Check-Out</span>
                        <p class="text-dark fw-bold text-sm mb-0"><?= date('D, d M Y', strtotime($checkOut)) ?></p>
                        <span class="text-muted text-xs">By 11:00</span>
                    </div>
                </div>
            </div>
            <div class="row align-items-center justify-content-between mb-1">
                <div class="col-12">
                    <div class="d-flex align-items-center">
                        <div class="square--30 circle text-orange-600 bg-orange-100"><i class="fa-regular fa-calendar"></i></div>
                        <span class="text-dark fw-bold ms-2 text-sm"><?= h($nights) ?> Night<?= $nights > 1 ? 's' : '' ?> Total Stay</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="bott-block d-block mb-3">
            <h5 class="fw-bold fs-6 text-slate-900 mb-2">Price Breakdown</h5>
            <ul class="list-group list-group-borderless">
                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-1">
                    <span class="text-sm text-slate-700">Room Stay (<?= h($nights) ?> night<?= $nights > 1 ? 's' : '' ?>)</span>
                    <span class="fw-bold text-slate-900 text-sm">TZS <?= number_format($subtotal) ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-1">
                    <span class="text-sm text-slate-700">8% Service & VAT</span>
                    <span class="fw-bold text-slate-900 text-sm">TZS <?= number_format($taxFee) ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-top mt-2">
                    <span class="fw-bold text-slate-900 fs-6">Total Amount</span>
                    <span class="fw-bold text-orange-600 fs-5">TZS <?= number_format($grandTotal) ?></span>
                </li>
            </ul>
        </div>

        <?php if (!$isStep2): ?>
            <div class="bott-block">
                <a href="<?= $this->Url->build([
                    'controller' => 'Pages',
                    'action' => 'bookingpage02',
                    '?' => $queryParams ?? []
                ]); ?>" class="btn fw-bold btn-primary full-width rounded-full shadow-sm py-2">
                    Proceed to Guest Info <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>