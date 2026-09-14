/**
 * FastNet Stays — Universal App Loading & Background Task Engine (app-loader.js)
 * Clean, lightweight, zero-dependency controller for page transitions and async operations.
 */
(function (window, document) {
    'use strict';

    // Prevent double initialization
    if (window.FastnetLoader) return;

    let progressTimer = null;
    let currentProgress = 0;
    let isLoaderVisible = false;
    const activeBgTasks = new Map();

    // DOM Elements Cache
    function getElements() {
        return {
            topBar: document.getElementById('fastnet-top-progress'),
            modal: document.getElementById('fastnet-app-loader'),
            modalTitle: document.getElementById('fastnet-loader-title'),
            modalSub: document.getElementById('fastnet-loader-subtext'),
            bgContainer: document.getElementById('fastnet-bg-loader')
        };
    }

    const FastnetLoader = {
        /**
         * Top-Bar Progress Controller (YouTube / Turbo style)
         */
        bar: {
            start: function () {
                const el = getElements().topBar;
                if (!el) return;
                currentProgress = 10;
                el.style.width = currentProgress + '%';
                el.classList.add('active');

                if (progressTimer) clearInterval(progressTimer);
                progressTimer = setInterval(() => {
                    if (currentProgress < 85) {
                        currentProgress += Math.random() * 8 + 2;
                        if (el) el.style.width = Math.min(85, currentProgress) + '%';
                    }
                }, 200);
            },
            set: function (percent) {
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
                const el = getElements().topBar;
                if (progressTimer) {
                    clearInterval(progressTimer);
                    progressTimer = null;
                }
                if (!el) return;
                el.style.width = '100%';
                setTimeout(() => {
                    el.classList.remove('active');
                    setTimeout(() => {
                        el.style.width = '0%';
                        currentProgress = 0;
                    }, 300);
                }, 200);
            }
        },

        /**
         * Full-Screen App Loading Modal
         * @param {Object|string} options - { title, message/subtext, lockScroll } or title string
         */
        show: function (options) {
            const els = getElements();
            if (!els.modal) return;

            let title = 'Loading FastNet Stays...';
            let subtext = 'Please wait a moment';

            if (typeof options === 'string') {
                title = options;
            } else if (typeof options === 'object' && options !== null) {
                if (options.title || options.message) title = options.title || options.message;
                if (options.subtext || options.subtitle) subtext = options.subtext || options.subtitle;
            }

            if (els.modalTitle) els.modalTitle.textContent = title;
            if (els.modalSub) els.modalSub.textContent = subtext;

            els.modal.classList.add('visible');
            els.modal.setAttribute('aria-hidden', 'false');
            isLoaderVisible = true;
            this.bar.start();

            if (options && options.lockScroll !== false) {
                document.body.style.overflow = 'hidden';
            }
        },

        /**
         * Update current modal text while loading
         */
        update: function (title, subtext) {
            const els = getElements();
            if (title && els.modalTitle) els.modalTitle.textContent = title;
            if (subtext && els.modalSub) els.modalSub.textContent = subtext;
        },

        /**
         * Hide Full-Screen App Loading Modal
         */
        hide: function () {
            const els = getElements();
            if (els.modal) {
                els.modal.classList.remove('visible');
                els.modal.setAttribute('aria-hidden', 'true');
            }
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
            let id = 'bg_' + Date.now() + '_' + Math.random().toString(36).substr(2, 4);
            let text = 'Processing in background...';

            if (typeof task === 'string') {
                text = task;
            } else if (typeof task === 'object' && task !== null) {
                if (task.id) id = task.id;
                if (task.text || task.message) text = task.text || task.message;
            }

            const els = getElements();
            if (!els.bgContainer) return id;

            // Update existing or create new
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

            // Trigger animation in next frame
            requestAnimationFrame(() => {
                pill.classList.add('active');
            });

            activeBgTasks.set(id, pill);
            return id;
        },

        /**
         * Complete and Dismiss Background Task Indicator
         * @param {string} id - Task ID returned from bg()
         */
        bgDone: function (id) {
            if (!id && activeBgTasks.size > 0) {
                // Done all if no id specified
                activeBgTasks.forEach((_, taskId) => this.bgDone(taskId));
                return;
            }

            const pill = activeBgTasks.get(id) || document.getElementById('fastnet-pill-' + id);
            if (pill) {
                pill.classList.remove('active');
                setTimeout(() => {
                    if (pill.parentNode) pill.parentNode.removeChild(pill);
                    activeBgTasks.delete(id);
                }, 300);
            }
        },

        /**
         * Helper to apply loading state to any button element
         * @param {HTMLElement|string} btn - Button element or selector
         * @param {boolean} isLoading - true to show spinner, false to restore
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
    window.showAppLoading = function (msg, sub) { FastnetLoader.show({ title: msg, subtext: sub }); };
    window.hideAppLoading = function () { FastnetLoader.hide(); };
    window.startBgLoading = function (msg) { return FastnetLoader.bg(msg); };
    window.stopBgLoading = function (id) { FastnetLoader.bgDone(id); };

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

    // Auto-progress on navigation links & form submissions
    document.addEventListener('click', function (e) {
        const link = e.target.closest('a');
        if (!link) return;
        const href = link.getAttribute('href');
        const target = link.getAttribute('target');

        // Ignore hash anchors, javascript:void, new tabs, downloads, external protocols
        if (!href || href.startsWith('#') || href.startsWith('javascript:') || target === '_blank' || link.hasAttribute('download')) {
            return;
        }

        // Internal navigation detected -> start top progress bar
        if (href.startsWith('/') || href.startsWith(window.location.origin)) {
            FastnetLoader.bar.start();
        }
    });

    // Auto-progress on form submit
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (form && !form.hasAttribute('data-no-loader')) {
            FastnetLoader.bar.start();
        }
    });

})(window, document);
