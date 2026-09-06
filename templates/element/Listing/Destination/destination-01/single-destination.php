<?php
$featuredDest = !empty($destinations[0]) ? $destinations[0] : [
    'id' => 1,
    'image_url' => 'assets/img/destination/tr-1.jpg',
    'city' => 'Zanzibar',
    'name' => 'Nungwi Beach Luxury Resort',
    'starting_price' => 250000
];
$title = $featuredDest['name'] ?? 'Luxury Resort';
$city = $featuredDest['city'] ?? 'Zanzibar';
$price = number_format((float)($featuredDest['starting_price'] ?? 250000));
$propId = $featuredDest['id'] ?? 1;
$img = !empty($featuredDest['image_url']) ? $featuredDest['image_url'] : 'assets/img/destination/tr-1.jpg';
?>
<!-- Single List -->
<div class="col-xl-12 col-lg-12 col-md-12 mb-3">
    <div class="card list-layout-block rounded-3 p-3 shadow-sm border border-slate-200">
        <div class="row">

            <div class="col-xl-4 col-lg-3 col-md">
                <div class="cardImage__caps rounded-2 overflow-hidden h-100" style="max-height: 180px;">
                    <img class="img-fluid h-100 w-100 object-fit-cover" src="<?= (str_starts_with($img, 'http')) ? h($img) : $this->Url->build('/' . h($img)) ?>" alt="<?= h($title) ?>">
                </div>
            </div>

            <div class="col-xl col-lg col-md">
                <div class="listLayout_midCaps mt-md-0 mt-3 mb-md-0 mb-3">
                    <div class="d-flex align-items-center justify-content-start mb-1">
                        <span class="label bg-light-success text-success fw-bold"><?= h($city) ?></span>
                    </div>
                    <h4 class="fs-5 fw-bold mb-1"><a href="<?= $this->Url->build('/hotel-detail/' . $propId); ?>" class="text-dark"><?= h($title) ?></a></h4>
                    <p class="text-muted-2 text-md mb-2"><i class="fa-solid fa-location-dot me-1"></i><?= h($city) ?>, Tanzania</p>
                    <div class="detail ellipsis-container mt-2">
                        <span class="ellipsis">WiFi</span>
                        <span class="ellipsis">Air Con</span>
                        <span class="ellipsis">Ocean View</span>
                    </div>
                </div>
            </div>

            <div class="col-xl-auto col-lg-auto col-md-auto text-right text-md-left d-flex align-items-start align-items-md-end flex-column">
                <div class="row align-items-center justify-content-start justify-content-md-end gx-2 mb-3">
                    <div class="col-auto text-start text-md-end">
                        <div class="text-md text-dark fw-medium">Exceptional</div>
                        <div class="text-md text-muted-2">Verified Rating</div>
                    </div>
                    <div class="col-auto">
                        <div class="square--40 rounded-2 bg-ratting text-light">4.9</div>
                    </div>
                </div>

                <div class="position-relative mt-auto full-width">
                    <div class="d-flex align-items-center justify-content-start justify-content-md-end mb-1">
                        <span class="label bg-light-danger text-danger">Featured</span>
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

        </div>
    </div>
</div>
<!-- /Single List -->