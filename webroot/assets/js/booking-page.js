/* Checkout countdown + payment widgets. Extracted from booking-page.php. */
// Real countdown: server-rendered remaining seconds, ticked locally from page-load time
// (immune to client clock skew). On expiry the form is blocked — the backend also rejects
// expired quotes, so this can never be bypassed by editing the DOM.
(function(){
  const bar = document.getElementById('agodaTimerBar');
  if (!bar) return; // no server guarantee -> no timer, never a fake number
  const initial = Math.max(0, parseInt(bar.dataset.remaining || '0', 10));
  const loadedAt = Date.now();
  const el = document.getElementById('agodaCountdown');
  const label = document.getElementById('agodaTimerLabel');
  const expired = document.getElementById('agodaTimerExpired');
  let quoteExpired = initial <= 0;
  function fmt(s){
    s = Math.max(0, s);
    const h = String(Math.floor(s/3600)).padStart(2,'0');
    const m = String(Math.floor((s%3600)/60)).padStart(2,'0');
    const sec = String(s%60).padStart(2,'0');
    return h+':'+m+':'+sec;
  }
  function onExpire(){
    quoteExpired = true;
    if (el) el.textContent = '00:00:00';
    if (label) label.style.display = 'none';
    if (expired) expired.style.display = 'inline';
    bar.style.background = '#fdecea';
    bar.style.borderBottomColor = '#f5c6cb';
    const btn = document.querySelector('#agodaCheckoutForm .agoda-next-btn');
    if (btn) { btn.disabled = true; btn.textContent = 'PRICE EXPIRED — REFRESH BELOW'; btn.style.opacity = '0.6'; btn.style.cursor = 'not-allowed'; }
  }
  window.agodaQuoteExpired = () => quoteExpired;
  function tick(){
    const elapsed = Math.floor((Date.now() - loadedAt) / 1000);
    const left = initial - elapsed;
    if (left <= 0) { onExpire(); return; }
    if (el) el.textContent = fmt(left);
    setTimeout(tick, 1000);
  }
  if (initial <= 0) { onExpire(); return; }
  tick();
  document.getElementById('agodaRefreshPrice')?.addEventListener('click', () => {
    // Drop the stale quote_id so the server mints a fresh quote with a new guarantee.
    const u = new URL(window.location.href);
    u.searchParams.delete('quote_id');
    u.searchParams.set('repriced', '1');
    window.location.href = u.toString();
  });
})();
function toggleLeadEdit(){const e=document.getElementById('leadEditFields');e.style.display=(e.style.display==='none'||e.style.display==='')?'block':'none'}
function toggleExtraPrefs(ev){ev.preventDefault();const e=document.getElementById('extraPrefs');e.style.display=e.style.display==='none'?'block':'none'}
function validateCustomerInfo(){
  const getVal = (sel)=> document.querySelector(sel)?.value.trim() || '';
  const fn=getVal('#inputFirstName'), ln=getVal('#inputLastName'), em=getVal('#inputEmail'), ph=getVal('#inputPhone');
  let valid=true;
  const errors=[];
  const setErr = (key, show)=>{
    const el=document.querySelector('.field-error[data-for="'+key+'"]');
    if(el) el.style.display=show?'block':'none';
    const inp=document.querySelector('[name="'+key+'"]');
    if(inp) inp.style.borderColor=show?'#c0392b':'#dadce0';
  };
  // First name
  if(!fn){ setErr('first_name', true); valid=false; errors.push('First name is required'); } else setErr('first_name', false);
  if(!ln){ setErr('last_name', true); valid=false; errors.push('Last name is required'); } else setErr('last_name', false);
  const emailOk=/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(em);
  if(!em || !emailOk){ setErr('email', true); valid=false; if(!em) errors.push('Email is required'); else errors.push('Valid email is required'); } else setErr('email', false);
  if(!ph){ setErr('phone', true); valid=false; errors.push('Phone is required'); } else setErr('phone', false);
  const errBox=document.getElementById('customerFormError');
  if(!valid){
    if(errBox){ errBox.style.display='block'; errBox.textContent=errors.join(' • '); }
    // Ensure edit section visible so user can fix
    const edit=document.getElementById('leadEditFields');
    if(edit && (edit.style.display==='none'||edit.style.display==='')) edit.style.display='block';
  } else {
    if(errBox){ errBox.style.display='none'; }
  }
  return valid;
}
function updateLeadPreview(){
  const fn=document.getElementById('inputFirstName')?.value.trim()||'';
  const ln=document.getElementById('inputLastName')?.value.trim()||'';
  const em=document.getElementById('inputEmail')?.value.trim()||'';
  const ph=document.getElementById('inputPhone')?.value.trim()||'';
  const nameEl=document.getElementById('leadDisplayName');
  const emailEl=document.getElementById('leadDisplayEmail');
  const phoneEl=document.getElementById('leadDisplayPhone');
  if(nameEl) nameEl.textContent=(fn+' '+ln).trim() || 'Please enter your details below';
  if(emailEl) emailEl.textContent=em || 'Email required';
  if(phoneEl) phoneEl.textContent= ph ? ('Tanzania '+ph) : 'Phone required';
}
document.addEventListener('DOMContentLoaded',()=>{
  const form=document.getElementById('agodaCheckoutForm');
  if(form){
    // Live preview update
    ['#inputFirstName','#inputLastName','#inputEmail','#inputPhone'].forEach(sel=>{
      const el=document.querySelector(sel);
      if(el) el.addEventListener('input', updateLeadPreview);
    });
    updateLeadPreview();
    form.addEventListener('submit', (ev)=>{
      if(window.agodaQuoteExpired && window.agodaQuoteExpired()){
        ev.preventDefault();
        ev.stopPropagation();
        const errBox=document.getElementById('customerFormError');
        if(errBox){ errBox.style.display='block'; errBox.textContent='This price guarantee has expired. Please refresh the live price to continue.'; }
        document.getElementById('agodaTimerBar')?.scrollIntoView({behavior:'smooth', block:'center'});
        return false;
      }
      if(!validateCustomerInfo()){
        ev.preventDefault();
        ev.stopPropagation();
        // scroll to error
        document.getElementById('leadEditFields')?.scrollIntoView({behavior:'smooth', block:'center'});
        return false;
      }
      // Disable button to prevent double click
      const btn=form.querySelector('.agoda-next-btn');
      if(btn){ btn.disabled=true; btn.textContent='Processing…'; btn.style.opacity='0.7'; }
    });
    const nextBtn=form.querySelector('.agoda-next-btn');
    if(nextBtn){
      // Remove old fallback that forced navigation on invalid; we now block properly
      nextBtn.addEventListener('click', (e)=>{
        // Trigger form submit validation; if invalid, prevent
        // No auto-force navigation — handled by submit handler
      });
    }
  }
  // Restore visual edit open if any field invalid on load
  const anyEmpty = !document.getElementById('inputFirstName')?.value.trim() || !document.getElementById('inputEmail')?.value.trim();
  if(anyEmpty){
    const edit=document.getElementById('leadEditFields');
    if(edit) edit.style.display='block';
  }
});
document.addEventListener('DOMContentLoaded',()=>{
  try{
    const u=JSON.parse(localStorage.getItem('user')||localStorage.getItem('fastnet_user')||'null');
    if(u){
      let changed=false;
      if(u.name && !document.querySelector('[name=first_name]')?.value){
        const parts=u.name.split(' ');
        const fn=document.querySelector('[name=first_name]'); if(fn){ fn.value=parts[0]||''; changed=true; }
        const ln=document.querySelector('[name=last_name]'); if(ln){ ln.value=parts.slice(1).join(' ')||''; changed=true; }
      }
      if(u.email && !document.querySelector('[name=email]')?.value){
        const e=document.querySelector('[name=email]'); if(e){ e.value=u.email; changed=true; }
      }
      if(u.phone && !document.querySelector('[name=phone]')?.value){
        const p=document.querySelector('[name=phone]'); if(p){ p.value=u.phone; changed=true; }
      }
      if(changed) updateLeadPreview();
    }
  }catch(e){}
});
