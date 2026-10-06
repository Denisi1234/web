/* Result cards interactions. Extracted from gh-hotel-cards.php element. */
function ghBookmark(propId, btn) {
    var icon = btn ? btn.querySelector('i') : null;
    var wasActive = !!(icon && icon.classList.contains('fa-solid'));
    if (icon) {
        icon.className = wasActive ? 'fa-regular fa-bookmark' : 'fa-solid fa-bookmark';
        icon.style.color = wasActive ? '' : '#fff';
        if(navigator.vibrate) try{ navigator.vibrate(10);}catch(e){}
    }
    // Mature UI: toggleWishlist is async + local-first — roll back optimistic icon if it rejects (e.g. storage blocked)
    if (typeof toggleWishlist === 'function') {
        try {
            var r = toggleWishlist(propId, btn);
            if (r && typeof r.catch === 'function') r.catch(function(){
                if (icon) { icon.className = wasActive ? 'fa-solid fa-bookmark' : 'fa-regular fa-bookmark'; icon.style.color = wasActive ? '#fff' : ''; }
                if (typeof window.fnsToast === 'function') window.fnsToast('Couldn’t save — please try again.');
            });
        } catch(e) {
            if (icon) { icon.className = wasActive ? 'fa-solid fa-bookmark' : 'fa-regular fa-bookmark'; icon.style.color = wasActive ? '#fff' : ''; }
        }
    }
}
window.ghScrollToCard = function(propId) {
    var c = document.getElementById('gh-card-' + propId);
    if (c) c.scrollIntoView({ behavior:'smooth', block:'nearest' });
};
// ── Card slider: sliding images for demo + real deploy (same logic: $prop['images'] array) ──
window._ghSlideIdx = window._ghSlideIdx || {};
window.ghGoSlide = function(propId, idx){
    var track = document.getElementById('gh-track-' + propId);
    var dots = document.getElementById('gh-dots-' + propId);
    var count = document.getElementById('gh-count-' + propId);
    if(!track) return;
    var total = track.children.length;
    if(idx < 0) idx = total - 1;
    if(idx >= total) idx = 0;
    window._ghSlideIdx[propId] = idx;
    track.style.transform = 'translateX(' + (-idx * 100) + '%)';
    if(dots){ Array.prototype.forEach.call(dots.children, function(d,i){ d.classList.toggle('a', i===idx); d.classList.toggle('active', i===idx); }); }
    if(count) count.textContent = (idx+1) + ' / ' + total;
};
window.ghSlide = function(propId, dir){ var cur = window._ghSlideIdx[propId] || 0; window.ghGoSlide(propId, cur + dir); };
// touch swipe for cards
(function(){
    document.addEventListener('DOMContentLoaded', function(){
        document.querySelectorAll('.gh-card-photo--slider').forEach(function(el){
            var pid = el.getAttribute('data-prop-id');
            if(!pid) return;
            var startX = 0, dx = 0, dragging = false;
            el.addEventListener('touchstart', function(e){ startX = e.touches[0].clientX; dragging = true; }, {passive:true});
            el.addEventListener('touchmove', function(e){ if(!dragging) return; dx = e.touches[0].clientX - startX; }, {passive:true});
            el.addEventListener('touchend', function(){ if(!dragging) return; dragging = false; if(Math.abs(dx) > 36){ window.ghSlide(parseInt(pid,10), dx < 0 ? 1 : -1); } dx = 0; });
            // also mouse drag for desktop
            el.addEventListener('mousedown', function(e){ startX = e.clientX; dragging = true; e.preventDefault(); });
            window.addEventListener('mouseup', function(e){ if(!dragging) return; dragging = false; var diff = e.clientX - startX; if(Math.abs(diff) > 36){ window.ghSlide(parseInt(pid,10), diff < 0 ? 1 : -1); } });
        });
    });
})();
// any-device shimmer: instant on any filter/search, auto-hide on hydrate + 8s fail-safe + CTA spinner
(function(){
  var ctaBusy=function(on){
    var btn=document.getElementById('fns_search_btn');
    var mBtn=document.querySelector('#fns_mobile_sheet .fns-btn-apply');
    if(btn) btn.setAttribute('aria-busy', on?'true':'false');
    if(mBtn) mBtn.setAttribute('aria-busy', on?'true':'false');
    var inputs=document.querySelectorAll('#gh_dest,#fns_m_input'); inputs.forEach(function(i){ i.setAttribute('aria-busy', on?'true':'false'); });
  };

  // The container swap delegates to FastnetLoading.skeleton, which is the same
  // code path the filter modal and fastnet-state.js use. These three copies of
  // show/hide were the reason the skeleton could get stuck: whichever definition
  // loaded last won, and one of them had no matching hide.
  var show=function(){
    var shim=document.getElementById('gh-shimmer-container');
    var sec=document.getElementById('gh_results_section');
    FastnetLoading.skeleton('gh-cards-container', true, shim);
    if(shim) shim.setAttribute('aria-hidden','false');
    if(sec) sec.setAttribute('aria-busy','true');
    var chips=document.getElementById('fns_shimmer_chips'); if(chips) chips.classList.add('show');
    ctaBusy(true);
  };
  var hide=function(){
    var shim=document.getElementById('gh-shimmer-container');
    var sec=document.getElementById('gh_results_section');
    FastnetLoading.skeleton('gh-cards-container', false, shim);
    if(shim) shim.setAttribute('aria-hidden','true');
    if(sec) sec.setAttribute('aria-busy','false');
    var chips=document.getElementById('fns_shimmer_chips'); if(chips) chips.classList.remove('show');
    ctaBusy(false);
  };

  // Only define these if nothing has claimed them yet - fastnet-state.js also
  // registers a pair, and whichever script evaluates last used to win.
  window.ghHideShimmer = window.ghHideShimmer || hide;
  window.ghTriggerShimmer = window.ghTriggerShimmer || show;
  window.fnsTriggerShimmer = window.fnsTriggerShimmer || show;

  // No skeleton on initial load: server already rendered real cards. Shimmer only
  // during AJAX transitions (fastnet:shimmer-show from FastNetState.hydrate).
  // fail-safe: hide after 8s if network hangs
  var t=null;
  window.addEventListener('fastnet:shimmer-show', function(){ clearTimeout(t); show(); t=setTimeout(hide,4000); });
  window.addEventListener('fastnet:shimmer-hide', function(){ clearTimeout(t); hide(); });
  // hook FastNetState hydrate
  var w=0;
  document.addEventListener('DOMContentLoaded', function(){
    var check=setInterval(function(){
      if(window.FastNetState && !w){
        w=1; clearInterval(check);
        var orig=window.FastNetState.hydrate;
        window.FastNetState.hydrate=async function(u){
          window.dispatchEvent(new CustomEvent('fastnet:shimmer-show'));
          try{ return await orig.call(this,u); } finally{ window.dispatchEvent(new CustomEvent('fastnet:shimmer-hide')); }
        };
      }
    },200);
    setTimeout(function(){ clearInterval(check); },5000);
  });
})();
