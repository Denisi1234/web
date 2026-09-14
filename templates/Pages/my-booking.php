<?php
/**
 * FastNet Stays - Bookings Profile Page
 */
$this->assign('title', 'Your bookings - FastNet Stays');
$userBookings = is_array($userBookings ?? null) ? $userBookings : (is_array($bookings ?? null) ? $bookings : []);
?>
<?= $this->Html->css('/assets/css/google-travel-layout.css') ?>
<?= $this->Html->css('/assets/css/google-travel-home.css') ?>
<?= $this->element('navbar'); ?>

<main id="main-content" role="main">
<style>
/* ── Bookings Hub Page Styles ────────────────────────────────────────────── */
.trivago-profile-wrapper {
    background-color: #f8fafc;
    min-height: calc(100vh - 70px);
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    color: #0f172a;
    padding-top: 36px;
    padding-bottom: 70px;
}

.trivago-profile-header {
    margin-bottom: 24px;
}
.trivago-profile-title {
    font-size: 28px;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 4px;
    letter-spacing: -0.02em;
}
.trivago-profile-sub {
    font-size: 14.5px;
    color: #64748b;
    margin-bottom: 0;
}

/* Notice Box */
.bk-notice-box {
    background-color: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 12px;
    padding: 16px 20px;
    display: flex;
    align-items: flex-start;
    gap: 14px;
    margin-bottom: 30px;
    font-size: 13.5px;
    line-height: 1.5;
    color: #14532d;
    max-width: 820px;
}

/* Booking Card */
.bk-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    transition: all 0.15s ease;
    max-width: 820px;
}
.bk-card:hover {
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.07);
}

