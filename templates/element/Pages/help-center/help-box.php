<?php
$helps = [
    [
        'icon' => 'fa-solid fa-mug-hot', 
        'title' => 'Get Started', 
        'style' => 'success', 
    ],
    [
        'icon' => 'fa-brands fa-apple', 
        'title' => 'IOS Integration', 
        'style' => 'purple', 
    ],
    [
        'icon' => 'fa-solid fa-globe', 
        'title' => 'Web Integration', 
        'style' => 'warning', 
    ]
];
?>

<?php foreach ($helps as $item): ?>
    <div class="col-xl-4 col-lg-4 col-md-12">
        <!-- Get started START -->
        <div class="card shadow rounded-4 h-100">
            <!-- Title -->
            <div class="card-header d-flex align-items-center justify-content-start">
                <div class="square--50 circle bg-light-<?= h($item['style']) ?> text-<?= h($item['style']) ?> me-2 flex-shrink-0"><i class="<?= h($item['icon']) ?> fs-3"></i></div>
                <h5 class="card-title mb-0"><?= h($item['title']) ?></h5>
            </div>

            <!-- List START -->
            <div class="card-body">
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link d-flex" href="#"><i class="fa-solid fa-circle-arrow-right pt-1 me-2"></i>How To Customize Gulp?</a></li>
                    <li class="nav-item"><a class="nav-link d-flex" href="#"><i class="fa-solid fa-circle-arrow-right pt-1 me-2"></i>Change Color & Logo</a></li>
                    <li class="nav-item"><a class="nav-link d-flex" href="#"><i class="fa-solid fa-circle-arrow-right pt-1 me-2"></i>Apply Dark Mode & RTL Version</a></li>
                    <li class="nav-item"><a class="nav-link d-flex" href="#"><i class="fa-solid fa-circle-arrow-right pt-1 me-2"></i>Add Custom Fields & Area</a></li>
                    <li class="nav-item"><a class="nav-link d-flex" href="#"><i class="fa-solid fa-circle-arrow-right pt-1 me-2"></i>Updates and Support</a></li>
                </ul>
            </div>
            <!-- List END -->
        </div>
        <!-- Get started END -->
    </div>
<?php endforeach; ?>