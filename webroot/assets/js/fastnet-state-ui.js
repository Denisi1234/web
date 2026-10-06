/* FastNetState URL engine. Split for the 300-line cap — load url, ui, state in order (shared globals, no IIFE). */
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
