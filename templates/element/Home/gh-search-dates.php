<script>
'use strict';
// ── Date helpers ──
function syncDate(){
  // auto-correct: checkout must be after checkin
  if(_co <= _ci){
    var c=new Date(_ci+'T00:00:00'); c.setDate(c.getDate()+1);
    _co=c.toISOString().slice(0,10);
  }
  document.getElementById('gh_ci').value=_ci; document.getElementById('gh_co').value=_co;
  document.getElementById('gh_ci_legacy').value=_ci; document.getElementById('gh_co_legacy').value=_co;
  var fmt=function(s){ try{return new Date(s+'T00:00:00').toLocaleDateString('en-US',{weekday:'short',month:'short',day:'numeric'});}catch(e){return s;}};
  var ciLbl=document.getElementById('fns_ci_lbl'), coLbl=document.getElementById('fns_co_lbl');
  if(ciLbl) ciLbl.textContent=fmt(_ci);
  if(coLbl) coLbl.textContent=fmt(_co);
  var nights=Math.max(1, Math.round((new Date(_co+'T00:00:00')-new Date(_ci+'T00:00:00'))/86400000));
  var badge=document.getElementById('fns_nights_badge'); if(badge) badge.textContent=nights+' night'+(nights!==1?'s':'');
  var mCi=document.getElementById('fns_m_ci_lbl'), mCo=document.getElementById('fns_m_co_lbl');
  if(mCi) mCi.textContent=fmt(_ci); if(mCo) mCo.textContent=fmt(_co);
  var gCi=document.getElementById('fns_g_ci'), gCo=document.getElementById('fns_g_co');
  if(gCi) gCi.textContent=fmt(_ci); if(gCo) gCo.textContent=fmt(_co);
  var gDates=document.getElementById('fns_g_dates');
  if(gDates) gDates.setAttribute('aria-label','Change dates: '+fmt(_ci)+' to '+fmt(_co));
  var mBadge=document.getElementById('fns_m_nights_badge'); if(mBadge) mBadge.textContent=nights+'n';
  var mWhen=document.getElementById('fns_m_when_val'); if(mWhen) mWhen.textContent=fmt(_ci)+' – '+fmt(_co)+' · '+nights+'n';
  var sum=document.getElementById('fns_cal_summary');
  if(sum) sum.innerHTML='<strong>'+nights+' night'+(nights!==1?'s':'')+'</strong> · '+fmt(_ci)+' – '+fmt(_co);
  updateClear();
  // highlight active picking box
  var ciBox=document.getElementById('fns_m_ci_box'), coBox=document.getElementById('fns_m_co_box');
  if(ciBox && coBox){ ciBox.style.borderColor=_picking==='ci'?'var(--fns-blue)':'transparent'; coBox.style.borderColor=_picking==='co'?'var(--fns-blue)':'transparent'; ciBox.style.background=_picking==='ci'?'#EFF6FF':'#F9FAFB'; coBox.style.background=_picking==='co'?'#EFF6FF':'#F9FAFB';}
  if(window.FastNetState) window.FastNetState.replaceState({checkin:_ci, checkout:_co, checkIn:_ci, checkOut:_co});
  if(document.getElementById('fns_pop_cal').classList.contains('open')) renderCal();
  if(document.getElementById('fns_mobile_sheet').classList.contains('open')) renderMobileCal();
}
window.fnsStep=function(f,d){
  var dt=new Date((f==='ci'?_ci:_co)+'T00:00:00'); dt.setDate(dt.getDate()+d);
  var iso=dt.toISOString().slice(0,10);
  var today=new Date().toISOString().slice(0,10);
  if(iso < today) return;
  if(f==='ci'){
    _ci=iso;
    if(_co <= _ci){ var c=new Date(dt); c.setDate(c.getDate()+1); _co=c.toISOString().slice(0,10); }
  } else {
    if(iso <= _ci) return;
    _co=iso;
  }
  syncDate();
};
function renderCal(){
  var now=new Date(); now.setDate(1); now.setMonth(now.getMonth()+_calOff);
  renderMonth('fns_cal_m1','fns_cal_t1',now.getFullYear(), now.getMonth());
  var nxt=new Date(now.getFullYear(), now.getMonth()+1, 1);
  renderMonth('fns_cal_m2','fns_cal_t2',nxt.getFullYear(), nxt.getMonth());
}
function renderMonth(gid,tid,yr,mo){
  var moNames=['January','February','March','April','May','June','July','August','September','October','November','December'];
  var t=document.getElementById(tid); if(t) t.textContent=moNames[mo]+' '+yr;
  var g=document.getElementById(gid); if(!g) return; g.innerHTML='';
  var first=new Date(yr,mo,1).getDay(), days=new Date(yr,mo+1,0).getDate();
  var today=new Date().toISOString().slice(0,10);
  for(var i=0;i<first;i++){ var b=document.createElement('div'); g.appendChild(b); }
  for(var d=1;d<=days;d++){
    var iso=yr+'-'+String(mo+1).padStart(2,'0')+'-'+String(d).padStart(2,'0');
    var btn=document.createElement('button'); btn.type='button'; btn.className='fns-day';
    btn.textContent=d; btn.setAttribute('data-iso',iso);
    if(iso<today){ btn.disabled=true; btn.classList.add('past');}
    if(iso===_ci && iso===_co) btn.classList.add('selected');
    else if(iso===_ci) btn.classList.add('range-start');
    else if(iso===_co) btn.classList.add('range-end');
    else if(iso>_ci && iso<_co) btn.classList.add('in-range');
    (function(s,el){
      el.addEventListener('mouseenter', function(){
        if(_picking==='co' && s>_ci && s!==_co){
          document.querySelectorAll('.fns-day[data-iso]').forEach(function(day){
            var di=day.getAttribute('data-iso'); if(di>_ci && di<=s && di!==_ci && di!==_co) day.classList.add('preview');
          });
        }
      });
      el.addEventListener('mouseleave', function(){ document.querySelectorAll('.fns-day.preview').forEach(function(e){e.classList.remove('preview');}); });
      el.addEventListener('click', function(){
        if(_picking==='ci' || s<=_ci){
          _ci=s;
          // if new check-in is on/after checkout, bump checkout to next day
          if(_co <= _ci){ var c=new Date(s+'T00:00:00'); c.setDate(c.getDate()+1); _co=c.toISOString().slice(0,10); }
          _picking='co';
        }
        else { _co=s; _picking='ci'; document.getElementById('fns_pop_cal').classList.remove('open'); fnsCloseSegActive(); if(window.FastNetState) window.FastNetState.pushState({checkin:_ci, checkout:_co, checkIn:_ci, checkOut:_co});}
        syncDate(); renderCal();
      });
    })(iso, btn);
    g.appendChild(btn);
  }
}
window.fnsNavCal=function(d){ _calOff+=d; renderCal(); };
window.fnsOpenCal=function(which){
  if(window.innerWidth<=991){ fnsOpenMobile(); fnsSheetGo('when'); return; }
  _picking=which||'ci';
  var pop=document.getElementById('fns_pop_cal');
  var isOpen=pop.classList.contains('open');
  fnsClosePopovers();
  if(!isOpen){
    pop.classList.add('open');
    document.getElementById('fns_seg_'+(which==='co'?'co':'ci')).classList.add('active');
    document.getElementById('fns_seg_'+(which==='co'?'co':'ci')).setAttribute('aria-expanded','true');
    renderCal(); syncDate();
  }
};
window.fnsPreset=function(preset){
  document.querySelectorAll('.fns-preset').forEach(function(c){ c.classList.toggle('active', c.getAttribute('data-preset')===preset);});
  var t=new Date(); t.setHours(0,0,0,0);
  if(preset==='weekend'){ var dy=t.getDay(); var fri=new Date(t); fri.setDate(t.getDate()+ (5 - dy + 7)%7 ); _ci=fri.toISOString().slice(0,10); var sun=new Date(fri); sun.setDate(fri.getDate()+2); _co=sun.toISOString().slice(0,10); }
  else if(preset==='week'){ _ci=t.toISOString().slice(0,10); var wk=new Date(t); wk.setDate(t.getDate()+7); _co=wk.toISOString().slice(0,10); }
  else if(preset==='custom') { /* keep */ }
  _picking='ci'; syncDate(); renderCal(); if(window.FastNetState) window.FastNetState.pushState({checkin:_ci, checkout:_co, checkIn:_ci, checkOut:_co});
};
window.fnsCalReset=function(){ var t=new Date(); _ci=t.toISOString().slice(0,10); var c=new Date(t); c.setDate(c.getDate()+1); _co=c.toISOString().slice(0,10); _picking='ci'; syncDate(); renderCal(); };
window.fnsCalDone=function(){ if(window.FastNetState) window.FastNetState.pushState({checkin:_ci, checkout:_co, checkIn:_ci, checkOut:_co}); document.getElementById('fns_pop_cal').classList.remove('open'); fnsCloseSegActive(); };

