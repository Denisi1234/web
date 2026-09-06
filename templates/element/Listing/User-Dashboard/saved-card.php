<?php
$cards = [
    [
        'img' => 'assets/img/vodacom-logo.png', 
        'title' => 'Vodacom M-Pesa (0754•••892)', 
        'name' => 'Daniel Duekoza', 
        'date' => 'Active', 
        'class' => 'bg-danger', 
        'style' => 'creditcardDropdown', 
    ],
    [
        'img' => 'assets/img/tigo-pesa-logo.jpg', 
        'title' => 'Tigo Pesa (0712•••563)', 
        'name' => 'Daniel Duekoza', 
        'date' => 'Active', 
        'class' => 'bg-primary', 
        'style' => 'creditcardDropdown1', 
    ]
];
?>

<?php foreach ($cards as $item): ?>
    <div class="col-xl-5 col-lg-6 col-md-6">
        <div class="card h-100">
            <div class="<?= h($item['class']) ?> p-4 rounded-3">
                <div class="d-flex justify-content-between align-items-start">
                    <img class="img-fluid" src="<?= $this->Url->build('/' . h($item['img'])) ?>" width="55" alt="">
                    <!-- Card action START -->
                    <div class="dropdown">
                        <a class="text-white" href="#" id="<?= h($item['style']) ?>" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                            <!-- Dropdown Icon -->
                            <svg width="24" height="24" fill="none">
                                <circle fill="currentColor" cx="12.5" cy="3.5" r="2.5"></circle>
                                <circle fill="currentColor" opacity="0.5" cx="12.5" cy="11.5" r="2.5"></circle>
                                <circle fill="currentColor" opacity="0.3" cx="12.5" cy="19.5" r="2.5"></circle>
                            </svg>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="<?= h($item['style']) ?>">
                            <li><a class="dropdown-item" href="#"><i class="bi bi-credit-card-2-front-fill me-2 fw-icon"></i>Edit card</a></li>
                            <li><a class="dropdown-item" href="#"><i class="bi bi-calculator me-2 fw-icon"></i>Currency converter</a></li>
                        </ul>
                    </div>
                    <!-- Card action END -->
                </div>
                <h4 class="text-white fs-6 mt-4"><?= h($item['title']) ?></h4>
                <div class="d-flex justify-content-between text-white mt-4">
                    <div class="d-flex flex-column">
                        <span class="text-md">Issued To</span>
                        <span class="text-sm fw-medium text-uppercase"><?= h($item['name']) ?></span>
                    </div>
                    <div class="d-flex text-end flex-column">
                        <span class="text-md">Valid Thru</span>
                        <span><?= h($item['date']) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>