<?php
/**
 * fastnetstays.com - Unified Shared Header Component
 * Reusable across all application pages (Home, Search/Stays, Detail, Checkout, Account).
 * Features dual-layer authentication sync (CakePHP Session + Client-Side localStorage).
 */

$controller = $this->request->getParam('controller');
$action = $this->request->getParam('action');
$showNavSearchBar = in_array($action, ['hotelList01', 'detail', 'hotelDetail']) || ($controller === 'Stays' && in_array($action, ['index', 'detail']));

// ── Authentication Resolution ──
$session = $this->getRequest()->getSession();
$isLoggedOut = (bool)$session->read('is_logged_out');
$sessionUser = $session->read('User');
$effectiveUser = !empty($userProfile) ? $userProfile : $sessionUser;
$isUserLoggedIn = !$isLoggedOut && !empty($effectiveUser) && (!empty($effectiveUser['id']) || !empty($effectiveUser['email']) || !empty($effectiveUser['name']));

$navUserInitial = !empty($effectiveUser['first_name']) 
    ? strtoupper(substr(trim($effectiveUser['first_name']), 0, 1)) 
    : (!empty($effectiveUser['name']) ? strtoupper(substr(trim($effectiveUser['name']), 0, 1)) : 'U');

$navUserName = !empty($effectiveUser['first_name']) 
    ? $effectiveUser['first_name'] 
    : (!empty($effectiveUser['name']) ? explode(' ', trim($effectiveUser['name']))[0] : 'Traveler');
?>
<?= $this->Html->css('/assets/css/navbar.css?v=' . filemtime(WWW_ROOT . 'assets/css/navbar.css')); ?>
<?= $this->Html->css('/assets/css/shared-header-mobile.css') ?>