// ── Guests ──
function syncGuests(){
  document.getElementById('gh_ad').value=_ad; document.getElementById('gh_ch').value=_ch; document.getElementById('gh_rm').value=_rm;
  var vals=['fns_ad_v','fns_ch_v','fns_rm_v','fns_m_ad_v','fns_m_ch_v','fns_m_rm_v'];
  var ids={'fns_ad_v':_ad,'fns_ch_v':_ch,'fns_rm_v':_rm,'fns_m_ad_v':_ad,'fns_m_ch_v':_ch,'fns_m_rm_v':_rm};
  Object.keys(ids).forEach(function(id){ var el=document.getElementById(id); if(el) el.textContent=ids[id]; });
  // buttons disabled
  document.getElementById('fns_ad_m').disabled=_ad<=1; document.getElementById('fns_ch_m').disabled=_ch<=0; document.getElementById('fns_rm_m').disabled=_rm<=1;
  document.getElementById('fns_m_ad_m').disabled=_ad<=1; document.getElementById('fns_m_ch_m').disabled=_ch<=0; document.getElementById('fns_m_rm_m').disabled=_rm<=1;
  document.getElementById('fns_ad_p').disabled=_ad>=10; document.getElementById('fns_ch_p').disabled=_ch>=6; document.getElementById('fns_rm_p').disabled=_rm>=5;
  document.getElementById('fns_m_ad_p').disabled=_ad>=10; document.getElementById('fns_m_ch_p').disabled=_ch>=6; document.getElementById('fns_m_rm_p').disabled=_rm>=5;
  var txt=_ad+' adult'+(_ad!==1?'s':'')+' · '+_rm+' room'+(_rm!==1?'s':'');
  if(_ch>0) txt=_ad+' adults · '+_ch+' child'+(_ch!==1?'ren':'')+' · '+_rm+' room'+(_rm!==1?'s':'');
  document.getElementById('fns_guests_lbl').textContent=txt;
  var mWho=document.getElementById('fns_m_who_val'); if(mWho) mWho.textContent=txt;
  var gCount=document.getElementById('fns_g_guest_count'); if(gCount) gCount.textContent=_ad+_ch;
  var gBtn=document.getElementById('fns_g_guests_btn'); if(gBtn) gBtn.setAttribute('aria-label','Change guests, currently '+(_ad+_ch)+' guests');
  if(window.FastNetState) window.FastNetState.replaceState({adults:String(_ad), children:String(_ch), rooms:String(_rm)});
  updateClear();
}
window.fnsG=function(f,d){
  if(f==='adults') _ad=Math.max(1, Math.min(10, _ad+d));
  if(f==='children') _ch=Math.max(0, Math.min(6, _ch+d));
  if(f==='rooms') _rm=Math.max(1, Math.min(5, _rm+d));
  syncGuests();
};
window.fnsMG=function(f,d){ fnsG(f,d); };
window.fnsGuestsToggle=function(e){
  e.stopPropagation();
  if(window.innerWidth<=991){ fnsOpenMobile(); fnsSheetGo('who'); return; }
  var pop=document.getElementById('fns_pop_guests');
  var isOpen=pop.classList.contains('open');
  fnsClosePopovers();
  if(!isOpen){ pop.classList.add('open'); document.getElementById('fns_seg_guests').classList.add('active'); document.getElementById('fns_seg_guests').setAttribute('aria-expanded','true'); }
};
window.fnsGuestsClose=function(){ document.getElementById('fns_pop_guests').classList.remove('open'); fnsCloseSegActive(); };
/* Exact-area default: on submit with no destination AND no picked bbox, stamp
   the current map viewport so explore-mode matches what the user sees.
   A typed/picked destination always wins — never constrain it by viewport. */
