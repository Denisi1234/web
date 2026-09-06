<?php
$footerSkin = $skin ?? 'skin-light-footer';
$isDark = str_contains($footerSkin, 'dark');
?>
<?= $this->Html->css('/assets/css/shared-footer.css') ?>
<footer class="footer <?= $footerSkin ?>">
    <div>
        <div class="container">
            <div class="row">

                <!-- Brand & Description -->
                <div class="col-lg-3 col-md-4">
                    <div class="footer-widget">
                        <div class="d-flex align-items-start flex-column mb-3">
                            <div class="d-inline-block mb-2">
                                <a class="d-flex flex-column text-decoration-none py-1" href="<?= $this->Url->build('/'); ?>" title="FastNetStays.com">
                                    <div class="d-flex align-items-baseline lh-1">
                                        <span class="fw-bold tracking-tight font-sans fs-4 me-0" style="color: <?= $isDark ? '#60a5fa' : '#003580' ?> !important;">FASTNET</span>
                                        <span class="fw-bold tracking-tight font-sans fs-4 me-0" style="color: #f87171 !important;">STAYS</span>
                                        <span class="fw-bold <?= $isDark ? 'text-light-50' : 'text-muted' ?> font-sans fs-6" style="font-size: 13px;">.com</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-1 mt-1">
                                        <span class="rounded-circle bg-danger d-inline-block" style="width: 8px; height: 8px;"></span>
                                        <span class="rounded-circle d-inline-block" style="width: 8px; height: 8px; background-color: #f97316;"></span>
                                        <span class="rounded-circle d-inline-block" style="width: 8px; height: 8px; background-color: #eab308;"></span>
                                        <span class="rounded-circle bg-success d-inline-block" style="width: 8px; height: 8px;"></span>
                                        <span class="rounded-circle bg-primary d-inline-block" style="width: 8px; height: 8px;"></span>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="footer-add pe-xl-3 mb-3">
                            <p class="<?= $isDark ? 'text-light-50' : 'text-muted' ?>">Book hotel rooms and fast stays instantly across Tanzania with fastnetstays.com.</p>
                        </div>
                        <div class="foot-socials">
                            <ul class="d-flex align-items-center gap-2 p-0 m-0 list-unstyled">
                                <li><a href="https://instagram.com" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-light-secondary rounded-circle d-flex align-items-center justify-content-center p-0" style="width:36px; height:36px;"><i class="fa-brands fa-instagram"></i></a></li>
                                <li><a href="https://facebook.com" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-light-secondary rounded-circle d-flex align-items-center justify-content-center p-0" style="width:36px; height:36px;"><i class="fa-brands fa-facebook-f"></i></a></li>
                                <li><a href="https://linkedin.com" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-light-secondary rounded-circle d-flex align-items-center justify-content-center p-0" style="width:36px; height:36px;"><i class="fa-brands fa-linkedin-in"></i></a></li>
                                <li><a href="https://twitter.com" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-light-secondary rounded-circle d-flex align-items-center justify-content-center p-0" style="width:36px; height:36px;"><i class="fa-brands fa-x-twitter"></i></a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Product Column -->
                <div class="col-lg-2 col-md-4">
                    <div class="footer-widget">
                        <h4 class="widget-title">Product</h4>
                        <ul class="footer-menu">
                            <li><a href="<?= $this->Url->build('/about-us'); ?>">How FastNetStays Works</a></li>
                            <li><a href="<?= $this->Url->build('/hotel-list-01'); ?>">Browse Stays</a></li>
                            <li><a href="<?= $this->Url->build('/destination-01'); ?>">Destinations</a></li>
                            <li><a href="<?= $this->Url->build('/privacy-policy'); ?>">Privacy Policy</a></li>
                            <li><a href="<?= $this->Url->build('/terms-of-service'); ?>">Terms of Use</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Company Column -->
                <div class="col-lg-2 col-md-4">
                    <div class="footer-widget">
                        <h4 class="widget-title">Company</h4>
                        <ul class="footer-menu">
                            <li><a href="<?= $this->Url->build('/about-us'); ?>">About Us</a></li>
                            <li><a href="<?= $this->Url->build('/faq'); ?>">FAQ</a></li>
                            <li><a href="<?= $this->Url->build('/contact'); ?>">Contact Support</a></li>
                            <li><a href="mailto:partners@fastnetstays.com">Partnership</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Contact Column -->
                <div class="col-lg-2 col-md-6">
                    <div class="footer-widget">
                        <h4 class="widget-title">Contact</h4>
                        <ul class="footer-menu">
                            <li><a href="<?= $this->Url->build('/contact'); ?>">Get Help 24/7</a></li>
                            <li><a href="<?= $this->Url->build('/contact'); ?>">Support Center</a></li>
                            <li><a href="mailto:support@fastnetstays.com">Email Us</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Get the App & Mobile Payments -->
                <div class="col-lg-3 col-md-6">
                    <div class="footer-widget">
                        <h4 class="widget-title">Get the App</h4>
                        <div class="d-flex flex-column gap-2 mb-4">
                            <a href="https://apple.com/app-store/" target="_blank" class="btn btn-dark d-inline-flex align-items-center gap-2 px-3 py-2 rounded-3 text-start w-100" style="max-width: 200px;">
                                <i class="fa-brands fa-apple fs-4"></i>
                                <div class="lh-1">
                                    <div style="font-size: 9px;" class="text-uppercase text-muted">Download on</div>
                                    <div class="fw-bold" style="font-size: 13px;">App Store</div>
                                </div>
                            </a>
                            <a href="https://play.google.com/store" target="_blank" class="btn btn-dark d-inline-flex align-items-center gap-2 px-3 py-2 rounded-3 text-start w-100" style="max-width: 200px;">
                                <i class="fa-brands fa-google-play fs-5"></i>
                                <div class="lh-1">
                                    <div style="font-size: 9px;" class="text-uppercase text-muted">GET IT ON</div>
                                    <div class="fw-bold" style="font-size: 13px;">Google Play</div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="footer-bottom border-top">
        <div class="container">
            <div class="row align-items-center justify-content-between">
                <div class="col-xl-6 col-lg-6 col-md-6">
                    <p class="mb-0">© <?= date('Y') ?> fastnetstays.com. All rights reserved.</p>
                </div>
                <div class="col-xl-6 col-lg-6 col-md-6">
                    <ul class="p-0 d-flex justify-content-start justify-content-md-end text-start text-md-end m-0 list-unstyled">
                        <li><a href="<?= $this->Url->build('/privacy-policy'); ?>">Terms of Service</a></li>
                        <li class="ms-3"><a href="<?= $this->Url->build('/privacy-policy'); ?>">Privacy Policy</a></li>
                        <li class="ms-3"><a href="<?= $this->Url->build('/faq'); ?>">FAQ</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</footer>
