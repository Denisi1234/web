<?php
/**
 * Trivago Bookings Profile Page
 */
$this->assign('title', 'Your bookings - FastNet Stays');
?>
<?= $this->element('navbar'); ?>

<?= $this->Html->css('/assets/css/profile.css'); ?>

<div class="trivago-profile-wrapper">
    <div class="container" style="max-width: 1120px;">
        <div class="row">
            
            <!-- Reusable Left Sidebar -->
            <?= $this->element('profile_sidebar', ['active' => 'bookings']); ?>

            <!-- Right Content -->
            <div class="col-lg-9 ps-lg-4">
                <div class="trivago-profile-header">
                    <h1 class="trivago-profile-title">Your bookings</h1>
                    <p class="trivago-profile-sub" style="font-size: 15px; color: #334155; margin-bottom: 24px;">Check bookings made with trivago DEALS</p>
                </div>

                <!-- Info Notice Box -->
                <div style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 18px 22px; display: flex; align-items: flex-start; gap: 14px; margin-bottom: 38px; font-size: 14px; line-height: 1.55; color: #334155; max-width: 820px;">
                    <i class="fa-solid fa-circle-info" style="font-size: 17px; color: #0f172a; margin-top: 2px; flex-shrink: 0;"></i>
                    <div>
                        Your booking details come from trivago Deals Ltd and aren't saved by us. See their <a href="<?= $this->Url->build('/privacy-policy'); ?>" style="color: #007fad; text-decoration: none; font-weight: 500;">Privacy Policy</a> for more info.
                    </div>
                </div>

                <!-- User Active Bookings List from Database -->
                <?php if (!empty($userBookings)): ?>
                    <div class="mb-4" style="max-width: 820px;">
                        <h2 style="font-size: 18px; font-weight: 800; color: #0f172a; margin-bottom: 14px;">Your Stays & Reservations</h2>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($userBookings as $b): ?>
                                <?php
                                    $bCode = $b['booking_code'] ?? ('BK' . ($b['id'] ?? ''));
                                    $propName = $b['room']['property']['name'] ?? 'FastNet Stay';
                                    $cityName = $b['room']['property']['city'] ?? ($b['property']['address'] ?? '');
                                    $roomTitle = $b['room']['room_number'] ?? ($b['room']['title'] ?? '');
                                    $checkIn = !empty($b['check_in']) ? date('d M Y', strtotime($b['check_in'])) : 'Flexible';
                                    $checkOut = !empty($b['check_out']) ? date('d M Y', strtotime($b['check_out'])) : 'Flexible';
                                    $priceFormatted = isset($b['total_price']) ? 'TSh ' . number_format((float)$b['total_price']) : '';
                                    $status = ucfirst(strtolower($b['status'] ?? ($b['booking_status'] ?? 'Pending')));
                                    $badgeClass = ($status === 'Confirmed') ? 'bg-success-subtle text-success' : 'bg-primary-subtle text-primary';
                                    $canCancel = !in_array(strtolower($status), ['cancelled', 'completed'], true) && !empty($b['id']);
                                ?>
                                <div class="card p-4 border rounded-3 shadow-xs bg-white">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 border-bottom pb-3 gap-2">
                                        <div>
                                            <span class="badge <?= $badgeClass; ?> fw-bold px-2 py-1 rounded"><?= htmlspecialchars($status); ?></span>
                                            <h4 class="fw-bold text-slate-900 mt-2 mb-0"><?= htmlspecialchars($propName); ?></h4>
                                            <p class="text-xs text-slate-500 mb-0"><?= htmlspecialchars($cityName); ?> · Booking ref: #<?= htmlspecialchars($bCode); ?></p>
                                        </div>
                                        <div class="text-end">
                                            <span class="fw-bold fs-6 text-slate-900 d-block"><?= htmlspecialchars($priceFormatted); ?></span>
                                            <a href="<?= $this->Url->build('/bookingpage-success?booking_code=' . urlencode($bCode)); ?>" class="btn btn-sm btn-outline-primary rounded-pill fw-bold px-3 mt-1">
                                                View Receipt
                                            </a>
                                        </div>
                                    </div>
                                    <div class="row g-3 text-sm text-slate-700">
                                        <div class="col-sm-6">
                                            <strong>Room:</strong> <?= htmlspecialchars($roomTitle); ?>
                                        </div>
                                        <div class="col-sm-6">
                                            <strong>Dates:</strong> <?= htmlspecialchars($checkIn); ?> – <?= htmlspecialchars($checkOut); ?>
                                        </div>
                                        <?php if ($canCancel): ?>
                                            <div class="col-12">
                                                <form method="post" action="<?= $this->Url->build('/my-booking/cancel'); ?>" onsubmit="return confirm('Cancel this booking?');">
                                                    <input type="hidden" name="booking_id" value="<?= htmlspecialchars((string)$b['id']); ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">Cancel booking</button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Find your trivago DEALS booking Form -->
                <div style="margin-bottom: 28px; max-width: 820px;">
                    <h2 style="font-size: 18px; font-weight: 800; color: #0f172a; margin-bottom: 4px;">Find your booking</h2>
                    <div style="font-size: 14px; color: #64748b; margin-bottom: 22px;">Enter your email and booking number</div>

                    <form id="findBookingForm" onsubmit="handleFindBooking(event)">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label style="display: block; font-size: 14px; font-weight: 700; color: #0f172a; margin-bottom: 8px;" for="bookingEmail">Email</label>
                                <input type="email" id="bookingEmail" class="form-control" style="height: 48px; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 0 16px; font-size: 15px;" placeholder="name@example.com" value="<?= htmlspecialchars($userProfile['email'] ?? ''); ?>" oninput="checkFormValidity()">
                            </div>
                            <div class="col-md-6">
                                <label style="display: block; font-size: 14px; font-weight: 700; color: #0f172a; margin-bottom: 8px;" for="bookingNumber">Booking number</label>
                                <input type="text" id="bookingNumber" class="form-control" style="height: 48px; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 0 16px; font-size: 15px;" placeholder="BKBJMQK3H4" oninput="checkFormValidity()">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" id="btnFindBooking" style="background-color: #007fad; color: #ffffff; font-size: 14.5px; font-weight: 700; padding: 11px 26px; border-radius: 8px; border: none; cursor: pointer; transition: all 0.15s ease;">
                                Find booking
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Results container if a booking is found -->
                <div id="bookingResultArea" class="mt-4" style="display: none; max-width: 820px;"></div>

            </div>

        </div>
    </div>
