<?php
$attractions = [
    [
        'img' => 'assets/img/attr/attr-7.jpg', 
        'title' => 'Kansas City', 
        'reviews' => '260 Things To Do', 
    ],
    [
        'img' => 'assets/img/attr/attr-5.jpg', 
        'title' => 'Los Angeles', 
        'reviews' => '240 Things To Do', 
    ],
    [
        'img' => 'assets/img/attr/attr-6.jpg', 
        'title' => 'San Antonio', 
        'reviews' => '312 Things To Do', 
    ],
    [
        'img' => 'assets/img/attr/attr-8.jpg', 
        'title' => 'San Francisco', 
        'reviews' => '220 Things To Do', 
    ],
    [
        'img' => 'assets/img/attr/attr-9.jpg', 
        'title' => 'Nashville', 
        'reviews' => '180 Things To Do', 
    ],
    [
        'img' => 'assets/img/attr/attr-10.jpg', 
        'title' => 'Philadelphia', 
        'reviews' => '260 Things To Do', 
    ],
    [
        'img' => 'assets/img/tours/tour-11.jpg', 
        'title' => 'San Jose', 
        'reviews' => '145 Things To Do', 
    ],
    [
        'img' => 'assets/img/tours/tour-10.jpg', 
        'title' => 'San Diego', 
        'reviews' => '310 Things To Do', 
    ]
];
?>

<?php foreach ($attractions as $item): ?>
    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
        <div class="pop-touritem">
            <a href="#" class="card rounded-3 border br-dashed m-0">
                <div class="flight-thumb-wrapper p-2 pb-0">
                    <div class="popFlights-item-overHidden rounded-3">
                        <img src="<?= $this->Url->build('/' . h($item['img'])) ?>" class="img-fluid" alt="">
                    </div>
                </div>
                <div class="touritem-middle position-relative p-3">
                    <div class="touritem-flexxer">
                        <div class="explot">
                            <h4 class="city fs-6 m-0 fw-bold">
                                <span><?= h($item['title']) ?></span>
                            </h4>
                            <div class="rates">
                                <div class="rat-reviews">
                                    <span><?= h($item['reviews']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
<?php endforeach; ?>