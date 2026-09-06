<div class="col-xl-4 col-lg-4 col-md-12">
    <div class="card rounded-3 me-xl-4 mb-4 border border-slate-200 shadow-sm overflow-hidden bg-white">
        <div class="card-top bg-primary position-relative p-4 text-center">
            <div class="position-absolute end-0 top-0 mt-3 me-3">
                <a href="javascript:void(0);" onclick="logoutUser()" class="square--35 circle bg-black/20 text-light d-flex align-items-center justify-content-center shadow-xs" title="Sign Out">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </a>
            </div>
            <div class="py-2">
                <div class="crd-thumbimg text-center mb-2">
                    <img src="<?= $this->Url->build('/assets/img/team-11.jpg'); ?>" class="img-fluid rounded-circle border border-3 border-white shadow-sm" width="90" height="90" style="object-fit: cover;" alt="User">
                </div>
                <div class="crd-capser text-center">
                    <h5 class="mb-0 text-light fw-bold fs-5" id="side-user-name">Guest Traveler</h5>
                    <span class="text-light opacity-75 fw-medium text-xs d-block" id="side-user-email">guest@fastnetstays.com</span>
                    <span class="badge bg-white/20 text-white text-xs mt-2"><i class="fa-solid fa-location-dot me-1"></i>Tanzania 🇹🇿</span>
                </div>
            </div>
        </div>

        <div class="card-middle px-4 py-4">
            <div class="crdapproval-groups">
                <div class="crdapproval-single d-flex align-items-center justify-content-start mb-3">
                    <div class="crdapproval-item">
                        <div class="square--40 circle bg-light-success text-success d-flex align-items-center justify-content-center"><i class="fa-solid fa-shield-check fs-6"></i></div>
                    </div>
                    <div class="crdapproval-caps ps-3">
                        <p class="fw-bold text-dark text-sm mb-0">FastNet Verified Member</p>
                        <span class="text-xs text-success"><i class="fa-solid fa-circle-check me-1"></i>Instant Booking Enabled</span>
                    </div>
                </div>

                <div class="crdapproval-single d-flex align-items-center justify-content-start mb-3">
                    <div class="crdapproval-item">
                        <div class="square--40 circle bg-light-primary text-primary d-flex align-items-center justify-content-center"><i class="fa-solid fa-wallet fs-6"></i></div>
                    </div>
                    <div class="crdapproval-caps ps-3">
                        <p class="fw-bold text-dark text-sm mb-0">Currency: TZS (TSh)</p>
                        <span class="text-xs text-muted">AzamPay & Mobile Money</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-3 border-top bg-slate-50 text-center">
            <a href="<?= $this->Url->build('/hotel-list-01'); ?>" class="btn btn-sm btn-primary full-width rounded-full fw-bold">
                <i class="fa-solid fa-magnifying-glass me-1"></i>Search Stays
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    try {
        const userStr = localStorage.getItem('user');
        if (userStr) {
            const user = JSON.parse(userStr);
            if (user.name) document.getElementById('side-user-name').textContent = user.name;
            if (user.email) document.getElementById('side-user-email').textContent = user.email;
        }
    } catch(e) {}
});
</script>