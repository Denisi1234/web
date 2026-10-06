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
// Localhost-only sample rooms for design review. NEVER renders in
// production: hostname must be local (production serves fastnetstays.com).
// Real backend rows always win — samples appear only when empty.
$__showSamples = false;
if (empty($roomList) && empty($roomGroups ?? null)) {
    try {
        $__host = strtolower((string)$this->getRequest()->getUri()->getHost());
    } catch (\Throwable $e) {
        $__host = '';
    }
    if (in_array($__host, ['localhost', '127.0.0.1', '::1'], true)) {
        $__showSamples = true;
        $__sampleImg = $this->Url->build('/assets/img/hotel/hotel-1.jpg');
        // Available deluxe, available suite, booked standard — covers every card state.
        $roomList = [
            ['id' => 9001, 'room_number' => '101', 'room_type_id' => 'Deluxe', 'max_adults' => 2, 'max_children' => 1, 'capacity' => 3,
                'customer_price' => 150000, 'price' => 150000, 'bed_configuration' => '1 King Bed', 'room_size' => '32 m²', 'floor' => '1st Floor',
                'status' => 'available', 'is_available' => true, 'amenities' => ['Wifi', 'Air conditioning', 'TV', 'Balcony', 'Hot shower', 'Breakfast'], 'photos' => [$__sampleImg]],
            ['id' => 9002, 'room_number' => '201', 'room_type_id' => 'Executive Suite', 'max_adults' => 4, 'max_children' => 2, 'capacity' => 6,
                'customer_price' => 320000, 'price' => 320000, 'bed_configuration' => '2 Queen Beds', 'room_size' => '58 m²', 'floor' => '2nd Floor',
                'status' => 'available', 'is_available' => true, 'amenities' => ['Wifi', 'Mini bar', 'Coffee', 'Work desk', 'Safe'], 'photos' => [$__sampleImg]],
            ['id' => 9003, 'room_number' => '102', 'room_type_id' => 'Standard', 'max_adults' => 2, 'max_children' => 0, 'capacity' => 2,
                'customer_price' => 95000, 'price' => 95000, 'bed_configuration' => '1 Double Bed', 'room_size' => '24 m²', 'floor' => '1st Floor',
                'status' => 'booked', 'is_available' => false, 'amenities' => ['Wifi', 'Fan'], 'photos' => [$__sampleImg]],
        ];
    }
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
?>

<?php if (empty($roomList)): ?>
  <div style="background:#fff;border:1px solid #e8eaed;border-radius:8px;padding:24px;text-align:center;color:#5f6368">
    <i class="fa-solid fa-hotel" style="font-size:32px;color:#9aa0a6;margin-bottom:10px;display:block;"></i>
    <div style="font-size:16px;font-weight:700;color:#202124;margin-bottom:4px;">No rooms available</div>
    <p style="font-size:13px;margin:0;">There are currently no rooms available for this property. Please try selecting different travel dates.</p>
  </div>
<?php else: ?>

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
  <?php if (!empty($__showSamples)): ?>
  <div style="background:#fffbeb;border:1px dashed #f59e0b;border-radius:12px;padding:10px 14px;font-size:12.5px;font-weight:600;color:#92400e;margin-bottom:12px">Sample rooms — localhost preview only, never shown in production.</div>
  <?php endif; ?>
  <?php // Real urgency count: booked rooms for these dates (never invented).
      $soldOutCount = 0;
      foreach ($flatRooms as $fr) { if (is_array($fr) && !$roomIsAvail($fr)) $soldOutCount++; }
      if ($soldOutCount > 0): ?>
  <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:12px;padding:10px 14px;font-size:13px;font-weight:600;color:#991b1b;margin-bottom:12px"><i class="fa-solid fa-clock" style="margin-right:6px"></i>Hurry up! <?= $soldOutCount ?> room<?= $soldOutCount > 1 ? 's' : '' ?> already booked for your dates!</div>
  <?php endif; ?>

  <?= $this->element('Listing/Hotel/hotel-detail/room-list', [
    'flatRooms' => $flatRooms, 'property' => $property ?? null, 'propertyId' => $propertyId ?? 0,
    'qp' => $qp ?? [], 'ci' => $ci ?? '', 'co' => $co ?? '', 'nights' => $nights ?? 1,
    'adultsCount' => $adultsCount ?? 2, 'roomsCount' => $roomsCount ?? 1,
    'getRoomAmenityIcon' => $getRoomAmenityIcon,
    'normImgUrl' => $normImgUrl ?? null, 'roomIsAvail' => $roomIsAvail,
  ]) ?>
<?php endif; ?>
<?= $this->Html->script('/assets/js/room-cards.js?v=' . filemtime(WWW_ROOT . 'assets/js/room-cards.js')) ?>

<style>
/* Phase 1 — mobile card parity tokens + Phase 2 desktop refinement */
.agoda-room-card.selected{border-color:#2563EB !important;border-width:1.6px !important;box-shadow:0 6px 16px rgba(0,0,0,0.08) !important}
.agoda-room-card{transition:border-color 180ms ease,box-shadow 180ms ease;border-radius:22px !important}
.agoda-room-card:hover{box-shadow:0 4px 12px rgba(0,0,0,0.06) !important}
.room-hero-wrap{aspect-ratio:auto;border-radius:16px !important}
.room-stat-pills span{transition:background 150ms ease}
.room-amenity-chips span{transition:border-color 150ms ease}
.room-price-main{color:#C2410C !important}
@media (min-width:993px){
  .agoda-room-card{box-shadow:0 4px 12px rgba(0,0,0,0.04) !important}
  .agoda-room-card > div:first-child{background:#fafafa}
  /* Desktop: full-bleed photo column + roomier type */
  .room-photo-col{padding:0 !important}
  .room-photo-col .room-hero-wrap{border-radius:0 !important;height:100% !important;min-height:280px !important}
  .room-card-title{font-size:20px !important}
  .room-price-main{font-size:24px !important}
  .room-details-col{padding:20px 20px 0 !important}
  .room-offer-price{padding:18px 20px !important}
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
