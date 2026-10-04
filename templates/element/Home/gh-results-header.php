<?php
/**
 * fastnetstays.com — Results header: "near X · N results" + sort.
 */
$count    = $totalCount ?? count($properties ?? []);
$totalHits = $totalHits ?? null;
$destName = !empty($queryParams['destination']) ? \App\Utility\TextFormatter::formatTitle((string)$queryParams['destination']) : 'Tanzania';
// Honest count: "48 of 120 stays" when backend paginator reports more hits than shown
$countLabel = number_format($count) . ' results';
if ($totalHits !== null && $totalHits > $count) $countLabel = number_format($count) . ' of ' . number_format($totalHits) . ' stays';
?>
<style>
/* Mobile sort button */
.gh-m-sort-btn { display:none; align-items:center; gap:6px; padding:6px 12px; border:1px solid #dadce0; border-radius:18px; background:#fff; font-size:13px; font-weight:500; color:#3c4043; cursor:pointer; font-family:'Google Sans',Roboto,sans-serif; white-space:nowrap; touch-action:manipulation; }
.gh-m-sort-btn i { font-size:11px; color:#5f6368; }
/* Mobile sort dropdown */
.gh-m-sort-menu { display:none; position:fixed; bottom:0; left:0; right:0; background:#fff; border-radius:20px 20px 0 0; box-shadow:0 -8px 32px rgba(0,0,0,.15); z-index:2000; padding:16px 0 calc(16px + env(safe-area-inset-bottom,0px)); }
.gh-m-sort-menu.open { display:block; }
.gh-m-sort-menu-title { font-size:15px; font-weight:700; color:#202124; padding:0 20px 14px; border-bottom:1px solid #e8eaed; margin-bottom:6px; font-family:'Google Sans',Roboto,sans-serif; }
.gh-m-sort-opt { display:flex; align-items:center; justify-content:space-between; padding:14px 20px; font-size:14px; color:#202124; font-family:'Google Sans',Roboto,sans-serif; cursor:pointer; text-decoration:none; touch-action:manipulation; }
.gh-m-sort-opt:active { background:#f8f9fa; }
.gh-m-sort-opt.active { color:#0f62fe; font-weight:600; }
.gh-m-sort-opt.active::after { content:'✓'; font-size:14px; color:#0f62fe; }
.gh-m-sort-backdrop { display:none; position:fixed; inset:0; z-index:1999; background:rgba(0,0,0,.4); }
.gh-m-sort-backdrop.open { display:block; }
@media(max-width:767px){
  .gh-results-count-row{ border-bottom:none !important; padding-bottom:4px !important; }
  .gh-near-part{ display:inline-block !important; max-width:52vw; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; vertical-align:bottom; }
  .gh-m-sort-btn { display:inline-flex; }
}
@media(max-width:380px){ .gh-near-part{ max-width:42vw; font-size:13px; } .gh-results-count-row{ font-size:13px !important; } }
</style>

<!-- Mobile sort backdrop -->
<div class="gh-m-sort-backdrop" id="gh_sort_backdrop" onclick="ghCloseMobileSort()"></div>
<!-- Mobile sort bottom sheet -->
<div class="gh-m-sort-menu" id="gh_sort_menu" role="dialog" aria-modal="true" aria-label="Sort results" aria-hidden="true">
  <div class="gh-m-sort-menu-title">Sort by</div>
  <?php
  $sortOpts = ['recommended'=>'Recommended · top rated','price_asc'=>'Price: low to high','price_desc'=>'Price: high to low','rating'=>'Highest rating'];
  $currentSort = $queryParams['sort'] ?? 'recommended';
  foreach($sortOpts as $k=>$lbl):
    $p = $queryParams; $p['sort'] = $k; $url = '/?' . http_build_query($p);
  ?>
  <a href="<?= h($url) ?>" class="gh-m-sort-opt <?= $currentSort===$k?'active':'' ?>" onclick="ghCloseMobileSort()"><?= h($lbl) ?></a>
  <?php endforeach; ?>
</div>

<div class="gh-results-count-row cds-section-head d-flex align-items-center justify-content-between mb-1 flex-wrap gap-2" style="border-bottom:1px solid #e8eaed; padding-bottom:8px;">
    <div style="font-size:14px; color:#161616; font-family:'IBM Plex Sans','Inter','Google Sans',Roboto,sans-serif;">
        <span class="gh-near-part" style="color:#161616;">near <?= h($destName) ?> <span style="color:#525252; margin:0 4px;">·</span></span><span id="gh_results_count" data-count="<?= (int)$count ?>" <?= $totalHits !== null && $totalHits > $count ? 'data-total="' . (int)$totalHits . '"' : '' ?> style="color:#161616;"><?= h($countLabel) ?></span>
        <span style="color:#5f6368; margin-left:6px; display:inline-flex; vertical-align:middle;" title="About results"><i class="fa-regular fa-circle-question" style="font-size:12px;"></i></span>
        <?php if (!empty($queryParams['amenities'])): ?>
            <span class="badge ms-2" style="background:#e8f0fe; color:#1967d2; font-size:11px; font-weight:500; border-radius:12px; padding:2px 8px;">Filtered</span>
        <?php endif; ?>
    </div>
    <div class="d-flex align-items-center gap-2">
        <!-- Desktop sort — hidden on mobile -->
        <div class="dropdown d-none d-md-block">
            <button type="button" class="btn btn-sm bg-white border d-inline-flex align-items-center gap-1" data-bs-toggle="dropdown" aria-expanded="false" title="Recommended sorts highest rated first" style="font-size:13px; font-weight:500; color:#3c4043; border-color:#dadce0 !important; border-radius:18px; padding:4px 10px; font-family:'Google Sans',Roboto,sans-serif;">
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
        <button type="button" class="gh-m-sort-btn d-md-none" onclick="ghOpenMobileSort()" aria-haspopup="dialog" aria-expanded="false" aria-controls="gh_sort_menu" aria-label="Sort results">
            <i class="fa-solid fa-arrow-up-wide-short"></i>
            Sort
        </button>
        <button type="button" class="btn p-0 border-0 bg-transparent d-inline-flex align-items-center justify-content-center d-none d-md-inline-flex" style="width:28px;height:28px; color:#5f6368;" title="How payments affect ranking" aria-label="How payments affect ranking" data-bs-toggle="tooltip" data-bs-placement="top">
            <i class="fa-regular fa-circle-question" style="font-size:14px;"></i>
        </button>
    </div>
</div>
<script>
(function(){
  // Prices are nightly everywhere. Stay totals render inline on each card
  // ("TSh X total for N nights") and map pills show nightly rates — one rule,
  // no mode switch. Display currency is owned by FastNetCurrency via [data-tzs].
  // Mobile sort — same keys as desktop dropdown (?sort=...), via AJAX state when available
  window.ghMobileSort=function(v){
    if(window.FastNetState && window.FastNetState.pushState){ window.FastNetState.pushState({sort:v}); return; }
    try{
      var u=new URL(window.location.href);
      u.searchParams.set('sort', v);
      window.location.href=u.toString();
    }catch(e){ window.location.search='?sort='+encodeURIComponent(v); }
  };
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
    if(m){ m.classList.add('open'); m.setAttribute('aria-hidden','false'); }
    if(b) b.classList.add('open');
    document.body.style.overflow='hidden';
    document.querySelectorAll('.gh-m-sort-btn').forEach(function(btn){ btn.setAttribute('aria-expanded','true'); });
  };
  window.ghCloseMobileSort = function(){
    var m=document.getElementById('gh_sort_menu'), b=document.getElementById('gh_sort_backdrop');
    if(m){ m.classList.remove('open'); m.setAttribute('aria-hidden','true'); }
    if(b) b.classList.remove('open');
    document.body.style.overflow='';
    document.querySelectorAll('.gh-m-sort-btn').forEach(function(btn){ btn.setAttribute('aria-expanded','false'); });
  };
</script>
