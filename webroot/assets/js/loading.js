/**
 * FastNet Stays — canonical loading controller.
 *
 * Single entry point for every loading state in the product. Replaces five
 * unrelated implementations (app-loader bar, p-dots, home-loader, home
 * shimmer, portal bar) and ~20 hand-rolled spinner blocks.
 *
 * Design rules:
 *  1. Reference-counted. Concurrent operations cannot hide each other's
 *     indicators.
 *  2. Every show has a guaranteed hide. `track()` wraps async work in a
 *     try/finally, and a watchdog force-completes any indicator that outlives
 *     its budget. This is the fix for the previous bug class where the top
 *     bar stayed pinned with a live interval running after a failed form.
 *  3. Progress only moves forward. The bar creeps toward 90% and waits; it
 *     never retreats.
 *
 * Usage:
 *   FastnetLoading.track(async () => { ... })   // bar + guaranteed done
 *   FastnetLoading.button(btn, true)            // inline dots
 *   FastnetLoading.busy(form, true)            // disable submit + dots
 *   FastnetLoading.region(el, true, 'Loading…')
 *   FastnetLoading.overlay.show({ title: 'Saving…' })
 *   FastnetLoading.bar.start() / .done()
 */
(function (global) {
  'use strict';

  if (global.FastnetLoading) return;

  /** Longest an indicator may stay up before the watchdog force-completes it. */
  var MAX_MS = 8000;
  /** Appear delay — 1ms: the bar starts instantly and finishes instantly,
     so loading feels professional with zero perceived wait. */
  var APPEAR_DELAY = 1;
  /** Where an indeterminate bar creeps to and waits. */
  var CREEP_TO = 90;

  var els = {};
  var barTimer = null;
  var creepTimer = null;
  var watchdog = null;

  /* Reference counts. The bar is only hidden when nothing is pending. */
  var pending = 0;
  var barShownAt = 0;

  function doc() {
    return global.document;
  }

  function ensureChrome() {
    var d = doc();
    if (!d || !d.body) return false;

    // Adopt the markup that layouts already render rather than injecting a
    // second bar, so there is exactly one progress element in the document.
    if (!els.progress) {
      els.progress =
        d.getElementById('fastnet-top-progress') || d.getElementById('fn-progress');

      if (els.progress) {
        els.progress.classList.add('fn-progress');
        els.progress.setAttribute('role', 'progressbar');
        els.progress.setAttribute('aria-label', 'Page loading progress');
        els.progress.setAttribute('aria-valuemin', '0');
        els.progress.setAttribute('aria-valuemax', '100');
        if (!els.progress.hasAttribute('aria-valuenow')) {
          els.progress.setAttribute('aria-valuenow', '0');
        }
      } else {
        var bar = d.createElement('div');
        bar.id = 'fastnet-top-progress';
        bar.className = 'fn-progress';
        bar.setAttribute('role', 'progressbar');
        bar.setAttribute('aria-label', 'Page loading progress');
        bar.setAttribute('aria-valuemin', '0');
        bar.setAttribute('aria-valuemax', '100');
        bar.setAttribute('aria-valuenow', '0');
        d.body.appendChild(bar);
        els.progress = bar;
      }
    }

    // Overlay intentionally never created (deleted): bar + skeletons only.
    return true;
  }

  function armWatchdog() {
    if (watchdog) return;
    watchdog = global.setTimeout(function () {
      // Something upstream failed to clean up. Release everything rather than
      // leaving the UI permanently blocked.
      watchdog = null;
      forceReset();
    }, MAX_MS);
  }

  function disarmWatchdog() {
    if (!watchdog) return;
    global.clearTimeout(watchdog);
    watchdog = null;
  }

  function forceReset() {
    pending = 0;

    if (barTimer) {
      global.clearTimeout(barTimer);
      barTimer = null;
    }
    if (creepTimer) {
      global.clearInterval(creepTimer);
      creepTimer = null;
    }

    if (els.progress) {
      els.progress.classList.remove('is-active', 'is-indeterminate');
      els.progress.style.width = '0%';
      els.progress.setAttribute('aria-valuenow', '0');
    }
    if (els.overlay) {
      els.overlay.classList.remove('is-visible');
      els.overlay.setAttribute('aria-hidden', 'true');
    }
    if (doc() && doc().body) doc().body.style.removeProperty('overflow');
  }

  /* ------------------------------------------------------------------ bar */

  var bar = {
    /** Begin (or join) a loading operation. Reference counted. */
    start: function () {
      if (!ensureChrome()) return;

      pending += 1;
      armWatchdog();

      // Reference counted: an inner operation finishing must not hide the bar
      // while an outer one is still running.
      if (pending > 1) return;

      barShownAt = Date.now();

      if (barTimer) global.clearTimeout(barTimer);
      barTimer = global.setTimeout(function () {
        barTimer = null;
        paintBar(35);

        // Professional snap: jump fast toward 90% and hold. Never retreats.
        var pct = 35;
        if (creepTimer) global.clearInterval(creepTimer);
        creepTimer = global.setInterval(function () {
          if (pct >= CREEP_TO) return;
          pct = Math.min(CREEP_TO, pct + Math.max(3, Math.round((CREEP_TO - pct) / 3)));
          paintBar(pct);
        }, 90);
      }, APPEAR_DELAY);
    },

    /** Explicitly move the bar (0-100). */
    set: function (pct) {
      if (!els.progress) return;
      var v = Math.max(0, Math.min(100, Math.round(pct)));
      paintBar(v);
    },

    /** End one operation. Hides the bar once nothing is pending. */
    done: function () {
      if (pending > 0) pending -= 1;

      if (pending > 0) return;

      if (barTimer) {
        global.clearTimeout(barTimer);
        barTimer = null;
      }
      if (creepTimer) {
        global.clearInterval(creepTimer);
        creepTimer = null;
      }

      if (!els.progress) return;

      // Never regress: only ever complete from a value below 100.
      paintBar(100);
      els.progress.setAttribute('aria-valuenow', '100');
      els.progress.classList.remove('is-indeterminate');

      global.setTimeout(function () {
        if (pending > 0) return; // a new op started during the fade
        els.progress.classList.remove('is-active');
        els.progress.style.width = '0%';
        els.progress.setAttribute('aria-valuenow', '0');
        disarmWatchdog();
      }, 120);
    },

    isActive: function () {
      return pending > 0;
    },

    /** True if the bar was on screen long enough to be worth showing. */
    wasVisible: function () {
      return barShownAt > 0 && Date.now() - barShownAt > APPEAR_DELAY;
    }
  };

  function paintBar(pct) {
    if (!els.progress) return;
    els.progress.style.width = pct + '%';
    els.progress.setAttribute('aria-valuenow', String(pct));
    els.progress.classList.add('is-active', 'is-indeterminate');
  }

  /* --------------------------------------------------------------- dots */

  var DOTS_HTML = '<i></i><i></i>';

  /**
   * Remove any legacy hand-rolled dot markup from a control.
   * Several templates still write <span class="p-dots"> via innerHTML; leaving
   * that in place alongside the canonical dots would render six dots.
   */
  function stripLegacyDots(el) {
    if (!el || !el.querySelectorAll) return;
    var legacy = el.querySelectorAll('.p-dots');
    for (var i = 0; i < legacy.length; i++) {
      var node = legacy[i];
      if (node.parentNode) node.parentNode.removeChild(node);
    }
  }

  /**
   * Show or hide the inline dot indicator inside a control.
   * Works on buttons, links and any element. Idempotent.
   */
  function button(el, on, opts) {
    if (!el) return;
    opts = opts || {};

    if (on) {
      if (el.__fnDots) return;

      stripLegacyDots(el);

      var dots = doc().createElement('span');
      dots.className = 'fn-dots' + (opts.small ? ' fn-dots--sm' : '');
      dots.setAttribute('aria-hidden', 'true');
      dots.innerHTML = DOTS_HTML;

      var target = opts.label ? el.querySelector('[data-fn-label]') : null;

      if (target) {
        el.__fnLabel = target.textContent;
        target.textContent = '';
        target.appendChild(dots);
      } else {
        el.insertBefore(dots, el.firstChild);
      }

      el.__fnDots = dots;
      el.__fnWasDisabled = el.disabled === true;
      el.disabled = true;
      el.setAttribute('aria-busy', 'true');
      return;
    }

    stripLegacyDots(el);

    var existing = el.__fnDots;
    if (!existing) return;
    el.__fnDots = null;

    if (el.__fnLabel !== undefined && el.__fnLabel !== null) {
      var slot = existing.parentNode;
      if (slot) slot.textContent = el.__fnLabel;
    } else if (existing.parentNode) {
      existing.parentNode.removeChild(existing);
    }

    el.__fnLabel = null;
    if (!el.__fnWasDisabled) el.disabled = false;
    el.removeAttribute('aria-busy');
  }

  /** Busy state for a form's submit control. */
  function busy(form, on) {
    if (!form || !form.querySelector) return;
    var btn =
      form.querySelector('button[type=submit], input[type=submit], button:not([type])');
    button(btn, on);
  }

  /* ------------------------------------------------------------ skeleton */

  /**
   * Swap a container between real content and its skeleton.
   *
   * @param {string|Element} target  container id or element
   * @param {boolean} on             show skeleton (true) or restore content
   * @param {Element}    [skeleton]  the skeleton node to show
   */
  function skeleton(target, on, skeletonEl) {
    var host = typeof target === 'string' ? doc().getElementById(target) : target;
    if (!host) return;

    if (on) {
      if (host.__fnSkeletonShown) return;
      host.__fnSkeletonShown = true;
      host.__fnPrevDisplay = host.style.display;
      host.style.display = 'none';
      if (skeletonEl) skeletonEl.style.display = '';
      if (host.setAttribute) host.setAttribute('aria-busy', 'true');
      return;
    }

    if (!host.__fnSkeletonShown) return;
    host.__fnSkeletonShown = false;
    host.style.display = host.__fnPrevDisplay || '';
    if (skeletonEl) skeletonEl.style.display = 'none';
    if (host.removeAttribute) host.removeAttribute('aria-busy');
  }

  /**
   * Build a skeleton block. Used instead of hand-written placeholder markup.
   *
   * @param {number} lines
   * @param {string} [extraClass]
   */
  function skeletonBlock(lines, extraClass) {
    var el = doc().createElement('div');
    el.className = 'fn-skeleton ' + (extraClass || '');
    el.setAttribute('aria-hidden', 'true');
    var html = '';
    for (var i = 0; i < (lines || 3); i++) {
      var w = [90, 60, 75, 50][i % 4];
      html += '<div class="fn-skeleton-line fn-w-' + w + '"></div>';
    }
    el.innerHTML = html;
    return el;
  }

  /* -------------------------------------------------------------- region */

  /**
   * Centred inline loading block for a content area.
   *
   * @param {string|Element} target
   * @param {boolean} on
   * @param {string} [text]
   */
  function region(target, on, text) {
    var el = typeof target === 'string' ? doc().getElementById(target) : target;
    if (!el) return;

    if (on) {
      if (el.__fnRegion) return;
      el.__fnRegion = true;
      el.__fnHtml = el.innerHTML;
      el.className = stripActiveClass(el.className) + ' fn-region-active';
      el.setAttribute('aria-busy', 'true');
      el.innerHTML =
        '<div class="fn-spinner"></div><div>' +
        (text || 'Loading…') +
        '</div>';
      return;
    }

    if (!el.__fnRegion) return;
    el.__fnRegion = false;
    if (el.__fnHtml !== undefined) el.innerHTML = el.__fnHtml;
    el.className = stripActiveClass(el.className);
    el.removeAttribute('aria-busy');
  }

  /** Remove our own marker class without assuming className is a string. */
  function stripActiveClass(className) {
    return String(className == null ? '' : className)
      .replace(/\bfn-region-active\b/g, '')
      .replace(/\s+/g, ' ')
      .trim();
  }

  /* ------------------------------------------------------------- overlay */

  // Overlay — DELETED. Bar + skeletons only (professional, never blocks).
  // show()/hide() are kept as no-op aliases so old call sites keep working.
  var overlay = {
    show: function (opts) {
      bar.start();
    },

    hide: function () {
      if (pending === 0) disarmWatchdog();
    }
  };

  /* --------------------------------------------------------------- track */

  /**
   * Run async work with a progress bar that is guaranteed to be released.
   *
   * This is the fix for the stuck-loader class of bug: the bar is started on
   * submit and previously only cleared on `window load`, so any request that
   * failed left it pinned at 82% with a live interval running.
   *
   * @param   {Function} work  may be async; its resolved value is passed through
   * @param   {object}   [opts]
   * @returns {Promise}
   */
  function track(work, opts) {
    opts = opts || {};
    bar.start();
    if (opts.overlay) overlay.show(opts);

    var result;
    try {
      result = work();
    } catch (err) {
      finish();
      throw err;
    }

    return Promise.resolve(result).then(
      function (value) {
        finish();
        return value;
      },
      function (err) {
        finish();
        throw err;
      }
    );

    function finish() {
      if (opts.overlay) overlay.hide();
      bar.done();
    }
  }

  global.FastnetLoading = {
    bar: bar,
    button: button,
    busy: busy,
    skeleton: skeleton,
    skeletonBlock: skeletonBlock,
    region: region,
    overlay: overlay,
    track: track,
    reset: forceReset,

    /**
     * True when the user has asked for reduced motion. Callers that build
     * their own indicators should honour this.
     */
    reducedMotion: function () {
      return !!(
        global.matchMedia &&
        global.matchMedia('(prefers-reduced-motion: reduce)').matches
      );
    }
  };
})(window);