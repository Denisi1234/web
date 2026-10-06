<?php
$totalCount = $totalCount ?? count($properties ?? []);
$apiCount = $totalCount;
/**
 * FastNet Stays — Enterprise Search Bar (Spec Authoritative)
 * - Desktop >=992px: Unified pill with vertical dividers, #E5E7EB border, 0 4px 20px rgba(0,0,0,.08) -> hover 0 8px 30px rgba(0,0,0,.12)
 * - Palette: #1A73E8 primary / #2563EB active border / #FFFFFF bg / #1F2937 text / #6B7280 secondary
 * - Typography: Inter / Google Sans 400/500/600
 * - Segments: Where to? | Check-in | Check-out (badge nights) | Guests & Rooms | Search CTA
 * - Mobile <992px: floating chip + bottom-sheet stepped modal
 * - a11y: Tab/Shift+Tab, Enter/Space open, Escape close, ArrowUp/Down suggestions, aria
 * - Defensive: 1-night min, past dates disabled, empty city -> All Tanzanian Destinations, skeleton trigger
 */
$today     = date('Y-m-d');
$checkIn   = $queryParams['checkin'] ?? $queryParams['checkIn'] ?? date('Y-m-d', strtotime('+7 days'));
$checkOut  = $queryParams['checkout'] ?? $queryParams['checkOut'] ?? date('Y-m-d', strtotime($checkIn . ' +1 day'));
$adults    = (int)($queryParams['adults']   ?? 2);
$children  = (int)($queryParams['children'] ?? 0);
$rooms     = (int)($queryParams['rooms']    ?? 1);
$destVal   = $queryParams['city'] ?? $queryParams['destination'] ?? 'Dar es Salaam';
if ($destVal === '' || $destVal === null) $destVal = '';

