<?php
/**
 * Trivago Hero & Search Section — fastnetstays.com
 */
$destVal = $queryParams['destination'] ?? ($queryParams['q'] ?? '');
$checkInVal = $queryParams['checkIn'] ?? date('Y-m-d', strtotime('+7 days'));
$checkOutVal = $queryParams['checkOut'] ?? date('Y-m-d', strtotime('+12 days'));
$adultsVal = (int)($queryParams['adults'] ?? 2);
$childrenVal = (int)($queryParams['children'] ?? 0);
$roomsVal = (int)($queryParams['rooms'] ?? 1);

$dateFormatted = date('j M', strtotime($checkInVal)) . ' - ' . date('j M', strtotime($checkOutVal));
$guestFormatted = "{$adultsVal} Guests, {$roomsVal} Room" . ($roomsVal > 1 ? 's' : '');

$greetingName = !empty($userProfile['first_name']) 
    ? $userProfile['first_name'] 
    : (!empty($userProfile['name']) ? explode(' ', trim($userProfile['name']))[0] : '');
?>

<?= $this->Html->css('/assets/css/trivago-hero.css?v=' . filemtime(WWW_ROOT . 'assets/css/trivago-hero.css')); ?>
<?= $this->Html->css('/assets/css/search-spacing.css') ?>

