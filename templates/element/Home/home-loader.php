<?php
// Home loader element — intentionally empty.
//
// The dark floating capsule (#home-mobile-loader-pill, "Finding best stays…")
// was removed: it clashed with the Carbon white theme and stacked with the
// thin top progress bar + skeleton screens (three indicators for one job).
// Loading feedback on home is now single-source:
//   - thin top bar via FastnetLoading.bar (all navigations / AJAX hydrations)
//   - skeleton cards via FastnetLoading.skeleton (filter/search transitions)
//   - CTA spinner on the Search button itself (aria-busy)
// showHomeLoader()/hideHomeLoader() in home-carousel.js are kept as bar-only
// aliases so existing callers (index.php, hero.php, hotel-detail.php) keep
// working with zero changes at call sites.
?>
