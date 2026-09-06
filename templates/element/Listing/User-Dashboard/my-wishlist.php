<?php
$catalogProps = !empty($allProperties) ? $allProperties : [
    [
        'id' => 11,
        'name' => 'Sunrise Luxury Lodge',
        'city' => 'Dar es Salaam',
        'area' => 'Mbezi Beach',
        'starting_price' => 70000,
        'star_rating' => 5,
        'rating' => 4.9,
        'image_url' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=700&q=85'
    ],
    [
        'id' => 12,
        'name' => 'Kimbeleko Safari Haven',
        'city' => 'Arusha',
        'area' => 'Sekei',
        'starting_price' => 70000,
        'star_rating' => 4,
        'rating' => 4.8,
        'image_url' => 'https://images.unsplash.com/photo-1582719508461-905c673771fd?auto=format&fit=crop&w=700&q=85'
    ]
];
?>

<div class="wishlist-container">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h5 class="fw-bold text-slate-900 mb-0">Saved Stays & Lodges</h5>
            <p class="text-xs text-muted mb-0" id="wishlist-count-label">Loading your saved stays...</p>
        </div>
        <div>
            <a href="<?= $this->Url->build('/hotel-list-01'); ?>" class="btn btn-sm btn-primary rounded-full fw-bold px-3 shadow-sm">
                <i class="fa-solid fa-plus me-1"></i>Explore More Stays
            </a>
        </div>
    </div>

    <!-- Container for dynamic wishlist cards -->
    <div id="wishlist-cards-list" class="row g-4">
        <!-- JavaScript will populate real items here or fallback state -->
    </div>

    <!-- Empty State Template -->
    <div id="wishlist-empty-state" class="text-center py-5 d-none">
        <div class="card p-5 border border-slate-200 rounded-3 shadow-xs bg-white">
            <div class="d-inline-flex justify-content-center align-items-center mb-3">
                <div class="square--80 circle bg-red-50 text-danger fs-1 d-flex align-items-center justify-content-center">
                    <i class="fa-regular fa-heart"></i>
                </div>
            </div>
            <h4 class="fw-bold text-slate-900 mb-2">Your Wishlist is Empty</h4>
            <p class="text-slate-600 max-w-md mx-auto mb-4 text-sm">Save your favorite Tanzanian lodges and safari stays by clicking the heart icon on any stay card while browsing.</p>
            <div class="d-flex justify-content-center gap-2">
                <a href="<?= $this->Url->build('/hotel-list-01'); ?>" class="btn btn-primary rounded-full px-4 fw-bold">
                    Find Stays in Tanzania <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
const CATALOG_PROPERTIES = <?= json_encode($catalogProps) ?>;

function getWishlistIds() {
    try {
        const stored = localStorage.getItem('fastnet_wishlist');
        if (stored) {
            const parsed = JSON.parse(stored);
            if (Array.isArray(parsed) && parsed.length > 0) return parsed;
        }
    } catch(e) {}
    // Default initial demonstration IDs if brand new session
    return [11, 12];
}

function saveWishlistIds(ids) {
    localStorage.setItem('fastnet_wishlist', JSON.stringify(ids));
}

async function fetchBackendWishlist() {
    const token = localStorage.getItem('auth_token');
    if (!token) return null;
    try {
        const res = await fetch('http://127.0.0.1:8000/api/wishlist', {
            headers: {
                'Authorization': 'Bearer ' + token,
                'Accept': 'application/json'
            }
        });
        if (res.ok) {
            const data = await res.json();
            return Array.isArray(data) ? data : (data.data || []);
        }
    } catch(e) {
        console.warn('Backend wishlist sync failed, using localStorage:', e);
    }
    return null;
}

