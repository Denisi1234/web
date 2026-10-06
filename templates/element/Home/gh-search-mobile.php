<script>
'use strict';
function renderMobileCal(){
  var grid=document.getElementById('fns_m_grid'); if(!grid) return;
  grid.innerHTML='';
  var start=new Date(_ci+'T00:00:00'); start.setDate(1);
  for(var m=0;m<6;m++){
    var cur=new Date(start.getFullYear(), start.getMonth()+m, 1);
    var yr=cur.getFullYear(), mo=cur.getMonth();
    var moNames=['January','February','March','April','May','June','July','August','September','October','November','December'];
    var wrap=document.createElement('div'); wrap.className='fns-m-month';
    var title=document.createElement('div'); title.className='fns-m-month-title'; title.textContent=moNames[mo]+' '+yr; wrap.appendChild(title);
    var daysWrap=document.createElement('div'); daysWrap.className='fns-m-days';
    var first=new Date(yr,mo,1).getDay(), days=new Date(yr,mo+1,0).getDate();
    var today=new Date().toISOString().slice(0,10);
    for(var i=0;i<first;i++){ var b=document.createElement('div'); daysWrap.appendChild(b); }
    for(var d=1;d<=days;d++){
      var iso=yr+'-'+String(mo+1).padStart(2,'0')+'-'+String(d).padStart(2,'0');
      var btn=document.createElement('button'); btn.type='button'; btn.className='fns-m-day'; btn.textContent=d; btn.setAttribute('data-iso',iso);
      btn.setAttribute('aria-label', iso);
      if(iso<today){ btn.disabled=true; btn.classList.add('past'); btn.setAttribute('aria-disabled','true');}
      if(iso===_ci && iso===_co) btn.classList.add('selected');
      else if(iso===_ci) btn.classList.add('range-start');
      else if(iso===_co) btn.classList.add('range-end');
      else if(iso>_ci && iso<_co) btn.classList.add('in-range');
      (function(s, el){
        el.addEventListener('click', function(){
          // haptic
          if(navigator.vibrate) try{ navigator.vibrate(12); }catch(e){}
          if(_picking==='ci' || s<=_ci){
            _ci=s;
            if(_co <= _ci){ var c=new Date(s+'T00:00:00'); c.setDate(c.getDate()+1); _co=c.toISOString().slice(0,10); }
            _picking='co';
          }
          else { _co=s; _picking='ci'; }
          syncDate(); renderMobileCal(); renderCal();
        });
      })(iso, btn);
      daysWrap.appendChild(btn);
    }
    wrap.appendChild(daysWrap); grid.appendChild(wrap);
  }
  // auto-scroll to selected month
  try{
    var firstSel=grid.querySelector('.fns-m-day.range-start, .fns-m-day.selected');
    if(firstSel) firstSel.scrollIntoView({block:'nearest', inline:'nearest'});
  }catch(e){}
}
window.fnsClearAllMobile=function(){
  document.getElementById('fns_m_input').value=''; document.getElementById('gh_dest').value=''; document.getElementById('gh_city').value='';
  var t=new Date(); _ci=t.toISOString().slice(0,10); var c=new Date(t); c.setDate(c.getDate()+1); _co=c.toISOString().slice(0,10);
  _ad=2; _ch=0; _rm=1;
  syncDate(); syncGuests(); renderDest(''); renderMobileCal();
  if(window.FastNetState) FastNetState.replaceState({city:'', destination:'', bounds:'', bbox:'', lat:'', lng:'', checkin:_ci, checkout:_co, adults:'2', children:'0', rooms:'1'});
};
window.fnsMobileSearch=function(){
  document.getElementById('gh_city').value=document.getElementById('fns_m_input').value || document.getElementById('gh_dest').value;
  var v=document.getElementById('gh_city').value;
  // ensure dates are valid before push
  if(_co <= _ci){ var c=new Date(_ci+'T00:00:00'); c.setDate(c.getDate()+1); _co=c.toISOString().slice(0,10); }
  fnsCloseMobile();
  if(window.FastNetState) window.FastNetState.pushState({city:v, destination:v, checkin:_ci, checkout:_co, checkIn:_ci, checkOut:_co, adults:String(_ad), children:String(_ch), rooms:String(_rm)});
};

