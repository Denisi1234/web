<?php
$currentDest = $queryParams['destination'] ?? ($queryParams['q'] ?? 'Dodoma');
$propertyType = $queryParams['property_type'] ?? '';
$neighborhood = $queryParams['neighborhood'] ?? '';
?>

<style>
/* FastNet Stays results filter styling */
.agoda-sidebar-container {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

.agoda-filter-title {
    font-size: 15px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 12px;
    letter-spacing: -0.01em;
}

.agoda-checkbox-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 12px;
    cursor: pointer;
}

.agoda-custom-checkbox {
    width: 20px;
    height: 20px;
    min-width: 20px;
    border: 1.5px solid #64748b;
    border-radius: 3px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background-color: #ffffff;
    transition: all 0.15s ease;
    margin-top: 1px;
}

.agoda-checkbox-item input[type="checkbox"] {
    display: none;
}

.agoda-checkbox-item input[type="checkbox"]:checked + .agoda-custom-checkbox {
    background-color: #1877f2;
    border-color: #1877f2;
}

.agoda-checkbox-item input[type="checkbox"]:checked + .agoda-custom-checkbox::after {
    content: "✓";
    color: #ffffff;
    font-size: 13px;
    font-weight: bold;
    line-height: 1;
}

.agoda-checkbox-text {
    font-size: 14px;
    font-weight: 500;
    color: #1e293b;
    line-height: 1.35;
}

.agoda-checkbox-subtext {
    font-size: 12px;
    color: #64748b;
    display: block;
    margin-top: 2px;
}

.agoda-filter-section {
    padding-bottom: 20px;
    margin-bottom: 20px;
    border-bottom: 1px solid #e2e8f0;
}
</style>

