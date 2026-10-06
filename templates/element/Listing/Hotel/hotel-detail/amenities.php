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

// Use closure to avoid global function redeclaration when element is included twice (About + Modal)
$getAmenityIcon = function(string $name): string {
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
};

// Full-list mode for the Details modal: every amenity, grouped, no preview,
// no nested modal (Bootstrap modals must never stack).
if (!empty($fullList)):
    if (empty($allAmenities)): ?>
<div style="font-size:13.5px;color:#5f6368;">Amenities are not available for this property.</div>
    <?php return; endif; ?>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px 12px;">
    <?php foreach ($categorized as $categoryName => $items): ?>
    <?php if (empty($items)) continue; ?>
    <div style="min-width:0">
        <div style="font-size:12px;font-weight:700;color:#5f6368;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;padding-bottom:6px;border-bottom:1px solid #e8eaed;"><?= h($categoryName) ?></div>
        <div style="display:flex;flex-direction:column;gap:8px;">
            <?php foreach ($items as $itm): ?>
            <div style="display:flex;align-items:center;gap:10px;font-size:13.5px;color:#3c4043;"><i class="fa-solid <?= $getAmenityIcon($itm) ?>" style="font-size:14px;width:20px;text-align:center;"></i><span><?= h($itm) ?></span></div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php return; endif; ?>

<?php if (!empty($allAmenities)): ?><div style="margin-top:16px;padding-top:16px;border-top:1px solid #e8eaed;">
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <h4 class="fs-6 fw-medium mb-0" style="color:#161616;font-family:'IBM Plex Sans','Inter',Roboto,sans-serif;font-weight:500;">
            <i class="fa-solid fa-list-check me-2" style="color:#0f62fe;"></i>Popular amenities
        </h4>
        <button type="button" class="btn btn-sm rounded-pill px-3 fw-medium" data-bs-toggle="modal" data-bs-target="#allAmenitiesModal" style="border:1px solid #dadce0;background:#fff;color:#0f62fe;font-family:'IBM Plex Sans','Inter',Roboto,sans-serif;">
            View all <?= count($allAmenities) ?>
        </button>
    </div>

    <!-- Quick Preview Grid (Top 8 Amenities) — Google 4-col, now integrated not separated -->
    <div class="row g-3">
        <?php foreach (array_slice($allAmenities, 0, 8) as $am): ?>
            <div class="col-md-3 col-6">
                <span class="d-flex align-items-center" style="color:#3c4043;font-size:13px;font-family:Roboto,sans-serif;">
                    <i class="fa-solid <?= $getAmenityIcon($am) ?> me-2" style="font-size:14px;width:18px;text-align:center;"></i>
                    <span><?= h($am) ?></span>
                </span>
            </div>
        <?php endforeach; ?>
    </div>
</div><?php else: ?><div class="alert alert-light border text-muted mt-3" role="status" style="border-radius:12px;border-color:#dadce0;">Amenities are not available for this property.</div><?php endif; ?>

<!-- Dedicated All Amenities Modal -->
<div class="modal fade" id="allAmenitiesModal" tabindex="-1" aria-labelledby="allAmenitiesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="border:1px solid #dadce0;">
            <div class="modal-header px-4 py-3 bg-white" style="border-bottom:1px solid #e8eaed;">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-hotel fs-5" style="color:#0f62fe;"></i>
                    <h5 class="modal-title fw-medium" id="allAmenitiesModalLabel" style="color:#161616;font-family:'IBM Plex Sans','Inter',Roboto,sans-serif;">
                        All amenities · <?= h($property['name'] ?? 'Lodge') ?>
                    </h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 py-4" style="background:#fff;">
                <div class="row g-4">
                    <?php foreach ($categorized as $categoryName => $items): ?>
                        <?php if (!empty($items)): ?>
                            <div class="col-md-6">
                                <h6 class="fw-medium pb-2 mb-3" style="color:#161616;font-family:'IBM Plex Sans','Inter',Roboto,sans-serif;border-bottom:1px solid #e8eaed;"><?= h($categoryName) ?></h6>
                                <ul class="list-unstyled mb-0">
                                    <?php foreach ($items as $itm): ?>
                                        <li class="d-flex align-items-center gap-2 mb-2" style="color:#3c4043;font-size:13px;font-family:Roboto,sans-serif;">
                                            <i class="fa-solid <?= $getAmenityIcon($itm) ?>" style="font-size:14px; width:20px; text-align:center;"></i>
                                            <span><?= h($itm) ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="modal-footer px-4 py-3" style="background:#f8f9fa;border-top:1px solid #e8eaed;">
                <button type="button" class="btn fw-medium px-4 py-2 rounded-pill" data-bs-dismiss="modal" style="background:#0f62fe;color:#fff;border:none;">Done</button>
            </div>
        </div>
    </div>
</div>
