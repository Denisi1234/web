<?php
$destinations = [
    [
        'img' => 'assets/img/city/c-8.png', 
        'location' => 'New York', 
        'title' => '10 Destinations', 
        'hotels' => '5 Hotels', 
    ],
    [
        'img' => 'assets/img/city/c-7.png', 
        'location' => 'Las Vegas', 
        'title' => '06 Destinations', 
        'hotels' => '14 Hotels', 
    ],
    [
        'img' => 'assets/img/city/c-1.png', 
        'location' => 'San Antonio', 
        'title' => '09 Destinations', 
        'hotels' => '16 Hotels', 
    ],
    [
        'img' => 'assets/img/city/c-2.png', 
        'location' => 'Houston', 
        'title' => '07 Destinations', 
        'hotels' => '18 Hotels', 
    ],
    [
        'img' => 'assets/img/city/c-3.png', 
        'location' => 'San Francisco', 
        'title' => '4 Destinations', 
        'hotels' => '12 Hotels', 
    ],
    [
        'img' => 'assets/img/city/c-4.png', 
        'location' => 'Nashville', 
        'title' => '16 Destinations', 
        'hotels' => '7 Hotels', 
    ],
    [
        'img' => 'assets/img/city/c-5.png', 
        'location' => 'Philadelphia', 
        'title' => '14 Destinations', 
        'hotels' => '10 Hotels', 
    ],
    [
        'img' => 'assets/img/city/c-6.png', 
        'location' => 'San Diego', 
        'title' => '12 Destinations', 
        'hotels' => '32 Hotels', 
    ]
];
?>

<?php foreach ($destinations as $item): ?>
    <div class="col-xl-3 col-lg-4 col-md-4 col-sm-6">
        <div class="destination-blocks bg-white p-2 rounded border br-dashed h-100">
            <div class="destination-blocks-pics p-1">
                <a href="javascript:void(0);"><img src="<?= $this->Url->build('/' . h($item['img'])) ?>" class="img-fluid rounded" alt=""></a>
            </div>
            <div class="destination-blocks-captions">
                <div class="touritem-flexxer text-center p-3">
                    <h4 class="city fs-5 m-0 fw-bold">
                        <span><?= h($item['location']) ?></span>
                    </h4>
                    <p class="detail ellipsis-container m-0">
                        <span class="ellipsis-item__normal"><?= h($item['title']) ?></span>
                        <span class="separate ellipsis-item__normal"></span>
                        <span class="ellipsis-item"><?= h($item['hotels']) ?></span>
                    </p>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>