document.getElementById('gh_search_form').addEventListener('submit', function(){
  try{
    var bb=document.getElementById('gh_bbox');
    var typed=(document.getElementById('gh_dest').value||'').trim();
    if(bb && !bb.value && !typed && window._ghMap){
      var b=window._ghMap.getBounds(); if(!b) return;
      var ne=b.getNorthEast(), sw=b.getSouthWest();
      bb.value=ne.lat.toFixed(4)+','+ne.lng.toFixed(4)+','+sw.lat.toFixed(4)+','+sw.lng.toFixed(4);
    }
  }catch(e){}
});
window.fnsGuestsApply=function(){ if(window.FastNetState) window.FastNetState.pushState({adults:String(_ad), children:String(_ch), rooms:String(_rm)}); fnsGuestsClose(); document.getElementById('gh_search_form').requestSubmit(); };

// ── Popover helpers ──
function fnsCloseSegActive(){ document.querySelectorAll('.fns-seg.active').forEach(function(el){ el.classList.remove('active'); el.setAttribute('aria-expanded','false'); }); }
function fnsClosePopovers(){
  document.getElementById('fns_pop_dest').classList.remove('open');
  document.getElementById('fns_pop_cal').classList.remove('open');
  document.getElementById('fns_pop_guests').classList.remove('open');
  fnsCloseSegActive();
}

