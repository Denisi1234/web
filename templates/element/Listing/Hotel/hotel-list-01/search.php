<style>
/* Trivago Filter Pills Bar */
.trivago-filter-bar {
    background-color: #ffffff;
    border-bottom: 1px solid #e5e7eb;
    padding: 10px 0;
    width: 100%;
}
.trivago-filter-scroll {
    display: flex;
    align-items: center;
    gap: 8px;
    overflow-x: auto;
    scrollbar-width: none;
    -ms-overflow-style: none;
    padding: 2px 4px;
}
.trivago-filter-scroll::-webkit-scrollbar {
    display: none;
}

.trivago-pill-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    border-radius: 9999px;
    border: 1px solid #d1d5db;
    background-color: #ffffff;
    color: #1e293b;
    font-size: 13.5px;
    font-weight: 500;
    text-decoration: none !important;
    white-space: nowrap;
    transition: all 0.15s ease;
    cursor: pointer;
}
.trivago-pill-btn:hover {
    border-color: #0f172a;
    background-color: #f8fafc;
    color: #0f172a;
}
.trivago-pill-btn.active {
    background-color: #0f172a;
    border-color: #0f172a;
    color: #ffffff;
}

.trivago-arrow-btn {
    width: 32px;
    height: 32px;
    min-width: 32px;
    border-radius: 50%;
    border: 1px solid #d1d5db;
    background: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #475569;
    cursor: pointer;
    font-size: 11px;
    transition: all 0.15s ease;
}
.trivago-arrow-btn:hover {
    background: #f1f5f9;
    color: #0f172a;
}
</style>

<?php
$queryParams = $queryParams ?? ($this->getRequest()->getQueryParams() ?? []);
$currentAmenities = [];
if (!empty($queryParams['amenities'])) {
    $currentAmenities = is_array($queryParams['amenities']) ? $queryParams['amenities'] : explode(',', (string)$queryParams['amenities']);
}
$currentAmenities = array_map('trim', $currentAmenities);

$toggleAmenityUrl = function(string $amenity) use ($queryParams, $currentAmenities): string {
    $newAm = $currentAmenities;
    $idx = array_search($amenity, $newAm, true);
    if ($idx !== false) {
        unset($newAm[$idx]);
    } else {
        $newAm[] = $amenity;
    }
    $params = $queryParams ?? [];
    if (!empty($newAm)) {
        $params['amenities'] = implode(',', $newAm);
    } else {
        unset($params['amenities']);
    }
    return '?' . http_build_query($params);
};
?>

