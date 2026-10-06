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
<?= $this->Html->css('/assets/css/gh-filter-chips.css?v=' . filemtime(WWW_ROOT . 'assets/css/gh-filter-chips.css')) ?>

<div class="fns-chips-wrap" id="fns_chips_wrap">
  <div class="container-fluid px-1 px-lg-2" style="max-width:100%;margin:0 auto;">
    <!-- Track prices — Carbon Toggle -->
    <div class="cds-toggle-track" style="display:flex;align-items:center;gap:8px;padding:4px 0 2px;font-size:13px;">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0f62fe" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
      <span class="cds-toggle-label" style="color:#161616;font-weight:600;font-family:'IBM Plex Sans','Inter',Roboto,sans-serif;font-size:13px;">Track prices</span>
      <button type="button" style="border:none;background:transparent;padding:0;line-height:0;" data-bs-toggle="tooltip" title="Get notified when prices drop" aria-label="About price tracking"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg></button>
      <label style="position:relative;display:inline-block;width:38px;height:22px;margin-left:4px;"><input type="checkbox" id="fns_track_chk" style="display:none;" onchange="fnsTrackToggle(this.checked)" role="switch" aria-checked="false" aria-label="Track prices"><span style="position:absolute;inset:0;border-radius:9999px;background:#E5E7EB;transition:.2s;display:flex;align-items:center;justify-content:flex-end;padding-right:3px;" id="fns_track_slider"><span style="width:16px;height:16px;border-radius:50%;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.2);display:block;"></span></span></label>
      <span id="fns_track_status" style="font-size:12px;color:#6B7280;">Off</span>
    </div>
    <div class="fns-chips-outer show-right" id="fns_chips_outer">
      <div class="fns-chips-row" id="fns_chips_row" role="toolbar" aria-label="Filters">

        <!-- All Filters -->
        <button type="button" class="fns-chip fns-chip-filters <?= $hasFilters ? 'active' : '' ?>" data-bs-toggle="modal" data-bs-target="#fnsFiltersModal" aria-haspopup="dialog" aria-expanded="false">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="4" y1="7" x2="20" y2="7"/><circle cx="9" cy="7" r="2.2" fill="#fff"/><line x1="4" y1="17" x2="20" y2="17"/><circle cx="15" cy="17" r="2.2" fill="#fff"/></svg>
          All filters<?php if($filterCount>0): ?><span class="badge"><?= $filterCount ?></span><?php endif; ?>
        </button>

        <!-- Price Range -->
        <div style="position:relative;flex-shrink:0;" id="fns_price_anchor">
          <button type="button" class="fns-chip <?= ($priceMin!==''||$priceMax!=='')?'active':'' ?>" id="fns_price_chip" aria-haspopup="dialog" aria-expanded="false" aria-controls="fns_price_pop" onclick="fnsTogglePrice(event)">
            <i class="fa-solid fa-money-bill-wave"></i>
            <?php if($priceMin!==''||$priceMax!==''): ?>
              <?= 'TZS '.($priceMin!==''?number_format((int)$priceMin):'0').' – '.($priceMax!==''?number_format((int)$priceMax):'Any') ?>
            <?php else: ?>
              <span class="fns-chip-long">Price Range</span><span class="fns-chip-short" style="display:none;">Price</span>
            <?php endif; ?>
            <i class="fa-solid fa-caret-down fns-caret"></i>
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

        <!-- Property Type — popular only; full list lives in All filters -->
        <div class="fns-chip-group" role="group" aria-label="Property type" style="display:contents;">
          <?php
          $types = ['Hotel'=>'fa-hotel','Apartment'=>'fa-building','Safari Lodge'=>'fa-campground'];
          foreach($types as $label=>$icon):
              $active = $propType === $label;
          ?>
          <a href="<?= h($buildUrl(['property_type'=> $active ? '' : $label])) ?>" class="fns-chip <?= $active?'active':'' ?>" role="button" aria-pressed="<?= $active?'true':'false' ?>" data-filter="property_type" data-value="<?= h($label) ?>">
            <i class="fa-solid <?= $icon ?>" style="font-size:11px;"></i> <?= h($label) ?>
          </a>
          <?php endforeach; ?>
        </div>

        <!-- Offers — mobile parity (opens All filters); desktop hidden via CSS -->
        <button type="button" class="fns-chip" data-filter="offers" data-bs-toggle="modal" data-bs-target="#fnsFiltersModal" aria-haspopup="dialog">
          <i class="fa-solid fa-tag"></i> Offers <i class="fa-solid fa-caret-down fns-caret"></i>
        </button>

        <!-- Guest Rating — one popular threshold; rest in All filters -->
        <a href="<?= h($buildUrl(['rating'=> $ratingSel==='4.0' ? '' : '4.0'])) ?>" class="fns-chip fns-chip-rating <?= $ratingSel==='4.0'?'active':'' ?>" aria-pressed="<?= $ratingSel==='4.0'?'true':'false' ?>" data-filter="rating" data-value="4.0">
          <span class="star">★</span> <span class="fns-chip-long">4.0+ Very Good</span><span class="fns-chip-short" style="display:none;">Guest rating</span> <i class="fa-solid fa-caret-down fns-caret d-lg-none"></i>
        </a>

        <!-- Free Cancellation toggle chip -->
        <a href="<?= h($buildUrl(['free_cancellation'=> $freeCancel ? '' : '1'])) ?>" class="fns-chip <?= $freeCancel?'active':'' ?>" role="switch" aria-checked="<?= $freeCancel?'true':'false' ?>" data-filter="free_cancellation" data-value="1">
          <i class="fa-solid fa-circle-check" style="font-size:12px;"></i> Free Cancellation
        </a>

        <!-- Popular Amenities — only blue when user has actively clicked/filtered.
             Rest live in All filters. -->
        <?php
        $amenChips = [
          'Wi-Fi'=>'fa-wifi',
          'Swimming pool'=>'fa-person-swimming',
          'Breakfast'=>'fa-mug-saucer',
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
<?= $this->Html->script('/assets/js/gh-filter-chips.js?v=' . filemtime(WWW_ROOT . 'assets/js/gh-filter-chips.js')) ?>
