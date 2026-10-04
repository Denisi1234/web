<?php
/**
 * FastNet Stays — Universal Shared App Loader & Background Task Indicator
 * Rendered at the top of <body> in default.php
 */
?>
<!-- 1. Sleek Top-Bar Progress Indicator -->
<div id="fastnet-top-progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-label="Page loading progress"></div>

<!-- 2. Full-Screen App Loading Modal / Splash -->
<div id="fastnet-app-loader" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="fastnet-loader-title">
    <div class="fastnet-loader-card">
        <div class="fastnet-spinner-wrap">
            <svg class="fastnet-spinner-svg" viewBox="0 0 50 50">
                <circle class="fastnet-spinner-circle" cx="25" cy="25" r="20" fill="none" stroke-width="4"></circle>
            </svg>
            <div class="fastnet-spinner-logo">
                <i class="fa-solid fa-hotel"></i>
            </div>
        </div>
        <h4 id="fastnet-loader-title" class="fastnet-loader-title">Loading...</h4>
        <p id="fastnet-loader-subtext" class="fastnet-loader-subtext">Please wait a moment</p>
    </div>
</div>

<!-- 3. Centered navigation card — RETIRED (bar-only, Google pattern).
     Container kept so navShow()/navHide() stay null-safe; inner card removed
     and the container is display:none via app-loader.css. -->
<div id="fastnet-nav-loader" aria-hidden="true" role="status" aria-label="Loading"></div>

<!-- 4. Floating Background Task Pill Indicator Container -->
<div id="fastnet-bg-loader" role="status" aria-live="polite" aria-atomic="true"></div>

<!-- 5. Professional Confirm Modal (replaces browser window.confirm)
     style="display:none" is a failsafe: hides before CSS/JS loads.
     fnsConfirm() in app-loader.js controls visibility via .visible class. -->
<div id="fns-confirm-overlay" role="dialog" aria-modal="true" aria-labelledby="fns-confirm-title" style="display:none">
    <div class="fns-confirm-card">
        <div class="fns-confirm-icon" aria-hidden="true">⚠</div>
        <p class="fns-confirm-title" id="fns-confirm-title">Are you sure?</p>
        <p class="fns-confirm-msg" id="fns-confirm-msg">This action cannot be undone.</p>
        <div class="fns-confirm-actions">
            <button class="fns-confirm-cancel" id="fns-confirm-cancel" type="button">Cancel</button>
            <button class="fns-confirm-ok"     id="fns-confirm-ok"     type="button">Confirm</button>
        </div>
    </div>
</div>
