<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= $this->fetch('title') ? h($this->fetch('title')) . ' | fastnetstays.com' : 'FastNet Stays — Hotel Deals, Lodges & Beach Escapes' ?></title>
        <meta name="author" content="FastNet Stays" />
        <meta name="website" content="https://www.fastnetstays.com" />
        <link rel="icon" type="image/png" sizes="32x32" href="<?= $this->Url->build('/assets/img/favicon-32x32.png'); ?>">
        <link rel="apple-touch-icon" sizes="180x180" href="<?= $this->Url->build('/assets/img/apple-touch-icon.png'); ?>">
        <link rel="manifest" href="<?= $this->Url->build('/manifest.json'); ?>">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

        <!-- Universal Loading System (must be first so no flash) -->
        <?= $this->Html->css('/assets/css/app-loader.css') ?>
        <?= $this->Html->css('/assets/css/fastnet-dots.css') ?>
        <?= $this->Html->script('/assets/js/app-loader.js?v=' . filemtime(WWW_ROOT . 'assets/js/app-loader.js')) ?>

        <!-- CSS Files -->
        <?= $this->Html->css([
            '/assets/css/bootstrap.min.css',
            '/assets/css/loading.css',
            '/assets/css/dropzone.min.css',
            '/assets/css/flatpickr.min.css',
            '/assets/css/flickity.min.css',
            '/assets/css/lightbox.min.css',
            '/assets/css/magnifypopup.css',
            '/assets/css/select2.min.css',
            '/assets/css/rangeSlider.min.css',
            '/assets/css/prism.css',
            '/assets/css/bootstrap-icons.css',
            '/assets/css/fontawesome.css',
            '/assets/css/style.css',
            '/assets/css/ui-tokens.css',
        ]); ?>

        <?= $this->fetch('meta') ?>
        <?= $this->fetch('css') ?>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/MaterialDesign-Webfont/7.4.47/css/materialdesignicons.min.css">
    </head>

    <body class="bg-light">

        <!-- Universal Loading Indicators (replaces old #preloader div) -->
        <div id="fastnet-top-progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-label="Page loading progress"></div>
        <div id="fastnet-bg-loader" role="status" aria-live="polite" aria-atomic="true"></div>
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

        <div id="main-wrapper">

            <?= $this->Flash->render() ?>
            <!-- Main Content -->
        	<?= $this->fetch('content') ?>

        </div>

        <!-- JavaScript Files -->
        <?= $this->Html->script('/assets/js/loading.js') ?>
        <?= $this->Html->script('/assets/js/fastnet-api.js') ?>
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
            '/assets/js/prism.js',
            '/assets/js/custom.js',
        ]); ?>

        <!-- Global toast -->
        <div id="fns_toast" role="status" aria-live="polite" aria-atomic="true" style="position:fixed;bottom:20px;bottom:calc(20px + env(safe-area-inset-bottom));left:50%;transform:translateX(-50%);background:#161616;color:#fff;padding:11px 18px;border-radius:9999px;font-size:13px;font-weight:500;display:none;z-index:4000;box-shadow:0 8px 30px rgba(0,0,0,.18);max-width:min(92vw,420px);text-align:center;pointer-events:none;font-family:'IBM Plex Sans',Roboto,sans-serif"></div>
        <script>
        window.fnsToast = window.fnsToast || function(msg, ms){
            var t = document.getElementById('fns_toast');
            if(!t) return;
            t.textContent = msg; t.style.display = 'block';
            clearTimeout(t._t); t._t = setTimeout(function(){ t.style.display = 'none'; }, ms || 2800);
        };
        </script>

        <?= $this->fetch('script') ?>

    </body>

</html>