/* Home map. Split for the 300-line cap — load core, markers, ui in order (shared globals, no IIFE). */
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

        // Placeholder tokens (pk.your_real… / *.demo) start with pk. but buy
        // nothing — Mapbox 401s and the frame goes grey. Reject them here so
        // the OSM style is chosen up front instead of after a failure.
        var _tok = (typeof window.MAPBOX_TOKEN === 'string') ? window.MAPBOX_TOKEN : '';
        var hasValidToken = _tok.indexOf('pk.') === 0 && _tok.indexOf('your_real') === -1 && _tok.slice(-5) !== '.demo';
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
