<?php
$footerSkin = $skin ?? 'skin-light-footer';
$isDark = str_contains($footerSkin, 'dark');
$isCompact = !empty($compact) && $compact === true;
?>
<?= $this->Html->css('/assets/css/shared-footer.css') ?>
<?php if ($isCompact): ?>
<!-- Google Travel Compact Micro-Footer — for index left scroll -->
<footer class="footer footer-compact <?= $footerSkin ?>">
    <div class="footer-compact-inner">
        <div class="fc-row fc-disclaimer"><span style="margin-right:4px;">ℹ️</span> Rates shown are nightly averages. Total includes estimated taxes and fees.</div>
        <div class="fc-row fc-links">
            <a href="<?= $this->Url->build('/about-us'); ?>">About</a><span class="fc-sep">·</span>
            <a href="<?= $this->Url->build('/privacy-policy'); ?>">Privacy</a><span class="fc-sep">·</span>
            <a href="<?= $this->Url->build('/terms-of-service'); ?>">Terms</a><span class="fc-sep">·</span>
            <a href="<?= $this->Url->build('/help-center'); ?>">Help</a><span class="fc-sep">·</span>
            <a href="<?= $this->Url->build('/faq'); ?>">FAQ</a>
        </div>
        <div class="fc-row fc-copy">© <?= date('Y') ?> FastNet Stays Ltd. All rights reserved.</div>
    </div>
</footer>
<?php else: ?>
<footer class="footer <?= $footerSkin ?>">
    <div>
        <div class="container">
            <div class="footer-grid">
                <!-- Column 1 Brand & Social ~28% -->
                <div class="footer-col footer-col--brand">
                    <div class="footer-widget">
                        <a class="footer-brand" href="<?= $this->Url->build('/'); ?>" title="FastNetStays.com" style="display:inline-flex;align-items:baseline;gap:3px;text-decoration:none;line-height:1;margin:2px 0 4px;flex-direction:row;">
                            <span style="font-family:'Grand Hotel','Brush Script MT',cursive;font-size:30px;font-weight:400;letter-spacing:-.02em;line-height:1;background:linear-gradient(45deg,#feda75 0%,#fa7e1e 18%,#d62976 38%,#962fbf 68%,#4f5bd5 100%);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;white-space:nowrap;display:inline-block;padding-bottom:2px;">FastNetStays</span>
                            <span style="font-family:'Grand Hotel',cursive;font-size:11px;color:<?= $isDark ? '#cbd5e1' : '#64748b' ?>;opacity:.95;transform:translateY(1px);">.com</span>
                        </a>
                        <p class="footer-tagline <?= $isDark ? 'text-light-50' : 'text-muted' ?>">Book hotel rooms and fast stays instantly across Tanzania with fastnetstays.com.</p>
                        <div class="foot-socials">
                            <ul>
                                <li><a href="https://instagram.com" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a></li>
                                <li><a href="https://facebook.com" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a></li>
                                <li><a href="https://linkedin.com" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a></li>
                                <li><a href="https://twitter.com" target="_blank" rel="noopener noreferrer" aria-label="X"><i class="fa-brands fa-x-twitter"></i></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- Column 2 Product ~18% -->
                <div class="footer-col footer-col--product">
                    <div class="footer-widget">
                        <h4 class="widget-title">Product</h4>
                        <ul class="footer-menu">
                            <li><a href="<?= $this->Url->build('/about-us'); ?>">How FastNetStays Works</a></li>
                            <li><a href="<?= $this->Url->build('/'); ?>">Browse Stays</a></li>
                            <li><a href="<?= $this->Url->build('/destination-01'); ?>">Destinations</a></li>
                            <li><a href="<?= $this->Url->build('/privacy-policy'); ?>">Privacy Policy</a></li>
                            <li><a href="<?= $this->Url->build('/terms-of-service'); ?>">Terms of Use</a></li>
                        </ul>
                    </div>
                </div>
                <!-- Column 3 Company ~18% -->
                <div class="footer-col footer-col--company">
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
                <!-- Column 4 Contact ~18% -->
                <div class="footer-col footer-col--contact">
                    <div class="footer-widget">
                        <h4 class="widget-title">Contact</h4>
                        <ul class="footer-menu">
                            <li><a href="<?= $this->Url->build('/contact'); ?>">Get Help 24/7</a></li>
                            <li><a href="<?= $this->Url->build('/contact'); ?>">Support Center</a></li>
                            <li><a href="mailto:support@fastnetstays.com">Email Us</a></li>
                        </ul>
                    </div>
                </div>
                <!-- Column 5 Get the App ~18% -->
                <div class="footer-col footer-col--app">
                    <div class="footer-widget">
                        <h4 class="widget-title">Get the App</h4>
                        <div class="footer-app-stack">
                            <a href="https://apple.com/app-store/" target="_blank" class="footer-app-badge" aria-label="Download on App Store">
                                <i class="fa-brands fa-apple"></i>
                                <span><small>Download on</small><strong>App Store</strong></span>
                            </a>
                            <a href="https://play.google.com/store" target="_blank" class="footer-app-badge" aria-label="Get it on Google Play">
                                <i class="fa-brands fa-google-play"></i>
                                <span><small>GET IT ON</small><strong>Google Play</strong></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <div class="footer-bottom-inner" style="justify-content:flex-start;">
                <p>© <?= date('Y') ?> fastnetstays.com. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>
<?php endif; ?>
