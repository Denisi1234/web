<?php
/**
 * FastNet Stays — Filter Chips Bar (Enterprise)
 * - Horizontally scrollable row with gradient fade edges
 * - Chips: Price Range (slider popover), Property Type, Guest Rating, Free Cancellation, Popular Amenities, All Filters (badge)
 * - Spec URL keys: price_min/price_max, property_type, rating, amenities, free_cancellation, city, checkin, checkout
 * - Legacy aliases supported
 */
$qp = $queryParams ?? [];
// normalize price
$priceMin = $qp['price_min'] ?? $qp['min_price'] ?? '';
$priceMax = $qp['price_max'] ?? $qp['max_price'] ?? '';
$amenList = !empty($qp['amenities']) ? array_map('trim', explode(',', $qp['amenities'])) : [];
$ratingSel = $qp['rating'] ?? '';
$freeCancel = !empty($qp['free_cancellation']);
$propType = $qp['property_type'] ?? '';
$hasFilters = !empty($amenList) || $priceMin !== '' || $priceMax !== '' || $ratingSel !== '' || $freeCancel || $propType !== '';
$filterCount = count(array_filter($amenList)) + ($priceMin !== '' || $priceMax !== '' ? 1 : 0) + ($ratingSel !== '' ? 1 : 0) + ($freeCancel ? 1 : 0) + ($propType !== '' ? 1 : 0) + (!empty($qp['payment'])||!empty($qp['meals'])||!empty($qp['neighborhood']) ? 1 : 0);

