<?php
$offers = [
    [
        'img' => 'assets/img/flt-3.png', 
        'name' => 'Flat', 
        'off' => '$899 off', 
        'title' => 'On Domestic Flights', 
        'tag' => 'LOG125F', 
        'class' => 'success', 
    ],
    [
        'img' => 'assets/img/goibibo.png', 
        'name' => 'Flat', 
        'off' => '$899 off', 
        'title' => 'On Domestic Flights', 
        'tag' => 'INT285', 
        'class' => 'purple', 
    ],
    [
        'img' => 'assets/img/flt-2.png', 
        'name' => 'Flat', 
        'off' => '$899 off', 
        'title' => 'On Domestic Flights', 
        'tag' => 'LOG125F', 
        'class' => 'danger', 
    ],
    [
        'img' => 'assets/img/flt-1.png', 
        'name' => 'Flat', 
        'off' => '$899 off', 
        'title' => 'On Domestic Flights', 
        'tag' => 'LOG125F', 
        'class' => 'warning', 
    ]
];
?>

<?php foreach ($offers as $item): ?>
    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
        <div class="pop-touritems">
            <div class="card bg-light-<?= h($item['class']) ?> rounded-3 p-4 m-0">
                <div class="card-body py-3 px-1">
                    <div class="position-relative">
                        <div class="offers-pic"><img src="<?= $this->Url->build('/' . h($item['img'])) ?>" class="img-fluid rounded" width="150" alt=""></div>
                    </div>
                    <div class="position-relative py-4 my-1">
                        <span class="mb-1 text-dark fw-medium"><?= h($item['name']) ?></span>
                        <h4 class="mb-1 text-<?= h($item['class']) ?>"><?= h($item['off']) ?></h4>
                        <h6 class="fw-normal fw-medium"><?= h($item['title']) ?></h6>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="bg-light-<?= h($item['class']) ?> border border-2 border-<?= h($item['class']) ?> br-dashed rounded-2 px-3 py-2">
                            <h5 class="fw-bold user-select-all text-<?= h($item['class']) ?> mb-0"><?= h($item['tag']) ?></h5>
                        </div>
                        <a href="#" class="nav-link text-<?= h($item['class']) ?>"><i class="fa-solid fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>