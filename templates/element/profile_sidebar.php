<?php
/**
 * Reusable Profile Sidebar Navigation Element
 *
 * Usage:
 *   <?= $this->element('profile_sidebar', ['active' => 'personal-info']); ?>
 *
 * Available active keys:
 *   - 'personal-info' (or 'my-profile')
 *   - 'security'
 *   - 'favourites' (or 'my-wishlists')
 *   - 'recently-viewed'
 *   - 'bookings' (or 'my-booking')
 *   - 'search-preferences'
 *   - 'notifications'
 *   - 'help-center'
 */
$active = $active ?? '';

// Map common aliases
$activeMap = [
    'my-profile' => 'personal-info',
    'my-wishlists' => 'favourites',
    'my-booking' => 'bookings',
    'account-security' => 'security',
];
if (isset($activeMap[$active])) {
    $active = $activeMap[$active];
}

$menuItems = [
    [
        'id' => 'personal-info',
        'url' => '/my-profile',
        'icon' => 'fa-regular fa-circle-user',
        'label' => 'Personal info',
    ],
    [
        'id' => 'security',
        'url' => '/security',
        'icon' => 'fa-solid fa-lock',
        'label' => 'Account security',
    ],
    [
        'id' => 'favourites',
        'url' => '/my-wishlists',
        'icon' => 'fa-regular fa-heart',
        'label' => 'Favourites',
    ],
    [
        'id' => 'recently-viewed',
        'url' => '/recently-viewed',
        'icon' => 'fa-solid fa-clock-rotate-left',
        'label' => 'Recently viewed',
    ],
    [
        'id' => 'bookings',
        'url' => '/my-booking',
        'icon' => 'fa-solid fa-suitcase',
        'label' => 'Bookings',
    ],
    [
        'id' => 'search-preferences',
        'url' => '/search-preferences',
        'icon' => 'fa-solid fa-magnifying-glass',
        'label' => 'Search preferences',
    ],
    [
        'id' => 'notifications',
        'url' => '/notifications',
        'icon' => 'fa-regular fa-bell',
        'label' => 'Notifications',
    ],
    [
        'id' => 'language-currency',
        'url' => '/language-and-currency',
        'icon' => 'fa-solid fa-globe',
        'label' => 'Language and currency',
    ],
    [
        'id' => 'help-center',
        'url' => '/help-center',
        'icon' => 'fa-regular fa-circle-question',
        'label' => 'Help and support',
    ],
];
?>

<style>
/* ── Trivago Profile Sidebar & Mobile Back Styles ────────────────────────── */
.trivago-back-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #0f172a;
    font-size: 15px;
    font-weight: 600;
    text-decoration: none !important;
    margin-bottom: 22px;
    padding: 6px 12px;
    border-radius: 8px;
    transition: background-color 0.12s ease;
}
.trivago-back-btn:hover {
    background-color: #f1f5f9;
    color: #007fad;
}

.trivago-side-nav {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.trivago-side-link {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 13px 20px;
    border-radius: 12px;
    color: #1e293b;
    font-size: 15px;
    font-weight: 600;
    text-decoration: none !important;
    transition: all 0.12s ease;
    white-space: nowrap;
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
    width: 22px;
    font-size: 18px;
    text-align: center;
    flex-shrink: 0;
}

/* ── Mobile Back Box Button (<= 991px) ───────────────────────────────────── */
.trivago-mobile-back-box {
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
.trivago-mobile-back-box:active,
.trivago-mobile-back-box:hover {
    background-color: #f1f5f9;
    border-color: #94a3b8;
    color: #007fad;
}
.trivago-mobile-back-container {
    padding-left: calc(var(--bs-gutter-x) * 0.5);
    padding-right: calc(var(--bs-gutter-x) * 0.5);
}
</style>

<!-- Mobile Left Chevron Back Button to Menu (<= 991px) -->
<div class="col-12 d-lg-none trivago-mobile-back-container mb-3">
    <a href="<?= $this->Url->build('/menu'); ?>" class="trivago-mobile-back-box" aria-label="Back to Menu">
        <i class="fa-solid fa-chevron-left"></i>
    </a>
</div>

<!-- Desktop Left Sidebar Navigation (>= 992px) -->
<div class="col-lg-3 pe-lg-4 d-none d-lg-block trivago-sidebar-col">
    <a href="<?= $this->Url->build('/'); ?>" class="trivago-back-btn">
        <i class="fa-solid fa-chevron-left"></i>
        <span>Back</span>
    </a>

    <nav class="trivago-side-nav">
        <?php foreach ($menuItems as $item): ?>
            <?php 
                $isActive = ($active === $item['id']);
                $href = (str_starts_with($item['url'], 'javascript:') ? $item['url'] : $this->Url->build($item['url']));
                $onclickAttr = !empty($item['onclick']) ? ' onclick="' . htmlspecialchars($item['onclick']) . '"' : '';
            ?>
            <a href="<?= $href; ?>" class="trivago-side-link <?= $isActive ? 'active' : ''; ?>"<?= $onclickAttr; ?>>
                <i class="<?= $item['icon']; ?>"></i>
                <span><?= htmlspecialchars($item['label']); ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
</div>



