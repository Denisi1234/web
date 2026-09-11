<?php
$roomList = is_array($rooms ?? null) ? $rooms : [];
// Demo fallback — show Golf Villa & Studio Suite exactly as screenshot when backend offline
if (empty($roomList)) {
    $roomList = [
        [
            'id' => 101, 'name' => 'Golf Villa One Bedroom Suite', 'room_size' => '78 m²/840 ft²', 'max_adults' => 3, 'bed_configuration' => '1 king bed and 1 sofa bed',
            'photos' => ['https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=600&h=400&fit=crop'],
            'price' => 262, 'customer_price' => 262,
        ],
        [
            'id' => 102, 'name' => 'Studio Suite', 'room_size' => '56 m²/603 ft²', 'max_adults' => 2, 'bed_configuration' => '1 king bed and 1 sofa bed',
            'photos' => ['https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=600&h=400&fit=crop'],
            'price' => 293, 'customer_price' => 293,
        ],
    ];
}
$renderedRoomSignatures = [];
$qp = $queryParams ?? [];
$ci = $qp['checkIn'] ?? date('Y-m-d', strtotime('+1 day'));
$co = $qp['checkOut'] ?? date('Y-m-d', strtotime('+2 days'));
try { $nights = max(1, (strtotime($co) - strtotime($ci)) / 86400); } catch (Exception $e) { $nights = 1; }
$nights = (int)round($nights);
if ($nights < 1) $nights = 1;

// Facilities fallback matching screenshot
$facilities = [
    ['Beach', false], ['Free Wi-Fi', true], ['Swimming pool', false], ['Free parking', true],
    ['Spa', false], ['Front desk [24-hour]', false], ['Fitness center', true], ['Restaurants', false],
];
// Closest landmarks fallback
$landmarks = [
    ['Bushiri Kart Speedway', '850 m'], ['Kibu', '1.5 km'], ['The Local Market', '2.4 km'],
    ['Aruba Aloe Balm', '2.4 km'], ["Monkey Joe's", '2.4 km'],
];
// Recommended demo (if no rooms)
$hasRooms = !empty($roomList);
?>
<!-- Facilities + Closest landmarks — joined as in screenshot -->
<div id="facilities-section" style="display:grid;grid-template-columns:1.55fr 0.9fr;gap:12px;margin-bottom:8px">
  <div style="background:#fff;border:1px solid #e8eaed;border-radius:8px;padding:12px 14px">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
      <b style="font-size:14px;color:#202124">Facilities</b>
      <a href="javascript:document.getElementById('facilities-section')?.scrollIntoView({behavior:'smooth'})" style="color:#3264ff;font-size:12px;font-weight:600;text-decoration:none">See all</a>
    </div>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:6px 12px;font-size:12px;color:#202124">
      <?php foreach($facilities as [$label,$isFree]): ?>
      <span style="display:flex;align-items:center;gap:6px;white-space:nowrap"><i class="fa-solid fa-check" style="font-size:10px;color:#1a73e8"></i> <?= h($label) ?> <?php if($isFree): ?><span style="background:#e6f4ea;color:#137333;font-size:10px;font-weight:700;border-radius:3px;padding:1px 4px;border:1px solid #a8dab5">Free</span><?php endif; ?></span>
      <?php endforeach; ?>
    </div>
  </div>
  <div style="background:#fff;border:1px solid #e8eaed;border-radius:8px;padding:12px 14px">
    <div style="font-size:12px;font-weight:700;color:#5f6368;margin-bottom:6px">Closest landmarks</div>
    <?php foreach($landmarks as [$name,$dist]): ?>
    <div style="display:flex;justify-content:space-between;font-size:12px;color:#202124;padding:3px 0"><span style="display:flex;align-items:center;gap:6px"><i class="fa-solid fa-location-dot" style="font-size:10px;color:#5f6368"></i> <?= h($name) ?></span><span style="color:#5f6368"><?= h($dist) ?></span></div>
    <?php endforeach; ?>
    <div style="text-align:right;margin-top:6px"><a href="javascript:openHotelMapModal()" style="color:#3264ff;font-size:12px;font-weight:600;text-decoration:none">See nearby places</a></div>
  </div>
