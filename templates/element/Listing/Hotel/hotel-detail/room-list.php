  <?php
      // Cheapest available rate across these rooms (real prices only).
      $cheapestPrice = INF;
      foreach ($flatRooms as $fr) {
          if (!is_array($fr) || !$roomIsAvail($fr)) continue;
          $fp = (float)($fr['customer_price'] ?? ($fr['price'] ?? 0));
          if ($fp > 0 && $fp < $cheapestPrice) $cheapestPrice = $fp;
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
      // Show the physical room number so two Deluxe rooms are distinguishable.
      if ($unitRaw !== '') {
          $title .= ' · ' . (preg_match('/^room\b/i', $unitRaw) ? \App\Utility\TextFormatter::formatTitle($unitRaw) : 'Room ' . \App\Utility\TextFormatter::formatTitle($unitRaw));
      }
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

  ?>
  <?php
      // Cheapest AVAILABLE room earns the red strip (computed from real rates).
      $isCheapest = $isAvail && $priceBase > 0 && $priceBase <= $cheapestPrice;
      $hasBreakfastRow = str_contains(strtolower(implode(' ', $roomAmenities)), 'breakfast');
      $shownAmenities = array_slice($roomAmenities, 0, 10);
      $hiddenAmenities = array_slice($roomAmenities, 10);
  ?>
  <div class="agoda-room-card" id="room_card_<?= $roomId ?>" data-room-id="<?= $roomId ?>" style="background:#fff;border:1px solid #e8eaed;border-radius:22px;overflow:hidden;display:grid;grid-template-columns:300px 1fr 230px;margin-bottom:16px;box-shadow:0 6px 16px rgba(0,0,0,0.05);transition:border-color 180ms ease, box-shadow 180ms ease">
    <?php if ($isCheapest): ?>
    <div style="grid-column:1/-1;background:#C2410C;color:#fff;font-size:13px;font-weight:700;padding:8px 18px">Lowest price available!</div>
    <?php endif; ?>
    
    <!-- Left Column: Photo (full-bleed on desktop) -->
    <div class="room-photo-col" style="padding:14px;border-right:1px solid #e8eaed;background:#fafafa">
      <div>
        <div class="room-hero-wrap" style="position:relative;border-radius:16px;overflow:hidden;height:165px;background:#e5e7eb" data-room-photos="<?= htmlspecialchars(json_encode($roomPhotos), ENT_QUOTES, 'UTF-8') ?>" data-room-id="<?= $roomId ?>">
          <img id="room_img_<?= $roomId ?>" src="<?= h($mainImg) ?>" alt="<?= h($title) ?>" style="width:100%;height:100%;object-fit:cover;object-position:center;display:block;background:#e5e7eb" loading="lazy" onerror="this.onerror=null;this.removeAttribute('src');this.style.background='#e5e7eb';this.alt='No photo available'">
          <div style="position:absolute;top:10px;left:10px;display:flex;flex-wrap:wrap;gap:6px">
            <?php if (!$isAvail): ?><span style="background:#f4f4f4;color:#525252;font-size:11px;font-weight:700;border-radius:6px;padding:5px 8px;">Unavailable for these dates</span><?php endif; ?>
          </div>
          <?php if (count($roomPhotos) > 1): ?>
          <span id="room_counter_<?= $roomId ?>" style="position:absolute;bottom:10px;right:10px;background:rgba(0,0,0,0.72);color:#fff;font-size:11px;border-radius:14px;padding:4px 10px;font-weight:700;letter-spacing:.04em;">1/<?= count($roomPhotos) ?></span>
          <button type="button" onclick="cycleRoomPhoto(<?= $roomId ?>, <?= htmlspecialchars(json_encode($roomPhotos), ENT_QUOTES, 'UTF-8') ?>, -1)" aria-label="Previous photo" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);background:rgba(255,255,255,0.94);border:none;border-radius:50%;width:44px;height:44px;display:flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 2px 6px rgba(0,0,0,0.2)"><i class="fa-solid fa-chevron-left" style="font-size:13px;color:#1a1d25"></i></button>
          <button type="button" onclick="cycleRoomPhoto(<?= $roomId ?>, <?= htmlspecialchars(json_encode($roomPhotos), ENT_QUOTES, 'UTF-8') ?>, 1)" aria-label="Next photo" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:rgba(255,255,255,0.94);border:none;border-radius:50%;width:44px;height:44px;display:flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 2px 6px rgba(0,0,0,0.2)"><i class="fa-solid fa-chevron-right" style="font-size:13px;color:#1a1d25"></i></button>
          <?php else: ?>
          <span id="room_counter_<?= $roomId ?>" style="display:none">1/1</span>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Middle Column: Title, specs, amenities, inclusions -->
    <div class="room-details-col" style="padding:18px;display:flex;flex-direction:column;">
      <div class="room-card-title" style="font-size:20px;font-weight:800;color:#1a1d25;line-height:1.3;"><?= h($title) ?></div>
      <div style="font-size:13px;font-weight:500;color:#5f6368;margin-top:4px;"><?= h(implode(' | ', array_filter([$size !== '' ? $size : null, 'Max ' . $maxAdults . ' adult' . ($maxAdults > 1 ? 's' : ''), $bed]))) ?></div>
      <div style="font-size:12.5px;font-weight:600;color:<?= $isAvail ? '#137333' : '#6f6f6f' ?>;margin-top:4px"><?= h($availText) ?></div>
      <?php if ($capacity >= 4): ?>
      <div style="margin-top:8px"><span style="display:inline-block;background:#7B3FE4;color:#fff;font-size:11px;font-weight:700;border-radius:6px;padding:5px 10px;">Fits groups</span></div>
      <?php endif; ?>
      <div class="room-amenity-list" style="display:grid;grid-template-columns:1fr 1fr;gap:6px 12px;margin-top:12px;">
        <?php foreach ($shownAmenities as $amItem): ?>
        <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:#3c4043;min-width:0"><i class="fa-solid <?= $getRoomAmenityIcon($amItem) ?>" style="font-size:13px;color:#5f6368;width:16px;text-align:center;flex:none"></i><span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= h($amItem) ?></span></div>
        <?php endforeach; ?>
      </div>
      <?php if (!empty($hiddenAmenities)): ?>
      <div id="room_more_<?= $roomId ?>" style="display:none;display:grid;grid-template-columns:1fr 1fr;gap:6px 12px;margin-top:6px;">
        <?php foreach ($hiddenAmenities as $amItem): ?>
        <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:#3c4043;min-width:0"><i class="fa-solid <?= $getRoomAmenityIcon($amItem) ?>" style="font-size:13px;color:#5f6368;width:16px;text-align:center;flex:none"></i><span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= h($amItem) ?></span></div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
      <div style="font-size:13px;color:#3c4043;margin-top:12px;display:flex;flex-direction:column;gap:4px">
        <div><i class="fa-solid fa-user-check" style="font-size:11px;color:#5f6368;width:16px"></i> <?= (int)min($maxAdults, $adultsCount) ?> adult<?= (int)min($maxAdults, $adultsCount) !== 1 ? 's' : '' ?> included</div>
        <?php if ($hasBreakfastRow): ?>
        <div style="color:#15803d;font-weight:600"><i class="fa-solid fa-mug-saucer" style="font-size:11px;width:16px"></i> Breakfast included</div>
        <?php endif; ?>
      </div>
      <?php if (!empty($hiddenAmenities)): ?>
      <a href="javascript:void(0)" onclick="(function(){var m=document.getElementById('room_more_<?= $roomId ?>');var open=m.style.display!=='none';m.style.display=open?'none':'grid';this.textContent=open?'See details':'Show less';}).call(this)" style="font-size:13.5px;font-weight:700;color:#0f62fe;text-decoration:none;margin-top:8px;display:inline-block">See details</a>
      <?php endif; ?>
    </div>
      <?php
          $bookParams = array_filter(array_merge($qp, [
              'property_id' => $propertyId,
              'room_id' => $roomId,
              'price' => $priceBase,
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
      <div class="room-offer-row" style="border-bottom:1px solid #e8eaed;flex:1;border-top:3px solid #0f62fe;">

        <!-- Pricing & Book Action -->
        <div class="room-offer-price" style="padding:18px;display:flex;flex-direction:column;align-items:flex-end;justify-content:center;text-align:right;background:#F8FAFC">
          <div style="font-size:13px;font-weight:700;color:#1a1d25"><?= h($adultsCount) ?> adult<?= $adultsCount>1?'s':'' ?></div>
          <div class="room-price-main" style="font-size:22px;font-weight:800;color:#C2410C;margin-top:4px">TSh <?= number_format($priceBase) ?></div>
          <div style="font-size:10.5px;color:#5f6368">Per night, incl. fees</div>
          <div style="font-size:11px;color:#202124;margin-top:2px;font-weight:500;"><?= (int)$roomsCount ?> room<?= (int)$roomsCount>1?'s':'' ?> · <?= $nights ?> night<?= $nights > 1 ? 's' : '' ?></div>
          <div style="display:flex;gap:10px;margin-top:12px;align-items:center;width:100%">
            <button type="button" onclick="toggleWishlist(<?= (int)$propertyId ?>,this)" aria-label="Save room" style="flex:none;width:48px;height:48px;border-radius:50%;background:#fff;border:1px solid #dadce0;display:inline-flex;align-items:center;justify-content:center;cursor:pointer"><i class="fa-regular fa-heart" style="font-size:18px;color:#1a1d25"></i></button>
            <?php if ($isAvail): ?>
            <?php // Url->build above returns separators as &amp; already, so no h() on the href — it would double-encode and params would land in amp;room_id. ?>
            <a href="<?= $bookUrl ?>" data-room-book="<?= $roomId ?>" onclick="selectRoomCard(<?= $roomId ?>)" style="flex:1;background:#2563EB;color:#fff;border:none;border-radius:24px;padding:12px 16px;text-align:center;text-decoration:none;box-shadow:0 1px 3px rgba(37,99,235,0.3);transition:background 0.15s ease;">
              <span style="display:block;font-size:16px;font-weight:800;line-height:1.2">Book</span>
              <span style="display:block;font-size:11px;font-weight:500;opacity:.9">You won't be charged yet</span>
            </a>
            <?php else: ?>
            <span style="flex:1;background:#f4f4f4;color:#6f6f6f;border-radius:24px;padding:12px 16px;text-align:center;font-size:14px;font-weight:700;display:inline-block;">
              Unavailable
            </span>
            <?php endif; ?>
          </div>
        </div>

      </div>

  </div>
  <?php endforeach; ?>

