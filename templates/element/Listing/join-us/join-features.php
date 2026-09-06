<?php
$features = [
    [
        'icon' => 'fa-wand-magic-sparkles', 
        'title' => 'Eco Friendly Team', 
        'desc' => 'Think of a news blog thats filled with content hourly on the day of going live. However, reviewersLorem ipsum is mostly a part of a Latin text by the classical.', 
    ],
    [
        'icon' => 'fa-gifts', 
        'title' => 'Multi Offers & Coupon', 
        'desc' => 'Think of a news blog thats filled with content hourly on the day of going live. However, reviewersLorem ipsum is mostly a part of a Latin text by the classical.', 
    ],
    [
        'icon' => 'fa-jet-fighter', 
        'title' => 'International Tour Package', 
        'desc' => 'Think of a news blog thats filled with content hourly on the day of going live. However, reviewersLorem ipsum is mostly a part of a Latin text by the classical.', 
    ],
    [
        'icon' => 'fa-droplet', 
        'title' => 'Room & Amenities Facility', 
        'desc' => 'Think of a news blog thats filled with content hourly on the day of going live. However, reviewersLorem ipsum is mostly a part of a Latin text by the classical.', 
    ],
    [
        'icon' => 'fa-snowflake', 
        'title' => 'Fully Friendly Support', 
        'desc' => 'Think of a news blog thats filled with content hourly on the day of going live. However, reviewersLorem ipsum is mostly a part of a Latin text by the classical.', 
    ],
    [
        'icon' => 'fa-sack-dollar', 
        'title' => 'Bonus & Flixible', 
        'desc' => 'Think of a news blog thats filled with content hourly on the day of going live. However, reviewersLorem ipsum is mostly a part of a Latin text by the classical.', 
    ]
];
?>

<?php foreach ($features as $item): ?>
    <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
        <div class="card h-100 rounded-4 border">
            <div class="card-body p-4">
                <div class="square--60 circle gray text-primary fs-3 mb-3"><i class="fa-solid <?= h($item['icon']) ?>"></i></div>
                <h4 class="fs-5"><?= h($item['title']) ?></h4>
                <p class="mb-0"><?= h($item['desc']) ?></p>
            </div>
        </div>
    </div>
<?php endforeach; ?>