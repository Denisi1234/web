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
        <!-- IBM Carbon productive type: IBM Plex Sans 400/500/600/700 -->
        <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="preconnect" href="https://api.mapbox.com" crossorigin>
        <link rel="preconnect" href="https://api.fastnetstays.com" crossorigin>
        <link rel="dns-prefetch" href="https://images.unsplash.com">
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
        <script type="application/ld+json">
        {
          "@context": "https://schema.org",
          "@graph": [
            {
              "@type": "WebSite",
              "@id": "https://www.fastnetstays.com/#website",
              "url": "https://www.fastnetstays.com",
              "name": "FastNet Stays",
              "description": "Online Hotel Booking, Luxury Lodges & Beach Escapes across Tanzania",
              "publisher": {
                "@id": "https://www.fastnetstays.com/#organization"
              },
              "potentialAction": {
                "@type": "SearchAction",
                "target": {
                  "@type": "EntryPoint",
                  "urlTemplate": "https://www.fastnetstays.com/?city={search_term_string}"
                },
                "query-input": {
                  "@type": "PropertyValueSpecification",
                  "valueRequired": true,
                  "valueName": "search_term_string"
                }
              }
            },
            {
              "@type": "TravelAgency",
              "@id": "https://www.fastnetstays.com/#organization",
              "name": "FastNet Stays",
              "url": "https://www.fastnetstays.com",
              "logo": {
                "@type": "ImageObject",
                "url": "https://www.fastnetstays.com/favicon-512.png",
                "width": "512",
                "height": "512"
              },
              "contactPoint": {
                "@type": "ContactPoint",
                "contactType": "customer service",
                "email": "support@fastnetstays.com",
                "availableLanguage": ["English"]
              },
              "sameAs": [
                "https://www.fastnetstays.com"
              ]
            },
            {
              "@type": "ItemList",
              "name": "FastNet Stays Sitelinks",
              "itemListElement": [
                {
                  "@type": "SiteNavigationElement",
                  "position": 1,
                  "name": "FastNet Hotels",
                  "description": "When booking a hotel in Dar es Salaam, play around with dates and price options on ...",
                  "url": "https://www.fastnetstays.com/?city=Dar%20es%20Salaam"
                },
                {
                  "@type": "SiteNavigationElement",
                  "position": 2,
                  "name": "Track and Compare Hotel Prices",
                  "description": "Set up price tracking. Track hotel prices for specific trip dates, or ...",
                  "url": "https://www.fastnetstays.com/?city=Dar%20es%20Salaam#track-prices"
                },
                {
                  "@type": "SiteNavigationElement",
                  "position": 3,
                  "name": "Hotel Deals",
                  "description": "Browse hotel deals across Tanzania — compare prices and book direct.",
                  "url": "https://www.fastnetstays.com/"
                },
                {
                  "@type": "SiteNavigationElement",
                  "position": 4,
                  "name": "Hotels to Zanzibar",
                  "description": "Beach stay. City hotel; Resort; Boutique — Off-peak travel is ...",
                  "url": "https://www.fastnetstays.com/?city=Zanzibar"
                },
                {
                  "@type": "SiteNavigationElement",
                  "position": 5,
                  "name": "Hotels to Arusha",
                  "description": "Safari lodge. Safari stay; City hotel; Lodge — Peak season deals ...",
                  "url": "https://www.fastnetstays.com/?city=Arusha"
                },
                {
                  "@type": "SiteNavigationElement",
                  "position": 6,
                  "name": "Stays",
                  "description": "Hotel suggestions are based on a route's cheapest nightly fares ...",
                  "url": "https://www.fastnetstays.com/"
                }
              ]
            },
            {
              "@type": "BreadcrumbList",
              "@id": "https://www.fastnetstays.com/#breadcrumb",
              "itemListElement": [
                {"@type": "ListItem","position": 1,"name": "Home","item": "https://www.fastnetstays.com/"},
                {"@type": "ListItem","position": 2,"name": "Hotels","item": "https://www.fastnetstays.com/?city=Dar%20es%20Salaam"},
                {"@type": "ListItem","position": 3,"name": "Zanzibar Hotels","item": "https://www.fastnetstays.com/?city=Zanzibar"}
              ]
            }
          ]
        }
        </script>

        <!-- CSS Files -->
        <?php
        // Home (Pages::index) is self-contained (split-view + Carbon) — skip plugin
        // stylesheets it never uses (dropzone/flatpickr/flickity/lightbox/etc.) to cut render-blocking CSS.
        $isHomePage = $this->getRequest()->getParam('controller') === 'Pages' && in_array($this->getRequest()->getParam('action'), ['index', 'display'], true);
        $globalCss = [
            '/assets/css/bootstrap.min.css',
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
        $globalCss = array_merge($globalCss, [
            '/assets/css/bootstrap-icons.css',
            '/assets/css/fontawesome.css',
            '/assets/css/style.css',
        ]);
        echo $this->Html->css($globalCss);
        ?>

        <?= $this->Html->css('/assets/css/ui-tokens.css') ?>
        <!-- Canonical loading system: one set of tokens/motion for every
             loading state. Loaded after app-loader.css so it wins. -->
        <?= $this->Html->css('/assets/css/loading.css') ?>
        <?= $this->Html->css('/assets/css/app-loader.css') ?>
        <?= $this->Html->css('/assets/css/fastnet-dots.css') ?>
        <?= $this->Html->css('/assets/css/shimmer.css') ?>
        <?= $this->fetch('meta') ?>
        <?= $this->element('api_direct') ?>
        <?= $this->fetch('css') ?>
        <?= $this->Html->css('/assets/css/site-spacing.css') ?>
        <!-- IBM Carbon LAST so components win over page CSS (Baymard layout untouched) -->
        <?= $this->Html->css('/assets/css/carbon-polish.css?v=' . filemtime(WWW_ROOT . 'assets/css/carbon-polish.css')) ?>
        <?php if ($isHomePage): ?>
        <?= $this->Html->css('/assets/css/carbon-home.css?v=' . filemtime(WWW_ROOT . 'assets/css/carbon-home.css')) ?>
        <?php else: ?>
        <?= $this->Html->css('/assets/css/carbon-journey.css?v=' . filemtime(WWW_ROOT . 'assets/css/carbon-journey.css')) ?>
        <?php endif; ?>
        <!-- Mobile declutter pass: phone-only overrides, loaded LAST so it wins -->
        <?= $this->Html->css('/assets/css/mobile-clean.css?v=' . filemtime(WWW_ROOT . 'assets/css/mobile-clean.css')) ?>

        <!-- Canonical loading controller. Must precede app-loader.js, which
             delegates its progress bar to it. -->
        <?= $this->Html->script('/assets/js/loading.js?v=' . filemtime(WWW_ROOT . 'assets/js/loading.js')) ?>
        <!-- Universal App Loader Engine -->
        <?= $this->Html->script('/assets/js/app-loader.js?v=' . filemtime(WWW_ROOT . 'assets/js/app-loader.js')) ?>
        <!-- Direct-to-backend forms (Bearer in JS, CakePHP proxy as fallback) -->
        <?= $this->Html->script('/assets/js/fastnet-api.js?v=' . filemtime(WWW_ROOT . 'assets/js/fastnet-api.js')) ?>
        <!-- Display currency (TZS/USD/EUR): deferred so it runs before page-level deferred scripts -->
        <?= $this->Html->script('/assets/js/fastnet-currency.js?v=' . filemtime(WWW_ROOT . 'assets/js/fastnet-currency.js'), ['defer' => true]) ?>

        <!-- Mapbox GL JS — production CSS & JS -->
        <link href="https://api.mapbox.com/mapbox-gl-js/v3.1.2/mapbox-gl.css" rel="stylesheet">
        <script src="https://api.mapbox.com/mapbox-gl-js/v3.1.2/mapbox-gl.js" defer></script>
        <?= $this->Html->script('/assets/js/fastnet-map-core.js') ?>

        <script>
            window.FASTNET_API_URL = (
                window.location.hostname === 'localhost' ||
                window.location.hostname === '127.0.0.1' ||
                window.location.hostname === ''
            ) ? 'http://127.0.0.1:8000' : 'https://api.fastnetstays.com';

            window.API_URL = function (path) {
                return window.FASTNET_API_URL + path;
            };

            // Mapbox configuration — public pk.* token only (never echo secret sk.*). Restrict token by HTTP Referrer in Mapbox dashboard.
            <?php
            $layoutMapboxToken = $mapboxToken ?? \Cake\Core\Configure::read('App.mapboxToken', env('MAPBOX_TOKEN', ''));
            $layoutMapboxStyle = $mapboxStyle ?? \Cake\Core\Configure::read('App.mapboxStyle', 'mapbox://styles/mapbox/streets-v12');
            if (!is_string($layoutMapboxToken) || !str_starts_with($layoutMapboxToken, 'pk.')) $layoutMapboxToken = '';
            if (!is_string($layoutMapboxStyle) || $layoutMapboxStyle === '') $layoutMapboxStyle = 'mapbox://styles/mapbox/streets-v12';
            // Only pk.* public tokens are echoed to HTML; secrets never leave server.
            ?>
            window.MAPBOX_TOKEN = <?= json_encode($layoutMapboxToken) ?> || window.MAPBOX_TOKEN || '';
            window.MAPBOX_STYLE = <?= json_encode($layoutMapboxStyle) ?> || window.MAPBOX_STYLE || 'mapbox://styles/mapbox/streets-v12';
            var _isMapboxStyle = window.MAPBOX_STYLE && window.MAPBOX_STYLE.indexOf('mapbox://') === 0;
            if (!window.MAPBOX_TOKEN && _isMapboxStyle) {
                window.MAPBOX_STYLE = 'https://basemaps.cartocdn.com/gl/positron-gl-style/style.json';
            }
            window.DEFAULT_MAPBOX_TOKEN = window.MAPBOX_TOKEN || '';
            if (window.MAPBOX_TOKEN && typeof mapboxgl !== 'undefined') {
                mapboxgl.accessToken = window.MAPBOX_TOKEN;
            }
            if (window.MAPBOX_TOKEN) {
                window.DEFAULT_MAPBOX_TOKEN = window.MAPBOX_TOKEN;
                if (typeof mapboxgl !== 'undefined') {
                    mapboxgl.accessToken = window.MAPBOX_TOKEN;
                }
            }
            // always dispatch — gh-home-map.js handles OSM fallback without token
            setTimeout(function(){ window.dispatchEvent(new CustomEvent('fastnet:mapbox-ready')); }, 0);

            // Fallback runtime fetch if token not server-injected (e.g. other pages or env missing)
            if (!window.MAPBOX_TOKEN) {
                fetch(window.API_URL('/api/map-config'))
                    .then(res => res.json())
                    .then(data => {
                        const tok = data && (data.mapbox_token || data.mapboxToken || data.token || (data.data && data.data.mapbox_token));
                        const sty = data && (data.mapbox_style || data.style);
                        if (tok && tok !== 'YOUR_MAPBOX_ACCESS_TOKEN' && tok !== 'pk.placeholder' && tok !== '' && tok.indexOf('pk.')===0) {
                            window.MAPBOX_TOKEN = tok;
                            window.DEFAULT_MAPBOX_TOKEN = tok;
                            if (sty) window.MAPBOX_STYLE = sty;
                            if (typeof mapboxgl !== 'undefined') {
                                mapboxgl.accessToken = tok;
                            }
                            window.dispatchEvent(new CustomEvent('fastnet:mapbox-ready'));
                        } else if (sty) {
                            window.MAPBOX_STYLE = sty;
                            window.dispatchEvent(new CustomEvent('fastnet:mapbox-ready'));
                        }
                    })
                    .catch(err => console.warn('Mapbox config error:', err));
            }

            // Global password toggle helper function
            function togglePasswordVisibility(fieldId, iconEl) {
                const input = document.getElementById(fieldId);
                if (!input) return;
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                
                const icon = iconEl.querySelector('i');
                if (icon) {
                    if (isPassword) {
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                }
            }
        </script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/MaterialDesign-Webfont/7.4.47/css/materialdesignicons.min.css">
    </head>

    <body>
        <a href="#main-content" class="cds-skip-link">Skip to main content</a>
        <!-- Universal Shared App Loader & Background Task Indicator -->
        <?= $this->element('app-loader') ?>

        <div id="main-wrapper">

            <!-- Flash messages (Carbon notifications) — rendered here so notices
                 never pile up unseen across public pages -->
            <style>
            .message{font-family:'IBM Plex Sans','Inter',Roboto,Arial,sans-serif;font-size:14px;line-height:1.5;padding:12px 16px;margin:12px 0;background:#f4f4f4;border:1px solid #e0e0e0;border-left:3px solid #0f62fe;color:#161616;cursor:pointer}
            .message.error{background:#fff1f1;border-left-color:#da1e28}
            .message.success{background:#defbe6;border-left-color:#24a148}
            .message.warning{background:#fcf4d6;border-left-color:#f1c21b}
            .message.hidden{display:none}
            </style>
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
            '/assets/js/custom.js',
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
        <script>
        window.fnsToast = window.fnsToast || function(msg, ms){
            var t = document.getElementById('fns_toast');
            if(!t) return;
            t.textContent = msg; t.style.display = 'block';
            clearTimeout(t._t); t._t = setTimeout(function(){ t.style.display = 'none'; }, ms || 2800);
        };
        </script>

    </body>
</html>
