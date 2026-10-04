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
                        <div style="margin:2px 0 4px;"><?= $this->element('logo', ['tone' => $isDark ? 'dark' : 'light', 'size' => 19]) ?></div>
                        <p class="footer-tagline <?= $isDark ? 'text-light-50' : 'text-muted' ?>">Book hotel rooms and fast stays instantly across Tanzania with fastnetstays.com.</p>
                        <div class="foot-socials">
                            <ul>
                                <li><a href="mailto:support@fastnetstays.com" aria-label="Email support"><i class="fa-regular fa-envelope"></i></a></li>
                                <li><a href="<?= $this->Url->build('/help-center'); ?>" aria-label="Help center"><i class="fa-regular fa-circle-question"></i></a></li>
                                <li><a href="<?= $this->Url->build('/language-and-currency'); ?>" aria-label="Language and currency"><i class="fa-solid fa-globe"></i></a></li>
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
                            <li><a href="<?= $this->Url->build('/?city=' . rawurlencode('Zanzibar')); ?>">Zanzibar Hotels</a></li>
                            <li><a href="<?= $this->Url->build('/?city=' . rawurlencode('Dar es Salaam')); ?>">Dar es Salaam Hotels</a></li>
                            <li><a href="<?= $this->Url->build('/?city=' . rawurlencode('Arusha')); ?>">Arusha Hotels</a></li>
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

            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <div class="footer-bottom-inner" style="justify-content:space-between;">
                <p>© <?= date('Y') ?> fastnetstays.com. All rights reserved.</p>
                <p><a href="<?= $this->Url->build('/language-and-currency'); ?>" aria-label="Language and currency settings"><i class="fa-solid fa-globe" style="margin-right:6px;"></i><span data-fx-footer-label>English · TZS</span></a></p>
            </div>
        </div>
    </div>
</footer>
<?php endif; ?>