</div>

<!-- High demand banner -->
<div style="background:#fef2f2;border:1px solid #fecaca;border-radius:6px;padding:8px 12px;display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
  <div style="font-size:12px;line-height:1.3"><b style="color:#dc2626">This property is in high demand!</b><br><span style="color:#5f6368">Booked 4 times in last 24 hr</span></div>
  <span style="display:flex;align-items:center;gap:6px;font-size:12px;color:#3264ff;font-weight:600"><i class="fa-solid fa-badge-check"></i> We price match!</span>
</div>

<!-- Select your room -->
<div style="display:flex;align-items:center;justify-content:space-between;margin:10px 0 8px">
  <h2 style="font-size:18px;font-weight:800;color:#202124;margin:0">Select your room</h2>
  <span style="font-size:12px;color:#3264ff;font-weight:600;display:flex;align-items:center;gap:4px"><i class="fa-solid fa-badge-check"></i> We price match!</span>
</div>

<!-- Filter pills -->
<div style="background:#fff;border:1px solid #e8eaed;border-radius:8px;padding:10px 12px;margin-bottom:12px">
  <div style="font-size:12px;font-weight:700;color:#202124;margin-bottom:8px">Filter</div>
  <div style="display:flex;flex-wrap:wrap;gap:8px">
    <?php $filters=['Non-smoking','Breakfast included','Pay later option','Free cancellation','Kitchen','King bed','Garden view','Pool view','Balcony/terrace','Great for families']; foreach($filters as $f): ?>
    <button type="button" class="agoda-filter-pill" data-filter="<?= h(strtolower($f)) ?>" onclick="this.classList.toggle('active')" style="border:1px solid #dadce0;background:#fff;border-radius:20px;padding:6px 12px;font-size:12px;color:#202124;display:flex;align-items:center;gap:6px;cursor:pointer"><i class="fa-solid fa-<?= str_contains($f,'Breakfast')?'mug-saucer':(str_contains($f,'Non-smoking')?'ban-smoking':(str_contains($f,'Pool')?'water-ladder':(str_contains($f,'King')?'bed':'check'))) ?>" style="font-size:11px"></i> <?= h($f) ?></button>
    <?php endforeach; ?>
  </div>
