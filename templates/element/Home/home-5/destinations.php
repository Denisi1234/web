<?php
$destinations = !empty($destinationsSummary) ? $destinationsSummary : [];

// If dynamic destinations are provided via controller or properties, generate from backend dataset
if (empty($destinations) && !empty($featuredResorts)) {
    $cityCounts = [];
    foreach ($featuredResorts as $p) {
        $cName = $p['city'] ?? ($p['area'] ?? 'Tanzania');
        if (!isset($cityCounts[$cName])) {
            $cityCounts[$cName] = [
                'title' => $cName,
                'city' => $cName,
                'img' => !empty($p['image_url']) ? $p['image_url'] : 'https://images.unsplash.com/photo-1540541338287-41700207dee6?auto=format&fit=crop&w=600&q=80',
                'count' => 1
            ];
        } else {
            $cityCounts[$cName]['count']++;
        }
    }
    $destinations = array_values($cityCounts);
}
?>

<?php if (!empty($destinations)): ?>
    <?php foreach ($destinations as $item): 
        $title = $item['title'] ?? ($item['name'] ?? 'Destination');
        $cityName = $item['city'] ?? $title;
        $count = (int)($item['count'] ?? 0);
        $img = !empty($item['img']) ? $item['img'] : 'https://images.unsplash.com/photo-1540541338287-41700207dee6?auto=format&fit=crop&w=600&q=80';
        $countLabel = ($count === 1) ? '1 Verified Lodge' : (($count > 1) ? "{$count} Verified Lodges" : 'Verified Lodges');
    ?>
        <div class="destinations-carousel-card">
            <div class="cardCities cursor rounded-3 shadow-sm position-relative overflow-hidden h-100">
                <div class="cardCities-image ratio ratio-4 h-100">
                    <img src="<?= (str_starts_with($img, 'http')) ? h($img) : $this->Url->build('/' . h($img)) ?>" class="img-fluid object-fit w-100 h-100" alt="<?= h($title) ?>" onerror="this.src='https://images.unsplash.com/photo-1540541338287-41700207dee6?auto=format&fit=crop&w=600&q=80'">
                </div>

                <div class="citiesCard-content d-flex flex-column justify-content-between text-center px-3 py-4">
                    <div class="cardCities-bg"></div>

                    <div class="citiesCard-topcaps">
                        <div class="d-flex align-items-center justify-content-center flex-wrap">
                            <div class="bg-transparents text-light text-xs rounded fw-medium p-2 m-1"><?= h($countLabel) ?></div>
                            <div class="bg-transparents text-light text-xs rounded fw-medium p-2 m-1">Fast Stays</div>
                        </div>
                    </div>

                    <div class="citiesCard-bottomcaps">
                        <h4 class="text-light fs-3 mb-3 fw-bold drop-shadow"><?= h($title) ?></h4>
                        <a href="<?= $this->Url->build('/hotel-list-01?destination=' . urlencode($cityName)); ?>" class="btn btn-whitener full-width fw-bold text-dark shadow-sm">
                            Explore Stays <i class="fa-solid fa-arrow-trend-up ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>