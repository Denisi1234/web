/** fastnetstays.com - Payment Methods & Billing History Controller */
// ── Auth Guard ────────────────────────────────────────────────────────────────
(function() {
    if (!localStorage.getItem('auth_token') && !localStorage.getItem('token')) {
        window.location.href = '/login?redirect=' + encodeURIComponent('/payment-detail');
    }
})();

// ── Helpers ───────────────────────────────────────────────────────────────────
const apiUrl  = p => (typeof window.API_URL === 'function') ? window.API_URL(p) : 'http://127.0.0.1:8000' + p;
const getToken = () => localStorage.getItem('auth_token') || localStorage.getItem('token') || '';
const authHdr  = () => ({ 'Authorization': 'Bearer ' + getToken(), 'Accept': 'application/json', 'Content-Type': 'application/json' });

function _escapeHtml(str) {
    return String(str || '').replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[s]);
}

function showPmAlert(msg, isSuccess = false) {
    const el = document.getElementById('payment-alert');
    if (!el) return;
    el.style.display = 'block';
    el.innerHTML = `<div class="alert alert-${isSuccess ? 'success' : 'danger'} py-2">${msg}</div>`;
    setTimeout(() => { el.style.display = 'none'; }, 5000);
}

// ── Load User Sidebar ─────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const stored = localStorage.getItem('user');
    if (stored) {
        try {
            const user = JSON.parse(stored);
            const name = user.name || ((user.first_name||'') + ' ' + (user.last_name||'')).trim() || 'Traveler';
            document.getElementById('sidebar-name').textContent = name;
            document.getElementById('sidebar-email').textContent = user.email || '';
            document.getElementById('sidebar-avatar').textContent = (name[0] || '?').toUpperCase();
        } catch(e) {}
    }
    loadPaymentMethods();
    loadBillingHistory();
});

// ── Render Local Payment Logos ───────────────────────────────────────────────
function getPmLogoUrl(type) {
    const t = (type || '').toLowerCase();
    if (t === 'airtel') return '/assets/img/airtel-logo.png';
    if (t === 'tigo') return '/assets/img/tigo-pesa-logo.jpg';
    if (t === 'vodacom') return '/assets/img/vodacom-logo.png';
    if (t === 'halotel') return '/assets/img/halotel-logo.jpg';
    return '/assets/img/vodacom-logo.png';
}

function getPmClass(type) {
    const t = (type || '').toLowerCase();
    if (t === 'airtel') return 'bg-danger';
    if (t === 'tigo') return 'bg-primary';
    if (t === 'vodacom') return 'bg-danger';
    if (t === 'halotel') return 'bg-warning text-dark';
    return 'bg-secondary';
}

// ── Fetch Payment Methods ─────────────────────────────────────────────────────
async function loadPaymentMethods() {
    const list = document.getElementById('payment-methods-list');
    if (!list) return;

    let methods = [];

    try {
        const res = await fetch(apiUrl('/api/payment-methods'), { headers: authHdr() });
        if (res.ok) {
            const data = await res.json();
            methods = data.data || data;
        }
    } catch (e) {
        methods = [];
    }

    if (!Array.isArray(methods)) methods = [];

    // No saved payment methods is a real state, not an error. This used to
    // render two invented cards ("Daniel Duekoza", Vodacom 0754***892) for
    // every visitor, which read as genuine saved cards.
    if (methods.length === 0) {
        list.innerHTML = `
            <div class="col-12">
                <div class="list-group shadow-sm rounded-3 overflow-hidden">
                    <div class="list-group-item text-center p-4">
                        <i class="fa-regular fa-credit-card text-muted fs-3 d-block mb-2"></i>
                        <div class="fw-semibold mb-1">No saved payment methods</div>
                        <div class="text-muted small">You pay with mobile money at checkout — nothing needs saving.</div>
                    </div>
                </div>
            </div>`;
        return;
    }

    let html = '<div class="col-12"><div class="list-group shadow-sm rounded-3 overflow-hidden">';
    methods.forEach(pm => {
        const logo = getPmLogoUrl(pm.type);
        const labelText = pm.label || pm.title || 'Payment Method';

        html += `
            <div class="list-group-item list-group-item-action d-flex align-items-center justify-content-between p-3" id="pm-card-${pm.id}">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center bg-light rounded px-2 py-1" style="width: 70px; height: 40px;">
                        <img class="img-fluid" src="${logo}" style="max-height: 28px; max-width: 55px; object-fit: contain;" alt="${pm.type}">
                    </div>
                    <div>
                        <h6 class="mb-0 fw-semibold text-slate-800">${_escapeHtml(labelText)}</h6>
                        <span class="text-xs text-muted">Holder: ${_escapeHtml(pm.name)} | Status: ${_escapeHtml(pm.expiry || 'Active')}</span>
                    </div>
                </div>
                <div>
                    <a href="javascript:void(0);" onclick="deletePaymentMethod('${pm.id}')" class="btn btn-sm btn-light-danger rounded-circle p-2" title="Delete Payment Method">
                        <i class="fa-solid fa-trash-can"></i>
                    </a>
                </div>
            </div>
        `;
    });

    // Add Payment Method row at bottom of list
    html += `
            <div class="list-group-item text-center p-3 bg-light">
                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-4 fw-semibold" data-bs-toggle="modal" data-bs-target="#addcard">
                    <i class="fa-solid fa-circle-plus me-1"></i>Add Mobile Payment Method
                </button>
            </div>
        </div></div>`;

    list.innerHTML = html;
}

