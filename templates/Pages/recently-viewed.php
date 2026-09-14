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
<style>
/* ── Trivago & Google Travel Recently Viewed Page Styles ─────────────────── */
.trivago-profile-wrapper {
    background-color: #f8fafc;
    min-height: calc(100vh - 70px);
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    color: #0f172a;
    padding-top: 36px;
    padding-bottom: 70px;
}

.trivago-profile-header {
    margin-bottom: 24px;
}
.trivago-profile-title {
    font-size: 28px;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 4px;
    letter-spacing: -0.02em;
}
.trivago-profile-sub {
    font-size: 14.5px;
    color: #64748b;
    margin-bottom: 0;
}

/* Clear All Button */
.rv-btn-clear {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #475569;
    font-size: 13px;
    font-weight: 600;
    padding: 7px 14px;
    border-radius: 8px;
    transition: all 0.15s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
}
.rv-btn-clear:hover {
    background: #fee2e2;
    border-color: #fca5a5;
    color: #dc2626;
}

/* Stays Grid & Cards */
.rv-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
}

.rv-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    transition: transform 0.15s ease, box-shadow 0.15s ease, opacity 0.25s ease;
    position: relative;
}
.rv-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(15, 23, 42, 0.08);
}

.rv-img-wrapper {
    position: relative;
    width: 100%;
    height: 180px;
    background-color: #e2e8f0;
    overflow: hidden;
}
.rv-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}
.rv-card:hover .rv-img {
    transform: scale(1.04);
}

.rv-wishlist-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 34px;
    height: 34px;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(4px);
    border: none;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    color: #e11d48;
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(0,0,0,0.12);
    transition: transform 0.15s ease;
    z-index: 2;
}
.rv-wishlist-btn:hover {
    transform: scale(1.1);
}

.rv-remove-btn {
    position: absolute;
    top: 10px;
    left: 10px;
    width: 30px;
    height: 30px;
    background: rgba(15, 23, 42, 0.65);
    color: #ffffff;
    border: none;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    cursor: pointer;
    transition: background 0.15s ease;
    z-index: 2;
}
.rv-remove-btn:hover {
    background: #dc2626;
}

.rv-card-body {
    padding: 16px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

.rv-rating-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12.5px;
    margin-bottom: 6px;
}
.rv-score-box {
    background: #0284c7;
    color: #ffffff;
    font-weight: 700;
    font-size: 11.5px;
    padding: 2px 6px;
    border-radius: 4px;
}