<section class="trivago-hero-section">
    <div class="container" style="max-width: 1120px;">
        
        <!-- Hero Header -->
        <h1 class="trivago-hero-title">
            <?= !empty($greetingName) ? 'Ready to find a great hotel deal, ' . h($greetingName) . '?' : 'Ready to find your next hotel deal?' ?>
        </h1>
        <p class="trivago-hero-sub">Save up to 40% on your next stay in Tanzania & East Africa</p>

        <!-- Search Form -->
        <form action="<?= $this->Url->build('/hotel-list-01'); ?>" method="GET" autocomplete="off">
            <div class="trivago-hero-search-card">
                
                <!-- 1. Destination Pod -->
                <div class="trivago-hero-pod trivago-hero-pod--dest" id="trivago_hero_dest_pod" onclick="openHeroRecentDropdown(event)">
                    <i class="fa-solid fa-magnifying-glass fs-5 text-slate-800"></i>
                    <div class="flex-grow-1">
                        <div class="trivago-pod-label">Destination</div>
                        <input type="text" name="destination" id="trivago_home_dest" class="trivago-pod-input" value="<?= h($destVal) ?>" placeholder="Where are you going?" onfocus="openHeroRecentDropdown(event)" oninput="handleHeroDestInput(event)" onkeydown="handleHeroDestKeydown(event)" required autocomplete="off">
                    </div>
                    <button type="button" class="border-0 bg-transparent text-slate-400 p-0 fs-6" onclick="clearHeroDest(event)" aria-label="Clear destination"><i class="fa-solid fa-xmark"></i></button>

                    <input type="hidden" name="lat" id="hero_hidden_lat">
                    <input type="hidden" name="lng" id="hero_hidden_lng">

                    <!-- Places You Recently Searched / Mapbox Top Destinations Dropdown -->
                    <div class="trivago-recent-dropdown shadow-lg" id="hero_recent_dropdown">
                        <div class="trivago-recent-header d-flex align-items-center justify-content-between" id="hero_recent_header">
                            <span id="hero_recent_header_title">Popular Destinations in Tanzania</span>
                            <span class="badge bg-white text-slate-500 border text-2xs fw-normal d-none d-sm-inline">
                                <i class="fa-solid fa-location-crosshairs text-primary me-1"></i>Mapbox Geocoding
                            </span>
                        </div>
                        <div class="trivago-recent-list" id="hero_dest_suggestions_list">
                            <!-- Populated dynamically via Mapbox API & JS -->
                        </div>
                        <div class="trivago-recent-footer d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-1.5">
                                <i class="fa-solid fa-magnifying-glass text-primary" style="font-size: 11px;"></i>
                                <span class="text-slate-600 fw-medium" style="font-size: 11px;">Type 2+ letters for live city, beach & lodge suggestions</span>
                            </div>
                            <span class="text-2xs text-slate-400 fw-bold">Mapbox</span>
                        </div>
                    </div>
                </div>

                <!-- 2. Check-in/out Dates Pod -->
                <div class="trivago-hero-pod trivago-hero-pod--dates" id="trivago_hero_date_pod" onclick="openHeroDatePicker(event)">
                    <i class="fa-regular fa-calendar fs-5 text-slate-800"></i>
                    <div>
                        <div class="trivago-pod-label">Check-in/out</div>
                        <div class="trivago-pod-val" id="trivago_date_display"><?= h($dateFormatted) ?></div>
                    </div>
                    
                    <input type="hidden" name="checkIn" id="trivago_hidden_checkin" value="<?= h($checkInVal) ?>">
                    <input type="hidden" name="checkOut" id="trivago_hidden_checkout" value="<?= h($checkOutVal) ?>">

                    <!-- Trivago Dual-Month Datepicker Modal -->
                    <div class="trivago-datepicker-modal" id="hero_datepicker_modal" onclick="event.stopPropagation();">
                        <div class="trivago-cal-header-row">
                            <button type="button" class="trivago-cal-nav-btn" onclick="navHeroCal(-1)">
                                <i class="fa-solid fa-chevron-left"></i>
                            </button>
                            <div class="d-flex align-items-center justify-content-around flex-grow-1">
                                <div class="trivago-cal-month-title" id="hero_month1_title">October 2026</div>
                                <div class="trivago-cal-month-title d-none d-md-block" id="hero_month2_title">November 2026</div>
                            </div>
                            <button type="button" class="trivago-cal-nav-btn" onclick="navHeroCal(1)">
                                <i class="fa-solid fa-chevron-right"></i>
                            </button>
                        </div>

                        <div class="trivago-cal-grid-wrapper">
                            <!-- Month 1 -->
                            <div>
                                <div class="trivago-cal-weekdays">
                                    <span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
                                </div>
                                <div class="trivago-cal-days-grid" id="hero_month1_grid"></div>
                            </div>

                            <!-- Month 2 -->
                            <div class="d-none d-md-block">
                                <div class="trivago-cal-weekdays">
                                    <span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
                                </div>
                                <div class="trivago-cal-days-grid" id="hero_month2_grid"></div>
                            </div>
                        </div>

                        <!-- Shortcut Pills Row -->
                        <div class="trivago-cal-shortcuts">
                            <button type="button" class="trivago-shortcut-btn" onclick="applyCalShortcut('tonight')">
                                <i class="fa-regular fa-calendar"></i> Tonight
                            </button>
                            <button type="button" class="trivago-shortcut-btn" onclick="applyCalShortcut('tomorrow')">
                                <i class="fa-regular fa-calendar"></i> Tomorrow night
                            </button>
                            <button type="button" class="trivago-shortcut-btn" onclick="applyCalShortcut('this_weekend')">
                                <i class="fa-regular fa-calendar"></i> This weekend
                            </button>
                            <button type="button" class="trivago-shortcut-btn" onclick="applyCalShortcut('next_weekend')">
                                <i class="fa-regular fa-calendar"></i> Next weekend
                            </button>
                        </div>
                    </div>

                </div>

                <!-- 3. Guests and rooms Pod -->
                <div class="trivago-hero-pod trivago-hero-pod--guests" id="trivago_hero_guest_pod" onclick="toggleTrivagoGuestsDropdown(event)">
                    <i class="fa-solid fa-user-group fs-5 text-slate-800"></i>
                    <div>
                        <div class="trivago-pod-label">Guests and rooms</div>
                        <div class="trivago-pod-val" id="trivago_guest_display"><?= h($guestFormatted) ?></div>
                    </div>

                    <!-- Trivago Guests & Rooms Stepper Modal -->
                    <div class="trivago-guests-modal" id="trivago_guest_modal" onclick="event.stopPropagation();">
                        <!-- Adults -->
                        <div class="trivago-guest-row">
                            <div class="trivago-guest-label">Adults</div>
                            <div class="trivago-stepper-control">
                                <button type="button" class="trivago-round-btn <?= $adultsVal <= 1 ? 'disabled' : '' ?>" id="hero_adults_minus" onclick="updateGuestCount('adults', -1)" aria-label="Remove adult"><i class="fa-solid fa-minus"></i></button>
                                <div class="trivago-count-box" id="trivago_adults_val"><?= $adultsVal ?></div>
                                <button type="button" class="trivago-round-btn" id="hero_adults_plus" onclick="updateGuestCount('adults', 1)" aria-label="Add adult"><i class="fa-solid fa-plus"></i></button>
                            </div>
                        </div>

                        <!-- Children -->
                        <div class="trivago-guest-row">
                            <div class="trivago-guest-label">Children</div>
                            <div class="trivago-stepper-control">
                                <button type="button" class="trivago-round-btn <?= $childrenVal <= 0 ? 'disabled' : '' ?>" id="hero_children_minus" onclick="updateGuestCount('children', -1)" aria-label="Remove child"><i class="fa-solid fa-minus"></i></button>
                                <div class="trivago-count-box" id="trivago_children_val"><?= $childrenVal ?></div>
                                <button type="button" class="trivago-round-btn" id="hero_children_plus" onclick="updateGuestCount('children', 1)" aria-label="Add child"><i class="fa-solid fa-plus"></i></button>
                            </div>
                        </div>

                        <!-- Rooms -->
                        <div class="trivago-guest-row">
                            <div class="trivago-guest-label">Rooms</div>
                            <div class="trivago-stepper-control">
                                <button type="button" class="trivago-round-btn <?= $roomsVal <= 1 ? 'disabled' : '' ?>" id="hero_rooms_minus" onclick="updateGuestCount('rooms', -1)" aria-label="Remove room"><i class="fa-solid fa-minus"></i></button>
                                <div class="trivago-count-box" id="trivago_rooms_val"><?= $roomsVal ?></div>
                                <button type="button" class="trivago-round-btn" id="hero_rooms_plus" onclick="updateGuestCount('rooms', 1)" aria-label="Add room"><i class="fa-solid fa-plus"></i></button>
                            </div>
                        </div>

                        <hr class="trivago-guest-divider">

                        <!-- Pet-friendly Checkbox -->
                        <label class="trivago-pet-row" for="hero_pet_friendly">
                            <div>
                                <div class="trivago-pet-title">Pet-friendly</div>
                                <div class="trivago-pet-sub">Only show stays that allow pets</div>
                            </div>
                            <input type="checkbox" name="pets" value="1" id="hero_pet_friendly" class="trivago-pet-checkbox" onchange="updateResetBtnState()">
                        </label>

                        <!-- Footer Action Bar -->
                        <div class="trivago-guest-footer">
                            <button type="button" class="trivago-reset-btn" id="hero_guest_reset_btn" onclick="resetGuestCounts()">RESET</button>
                            <button type="button" class="trivago-apply-btn" onclick="applyGuestSelection()">Apply</button>
                        </div>
                    </div>

                    <input type="hidden" name="adults" id="trivago_hidden_adults" value="<?= $adultsVal ?>">
                    <input type="hidden" name="children" id="trivago_hidden_children" value="<?= $childrenVal ?>">
                    <input type="hidden" name="rooms" id="trivago_hidden_rooms" value="<?= $roomsVal ?>">
                </div>

                <!-- 4. Blue Search Button -->
                <button type="submit" class="trivago-hero-search-btn" onclick="if(typeof window.showHomeLoader==='function'){window.showHomeLoader();}">
                    <span>Search</span>
                </button>

            </div>
        </form>

    </div>
</section>

<?= $this->Html->script('/assets/js/trivago-hero.js?v=' . filemtime(WWW_ROOT . 'assets/js/trivago-hero.js')); ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    initTrivagoHero({
        adults: <?= (int)$adultsVal ?>,
        children: <?= (int)$childrenVal ?>,
        rooms: <?= (int)$roomsVal ?>,
        checkIn: "<?= h($checkInVal) ?>",
        checkOut: "<?= h($checkOutVal) ?>"
    });
});
</script>
