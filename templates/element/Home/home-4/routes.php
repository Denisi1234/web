<?php
$routes = [
    [
        'img' => 'assets/img/destination/tr-1.jpg', 
        'name' => 'New York', 
        'name2' => 'Los Angeles', 
        'title' => 'Round-trip', 
        'days' => '3 days', 
        'price' => 'US$492', 
    ],
    [
        'img' => 'assets/img/destination/tr-2.jpg', 
        'name' => 'San Diego', 
        'name2' => 'San Jose', 
        'title' => 'Round-trip', 
        'days' => '3 days', 
        'price' => 'US$492', 
    ],
    [
        'img' => 'assets/img/destination/tr-3.jpg', 
        'name' => 'Dallas', 
        'name2' => 'Philadelphia', 
        'title' => 'Round-trip', 
        'days' => '3 days', 
        'price' => 'US$492', 
    ],
    [
        'img' => 'assets/img/destination/tr-4.jpg', 
        'name' => 'Nashville', 
        'name2' => 'Denver', 
        'title' => 'Round-trip', 
        'days' => '3 days', 
        'price' => 'US$492', 
    ],
    [
        'img' => 'assets/img/destination/tr-5.jpg', 
        'name' => 'Chicago', 
        'name2' => 'San Francisco', 
        'title' => 'Round-trip', 
        'days' => '3 days', 
        'price' => 'US$492', 
    ],
    [
        'img' => 'assets/img/destination/tr-10.jpg', 
        'name' => 'San Antonio', 
        'name2' => 'Las Vegas', 
        'title' => 'Round-trip', 
        'days' => '3 days', 
        'price' => 'US$492', 
    ],
    [
        'img' => 'assets/img/destination/tr-9.jpg', 
        'name' => 'Columbus', 
        'name2' => 'Kansas City', 
        'title' => 'Round-trip', 
        'days' => '3 days', 
        'price' => 'US$492', 
    ]
];
?>

<?php foreach ($routes as $item): ?>
    <div class="carousel-cell">
        <div class="pop-touritem mb-4">
            <a href="JavaScript:Void(0);" class="card rounded-3 shadow-wrap h-100 m-0">
                <div class="flight-thumb-wrapper">
                    <div class="popFlights-item-overHidden">
                        <img src="<?= $this->Url->build('/' . h($item['img'])) ?>" class="img-fluid" alt="">
                    </div>
                </div>
                <div class="touritem-middle position-relative p-3">
                    <div class="touritem-flexxer">
                        <h4 class="city fs-6 m-0 fw-bold">
                            <span><?= h($item['name']) ?></span>
                            <span class="svg-icon svg-icon-muted svg-icon-2hx px-1">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M17.4 7H4C3.4 7 3 7.4 3 8C3 8.6 3.4 9 4 9H17.4V7ZM6.60001 15H20C20.6 15 21 15.4 21 16C21 16.6 20.6 17 20 17H6.60001V15Z" fill="currentColor" />
                                    <path opacity="0.3" d="M17.4 3V13L21.7 8.70001C22.1 8.30001 22.1 7.69999 21.7 7.29999L17.4 3ZM6.6 11V21L2.3 16.7C1.9 16.3 1.9 15.7 2.3 15.3L6.6 11Z" fill="currentColor" />
                                </svg>
                            </span>
                            <span><?= h($item['name2']) ?></span>
                        </h4>
                        <p class="detail ellipsis-container">
                            <span class="ellipsis-item__normal"><?= h($item['title']) ?></span>
                            <span class="separate ellipsis-item__normal"></span>
                            <span class="ellipsis-item"><?= h($item['days']) ?></span>
                        </p>
                    </div>
                    <div class="flight-foots">
                        <h5 class="fs-5 low-price m-0"><span class="tag-span">From</span> <span class="price"><?= h($item['price']) ?></span></h5>
                    </div>
                </div>
            </a>
        </div>
    </div>
<?php endforeach; ?>