// ── Mobile sheet (enterprise) ──
window.fnsOpenMobile=function(){
  var sheet=document.getElementById('fns_mobile_sheet');
  var backdrop=document.getElementById('fns_sheet_backdrop');
  sheet.classList.add('open'); if(backdrop) backdrop.classList.add('open');
  document.body.style.overflow='hidden';
  // prevent background scroll jump
  document.documentElement.style.overflow='hidden';
  renderDest(document.getElementById('gh_dest').value);
  renderMobileCal(); syncGuests();
  fnsSheetGo('where');
  setTimeout(function(){ try{ document.getElementById('fns_m_input').focus(); }catch(e){} }, 280);
};
window.fnsCloseMobile=function(){
  var sheet=document.getElementById('fns_mobile_sheet');
  var backdrop=document.getElementById('fns_sheet_backdrop');
  sheet.classList.remove('open'); if(backdrop) backdrop.classList.remove('open');
  document.body.style.overflow=''; document.documentElement.style.overflow='';
};
window.fnsSheetGo=function(step){
  document.querySelectorAll('.fns-sheet-tab').forEach(function(t){ t.classList.toggle('active', t.getAttribute('data-step')===step); t.setAttribute('aria-selected', t.getAttribute('data-step')===step ? 'true':'false'); });
  document.querySelectorAll('.fns-acc-head').forEach(function(h){ h.classList.remove('active'); h.setAttribute('aria-expanded','false'); });
  document.querySelectorAll('.fns-acc-body').forEach(function(b){ b.classList.remove('open'); });
  var head=document.querySelector('#fns_acc_'+step+' .fns-acc-head');
  var body=document.getElementById('fns_acc_'+step+'_body');
  if(head) { head.classList.add('active'); head.setAttribute('aria-expanded','true'); }
  if(body) body.classList.add('open');
  if(step==='when'){ renderMobileCal(); }
};
window.fnsAccToggle=function(step){
  var head=document.querySelector('#fns_acc_'+step+' .fns-acc-head');
  var body=document.getElementById('fns_acc_'+step+'_body');
  var isOpen=body.classList.contains('open');
  // close others
  document.querySelectorAll('.fns-acc-body').forEach(function(b){ b.classList.remove('open'); });
  document.querySelectorAll('.fns-acc-head').forEach(function(h){ h.classList.remove('active'); h.setAttribute('aria-expanded','false'); });
  if(!isOpen){ head.classList.add('active'); head.setAttribute('aria-expanded','true'); body.classList.add('open'); if(step==='when') renderMobileCal(); }
};
window.fnsMDestInput=function(v){
  document.getElementById('gh_dest').value=v; document.getElementById('gh_city').value=v;
  document.getElementById('gh_lat').value=''; document.getElementById('gh_lng').value='';
  var _bbm=document.getElementById('gh_bbox'); if(_bbm) _bbm.value='';
  updateClear(); renderDest(v);
  if(window.FastNetState) FastNetState.replaceState({city:v, destination:v, lat:'', lng:'', bbox:''});
};
window.fnsMClear=function(){ document.getElementById('fns_m_input').value=''; document.getElementById('gh_dest').value=''; document.getElementById('gh_city').value=''; document.getElementById('gh_lat').value=''; document.getElementById('gh_lng').value=''; var _bb3=document.getElementById('gh_bbox'); if(_bb3) _bb3.value=''; updateClear(); renderDest(''); if(window.FastNetState) FastNetState.replaceState({city:'', destination:'', lat:'', lng:'', bbox:''}); };
</script>
