<?php
$destinations = [
    [
        'img' => 'assets/img/city/c-8.png', 
        'title' => 'San Jose', 
        'btn' => 'Discover', 
    ],
    [
        'img' => 'assets/img/city/c-7.png', 
        'title' => 'Houston', 
        'btn' => 'Discover', 
    ],
    [
        'img' => 'assets/img/city/c-6.png', 
        'title' => 'San Francisco', 
        'btn' => 'Discover', 
    ],
    [
        'img' => 'assets/img/city/c-4.png', 
        'title' => 'San Diego', 
        'btn' => 'Discover', 
    ],
    [
        'img' => 'assets/img/city/ct-12.png', 
        'title' => 'Los Angeles', 
        'btn' => 'Discover', 
    ],
    [
        'img' => 'assets/img/city/ct-9.png', 
        'title' => 'New Orleans', 
        'btn' => 'Discover', 
    ]
];
?>

<?php foreach ($destinations as $item): ?>
    <div class="carousel-cell">
        <div class="cardCities cursor rounded-2">
            <div class="cardCities-image ratio ratio-4">
                <img src="<?= $this->Url->build('/' . h($item['img'])) ?>" class="img-fluid object-fit" alt="image">
            </div>

            <div class="citiesCard-content d-flex flex-column justify-content-between text-center px-4 py-4">
                <div class="cardCities-bg"></div>

                <div class="citiesCard-topcaps">
                    <div class="d-flex align-items-center justify-content-center flex-wrap">
                        <div class="bg-transparents text-light text-xs rounded fw-medium p-2 m-1">10 Hotels</div>
                        <div class="bg-transparents text-light text-xs rounded fw-medium p-2 m-1">25 Flights</div>
                        <div class="bg-transparents text-light text-xs rounded fw-medium p-2 m-1">17 Cars</div>
                        <div class="bg-transparents text-light text-xs rounded fw-medium p-2 m-1">22 Tours</div>
                        <div class="bg-transparents text-light text-xs rounded fw-medium p-2 m-1">36 Activities</div>
                    </div>
                </div>

                <div class="citiesCard-bottomcaps">
                    <h4 class="text-light fs-3 mb-3"><?= h($item['title']) ?></h4>
                    <button class="btn btn-whitener full-width fw-medium"><?= h($item['btn']) ?><i class="fa-solid fa-arrow-trend-up ms-2"></i></button>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>