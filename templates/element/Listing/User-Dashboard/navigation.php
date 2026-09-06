<div class="col-xl-12 col-lg-12 col-md-12 mb-4 mt-6 mt-md-0">
    <button class="btn btn-dark fw-medium full-width d-block d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#offcanvasDashboard" aria-controls="offcanvasDashboard"><i class="fa-solid fa-gauge me-2"></i>
        Dashboard Navigation
    </button>
    <div class="offcanvas offcanvas-start" data-bs-scroll="true" data-bs-backdrop="false" tabindex="-1" id="offcanvasDashboard" aria-labelledby="offcanvasScrollingLabel">
        <div class="offcanvas-header gray-simple">
            <h5 class="offcanvas-title" id="offcanvasScrollingLabel">Navigation</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0 nav-menus-wrapper">
            <ul class="user-Dashboard-longmenu">
                <li><a href="<?= $this->Url->build('/my-profile'); ?>" class="sub-menu-item"><i class="fa-regular fa-id-card me-2"></i>My Profile</a></li>
                <li><a href="<?= $this->Url->build('/my-booking'); ?>" class="sub-menu-item"><i class="fa-solid fa-ticket me-2"></i>My Booking</a></li>

                <li><a href="<?= $this->Url->build('/payment-detail'); ?>" class="sub-menu-item"><i class="fa-solid fa-wallet me-2"></i>Payment Details</a></li>
                <li><a href="<?= $this->Url->build('/my-wishlists'); ?>" class="sub-menu-item"><i class="fa-solid fa-shield-heart me-2"></i>My Wishlist</a></li>
                <li><a href="<?= $this->Url->build('/settings'); ?>" class="sub-menu-item"><i class="fa-solid fa-sliders me-2"></i>Settings</a></li>
                <li><a href="<?= $this->Url->build('/delete-account'); ?>" class="sub-menu-item"><i class="fa-solid fa-trash-can me-2"></i>Delete Profile</a></li>
                <li><a href="<?= $this->Url->build('/login'); ?>" class="sub-menu-item"><i class="fa-solid fa-power-off me-2"></i>Sign Out</a></li>
            </ul>
        </div>
    </div>
</div>