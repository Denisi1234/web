/* Home map. Split for the 300-line cap — load core, markers, ui in order (shared globals, no IIFE). */
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
