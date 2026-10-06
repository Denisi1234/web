/* FastNet app loader. Split for the 300-line cap — load app-loader-core.js first, then app-loader-dialog.js. */
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
})(window, document);
