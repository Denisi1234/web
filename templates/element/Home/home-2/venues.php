<?php
$venues = [
    [
        'img' => 'assets/img/attr/attr-1.jpg', 
        'title' => 'Long Beach', 
        'rates' => '4.8', 
        'desc' => 'Cicero famously orated against his political opponent Lucius Sergius Catilina.', 
        'price' => '$492 - $799', 
        'rooms' => '120 Rooms', 
    ],
    [
        'img' => 'assets/img/attr/attr-2.jpg', 
        'title' => 'Jacksonville', 
        'rates' => '4.7', 
        'desc' => 'Cicero famously orated against his political opponent Lucius Sergius Catilina.', 
        'price' => '$492 - $799', 
        'rooms' => '78 Rooms', 
    ],
    [
        'img' => 'assets/img/attr/attr-3.jpg', 
        'title' => 'Kansas City', 
        'rates' => '4.9', 
        'desc' => 'Cicero famously orated against his political opponent Lucius Sergius Catilina.', 
        'price' => '$492 - $799', 
        'rooms' => '65 Rooms', 
    ],
    [
        'img' => 'assets/img/attr/attr-4.jpg', 
        'title' => 'Los Angeles', 
        'rates' => '4.6', 
        'desc' => 'Cicero famously orated against his political opponent Lucius Sergius Catilina.', 
        'price' => '$492 - $799', 
        'rooms' => '23 Rooms', 
    ],
    [
        'img' => 'assets/img/attr/attr-5.jpg', 
        'title' => 'San Antonio', 
        'rates' => '4.7', 
        'desc' => 'Cicero famously orated against his political opponent Lucius Sergius Catilina.', 
        'price' => '$492 - $799', 
        'rooms' => '45 Rooms', 
    ],
    [
        'img' => 'assets/img/attr/attr-6.jpg', 
        'title' => 'Philadelphia', 
        'rates' => '4.8', 
        'desc' => 'Cicero famously orated against his political opponent Lucius Sergius Catilina.', 
        'price' => '$492 - $799', 
        'rooms' => '35 Rooms', 
    ],
    [
        'img' => 'assets/img/attr/attr-8.jpg', 
        'title' => 'San Jose', 
        'rates' => '4.9', 
        'desc' => 'Cicero famously orated against his political opponent Lucius Sergius Catilina.', 
        'price' => '$492 - $799', 
        'rooms' => '48 Rooms', 
    ],
    [
        'img' => 'assets/img/attr/attr-7.jpg', 
        'title' => 'San Diego', 
        'rates' => '4.7', 
        'desc' => 'Cicero famously orated against his political opponent Lucius Sergius Catilina.', 
        'price' => '$492 - $799', 
        'rooms' => '85 Rooms', 
    ]
];
?>

<?php foreach ($venues as $item): ?>
    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
        <div class="pop-touritem">
            <div class="card rounded-3 border m-0">
                <div class="flight-thumb-wrapper">
                    <div class="popFlights-item-overHidden">
                        <img src="<?= $this->Url->build('/' . h($item['img'])) ?>" class="img-fluid" alt="">
                    </div>
                </div>
                <div class="touritem-middle position-relative p-3">
                    <div class="touritem-flexxer">
                        <div class="d-flex align-items-center justify-content-between">
                            <h4 class="city fs-6 m-0 fw-bold">
                                <span><?= h($item['title']) ?></span>
                            </h4>
                            <span class="city-rates"><i class="fa-solid fa-star text-warning me-1"></i><span class="fw-bold text-dark"><?= h($item['rates']) ?></span></span>
                        </div>
                        <div class="detail ellipsis-container mt-2">
                            <p class="m-0 text-md"><?= h($item['desc']) ?></p>
                        </div>
                        <div class="flight-footer">
                            <h5 class="fs-5 low-price m-0"><span class="tag-span">From</span> <span class="price"><?= h($item['price']) ?></span></h5>
                            <div class="rates">
                                <div class="rat-reviews">
                                    <span><?= h($item['rooms']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="booking-wrapes d-flex align-items-center mt-3">
                        <button class="btn btn-md btn-light-primary fw-medium rounded full-width me-2">Request Book<i class="fa-solid fa-arrow-trend-up ms-2"></i></button>
                        <button class="btn btn-md btn-light-success fs-5 px-3 rounded ms-1"><i class="fa-solid fa-heart"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>