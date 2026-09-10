<?php
/**
 * FastNet Stays — All Filters Modal (Enterprise)
 * - 2-column layout, histogram, AzamPay, Meals, Neighborhoods, sticky footer Show [N]
 */
$qp = $queryParams ?? [];
$totalCount = $totalCount ?? count($properties ?? []);
$selectedAmenities = !empty($qp['amenities']) ? array_map('trim', explode(',', $qp['amenities'])) : [];
$minPrice = $qp['price_min'] ?? $qp['min_price'] ?? '';
$maxPrice = $qp['price_max'] ?? $qp['max_price'] ?? '';
$selectedRating = $qp['rating'] ?? '';
$freeCancel = !empty($qp['free_cancellation']);
$propType = $qp['property_type'] ?? '';
$payment = $qp['payment'] ?? '';
$meals = $qp['meals'] ?? '';
$neighborhood = $qp['neighborhood'] ?? '';
$amenityOptions = [
    ['key'=>'Wi-Fi','label'=>'Free Wi-Fi','icon'=>'fa-wifi'],
    ['key'=>'Swimming pool','label'=>'Swimming Pool','icon'=>'fa-person-swimming'],
    ['key'=>'Air conditioning','label'=>'Air Conditioning','icon'=>'fa-snowflake'],
    ['key'=>'Breakfast','label'=>'Breakfast Included','icon'=>'fa-mug-saucer'],
    ['key'=>'Airport shuttle','label'=>'Airport Shuttle','icon'=>'fa-van-shuttle'],
    ['key'=>'Free Parking','label'=>'Free Parking','icon'=>'fa-square-parking'],
    ['key'=>'Fitness center','label'=>'Fitness Center','icon'=>'fa-dumbbell'],
    ['key'=>'Spa','label'=>'Spa','icon'=>'fa-spa'],
    ['key'=>'Pet friendly','label'=>'Pet Friendly','icon'=>'fa-paw'],
    ['key'=>'Room service','label'=>'Room Service','icon'=>'fa-bell-concierge'],
    ['key'=>'4-star hotel','label'=>'4-Star Hotel','icon'=>'fa-star'],
    ['key'=>'5-star hotel','label'=>'5-Star Hotel','icon'=>'fa-star'],
];
?>
<style>
#fnsFiltersModal .modal-content{border:1px solid #E5E7EB;box-shadow:0 20px 60px rgba(0,0,0,.18);}
#fnsFiltersModal .modal-header{border-bottom:1px solid #E5E7EB;background:#fff;}
#fnsFiltersModal .modal-footer{position:sticky;bottom:0;background:#fff;border-top:1px solid #E5E7EB;z-index:2;}
.fns-hist{display:flex;align-items:flex-end;gap:3px;height:56px;margin:10px 0 12px;}
.fns-hist-bar{flex:1;background:#E5E7EB;border-radius:3px 3px 0 0;min-width:3px;}
.fns-hist-bar.active{background:#2563EB;}
.fns-range{position:relative;height:6px;background:#E5E7EB;border-radius:9999px;margin:16px 0 6px;}
.fns-range-fill{position:absolute;top:0;bottom:0;background:#2563EB;border-radius:9999px;}
.fns-range thumb {}
</style>
<div class="modal fade" id="fnsFiltersModal" tabindex="-1" aria-labelledby="fnsFiltersLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl" style="max-width:860px;">
    <div class="modal-content rounded-4 overflow-hidden" style="font-family:'Inter','Google Sans',Roboto,sans-serif;">
      <div class="modal-header px-4 py-3">
        <h5 class="modal-title fw-bold" id="fnsFiltersLabel" style="color:#1F2937;font-size:18px;"><i class="fa-solid fa-sliders me-2" style="color:#2563EB;"></i>All filters</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="<?= $this->Url->build('/') ?>" method="GET" id="fns_filter_form" onsubmit="if(window.fnsTriggerShimmer) fnsTriggerShimmer();">
        <!-- preserve search -->
        <input type="hidden" name="city" value="<?= h($qp['city'] ?? $qp['destination'] ?? '') ?>">
        <input type="hidden" name="destination" value="<?= h($qp['destination'] ?? $qp['city'] ?? '') ?>">
        <input type="hidden" name="checkin" value="<?= h($qp['checkin'] ?? $qp['checkIn'] ?? '') ?>">
        <input type="hidden" name="checkout" value="<?= h($qp['checkout'] ?? $qp['checkOut'] ?? '') ?>">
        <input type="hidden" name="checkIn" value="<?= h($qp['checkIn'] ?? $qp['checkin'] ?? '') ?>">
        <input type="hidden" name="checkOut" value="<?= h($qp['checkOut'] ?? $qp['checkout'] ?? '') ?>">
        <input type="hidden" name="adults" value="<?= (int)($qp['adults']??2) ?>">
        <input type="hidden" name="children" value="<?= (int)($qp['children']??0) ?>">
        <input type="hidden" name="rooms" value="<?= (int)($qp['rooms']??1) ?>">
        <?php if(!empty($qp['lat'])): ?><input type="hidden" name="lat" value="<?= h($qp['lat']) ?>"><input type="hidden" name="lng" value="<?= h($qp['lng']) ?>"><?php endif; ?>

        <div class="modal-body p-0" style="background:#fff;">
          <div class="container-fluid px-4 py-4">
            <div class="row g-4">
              <!-- Left column -->
              <div class="col-lg-6">
                <!-- Price Per Night -->
                <div class="mb-4 pb-4 border-bottom" style="border-color:#E5E7EB !important;">
                  <h6 class="fw-bold mb-1" style="color:#1F2937;font-size:15px;">Price per night</h6>
                  <p class="mb-2" style="font-size:12px;color:#6B7280;">Histogram shows pricing distribution for your dates</p>
                  <div class="fns-hist" id="fns_modal_hist" aria-hidden="true"></div>
                  <div class="fns-range" aria-hidden="true"><div class="fns-range-fill" id="fns_modal_fill"></div></div>
                  <div class="row g-3 mt-1">
                    <div class="col-6"><label class="d-block mb-1" style="font-size:11px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:#6B7280;">Minimum (TZS)</label><input type="number" name="price_min" id="fns_mdl_min" class="form-control" placeholder="0" value="<?= h($minPrice) ?>" min="0" style="border-color:#E5E7EB;border-radius:10px;"></label><input type="hidden" name="min_price" id="fns_mdl_min_legacy" value="<?= h($minPrice) ?>"></div>
                    <div class="col-6"><label class="d-block mb-1" style="font-size:11px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:#6B7280;">Maximum (TZS)</label><input type="number" name="price_max" id="fns_mdl_max" class="form-control" placeholder="Any" value="<?= h($maxPrice) ?>" min="0" style="border-color:#E5E7EB;border-radius:10px;"></label><input type="hidden" name="max_price" id="fns_mdl_max_legacy" value="<?= h($maxPrice) ?>"></div>
                  </div>
                </div>
                <!-- Payment Options -->
                <div class="mb-4 pb-4 border-bottom" style="border-color:#E5E7EB !important;">
                  <h6 class="fw-bold mb-2" style="color:#1F2937;font-size:15px;">Payment options</h6>
                  <div class="d-grid gap-2">
                    <label class="d-flex align-items-center gap-3 p-3 border rounded-3" style="border-color:<?= $payment==='azampay'?'#2563EB':'#E5E7EB' ?> !important;background:<?= $payment==='azampay'?'#EFF6FF':'#fff' ?>;cursor:pointer;">
                      <input type="radio" name="payment" value="azampay" <?= $payment==='azampay'?'checked':'' ?> style="accent-color:#2563EB;">
                      <span style="flex:1;"><span class="fw-semibold" style="color:#1F2937;">Pay Online via AzamPay</span><br><span style="font-size:12px;color:#6B7280;">M-Pesa, Tigo Pesa, Airtel Money — instant confirmation</span></span>
                      <span style="display:flex;gap:4px;align-items:center;"><img src="/assets/img/airtel-logo.png" alt="" style="height:16px;width:auto;object-fit:contain;opacity:.9;" onerror="this.style.display='none'"><span style="font-size:10px;font-weight:700;background:#FF6B00;color:#fff;border-radius:4px;padding:2px 4px;">AzamPay</span></span>
                    </label>
                    <label class="d-flex align-items-center gap-3 p-3 border rounded-3" style="border-color:<?= $payment==='pay_at_property'?'#2563EB':'#E5E7EB' ?> !important;background:<?= $payment==='pay_at_property'?'#EFF6FF':'#fff' ?>;cursor:pointer;">
                      <input type="radio" name="payment" value="pay_at_property" <?= $payment==='pay_at_property'?'checked':'' ?> style="accent-color:#2563EB;">
                      <span style="flex:1;"><span class="fw-semibold" style="color:#1F2937;">Pay at Property</span><br><span style="font-size:12px;color:#6B7280;">Reserve now, pay when you stay</span></span>
                      <i class="fa-solid fa-house" style="color:#6B7280;"></i>
                    </label>
                    <label class="d-flex align-items-center gap-2" style="font-size:13px;color:#6B7280;cursor:pointer;"><input type="radio" name="payment" value="" <?= $payment===''?'checked':'' ?> style="accent-color:#2563EB;"> Any payment method</label>
                  </div>
                </div>
                <!-- Meals -->
                <div class="mb-4 pb-4 border-bottom" style="border-color:#E5E7EB !important;">
                  <h6 class="fw-bold mb-2" style="color:#1F2937;font-size:15px;">Meals</h6>
                  <div class="d-flex flex-wrap gap-2">
                    <?php foreach(['breakfast'=>'Breakfast included','all_inclusive'=>'All-inclusive','self_catering'=>'Self-catering'] as $k=>$lbl): ?>
                    <label class="btn rounded-pill px-3 py-2 <?= $meals===$k?'btn-primary':'btn-outline-secondary' ?>" style="<?= $meals===$k?'background:#2563EB;border-color:#2563EB;':'' ?>font-size:13px;font-weight:600;cursor:pointer;">
                      <input type="radio" name="meals" value="<?= $k ?>" class="d-none" <?= $meals===$k?'checked':'' ?>> <?= h($lbl) ?>
                    </label>
                    <?php endforeach; ?>
                    <label class="btn rounded-pill px-3 py-2 <?= $meals===''?'btn-primary':'btn-outline-secondary' ?>" style="<?= $meals===''?'background:#2563EB;border-color:#2563EB;':'' ?>font-size:13px;font-weight:600;cursor:pointer;">
                      <input type="radio" name="meals" value="" class="d-none" <?= $meals===''?'checked':'' ?>> Any
                    </label>
                  </div>
                </div>
                <!-- Neighborhoods -->
                <div>
                  <h6 class="fw-bold mb-2" style="color:#1F2937;font-size:15px;">Neighborhoods &amp; proximity</h6>
                  <div class="d-grid gap-2">
                    <?php foreach(['city_center'=>'Near city center (≤2 km)','airport'=>'Near airport (≤10 km)','beachfront'=>'Beachfront','quiet_area'=>'Quiet area'] as $k=>$lbl):
                        $active = $neighborhood===$k;
                    ?>
                    <label class="d-flex align-items-center gap-2 p-2 border rounded-3" style="border-color:<?= $active?'#2563EB':'#E5E7EB' ?>;background:<?= $active?'#EFF6FF':'#fff' ?>;cursor:pointer;font-size:13px;">
                      <input type="radio" name="neighborhood" value="<?= $k ?>" <?= $active?'checked':'' ?> style="accent-color:#2563EB;">
                      <span style="color:#1F2937;font-weight:500;"><?= h($lbl) ?></span>
                    </label>
                    <?php endforeach; ?>
                    <label class="d-flex align-items-center gap-2" style="font-size:13px;color:#6B7280;cursor:pointer;"><input type="radio" name="neighborhood" value="" <?= $neighborhood===''?'checked':'' ?> style="accent-color:#2563EB;"> Any location</label>
                  </div>
                </div>
              </div>
              <!-- Right column -->
              <div class="col-lg-6">
                <!-- Property Type -->
                <div class="mb-4 pb-4 border-bottom" style="border-color:#E5E7EB !important;">
                  <h6 class="fw-bold mb-2" style="color:#1F2937;font-size:15px;">Property type</h6>
                  <div class="d-flex flex-wrap gap-2">
                    <?php foreach(['Hotel','Resort','Apartment','Safari Lodge','Villa'] as $t):
                        $active = $propType===$t;
                    ?>
                    <label class="btn rounded-pill px-3 py-2 <?= $active?'btn-primary':'btn-outline-secondary' ?>" style="<?= $active?'background:#2563EB;border-color:#2563EB;':'' ?>font-size:13px;font-weight:600;cursor:pointer;">
                      <input type="radio" name="property_type" value="<?= h($t) ?>" class="d-none" <?= $active?'checked':'' ?>> <?= h($t) ?>
                    </label>
                    <?php endforeach; ?>
                    <label class="btn rounded-pill px-3 py-2 <?= $propType===''?'btn-primary':'btn-outline-secondary' ?>" style="<?= $propType===''?'background:#2563EB;border-color:#2563EB;':'' ?>font-size:13px;font-weight:600;cursor:pointer;">
                      <input type="radio" name="property_type" value="" class="d-none" <?= $propType===''?'checked':'' ?>> Any
                    </label>
                  </div>
                </div>
                <!-- Popular Amenities -->
                <div class="mb-4 pb-4 border-bottom" style="border-color:#E5E7EB !important;">
                  <div class="d-flex align-items-center justify-content-between mb-2">
                    <h6 class="fw-bold mb-0" style="color:#1F2937;font-size:15px;">Popular amenities</h6>
                    <span style="font-size:11px;color:#6B7280;">Select all that apply</span>
                  </div>
                  <div class="row g-2">
                    <?php foreach($amenityOptions as $opt):
                        $isChecked = in_array($opt['key'], $selectedAmenities, true) || in_array(strtolower($opt['key']), array_map('strtolower',$selectedAmenities), true);
                    ?>
                    <div class="col-6">
                      <label class="d-flex align-items-center gap-2 p-2 border rounded-3 h-100" style="border-color:<?= $isChecked?'#2563EB':'#E5E7EB' ?>;background:<?= $isChecked?'#EFF6FF':'#fff' ?>;cursor:pointer;transition:all .15s;">
                        <input type="checkbox" name="amenities[]" value="<?= h($opt['key']) ?>" <?= $isChecked?'checked':'' ?> style="accent-color:#2563EB;">
                        <i class="fa-solid <?= $opt['icon'] ?>" style="font-size:13px;color:#2563EB;width:16px;text-align:center;"></i>
                        <span style="font-size:13px;color:#1F2937;font-weight:500;"><?= h($opt['label']) ?></span>
                      </label>
                    </div>
                    <?php endforeach; ?>
                  </div>
                </div>
                <!-- Guest rating -->
                <div class="mb-4 pb-4 border-bottom" style="border-color:#E5E7EB !important;">
                  <h6 class="fw-bold mb-2" style="color:#1F2937;font-size:15px;">Guest rating</h6>
                  <div class="d-flex flex-wrap gap-2">
                    <?php foreach(['4.5'=>'Exceptional (4.5+)','4.0'=>'Very good (4.0+)','3.5'=>'Good (3.5+)'] as $val=>$lbl): ?>
                    <label class="btn rounded-pill px-3 py-2 <?= $selectedRating===$val?'btn-primary':'btn-outline-secondary' ?>" style="<?= $selectedRating===$val?'background:#2563EB;border-color:#2563EB;':'' ?>font-size:13px;font-weight:600;cursor:pointer;">
                      <input type="radio" name="rating" value="<?= $val ?>" class="d-none" <?= $selectedRating===$val?'checked':'' ?>> <?= h($lbl) ?>
                    </label>
                    <?php endforeach; ?>
                    <?php if($selectedRating!==''): ?>
                    <label class="btn rounded-pill px-3 py-2 btn-outline-secondary" style="font-size:13px;font-weight:600;cursor:pointer;"><input type="radio" name="rating" value="" class="d-none"> Any rating</label>
                    <?php endif; ?>
                  </div>
                </div>
                <!-- Booking flexibility -->
                <div>
                  <h6 class="fw-bold mb-2" style="color:#1F2937;font-size:15px;">Booking flexibility</h6>
                  <label class="d-flex align-items-center justify-content-between p-3 border rounded-3" style="border-color:<?= $freeCancel?'#2563EB':'#E5E7EB' ?>;background:<?= $freeCancel?'#EFF6FF':'#fff' ?>;cursor:pointer;">
                    <span>
                      <span style="font-weight:600;color:#1F2937;font-size:14px;">Free cancellation only</span><br>
                      <span style="font-size:12px;color:#6B7280;">Show only stays with free cancellation</span>
                    </span>
                    <span class="form-check form-switch m-0"><input class="form-check-input" type="checkbox" name="free_cancellation" value="1" <?= $freeCancel?'checked':'' ?> style="width:42px;height:24px;" role="switch"></span>
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- Sticky footer -->
        <div class="modal-footer px-4 py-3 d-flex justify-content-between align-items-center">
          <a href="<?= $this->Url->build('/' . (!empty($qp['city']??$qp['destination']??'') ? '?city='.urlencode($qp['city']??$qp['destination']??'') . (!empty($qp['checkin']??$qp['checkIn']??'') ? '&checkin='.urlencode($qp['checkin']??$qp['checkIn']??'') : '') . (!empty($qp['checkout']??$qp['checkOut']??'') ? '&checkout='.urlencode($qp['checkout']??$qp['checkOut']??'') : '') . '&adults='.(int)($qp['adults']??2) : '')) ?>" class="btn btn-link text-decoration-none fw-semibold" style="color:#6B7280;" onclick="event.preventDefault(); document.getElementById('fns_filter_form').reset(); document.getElementById('fns_mdl_min').value=''; document.getElementById('fns_mdl_max').value=''; document.getElementById('fns_mdl_min_legacy').value=''; document.getElementById('fns_mdl_max_legacy').value=''; if(window.FastNetState) { FastNetState.pushState({price_min:'',price_max:'',min_price:'',max_price:'',amenities:'',rating:'',free_cancellation:'',property_type:'',payment:'',meals:'',neighborhood:''}); bootstrap.Modal.getInstance(document.getElementById('fnsFiltersModal')).hide(); } else window.location.href=this.href;">Reset</a>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold" style="background:#2563EB;border-color:#2563EB;">Show <?= (int)$totalCount ?> properties</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- backward compat: keep old #ghFiltersModal id pointing to new modal via JS -->
<script>
(function(){
  // alias old modal id for existing chips triggers
  var old=document.getElementById('ghFiltersModal');
  var neu=document.getElementById('fnsFiltersModal');
  if(old && neu && old!==neu){ /* if legacy modal still rendered, hide it */ old.id='ghFiltersModal_legacy'; }
  // ensure chips button targets fns modal if legacy attribute present
  document.querySelectorAll('[data-bs-target="#ghFiltersModal"]').forEach(function(btn){ btn.setAttribute('data-bs-target','#fnsFiltersModal'); });
  function buildHist(){
    var hist=document.getElementById('fns_modal_hist'), fill=document.getElementById('fns_modal_fill');
    if(!hist) return;
    hist.innerHTML='';
    var vals=[2,4,7,12,18,28,36,44,40,33,26,18,11,6,3];
    var max=Math.max(...vals);
    var min=parseInt(document.getElementById('fns_mdl_min')?.value||0,10)||0;
    var maxP=parseInt(document.getElementById('fns_mdl_max')?.value||500000,10)||500000;
    vals.forEach(function(v,i){
      var bar=document.createElement('div'); bar.className='fns-hist-bar'; bar.style.height=(v/max*100)+'%';
      var bucketMid=i*33333;
      if(bucketMid>=min && bucketMid<= (maxP||500000)) bar.classList.add('active');
      hist.appendChild(bar);
    });
    if(fill){
      var pctMin=(Math.min(min,500000)/500000)*100, pctMax=(Math.min(maxP,500000)/500000)*100;
      fill.style.left=pctMin+'%'; fill.style.right=(100-pctMax)+'%';
    }
  }
  document.addEventListener('DOMContentLoaded', buildHist);
  ['fns_mdl_min','fns_mdl_max'].forEach(function(id){
    document.getElementById(id)?.addEventListener('input', function(){
      var legacy=document.getElementById(id+'_legacy');
      if(legacy) legacy.value=this.value;
      buildHist();
    });
  });
  // sync legacy on submit
  document.getElementById('fns_filter_form')?.addEventListener('submit', function(e){
    document.getElementById('fns_mdl_min_legacy').value=document.getElementById('fns_mdl_min').value;
    document.getElementById('fns_mdl_max_legacy').value=document.getElementById('fns_mdl_max').value;
    // intercept with FastNetState if available
    if(window.FastNetState){
      e.preventDefault();
      var fd=new FormData(this);
      var obj={}; fd.forEach(function(v,k){
        if(k==='amenities[]'){ obj['amenities'] = obj['amenities'] ? obj['amenities']+','+v : v; }
        else obj[k]=v;
      });
      // map to spec
      if(obj['price_min']!==undefined) obj['min_price']=obj['price_min'];
      if(obj['price_max']!==undefined) obj['max_price']=obj['price_max'];
      window.FastNetState.pushState(obj);
      var m=bootstrap.Modal.getInstance(document.getElementById('fnsFiltersModal')); if(m) m.hide();
      if(typeof fnsTriggerShimmer==='function') fnsTriggerShimmer();
    }
  });
  // update Show N count live via FastNetState hydrate
  window.addEventListener('fastnet:markers-update', function(e){
    var btn=document.querySelector('#fnsFiltersModal .modal-footer .btn-primary');
    if(btn && Array.isArray(e.detail)) btn.textContent='Show '+e.detail.length+' properties';
  });
})();
</script>
<!-- keep legacy modal hidden but available for fallback -->
<div class="d-none" aria-hidden="true" id="ghFiltersModal_legacy_holder"></div>
