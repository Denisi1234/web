<?php
$offs = [
    [
        'img' => 'assets/img/city/ct-6.png', 
        'tag' => '20% Off', 
        'title' => 'Los Angeles', 
        'person' => '3 Person', 
        'price' => '$849 - $999', 
    ],
    [
        'img' => 'assets/img/city/ct-5.png', 
        'tag' => '15% Off', 
        'title' => 'United Kingdom', 
        'person' => '2 Person', 
        'price' => '$399 - $599', 
    ],
    [
        'img' => 'assets/img/city/ct-1.png', 
        'tag' => '20% Off', 
        'title' => 'France', 
        'person' => '3 Person', 
        'price' => '$569 - $799', 
    ]
];
?>

<?php foreach ($offs as $item): ?>
    <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
        <div class="pop-touritems">
            <a href="#" class="card rounded-3 border br-dashed border-2 m-0">
                <div class="offers-container d-flex align-items-center justify-content-start p-2">
                    <div class="offers-flex position-relative">
                        <div class="offer-tags position-absolute start-0 top-0 mt-2 ms-2"><span class="label text-light bg-danger fw-medium"><?= h($item['tag']) ?></span></div>
                        <div class="offers-pic"><img src="<?= $this->Url->build('/' . h($item['img'])) ?>" class="img-fluid rounded" width="110" alt=""> </div>
                    </div>
                    <div class="offers-captions ps-3">
                        <h4 class="city fs-6 m-0 fw-bold">
                            <span><?= h($item['title']) ?></span>
                        </h4>
                        <p class="detail ellipsis-container">
                            <span class="ellipsis-item__normal">Round-trip</span>
                            <span class="separate ellipsis-item__normal"></span>
                            <span class="ellipsis-item">3D/4N</span>
                            <span class="separate ellipsis-item__normal"></span>
                            <span class="ellipsis-item"><?= h($item['person']) ?></span>
                        </p>
                        <div class="booking-wrapes d-flex align-items-center justify-content-between">
                            <h5 class="fs-5 low-price m-0"><span class="tag-span">From</span> <span class="price"><?= h($item['price']) ?></span></h5>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
<?php endforeach; ?>