$adults = max(1, min(10, $adults));
$children = max(0, min(6, $children));
$rooms = max(1, min(5, $rooms));
if (strtotime($checkIn)  < strtotime($today)) $checkIn  = date('Y-m-d', strtotime('+7 days'));
if (strtotime($checkOut) <= strtotime($checkIn)) $checkOut = date('Y-m-d', strtotime($checkIn . ' +1 day'));
$nights = max(1, (int)round((strtotime($checkOut)-strtotime($checkIn))/86400));
$guestText = $adults . ' adult' . ($adults!==1?'s':'') . ' · ' . $rooms . ' room' . ($rooms!==1?'s':'');
if ($children>0) $guestText = $adults . ' adults · ' . $children . ' child' . ($children!==1?'ren':'') . ' · ' . $rooms . ' room' . ($rooms!==1?'s':'');
$fmtCI = date('D, M j', strtotime($checkIn));
$fmtCO = date('D, M j', strtotime($checkOut));
$mSummary = h($destVal ?: 'All Tanzanian Destinations') . ' • ' . date('M j', strtotime($checkIn)) . '–' . date('j', strtotime($checkOut)) . ' • ' . $adults . ' guest' . ($adults!==1?'s':'');
?>
<?= $this->element('Home/gh-search-style') ?>
<div class="fns-search-wrap" id="fns_search_wrap">
  <div class="container-fluid px-1 px-lg-2" style="max-width:100%;margin:0 auto;">
    <form action="<?= $this->Url->build('/') ?>" method="GET" autocomplete="off" id="gh_search_form" role="search" aria-label="Find stays" novalidate>
      <input type="hidden" name="lat" id="gh_lat" value="<?= h($queryParams['lat'] ?? '') ?>">
      <input type="hidden" name="lng" id="gh_lng" value="<?= h($queryParams['lng'] ?? '') ?>">
      <input type="hidden" name="bbox" id="gh_bbox" value="<?= h($queryParams['bbox'] ?? '') ?>">
      <input type="hidden" name="checkin" id="gh_ci" value="<?= h($checkIn) ?>">
      <input type="hidden" name="checkout" id="gh_co" value="<?= h($checkOut) ?>">
      <!-- legacy aliases for backward compat -->
      <input type="hidden" name="checkIn" id="gh_ci_legacy" value="<?= h($checkIn) ?>">
      <input type="hidden" name="checkOut" id="gh_co_legacy" value="<?= h($checkOut) ?>">
      <input type="hidden" name="city" id="gh_city" value="<?= h($destVal) ?>">
      <input type="hidden" name="adults" id="gh_ad" value="<?= $adults ?>">
      <input type="hidden" name="children" id="gh_ch" value="<?= $children ?>">
      <input type="hidden" name="rooms" id="gh_rm" value="<?= $rooms ?>">

      <div class="fns-pill cds-search-pill" id="fns_pill" role="group" aria-label="Search stays">
        <!-- Where -->
        <div class="fns-seg" id="fns_seg_where" role="combobox" aria-expanded="false" aria-haspopup="listbox" aria-owns="fns_pop_dest" aria-controls="fns_pop_dest" tabindex="0" aria-label="Destination">
          <span class="fns-seg-label">Where to?</span>
          <input type="text" id="gh_dest" name="destination" class="fns-seg-input" value="<?= h($destVal) ?>" placeholder="City, landmark, or hotel name" autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="fns_pop_dest" aria-activedescendant="" aria-label="Destination input" onfocus="fnsDestFocus()" oninput="fnsDestInput(this.value)" onkeydown="fnsDestKey(event)">
          <button type="button" class="fns-clear" id="fns_clear" onclick="fnsDestClear()" aria-label="Clear destination" tabindex="-1"><i class="fa-solid fa-xmark" style="font-size:12px;"></i></button>
          <!-- Destination popover -->
          <div class="fns-pop fns-pop-dest" id="fns_pop_dest" role="dialog" aria-label="Destination suggestions" onclick="event.stopPropagation()">
            <div id="fns_dd_popular"></div>
            <div id="fns_dd_mbx"></div>
            <div id="fns_dd_recent" class="fns-recent"></div>
          </div>
        </div>
        <div class="fns-divider" aria-hidden="true"></div>
        <!-- Check-in -->
        <div class="fns-seg" id="fns_seg_ci" role="button" tabindex="0" aria-label="Check-in date" aria-haspopup="dialog" aria-expanded="false" aria-controls="fns_pop_cal" onclick="fnsOpenCal('ci')" onkeydown="if(event.key==='Enter'||event.key===' ') {event.preventDefault(); fnsOpenCal('ci');}">
          <span class="fns-seg-label">Check-in</span>
          <span class="fns-seg-value" id="fns_ci_lbl"><?= h($fmtCI) ?></span>
          <span class="sr-only" id="fns_ci_placeholder">Add date</span>
        </div>
        <div class="fns-divider" aria-hidden="true"></div>
        <!-- Check-out -->
        <div class="fns-seg" id="fns_seg_co" role="button" tabindex="0" aria-label="Check-out date" aria-haspopup="dialog" aria-expanded="false" aria-controls="fns_pop_cal" onclick="fnsOpenCal('co')" onkeydown="if(event.key==='Enter'||event.key===' ') {event.preventDefault(); fnsOpenCal('co');}">
          <span class="fns-seg-label">Check-out</span>
          <span style="display:flex;align-items:center;gap:0;flex-wrap:nowrap;">
            <span class="fns-seg-value" id="fns_co_lbl"><?= h($fmtCO) ?></span>
            <span class="fns-nights-badge" id="fns_nights_badge"><?= $nights ?> night<?= $nights!==1?'s':'' ?></span>
          </span>
        </div>
        <div class="fns-divider" aria-hidden="true"></div>
        <!-- Guests -->
        <div class="fns-seg" id="fns_seg_guests" role="button" tabindex="0" aria-haspopup="dialog" aria-expanded="false" aria-controls="fns_pop_guests" onclick="fnsGuestsToggle(event)" onkeydown="if(event.key==='Enter'||event.key===' ') {event.preventDefault(); fnsGuestsToggle(event);}">
          <span class="fns-seg-label">Guests &amp; Rooms</span>
          <span class="fns-seg-value" id="fns_guests_lbl"><?= h($guestText) ?></span>
        </div>
        <!-- CTA -->
        <div style="display:flex;align-items:center;padding:6px 6px 6px 4px;">
          <button type="submit" class="fns-cta" id="fns_search_btn" aria-label="Search stays" aria-busy="false">
            <span class="fns-cta-icon"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></span>
            <span class="fns-cta-text">Search</span>
            <span class="fns-cta-spinner" aria-hidden="true"></span>
          </button>
        </div>

        <!-- Calendar popover (anchored to pill) -->
        <div class="fns-pop fns-pop-cal" id="fns_pop_cal" role="dialog" aria-modal="false" aria-label="Choose dates" onclick="event.stopPropagation()">
          <div class="fns-cal-head">
            <button type="button" class="fns-cal-nav" onclick="fnsNavCal(-1)" aria-label="Previous month"><i class="fa-solid fa-chevron-left" style="font-size:12px;"></i></button>
            <div class="fns-cal-titles"><span id="fns_cal_t1"></span><span id="fns_cal_t2"></span></div>
            <button type="button" class="fns-cal-nav" onclick="fnsNavCal(1)" aria-label="Next month"><i class="fa-solid fa-chevron-right" style="font-size:12px;"></i></button>
          </div>
          <div class="fns-cal-presets" id="fns_cal_presets" role="group" aria-label="Date presets">
            <button type="button" class="fns-preset active" data-preset="custom" onclick="fnsPreset('custom')">Custom</button>
            <button type="button" class="fns-preset" data-preset="weekend" onclick="fnsPreset('weekend')">Weekend</button>
            <button type="button" class="fns-preset" data-preset="week" onclick="fnsPreset('week')">1 Week</button>
          </div>
          <div class="fns-cal-grid-wrap">
            <div><div class="fns-cal-weekdays"><span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span></div><div class="fns-cal-days" id="fns_cal_m1"></div></div>
            <div><div class="fns-cal-weekdays"><span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span></div><div class="fns-cal-days" id="fns_cal_m2"></div></div>
          </div>
          <div class="fns-cal-footer">
            <span class="fns-cal-summary" id="fns_cal_summary"><strong><?= $nights ?> night<?= $nights!==1?'s':'' ?></strong> · <?= h($fmtCI) ?> – <?= h($fmtCO) ?></span>
            <div style="display:flex;gap:8px;">
              <button type="button" class="fns-btn-reset" onclick="fnsCalReset()">Reset</button>
              <button type="button" class="fns-btn-apply" onclick="fnsCalDone()">Done</button>
            </div>
          </div>
        </div>

        <!-- Guests popover -->
        <div class="fns-pop fns-pop-guests" id="fns_pop_guests" role="dialog" aria-label="Guests and rooms" onclick="event.stopPropagation()">
          <div class="fns-g-row">
            <div><div class="fns-g-label">Adults</div><div class="fns-g-sub">Ages 13+</div></div>
            <div class="fns-stepper">
              <button type="button" class="fns-step-btn" id="fns_ad_m" aria-label="Decrease adults" onclick="fnsG('adults',-1)">−</button>
              <span class="fns-step-val" id="fns_ad_v" aria-live="polite"><?= $adults ?></span>
              <button type="button" class="fns-step-btn" id="fns_ad_p" aria-label="Increase adults" onclick="fnsG('adults',1)">+</button>
            </div>
          </div>
          <div class="fns-g-row">
            <div><div class="fns-g-label">Children</div><div class="fns-g-sub">Ages 0–12</div></div>
            <div class="fns-stepper">
              <button type="button" class="fns-step-btn" id="fns_ch_m" aria-label="Decrease children" onclick="fnsG('children',-1)">−</button>
              <span class="fns-step-val" id="fns_ch_v" aria-live="polite"><?= $children ?></span>
              <button type="button" class="fns-step-btn" id="fns_ch_p" aria-label="Increase children" onclick="fnsG('children',1)">+</button>
            </div>
          </div>
          <div class="fns-g-row">
            <div><div class="fns-g-label">Rooms</div><div class="fns-g-sub">Max 5</div></div>
            <div class="fns-stepper">
              <button type="button" class="fns-step-btn" id="fns_rm_m" aria-label="Decrease rooms" onclick="fnsG('rooms',-1)">−</button>
              <span class="fns-step-val" id="fns_rm_v" aria-live="polite"><?= $rooms ?></span>
              <button type="button" class="fns-step-btn" id="fns_rm_p" aria-label="Increase rooms" onclick="fnsG('rooms',1)">+</button>
            </div>
          </div>
          <div class="fns-apply-bar">
            <button type="button" class="fns-btn-reset" onclick="fnsGuestsClose()">Close</button>
            <button type="button" class="fns-btn-apply" onclick="fnsGuestsApply()">Apply</button>
          </div>
        </div>
      </div>
    </form>
    <!-- Mobile: Google-Hotels stacked search (screenshot parity, <992px) -->
    <div class="container-fluid px-1 px-lg-2 d-lg-none" style="max-width:100%;margin:0 auto;">
      <!-- legacy chip kept hidden for JS compat -->
      <div class="fns-mobile-chip" id="fns_mobile_chip" role="button" tabindex="-1" aria-hidden="true" style="display:none !important;">
        <span class="fns-mobile-chip-text" id="fns_chip_text"><?= h($mSummary) ?></span>
      </div>
      <div class="fns-m-google" id="fns_m_google">
        <!-- Row 1: destination search box -->
        <button type="button" class="fns-g-searchbox" id="fns_g_searchbox" aria-label="Search for places, hotels and more" onclick="fnsOpenMobile();fnsSheetGo('where');">
          <span class="fns-g-search-icon" aria-hidden="true"><i class="fa-solid fa-magnifying-glass"></i></span>
          <span class="fns-g-search-text <?= trim((string)$destVal) !== '' ? 'has-value' : '' ?>" id="fns_g_dest_text"><?= trim((string)$destVal) !== '' ? h($destVal) : 'Search for places, hotels and more' ?></span>
        </button>
        <!-- Row 2: dates + guests -->
        <div class="fns-g-row2">
          <button type="button" class="fns-g-dates" id="fns_g_dates" aria-label="Change dates: <?= h($fmtCI) ?> to <?= h($fmtCO) ?>" onclick="fnsOpenMobile();fnsSheetGo('when');">
            <span class="fns-g-cal-icon" aria-hidden="true"><i class="fa-regular fa-calendar"></i></span>
            <span class="fns-g-date" id="fns_g_ci"><?= h($fmtCI) ?></span>
            <span class="fns-g-vdiv" aria-hidden="true"></span>
            <span class="fns-g-date fns-g-date-co" id="fns_g_co"><?= h($fmtCO) ?></span>
          </button>
          <button type="button" class="fns-g-guests" id="fns_g_guests_btn" aria-label="Change guests, currently <?= (int)($adults + $children) ?> guests" onclick="fnsOpenMobile();fnsSheetGo('who');">
            <span class="fns-g-person-icon" aria-hidden="true"><i class="fa-regular fa-user"></i></span>
            <span class="fns-g-guest-count" id="fns_g_guest_count"><?= (int)($adults + $children) ?></span>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- shared backdrop -->