$buildUrl = function(array $overrides) use ($qp): string {
    $p = $qp;
    // map legacy/spec to keep both but spec is primary
    foreach ($overrides as $k => $v) {
        if ($v === '' || $v === null) unset($p[$k]);
        else $p[$k] = $v;
        // mirror alias
        $aliasMap = ['price_min'=>'min_price','min_price'=>'price_min','price_max'=>'max_price','max_price'=>'price_max','city'=>'destination','destination'=>'city','checkin'=>'checkIn','checkIn'=>'checkin','checkout'=>'checkOut','checkOut'=>'checkout'];
        if (isset($aliasMap[$k])) {
            if ($v === '' || $v === null) unset($p[$aliasMap[$k]]);
            else $p[$aliasMap[$k]] = $v;
        }
    }
    // clean empty amenities
    if (isset($p['amenities']) && $p['amenities']==='') unset($p['amenities']);
    if (isset($p['rating']) && $p['rating']==='') unset($p['rating']);
    if (isset($p['property_type']) && $p['property_type']==='') unset($p['property_type']);
    if (isset($p['free_cancellation']) && $p['free_cancellation']==='') unset($p['free_cancellation']);
    return '/?' . http_build_query(array_filter($p, fn($v)=>$v!=='' && $v!==null));
};
$toggleAmen = function(string $key) use ($buildUrl, $amenList): string {
    $new = $amenList;
    $idx = array_search($key, $new, true);
    if ($idx !== false) unset($new[$idx]); else $new[] = $key;
    $new = array_values(array_filter($new));
    return $buildUrl(['amenities'=> implode(',', $new)]);
};
?>
<style>
/* ── Filter chips bar ── */
.fns-chips-wrap{position:sticky;top:132px;z-index:800;background:#fff;border-bottom:1px solid #E5E7EB;}
.fns-chips-outer{position:relative;}
.fns-chips-outer::before,
.fns-chips-outer::after{content:'';position:absolute;top:0;bottom:0;width:24px;pointer-events:none;z-index:2;transition:opacity .18s;}
.fns-chips-outer::before{left:0;background:linear-gradient(to right,#fff 30%, transparent);opacity:0;}
.fns-chips-outer::after{right:36px;background:linear-gradient(to left,#fff 30%, transparent);opacity:1;}
.fns-chips-outer.show-left::before{opacity:1;}
.fns-chips-outer.show-right::after{opacity:1;}
.fns-chips-row{display:flex;align-items:center;gap:8px;overflow-x:auto;overflow-y:hidden;scrollbar-width:none;scroll-behavior:smooth;padding:4px 0 8px;}
.fns-chips-row::-webkit-scrollbar{display:none;}
.fns-chip{position:relative;display:inline-flex;align-items:center;gap:8px;padding:8px 12px;margin-right:0;border:1px solid #E5E7EB;border-radius:9999px;background:#fff;color:#1F2937;font-size:13px;font-weight:500;font-family:'Inter','Google Sans',Roboto,sans-serif;cursor:pointer;text-decoration:none !important;white-space:nowrap;flex-shrink:0;line-height:16px;transition:all .15s;height:36px;}
.fns-chip:hover{border-color:#D1D5DB;background:#F9FAFB;box-shadow:0 1px 3px rgba(0,0,0,.06);}
.fns-chip.active{background:#EFF6FF;border-color:#2563EB;color:#1D4ED8;box-shadow:0 1px 2px rgba(37,99,235,.12);}
.fns-chip:focus-visible{outline:2px solid #2563EB;outline-offset:2px;}
.fns-chip-filters{font-weight:700;gap:8px;}
.fns-chip-filters.active{background:#1F2937;border-color:#1F2937;color:#fff;}
.fns-chip-filters .badge{display:inline-flex;align-items:center;justify-content:center;min-width:20px;height:20px;padding:0 6px;border-radius:9999px;background:#fff;color:#1F2937;font-size:11px;font-weight:700;margin-left:2px;}
.fns-chip-filters.active .badge{background:#fff;color:#1F2937;}
.fns-scroll-btn{position:absolute;right:0;top:50%;transform:translateY(-50%);width:36px;height:36px;border-radius:50%;border:1px solid #E5E7EB;background:#fff;color:#6B7280;display:flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 1px 4px rgba(0,0,0,.08);z-index:3;transition:all .15s;}
.fns-scroll-btn:hover{background:#F9FAFB;border-color:#D1D5DB;color:#1F2937;}
.fns-scroll-btn:focus-visible{outline:2px solid #2563EB;outline-offset:2px;}
/* Price popover */
.fns-price-pop{position:absolute;top:calc(100% + 10px);left:0;background:#fff;border:1px solid #E5E7EB;border-radius:16px;box-shadow:0 8px 30px rgba(0,0,0,.12);z-index:1200;padding:16px;width:340px;display:none;}
.fns-price-pop.open{display:block;animation:fnsPopIn .16s ease;}
.fns-price-hist{display:flex;align-items:flex-end;gap:2px;height:48px;margin:8px 0 12px;padding:0 2px;}
.fns-price-bar{flex:1;background:#E5E7EB;border-radius:2px 2px 0 0;min-width:2px;}
.fns-price-bar.active{background:#2563EB;}
.fns-range-wrap{position:relative;height:6px;background:#E5E7EB;border-radius:9999px;margin:18px 0 8px;}
.fns-range-fill{position:absolute;top:0;bottom:0;background:#2563EB;border-radius:9999px;}
.fns-range-input{position:absolute;top:-7px;width:100%;-webkit-appearance:none;appearance:none;background:transparent;pointer-events:none;}
.fns-range-input::-webkit-slider-thumb{-webkit-appearance:none;width:20px;height:20px;border-radius:50%;background:#fff;border:2px solid #2563EB;box-shadow:0 1px 4px rgba(0,0,0,.12);pointer-events:auto;cursor:pointer;}
.fns-range-input::-moz-range-thumb{width:20px;height:20px;border-radius:50%;background:#fff;border:2px solid #2563EB;box-shadow:0 1px 4px rgba(0,0,0,.12);pointer-events:auto;cursor:pointer;}
/* Rating dots etc */
.fns-chip-rating .star{color:#F59E0B;font-size:12px;}
.fns-chip-rating.active .star{color:#fff;}
/* Mobile handle */
.fns-chips-handle{display:none;}
@media(max-width:991px){
  .fns-chips-wrap{top:64px;padding:0 16px;background:#fff;}
  .fns-chips-wrap .container-fluid{padding-left:0 !important;padding-right:0 !important;}
  .fns-chip{padding:8px 12px;font-size:13px;height:40px;gap:8px;}
  .fns-chip i{font-size:12px !important;}
  .fns-chips-row{padding:10px 0 12px;gap:0;}
  .fns-chips-handle{display:flex;justify-content:center;padding:8px 0 0;}
  .fns-chips-handle span{width:40px;height:5px;background:#E5E7EB;border-radius:9999px;display:block;}
  .fns-price-pop{position:fixed !important;left:12px !important;right:12px !important;top:auto !important;bottom:12px !important;width:auto !important;max-width:none !important;border-radius:20px;max-height:78vh;overflow-y:auto;}
  .fns-scroll-btn{width:40px;height:40px;}
}
@media(max-width:380px){
  .fns-chip{padding:9px 12px;font-size:12.5px;min-height:38px;}
}
</style>

<div class="fns-chips-wrap" id="fns_chips_wrap">
  <div class="container-fluid px-1 px-lg-2" style="max-width:100%;margin:0 auto;">
    <!-- Track prices row — compact -->
    <div style="display:flex;align-items:center;gap:8px;padding:4px 0 2px;font-size:13px;">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1A73E8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
      <span style="color:#1A73E8;font-weight:600;font-family:'Inter','Google Sans',Roboto,sans-serif;font-size:13px;">Track prices</span>
      <button type="button" style="border:none;background:transparent;padding:0;line-height:0;" data-bs-toggle="tooltip" title="Get notified when prices drop" aria-label="About price tracking"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg></button>
      <label style="position:relative;display:inline-block;width:38px;height:22px;margin-left:4px;"><input type="checkbox" id="fns_track_chk" style="display:none;" onchange="fnsTrackToggle(this.checked)" role="switch" aria-checked="false" aria-label="Track prices"><span style="position:absolute;inset:0;border-radius:9999px;background:#E5E7EB;transition:.2s;display:flex;align-items:center;justify-content:flex-end;padding-right:3px;" id="fns_track_slider"><span style="width:16px;height:16px;border-radius:50%;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.2);display:block;"></span></span></label>
      <span id="fns_track_status" style="font-size:12px;color:#6B7280;">Off</span>
    </div>
    <div class="fns-chips-handle"><span></span></div>

    <div class="fns-chips-outer show-right" id="fns_chips_outer">
      <div class="fns-chips-row" id="fns_chips_row" role="toolbar" aria-label="Filters">

        <!-- All Filters -->
        <button type="button" class="fns-chip fns-chip-filters <?= $hasFilters ? 'active' : '' ?>" data-bs-toggle="modal" data-bs-target="#fnsFiltersModal" aria-haspopup="dialog" aria-expanded="false">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><line x1="4" y1="6" x2="20" y2="6"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="18" x2="20" y2="18"/></svg>
          All filters<?php if($filterCount>0): ?><span class="badge"><?= $filterCount ?></span><?php endif; ?>
        </button>

        <!-- Price Range -->
        <div style="position:relative;flex-shrink:0;" id="fns_price_anchor">
          <button type="button" class="fns-chip <?= ($priceMin!==''||$priceMax!=='')?'active':'' ?>" id="fns_price_chip" aria-haspopup="dialog" aria-expanded="false" aria-controls="fns_price_pop" onclick="fnsTogglePrice(event)">
            <i class="fa-solid fa-tag" style="font-size:11px;"></i>
            <?= ($priceMin!==''||$priceMax!=='') ? 'TZS '.($priceMin!==''?number_format((int)$priceMin):'0').' – '.($priceMax!==''?number_format((int)$priceMax):'Any') : 'Price Range' ?>
            <i class="fa-solid fa-chevron-down" style="font-size:9px;color:currentColor;"></i>
          </button>
          <div class="fns-price-pop" id="fns_price_pop" role="dialog" aria-label="Price range" onclick="event.stopPropagation()">
            <div style="font-size:13px;font-weight:700;color:#1F2937;font-family:'Inter',Roboto,sans-serif;margin-bottom:4px;">Price per night</div>
            <div style="font-size:12px;color:#6B7280;margin-bottom:8px;">Histogram shows price distribution</div>
            <div class="fns-price-hist" id="fns_price_hist" aria-hidden="true"></div>
            <div style="display:flex;gap:12px;margin-bottom:6px;">
              <label style="flex:1;"><span style="font-size:11px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:#6B7280;">Min (TZS)</span><input type="number" id="fns_price_min" value="<?= h($priceMin) ?>" placeholder="0" min="0" max="1000000" step="1000" style="width:100%;margin-top:4px;padding:9px 12px;border:1px solid #E5E7EB;border-radius:10px;font-size:14px;outline:none;"></label>
              <label style="flex:1;"><span style="font-size:11px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:#6B7280;">Max (TZS)</span><input type="number" id="fns_price_max" value="<?= h($priceMax) ?>" placeholder="Any" min="0" max="1000000" step="1000" style="width:100%;margin-top:4px;padding:9px 12px;border:1px solid #E5E7EB;border-radius:10px;font-size:14px;outline:none;"></label>
            </div>
            <div class="fns-range-wrap" aria-hidden="true"><div class="fns-range-fill" id="fns_range_fill"></div><input type="range" min="0" max="500000" step="10000" id="fns_range_min" class="fns-range-input"><input type="range" min="0" max="500000" step="10000" id="fns_range_max" class="fns-range-input"></div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:14px;">
              <button type="button" class="fns-btn-reset" onclick="fnsPriceClear()">Clear</button>
              <button type="button" class="fns-btn-apply" onclick="fnsPriceApply()">Apply</button>
            </div>
          </div>
        </div>

        <!-- Property Type -->
        <div class="fns-chip-group" role="group" aria-label="Property type" style="display:contents;">
          <?php
          $types = ['Hotel'=>'fa-hotel','Resort'=>'fa-umbrella-beach','Apartment'=>'fa-building','Safari Lodge'=>'fa-campground','Villa'=>'fa-house-chimney'];
          foreach($types as $label=>$icon):
              $active = $propType === $label;
          ?>
          <a href="<?= h($buildUrl(['property_type'=> $active ? '' : $label])) ?>" class="fns-chip <?= $active?'active':'' ?>" role="button" aria-pressed="<?= $active?'true':'false' ?>" data-filter="property_type" data-value="<?= h($label) ?>">
            <i class="fa-solid <?= $icon ?>" style="font-size:11px;"></i> <?= h($label) ?>
          </a>
          <?php endforeach; ?>
        </div>

        <!-- Guest Rating -->
        <a href="<?= h($buildUrl(['rating'=> $ratingSel==='4.5' ? '' : '4.5'])) ?>" class="fns-chip fns-chip-rating <?= $ratingSel==='4.5'?'active':'' ?>" aria-pressed="<?= $ratingSel==='4.5'?'true':'false' ?>" data-filter="rating" data-value="4.5">
          <span class="star">★</span> 4.5+ Exceptional
        </a>
        <a href="<?= h($buildUrl(['rating'=> $ratingSel==='4.0' ? '' : '4.0'])) ?>" class="fns-chip fns-chip-rating <?= $ratingSel==='4.0'?'active':'' ?>" aria-pressed="<?= $ratingSel==='4.0'?'true':'false' ?>" data-filter="rating" data-value="4.0">
          <span class="star">★</span> 4.0+ Very Good
        </a>
        <a href="<?= h($buildUrl(['rating'=> $ratingSel==='3.5' ? '' : '3.5'])) ?>" class="fns-chip fns-chip-rating <?= $ratingSel==='3.5'?'active':'' ?>" aria-pressed="<?= $ratingSel==='3.5'?'true':'false' ?>" data-filter="rating" data-value="3.5">
          <span class="star">★</span> 3.5+ Good
        </a>

        <!-- Free Cancellation toggle chip -->
        <a href="<?= h($buildUrl(['free_cancellation'=> $freeCancel ? '' : '1'])) ?>" class="fns-chip <?= $freeCancel?'active':'' ?>" role="switch" aria-checked="<?= $freeCancel?'true':'false' ?>" data-filter="free_cancellation" data-value="1">
          <i class="fa-solid fa-circle-check" style="font-size:12px;"></i> Free Cancellation
        </a>

        <!-- Popular Amenities — only blue when user has actively clicked/filtered -->
        <?php
        $amenChips = [
          'Wi-Fi'=>'fa-wifi',
          'Swimming pool'=>'fa-person-swimming',
          'Air conditioning'=>'fa-snowflake',
          'Breakfast'=>'fa-mug-saucer',
          'Airport shuttle'=>'fa-van-shuttle',
        ];
        foreach($amenChips as $amen=>$icon):
            $active = in_array($amen, $amenList, true) || in_array(strtolower($amen), array_map('strtolower',$amenList), true);
        ?>
        <a href="<?= h($toggleAmen($amen)) ?>" class="fns-chip <?= $active?'active':'' ?>" aria-pressed="<?= $active?'true':'false' ?>" data-filter="amenities" data-value="<?= h($amen) ?>">
          <i class="fa-solid <?= $icon ?>" style="font-size:11px;"></i> <?= h($amen==='Wi-Fi'?'Free Wi-Fi':$amen) ?>
        </a>
        <?php endforeach; ?>

      </div>
      <button type="button" class="fns-scroll-btn" id="fns_scroll_btn" onclick="document.getElementById('fns_chips_row').scrollBy({left:260,behavior:'smooth'})" aria-label="Scroll filters right">
        <i class="fa-solid fa-chevron-right" style="font-size:11px;"></i>
      </button>
    </div>
  </div>
</div>

<script>
(function(){
'use strict';
function fnsTrackToggle(on){
  var slider=document.getElementById('fns_track_slider');
  var chk=document.getElementById('fns_track_chk');
  var lbl=document.getElementById('fns_track_status');
  if(chk) chk.setAttribute('aria-checked', on?'true':'false');
  if(lbl) lbl.textContent=on?'On':'Off';
  if(slider){ slider.style.background=on?'#1A73E8':'#E5E7EB'; slider.style.justifyContent=on?'flex-start':'flex-end'; slider.style.paddingLeft=on?'3px':'0'; slider.style.paddingRight=on?'0':'3px'; }
}
window.fnsTrackToggle=fnsTrackToggle;
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
</script>
