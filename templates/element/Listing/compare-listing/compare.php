<?php
$compares = [
    [
        'img' => 'assets/img/hotel/hotel-1.jpg', 
        'title' => 'Roojika Apartments', 
        'price' => '$50,999', 
        'style' => '', 
    ],
    [
        'img' => 'assets/img/hotel/hotel-2.jpg', 
        'title' => 'Green Susmita Estae', 
        'price' => '$67,500', 
        'style' => '', 
    ],
    [
        'img' => 'assets/img/hotel/hotel-3.jpg', 
        'title' => 'Avada Real Setate', 
        'price' => '$90,780', 
        'style' => 'pricing--emphasise', 
    ],
    [
        'img' => 'assets/img/hotel/hotel-4.jpg', 
        'title' => 'Sujata Real Estate', 
        'price' => '$87,599', 
        'style' => '', 
    ]
];
?>

<?php foreach ($compares as $item): ?>
    <div class="col-lg-3 col-md-4 col-sm-12 text-center <?= h($item['style']) ?>">
        <div class="comp-property">
            <a href="JavaScript:Void(0);">
                <div class="clp-img">
                    <img src="<?= $this->Url->build('/' . h($item['img'])) ?>" class="img-fluid rounded" alt="">
                    <span class="remove-from-compare"><i class="fa-solid fa-xmark"></i></span>
                </div>

                <div class="clp-title">
                    <h4><?= h($item['title']) ?></h4>
                    <span><?= h($item['price']) ?></span>
                </div>
            </a>
            <ul>
                <li>
                    1750 sq ft
                    <span class="show-mb"></span>
                </li>
                <li>
                    3
                    <span class="show-mb">Rooms</span>
                </li>
                <li>
                    2
                    <span class="show-mb">Bedrooms</span>
                </li>
                <li>
                    2
                    <span class="show-mb">Bathrooms</span>
                </li>
                <li>
                    <div class="checkmark"></div>
                    <span class="show-mb">Air Conditioning</span>
                </li>
                <li>
                    <div class="crossmark"></div>
                    <span class="show-mb"> No Swimming Pool</span>
                </li>
                <li>
                    <div class="checkmark"></div>
                    <span class="show-mb">Laundry Room</span>
                </li>
                <li>
                    <div class="crossmark"></div>
                    <span class="show-mb">No Window Covering</span>
                </li>
                <li class="inactive">
                    1 - 5 Year
                    <span class="show-mb">Age</span>
                </li>
                <li class="inactive">
                    <div class="checkmark"></div>
                    <span class="show-mb">Alarm</span>
                </li>
                <li class="inactive">
                    Forced Air
                    <span class="show-mb"></span>
                </li>
                <li class="inactive">
                    <div class="checkmark"></div>
                    <span class="show-mb">Parking</span>
                </li>
            </ul>
        </div>
    </div>
<?php endforeach; ?>