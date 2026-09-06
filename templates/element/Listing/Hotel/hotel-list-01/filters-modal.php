<?php
/**
 * fastnetstays.com - Dedicated Amenities & Search Filters Modal
 * Allows filtering stays dynamically by all real amenities, price range, rating, and cancellation policy.
 */

$selectedAmenities = [];
if (!empty($queryParams['amenities'])) {
    $selectedAmenities = is_array($queryParams['amenities']) ? $queryParams['amenities'] : explode(',', (string)$queryParams['amenities']);
}
$selectedAmenities = array_map('trim', $selectedAmenities);

$minPrice = $queryParams['min_price'] ?? '';
$maxPrice = $queryParams['max_price'] ?? '';
$selectedRating = $queryParams['rating'] ?? '';
$freeCancel = !empty($queryParams['free_cancellation']);

$amenityOptions = [
    ['key' => 'Wi-Fi', 'label' => 'High-Speed Wi-Fi', 'icon' => 'fa-wifi'],
    ['key' => 'Air conditioning', 'label' => 'Air Conditioning', 'icon' => 'fa-snowflake'],
    ['key' => 'Swimming pool', 'label' => 'Swimming Pool', 'icon' => 'fa-person-swimming'],
    ['key' => 'Breakfast', 'label' => 'Breakfast Included', 'icon' => 'fa-mug-saucer'],
    ['key' => 'Free Parking', 'label' => 'Free On-Site Parking', 'icon' => 'fa-square-parking'],
    ['key' => 'Balcony', 'label' => 'Balcony / Terrace', 'icon' => 'fa-mountain-sun'],
    ['key' => 'Bar / Lounge', 'label' => 'Bar / Lounge', 'icon' => 'fa-martini-glass'],
    ['key' => '24/7 Front desk', 'label' => '24/7 Front Desk', 'icon' => 'fa-bell-concierge'],
    ['key' => 'Private Bathroom', 'label' => 'Private Bathroom', 'icon' => 'fa-bath'],
    ['key' => 'LED TV', 'label' => 'LED Smart TV', 'icon' => 'fa-tv'],
    ['key' => 'Pet friendly', 'label' => 'Pet Friendly', 'icon' => 'fa-paw'],
    ['key' => 'Laundry service', 'label' => 'Laundry Service', 'icon' => 'fa-shirt'],
];
?>

