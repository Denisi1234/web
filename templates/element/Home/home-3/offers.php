<?php
$offers = [
    [
        'img' => 'assets/img/flt-1.png', 
        'tag' => '20% Discount', 
        'class' => 'danger', 
        'name' => 'DHL', 
        'location' => 'Delhi', 
        'name2' => 'NWK', 
        'location2' => 'New York', 
        'title' => 'Travel Between', 
        'dates' => '20 Sep, Fri - 10 Oct, Tue', 
    ],
    [
        'img' => 'assets/img/flt-2.png', 
        'tag' => 'New Flight', 
        'class' => 'warning', 
        'name' => 'DHL', 
        'location' => 'Delhi', 
        'name2' => 'NWK', 
        'location2' => 'New York', 
        'title' => 'Travel Between', 
        'dates' => '20 Sep, Fri - 10 Oct, Tue', 
    ],
    [
        'img' => 'assets/img/flt-3.png', 
        'tag' => '30% Discount', 
        'class' => 'danger', 
        'name' => 'DHL', 
        'location' => 'Delhi', 
        'name2' => 'NWK', 
        'location2' => 'New York', 
        'title' => 'Travel Between', 
        'dates' => '20 Sep, Fri - 10 Oct, Tue', 
    ],
    [
        'img' => 'assets/img/flt-4.png', 
        'tag' => 'Recommended', 
        'class' => 'success', 
        'name' => 'DHL', 
        'location' => 'Delhi', 
        'name2' => 'NWK', 
        'location2' => 'New York', 
        'title' => 'Travel Between', 
        'dates' => '20 Sep, Fri - 10 Oct, Tue', 
    ],
    [
        'img' => 'assets/img/flt-1.png', 
        'tag' => 'Trending', 
        'class' => 'warning', 
        'name' => 'DHL', 
        'location' => 'Delhi', 
        'name2' => 'NWK', 
        'location2' => 'New York', 
        'title' => 'Travel Between', 
        'dates' => '20 Sep, Fri - 10 Oct, Tue', 
    ],
    [
        'img' => 'assets/img/flt-3.png', 
        'tag' => '20% Discount', 
        'class' => 'danger', 
        'name' => 'DHL', 
        'location' => 'Delhi', 
        'name2' => 'NWK', 
        'location2' => 'New York', 
        'title' => 'Travel Between', 
        'dates' => '20 Sep, Fri - 10 Oct, Tue', 
    ]
];
?>

<?php foreach ($offers as $item): ?>
    <div class="carousel-cell">
        <div class="card rounded-3 border-0 m-0 fltsOffers-card">
            <div class="card-body">
                <div class="d-inline-flex mb-3"><span class="label bg-<?= h($item['class']) ?> text-light"><?= h($item['tag']) ?></span></div>
                <div class="fltsOffers-flex d-flex align-items-center justify-content-between">
                    <div class="fltsOffers-firster">
                        <h6 class="text-dark fw-bold fs-6 m-0"><?= h($item['name']) ?></h6>
                        <p class="text-muted-2 text-md m-0"><?= h($item['location']) ?></p>
                    </div>
                    <div class="fltsOffers-middler text-muted fs-5"><i class="fa-solid fa-jet-fighter"></i></div>
                    <div class="fltsOffers-ender">
                        <h6 class="text-dark fw-bold fs-6 m-0"><?= h($item['name2']) ?></h6>
                        <p class="text-muted-2 text-md m-0"><?= h($item['location2']) ?></p>
                    </div>
                </div>
                <div class="fltsFlite-name d-flex flex-column align-items-center justify-content-center my-3">
                    <p class="text-muted m-0"><?= h($item['title']) ?></p>
                    <h6 class="fw-bold text-dhani"><?= h($item['dates']) ?></h6>
                </div>
                <div class="fltsFlite-name d-flex align-items-center justify-content-center mb-1">
                    <img src="<?= $this->Url->build('/' . h($item['img'])) ?>" class="img-fluid" width="120" alt="">
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>