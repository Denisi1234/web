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
        <h4 id="fastnet-loader-title" class="fastnet-loader-title">Loading FastNet Stays...</h4>
        <p id="fastnet-loader-subtext" class="fastnet-loader-subtext">Connecting to servers across Tanzania</p>
    </div>
</div>

<!-- 3. Floating Background Task Pill Indicator Container -->
<div id="fastnet-bg-loader" role="status" aria-live="polite" aria-atomic="true"></div>