<!-- Trivago Filters & Amenities Modal -->
<div class="modal fade" id="trivagoFiltersModal" tabindex="-1" aria-labelledby="trivagoFiltersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            
            <!-- Modal Header -->
            <div class="modal-header border-bottom px-4 py-3 bg-white">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-sliders text-primary fs-5"></i>
                    <h5 class="modal-title fw-bold text-slate-900" id="trivagoFiltersModalLabel">Filter Stays & Amenities</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Form -->
            <form action="<?= $this->Url->build('/hotel-list-01'); ?>" method="GET" id="amenities_filter_form" onsubmit="triggerListingShimmer()">
                <!-- Preserve existing search parameters -->
                <input type="hidden" name="destination" value="<?= h($queryParams['destination'] ?? 'Dar es Salaam') ?>">
                <input type="hidden" name="checkIn" value="<?= h($queryParams['checkIn'] ?? '') ?>">
                <input type="hidden" name="checkOut" value="<?= h($queryParams['checkOut'] ?? '') ?>">
                <input type="hidden" name="adults" value="<?= (int)($queryParams['adults'] ?? 2) ?>">
                <input type="hidden" name="children" value="<?= (int)($queryParams['children'] ?? 0) ?>">
                <input type="hidden" name="rooms" value="<?= (int)($queryParams['rooms'] ?? 1) ?>">

                <div class="modal-body px-4 py-3">
                    
                    <!-- 1. Popular Amenities Grid -->
                    <div class="mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h6 class="fw-bold text-slate-900 mb-0">Popular Amenities</h6>
                            <span class="text-xs text-slate-500">Select all that apply</span>
                        </div>
                        <div class="row g-2">
                            <?php foreach ($amenityOptions as $opt): ?>
                                <?php 
                                    $isChecked = in_array($opt['key'], $selectedAmenities, true) || in_array(strtolower($opt['key']), array_map('strtolower', $selectedAmenities), true);
                                ?>
                                <div class="col-md-4 col-6">
                                    <label class="d-flex align-items-center gap-2 p-2 border rounded-3 cursor-pointer bg-white hover:bg-slate-50 transition-all h-100" style="font-size: 13px;">
                                        <input type="checkbox" name="amenities[]" value="<?= h($opt['key']) ?>" class="form-check-input mt-0" <?= $isChecked ? 'checked' : '' ?>>
                                        <i class="fa-solid <?= $opt['icon'] ?> text-primary" style="font-size: 13px; width: 16px; text-align: center;"></i>
                                        <span class="text-slate-800 fw-medium"><?= h($opt['label']) ?></span>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- 2. Price Range -->
                    <div class="mb-4 pb-3 border-bottom">
                        <h6 class="fw-bold text-slate-900 mb-2">Price per night (TZS / USD)</h6>
                        <div class="row g-3 align-items-center">
                            <div class="col-6">
                                <label class="text-xs text-slate-500 mb-1 d-block">Minimum Price</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light border-slate-300">Min</span>
                                    <input type="number" name="min_price" class="form-control" placeholder="0" value="<?= h($minPrice) ?>" min="0">
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="text-xs text-slate-500 mb-1 d-block">Maximum Price</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light border-slate-300">Max</span>
                                    <input type="number" name="max_price" class="form-control" placeholder="Any" value="<?= h($maxPrice) ?>" min="0">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Guest Rating -->
                    <div class="mb-4 pb-3 border-bottom">
                        <h6 class="fw-bold text-slate-900 mb-2">Guest Rating</h6>
                        <div class="d-flex gap-2 flex-wrap">
                            <label class="btn btn-outline-secondary btn-sm rounded-pill px-3 <?= $selectedRating === '8.5' ? 'active' : '' ?>">
                                <input type="radio" name="rating" value="8.5" class="d-none" <?= $selectedRating === '8.5' ? 'checked' : '' ?>>
                                <strong>8.5+</strong> Exceptional
                            </label>
                            <label class="btn btn-outline-secondary btn-sm rounded-pill px-3 <?= $selectedRating === '8.0' ? 'active' : '' ?>">
                                <input type="radio" name="rating" value="8.0" class="d-none" <?= $selectedRating === '8.0' ? 'checked' : '' ?>>
                                <strong>8.0+</strong> Very good
                            </label>
                            <label class="btn btn-outline-secondary btn-sm rounded-pill px-3 <?= $selectedRating === '7.0' ? 'active' : '' ?>">
                                <input type="radio" name="rating" value="7.0" class="d-none" <?= $selectedRating === '7.0' ? 'checked' : '' ?>>
                                <strong>7.0+</strong> Good
                            </label>
                        </div>
                    </div>

                    <!-- 4. Cancellation Policy -->
                    <div>
                        <h6 class="fw-bold text-slate-900 mb-2">Booking Flexibility</h6>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="free_cancellation" value="1" id="free_cancel_check" <?= $freeCancel ? 'checked' : '' ?>>
                            <label class="form-check-label text-slate-800 fw-medium" for="free_cancel_check">
                                Free cancellation only
                            </label>
                            <div class="text-xs text-slate-500">Show only stays offering free cancellation with no booking fees</div>
                        </div>
                    </div>

                </div>

                <!-- Modal Footer -->
                <div class="modal-footer border-top px-4 py-3 bg-light d-flex justify-content-between">
                    <a href="<?= $this->Url->build('/hotel-list-01?destination=' . urlencode($queryParams['destination'] ?? 'Dar es Salaam')); ?>" class="btn btn-link text-slate-600 text-decoration-none fw-semibold p-0">
                        Clear all filters
                    </a>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary px-3 py-2 fw-semibold rounded-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4 py-2 fw-bold rounded-2">Apply Filters</button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>
