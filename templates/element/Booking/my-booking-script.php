<script>
/* FastNet Stays - My Bookings Controller (Real Working Cancel & Tabs) */
let bkTab = 'upcoming';
let bkSoonestFirst = true;
let pendingCancelCode = null;

function bkTabOf(card) {
  return card.getAttribute('data-tab') || 'upcoming';
}

function updateTabCounts() {
  const cards = Array.from(document.querySelectorAll('.cds-bk-card'));
  const counts = { upcoming: 0, completed: 0, cancelled: 0 };
  cards.forEach(card => {
    const t = bkTabOf(card);
    if (counts[t] !== undefined) counts[t]++;
  });
  document.querySelectorAll('.cds-bk-tab').forEach(btn => {
    const t = btn.getAttribute('data-bk-tab');
    if (t && counts[t] !== undefined) {
      const label = t.charAt(0).toUpperCase() + t.slice(1);
      btn.innerText = `${label} (${counts[t]})`;
    }
  });
}

function applyBkFilters() {
  const searchInput = document.getElementById('bkSearch');
  const clearBtn = document.getElementById('bkClearSearch');
  const q = (searchInput?.value || '').trim().toLowerCase();
  
  if (clearBtn) {
    clearBtn.style.display = q !== '' ? 'flex' : 'none';
  }

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
    return bkSoonestFirst ? (da < db ? -1 : (da > db ? 1 : 0)) : (da > db ? -1 : (da < db ? 1 : 0));
  });

  visible.forEach(card => wrap.appendChild(card));

  const empty = document.getElementById('bkEmpty');
  const title = document.getElementById('bkEmptyTitle');
  const sub = document.getElementById('bkEmptySub');
  const icon = document.getElementById('bkEmptyIcon');

  if (empty) {
    const showEmpty = visible.length === 0;
    empty.style.display = showEmpty ? '' : 'none';
    if (showEmpty && title && sub) {
      if (q !== '') {
        if (icon) icon.className = 'fa-solid fa-magnifying-glass';
        title.innerText = 'No matches for "' + q + '"';
        sub.innerText = 'Try a booking code, lodge name, or city.';
      } else if (bkTab === 'upcoming') {
        if (icon) icon.className = 'fa-solid fa-suitcase';
        title.innerText = 'No upcoming stays';
        sub.innerText = 'Your confirmed reservations will appear here.';
      } else if (bkTab === 'completed') {
        if (icon) icon.className = 'fa-solid fa-suitcase';
        title.innerText = 'No completed stays yet';
        sub.innerText = 'Finished stays will appear here.';
      } else {
        if (icon) icon.className = 'fa-regular fa-circle-xmark';
        title.innerText = 'No cancelled stays';
        sub.innerText = 'Cancelled reservations will appear here.';
      }
    }
  }
}

// Tab Switching
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

// Search input and clear button
const bkSearchEl = document.getElementById('bkSearch');
if (bkSearchEl) {
  bkSearchEl.addEventListener('input', applyBkFilters);
}

const bkClearBtn = document.getElementById('bkClearSearch');
if (bkClearBtn) {
  bkClearBtn.addEventListener('click', () => {
    if (bkSearchEl) {
      bkSearchEl.value = '';
      bkSearchEl.focus();
    }
    applyBkFilters();
  });
}

// Sort button toggle (Soonest First <-> Latest First)
const bkSortBtn = document.getElementById('bkSortBtn');
if (bkSortBtn) {
  bkSortBtn.addEventListener('click', () => {
    bkSoonestFirst = !bkSoonestFirst;
    const sortIcon = document.getElementById('bkSortIcon');
    if (sortIcon) {
      sortIcon.className = bkSoonestFirst ? 'fa-solid fa-arrow-up' : 'fa-solid fa-arrow-down';
    }
    bkSortBtn.title = bkSoonestFirst
      ? 'Soonest first — tap for latest first'
      : 'Latest first — tap for soonest first';
    applyBkFilters();
  });
}

