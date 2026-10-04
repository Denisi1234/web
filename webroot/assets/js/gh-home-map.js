/**
 * fastnetstays.com — FastNet OWN Map Style (not Google)
 * Mapbox GL JS with FastNet price pills: [🏨] TSH 176,697
 * Style: FastNet Streets (custom, not Google light) — own colors.
 */
(function () {
    'use strict';

    var _map     = null;
    var _markers = {};      // propId → { marker, el }
    var _sat     = false;
    var _cfg     = null;
    var _inited  = false;
    var _fitted  = false;   // fit bounds only on first markers load — refreshes must not yank the map
    var _boxTries = 0;      // zero-size-container retries before construct
    var _loadWatchdog = null;

    window.initGhHomeMap = function (cfg) {
        if (_inited) return;
        // keep cfg for mobile lazy init (when container was hidden)
        if (cfg) {
            _cfg = cfg;
            window._ghCfg = cfg;
        } else if (_cfg) {
            cfg = _cfg;
        } else if (window._ghCfg) {
            cfg = window._ghCfg;
            _cfg = cfg;
        }
        if (!cfg) return;

        if (typeof mapboxgl === 'undefined') {
            console.warn('[FastNet] mapboxgl not loaded');
            return;
        }
        // defer init if container hidden on mobile (d-none) — will init when View Map clicked
        var _container = document.getElementById('gh-interactive-map');
        if (_container && _container.offsetParent === null && window.innerWidth < 992) {
            console.info('[FastNet] Deferring map init — container hidden on mobile');
            return;
        }
        var osmStyle = {
            version: 8,
            sources: {
                'osm-tiles': {
                    type: 'raster',
                    tiles: [
                        'https://a.tile.openstreetmap.org/{z}/{x}/{y}.png',
                        'https://b.tile.openstreetmap.org/{z}/{x}/{y}.png',
                        'https://c.tile.openstreetmap.org/{z}/{x}/{y}.png'
                    ],
                    tileSize: 256,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                }
            },
            layers: [
                {
                    id: 'osm-tiles-layer',
                    type: 'raster',
                    source: 'osm-tiles',
                    minzoom: 0,
                    maxzoom: 19
                }
            ]
        };

        var hasValidToken = window.MAPBOX_TOKEN && typeof window.MAPBOX_TOKEN === 'string' && window.MAPBOX_TOKEN.startsWith('pk.');
        var _style = (cfg && cfg.style) || window.MAPBOX_STYLE;
        
        if (!hasValidToken || !_style || _style.indexOf('mapbox://') !== 0) {
            if (!_style || _style.indexOf('mapbox://') === 0) {
                _style = osmStyle;
            }
        }
        
        if (hasValidToken && typeof mapboxgl !== 'undefined') {
            mapboxgl.accessToken = window.MAPBOX_TOKEN;
        }
        
        // Never construct into a zero-size box: the canvas would lock at
        // 0×0 and stay blank even after layout settles. Wait for geometry.
        var _box = document.getElementById('gh-interactive-map');
        if (_box && (_box.clientWidth === 0 || _box.clientHeight === 0)) {
            _boxTries += 1;
            if (_boxTries < 8) {
                setTimeout(function(){ try{ window.initGhHomeMap(cfg); }catch(e){} }, 300);
                return;
            }
        }

        _inited = true;

        try {
            _map = new mapboxgl.Map({
                container: 'gh-interactive-map',
                style: _style,
                center: [cfg.defaultLng || 39.2450, cfg.defaultLat || -6.7725],
                zoom:   cfg.defaultZoom || 12,
                attributionControl: false,
            });
        } catch (err) {
            console.warn('[FastNet] Primary map style init failed, switching to OpenStreetMap', err);
            try {
                _map = new mapboxgl.Map({
                    container: 'gh-interactive-map',
                    style: osmStyle,
                    center: [cfg.defaultLng || 39.2450, cfg.defaultLat || -6.7725],
                    zoom:   cfg.defaultZoom || 12,
                    attributionControl: false,
                });
            } catch (fallbackErr) {
                console.error('[FastNet] OpenStreetMap fallback also failed', fallbackErr);
                // Dead WebGL/driver: say so with a retry instead of silent gray.
                try{ _showMapError(); }catch(e){}
                return;
            }
        }

        window._ghMap = _map;

        _map.addControl(new mapboxgl.AttributionControl({ compact: true }), 'bottom-right');
        // Bulletproof sizing: the canvas locks to container size at
        // construction, but Bootstrap grid + webfonts settle afterwards —
        // without re-sync the tiles cover only part of the frame (half map).
        // Re-sync on every signal that can change geometry. resize() never
        // changes container size, so the observer cannot loop.
        var _rsz = function(){ try{ if(_map) _map.resize(); }catch(e){} };
        setTimeout(_rsz, 100);
        setTimeout(_rsz, 500);
        setTimeout(_rsz, 1500);
        window.addEventListener('load', _rsz);
        try{
            if(document.fonts && document.fonts.ready) document.fonts.ready.then(_rsz);
        }catch(e){}
        try{
            if(window.ResizeObserver){
                var _mc=document.getElementById('gh-interactive-map');
                if(_mc) new ResizeObserver(function(){ _rsz(); }).observe(_mc);
            }
        }catch(e){}
        setTimeout(function(){
            try{ _map.resize(); }catch(e){}
            var el=document.getElementById('gh-interactive-map');
            if(el){
                el.style.background='';
                el.style.display='';
                var fb=el.querySelector('div[style*="Map loading"]');
                if(fb && !el.classList.contains('mapboxgl-map')) fb.remove();
                if(el.firstChild && el.firstChild.textContent && el.firstChild.textContent.indexOf('Map loading')!==-1){
                    el.firstChild.remove();
                }
                el.removeAttribute('data-fallback-shown');
            }
        }, 200);
        _map.on('error', function(e){
            console.warn('[FastNet] Mapbox tile error', e && e.error && e.error.message);
            if(e && e.error && (e.error.status === 401 || e.error.status === 403)){
                console.info('[FastNet] Mapbox token unauthorized — switching to OpenStreetMap tiles');
                try{ _map.setStyle(osmStyle); }catch(err){}
            }
        });
        // Watchdog: never leave a silent gray box. If tiles never arrive,
        // say so honestly with a retry — listings beside it keep working.
        if (_loadWatchdog) { try{ clearTimeout(_loadWatchdog); }catch(e){} _loadWatchdog = null; }
        _loadWatchdog = setTimeout(function(){
            var ok = false;
            try{ ok = _map && _map.loaded(); }catch(e){ ok = false; }
            if (!ok) _showMapError();
        }, 10000);
        _map.on('load', function(){
            if (_loadWatchdog) { try{ clearTimeout(_loadWatchdog); }catch(e){} _loadWatchdog = null; }
        });
        _map.on('idle', function(){
            if (!_loadWatchdog) return;
            var ok = false;
            try{ ok = _map && _map.loaded(); }catch(e){ ok = false; }
            if (ok) { try{ clearTimeout(_loadWatchdog); }catch(e){} _loadWatchdog = null; }
        });

        _map.on('load', function () {
            try{ _map.resize(); }catch(e){}
            // Focused search area wins: fit the picked viewport once, then
            // hand control back to result-driven framing below.
            try{
                if (cfg && cfg.focusBbox && typeof cfg.focusBbox === 'string') {
                    var p = cfg.focusBbox.split(',').map(Number);
                    if (p.length === 4 && p.every(isFinite)) {
                        _map.fitBounds([[p[1], p[0]], [p[3], p[2]]], { padding: 48, duration: 900 });
                        _fitted = true;
                    }
                }
            }catch(e){}
            // clear fallback overlay once map actually loads
            var el=document.getElementById('gh-interactive-map');
            if(el){
                el.style.background='';
                el.removeAttribute('data-fallback-shown');
                var fb=el.querySelector('div[style*="Map loading"]');
                if(fb) fb.remove();
            }
            _addMarkers(cfg.markers || []);
            // FastNet own colors + hide POIs (brand, not Google)
            try{
                var style=_map.getStyle();
                style.layers.forEach(function(l){
                    var id=(l.id||'').toLowerCase();
                    if(id.indexOf('poi')!==-1 || id.indexOf('restaurant')!==-1 || id.indexOf('shop')!==-1 || id.indexOf('food')!==-1 || id.indexOf('attraction')!==-1 || id.indexOf('sightseeing')!==-1){
                        _map.setLayoutProperty(l.id,'visibility','none');
                    }
                    // FastNet accents
                    if(id==='water' || id.indexOf('water')!==-1){
                        try{ _map.setPaintProperty(l.id,'fill-color','#e8f0fe'); }catch(e){}
                    }
                    if(id.indexOf('park')!==-1 || id.indexOf('green')!==-1 || id.indexOf('landcover')!==-1){
                        try{ if(_map.getPaintProperty(l.id,'fill-color')!==undefined) _map.setPaintProperty(l.id,'fill-color','#e6f4ea'); }catch(e){}
                    }
                });
                // roads subtle
                try{
                    if(_map.getLayer('road-primary')) _map.setPaintProperty('road-primary','line-color','#ffffff');
                }catch(e){}
            }catch(e){}
            // handle empty
            if(!cfg.markers || cfg.markers.length===0){
                var el=document.getElementById('gh-interactive-map');
                if(el && !el.querySelector('.gh-map-empty')){
                    var msg=document.createElement('div');
                    msg.className='gh-map-empty';
                    msg.style.cssText='position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,.92);z-index:5;font-family:\'IBM Plex Sans\',Inter,Roboto,sans-serif;color:#525252;font-size:13px;text-align:center;padding:16px;';
                    msg.innerHTML='<div><div style="font-size:28px;margin-bottom:8px;">🗺️</div>No stays in this area<br><span style="font-size:11px;color:#9aa0a6;">Pan or zoom to explore</span></div>';
                    el.appendChild(msg);
                }
            }
        });

        // UX: debounced Update list — only pulse after idle 800ms, not every micro-move
        var _moveDebounce = null;
        var _lastCenter = null;
        _map.on('movestart', function(){ _lastCenter = _map.getCenter().toString(); });
        _map.on('moveend', function () {
            clearTimeout(_moveDebounce);
            _moveDebounce = setTimeout(function(){
                var cur = _map.getCenter().toString();
                if(cur === _lastCenter) return;
                var btn = document.getElementById('gh_update_list_btn');
                if (btn) { btn.classList.remove('gh-pulse'); void btn.offsetWidth; btn.classList.add('gh-pulse'); btn.style.display='inline-flex'; }
                // FastNetState: passive bounds sync via replaceState (no reload)
                try{
                    var b=_map.getBounds();
                    var ne=b.getNorthEast(), sw=b.getSouthWest();
                    var bounds = ne.lat.toFixed(4)+','+ne.lng.toFixed(4)+','+sw.lat.toFixed(4)+','+sw.lng.toFixed(4);
                    if(window.FastNetState) window.FastNetState.replaceState({bounds:bounds});
                }catch(e){}
            }, 800);
        });
    };

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
    window.ghHighlightMarker = function (id, on) { _hlMarker(id, on); };

    window.ghFocusMarker = function (id) {
        var m = _markers[id];
        if (!m || !_map) return;
        var ll = m.marker.getLngLat();
        _map.flyTo({ center: [ll.lng, ll.lat], zoom: 15, speed: 1.2 });
        _hlMarker(id, true);
        setTimeout(function () { _hlMarker(id, false); }, 2500);
    };

    window.ghToggleSatellite = function () {
        if (!_map) return;
        _sat = !_sat;
        var base = window.MAPBOX_STYLE || 'mapbox://styles/mapbox/streets-v12';
        _map.setStyle(_sat ? 'mapbox://styles/mapbox/satellite-streets-v12' : base);
        // Re-add markers after style change + keep FastNet clean (no POI icons)
        _markers = {};
        _map.once('style.load', function () {
            try{
                var st=_map.getStyle();
                st.layers.forEach(function(l){
                    var id=(l.id||'').toLowerCase();
                    if(id.indexOf('poi')!==-1 || id.indexOf('restaurant')!==-1 || id.indexOf('shop')!==-1 || id.indexOf('food')!==-1 || id.indexOf('attraction')!==-1 || id.indexOf('sightseeing')!==-1){
                        _map.setLayoutProperty(l.id,'visibility','none');
                    }
                });
            }catch(e){}
            if (_cfg) _addMarkers(_cfg.markers || []);
        });
        var btn = document.getElementById('gh_layers_btn');
        if (btn) btn.classList.toggle('active', _sat);
    };

    window.ghUpdateListFromMap = function () {
        if (!_map) return;
        var c = _map.getCenter();
        var latEl = document.getElementById('gh_lat');
        var lngEl = document.getElementById('gh_lng');
        if (latEl) latEl.value = c.lat.toFixed(6);
        if (lngEl) lngEl.value = c.lng.toFixed(6);
        // FastNet client-side filter — hide cards outside bounds (no full submit needed, UX fast)
        try{
            var b=_map.getBounds();
            var visible=0, total=0;
            document.querySelectorAll('.gh-card').forEach(function(card){
                var id=card.getAttribute('data-property-id');
                var mm=_markers[id];
                total++;
                if(!mm || !mm.marker){ card.style.display=''; visible++; return; }
                var ll=mm.marker.getLngLat();
                var inside=b.contains(ll);
                card.style.display=inside?'':'none';
                if(inside) visible++;
            });
            // update header count if exists
            var countRow=document.querySelector('.gh-results-count-row');
            if(countRow){
                // find the number span — second span
                var txt=countRow.textContent;
                // replace number
                // simple: update the "X results" text node — find span with number
                var spans=countRow.querySelectorAll('span');
                // last attempt: update text containing "results"
                countRow.querySelectorAll('*').forEach(function(el){
                    if(el.textContent && el.textContent.indexOf('results')!==-1 && el.children.length===0){
                        // el is the span with "99 results"
                        // keep original total in data attribute
                    }
                });
            }
            // also update places count in mobile guests bottom
            var places=document.getElementById('gh_m_guests_places');
            if(places) places.textContent=visible+' places';
            // show toast
            var btn=document.getElementById('gh_update_list_btn');
            if(btn){ btn.innerHTML='<span style="width:14px;height:14px;background:#1a73e8;border-radius:2px;display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:10px;">✓</span> <span>Updated — '+visible+' stays</span>'; setTimeout(function(){ btn.innerHTML='<span style="width:14px;height:14px;border:1.5px solid #5f6368;border-radius:2px;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;background:#fff;"></span> <span>Update list when map moves</span>'; },2200); }
        }catch(e){}
        // keep hidden inputs synced; do not auto-submit to keep fast UX
    };
    window.ghRecenter = function(){
        if(!_map || !_cfg) return;
        var ms=_cfg.markers||[];
        if(ms.length===0) return;
        var b=new mapboxgl.LngLatBounds();
        ms.forEach(function(m){ if(m.lat&&m.lng) b.extend([m.lng,m.lat]); });
        _map.fitBounds(b,{padding:56,maxZoom:14,duration:700});
    };
    window.ghToggleFullscreen = function(){
        var frame=document.querySelector('.gh-map-frame');
        if(!frame) return;
        if(!document.fullscreenElement){
            frame.requestFullscreen().catch(function(){});
        } else {
            document.exitFullscreen().catch(function(){});
        }
    };

    window.ghSaveMapPos = function () {
        if (!_map) return;
        try {
            var c = _map.getCenter();
            localStorage.setItem('fn_map_pos', JSON.stringify({ lat: c.lat, lng: c.lng, zoom: _map.getZoom() }));
        } catch (e) {}
    };

    window.ghToggleMobileView = function () {
        var listCol = document.getElementById('gh_list_col');
        var mapCol  = document.getElementById('gh_map_col');
        var sheet   = document.getElementById('gh_mobile_sheet');
        var sheetScroll = document.getElementById('gh_sheet_scroll');
        var icon    = document.getElementById('gh_mob_icon');
        var text    = document.getElementById('gh_mob_text');
        // Desktop fallback: binary toggle
        if (window.innerWidth >= 992) {
            if (!listCol) return;
            var showingList = listCol.style.display !== 'none';
            listCol.style.display = showingList ? 'none' : '';
            if (mapCol) mapCol.style.display = showingList ? 'block' : 'none';
            if (icon) icon.className = showingList ? 'fa-solid fa-list text-warning' : 'fa-solid fa-map-location-dot text-warning';
            if (text) text.textContent = showingList ? 'View List' : 'View Map';
            if (!showingList && _map) setTimeout(function () { _map.resize(); }, 120);
            return;
        }
        // Mobile: show map + horizontal carousel (spec) + peek sheet — real map from backend
        var carousel = document.getElementById('gh_mobile_carousel');
        if (!sheet) return;
        var isOpen = sheet.classList.contains('open');
        var mapCol = document.getElementById('gh_map_col');
        if (!isOpen) {
            // clone cards into sheet on first open
            if (sheetScroll && !sheetScroll.dataset.filled) {
                var cards = document.getElementById('gh-cards-container');
                var header = document.querySelector('.gh-left-scroll');
                if (cards) {
                    var clone = cards.cloneNode(true);
                    clone.id = 'gh-sheet-cards';
                    var resHeader = document.querySelector('#gh_list_col .gh-left-scroll');
                    if (resHeader) {
                        var h = resHeader.querySelector('.gh-results-count-row');
                        if (h) sheetScroll.appendChild(h.cloneNode(true));
                    }
                    sheetScroll.appendChild(clone);
                    sheetScroll.dataset.filled = '1';
                }
            }
            // populate horizontal carousel
            if (carousel && !carousel.dataset.filled) {
                var cards2 = document.getElementById('gh-cards-container');
                if (cards2) {
                    cards2.querySelectorAll('.gh-card').forEach(function(card){
                        var id = card.getAttribute('data-property-id');
                        var clone = card.cloneNode(true);
                        clone.style.flex='0 0 78vw';
                        clone.style.maxWidth='320px';
                        clone.style.cursor='pointer';
                        clone.addEventListener('click', function(){ if(window.ghFocusMarker) window.ghFocusMarker(parseInt(id)); });
                        carousel.appendChild(clone);
                    });
                    carousel.dataset.filled='1';
                    // scroll sync: panning carousel centers marker
                    var scrollTimeout=null;
                    carousel.addEventListener('scroll', function(){
                        clearTimeout(scrollTimeout);
                        scrollTimeout=setTimeout(function(){
                            var center = carousel.scrollLeft + carousel.offsetWidth/2;
                            var best=null, bestDist=Infinity;
                            carousel.querySelectorAll('.gh-card').forEach(function(c){
                                var dist=Math.abs(c.offsetLeft + c.offsetWidth/2 - center);
                                if(dist<bestDist){ bestDist=dist; best=c; }
                            });
                            if(best){ var bid=best.getAttribute('data-property-id'); if(bid && window.ghFocusMarker) window.ghFocusMarker(parseInt(bid)); }
                        }, 150);
                    }, {passive:true});
                }
            }
            sheet.classList.add('open');
            sheet.classList.remove('peek');
            sheet.setAttribute('aria-hidden','false');
            if (carousel) { carousel.style.display='flex'; carousel.setAttribute('aria-hidden','false'); }
            // show real map as full-screen overlay on mobile
            if (mapCol) {
                mapCol.classList.add('gh-mobile-active');
                mapCol.classList.remove('d-none');
                mapCol.style.display='block';
                mapCol.setAttribute('aria-hidden','false');
            }
            var mapWrap = document.querySelector('.gh-map-sticky');
            if (mapWrap) {
                mapWrap.style.display='block';
                if (mapWrap.parentElement) {
                    mapWrap.parentElement.style.display='block';
                    mapWrap.parentElement.classList.remove('d-none');
                }
            }
            // lazy init if deferred on mobile
            if (!_inited && (_cfg || window._ghCfg)) {
                try { window.initGhHomeMap(_cfg || window._ghCfg); } catch(e) { console.warn('mobile init', e); }
            }
            
            // ensure map resizes and fits markers after becoming visible
            var doResizeSequence = function() {
                if (_map) {
                    try { _map.resize(); } catch(e) {}
                    if (window.ghRecenter) try { window.ghRecenter(); } catch(e) {}
                }
            };
            
            setTimeout(doResizeSequence, 100);
            setTimeout(doResizeSequence, 300);
            setTimeout(doResizeSequence, 600);
            
            if (icon) icon.className = 'fa-solid fa-list';
            if (text) text.textContent = 'View List';
            document.body.style.overflow='hidden';
        } else {
            sheet.classList.remove('open');
            sheet.setAttribute('aria-hidden','true');
            if (carousel) { carousel.style.display='none'; carousel.setAttribute('aria-hidden','true'); }
            if (mapCol) {
                mapCol.classList.remove('gh-mobile-active');
                mapCol.classList.add('d-none');
                mapCol.style.display='';
                mapCol.setAttribute('aria-hidden','true');
            }
            if (icon) icon.className = 'fa-solid fa-map-location-dot text-warning';
            if (text) text.textContent = 'View Map';
            document.body.style.overflow='';
            if (_map) setTimeout(function(){ try{ _map.resize(); }catch(e){} }, 120);
        }
    };
    // Sheet drag handle — tap to toggle peek/open
    document.addEventListener('DOMContentLoaded', function(){
        var handle = document.getElementById('gh_sheet_handle');
        var sheet = document.getElementById('gh_mobile_sheet');
        if (!handle || !sheet) return;
        var startY=0, curY=0, dragging=false;
        handle.addEventListener('touchstart', function(e){ dragging=true; startY=e.touches[0].clientY; }, {passive:true});
        handle.addEventListener('touchmove', function(e){ if(!dragging) return; curY=e.touches[0].clientY; }, {passive:true});
        handle.addEventListener('touchend', function(){ if(!dragging) return; dragging=false; var dy = curY - startY; if (Math.abs(dy)>30){ if(dy<0) sheet.classList.add('open'); else sheet.classList.remove('open'); } else { sheet.classList.toggle('open'); } curY=0; });
        handle.addEventListener('click', function(){ sheet.classList.toggle('open'); });
    });
})();
