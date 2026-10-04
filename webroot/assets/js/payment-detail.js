/** fastnetstays.com - Payments & Refunds (live records only, never fabricated) */
(function () {
    'use strict';

    if (!localStorage.getItem('auth_token') && !localStorage.getItem('token')) {
        window.location.href = '/login?redirect=' + encodeURIComponent('/payment-detail');
        return;
    }

    const apiUrl = p => (typeof window.API_URL === 'function') ? window.API_URL(p) : 'http://127.0.0.1:8000' + p;
    const getToken = () => { try { return localStorage.getItem('auth_token') || localStorage.getItem('token') || ''; } catch (e) { return ''; } };
    const authHdr = () => ({ 'Authorization': 'Bearer ' + getToken(), 'Accept': 'application/json', 'Content-Type': 'application/json' });

    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c]));

    function showPmAlert(msg, isSuccess) {
        const el = document.getElementById('payment-alert');
        if (!el) return;
        el.style.display = 'block';
        el.innerHTML = '<div class="cds-alert ' + (isSuccess ? 'cds-alert-ok' : 'cds-alert-err') + '">' + esc(msg) + '</div>';
        setTimeout(() => { el.style.display = 'none'; }, 5000);
    }

    function logoFor(type) {
        const t = (type || '').toLowerCase();
        if (t === 'airtel') return '/assets/img/airtel-logo.png';
        if (t === 'tigo') return '/assets/img/tigo-pesa-logo.jpg';
        if (t === 'halotel' || t === 'halopesa') return '/assets/img/halotel-logo.jpg';
        return '/assets/img/vodacom-logo.png';
    }

    function networkName(type) {
        const t = (type || '').toLowerCase();
        if (t === 'airtel') return 'Airtel Money';
        if (t === 'tigo') return 'Tigo Pesa';
        if (t === 'halotel' || t === 'halopesa') return 'HaloPesa';
        return 'M-Pesa';
    }

    // Privacy: show network + masked number only (e.g. 255 712 ••• 678).
    function maskNumber(raw) {
        const d = String(raw || '').replace(/\D/g, '');
        if (d.length < 7) return '•••';
        return d.slice(0, 3) + ' ' + d.slice(3, 6) + ' ••• ' + d.slice(-3);
    }

    function payBadge(paymentStatus, bookingStatus) {
        const s = String(paymentStatus || bookingStatus || 'pending').toLowerCase();
        const cls = {
            paid: 'cds-b-paid', successful: 'cds-b-paid', completed: 'cds-b-paid', confirmed: 'cds-b-paid',
            pending: 'cds-b-pending',
            failed: 'cds-b-failed', cancelled: 'cds-b-cancelled', canceled: 'cds-b-cancelled',
            refunded: 'cds-b-refunded',
            amount_mismatch: 'cds-b-review', review: 'cds-b-review'
        }[s] || 'cds-b-pending';
        const label = s === 'amount_mismatch' ? 'Under review' : s.replace(/_/g, ' ');
        return '<span class="cds-badge ' + cls + '">' + esc(label) + '</span>';
    }

    function fmtDate(raw) {
        if (!raw) return '—';
        const d = new Date(raw);
        return isNaN(d) ? '—' : d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    function emptyRow(msg, sub) {
        return '<tr><td colspan="5"><div class="cds-empty"><i class="fa-regular fa-file-lines" aria-hidden="true"></i><b>' + esc(msg) + '</b><span>' + esc(sub) + '</span></div></td></tr>';
    }

    async function loadPaymentMethods() {
        const list = document.getElementById('payment-methods-list');
        if (!list) return;
        let methods = [];
        try {
            const res = await fetch(apiUrl('/api/payment-methods'), { headers: authHdr() });
            if (res.ok) {
                const data = await res.json().catch(() => ({}));
                const m = data.data || data;
                if (Array.isArray(m)) methods = m;
            }
        } catch (e) { methods = []; }

        if (methods.length === 0) {
            list.innerHTML = '<div class="cds-empty"><i class="fa-solid fa-wallet" aria-hidden="true"></i><b>No saved numbers</b><span>You pay with mobile money at checkout — nothing needs saving.</span><div><button type="button" class="cds-pm-add" data-bs-toggle="modal" data-bs-target="#addcard"><i class="fa-solid fa-plus"></i>Add number</button></div></div>';
            return;
        }

        let html = '';
        methods.forEach(pm => {
            const type = pm.provider || pm.type || '';
            const phone = pm.phone_number || pm.phone || '';
            html += '<div class="cds-pm-row" id="pm-card-' + esc(pm.id) + '">'
                + '<span class="cds-pm-logo"><img src="' + logoFor(type) + '" alt="" onerror="this.style.display=\'none\'"></span>'
                + '<span><span class="cds-pm-name">' + esc(pm.label || networkName(type)) + '</span><br>'
                + '<span class="cds-pm-num">' + esc(maskNumber(phone)) + (pm.name ? ' · ' + esc(pm.name) : '') + '</span></span>'
                + '<button type="button" class="cds-pm-del" data-pm-del="' + esc(pm.id) + '" title="Delete number" aria-label="Delete number"><i class="fa-solid fa-trash-can"></i></button>'
                + '</div>';
        });
        html += '<div><button type="button" class="cds-pm-add" data-bs-toggle="modal" data-bs-target="#addcard"><i class="fa-solid fa-plus"></i>Add number</button></div>';
        list.innerHTML = html;
        list.querySelectorAll('[data-pm-del]').forEach(btn => btn.addEventListener('click', () => deletePaymentMethod(btn.getAttribute('data-pm-del'))));
    }

    async function loadBillingHistory() {
        const tbody = document.getElementById('billing-history-rows');
        const rbody = document.getElementById('refund-history-rows');
        let bookings = [];
        try {
            const res = await fetch(apiUrl('/api/bookings'), { headers: authHdr() });
            if (res.ok) {
                const data = await res.json().catch(() => ({}));
                const b = data.data || data.bookings || data;
                if (Array.isArray(b)) bookings = b;
            }
        } catch (e) { bookings = []; }

        // Newest first by creation/check-in date.
        bookings.sort((a, b) => new Date(b.created_at || b.check_in || 0) - new Date(a.created_at || a.check_in || 0));

        if (!tbody) return;
        if (bookings.length === 0) {
            tbody.innerHTML = emptyRow('No transactions yet', 'Your bookings and payments will appear here.');
            if (rbody) rbody.innerHTML = emptyRow('No refunds', 'Approved refunds to your mobile-money number will appear here.');
            return;
        }

        let html = '';
        const refunds = [];
        bookings.forEach((b, i) => {
            const ref = b.booking_code || b.reference || (b.id ? String(b.id) : '—');
            const ps = String(b.payment_status || '').toLowerCase();
            if (ps === 'refunded') refunds.push(b);
            const amount = Number(b.total_price ?? b.total_amount ?? 0).toLocaleString('en-US');
            html += '<tr><td>' + String(i + 1).padStart(2, '0') + '</td>'
                + '<td><strong>' + esc(ref) + '</strong></td>'
                + '<td class="hide-sm">' + esc(fmtDate(b.created_at || b.check_in)) + '</td>'
                + '<td>' + payBadge(b.payment_status, b.status || b.booking_status) + '</td>'
                + '<td style="text-align:right;font-weight:600;white-space:nowrap">TSh ' + esc(amount) + '</td></tr>';
        });
        tbody.innerHTML = html;

        if (rbody) {
            if (refunds.length === 0) {
                rbody.innerHTML = emptyRow('No refunds', 'Approved refunds to your mobile-money number will appear here.');
            } else {
                let rh = '';
                refunds.forEach((b, i) => {
                    const ref = b.booking_code || b.reference || (b.id ? String(b.id) : '—');
                    const amount = Number(b.total_price ?? b.total_amount ?? 0).toLocaleString('en-US');
                    rh += '<tr><td>' + String(i + 1).padStart(2, '0') + '</td>'
                        + '<td><strong>' + esc(ref) + '</strong></td>'
                        + '<td class="hide-sm">' + esc(fmtDate(b.updated_at || b.created_at || b.check_in)) + '</td>'
                        + '<td>' + payBadge('refunded') + '</td>'
                        + '<td style="text-align:right;font-weight:600;white-space:nowrap">TSh ' + esc(amount) + '</td></tr>';
                });
                rbody.innerHTML = rh;
            }
        }
    }

    async function deletePaymentMethod(id) {
        if (!id) return;
        if (typeof window.fnsConfirm === 'function') {
            if (!await window.fnsConfirm('Remove this saved number?')) return;
        } else if (!window.confirm('Remove this saved number?')) return;
        try {
            const res = await fetch(apiUrl('/api/payment-methods/' + encodeURIComponent(id)), {
                method: 'DELETE', headers: authHdr()
            });
            if (res.ok) {
                showPmAlert('Number removed.', true);
                loadPaymentMethods();
            } else {
                // Never fake success: if the backend did not delete it, say so
                // and keep the row on screen.
                const data = await res.json().catch(() => ({}));
                showPmAlert(data.message || 'Could not remove the number. Please try again.', false);
            }
        } catch (e) {
            showPmAlert('Could not reach the server. The number was not removed.', false);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const stored = (() => { try { return localStorage.getItem('user'); } catch (e) { return null; } });
        if (stored) {
            try {
                const user = JSON.parse(stored);
                const name = user.name || (((user.first_name || '') + ' ' + (user.last_name || '')).trim()) || 'Traveler';
                const nEl = document.getElementById('sidebar-name');
                const eEl = document.getElementById('sidebar-email');
                const aEl = document.getElementById('sidebar-avatar');
                if (nEl) nEl.textContent = name;
                if (eEl) eEl.textContent = user.email || '';
                if (aEl) aEl.textContent = (name[0] || '?').toUpperCase();
            } catch (e) { /* corrupted local profile — live data below still loads */ }
        }
        loadPaymentMethods();
        loadBillingHistory();

        document.getElementById('add-payment-form')?.addEventListener('submit', async function (e) {
            e.preventDefault();
            const type = document.getElementById('pm-type').value;
            const phone = document.getElementById('mobile-phone').value.trim();
            const name = document.getElementById('mobile-name').value.trim();
            const btn = document.getElementById('pm-add-btn');
            const digits = phone.replace(/\D/g, '');
            const full = digits.startsWith('255') ? digits : ('255' + digits.replace(/^0+/, ''));
            if (!/^255\d{9}$/.test(full)) { showPmAlert('Enter a valid Tanzanian number, e.g. 712 345 678.', false); return; }
            const labels = { vodacom: 'M-Pesa', tigo: 'Tigo Pesa', airtel: 'Airtel Money', halotel: 'HaloPesa' };
            btn.disabled = true;
            btn.textContent = 'Saving…';
            try {
                const res = await fetch(apiUrl('/api/payment-methods'), {
                    method: 'POST', headers: authHdr(),
                    body: JSON.stringify({ type: type, provider: type, phone: full, name: name, label: (labels[type] || type) + ' (' + full + ')' })
                });
                const data = await res.json().catch(() => ({}));
                if (res.ok || res.status === 201) {
                    const modalEl = document.getElementById('addcard');
                    const inst = (window.bootstrap && window.bootstrap.Modal && modalEl) ? (window.bootstrap.Modal.getInstance(modalEl) || new window.bootstrap.Modal(modalEl)) : null;
                    if (inst) inst.hide();
                    showPmAlert('Number saved.', true);
                    loadPaymentMethods();
                    this.reset();
                } else {
                    showPmAlert(data.message || 'Could not save the number.', false);
                }
            } catch (err) {
                showPmAlert('Could not reach the server. The number was not saved.', false);
            } finally {
                btn.disabled = false;
                btn.textContent = 'Save number';
            }
        });
    });
})();
