<?php
$destinationItems = !empty($properties) ? $properties : [];
?>

<?php if (empty($destinationItems)): ?>
    <div class="col-xl-12 col-lg-12 col-md-12 mb-3">
        <div class="text-center py-5 bg-white rounded-3 shadow-sm border p-4">
            <div class="fs-1 mb-2">🏨</div>
            <h5 class="fw-bold">No properties listed in <?= h($selectedDest ?? 'this location') ?> yet</h5>
            <p class="text-muted text-sm">Check back later or search another location.</p>
        </div>
    </div>
<?php else: ?>
    <?php foreach ($destinationItems as $item): 
        $propId = $item['id'];
        $title = $item['name'] ?? 'FastNet Stays Lodge';
        $city = $item['city'] ?? ($item['area'] ?? ($selectedDest ?? 'Tanzania'));
        $price = number_format((float)($item['price_per_night'] ?? ($item['starting_price'] ?? 150000)));
        
        // Image parsing
        $img = 'assets/img/destination/tr-3.jpg';
        if (!empty($item['images'])) {
            $imagesList = is_string($item['images']) ? json_decode($item['images'], true) : $item['images'];
            if (!empty($imagesList) && is_array($imagesList)) {
                $img = $imagesList[0];
            }
        } elseif (!empty($item['image_url'])) {
            $img = $item['image_url'];
        }
    ?>
        <div class="col-xl-12 col-lg-12 col-md-12 mb-3">
            <div class="card list-layout-block rounded-3 p-3 shadow-sm border border-slate-200">
                <div class="row">

                    <div class="col-xl-4 col-lg-3 col-md-4">
                        <div class="cardImage__caps rounded-2 overflow-hidden h-100" style="max-height: 180px;">
                            <img class="img-fluid h-100 w-100 object-fit-cover" src="<?= (str_starts_with($img, 'http')) ? h($img) : $this->Url->build('/' . h($img)) ?>" alt="<?= h($title) ?>">
                        </div>
                    </div>

                    <div class="col-xl col-lg col-md-5">
                        <div class="listLayout_midCaps mt-md-0 mt-3 mb-md-0 mb-3">
                            <div class="d-flex align-items-center justify-content-start mb-1">
                                <span class="label bg-light-success text-success fw-bold"><?= h($city) ?></span>
                            </div>
                            <h4 class="fs-5 fw-bold mb-1"><a href="<?= $this->Url->build('/hotel-detail/' . $propId); ?>" class="text-dark"><?= h($title) ?></a></h4>
                            <p class="text-muted-2 text-md mb-2"><i class="fa-solid fa-location-dot me-1"></i><?= h($item['address'] ?? ($city . ', Tanzania')) ?></p>
                            <div class="detail ellipsis-container mt-2">
                                <span class="ellipsis">WiFi</span>
                                <span class="ellipsis">Air Con</span>
                                <span class="ellipsis">Free Parking</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-auto col-lg-auto col-md-3 text-start text-md-left d-flex align-items-start align-items-md-end flex-column">
                        <div class="row align-items-center justify-content-start justify-content-md-end gx-2 mb-3">
                            <div class="col-auto text-start text-md-end">
                                <div class="text-md text-dark fw-medium">Exceptional</div>
                                <div class="text-md text-muted-2">Verified Rating</div>
                            </div>
                            <div class="col-auto">
                                <div class="square--40 rounded-2 bg-ratting text-light">4.8</div>
                            </div>
                        </div>

                        <div class="position-relative mt-auto full-width">
                            <div class="d-flex align-items-center justify-content-start justify-content-md-end mb-1">
                                <span class="label bg-light-danger text-danger">Special Offer</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-start justify-content-md-end">
                                <div class="text-dark fw-bold fs-3 text-orange-600">TZS <?= h($price) ?></div>
                            </div>
                            <div class="d-flex align-items-start align-items-md-end text-start text-md-end flex-column mt-2">
                                <a href="<?= $this->Url->build('/hotel-detail/' . $propId); ?>" class="btn btn-md bg-[#ea580c] hover:bg-[#c2410c] text-white rounded-full fw-bold px-lg-4 shadow-sm transition">See Availability<i class="fa-solid fa-arrow-trend-up ms-2"></i></a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>