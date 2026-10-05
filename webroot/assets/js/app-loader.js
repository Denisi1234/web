/**
 * FastNet Stays — Universal App Loading & Background Task Engine (app-loader.js)
 *
 * The top progress bar now delegates to loading.js, which is the single
 * canonical loading controller. What remains here is the navigation overlay,
 * the confirm dialog and the background-task pills.
 * — Top bar: delegated to FastnetLoading (guaranteed teardown + watchdog)
 * — Nav loader: shown only after 300ms delay (instant clicks never flash)
 * — Confirm modal: sleek replacement for browser window.confirm()
 */
(function (window, document) {
    'use strict';

    // Prevent double initialization
    if (window.FastnetLoader) return;

    let progressTimer = null;
    let currentProgress = 0;
    let isLoaderVisible = false;
    const activeBgTasks = new Map();

    /* Fallback bar, used only if loading.js failed to load. Still clears its
     * interval in done() and self-limits, so it cannot strand the UI. */
    function legacyBarStart() {
        const el = getElements().topBar;
        if (!el) return;
        if (currentProgress > 0) {
            el.style.transition = 'none';
            el.style.width = '0%';
            currentProgress = 0;
        }
        requestAnimationFrame(() => {
            el.style.transition = 'width 0.18s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.22s ease';
            currentProgress = 12;
            el.style.width = currentProgress + '%';
            el.classList.add('active');
        });
        if (progressTimer) clearInterval(progressTimer);
        progressTimer = setInterval(() => {
            if (currentProgress < 82) {
                const increment = Math.max(1.5, (82 - currentProgress) * 0.12 + Math.random() * 3);
                currentProgress = Math.min(82, currentProgress + increment);
                if (el) el.style.width = currentProgress + '%';
            }
        }, 180);
        // Safety net: never leave the interval running past its budget.
        setTimeout(legacyBarDone, 20000);
    }

    function legacyBarDone() {
        const el = getElements().topBar;
        if (progressTimer) { clearInterval(progressTimer); progressTimer = null; }
        if (!el) return;
        el.style.transition = 'width 0.12s ease, opacity 0.25s ease 0.15s';
        el.style.width = '100%';
        setTimeout(() => {
            el.classList.remove('active');
            setTimeout(() => {
                el.style.transition = 'none';
                el.style.width = '0%';
                currentProgress = 0;
            }, 280);
        }, 150);
    }

    // DOM Elements Cache
    function getElements() {
        return {
            topBar:      document.getElementById('fastnet-top-progress'),
            modal:       document.getElementById('fastnet-app-loader'),
            modalTitle:  document.getElementById('fastnet-loader-title'),
            modalSub:    document.getElementById('fastnet-loader-subtext'),
            bgContainer: document.getElementById('fastnet-bg-loader')
        };
    }

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

    const FastnetLoader = {
        /**
         * Top-Bar Progress Controller.
         *
         * Delegates to FastnetLoading.bar, which is the single canonical
         * implementation. The previous local version started a 180ms interval
         * that was only ever cleared by bar.done(), and bar.done() was in turn
         * only reached on `window load` — so any form submit that failed left
         * the bar pinned with the interval still running.
         */
        bar: {
            start: function () {
                return window.FastnetLoading
                    ? window.FastnetLoading.bar.start()
                    : legacyBarStart();
            },
            set: function (percent) {
                if (window.FastnetLoading) return window.FastnetLoading.bar.set(percent);
                const el = getElements().topBar;
                if (!el) return;
                currentProgress = Math.max(0, Math.min(100, percent));
                el.style.width = currentProgress + '%';
                if (currentProgress > 0) el.classList.add('active');
            },
            inc: function (amount) {
                this.set(currentProgress + (amount || 10));
            },
            done: function () {
                return window.FastnetLoading ? window.FastnetLoading.bar.done() : legacyBarDone();
            },
            isActive: function () {
                return window.FastnetLoading
                    ? window.FastnetLoading.bar.isActive()
                    : false;
            }
        },

        /**
         * Full-Screen modal — DELETED. Bar-only alias (professional, instant).
         * Never locks scroll or shows a veil; heavy ops use bar + skeletons.
         * @param {Object|string} options - ignored, kept for call-site compat
         */
        show: function (options) {
            isLoaderVisible = true;
            this.bar.start();
        },

        /**
         * Update current modal text while loading
         */
        update: function (title, subtext) {
            const els = getElements();
            if (title   && els.modalTitle) els.modalTitle.textContent = title;
            if (subtext && els.modalSub)   els.modalSub.textContent   = subtext;
        },

        /**
         * Hide Full-Screen App Loading Modal + cancel any pill that was
         * scheduled but hasn't shown yet. Without clearing the pending
         * 300ms timer, a hide() that runs before it fires (fast localhost
         * backend answers in ms) is followed by the pill popping up anyway
         * and sticking forever.
         */
        hide: function () {
            if (typeof navHide === 'function') navHide();
            var nav = document.getElementById('fastnet-nav-loader');
            if (nav) { nav.classList.remove('visible'); nav.setAttribute('aria-hidden', 'true'); }
            document.body.style.overflow = '';
            isLoaderVisible = false;
            this.bar.done();
        },

        /**
         * Corner Floating Background Task Indicator
         * @param {Object|string} task - { id, text } or text string
         * @returns {string} Task ID
         */
        bg: function (task) {
            let id   = 'bg_' + Date.now() + '_' + Math.random().toString(36).substr(2, 4);
            let text = 'Processing...';

            if (typeof task === 'string') {
                text = task;
            } else if (typeof task === 'object' && task !== null) {
                if (task.id)               id   = task.id;
                if (task.text || task.message) text = task.text || task.message;
            }

            const els = getElements();
            if (!els.bgContainer) return id;

            let pill = document.getElementById('fastnet-pill-' + id);
            if (!pill) {
                pill = document.createElement('div');
                pill.id = 'fastnet-pill-' + id;
                pill.className = 'fastnet-bg-pill';
                pill.innerHTML = '<div class="fastnet-bg-spinner"></div><span class="fastnet-bg-text"></span>';
                els.bgContainer.appendChild(pill);
            }

            const textEl = pill.querySelector('.fastnet-bg-text');
            if (textEl) textEl.textContent = text;

            requestAnimationFrame(() => { pill.classList.add('active'); });
            activeBgTasks.set(id, pill);
            return id;
        },

        /**
         * Complete and Dismiss Background Task Indicator
         * @param {string} id - Task ID returned from bg()
         */
        bgDone: function (id) {
            if (!id && activeBgTasks.size > 0) {
                activeBgTasks.forEach((_, taskId) => this.bgDone(taskId));
                return;
            }
            const pill = activeBgTasks.get(id) || document.getElementById('fastnet-pill-' + id);
            if (pill) {
                pill.classList.remove('active');
                setTimeout(() => {
                    if (pill.parentNode) pill.parentNode.removeChild(pill);
                    activeBgTasks.delete(id);
                }, 280);
            }
        },

        /**
         * Apply loading state to any button element
         * @param {HTMLElement|string} btn
         * @param {boolean} isLoading
         */
        button: function (btn, isLoading) {
            const el = typeof btn === 'string' ? document.querySelector(btn) : btn;
            if (!el) return;
            if (isLoading) {
                el.disabled = true;
                el.classList.add('btn-loading');
            } else {
                el.disabled = false;
                el.classList.remove('btn-loading');
            }
        }
    };

    // Global Window Bindings & Aliases
    window.FastnetLoader = FastnetLoader;
    window.showAppLoading  = function (msg, sub) { FastnetLoader.show({ title: msg, subtext: sub }); };
    window.hideAppLoading  = function ()          { FastnetLoader.hide(); };
    window.startBgLoading  = function (msg)       { return FastnetLoader.bg(msg); };
    window.stopBgLoading   = function (id)        { FastnetLoader.bgDone(id); };
    window.showNavLoading  = function ()          { if (typeof navShow === 'function') navShow(); };
    window.hideNavLoading  = function ()          { if (typeof navHide === 'function') navHide(); };

    // Initial page load progress lifecycle
    FastnetLoader.bar.start();

    document.addEventListener('DOMContentLoaded', function () {
        FastnetLoader.bar.set(90);
    });

    window.addEventListener('load', function () {
        FastnetLoader.bar.done();
        const legacyPreloader = document.getElementById('preloader');
        if (legacyPreloader) {
            legacyPreloader.style.opacity = '0';
            setTimeout(() => { legacyPreloader.style.display = 'none'; }, 200);
        }
    });

    /* ── Navigation Loading Indicator ──────────────────────────────────────
     * Shown only when next paint takes > 300ms.
     * Instant link clicks (fast server) never show any overlay.
     * AJAX zones (search, filter chips) excluded via managedZone().
     */
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

})(window, document);
