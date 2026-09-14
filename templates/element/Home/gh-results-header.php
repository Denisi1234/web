<?php
/**
 * fastnetstays.com — Google Hotels Results Header
 * "near Dar es Salaam • 118 results" style subheader.
 */
$count    = $totalCount ?? count($properties ?? []);
$destName = !empty($queryParams['destination']) ? \App\Utility\TextFormatter::formatTitle((string)$queryParams['destination']) : 'Tanzania';
?>
<style>
.gh-m-price-toggle { display:none; gap:8px; margin-top:8px; overflow-x:auto; scrollbar-width:none; }
.gh-m-price-toggle::-webkit-scrollbar{display:none}
.gh-m-price-pill { display:inline-flex; align-items:center; gap:6px; padding:6px 12px; border-radius:16px; border:1px solid #dadce0; background:#fff; font-size:13px; font-weight:500; color:#3c4043; white-space:nowrap; font-family:'Google Sans',Roboto,sans-serif; }
.gh-m-price-pill.active { background:#e8f0fe; border-color:#aecbfa; color:#1967d2; }
/* Mobile sort button */
.gh-m-sort-btn { display:none; align-items:center; gap:6px; padding:6px 12px; border:1px solid #dadce0; border-radius:18px; background:#fff; font-size:13px; font-weight:500; color:#3c4043; cursor:pointer; font-family:'Google Sans',Roboto,sans-serif; white-space:nowrap; touch-action:manipulation; }
.gh-m-sort-btn i { font-size:11px; color:#5f6368; }
/* Mobile sort dropdown */
.gh-m-sort-menu { display:none; position:fixed; bottom:0; left:0; right:0; background:#fff; border-radius:20px 20px 0 0; box-shadow:0 -8px 32px rgba(0,0,0,.15); z-index:2000; padding:16px 0 calc(16px + env(safe-area-inset-bottom,0px)); }
.gh-m-sort-menu.open { display:block; }
.gh-m-sort-menu-title { font-size:15px; font-weight:700; color:#202124; padding:0 20px 14px; border-bottom:1px solid #e8eaed; margin-bottom:6px; font-family:'Google Sans',Roboto,sans-serif; }
.gh-m-sort-opt { display:flex; align-items:center; justify-content:space-between; padding:14px 20px; font-size:14px; color:#202124; font-family:'Google Sans',Roboto,sans-serif; cursor:pointer; text-decoration:none; touch-action:manipulation; }
.gh-m-sort-opt:active { background:#f8f9fa; }
.gh-m-sort-opt.active { color:#1a73e8; font-weight:600; }
.gh-m-sort-opt.active::after { content:'✓'; font-size:14px; color:#1a73e8; }
.gh-m-sort-backdrop { display:none; position:fixed; inset:0; z-index:1999; background:rgba(0,0,0,.4); }
.gh-m-sort-backdrop.open { display:block; }
@media(max-width:767px){
  .gh-m-price-toggle{ display:flex; }
  .gh-results-count-row{ border-bottom:none !important; padding-bottom:4px !important; }
  .gh-near-part{ display:inline-block !important; max-width:52vw; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; vertical-align:bottom; }
  .gh-m-sort-btn { display:inline-flex; }
}
@media(max-width:380px){ .gh-near-part{ max-width:42vw; font-size:13px; } .gh-results-count-row{ font-size:13px !important; } }
</style>

<!-- Mobile sort backdrop -->
<div class="gh-m-sort-backdrop" id="gh_sort_backdrop" onclick="ghCloseMobileSort()"></div>
<!-- Mobile sort bottom sheet -->
<div class="gh-m-sort-menu" id="gh_sort_menu" role="dialog" aria-modal="true" aria-label="Sort results">
  <div class="gh-m-sort-menu-title">Sort by</div>
  <?php
  $sortOpts = ['recommended'=>'Recommended','price_asc'=>'Price: low to high','price_desc'=>'Price: high to low','rating'=>'Highest rating'];
  $currentSort = $queryParams['sort'] ?? 'recommended';
  foreach($sortOpts as $k=>$lbl):
    $p = $queryParams; $p['sort'] = $k; $url = '/?' . http_build_query($p);
  ?>
  <a href="<?= h($url) ?>" class="gh-m-sort-opt <?= $currentSort===$k?'active':'' ?>" onclick="ghCloseMobileSort()"><?= h($lbl) ?></a>
  <?php endforeach; ?>
</div>

<div class="gh-results-count-row d-flex align-items-center justify-content-between mb-1 flex-wrap gap-2" style="border-bottom:1px solid #e8eaed; padding-bottom:8px;">
    <div style="font-size:14px; color:#202124; font-family:'Google Sans',Roboto,sans-serif;">
        <span class="gh-near-part" style="color:#202124;">near <?= h($destName) ?> <span style="color:#5f6368; margin:0 4px;">·</span></span><span id="gh_results_count" data-count="<?= (int)$count ?>" style="color:#202124;"><?= number_format($count) ?> results</span>
        <span style="color:#5f6368; margin-left:6px; display:inline-flex; vertical-align:middle;" title="About results"><i class="fa-regular fa-circle-question" style="font-size:12px;"></i></span>
        <?php if (!empty($queryParams['amenities'])): ?>
            <span class="badge ms-2" style="background:#e8f0fe; color:#1967d2; font-size:11px; font-weight:500; border-radius:12px; padding:2px 8px;">Filtered</span>
        <?php endif; ?>
    </div>
    <div class="d-flex align-items-center gap-2">
        <!-- Desktop sort — hidden on mobile -->
        <div class="dropdown d-none d-md-block">
            <button type="button" class="btn btn-sm bg-white border d-inline-flex align-items-center gap-1" data-bs-toggle="dropdown" aria-expanded="false" style="font-size:13px; font-weight:500; color:#3c4043; border-color:#dadce0 !important; border-radius:18px; padding:4px 10px; font-family:'Google Sans',Roboto,sans-serif;">
                <?php $sortLabel = ['recommended'=>'Recommended','price_asc'=>'Price: low to high','price_desc'=>'Price: high to low','rating'=>'Highest rating'][($queryParams['sort'] ?? 'recommended')] ?? 'Recommended'; echo h($sortLabel); ?>
                <i class="fa-solid fa-chevron-down" style="font-size:10px; color:#5f6368"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border rounded-3 p-1" style="min-width:200px;">
                <?php
                foreach($sortOpts as $k=>$lbl):
                  $p=$queryParams; $p['sort']=$k; $url='/?'.http_build_query($p);
                ?>
                <li><a class="dropdown-item rounded <?= ($queryParams['sort']??'recommended')===$k?'active':'' ?>" href="<?= h($url) ?>" style="font-size:13px;"><?= h($lbl) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <!-- Mobile sort button — visible only on phones -->
        <button type="button" class="gh-m-sort-btn d-md-none" onclick="ghOpenMobileSort()" aria-haspopup="dialog" aria-label="Sort results">
            <i class="fa-solid fa-arrow-up-wide-short"></i>
            Sort
        </button>
        <button type="button" class="btn p-0 border-0 bg-transparent d-inline-flex align-items-center justify-content-center d-none d-md-inline-flex" style="width:28px;height:28px; color:#5f6368;" title="How payments affect ranking" aria-label="How payments affect ranking" data-bs-toggle="tooltip" data-bs-placement="top">
            <i class="fa-regular fa-circle-question" style="font-size:14px;"></i>
        </button>
    </div>
</div>
<div class="gh-m-price-toggle" role="group" aria-label="Price display mode">
    <button type="button" class="gh-m-price-pill active" id="gh_price_nightly" data-mode="nightly" aria-pressed="true" onclick="ghSetPriceMode('nightly')"><i class="fa-solid fa-xmark" style="font-size:11px;"></i> Nightly total price</button>
    <button type="button" class="gh-m-price-pill" id="gh_price_stay" data-mode="stay" aria-pressed="false" onclick="ghSetPriceMode('stay')">Stay total price</button>
</div>
<script>
(function(){
  var NIGHTLY='nightly', STAY='stay';
  function applyMode(mode){
    var nBtn=document.getElementById('gh_price_nightly'), sBtn=document.getElementById('gh_price_stay');
    if(!nBtn||!sBtn) return;
    var isNightly = mode===NIGHTLY;
    nBtn.classList.toggle('active', isNightly); nBtn.setAttribute('aria-pressed', isNightly?'true':'false');
    sBtn.classList.toggle('active', !isNightly); sBtn.setAttribute('aria-pressed', !isNightly?'true':'false');
    try{ localStorage.setItem('gh_price_mode', mode); }catch(e){}
    // update all card prices (desktop + mobile bottom bar)
    document.querySelectorAll('.gh-hotel-price[data-nightly],.gh-card-viewmap-price[data-nightly]').forEach(function(el){
      var nightly=el.getAttribute('data-nightly')||'', total=el.getAttribute('data-total')||'';
      var sub=el.querySelector('.gh-price-night');
      if(mode===STAY && total){
        el.childNodes[0].textContent=total+' ';
        if(sub) sub.textContent='total';
      } else {
        el.childNodes[0].textContent=nightly+' ';
        if(sub) sub.textContent='/night';
      }
    });
    // also update map mini price pills
    document.querySelectorAll('.gh-card-map-price[data-nightly]').forEach(function(el){
      el.textContent = mode===STAY ? (el.getAttribute('data-total')||el.textContent) : (el.getAttribute('data-nightly')||el.textContent);
    });
    // map markers (Mapbox pills)
    if(window._ghCfg && window._ghCfg.markers){
      // markers label is nightly; stay mode shows total = nightly * nights
      try{
        var nights = parseInt(document.getElementById('gh_results_count')?.getAttribute('data-nights')||'1',10)||1;
        if(window.ghRefreshMarkers){
          // rebuild labels for stay mode
          var m = window._ghCfg.markers.map(function(x){
            var p = parseInt(String(x.label).replace(/[^0-9]/g,''))||0;
            var lbl = mode===STAY && nights>1 ? (x.label.replace(String(p), String(p*nights))) : x.label;
            // keep original nightly in _orig
            if(!x._orig) x._orig=x.label;
            x.label = mode===STAY ? x._orig.replace(String(p), String(p*nights)) : x._orig;
            return x;
          });
          // only refresh if mode switch needs visual (avoid full re-cluster flicker — just update DOM)
          document.querySelectorAll('.gh-mm-price').forEach(function(node){
            var id=node.closest('.gh-map-marker')?.getAttribute('data-id');
            var mk= m.find(function(v){ return String(v.id)===String(id); });
            if(mk) node.textContent=mk.label;
          });
        }
      }catch(e){}
    }
  }
  window.ghSetPriceMode=function(mode){ applyMode(mode); };
  // Mobile sort — same keys as desktop dropdown (?sort=...), via AJAX state when available
  window.ghMobileSort=function(v){
    if(window.FastNetState && window.FastNetState.pushState){ window.FastNetState.pushState({sort:v}); return; }
    try{
      var u=new URL(window.location.href);
      u.searchParams.set('sort', v);
      window.location.href=u.toString();
    }catch(e){ window.location.search='?sort='+encodeURIComponent(v); }
  };
  // init from storage
  document.addEventListener('DOMContentLoaded', function(){
    var saved='nightly';
    try{ saved=localStorage.getItem('gh_price_mode')||'nightly'; }catch(e){}
    // also respect query ?price_mode=
    try{ var u=new URL(window.location.href); var q=u.searchParams.get('price_mode'); if(q==='stay'||q==='nightly') saved=q; }catch(e){}
    applyMode(saved);
    // expose nights for markers
    var ci='<?= h($queryParams['checkin'] ?? $queryParams['checkIn'] ?? '') ?>', co='<?= h($queryParams['checkout'] ?? $queryParams['checkOut'] ?? '') ?>';
    var nights=1; try{ if(ci&&co) nights=Math.max(1, Math.round((new Date(co)-new Date(ci))/86400000)); }catch(e){}
    var rc=document.getElementById('gh_results_count'); if(rc) rc.setAttribute('data-nights', String(nights));
  });
  // keep price mode after AJAX hydrate (FastNetState replaces cards)
  window.addEventListener('fastnet:shimmer-hide', function(){
    var m='nightly'; try{ m=localStorage.getItem('gh_price_mode')||'nightly'; }catch(e){}
    setTimeout(function(){ applyMode(m); }, 30);
  });
  // live total stays: update "X results" when markers change (filter/map)
  window.addEventListener('fastnet:markers-update', function(e){
    var arr=e.detail; if(!Array.isArray(arr)) return;
    var el=document.getElementById('gh_results_count');
    if(el){ el.setAttribute('data-count', String(arr.length)); el.textContent= new Intl.NumberFormat().format(arr.length)+' results'; }
  });
  // also after FastNetState hydrate (payload.totalCount)
  document.addEventListener('DOMContentLoaded', function(){
    var iv=setInterval(function(){
      if(window.FastNetState && !window._ghCountHooked){
        window._ghCountHooked=true; clearInterval(iv);
        var orig=window.FastNetState.hydrate;
        window.FastNetState.hydrate=async function(u){
          var r=await orig.call(this,u);
          if(r && typeof r.totalCount==='number'){
            var el=document.getElementById('gh_results_count');
            if(el){ el.setAttribute('data-count', String(r.totalCount)); el.textContent= new Intl.NumberFormat().format(r.totalCount)+' results'; }
          }
          return r;
        };
      }
    },200);
  });
})();
  window.ghOpenMobileSort = function(){
    var m=document.getElementById('gh_sort_menu'), b=document.getElementById('gh_sort_backdrop');
    if(m) m.classList.add('open');
    if(b) b.classList.add('open');
    document.body.style.overflow='hidden';
  };
  window.ghCloseMobileSort = function(){
    var m=document.getElementById('gh_sort_menu'), b=document.getElementById('gh_sort_backdrop');
    if(m) m.classList.remove('open');
    if(b) b.classList.remove('open');
    document.body.style.overflow='';
  };
</script>