// Global close handlers
document.addEventListener('click', function(e){
  if(!document.getElementById('fns_seg_where').contains(e.target)) { document.getElementById('fns_pop_dest').classList.remove('open'); document.getElementById('fns_seg_where').classList.remove('active'); document.getElementById('fns_seg_where').setAttribute('aria-expanded','false'); }
  if(!document.getElementById('fns_pop_cal').contains(e.target) && !document.getElementById('fns_seg_ci').contains(e.target) && !document.getElementById('fns_seg_co').contains(e.target)) { document.getElementById('fns_pop_cal').classList.remove('open'); document.getElementById('fns_seg_ci').classList.remove('active'); document.getElementById('fns_seg_co').classList.remove('active'); document.getElementById('fns_seg_ci').setAttribute('aria-expanded','false'); document.getElementById('fns_seg_co').setAttribute('aria-expanded','false'); }
  if(!document.getElementById('fns_pop_guests').contains(e.target) && !document.getElementById('fns_seg_guests').contains(e.target)) { document.getElementById('fns_pop_guests').classList.remove('open'); document.getElementById('fns_seg_guests').classList.remove('active'); document.getElementById('fns_seg_guests').setAttribute('aria-expanded','false'); }
});
document.addEventListener('keydown', function(e){
  if(e.key==='Escape'){ fnsClosePopovers(); fnsCloseMobile(); }
  if(e.key==='Tab' && document.getElementById('fns_mobile_sheet').classList.contains('open')){
    // trap focus inside sheet
    var sheet=document.getElementById('fns_mobile_sheet');
    var focusable=sheet.querySelectorAll('button, input, [tabindex]:not([tabindex="-1"])');
    if(!focusable.length) return;
    var first=focusable[0], last=focusable[focusable.length-1];
    if(e.shiftKey && document.activeElement===first){ e.preventDefault(); last.focus(); }
    else if(!e.shiftKey && document.activeElement===last){ e.preventDefault(); first.focus(); }
  }
});
/* desktop: no scroll toggle needed — mobile pill is now always visible via CSS */
document.addEventListener('DOMContentLoaded', function(){
  updateClear(); renderDest(document.getElementById('gh_dest').value);
  renderCal(); syncGuests();
  // spec: graceful empty state — if city blank, try geolocated city (client-side)
  try{
    var destInput=document.getElementById('gh_dest');
    var isEmpty=!destInput.value.trim() || destInput.value.trim()==='All Tanzanian Destinations';
    var hasNoCityParam=!new URLSearchParams(window.location.search).has('city') && !new URLSearchParams(window.location.search).has('destination');
    if(isEmpty && hasNoCityParam && navigator.geolocation && window.MAPBOX_TOKEN){
      navigator.geolocation.getCurrentPosition(function(pos){
        var lat=pos.coords.latitude, lng=pos.coords.longitude;
        fetch('https://api.mapbox.com/geocoding/v5/mapbox.places/'+lng+','+lat+'.json?types=place&access_token='+window.MAPBOX_TOKEN)
          .then(function(r){return r.json();}).then(function(d){
            var feat=d.features&&d.features[0];
            if(feat && feat.text){
              var city=feat.text;
              // only autofill if user hasn't typed meanwhile
              if(!document.getElementById('gh_dest').value.trim() || document.getElementById('gh_dest').value.trim()==='All Tanzanian Destinations'){
                document.getElementById('gh_dest').value=city;
                document.getElementById('gh_city').value=city;
                document.getElementById('fns_m_input').value=city;
                updateClear(); renderDest(city);
                if(window.FastNetState) FastNetState.replaceState({city:city, destination:city, lat:String(lat), lng:String(lng)});
              }
            }
          }).catch(function(){});
      }, function(){}, {timeout:4000, maximumAge:600000});
    }
  }catch(e){}
  // keep mobile search count in sync with hydrate
  window.addEventListener('fastnet:markers-update', function(e){
    var cnt=Array.isArray(e.detail)?e.detail.length: (e.detail&&e.detail.totalCount) || <?= (int)$apiCount ?>;
    var el=document.getElementById('fns_m_search_count'); if(el) el.textContent='· '+cnt+' stays';
  });
  // spring physics for sheet drag (handle + sheet)
  var sheet=document.getElementById('fns_mobile_sheet');
  var handle=sheet.querySelector('.fns-sheet-drag');
  var startY=0, curY=0, dragging=false;
  function onStart(e){ var y=(e.touches?e.touches[0].clientY:e.clientY); startY=y; dragging=true; sheet.style.transition='none'; }
  function onMove(e){ if(!dragging) return; var y=(e.touches?e.touches[0].clientY:e.clientY); curY=y-startY; if(curY>0){ sheet.style.transform='translateY('+curY+'px)'; var backdrop=document.getElementById('fns_sheet_backdrop'); if(backdrop) backdrop.style.opacity=Math.max(0,1-curY/400); } else { sheet.style.transform=''; } }
  function onEnd(){ if(!dragging) return; dragging=false; sheet.style.transition=''; var backdrop=document.getElementById('fns_sheet_backdrop'); if(backdrop) backdrop.style.opacity=''; if(curY>120){ fnsCloseMobile(); } else { sheet.style.transform=''; } curY=0; }
  (handle||sheet).addEventListener('touchstart', onStart, {passive:true});
  sheet.addEventListener('touchmove', onMove, {passive:true});
  sheet.addEventListener('touchend', onEnd, {passive:true});
  // mouse drag for desktop testing
  handle && handle.addEventListener('mousedown', function(e){ onStart(e); var mm=function(ev){ onMove(ev); }; var mu=function(){ document.removeEventListener('mousemove',mm); document.removeEventListener('mouseup',mu); onEnd(); }; document.addEventListener('mousemove',mm); document.addEventListener('mouseup',mu); });
  // ensure chip is visible and pill hidden via CSS — no JS scroll toggle
});
// legacy aliases
window.ghDestFocus=fnsDestFocus; window.ghDestInput=fnsDestInput; window.ghDestKey=fnsDestKey; window.ghDestClear=fnsDestClear;
window.ghOpenCal=fnsOpenCal; window.ghNavCal=fnsNavCal; window.ghPreset=fnsPreset; window.ghCalReset=fnsCalReset; window.ghCalDone=fnsCalDone;
</script>
