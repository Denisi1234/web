<?php
$articles = [
    [
        'id' => 1,
        'image' => 'https://images.unsplash.com/photo-1516426122078-c23e76319801?auto=format&fit=crop&w=700&q=85',
        'category' => 'Safari & Wildlife',
        'title' => 'Top 10 Lodges for the Great Serengeti Migration',
        'excerpt' => 'Discover the best luxury safari camps and budget-friendly lodges located near the prime migration river crossings in Northern Tanzania.',
        'date' => '24 Aug 2026',
        'author' => 'Amani Safari',
        'read_time' => '5 min read'
    ],
    [
        'id' => 2,
        'image' => 'https://images.unsplash.com/photo-1540541338287-41700207dee6?auto=format&fit=crop&w=700&q=85',
        'category' => 'Beach & Islands',
        'title' => 'The Ultimate Zanzibar Getaway: Nungwi vs Paje',
        'excerpt' => 'Compare northern sunsets with eastern kitesurfing beaches to choose the ideal coastal resort for your tropical holiday.',
        'date' => '18 Aug 2026',
        'author' => 'Sarah Mwita',
        'read_time' => '4 min read'
    ],
    [
        'id' => 3,
        'image' => 'https://images.unsplash.com/photo-1582719508461-905c673771fd?auto=format&fit=crop&w=700&q=85',
        'category' => 'Travel Tips',
        'title' => 'How to Pay and Travel Seamlessly with AzamPay in Tanzania',
        'excerpt' => 'Everything you need to know about using local mobile money, instant bookings, and cashless travel across Tanzanian stays.',
        'date' => '12 Aug 2026',
        'author' => 'FastNet Team',
        'read_time' => '3 min read'
    ]
];
?>

<?php foreach ($articles as $art): ?>
    <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
        <div class="card blog-grid-layout border border-slate-200 rounded-3 overflow-hidden shadow-sm hover:shadow-md transition h-100 bg-white">
            <div class="card-img-top position-relative overflow-hidden" style="height: 220px;">
                <img src="<?= h($art['image']) ?>" alt="<?= h($art['title']) ?>" class="img-fluid w-100 h-100 object-fit-cover">
                <span class="badge bg-primary position-absolute top-0 start-0 m-3 text-xs fw-bold px-3 py-1.5 shadow-xs"><?= h($art['category']) ?></span>
            </div>
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center gap-3 text-xs text-muted mb-2">
                        <span><i class="fa-regular fa-calendar me-1"></i><?= h($art['date']) ?></span>
                        <span>•</span>
                        <span><i class="fa-regular fa-clock me-1"></i><?= h($art['read_time']) ?></span>
                    </div>
                    <h5 class="fw-bold text-slate-900 fs-6 mb-2">
                        <a href="<?= $this->Url->build('/hotel-list-01'); ?>" class="text-dark hover:text-orange-600 transition"><?= h($art['title']) ?></a>
                    </h5>
                    <p class="text-slate-600 text-sm mb-3"><?= h($art['excerpt']) ?></p>
                </div>
                <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                    <span class="text-xs fw-bold text-slate-700"><i class="fa-solid fa-user-pen me-1 text-primary"></i><?= h($art['author']) ?></span>
                    <a href="<?= $this->Url->build('/hotel-list-01'); ?>" class="text-sm fw-bold text-orange-600 hover:text-orange-700">
                        Read Guide <i class="fa-solid fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
