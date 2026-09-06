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
        <link rel="canonical" href="<?= 'https://www.fastnetstays.com' . rawurldecode($this->getRequest()->getPath()) ?>" />
        <link rel="alternate" type="text/plain" href="https://www.fastnetstays.com/llms.txt" title="LLM Knowledge Graph" />

        <!-- Open Graph / Facebook / WhatsApp -->
        <meta property="og:type" content="website" />
        <meta property="og:site_name" content="FastNet Stays" />
        <meta property="og:url" content="<?= 'https://www.fastnetstays.com' . rawurldecode($this->getRequest()->getPath()) ?>" />
        <meta property="og:title" content="<?= $this->fetch('title') ? h($this->fetch('title')) . ' | fastnetstays.com' : 'FastNet Stays — Online Hotel Booking & Best Prices Guaranteed' ?>" />
        <meta property="og:description" content="Online Hotel Booking — FastNet Stays - Best Prices Guaranteed with Deals, Special Member Prices. Book Hotels, Lodges & Beach Resorts Across Tanzania!" />
        <meta property="og:image" content="https://www.fastnetstays.com/assets/img/og-preview.png" />

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:site" content="@fastnetstays" />
        <meta name="twitter:title" content="FastNet Stays — Online Hotel Booking & Best Prices Guaranteed" />
        <meta name="twitter:description" content="Book hotel rooms, luxury resorts, and beach escapes across Tanzania with fastnetstays.com." />
        <meta name="twitter:image" content="https://www.fastnetstays.com/assets/img/og-preview.png" />

        <!-- Favicon & Touch Icons for Google Search Snippet Logo -->
        <link rel="icon" type="image/png" sizes="32x32" href="<?= $this->Url->build('/assets/img/favicon.png'); ?>">
        <link rel="apple-touch-icon" sizes="180x180" href="<?= $this->Url->build('/assets/img/favicon.png'); ?>">
        <meta name="theme-color" content="#006CE4">

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
                  "urlTemplate": "https://www.fastnetstays.com/hotel-list-01?destination={search_term_string}"
                },
                "query-input": "required name=search_term_string"
              }
            },
            {
              "@type": "TravelAgency",
              "@id": "https://www.fastnetstays.com/#organization",
              "name": "FastNet Stays",
              "url": "https://www.fastnetstays.com",
              "logo": {
                "@type": "ImageObject",
                "url": "https://www.fastnetstays.com/assets/img/favicon.png"
              },
              "contactPoint": {
                "@type": "ContactPoint",
                "telephone": "+255-700-000-000",
                "contactType": "customer service",
                "email": "support@fastnetstays.com",
                "availableLanguage": ["English", "Swahili"]
              },
              "sameAs": [
                "https://www.fastnetstays.com"
              ]
            },
            {
              "@type": "ItemList",
              "itemListElement": [
                {
                  "@type": "SiteNavigationElement",
                  "position": 1,
                  "name": "Book Hotels & Lodges",
                  "description": "Best Price Guarantee, Special Member Offers & Huge Discounts",
                  "url": "https://www.fastnetstays.com/hotels"
                },
                {
                  "@type": "SiteNavigationElement",
                  "position": 2,
                  "name": "Zanzibar Beach Escapes",
                  "description": "Exclusive beachfront resorts, Stone Town hotels and villas",
                  "url": "https://www.fastnetstays.com/hotel-list-01?destination=Zanzibar"
                },
                {
                  "@type": "SiteNavigationElement",
                  "position": 3,
                  "name": "Serengeti & Arusha Safaris",
                  "description": "Safari lodges, luxury tented camps and wildlife retreats",
                  "url": "https://www.fastnetstays.com/hotel-list-01?destination=Arusha"
                },
                {
                  "@type": "SiteNavigationElement",
                  "position": 4,
                  "name": "List Your Property",
                  "description": "Partner with FastNet Stays and receive bookings with instant payouts",
                  "url": "https://www.fastnetstays.com/join-us"
                }
              ]
            }
          ]
        }
        </script>

        <!-- CSS Files -->
        <?= $this->Html->css([
            '/assets/css/bootstrap.min.css',
            '/assets/css/animation.css',
            '/assets/css/dropzone.min.css',
            '/assets/css/flatpickr.min.css',
            '/assets/css/flickity.min.css',
            '/assets/css/lightbox.min.css',
            '/assets/css/magnifypopup.css',
            '/assets/css/select2.min.css',
            '/assets/css/rangeSlider.min.css',
            '/assets/css/slick.css',
            '/assets/css/prism.css',
            '/assets/css/bootstrap-icons.css',
            '/assets/css/fontawesome.css',
            '/assets/css/style.css',
        ]); ?>

        <?= $this->fetch('meta') ?>
        <?= $this->fetch('css') ?>
        <?= $this->Html->css('/assets/css/site-spacing.css') ?>

        <!-- Mapbox GL JS -->
        <script src="https://api.mapbox.com/mapbox-gl-js/v3.1.2/mapbox-gl.js"></script>
        <link href="https://api.mapbox.com/mapbox-gl-js/v3.1.2/mapbox-gl.css" rel="stylesheet" />

        <script>
            window.FASTNET_API_URL = (
                window.location.hostname === 'localhost' ||
                window.location.hostname === '127.0.0.1' ||
                window.location.hostname === ''
            ) ? 'http://127.0.0.1:8000' : 'https://api.fastnetstays.com';

            window.API_URL = function (path) {
                return window.FASTNET_API_URL + path;
            };

            // Mapbox configuration is supplied by the backend at runtime.
            window.DEFAULT_MAPBOX_TOKEN = window.MAPBOX_TOKEN || '';
            window.MAPBOX_TOKEN = window.DEFAULT_MAPBOX_TOKEN;
            if (typeof mapboxgl !== 'undefined' && window.DEFAULT_MAPBOX_TOKEN) {
                mapboxgl.accessToken = window.DEFAULT_MAPBOX_TOKEN;
            }

            fetch(window.API_URL('/api/map-config'))
                .then(res => res.json())
                .then(data => {
                    if (data && data.mapbox_token && data.mapbox_token !== 'YOUR_MAPBOX_ACCESS_TOKEN') {
                        window.MAPBOX_TOKEN = data.mapbox_token;
                        if (typeof mapboxgl !== 'undefined') {
                            mapboxgl.accessToken = data.mapbox_token;
                        }
                        window.dispatchEvent(new CustomEvent('fastnet:mapbox-ready'));
                    }
                })
                .catch(err => console.warn('Mapbox config error:', err));

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
        
        <div id="preloader">
            <div class="preloader"><span></span><span></span></div>
        </div>

        <div id="main-wrapper">

            <!-- Main Content -->
        	<?= $this->fetch('content') ?>

            <a id="back2Top" class="top-scroll" title="Back to top" href="#"><i class="fa-solid fa-sort-up"></i></a>

        </div>

        <!-- JavaScript Files -->
        <?= $this->Html->script([
            '/assets/js/jquery.min.js',
            '/assets/js/popper.min.js',
            '/assets/js/bootstrap.min.js',
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
            '/assets/js/custom.js',
            '/assets/js/active.js',
            '/assets/js/contact.js',
        ]); ?>

        <?= $this->fetch('script') ?>

    </body>
</html>
