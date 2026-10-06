/* FastNetState URL engine. Split for the 300-line cap — load url, ui, state in order (shared globals, no IIFE). */
/**
 * FastNetState 2.0 — Enterprise URL State, Deep-Linking & History Engine
 * Spec: ?city=Arusha&checkin=2026-10-15&checkout=2026-10-18&adults=2&children=0&rooms=1&price_min=50000&price_max=350000&amenities=wifi,pool&property_type=Hotel&rating=4.5
 * Legacy aliases kept: destination↔city, checkIn↔checkin, min_price↔price_min, etc.
 * Zero reload on filter changes, popstate support, AJAX hydration with AbortController, skeleton shimmer
 */

'use strict';
const SPEC_ALLOWED = ['city','checkin','checkout','adults','children','rooms','amenities','price_min','price_max','rating','free_cancellation','property_type','payment','meals','neighborhood','sort','lat','lng','bounds','offers'];
var LEGACY_MAP = {destination:'city', q:'city', checkIn:'checkin', checkOut:'checkout', min_price:'price_min', max_price:'price_max', bbox:'bounds'};
var REVERSE_MAP = {city:'destination', checkin:'checkIn', checkout:'checkOut', price_min:'min_price', price_max:'max_price'};
const ALL_ALLOWED = [...new Set([...SPEC_ALLOWED, ...Object.keys(LEGACY_MAP), ...Object.values(LEGACY_MAP), 'destination','checkIn','checkOut','min_price','max_price','q'])];

