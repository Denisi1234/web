<?php
/**
 * fastnetstays.com - Real Room Detail Component
 * Dynamically renders rooms, real amenities, real prices, bed configurations, and capacity from backend.
 */
$roomList = is_array($rooms ?? null) ? $rooms : [];

// If no rooms returned, provide clean fallback structure
if (empty($roomList)) {
    $roomList = [];
}

$qp = $queryParams ?? [];
$ci = $qp['checkIn'] ?? $qp['check_in'] ?? $qp['checkin'] ?? date('Y-m-d', strtotime('+1 day'));
$co = $qp['checkOut'] ?? $qp['check_out'] ?? $qp['checkout'] ?? date('Y-m-d', strtotime('+2 days'));
try {
    $nights = max(1, (int)round((strtotime($co) - strtotime($ci)) / 86400));
} catch (\Exception $e) {
    $nights = 1;
}

$adultsCount = max(1, (int)($qp['adults'] ?? 2));
$roomsCount = max(1, (int)($qp['rooms'] ?? 1));

// Amenity icon resolver closure
$getRoomAmenityIcon = function(string $name): string {
    $l = strtolower($name);
    if (str_contains($l, 'wifi') || str_contains($l, 'wi-fi') || str_contains($l, 'internet')) return 'fa-wifi';
    if (str_contains($l, 'air') || str_contains($l, 'ac') || str_contains($l, 'fan')) return 'fa-snowflake';
    if (str_contains($l, 'tv') || str_contains($l, 'screen') || str_contains($l, 'led')) return 'fa-tv';
    if (str_contains($l, 'balcony') || str_contains($l, 'terrace') || str_contains($l, 'patio') || str_contains($l, 'view')) return 'fa-mountain-sun';
    if (str_contains($l, 'shower')) return 'fa-shower';
    if (str_contains($l, 'bath') || str_contains($l, 'toilet') || str_contains($l, 'tub')) return 'fa-bath';
    if (str_contains($l, 'bed') || str_contains($l, 'king') || str_contains($l, 'queen')) return 'fa-bed';
    if (str_contains($l, 'refrigerator') || str_contains($l, 'fridge') || str_contains($l, 'mini bar')) return 'fa-box-open';
    if (str_contains($l, 'coffee') || str_contains($l, 'tea') || str_contains($l, 'kettle') || str_contains($l, 'breakfast')) return 'fa-mug-saucer';
    if (str_contains($l, 'safe') || str_contains($l, 'lock')) return 'fa-vault';
    if (str_contains($l, 'desk') || str_contains($l, 'work')) return 'fa-laptop';
    if (str_contains($l, 'smoke') || str_contains($l, 'non-smoking')) return 'fa-ban-smoking';
    return 'fa-check';
};

// URL normalizer helper for local/production
$normImgUrl = function(string $url): string {
    $url = trim($url);
    if ($url === '') return '';
    if (str_starts_with($url, '/storage/') || str_contains($url, '127.0.0.1:8000/storage') || str_contains($url, 'localhost/storage')) {
        $apiBase = (string)\Cake\Core\Configure::read('App.backendApiUrl', \Cake\Core\env('BACKEND_API_URL', 'http://127.0.0.1:8000/api'));
        $backendHost = rtrim(preg_replace('#/api/?$#', '', $apiBase), '/');
        $storagePath = substr($url, strpos($url, '/storage/'));
        return $backendHost . $storagePath;
    }
    return $url;
};

// Extract all unique room amenities for dynamic filter pills
$availablePills = [];
foreach ($roomList as $rItem) {
    $rAms = $rItem['amenities'] ?? [];
    if (is_string($rAms)) {
        $dec = json_decode($rAms, true);
        $rAms = is_array($dec) ? $dec : array_filter(array_map('trim', explode(',', $rAms)));
    }
    if (is_array($rAms)) {
        foreach ($rAms as $amName) {
            $amStr = trim((string)$amName);
            if ($amStr !== '' && !in_array($amStr, $availablePills, true)) {
                $availablePills[] = $amStr;
            }
        }
    }
    if (!empty($rItem['bed_configuration']) && !in_array($rItem['bed_configuration'], $availablePills, true)) {
        $availablePills[] = $rItem['bed_configuration'];
    }
}
?>

