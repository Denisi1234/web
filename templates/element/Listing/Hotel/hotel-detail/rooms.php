<?php
$roomList = is_array($rooms ?? null) ? $rooms : [];
$renderedRoomSignatures = [];
// compute nights for total price
$qp = $queryParams ?? [];
$ci = $qp['checkIn'] ?? date('Y-m-d', strtotime('+1 day'));
$co = $qp['checkOut'] ?? date('Y-m-d', strtotime('+2 days'));
try { $nights = max(1, (strtotime($co) - strtotime($ci)) / 86400); } catch (Exception $e) { $nights = 1; }
$nights = (int)round($nights);
if ($nights < 1) $nights = 1;
$checkInLabel = date('M j', strtotime($ci));
$checkOutLabel = date('M j', strtotime($co));
$guestsLabel = (int)($qp['adults'] ?? 2) . ' guests' . (!empty($qp['children']) ? ', ' . (int)$qp['children'] . ' children' : '') . ' · ' . (int)($qp['rooms'] ?? 1) . ' room';
?>
<!-- Date & Guest Re-selector Bar — Google Hotels -->
<div class="gh-rooms-bar" style="display:flex;align-items:center;gap:8px;padding:10px 12px;background:#fff;border:1px solid #dadce0;border-radius:12px;margin-bottom:16px;position:sticky;top:108px;z-index:20;box-shadow:0 1px 3px rgba(60,64,67,0.08);">
    <button type="button" class="gh-rooms-date-pill" onclick="document.getElementById('rooms-section')?.scrollIntoView({behavior:'smooth'}); window.scrollBy(0,-120); document.querySelector('.gh-sb-date')?.click();" style="display:flex;align-items:center;gap:8px;background:#fff;border:1px solid #dadce0;border-radius:20px;padding:7px 14px;font-size:13px;font-weight:500;color:#202124;cursor:pointer;font-family:'Google Sans',Roboto,sans-serif;">
        <i class="fa-regular fa-calendar" style="color:#1a73e8;"></i>
        <span><?= h($checkInLabel) ?> → <?= h($checkOutLabel) ?></span>
    </button>
    <button type="button" class="gh-rooms-guest-pill" onclick="document.querySelector('.gh-sb-guests')?.click();" style="display:flex;align-items:center;gap:8px;background:#fff;border:1px solid #dadce0;border-radius:20px;padding:7px 14px;font-size:13px;font-weight:500;color:#202124;cursor:pointer;font-family:'Google Sans',Roboto,sans-serif;">
        <i class="fa-solid fa-user-group" style="color:#1a73e8;font-size:12px;"></i>
        <span><?= h($guestsLabel) ?></span>
        <i class="fa-solid fa-caret-down" style="color:#5f6368;font-size:10px;"></i>
    </button>
    <span style="margin-left:auto;font-size:12px;color:#5f6368;">Prices update when dates change</span>
</div>

<?php if (empty($roomList)): ?>
    <div class="alert alert-light border text-center py-4" role="status" style="border-radius:12px;border-color:#dadce0;">
        <i class="fa-solid fa-bed text-muted mb-2" style="font-size:24px;"></i>
        <div class="fw-semibold text-slate-800" style="font-family:'Google Sans',sans-serif;">Room availability is not currently available</div>
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
    $totalPrice = $price > 0 ? $price * $nights : 0;
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
    $photoCount = is_array($photos) ? count($photos) : 0;
?>

    <article class="gh-room-card">
        <div class="gh-room-photo">
            <?php if ($img !== ''): ?><img src="<?= str_starts_with($img, 'http') ? h($img) : $this->Url->build('/' . h($img)) ?>" alt="<?= h($title) ?>">
            <?php else: ?><div class="gh-room-photo-empty" role="status"><i class="fa-solid fa-image"></i><span>Photo unavailable</span></div><?php endif; ?>
            <?php if ($photoCount > 1): ?><button type="button" class="gh-room-photo-count" onclick="openPhotoLightbox(0)"><i class="fa-solid fa-camera"></i> <?= $photoCount ?> photos</button><?php endif; ?>
        </div>
        <div class="gh-room-main">
            <h3 class="gh-room-title"><?= h($title) ?></h3>
            <div class="gh-room-badges">
                <?php if ($bed !== ''): ?><span><i class="fa-solid fa-bed"></i><?= h($bed) ?></span><?php endif; ?>
                <?php if ($adults !== null): ?><span><i class="fa-solid fa-user-group"></i>Fits <?= h($adults) ?> Adult<?= $adults===1?'':'s' ?><?= $children>0 ? ', '.h($children).' Child'.($children===1?'':'ren'):'' ?></span><?php endif; ?>
                <?php if ($size !== ''): ?><span><i class="fa-solid fa-ruler-combined"></i><?= h($size) ?></span><?php endif; ?>
            </div>
            <?php if (!empty($roomAmenities)): ?><div class="gh-room-amenities">
                <?php foreach (array_slice($roomAmenities,0,4) as $rm): $n=is_array($rm)?($rm['name']??''):$rm; if($n==='') continue; ?>
                    <span><i class="fa-solid fa-circle-check"></i><?= h($n) ?></span>
                <?php endforeach; ?>
            </div><?php endif; ?>
            <?php if (!empty($item['free_cancellation'])): ?><div class="gh-room-cancel"><i class="fa-solid fa-check"></i> Free cancellation until 24h before check-in</div>
            <?php elseif (!empty($item['non_smoking'])): ?><div class="gh-room-cancel" style="background:#f8f9fa;color:#5f6368;border-color:#e8eaed;"><i class="fa-solid fa-ban-smoking"></i> Non-smoking</div><?php endif; ?>
        </div>
        <div class="gh-room-pricing">
            <?php if ($price > 0): ?>
                <div class="gh-room-price">TSH <?= number_format($price) ?><span>/night</span></div>
                <div class="gh-room-taxes">Includes taxes and fees</div>
                <div class="gh-room-total">TSH <?= number_format($totalPrice) ?> total for <?= $nights ?> night<?= $nights===1?'':'s' ?></div>
                <?php if ($roomId !== null): ?><a href="<?= $this->Url->build('/booking-page?' . http_build_query(array_merge($queryParams ?? [], ['property_id' => (int)($propertyId ?? 0), 'room_id' => $roomId, 'price' => $price]))) ?>" class="gh-room-reserve">Reserve</a><?php endif; ?>
            <?php else: ?><span class="text-muted small">Price unavailable</span><?php endif; ?>
        </div>
    </article>
