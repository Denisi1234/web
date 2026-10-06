/* FastNet loading indicators. Split for the 300-line cap — load loading-core.js first, then loading-ui.js. */
(function (global) {
  'use strict';

  function doc() {
    return global.document;
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
  var FL = global.FastnetLoading || {};
  FL.button = button;
  FL.busy = busy;
  FL.skeleton = skeleton;
  FL.skeletonBlock = skeletonBlock;
  FL.region = region;
  global.FastnetLoading = FL;
})();