.rv-card-title {
    font-size: 16px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.3;
    margin-bottom: 4px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.rv-card-location {
    font-size: 13px;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 5px;
    margin-bottom: 12px;
}

.rv-card-footer {
    margin-top: auto;
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    padding-top: 12px;
    border-top: 1px solid #f1f5f9;
}

.rv-price-label {
    font-size: 11px;
    color: #64748b;
    text-transform: uppercase;
    font-weight: 600;
}
.rv-price-val {
    font-size: 17px;
    font-weight: 800;
    color: #0f172a;
}
.rv-price-unit {
    font-size: 12px;
    color: #64748b;
    font-weight: 500;
}

.rv-view-btn {
    background-color: #007fad;
    color: #ffffff;
    font-size: 13px;
    font-weight: 600;
    padding: 8px 16px;
    border-radius: 8px;
    text-decoration: none !important;
    transition: background-color 0.12s ease;
}
.rv-view-btn:hover {
    background-color: #006b94;
    color: #ffffff;
}

/* Empty State */
.rv-empty-state {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    padding: 40px 24px;
    text-align: center;
}
.rv-illustration-wrapper {
    max-width: 480px;
    margin: 0 auto 24px auto;
}
.rv-illustration-img {
    width: 100%;
    height: auto;
    display: block;
    object-fit: contain;
}
.rv-btn-search-stays {
    background-color: #007fad;
    color: #ffffff;
    font-size: 14.5px;
    font-weight: 700;
    padding: 12px 28px;
    border-radius: 8px;
    border: none;
    text-decoration: none !important;
    display: inline-block;
    cursor: pointer;
    transition: background-color 0.15s ease;
}
.rv-btn-search-stays:hover {
    background-color: #006b94;
    color: #ffffff;
}
</style>

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
                        $sReviews = (int)($stay['reviews_count'] ?? 120);
                        $sImg = !empty($stay['image_url']) ? $stay['image_url'] : ($stay['image'] ?? 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=600&h=400&fit=crop');
                    ?>
                    <div class="rv-card" id="rv_card_<?= $sId ?>" data-property-id="<?= $sId ?>">
                        <div class="rv-img-wrapper">
                            <img src="<?= h($sImg) ?>" alt="<?= h($sName) ?>" class="rv-img" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1566073771259-6a8506099945?w=600&h=400&fit=crop';">
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
                                <span class="text-slate-400">&middot; <?= number_format($sReviews) ?> reviews</span>
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

<script>
// Remove single item from recently viewed
async function removeRecentlyViewed(id) {
    const card = document.getElementById('rv_card_' + id);
    if (card) {
        card.style.opacity = '0';
        card.style.transform = 'scale(0.95)';
        setTimeout(() => {
            card.remove();
            checkEmptyState();
        }, 250);
    }
    // Remove from localStorage
    try {
        let recents = JSON.parse(localStorage.getItem('fastnet_recently_viewed') || '[]');
        if (Array.isArray(recents)) {
            recents = recents.filter(item => item && parseInt(item.id) !== parseInt(id));
            localStorage.setItem('fastnet_recently_viewed', JSON.stringify(recents));
        }
    } catch(e) {}
    // Remove from server session
    try {
        const csrf = document.querySelector('meta[name="csrfToken"]')?.content || '';
        await fetch('/recently-viewed/remove/' + id, {
            method: 'POST',
            headers: { 'X-CSRF-Token': csrf, 'Accept': 'application/json' }
        });
    } catch(e) {}
}

// Clear all recently viewed items
async function clearRecentlyViewed() {
    if (!confirm('Clear all recently viewed stays?')) return;
    const grid = document.getElementById('rv_grid_container');
    if (grid) grid.innerHTML = '';
    // Clear localStorage
    try {
        localStorage.removeItem('fastnet_recently_viewed');
    } catch(e) {}
    // Clear server session
    try {
        const csrf = document.querySelector('meta[name="csrfToken"]')?.content || '';
        await fetch('/recently-viewed/clear', {
            method: 'POST',
            headers: { 'X-CSRF-Token': csrf, 'Accept': 'application/json' }
        });
    } catch(e) {}
    checkEmptyState();
}

function checkEmptyState() {
    const grid = document.getElementById('rv_grid_container');
    const emptyBox = document.getElementById('rv_empty_container');
    const clearBtn = document.getElementById('btn_clear_all');
    const countSub = document.getElementById('rv_count_sub');
    const remainingCards = grid ? grid.querySelectorAll('.rv-card') : [];
    
    if (remainingCards.length === 0) {
        if (grid) grid.style.display = 'none';
        if (emptyBox) emptyBox.style.display = 'block';
        if (clearBtn) clearBtn.style.display = 'none';
        if (countSub) countSub.innerText = 'Stays and properties you recently browsed';
    } else {
        if (grid) grid.style.display = 'grid';
        if (emptyBox) emptyBox.style.display = 'none';
        if (clearBtn) clearBtn.style.display = 'inline-flex';
        if (countSub) countSub.innerText = remainingCards.length + ' stays saved from your browsing';
    }
}

// Client-side hydration from localStorage if session was clean
document.addEventListener('DOMContentLoaded', function() {
    if (typeof syncWishlistButtons === 'function') {
        syncWishlistButtons();
    }
});
</script>

<?= $this->element('footer', ['skin' => 'skin-light-footer']) ?>
