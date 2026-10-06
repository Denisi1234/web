/* FastNet app loader. Split for the 300-line cap — load app-loader-core.js first, then app-loader-dialog.js. */
(function (window, document) {

    /* ── Professional Confirm Modal ─────────────────────────────────────────
     * Replaces browser window.confirm() with a sleek, branded modal.
     * Returns a Promise<boolean> — resolves true (OK) or false (Cancel).
     */
    function fnsConfirm(msg) {
        return new Promise(function (resolve) {
            // Build or reuse overlay
            var overlay = document.getElementById('fns-confirm-overlay');
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.id = 'fns-confirm-overlay';
                overlay.setAttribute('role', 'dialog');
                overlay.setAttribute('aria-modal', 'true');
                overlay.setAttribute('aria-labelledby', 'fns-confirm-title');
                overlay.innerHTML =
                    '<div class="fns-confirm-card">' +
                      '<div class="fns-confirm-icon" aria-hidden="true">⚠</div>' +
                      '<p class="fns-confirm-title" id="fns-confirm-title">Are you sure?</p>' +
                      '<p class="fns-confirm-msg" id="fns-confirm-msg"></p>' +
                      '<div class="fns-confirm-actions">' +
                        '<button class="fns-confirm-cancel" id="fns-confirm-cancel" type="button">Cancel</button>' +
                        '<button class="fns-confirm-ok" id="fns-confirm-ok" type="button">Confirm</button>' +
                      '</div>' +
                    '</div>';
                document.body.appendChild(overlay);
            }

            var msgEl     = overlay.querySelector('#fns-confirm-msg');
            var okBtn     = overlay.querySelector('#fns-confirm-ok');
            var cancelBtn = overlay.querySelector('#fns-confirm-cancel');

            if (msgEl) msgEl.textContent = msg || 'This action cannot be undone.';

            function close(result) {
                overlay.classList.remove('visible');
                document.removeEventListener('keydown', keyHandler, true);
                // Wait for CSS fade-out, then hide completely
                setTimeout(function () {
                    overlay.style.display = 'none';
                    resolve(result);
                }, 200);
            }
            function keyHandler(e) {
                if (e.key === 'Escape') { e.preventDefault(); close(false); }
                if (e.key === 'Enter')  { e.preventDefault(); close(true); }
            }

            if (okBtn)     okBtn.onclick     = function () { close(true);  };
            if (cancelBtn) cancelBtn.onclick = function () { close(false); };
            overlay.onclick = function (e) { if (e.target === overlay) close(false); };
            document.addEventListener('keydown', keyHandler, true);

            // Show — set display:flex first, then rAF triggers CSS transition
            overlay.style.display = 'flex';
            requestAnimationFrame(function () {
                requestAnimationFrame(function () {
                    overlay.classList.add('visible');
                    setTimeout(function () { try { cancelBtn.focus(); } catch (e2) {} }, 80);
                });
            });
        });
    }
    window.fnsConfirm = fnsConfirm;
    var navTimer = null;

    function managedZone(el) {
        if (!el || !el.closest) return false;
        return !!el.closest('#gh_search_form, #fnsFiltersModal, #fns_mobile_sheet, [data-no-loader]');
    }
    function navShow() {
        var el = document.getElementById('fastnet-nav-loader');
        if (!el) return;
        el.classList.add('visible');
        el.setAttribute('aria-hidden', 'false');
    }
    function navHide() {
        if (navTimer) { clearTimeout(navTimer); navTimer = null; }
        var el = document.getElementById('fastnet-nav-loader');
        if (el) { el.classList.remove('visible'); el.setAttribute('aria-hidden', 'true'); }
    }
    function navSchedule() {
        if (navTimer) return;
        navTimer = setTimeout(navShow, 300); // Only shows if page takes > 300ms
    }

    // Hide on page restore (back/forward cache)
    window.addEventListener('pageshow', navHide);

    // Intercept internal navigation links
    document.addEventListener('click', function (e) {
        if (e.defaultPrevented ||
            (e.button !== undefined && e.button !== 0) ||
            e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

        var link = e.target.closest ? e.target.closest('a') : null;
        if (!link || managedZone(link)) return;

        const href   = link.getAttribute('href');
        const target = link.getAttribute('target');

        // Ignore: hash anchors, javascript:, new tabs, downloads, external protocols
        if (!href ||
            href.startsWith('#') ||
            href.startsWith('javascript:') ||
            target === '_blank' ||
            link.hasAttribute('download')) return;

        if (href.startsWith('/') || href.startsWith(window.location.origin)) {
            FastnetLoader.bar.start();
            // Stay-detail / booking journeys use the thin top bar + skeletons
            // only — the centered nav pill is suppressed there (it stacked
            // with page-level loaders and felt cheap on mobile taps).
            if (href.indexOf('/hotel-detail/') === -1 && href.indexOf('/booking-page') === -1) {
                navSchedule();
            }
        }
    });

    // Auto-progress on form submit (non-AJAX managed zones)
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (!form) return;
        if (form.hasAttribute('data-no-loader')) return;

        // If a form-level handler already preventDefault()ed, no native
        // navigation will follow this event — it is an AJAX submit (login,
        // OTP, signup...). Those handlers own their buttons and never clear
        // the global pill on failure, so starting the speculative loader here
        // strands "Loading…" on screen forever. Don't start it at all.
        if (e.defaultPrevented) return;

        FastnetLoader.bar.start();
        if (!managedZone(form)) navSchedule();

        // Safety net for native submits cancelled late (e.g. validation that
        // preventDefaults after an async check): release the bar instantly.
        setTimeout(function () {
            if (e.defaultPrevented) {
                FastnetLoader.bar.done();
                navHide();
            }
        }, 200);
    });
  window.navShow = navShow;
  window.navHide = navHide;
})(window, document);
