/* FastNet host onboarding — split for the 300-line cap. Loads after fastnet-api.js; order: upload, map-search, map-util, map-main, rooms. */
(function () {
  /* ---------- step 2 · smart location (only when present) ---------- */
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
    var marker = null;
    var __st = window.__obMapState || {};

    var osmMarker = null;
    // A previous visit already proved Mapbox tiles dead (gray canvas) — skip
    // it entirely and paint OSM instantly instead of re-proving the failure.
    function mbxRememberedDead() {
      try {
        var t = parseInt(window.localStorage.getItem('fns_mbx_dead') || '0', 10);
        return t > 0 && (Date.now() - t) < 7 * 86400000;
      } catch (e) { return false; }
    }
    function setPoint(lng, lat) {
      latEl.value = (+lat).toFixed(6);
      lngEl.value = (+lng).toFixed(6);
      if (marker) { try { marker.setLngLat([lng, lat]); } catch (e) {} }
      if (osmMarker) { try { osmMarker.setLatLng([lat, lng]); } catch (e) {} }
    }
    function ctxVal(ctx, keys) {
      if (!ctx) return '';
      for (var i = 0; i < ctx.length; i++) {
        var id = ctx[i].id || '';
        for (var k = 0; k < keys.length; k++) {
          if (id.indexOf(keys[k]) === 0) return ctx[i].text || '';
        }
      }
      return '';
    }
    function applyPlace(f) {
      var c = f.center || [];
      if (c.length === 2) {
        setPoint(c[0], c[1]);
        if (__st.map) { try { __st.map.flyTo({ center: c, zoom: 14 }); } catch (e) {} }
        if (__st.osmMap) { try { __st.osmMap.setView([c[1], c[0]], 14); } catch (e) {} }
      }
      if (f.place_name) addrEl.value = f.place_name;
      var city = ctxVal(f.context, ['place']);
      var area = ctxVal(f.context, ['neighborhood', 'locality']);
      if (city) cityEl.value = city;
      if (area) areaEl.value = area;
      if (geoMsg) geoMsg.textContent = 'Pinned: ' + (f.text || f.place_name || 'location');
      listEl.style.display = 'none';
    }
    function geoSearch(q, cb) {
      if (!q || q.length < 2) { cb([]); return; }
      if (__st.mbToken) {
        fetch('https://api.mapbox.com/geocoding/v5/mapbox.places/' + encodeURIComponent(q) + '.json?access_token=' + __st.mbToken + '&country=tz&limit=5&types=address,place,locality,neighborhood,poi')
          .then(function (r) { return r.ok ? r.json() : null; })
          .then(function (j) { cb((j && j.features) || []); })
          .catch(function () { osmGeoSearch(q, cb); });
        return;
      }
      osmGeoSearch(q, cb);
    }
    // OpenStreetMap fallback — works on Contabo with zero Mapbox config.
    function osmGeoSearch(q, cb) {
      fetch('https://nominatim.openstreetmap.org/search?format=jsonv2&countrycodes=tz&limit=5&addressdetails=1&q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' } })
        .then(function (r) { return r.ok ? r.json() : null; })
        .then(function (arr) {
          (arr || []).forEach(function (p) {
            // Normalize to the Mapbox feature shape the UI already consumes.
            p.text = (p.display_name || '').split(',')[0];
            p.place_name = p.display_name;
            p.center = [parseFloat(p.lon), parseFloat(p.lat)];
            var a = p.address || {};
            p.context = [
              { id: 'place', text: a.city || a.town || a.village || a.municipality || '' },
              { id: 'neighborhood', text: a.suburb || a.neighbourhood || a.quarter || '' }
            ];
          });
          cb(arr || []);
        })
        .catch(function () { cb([]); });
    }
    function reverse(lng, lat) {
      if (__st.mbToken) {
        fetch('https://api.mapbox.com/geocoding/v5/mapbox.places/' + lng + ',' + lat + '.json?access_token=' + __st.mbToken + '&country=tz&limit=1&types=address,place,locality,neighborhood,poi')
          .then(function (r) { return r.ok ? r.json() : null; })
          .then(function (j) {
            var f = j && j.features && j.features[0];
            if (!f) { osmReverse(lng, lat); return; }
            addrEl.value = f.place_name || addrEl.value;
            var city = ctxVal(f.context, ['place']);
            var area = ctxVal(f.context, ['neighborhood', 'locality']);
            if (city) cityEl.value = city;
            if (area) areaEl.value = area;
            if (geoMsg) geoMsg.textContent = 'Pinned: ' + (f.text || 'location');
          })
          .catch(function () { osmReverse(lng, lat); });
        return;
      }
      osmReverse(lng, lat);
    }
    function osmReverse(lng, lat) {
      fetch('https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=' + encodeURIComponent(lat) + '&lon=' + encodeURIComponent(lng) + '&addressdetails=1', { headers: { 'Accept': 'application/json' } })
        .then(function (r) { return r.ok ? r.json() : null; })
        .then(function (p) {
          if (!p) return;
          if (p.display_name) addrEl.value = p.display_name;
          var a = p.address || {};
          var city = a.city || a.town || a.village || a.municipality || '';
          var area = a.suburb || a.neighbourhood || a.quarter || '';
          if (city) cityEl.value = city;
          if (area) areaEl.value = area;
          if (geoMsg) geoMsg.textContent = 'Pinned: ' + ((p.display_name || '').split(',')[0] || 'location');
        })
        .catch(function () {});
    }
    var deb = null;
    searchEl.addEventListener('input', function () {
      clearTimeout(deb);
      var q = searchEl.value.trim();
      if (q.length < 2) { listEl.style.display = 'none'; return; }
      deb = setTimeout(function () {
        geoSearch(q, function (feats) {
          listEl.innerHTML = '';
          if (!feats.length) { listEl.style.display = 'none'; return; }
          feats.forEach(function (f) {
            var b = document.createElement('button');
            b.type = 'button';
            b.innerHTML = (f.text || '').replace(/</g, '&lt;') + '<small>' + (f.place_name || '').replace(/</g, '&lt;') + '</small>';
            b.onclick = function () { searchEl.value = f.text || ''; applyPlace(f); };
            listEl.appendChild(b);
          });
          listEl.style.display = 'block';
        });
      }, 300);
    });
    document.addEventListener('click', function (e) {
      if (mapWrap && !mapWrap.contains(e.target)) listEl.style.display = 'none';
    });
    /* fields → map: typing city/area/address moves the pin */
    var fldDeb = null;
    [addrEl, areaEl, cityEl].forEach(function (el) {
      el.addEventListener('input', function () {
        clearTimeout(fldDeb);
        fldDeb = setTimeout(function () {
          var q = [addrEl.value.trim(), areaEl.value.trim(), cityEl.value.trim()].filter(Boolean).join(', ');
          if (q.length < 3) return;
          geoSearch(q, function (feats) {
            var f = feats && feats[0];
            if (!f || !f.center) return;
            setPoint(f.center[0], f.center[1]);
            try { if (__st.map) __st.map.easeTo({ center: f.center, zoom: Math.max(__st.map.getZoom(), 13), duration: 600 }); } catch (e) {}
            try { if (__st.osmMap) __st.osmMap.setView([f.center[1], f.center[0]], Math.max(__st.osmMap.getZoom(), 13)); } catch (e) {}
            if (geoMsg) geoMsg.textContent = 'Pinned: ' + (f.text || 'location');
          });
        }, 700);
      });
    });
    window.__obMap = { setPoint: setPoint, reverse: reverse, dead: mbxRememberedDead };
  }
})();
