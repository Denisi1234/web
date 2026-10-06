<?php
/**
 * FastNet Stays - Recently Viewed Profile Page
 */
$this->assign('title', 'Recently viewed - FastNet Stays');
$recentStays = is_array($recentStays ?? null) ? $recentStays : [];
?>
<?= $this->Html->css('/assets/css/google-travel-layout.css') ?>
<?= $this->Html->css('/assets/css/google-travel-home.css') ?>
<?= $this->element('navbar'); ?>

<main id="main-content" role="main">
<?= $this->Html->css('/assets/css/recently-viewed.css') ?>

<div class="trivago-profile-wrapper">
    <div class="container" style="max-width: 1140px; padding-left: 16px; padding-right: 16px;">
        <div class="row">
            
            <!-- Reusable Left Sidebar -->
            <?= $this->element('profile_sidebar', ['active' => 'recently-viewed']); ?>

            <!-- Right Content -->
            <div class="col-lg-9 ps-lg-4">
                
                <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 trivago-profile-header">
                    <div>
                        <h1 class="trivago-profile-title">Recently viewed</h1>
                        <p class="trivago-profile-sub" id="rv_count_sub">
                            <?= count($recentStays) > 0 ? count($recentStays) . ' stays saved from your browsing' : 'Stays and properties you recently browsed' ?>
                        </p>
                    </div>
                    <?php if (!empty($recentStays)): ?>
                    <button type="button" class="rv-btn-clear align-self-start align-self-sm-center" id="btn_clear_all" onclick="clearRecentlyViewed()">
                        <i class="fa-regular fa-trash-can"></i>
                        <span>Clear history</span>
                    </button>
                    <?php endif; ?>
                </div>

                <!-- Stays Grid -->
                <div class="rv-grid" id="rv_grid_container" style="<?= empty($recentStays) ? 'display: none;' : '' ?>">
                    <?php foreach ($recentStays as $stay): 
                        $sId = (int)($stay['id'] ?? 0);
                        $sName = $stay['name'] ?? 'Stay';
                        $sCity = $stay['city'] ?? 'Tanzania';
                        $sArea = $stay['area'] ?? '';
                        $sLocation = $sArea && $sCity && !str_contains(strtolower($sArea), strtolower($sCity)) ? $sArea . ', ' . $sCity : ($sArea ?: $sCity);
                        $sPrice = (float)($stay['price_per_night'] ?? ($stay['price'] ?? 85000));
                        $sRating = (float)($stay['rating'] ?? 4.8);
                        $score10 = ($sRating <= 5.0) ? round($sRating * 2, 1) : round($sRating, 1);
                        // Real values only - this defaulted to 120 reviews and a
                        // stock Unsplash photo for any stay missing them.
                        $sReviews = (int)($stay['reviews_count'] ?? 0);
                        $sImg = !empty($stay['image_url']) ? $stay['image_url'] : ($stay['image'] ?? '');
                    ?>
                    <div class="rv-card" id="rv_card_<?= $sId ?>" data-property-id="<?= $sId ?>">
                        <div class="rv-img-wrapper">
                            <img src="<?= h($sImg) ?>" alt="<?= h($sName) ?>" class="rv-img" loading="lazy" onerror="this.onerror=null;this.removeAttribute('src');this.alt='No photo available';">
                            <button type="button" class="rv-remove-btn" title="Remove from recently viewed" onclick="removeRecentlyViewed(<?= $sId ?>)">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                            <button type="button" class="rv-wishlist-btn" title="Save to wishlist" data-property-id="<?= $sId ?>" onclick="toggleWishlist(<?= $sId ?>, this)">
                                <i class="fa-regular fa-heart"></i>
                            </button>
                        </div>
                        <div class="rv-card-body">
                            <div class="rv-rating-badge">
                                <span class="rv-score-box"><?= number_format($score10, 1) ?></span>
                                <span class="fw-semibold text-slate-700"><?= $score10 >= 8.5 ? 'Excellent' : 'Very good' ?></span>
                                <?php if ($sReviews > 0): ?><span class="text-slate-400">&middot; <?= number_format($sReviews) ?> <?= $sReviews === 1 ? 'review' : 'reviews' ?></span><?php endif; ?>
                            </div>
                            <h2 class="rv-card-title" title="<?= h($sName) ?>"><?= h($sName) ?></h2>
                            <div class="rv-card-location">
                                <i class="fa-solid fa-location-dot text-slate-400"></i>
                                <span><?= h($sLocation) ?></span>
                            </div>
                            <div class="rv-card-footer">
                                <div>
                                    <div class="rv-price-label">Starting from</div>
                                    <div class="rv-price-val">TSh <?= number_format($sPrice) ?> <span class="rv-price-unit">/ night</span></div>
                                </div>
                                <a href="<?= $this->Url->build('/hotel-detail/' . $sId); ?>" class="rv-view-btn">
                                    View stay
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Empty State -->
                <div class="rv-empty-state" id="rv_empty_container" style="<?= !empty($recentStays) ? 'display: none;' : '' ?>">
                    <div class="rv-illustration-wrapper">
                        <img src="<?= $this->Url->build('/assets/img/recently-viewed-illustration.png'); ?>" alt="Recently viewed illustration" class="rv-illustration-img">
                    </div>
                    <h2 class="fs-4 fw-bold text-slate-900 mb-2">No recently viewed stays yet</h2>
                    <p class="text-slate-600 mb-4" style="max-width: 460px; margin: 0 auto;">
                        Explore hotels, lodges and resorts across Tanzania, and we’ll save them here so you can easily continue your search later.
                    </p>
                    <a href="<?= $this->Url->build('/hotel-list-01'); ?>" class="rv-btn-search-stays">
                        Search stays
                    </a>
                </div>

            </div>

        </div>
    </div>
</div>
</main>
<?= $this->element('Recently/recently-viewed-script') ?>

<?= $this->element('footer', ['skin' => 'skin-light-footer']) ?>