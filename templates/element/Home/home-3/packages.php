<?php
$packages = [
    [
        'img' => 'assets/img/tours/tour-1.jpg', 
        'title' => 'Amazing Goa Trip Package with Flights', 
        'reviews' => '142 Reviews', 
        'price' => '492', 
    ],
    [
        'img' => 'assets/img/tours/tour-2.jpg', 
        'title' => 'Electrifying Trip to Goa', 
        'reviews' => '142 Reviews', 
        'price' => '569', 
    ],
    [
        'img' => 'assets/img/tours/tour-3.jpg', 
        'title' => 'Thrilling Holiday in Goa', 
        'reviews' => '142 Reviews', 
        'price' => '479', 
    ],
    [
        'img' => 'assets/img/tours/tour-4.jpg', 
        'title' => 'All Inclusive Romantic Goa 6N Holiday', 
        'reviews' => '142 Reviews', 
        'price' => '399', 
    ],
    [
        'img' => 'assets/img/tours/tour-5.jpg', 
        'title' => 'Intimate Weekend Getaway to Goa', 
        'reviews' => '142 Reviews', 
        'price' => '456', 
    ],
    [
        'img' => 'assets/img/tours/tour-6.jpg', 
        'title' => 'Luxurious Honeymoon in Goa', 
        'reviews' => '142 Reviews', 
        'price' => '362', 
    ]
];
?>

<?php foreach ($packages as $item): ?>
    <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
        <div class="pop-touritem">
            <a href="#" class="card rounded-3 border br-dashed m-0">
                <div class="flight-thumb-wrapper p-2 pb-0">
                    <div class="popFlights-item-overHidden rounded-3">
                        <img src="<?= $this->Url->build('/' . h($item['img'])) ?>" class="img-fluid" alt="">
                    </div>
                </div>
                <div class="touritem-middle position-relative p-3">
                    <div class="touritem-flexxer">
                        <div class="tourist-wooks position-relative mb-3">
                            <ul class="activities-flex">
                                <li>
                                    <div class="actv-wrap">
                                        <div class="actv-wrap-ico"><i class="fa-solid fa-jet-fighter"></i></div>
                                        <div class="actv-wrap-caps">3 Flights</div>
                                    </div>
                                </li>
                                <li>
                                    <div class="actv-wrap">
                                        <div class="actv-wrap-ico"><i class="fa-solid fa-building-wheat"></i></div>
                                        <div class="actv-wrap-caps">2 Hotels</div>
                                    </div>
                                </li>
                                <li>
                                    <div class="actv-wrap">
                                        <div class="actv-wrap-ico"><i class="fa-solid fa-person-walking-luggage"></i></div>
                                        <div class="actv-wrap-caps">0 Activity</div>
                                    </div>
                                </li>
                                <li>
                                    <div class="actv-wrap">
                                        <div class="actv-wrap-ico"><i class="fa-solid fa-bus"></i></div>
                                        <div class="actv-wrap-caps">2 Transfers</div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="explot">
                            <h4 class="city fs-title m-0 fw-bold">
                                <span><?= h($item['title']) ?></span>
                            </h4>
                            <div class="rates">
                                <div class="rat-reviews">
                                    <strong><i class="fa-solid fa-star text-warning me-1"></i>4.6</strong><span>(<?= h($item['reviews']) ?>)</span>
                                </div>
                            </div>
                        </div>
                        <div class="touritem-amenties my-4">
                            <ul class="activities-flex">
                                <li>
                                    <div class="actv-wrap">
                                        <div class="actv-wrap-caps text-dark fw-bold fs-6"><span class="text-dhani me-1">2N</span>Amman</div>
                                    </div>
                                </li>
                                <li>
                                    <div class="actv-wrap">
                                        <div class="actv-wrap-caps text-dark fw-bold fs-6"><span class="text-dhani me-1">1N</span>Petra</div>
                                    </div>
                                </li>
                                <li>
                                    <div class="actv-wrap">
                                        <div class="actv-wrap-caps text-dark fw-bold fs-6"><span class="text-dhani me-1">2N</span>Dhaka</div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="booking-wrapes d-flex align-items-start justify-content-start flex-column">
                        <h5 class="fs-5 low-price m-0">$<span class="price text-primary"><?= h($item['price']) ?></span></h5>
                        <div class="text-muted-2 text-sm">For 2 Person</div>
                    </div>
                </div>
            </a>
        </div>
    </div>
<?php endforeach; ?>