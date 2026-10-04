<?php
/**
 * fastnetstays.com - Real Room Detail Component
 * Dynamically renders rooms, real amenities, real prices, bed configurations, and capacity from backend.
 */
$roomList = is_array($rooms ?? null) ? $rooms : [];
// Belt & braces: drop any non-room scalars (backend error shapes) — every loop below indexes into room arrays
$roomList = array_values(array_filter($roomList, fn($r) => is_array($r)));

// If no rooms returned, provide clean fallback structure
if (empty($roomList)) {
    $roomList = [];
}

$qp = $queryParams ?? [];
$propertyId = (int)($propertyId ?? ($detailPropertyId ?? ($property['id'] ?? ($qp['property_id'] ?? ($qp['id'] ?? ($roomList[0]['property_id'] ?? 0))))));
$ci = $qp['checkIn'] ?? $qp['check_in'] ?? $qp['checkin'] ?? date('Y-m-d', strtotime('+7 days'));
$co = $qp['checkOut'] ?? $qp['check_out'] ?? $qp['checkout'] ?? date('Y-m-d', strtotime($ci . ' +1 day'));
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
      <i class="fa-solid <?= $getRoomAmenityIcon($pill) ?>" style="font-size:11px;color:#0f62fe;"></i> <?= h($pill) ?>
    </button>
    <?php endforeach; ?>
  </div>
