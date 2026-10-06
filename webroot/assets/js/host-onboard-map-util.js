/* FastNet host onboarding — split for the 300-line cap. Loads after fastnet-api.js; order: upload, map-search, map-util, map-main, rooms. */
(function () {
    function hideStatic() {
      var st = document.getElementById('obStatic');
      if (st && st.parentNode) st.parentNode.removeChild(st);
    }
    function loadLeaflet(cb) {
      if (typeof window.L !== 'undefined' && window.L.map) { cb(true); return; }
      var css = document.querySelector('link[data-leaflet]');
      if (!css) {
        css = document.createElement('link');
        css.rel = 'stylesheet';
        css.setAttribute('data-leaflet', '1');
        css.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
        document.head.appendChild(css);
      }
      var js = document.querySelector('script[data-leaflet]');
      if (js) {
        var tries = 0;
        (function poll() {
          if (typeof window.L !== 'undefined') { cb(true); return; }
          if (++tries >= 40) { cb(false); return; }
          setTimeout(poll, 100);
        })();
        return;
      }
      js = document.createElement('script');
      js.setAttribute('data-leaflet', '1');
      js.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
      js.onload = function () { cb(true); };
      js.onerror = function () { cb(false); };
      document.head.appendChild(js);
    }
    function unlockManual(mapEl, latEl, lngEl, geoMsg) {
      if (mapEl) mapEl.classList.remove('loading');
      if (latEl) { latEl.readOnly = false; latEl.classList.remove('locked'); }
      if (lngEl) { lngEl.readOnly = false; lngEl.classList.remove('locked'); }
      if (geoMsg) geoMsg.textContent = 'Map offline — you may enter coordinates manually.';
    }
    // mapbox-gl.js may still be downloading (deferred CDN) — poll briefly
    // instead of giving up on the first check (was a flaky no-map).
    function waitForGL(cb) {
      var tries = 0;
      (function poll() {
        if (typeof mapboxgl !== 'undefined') { cb(true); return; }
        if (++tries >= 30) { cb(false); return; }
        setTimeout(poll, 100);
      })();
    }
  window.__obMapUtil = { hideStatic: hideStatic, loadLeaflet: loadLeaflet, unlockManual: unlockManual, waitForGL: waitForGL };
})();
