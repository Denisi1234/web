<?php
$offices = [
    [
        'icon' => 'fa-briefcase', 
        'title' => 'Main Office (Dar es Salaam)', 
        'name' => 'Sinza Makaburini Street, Block 4,', 
        'location' => 'Kinondoni, Dar es Salaam, TANZANIA', 
        'email' => 'support@fastnetstays.com', 
    ],
    [
        'icon' => 'fa-headset', 
        'title' => '24/7 Customer Support', 
        'name' => 'Dedicated Guest Helpline,', 
        'location' => '+255 700 000 000 (Toll Free)', 
        'email' => 'help@fastnetstays.com', 
    ],
    [
        'icon' => 'fa-envelope-open-text', 
        'title' => 'Arusha Host Portal', 
        'name' => '45 Sekei Road,', 
        'location' => 'Arusha City, TANZANIA', 
        'email' => 'partners@fastnetstays.com', 
    ]
];
?>

<?php foreach ($offices as $item): ?>
    <div class="col-xl-4 col-lg-4 col-md-4">
        <div class="card p-4 rounded-4 border br-dashed text-center">
            <div class="crds-icons d-inline-flex mx-auto mb-3 text-primary fs-2"><i class="fa-solid <?= h($item['icon']) ?>"></i></div>
            <div class="crds-desc">
                <h5><?= h($item['title']) ?></h5>
                <p class="text-md lh-2 mb-0"><?= h($item['name']) ?><br><?= h($item['location']) ?><br><?= h($item['email']) ?></p>
            </div>
        </div>
    </div>
<?php endforeach; ?>