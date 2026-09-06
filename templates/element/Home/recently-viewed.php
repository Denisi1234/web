<?php
$recentStays = is_array($recentStays ?? null) ? $recentStays : [];
if ($recentStays === []) {
    return;
}
?>
<!-- Recently Viewed Section Start (fastnetstays.com) -->
<section class="py-4 bg-white position-relative" id="home-recently-viewed-section">
	<?= $this->Html->css('/assets/css/recently-viewed.css'); ?>

	<div class="container" style="max-width: 1180px; padding-left: 20px; padding-right: 20px;">
		<div class="d-flex align-items-center justify-content-between mb-3">
			<h2 class="fs-4 fw-bold text-dark mb-0 tracking-tight" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
				Recently viewed stays
			</h2>
			<div class="d-flex align-items-center gap-2">
				<button type="button" class="btn btn-sm btn-white border rounded-circle shadow-xs d-flex align-items-center justify-content-center p-0" style="width: 32px; height: 32px;" onclick="scrollRecent('left')">
					<i class="fa-solid fa-chevron-left text-slate-600" style="font-size: 12px;"></i>
				</button>
				<button type="button" class="btn btn-sm btn-white border rounded-circle shadow-xs d-flex align-items-center justify-content-center p-0" style="width: 32px; height: 32px;" onclick="scrollRecent('right')">
					<i class="fa-solid fa-chevron-right text-slate-600" style="font-size: 12px;"></i>
				</button>
			</div>
		</div>

		<!-- Recently Viewed Shimmer Placeholder -->
		<div class="d-flex align-items-center gap-3 overflow-hidden pb-2 home-shimmer-container" id="home-recently-viewed-shimmer">
			<?php for ($i = 0; $i < 3; $i++): ?>
			<div class="trivago-recent-combo-card" style="border: 1px dashed #cbd5e1; pointer-events: none;">
				<div class="trivago-recent-query-box" style="background-color: #f8fafc;">
					<div class="fastnet-shimmer shimmer-rounded-xs" style="width: 90px; height: 16px; background-color: #e2e8f0; margin-bottom: 12px;"></div>
					<div class="d-flex flex-column gap-1.5">
						<div class="fastnet-shimmer shimmer-rounded-xs" style="width: 100px; height: 11px; background-color: #e2e8f0;"></div>
						<div class="fastnet-shimmer shimmer-rounded-xs" style="width: 80px; height: 11px; background-color: #e2e8f0;"></div>
					</div>
				</div>
				<div class="trivago-recent-prop-card" style="border: 1px solid #e2e8f0; pointer-events: none;">
					<div class="fastnet-shimmer" style="height: 80px; width: 100%; background-color: #e2e8f0;"></div>
					<div class="p-2 d-flex flex-column gap-1.5">
						<div class="fastnet-shimmer shimmer-rounded-xs" style="width: 90%; height: 12px; background-color: #e2e8f0;"></div>
						<div class="fastnet-shimmer shimmer-rounded-xs" style="width: 65%; height: 10px; background-color: #e2e8f0;"></div>
						<div class="fastnet-shimmer shimmer-rounded-xs" style="width: 45%; height: 10px; background-color: #e2e8f0;"></div>
					</div>
				</div>
			</div>
			<?php endfor; ?>
		</div>

		<?php
		$defCheckIn = date('Y-m-d', strtotime('+7 days'));
		$defCheckOut = date('Y-m-d', strtotime('+12 days'));
		$defDatesText = date('j M', strtotime($defCheckIn)) . ' - ' . date('j M', strtotime($defCheckOut));
		?>

		<!-- Recently Viewed Horizontal Scroll / Grid List (Real Content) -->
		<div class="d-flex align-items-center gap-3 overflow-x-auto pb-2 home-real-container" id="home-recently-viewed-list" style="scrollbar-width: none; -ms-overflow-style: none;">
			<?php foreach ($recentStays as $stay): 
				$stayId = $stay['id'] ?? 11;
				$stayName = $stay['name'] ?? 'Stay';
				$stayCity = $stay['city'] ?? 'Tanzania';
				$stayRating = $stay['rating'] ?? ($stay['star_rating'] ?? 4.5);
				$stayReviews = $stay['reviews_count'] ?? 120;
				$stayRatingText = $stay['rating_text'] ?? 'Very good';
				$stayImage = $stay['image_url'] ?? 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=400&q=80';
			?>
			<!-- Combo Card (Search Pill + Real Property Card) -->
			<div class="trivago-recent-combo-card">
				<!-- Search Pill on Left -->
				<a href="<?= $this->Url->build('/hotel-list-01?destination=' . urlencode($stayCity) . '&checkIn=' . $defCheckIn . '&checkOut=' . $defCheckOut . '&adults=2&rooms=1'); ?>" class="trivago-recent-query-box text-decoration-none">
					<div class="d-flex align-items-center justify-content-between w-100 mb-2">
						<span class="trivago-recent-city"><?= h(ucfirst($stayCity)) ?></span>
						<i class="fa-solid fa-magnifying-glass trivago-recent-search-icon"></i>
					</div>
					<div class="trivago-recent-meta">
						<div class="trivago-recent-dates"><?= h($defDatesText) ?></div>
						<div class="trivago-recent-guests">2 Guests, 1 Room</div>
					</div>
				</a>

				<!-- Viewed Property Card on Right -->
				<a href="<?= $this->Url->build('/hotel-detail/' . $stayId); ?>" class="trivago-recent-prop-card text-decoration-none">
					<div class="trivago-recent-img-wrap">
						<img src="<?= h($stayImage) ?>" alt="<?= h($stayName) ?>" class="trivago-recent-img" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=400&q=80';">
					</div>
					<div class="trivago-recent-prop-info">
						<h4 class="trivago-recent-prop-title" title="<?= h($stayName) ?>"><?= h(mb_strimwidth($stayName, 0, 18, '...')) ?></h4>
						<div class="trivago-recent-prop-rating">
							<span class="fw-bold text-slate-900"><?= number_format((float)$stayRating, 1) ?></span>
							<span class="text-slate-600">- <?= h($stayRatingText) ?></span>
							<span class="text-slate-400"><?= number_format((int)$stayReviews) ?></span>
						</div>
						<div class="trivago-recent-prop-stars">
							<i class="fa-solid fa-star"></i>
							<i class="fa-solid fa-star"></i>
							<i class="fa-solid fa-star"></i>
							<span><?= h(ucfirst($stayCity)) ?></span>
						</div>
					</div>
				</a>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<!-- Recently Viewed Section End -->
