<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= $this->fetch('title') ? h($this->fetch('title')) . ' | fastnetstays.com' : 'fastnetstays.com - Hotel Deals, Lodges & Beach Escapes' ?></title>
        <meta name="description" content="Bootstrap 5 Landing Template" />
	    <meta name="author" content="Shreethemes" />
	    <meta name="website" content="https://shreethemes.in" />
	    <meta name="email" content="support@shreethemes.in" />
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
            '/assets/css/prism.css',
            '/assets/css/bootstrap-icons.css',
            '/assets/css/fontawesome.css',
            '/assets/css/style.css',
        ]); ?>

        <?= $this->fetch('meta') ?>
        <?= $this->fetch('css') ?>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/MaterialDesign-Webfont/7.4.47/css/materialdesignicons.min.css">
    </head>

    <body class="bg-light">

        <div id="preloader">
            <div class="preloader"><span></span><span></span></div>
        </div>

        <div id="main-wrapper">

            <!-- Main Content -->
        	<?= $this->fetch('content') ?>

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
            '/assets/js/prism.js',
            '/assets/js/custom.js',
        ]); ?>

        <?= $this->fetch('script') ?>

    </body>

</html>