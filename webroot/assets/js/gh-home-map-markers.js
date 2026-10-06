/* Home map. Split for the 300-line cap — load core, markers, ui in order (shared globals, no IIFE). */
    /* ─── Add markers — FastNet clustering: grid ~1km, +N pills, cap 60 ─── */
    function _addMarkers(markers) {
        if (!_map) return;
        // clear stale "no stays" overlay once real markers arrive
        try{
            var mapEl = document.getElementById('gh-interactive-map');
            var stale = mapEl && mapEl.querySelector('.gh-map-empty');
            if (stale && markers && markers.length) stale.remove();
        }catch(e){}
        var valid = markers.filter(function(m){ return m.lat && m.lng; });
        if(valid.length > 60) valid = valid.slice(0,60);
        // Grid clustering ~1km (0.009 deg)
        var grid = {};
        valid.forEach(function(m){
            var k = m.lat.toFixed(2)+','+m.lng.toFixed(2);
            if(!grid[k]) grid[k]=[];
            grid[k].push(m);
        });
        Object.keys(grid).forEach(function(k){
            var group = grid[k];
            if(group.length===1){
                var m=group[0];
                var el = _makeMarkerEl(m);
                var mk = new mapboxgl.Marker({ element: el, anchor: 'bottom' }).setLngLat([m.lng, m.lat]).addTo(_map);
                _markers[m.id] = { marker: mk, el: el, data: m };
                el.addEventListener('mouseenter', function(){ _hlMarker(m.id,true); _hlCard(m.id,true); });
                el.addEventListener('mouseleave', function(){ _hlMarker(m.id,false); _hlCard(m.id,false); });
                el.addEventListener('click', function(e){ e.stopPropagation(); if(window.ghScrollToCard) window.ghScrollToCard(m.id); _hlMarker(m.id,true); _hlCard(m.id,true); setTimeout(function(){ _hlMarker(m.id,false); _hlCard(m.id,false); },2200); });
            } else {
                // cluster pill — avg position, show count + avg price (numeric base, view-aware)
                var avgLat = group.reduce(function(s,x){ return s+x.lat; },0)/group.length;
                var avgLng = group.reduce(function(s,x){ return s+x.lng; },0)/group.length;
                var avgBase = Math.round(group.reduce(function(s,x){ return s+_markerBase(x); },0)/group.length);
                var avgDisp = avgBase ? _markerLabel({ price: avgBase, label: '' }) : '';
                var clusterData = { id:'cluster_'+k, lat:avgLat, lng:avgLng, label: (avgDisp ? avgDisp : '')+'  •  +'+group.length, title: group.length+' stays', count:group.length, members:group };
                var cel = _makeClusterEl(clusterData);
                var cmk = new mapboxgl.Marker({ element: cel, anchor: 'bottom' }).setLngLat([avgLng, avgLat]).addTo(_map);
                // store under cluster id for highlight
                _markers[clusterData.id] = { marker: cmk, el: cel, data: clusterData };
                cel.addEventListener('click', function(e){
                    e.stopPropagation();
                    // expand to bounds + highlight all members
                    var b=new mapboxgl.LngLatBounds();
                    group.forEach(function(mm){ b.extend([mm.lng, mm.lat]); });
                    _map.fitBounds(b,{padding:56,maxZoom:15,duration:700});
                    group.forEach(function(mm){ _hlCard(mm.id,true); });
                    setTimeout(function(){ group.forEach(function(mm){ _hlCard(mm.id,false); }); },2000);
                });
                cel.addEventListener('mouseenter', function(){ group.forEach(function(mm){ _hlCard(mm.id,true); }); });
                cel.addEventListener('mouseleave', function(){ group.forEach(function(mm){ _hlCard(mm.id,false); }); });
            }
        });

        // Fit markers: first load fits all; single result centers on its pin (region follows location).
        // Later refreshes are handled by ghRefreshMarkers (off-screen reframe only) so filters don't yank the map.
        if (!_fitted && markers.length === 1 && markers[0].lat && markers[0].lng) {
            _map.flyTo({ center: [markers[0].lng, markers[0].lat], zoom: 14, duration: 800 });
            _fitted = true;
        } else if (!_fitted && markers.length > 1) {
            var bounds = new mapboxgl.LngLatBounds();
            markers.forEach(function (m) { if (m.lat && m.lng) bounds.extend([m.lng, m.lat]); });
            _map.fitBounds(bounds, { padding: 56, maxZoom: 14, duration: 800 });
            _fitted = true;
        }
    }

    /* ─── Price labels: nightly rates, display-currency aware ───
     * Source data is never mutated. The nightly TZS base comes from the
     * numeric `price` payload field (falls back to parsing legacy labels);
     * stay totals live inline on the cards, so pills stay nightly-only. */
    function _tzsDisplay(n) {
        try {
            if (window.FastNetCurrency && typeof window.FastNetCurrency.format === 'function' &&
                window.FastNetCurrency.code !== 'TZS') return window.FastNetCurrency.format(n);
        } catch (e) {}
        return 'TSh ' + Math.round(n).toLocaleString('en-US');
    }
    function _markerBase(m) {
        if (typeof m.price === 'number' && m.price > 0) return m.price;
        return parseInt(String(m.label || '').replace(/[^0-9]/g, ''), 10) || 0;
    }
    function _markerLabel(m) {
        var base = _markerBase(m);
        if (!base) return m.label || '';
        return _tzsDisplay(base);
    }

    /* ─── Create DOM element for marker — UX: a11y + contrast ─── */
    function _makeMarkerEl(m) {
        var disp = _markerLabel(m);
        var el = document.createElement('div');
        el.className = 'gh-map-marker';
        el.setAttribute('data-id', m.id);
        el.setAttribute('role', 'button');
        el.setAttribute('tabindex', '0');
        el.setAttribute('aria-label', (m.title || 'Hotel') + ' ' + disp);
        // GREAT price green ring handled via CSS if label contains GREAT? Keep blue for now
        el.innerHTML =
            '<div class="gh-mm-icon" aria-hidden="true"><i class="fa-solid fa-bed"></i></div>' +
            '<span class="gh-mm-price">' + disp + '</span>';
        el.addEventListener('keydown', function(e){ if(e.key==='Enter'||e.key===' ') { e.preventDefault(); el.click(); } });
        return el;
    }
    function _makeClusterEl(m){
        var el=document.createElement('div');
        el.className='gh-map-marker gh-cluster';
        el.setAttribute('data-id', m.id);
        el.setAttribute('role','button');
        el.setAttribute('tabindex','0');
        el.setAttribute('aria-label', m.count+' stays clustered '+m.label);
        el.innerHTML='<div class="gh-mm-icon" aria-hidden="true"><i class="fa-solid fa-layer-group"></i></div><span class="gh-mm-price">'+m.label+'</span>';
        el.addEventListener('keydown', function(e){ if(e.key==='Enter'||e.key===' ') { e.preventDefault(); el.click(); } });
        return el;
    }

    /* ─── Highlight marker ─── */
    function _hlMarker(id, on) {
        var m = _markers[id];
        if (!m) return;
        if (on) {
            m.el.classList.add('active');
            m.el.style.zIndex = 9999;
        } else {
            m.el.classList.remove('active');
            m.el.style.zIndex = '';
        }
    }

    function _clearMarkers(){
        Object.keys(_markers).forEach(function(k){
            try{ _markers[k].marker.remove(); }catch(e){}
        });
        _markers={};
    }
    window.ghRefreshMarkers = function(markers){
        _clearMarkers();
        _addMarkers(markers||[]);
        _cfg.markers = markers||[];
        // Region follows location: if the new result set is entirely outside the viewport (new city search),
        // reframe once so Arusha results don't sit on a Dar es Salaam map.
        try{
            var list = markers || [];
            if (_map && list.length && _fitted) {
                var b = _map.getBounds();
                var anyVisible = list.some(function(m){ return m.lat && m.lng && b.contains([m.lng, m.lat]); });
                if (!anyVisible) {
                    if (list.length === 1) {
                        _map.flyTo({ center: [list[0].lng, list[0].lat], zoom: 14, duration: 800 });
                    } else {
                        var nb = new mapboxgl.LngLatBounds();
                        list.forEach(function(m){ if (m.lat && m.lng) nb.extend([m.lng, m.lat]); });
                        _map.fitBounds(nb, { padding: 56, maxZoom: 14, duration: 800 });
                    }
                }
            }
        }catch(e){}
    };
    window.addEventListener('fastnet:markers-update', function(e){
        if(e.detail) window.ghRefreshMarkers(e.detail);
    });
    window.addEventListener('fastnet:currency-change', function(){
        try { if (window.ghRefreshMarkers && _cfg && _cfg.markers) window.ghRefreshMarkers(_cfg.markers); } catch (e) {}
    });

    /* ─── Map error overlay with retry — honest failure, recoverable ─── */
    function _showMapError(){
        var el = document.getElementById('gh-interactive-map');
        if(!el || el.querySelector('.gh-map-error')) return;
        var ov = document.createElement('div');
        ov.className = 'gh-map-error';
        ov.setAttribute('role', 'alert');
        ov.style.cssText = 'position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:#e8ecef;z-index:6;text-align:center;padding:20px;';
        ov.innerHTML = '<div style="font-family:\'IBM Plex Sans\',Inter,Roboto,sans-serif;">'
            + '<div style="font-size:15px;font-weight:600;color:#202124;margin-bottom:4px;">Map couldn\'t load</div>'
            + '<div style="font-size:12.5px;color:#5f6368;margin-bottom:12px;">Check your connection — the stays list still works.</div>'
            + '<button type="button" id="gh_map_retry" style="background:#0f62fe;color:#fff;border:none;border-radius:4px;padding:9px 20px;font-size:13px;font-weight:600;cursor:pointer;">Retry map</button>'
            + '</div>';
        el.appendChild(ov);
        var btn = ov.querySelector('#gh_map_retry');
        if(btn) btn.addEventListener('click', function(){ window.ghRetryMap(); });
    }
    window.ghRetryMap = function(){
        var el = document.getElementById('gh-interactive-map');
        if(el){ var ov = el.querySelector('.gh-map-error'); if(ov) ov.remove(); }
        try{ if(_map){ _map.remove(); } }catch(e){}
        _map = null; window._ghMap = null;
        _inited = false; _fitted = false; _boxTries = 0; _markers = {};
        if (_loadWatchdog) { try{ clearTimeout(_loadWatchdog); }catch(e){} _loadWatchdog = null; }
        try{ window.initGhHomeMap(_cfg || window._ghCfg); }catch(e){ console.warn('map retry', e); }
    };

    /* ─── Highlight card in list ─── */
    function _hlCard(id, on) {
        var card = document.getElementById('gh-card-' + id);
        if (!card) return;
        card.classList.toggle('map-hovered', on);
    }

    /* ─── Public API ─── */