document.addEventListener('DOMContentLoaded', () => {
  applyBkFilters();
  updateTabCounts();
});

// Cancel Modal Dialog Logic
let pendingCancelEmail = '';

function openCancelModal(bookingCode, guestEmail) {
  pendingCancelCode = bookingCode;
  pendingCancelEmail = guestEmail || '';
  const modal = document.getElementById('bkCancelModal');
  const desc = document.getElementById('bkCancelDesc');
  if (desc) {
    desc.innerText = 'Booking ' + (bookingCode || '') + ' will be cancelled. This action cannot be undone.';
  }
  if (modal) {
    modal.classList.add('is-open');
  }
}

function cancelBookingAction(bookingCode, guestEmail) {
  openCancelModal(bookingCode, guestEmail);
}

function closeCancelModal() {
  pendingCancelCode = null;
  pendingCancelEmail = '';
  const modal = document.getElementById('bkCancelModal');
  if (modal) {
    modal.classList.remove('is-open');
  }
}

// Close modal when clicking on overlay background
const cancelModalOverlay = document.getElementById('bkCancelModal');
if (cancelModalOverlay) {
  cancelModalOverlay.addEventListener('click', (e) => {
    if (e.target === cancelModalOverlay) {
      closeCancelModal();
    }
  });
}

const confirmCancelBtn = document.getElementById('bkConfirmCancelBtn');
if (confirmCancelBtn) {
  confirmCancelBtn.addEventListener('click', async () => {
    const bookingCode = pendingCancelCode;
    const guestEmail = pendingCancelEmail;
    closeCancelModal();
    if (!bookingCode) return;

    showBookingToast('Cancelling stay…');
    const csrf = document.querySelector('meta[name="csrfToken"]')?.content || '';
    try {
      const res = await fetch('<?= $this->Url->build('/my-booking/cancel') ?>', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-Token': csrf
        },
        body: JSON.stringify({ booking_id: bookingCode, email: guestEmail })
      });
      const data = await res.json().catch(() => ({}));
      if (res.ok && data.status !== 'error') {
        showBookingToast(data.message || 'Stay cancelled. The property has been notified.');
        const card = document.getElementById('bk_card_' + bookingCode) 
          || document.querySelector('[data-search*="' + bookingCode.toLowerCase() + '"]')
          || document.querySelector('.cds-bk-card');
        if (card) {
          card.setAttribute('data-tab', 'cancelled');
          const badge = card.querySelector('.bk-status-badge');
          if (badge) {
            badge.className = 'cds-tag cds-t-cancelled bk-status-badge';
            badge.innerText = 'CANCELLED';
          }
          const actions = card.querySelector('.cds-bk-actions');
          if (actions) {
            actions.innerHTML = '<a href="<?= $this->Url->build('/hotel-list-01') ?>" class="cds-btn cds-btn-primary"><i class="fa-solid fa-rotate-right" aria-hidden="true"></i> Book again</a>';
          }
          updateTabCounts();
          // Switch to cancelled tab like mobile app does
          const cancelledTabBtn = document.querySelector('.cds-bk-tab[data-bk-tab="cancelled"]');
          if (cancelledTabBtn) {
            cancelledTabBtn.click();
          } else {
            applyBkFilters();
          }
        } else {
          setTimeout(() => { window.location.href = '<?= $this->Url->build('/my-booking') ?>'; }, 1200);
        }
      } else {
        showBookingToast(data.message || 'Could not cancel booking. Please try again.');
      }
    } catch (e) {
      showBookingToast('Network error cancelling booking.');
    }
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
  if (typeof openReceiptModal === 'function') {
    openReceiptModal();
    return;
  }
  window.location.href = '/bookingpage-success?booking_id=' + encodeURIComponent(bookingCode) + '&view=receipt';
}
</script>

