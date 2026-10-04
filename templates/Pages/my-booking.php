<?php
/**
 * FastNet Stays - My Bookings (live records only)
 */
$this->assign('title', 'My bookings - FastNet Stays');
$this->assign('description', 'Review upcoming stays, download receipts and track payments on FastNet Stays.');
$userBookings = is_array($userBookings ?? null) ? $userBookings : (is_array($bookings ?? null) ? $bookings : []);
$pendingPayments = is_array($pendingPayments ?? null) ? $pendingPayments : [];
?>
<?= $this->element('navbar'); ?>
<main id="main-content" role="main" style="background:var(--cds-gray-10);min-height:85vh">
<style>
.cds-bk-hero{padding:28px 0 4px}
.cds-eyebrow{font-size:12px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:var(--cds-blue-60)}
.cds-h1{font-size:30px;font-weight:300;color:#161616;margin:8px 0 6px}
.cds-lede{font-size:14px;color:#525252;margin:0;max-width:640px}
.cds-bk-body{max-width:960px;margin:0 auto;padding:20px 16px 56px;display:flex;flex-direction:column;gap:20px}
.cds-sec-label{font-size:12px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:#525252;margin:0 0 4px}
.cds-sec-title{font-size:20px;font-weight:400;color:#161616;margin:0 0 10px}
.cds-card{background:#fff;border:1px solid #e0e0e0}
.cds-pend{border-left:4px solid #f1c21b}
.cds-bk-row{display:flex;gap:16px;align-items:center;padding:16px;border-top:1px solid #e0e0e0;flex-wrap:wrap}
.cds-bk-row:first-child{border-top:none}
.cds-bk-main{flex:1 1 260px;min-width:0}
.cds-bk-prop{font-size:15px;font-weight:600;color:#161616;margin:6px 0 2px}
.cds-bk-meta{font-size:12.5px;color:#6f6f6f}
.cds-bk-meta strong{color:#393939}
.cds-bk-side{text-align:right;display:flex;flex-direction:column;gap:8px;align-items:flex-end}
.cds-bk-amt{font-size:17px;font-weight:600;color:#161616;white-space:nowrap}
.cds-bk-actions{display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end}
.cds-tag{display:inline-block;font-size:11px;font-weight:600;letter-spacing:.05em;text-transform:uppercase;padding:3px 8px}
.cds-t-confirmed{background:#defbe6;color:#0e6027}
.cds-t-pending{background:#fcf4d6;color:#8e6a00}
.cds-t-cancelled{background:#fff1f1;color:#b81922}
.cds-t-completed{background:#d0e2ff;color:#0043ce}
.cds-t-checkin{background:#e8daff;color:#491d8b}
.cds-t-paid{background:#defbe6;color:#0e6027}
.cds-btn{display:inline-block;background:var(--cds-blue-60);color:#fff;border:1px solid transparent;padding:9px 18px;font-size:13.5px;border-radius:0;text-decoration:none!important;cursor:pointer;min-height:40px}
.cds-btn:hover{background:#0353e9;color:#fff}
.cds-btn-ghost{display:inline-block;background:transparent;color:var(--cds-blue-60);border:1px solid var(--cds-blue-60);padding:9px 18px;font-size:13.5px;border-radius:0;text-decoration:none!important;cursor:pointer;min-height:40px}
.cds-btn-ghost:hover{background:var(--cds-blue-60);color:#fff}
.cds-btn-danger{display:inline-block;background:transparent;color:#da1e28;border:1px solid #e0e0e0;padding:9px 18px;font-size:13.5px;border-radius:0;cursor:pointer;min-height:40px}
.cds-btn-danger:hover{background:#fff1f1;border-color:#da1e28}
.cds-btn-pay{background:#8e6a00;border-color:transparent;color:#fff}
.cds-btn-pay:hover{background:#6f5200;color:#fff}
.cds-empty{background:#fff;border:1px solid #e0e0e0;padding:40px 20px;text-align:center}
.cds-empty i{font-size:28px;color:#8d8d8d;display:block;margin-bottom:12px}
.cds-empty b{display:block;font-size:15px;color:#161616;margin-bottom:4px}
.cds-empty span{font-size:13.5px;color:#525252}
.cds-find{background:#fff;border:1px solid #e0e0e0;padding:20px}
.cds-find h2{font-size:18px;font-weight:400;color:#161616;margin:0 0 4px}
.cds-find p.sub{font-size:13px;color:#525252;margin:0 0 14px}
.cds-grid2{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px}
.cds-field label{display:block;font-size:12px;color:#525252;margin-bottom:8px}
#findBookingForm.cds-form input{width:100%!important;background:#f4f4f4!important;border:none!important;border-bottom:1px solid #8d8d8d!important;border-radius:0!important;height:44px!important;padding:0 12px!important;font-size:14px!important;color:#161616!important}
#findBookingForm.cds-form input:focus{outline:2px solid var(--cds-focus)!important;outline-offset:-2px!important}
.cds-note{display:flex;gap:12px;background:#fff;border:1px solid #e0e0e0;border-left:4px solid #24a148;padding:12px 16px;font-size:13.5px;color:#161616;margin-bottom:4px}
.cds-note.info{border-left-color:var(--cds-blue-60)}
.cds-dl{display:grid;grid-template-columns:130px 1fr;gap:8px 12px;font-size:13.5px;padding:4px 0}
.cds-dl dt{font-size:11.5px;text-transform:uppercase;letter-spacing:.05em;color:#6f6f6f}
.cds-dl dd{margin:0;color:#161616;font-weight:600}
@media(max-width:640px){.cds-grid2{grid-template-columns:1fr}.cds-bk-side{text-align:left;align-items:flex-start}.cds-bk-actions{justify-content:flex-start}}
</style>

<div class="container" style="max-width:960px">
  <div class="cds-bk-hero">
    <div class="cds-eyebrow">Account / Bookings</div>
    <h1 class="cds-h1">My bookings</h1>
    <p class="cds-lede">Upcoming and past stays, receipts, payments and cancellations.</p>
  </div>
</div>

<div class="container" style="max-width:1180px">
  <div class="row">
    <?= $this->element('profile_sidebar', ['active' => 'bookings']); ?>
    <div class="col-lg-9 ps-lg-4">
<div class="cds-bk-body" style="max-width:820px;padding-left:0;padding-right:0">
  <div id="bookingResultArea" style="display:none"></div>

  <?php if (!empty($pendingPayments)): ?>
  <section aria-label="Awaiting payment">
    <p class="cds-sec-label">Action needed</p>
    <h2 class="cds-sec-title">Awaiting payment</h2>
    <div class="cds-card cds-pend">
      <?php foreach ($pendingPayments as $pp):
        $ppCode = (string)($pp['booking_code'] ?? '');
        $ppAmt = (float)($pp['amount'] ?? 0);
        $ppMethod = (string)($pp['payment_method'] ?? '');
      ?>
      <div class="cds-bk-row">
        <div class="cds-bk-main">
          <span class="cds-tag cds-t-pending">Awaiting payment</span>
          <div class="cds-bk-prop">Ref #<?= h($ppCode !== '' ? $ppCode : $pp['payment_id']) ?></div>
          <div class="cds-bk-meta"><?= $ppMethod !== '' ? h(ucfirst($ppMethod)) . ' · ' : '' ?>Complete before the request expires</div>
        </div>
        <div class="cds-bk-side">
          <?php if ($ppAmt > 0): ?><div class="cds-bk-amt">TSh <?= number_format($ppAmt) ?></div><?php endif; ?>
          <div class="cds-bk-actions">
            <a href="<?= $this->Url->build('/booking-payment', ['?' => ['payment_id' => $pp['payment_id']]]) ?>" class="cds-btn cds-btn-pay">Complete payment</a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <section aria-label="Your stays">
    <p class="cds-sec-label">Reservations</p>
    <h2 class="cds-sec-title">Active &amp; past stays</h2>
    <?php if (!empty($userBookings)): ?>
    <div class="cds-card">
      <?php foreach ($userBookings as $b):
        $bCode = $b['booking_code'] ?? ($b['booking_number'] ?? ('BK' . ($b['id'] ?? '')));
        $propName = $b['room']['property']['name'] ?? ($b['property']['name'] ?? ($b['property_name'] ?? 'FastNet Stay'));
        $cityName = $b['room']['property']['city'] ?? ($b['property']['city'] ?? ($b['property']['address'] ?? 'Tanzania'));
        $roomTitle = $b['room']['room_number'] ?? ($b['room']['title'] ?? ($b['room_title'] ?? 'Standard room'));
        $checkIn = !empty($b['check_in']) ? date('d M Y', strtotime($b['check_in'])) : 'Flexible';
        $checkOut = !empty($b['check_out']) ? date('d M Y', strtotime($b['check_out'])) : 'Flexible';
        $price = isset($b['total_price']) ? 'TSh ' . number_format((float)$b['total_price']) : '';
        $rawStatus = strtolower($b['status'] ?? ($b['booking_status'] ?? 'confirmed'));
                                $tagCls = match(true) {
                                  $rawStatus === 'confirmed' => 'cds-t-confirmed',
                                  $rawStatus === 'pending' => 'cds-t-pending',
                                  $rawStatus === 'completed' => 'cds-t-completed',
                                  in_array($rawStatus, ['checked in', 'checked-in']) => 'cds-t-checkin',
                                  in_array($rawStatus, ['cancelled', 'canceled']) => 'cds-t-cancelled',
                                  default => 'cds-t-confirmed',
                                };
        $canCancel = !in_array($rawStatus, ['cancelled', 'canceled', 'completed'], true) && !empty($bCode);
        $cardGuestEmail = (string)(is_array($b['guest'] ?? null) ? ($b['guest']['email'] ?? '') : ($b['guest_email'] ?? ''));
        $detailsParams = ['booking_code' => $bCode];
        if ($cardGuestEmail !== '') $detailsParams['email'] = $cardGuestEmail;
      ?>
      <div class="cds-bk-row" id="bk_card_<?= h($bCode) ?>">
        <div class="cds-bk-main">
          <span class="cds-tag <?= $tagCls ?> bk-status-badge"><?= h(ucfirst($rawStatus)) ?></span>
          <div class="cds-bk-prop"><?= h($propName) ?></div>
          <div class="cds-bk-meta"><?= h($roomTitle) ?> · <?= h($cityName) ?></div>
          <div class="cds-bk-meta"><?= h($checkIn) ?> → <?= h($checkOut) ?> · Ref <strong>#<?= h($bCode) ?></strong></div>
        </div>
        <div class="cds-bk-side">
          <?php if ($price !== ''): ?><div class="cds-bk-amt"><?= h($price) ?></div><?php endif; ?>
          <div class="cds-bk-actions">
            <?php if ($bCode !== ''): ?>
            <a href="<?= $this->Url->build('/bookingpage-success', ['?' => $detailsParams]) ?>" class="cds-btn-ghost">View details</a>
            <?php endif; ?>
            <button type="button" class="cds-btn" onclick="downloadBookingReceipt('<?= h($bCode) ?>')">Receipt PDF</button>
            <?php if ($canCancel): ?><button type="button" class="cds-btn-danger" onclick="cancelBookingAction('<?= h($bCode) ?>')">Cancel stay</button><?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="cds-empty">
      <i class="fa-solid fa-suitcase" aria-hidden="true"></i>
      <b>No bookings yet</b>
      <span>Your confirmed stays will appear here.</span>
      <div style="margin-top:14px"><a href="<?= $this->Url->build('/hotel-list-01') ?>" class="cds-btn">Search stays</a></div>
    </div>
    <?php endif; ?>
  </section>

  <section aria-label="Find your booking">
    <p class="cds-sec-label">Can’t find it</p>
    <h2 class="cds-sec-title">Find your booking</h2>
    <div class="cds-find">
      <p class="sub">Look up any reservation with the guest email and booking reference.</p>
      <form id="findBookingForm" class="cds-form" onsubmit="handleFindBooking(event)">
        <div class="cds-grid2">
          <div class="cds-field">
            <label for="bookingEmail">Guest email</label>
            <input type="email" id="bookingEmail" placeholder="name@example.com" value="<?= h($userProfile['email'] ?? '') ?>" required>
          </div>
          <div class="cds-field">
            <label for="bookingNumber">Booking reference</label>
            <input type="text" id="bookingNumber" placeholder="e.g. BK12345" required>
          </div>
        </div>
        <div style="display:flex;justify-content:flex-end">
          <button type="submit" id="btnFindBooking" class="cds-btn">Find booking</button>
        </div>
      </form>
    </div>
  </section>
</div>
    </div>
  </div>
</div>

<div id="trivago-toast" style="position:fixed;bottom:24px;right:24px;background:#161616;color:#fff;padding:12px 20px;font-size:13.5px;z-index:9999;display:none;opacity:0;transition:opacity .25s ease"></div>

<script>
async function confirmAction(msg) {
  if (typeof window.fnsConfirm === 'function') {
    try { return await window.fnsConfirm(msg); } catch (e) { return false; }
  }
  return window.confirm(msg);
}
async function cancelBookingAction(bookingId) {
  if (!await confirmAction('Cancel this booking? The room is released immediately and refunds follow the property policy.')) return;
  showBookingToast('Processing cancellation…');
  const csrf = document.querySelector('meta[name="csrfToken"]')?.content || '';
  try {
    const res = await fetch('<?= $this->Url->build('/my-booking/cancel') ?>', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-Token': csrf },
      body: JSON.stringify({ booking_id: bookingId })
    });
    const data = await res.json().catch(() => ({}));
    if (res.ok) {
      showBookingToast(data.message || 'Booking cancelled');
      const card = document.getElementById('bk_card_' + bookingId);
      if (card) {
        const badge = card.querySelector('.bk-status-badge');
        if (badge) { badge.className = 'cds-tag cds-t-cancelled bk-status-badge'; badge.innerText = 'Cancelled'; }
        const btn = card.querySelector('.cds-btn-danger');
        if (btn) btn.remove();
      }
    } else {
      showBookingToast(data.message || 'Could not cancel booking');
    }
  } catch (e) {
    showBookingToast('Network error cancelling booking');
  }
}

function handleFindBooking(e) {
  e.preventDefault();
  const email = document.getElementById('bookingEmail').value.trim();
  const num = document.getElementById('bookingNumber').value.trim();
  const resArea = document.getElementById('bookingResultArea');
  const btn = document.getElementById('btnFindBooking');
  if (!email || !num) return;
  btn.disabled = true;
  const btnHtml = btn.innerHTML;
  btn.innerHTML = 'Searching…';
  fetch('<?= $this->Url->build('/my-booking/find') ?>', {
    method: 'POST',
    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-Token': document.querySelector('meta[name="csrfToken"]')?.content || '' },
    body: JSON.stringify({ email: email, booking_number: num })
  }).then(async response => {
    const data = await response.json().catch(() => ({}));
    if (!response.ok) throw new Error(data.message || 'Booking not found.');
    const b = data.booking || {};
    const prop = (b.room && b.room.property) || b.property || {};
    const safe = v => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c]));
    const code = b.booking_code || num;
    const price = b.total_price ? 'TSh ' + Number(b.total_price).toLocaleString() : '';
        const status = (b.status || b.booking_status || 'Confirmed');
        const statusCls = /cancel/i.test(status) ? 'cds-t-cancelled' : (/check.?in/i.test(status) ? 'cds-t-checkin' : (/complet/i.test(status) ? 'cds-t-completed' : (/pend/i.test(status) ? 'cds-t-pending' : 'cds-t-confirmed')));
    const checkIn = b.check_in ? new Date(b.check_in).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : 'Flexible';
    const checkOut = b.check_out ? new Date(b.check_out).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : 'Flexible';
    const gEmail = (b.guest && b.guest.email) || b.guest_email || email;
    resArea.innerHTML = '<div class="cds-card" style="margin-bottom:16px"><div class="cds-bk-row">'
      + '<div class="cds-bk-main"><span class="cds-tag ' + statusCls + '">' + safe(status) + '</span>'
      + '<div class="cds-bk-prop">' + safe(prop.name || 'FastNet Stay') + '</div>'
      + '<div class="cds-bk-meta">Ref <strong>#' + safe(code) + '</strong> · ' + safe(checkIn) + ' → ' + safe(checkOut) + '</div></div>'
      + '<div class="cds-bk-side">' + (price ? '<div class="cds-bk-amt">' + safe(price) + '</div>' : '')
      + '<div class="cds-bk-actions"><a class="cds-btn-ghost" href="<?= $this->Url->build('/bookingpage-success') ?>?booking_code=' + encodeURIComponent(code) + '&email=' + encodeURIComponent(gEmail) + '">View details</a></div>'
      + '</div></div></div>';
    resArea.style.display = 'block';
    resArea.scrollIntoView({ behavior: 'smooth', block: 'center' });
    showBookingToast('Booking found');
  }).catch(error => {
    resArea.innerHTML = '<div class="cds-card" style="margin-bottom:16px;border-left:4px solid #da1e28"><div class="cds-bk-row"><div class="cds-bk-main">' + String(error.message || 'Booking not found.').replace(/[<>&"]/g, '') + '</div></div></div>';
    resArea.style.display = 'block';
    showBookingToast('Booking not found');
  }).finally(() => {
    btn.disabled = false;
    btn.innerHTML = btnHtml;
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

function downloadBookingReceipt(bookingCode) {
  if (!bookingCode) return;
  showBookingToast('Generating receipt…');
  const body = new URLSearchParams();
  body.set('booking_id', bookingCode);
  fetch('/booking-receipt', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    body: body.toString(),
    credentials: 'same-origin'
  })
  .then(r => r.json().catch(() => ({})))
  .then(data => {
    if (data && data.status === 'success' && data.receipt_url) {
      window.open(data.receipt_url, '_blank', 'noopener');
      showBookingToast('Receipt ready');
    } else {
      showBookingToast((data && data.message) || 'Could not generate receipt.');
    }
  })
  .catch(() => showBookingToast('Error generating receipt.'));
}
</script>
</main>
<?= $this->element('footer', ['skin' => 'skin-light-footer']) ?>
