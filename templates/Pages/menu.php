<?php
/**
 * Trivago Mobile Profile Menu Hub Page
 */
$this->assign('title', 'Menu - FastNet Stays');
?>
<?= $this->Html->css('/assets/css/google-travel-layout.css') ?>
<?= $this->Html->css('/assets/css/google-travel-home.css') ?>
<?= $this->element('navbar') ?>
<main id="main-content" style="background:#f8f9fa;min-height:85vh;" role="main">
    <script>
        // Only visible on mobile/tablet viewports; redirect desktop to /my-profile
        if (window.innerWidth > 991) {
            window.location.replace('<?= $this->Url->build('/my-profile'); ?>');
        }
        window.addEventListener('resize', function() {
            if (window.innerWidth > 991) {
                window.location.replace('<?= $this->Url->build('/my-profile'); ?>');
            }
        });
    </script>
    
    <style>
    @media (min-width: 992px) {
        body {
            display: none !important;
        }
    }
    * {
        box-sizing: border-box;
    }
    .trivago-menu-page {
        max-width: 540px;
        margin: 0 auto;
        min-height: 100vh;
        background-color: #ffffff;
        padding-bottom: 40px;
    }

    /* Top Bar Header */
    .trivago-menu-topbar {
        position: sticky;
        top: 0;
        z-index: 100;
        background-color: #ffffff;
        height: 58px;
        display: flex;
        align-items: center;
        padding: 0 16px;
        border-bottom: 1px solid #f1f5f9;
    }
    .trivago-menu-back-box {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        color: #0f172a;
        font-size: 14px;
        text-decoration: none !important;
        transition: all 0.12s ease;
        background-color: #ffffff;
    }
    .trivago-menu-back-box:active,
    .trivago-menu-back-box:hover {
        background-color: #f1f5f9;
        border-color: #94a3b8;
    }
    .trivago-menu-heading {
        flex-grow: 1;
        text-align: center;
        font-size: 18px;
        font-weight: 700;
        color: #0f172a;
        margin-right: 36px; /* Balance left button */
        letter-spacing: -0.01em;
    }

    /* User Profile Strip */
    .trivago-user-strip {
        background-color: #f8fafc;
        padding: 18px 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        text-decoration: none !important;
        transition: background-color 0.12s ease;
    }
    .trivago-user-strip:hover {
        background-color: #f1f5f9;
    }
    .trivago-user-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background-color: #e0f2fe;
        color: #0284c7;
        font-size: 19px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border: 1.5px solid #bae6fd;
    }
    .trivago-user-meta {
        display: flex;
        flex-direction: column;
        overflow: hidden;
        flex-grow: 1;
    }
    .trivago-user-name {
        font-size: 16.5px;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.25;
        margin-bottom: 3px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .trivago-user-email {
        font-size: 14px;
        color: #64748b;
        font-weight: 400;
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Group Sections */
    .trivago-section-title {
        font-size: 16px;
        font-weight: 800;
        color: #0f172a;
        padding: 24px 20px 8px 20px;
        letter-spacing: -0.01em;
    }

    .trivago-nav-list {
        display: flex;
        flex-direction: column;
    }
    .trivago-nav-item {
        display: flex;
        align-items: center;
        gap: 18px;
        padding: 15px 20px;
        color: #0f172a;
        text-decoration: none !important;
        font-size: 15.5px;
        font-weight: 500;
        transition: background-color 0.12s ease;
    }
    .trivago-nav-item:active,
    .trivago-nav-item:hover {
        background-color: #f8fafc;
        color: #007fad;
    }
    .trivago-nav-item i.trivago-nav-icon {
        width: 24px;
        font-size: 20px;
        color: #1e293b;
        text-align: center;
        flex-shrink: 0;
        transition: color 0.12s ease;
    }
    .trivago-nav-item:hover i.trivago-nav-icon {
        color: #007fad;
    }
    .trivago-nav-item span {
        flex-grow: 1;
        line-height: 1.3;
    }
    .trivago-nav-chevron {
        font-size: 14px;
        color: #94a3b8;
        flex-shrink: 0;
        margin-left: auto;
        transition: color 0.12s ease, transform 0.12s ease;
    }
    .trivago-nav-item:hover .trivago-nav-chevron {
        color: #007fad;
        transform: translateX(2px);
    }
    </style>

<div class="trivago-menu-page">
    <!-- Top Bar with Left Chevron & Menu Title -->
    <header class="trivago-menu-topbar">
        <a href="<?= $this->Url->build('/'); ?>" class="trivago-menu-back-box" aria-label="Go Back">
            <i class="fa-solid fa-chevron-left"></i>
        </a>
        <h1 class="trivago-menu-heading">Menu</h1>
    </header>

    <!-- User Profile Header Card -->
    <?php
        $firstName = $userProfile['first_name'] ?? '';
        $lastName = $userProfile['last_name'] ?? '';
        $userName = !empty($userProfile['full_name']) ? $userProfile['full_name'] : (trim($firstName . ' ' . $lastName) ?: ($userProfile['name'] ?? 'Traveler'));
        $userInitial = !empty($firstName) ? strtoupper(substr($firstName, 0, 1)) : (!empty($userName) ? strtoupper(substr(trim($userName), 0, 1)) : 'U');
        $userEmail = !empty($userProfile['email']) ? $userProfile['email'] : '';
    ?>
    <a href="<?= $this->Url->build('/my-profile'); ?>" class="trivago-user-strip">
        <div class="trivago-user-avatar">
            <?= htmlspecialchars($userInitial); ?>
        </div>
        <div class="trivago-user-meta">
            <div class="trivago-user-name"><?= htmlspecialchars($userName); ?></div>
            <div class="trivago-user-email"><?= htmlspecialchars($userEmail); ?></div>
        </div>
        <i class="fa-solid fa-chevron-right trivago-nav-chevron"></i>
    </a>

    <!-- Account Section -->
    <div class="trivago-section-title">Account</div>
    <div class="trivago-nav-list">
        <a href="<?= $this->Url->build('/my-profile'); ?>" class="trivago-nav-item">
            <i class="fa-regular fa-circle-user trivago-nav-icon"></i>
            <span>Personal info</span>
            <i class="fa-solid fa-chevron-right trivago-nav-chevron"></i>
        </a>
        <a href="<?= $this->Url->build('/security'); ?>" class="trivago-nav-item">
            <i class="fa-solid fa-lock trivago-nav-icon"></i>
            <span>Account security</span>
            <i class="fa-solid fa-chevron-right trivago-nav-chevron"></i>
        </a>
    </div>

    <!-- Trips Section -->
    <div class="trivago-section-title">Trips</div>
    <div class="trivago-nav-list">
        <a href="<?= $this->Url->build('/my-wishlists'); ?>" class="trivago-nav-item">
            <i class="fa-regular fa-heart trivago-nav-icon"></i>
            <span>Favourites</span>
            <i class="fa-solid fa-chevron-right trivago-nav-chevron"></i>
        </a>
        <a href="<?= $this->Url->build('/recently-viewed'); ?>" class="trivago-nav-item">
            <i class="fa-solid fa-clock-rotate-left trivago-nav-icon"></i>
            <span>Recently viewed</span>
            <i class="fa-solid fa-chevron-right trivago-nav-chevron"></i>
        </a>
        <a href="<?= $this->Url->build('/my-booking'); ?>" class="trivago-nav-item">
            <i class="fa-solid fa-suitcase trivago-nav-icon"></i>
            <span>Bookings</span>
            <i class="fa-solid fa-chevron-right trivago-nav-chevron"></i>
        </a>
    </div>

    <!-- Preferences Section -->
    <div class="trivago-section-title">Preferences</div>
    <div class="trivago-nav-list">
        <a href="<?= $this->Url->build('/search-preferences'); ?>" class="trivago-nav-item">
            <i class="fa-solid fa-magnifying-glass trivago-nav-icon"></i>
            <span>Search preferences</span>
            <i class="fa-solid fa-chevron-right trivago-nav-chevron"></i>
        </a>
        <a href="<?= $this->Url->build('/notifications'); ?>" class="trivago-nav-item">
            <i class="fa-regular fa-bell trivago-nav-icon"></i>
            <span>Notifications</span>
            <i class="fa-solid fa-chevron-right trivago-nav-chevron"></i>
        </a>
        <a href="<?= $this->Url->build('/language-and-currency'); ?>" class="trivago-nav-item">
            <i class="fa-solid fa-globe trivago-nav-icon"></i>
            <span>Language and currency</span>
            <i class="fa-solid fa-chevron-right trivago-nav-chevron"></i>
        </a>
        <a href="<?= $this->Url->build('/help-center'); ?>" class="trivago-nav-item">
            <i class="fa-regular fa-circle-question trivago-nav-icon"></i>
            <span>Help and support</span>
            <i class="fa-solid fa-chevron-right trivago-nav-chevron"></i>
        </a>
    </div>
</div>
</main>
<?= $this->element('footer', ['skin' => 'skin-light-footer']) ?>