<div class="col-xl-3 col-lg-4 col-md-12 agoda-sidebar-container">
    <div class="bg-white rounded-3 p-4 border border-slate-200 shadow-sm">
        
        <form method="GET" action="<?= $this->Url->build('/hotel-list-01'); ?>" id="sidebar-filters-form">
            <?php if (!empty($queryParams['destination'])): ?>
                <input type="hidden" name="destination" value="<?= h($queryParams['destination']) ?>">
            <?php endif; ?>
            <?php if (!empty($queryParams['checkIn'])): ?>
                <input type="hidden" name="checkIn" value="<?= h($queryParams['checkIn']) ?>">
            <?php endif; ?>
            <?php if (!empty($queryParams['checkOut'])): ?>
                <input type="hidden" name="checkOut" value="<?= h($queryParams['checkOut']) ?>">
            <?php endif; ?>
            <?php if (!empty($queryParams['adults'])): ?>
                <input type="hidden" name="adults" value="<?= h($queryParams['adults']) ?>">
            <?php endif; ?>
            <?php if (!empty($queryParams['rooms'])): ?>
                <input type="hidden" name="rooms" value="<?= h($queryParams['rooms']) ?>">
            <?php endif; ?>

            <!-- 1. Top Feature: Kids stay for free -->
            <div class="agoda-filter-section">
                <div class="text-slate-400 fw-bold mb-2">.</div>
                <label class="agoda-checkbox-item">
                    <input type="checkbox" name="kids_free" value="1" <?= !empty($queryParams['kids_free']) ? 'checked' : '' ?> onchange="this.form.submit()">
                    <span class="agoda-custom-checkbox"></span>
                    <span class="agoda-checkbox-text">Kids stay for free</span>
                </label>
            </div>

            <!-- 2. Property Type Section -->
            <div class="agoda-filter-section">
                <div class="agoda-filter-title">Property type</div>
                
                <label class="agoda-checkbox-item">
                    <input type="checkbox" name="type[]" value="entire_homes" <?= in_array('entire_homes', (array)($queryParams['type'] ?? [])) ? 'checked' : '' ?> onchange="this.form.submit()">
                    <span class="agoda-custom-checkbox"></span>
                    <span class="agoda-checkbox-text">Entire homes & apartments</span>
                </label>

                <label class="agoda-checkbox-item">
                    <input type="checkbox" name="type[]" value="apartment" <?= in_array('apartment', (array)($queryParams['type'] ?? [])) ? 'checked' : '' ?> onchange="this.form.submit()">
                    <span class="agoda-custom-checkbox"></span>
                    <span class="agoda-checkbox-text">Apartment/Flat</span>
                </label>

                <label class="agoda-checkbox-item">
                    <input type="checkbox" name="type[]" value="resort" <?= in_array('resort', (array)($queryParams['type'] ?? [])) ? 'checked' : '' ?> onchange="this.form.submit()">
                    <span class="agoda-custom-checkbox"></span>
                    <span class="agoda-checkbox-text">Resort</span>
                </label>

                <label class="agoda-checkbox-item">
                    <input type="checkbox" name="type[]" value="hotel" <?= in_array('hotel', (array)($queryParams['type'] ?? [])) ? 'checked' : '' ?> onchange="this.form.submit()">
                    <span class="agoda-custom-checkbox"></span>
                    <span class="agoda-checkbox-text">Hotel</span>
                </label>

                <label class="agoda-checkbox-item">
                    <input type="checkbox" name="type[]" value="entire_house" <?= in_array('entire_house', (array)($queryParams['type'] ?? [])) ? 'checked' : '' ?> onchange="this.form.submit()">
                    <span class="agoda-custom-checkbox"></span>
                    <span class="agoda-checkbox-text">Entire House</span>
                </label>

                <label class="agoda-checkbox-item">
                    <input type="checkbox" name="type[]" value="villa" <?= in_array('villa', (array)($queryParams['type'] ?? [])) ? 'checked' : '' ?> onchange="this.form.submit()">
                    <span class="agoda-custom-checkbox"></span>
                    <span class="agoda-checkbox-text">Villa</span>
                </label>
            </div>

            <!-- 3. Neighborhood Section -->
            <div class="agoda-filter-section border-bottom-0 pb-0 mb-0">
                <div class="agoda-filter-title">Neighborhood</div>

                <label class="agoda-checkbox-item">
                    <input type="checkbox" name="neighborhood[]" value="bavaro" <?= in_array('bavaro', (array)($queryParams['neighborhood'] ?? [])) ? 'checked' : '' ?> onchange="this.form.submit()">
                    <span class="agoda-custom-checkbox"></span>
                    <div>
                        <span class="agoda-checkbox-text">City and beach areas</span>
                        <span class="agoda-checkbox-subtext">Shopping, Luxury stay</span>
                    </div>
                </label>

                <label class="agoda-checkbox-item">
                    <input type="checkbox" name="neighborhood[]" value="pueblo_bavaro" <?= in_array('pueblo_bavaro', (array)($queryParams['neighborhood'] ?? [])) ? 'checked' : '' ?> onchange="this.form.submit()">
                    <span class="agoda-custom-checkbox"></span>
                    <span class="agoda-checkbox-text">Pueblo Bavaro</span>
                </label>

                <label class="agoda-checkbox-item">
                    <input type="checkbox" name="neighborhood[]" value="city_center" <?= in_array('city_center', (array)($queryParams['neighborhood'] ?? [])) ? 'checked' : '' ?> onchange="this.form.submit()">
                    <span class="agoda-custom-checkbox"></span>
                    <span class="agoda-checkbox-text">City Center</span>
                </label>

                <label class="agoda-checkbox-item">
                    <input type="checkbox" name="neighborhood[]" value="area_c" <?= in_array('area_c', (array)($queryParams['neighborhood'] ?? [])) ? 'checked' : '' ?> onchange="this.form.submit()">
                    <span class="agoda-custom-checkbox"></span>
                    <span class="agoda-checkbox-text">Area C / Kilimani</span>
                </label>
            </div>

        </form>

    </div>
</div>
