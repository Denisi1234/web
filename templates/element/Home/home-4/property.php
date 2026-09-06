<?php
$propertys = [
    [
        'img' => 'assets/img/property/img-1.jpg', 
        'name' => 'House', 
        'title' => 'Equitable Property Group', 
        'price' => 'US$492', 
    ],
    [
        'img' => 'assets/img/property/img-2.jpg', 
        'name' => 'Villa', 
        'title' => 'Apogee Property Advisors', 
        'price' => 'US$492', 
    ],
    [
        'img' => 'assets/img/property/img-3.jpg', 
        'name' => 'Apartment', 
        'title' => 'Landmark Realty Group', 
        'price' => 'US$492', 
    ],
    [
        'img' => 'assets/img/property/img-4.jpg', 
        'name' => 'Condos', 
        'title' => 'Cobblestone Realty Partners', 
        'price' => 'US$492', 
    ],
    [
        'img' => 'assets/img/property/img-5.jpg', 
        'name' => 'Building', 
        'title' => 'Magnolia Group Real Estate', 
        'price' => 'US$492', 
    ],
    [
        'img' => 'assets/img/property/img-6.jpg', 
        'name' => 'Apartment', 
        'title' => 'Haven Group Real Estate', 
        'price' => 'US$492', 
    ]
];
?>

<?php foreach ($propertys as $item): ?>
    <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
        <div class="pop-touritem">
            <a href="#" class="card rounded-3 border m-0">
                <div class="flight-thumb-wrapper">
                    <div class="popFlights-item-overHidden">
                        <img src="<?= $this->Url->build('/' . h($item['img'])) ?>" class="img-fluid" alt="">
                    </div>
                </div>
                <div class="touritem-middle position-relative p-3">
                    <div class="touritem-flexxer">
                        <div class="d-flex align-items-start justify-content-start flex-column">
                            <span class="city-destination label text-success bg-light-success mb-1"><?= h($item['name']) ?></span>
                            <h4 class="city fs-title m-0 fw-bold">
                                <span><?= h($item['title']) ?></span>
                            </h4>
                        </div>
                        <div class="detail ellipsis-container mt-3">
                            <span class="ellipsis">3 Beds</span>
                            <span class="ellipsis">2 Baths</span>
                            <span class="ellipsis">2100 sqft</span>
                            <span class="ellipsis">1 Store</span>
                        </div>
                    </div>
                    <div class="flight-footer">
                        <div class="epocsic">
                            <span class="label d-inline-flex bg-light-danger text-danger mb-1">15% Off</span>
                            <h5 class="fs-5 low-price m-0"><span class="tag-span">From</span> <span class="price"><?= h($item['price']) ?></span></h5>
                        </div>
                        <div class="rates">
                            <div class="star-rates">
                                <i class="fa-solid fa-star active"></i><i class="fa-solid fa-star active"></i><i class="fa-solid fa-star active"></i><i class="fa-solid fa-star active"></i><i class="fa-solid fa-star active"></i>
                            </div>
                            <div class="rat-reviews">
                                <strong>4.6</strong><span>(142 Reviews)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
<?php endforeach; ?>