</div>
<style>
.agoda-filter-pill.active{background:#e8f0fe !important;border-color:#0f62fe !important;color:#0f62fe !important;font-weight:600;}
</style>
<?php endif; ?>

<?php if (empty($roomList)): ?>
  <div style="background:#fff;border:1px solid #e8eaed;border-radius:8px;padding:24px;text-align:center;color:#5f6368">
    <i class="fa-solid fa-hotel" style="font-size:32px;color:#9aa0a6;margin-bottom:10px;display:block;"></i>
    <div style="font-size:16px;font-weight:700;color:#202124;margin-bottom:4px;">No rooms available</div>
    <p style="font-size:13px;margin:0;">There are currently no rooms available for this property. Please try selecting different travel dates.</p>
  </div>
<?php else:
  // Category picks: cheapest CATEGORY by from-price, roomiest CATEGORY by max capacity.
  // Groups come pre-sorted cheapest-first per category from the controller.
  $groupPicks = $roomGroups ?? [];
  $cheapestGroup = $groupPicks[0] ?? null;
  $roomiestGroup = null;
  foreach ($groupPicks as $gp) {
      if ($roomiestGroup === null || (int)($gp['maxCapacity'] ?? 0) > (int)($roomiestGroup['maxCapacity'] ?? 0)) {
          $roomiestGroup = $gp;
      }
  }
  if ($roomiestGroup !== null && $cheapestGroup !== null && $roomiestGroup['label'] === $cheapestGroup['label'] && ($roomiestGroup['fromPrice'] ?? 0) === ($cheapestGroup['fromPrice'] ?? 0)) {
      $roomiestGroup = null; // same category wins both — don't duplicate the card
  }
  $groupCover = function(array $gp) use ($normImgUrl): string {
      foreach (($gp['rooms'] ?? []) as $gm) {
          if (!is_array($gm)) continue;
          $ph = $gm['photos'] ?? ($gm['images'] ?? []);
          if (is_string($ph)) $ph = json_decode($ph, true) ?: [];
          if (is_array($ph)) foreach ($ph as $one) {
              $u = $normImgUrl(is_array($one) ? ($one['url'] ?? $one['image_url'] ?? '') : (string)$one);
              if ($u !== '') return $u;
          }
          if (!empty($gm['primary_image_url'])) {
              $u = $normImgUrl((string)$gm['primary_image_url']);
              if ($u !== '') return $u;
          }
      }
      return '';
  };
?>

  <?php if ($cheapestGroup):
    $cPrice = (float)($cheapestGroup['fromPrice'] ?? 0);
    $cName = ($cheapestGroup['label'] ?? 'Standard') . ' Room';
    $cAvail = (int)($cheapestGroup['availableCount'] ?? 0);
    $cImg = $groupCover($cheapestGroup);
  ?>
  <!-- Recommended Summary Cards — Phase 2: mobile chip palette parity -->
   <div style="font-size:14px;font-weight:800;color:#202124;margin:8px 0">Recommended for you</div>
  <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:10px;margin-bottom:12px">
    <div class="recommended-card" style="background:#fff;border:1px solid #e8eaed;border-radius:12px;padding:12px;display:flex;gap:12px;align-items:center">
      <div style="flex:1">
        <span style="background:rgba(232,212,201,0.9);color:#9A4B2F;font-size:10px;font-weight:700;border-radius:6px;padding:3px 7px">Lowest Price</span>
        <div style="font-size:13.5px;font-weight:700;color:#202124;margin-top:6px"><?= h($cName) ?></div>
        <div style="font-size:11.5px;color:#5f6368">From <span style="color:#C2410C;font-weight:800">TSh <?= number_format($cPrice) ?></span>/night · <?= $cAvail ?> of <?= (int)($cheapestGroup['count'] ?? 1) ?> available</div>
        <div style="font-size:11px;color:#137333;font-weight:600;margin-top:2px"><i class="fa-solid fa-check" style="font-size:10px"></i> Best value available</div>
      </div>
      <?php if ($cImg !== ''): ?><img src="<?= h($cImg) ?>" alt="<?= h($cName) ?>" style="width:84px;height:64px;border-radius:8px;object-fit:cover;flex-shrink:0;background:#e5e7eb"><?php endif; ?>
    </div>

    <?php if ($roomiestGroup):
      $mPrice = (float)($roomiestGroup['fromPrice'] ?? 0);
      $mName = ($roomiestGroup['label'] ?? 'Standard') . ' Room';
      $mImg = $groupCover($roomiestGroup);
      $mCap = (int)($roomiestGroup['maxCapacity'] ?? 0);
      $diff = $mPrice - $cPrice;
    ?>
    <div class="recommended-card" style="background:#fff;border:1px solid #e8eaed;border-radius:12px;padding:12px;display:flex;gap:12px;align-items:center">
      <div style="flex:1">
        <span style="background:rgba(231,219,248,0.9);color:#7B3FE4;font-size:10px;font-weight:700;border-radius:6px;padding:3px 7px">Most Spacious</span>
        <div style="font-size:13.5px;font-weight:700;color:#202124;margin-top:6px"><?= h($mName) ?></div>
        <div style="font-size:11.5px;color:#5f6368">From <span style="color:#C2410C;font-weight:800">TSh <?= number_format($mPrice) ?></span>/night<?= $mCap > 0 ? ' · Sleeps ' . $mCap : '' ?></div>
        <div style="font-size:11px;color:#7B3FE4;font-weight:600;margin-top:2px"><i class="fa-solid fa-sparkles" style="font-size:10px"></i> <?= $diff > 0 ? '+TSh ' . number_format($diff) . ' for extra comfort' : 'Spacious retreat' ?></div>
      </div>
      <?php if ($mImg !== ''): ?><img src="<?= h($mImg) ?>" alt="<?= h($mName) ?>" style="width:84px;height:64px;border-radius:8px;object-fit:cover;flex-shrink:0;background:#e5e7eb"><?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <!-- Detailed Room Cards — one card per PHYSICAL ROOM, each with its own
       Reserve button. A category with 2 rooms renders 2 cards, not 1. -->
  <?php
  // Flatten groups (already cheapest-first within each group) into rooms.
  // Per-room availability uses the same rule as the controller: backend
  // is_available wins, otherwise anything not in maintenance counts.
  $maintenanceStatuses = ['maintenance', 'out_of_service', 'inactive', 'disabled'];
  $roomIsAvail = function(array $r) use ($maintenanceStatuses): bool {
      if (array_key_exists('is_available', $r)) return (bool)$r['is_available'];
      return !in_array(strtolower(trim((string)($r['status'] ?? 'available'))), $maintenanceStatuses, true);
  };
  $flatRooms = [];
  if (!empty($roomGroups)) {
      foreach ($roomGroups as $g) {
          foreach (($g['rooms'] ?? []) as $m) {
              if (is_array($m)) $flatRooms[] = $m;
          }
      }
  } else {
      // Fallback: element rendered without controller groups.
      foreach ($roomList as $solo) {
          if (is_array($solo)) $flatRooms[] = $solo;
      }
  }
  ?>
  <?php foreach ($flatRooms as $rIdx => $item):
      if (!is_array($item)) continue;
      $roomId = (int)($item['id'] ?? ($rIdx + 1));
      $typeRaw = trim((string)($item['room_type_id'] ?? ($item['type'] ?? 'Standard')));
      $groupLabel = \App\Utility\TextFormatter::formatTitle($typeRaw !== '' ? $typeRaw : 'Standard');
      $isAvail = $roomIsAvail($item);
      $catAdults = max(1, (int)($item['max_adults'] ?? ($item['capacity'] ?? 2)));
      $catChildren = (int)($item['max_children'] ?? 0);
      $catCapacity = max(1, (int)($item['capacity'] ?? ($catAdults + $catChildren)));

      // Title: "Deluxe Room". Sub: this room's own availability.
      $title = $groupLabel . ' Room';
      $unitRaw = trim((string)($item['room_number'] ?? ''));
      // Show the physical room number on the card so two Deluxe rooms are
      // distinguishable and each books exactly the room shown.
      if ($unitRaw !== '') {
          $title .= ' · ' . (preg_match('/^room\b/i', $unitRaw) ? \App\Utility\TextFormatter::formatTitle($unitRaw) : 'Room ' . \App\Utility\TextFormatter::formatTitle($unitRaw));
      }
      $unitRaw = trim((string)($item['room_number'] ?? ''));
      if ($isAvail) {
          $availText = 'Available for selected dates';
      } else {
          $availText = 'Unavailable for selected dates';
      }
      $rawSize = $item['room_size'] ?? ($item['size'] ?? ($item['area'] ?? ''));
      $size = !empty($rawSize) ? (is_numeric($rawSize) ? $rawSize . ' m²' : (string)$rawSize) : '';
      
      $bed = \App\Utility\TextFormatter::formatTitle(trim((string)($item['bed_configuration'] ?? ($item['beds'] ?? '1 Double Bed'))));
      if ($bed !== '' && !preg_match('/\b(bed|beds)\b/i', $bed)) {
          $bed .= ' Bed';
      }
      $maxAdults = $catAdults;
      $maxChildren = $catChildren;
      $capacity = $catCapacity;
      $floor = !empty($item['floor']) ? \App\Utility\TextFormatter::formatTitle((string)$item['floor']) : '';

      // This room's own photos — no merging across the category.
      $roomPhotos = [];
      $photosRaw = $item['photos'] ?? ($item['images'] ?? []);
      if (is_string($photosRaw)) $photosRaw = json_decode($photosRaw, true) ?: [];
      if (is_array($photosRaw)) {
          foreach ($photosRaw as $p) {
              $u = is_array($p) ? ($p['url'] ?? $p['image_url'] ?? '') : (string)$p;
              $u = $normImgUrl($u);
              if ($u !== '' && !in_array($u, $roomPhotos, true)) $roomPhotos[] = $u;
          }
      }
      if (!empty($item['primary_image_url'])) {
          $u = $normImgUrl((string)$item['primary_image_url']);
          if ($u !== '' && !in_array($u, $roomPhotos, true)) $roomPhotos[] = $u;
      }
      if (empty($roomPhotos) && !empty($property['image_url'])) {
          $u = $normImgUrl((string)$property['image_url']);
          if ($u !== '') $roomPhotos[] = $u;
      }
      // Removed: a stock Unsplash room photo was injected into every room
      // without one, so unphotographed rooms looked real. With no photo the
      // card simply has no image.
      $mainImg = $roomPhotos[0] ?? '';

      // This room's own facilities — no union across the category.
      $amenitiesRaw = $item['amenities'] ?? [];
      // Parse room amenities
      if (is_string($amenitiesRaw)) {
          $dec = json_decode($amenitiesRaw, true);
          $roomAmenities = is_array($dec) ? $dec : array_filter(array_map('trim', explode(',', $amenitiesRaw)));
      } elseif (is_array($amenitiesRaw)) {
          $roomAmenities = $amenitiesRaw;
      } else {
          $roomAmenities = [];
      }
      // Removed: four fabricated amenities ("Air conditioning", "Wi-Fi",
      // "Private bathroom", "Shower") were shown on any room that had none
      // recorded, implying facilities the property may not have.

      // Base price: bookable unit customer rate (fee-inclusive). No invented packages,
      // no strike prices — one honest rate built only from real signals.
      $priceBase = (float)($item['customer_price'] ?? ($item['price'] ?? 0));
      $amenText = strtolower(implode(' ', $roomAmenities) . ' ' . implode(' ', (array)($property['amenities'] ?? [])));
      $hasBreakfast = str_contains($amenText, 'breakfast');
      $hasWifi = str_contains($amenText, 'wi-fi') || str_contains($amenText, 'wifi');
      $cancelPolicy = trim((string)($calculation['cancellation_policy'] ?? ($quote['calculation']['cancellation_policy'] ?? '')));
      // cancellation_policy is now a real (nullable) property field, so test it
      // properly - a bare !== '' comparison is true for null.
      $hasFreeCancel = ($cancelPolicy !== null && trim((string)$cancelPolicy) !== '')
          || !empty($property['free_cancellation'])
          || str_contains(strtolower((string)($property['cancellation_policy'] ?? '')), 'free');

      $unitDisplay = $unitRaw !== ''
          ? (preg_match('/^room\b/i', $unitRaw) ? \App\Utility\TextFormatter::formatTitle($unitRaw) : 'Room ' . \App\Utility\TextFormatter::formatTitle($unitRaw))
          : '';
      $offers = [
          [
              // No invented rate-plan name; use the room's own title.
              'title' => $title ?: 'Room',
              'unit' => $unitDisplay,
              'adults' => min($maxAdults, $adultsCount),
              'breakfast' => $hasBreakfast ? 'Breakfast included' : '',
              // Report the real policy. Never assert "Free cancellation" or
              // "Non-refundable" when the property has not stated a policy.
              'cancel' => $hasFreeCancel ? $cancelPolicy : '',
              'pay' => '',
              'wifi' => $hasWifi ? 'Wi-Fi' : '',
              'price' => $priceBase,
              'strike' => null,
              'off' => null,
              'freeCancel' => $hasFreeCancel,
          ],
      ];

      // Build search token for filter pills (category + bed + amenities; numbers stay internal)
      $filterTokens = strtolower($title . ' ' . $groupLabel . ' ' . $bed . ' ' . $size . ' ' . implode(' ', $roomAmenities));
  ?>
  <div class="agoda-room-card" id="room_card_<?= $roomId ?>" data-room-filters="<?= h($filterTokens) ?>" data-room-id="<?= $roomId ?>" style="background:#fff;border:1px solid #e8eaed;border-radius:16px;overflow:hidden;display:grid;grid-template-columns:260px 1fr;margin-bottom:16px;box-shadow:0 1px 3px rgba(0,0,0,0.04);transition:border-color 180ms ease, box-shadow 180ms ease">
    
    <!-- Left Column: Photo & Room Details -->
    <div style="padding:14px;border-right:1px solid #e8eaed;display:flex;flex-direction:column;justify-content:space-between;background:#fafafa">
      <div>
        <div class="room-hero-wrap" style="position:relative;border-radius:16px;overflow:hidden;height:165px;background:#e5e7eb" data-room-photos="<?= htmlspecialchars(json_encode($roomPhotos), ENT_QUOTES, 'UTF-8') ?>" data-room-id="<?= $roomId ?>">
          <img id="room_img_<?= $roomId ?>" src="<?= h($mainImg) ?>" alt="<?= h($title) ?>" style="width:100%;height:100%;object-fit:cover;object-position:center;display:block;background:#e5e7eb" loading="lazy" onerror="this.onerror=null;this.removeAttribute('src');this.style.background='#e5e7eb';this.alt='No photo available'">
          <div style="position:absolute;top:10px;left:10px;display:flex;flex-wrap:wrap;gap:6px">
            <?php if (!$isAvail): ?><span style="background:#f4f4f4;color:#525252;font-size:11px;font-weight:700;border-radius:6px;padding:5px 8px;">Unavailable for these dates</span><?php endif; ?>
            <?php if ($hasFreeCancel): ?><span style="background:rgba(231,219,248,0.95);color:#7B3FE4;font-size:11px;font-weight:700;border-radius:6px;padding:5px 8px;">Free cancellation</span><?php endif; ?>
          </div>
          <span style="position:absolute;top:10px;right:10px;background:#15803d;color:#fff;font-size:10px;font-weight:700;border-radius:6px;padding:4px 8px;<?= count($roomPhotos) > 1 ? '' : 'display:none' ?>">Available</span>
          <?php if (count($roomPhotos) > 1): ?>
          <span id="room_counter_<?= $roomId ?>" style="position:absolute;bottom:10px;right:10px;background:rgba(0,0,0,0.72);color:#fff;font-size:11px;border-radius:14px;padding:4px 10px;font-weight:700;letter-spacing:.04em;">1/<?= count($roomPhotos) ?></span>
          <button type="button" onclick="cycleRoomPhoto(<?= $roomId ?>, <?= htmlspecialchars(json_encode($roomPhotos), ENT_QUOTES, 'UTF-8') ?>, -1)" aria-label="Previous photo" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);background:rgba(255,255,255,0.94);border:none;border-radius:50%;width:44px;height:44px;display:flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 2px 6px rgba(0,0,0,0.2)"><i class="fa-solid fa-chevron-left" style="font-size:13px;color:#1a1d25"></i></button>
          <button type="button" onclick="cycleRoomPhoto(<?= $roomId ?>, <?= htmlspecialchars(json_encode($roomPhotos), ENT_QUOTES, 'UTF-8') ?>, 1)" aria-label="Next photo" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:rgba(255,255,255,0.94);border:none;border-radius:50%;width:44px;height:44px;display:flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 2px 6px rgba(0,0,0,0.2)"><i class="fa-solid fa-chevron-right" style="font-size:13px;color:#1a1d25"></i></button>
          <?php else: ?>
          <span id="room_counter_<?= $roomId ?>" style="display:none">1/1</span>
          <?php endif; ?>
        </div>

        <div class="room-card-title" style="font-size:18px;font-weight:800;color:#1a1d25;margin-top:12px;line-height:1.3;"><?= h($title) ?><?php if ($size): ?> <span style="font-size:13px;font-weight:500;color:#5f6368;white-space:nowrap;"><?= h($size) ?></span><?php endif; ?></div>
        <div style="font-size:12.5px;font-weight:600;color:<?= $isAvail ? '#137333' : '#6f6f6f' ?>;margin-top:2px"><?= h($availText) ?></div>
        
        <!-- Stat Pills — mobile parity (F8FAFC r14) — desktop also shows pills -->
        <div class="room-stat-pills" style="display:flex;flex-wrap:wrap;gap:8px;margin-top:10px">
          <?php if ($size): ?><span style="display:inline-flex;align-items:center;gap:5px;background:#F8FAFC;border:1px solid #e8eaed;border-radius:14px;padding:7px 10px;font-size:12px;font-weight:600;color:#202124"><i class="fa-solid fa-maximize" style="font-size:11px;color:#5f6368"></i> <?= h($size) ?></span><?php endif; ?>
          <span style="display:inline-flex;align-items:center;gap:5px;background:#F8FAFC;border:1px solid #e8eaed;border-radius:14px;padding:7px 10px;font-size:12px;font-weight:600;color:#202124"><i class="fa-solid fa-user-group" style="font-size:11px;color:#5f6368"></i> <?= h($maxAdults) ?> adult<?= $maxAdults>1?'s':'' ?><?php if ($maxChildren>0): ?> · <?= h($maxChildren) ?> child<?php endif; ?></span>
          <span style="display:inline-flex;align-items:center;gap:5px;background:#F8FAFC;border:1px solid #e8eaed;border-radius:14px;padding:7px 10px;font-size:12px;font-weight:600;color:#202124"><i class="fa-solid fa-bed" style="font-size:11px;color:#5f6368"></i> <?= h($bed) ?></span>
        </div>
        <!-- Fallback inline meta for desktop legacy (hidden on mobile via CSS) -->
        <div class="room-meta-inline" style="font-size:12px;color:#5f6368;margin-top:6px;line-height:1.4;display:none">
          <?php if ($size): ?><span><i class="fa-solid fa-maximize" style="font-size:10px"></i> <?= h($size) ?></span> · <?php endif; ?>
          <span><i class="fa-solid fa-user-group" style="font-size:10px"></i> Max <?= h($maxAdults) ?> adults<?php if ($maxChildren > 0): ?> · <?= h($maxChildren) ?> children<?php endif; ?></span> · <i class="fa-solid fa-bed" style="font-size:10px;color:#0f62fe"></i> <?= h($bed) ?>
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
            <i class="fa-solid <?= $getRoomAmenityIcon($amItem) ?>" style="font-size:11px;color:#0f62fe;width:12px;text-align:center"></i>
            <span><?= h($amItem) ?></span>
          </span>
          <?php endforeach; ?>
        </div>
        <!-- Mobile-only info block (price lives once, in the offers below + sticky bar — no duplicate) -->
        <div class="room-mobile-price-block" style="display:none;margin-top:14px;background:#F8FAFC;border:1px solid #e8eaed;border-radius:18px;padding:14px">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px">
            <div>
              <div style="font-size:13px;font-weight:700;color:#1a1d25"><?= h($adultsCount) ?> adult<?= $adultsCount>1?'s':'' ?> · <?= h($nights) ?> night<?= $nights>1?'s':'' ?></div>
              <div style="font-size:12px;color:#5f6368;margin-top:4px">You won't be charged yet — see rate below</div>
            </div>
          </div>
          <div style="margin-top:12px;display:flex;flex-direction:column;gap:6px;font-size:13px;font-weight:600;color:#202124">
            <span style="display:flex;align-items:center;gap:8px"><i class="fa-solid fa-circle-info" style="font-size:13px;color:#5f6368"></i> <?= h($o['cancel']) ?></span>
            <?php if (!empty($o['wifi'])): ?>
            <span style="display:flex;align-items:center;gap:8px"><i class="fa-solid fa-wifi" style="font-size:13px;color:#0f62fe"></i> <?= h($o['wifi']) ?></span>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column: Stacked Offers & Booking Rates (sole price source on all screens) -->
    <div style="display:flex;flex-direction:column;background:#fff">
      <?php foreach ($offers as $oi => $o): 
          $isFirstOffer = ($oi === 0);
          $bookParams = array_filter(array_merge($qp, [
              'property_id' => $propertyId,
              'room_id' => $roomId,
              'price' => $o['price'],
              'checkIn' => $ci,
              'checkOut' => $co,
              'adults' => $adultsCount,
              'rooms' => $roomsCount
          ]), fn($v) => $v !== null && $v !== '');
          // Remove duplicate snake_case keys if camelCase checkIn/checkOut are set
          unset($bookParams['check_in'], $bookParams['check_out'], $bookParams['checkin'], $bookParams['checkout']);
          // Array query syntax: Url->build encodes separators once, so h()
          // below yields valid &amp; — concatenating a prebuilt query string
          // double-encodes to &amp;amp; and every param lands in amp;room_id.
          $bookUrl = $this->Url->build(['controller' => 'Bookings', 'action' => 'bookingPage', '?' => $bookParams]);
      ?>
      <div style="display:grid;grid-template-columns:1fr 200px;gap:0;border-bottom:1px solid #e8eaed;flex:1;<?= $isFirstOffer ? 'border-top:3px solid #0f62fe;' : '' ?>">
        
        <!-- Offer Inclusions -->
