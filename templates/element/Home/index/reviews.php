<?php
$reviews = [
    [
        'img' => 'assets/img/team-1.jpg', 
        'name' => 'Aman Diwakar', 
        'title' => 'United States', 
        'desc' => 'Nemo enim ipsam voluptatem, quia voluptas sit, aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos, qui ratione voluptatem sequi nesciunt', 
    ],
    [
        'img' => 'assets/img/team-2.jpg', 
        'name' => 'Kunal M. Thakur', 
        'title' => 'United States', 
        'desc' => 'Nemo enim ipsam voluptatem, quia voluptas sit, aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos, qui ratione voluptatem sequi nesciunt', 
    ],
    [
        'img' => 'assets/img/team-3.jpg', 
        'name' => 'Divya Talwar', 
        'title' => 'United States', 
        'desc' => 'Nemo enim ipsam voluptatem, quia voluptas sit, aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos, qui ratione voluptatem sequi nesciunt', 
    ],
    [
        'img' => 'assets/img/team-4.jpg', 
        'name' => 'Karan Maheshwari', 
        'title' => 'United States', 
        'desc' => 'Nemo enim ipsam voluptatem, quia voluptas sit, aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos, qui ratione voluptatem sequi nesciunt', 
    ],
    [
        'img' => 'assets/img/team-5.jpg', 
        'name' => 'Ritika Mathur', 
        'title' => 'United States', 
        'desc' => 'Nemo enim ipsam voluptatem, quia voluptas sit, aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos, qui ratione voluptatem sequi nesciunt', 
    ]
];
?>

<?php foreach ($reviews as $item): ?>
    <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6">
        <div class="card border-0 rounded-3">
            <div class="card-body">
                <div class="position-absolute top-0 end-0 mt-3 me-3"><span class="square--40 circle text-primary bg-light-primary"><i class="fa-solid fa-quote-right"></i></span></div>
                <div class="d-flex align-items-center flex-thumbes">
                    <div class="revws-pic"><img src="<?= $this->Url->build('/' . h($item['img'])) ?>" class="img-fluid rounded-2" width="80" alt=""></div>
                    <div class="revws-caps ps-3">
                        <h6 class="fw-bold fs-6 m-0"><?= h($item['name']) ?></h6>
                        <p class="text-muted-2 text-md m-0"><?= h($item['title']) ?></p>
                        <div class="d-flex align-items-center justify-content-start">
                            <span class="me-1 text-xs text-warning"><i class="fa-solid fa-star"></i></span>
                            <span class="me-1 text-xs text-warning"><i class="fa-solid fa-star"></i></span>
                            <span class="me-1 text-xs text-warning"><i class="fa-solid fa-star"></i></span>
                            <span class="me-1 text-xs text-warning"><i class="fa-solid fa-star"></i></span>
                        </div>
                    </div>
                </div>
                <div class="revws-desc mt-3">
                    <p class="m-0 text-md"><?= h($item['desc']) ?></p>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>