<!-- Shared fastnetstays.com Accommodation Header -->
<div class="agoda-sticky-wrapper">
    <header class="agoda-top-header" style="height: 64px;">
        <div class="container-fluid px-2 px-md-3 px-lg-4 max-w-[1440px] mx-auto h-100">
            <div class="d-flex align-items-center justify-content-between flex-wrap flex-lg-nowrap h-100">
                
                <!-- fastnetstays.com Brand Logo -->
                <a class="d-flex align-items-center text-decoration-none py-1 select-none me-2 me-lg-3 flex-shrink-0 order-1" href="<?= $this->Url->build('/'); ?>" title="fastnetstays.com">
                    <span style="font-size: 23px; font-weight: 850; letter-spacing: -0.03em; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1;">
                        <span style="color: #d93025;">fast</span><span style="color: #1a73e8;">net</span><span style="color: #f29900;">stays</span><span style="color: #64748b; font-size: 16px; font-weight: 700;">.com</span>
                    </span>
                </a>

                <!-- Context-Aware Search Bar (Order 2 on desktop inline, Order 3 on mobile full-width row) -->
                <?php if ($showNavSearchBar): ?>
                    <div class="order-3 order-lg-2 w-100 w-lg-auto flex-lg-grow-1 mx-lg-3 my-2 my-lg-0" style="max-width: 840px;" id="nav_search_wrapper">
                        <?= $this->element('nav/hotel-search-bar'); ?>
                    </div>
                <?php endif; ?>

                <!-- 1. Logged-in Header Controls (Order 2 on mobile ms-auto, Order 3 on desktop) -->
                <div class="d-flex align-items-center gap-2 gap-md-3 ms-auto flex-shrink-0 order-2 order-lg-3" id="nav_logged_in_wrapper" style="<?= $isUserLoggedIn ? '' : 'display: none !important;'; ?>">
                    <!-- Favourites Link -->
                    <a href="<?= $this->Url->build('/my-wishlists'); ?>" class="d-flex align-items-center text-decoration-none text-slate-700 hover:text-primary fw-semibold p-1" style="font-size: 14px; gap: 6px;">
                        <i class="fa-regular fa-heart" style="font-size: 17px;"></i>
                        <span class="d-none d-xl-inline">Favourites</span>
                    </a>

                    <!-- Language & Currency Dropdown (Desktop Only, hidden on mobile like Trivago) -->
                    <div class="position-relative d-none d-lg-block" id="nav_lang_menu_wrapper">
                        <button type="button" class="btn btn-link text-decoration-none fw-semibold text-slate-700 hover:text-primary d-flex align-items-center p-1" id="nav_lang_btn" onclick="toggleNavLangDropdown(event)" style="font-size: 14px; gap: 6px;">
                            <i class="fa-solid fa-globe"></i>
                            <span>EN · $</span>
                        </button>

                        <div class="trivago-user-dropdown" id="nav_lang_dropdown" onclick="event.stopPropagation();" style="width: 240px;">
                            <div class="trivago-user-sec-title">Language</div>
                            <a href="javascript:void(0);" class="trivago-user-item fw-bold text-primary" onclick="setLang('EN · $')">
                                <i class="fa-solid fa-check trivago-user-item-icon text-primary"></i>
                                <span>English (US)</span>
                            </a>
                            <a href="javascript:void(0);" class="trivago-user-item" onclick="setLang('SW · TSh')">
                                <i class="fa-solid fa-globe trivago-user-item-icon"></i>
                                <span>Kiswahili (TZ)</span>
                            </a>

                            <div class="trivago-user-sec-title" style="border-top: 1px solid #f1f5f9; margin-top: 6px; padding-top: 10px;">Currency</div>
                            <a href="javascript:void(0);" class="trivago-user-item fw-bold text-primary" onclick="setLang('EN · $')">
                                <i class="fa-solid fa-check trivago-user-item-icon text-primary"></i>
                                <span>USD ($)</span>
                            </a>
                            <a href="javascript:void(0);" class="trivago-user-item" onclick="setLang('SW · TSh')">
                                <i class="fa-solid fa-coins trivago-user-item-icon"></i>
                                <span>TZS (TSh)</span>
                            </a>
                            <a href="javascript:void(0);" class="trivago-user-item" onclick="setLang('EN · €')">
                                <i class="fa-solid fa-euro-sign trivago-user-item-icon"></i>
                                <span>EUR (€)</span>
                            </a>
                        </div>
                    </div>

                    <!-- User Account Dropdown -->
                    <div class="position-relative" id="nav_user_menu_wrapper">
                        <button type="button" class="border-0 bg-transparent p-0 d-flex align-items-center justify-content-center" id="nav_user_btn" onclick="toggleNavUserDropdown(event)" style="width: 38px; height: 38px; border-radius: 8px; border: 1.5px solid #0284c7 !important; background-color: #f0f9ff; color: #0284c7; font-weight: 800; font-size: 16px; transition: all 0.15s ease;">
                            <?= htmlspecialchars($navUserInitial); ?>
                        </button>

                        <!-- User Account Menu -->
                        <div class="trivago-user-dropdown" id="nav_user_dropdown" onclick="event.stopPropagation();">
                            <a href="<?= $this->Url->build('/my-profile'); ?>" class="trivago-user-head-row text-decoration-none">
                                <span class="fw-bold text-slate-900" style="font-size: 14.5px;">Welcome back, <?= htmlspecialchars($navUserName); ?></span>
                                <i class="fa-solid fa-chevron-right text-slate-400 ms-auto" style="font-size: 13px;"></i>
                            </a>

                            <div class="trivago-user-scroll-body">
                                <div class="trivago-user-sec-title">Account</div>
                                <a href="<?= $this->Url->build('/my-profile'); ?>" class="trivago-user-item">
                                    <i class="fa-regular fa-circle-user trivago-user-item-icon"></i>
                                    <span>Personal info</span>
                                </a>
                                <a href="<?= $this->Url->build('/security'); ?>" class="trivago-user-item">
                                    <i class="fa-solid fa-lock trivago-user-item-icon"></i>
                                    <span>Account security</span>
                                </a>

                                <div class="trivago-user-sec-title">Trips</div>
                                <a href="<?= $this->Url->build('/my-wishlists'); ?>" class="trivago-user-item">
                                    <i class="fa-regular fa-heart trivago-user-item-icon"></i>
                                    <span>Favourites</span>
                                </a>
                                <a href="<?= $this->Url->build('/recently-viewed'); ?>" class="trivago-user-item">
                                    <i class="fa-solid fa-clock-rotate-left trivago-user-item-icon"></i>
                                    <span>Recently viewed</span>
                                </a>
                                <a href="<?= $this->Url->build('/my-booking'); ?>" class="trivago-user-item">
                                    <i class="fa-solid fa-suitcase trivago-user-item-icon"></i>
                                    <span>Bookings</span>
                                </a>

                                <div class="trivago-user-sec-title">Preferences</div>
                                <a href="<?= $this->Url->build('/search-preferences'); ?>" class="trivago-user-item">
                                    <i class="fa-solid fa-magnifying-glass trivago-user-item-icon"></i>
                                    <span>Search preferences</span>
                                </a>
                                <a href="<?= $this->Url->build('/notifications'); ?>" class="trivago-user-item">
                                    <i class="fa-regular fa-bell trivago-user-item-icon"></i>
                                    <span>Notifications</span>
                                </a>
                                <a href="<?= $this->Url->build('/language-and-currency'); ?>" class="trivago-user-item">
                                    <i class="fa-solid fa-globe trivago-user-item-icon"></i>
                                    <span>Language and currency</span>
                                </a>

                                <div class="trivago-user-sec-title">Support</div>
                                <a href="<?= $this->Url->build('/help-center'); ?>" class="trivago-user-item">
                                    <i class="fa-regular fa-circle-question trivago-user-item-icon"></i>
                                    <span>Help and FAQ</span>
                                </a>
                                <a href="<?= $this->Url->build('/logout'); ?>" class="trivago-user-item text-danger" onclick="handleNavLogout(event)">
                                    <i class="fa-solid fa-arrow-right-from-bracket trivago-user-item-icon text-danger"></i>
                                    <span>Log out</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Logged-out Header Controls ([🌐 EN · $] [👤 Sign in] [☰ Menu]) -->
                <div class="d-flex align-items-center gap-4 ms-auto" id="nav_logged_out_wrapper" style="<?= $isUserLoggedIn ? 'display: none !important;' : ''; ?>">
                    <!-- Language & Currency Button (Desktop Only) -->
                    <div class="position-relative d-none d-lg-block" id="nav_logged_out_lang_wrapper">
                        <button type="button" class="btn btn-link text-decoration-none fw-bold text-slate-800 hover:text-primary d-flex align-items-center p-0 border-0" id="nav_logged_out_lang_btn" onclick="toggleNavLangDropdown(event)" style="font-size: 15px; gap: 8px; color: #0f172a !important;">
                            <i class="fa-solid fa-globe" style="font-size: 17px;"></i>
                            <span>EN · $</span>
                        </button>

                        <div class="trivago-user-dropdown" id="nav_logged_out_lang_dropdown" onclick="event.stopPropagation();" style="width: 240px;">
                            <div class="trivago-user-sec-title">Language</div>
                            <a href="javascript:void(0);" class="trivago-user-item fw-bold text-primary" onclick="setLang('EN · $')">
                                <i class="fa-solid fa-check trivago-user-item-icon text-primary"></i>
                                <span>English (US)</span>
                            </a>
                            <a href="javascript:void(0);" class="trivago-user-item" onclick="setLang('SW · TSh')">
                                <i class="fa-solid fa-globe trivago-user-item-icon"></i>
                                <span>Kiswahili (TZ)</span>
                            </a>

                            <div class="trivago-user-sec-title" style="border-top: 1px solid #f1f5f9; margin-top: 6px; padding-top: 10px;">Currency</div>
                            <a href="javascript:void(0);" class="trivago-user-item fw-bold text-primary" onclick="setLang('EN · $')">
                                <i class="fa-solid fa-check trivago-user-item-icon text-primary"></i>
                                <span>USD ($)</span>
                            </a>
                            <a href="javascript:void(0);" class="trivago-user-item" onclick="setLang('SW · TSh')">
                                <i class="fa-solid fa-coins trivago-user-item-icon"></i>
                                <span>TZS (TSh)</span>
                            </a>
                        </div>
                    </div>

                    <!-- Sign In Link -->
                    <a href="<?= $this->Url->build('/login'); ?>" class="d-flex align-items-center text-decoration-none fw-bold text-slate-800 hover:text-primary p-0" style="font-size: 15px; gap: 8px; color: #0f172a !important;">
                        <i class="fa-regular fa-circle-user" style="font-size: 18px;"></i>
                        <span>Sign in</span>
                    </a>

                    <!-- Menu Trigger -->
                    <div class="position-relative" id="nav_logged_out_menu_wrapper">
                        <button type="button" class="btn btn-link text-decoration-none fw-bold text-slate-800 hover:text-primary d-flex align-items-center p-0 border-0" id="nav_logged_out_menu_btn" onclick="handleMenuClick(event)" style="font-size: 15px; gap: 8px; color: #0f172a !important;">
                            <i class="fa-solid fa-bars" style="font-size: 17px;"></i>
                            <span>Menu</span>
                        </button>

                        <!-- Logged-out Desktop Menu Dropdown -->
                        <div class="trivago-user-dropdown" id="nav_logged_out_menu_dropdown" onclick="event.stopPropagation();" style="width: 240px;">
                            <a href="<?= $this->Url->build('/login'); ?>" class="trivago-user-item fw-bold text-primary">
                                <i class="fa-regular fa-circle-user trivago-user-item-icon text-primary"></i>
                                <span>Sign in</span>
                            </a>
                            <a href="<?= $this->Url->build('/recently-viewed'); ?>" class="trivago-user-item">
                                <i class="fa-solid fa-clock-rotate-left trivago-user-item-icon"></i>
                                <span>Recently viewed</span>
                            </a>
                            <a href="<?= $this->Url->build('/language-and-currency'); ?>" class="trivago-user-item">
                                <i class="fa-solid fa-globe trivago-user-item-icon"></i>
                                <span>Language and currency</span>
                            </a>
                            <a href="<?= $this->Url->build('/help-center'); ?>" class="trivago-user-item">
                                <i class="fa-regular fa-circle-question trivago-user-item-icon"></i>
                                <span>Help and support</span>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </header>