<div style="padding:14px;border-right:1px solid #e8eaed;display:flex;flex-direction:column;justify-content:center">
           <div style="font-size:12px;font-weight:700;color:#202124;margin-bottom:2px"><?= h(!empty($o['unit']) ? $o['unit'] : $o['title']) ?></div>
           <div style="font-size:11.5px;color:#3c4043;display:flex;flex-direction:column;gap:3px">
             <div><i class="fa-solid fa-user-check" style="font-size:10px;color:#5f6368;width:14px"></i> <?= h($o['adults']) ?> adult<?= (int)$o['adults'] !== 1 ? 's' : '' ?> included</div>
            <?php if (!empty($o['breakfast'])): ?>
            <div style="color:#15803d;font-weight:600">
              <i class="fa-solid fa-mug-saucer" style="font-size:10px;width:14px"></i> <?= h($o['breakfast']) ?>
            </div>
            <?php endif; ?>

            <div style="color:<?= $o['freeCancel'] ? '#15803d;font-weight:600' : '#5f6368' ?>">
              <i class="fa-solid <?= $o['freeCancel'] ? 'fa-circle-check' : 'fa-circle-info' ?>" style="font-size:10px;width:14px"></i> <?= h($o['cancel']) ?>
            </div>
            <div><i class="fa-solid fa-credit-card" style="font-size:10px;color:#5f6368;width:14px"></i> <?= h($o['pay']) ?></div>
            <?php if (!empty($o['wifi'])): ?>
            <div><i class="fa-solid fa-wifi" style="font-size:10px;color:#0f62fe;width:14px"></i> <?= h($o['wifi']) ?></div>
            <?php endif; ?>
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
          <div style="font-size:10.5px;color:#5f6368">Per night, incl. fees</div>
          <div style="font-size:11px;color:#202124;margin-top:2px;font-weight:500;">1 room · <?= $nights ?> night<?= $nights > 1 ? 's' : '' ?></div>
          
