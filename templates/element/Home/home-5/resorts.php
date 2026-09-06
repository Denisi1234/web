<?php
$resortItems = !empty($featuredResorts) ? $featuredResorts : [];
if (empty($resortItems) && isset($properties) && !empty($properties)) {
    $resortItems = $properties;
}

function resolvePropertyImage($item) {
    if (!empty($item['image_url'])) return $item['image_url'];
    if (!empty($item['primary_image_url'])) return $item['primary_image_url'];
    if (!empty($item['cover_image'])) return $item['cover_image'];
    if (!empty($item['featured_image'])) return $item['featured_image'];
    
    if (!empty($item['rooms']) && is_array($item['rooms'])) {
        foreach ($item['rooms'] as $r) {
            if (!empty($r['primary_image_url'])) return $r['primary_image_url'];
            if (!empty($r['photos'])) {
                $photos = is_string($r['photos']) ? json_decode($r['photos'], true) : $r['photos'];
                if (is_array($photos) && !empty($photos[0])) return $photos[0];
            }
        }
    }
    return '';
}
?>

<?php if (empty($resortItems)): ?>
    <div class="w-100 text-center py-4 text-muted">
        <p class="mb-0">No stays currently available.</p>
    </div>
<?php else: ?>
    <?php foreach ($resortItems as $item): 
        $title = $item['name'] ?? '';
        $city = $item['city'] ?? ($item['area'] ?? '');
        $area = $item['area'] ?? $city;
        $price = (float)($item['starting_price'] ?? ($item['price_per_night'] ?? ($item['price'] ?? 0)));
        $propId = $item['id'] ?? 0;
        $img = resolvePropertyImage($item);
        $rating = !empty($item['rating']) ? number_format((float)$item['rating'], 1) : null;
        $stars = !empty($item['star_rating']) ? max(1, min(5, (int)$item['star_rating'])) : 0;
    ?>
        <div class="resorts-carousel-card">
            <div class="pop-touritem h-100">
                <a href="<?= $this->Url->build('/hotel-detail/' . $propId); ?>" class="card rounded-3 border m-0 h-100 shadow-sm text-decoration-none hover:shadow-lg transition">
                    <div class="flight-thumb-wrapper position-relative">
                        <div class="popFlights-item-overHidden" style="height: 200px;">
                            <?php if ($img !== ''): ?><img src="<?= (str_starts_with($img, 'http')) ? h($img) : $this->Url->build('/' . h($img)) ?>" class="img-fluid w-100 h-100 object-fit-cover" alt="<?= h($title) ?>"><?php else: ?><div class="home-property-image-empty" role="status">Photo unavailable</div><?php endif; ?>
                        </div>
                        <button type="button" class="btn btn-wishlist-toggle position-absolute top-0 end-0 m-2.5 square--35 circle bg-white/90 shadow-sm border-0 d-flex align-items-center justify-content-center z-10" data-property-id="<?= $propId ?>" onclick="event.preventDefault(); event.stopPropagation(); toggleWishlist(<?= $propId ?>, this);" title="Save to Wishlist">
                            <i class="fa-regular fa-heart text-danger fs-6"></i>
                        </button>
                    </div>
                    <div class="touritem-middle position-relative p-3 d-flex flex-column justify-content-between">
                        <div class="touritem-flexxer">
                            <div class="d-flex align-items-center mb-1">
                                <?php if ($stars > 0): ?><div class="d-inline-block text-warning text-xs"><?= str_repeat('<i class="fa fa-star"></i>', $stars) ?></div><?php endif; ?>
                            </div>
                            <h4 class="city fs-title m-0 fw-bold text-dark text-truncate">
                                <span><?= h($title) ?></span>
                            </h4>
                            <p class="detail ellipsis-container text-muted mt-1 text-xs mb-2">
                                <span class="ellipsis-item__normal text-primary font-bold"><i class="fa-solid fa-location-dot me-1"></i><?= h($area) ?></span>
                                <span class="separate ellipsis-item__normal">,</span>
                                <span class="ellipsis-item text-muted"><?= h($city) ?></span>
                            </p>

                            <?php if (!empty($item['instant_confirmation'])): ?><div class="touritem-centrio"><div class="d-block position-relative"><span class="label bg-light-success text-success text-xs">Instant Confirmation</span></div></div><?php endif; ?>
                        </div>
                        <div class="trsms-foots mt-3 pt-2 border-top">
                            <div class="flts-flex d-flex align-items-end justify-content-between">
                                <div class="flts-flex-strat">
                                    <?php if ($price > 0): ?>
                                        <div class="d-flex align-items-center justify-content-start mb-0.5">
                                            <span class="label bg-offer text-light text-xs">Best Price</span>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <div class="text-dark fw-bold fs-6 text-orange-600">TZS <?= number_format($price) ?></div>
                                        </div>
                                        <div class="text-muted-2 text-xs">Per Night</div>
                                    <?php endif; ?>
                                </div>

                                <?php if ($rating !== null): ?><div class="flts-flex-end">
                                    <div class="row align-items-center justify-content-end gx-1">
                                        <div class="col-auto text-end">
                                            <div class="text-xs text-dark fw-bold">Superb</div>
                                            <div class="text-xs text-muted"><?= h($rating) ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <div class="square--32 rounded bg-ratting text-light fw-bold text-xs d-flex align-items-center justify-content-center"><?= h($rating) ?></div>
                                        </div>
                                    </div>
                                </div><?php endif; ?>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
