<?php
/**
 * fastnetstays.com - Dedicated Property Amenities Component
 * Dynamically parses, categorizes, and displays all property and room amenities.
 */

$rawAmenities = $property['amenities'] ?? [];
if (is_string($rawAmenities)) {
    $decoded = json_decode($rawAmenities, true);
    $allAmenities = is_array($decoded) ? $decoded : array_filter(array_map('trim', explode(',', $rawAmenities)));
} elseif (is_array($rawAmenities)) {
    $allAmenities = $rawAmenities;
} else {
    $allAmenities = [];
}

// Harvest additional room amenities if available
if (!empty($rooms) && is_array($rooms)) {
    foreach ($rooms as $r) {
        if (!empty($r['amenities'])) {
            $rAm = is_string($r['amenities']) ? json_decode($r['amenities'], true) : $r['amenities'];
            if (!is_array($rAm) && is_string($r['amenities'])) {
                $rAm = array_filter(array_map('trim', explode(',', $r['amenities'])));
            }
            if (is_array($rAm)) {
                foreach ($rAm as $amItem) {
                    $amName = is_array($amItem) ? ($amItem['name'] ?? '') : $amItem;
                    if ($amName !== '' && !in_array($amName, $allAmenities, true)) {
                        $allAmenities[] = $amName;
                    }
                }
            }
        }
    }
}

// Categorization helper
$categorized = [
    'Popular Facilities' => [],
    'Room Comfort' => [],
    'Bathroom & Hygiene' => [],
    'Services & Security' => []
];

foreach ($allAmenities as $item) {
    $lower = strtolower($item);
    if (str_contains($lower, 'wifi') || str_contains($lower, 'wi-fi') || str_contains($lower, 'pool') || str_contains($lower, 'parking') || str_contains($lower, 'breakfast')) {
        $categorized['Popular Facilities'][] = $item;
    } elseif (str_contains($lower, 'bath') || str_contains($lower, 'shower') || str_contains($lower, 'toilet') || str_contains($lower, 'towel')) {
        $categorized['Bathroom & Hygiene'][] = $item;
    } elseif (str_contains($lower, 'front desk') || str_contains($lower, 'security') || str_contains($lower, 'laundry') || str_contains($lower, 'shuttle')) {
        $categorized['Services & Security'][] = $item;
    } else {
        $categorized['Room Comfort'][] = $item;
    }
}

function getAmenityIcon(string $name): string {
    $l = strtolower($name);
    if (str_contains($l, 'wifi') || str_contains($l, 'wi-fi')) return 'fa-wifi text-success';
    if (str_contains($l, 'air') || str_contains($l, 'ac')) return 'fa-snowflake text-info';
    if (str_contains($l, 'parking')) return 'fa-square-parking text-primary';
    if (str_contains($l, 'breakfast') || str_contains($l, 'tea') || str_contains($l, 'coffee')) return 'fa-mug-saucer text-warning';
    if (str_contains($l, 'pool')) return 'fa-person-swimming text-info';
    if (str_contains($l, 'tv')) return 'fa-tv text-secondary';
    if (str_contains($l, 'bath') || str_contains($l, 'shower')) return 'fa-bath text-primary';
    if (str_contains($l, 'bar') || str_contains($l, 'lounge')) return 'fa-martini-glass text-danger';
    if (str_contains($l, 'security')) return 'fa-shield-halved text-success';
    if (str_contains($l, 'front') || str_contains($l, 'desk')) return 'fa-bell-concierge text-primary';
    if (str_contains($l, 'balcony')) return 'fa-mountain-sun text-warning';
    if (str_contains($l, 'laundry')) return 'fa-shirt text-info';
    return 'fa-circle-check text-success';
}
?>

<?php if (!empty($allAmenities)): ?><div class="card mb-4 border-0 p-4 shadow-sm rounded-3">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h4 class="fs-5 fw-bold text-slate-900 mb-0">
            <i class="fa-solid fa-list-check me-2 text-primary"></i>Property Amenities & Facilities
        </h4>
        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#allAmenitiesModal">
            View all (<?= count($allAmenities) ?>)
        </button>
    </div>

    <!-- Quick Preview Grid (Top 8 Amenities) -->
    <div class="row g-3">
        <?php foreach (array_slice($allAmenities, 0, 8) as $am): ?>
            <div class="col-md-3 col-6">
                <span class="d-flex align-items-center text-slate-700 fs-6">
                    <i class="fa-solid <?= getAmenityIcon($am) ?> me-2 fs-5"></i>
                    <span><?= h($am) ?></span>
                </span>
            </div>
        <?php endforeach; ?>
    </div>
</div><?php else: ?><div class="alert alert-light border text-muted" role="status">Amenities are not available for this property.</div><?php endif; ?>

<!-- Dedicated All Amenities Modal -->
<div class="modal fade" id="allAmenitiesModal" tabindex="-1" aria-labelledby="allAmenitiesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-bottom px-4 py-3 bg-white">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-hotel text-primary fs-5"></i>
                    <h5 class="modal-title fw-bold text-slate-900" id="allAmenitiesModalLabel">
                        All Facilities & Amenities at <?= h($property['name'] ?? 'Lodge') ?>
                    </h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 py-4">
                <div class="row g-4">
                    <?php foreach ($categorized as $categoryName => $items): ?>
                        <?php if (!empty($items)): ?>
                            <div class="col-md-6">
                                <h6 class="fw-bold text-slate-900 pb-2 border-bottom mb-3"><?= h($categoryName) ?></h6>
                                <ul class="list-unstyled mb-0">
                                    <?php foreach ($items as $itm): ?>
                                        <li class="d-flex align-items-center gap-2 mb-2.5 text-slate-700" style="font-size: 14px;">
                                            <i class="fa-solid <?= getAmenityIcon($itm) ?>" style="font-size: 15px; width: 20px; text-align: center;"></i>
                                            <span><?= h($itm) ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="modal-footer border-top px-4 py-3 bg-light">
                <button type="button" class="btn btn-primary fw-bold px-4 py-2 rounded-2" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
