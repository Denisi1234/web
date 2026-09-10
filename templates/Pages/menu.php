<?php
/**
 * Trivago Mobile Profile Menu Hub Page
 */
$this->assign('title', 'Menu - FastNet Stays');
?>
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
    <li class="breadcrumb-item active" aria-current="page" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><span itemprop="name">Menu</span><meta itemprop="position" content="2"></li>
  </ol>
</nav>
<main id="main-content" style="background:#f8f9fa;min-height:85vh;" role="main">
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title><?= $this->fetch('title'); ?></title>
    
    <!-- Google Fonts & Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
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
        margin: 0;
        padding: 0;
    }
    body {
        background-color: #ffffff;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        color: #0f172a;
        -webkit-tap-highlight-color: transparent;
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
    .trivago-nav-item i {
        width: 24px;
        font-size: 20px;
        color: #1e293b;
        text-align: center;
        flex-shrink: 0;
        transition: color 0.12s ease;
    }
    .trivago-nav-item:hover i {
        color: #007fad;
    }
    .trivago-nav-item span {
        flex-grow: 1;
        line-height: 1.3;
    }
    </style>
</head>
<body>

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
    </a>

    <!-- Account Section -->
    <div class="trivago-section-title">Account</div>
    <div class="trivago-nav-list">
        <a href="<?= $this->Url->build('/my-profile'); ?>" class="trivago-nav-item">
            <i class="fa-regular fa-circle-user"></i>
            <span>Personal info</span>
        </a>
        <a href="<?= $this->Url->build('/security'); ?>" class="trivago-nav-item">
            <i class="fa-solid fa-lock"></i>
            <span>Account security</span>
        </a>
    </div>

    <!-- Trips Section -->
    <div class="trivago-section-title">Trips</div>
    <div class="trivago-nav-list">
        <a href="<?= $this->Url->build('/my-wishlists'); ?>" class="trivago-nav-item">
            <i class="fa-regular fa-heart"></i>
            <span>Favourites</span>
        </a>
        <a href="<?= $this->Url->build('/recently-viewed'); ?>" class="trivago-nav-item">
            <i class="fa-solid fa-clock-rotate-left"></i>
            <span>Recently viewed</span>
        </a>
        <a href="<?= $this->Url->build('/my-booking'); ?>" class="trivago-nav-item">
            <i class="fa-solid fa-suitcase"></i>
            <span>Bookings</span>
        </a>
    </div>

    <!-- Preferences Section -->
    <div class="trivago-section-title">Preferences</div>
    <div class="trivago-nav-list">
        <a href="<?= $this->Url->build('/search-preferences'); ?>" class="trivago-nav-item">
            <i class="fa-solid fa-magnifying-glass"></i>
            <span>Search preferences</span>
        </a>
        <a href="<?= $this->Url->build('/notifications'); ?>" class="trivago-nav-item">
            <i class="fa-regular fa-bell"></i>
            <span>Notifications</span>
        </a>
        <a href="<?= $this->Url->build('/language-and-currency'); ?>" class="trivago-nav-item">
            <i class="fa-solid fa-globe"></i>
            <span>Language and currency</span>
        </a>
        <a href="<?= $this->Url->build('/help-center'); ?>" class="trivago-nav-item">
            <i class="fa-regular fa-circle-question"></i>
            <span>Help and support</span>
        </a>
    </div>
</div>

</body>
</html>
</main>
<?= $this->element('footer', ['skin' => 'skin-light-footer']) ?>
