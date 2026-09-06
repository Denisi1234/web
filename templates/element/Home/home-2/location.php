<?php
$locations = [
    [
        'img' => 'assets/img/city/ct-7.png', 
        'title' => 'New York', 
        'hotels' => '10 hotels', 
        'rental' => '5 Rental', 
    ],
    [
        'img' => 'assets/img/city/ct-2.png', 
        'title' => 'Los Angeles', 
        'hotels' => '12 hotels', 
        'rental' => '4 Rental', 
    ],
    [
        'img' => 'assets/img/city/ct-3.png', 
        'title' => 'San Diego', 
        'hotels' => '08 hotels', 
        'rental' => '6 Rental', 
    ],
    [
        'img' => 'assets/img/city/ct-4.png', 
        'title' => 'San Francisco', 
        'hotels' => '32 hotels', 
        'rental' => '12 Rental', 
    ],
    [
        'img' => 'assets/img/city/ct-5.png', 
        'title' => 'Houston', 
        'hotels' => '22 hotels', 
        'rental' => '16 Rental', 
    ],
    [
        'img' => 'assets/img/city/ct-6.png', 
        'title' => 'San Jose', 
        'hotels' => '25 hotels', 
        'rental' => '15 Rental', 
    ],
    [
        'img' => 'assets/img/city/ct-1.png', 
        'title' => 'Denver', 
        'hotels' => '29 hotels', 
        'rental' => '14 Rental', 
    ],
    [
        'img' => 'assets/img/city/ct-10.png', 
        'title' => 'California', 
        'hotels' => '22 hotels', 
        'rental' => '12 Rental', 
    ]
];
?>

<?php foreach ($locations as $item): ?>
    <div class="col-xl-3 col-lg-3 col-md-4 col-sm-6">
        <div class="card destination-card border-0 rounded-3 overflow-hidden m-0">
            <div class="destination-card-wraps position-relative">
                <div class="destination-card-thumbs">
                    <div class="destinations-pics"><a href="#"><img src="<?= $this->Url->build('/' . h($item['img'])) ?>" class="img-fluid" alt=""></a></div>
                </div>
                <div class="destination-card-description position-absolute start-0 bottom-0 ps-4 pb-4 z-2">
                    <div class="exploterr-text">
                        <h3 class="text-light fw-bold mb-1"><?= h($item['title']) ?></h3>
                        <p class="detail ellipsis-container text-light">
                            <span class="ellipsis-item__normal"><?= h($item['hotels']) ?></span>
                            <span class="separate ellipsis-item__normal"></span>
                            <span class="ellipsis-item"><?= h($item['rental']) ?></span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>