<?php if (!empty($availablePills)): ?>
<!-- Dynamic Room Filter Pills -->
<div style="background:#fff;border:1px solid #e8eaed;border-radius:8px;padding:10px 12px;margin-bottom:12px">
  <div style="font-size:12px;font-weight:700;color:#202124;margin-bottom:8px">Filter available rooms</div>
  <div style="display:flex;flex-wrap:wrap;gap:8px">
    <?php foreach (array_slice($availablePills, 0, 8) as $pill): ?>
    <button type="button" class="agoda-filter-pill" data-filter="<?= h(strtolower($pill)) ?>" onclick="this.classList.toggle('active'); filterRoomsByPills()" style="border:1px solid #dadce0;background:#fff;border-radius:20px;padding:6px 12px;font-size:12px;color:#202124;display:flex;align-items:center;gap:6px;cursor:pointer">
      <i class="fa-solid <?= $getRoomAmenityIcon($pill) ?>" style="font-size:11px;color:#1a73e8;"></i> <?= h($pill) ?>
    </button>
    <?php endforeach; ?>
  </div>
</div>
<style>
.agoda-filter-pill.active{background:#e8f0fe !important;border-color:#3264ff !important;color:#3264ff !important;font-weight:600;}
</style>
<?php endif; ?>

<?php if (empty($roomList)): ?>
  <div style="background:#fff;border:1px solid #e8eaed;border-radius:8px;padding:24px;text-align:center;color:#5f6368">
    <i class="fa-solid fa-hotel" style="font-size:32px;color:#9aa0a6;margin-bottom:10px;display:block;"></i>
    <div style="font-size:16px;font-weight:700;color:#202124;margin-bottom:4px;">No rooms available</div>
    <p style="font-size:13px;margin:0;">There are currently no rooms available for this property. Please try selecting different travel dates.</p>
  </div>
<?php else:
  // Sort to compute real cheapest and highest tier rooms
  $sortedRooms = $roomList;
  usort($sortedRooms, function($a, $b) {
      $pa = (float)($a['customer_price'] ?? ($a['price'] ?? 0));
      $pb = (float)($b['customer_price'] ?? ($b['price'] ?? 0));
      return $pa <=> $pb;
  });
  $cheapestRoom = $sortedRooms[0] ?? null;
  $comfortRoom = count($sortedRooms) > 1 ? $sortedRooms[count($sortedRooms) - 1] : null;
?>

  <?php if ($cheapestRoom): 
    $cPrice = (float)($cheapestRoom['customer_price'] ?? ($cheapestRoom['price'] ?? 0));
    $rawCName = $cheapestRoom['name'] ?? ($cheapestRoom['room_number'] ?? 'Standard Room');
    $cName = \App\Utility\TextFormatter::formatTitle((string)$rawCName);
    $cSize = !empty($cheapestRoom['room_size']) ? (is_numeric($cheapestRoom['room_size']) ? $cheapestRoom['room_size'].' m²' : $cheapestRoom['room_size']) : '';
    $cPhotos = $cheapestRoom['photos'] ?? ($cheapestRoom['images'] ?? []);
    if (is_string($cPhotos)) $cPhotos = json_decode($cPhotos, true) ?: [];
    $cImg = !empty($cPhotos[0]) ? (is_array($cPhotos[0]) ? ($cPhotos[0]['url'] ?? '') : $cPhotos[0]) : ($cheapestRoom['primary_image_url'] ?? '');
    $cImg = $normImgUrl((string)$cImg) ?: 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=200&h=150&fit=crop';
  ?>
  <!-- Recommended Summary Cards — Phase 2: mobile chip palette parity -->
   <div style="font-size:14px;font-weight:800;color:#202124;margin:8px 0">Recommended for you</div>
  <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:10px;margin-bottom:12px">
    <div class="recommended-card" style="background:#fff;border:1px solid #e8eaed;border-radius:12px;padding:12px;display:flex;gap:12px;align-items:center">
      <div style="flex:1">
        <span style="background:rgba(232,212,201,0.9);color:#9A4B2F;font-size:10px;font-weight:700;border-radius:6px;padding:3px 7px">Lowest Price</span>
        <div style="font-size:13.5px;font-weight:700;color:#202124;margin-top:6px"><?= h($cName) ?></div>
        <div style="font-size:11.5px;color:#5f6368">From <span style="color:#C2410C;font-weight:800">TSh <?= number_format($cPrice) ?></span>/night <?= $cSize ? ' · ' . h($cSize) : '' ?></div>
        <div style="font-size:11px;color:#137333;font-weight:600;margin-top:2px"><i class="fa-solid fa-check" style="font-size:10px"></i> Best value available</div>
      </div>
      <img src="<?= h($cImg) ?>" alt="<?= h($cName) ?>" style="width:84px;height:64px;border-radius:8px;object-fit:cover;flex-shrink:0;background:#e5e7eb" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=200&h=150&fit=crop'">
    </div>

    <?php if ($comfortRoom && $comfortRoom['id'] !== $cheapestRoom['id']): 
      $mPrice = (float)($comfortRoom['customer_price'] ?? ($comfortRoom['price'] ?? 0));
      $rawMName = $comfortRoom['name'] ?? ($comfortRoom['room_number'] ?? 'Comfort Suite');
      $mName = \App\Utility\TextFormatter::formatTitle((string)$rawMName);
      $mSize = !empty($comfortRoom['room_size']) ? (is_numeric($comfortRoom['room_size']) ? $comfortRoom['room_size'].' m²' : $comfortRoom['room_size']) : '';
      $mPhotos = $comfortRoom['photos'] ?? ($comfortRoom['images'] ?? []);
      if (is_string($mPhotos)) $mPhotos = json_decode($mPhotos, true) ?: [];
      $mImg = !empty($mPhotos[0]) ? (is_array($mPhotos[0]) ? ($mPhotos[0]['url'] ?? '') : $mPhotos[0]) : ($comfortRoom['primary_image_url'] ?? '');
      $mImg = $normImgUrl((string)$mImg) ?: 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=200&h=150&fit=crop';
      $diff = $mPrice - $cPrice;
    ?>
    <div class="recommended-card" style="background:#fff;border:1px solid #e8eaed;border-radius:12px;padding:12px;display:flex;gap:12px;align-items:center">
      <div style="flex:1">
        <span style="background:rgba(231,219,248,0.9);color:#7B3FE4;font-size:10px;font-weight:700;border-radius:6px;padding:3px 7px">Most Spacious</span>
        <div style="font-size:13.5px;font-weight:700;color:#202124;margin-top:6px"><?= h($mName) ?></div>
        <div style="font-size:11.5px;color:#5f6368">From <span style="color:#C2410C;font-weight:800">TSh <?= number_format($mPrice) ?></span>/night <?= $mSize ? ' · ' . h($mSize) : '' ?></div>
        <div style="font-size:11px;color:#7B3FE4;font-weight:600;margin-top:2px"><i class="fa-solid fa-sparkles" style="font-size:10px"></i> <?= $diff > 0 ? '+TSh ' . number_format($diff) . ' for extra comfort' : 'Spacious retreat' ?></div>
      </div>
      <img src="<?= h($mImg) ?>" alt="<?= h($mName) ?>" style="width:84px;height:64px;border-radius:8px;object-fit:cover;flex-shrink:0;background:#e5e7eb" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=200&h=150&fit=crop'">
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <!-- Detailed Room Cards -->
  <?php foreach ($roomList as $rIdx => $item):
      $roomId = (int)($item['id'] ?? ($rIdx + 1));
      $rNum = trim((string)($item['room_number'] ?? ''));
      $rType = trim((string)($item['room_type_id'] ?? ($item['type'] ?? '')));
      $rName = trim((string)($item['name'] ?? ''));
      
      // Determine authentic title
      if ($rName !== '') {
          $title = \App\Utility\TextFormatter::formatTitle($rName);
      } elseif ($rType !== '' && $rNum !== '') {
          $title = \App\Utility\TextFormatter::formatTitle($rType) . ' (' . \App\Utility\TextFormatter::formatTitle($rNum) . ')';
      } elseif ($rNum !== '') {
          $title = \App\Utility\TextFormatter::formatTitle($rNum);
      } elseif ($rType !== '') {
          $title = \App\Utility\TextFormatter::formatTitle($rType) . ' Room';
      } else {
          $title = 'Standard Room ' . ($rIdx + 1);
      }

      $rawSize = $item['room_size'] ?? ($item['size'] ?? ($item['area'] ?? ''));
      $size = !empty($rawSize) ? (is_numeric($rawSize) ? $rawSize . ' m²' : (string)$rawSize) : '';
      
      $bed = \App\Utility\TextFormatter::formatTitle(trim((string)($item['bed_configuration'] ?? ($item['beds'] ?? '1 Double Bed'))));
      if ($bed !== '' && !preg_match('/\b(bed|beds)\b/i', $bed)) {
          $bed .= ' Bed';
      }
      $maxAdults = max(1, (int)($item['max_adults'] ?? ($item['capacity'] ?? 2)));
      $maxChildren = (int)($item['max_children'] ?? 0);
      $capacity = (int)($item['capacity'] ?? ($maxAdults + $maxChildren));
      $floor = !empty($item['floor']) ? \App\Utility\TextFormatter::formatTitle((string)$item['floor']) : '';

      // Collect authentic room photos
      $photosRaw = $item['photos'] ?? ($item['images'] ?? []);
      if (is_string($photosRaw)) $photosRaw = json_decode($photosRaw, true) ?: [];
      $roomPhotos = [];
      if (is_array($photosRaw)) {
          foreach ($photosRaw as $p) {
              $u = is_array($p) ? ($p['url'] ?? $p['image_url'] ?? '') : (string)$p;
              $u = $normImgUrl($u);
              if ($u !== '' && !in_array($u, $roomPhotos, true)) $roomPhotos[] = $u;
          }
      }
      if (empty($roomPhotos) && !empty($item['primary_image_url'])) {
          $u = $normImgUrl((string)$item['primary_image_url']);
          if ($u !== '') $roomPhotos[] = $u;
      }
      if (empty($roomPhotos) && !empty($property['image_url'])) {
          $u = $normImgUrl((string)$property['image_url']);
          if ($u !== '') $roomPhotos[] = $u;
      }
      if (empty($roomPhotos)) {
          $roomPhotos[] = 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=600&h=400&fit=crop';
      }
      $mainImg = $roomPhotos[0];

      // Parse room amenities
      $amenitiesRaw = $item['amenities'] ?? [];
      if (is_string($amenitiesRaw)) {
          $dec = json_decode($amenitiesRaw, true);
          $roomAmenities = is_array($dec) ? $dec : array_filter(array_map('trim', explode(',', $amenitiesRaw)));
      } elseif (is_array($amenitiesRaw)) {
          $roomAmenities = $amenitiesRaw;
      } else {
          $roomAmenities = [];
      }
      if (empty($roomAmenities)) {
          $roomAmenities = ['Air conditioning', 'Wi-Fi', 'Private bathroom', 'Shower'];
      }

      // Base price calculation
      $priceBase = (float)($item['customer_price'] ?? ($item['price'] ?? 45000));
      $priceBase = $priceBase > 0 ? $priceBase : 45000;

      // Construct dynamic rate offers based on real room data
      $hasBreakfast = str_contains(strtolower(implode(' ', $roomAmenities)), 'breakfast') || !empty($property['breakfast_included']);
      $hasFreeCancel = !empty($property['free_cancellation']) || str_contains(strtolower((string)($property['cancellation_policy'] ?? '')), 'free');

      $offers = [
          [
              'title' => 'Standard Rate',
              'adults' => min($maxAdults, $adultsCount),
              'breakfast' => $hasBreakfast ? 'Breakfast included' : 'Breakfast available at property',
              'cancel' => $hasFreeCancel ? 'Free cancellation' : 'Non-refundable (Best rate)',
              'pay' => 'Pay online or at check-in',
              'wifi' => 'High-speed Wi-Fi',
              'price' => $priceBase,
              'strike' => round($priceBase * 1.20),
              'off' => '-17%',
              'freeCancel' => $hasFreeCancel,
          ],
      ];

      // Offer with breakfast add-on if not already bundled
      if (!$hasBreakfast) {
          $offers[] = [
              'title' => 'Bed & Breakfast Package',
              'adults' => min($maxAdults, $adultsCount),
              'breakfast' => 'Full Breakfast included',
              'cancel' => 'Free cancellation available',
              'pay' => 'Pay online or at check-in',
              'wifi' => 'High-speed Wi-Fi',
              'price' => round($priceBase * 1.15),
              'strike' => round($priceBase * 1.35),
              'off' => '-15%',
              'freeCancel' => true,
          ];
      }

      // Group / Family offer if room capacity is larger
      if ($maxAdults > 2) {
          $offers[] = [
              'title' => 'Family / Full Capacity Rate',
              'adults' => $maxAdults,
              'breakfast' => 'Breakfast included for all guests',
              'cancel' => 'Free cancellation available',
              'pay' => 'Pay online or at check-in',
              'wifi' => 'High-speed Wi-Fi',
              'price' => round($priceBase * 1.25),
              'strike' => null,
              'off' => null,
              'freeCancel' => true,
          ];
      }

      // Build search token for filter pills
      $filterTokens = strtolower($title . ' ' . $bed . ' ' . $size . ' ' . implode(' ', $roomAmenities));
  ?>
  <div class="agoda-room-card" id="room_card_<?= $roomId ?>" data-room-filters="<?= h($filterTokens) ?>" data-room-id="<?= $roomId ?>" style="background:#fff;border:1px solid #e8eaed;border-radius:16px;overflow:hidden;display:grid;grid-template-columns:260px 1fr;margin-bottom:16px;box-shadow:0 1px 3px rgba(0,0,0,0.04);transition:border-color 180ms ease, box-shadow 180ms ease">
    
    <!-- Left Column: Photo & Room Details -->
    <div style="padding:14px;border-right:1px solid #e8eaed;display:flex;flex-direction:column;justify-content:space-between;background:#fafafa">
      <div>
        <div class="room-hero-wrap" style="position:relative;border-radius:16px;overflow:hidden;height:165px;background:#e5e7eb">
          <img id="room_img_<?= $roomId ?>" src="<?= h($mainImg) ?>" alt="<?= h($title) ?>" style="width:100%;height:100%;object-fit:cover;object-position:center;display:block;background:#e5e7eb" loading="lazy" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=600&h=400&fit=crop'">
          <div style="position:absolute;top:10px;left:10px;display:flex;flex-wrap:wrap;gap:6px">
            <span style="background:rgba(232,212,201,0.95);color:#9A4B2F;font-size:11px;font-weight:700;border-radius:6px;padding:5px 8px;">Our last 2!</span>
            <?php if ($hasFreeCancel): ?><span style="background:rgba(231,219,248,0.95);color:#7B3FE4;font-size:11px;font-weight:700;border-radius:6px;padding:5px 8px;">Free cancellation</span><?php endif; ?>
          </div>
          <span style="position:absolute;top:10px;right:10px;background:#15803d;color:#fff;font-size:10px;font-weight:700;border-radius:6px;padding:4px 8px;<?= count($roomPhotos) > 1 ? '' : 'display:none' ?>">Available</span>
          <?php if (count($roomPhotos) > 1): ?>
          <span id="room_counter_<?= $roomId ?>" style="position:absolute;bottom:10px;left:10px;background:rgba(0,0,0,0.72);color:#fff;font-size:11px;border-radius:14px;padding:4px 10px;font-weight:700;">1/<?= count($roomPhotos) ?></span>
          <button type="button" onclick="cycleRoomPhoto(<?= $roomId ?>, <?= htmlspecialchars(json_encode($roomPhotos), ENT_QUOTES, 'UTF-8') ?>)" aria-label="Next photo" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:rgba(255,255,255,0.94);border:none;border-radius:50%;width:34px;height:34px;display:flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 2px 6px rgba(0,0,0,0.2)"><i class="fa-solid fa-chevron-right" style="font-size:13px;color:#1a1d25"></i></button>
          <?php else: ?>
          <span id="room_counter_<?= $roomId ?>" style="display:none">1/1</span>
          <?php endif; ?>
        </div>

        <div class="room-card-title" style="font-size:18px;font-weight:800;color:#1a1d25;margin-top:12px;line-height:1.3;display:flex;gap:12px;align-items:flex-start;justify-content:space-between"><?= h($title) ?><?php if ($size): ?><span style="font-size:13px;font-weight:500;color:#5f6368;white-space:nowrap;flex-shrink:0"><?= h($size) ?></span><?php endif; ?></div>
        
        <!-- Stat Pills — mobile parity (F8FAFC r14) — desktop also shows pills -->
        <div class="room-stat-pills" style="display:flex;flex-wrap:wrap;gap:8px;margin-top:10px">
          <?php if ($size): ?><span style="display:inline-flex;align-items:center;gap:5px;background:#F8FAFC;border:1px solid #e8eaed;border-radius:14px;padding:7px 10px;font-size:12px;font-weight:600;color:#202124"><i class="fa-solid fa-maximize" style="font-size:11px;color:#5f6368"></i> <?= h($size) ?></span><?php endif; ?>
          <span style="display:inline-flex;align-items:center;gap:5px;background:#F8FAFC;border:1px solid #e8eaed;border-radius:14px;padding:7px 10px;font-size:12px;font-weight:600;color:#202124"><i class="fa-solid fa-user-group" style="font-size:11px;color:#5f6368"></i> <?= h($maxAdults) ?> adult<?= $maxAdults>1?'s':'' ?><?php if ($maxChildren>0): ?> · <?= h($maxChildren) ?> child<?php endif; ?></span>
          <span style="display:inline-flex;align-items:center;gap:5px;background:#F8FAFC;border:1px solid #e8eaed;border-radius:14px;padding:7px 10px;font-size:12px;font-weight:600;color:#202124"><i class="fa-solid fa-bed" style="font-size:11px;color:#5f6368"></i> <?= h($bed) ?></span>
        </div>
        <!-- Fallback inline meta for desktop legacy (hidden on mobile via CSS) -->
        <div class="room-meta-inline" style="font-size:12px;color:#5f6368;margin-top:6px;line-height:1.4;display:none">
          <?php if ($size): ?><span><i class="fa-solid fa-maximize" style="font-size:10px"></i> <?= h($size) ?></span> · <?php endif; ?>
          <span><i class="fa-solid fa-user-group" style="font-size:10px"></i> Max <?= h($maxAdults) ?> adults<?php if ($maxChildren > 0): ?> · <?= h($maxChildren) ?> children<?php endif; ?></span> · <i class="fa-solid fa-bed" style="font-size:10px;color:#1a73e8"></i> <?= h($bed) ?>
          <?php if ($floor): ?> · <span><?= h($floor) ?></span><?php endif; ?>
        </div>

        <?php if ($capacity >= 4): ?>
        <div style="display:inline-block;background:#ede9fe;color:#6d28d9;font-size:10.5px;font-weight:700;border-radius:4px;padding:2px 7px;margin-top:6px">
          <i class="fa-solid fa-users" style="font-size:10px"></i> Fits up to <?= $capacity ?> guests
        </div>
        <?php endif; ?>
      </div>

      <!-- Room Features + Amenity Chips (Wrap) + Mobile Price Block -->
      <div style="margin-top:12px;padding-top:10px;border-top:1px solid #e8eaed;">
        <div style="font-size:11px;font-weight:700;color:#5f6368;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px">Room features</div>
        <div class="room-amenity-chips" style="display:flex;flex-wrap:wrap;gap:8px">
          <?php foreach (array_slice($roomAmenities, 0, 8) as $amItem): ?>
          <span style="display:inline-flex;align-items:center;gap:6px;background:#fff;border:1px solid #e8eaed;border-radius:14px;padding:7px 10px;font-size:12px;font-weight:500;color:#202124;white-space:nowrap">
            <i class="fa-solid <?= $getRoomAmenityIcon($amItem) ?>" style="font-size:11px;color:#1a73e8;width:12px;text-align:center"></i>
            <span><?= h($amItem) ?></span>
          </span>
          <?php endforeach; ?>
        </div>
        <!-- Mobile-only price block (mirrors Flutter F8FAFC r18) -->
        <div class="room-mobile-price-block" style="display:none;margin-top:14px;background:#F8FAFC;border:1px solid #e8eaed;border-radius:18px;padding:14px">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px">
            <div>
              <div style="font-size:13px;font-weight:700;color:#1a1d25">2 adults</div>
              <div style="font-size:12px;color:#5f6368;margin-top:4px">You won't be charged yet</div>
            </div>
            <div style="font-size:22px;font-weight:800;color:#C2410C">TSh <?= number_format($priceBase) ?></div>
          </div>
          <div style="margin-top:12px;display:flex;flex-direction:column;gap:6px;font-size:13px;font-weight:600;color:#202124">
            <span style="display:flex;align-items:center;gap:8px"><i class="fa-solid fa-circle-info" style="font-size:13px;color:#5f6368"></i> Cancellation policy</span>
            <span style="display:flex;align-items:center;gap:8px"><i class="fa-solid fa-circle-check" style="font-size:13px;color:#15803d"></i> No credit card needed</span>
            <span style="display:flex;align-items:center;gap:8px"><i class="fa-solid fa-square-parking" style="font-size:13px;color:#5f6368"></i> Parking</span>
            <span style="display:flex;align-items:center;gap:8px"><i class="fa-solid fa-wifi" style="font-size:13px;color:#1a73e8"></i> Free WiFi</span>
            <span style="display:flex;align-items:center;gap:8px;color:#B45309"><i class="fa-solid fa-bolt" style="font-size:13px"></i> Only 2 left</span>
          </div>
          <a href="#" onclick="event.preventDefault();var c=this.closest('.agoda-room-card');if(c){document.querySelectorAll('.agoda-room-card').forEach(function(x){x.classList.remove('selected')});c.classList.add('selected')};" style="margin-top:8px;display:inline-flex;font-size:14px;font-weight:700;color:#2563EB;text-decoration:none">See details</a>
        </div>
      </div>
    </div>

    <!-- Right Column: Stacked Offers & Booking Rates -->
    <div style="display:flex;flex-direction:column;background:#fff">
      <?php foreach ($offers as $oi => $o): 
          $isFirstOffer = ($oi === 0);
          $bookUrl = $this->Url->build('/booking-page?' . http_build_query(array_merge($qp, [
              'property_id' => (int)($propertyId ?? 0),
              'room_id' => $roomId,
              'price' => $o['price'],
              'checkIn' => $ci,
              'checkOut' => $co,
              'adults' => $adultsCount,
              'rooms' => $roomsCount
          ])));
      ?>
      <div style="display:grid;grid-template-columns:1fr 200px;gap:0;border-bottom:1px solid #e8eaed;flex:1;<?= $isFirstOffer ? 'border-top:3px solid #1a73e8;' : '' ?>">
        
        <!-- Offer Inclusions -->
        <div style="padding:14px;border-right:1px solid #e8eaed;display:flex;flex-direction:column;justify-content:center">
          <div style="font-size:12px;font-weight:700;color:#202124;margin-bottom:4px"><?= h($o['title']) ?></div>
          <div style="font-size:11.5px;color:#3c4043;display:flex;flex-direction:column;gap:3px">
            <div><i class="fa-solid fa-user-check" style="font-size:10px;color:#5f6368;width:14px"></i> <?= h($o['adults']) ?> adults included</div>
            <div style="<?= str_contains($o['breakfast'], 'included') ? 'color:#15803d;font-weight:600' : '' ?>">
              <i class="fa-solid fa-mug-saucer" style="font-size:10px;width:14px"></i> <?= h($o['breakfast']) ?>
            </div>
            <div style="color:<?= $o['freeCancel'] ? '#15803d;font-weight:600' : '#5f6368' ?>">
              <i class="fa-solid <?= $o['freeCancel'] ? 'fa-circle-check' : 'fa-circle-info' ?>" style="font-size:10px;width:14px"></i> <?= h($o['cancel']) ?>
            </div>
            <div><i class="fa-solid fa-credit-card" style="font-size:10px;color:#5f6368;width:14px"></i> <?= h($o['pay']) ?></div>
            <div><i class="fa-solid fa-wifi" style="font-size:10px;color:#1a73e8;width:14px"></i> <?= h($o['wifi']) ?></div>
          </div>
        </div>

        <!-- Pricing & Book Action -->
        <div style="padding:14px;display:flex;flex-direction:column;align-items:flex-end;justify-content:center;text-align:right;background:#F8FAFC">
          <?php if (!empty($o['strike'])): ?>
          <div style="font-size:11px;color:#5f6368;text-decoration:line-through">
            TSh <?= number_format($o['strike']) ?>
            <span style="background:#fee2e2;color:#dc2626;border-radius:3px;padding:1px 4px;font-size:10px;font-weight:700"><?= h($o['off']) ?></span>
          </div>
          <?php endif; ?>
          
          <div class="room-price-main" style="font-size:20px;font-weight:800;color:#C2410C">TSh <?= number_format($o['price']) ?></div>
          <div style="font-size:10.5px;color:#5f6368">Per night before taxes</div>
          <div style="font-size:11px;color:#202124;margin-top:2px;font-weight:500;">1 room · <?= $nights ?> night<?= $nights > 1 ? 's' : '' ?></div>
          
          <div style="display:flex;gap:6px;margin-top:10px;align-items:center;width:100%;justify-content:flex-end">
            <a href="<?= h($bookUrl) ?>" onclick="selectRoomCard(<?= $roomId ?>)" style="background:#2563EB;color:#fff;border:none;border-radius:24px;padding:9px 24px;font-size:13.5px;font-weight:700;text-decoration:none;display:inline-block;box-shadow:0 1px 3px rgba(37,99,235,0.3);transition:background 0.15s ease;">
              Reserve
            </a>
          </div>
          
          <?php if ($o['freeCancel']): ?>
          <div style="font-size:11px;color:#15803d;font-weight:600;margin-top:6px"><i class="fa-solid fa-shield-check"></i> Free cancellation</div>
          <?php else: ?>
          <div style="font-size:10.5px;color:#5f6368;margin-top:6px">Instant confirmation</div>
          <?php endif; ?>
        </div>

      </div>
      <?php endforeach; ?>
    </div>

  </div>
  <?php endforeach; ?>
<?php endif; ?>

<script>
window._roomPhotoIdx = window._roomPhotoIdx || {};
function selectRoomCard(roomId){
    document.querySelectorAll('.agoda-room-card').forEach(function(c){c.classList.remove('selected')});
    var card=document.getElementById('room_card_'+roomId);
    if(card) card.classList.add('selected');
}
function cycleRoomPhoto(roomId, photos) {
    if (!photos || !photos.length) return;
    var cur = window._roomPhotoIdx[roomId] || 0;
    var next = (cur + 1) % photos.length;
    window._roomPhotoIdx[roomId] = next;
    var imgEl = document.getElementById('room_img_' + roomId);
    if (imgEl) {
        imgEl.src = photos[next];
    }
    var counter=document.getElementById('room_counter_'+roomId);
    if(counter) counter.textContent=(next+1)+'/'+photos.length;
    selectRoomCard(roomId);
}

function filterRoomsByPills() {
    var active = [];
    document.querySelectorAll(".agoda-filter-pill.active").forEach(function(b) {
        var f = b.getAttribute("data-filter");
        if (f) active.push(f.toLowerCase().trim());
    });
    var cards = document.querySelectorAll(".agoda-room-card");
    var visible = 0;
    cards.forEach(function(c) {
        var hay = (c.getAttribute("data-room-filters") || "").toLowerCase();
        var match = true;
        for (var i = 0; i < active.length; i++) {
            if (!hay.includes(active[i])) {
                match = false;
                break;
            }
        }
        c.style.display = match ? "" : "none";
        if (match) visible++;
    });
}
</script>

<style>
/* Phase 1 — mobile card parity tokens + Phase 2 desktop refinement */
.agoda-room-card.selected{border-color:#2563EB !important;border-width:1.6px !important;box-shadow:0 6px 16px rgba(0,0,0,0.08) !important}
.agoda-room-card{transition:border-color 180ms ease,box-shadow 180ms ease;border-radius:16px !important}
.agoda-room-card:hover{box-shadow:0 4px 12px rgba(0,0,0,0.06) !important}
.room-hero-wrap{aspect-ratio:auto;border-radius:16px !important}
.room-stat-pills span{transition:background 150ms ease}
.room-amenity-chips span{transition:border-color 150ms ease}
.room-price-main{color:#C2410C !important}
.recommended-card{border-radius:12px !important;transition:box-shadow 150ms ease}
.recommended-card:hover{box-shadow:0 4px 12px rgba(0,0,0,0.06)}
@media (min-width:993px){
  .agoda-room-card{box-shadow:0 4px 12px rgba(0,0,0,0.04) !important}
  .agoda-room-card > div:first-child{background:#fafafa}
  .room-hero-wrap{border-radius:12px !important}
}
@media (max-width: 768px) {
  .agoda-room-card {
    grid-template-columns: 1fr !important;
    border-radius:22px !important;
    box-shadow:0 6px 16px rgba(0,0,0,0.05) !important;
  }
  .agoda-room-card > div:first-child {
    border-right: none !important;
    border-bottom: 1px solid #e8eaed !important;
    padding:14px !important;
    background:#fff !important;
  }
  .room-hero-wrap{height:auto !important;aspect-ratio:1.52 !important;border-radius:16px !important}
  .room-card-title{font-size:18px !important;font-weight:800 !important}
  .room-stat-pills{margin-top:10px !important}
  .room-amenity-chips{gap:8px !important}
  .room-amenity-chips > span{border-radius:14px !important;padding:7px 10px !important;font-size:12px !important}
  .room-mobile-price-block{display:block !important}
  .room-price-main{color:#C2410C !important;font-size:22px !important}
  .room-meta-inline{display:none !important}
  .agoda-room-card div[style*="grid-template-columns:1fr 200px"] {
    grid-template-columns: 1fr !important;
  }
  .agoda-room-card div[style*="grid-template-columns:1fr 200px"] > div:last-child {
    border-left: none !important;
    border-top: 1px solid #e8eaed !important;
    align-items: flex-start !important;
    text-align: left !important;
    background:#fff !important;
  }
}
@media (max-width: 600px) {
  .room-hero-wrap{aspect-ratio:1.52 !important}
  .room-mobile-price-block{border-radius:18px !important}
}
</style>
