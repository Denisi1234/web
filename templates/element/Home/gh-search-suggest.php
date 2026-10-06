<script>
'use strict';
var _ci='<?= h($checkIn) ?>', _co='<?= h($checkOut) ?>';
var _ad=<?= $adults ?>, _ch=<?= $children ?>, _rm=<?= $rooms ?>;
var _calOff=0, _picking='ci', _ddIdx=-1;

// ── Popular Tanzanian Destinations (spec) + alias keys for typo/short-code tolerance ──
var POPULAR=[
  {id:'arusha', label:'Arusha', sub:'Gateway to Serengeti & Ngorongoro', icon:'fa-mountain-sun', lat:-3.3869, lng:36.6829, aka:'arusa arushatown'},
  {id:'zanzibar', label:'Zanzibar', sub:'Stone Town & Beaches', icon:'fa-umbrella-beach', lat:-6.1659, lng:39.1996, aka:'znz stonetown zanzibartown'},
  {id:'dar', label:'Dar es Salaam', sub:'Commercial capital', icon:'fa-city', lat:-6.7924, lng:39.2083, aka:'dsm daressalaam daressalam daresalaam'},
  {id:'kilimanjaro', label:'Kilimanjaro', sub:'Mount Kilimanjaro region', icon:'fa-mountain', lat:-3.0674, lng:37.3556, aka:'moshi'},
  {id:'serengeti', label:'Serengeti', sub:'National Park', icon:'fa-paw', lat:-2.3333, lng:34.8333, aka:'seronera'},
  {id:'mwanza', label:'Mwanza', sub:'Lake Victoria', icon:'fa-water', lat:-2.5167, lng:32.9, aka:'lakevictoria'}
];
function fnsMatchPopular(p, ql, qslug){
  if(!ql) return true;
  if(p.label.toLowerCase().indexOf(ql)!==-1 || p.sub.toLowerCase().indexOf(ql)!==-1) return true;
  if(p.aka && p.aka.indexOf(ql)!==-1) return true;
  if(qslug && p.aka && p.aka.replace(/[^a-z0-9 ]/g,'').replace(/ /g,'').indexOf(qslug)!==-1) return true;
  return false;
}
function renderDest(q){
  var popularEl=document.getElementById('fns_dd_popular');
  var recentEl=document.getElementById('fns_dd_recent');
  if(!popularEl) return;
  var ql=(q||'').toLowerCase().trim();
  var qslug=ql.replace(/[^a-z0-9]/g,'');
  var filtered= POPULAR.filter(function(p){ return fnsMatchPopular(p, ql, qslug); });
  var html='<div class="fns-dd-section"><i class="fa-solid fa-fire" style="margin-right:6px;"></i>Popular Tanzanian Destinations</div>';
  if(filtered.length===0) html+='<div style="padding:10px 16px;color:var(--fns-text-sec);font-size:13px;">No matches — try Arusha, Zanzibar, …</div>';
  filtered.forEach(function(p,i){
    var active=i===_ddIdx?' highlight':'';
    var optId='fns-opt-'+i;
    var sel=i===_ddIdx?' aria-selected="true"':' aria-selected="false"';
    html+='<div class="fns-dd-item'+active+'" id="'+optId+'" role="option"'+sel+' data-value="'+p.label+'" data-idx="'+i+'" onclick="fnsPickDest(\''+p.label.replace(/'/g,"\\'")+'\','+p.lat+','+p.lng+')" onmouseenter="fnsHlDest('+i+')"><span class="fns-dd-icon"><i class="fa-solid '+p.icon+'"></i></span><span><div class="fns-dd-label">'+p.label+'</div><div class="fns-dd-sub">'+p.sub+'</div></span></div>';
  });
  popularEl.innerHTML=html;
  recentEl.innerHTML='';
  // also sync mobile list (thumb-friendly 56px rows)
  syncMobileList(ql, filtered, []);
  // live Mapbox autocomplete for real Tanzanian areas (debounced, cached)
  fnsMbxSuggest(ql);
}
// ── Real Mapbox autocomplete (Tanzania-only) ──
var _mbxCache={}, _mbxResults=[], _mbxQ='', _mbxT=null, _mbxAbort=null;
var _mbxSession=(function(){ try{ if(window.crypto&&crypto.randomUUID) return crypto.randomUUID(); }catch(e){} return 'fns-'+Date.now()+'-'+Math.floor(Math.random()*1e6); })();
function mbxToken(){ return window.MAPBOX_TOKEN || window.DEFAULT_MAPBOX_TOKEN || ''; }
function escHtml(s){ return String(s==null?'':s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function mbxCtx(f, prefix){ var c=f.context||[]; for(var i=0;i<c.length;i++){ if(c[i].id&&c[i].id.indexOf(prefix)===0) return c[i].text; } return null; }
// Backend filters by city-level name, so map exact POI coords but search its parent city.
function mbxCityName(f){
  var t=f.place_type||[];
  if(t.indexOf('country')!==-1) return '';
  if(t.indexOf('place')!==-1||t.indexOf('region')!==-1) return f.text||'';
  return mbxCtx(f,'place')||mbxCtx(f,'region')||f.text||'';
}
function mbxIcon(f){
  var t=(f.place_type||[]).join(' ');
  if(t.indexOf('poi')!==-1) return 'fa-location-dot';
  if(t.indexOf('address')!==-1) return 'fa-house';
  if(t.indexOf('neighborhood')!==-1||t.indexOf('locality')!==-1) return 'fa-map-pin';
  return 'fa-city';
}
function mbxSub(f){
  var parts=String(f.place_name||'').split(',');
  parts.shift();
  var rest=parts.join(',').trim();
  return rest||'Tanzania';
}
function renderMbx(){
  var box=document.getElementById('fns_dd_mbx');
  if(!box) return;
  if(_mbxResults.length && _mbxQ){
    var h='<div class="fns-dd-section"><i class="fa-solid fa-location-dot" style="margin-right:6px;"></i>Real places</div>';
    _mbxResults.forEach(function(f,i){
      h+='<div class="fns-dd-item" onclick="fnsPickMbx('+i+')"><span class="fns-dd-icon"><i class="fa-solid '+mbxIcon(f)+'"></i></span><span><div class="fns-dd-label">'+escHtml(f.text)+'</div><div class="fns-dd-sub">'+escHtml(mbxSub(f))+'</div></span></div>';
    });
    box.innerHTML=h;
  } else box.innerHTML='';
}
function syncMobileList(ql, filtered, recent){
  var mList=document.getElementById('fns_m_list');
  if(!mList) return;
  if(!(window.innerWidth<=991 || document.getElementById('fns_mobile_sheet').classList.contains('open'))) return;
  var mh='';
  if(_mbxQ===ql && _mbxResults.length){
    mh+='<div class="fns-dd-section">Real places</div>';
    _mbxResults.forEach(function(f,i){
      mh+='<div class="fns-dd-item mob" role="option" tabindex="0" onclick="fnsPickMbx('+i+');fnsSheetGo(\'when\');" onkeydown="if(event.key===\'Enter\') this.click()"><span class="fns-dd-icon" aria-hidden="true"><i class="fa-solid '+mbxIcon(f)+'"></i></span><span><div class="fns-dd-label">'+escHtml(f.text)+'</div><div class="fns-dd-sub">'+escHtml(mbxSub(f))+'</div></span><span style="margin-left:auto;color:#9CA3AF;"><i class="fa-solid fa-chevron-right" style="font-size:11px;"></i></span></div>';
    });
  }
  filtered.forEach(function(p){ mh+='<div class="fns-dd-item mob" role="option" tabindex="0" onclick="fnsPickDest(\''+p.label.replace(/'/g,"\\'")+'\','+p.lat+','+p.lng+');fnsSheetGo(\'when\');" onkeydown="if(event.key===\'Enter\') this.click()"><span class="fns-dd-icon" aria-hidden="true"><i class="fa-solid '+p.icon+'"></i></span><span><div class="fns-dd-label">'+p.label+'</div><div class="fns-dd-sub">'+p.sub+'</div></span><span style="margin-left:auto;color:#9CA3AF;"><i class="fa-solid fa-chevron-right" style="font-size:11px;"></i></span></div>'; });
  if(!filtered.length && !(_mbxQ===ql && _mbxResults.length)) mh='<div style="padding:16px;color:var(--fns-text-sec);font-size:14px;text-align:center;">No matches — try Arusha, Zanzibar, Mwanza</div>';
  mList.innerHTML=mh;
}
function fnsMbxSuggest(ql){
  clearTimeout(_mbxT);
  if(_mbxAbort){ try{_mbxAbort.abort();}catch(e){} _mbxAbort=null; }
  if(!ql || ql.length<2 || !mbxToken()){ _mbxResults=[]; _mbxQ=''; renderMbx(); return; }
  if(_mbxCache[ql]){
    _mbxResults=_mbxCache[ql]; _mbxQ=ql; renderMbx();
    syncMobileList(ql,
      POPULAR.filter(function(p){ return fnsMatchPopular(p, ql, ql.replace(/[^a-z0-9]/g,'')); }),
      []);
    return;
  }
  _mbxT=setTimeout(function(){
    var curQ=ql;
    var box=document.getElementById('fns_dd_mbx');
    var popOpen=false;
    try{ popOpen=document.getElementById('fns_pop_dest').classList.contains('open'); }catch(e){}
    if(box && popOpen) box.innerHTML='<div style="padding:10px 16px;color:var(--fns-text-sec);font-size:13px;"><span class="p-dots" style="margin-right:6px" aria-hidden="true"><span class="p-dot"></span><span class="p-dot"></span><span class="p-dot"></span></span>Searching real places…</div>';
    try{ _mbxAbort=new AbortController(); }catch(e){ _mbxAbort=null; }
    var params='country=tz&limit=6&types=place,locality,neighborhood,address,poi&language=en&bbox=28.85,-11.75,40.5,-0.95&session_token='+encodeURIComponent(_mbxSession)+'&access_token='+encodeURIComponent(mbxToken());
    fetch('https://api.mapbox.com/geocoding/v5/mapbox.places/'+encodeURIComponent(ql)+'.json?'+params, {signal:_mbxAbort?_mbxAbort.signal:undefined})
      .then(function(r){ if(!r.ok) throw new Error('geo '+r.status); return r.json(); })
      .then(function(d){
        // ignore stale responses if the user kept typing
        var nowD=document.getElementById('gh_dest')?document.getElementById('gh_dest').value:'';
        var nowM=document.getElementById('fns_m_input')?document.getElementById('fns_m_input').value:'';
        if(nowD.toLowerCase().trim()!==curQ && nowM.toLowerCase().trim()!==curQ) return;
        var feats=(d&&d.features)||[];
        _mbxCache[curQ]=feats; _mbxResults=feats; _mbxQ=curQ;
        renderMbx();
        syncMobileList(curQ,
          POPULAR.filter(function(p){ return fnsMatchPopular(p, curQ, curQ.replace(/[^a-z0-9]/g,'')); }),
          []);
      })
      .catch(function(e){ if(e&&e.name==='AbortError') return; renderMbx(); });
  }, 250);
}
window.fnsPickMbx=function(i){
  var f=_mbxResults[i]; if(!f) return;
  var city=mbxCityName(f);
  var lng=(f.center&&f.center.length>1)?f.center[0]:null;
  var lat=(f.center&&f.center.length>1)?f.center[1]:null;
  var bbox='';
  if(f.bbox&&f.bbox.length===4){ bbox=f.bbox[3]+','+f.bbox[2]+','+f.bbox[1]+','+f.bbox[0]; }
  fnsPickDest(city, lat, lng, bbox);
  if(window.innerWidth<=991 && typeof fnsSheetGo==='function'){ try{ fnsSheetGo('when'); }catch(e){} }
};
window.fnsPickDest=function(val, lat, lng, bbox){
  document.getElementById('gh_dest').value=val;
  document.getElementById('gh_city').value=val;
  document.getElementById('fns_m_input').value=val;
  var latEl=document.getElementById('gh_lat'), lngEl=document.getElementById('gh_lng'), bbEl=document.getElementById('gh_bbox');
  if(latEl) latEl.value=(lat!==undefined&&lat!==null&&lat!=='')?lat:'';
  if(lngEl) lngEl.value=(lng!==undefined&&lng!==null&&lng!=='')?lng:'';
  if(bbEl) bbEl.value=bbox||'';
  fnsClosePopovers();
  document.getElementById('fns_m_where_val').textContent=val;
  var _gDt=document.getElementById('fns_g_dest_text');
  if(_gDt){ _gDt.textContent=val||'Search for places, hotels and more'; _gDt.classList.toggle('has-value', !!val); }
  if(window.FastNetState) FastNetState.replaceState({city:val, destination:val, lat:(lat||''), lng:(lng||''), bbox:(bbox||'')});
  updateClear();
};
window.fnsHlDest=function(i){
  // Hover highlight only — NEVER re-render here: renderDest() replaces innerHTML
  // and destroys the node under the cursor before click fires (popular items felt unclickable).
  _ddIdx=i;
  var items=document.querySelectorAll('#fns_dd_popular .fns-dd-item, #fns_dd_mbx .fns-dd-item, #fns_dd_recent .fns-dd-item');
  items.forEach(function(el, idx){
    el.classList.toggle('highlight', idx===i);
    el.setAttribute('aria-selected', idx===i ? 'true' : 'false');
  });
  var input=document.getElementById('gh_dest');
  if(input && items[i]) input.setAttribute('aria-activedescendant', items[i].id || '');
};

function updateClear(){
  var v=document.getElementById('gh_dest').value.trim();
  var c=document.getElementById('fns_clear');
  if(c) c.classList.toggle('show', v.length>0);
  var mWhere=document.getElementById('fns_m_where_val');
  if(mWhere) mWhere.textContent= v || 'All Tanzanian Destinations';
  var gDt=document.getElementById('fns_g_dest_text');
  if(gDt){ gDt.textContent=v||'Search for places, hotels and more'; gDt.classList.toggle('has-value', !!v); }
  var chip=document.getElementById('fns_chip_text');
  if(chip){
    var ciLbl=document.getElementById('fns_ci_lbl')?document.getElementById('fns_ci_lbl').textContent:'';
    var coLbl=document.getElementById('fns_co_lbl')?document.getElementById('fns_co_lbl').textContent:'';
    // short format
    try{
      var a=new Date(_ci+'T00:00:00'), b=new Date(_co+'T00:00:00');
      var s=a.toLocaleDateString('en-US',{month:'short',day:'numeric'});
      var e=b.toLocaleDateString('en-US',{day:'numeric'});
      chip.textContent=(v||'All Tanzanian Destinations')+' • '+s+'–'+e+' • '+_ad+' guest'+(_ad!==1?'s':'');
    }catch(e){ chip.textContent=(v||'All Tanzanian Destinations')+' • '+_ad+' guests';}
  }
}
window.fnsDestFocus=function(){
  if(window.innerWidth<=991){ fnsOpenMobile(); return; }
  document.getElementById('fns_seg_where').classList.add('active');
  document.getElementById('fns_seg_where').setAttribute('aria-expanded','true');
  document.getElementById('fns_pop_dest').classList.add('open');
  renderDest(document.getElementById('gh_dest').value);
};
window.fnsDestInput=function(v){
  updateClear();
  _ddIdx=-1;
  renderDest(v);
  document.getElementById('gh_city').value=v;
  document.getElementById('gh_lat').value=''; document.getElementById('gh_lng').value='';
  var _bb=document.getElementById('gh_bbox'); if(_bb) _bb.value='';
  if(window.FastNetState) FastNetState.replaceState({city:v, destination:v, lat:'', lng:'', bbox:''});
  var pop=document.getElementById('fns_pop_dest');
  if(pop && !pop.classList.contains('open')){ pop.classList.add('open'); document.getElementById('fns_seg_where').setAttribute('aria-expanded','true');}
};
window.fnsDestKey=function(e){
  var sel='#fns_dd_popular .fns-dd-item, #fns_dd_mbx .fns-dd-item, #fns_dd_recent .fns-dd-item';
  var input=document.getElementById('gh_dest');
  var fresh=function(){ return document.querySelectorAll(sel); };
  if(e.key==='ArrowDown'){ e.preventDefault(); var n=fresh().length; _ddIdx=Math.min(_ddIdx+1, n-1); renderDest(e.target.value); var it=fresh(); it[_ddIdx]?.scrollIntoView({block:'nearest'}); if(input&&it[_ddIdx]){input.setAttribute('aria-activedescendant', it[_ddIdx].id || ''); input.setAttribute('aria-expanded','true');}}
  else if(e.key==='ArrowUp'){ e.preventDefault(); _ddIdx=Math.max(_ddIdx-1,0); renderDest(e.target.value); var it2=fresh(); if(input&&it2[_ddIdx]) input.setAttribute('aria-activedescendant', it2[_ddIdx].id || '');}
  else if(e.key==='Enter'){ e.preventDefault(); var cur=fresh(); if(_ddIdx>=0 && cur[_ddIdx]){ cur[_ddIdx].click(); } else { fnsClosePopovers(); document.getElementById('gh_search_form').requestSubmit(); } }
  else if(e.key==='Escape'){ fnsClosePopovers(); if(input){input.setAttribute('aria-activedescendant',''); input.setAttribute('aria-expanded','false');} }
};
window.fnsDestClear=function(){
  document.getElementById('gh_dest').value='';
  document.getElementById('gh_city').value='';
  document.getElementById('fns_m_input').value='';
  document.getElementById('gh_lat').value=''; document.getElementById('gh_lng').value='';
  var _bb2=document.getElementById('gh_bbox'); if(_bb2) _bb2.value='';
  updateClear(); renderDest('');
  document.getElementById('gh_dest').focus();
  if(window.FastNetState) FastNetState.replaceState({city:'', destination:'', lat:'', lng:'', bbox:''});
};
</script>
