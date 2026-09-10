<?php
$this->assign('title', 'Settings & Email Preferences | FastNetStays');
?>

<!-- Include Navbar -->
<?= $this->Html->css('/assets/css/google-travel-layout.css') ?>
<?= $this->Html->css('/assets/css/google-travel-home.css') ?>
<?= $this->element('navbar') ?>
<div class="gh-m-tabs" role="tablist" aria-label="Travel types">
  <a href="/?explore=1" role="tab">Explore</a>
  <a href="/?homes=1" role="tab">Homes</a>
  <a href="/" role="tab" class="active" aria-selected="true">Hotels</a>
  <a href="/?destination=Vacation" role="tab">Vacation rentals</a>
</div>
<nav aria-label="Breadcrumb" class="container-fluid px-2 px-lg-2" style="max-width:100%;margin:0 auto;background:#f8f9fa;">
  <ol class="breadcrumb mb-0 py-1" style="background:transparent;font-size:12px;line-height:1.2;--bs-breadcrumb-divider:'›';" itemscope itemtype="https://schema.org/BreadcrumbList">
    <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a href="/" itemprop="item" style="color:#5f6368;text-decoration:none;"><span itemprop="name">Home</span></a><meta itemprop="position" content="1"></li>
    <li class="breadcrumb-item active" aria-current="page" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><span itemprop="name">Settings</span><meta itemprop="position" content="2"></li>
  </ol>
</nav>
<main id="main-content" style="background:#f8f9fa;min-height:85vh;" role="main">

<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    .account-page-body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        background-color: #f6f5f3;
        color: #0f172a;
        -webkit-font-smoothing: antialiased;
    }

    /* Desktop Sidebar Links */
    .sidebar-link {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 13px 18px;
        font-size: 14px;
        font-weight: 500;
        color: #1e293b;
        border-bottom: 1px solid #f1f1f1;
        transition: background-color 0.12s ease;
        position: relative;
        text-decoration: none;
    }

    .sidebar-link:last-child {
        border-bottom: none;
    }

    .sidebar-link:hover {
        background-color: #faf9f8;
    }

    .sidebar-link.active {
        font-weight: 700;
        color: #0f172a;
        background-color: #ffffff;
    }

    .sidebar-link.active::after {
        content: '';
        position: absolute;
        right: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background-color: #6d28d9;
    }
</style>

