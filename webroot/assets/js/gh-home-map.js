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

    window.initGhHomeMap = function (cfg) {
        if (_inited) return;
        _cfg = cfg;

        if (!window.MAPBOX_TOKEN || typeof mapboxgl === 'undefined') {
            console.warn('[FastNet] Mapbox token or library not available');
            return;
        }
        _inited = true;
        mapboxgl.accessToken = window.MAPBOX_TOKEN;

        // FastNet own style — not Google light; uses Streets with FastNet accents
        _map = new mapboxgl.Map({
            container: 'gh-interactive-map',
            style: 'mapbox://styles/mapbox/streets-v12',
            center: [cfg.defaultLng, cfg.defaultLat],
            zoom:   cfg.defaultZoom || 12,
            attributionControl: false,
        });

        window._ghMap = _map;

        _map.addControl(new mapboxgl.AttributionControl({ compact: true }), 'bottom-right');

        _map.on('load', function () {
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
                    msg.style.cssText='position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,.92);z-index:5;font-family:Google Sans,Roboto,sans-serif;color:#5f6368;font-size:13px;text-align:center;padding:16px;';
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
                // cluster pill — avg position, show count + avg price
                var avgLat = group.reduce(function(s,x){ return s+x.lat; },0)/group.length;
                var avgLng = group.reduce(function(s,x){ return s+x.lng; },0)/group.length;
                var avgPrice = Math.round(group.reduce(function(s,x){ var p=parseInt((x.label||'').replace(/[^0-9]/g,''))||0; return s+p; },0)/group.length);
                var clusterData = { id:'cluster_'+k, lat:avgLat, lng:avgLng, label: (avgPrice ? 'TSH '+ avgPrice.toLocaleString() : '')+'  •  +'+group.length, title: group.length+' stays', count:group.length, members:group };
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

        // Fit all markers
        if (markers.length > 1) {
            var bounds = new mapboxgl.LngLatBounds();
            markers.forEach(function (m) { if (m.lat && m.lng) bounds.extend([m.lng, m.lat]); });
            _map.fitBounds(bounds, { padding: 56, maxZoom: 14, duration: 800 });
        }
    }

    /* ─── Create DOM element for marker — UX: a11y + contrast ─── */
    function _makeMarkerEl(m) {
        var el = document.createElement('div');
        el.className = 'gh-map-marker';
        el.setAttribute('data-id', m.id);
        el.setAttribute('role', 'button');
        el.setAttribute('tabindex', '0');
        el.setAttribute('aria-label', (m.title || 'Hotel') + ' ' + m.label);
        // GREAT price green ring handled via CSS if label contains GREAT? Keep blue for now
        el.innerHTML =
            '<div class="gh-mm-icon" aria-hidden="true"><i class="fa-solid fa-bed"></i></div>' +
            '<span class="gh-mm-price">' + m.label + '</span>';
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
    };
    window.addEventListener('fastnet:markers-update', function(e){
        if(e.detail) window.ghRefreshMarkers(e.detail);
    });

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
        _map.setStyle(_sat ? 'mapbox://styles/mapbox/satellite-streets-v12' : 'mapbox://styles/mapbox/streets-v12');
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
        // Mobile: show map + horizontal carousel (spec) + peek sheet
        var carousel = document.getElementById('gh_mobile_carousel');
        if (!sheet) return;
        var isOpen = sheet.classList.contains('open');
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
            var mapWrap = document.querySelector('.gh-map-sticky');
            if (mapWrap) { mapWrap.style.display='block'; mapWrap.parentElement.style.display='block'; mapWrap.parentElement.classList.remove('d-none'); }
            if (_map) setTimeout(function(){ _map.resize(); }, 150);
            if (icon) icon.className = 'fa-solid fa-list';
            if (text) text.textContent = 'View List';
            document.body.style.overflow='hidden';
        } else {
            sheet.classList.remove('open');
            sheet.setAttribute('aria-hidden','true');
            if (carousel) { carousel.style.display='none'; carousel.setAttribute('aria-hidden','true'); }
            if (icon) icon.className = 'fa-solid fa-map-location-dot text-warning';
            if (text) text.textContent = 'View Map';
            document.body.style.overflow='';
            if (_map) setTimeout(function(){ _map.resize(); }, 120);
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
