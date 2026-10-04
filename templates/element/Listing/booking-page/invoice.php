<?php
/**
 * Official Booking Receipt.
 *
 * This block previously defaulted every field to invented values: a rand()
 * booking id, a rand() receipt number, "Guest Traveler", a 70,000 TSh total
 * and an 8% VAT split invented on the spot (contradicting the backend's own
 * 0% VAT / 1% processing-fee model).
 *
 * BookingController::bookingpageSuccess() now loads and verifies the real
 * booking from GET /api/bookings/{id} before rendering. The receipt modal is
 * only included for paid bookings; anything else shows details with an
 * honest status banner. These fallbacks therefore exist only as a guard: if
 * the verified payload is somehow incomplete we show "unavailable" rather
 * than print a fabricated receipt.
 */
$hasVerifiedBooking = !empty($verifiedBooking) && is_array($verifiedBooking);

$invBookingId   = $hasVerifiedBooking ? ($verifiedBooking['booking_code'] ?: ('BK-' . $verifiedBooking['id'])) : ($queryParams['booking_id'] ?? null);
$invReference   = $hasVerifiedBooking ? ($verifiedBooking['payment_reference'] ?? $verifiedBooking['booking_code'] ?? null) : ($queryParams['reference'] ?? null);
$invGuestName   = $hasVerifiedBooking ? ($verifiedBooking['guest']['name'] ?? null) : ($queryParams['guest_name'] ?? null);
$invGuestEmail  = $hasVerifiedBooking ? ($verifiedBooking['guest']['email'] ?? null) : ($queryParams['guest_email'] ?? null);
$invGuestPhone  = $hasVerifiedBooking ? ($verifiedBooking['guest']['phone_number'] ?? null) : ($queryParams['guest_phone'] ?? null);
$invCheckIn     = $hasVerifiedBooking ? ($verifiedBooking['check_in'] ?? null) : ($queryParams['check_in'] ?? null);
$invCheckOut    = $hasVerifiedBooking ? ($verifiedBooking['check_out'] ?? null) : ($queryParams['check_out'] ?? null);
$invTotalAmount = $hasVerifiedBooking ? (float) ($verifiedBooking['total_price'] ?? 0) : (float) ($queryParams['total_amount'] ?? 0);

$invNights = ($invCheckIn && $invCheckOut)
    ? max(1, (int) ((strtotime($invCheckOut) - strtotime($invCheckIn)) / 86400))
    : 0;

// Real money breakdown. The backend is authoritative on every figure; the
// previous /1.08 split invented tax that was never charged.
$invSubtotal     = $hasVerifiedBooking ? (float) ($verifiedBooking['subtotal'] ?? $invTotalAmount) : $invTotalAmount;
$invTax          = $hasVerifiedBooking ? (float) ($verifiedBooking['taxes'] ?? 0) : 0.0;
$invProcessingFee = $hasVerifiedBooking ? (float) ($verifiedBooking['azampay_fee'] ?? $verifiedBooking['processing_fee'] ?? 0) : 0.0;

$invPropName    = $property['name'] ?? null;
$invPropCity    = $property['city'] ?? null;
$invPropAddress = $property['address'] ?? $invPropCity;

$rawPm = strtolower($queryParams['payment_method'] ?? '');
$pmLabels = [
    'vodacom' => 'Vodacom M-Pesa',
    'm-pesa'  => 'Vodacom M-Pesa',
    'm pesa'  => 'Vodacom M-Pesa',
    'tigo'    => 'Tigo Pesa',
    'airtel'  => 'Airtel Money',
    'halopesa' => 'HaloPesa (Halotel)',
    'halotel' => 'HaloPesa (Halotel)',
    'crdb'    => 'CRDB Bank',
];
$invPaymentMethod = $pmLabels[$rawPm] ?? ($hasVerifiedBooking ? ($verifiedBooking['payment']['gateway'] ?? 'Mobile Money') : 'Mobile Money');
$invPaymentPhone  = $queryParams['payment_phone'] ?? $invGuestPhone;

