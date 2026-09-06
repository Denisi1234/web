<?php
$facts = [
    [
        'number' => '32K', 
        'title' => 'Overall', 
        'title2' => 'Booking', 
    ],
    [
        'number' => '25+', 
        'title' => 'Years', 
        'title2' => 'Successfully', 
    ],
    [
        'number' => '45K', 
        'title' => 'Happly', 
        'title2' => 'Users', 
    ],
    [
        'number' => '22', 
        'title' => 'Countries', 
        'title2' => 'We Work', 
    ]
];
?>

<?php foreach ($facts as $item): ?>
    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-6">
        <div class="urfacts-wrap d-flex align-items-center justify-content-center">
            <div class="urfacts-first flex-shrink-0">
                <h3 class="fs-1 fw-medium text-primary mb-0"><?= h($item['number']) ?></h3>
            </div>
            <div class="urfacts-caps ps-3">
                <p class="text-muted-2 lh-base mb-0"><?= h($item['title']) ?><br><?= h($item['title2']) ?></p>
            </div>
        </div>
    </div>
<?php endforeach; ?>