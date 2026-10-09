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
      showBookingToast(data.message || 'Booking cancelled. The property has been notified.');
      const card = document.getElementById('bk_card_' + bookingId);
      if (card) {
        card.setAttribute('data-tab', 'cancelled');
        const badge = card.querySelector('.bk-status-badge');
        if (badge) { badge.className = 'cds-tag cds-t-cancelled bk-status-badge'; badge.innerText = 'Cancelled'; }
        const actions = card.querySelector('.cds-bk-actions');
        if (actions) {
          actions.innerHTML = '<a class="cds-btn" href="<?= $this->Url->build('/hotel-list-01') ?>">Book again</a>';
        }
        applyBkFilters();
      } else {
        setTimeout(() => { window.location.href = '<?= $this->Url->build('/my-booking') ?>'; }, 1400);
      }
    } else {
      showBookingToast(data.message || 'Could not cancel booking');
    }
  } catch (e) {
    showBookingToast('Network error cancelling booking');
  }
}

/* Real date change (quote -> confirm -> apply), same engine as the app. */
let rsCode = '';
function openReschedule(code, currentDates) {
  rsCode = code || '';
  if (!rsCode) return;
  document.getElementById('rsCurrent').innerText = 'Current: ' + (currentDates || '');
  const today = new Date().toISOString().slice(0, 10);
  const inEl = document.getElementById('rsIn');
  const outEl = document.getElementById('rsOut');
  inEl.min = today; outEl.min = today; inEl.value = ''; outEl.value = '';
  document.getElementById('rsQuote').innerHTML = '';
  const go = document.getElementById('rsGoBtn');
  go.innerText = 'Check price';
  go.onclick = quoteReschedule;
  document.getElementById('rsModal').style.display = 'flex';
}
function closeReschedule() {
  document.getElementById('rsModal').style.display = 'none';
}
function rsMoney(v) {
  const n = Number(v || 0);
  return 'TSh ' + n.toLocaleString('en-US', { maximumFractionDigits: 0 });
}
async function quoteReschedule() {
  const inV = document.getElementById('rsIn').value;
  const outV = document.getElementById('rsOut').value;
  if (!inV || !outV || outV <= inV) {
    showBookingToast('Choose valid new dates first.');
    return;
  }
  const go = document.getElementById('rsGoBtn');
  const goHtml = go.innerText;
  go.innerText = 'Checking…';
  try {
    const res = await fetch('<?= $this->Url->build('/my-booking/reschedule/quote') ?>', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-Token': document.querySelector('meta[name="csrfToken"]')?.content || '' },
      body: JSON.stringify({ booking_id: rsCode, check_in: inV, check_out: outV })
    });
    const data = await res.json().catch(() => ({}));
    const box = document.getElementById('rsQuote');
    if (res.ok && data && data.valid) {
      const diff = Number(data.diff || 0);
      const bal = Number(data.balance_due || 0);
      const diffLine = diff > 0
        ? '+' + rsMoney(Math.abs(diff)) + ' extra' + (bal > 0 ? ' — payable at the property' : '')
        : (diff < 0 ? '−' + rsMoney(Math.abs(diff)) + ' (refunds via support)' : 'No price change');
      const safe = v => String(v ?? '').replace(/[<>&"]/g, '');
      box.innerHTML = '<div class="cds-bk-quote">'
        + '<div class="cds-bk-qrow"><span>New total</span><b>' + rsMoney(data.new_total) + '</b></div>'
        + '<div class="cds-bk-qrow"><span>Nights</span><b>' + safe(data.nights) + '</b></div>'
        + '<div class="cds-bk-qrow"><span>Difference</span><b>' + diffLine + '</b></div>'
        + '<p class="cds-bk-qmsg">' + safe(data.message) + '</p></div>';
      go.innerText = 'Move booking';
      go.onclick = applyReschedule;
    } else {
      box.innerHTML = '<div class="cds-bk-quote bad">' + String((data && data.message) || 'Those dates are unavailable for this room.').replace(/[<>&"]/g, '') + '</div>';
      showBookingToast((data && data.message) || 'Dates unavailable');
    }
  } catch (e) {
    showBookingToast('Network error checking dates');
  } finally {
    if (document.getElementById('rsGoBtn').innerText === 'Checking…') go.innerText = goHtml;
  }
}
async function applyReschedule() {
  const inV = document.getElementById('rsIn').value;
  const outV = document.getElementById('rsOut').value;
  const go = document.getElementById('rsGoBtn');
  go.innerText = 'Moving…';
  try {
    const res = await fetch('<?= $this->Url->build('/my-booking/reschedule/apply') ?>', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-Token': document.querySelector('meta[name="csrfToken"]')?.content || '' },
      body: JSON.stringify({ booking_id: rsCode, check_in: inV, check_out: outV })
    });
    const data = await res.json().catch(() => ({}));
    if (res.ok && data && data.status === 'success') {
      closeReschedule();
      showBookingToast(data.message || 'Booking moved to the new dates.');
      setTimeout(() => window.location.reload(), 1400);
    } else {
      showBookingToast((data && data.message) || 'Could not move the booking.');
      go.innerText = 'Move booking';
    }
  } catch (e) {
    showBookingToast('Network error moving booking');
    go.innerText = 'Move booking';
  }
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
<style>
#rsModal input[type="date"]{width:100%;background:#f4f4f4;border:none;border-bottom:1px solid #8d8d8d;border-radius:0;height:44px;padding:0 12px;font-size:14px;color:#161616}
#rsModal input[type="date"]:focus{outline:2px solid var(--cds-focus);outline-offset:-2px}
.cds-bk-quote{border:1px solid #e0e0e0;padding:12px 14px;margin-top:12px}
.cds-bk-quote.bad{border-left:4px solid #da1e28;font-size:13.5px;color:#161616}
.cds-bk-qrow{display:flex;justify-content:space-between;gap:12px;padding:4px 0;font-size:13.5px}
.cds-bk-qrow span{color:#6f6f6f}
.cds-bk-qrow b{color:#161616}
.cds-bk-qmsg{font-size:12.5px;color:#525252;margin:8px 0 0;line-height:1.5}
</style>
<div id="rsModal" style="display:none;position:fixed;inset:0;z-index:10000;background:rgba(0,0,0,.5);align-items:center;justify-content:center;padding:16px" role="dialog" aria-modal="true" aria-label="Change booking dates">
  <div style="background:#fff;max-width:480px;width:100%;padding:24px">
    <p class="cds-sec-label">Bookings</p>
    <h3 class="cds-sec-title" style="font-size:20px">Change dates</h3>
    <p id="rsCurrent" style="font-size:13px;color:#525252;margin:0 0 12px"></p>
    <div class="cds-grid2">
      <div class="cds-field"><label for="rsIn">New check-in</label><input type="date" id="rsIn"></div>
      <div class="cds-field"><label for="rsOut">New check-out</label><input type="date" id="rsOut"></div>
    </div>
    <div id="rsQuote"></div>
    <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:16px">
      <button type="button" class="cds-btn-ghost" onclick="closeReschedule()">Back</button>
      <button type="button" class="cds-btn" id="rsGoBtn" onclick="quoteReschedule()">Check price</button>
    </div>
  </div>
</div>
