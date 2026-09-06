<?php
$attractions = [
    [
        'icon' => 'fa-brands fa-servicestack', 
        'title' => 'Top Attractions', 
        'name' => 'Hong Kong Disneyland (170m)', 
        'name2' => 'Hong Kong Museum (250m)', 
        'name3' => 'The Peak Tram (80m)', 
    ],
    [
        'icon' => 'fa-solid fa-jet-fighter', 
        'title' => 'Nearest Airport & Metro', 
        'name' => 'Airport: Janghai Airport (370m)', 
        'name2' => 'Airport: Shivalay Airport (2.4km)', 
        'name3' => 'Metro: Mandpam (500m)', 
    ],
    [
        'icon' => 'fa-solid fa-martini-glass-empty', 
        'title' => 'Cafe & Bars', 
        'name' => 'Cafe: Bekker Cofee Cafe (60m)', 
        'name2' => 'Cafe: Levendaram restaurants (120m)', 
        'name3' => 'Bar: The Blue Bar (90m)', 
    ]
];
?>

<?php foreach ($attractions as $item): ?>
    <div class="col-xl-4 col-lg-4 col-md-4">
        <div class="card p-3 mb-4">
            <div class="nearestServ-wrap">
                <div class="nearestServ-head d-flex mb-1">
                    <h6 class="fs-6 fw-semibold text-primary mb-1"><i class="<?= h($item['icon']) ?> me-2"></i><?= h($item['title']) ?></h6>
                </div>
                <div class="nearestServ-caps">
                    <ul class="row align-items-start g-2 p-0 m-0">
                        <li class="col-12 text-muted-2"><?= h($item['name']) ?></li>
                        <li class="col-12 text-muted-2"><?= h($item['name2']) ?></li>
                        <li class="col-12 text-muted-2"><?= h($item['name3']) ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>