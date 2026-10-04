/**
 * fastnetstays.com — Display currency (TZS / USD / EUR)
 *
 * Server prices are ALWAYS rendered in TZS (authoritative, SEO canonical).
 * This module converts displayed amounts client-side using live rates from
 * GET /api/currencies (each entry carries `tzs_per_unit`), cached 24h.
 *
 * Rules (honesty first):
 *  - No hardcoded rates. Without a live or cached rate we stay in TZS.
 *  - Foreign amounts are rounded to whole units (display only).
 *  - Filter inputs / backend params stay TZS — conversion is display-only.
 *  - Checkout always charges in the backend currency, never the display one.
 *
 * Contract for templates:
 *  - Any amount: <span data-tzs="175000">TSh 175,000</span>
 *  - Converted via format(tzs), header painted via paint(), changes announced
 *    with `fastnet:currency-change` (map pills + price-mode toggle listen).
 */
(function (window, document) {
  'use strict';

  var LS_CODE = 'fastnet_curr';
  var LS_RATES = 'fastnet_curr_rates';
  var TTL = 24 * 60 * 60 * 1000;
  var SUPPORTED = ['TZS', 'USD', 'EUR'];
  var SYMBOLS = { TZS: 'TSh', USD: '$', EUR: '\u20AC' };

  var code = 'TZS';
  var rates = {}; // e.g. { USD: 2480, EUR: 2690 } = TZS per 1 unit
  var ratesOk = false;

  function store(k, v) { try { localStorage.setItem(k, v); } catch (e) {} }
  function read(k) { try { return localStorage.getItem(k); } catch (e) { return null; } }

  function symbol(c) { return SYMBOLS[c] || c; }

  /** Format a TZS integer into the active display currency. */
  function format(tzs) {
    var n = Math.round(Number(tzs) || 0);
    if (code === 'TZS' || !ratesOk || !rates[code]) {
      return 'TSh ' + n.toLocaleString('en-US');
    }
    var foreign = Math.round(n / rates[code]);
    return symbol(code) + foreign.toLocaleString('en-US');
  }

  /** Rewrite every [data-tzs] amount on the page. */
  function writeAmount(el, text) {
    // Price spans carry a "/night" suffix child — replace only the amount
    // text node so the suffix survives.
    var sub = el.querySelector ? el.querySelector('.gh-price-night') : null;
    if (sub && el.childNodes.length) {
      el.childNodes[0].textContent = text + ' ';
    } else {
      el.textContent = text;
    }
  }
  function apply() {
    document.querySelectorAll('[data-tzs]').forEach(function (el) {
      writeAmount(el, format(el.getAttribute('data-tzs')));
    });
    paint();
  }

  /** Header pill label: "EN · TSh" / "EN · $" / "EN · €". */
  function paint() {
    var label = 'EN \u00B7 ' + symbol(code === 'TZS' || !ratesOk ? 'TZS' : code);
    document.querySelectorAll('#nav_lang_btn span, #nav_logged_out_lang_btn span').forEach(function (el) {
      el.textContent = label;
    });
    // Footer currency link, if present.
    document.querySelectorAll('[data-fx-footer-label]').forEach(function (el) {
      el.textContent = 'English · ' + (code === 'TZS' || !ratesOk ? 'TZS' : code);
    });
    // Check the active option in header dropdowns.
    document.querySelectorAll('[data-fx-option]').forEach(function (el) {
      var on = el.getAttribute('data-fx-option') === code;
      el.classList.toggle('fw-bold', on);
      el.classList.toggle('text-primary', on);
      var icon = el.querySelector('.trivago-user-item-icon');
      if (icon) {
        icon.className = (on ? 'fa-solid fa-check trivago-user-item-icon text-primary'
                             : 'fa-solid fa-coins trivago-user-item-icon');
      }
    });
  }

  function announce() {
    try { window.dispatchEvent(new CustomEvent('fastnet:currency-change', { detail: { code: code } })); } catch (e) {}
  }

  function useCached() {
    try {
      var raw = read(LS_RATES);
      if (!raw) return false;
      var cached = JSON.parse(raw);
      if (!cached || !cached.ts || (Date.now() - cached.ts) > TTL || !cached.rates) return false;
      rates = cached.rates;
      ratesOk = !!(rates.USD || rates.EUR);
      return ratesOk;
    } catch (e) { return false; }
  }

  function fetchRates() {
    return fetch('/api/currencies', { headers: { Accept: 'application/json' } })
      .then(function (r) { if (!r.ok) throw new Error('currencies ' + r.status); return r.json(); })
      .then(function (d) {
        var list = (d && d.currencies) || [];
        var fresh = {};
        list.forEach(function (c) {
          var cd = c.code || c.currency_code;
          var per = Number(c.tzs_per_unit);
          if (SUPPORTED.indexOf(cd) !== -1 && per > 0) fresh[cd] = per;
        });
        if (fresh.USD || fresh.EUR) {
          rates = fresh;
          ratesOk = true;
          store(LS_RATES, JSON.stringify({ ts: Date.now(), rates: fresh }));
        }
        return ratesOk;
      })
      .catch(function () { return useCached(); });
  }

  /** Switch display currency. Falls back to TZS when no rate is available. */
  function set(next) {
    if (SUPPORTED.indexOf(next) === -1) next = 'TZS';
    code = next;
    store(LS_CODE, code);
    var done = function (ok) {
      ratesOk = ok && (code === 'TZS' || !!rates[code]);
      if (code !== 'TZS' && !rates[code]) {
        code = 'TZS';
        store(LS_CODE, code);
        if (typeof window.fnsToast === 'function') window.fnsToast('Live rates unavailable — showing TZS.');
      }
      apply();
      announce();
    };
    if (code === 'TZS' || rates[code]) { done(true); return; }
    if (useCached() && rates[code]) { done(true); return; }
    fetchRates().then(done);
  }

  function init() {
    var saved = read(LS_CODE);
    code = SUPPORTED.indexOf(saved) !== -1 ? saved : 'TZS';
    store(LS_CODE, code);
    paint();
    if (code === 'TZS') return; // nothing to convert; no request needed
    if (useCached()) { apply(); announce(); return; }
    fetchRates().then(function (ok) {
      if (ok && rates[code]) { apply(); announce(); }
      else { code = 'TZS'; store(LS_CODE, code); paint(); }
    });
  }

  // Flush any set() calls that arrived before init (deferred order safety).
  window.FastNetCurrency = {
    format: format, symbol: symbol, set: set, paint: paint, apply: apply,
    get code() { return ratesOk ? code : 'TZS'; },
    get ready() { return ratesOk; }
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
  } else {
    init();
  }
  // AJAX hydration (FastNetState) swaps in fresh server-rendered cards in TZS —
  // reconvert once the skeleton settles. Skipped in TZS mode (nothing to do).
  window.addEventListener('fastnet:shimmer-hide', function () {
    if ((read(LS_CODE) || 'TZS') === 'TZS') return;
    setTimeout(apply, 30);
  });
})(window, document);