async function renderWishlistPage() {
    const listContainer = document.getElementById('wishlist-cards-list');
    const emptyState = document.getElementById('wishlist-empty-state');
    const countLabel = document.getElementById('wishlist-count-label');
    if (!listContainer) return;

    let savedIds = getWishlistIds();
    
    // Attempt backend sync
    const backendItems = await fetchBackendWishlist();
    let displayProps = [];

    if (backendItems && backendItems.length > 0) {
        displayProps = backendItems;
        savedIds = backendItems.map(item => item.id);
        saveWishlistIds(savedIds);
    } else {
        // Match from catalog
        displayProps = CATALOG_PROPERTIES.filter(p => savedIds.includes(parseInt(p.id)));
    }

    if (displayProps.length === 0) {
        listContainer.innerHTML = '';
        emptyState.classList.remove('d-none');
        countLabel.textContent = '0 saved stays';
        return;
    }

    emptyState.classList.add('d-none');
    countLabel.textContent = `Showing ${displayProps.length} saved ${displayProps.length === 1 ? 'stay' : 'stays'}`;

    let html = '';
    displayProps.forEach(item => {
        const id = item.id;
        const name = item.name || 'FastNet Lodge';
        const city = item.city || 'Tanzania';
        const area = item.area || city;
        const price = item.starting_price || item.price_per_night || item.price || 70000;
        const stars = Math.max(1, Math.min(5, parseInt(item.star_rating || 4)));
        const rating = item.rating ? parseFloat(item.rating).toFixed(1) : '4.8';
        const img = item.image_url || item.primary_image_url || item.cover_image || 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=700&q=85';
        const detailUrl = '<?= $this->Url->build('/hotel-detail'); ?>/' + id;

        html += `
        <div class="col-xl-12 col-lg-12 col-12" id="wishlist-item-${id}">
            <div class="card list-layout-block border border-slate-200 rounded-3 p-3 shadow-xs hover:shadow-md transition bg-white">
                <div class="row align-items-center">
                    <div class="col-xl-4 col-lg-4 col-md-5">
                        <div class="cardImage__caps rounded-3 overflow-hidden position-relative" style="height: 200px;">
                            <a href="${detailUrl}" class="d-block w-100 h-100">
                                <img class="img-fluid w-100 h-100 object-fit-cover" src="${img}" alt="${name}" onerror="this.src='https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=700&q=85'">
                            </a>
                            <span class="badge bg-danger position-absolute top-0 start-0 m-3 text-xs shadow-xs"><i class="fa-solid fa-heart me-1"></i>Favorite</span>
                        </div>
                    </div>
                    <div class="col-xl-5 col-lg-5 col-md-7">
                        <div class="listLayout_midCaps mt-md-0 mt-3 mb-md-0 mb-3">
                            <div class="d-flex align-items-center justify-content-start mb-1">
                                <div class="d-inline-block text-warning text-xs">
                                    ${'★'.repeat(stars)}
                                </div>
                                <span class="badge bg-light-primary text-primary ms-2 text-xs">FastNet Verified</span>
                            </div>
                            <h4 class="fs-5 fw-bold mb-1">
                                <a href="${detailUrl}" class="text-dark hover:text-orange-600 transition">${name}</a>
                            </h4>
                            <p class="text-muted-2 text-md mb-2">
                                <i class="fa-solid fa-location-dot me-1 text-danger"></i>${area}, ${city}, Tanzania
                            </p>
                            <div class="detail ellipsis-container mt-2 d-flex flex-wrap gap-1">
                                <span class="badge bg-light text-slate-700 border border-slate-200"><i class="fa-solid fa-wifi me-1 text-success"></i>Free WiFi</span>
                                <span class="badge bg-light text-slate-700 border border-slate-200"><i class="fa-solid fa-snowflake me-1 text-info"></i>Air Conditioning</span>
                                <span class="badge bg-light text-slate-700 border border-slate-200"><i class="fa-solid fa-square-parking me-1 text-primary"></i>Free Parking</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-3 col-md-12 text-md-end border-start-md ps-md-3 pt-md-0 pt-3 border-top-sm">
                        <div class="h-100 d-flex flex-column justify-content-between align-items-md-end">
                            <div class="d-flex align-items-center justify-content-md-end gap-2 mb-2">
                                <div class="text-end">
                                    <div class="text-sm text-dark fw-bold">Superb</div>
                                    <div class="text-xs text-muted-2">${rating} score</div>
                                </div>
                                <div class="square--30 rounded-2 bg-success text-light fw-bold text-center lh-30 text-xs">${rating}</div>
                            </div>
                            <div class="mb-2">
                                <span class="text-xs text-muted d-block">Price per night</span>
                                <div class="text-dark fw-bold fs-5 text-orange-600">TZS ${Number(price).toLocaleString()}</div>
                            </div>
                            <div class="d-flex flex-column gap-2 w-100">
                                <a href="${detailUrl}" class="btn btn-primary btn-sm rounded-full fw-bold w-100 shadow-sm">
                                    Book This Stay <i class="fa-solid fa-arrow-right ms-1"></i>
                                </a>
                                <button type="button" onclick="removeFromWishlistPage(${id})" class="btn btn-outline-danger btn-sm rounded-full fw-medium w-100">
                                    <i class="fa-solid fa-trash-can me-1"></i>Remove
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>`;
    });

    listContainer.innerHTML = html;
}

async function removeFromWishlistPage(propertyId) {
    const card = document.getElementById('wishlist-item-' + propertyId);
    if (card) {
        card.style.opacity = '0.4';
    }

    let savedIds = getWishlistIds().filter(id => id !== parseInt(propertyId));
    saveWishlistIds(savedIds);

    // Call backend API if user is logged in
    const token = localStorage.getItem('auth_token');
    if (token) {
        try {
            await fetch('http://127.0.0.1:8000/api/wishlist/' + propertyId, {
                method: 'DELETE',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                }
            });
        } catch(e) {}
    }

    if (card) {
        card.remove();
    }

    // Refresh display
    renderWishlistPage();
}

document.addEventListener("DOMContentLoaded", function() {
    renderWishlistPage();
});
</script>