<div style="display:flex;gap:6px;margin-top:10px;align-items:center;width:100%;justify-content:flex-end">
            <?php if ($isAvail): ?>
            <?php // Url->build above returns separators as &amp; already, so no h() on the href — it would double-encode and params would land in amp;room_id. ?>
            <a href="<?= $bookUrl ?>" onclick="selectRoomCard(<?= $roomId ?>)" style="background:#0f62fe;color:#fff;border:none;border-radius:24px;padding:9px 24px;font-size:13.5px;font-weight:700;text-decoration:none;display:inline-block;box-shadow:0 1px 3px rgba(37,99,235,0.3);transition:background 0.15s ease;">
              Reserve
            </a>
            <?php else: ?>
            <span style="background:#f4f4f4;color:#6f6f6f;border-radius:24px;padding:9px 24px;font-size:13.5px;font-weight:700;display:inline-block;">
              Unavailable
            </span>
            <?php endif; ?>
          </div>
          
          <?php if ($o['freeCancel']): ?>
          <div style="font-size:11px;color:#15803d;font-weight:600;margin-top:6px"><i class="fa-solid fa-shield-check"></i> Free cancellation</div>
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
function cycleRoomPhoto(roomId, photos, dir) {
    if (!photos || !photos.length) return;
    dir = (dir === -1) ? -1 : 1;
    var cur = window._roomPhotoIdx[roomId] || 0;
    var total = photos.length;
    var next = (cur + dir + total) % total;
    window._roomPhotoIdx[roomId] = next;
    var imgEl = document.getElementById('room_img_' + roomId);
    if (imgEl) {
        imgEl.src = photos[next];
    }
    var counter=document.getElementById('room_counter_'+roomId);
    if(counter) counter.textContent=(next+1)+'/'+photos.length;
    selectRoomCard(roomId);
}
// Swipe on room photos (mobile): left = next, right = previous
(function(){
    function photosOf(wrap){
        try { return JSON.parse(wrap.getAttribute('data-photos') || '[]'); } catch(e){ return []; }
    }
    document.addEventListener('touchstart', function(e){
        var wrap = e.target.closest ? e.target.closest('.room-hero-wrap') : null;
        if(!wrap || !wrap.hasAttribute('data-room-id')) return;
        wrap._swipeX = e.touches[0].clientX;
    }, {passive:true});
    document.addEventListener('touchend', function(e){
        var wrap = e.target.closest ? e.target.closest('.room-hero-wrap') : null;
        if(!wrap || wrap._swipeX === undefined) return;
        var dx = e.changedTouches[0].clientX - wrap._swipeX;
        wrap._swipeX = undefined;
        if(Math.abs(dx) < 36) return;
        var id = parseInt(wrap.getAttribute('data-room-id'), 10);
        cycleRoomPhoto(id, photosOf(wrap), dx < 0 ? 1 : -1);
    }, {passive:true});
})();

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
.agoda-room-card.selected{border-color:#0f62fe !important;border-width:1.6px !important;box-shadow:0 6px 16px rgba(0,0,0,0.08) !important}
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
