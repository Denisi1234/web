/* FastNet host onboarding — split for the 300-line cap. Loads after fastnet-api.js; order: upload, map-search, map-util, map-main, rooms. */
(function () {
  var DAR = { lng: 39.2083, lat: -6.7924 };
  var mapEl = document.getElementById('obMap');
  if (mapEl) {
    var latEl = document.getElementById('obLat');
    var lngEl = document.getElementById('obLng');
    var cityEl = document.getElementById('obCity');
    var areaEl = document.getElementById('obArea');
    var addrEl = document.getElementById('obAddress');
    var searchEl = document.getElementById('obSearch');
    var listEl = document.getElementById('obSearchList');
    var geoBtn = document.getElementById('obGeoBtn');
    var geoMsg = document.getElementById('obGeoMsg');
    var mapWrap = document.getElementById('obMapWrap');
    var map = null, marker = null, mbToken = '';
    var osmMap = null, osmMarker = null, useOsm = false;
    window.__obMapState = window.__obMapState || {};
    function __obPublish() { window.__obMapState.map = map; window.__obMapState.osmMap = osmMap; window.__obMapState.mbToken = mbToken; }
    (function seed() {
      var la = parseFloat(latEl.value), ln = parseFloat(lngEl.value);
      if (isFinite(la) && isFinite(ln) && (la !== DAR.lat || ln !== DAR.lng)) DAR = { lng: ln, lat: la };
    })();
  if (mapEl) {
    function initOsmMap() {
      // Real map with zero config: OSM tiles + draggable pin + Nominatim search.
      mapEl.classList.add('loading');
      window.__obMapUtil.loadLeaflet(function (ok) {
        mapEl.classList.remove('loading');
        if (!ok || typeof window.L === 'undefined') { window.__obMapUtil.unlockManual(mapEl, latEl, lngEl, geoMsg); return; }
        try {
          window.__obMapUtil.hideStatic();
          useOsm = true; __obPublish();
          var lat = parseFloat(latEl.value) || DAR.lat;
          var lng = parseFloat(lngEl.value) || DAR.lng;
          osmMap = window.L.map('obMap').setView([lat, lng], 13);
          window.L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(osmMap);
          osmMarker = window.L.marker([lat, lng], { draggable: true }).addTo(osmMap); __obPublish();
          osmMarker.on('dragend', function () {
            var p = osmMarker.getLatLng();
            setPoint(p.lng.toFixed(6), p.lat.toFixed(6));
            reverse(p.lng.toFixed(6), p.lat.toFixed(6));
          });
          osmMap.on('click', function (e) {
            setPoint(e.latlng.lng.toFixed(6), e.latlng.lat.toFixed(6));
            osmMarker.setLatLng(e.latlng);
            reverse(e.latlng.lng.toFixed(6), e.latlng.lat.toFixed(6));
          });
          setTimeout(function () { try { osmMap.invalidateSize(); } catch (e) {} }, 300);
          if (geoMsg) geoMsg.textContent = 'Tap the map or drag the pin to set the location.';
        } catch (e) { window.__obMapUtil.unlockManual(mapEl, latEl, lngEl, geoMsg); }
      });
    }
    function initMap(token) {
      mbToken = token; __obPublish();
      mapEl.classList.add('loading');
      var preStyle = (typeof window.MAPBOX_STYLE === 'string' && window.MAPBOX_STYLE) ? window.MAPBOX_STYLE : 'mapbox://styles/mapbox/streets-v12';
      window.__obMapUtil.waitForGL(function (ok) {
        if (!ok) { initOsmMap(); return; }
        mapboxgl.accessToken = token;
        try {
          map = new mapboxgl.Map({ container: 'obMap', style: preStyle, center: [DAR.lng, DAR.lat], zoom: 12 }); __obPublish();
        } catch (e) { window.__obMapUtil.unlockManual(mapEl, latEl, lngEl, geoMsg); return; }
        function ready() {
          mapEl.classList.remove('loading');
          window.__obMapUtil.hideStatic();
          try { map.resize(); } catch (e) {}
        }
        // Gray-canvas guard (the reported bug: logo + pin render, tiles never
        // paint — e.g. key valid for geocoding but blocked for tiles). Any
        // 401/403 switches instantly; otherwise a 7s no-paint watchdog heals
        // to the OSM backup instead of stranding the host on gray.
        var styleOk = false, switched = false;
        function switchToOsm(reason) {
          if (switched) return; switched = true;
          try { if (map) map.remove(); } catch (e) {}
          map = null; marker = null;
          // Remember the failure for 7 days: next visits skip Mapbox
          // entirely and paint OSM instantly instead of re-proving gray.
          try { window.localStorage.setItem('fns_mbx_dead', String(Date.now())); } catch (e) {}
          if (geoMsg) geoMsg.textContent = reason || 'Live map failed — backup map loaded.';
          initOsmMap();
        }
        map.on('load', function () {
          styleOk = true;
          try { window.localStorage.removeItem('fns_mbx_dead'); } catch (e) {}
          ready();
        });
        setTimeout(function () { if (!styleOk) switchToOsm('Live map did not paint — backup map loaded.'); }, 4000);
        setTimeout(function () { try { if (map) map.resize(); } catch (e) {} }, 400);
        map.on('error', function (ev) {
          var st = ev && ev.error && ev.error.status;
          if (st === 401 || st === 403) switchToOsm('Map key blocked for tiles — backup map loaded.');
          else mapEl.classList.remove('loading');
        });
        marker = new mapboxgl.Marker({ draggable: true }).setLngLat([DAR.lng, DAR.lat]).addTo(map); __obPublish();
        marker.on('dragend', function () {
          var p = marker.getLngLat();
          setPoint(p.lng, p.lat);
          reverse(p.lng.toFixed(6), p.lat.toFixed(6));
        });
        map.on('click', function (e) {
          setPoint(e.lngLat.lng, e.lngLat.lat);
          reverse(e.lngLat.lng.toFixed(6), e.lngLat.lat.toFixed(6));
        });
      });
    }
    // Server inlines window.MAPBOX_TOKEN on step 2 — skip the extra
    // /api/map-config round trip and start the map immediately.
    // No token (typical Contabo miss-config) → real OSM map, not a dead box.
    (function bootMap() {
      // Fastest path: tiles already proven dead on this device → OSM now.
      if (window.__obMap.dead()) { initOsmMap(); return; }
      var preRaw = (typeof window.MAPBOX_TOKEN === 'string') ? window.MAPBOX_TOKEN : '';
      var pre = (preRaw.indexOf('pk.') === 0 && preRaw.indexOf('your_real') === -1 && preRaw.slice(-5) !== '.demo') ? preRaw : '';
      if (pre) { initMap(pre); return; }
      // 3s cap: a hanging backend must never stall the map (was unbounded).
      var ctl = null;
      try { ctl = new AbortController(); setTimeout(function () { try { ctl.abort(); } catch (e) {} }, 3000); } catch (e) { ctl = null; }
      fetch('/api/map-config', { headers: { 'Accept': 'application/json' }, signal: ctl ? ctl.signal : undefined })
        .then(function (r) { return r.ok ? r.json() : null; })
        .then(function (j) {
          var t = j && (j.mapbox_token || j.mapboxToken || j.token);
          if (t && t.indexOf('pk.') === 0 && t.indexOf('your_real') === -1 && t.slice(-5) !== '.demo' && !window.__obMap.dead()) initMap(t);
          else initOsmMap();
        })
        .catch(initOsmMap);
    })();
    if (geoBtn) {
      geoBtn.addEventListener('click', function () {
        if (!navigator.geolocation) { if (geoMsg) geoMsg.textContent = 'Geolocation not supported here.'; return; }
        if (geoMsg) geoMsg.textContent = 'Locating…';
        navigator.geolocation.getCurrentPosition(function (pos) {
          var lng = +pos.coords.longitude.toFixed(6), lat = +pos.coords.latitude.toFixed(6);
          setPoint(lng, lat);
          if (map) { try { map.flyTo({ center: [lng, lat], zoom: 14 }); } catch (e) {} }
          if (osmMap) { try { osmMap.setView([lat, lng], 14); } catch (e) {} }
          reverse(lng, lat);
          if (geoMsg) geoMsg.textContent = 'Pinned to your location.';
        }, function () {
          if (geoMsg) geoMsg.textContent = 'Location blocked — search or tap the map instead.';
        }, { timeout: 10000 });
      });
    }
  }
  }
})();