<div class="trivago-filter-bar">
    <div class="container-fluid px-2 px-md-3 px-lg-4 max-w-[1440px] mx-auto">
        
        <!-- Mobile ONLY: 3-Segmented Action Bar (Matches Trivago Mobile Screenshot: Sort | Filter | Map) -->
        <div class="d-flex d-lg-none align-items-center border rounded-3 overflow-hidden bg-white mb-2.5 shadow-2xs text-center" style="height: 44px; border-color: #cbd5e1 !important;">
            <!-- 1. Sort -->
            <div class="dropdown flex-grow-1 h-100 border-end" style="border-color: #cbd5e1 !important;">
                <button type="button" class="btn btn-white w-100 h-100 border-0 rounded-0 fw-bold text-slate-800 d-flex align-items-center justify-content-center gap-1.5" style="font-size: 14px;" data-bs-toggle="dropdown">
                    <span>Sort</span>
                </button>
                <ul class="dropdown-menu shadow-sm border rounded-2 p-1 text-sm mt-1">
                    <li><a class="dropdown-item py-1.5 px-3 rounded <?= empty($queryParams['sort']) || $queryParams['sort'] === 'recommended' ? 'active' : '' ?>" href="?<?= http_build_query(array_merge($queryParams ?? [], ['sort' => 'recommended'])) ?>" onclick="triggerListingShimmer()">Our recommendations</a></li>
                    <li><a class="dropdown-item py-1.5 px-3 rounded <?= ($queryParams['sort'] ?? '') === 'price_low' ? 'active' : '' ?>" href="?<?= http_build_query(array_merge($queryParams ?? [], ['sort' => 'price_low'])) ?>" onclick="triggerListingShimmer()">Price: Low to High</a></li>
                    <li><a class="dropdown-item py-1.5 px-3 rounded <?= ($queryParams['sort'] ?? '') === 'rating_price' ? 'active' : '' ?>" href="?<?= http_build_query(array_merge($queryParams ?? [], ['sort' => 'rating_price'])) ?>" onclick="triggerListingShimmer()">Rating & Price</a></li>
                </ul>
            </div>

            <!-- 2. Filter -->
            <button type="button" class="btn btn-white flex-grow-1 h-100 border-0 border-end rounded-0 fw-bold text-slate-800 d-flex align-items-center justify-content-center gap-1.5" style="font-size: 14px; border-color: #cbd5e1 !important;" data-bs-toggle="modal" data-bs-target="#trivagoFiltersModal">
                <span>Filter <?= !empty($currentAmenities) ? '(' . count($currentAmenities) . ')' : '' ?></span>
            </button>

            <!-- 3. Map -->
            <button type="button" class="btn btn-white flex-grow-1 h-100 border-0 rounded-0 fw-bold text-slate-800 d-flex align-items-center justify-content-center gap-1.5" style="font-size: 14px;" onclick="toggleMobileListView()">
                <span>Map</span>
            </button>
        </div>

        <!-- Horizontal Filter Strip (Desktop pills preserved perfectly) -->
        <div class="d-flex align-items-center gap-2">
            <div class="trivago-filter-scroll flex-grow-1" id="trivago-filter-scroll">
                
                <!-- Desktop: All Filters Main Button -->
                <button type="button" class="trivago-pill-btn fw-bold d-none d-lg-inline-flex <?= !empty($currentAmenities) ? 'active' : '' ?>" data-bs-toggle="modal" data-bs-target="#trivagoFiltersModal">
                    <i class="fa-solid fa-sliders <?= !empty($currentAmenities) ? 'text-white' : 'text-slate-700' ?>"></i>
                    <span>All Filters <?= !empty($currentAmenities) ? '(' . count($currentAmenities) . ')' : '' ?></span>
                </button>

                <!-- Desktop: Sort by Dropdown -->
                <div class="dropdown d-none d-lg-inline-block">
                    <button type="button" class="trivago-pill-btn dropdown-toggle <?= (!empty($queryParams['sort']) && $queryParams['sort'] !== 'recommended') ? 'active' : '' ?>" data-bs-toggle="dropdown">
                        <span>Sort by</span>
                    </button>
                    <ul class="dropdown-menu shadow-sm border rounded-2 p-1 text-sm mt-1">
                        <li><a class="dropdown-item py-1.5 px-3 rounded <?= empty($queryParams['sort']) || $queryParams['sort'] === 'recommended' ? 'active' : '' ?>" href="?<?= http_build_query(array_merge($queryParams ?? [], ['sort' => 'recommended'])) ?>" onclick="triggerListingShimmer()">Our recommendations</a></li>
                        <li><a class="dropdown-item py-1.5 px-3 rounded <?= ($queryParams['sort'] ?? '') === 'price_low' ? 'active' : '' ?>" href="?<?= http_build_query(array_merge($queryParams ?? [], ['sort' => 'price_low'])) ?>" onclick="triggerListingShimmer()">Price: Low to High</a></li>
                        <li><a class="dropdown-item py-1.5 px-3 rounded <?= ($queryParams['sort'] ?? '') === 'rating_price' ? 'active' : '' ?>" href="?<?= http_build_query(array_merge($queryParams ?? [], ['sort' => 'rating_price'])) ?>" onclick="triggerListingShimmer()">Rating & Price</a></li>
                    </ul>
                </div>

                <!-- Desktop: Price Filter Dropdown -->
                <div class="dropdown d-none d-lg-inline-block">
                    <button type="button" class="trivago-pill-btn dropdown-toggle <?= (!empty($queryParams['min_price']) || !empty($queryParams['max_price'])) ? 'active' : '' ?>" data-bs-toggle="dropdown">
                        <span>Price</span>
                    </button>
                    <ul class="dropdown-menu shadow-sm border rounded-2 p-2 text-sm mt-1" style="min-width: 210px;">
                        <li><a class="dropdown-item py-1.5 px-3 rounded" href="?<?= http_build_query(array_merge($queryParams ?? [], ['max_price' => 100000, 'min_price' => ''])) ?>" onclick="triggerListingShimmer()">Under TZS 100,000</a></li>
                        <li><a class="dropdown-item py-1.5 px-3 rounded" href="?<?= http_build_query(array_merge($queryParams ?? [], ['min_price' => 100000, 'max_price' => 250000])) ?>" onclick="triggerListingShimmer()">TZS 100,000 - 250,000</a></li>
                        <li><a class="dropdown-item py-1.5 px-3 rounded" href="?<?= http_build_query(array_merge($queryParams ?? [], ['min_price' => 250000, 'max_price' => ''])) ?>" onclick="triggerListingShimmer()">TZS 250,000+</a></li>
                    </ul>
                </div>

                <!-- Wi-Fi -->
                <a href="<?= $toggleAmenityUrl('Wi-Fi') ?>" class="trivago-pill-btn <?= in_array('Wi-Fi', $currentAmenities, true) ? 'active' : '' ?>" onclick="triggerListingShimmer()">
                    <i class="fa-solid fa-wifi <?= in_array('Wi-Fi', $currentAmenities, true) ? 'text-white' : 'text-slate-600' ?>"></i>
                    <span>Wi-Fi</span>
                </a>

                <!-- Air Conditioning -->
                <a href="<?= $toggleAmenityUrl('Air conditioning') ?>" class="trivago-pill-btn <?= in_array('Air conditioning', $currentAmenities, true) ? 'active' : '' ?>" onclick="triggerListingShimmer()">
                    <i class="fa-solid fa-snowflake <?= in_array('Air conditioning', $currentAmenities, true) ? 'text-white' : 'text-slate-600' ?>"></i>
                    <span>Air conditioning</span>
                </a>

                <!-- Breakfast Included -->
                <a href="<?= $toggleAmenityUrl('Breakfast') ?>" class="trivago-pill-btn <?= in_array('Breakfast', $currentAmenities, true) ? 'active' : '' ?>" onclick="triggerListingShimmer()">
                    <i class="fa-solid fa-mug-saucer <?= in_array('Breakfast', $currentAmenities, true) ? 'text-white' : 'text-slate-600' ?>"></i>
                    <span>Breakfast included</span>
                </a>

                <!-- Swimming Pool -->
                <a href="<?= $toggleAmenityUrl('Swimming pool') ?>" class="trivago-pill-btn <?= in_array('Swimming pool', $currentAmenities, true) ? 'active' : '' ?>" onclick="triggerListingShimmer()">
                    <i class="fa-solid fa-person-swimming <?= in_array('Swimming pool', $currentAmenities, true) ? 'text-white' : 'text-slate-600' ?>"></i>
                    <span>Swimming pool</span>
                </a>

                <!-- Rating 8.0+ -->
                <?php $isRating8 = ($queryParams['rating'] ?? '') === '8.0'; ?>
                <a href="?<?= http_build_query(array_merge($queryParams ?? [], ['rating' => $isRating8 ? '' : '8.0'])) ?>" class="trivago-pill-btn <?= $isRating8 ? 'active' : '' ?>" onclick="triggerListingShimmer()">
                    <span>Rating: 8.0+</span>
                </a>

                <!-- Free Cancellation -->
                <?php $isFreeCancel = !empty($queryParams['free_cancellation']); ?>
                <a href="?<?= http_build_query(array_merge($queryParams ?? [], ['free_cancellation' => $isFreeCancel ? '' : '1'])) ?>" class="trivago-pill-btn <?= $isFreeCancel ? 'active' : '' ?>" onclick="triggerListingShimmer()">
                    <i class="fa-solid fa-check text-success"></i>
                    <span>Free cancellation</span>
                </a>
            </div>

            <!-- Scroll arrow right -->
            <button type="button" class="trivago-arrow-btn d-none d-md-flex" onclick="document.getElementById('trivago-filter-scroll').scrollBy({left: 200, behavior: 'smooth'})" aria-label="Scroll right">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        </div>

    </div>
</div>

<!-- Included Dedicated Filters & Amenities Modal -->
<?= $this->element('Listing/Hotel/hotel-list-01/filters-modal'); ?>

<script>
function triggerListingShimmer() {
    const cards = document.getElementById('hotel-cards-container');
    const shimmer = document.getElementById('hotel-shimmer-container');
    if (cards && shimmer) {
        cards.style.display = 'none';
        shimmer.style.display = 'block';
    }
}
</script>