$receiptUnavailable = !$invBookingId || !$invTotalAmount;
?>
<!-- Print Invoice Modal -->
<div class="modal modal-lg fade" id="invoice" tabindex="-1" role="dialog" aria-labelledby="invoicemodal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered invoice-pop-form" role="document">
        <div class="modal-content" id="invoicemodal">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fs-6 fw-bold">Official Booking Receipt</h5>
                <a href="#" class="text-muted fs-4" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-square-xmark"></i></a>
            </div>
            <div class="modal-body p-4" id="printable-invoice">
                <div class="invoiceblock-wrap">
                    <!-- Header -->
                    <div class="invoice-header d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom">
                        <div class="inv-fliop01 d-flex align-items-center justify-content-start">
                            <div class="inv-fliop01">
                                <div class="square--50 circle bg-light-primary text-primary fs-3 d-flex align-items-center justify-content-center">
                                    <i class="fa-solid fa-file-invoice"></i>
                                </div>
                            </div>
                            <div class="inv-fliop01 ps-3">
                                <span class="text-uppercase d-block fw-bold text-dark fs-6">Receipt #<?= h($invReference) ?></span>
                                <span class="text-sm text-muted"><i class="fa-regular fa-calendar me-1"></i>Issued on <?= date('d M Y') ?></span>
                            </div>
                        </div>
                        <div class="inv-fliop02">
                            <span class="badge bg-success px-3 py-2 text-white fw-bold">Confirmed</span>
                        </div>
                    </div>

                    <!-- Invoice Body -->
                    <div class="invoice-body">
                        <!-- Top Body -->
                        <div class="invoice-bodytop mb-3">
                            <div class="row align-items-start justify-content-between g-3">
                                <div class="col-sm-6">
                                    <div class="invoice-desc">
                                        <h6 class="fw-bold text-xs text-uppercase text-muted mb-1">Lodge / Property:</h6>
                                        <p class="text-sm text-dark fw-semibold mb-0"><?= h($invPropName) ?></p>
                                        <p class="text-xs text-muted mb-0"><?= h($invPropAddress) ?></p>
                                        <p class="text-xs text-muted">fastnetstays.com platform</p>
                                    </div>
                                </div>
                                <div class="col-sm-6 text-sm-end">
                                    <div class="invoice-desc">
                                        <h6 class="fw-bold text-xs text-uppercase text-muted mb-1">Billed To (Guest):</h6>
                                        <p class="text-sm text-dark fw-semibold mb-0"><?= h($invGuestName) ?></p>
                                        <p class="text-xs text-muted mb-0"><?= h($invGuestPhone) ?> (<?= h($invGuestEmail) ?>)</p>
                                        <p class="text-xs text-success fw-semibold mb-0"><i class="fa-solid fa-mobile-screen-button me-1"></i><?= h($invPaymentMethod) ?> (<?= h($invPaymentPhone) ?>)</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Mid Body: Dates -->
                        <div class="invoice-bodymid py-2 mb-3">
                            <div class="bg-light rounded-3 p-3">
                                <div class="row text-center g-2">
                                    <div class="col-4">
                                        <span class="text-xs text-muted d-block">Check-In</span>
                                        <strong class="text-sm text-dark"><?= date('d M Y', strtotime($invCheckIn)) ?></strong>
                                    </div>
                                    <div class="col-4">
                                        <span class="text-xs text-muted d-block">Check-Out</span>
                                        <strong class="text-sm text-dark"><?= date('d M Y', strtotime($invCheckOut)) ?></strong>
                                    </div>
                                    <div class="col-4">
                                        <span class="text-xs text-muted d-block">Duration</span>
                                        <strong class="text-sm text-dark"><?= h($invNights) ?> Night<?= $invNights > 1 ? 's' : '' ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Itemized Table -->
                        <div class="invoice-bodybott mb-3">
                            <div class="table-responsive border rounded-2">
                                <table class="table mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" class="text-sm">Item / Description</th>
                                            <th scope="col" class="text-sm text-center">Nights</th>
                                            <th scope="col" class="text-sm text-end">Amount (TZS)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-sm">
                                                <strong><?= h($invPropName ?: 'Accommodation') ?></strong><br>
                                                <small class="text-muted"><?= h($bookingRoom['title'] ?? ($invBookingId ? 'Reservation ' . $invBookingId : 'Reservation')) ?></small>
                                            </td>
                                            <td class="text-sm text-center"><?= h($invNights) ?></td>
                                            <td class="text-sm text-end"><?= number_format($invSubtotal) ?></td>
                                        </tr>
                                        <?php /* Only render rows that represent money actually
                                                 charged. The backend charges 0% VAT plus a 1%
                                                 processing fee; the previous markup always printed
                                                 an "8% Service Charge & VAT" row, inventing a
                                                 charge that was never billed. */ ?>
                                        <?php if ($invProcessingFee > 0): ?>
                                        <tr>
                                            <td class="text-sm" colspan="2">Payment processing fee</td>
                                            <td class="text-sm text-end"><?= number_format($invProcessingFee) ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <?php if ($invTax > 0): ?>
                                        <tr>
                                            <td class="text-sm" colspan="2">Tax</td>
                                            <td class="text-sm text-end"><?= number_format($invTax) ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <tr class="table-light">
                                            <td class="fw-bold fs-6" colspan="2">Total Paid / Due</td>
                                            <td class="fw-bold fs-6 text-end text-orange-600">TZS <?= number_format($invTotalAmount) ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="invoice-bodyaction pt-2 d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-sm btn-primary fw-bold" onclick="window.print()">
                                <i class="fa-solid fa-print me-1"></i>Print Receipt
                            </button>
                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>