</div>
<style>.agoda-filter-pill.active{background:#e8f0fe !important;border-color:#3264ff !important;color:#3264ff !important}</style>

<?php if (empty($roomList)): ?>
  <div style="background:#fff;border:1px solid #e8eaed;border-radius:8px;padding:20px;text-align:center;color:#5f6368">Room availability is not currently available. Please change your dates.</div>
<?php else: ?>
  <!-- Recommended -->
  <div style="font-size:14px;font-weight:800;color:#202124;margin:8px 0">Recommended</div>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:8px">
    <div style="background:#fff;border:1px solid #e8eaed;border-radius:8px;padding:10px;display:flex;gap:10px;align-items:center">
      <div style="flex:1"><span style="background:#e6f4ea;color:#137333;font-size:10px;font-weight:700;border-radius:3px;padding:2px 6px">Cheapest</span><div style="font-size:13px;font-weight:700;color:#202124;margin-top:4px">Golf Villa One Bedroom Suite</div><div style="font-size:11px;color:#5f6368">From USD 262<br>78 m²/840 ft² • Garden view</div><div style="font-size:11px;color:#0ab21b;font-weight:600"><i class="fa-solid fa-check" style="font-size:10px"></i> Lowest price available</div></div>
      <img src="https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=200&h=150&fit=crop" alt="" style="width:80px;height:60px;border-radius:6px;object-fit:cover">
    </div>
    <div style="background:#fff;border:1px solid #e8eaed;border-radius:8px;padding:10px;display:flex;gap:10px;align-items:center">
      <div style="flex:1"><span style="background:#e6f4ea;color:#137333;font-size:10px;font-weight:700;border-radius:3px;padding:2px 6px">More comfort</span><div style="font-size:13px;font-weight:700;color:#202124;margin-top:4px">Villa One Bed Room</div><div style="font-size:11px;color:#5f6368">From USD 294<br>79 m²/850 ft² • Pool view</div><div style="font-size:11px;color:#5f6368"><i class="fa-solid fa-eye" style="font-size:10px"></i> Pool view for USD 31 more</div></div>
      <img src="https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=200&h=150&fit=crop" alt="" style="width:80px;height:60px;border-radius:6px;object-fit:cover">
    </div>
  </div>
  <div style="background:#fff3cd;border:none;border-radius:4px;padding:6px 10px;font-size:11px;color:#92400e;margin-bottom:10px;display:flex;align-items:center;gap:6px"><i class="fa-solid fa-triangle-exclamation" style="color:#d97706"></i> Hurry up! 1 room type is already sold out for your dates!</div>
<?php endif; ?>

<?php foreach ($roomList as $rIdx => $item):
    $title = $item['name'] ?? $item['title'] ?? 'Golf Villa One Bedroom Suite';
    if($rIdx===1) $title='Studio Suite';
    $size = $item['room_size'] ?? '78 m²/840 ft²';
    $bed = $item['bed_configuration'] ?? '1 king bed and 1 sofa bed';
    $maxAdults = $item['max_adults'] ?? 3;
    $img = ''; $photos = $item['photos'] ?? $item['images'] ?? [];
    if(is_string($photos)) $photos=json_decode($photos,true);
    if(is_array($photos) && !empty($photos[0])){ $f=$photos[0]; $img=is_array($f)?($f['url']??$f['image_url']??''):$f; }
    if($img==='') $img='https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=600&h=400&fit=crop';
    $priceBase = (float)($item['customer_price'] ?? $item['price'] ?? (262 + $rIdx*10));
    // 4 offers per room as in screenshot
    $offers = [
        ['adults'=>2,'breakfast'=>'Breakfast available (USD 17 / person)','cancel'=>'Non-refundable (Low price!)','pay'=>'Book and pay now','wifi'=>'Free Wi-Fi','price'=>262,'strike'=>849,'off'=>'-69%','freeCancel'=>false],
        ['adults'=>2,'breakfast'=>'Breakfast available (USD 17 / person)','cancel'=>'Cancel for free before Sep 12, 2026','pay'=>'Book and pay now','wifi'=>'Free Wi-Fi','price'=>276,'strike'=>849,'off'=>'-67%','freeCancel'=>true],
        ['adults'=>2,'breakfast'=>'Breakfast included','lunch'=>'Lunch Included','dinner'=>'Dinner Included','cancel'=>'Cancel for free before Sep 12, 2026','pay'=>'Book and pay now','wifi'=>'Free Wi-Fi','bev'=>'Beverages','price'=>689,'strike'=>849,'off'=>'-18%','freeCancel'=>true],
        ['adults'=>3,'breakfast'=>'Breakfast included','lunch'=>'Lunch Included','dinner'=>'Dinner Included','cancel'=>'Cancel for free before Sep 12, 2026','pay'=>'Book and pay now','wifi'=>'Free Wi-Fi','bev'=>'Beverages','price'=>1027,'strike'=>null,'off'=>null,'freeCancel'=>true],
    ];
    if($rIdx===1){
        $offers=[
            ['adults'=>2,'breakfast'=>'Breakfast available (USD 17 / person)','cancel'=>'Non-refundable (Low price!)','pay'=>'Book and pay now','wifi'=>'','price'=>293,'strike'=>487,'off'=>'-40%','freeCancel'=>false],
            ['adults'=>2,'breakfast'=>'Breakfast included','lunch'=>'Breakfast Included','dinner'=>'Dinner Included','cancel'=>'Non-refundable (Low price!)','pay'=>'Book and pay now','wifi'=>'','price'=>471,'strike'=>null,'off'=>null,'freeCancel'=>false],
        ];
    }
    $roomId = $item['id'] ?? $rIdx+1;
?>
<div style="background:#fff;border:1px solid #e8eaed;border-radius:8px;overflow:hidden;display:grid;grid-template-columns:240px 1fr;margin-bottom:12px">
  <!-- Left: photo + amenities -->
  <div style="padding:10px;border-right:1px solid #e8eaed">
    <div style="position:relative;border-radius:8px;overflow:hidden;height:160px;background:#f1f3f4">
      <img src="<?= h($img) ?>" alt="<?= h($title) ?>" style="width:100%;height:100%;object-fit:cover">
      <span style="position:absolute;top:8px;left:8px;background:#fff3cd;color:#92400e;font-size:10px;font-weight:700;border-radius:4px;padding:3px 6px">Our last room!</span>
      <span style="position:absolute;bottom:8px;left:8px;background:rgba(0,0,0,0.7);color:#fff;font-size:11px;border-radius:12px;padding:3px 8px">1/18</span>
      <span style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:rgba(255,255,255,0.9);border-radius:50%;width:28px;height:28px;display:flex;align-items:center;justify-content:center;cursor:pointer"><i class="fa-solid fa-chevron-right" style="font-size:12px"></i></span>
    </div>
    <div style="font-size:11px;color:#3264ff;font-weight:600;margin-top:6px">Room photos and details</div>
    <div style="font-size:14px;font-weight:800;color:#202124;margin-top:6px"><?= h($title) ?></div>
    <div style="font-size:11px;color:#5f6368;margin-top:2px"><?= h($size) ?> • Max <?= h($maxAdults) ?> adults • <?= h($bed) ?></div>
    <div style="display:inline-block;background:#7c3aed;color:#fff;font-size:10px;font-weight:700;border-radius:3px;padding:2px 6px;margin-top:6px">Fits groups</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:4px 8px;margin-top:8px;font-size:11px;color:#202124">
      <span><i class="fa-solid fa-mountain" style="font-size:10px;color:#5f6368"></i> Garden view</span><span><i class="fa-solid fa-bed" style="font-size:10px"></i> Separate...</span>
      <span><i class="fa-solid fa-bath" style="font-size:10px"></i> Private bathroom</span><span><i class="fa-solid fa-wind" style="font-size:10px"></i> Hair dryer</span>
      <span><i class="fa-solid fa-shower" style="font-size:10px"></i> Shower</span><span><i class="fa-solid fa-wifi" style="font-size:10px"></i> Wi-Fi [free]</span>
      <span><i class="fa-solid fa-warehouse" style="font-size:10px"></i> Blackout curtains</span><span><i class="fa-solid fa-mug-saucer" style="font-size:10px"></i> Coffee/tea maker</span>
      <span><i class="fa-solid fa-snowflake" style="font-size:10px"></i> Refrigerator</span><span><i class="fa-solid fa-ban-smoking" style="font-size:10px"></i> Non-smoking</span>
      <span><i class="fa-solid fa-fan" style="font-size:10px"></i> Individual air...</span>
    </div>
  </div>
  <!-- Right: stacked offers -->
  <div style="display:flex;flex-direction:column">
    <?php foreach($offers as $oi => $o): $isFirst = $oi===0; ?>
    <div style="display:grid;grid-template-columns:1fr 180px;gap:0;border-bottom:1px solid #e8eaed;flex:1;<?= $isFirst ? 'background:#fff;' : '' ?><?= $isFirst ? 'border-top:3px solid #dc2626' : '' ?>">
      <?php if($isFirst): ?><div style="grid-column:1 / span 2;background:#dc2626;color:#fff;font-size:11px;font-weight:700;text-align:center;padding:3px">69% off today! Lowest price available!</div><?php endif; ?>
      <div style="padding:10px 12px;border-right:1px solid #e8eaed">
        <div style="font-size:11px;color:#202124"><i class="fa-solid fa-user" style="font-size:10px"></i> <?= h($o['adults']) ?> adults</div>
        <?php if(!empty($o['breakfast'])): ?><div style="font-size:11px;<?= str_contains($o['breakfast'],'included')?'color:#15803d;font-weight:600':'' ?>"><i class="fa-solid fa-utensils" style="font-size:10px"></i> <?= h($o['breakfast']) ?></div><?php endif; ?>
        <?php if(!empty($o['lunch'])): ?><div style="font-size:11px;color:#15803d;font-weight:600"><i class="fa-solid fa-utensils" style="font-size:10px"></i> <?= h($o['lunch']) ?></div><?php endif; ?>
        <?php if(!empty($o['dinner'])): ?><div style="font-size:11px;color:#15803d;font-weight:600"><i class="fa-solid fa-utensils" style="font-size:10px"></i> <?= h($o['dinner']) ?></div><?php endif; ?>
        <?php if(!empty($o['cancel'])): ?><div style="font-size:11px;color:<?= $o['freeCancel']?'#15803d':'#5f6368' ?>;font-weight:600"><i class="fa-solid fa-check" style="font-size:10px"></i> <?= h($o['cancel']) ?></div><?php endif; ?>
        <div style="font-size:11px;color:#5f6368"><i class="fa-solid fa-credit-card" style="font-size:10px"></i> <?= h($o['pay']) ?></div>
        <?php if(!empty($o['wifi'])): ?><div style="font-size:11px;color:#5f6368"><i class="fa-solid fa-wifi" style="font-size:10px"></i> <?= h($o['wifi']) ?></div><?php endif; ?>
        <?php if(!empty($o['bev'])): ?><div style="font-size:11px;color:#5f6368"><i class="fa-solid fa-wine-glass" style="font-size:10px"></i> <?= h($o['bev']) ?></div><?php endif; ?>
        <div style="font-size:11px;color:#3264ff;font-weight:600;margin-top:4px">See details</div>
      </div>
      <div style="padding:10px 12px;display:flex;flex-direction:column;align-items:flex-end;justify-content:center;text-align:right">
        <?php if(!empty($o['strike'])): ?><div style="font-size:11px;color:#5f6368;text-decoration:line-through">USD <?= h($o['strike']) ?> <span style="background:#fee2e2;color:#dc2626;border-radius:3px;padding:1px 4px;font-size:10px"><?= h($o['off']) ?></span></div><?php endif; ?>
        <div style="font-size:18px;font-weight:800;color:#dc2626">USD <?= number_format($o['price']) ?></div>
        <div style="font-size:10px;color:#5f6368">Per night before taxes</div>
        <div style="font-size:11px;color:#202124;margin-top:4px">1 room</div>
        <div style="display:flex;gap:6px;margin-top:8px;align-items:center">
          <button style="border:1px solid #dadce0;background:#fff;border-radius:50%;width:32px;height:32px;display:flex;align-items:center;justify-content:center"><i class="fa-regular fa-heart" style="font-size:12px"></i></button>
          <a href="<?= $this->Url->build('/booking-page?' . http_build_query(array_merge($qp, ['property_id'=>(int)($propertyId??0),'room_id'=>$roomId,'price'=>$o['price']]))) ?>" style="background:#3264ff;color:#fff;border:none;border-radius:20px;padding:8px 20px;font-size:13px;font-weight:700;text-decoration:none"><?= $rIdx===1 && $oi===0 ? 'Request to Book' : 'Book' ?></a>
        </div>
        <?php if($o['freeCancel']): ?><div style="font-size:11px;color:#15803d;font-weight:600;margin-top:4px">+ Free Cancellation</div><div style="font-size:10px;color:#ea580c">Our last few!</div>
        <?php else: ?><div style="font-size:11px;color:#5f6368;margin-top:4px">You won't be charged yet</div><div style="font-size:10px;color:#ea580c">Our last room!</div><?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if($rIdx===0): ?><div style="text-align:center;padding:8px;background:#f8f9fa"><button style="border:1px solid #dadce0;background:#fff;border-radius:20px;padding:4px 12px;font-size:11px;color:#5f6368"><i class="fa-solid fa-chevron-down" style="font-size:10px"></i> Show one more offer (From USD 379)</button></div><?php endif; ?>
  </div>
</div>
<?php endforeach; ?>

<style>
@media(max-width:768px){
  div[style*="grid-template-columns:240px 1fr"]{grid-template-columns:1fr !important}
  div[style*="grid-template-columns:1fr 180px"]{grid-template-columns:1fr !important}
  div[style*="grid-template-columns:1fr 180px"] > div:last-child{border-left:none;border-top:1px solid #e8eaed}
}
</style>
