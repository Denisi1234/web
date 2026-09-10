/**
 * FastNetState 2.0 — Enterprise URL State, Deep-Linking & History Engine
 * Spec: ?city=Arusha&checkin=2026-10-15&checkout=2026-10-18&adults=2&children=0&rooms=1&price_min=50000&price_max=350000&amenities=wifi,pool&property_type=Hotel&rating=4.5
 * Legacy aliases kept: destination↔city, checkIn↔checkin, min_price↔price_min, etc.
 * Zero reload on filter changes, popstate support, AJAX hydration with AbortController, skeleton shimmer
 */
(function(){
'use strict';
const SPEC_ALLOWED = ['city','checkin','checkout','adults','children','rooms','amenities','price_min','price_max','rating','free_cancellation','property_type','payment','meals','neighborhood','sort','lat','lng','bounds','offers'];
const LEGACY_MAP = {destination:'city', q:'city', checkIn:'checkin', checkOut:'checkout', min_price:'price_min', max_price:'price_max'};
const REVERSE_MAP = {city:'destination', checkin:'checkIn', checkout:'checkOut', price_min:'min_price', price_max:'max_price'};
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
function showShimmer(on){
  const cards=document.getElementById('gh-cards-container');
  const shim=document.getElementById('gh-shimmer-container');
  const bar=document.getElementById('fastnet-progress');
  if(on){
    if(cards&&shim){ cards.style.opacity='0.55'; shim.style.display='block'; }
    if(bar) bar.style.width='30%';
  } else {
    if(cards&&shim){ cards.style.opacity=''; shim.style.display='none'; }
    if(bar) setTimeout(()=>bar.style.width='0%', 280);
  }
}
async function hydrate(url){
  if(_abort) _abort.abort();
  _abort=new AbortController();
  const fetchUrl = url + (url.includes('?')?'&':'?') + 'format=json';
  if(_lastFetchUrl===fetchUrl) return;
  _lastFetchUrl=fetchUrl;
  showShimmer(true);
  try{
    const res=await fetch(fetchUrl, {signal:_abort.signal, headers:{'X-Requested-With':'XMLHttpRequest'}});
    if(!res.ok) throw new Error('fetch '+res.status);
    const data=await res.json();
    if(data.html){
      const container=document.getElementById('gh-cards-container');
      if(container) container.innerHTML=data.html;
    }
    if(data.markers){
      if(window._ghMap && typeof window.ghRefreshMarkers==='function'){
        window.ghRefreshMarkers(data.markers);
      } else if(window._ghMap){
        window.dispatchEvent(new CustomEvent('fastnet:markers-update', {detail:data.markers}));
      }
    }
    refreshDetailLinks();
  }catch(e){
    if(e.name!=='AbortError') console.warn('[FastNetState] hydrate failed',e);
  }finally{
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
function syncUI(state){
  const s=aliasNormalize(state);
  const city=s.city||s.destination||'';
  const destDom=document.getElementById('gh_dest'); if(destDom && city) destDom.value=city;
  const cityDom=document.getElementById('gh_city'); if(cityDom) cityDom.value=city;
  const mInput=document.getElementById('fns_m_input'); if(mInput && city) mInput.value=city;
  const chipText=document.getElementById('fns_chip_text');
  const ci=s.checkin||s.checkIn||'', co=s.checkout||s.checkOut||'';
  if(ci) {
    const el=document.getElementById('gh_ci'); if(el) el.value=ci;
    const leg=document.getElementById('gh_ci_legacy'); if(leg) leg.value=ci;
    try{
      const fmt=new Date(ci+'T00:00:00').toLocaleDateString('en-US',{weekday:'short',month:'short',day:'numeric'});
      const lbl=document.getElementById('fns_ci_lbl'); if(lbl) lbl.textContent=fmt;
      const mLbl=document.getElementById('fns_m_ci_lbl'); if(mLbl) mLbl.textContent=fmt;
    }catch(e){}
  }
  if(co){
    const el=document.getElementById('gh_co'); if(el) el.value=co;
    const leg=document.getElementById('gh_co_legacy'); if(leg) leg.value=co;
    try{
      const fmt=new Date(co+'T00:00:00').toLocaleDateString('en-US',{weekday:'short',month:'short',day:'numeric'});
      const lbl=document.getElementById('fns_co_lbl'); if(lbl) lbl.textContent=fmt;
      const mLbl=document.getElementById('fns_m_co_lbl'); if(mLbl) mLbl.textContent=fmt;
    }catch(e){}
  }
  if(ci && co){
    try{
      const a=new Date(ci+'T00:00:00'), b=new Date(co+'T00:00:00');
      const nights=Math.max(1, Math.round((b-a)/86400000));
      const badge=document.getElementById('fns_nights_badge'); if(badge) badge.textContent=nights+' night'+(nights!==1?'s':'');
      const mBadge=document.getElementById('fns_m_nights_badge'); if(mBadge) mBadge.textContent=nights+'n';
      const sum=document.getElementById('fns_cal_summary'); if(sum) sum.innerHTML='<strong>'+nights+' night'+(nights!==1?'s':'')+'</strong> · '+new Date(ci+'T00:00:00').toLocaleDateString('en-US',{weekday:'short',month:'short',day:'numeric'})+' – '+new Date(co+'T00:00:00').toLocaleDateString('en-US',{weekday:'short',month:'short',day:'numeric'});
      const whereVal=document.getElementById('fns_m_where_val'); if(whereVal) whereVal.textContent=city||'All Tanzanian Destinations';
      if(chipText){
        const shortCi=new Date(ci+'T00:00:00').toLocaleDateString('en-US',{month:'short',day:'numeric'});
        const shortCo=new Date(co+'T00:00:00').toLocaleDateString('en-US',{day:'numeric'});
        const ad=s.adults||'2';
        chipText.textContent=(city||'All Tanzanian Destinations')+' • '+shortCi+'–'+shortCo+' • '+ad+' guest'+(ad!=='1'?'s':'');
      }
    }catch(e){}
  }
  const ad=s.adults, ch=s.children, rm=s.rooms;
  [['fns_ad_v',ad],['fns_ch_v',ch],['fns_rm_v',rm],['fns_m_ad_v',ad],['fns_m_ch_v',ch],['fns_m_rm_v',rm],['gh_ad_v',ad],['gh_ch_v',ch],['gh_rm_v',rm]].forEach(([id,val])=>{
    const el=document.getElementById(id); if(el && val!==undefined) el.textContent=val;
  });
  const hiddenMap={adults:'gh_ad', children:'gh_ch', rooms:'gh_rm'};
  Object.keys(hiddenMap).forEach(k=>{ const v=s[k]; if(v!==undefined){ const el=document.getElementById(hiddenMap[k]); if(el) el.value=v; }});
  if(ad||ch||rm){
    const txt=(ad||'2')+' adult'+(ad!=='1'?'s':'')+' · '+(rm||'1')+' room'+(rm!=='1'?'s':'');
    const withChildren=ch && ch!=='0' ? (ad||'2')+' adults · '+ch+' child'+(ch!=='1'?'ren':'')+' · '+(rm||'1')+' room'+(rm!=='1'?'s':'') : txt;
    const gl=document.getElementById('fns_guests_lbl'); if(gl) gl.textContent=withChildren;
    const mWho=document.getElementById('fns_m_who_val'); if(mWho) mWho.textContent=withChildren;
    const ghLbl=document.getElementById('gh_guests_lbl'); if(ghLbl) ghLbl.textContent=(ad||'2')+(ch&&ch!=='0'?'+'+ch:'');
  }
  // refresh chips active state — amenities only blue when actively filtered (clicked)
  try{
    const curParams=new URLSearchParams(window.location.search);
    const curAmenities=(curParams.get('amenities')||'').split(',').map(s=>s.trim().toLowerCase()).filter(Boolean);
    const curProp=curParams.get('property_type')||'';
    const curRating=curParams.get('rating')||'';
    const curFree=curParams.get('free_cancellation')||'';
    const curMin=curParams.get('price_min')||curParams.get('min_price')||'';
    const curMax=curParams.get('price_max')||curParams.get('max_price')||'';
    document.querySelectorAll('.fns-chip[data-filter], .gh-chip[data-filter]').forEach(chip=>{
      const filter=chip.getAttribute('data-filter');
      const val=(chip.getAttribute('data-value')||'').toLowerCase();
      let isActive=false;
      if(filter==='amenities'){
        isActive=curAmenities.includes(val);
      } else if(filter==='property_type'){
        isActive=curProp.toLowerCase()===val;
      } else if(filter==='rating'){
        isActive=curRating===chip.getAttribute('data-value');
      } else if(filter==='free_cancellation'){
        isActive=curFree==='1';
      }
      chip.classList.toggle('active', isActive);
      chip.setAttribute('aria-pressed', isActive?'true':'false');
      if(filter==='free_cancellation') chip.setAttribute('aria-checked', isActive?'true':'false');
    });
    // price chip active when any price filter present
    const priceChip=document.getElementById('fns_price_chip');
    if(priceChip){
      const priceActive=curMin!=='' || curMax!=='';
      priceChip.classList.toggle('active', priceActive);
      priceChip.setAttribute('aria-pressed', priceActive?'true':'false');
    }
  }catch(e){}
  // fallback for chips without data-filter (legacy) — keep white unless matched
  document.querySelectorAll('.fns-chip:not([data-filter]):not(.fns-chip-filters), .gh-chip:not([data-filter])').forEach(chip=>{
    const href=chip.getAttribute('href');
    if(!href || href.includes('amenities') || chip.hasAttribute('data-filter')) return;
    // leave as-is (server-rendered)
  });
  refreshDetailLinks();
}

const FastNetState={
  parse: parseUrl,
  buildUrl,
  getState: parseUrl,
  setState(partial, opts={}){
    const cur=aliasNormalize(parseUrl());
    const next={...cur, ...aliasNormalize(partial)};
    Object.keys(partial).forEach(k=>{ const spec=LEGACY_MAP[k]||k; if(partial[k]===''||partial[k]===null||partial[k]===undefined){ delete next[k]; delete next[spec]; if(REVERSE_MAP[k]) delete next[REVERSE_MAP[k]]; if(LEGACY_MAP[k]) delete next[LEGACY_MAP[k]]; }});
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
  if(!document.getElementById('fastnet-progress')){
    const bar=document.createElement('div');
    bar.id='fastnet-progress';
    bar.style.cssText='position:fixed;top:0;left:0;height:2px;width:0;background:#2563EB;z-index:9999;transition:width .3s ease;';
    document.body.appendChild(bar);
  }
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
      // recent save
      const cityVal=obj['city']||obj['destination']||'';
      if(cityVal) try{ const k='fns_recent_searches'; let arr=JSON.parse(localStorage.getItem(k)||'[]'); arr=arr.filter(v=>v.toLowerCase()!==cityVal.toLowerCase()); arr.unshift(cityVal); localStorage.setItem(k, JSON.stringify(arr.slice(0,5))); }catch(e){}
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
  setInterval(patchGuests, 800);
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
  patchDate(); setInterval(patchDate, 800);
});

// bounds helper
window.FastNetMapBounds={update(boundsStr,isPassive){ if(isPassive) FastNetState.replaceState({bounds:boundsStr}); else FastNetState.pushState({bounds:boundsStr}); }};

// shimmer trigger alias
window.fnsTriggerShimmer=function(){ const c=document.getElementById('gh-cards-container'), s=document.getElementById('gh-shimmer-container'); if(c&&s){ c.style.opacity='0.55'; s.style.display='block'; }};
window.ghTriggerShimmer=window.fnsTriggerShimmer;
})();
