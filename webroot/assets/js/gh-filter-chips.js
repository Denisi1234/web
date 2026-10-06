/* Filter chips interactions. Extracted for the 300-line cap. */
(function(){
'use strict';
function fnsDeviceId(){
  try{
    var k='fns_device_id', v=localStorage.getItem(k);
    if(v) return v;
    v = (window.crypto && crypto.randomUUID) ? crypto.randomUUID() : ('dev-'+Date.now()+'-'+Math.floor(Math.random()*1e9));
    localStorage.setItem(k, v);
    return v;
  }catch(e){ return 'dev-anon'; }
}
function fnsTrackCity(){
  try{
    var u=new URL(window.location.href);
    return u.searchParams.get('city') || u.searchParams.get('destination') || '';
  }catch(e){ return ''; }
}
function fnsTrackPrice(){
  // Alerts are priced in TZS (backend currency) — read the numeric base,
  // never the rendered text (which follows the display currency).
  var min=0;
  document.querySelectorAll('.gh-hotel-price').forEach(function(el){
    var p=parseInt(el.getAttribute('data-tzs')||'0',10)||0;
    if(p>0 && (min===0||p<min)) min=p;
  });
  return min;
}
function fnsTrackPaint(on){
  var slider=document.getElementById('fns_track_slider');
  var chk=document.getElementById('fns_track_chk');
  var lbl=document.getElementById('fns_track_status');
  if(chk){ chk.setAttribute('aria-checked', on?'true':'false'); if(chk.checked!==on) chk.checked=on; }
  if(lbl) lbl.textContent=on?'On':'Off';
  if(slider){ slider.style.background=on?'#0f62fe':'#E5E7EB'; slider.style.justifyContent=on?'flex-start':'flex-end'; slider.style.paddingLeft=on?'3px':'0'; slider.style.paddingRight=on?'0':'3px'; }
}
function fnsTrackToggle(on, silent){
  fnsTrackPaint(on);
  // Real backend alert (POST /alerts via /api proxy) keyed by per-device id — never the shared guest bucket.
  // localStorage stays as offline fallback so the toggle is never fake.
  var city=fnsTrackCity(), price=fnsTrackPrice(), uid=fnsDeviceId();
  var done=function(ok, serverId){
    try{
      if(on){ localStorage.setItem('gh_track_prices','1'); if(serverId) localStorage.setItem('gh_price_alert_id', serverId); }
      else { localStorage.removeItem('gh_track_prices'); localStorage.removeItem('gh_price_alert_id'); }
    }catch(e){}
    if(!silent){
      if(typeof window.fnsToast==='function') window.fnsToast(on ? (ok ? 'Price alert on for '+(city||'Tanzania')+'.' : 'Alert saved on this device. Will sync when online.') : 'Price tracking off.');
      else if(typeof showWishlistToast==='function') showWishlistToast(on?'Price alert on.':'Price tracking off.');
    }
  };
  if(on){
    fetch('/api/alerts', {method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json'}, body:JSON.stringify({user_id:uid, city:city||'Tanzania', current_price:price||0})})
      .then(function(r){ return r.ok ? r.json() : null; })
      .then(function(d){ done(!!d, d && (d.alert ? d.alert.id : d.id)); })
      .catch(function(){ done(false, null); });
  } else {
    var aid=null; try{ aid=localStorage.getItem('gh_price_alert_id'); }catch(e){}
    if(aid){
      fetch('/api/alerts/'+encodeURIComponent(aid)+'?user_id='+encodeURIComponent(uid), {method:'DELETE', headers:{'Accept':'application/json'}}).catch(function(){});
    }
    done(true, null);
  }
}
window.fnsTrackToggle=fnsTrackToggle;
document.addEventListener('DOMContentLoaded', function(){
  // Restore: backend first (per-device), local flag as offline fallback
  var local=false;
  try{ local=localStorage.getItem('gh_track_prices')==='1'; }catch(e){}
  if(local) fnsTrackPaint(true);
  try{
    fetch('/api/alerts?user_id='+encodeURIComponent(fnsDeviceId()), {headers:{'Accept':'application/json'}})
      .then(function(r){ return r.ok ? r.json() : null; })
      .then(function(d){
        var arr=(d&&(d.alerts||d.data))||[];
        var city=(fnsTrackCity()||'').toLowerCase();
        var live=arr.some(function(a){ return a && a.is_active!==false && (!city || String(a.city||'').toLowerCase()===city || !a.city); });
        fnsTrackPaint(live || local);
        if(live){ try{localStorage.setItem('gh_track_prices','1');}catch(e){} }
      })
      .catch(function(){});
  }catch(e){}
});
// overflow fade & scroll btn
function updFade(){
  var row=document.getElementById('fns_chips_row');
  var outer=document.getElementById('fns_chips_outer');
  var btn=document.getElementById('fns_scroll_btn');
  if(!row||!outer) return;
  var canScrollLeft=row.scrollLeft>8;
  var canScrollRight=row.scrollWidth - row.clientWidth - row.scrollLeft > 8;
  outer.classList.toggle('show-left', canScrollLeft);
  outer.classList.toggle('show-right', canScrollRight);
  if(btn) btn.style.display= canScrollRight ? 'flex' : 'none';
}
document.addEventListener('DOMContentLoaded', updFade);
window.addEventListener('resize', updFade);
document.getElementById('fns_chips_row')?.addEventListener('scroll', updFade, {passive:true});

// Price popover histogram (spec: pricing distribution)
function buildHist(){
  var hist=document.getElementById('fns_price_hist');
  if(!hist) return;
  hist.innerHTML='';
  // pseudo distribution centered around 120k-180k
  var values=[3,5,9,14,22,31,38,42,35,28,20,13,8,5,3];
  var max=Math.max(...values);
  values.forEach(function(v,i){
    var bar=document.createElement('div');
    bar.className='fns-price-bar';
    bar.style.height=(v/max*100)+'%';
    // highlight active range based on current min/max
    var min=parseInt(document.getElementById('fns_price_min')?.value||0,10);
    var maxP=parseInt(document.getElementById('fns_price_max')?.value||500000,10);
    // map bars to price buckets 0-500k in 33k steps
    var bucketMid=i*33333;
    if(bucketMid>=min && bucketMid<= (maxP||500000)) bar.classList.add('active');
    hist.appendChild(bar);
  });
}
function syncRangeFill(){
  var minEl=document.getElementById('fns_range_min'), maxEl=document.getElementById('fns_range_max'), fill=document.getElementById('fns_range_fill');
  if(!minEl||!maxEl||!fill) return;
  var min=parseInt(document.getElementById('fns_price_min').value||0,10)||0;
  var max=parseInt(document.getElementById('fns_price_max').value||500000,10)||500000;
  if(max<=min) max=min+20000;
  minEl.value=Math.min(min,500000); maxEl.value=Math.min(max,500000);
  var pctMin=(min/500000)*100, pctMax=(max/500000)*100;
  fill.style.left=pctMin+'%'; fill.style.right=(100-pctMax)+'%';
  buildHist();
}
window.fnsTogglePrice=function(e){
  e.stopPropagation();
  var pop=document.getElementById('fns_price_pop');
  var chip=document.getElementById('fns_price_chip');
  var open=pop.classList.contains('open');
  document.querySelectorAll('.fns-price-pop.open').forEach(function(p){p.classList.remove('open');});
  if(!open){
    pop.classList.add('open');
    chip.setAttribute('aria-expanded','true');
    buildHist(); syncRangeFill();
    // focus min
    setTimeout(function(){ document.getElementById('fns_price_min')?.focus(); }, 80);
  } else chip.setAttribute('aria-expanded','false');
};
window.fnsPriceClear=function(){
  document.getElementById('fns_price_min').value='';
  document.getElementById('fns_price_max').value='';
  syncRangeFill();
  if(window.FastNetState) window.FastNetState.pushState({price_min:'', price_max:'', min_price:'', max_price:''});
  document.getElementById('fns_price_pop').classList.remove('open');
};
window.fnsPriceApply=function(){
  var min=document.getElementById('fns_price_min').value.trim();
  var max=document.getElementById('fns_price_max').value.trim();
  if(min!=='' && max!=='' && parseInt(min,10)>parseInt(max,10)){
    // swap per defensive validation
    var tmp=min; min=max; max=tmp;
    document.getElementById('fns_price_min').value=min;
    document.getElementById('fns_price_max').value=max;
  }
  if(window.FastNetState) window.FastNetState.pushState({price_min:min, price_max:max, min_price:min, max_price:max});
  else window.location.href='/?'+new URLSearchParams({price_min:min, price_max:max}).toString();
  document.getElementById('fns_price_pop').classList.remove('open');
  document.getElementById('fns_price_chip').setAttribute('aria-expanded','false');
};
['fns_price_min','fns_price_max'].forEach(function(id){
  document.getElementById(id)?.addEventListener('input', syncRangeFill);
});
document.getElementById('fns_range_min')?.addEventListener('input', function(){ document.getElementById('fns_price_min').value=this.value; syncRangeFill(); });
document.getElementById('fns_range_max')?.addEventListener('input', function(){ document.getElementById('fns_price_max').value=this.value; syncRangeFill(); });
document.addEventListener('click', function(e){
  if(!document.getElementById('fns_price_anchor').contains(e.target)){
    document.getElementById('fns_price_pop')?.classList.remove('open');
    document.getElementById('fns_price_chip')?.setAttribute('aria-expanded','false');
  }
});
document.addEventListener('keydown', function(e){ if(e.key==='Escape'){ document.getElementById('fns_price_pop')?.classList.remove('open'); }});
document.addEventListener('DOMContentLoaded', function(){ buildHist(); syncRangeFill(); });
})();
