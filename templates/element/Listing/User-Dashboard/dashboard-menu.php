<?php
$currentAction = $this->request->getParam('action');
?>
<!-- User Dashboard Menu -->
<div class="dashboard-menus border-top d-none d-lg-block bg-white shadow-2xs">
    <div class="container">
        <div class="row">
            <div class="col-xl-12 col-lg-12 col-md-12 nav-menus-wrapper">
                <ul class="user-Dashboard-menu d-flex align-items-center gap-1 mb-0 py-2">
                    <li>
                        <a href="<?= $this->Url->build('/my-profile'); ?>" class="sub-menu-item <?= ($currentAction === 'myProfile') ? 'active text-primary fw-bold' : '' ?>">
                            <i class="fa-regular fa-id-card me-2"></i>My Profile
                        </a>
                    </li>
                    <li>
                        <a href="<?= $this->Url->build('/my-booking'); ?>" class="sub-menu-item <?= ($currentAction === 'myBooking') ? 'active text-primary fw-bold' : '' ?>">
                            <i class="fa-solid fa-ticket me-2"></i>My Bookings
                        </a>
                    </li>
                    <li>
                        <a href="<?= $this->Url->build('/my-wishlists'); ?>" class="sub-menu-item <?= ($currentAction === 'myWishlists') ? 'active text-primary fw-bold' : '' ?>">
                            <i class="fa-solid fa-shield-heart me-2 text-danger"></i>My Wishlist
                        </a>
                    </li>
                    <li>
                        <a href="<?= $this->Url->build('/settings'); ?>" class="sub-menu-item <?= ($currentAction === 'settings') ? 'active text-primary fw-bold' : '' ?>">
                            <i class="fa-solid fa-sliders me-2"></i>Settings
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0);" onclick="logoutUser()" class="sub-menu-item text-danger">
                            <i class="fa-solid fa-power-off me-2"></i>Sign Out
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
<!-- End User Dashboard Menu -->