<?php
$bookingsList = !empty($userBookings) ? $userBookings : [
    [
        'id' => 101,
        'reference' => 'FN-8429',
        'property_name' => 'Sunrise Luxury Lodge',
        'city' => 'Dar es Salaam',
        'room_name' => 'Executive Suite with Ocean View',
        'check_in' => date('Y-m-d', strtotime('+2 days')),
        'check_out' => date('Y-m-d', strtotime('+5 days')),
        'guest_name' => 'John Mwangi',
        'total_amount' => 210000,
        'status' => 'confirmed'
    ]
];
?>

<div class="mb-4">
    <div class="row g-2 align-items-center mb-3">
        <div class="col-md-8">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                <input type="text" id="booking-search-input" class="form-control border-start-0" placeholder="Search by booking reference (e.g. FN-8429) or guest name">
            </div>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="<?= $this->Url->build('/hotel-list-01'); ?>" class="btn btn-primary btn-sm rounded-full fw-bold px-3">
                <i class="fa-solid fa-plus me-1"></i>Book New Stay
            </a>
        </div>
    </div>
</div>

<?php foreach ($bookingsList as $b): 
    $bId = $b['id'] ?? rand(100, 999);
    $bRef = $b['reference'] ?? ($b['booking_code'] ?? ('FN-' . $bId));
    $bProp = $b['property_name'] ?? ($b['property']['name'] ?? ($b['room']['property']['name'] ?? 'FastNet Lodge'));
    $bCity = $b['city'] ?? ($b['property']['city'] ?? ($b['room']['property']['city'] ?? 'Tanzania'));
    $bRoom = $b['room_name'] ?? ($b['room']['room_number'] ?? 'Standard Room');
    $bCheckIn = !empty($b['check_in']) ? date('D, d M Y', strtotime($b['check_in'])) : 'Upcoming';
    $bCheckOut = !empty($b['check_out']) ? date('D, d M Y', strtotime($b['check_out'])) : 'Upcoming';
    $bGuest = $b['guest_name'] ?? ($b['guest']['name'] ?? 'Guest Traveler');
    $bAmount = (float)($b['total_amount'] ?? ($b['total_price'] ?? 70000));
    $bStatus = strtolower($b['status'] ?? 'confirmed');
?>
    <!-- Single Booking Card -->
    <div class="card border br-dashed mb-3 rounded-3 shadow-xs bg-white">
        <!-- Card header -->
        <div class="card-header bg-slate-50/70 border-bottom d-flex flex-column flex-md-row justify-content-between align-items-md-center py-3">
            <div class="d-flex align-items-center">
                <div class="square--45 circle bg-orange-100 text-orange-600 flex-shrink-0 d-flex align-items-center justify-content-center fs-5">
                    <i class="fa-solid fa-hotel"></i>
                </div>
                <div class="ms-3">
                    <h6 class="card-title text-slate-900 fs-6 fw-bold mb-0"><?= h($bProp) ?> (<?= h($bCity) ?>)</h6>
                    <ul class="nav nav-divider small gap-2 text-xs text-muted mb-0">
                        <li>Reference: <strong class="text-orange-600 font-monospace"><?= h($bRef) ?></strong></li>
                        <li>•</li>
                        <li>Room: <?= h($bRoom) ?></li>
                    </ul>
                </div>
            </div>

            <div class="mt-2 mt-md-0 d-flex align-items-center gap-2">
                <span class="badge bg-success text-white fw-bold px-2.5 py-1 text-xs">
                    <i class="fa-solid fa-circle-check me-1"></i><?= ucfirst($bStatus) ?>
                </span>
            </div>
        </div>

        <!-- Card body -->
        <div class="card-body p-3">
            <div class="row g-3 align-items-center">
                <div class="col-sm-6 col-md-3">
                    <span class="text-xs text-muted d-block">Check-In Date</span>
                    <h6 class="mb-0 fs-sm fw-bold text-slate-800"><?= h($bCheckIn) ?></h6>
                </div>

                <div class="col-sm-6 col-md-3">
                    <span class="text-xs text-muted d-block">Check-Out Date</span>
                    <h6 class="mb-0 fs-sm fw-bold text-slate-800"><?= h($bCheckOut) ?></h6>
                </div>

                <div class="col-sm-6 col-md-3">
                    <span class="text-xs text-muted d-block">Booked For</span>
                    <h6 class="mb-0 fs-sm fw-medium text-slate-800"><?= h($bGuest) ?></h6>
                </div>

                <div class="col-sm-6 col-md-3 text-sm-end">
                    <span class="text-xs text-muted d-block">Total Paid</span>
                    <h6 class="mb-0 fs-6 fw-bold text-orange-600">TZS <?= number_format($bAmount) ?></h6>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>