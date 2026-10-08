<script>
/* Card tabs + search + sort (mirrors the mobile app). */
let bkTab = 'upcoming';
function bkTabOf(card) { return card.getAttribute('data-tab') || 'upcoming'; }
function applyBkFilters() {
  const q = (document.getElementById('bkSearch')?.value || '').trim().toLowerCase();
  const sort = document.getElementById('bkSort')?.value || 'soon';
  const wrap = document.getElementById('bkCards');
  if (!wrap) return;
  const cards = Array.from(wrap.querySelectorAll('.cds-bk-card'));
  const visible = [];
  cards.forEach(card => {
    const okTab = bkTabOf(card) === bkTab;
    const hay = (card.getAttribute('data-search') || '');
    const okSearch = q === '' || hay.indexOf(q) !== -1;
    const show = okTab && okSearch;
    card.style.display = show ? '' : 'none';
    if (show) visible.push(card);
  });
  visible.sort((a, b) => {
    const da = a.getAttribute('data-checkin') || '';
    const db = b.getAttribute('data-checkin') || '';
    if (!da && !db) return 0;
    if (!da) return 1;
    if (!db) return -1;
    return sort === 'soon' ? (da < db ? -1 : (da > db ? 1 : 0)) : (da > db ? -1 : (da < db ? 1 : 0));
  });
  visible.forEach(card => wrap.appendChild(card));
  const empty = document.getElementById('bkEmpty');
  const title = document.getElementById('bkEmptyTitle');
  const sub = document.getElementById('bkEmptySub');
  if (empty) {
    const showEmpty = visible.length === 0;
    empty.style.display = showEmpty ? '' : 'none';
    if (showEmpty && title && sub) {
      if (q !== '') {
        title.innerText = 'No matches for "' + q + '"';
        sub.innerText = 'Try a booking code, lodge name, or city.';
      } else if (bkTab === 'upcoming') {
        title.innerText = 'No upcoming stays';
        sub.innerText = 'Your confirmed reservations will appear here.';
      } else if (bkTab === 'completed') {
        title.innerText = 'No completed stays yet';
        sub.innerText = 'Finished stays will appear here.';
      } else {
        title.innerText = 'No cancelled stays';
        sub.innerText = 'Cancelled reservations will appear here.';
      }
    }
  }
}
document.querySelectorAll('.cds-bk-tab').forEach(btn => {
  btn.addEventListener('click', () => {
    bkTab = btn.getAttribute('data-bk-tab') || 'upcoming';
    document.querySelectorAll('.cds-bk-tab').forEach(b => {
      const on = b === btn;
      b.classList.toggle('is-active', on);
      b.setAttribute('aria-selected', on ? 'true' : 'false');
    });
    applyBkFilters();
  });
});
document.getElementById('bkSearch')?.addEventListener('input', applyBkFilters);
document.getElementById('bkSort')?.addEventListener('change', applyBkFilters);
document.addEventListener('DOMContentLoaded', applyBkFilters);
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