function aliasNormalize(obj){
  const out={...obj};
  Object.keys(LEGACY_MAP).forEach(legacy=>{
    const spec=LEGACY_MAP[legacy];
    if(out[legacy]!==undefined && out[legacy]!=='' && (out[spec]===undefined || out[spec]==='')){
      out[spec]=out[legacy];
    }
  });
  // also copy spec -> legacy for templates that read legacy
  Object.keys(REVERSE_MAP).forEach(spec=>{
    const legacy=REVERSE_MAP[spec];
    if(out[spec]!==undefined && out[spec]!=='' && (out[legacy]===undefined || out[legacy]==='')){
      out[legacy]=out[spec];
    }
  });
  return out;
}
function cleanParams(obj){
  const aliased=aliasNormalize(obj);
  const out={};
  Object.keys(aliased).forEach(k=>{
    if(!SPEC_ALLOWED.includes(k) && !['destination','checkIn','checkOut','min_price','max_price'].includes(k)) return;
    // only keep spec as canonical for URL building; but we will build spec-only URL
    if(!SPEC_ALLOWED.includes(k)) return;
    let v=aliased[k];
    if(v===undefined||v===null) return;
    v=String(v).trim();
    if(v==='') return;
    if(k==='amenities'){
      const parts=v.split(',').map(s=>s.trim()).filter(Boolean);
      const uniq=[...new Set(parts)];
      if(!uniq.length) return;
      v=uniq.join(',');
    }
    if(k==='price_min' || k==='price_max'){
      if(v!=='' && isNaN(Number(v))) return;
    }
    out[k]=v;
  });
  if(out['sort']==='recommended' || out['sort']==='') delete out['sort'];
  return out;
}
function correctDates(params){
  const today=new Date(); today.setHours(0,0,0,0);
  let ci=params['checkin'] ? new Date(params['checkin']+'T00:00:00') : null;
  let co=params['checkout'] ? new Date(params['checkout']+'T00:00:00') : null;
  if(ci && ci < today){
    const fixed=new Date(today); fixed.setDate(fixed.getDate()+7);
    params['checkin']=fixed.toISOString().slice(0,10);
    ci=fixed;
  }
  if(ci && co && co <= ci){
    const nxt=new Date(ci); nxt.setDate(nxt.getDate()+1);
    params['checkout']=nxt.toISOString().slice(0,10);
  }
  return params;
}
function parseUrl(){
  const sp=new URLSearchParams(window.location.search);
  const o={};
  ALL_ALLOWED.forEach(k=>{
    const v=sp.get(k);
    if(v!==null) o[k]=v;
  });
  const normalized=aliasNormalize(o);
  // pick spec priority
  const specOut={};
  SPEC_ALLOWED.forEach(k=>{ if(normalized[k]!==undefined && normalized[k]!=='' ) specOut[k]=normalized[k]; });
  // also keep raw for sync
  return {...specOut, _raw:o};
}
function buildUrl(params){
  const withAlias=aliasNormalize(params);
  const clean=correctDates(cleanParams(withAlias));
  const sp=new URLSearchParams();
  // spec primary; sort keys for stable URL
  Object.keys(clean).sort().forEach(k=>sp.set(k, clean[k]));
  const qs=sp.toString();
  return window.location.pathname + (qs ? '?' + qs : '');
}
let _abort=null;
let _lastFetchUrl='';
let _lastSuccessUrl='';
// Instant client cache: repeat searches (typing, back/forward) render in ~0ms.
var _jsonCache=new Map();
const _CACHE_TTL=60000, _CACHE_FRESH=20000;
function _cacheGet(k){ const e=_jsonCache.get(k); if(!e) return null; if(Date.now()-e.t>_CACHE_TTL){ _jsonCache.delete(k); return null; } return e; }
function _cacheSet(k,d){ if(_jsonCache.size>60){ const f=_jsonCache.keys().next().value; _jsonCache.delete(f); } _jsonCache.set(k,{t:Date.now(),d}); }
function applyHydrateData(data){
    if(data.html){
      const container=document.getElementById('gh-cards-container');
      if(container){
        let htmlToInject=data.html;
        try{
          const tmp=document.createElement('div');
          tmp.innerHTML=data.html;
          const inner=tmp.querySelector('#gh-cards-container');
          if(inner){ htmlToInject=inner.innerHTML; }
        }catch(e){}
        container.innerHTML=htmlToInject;
      }
    }
    if(data.markers){
      if(window._ghMap && typeof window.ghRefreshMarkers==='function'){
        window.ghRefreshMarkers(data.markers);
      } else if(window._ghMap){
        window.dispatchEvent(new CustomEvent('fastnet:markers-update', {detail:data.markers}));
      }
    }
    refreshDetailLinks();
    const live=document.getElementById('gh_results_live');
    if(live && data.totalCount!==undefined) live.textContent=data.totalCount + ' stays';
}
function showShimmer(on){
  const shim=document.getElementById('gh-shimmer-container');
  const section=document.getElementById('gh_results_section');
  const btn=document.getElementById('fns_search_btn');
  const chip=document.getElementById('fns_shimmer_chips');

  // Container swap and button state go through the canonical loading
  // controller so this path stays idempotent and always has a matching hide.
  FastnetLoading.skeleton('gh-cards-container', on, shim);
  if(shim&&on) shim.setAttribute('aria-hidden','false');
  if(shim&&!on) shim.setAttribute('aria-hidden','true');
  if(chip) chip.classList.toggle('show', !!on);
  if(section) section.setAttribute('aria-busy', on ? 'true' : 'false');
  if(btn){ btn.setAttribute('aria-busy', on ? 'true' : 'false'); btn.disabled=!!on; }
}
async function hydrate(url){
  const fetchUrl = url + (url.includes('?')?'&':'?') + 'format=json';
  // Case-insensitive cache key: ?city=ARUSHA and ?city=arusha share one entry.
  const cacheKey = fetchUrl.toLowerCase();
  // only skip if last successful fetch was same URL
  if(_lastSuccessUrl===fetchUrl) return;
  // Instant hit: render cache in 0ms, skip shimmer + network when fresh.
  const hit=_cacheGet(cacheKey);
  if(hit && (Date.now()-hit.t)<_CACHE_FRESH){
    try{ applyHydrateData(hit.d); }catch(e){}
    _lastSuccessUrl=fetchUrl; _lastFetchUrl=fetchUrl;
    return hit.d;
  }
  // Stale-while-revalidate: paint old results now, refresh in background.
  if(hit){
    try{ applyHydrateData(hit.d); }catch(e){}
  }
  if(_abort) _abort.abort();
  _abort=new AbortController();
  _lastFetchUrl=fetchUrl;
  if(!hit) showShimmer(true);
  try{
    const res=await fetch(fetchUrl, {signal:_abort.signal, headers:{'X-Requested-With':'XMLHttpRequest'}});
    if(!res.ok) throw new Error('fetch '+res.status);
    const data=await res.json();
    _cacheSet(cacheKey, data);
    applyHydrateData(data);
    _lastSuccessUrl=fetchUrl;
    return data;
  }catch(e){
    if(e.name!=='AbortError'){
      console.warn('[FastNetState] hydrate failed',e);
      _lastSuccessUrl='';
      // Mature UI: tell the user stale results are shown (old cards stay mounted) instead of failing silently
      if(typeof window.fnsToast==='function') window.fnsToast('Couldn’t refresh results — showing saved list.');
    } else {
      _lastSuccessUrl='';
    }
  }finally{
    // No artificial minimum: the skeleton used to be pinned for 380ms even
    // when the results were already cached and on screen. A sub-frame flash
    // is avoided by the reveal transition instead of by holding the placeholder.
    showShimmer(false);
  }
}
function refreshDetailLinks(){
  const state=cleanParams(aliasNormalize(parseUrl()));
  const qs=new URLSearchParams(state).toString();
  document.querySelectorAll('.gh-card, .fns-card').forEach(card=>{
    const id=card.getAttribute('data-property-id');
    if(!id) return;
    card.querySelectorAll('a[href*="/hotel-detail"]').forEach(a=>{
      const base='/hotel-detail/'+id;
      a.href=base + (qs ? '?'+qs : '');
    });
    card.setAttribute('onclick', "window.location='/hotel-detail/"+id+(qs ? '?'+qs : '')+"'");
  });
}