</div>

<!-- Floating Toast -->
<div id="trivago-toast"></div>


<script>
function checkFormValidity() {
    const email = document.getElementById('bookingEmail').value.trim();
    const num = document.getElementById('bookingNumber').value.trim();
    const btn = document.getElementById('btnFindBooking');
    
    if (email.length > 3 && email.includes('@') && num.length > 2) {
        btn.style.backgroundColor = '#007fad';
        btn.style.cursor = 'pointer';
        btn.removeAttribute('disabled');
    } else {
        btn.style.backgroundColor = '#d1d5db';
        btn.style.cursor = 'not-allowed';
        btn.setAttribute('disabled', 'disabled');
    }
}

function handleFindBooking(e) {
    e.preventDefault();
    const email = document.getElementById('bookingEmail').value.trim();
    const num = document.getElementById('bookingNumber').value.trim();
    const resArea = document.getElementById('bookingResultArea');
    const btn = document.getElementById('btnFindBooking');

    btn.disabled = true;
    btn.textContent = 'Searching...';
    showBookingToast('Searching for booking #' + num + '...');

    fetch(<?= json_encode($this->Url->build('/my-booking/find')) ?>, {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, booking_number: num })
    }).then(async response => {
        const data = await response.json();
        if (!response.ok) throw new Error(data.message || 'Booking not found.');
        const b = data.booking || {};
        const property = b.property || {};
        const safe = value => String(value ?? '').replace(/[&<>"']/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[char]));
        if (resArea) {
            resArea.innerHTML = `
                <div class="card p-4 border rounded-3 shadow-xs bg-white">
                    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3">
                        <div>
                            <span class="badge bg-success-subtle text-success fw-bold px-2 py-1 rounded">${safe(b.booking_status || b.payment_status || 'Pending')}</span>
                            <h4 class="fw-bold text-slate-900 mt-2 mb-0">${safe(b.booking_code || num)}</h4>
                            <p class="text-xs text-slate-500 mb-0">Booked for ${safe(b.guest?.email || email)}</p>
                        </div>
                        <a href="<?= $this->Url->build('/bookingpage-success'); ?>?booking_id=${encodeURIComponent(b.id || b.booking_id || b.booking_code || num)}" class="btn btn-sm btn-outline-primary rounded-full fw-bold px-3">
                            View receipt
                        </a>
                    </div>
                    <div class="row g-3 text-sm text-slate-700">
                        <div class="col-sm-6">
                            <strong>Property:</strong> ${safe(property.name || 'FastNet Stay')}
                        </div>
                        <div class="col-sm-6">
                            <strong>Dates:</strong> ${safe(b.check_in_formatted || b.check_in || '')} - ${safe(b.check_out_formatted || b.check_out || '')}
                        </div>
                    </div>
                </div>
            `;
            resArea.style.display = 'block';
            showBookingToast('Booking found');
        }
    }).catch(error => {
        if (resArea) {
            resArea.innerHTML = '<div class="alert alert-warning" role="alert">' + error.message.replace(/[&<>"']/g, '') + '</div>';
            resArea.style.display = 'block';
        }
        showBookingToast('Booking not found');
    }).finally(() => {
        btn.disabled = false;
        btn.textContent = 'Find booking';
    });
}

function showBookingToast(msg) {
    const toast = document.getElementById('trivago-toast');
    if (!toast) return;
    toast.innerText = msg;
    toast.style.display = 'block';
    setTimeout(() => { toast.style.opacity = '1'; }, 10);
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => { toast.style.display = 'none'; }, 250);
    }, 2500);
}
</script>
