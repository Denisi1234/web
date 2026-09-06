<?php
/**
 * Trivago Recently Viewed Profile Page
 */
$this->assign('title', 'Recently viewed - FastNet Stays');
?>
<?= $this->element('navbar'); ?>

<style>
/* ── Trivago Recently Viewed Page Styles ─────────────────────────── */
.trivago-profile-wrapper {
    background-color: #ffffff;
    min-height: calc(100vh - 70px);
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    color: #0f172a;
    padding-top: 36px;
    padding-bottom: 70px;
}

/* Left Sidebar Navigation */
.trivago-back-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #0f172a;
    font-size: 15px;
    font-weight: 600;
    text-decoration: none !important;
    margin-bottom: 24px;
    padding: 6px 12px;
    border-radius: 6px;
    transition: background-color 0.12s ease;
}
.trivago-back-btn:hover {
    background-color: #f1f5f9;
    color: #007fad;
}

.trivago-side-nav {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.trivago-side-link {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 12px 18px;
    border-radius: 10px;
    color: #1e293b;
    font-size: 15px;
    font-weight: 600;
    text-decoration: none !important;
    transition: all 0.12s ease;
}
.trivago-side-link:hover {
    background-color: #f8fafc;
    color: #0f172a;
}
.trivago-side-link.active {
    background-color: #e0f2fe;
    color: #0284c7;
    font-weight: 700;
}

.trivago-side-link i {
    width: 20px;
    font-size: 18px;
    text-align: center;
}

/* Right Content Area */
.trivago-profile-header {
    margin-bottom: 12px;
}
.trivago-profile-title {
    font-size: 32px;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 12px;
    letter-spacing: -0.02em;
}

.trivago-empty-intro {
    font-size: 15.5px;
    color: #334155;
    margin-bottom: 30px;
}

/* Illustration Container */
.rv-illustration-wrapper {
    width: 100%;
    max-width: 820px;
    margin-bottom: 36px;
}
.rv-illustration-img {
    width: 100%;
    height: auto;
    display: block;
    object-fit: contain;
}

/* Search Stays CTA Button */
.rv-btn-search-stays {
    background-color: #007fad;
    color: #ffffff;
    font-size: 15px;
    font-weight: 700;
    padding: 12px 28px;
    border-radius: 8px;
    border: none;
    text-decoration: none !important;
    display: inline-block;
    cursor: pointer;
    transition: background-color 0.15s ease, transform 0.12s ease;
}
.rv-btn-search-stays:hover {
    background-color: #006b94;
    color: #ffffff;
    transform: translateY(-1px);
}

@media (max-width: 991px) {
    .trivago-profile-wrapper {
        padding-top: 16px;
    }
    .trivago-side-nav {
        margin-bottom: 24px;
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 16px;
    }
}
</style>

<div class="trivago-profile-wrapper">
    <div class="container" style="max-width: 1120px;">
        <div class="row">
            
            <!-- Reusable Left Sidebar -->
            <?= $this->element('profile_sidebar', ['active' => 'recently-viewed']); ?>

            <!-- Right Content -->
            <div class="col-lg-9 ps-lg-4">
                <div class="trivago-profile-header">
                    <h1 class="trivago-profile-title">Recently viewed</h1>
                    <p class="trivago-empty-intro">
                        Explore stays and we’ll save them here so you can easily continue your search later.
                    </p>
                </div>

                <!-- Hotel Bellhop Illustration Banner -->
                <div class="rv-illustration-wrapper">
                    <img src="<?= $this->Url->build('/assets/img/recently-viewed-illustration.png'); ?>" alt="Recently viewed illustration" class="rv-illustration-img">
                </div>

                <!-- Search Stays CTA Button aligned to bottom right -->
                <div class="d-flex justify-content-end" style="max-width: 820px;">
                    <a href="<?= $this->Url->build('/hotel-list-01'); ?>" class="rv-btn-search-stays">
                        Search stays
                    </a>
                </div>

            </div>

        </div>
    </div>
</div>
