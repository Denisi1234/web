<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= $this->fetch('title') ? h($this->fetch('title')) . ' | fastnetstays.com' : 'FastNet Stays — Online Hotel Booking & Best Prices Guaranteed' ?></title>
        <meta name="description" content="<?= $this->fetch('description') ? h($this->fetch('description')) : 'Online Hotel Booking — FastNet Stays - Best Prices Guaranteed with Deals, Special Member Prices. Book Hotels, Lodges & Beach Resorts Across Tanzania! Mobile Friendly & Instant Confirmation.' ?>" />
	    <meta name="author" content="FastNet Stays" />
	    <meta name="website" content="https://www.fastnetstays.com" />
	    <meta name="email" content="support@fastnetstays.com" />
        <meta name="csrfToken" content="<?= $this->request->getAttribute('csrfToken'); ?>">
	    <meta name="version" content="1.0.0" />
        <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1" />
        <?php
        $canonReq = $this->getRequest();
        $canonPath = rawurldecode($canonReq->getPath());
        $canonQs = '';
        $canonCity = $canonReq->getQuery('city') ?? $canonReq->getQuery('destination') ?? '';
        if (in_array($canonPath, ['/', '/hotel-list-01', '/hotels', '/stays'], true) && $canonCity !== '') {
            $canonQs = '?city=' . rawurlencode(trim((string)$canonCity));
        }
        $canonUrl = 'https://www.fastnetstays.com' . $canonPath . $canonQs;
        ?>
        <link rel="canonical" href="<?= h($canonUrl) ?>" />
        <link rel="alternate" hreflang="en-TZ" href="<?= h($canonUrl) ?>" />
        <!-- sw-TZ omitted: no Swahili content ships yet — claiming it would mislead crawlers -->
        <link rel="alternate" hreflang="x-default" href="<?= h($canonUrl) ?>" />
        <link rel="alternate" type="text/plain" href="https://www.fastnetstays.com/llms.txt" title="LLM Knowledge Graph" />
        <!-- Production: resource hints — fast LCP + CLS -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <!-- IBM Carbon productive type: IBM Plex Sans 400/500/600/700 + Roboto 400/500 (search UI).
             Single preconnected request — replaces the old render-blocking @import in google-travel-home.css. -->
        <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
        <link rel="preconnect" href="https://api.mapbox.com" crossorigin>
        <link rel="preconnect" href="https://api.fastnetstays.com" crossorigin>
        <?php if (!in_array($this->getRequest()->getParam('controller'), ['Account'], true)): ?>
        <link rel="preload" href="/assets/css/google-travel-layout.css?v=<?= filemtime(WWW_ROOT . 'assets/css/google-travel-layout.css') ?>" as="style">
        <link rel="preload" href="/assets/css/google-travel-cards.css?v=<?= filemtime(WWW_ROOT . 'assets/css/google-travel-cards.css') ?>" as="style">
        <?php else: ?>
        <link rel="preload" href="/assets/css/google-travel-layout.css?v=<?= filemtime(WWW_ROOT . 'assets/css/google-travel-layout.css') ?>" as="style">
        <?php endif; ?>
        <meta http-equiv="x-dns-prefetch-control" content="on">
        <meta name="googlebot" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1" />
        <meta name="bingbot" content="index, follow, max-media-preview:large" />

        <!-- Open Graph / Facebook / WhatsApp -->
        <meta property="og:type" content="website" />
        <meta property="og:site_name" content="FastNet Stays" />
        <meta property="og:url" content="<?= h($canonUrl) ?>" />
        <meta property="og:title" content="<?= $this->fetch('title') ? h($this->fetch('title')) . ' | fastnetstays.com' : 'FastNet Stays — Online Hotel Booking & Best Prices Guaranteed' ?>" />
        <meta property="og:description" content="Online Hotel Booking — FastNet Stays - Best Prices Guaranteed with Deals, Special Member Prices. Book Hotels, Lodges & Beach Resorts Across Tanzania!" />
        <meta property="og:image" content="https://www.fastnetstays.com/favicon-512.png" />

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:site" content="@fastnetstays" />
        <meta name="twitter:title" content="FastNet Stays — Online Hotel Booking & Best Prices Guaranteed" />
        <meta name="twitter:description" content="Book hotel rooms, luxury resorts, and beach escapes across Tanzania with fastnetstays.com." />
        <meta name="twitter:image" content="https://www.fastnetstays.com/favicon-512.png" />

        <!-- Favicon & Touch Icons for Google Search Snippet Logo -->
        <link rel="icon" type="image/png" sizes="16x16" href="<?= $this->Url->build('/assets/img/favicon-16x16.png'); ?>">
        <link rel="icon" type="image/png" sizes="32x32" href="<?= $this->Url->build('/assets/img/favicon-32x32.png'); ?>">
        <link rel="icon" type="image/png" sizes="48x48" href="<?= $this->Url->build('/assets/img/favicon-48x48.png'); ?>">
        <link rel="shortcut icon" href="<?= $this->Url->build('/favicon.ico'); ?>" sizes="16x16 32x32 48x48">
        <link rel="apple-touch-icon" sizes="180x180" href="<?= $this->Url->build('/assets/img/apple-touch-icon.png'); ?>">
        <link rel="manifest" href="<?= $this->Url->build('/manifest.json'); ?>">
        <meta name="theme-color" content="#0f62fe">
        <meta name="format-detection" content="telephone=no" />

        <!-- Google Rich Result Structured Data (Schema.org JSON-LD for Sitelinks & Brand Search) -->
        <?= $this->element('Layout/default-seo') ?>

        <!-- CSS Files -->
        <?php
        $isHomePage = $this->getRequest()->getParam('controller') === 'Pages' && in_array($this->getRequest()->getParam('action'), ['index', 'display'], true);
        $globalCss = [
            '/assets/css/bootstrap.min.css',
            '/assets/css/bootstrap-icons.css',
            '/assets/css/fontawesome.css',
            '/assets/css/theme.min.css',
        ];
        if (!$isHomePage) {
            $globalCss = array_merge($globalCss, [
                '/assets/css/dropzone.min.css',
                '/assets/css/flatpickr.min.css',
                '/assets/css/flickity.min.css',
                '/assets/css/lightbox.min.css',
                '/assets/css/magnifypopup.css',
                '/assets/css/select2.min.css',
                '/assets/css/rangeSlider.min.css',
                '/assets/css/slick.css',
                '/assets/css/prism.css',
            ]);
        }
        echo $this->Html->css($globalCss);
        ?>

        <?= $this->Html->css('/assets/css/ui-tokens.css') ?>
        <?= $this->Html->css(['/assets/css/loading-01.css', '/assets/css/loading-02.css']) ?>
        <?= $this->Html->css('/assets/css/app-loader.css') ?>
        <?= $this->Html->css('/assets/css/fastnet-dots.css') ?>
        <?= $this->Html->css('/assets/css/shimmer.css') ?>
        <?= $this->fetch('meta') ?>
        <?= $this->element('api_direct') ?>
        <?= $this->fetch('css') ?>
        <?= $this->Html->css('/assets/css/site-spacing.css') ?>
        <!-- IBM Carbon LAST so components win over page CSS -->
        <?= $this->Html->css('/assets/css/carbon-polish.css?v=1.0.2') ?>
        <?php if ($isHomePage): ?>
        <?= $this->Html->css('/assets/css/carbon-home-01.css?v=1.0.2') ?>
        <?= $this->Html->css('/assets/css/carbon-home-02.css?v=1.0.2') ?>
        <?php else: ?>
        <?= $this->Html->css('/assets/css/carbon-journey.css?v=1.0.2') ?>
        <?php endif; ?>
        <!-- Mobile declutter pass -->
        <?= $this->Html->css('/assets/css/mobile-clean.css?v=1.0.2') ?>

        <!-- Scripts with defer to unblock browser initial paint -->
        <?= $this->Html->script('/assets/js/loading-core.js?v=1.0.2', ['defer' => true]) ?>
        <?= $this->Html->script('/assets/js/loading-ui.js?v=1.0.2', ['defer' => true]) ?>
        <?= $this->Html->script('/assets/js/app-loader-core.js?v=1.0.2', ['defer' => true]) ?>
        <?= $this->Html->script('/assets/js/app-loader-dialog.js?v=1.0.2', ['defer' => true]) ?>
        <?= $this->Html->script('/assets/js/fastnet-api-core.js?v=1.0.2', ['defer' => true]) ?>
        <?= $this->Html->script('/assets/js/fastnet-api-opt.js?v=1.0.2', ['defer' => true]) ?>
        <?= $this->Html->script('/assets/js/fastnet-api-submit.js?v=1.0.2', ['defer' => true]) ?>
        <?= $this->Html->script('/assets/js/fastnet-currency.js?v=1.0.2', ['defer' => true]) ?>

        <!-- Mapbox GL JS -->
        <link href="https://api.mapbox.com/mapbox-gl-js/v3.1.2/mapbox-gl.css" rel="stylesheet">
        <script src="https://api.mapbox.com/mapbox-gl-js/v3.1.2/mapbox-gl.js" defer></script>
        <?= $this->Html->script('/assets/js/fastnet-map-core.js', ['defer' => true]) ?>

        <?= $this->element('Layout/default-bootstrap') ?>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/MaterialDesign-Webfont/7.4.47/css/materialdesignicons.min.css" media="print" onload="this.media='all'">
    </head>

    <body>
        <a href="#main-content" class="cds-skip-link">Skip to main content</a>
        <!-- Universal Shared App Loader & Background Task Indicator -->
        <?= $this->element('app-loader') ?>

        <div id="main-wrapper">

            <!-- Flash messages (Carbon notifications) — rendered here so notices
                 never pile up unseen across public pages -->
            <?= $this->Html->css('/assets/css/default-flash.css') ?>
            <div class="container" style="max-width:1140px">
                <?= $this->Flash->render() ?>
            </div>

            <!-- Main Content -->
        	<?= $this->fetch('content') ?>

            <!-- SEO: crawlable sitelinks anchors (matches JSON-LD SiteNavigationElement) - helps Google generate expanded sitelinks like screenshot) -->
            <nav aria-label="Sitelinks" style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden;">
                <a href="https://www.fastnetstays.com/?city=Dar%20es%20Salaam">FastNet Hotels</a>
                <a href="https://www.fastnetstays.com/?city=Dar%20es%20Salaam#track-prices">Track and Compare Hotel Prices</a>
                <a href="https://www.fastnetstays.com/">Hotel Deals</a>
                <a href="https://www.fastnetstays.com/?city=Zanzibar">Hotels to Zanzibar</a>
                <a href="https://www.fastnetstays.com/?city=Arusha">Hotels to Arusha</a>
                <a href="https://www.fastnetstays.com/">Stays</a>
            </nav>

            <a id="back2Top" class="top-scroll" title="Back to top" href="#"><i class="fa-solid fa-sort-up"></i></a>

        </div>

        <!-- JavaScript Files -->
        <?= $this->Html->script([
            '/assets/js/jquery.min.js',
            '/assets/js/popper.min.js',
            '/assets/js/bootstrap.min.js',
            '/assets/js/custom-site.js',
            '/assets/js/custom-nav.js',
            '/assets/js/active.js',
        ]); ?>
        <?php if (!$isHomePage): ?>
        <?= $this->Html->script([
            '/assets/js/dropzone.min.js',
            '/assets/js/flatpickr.js',
            '/assets/js/flickity.pkgd.min.js',
            '/assets/js/lightbox.min.js',
            '/assets/js/rangeslider.js',
            '/assets/js/select2.min.js',
            '/assets/js/counterup.min.js',
            '/assets/js/slick.js',
            '/assets/js/prism.js',
            '/assets/js/addadult.js',
            '/assets/js/browselocation.js',
            '/assets/js/contact.js',
        ], ['defer' => true]); ?>
        <?php endif; ?>

        <?= $this->fetch('script') ?>

        <!-- Global toast (all pages): polite live region + fnsToast helper. Home defines its own richer copy. -->
        <div id="fns_toast" role="status" aria-live="polite" aria-atomic="true" style="position:fixed;bottom:20px;bottom:calc(20px + env(safe-area-inset-bottom));left:50%;transform:translateX(-50%);background:#161616;color:#fff;padding:11px 18px;border-radius:9999px;font-size:13px;font-weight:500;display:none;z-index:4000;box-shadow:0 8px 30px rgba(0,0,0,.18);max-width:min(92vw,420px);text-align:center;pointer-events:none;font-family:'IBM Plex Sans','Inter',Roboto,sans-serif"></div>
        <?= $this->element('Layout/default-foot-script') ?>

    </body>
</html>