.bk-status-badge {
    font-size: 12px;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 6px;
    text-transform: capitalize;
    display: inline-block;
}
.bk-status-confirmed { background-color: #dcfce7; color: #15803d; }
.bk-status-pending { background-color: #fef3c7; color: #b45309; }
.bk-status-cancelled { background-color: #fee2e2; color: #b91c1c; }
.bk-status-completed { background-color: #e0f2fe; color: #0369a1; }

.bk-prop-title {
    font-size: 18px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.3;
}
.bk-prop-meta {
    font-size: 13px;
    color: #64748b;
}

.bk-price-val {
    font-size: 18px;
    font-weight: 800;
    color: #0f172a;
}

.bk-btn-receipt {
    background: #007fad;
    color: #ffffff;
    font-size: 13px;
    font-weight: 600;
    padding: 7px 16px;
    border-radius: 8px;
    text-decoration: none !important;
    transition: background-color 0.15s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.bk-btn-receipt:hover {
    background: #006b94;
    color: #ffffff;
}

.bk-btn-cancel {
    background: #ffffff;
    border: 1px solid #fca5a5;
    color: #dc2626;
    font-size: 12.5px;
    font-weight: 600;
    padding: 5px 12px;
    border-radius: 6px;
    transition: all 0.15s ease;
}
.bk-btn-cancel:hover {
    background: #fee2e2;
}

/* Empty State */
.bk-empty-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 40px 24px;
    text-align: center;
    max-width: 820px;
    margin-bottom: 36px;
}
.bk-empty-icon {
    width: 64px;
    height: 64px;
    background: #f1f5f9;
    color: #64748b;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    margin: 0 auto 18px auto;
}

/* Find Booking Form Card */
.bk-find-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 24px;
    max-width: 820px;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
}
</style>

<div class="trivago-profile-wrapper">
    <div class="container" style="max-width: 1140px; padding-left: 16px; padding-right: 16px;">
        <div class="row">
            
            <!-- Reusable Left Sidebar -->
            <?= $this->element('profile_sidebar', ['active' => 'bookings']); ?>

            <!-- Right Content -->
            <div class="col-lg-9 ps-lg-4">
                <div class="trivago-profile-header">
                    <h1 class="trivago-profile-title">Your bookings</h1>
                    <p class="trivago-profile-sub">Review upcoming stays, download receipts, or check past reservations</p>
                </div>

                <!-- Info Notice Box -->
                <div class="bk-notice-box">
                    <i class="fa-solid fa-shield-halved" style="font-size: 18px; color: #15803d; margin-top: 2px; flex-shrink: 0;"></i>
                    <div>
                        Your bookings are securely stored with <strong>FastNet Stays</strong>. View room selections, check-in schedules, totals in TSh, and manage cancellation when eligible.
                    </div>
                </div>

                <!-- User Active Bookings List -->
                <?php if (!empty($userBookings)): ?>
                    <div class="mb-5" style="max-width: 820px;">
                        <h2 style="font-size: 17px; font-weight: 800; color: #0f172a; margin-bottom: 14px;">Active & Past Stays</h2>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($userBookings as $b): 
                                $bCode = $b['booking_code'] ?? ($b['booking_number'] ?? ('BK' . ($b['id'] ?? '')));
                                $propName = $b['room']['property']['name'] ?? ($b['property']['name'] ?? ($b['property_name'] ?? 'FastNet Stay'));
                                $cityName = $b['room']['property']['city'] ?? ($b['property']['city'] ?? ($b['property']['address'] ?? 'Tanzania'));
                                $roomTitle = $b['room']['room_number'] ?? ($b['room']['title'] ?? ($b['room_title'] ?? 'Standard Stay Room'));
                                $checkIn = !empty($b['check_in']) ? date('d M Y', strtotime($b['check_in'])) : 'Flexible';
                                $checkOut = !empty($b['check_out']) ? date('d M Y', strtotime($b['check_out'])) : 'Flexible';
                                $priceFormatted = isset($b['total_price']) ? 'TSh ' . number_format((float)$b['total_price']) : '';
                                $rawStatus = strtolower($b['status'] ?? ($b['booking_status'] ?? 'Confirmed'));
                                $status = ucfirst($rawStatus);
                                $badgeClass = match($rawStatus) {
                                    'confirmed' => 'bk-status-confirmed',
                                    'pending' => 'bk-status-pending',
                                    'cancelled' => 'bk-status-cancelled',
                                    'completed' => 'bk-status-completed',
                                    default => 'bk-status-confirmed',
                                };
                                $canCancel = !in_array($rawStatus, ['cancelled', 'completed'], true) && !empty($bCode);
                            ?>
                                <div class="bk-card" id="bk_card_<?= h($bCode) ?>">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 border-bottom pb-3 gap-2">
                                        <div>
                                            <span class="bk-status-badge <?= $badgeClass; ?>"><?= htmlspecialchars($status); ?></span>
                                            <h3 class="bk-prop-title mt-2 mb-1"><?= htmlspecialchars($propName); ?></h3>
                                            <p class="bk-prop-meta mb-0">
                                                <i class="fa-solid fa-location-dot me-1 text-slate-400"></i><?= htmlspecialchars($cityName); ?>
                                                <span class="mx-1">&middot;</span>
                                                Ref: <strong class="text-slate-800">#<?= htmlspecialchars($bCode); ?></strong>
                                            </p>
                                        </div>
                                        <div class="text-end">
                                            <div class="bk-price-val mb-1"><?= htmlspecialchars($priceFormatted); ?></div>
                                            <a href="<?= $this->Url->build('/bookingpage-success?booking_code=' . urlencode($bCode)); ?>" class="bk-btn-receipt">
                                                <i class="fa-regular fa-file-lines"></i>
                                                <span>View Receipt</span>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="row g-3 text-sm text-slate-700 align-items-center">
                                        <div class="col-sm-5">
                                            <div class="text-xs text-slate-400 fw-semibold">ROOM TYPE</div>
                                            <div class="fw-semibold text-slate-800"><?= htmlspecialchars($roomTitle); ?></div>
                                        </div>
                                        <div class="col-sm-4">
                                            <div class="text-xs text-slate-400 fw-semibold">STAY DATES</div>
                                            <div class="fw-semibold text-slate-800"><i class="fa-regular fa-calendar me-1 text-slate-400"></i><?= htmlspecialchars($checkIn); ?> – <?= htmlspecialchars($checkOut); ?></div>
                                        </div>
                                        <div class="col-sm-3 text-sm-end">
                                            <?php if ($canCancel): ?>
                                                <button type="button" class="bk-btn-cancel" onclick="cancelBookingAction('<?= h($bCode) ?>')">
                                                    Cancel stay
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Empty State Banner -->
                    <div class="bk-empty-card">
                        <div class="bk-empty-icon">
                            <i class="fa-solid fa-suitcase"></i>
                        </div>
                        <h2 class="fs-5 fw-bold text-slate-900 mb-2">No active bookings found</h2>
                        <p class="text-slate-600 mb-4" style="max-width: 440px; margin: 0 auto; font-size: 14px;">
                            You don't have any stays scheduled right now. Explore properties across Tanzania to make your next reservation.
                        </p>
                        <a href="<?= $this->Url->build('/hotel-list-01'); ?>" class="btn btn-primary px-4 py-2 fw-bold rounded-pill" style="font-size: 14px;">
                            Search stays
                        </a>
                    </div>
                <?php endif; ?>

                <!-- Find your booking Lookup Form -->
                <div class="bk-find-card mb-4">
                    <h2 style="font-size: 17px; font-weight: 800; color: #0f172a; margin-bottom: 4px;">Find your booking</h2>
                    <div style="font-size: 13.5px; color: #64748b; margin-bottom: 20px;">Lookup your reservation by email and booking reference number</div>

                    <form id="findBookingForm" onsubmit="handleFindBooking(event)">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label style="display: block; font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 6px;" for="bookingEmail">Guest Email</label>
                                <input type="email" id="bookingEmail" class="form-control" style="height: 44px; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 0 14px; font-size: 14px;" placeholder="name@example.com" value="<?= htmlspecialchars($userProfile['email'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label style="display: block; font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 6px;" for="bookingNumber">Booking Number / Reference</label>
                                <input type="text" id="bookingNumber" class="form-control" style="height: 44px; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 0 14px; font-size: 14px;" placeholder="e.g. BK12345 or FS-2026-..." required>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" id="btnFindBooking" class="btn btn-primary fw-bold px-4 py-2" style="font-size: 14px; border-radius: 8px;">
                                <i class="fa-solid fa-magnifying-glass me-1"></i> Find booking
                            </button>
                        </div>
                    </form>

                    <!-- Results container if a booking is found -->
                    <div id="bookingResultArea" class="mt-4" style="display: none;"></div>
                </div>

            </div>

        </div>
    </div>
</div>

<!-- Floating Toast -->
<div id="trivago-toast" style="position: fixed; bottom: 24px; right: 24px; background: #0f172a; color: #fff; padding: 12px 20px; border-radius: 10px; font-size: 13.5px; z-index: 9999; display: none; opacity: 0; transition: opacity 0.25s ease; box-shadow: 0 8px 24px rgba(0,0,0,0.15);"></div>

<script>
async function cancelBookingAction(bookingId) {
    if (!confirm('Are you sure you want to cancel this booking?')) return;
    showBookingToast('Processing cancellation...');
    const csrf = document.querySelector('meta[name="csrfToken"]')?.content || '';
    try {
        const res = await fetch('<?= $this->Url->build('/my-booking/cancel'); ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-Token': csrf },
            body: JSON.stringify({ booking_id: bookingId })
        });
        const data = await res.json();
        if (res.ok) {
            showBookingToast(data.message || 'Booking cancelled successfully');
            const card = document.getElementById('bk_card_' + bookingId);
            if (card) {
                const badge = card.querySelector('.bk-status-badge');
                if (badge) {
                    badge.className = 'bk-status-badge bk-status-cancelled';
                    badge.innerText = 'Cancelled';
                }
                const btn = card.querySelector('.bk-btn-cancel');
                if (btn) btn.remove();
            }
        } else {
            showBookingToast(data.message || 'Could not cancel booking');
        }
    } catch(e) {
        showBookingToast('Network error cancelling booking');
    }
}

function handleFindBooking(e) {
    e.preventDefault();
    const email = document.getElementById('bookingEmail').value.trim();
    const num = document.getElementById('bookingNumber').value.trim();
    const resArea = document.getElementById('bookingResultArea');
    const btn = document.getElementById('btnFindBooking');

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Searching...';
    showBookingToast('Searching for booking #' + num + '...');

    fetch('<?= $this->Url->build('/my-booking/find'); ?>', {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-Token': document.querySelector('meta[name="csrfToken"]')?.content || '' },
        body: JSON.stringify({ email: email, booking_number: num })
    }).then(async response => {
        const data = await response.json();
        if (!response.ok) throw new Error(data.message || 'Booking not found.');
        const b = data.booking || {};
        const prop = b.room?.property || b.property || {};
        const safe = v => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c]));
        const code = b.booking_code || num;
        const price = b.total_price ? 'TSh ' + Number(b.total_price).toLocaleString() : '';
        const status = (b.status || b.booking_status || 'Confirmed');
        const checkIn = b.check_in ? new Date(b.check_in).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : 'Flexible';
        const checkOut = b.check_out ? new Date(b.check_out).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : 'Flexible';
        
        if (resArea) {
            resArea.innerHTML = `
                <div class="bk-card mt-3">
                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 border-bottom pb-3 gap-2">
                        <div>
                            <span class="bk-status-badge bk-status-${status.toLowerCase()}">${safe(status)}</span>
                            <h3 class="bk-prop-title mt-2 mb-1">${safe(prop.name || 'FastNet Stay')}</h3>
                            <p class="bk-prop-meta mb-0">Ref: <strong class="text-slate-800">#${safe(code)}</strong></p>
                        </div>
                        <div class="text-end">
                            <div class="bk-price-val mb-1">${safe(price)}</div>
                            <a href="<?= $this->Url->build('/bookingpage-success'); ?>?booking_code=${encodeURIComponent(code)}" class="bk-btn-receipt">
                                <i class="fa-regular fa-file-lines"></i>
                                <span>View Receipt</span>
                            </a>
                        </div>
                    </div>
                    <div class="row g-3 text-sm text-slate-700">
                        <div class="col-sm-6">
                            <div class="text-xs text-slate-400 fw-semibold">GUEST EMAIL</div>
                            <div class="fw-semibold text-slate-800">${safe(b.guest?.email || email)}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-xs text-slate-400 fw-semibold">STAY DATES</div>
                            <div class="fw-semibold text-slate-800">${safe(checkIn)} – ${safe(checkOut)}</div>
                        </div>
                    </div>
                </div>
            `;
            resArea.style.display = 'block';
            showBookingToast('Booking found!');
        }
    }).catch(error => {
        if (resArea) {
            resArea.innerHTML = '<div class="alert alert-warning mt-3 rounded-3" role="alert"><i class="fa-solid fa-triangle-exclamation me-2"></i>' + error.message.replace(/[&<>"']/g, '') + '</div>';
            resArea.style.display = 'block';
        }
        showBookingToast('Booking not found');
    }).finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-magnifying-glass me-1"></i> Find booking';
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
</main>
<?= $this->element('footer', ['skin' => 'skin-light-footer']) ?>
