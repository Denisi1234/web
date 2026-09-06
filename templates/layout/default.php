<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= $this->fetch('title') ? h($this->fetch('title')) . ' | fastnetstays.com' : 'fastnetstays.com - Hotel Deals, Lodges & Beach Escapes' ?></title>
        <meta name="description" content="Book hotel rooms, luxury resorts, and beach escapes across Tanzania with fastnetstays.com." />
	    <meta name="author" content="fastnetstays.com" />
	    <meta name="website" content="https://fastnetstays.com" />
	    <meta name="email" content="support@fastnetstays.com" />
        <meta name="csrfToken" content="<?= $this->request->getAttribute('csrfToken'); ?>">
	    <meta name="version" content="1.0.0" />
        <link rel="icon" type="image/x-icon" href="<?= $this->Url->build('/assets/img/favicon.png'); ?>">

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