</div>

<script>
// Direct inline fail-safe event binding for instant click response
(function() {
    function closeAllPopups() {
        document.querySelectorAll('.trivago-user-dropdown').forEach(function(d) {
            d.classList.remove('show');
        });
        var btn = document.getElementById('nav_user_btn');
        if (btn) btn.style.outline = 'none';
    }

    document.addEventListener('DOMContentLoaded', function() {
        var menuBtn = document.getElementById('nav_logged_out_menu_btn');
        var userBtn = document.getElementById('nav_user_btn');
        var langBtnIn = document.getElementById('nav_lang_btn');
        var langBtnOut = document.getElementById('nav_logged_out_lang_btn');

        if (menuBtn) {
            menuBtn.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                var drop = document.getElementById('nav_logged_out_menu_dropdown');
                if (!drop) return;
                var wasOpen = drop.classList.contains('show');
                closeAllPopups();
                if (!wasOpen) drop.classList.add('show');
            };
        }

        if (userBtn) {
            userBtn.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                var drop = document.getElementById('nav_user_dropdown');
                if (!drop) return;
                var wasOpen = drop.classList.contains('show');
                closeAllPopups();
                if (!wasOpen) {
                    drop.classList.add('show');
                    userBtn.style.outline = '2px solid #007fad';
                    userBtn.style.outlineOffset = '-2px';
                }
            };
        }

        [langBtnIn, langBtnOut].forEach(function(btn) {
            if (!btn) return;
            btn.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                var inWrap = document.getElementById('nav_logged_in_wrapper');
                var isLogged = inWrap && inWrap.style.display !== 'none';
                var dropId = isLogged ? 'nav_lang_dropdown' : 'nav_logged_out_lang_dropdown';
                var drop = document.getElementById(dropId) || document.getElementById('nav_lang_dropdown');
                if (!drop) return;
                var wasOpen = drop.classList.contains('show');
                closeAllPopups();
                if (!wasOpen) drop.classList.add('show');
            };
        });

        document.addEventListener('click', function(e) {
            if (!e.target.closest('#nav_logged_out_menu_wrapper') && 
                !e.target.closest('#nav_user_menu_wrapper') && 
                !e.target.closest('#nav_lang_menu_wrapper') && 
                !e.target.closest('#nav_logged_out_lang_wrapper')) {
                closeAllPopups();
            }
        });
    });
})();
</script>

<?= $this->Html->script('/assets/js/navbar.js?v=' . filemtime(WWW_ROOT . 'assets/js/navbar.js')); ?>
