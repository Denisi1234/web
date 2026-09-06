<?php
$similars = [
    [
        'img' => 'assets/img/hotel/hotel-8.jpg', 
        'title' => 'Value Hotel Balestier', 
        'location' => 'Delhi', 
        'from' => '3.5 Km From Delhi', 
        'tag' => '15% Off', 
        'price' => 'US$59', 
    ],
    [
        'img' => 'assets/img/hotel/hotel-5.jpg', 
        'title' => 'Mercure Singapore Tyrwhitt', 
        'location' => 'Delhi', 
        'from' => '3.5 Km From Delhi', 
        'tag' => '15% Off', 
        'price' => 'US$59', 
    ],
    [
        'img' => 'assets/img/hotel/hotel-4.jpg', 
        'title' => 'Hotel Calmo Chinatown', 
        'location' => 'Delhi', 
        'from' => '3.5 Km From Delhi', 
        'tag' => '15% Off', 
        'price' => 'US$59', 
    ],
    [
        'img' => 'assets/img/hotel/hotel-3.jpg', 
        'title' => 'Royal Plaza on Scotts', 
        'location' => 'Delhi', 
        'from' => '3.5 Km From Delhi', 
        'tag' => '15% Off', 
        'price' => 'US$59', 
    ],
    [
        'img' => 'assets/img/hotel/hotel-2.jpg', 
        'title' => 'Dorsett Singapore', 
        'location' => 'Delhi', 
        'from' => '3.5 Km From Delhi', 
        'tag' => '15% Off', 
        'price' => 'US$59', 
    ]
];
?>

<?php foreach ($similars as $item): ?>
    <div class="carousel-cell">
        <div class="pop-touritem">
            <a href="#" class="card rounded-3 border br-dashed m-0">
                <div class="flight-thumb-wrapper">
                    <div class="popFlights-item-overHidden">
                        <img src="<?= $this->Url->build('/' . h($item['img'])) ?>" class="img-fluid" alt="">
                    </div>
                </div>
                <div class="touritem-middle position-relative p-3">
                    <div class="touritem-flexxer">
                        <h4 class="city fs-6 m-0 fw-bold">
                            <span><?= h($item['title']) ?></span>
                        </h4>
                        <p class="detail ellipsis-container">
                            <span class="ellipsis-item__normal"><?= h($item['location']) ?></span>
                            <span class="separate ellipsis-item__normal"></span>
                            <span class="ellipsis-item"><?= h($item['from']) ?></span>
                        </p>

                        <div class="touritem-centrio mt-4">
                            <div class="d-block position-relative"><span class="label bg-light-success text-success">Free Cancellation Till 10 Aug 26</span></div>
                            <div class="aments-lists mt-2">
                                <ul class="p-0 row gx-3 gy-2 align-items-start flex-wrap">
                                    <li class="col-auto text-dark text-md text-muted-2 d-inline-flex align-items-center"><i class="fa-solid fa-check text-success me-1"></i>Cooling</li>
                                    <li class="col-auto text-dark text-md text-muted-2 d-inline-flex align-items-center"><i class="fa-solid fa-check text-success me-1"></i>Pet Allow</li>
                                    <li class="col-auto text-dark text-md text-muted-2 d-inline-flex align-items-center"><i class="fa-solid fa-check text-success me-1"></i>Free WiFi</li>
                                    <li class="col-auto text-dark text-md text-muted-2 d-inline-flex align-items-center"><i class="fa-solid fa-check text-success me-1"></i>Food</li>
                                    <li class="col-auto text-dark text-md text-muted-2 d-inline-flex align-items-center"><i class="fa-solid fa-check text-success me-1"></i>Parking</li>
                                    <li class="col-auto text-dark text-md text-muted-2 d-inline-flex align-items-center"><i class="fa-solid fa-check text-success me-1"></i>Spa & Massage</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="trsms-foots mt-4">
                        <div class="flts-flex d-flex align-items-end justify-content-between">
                            <div class="flts-flex-strat">
                                <div class="d-flex align-items-center justify-content-start">
                                    <span class="label bg-offer text-light"><?= h($item['tag']) ?></span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="text-dark fw-bold fs-4"><?= h($item['price']) ?></div>
                                    <div class="text-muted-2 fw-medium text-decoration-line-through ms-2">US$79</div>
                                </div>
                                <div class="d-flex align-items-start flex-column">
                                    <div class="text-muted-2 text-sm">Per Night</div>
                                </div>
                            </div>

                            <div class="flts-flex-end">
                                <div class="row align-items-center justify-content-end gx-2">
                                    <div class="col-auto text-start text-md-end">
                                        <div class="text-md text-dark fw-medium">Exceptional</div>
                                        <div class="text-md text-muted-2">3,014 reviews</div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="square--40 rounded-2 bg-ratting text-light">4.8</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
<?php endforeach; ?>