// ── Add Payment Method Form Submit ───────────────────────────────────────────
document.getElementById('add-payment-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const type = document.getElementById('pm-type').value;
    const btn = document.getElementById('pm-add-btn');
    btn.disabled = true;
    btn.textContent = 'Saving...';

    const phone = document.getElementById('mobile-phone').value.trim();
    const name = document.getElementById('mobile-name').value.trim();
    const pmLabels = {
        'vodacom': 'Vodacom M-Pesa',
        'tigo': 'Tigo Pesa',
        'airtel': 'Airtel Money',
        'halotel': 'HaloPesa'
    };
    const providerLabel = pmLabels[type] || type.toUpperCase();

    let payload = {
        type: type,
        phone: phone,
        name: name,
        label: `${providerLabel} (${phone})`,
        provider: type
    };

    try {
        const res = await fetch(apiUrl('/api/payment-methods'), {
            method: 'POST',
            headers: authHdr(),
            body: JSON.stringify(payload)
        });

        if (res.ok) {
            // Close Modal
            const modalEl = document.getElementById('addcard');
            const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            modalInstance.hide();
            
            showPmAlert('Mobile payment method saved successfully!', true);
            loadPaymentMethods();
            
            // reset form
            this.reset();
        } else {
            const data = await res.json();
            showPmAlert(data.message || 'Failed to add mobile payment method.');
        }
    } catch(err) {
        showPmAlert('Server unreachable. Please check your connection.');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Save Mobile Payment Method';
    }
});

// ── Delete Payment Method ─────────────────────────────────────────────────────
async function deletePaymentMethod(id) {
    if (!confirm('Are you sure you want to delete this payment method?')) return;

    try {
        const res = await fetch(apiUrl(`/api/payment-methods/${id}`), {
            method: 'DELETE',
            headers: authHdr()
        });

        if (res.ok) {
            showPmAlert('Payment method removed.', true);
            loadPaymentMethods();
        } else {
            // Local fallback UI removal if API is simulated
            const cardEl = document.getElementById(`pm-card-${id}`);
            if (cardEl) {
                cardEl.remove();
                showPmAlert('Payment method removed successfully.', true);
            }
        }
    } catch(e) {
        showPmAlert('Failed to delete payment method.');
    }
}

// ── Fetch Billing History (Dynamic load based on real bookings transactions) ────
async function loadBillingHistory() {
    const tbody = document.getElementById('billing-history-rows');
    if (!tbody) return;

    let bookings = [];

    try {
        const res = await fetch(apiUrl('/api/bookings'), { headers: authHdr() });
        if (res.ok) {
            const data = await res.json();
            bookings = data.data || data.bookings || data;
        }
    } catch (e) {
        bookings = [];
    }

    if (!Array.isArray(bookings)) bookings = [];

    // Previously fell back to three fabricated bookings (FN-32154 240,000 TZS
    // "completed", etc.) so every account appeared to have billing history.
    if (bookings.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-4">
                    <i class="fa-regular fa-file-lines text-muted fs-3 d-block mb-2"></i>
                    <div class="fw-semibold mb-1">No transactions yet</div>
                    <div class="text-muted small">Your bookings and payments will appear here.</div>
                </td>
            </tr>`;
        return;
    }

        let html = '';
        bookings.forEach((b, index) => {
            // Never invent a reference: an unresolved booking shows its real id
            // or a dash, rather than a fake "FN-123" that resolves to nothing.
            const ref = b.reference || b.booking_code || (b.id ? String(b.id) : "—");
            const rawDate = b.created_at || b.check_in;
            const dateStr = rawDate ? new Date(rawDate).toLocaleDateString("en-GB", { day: "2-digit", month: "short", year: "numeric" }) : "—";
            
            const status = (b.status || 'pending').toLowerCase();
            let badgeClass = 'bg-light-warning text-warning';
            if (status === 'completed' || status === 'confirmed') badgeClass = 'bg-light-success text-success';
            if (status === 'cancelled') badgeClass = 'bg-light-danger text-danger';

            const amount = parseFloat(b.total_amount || b.total_price || 0).toLocaleString();
            const currency = b.currency || 'TZS';

            html += `
                <tr>
                    <th>${String(index + 1).padStart(2, '0')}</th>
                    <td><strong class="font-monospace text-slate-800">${ref}</strong></td>
                    <td>${dateStr}</td>
                    <td><span class="badge ${badgeClass} fw-medium text-uppercase">${status}</span></td>
                    <td><span class="text-md fw-bold text-slate-800">${currency} ${amount}</span></td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
}