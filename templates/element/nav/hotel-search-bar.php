<?php
echo $this->Html->css('/assets/css/search-spacing.css');
?>
<style>
/* Hotel-detail search — desktop 90% correct, mobile deleted per request */
.nav-mobile-chip{display:none}
.nav-mobile-sheet{display:none}
@media(max-width:767px){
  #nav_search_wrapper{display:none !important}
  .nav-mobile-chip{display:none !important}
  .nav-mobile-sheet{display:none !important}
}
</style>
<?php
$today = date('Y-m-d');
$navCheckIn = !empty($queryParams['checkIn']) ? $queryParams['checkIn'] : date('Y-m-d', strtotime('+7 days'));
if (strtotime($navCheckIn) < strtotime($today)) {
    $navCheckIn = $today;
}
$navCheckOut = !empty($queryParams['checkOut']) ? $queryParams['checkOut'] : date('Y-m-d', strtotime($navCheckIn . ' +5 days'));
if (strtotime($navCheckOut) <= strtotime($navCheckIn)) {
    $navCheckOut = date('Y-m-d', strtotime($navCheckIn . ' +1 day'));
}
$navNights = max(1, (int)round((strtotime($navCheckOut) - strtotime($navCheckIn)) / 86400));
$navDest = $queryParams['destination'] ?? ($queryParams['q'] ?? 'Dar es Salaam');
?>
                    <!-- Trivago Integrated Search Pods in Header — consolidated to home (/) -->
                    <form action="<?= $this->Url->build('/'); ?>" method="GET" class="d-flex align-items-center flex-grow-1 mx-3 position-relative" style="max-width: 840px;" id="nav_search_form">
                        <input type="hidden" name="lat" id="nav_hidden_lat" value="<?= h($queryParams['lat'] ?? '') ?>">
                        <input type="hidden" name="lng" id="nav_hidden_lng" value="<?= h($queryParams['lng'] ?? '') ?>">
                        <div class="d-flex align-items-center border rounded-3 bg-white w-100 shadow-xs" id="nav_search_container" style="height: 46px; border-color: #cbd5e1 !important; transition: border 0.15s ease;">
                            <!-- 1. Destination -->
                            <div class="px-3 py-1 border-end position-relative d-flex align-items-center gap-2.5 flex-grow-1" id="nav_dest_pod" style="min-width: 200px; flex-grow: 1.3; cursor: text;" onclick="openNavRecentDropdown(event)">
                                <i class="fa-solid fa-magnifying-glass text-slate-700" style="font-size: 17px;"></i>
                                <div class="d-flex flex-column flex-grow-1 overflow-hidden">
                                    <span style="font-size: 10.5px; color: #64748b; font-weight: 600; line-height: 1.1;">Destination</span>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <input name="destination" id="nav_dest_input" type="text" class="border-0 p-0 fw-bold text-slate-900" style="font-size: 14px; outline: none; background: transparent; width: 100%;" value="<?= h($navDest) ?>" onfocus="openNavRecentDropdown(event)" oninput="handleNavDestInput(event)" onkeydown="handleNavDestKeydown(event)" autocomplete="off" placeholder="Where to? (e.g. Zanzibar, Arusha)">
                                        <button type="button" class="border-0 bg-transparent text-slate-400 p-0 ms-1" onclick="clearNavDest(event)" aria-label="Clear destination" style="font-size: 12px;"><i class="fa-solid fa-xmark"></i></button>
                                    </div>
                                </div>
                            </div>

                            <!-- 2. Check-in/out -->
                            <div class="px-3 py-1 border-end d-flex align-items-center gap-2.5 position-relative" id="nav_date_pod" style="min-width: 175px; flex-grow: 1; cursor: pointer;" onclick="openNavDatePicker(event)">
                                <i class="fa-regular fa-calendar text-slate-700" style="font-size: 17px;"></i>
                                <div class="d-flex flex-column overflow-hidden">
                                    <span style="font-size: 10.5px; color: #64748b; font-weight: 600; line-height: 1.1;">Check-in/out</span>
                                    <span class="fw-bold text-slate-900" id="nav_date_display" style="font-size: 13.5px; white-space: nowrap;">
                                        <?= date('j M', strtotime($navCheckIn)) ?> - <?= date('j M', strtotime($navCheckOut)) ?>
                                    </span>
                                </div>

                                <input type="hidden" name="checkIn" id="nav_hidden_checkin" value="<?= h($navCheckIn) ?>">
                                <input type="hidden" name="checkOut" id="nav_hidden_checkout" value="<?= h($navCheckOut) ?>">

                                <!-- Trivago Dual-Month Datepicker Modal (Header) -->
                                <div class="trivago-datepicker-modal" id="nav_datepicker_modal" onclick="event.stopPropagation();" style="left: -180px; width: 660px;">
                                    <div class="trivago-cal-header-row">
                                        <button type="button" class="trivago-cal-nav-btn" onclick="navNavCal(-1)">
                                            <i class="fa-solid fa-chevron-left"></i>
                                        </button>
                                        <div class="d-flex align-items-center justify-content-around flex-grow-1">
                                            <div class="trivago-cal-month-title" id="nav_month1_title">October 2026</div>
                                            <div class="trivago-cal-month-title d-none d-md-block" id="nav_month2_title">November 2026</div>
                                        </div>
                                        <button type="button" class="trivago-cal-nav-btn" onclick="navNavCal(1)">
                                            <i class="fa-solid fa-chevron-right"></i>
                                        </button>
                                    </div>

                                    <div class="trivago-cal-grid-wrapper">
                                        <!-- Month 1 -->
                                        <div>
                                            <div class="trivago-cal-weekdays">
                                                <span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
                                            </div>
                                            <div class="trivago-cal-days-grid" id="nav_month1_grid"></div>
                                        </div>

                                        <!-- Month 2 -->
                                        <div class="d-none d-md-block">
                                            <div class="trivago-cal-weekdays">
                                                <span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
                                            </div>
                                            <div class="trivago-cal-days-grid" id="nav_month2_grid"></div>
                                        </div>
                                    </div>

                                    <!-- Shortcut Pills Row -->
                                    <div class="trivago-cal-shortcuts">
                                        <button type="button" class="trivago-shortcut-btn" onclick="applyNavCalShortcut('tonight')">
                                            <i class="fa-regular fa-calendar"></i> Tonight
                                        </button>
                                        <button type="button" class="trivago-shortcut-btn" onclick="applyNavCalShortcut('tomorrow')">
                                            <i class="fa-regular fa-calendar"></i> Tomorrow night
                                        </button>
                                        <button type="button" class="trivago-shortcut-btn" onclick="applyNavCalShortcut('this_weekend')">
                                            <i class="fa-regular fa-calendar"></i> This weekend
                                        </button>
                                        <button type="button" class="trivago-shortcut-btn" onclick="applyNavCalShortcut('next_weekend')">
                                            <i class="fa-regular fa-calendar"></i> Next weekend
                                        </button>
                                    </div>
                                </div>

                            </div>

                            <!-- 3. Guests and rooms -->
                            <div class="px-3 py-1 d-flex align-items-center gap-2.5 position-relative flex-grow-1" id="nav_guest_pod" style="min-width: 165px; flex-grow: 1; cursor: pointer;" onclick="openNavGuestModal(event)">
                                <i class="fa-solid fa-user-group text-slate-700" style="font-size: 17px;"></i>
                                <div class="d-flex flex-column overflow-hidden">
                                    <span style="font-size: 10.5px; color: #64748b; font-weight: 600; line-height: 1.1;">Guests and rooms</span>
                                    <span class="fw-bold text-slate-900" id="nav_guest_display" style="font-size: 13.5px; white-space: nowrap;">
                                        <?= (int)($queryParams['adults'] ?? 2) ?> Guests, <?= (int)($queryParams['rooms'] ?? 1) ?> Room
                                    </span>
                                </div>

                                <input type="hidden" name="adults" id="nav_hidden_adults" value="<?= (int)($queryParams['adults'] ?? 2) ?>">
                                <input type="hidden" name="children" id="nav_hidden_children" value="<?= (int)($queryParams['children'] ?? 0) ?>">
                                <input type="hidden" name="rooms" id="nav_hidden_rooms" value="<?= (int)($queryParams['rooms'] ?? 1) ?>">

                                <!-- Trivago Guests & Rooms Stepper Modal (Header) -->
                                <div class="trivago-guests-modal" id="nav_guest_modal" onclick="event.stopPropagation();" style="right: 0; width: 340px;">
                                    <!-- Adults -->
                                    <div class="trivago-guest-row">
                                        <div class="trivago-guest-label">Adults</div>
                                        <div class="trivago-stepper-control">
                                            <button type="button" class="trivago-round-btn <?= (int)($queryParams['adults'] ?? 2) <= 1 ? 'disabled' : '' ?>" id="nav_adults_minus" onclick="updateNavGuestCount('adults', -1)" aria-label="Remove adult"><i class="fa-solid fa-minus"></i></button>
                                            <div class="trivago-count-box" id="nav_adults_val"><?= (int)($queryParams['adults'] ?? 2) ?></div>
                                            <button type="button" class="trivago-round-btn" id="nav_adults_plus" onclick="updateNavGuestCount('adults', 1)" aria-label="Add adult"><i class="fa-solid fa-plus"></i></button>
                                        </div>
                                    </div>

                                    <!-- Children -->
                                    <div class="trivago-guest-row">
                                        <div class="trivago-guest-label">Children</div>
                                        <div class="trivago-stepper-control">
                                            <button type="button" class="trivago-round-btn <?= (int)($queryParams['children'] ?? 0) <= 0 ? 'disabled' : '' ?>" id="nav_children_minus" onclick="updateNavGuestCount('children', -1)" aria-label="Remove child"><i class="fa-solid fa-minus"></i></button>
                                            <div class="trivago-count-box" id="nav_children_val"><?= (int)($queryParams['children'] ?? 0) ?></div>
                                            <button type="button" class="trivago-round-btn" id="nav_children_plus" onclick="updateNavGuestCount('children', 1)" aria-label="Add child"><i class="fa-solid fa-plus"></i></button>
                                        </div>
                                    </div>

                                    <!-- Rooms -->
                                    <div class="trivago-guest-row">
                                        <div class="trivago-guest-label">Rooms</div>
                                        <div class="trivago-stepper-control">
                                            <button type="button" class="trivago-round-btn <?= (int)($queryParams['rooms'] ?? 1) <= 1 ? 'disabled' : '' ?>" id="nav_rooms_minus" onclick="updateNavGuestCount('rooms', -1)" aria-label="Remove room"><i class="fa-solid fa-minus"></i></button>
                                            <div class="trivago-count-box" id="nav_rooms_val"><?= (int)($queryParams['rooms'] ?? 1) ?></div>
                                            <button type="button" class="trivago-round-btn" id="nav_rooms_plus" onclick="updateNavGuestCount('rooms', 1)" aria-label="Add room"><i class="fa-solid fa-plus"></i></button>
                                        </div>
                                    </div>

                                    <hr class="trivago-guest-divider">

                                    <!-- Pet-friendly Checkbox -->
                                    <label class="trivago-pet-row" for="nav_pet_friendly">
                                        <div>
                                            <div class="trivago-pet-title">Pet-friendly</div>
                                            <div class="trivago-pet-sub">Only show stays that allow pets</div>
                                        </div>
                                        <input type="checkbox" name="pets" value="1" id="nav_pet_friendly" class="trivago-pet-checkbox" onchange="updateNavResetBtnState()">
                                    </label>

                                    <!-- Footer Action Bar -->
                                    <div class="trivago-guest-footer">
                                        <button type="button" class="trivago-reset-btn" id="nav_guest_reset_btn" onclick="resetNavGuestCounts()">RESET</button>
                                        <button type="button" class="trivago-apply-btn" onclick="applyNavGuestSelection()">Apply</button>
                                    </div>
                                </div>
                            </div>

                            <!-- 4. Blue Search Button (Desktop Only) -->
                            <button type="submit" class="btn btn-primary d-none d-lg-flex align-items-center justify-content-center m-1 rounded-2" style="background-color: #007fad; border-color: #007fad; width: 40px; height: 38px;">
                                <i class="fa-solid fa-magnifying-glass text-white fs-6"></i>
                            </button>
                        </div>

                        <!-- Mapbox Auto-Suggestion / Recent Destinations Dropdown -->
                        <div class="trivago-recent-dropdown shadow-lg" id="nav_recent_dropdown">
                            <div class="trivago-recent-header d-flex align-items-center justify-content-between" id="nav_recent_header">
                                <span id="nav_recent_header_title">Popular Destinations in Tanzania</span>
                                <span class="badge bg-white text-slate-500 border text-2xs fw-normal d-none d-sm-inline">
                                    <i class="fa-solid fa-location-crosshairs text-primary me-1"></i>Mapbox Geocoding
                                </span>
                            </div>
                            <div class="trivago-recent-list" id="nav_dest_suggestions_list">
                                <!-- Dynamic items loaded via Mapbox API and JS -->
                            </div>
                            <div class="trivago-recent-footer d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-1.5">
                                    <i class="fa-solid fa-magnifying-glass text-primary" style="font-size: 11px;"></i>
                                    <span class="text-slate-600 fw-medium" style="font-size: 11px;">Type 2+ letters for live city, beach & lodge suggestions</span>
                                </div>
                                <span class="text-2xs text-slate-400 fw-bold">Mapbox</span>
                            </div>
                        </div>
                    </form>
                    <!-- Mobile too-clean chip (replaces 3-pod row) -->
                    <div class="nav-mobile-chip" id="nav_mobile_chip" role="button" tabindex="0" aria-label="Open search" onclick="openNavMobileSheet()" onkeydown="if(event.key==='Enter'||event.key===' ') {event.preventDefault(); openNavMobileSheet();}">
                        <span class="nav-mobile-chip-icon" aria-hidden="true"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <span class="nav-mobile-chip-text" id="nav_chip_text"><?= h($navDest) ?> • <?= date('j M', strtotime($navCheckIn)) ?>–<?= date('j M', strtotime($navCheckOut)) ?> • <?= (int)($queryParams['adults'] ?? 2) ?> guests</span>
                        <span class="nav-mobile-chip-chevron" aria-hidden="true"><i class="fa-solid fa-sliders"></i></span>
                    </div>
                    <!-- Mobile bottom sheet -->
                    <div class="nav-mobile-sheet" id="nav_mobile_sheet" role="dialog" aria-modal="true" aria-label="Search stays" onclick="if(event.target===this) closeNavMobileSheet()">
                        <div class="nav-mobile-sheet-inner">
                            <div class="sheet-drag" aria-hidden="true"><span></span></div>
                            <div class="sheet-header">
                                <span class="sheet-header-title">Search stays</span>
                                <button type="button" onclick="closeNavMobileSheet()" aria-label="Close search" style="width:36px;height:36px;border-radius:50%;border:1px solid #E5E7EB;background:#fff;display:flex;align-items:center;justify-content:center;"><i class="fa-solid fa-xmark"></i></button>
                            </div>
                            <div class="sheet-body" id="nav_sheet_body"></div>
                        </div>
                    </div>
                    <script>
                    function openNavMobileSheet(){ var s=document.getElementById('nav_mobile_sheet'); var c=document.getElementById('nav_search_container'); var b=document.getElementById('nav_sheet_body'); if(c&&b&&!b.contains(c)){ b.appendChild(c); c.style.display='flex'; } if(s) s.classList.add('open'); document.body.style.overflow='hidden'; }
                    function closeNavMobileSheet(){ var s=document.getElementById('nav_mobile_sheet'); var c=document.getElementById('nav_search_container'); var f=document.getElementById('nav_search_form'); if(c&&f){ if(!f.contains(c)) f.appendChild(c); c.style.display=''; } if(s) s.classList.remove('open'); document.body.style.overflow=''; }
                    // keep chip text in sync
                    document.addEventListener('DOMContentLoaded', function(){
                      var d=document.getElementById('nav_dest_input'), chip=document.getElementById('nav_chip_text');
                      if(d&&chip){ d.addEventListener('input', function(){ chip.textContent=(d.value||'Where to?')+' • '+ (document.getElementById('nav_date_display')?.textContent||'')+' • '+(document.getElementById('nav_guest_display')?.textContent||''); }); }
                    });
                    </script>

                    <?= $this->Html->script('/assets/js/nav-search.js?v=' . filemtime(WWW_ROOT . 'assets/js/nav-search.js')); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initNavSearch({
        adults: <?= (int)($queryParams['adults'] ?? 2) ?>,
        children: <?= (int)($queryParams['children'] ?? 0) ?>,
        rooms: <?= (int)($queryParams['rooms'] ?? 1) ?>,
        checkIn: "<?= h($navCheckIn) ?>",
        checkOut: "<?= h($navCheckOut) ?>"
    });
});
</script>