<div class="account-page-body min-h-screen">

    <!-- ========================================================================= -->
    <!-- DESKTOP VIEW: Constant Left Sidebar & Canvas (>= md)                      -->
    <!-- ========================================================================= -->
    <div class="hidden md:flex flex-col min-h-screen w-full">
        <main class="flex-grow max-w-[1180px] w-full mx-auto px-4 sm:px-8 py-8 sm:py-9">
            <div class="flex flex-col md:flex-row gap-6 items-start w-full">
                
                <!-- Shared Left Sidebar Card -->
                <aside class="w-full md:w-[260px] shrink-0">
                    <div class="bg-white border border-[#e5e5e5] rounded-xl shadow-[0_1px_3px_rgba(0,0,0,0.02)] overflow-hidden">
                        
                        <!-- Avatar & FastNet member Badge -->
                        <div class="pt-7 pb-5 px-4 flex flex-col items-center justify-center border-b border-[#e5e5e5]">
                            <img id="desktop-sidebar-avatar" src="https://images.unsplash.com/photo-1531427186611-ecfd6d936c79?auto=format&fit=crop&w=160&q=80" alt="Avatar" class="w-[72px] h-[72px] rounded-full object-cover shadow-sm">
                            <span class="bg-[#ede9fe] text-[#6d28d9] text-[11.5px] font-semibold px-3 py-0.5 rounded-md mt-2.5 inline-block">
                                FastNet member
                            </span>
                        </div>

                        <!-- Sidebar Navigation List -->
                        <nav class="flex flex-col">
                            <a href="<?= $this->Url->build('/my-profile'); ?>" class="sidebar-link">
                                <div class="flex items-center gap-3">
                                    <svg class="w-[18px] h-[18px] text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                    <span>Account</span>
                                </div>
                            </a>

                            <a href="<?= $this->Url->build('/my-booking'); ?>" class="sidebar-link">
                                <div class="flex items-center gap-3">
                                    <svg class="w-[18px] h-[18px] text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="9" cy="6" r="3"></circle>
                                        <path d="M15 12v7"></path>
                                        <path d="M18 12h-6a3 3 0 0 0-3 3v4"></path>
                                        <rect width="6" height="8" x="14" y="11" rx="1"></rect>
                                    </svg>
                                    <span>My bookings</span>
                                </div>
                            </a>

                            <a href="javascript:void(0);" class="sidebar-link">
                                <div class="flex items-center gap-3">
                                    <svg class="w-[18px] h-[18px] text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                                    </svg>
                                    <span>Alerts</span>
                                </div>
                            </a>

                            <a href="<?= $this->Url->build('/settings'); ?>" class="sidebar-link active">
                                <div class="flex items-center gap-3">
                                    <svg class="w-[18px] h-[18px] text-slate-800" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="m3 11 18-5v12L3 14v-3z"></path>
                                        <path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"></path>
                                    </svg>
                                    <span>Settings & Email preferences</span>
                                </div>
                            </a>

                            <a href="<?= $this->Url->build('/how-we-work'); ?>" class="sidebar-link">
                                <div class="flex items-center gap-3">
                                    <svg class="w-[18px] h-[18px] text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="12" y1="16" x2="12" y2="12"></line>
                                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                                    </svg>
                                    <span>How FastNetStays Works</span>
                                </div>
                            </a>

                            <a href="<?= $this->Url->build('/contact'); ?>" class="sidebar-link">
                                <div class="flex items-center gap-3">
                                    <svg class="w-[18px] h-[18px] text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                                    </svg>
                                    <span>Get help</span>
                                </div>
                                <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                    <polyline points="15 3 21 3 21 9"></polyline>
                                    <line x1="10" y1="14" x2="21" y2="3"></line>
                                </svg>
                            </a>
                        </nav>
                    </div>
                </aside>

                <!-- Right Side Settings Content Canvas -->
                <section class="flex-1 w-full flex flex-col gap-4">
                    
                    <!-- Notification & Email Preferences -->
                    <div class="card mb-2 border border-slate-200 shadow-sm rounded-3 bg-white">
                        <div class="card-header bg-slate-50 border-bottom border-slate-200 py-3">
                            <h4 class="fs-6 fw-bold mb-0 text-slate-900"><i class="fa-regular fa-bell me-2 text-warning"></i>Notification & Email Preferences</h4>
                        </div>
                        <div class="card-body p-4">
                            <div id="notify-alert-box"></div>
                            <div class="d-flex flex-column gap-3 mb-4">
                                <div class="d-flex align-items-center justify-content-between p-2 rounded hover:bg-slate-50 transition">
                                    <div>
                                        <p class="fw-bold text-dark text-sm mb-0">Booking Confirmation & Receipts</p>
                                        <span class="text-xs text-muted">Receive instant email and SMS receipts for every completed lodge booking</span>
                                    </div>
                                    <div class="form-check form-switch fs-5">
                                        <input class="form-check-input notify-toggle" type="checkbox" id="notify_bookings" checked>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center justify-content-between p-2 rounded hover:bg-slate-50 transition">
                                    <div>
                                        <p class="fw-bold text-dark text-sm mb-0">Special Offers & Tanzanian Stay Discounts</p>
                                        <span class="text-xs text-muted">Get notified about weekend getaway flash deals in Zanzibar, Serengeti, and Arusha</span>
                                    </div>
                                    <div class="form-check form-switch fs-5">
                                        <input class="form-check-input notify-toggle" type="checkbox" id="notify_offers" checked>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center justify-content-between p-2 rounded hover:bg-slate-50 transition">
                                    <div>
                                        <p class="fw-bold text-dark text-sm mb-0">Host & Lodge Arrival Messages</p>
                                        <span class="text-xs text-muted">Direct messaging and check-in coordination alerts from your lodge hosts</span>
                                    </div>
                                    <div class="form-check form-switch fs-5">
                                        <input class="form-check-input notify-toggle" type="checkbox" id="notify_messages" checked>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="button" onclick="saveNotificationPreferences()" class="btn btn-primary rounded-full px-4 fw-bold shadow-sm">
                                    Save Notification Preferences
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Security & Two-Factor -->
                    <div class="card border border-slate-200 shadow-sm rounded-3 bg-white">
                        <div class="card-header bg-slate-50 border-bottom border-slate-200 py-3">
                            <h4 class="fs-6 fw-bold mb-0 text-slate-900"><i class="fa-solid fa-shield-halved me-2 text-success"></i>Security & Account Safety</h4>
                        </div>
                        <div class="card-body p-4">
                            <!-- Two-Factor SMS Verification -->
                            <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 p-3 bg-slate-50 rounded-3 border border-slate-100">
                                <div>
                                    <h6 class="fw-bold text-slate-900 mb-1"><i class="fa-solid fa-mobile-screen me-2 text-primary"></i>Two-Factor SMS Verification</h6>
                                    <p class="text-xs text-slate-600 mb-0">Receive a one-time NextSMS OTP code on your phone when signing in from an unrecognized device.</p>
                                </div>
                                <div class="form-check form-switch fs-4">
                                    <input class="form-check-input" type="checkbox" role="switch" id="setting-2fa-switch" checked onchange="toggle2FAPreference(this.checked)">
                                </div>
                            </div>
                        </div>
                    </div>

                </section>
            </div>
        </main>
    </div>

    <!-- ========================================================================= -->
    <!-- MOBILE VIEW: Native App Settings View (< 768px)                           -->
    <!-- ========================================================================= -->
    <div class="block md:hidden bg-[#f4f7fa] min-h-screen pb-20 w-full">
        <div class="bg-white px-4 py-4 flex flex-col border-b border-gray-200">
            <div class="flex items-center justify-between mb-1">
                <h1 class="text-xl font-bold text-slate-900 tracking-tight mb-0">Settings & Notifications</h1>
                <a href="<?= $this->Url->build('/my-profile'); ?>" class="text-xs font-semibold text-[#7c3aed] hover:underline">Account</a>
            </div>
            <p class="text-xs text-slate-500 mb-0">Manage email preferences and SMS security alerts.</p>
        </div>

        <div class="p-4 flex flex-col gap-4">
            <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
                <h3 class="font-bold text-slate-900 text-sm mb-3">Email Preferences</h3>
                <div class="flex flex-col gap-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-700">Booking Confirmations</span>
                        <input class="form-check-input" type="checkbox" checked>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-700">Special Stay Offers</span>
                        <input class="form-check-input" type="checkbox" checked>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Footer -->
<?= $this->element('footer', ['skin' => 'skin-dark-footer']) ?>

<script>
    function saveNotificationPreferences() {
        const box = document.getElementById('notify-alert-box');
        if (box) {
            box.innerHTML = '<div class="alert alert-success py-2 mb-3">Notification preferences saved successfully!</div>';
            setTimeout(() => { box.innerHTML = ''; }, 4000);
        }
    }

    function toggle2FAPreference(enabled) {
        const box = document.getElementById('notify-alert-box');
        if (box) {
            box.innerHTML = `<div class="alert alert-info py-2 mb-3">Two-Factor SMS Verification ${enabled ? 'enabled' : 'disabled'}.</div>`;
            setTimeout(() => { box.innerHTML = ''; }, 4000);
        }
    }
</script>