<?php
$features = [
    [
        'class' => 'primary', 
        'title' => 'Get Superb Deals', 
        'desc' => 'Just fill up a page with draft copy about the client’s business and they will actually read it and comment on it. They will be drawn to it, fiercely. Do it the wrong way.', 
    ],
    [
        'class' => 'info', 
        'title' => '100% Transparency Price', 
        'desc' => 'Just fill up a page with draft copy about the client’s business and they will actually read it and comment on it. They will be drawn to it, fiercely. Do it the wrong way.', 
    ],
    [
        'class' => 'success', 
        'title' => 'Pure Trusted & Free', 
        'desc' => 'Just fill up a page with draft copy about the client’s business and they will actually read it and comment on it. They will be drawn to it, fiercely. Do it the wrong way.', 
    ],
    [
        'class' => 'warning', 
        'title' => 'Travel With Confidence', 
        'desc' => 'Just fill up a page with draft copy about the client’s business and they will actually read it and comment on it. They will be drawn to it, fiercely. Do it the wrong way.', 
    ]
];
?>

<?php foreach ($features as $item): ?>
    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6">
        <div class="fbxer-wraps">
            <div class="fbxerWraps-icons mb-3">
                <div class="square--70 circle bg-light-<?= h($item['class']) ?>"><i class="fa-solid fa-gifts text-<?= h($item['class']) ?> fs-3"></i> </div>
            </div>
            <div class="fbxerWraps-caps">
                <h5 class="fw-bold fs-6"><?= h($item['title']) ?></h5>
                <p class="fw-light fs-6 m-0"><?= h($item['desc']) ?></p>
            </div>
        </div>
    </div>
<?php endforeach; ?>