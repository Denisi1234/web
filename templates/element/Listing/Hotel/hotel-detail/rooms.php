<?php
$roomList = is_array($rooms ?? null) ? $rooms : [];
$renderedRoomSignatures = [];
?>

<?php if (empty($roomList)): ?>
    <div class="alert alert-light border text-center py-4" role="status">
        <i class="fa-solid fa-bed text-muted mb-2"></i>
        <div class="fw-semibold text-slate-800">Room availability is not currently available</div>
        <div class="text-muted small">Please change your dates or contact support before booking.</div>
    </div>
<?php endif; ?>

<?php foreach ($roomList as $item):
    $roomSignature = implode('|', [
        (string)($item['room_type_id'] ?? ($item['name'] ?? ($item['title'] ?? ''))),
        (string)($item['room_number'] ?? ''),
        (string)($item['bed_configuration'] ?? ''),
        (string)($item['price'] ?? ($item['customer_price'] ?? '')),
    ]);
    if (isset($renderedRoomSignatures[$roomSignature])) continue;
    $renderedRoomSignatures[$roomSignature] = true;

    $roomType = trim((string)($item['room_type_id'] ?? ''));
    $roomNumber = trim((string)($item['room_number'] ?? ''));
    $title = $item['name'] ?? ($item['title'] ?? '');
    if ($title === '' && $roomType !== '') $title = $roomType . ' Room';
    $title = $title !== '' ? $title : 'Room';
    if ($roomNumber !== '' && stripos($title, $roomNumber) === false) $title .= ' (' . $roomNumber . ')';

    $bed = trim((string)($item['bed_configuration'] ?? ''));
    $adults = isset($item['max_adults']) && is_numeric($item['max_adults']) ? (int)$item['max_adults'] : null;
    $children = isset($item['max_children']) && is_numeric($item['max_children']) ? (int)$item['max_children'] : null;
    $size = !empty($item['room_size']) ? trim((string)$item['room_size']) . ' sqm' : '';
    $price = (float)($item['customer_price'] ?? ($item['price'] ?? 0));
    $roomId = !empty($item['id']) ? (int)$item['id'] : null;

    $roomAmenities = $item['amenities'] ?? [];
    if (is_string($roomAmenities)) {
        $decodedRoomAmenities = json_decode($roomAmenities, true);
        $roomAmenities = is_array($decodedRoomAmenities) ? $decodedRoomAmenities : array_filter(array_map('trim', explode(',', $roomAmenities)));
    }
    $roomAmenities = is_array($roomAmenities) ? array_values(array_filter($roomAmenities)) : [];

    $img = '';
    $photos = $item['photos'] ?? ($item['images'] ?? []);
    if (is_string($photos)) $photos = json_decode($photos, true);
    if (is_array($photos) && !empty($photos[0])) {
        $firstPhoto = $photos[0];
        $img = is_array($firstPhoto) ? ($firstPhoto['url'] ?? ($firstPhoto['image_url'] ?? '')) : $firstPhoto;
    }
    if ($img === '') $img = (string)($item['primary_image_url'] ?? '');
?>
    <article class="fn-room-option">
        <header class="fn-room-option-header"><h3><?= h($title) ?></h3></header>
        <div class="fn-room-option-body">
            <div class="fn-room-option-photo">
                <?php if ($img !== ''): ?><img src="<?= str_starts_with($img, 'http') ? h($img) : $this->Url->build('/' . h($img)) ?>" alt="<?= h($title) ?>">
                <?php else: ?><div class="roomavls-image-empty" role="status">Photo unavailable</div><?php endif; ?>
            </div>
            <div class="fn-room-option-details">
                <div class="fn-room-facts">
                    <?php if ($bed !== ''): ?><span><i class="fa-solid fa-bed"></i><?= h($bed) ?></span><?php endif; ?>
                    <?php if ($adults !== null): ?><span><i class="fa-solid fa-user-group"></i><?= h($adults) ?> adult<?= $adults === 1 ? '' : 's' ?><?= $children > 0 ? ', ' . h($children) . ' child' . ($children === 1 ? '' : 'ren') : '' ?></span><?php endif; ?>
                    <?php if ($size !== ''): ?><span><i class="fa-solid fa-vector-square"></i><?= h($size) ?></span><?php endif; ?>
                </div>
                <?php if (!empty($roomAmenities)): ?><ul class="fn-room-amenities">
                    <?php foreach ($roomAmenities as $roomAmenity): $amenityName = is_array($roomAmenity) ? ($roomAmenity['name'] ?? '') : $roomAmenity; if ($amenityName === '') continue; ?>
                        <li><i class="fa-solid fa-circle-check"></i><?= h($amenityName) ?></li>
                    <?php endforeach; ?>
                </ul><?php endif; ?>
                <?php if (!empty($item['instant_confirmation']) || !empty($item['free_cancellation']) || !empty($item['non_smoking'])): ?><div class="fn-room-policies">
                    <?php if (!empty($item['instant_confirmation'])): ?><span><i class="fa-solid fa-circle-check"></i>Instant confirmation</span><?php endif; ?>
                    <?php if (!empty($item['free_cancellation'])): ?><span><i class="fa-solid fa-shield"></i>Free cancellation</span><?php endif; ?>
                    <?php if (!empty($item['non_smoking'])): ?><span><i class="fa-solid fa-ban-smoking"></i>Non-smoking</span><?php endif; ?>
                </div><?php endif; ?>
            </div>
            <div class="fn-room-option-rate">
                <?php if ($price > 0): ?>
                    <span class="fn-room-rate-label">Per night</span><strong>TZS <?= number_format($price) ?></strong><small>Taxes included</small>
                    <?php if ($roomId !== null): ?><a href="<?= $this->Url->build('/booking-page?' . http_build_query(array_merge($queryParams ?? [], ['property_id' => (int)($propertyId ?? 0), 'room_id' => $roomId, 'price' => $price]))) ?>" class="fn-room-book-button">Book this room <i class="fa-solid fa-arrow-right"></i></a><?php endif; ?>
                <?php else: ?><span class="text-muted small">Price unavailable</span><?php endif; ?>
            </div>
        </div>
    </article>
<?php endforeach; ?>