<?php endforeach; ?>

<style>
/* Google Hotels Room Card — 12px rounded, #dadce0, 140px photo */
.gh-rooms-bar { font-family:'Google Sans',Roboto,'Segoe UI',Arial,sans-serif; }
.gh-room-card{display:flex;gap:0;background:#fff;border:1px solid #dadce0;border-radius:12px;overflow:hidden;margin-bottom:14px;box-shadow:0 1px 3px rgba(60,64,67,0.08);transition:box-shadow .15s ease;}
.gh-room-card:hover{box-shadow:0 2px 8px rgba(60,64,67,0.15);}
.gh-room-photo{flex:0 0 140px;position:relative;background:#f8f9fa;overflow:hidden;min-height:140px;}
.gh-room-photo img{width:100%;height:100%;object-fit:cover;display:block;min-height:140px;}
.gh-room-photo-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;min-height:140px;color:#9aa0a6;font-size:12px;gap:6px;}
.gh-room-photo-count{position:absolute;bottom:8px;left:8px;background:rgba(0,0,0,0.65);color:#fff;font-size:11px;font-weight:500;padding:4px 8px;border-radius:12px;border:none;display:flex;align-items:center;gap:4px;cursor:pointer;}
.gh-room-main{flex:1 1 auto;min-width:0;padding:14px 16px;display:flex;flex-direction:column;gap:6px;}
.gh-room-title{font-size:17px;font-weight:500;color:#202124;margin:0;line-height:1.3;font-family:'Google Sans',Roboto,'Segoe UI',Arial,sans-serif;}
.gh-room-badges{display:flex;flex-wrap:wrap;gap:8px 14px;font-size:12px;color:#5f6368;}
.gh-room-badges span{display:inline-flex;align-items:center;gap:5px;}
.gh-room-badges i{color:#5f6368;font-size:11px;}
.gh-room-amenities{display:flex;flex-wrap:wrap;gap:6px 12px;margin-top:4px;}
.gh-room-amenities span{font-size:12px;color:#3c4043;display:inline-flex;align-items:center;gap:5px;}
.gh-room-amenities i{color:#1a73e8;font-size:10px;}
.gh-room-cancel{display:inline-flex;align-items:center;gap:6px;background:#e6f4ea;color:#188038;border:1px solid #a8dab5;font-size:11px;font-weight:500;padding:4px 8px;border-radius:12px;margin-top:6px;width:fit-content;}
.gh-room-pricing{flex:0 0 180px;border-left:1px solid #e8eaed;padding:14px 16px;display:flex;flex-direction:column;align-items:flex-end;justify-content:center;text-align:right;background:#fff;}
.gh-room-price{font-size:20px;font-weight:700;color:#202124;line-height:1;letter-spacing:-0.02em;}
.gh-room-price span{font-size:12px;font-weight:400;color:#5f6368;margin-left:2px;}
.gh-room-taxes{font-size:11px;color:#5f6368;margin-top:2px;}
.gh-room-total{font-size:12px;color:#202124;font-weight:500;margin-top:6px;}
.gh-room-reserve{background:#1a73e8;color:#fff !important;border:none;border-radius:8px;padding:10px 20px;font-size:13px;font-weight:500;height:40px;display:inline-flex;align-items:center;justify-content:center;min-width:110px;margin-top:10px;text-decoration:none !important;transition:background .15s ease;}
.gh-room-reserve:hover{background:#1557d0;color:#fff !important;}
@media(max-width:767px){
  .gh-room-card{flex-direction:column;}
  .gh-room-photo{flex:0 0 auto;height:180px;min-height:180px;}
  .gh-room-pricing{flex:0 0 auto;border-left:none;border-top:1px solid #e8eaed;align-items:stretch;text-align:left;padding:12px 16px;}
  .gh-room-price{font-size:18px;}
  .gh-room-reserve{width:100%;}
  .gh-rooms-bar{position:static;flex-wrap:wrap;}
}
</style>
