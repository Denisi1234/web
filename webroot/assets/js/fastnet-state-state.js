/* FastNetState URL engine. Split for the 300-line cap — load url, ui, state in order (shared globals, no IIFE). */
const FastNetState={
  parse: parseUrl,
  buildUrl,
  getState: parseUrl,
  setState(partial, opts={}){
    const cur=aliasNormalize(parseUrl());
    const next={...cur, ...aliasNormalize(partial)};
    Object.keys(partial).forEach(k=>{ const spec=LEGACY_MAP[k]||k; if(partial[k]===''||partial[k]===null||partial[k]===undefined){ delete next[k]; delete next[spec]; if(REVERSE_MAP[k]) delete next[REVERSE_MAP[k]]; if(LEGACY_MAP[k]) delete next[LEGACY_MAP[k]]; }});
    // City changed → drop stale map position (bounds/lat/lng) so the old
    // viewport can never filter out the new city's backend results
    // (e.g. Arusha search with leftover Dar bounds returned empty).
    // Values explicitly passed by the caller (place-picker coords) are kept.
    (function(){
      const hasNewCity=['city','destination','q'].some(function(k){ return partial[k]!==undefined && partial[k]!==null && String(partial[k]).trim()!==''; });
      if(!hasNewCity) return;
      const norm=function(v){ return String(v||'').trim().toLowerCase(); };
      const newCity=norm(partial.city!==undefined?partial.city:(partial.destination!==undefined?partial.destination:partial.q));
      const oldCity=norm(cur.city!==undefined?cur.city:(cur.destination!==undefined?cur.destination:cur.q));
      if(newCity==='' || newCity===oldCity) return;
      const given={};
      Object.keys(partial).forEach(function(k){ given[LEGACY_MAP[k]||k]=partial[k]; });
      ['bounds','lat','lng'].forEach(function(mk){
        const v=given[mk];
        if(v===''||v===null||v===undefined){ delete next[mk]; }
      });
    })();
    // Keep search-form hidden inputs in sync so a full submit sends the same state.
    try{
      const latEl=document.getElementById('gh_lat'), lngEl=document.getElementById('gh_lng'), bbEl=document.getElementById('gh_bbox');
      if(latEl) latEl.value=next.lat||'';
      if(lngEl) lngEl.value=next.lng||'';
      if(bbEl) bbEl.value=next.bounds||'';
    }catch(e){}
    const url=buildUrl(next);
    const method=opts.replace ? 'replaceState':'pushState';
    if(url!==window.location.pathname+window.location.search){
      window.history[method]({fastnet:true},'',url);
    }
    syncUI(next);
    hydrate(url);
    return url;
  },
  replaceState(partial){ return this.setState(partial,{replace:true}); },
  pushState(partial){ return this.setState(partial,{replace:false}); },
  syncUI, hydrate
};
window.FastNetState=FastNetState;

// intercept chip links — instant blue feedback on click, then sync
document.addEventListener('click', function(e){
  const a=e.target.closest('a.fns-chip, a.gh-chip');
  if(!a) return;
  const href=a.getAttribute('href');
  if(!href || (!href.startsWith('/?') && !href.startsWith('/') && !href.startsWith('?'))) return;
  e.preventDefault();
  // instant visual: toggle blue immediately for amenity chips (optimistic)
  const filter=a.getAttribute('data-filter');
  if(filter==='amenities'){
    const willBeActive=!a.classList.contains('active');
    a.classList.toggle('active', willBeActive);
    a.setAttribute('aria-pressed', willBeActive?'true':'false');
    if(navigator.vibrate) try{ navigator.vibrate(10); }catch(e){}
  }
  const url=new URL(href, window.location.origin);
  const params={};
  url.searchParams.forEach((v,k)=>params[k]=v);
  const built=url.pathname + url.search;
  window.history.pushState({fastnet:true},'',built);
  syncUI(params);
  hydrate(built);
}, true);

window.addEventListener('popstate', function(){
  const state=aliasNormalize(parseUrl());
  const corrected=correctDates({...state});
  const url=buildUrl(corrected);
  if(url!==window.location.pathname+window.location.search){
    window.history.replaceState({fastnet:true},'',url);
  }
  syncUI(corrected);
  hydrate(url);
});

