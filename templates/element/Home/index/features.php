<?php
$features = [
    [
        'icon' => 'fa-sack-dollar', 
        'title' => 'Easy Booking', 
        'desc' => 'Cicero famously orated against his political opponent Lucius Sergius Catilina. Occasionally the first Oration against Catiline.', 
    ],
    [
        'icon' => 'fa-umbrella-beach', 
        'title' => 'Best Destinations', 
        'desc' => 'Cicero famously orated against his political opponent Lucius Sergius Catilina. Occasionally the first Oration against Catiline.', 
    ],
    [
        'icon' => 'fa-person-walking-luggage', 
        'title' => 'Travel Guides', 
        'desc' => 'Cicero famously orated against his political opponent Lucius Sergius Catilina. Occasionally the first Oration against Catiline.', 
    ],
    [
        'icon' => 'fa-headset', 
        'title' => 'Friendly Support', 
        'desc' => 'Cicero famously orated against his political opponent Lucius Sergius Catilina. Occasionally the first Oration against Catiline.', 
    ]
];
?>

<?php foreach ($features as $item): ?>
    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-5">
        <div class="featuresBox-wrap">
            <div class="featuresBox-icons mb-3">
                <i class="fa-solid <?= h($item['icon']) ?> fs-1 text-primary"></i>
            </div>
            <div class="featuresBox-captions">
                <h4 class="fw-bold fs-5 lh-base mb-0"><?= h($item['title']) ?></h4>
                <p class="m-0"><?= h($item['desc']) ?></p>
            </div>
        </div>
    </div>
<?php endforeach; ?>