<div class="fns-sheet-backdrop" id="fns_sheet_backdrop" aria-hidden="true" onclick="fnsCloseMobile()"></div>
<!-- Mobile: bottom sheet stepped modal -->
<div class="fns-mobile-sheet" id="fns_mobile_sheet" role="dialog" aria-modal="true" aria-label="Search stays">
  <div class="fns-sheet-drag" aria-hidden="true"><span></span></div>
  <div class="fns-sheet-header">
    <div class="fns-sheet-header-title">Search stays</div>
    <button type="button" class="fns-sheet-back" onclick="fnsCloseMobile()" aria-label="Close search"><i class="fa-solid fa-xmark"></i></button>
  </div>
  <div class="fns-sheet-tabs" role="tablist">
    <button type="button" class="fns-sheet-tab active" data-step="where" role="tab" aria-selected="true" onclick="fnsSheetGo('where')">Where</button>
    <button type="button" class="fns-sheet-tab" data-step="when" role="tab" onclick="fnsSheetGo('when')">When</button>
    <button type="button" class="fns-sheet-tab" data-step="who" role="tab" onclick="fnsSheetGo('who')">Who</button>
  </div>
  <div class="fns-sheet-step" id="fns_sheet_body">
    <!-- Where -->
    <div class="fns-sheet-accordion" id="fns_acc_where">
      <button type="button" class="fns-acc-head active" aria-expanded="true" onclick="fnsAccToggle('where')">
        <span class="fns-acc-title">Where to?</span>
        <span class="fns-acc-value" id="fns_m_where_val"><?= h($destVal ?: 'All Tanzanian Destinations') ?></span>
        <i class="fa-solid fa-chevron-down fns-acc-chevron" aria-hidden="true"></i>
      </button>
      <div class="fns-acc-body open" id="fns_acc_where_body">
        <div class="fns-m-input-wrap" style="margin-bottom:12px;">
          <i class="fa-solid fa-magnifying-glass" style="color:var(--fns-text-sec);font-size:15px;flex-shrink:0;" aria-hidden="true"></i>
          <input type="text" id="fns_m_input" class="fns-m-input" value="<?= h($destVal) ?>" placeholder="City, landmark, or hotel name" autocomplete="off" aria-label="Destination" oninput="fnsMDestInput(this.value)" enterkeyhint="search">
          <button type="button" class="fns-m-clear" onclick="fnsMClear()" aria-label="Clear destination"><i class="fa-solid fa-xmark" style="font-size:12px;"></i></button>
        </div>
        <div id="fns_m_list"></div>
      </div>
    </div>
    <!-- When -->
    <div class="fns-sheet-accordion" id="fns_acc_when">
      <button type="button" class="fns-acc-head" aria-expanded="false" onclick="fnsAccToggle('when')">
        <span class="fns-acc-title">When?</span>
        <span class="fns-acc-value" id="fns_m_when_val"><?= h($fmtCI) ?> – <?= h($fmtCO) ?> · <?= $nights ?>n</span>
        <i class="fa-solid fa-chevron-down fns-acc-chevron" aria-hidden="true"></i>
      </button>
      <div class="fns-acc-body" id="fns_acc_when_body">
        <div style="display:flex;gap:8px;margin-bottom:12px;">
          <div class="fns-m-cal-box" id="fns_m_ci_box"><button type="button" onclick="fnsStep('ci',-1)" aria-label="Previous check-in day"><i class="fa-solid fa-chevron-left" style="font-size:10px;"></i></button><span id="fns_m_ci_lbl" style="font-weight:700;font-family:'Inter',Roboto,sans-serif;font-size:13.5px;"><?= h($fmtCI) ?></span><button type="button" onclick="fnsStep('ci',1)" aria-label="Next check-in day"><i class="fa-solid fa-chevron-right" style="font-size:10px;"></i></button></div>
          <div class="fns-m-cal-box" id="fns_m_co_box"><span id="fns_m_co_lbl" style="font-weight:700;font-family:'Inter',Roboto,sans-serif;font-size:13.5px;"><?= h($fmtCO) ?></span><span class="fns-nights-badge" id="fns_m_nights_badge"><?= $nights ?>n</span></div>
        </div>
        <div class="fns-m-weekdays" aria-hidden="true"><span>S</span><span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span></div>
        <div id="fns_m_grid" style="max-height:52vh;overflow-y:auto;-webkit-overflow-scrolling:touch;overscroll-behavior:contain;"></div>
        <div style="display:flex;gap:8px;margin-top:12px;flex-wrap:wrap;" role="group" aria-label="Date presets">
          <button type="button" class="fns-preset" onclick="fnsPreset('weekend')">Weekend</button>
          <button type="button" class="fns-preset" onclick="fnsPreset('week')">1 Week</button>
          <button type="button" class="fns-preset" onclick="fnsPreset('custom')">Custom</button>
        </div>
      </div>
    </div>
    <!-- Who -->
    <div class="fns-sheet-accordion" id="fns_acc_who">
      <button type="button" class="fns-acc-head" aria-expanded="false" onclick="fnsAccToggle('who')">
        <span class="fns-acc-title">Who's coming?</span>
        <span class="fns-acc-value" id="fns_m_who_val"><?= h($guestText) ?></span>
        <i class="fa-solid fa-chevron-down fns-acc-chevron" aria-hidden="true"></i>
      </button>
      <div class="fns-acc-body" id="fns_acc_who_body">
        <div class="fns-g-row mob"><div><div class="fns-g-label">Adults</div><div class="fns-g-sub">Ages 13+</div></div><div class="fns-stepper"><button type="button" class="fns-step-btn mob" id="fns_m_ad_m" onclick="fnsMG('adults',-1)" aria-label="Decrease adults">−</button><span class="fns-step-val" id="fns_m_ad_v" style="min-width:28px;font-size:16px;"><?= $adults ?></span><button type="button" class="fns-step-btn mob" id="fns_m_ad_p" onclick="fnsMG('adults',1)" aria-label="Increase adults">+</button></div></div>
        <div class="fns-g-row mob"><div><div class="fns-g-label">Children</div><div class="fns-g-sub">Ages 0–12</div></div><div class="fns-stepper"><button type="button" class="fns-step-btn mob" id="fns_m_ch_m" onclick="fnsMG('children',-1)" aria-label="Decrease children">−</button><span class="fns-step-val" id="fns_m_ch_v" style="min-width:28px;font-size:16px;"><?= $children ?></span><button type="button" class="fns-step-btn mob" id="fns_m_ch_p" onclick="fnsMG('children',1)" aria-label="Increase children">+</button></div></div>
        <div class="fns-g-row mob"><div><div class="fns-g-label">Rooms</div><div class="fns-g-sub">1–5 rooms</div></div><div class="fns-stepper"><button type="button" class="fns-step-btn mob" id="fns_m_rm_m" onclick="fnsMG('rooms',-1)" aria-label="Decrease rooms">−</button><span class="fns-step-val" id="fns_m_rm_v" style="min-width:28px;font-size:16px;"><?= $rooms ?></span><button type="button" class="fns-step-btn mob" id="fns_m_rm_p" onclick="fnsMG('rooms',1)" aria-label="Increase rooms">+</button></div></div>
      </div>
    </div>
  </div>
  <div class="fns-sheet-footer">
    <button type="button" class="fns-btn-reset" onclick="fnsClearAllMobile()">Clear all</button>
    <button type="button" class="fns-btn-apply" onclick="fnsMobileSearch()">Search stays <span id="fns_m_search_count" style="opacity:.9;font-weight:600;">· <?= (int)$apiCount ?> stays</span></button>
  </div>
</div>
<?= $this->element('Home/gh-search-suggest', ['checkIn' => $checkIn ?? '', 'checkOut' => $checkOut ?? '', 'adults' => $adults ?? 2, 'children' => $children ?? 0, 'rooms' => $rooms ?? 1]) ?>
<?= $this->element('Home/gh-search-dates') ?>
<?= $this->element('Home/gh-search-mobile', ['apiCount' => $apiCount ?? 0]) ?>