document.addEventListener('DOMContentLoaded', function(){
  // ensure amenities chips start white (only blue when actively filtered)
  try{ syncUI(parseUrl()); }catch(e){}
  // Removed a second, hand-rolled 2px progress bar that was appended to the
  // body here and stacked on top of the real one. Progress is now owned
  // solely by FastnetLoading.bar.
  const form=document.getElementById('gh_search_form');
  if(form){
    form.addEventListener('submit', function(e){
      e.preventDefault();
      const fd=new FormData(form);
      const obj={};
      fd.forEach((v,k)=>{
        if(k==='amenities[]'){ obj['amenities']= obj['amenities'] ? obj['amenities']+','+v : v; }
        else obj[k]=v;
      });
      // ensure spec sync: city/destination, price_min/min_price etc
      if(obj['destination'] && !obj['city']) obj['city']=obj['destination'];
      if(obj['city'] && !obj['destination']) obj['destination']=obj['city'];
      if(obj['checkin'] && !obj['checkIn']) obj['checkIn']=obj['checkin'];
      if(obj['checkout'] && !obj['checkOut']) obj['checkOut']=obj['checkout'];
      // recent searches removed — nothing stored
      FastNetState.pushState(obj);
    });
  }
  const dest=document.getElementById('gh_dest');
  if(dest){
    let t=null;
    dest.addEventListener('input', function(){
      clearTimeout(t);
      const v=this.value.trim();
      t=setTimeout(()=>FastNetState.replaceState({city:v, destination:v}), 450);
    });
  }
  // hook guests stepper after search bar loads (in case fnsG not yet defined, patch later)
  function patchGuests(){
    if(typeof window.fnsG==='function' && !window.fnsG._patched){
      const orig=window.fnsG;
      window.fnsG=function(f,d){
        const r=orig(f,d);
        const ad=document.getElementById('gh_ad')?.value, ch=document.getElementById('gh_ch')?.value, rm=document.getElementById('gh_rm')?.value;
        FastNetState.pushState({adults:ad, children:ch, rooms:rm});
        return r;
      };
      window.fnsG._patched=true;
    }
    if(typeof window.ghG==='function' && !window.ghG._patched){
      const orig=window.ghG;
      window.ghG=function(f,d){
        const r=orig(f,d);
        const ad=document.getElementById('gh_ad')?.value, ch=document.getElementById('gh_ch')?.value, rm=document.getElementById('gh_rm')?.value;
        FastNetState.pushState({adults:ad, children:ch, rooms:rm});
        return r;
      };
      window.ghG._patched=true;
    }
  }
  patchGuests();
  // date stepper patch
  function patchDate(){
    if(typeof window.fnsStep==='function' && !window.fnsStep._patched){
      const o=window.fnsStep;
      window.fnsStep=function(f,d){ const r=o(f,d); const ci=document.getElementById('gh_ci')?.value, co=document.getElementById('gh_co')?.value; FastNetState.pushState({checkin:ci, checkout:co, checkIn:ci, checkOut:co}); return r; };
      window.fnsStep._patched=true;
    }
    if(typeof window.ghStep==='function' && !window.ghStep._patched){
      const o=window.ghStep;
      window.ghStep=function(f,d){ const r=o(f,d); const ci=document.getElementById('gh_ci')?.value, co=document.getElementById('gh_co')?.value; FastNetState.pushState({checkin:ci, checkout:co, checkIn:ci, checkOut:co}); return r; };
      window.ghStep._patched=true;
    }
  }
  patchDate();

  /* The guest/date stepper patches above can only apply once the search bar's
     own deferred script has run. Both pollers used to run every 800ms for the
     entire life of the page — 1.25Hz of wakeups that no-op once patched.
     Poll until every target is patched, then stop for good. */
  (function patchWhenReady(){
    var PATCH_INTERVAL = 400;
    var PATCH_CEILING = 60; // ~24s: long enough for deferred scripts under load
    var tries = 0;

    var timer = setInterval(function(){
      patchGuests();
      patchDate();
      tries++;

      var patched =
        (typeof window.fnsStep !== 'function' || window.fnsStep._patched) &&
        (typeof window.ghStep !== 'function' || window.ghStep._patched) &&
        (window.ghG === undefined || window.ghG._patched);

      if (patched || tries >= PATCH_CEILING) clearInterval(timer);
    }, PATCH_INTERVAL);
  })();
  // ── Fastest search: idle-prefetch popular destinations ──
  // Warms client cache + server 90s cache + CDN so Arusha/Zanzibar/Dar open instantly.
  function prefetchPopular(){
    try{
      const cur=new URLSearchParams(window.location.search);
      const curCity=(cur.get('city')||cur.get('destination')||'').toLowerCase();
      ['Arusha','Zanzibar','Dar es Salaam'].forEach(function(city){
        if(curCity===city.toLowerCase()) return;
        const u='/?city='+encodeURIComponent(city)+'&format=json';
        const k=u.toLowerCase();
        if(_jsonCache.has(k)) return;
        fetch(u,{headers:{'X-Requested-With':'XMLHttpRequest'}}).then(function(r){
          if(!r.ok) return null; return r.json();
        }).then(function(d){ if(d) _cacheSet(k,d); }).catch(function(){});
      });
    }catch(e){}
  }
  if('requestIdleCallback' in window){
    requestIdleCallback(prefetchPopular,{timeout:4000});
  } else {
    setTimeout(prefetchPopular,2500);
  }
});

// bounds helper
window.FastNetMapBounds={update(boundsStr,isPassive){ if(isPassive) FastNetState.replaceState({bounds:boundsStr}); else FastNetState.pushState({bounds:boundsStr}); }};

// Shimmer triggers.
//
// These were defined a second time here, and because fastnet-state.js is
// deferred this version won - it set opacity/display directly and had no hide
// counterpart, no timer and no event dispatch. The filter modal calls this on
// submit, so the skeleton could stay on screen permanently.
//
// Both now delegate to the canonical controller, which is idempotent and
// paired with FastnetLoading.skeleton(..., false).
window.fnsTriggerShimmer = function () {
  FastnetLoading.skeleton('gh-cards-container', true, document.getElementById('gh-shimmer-container'));
};
window.ghTriggerShimmer = window.fnsTriggerShimmer;
window.ghHideShimmer = window.ghHideShimmer || function () {
  FastnetLoading.skeleton('gh-cards-container', false, document.getElementById('gh-shimmer-container'));
};
