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
// Role gating for sidebar — only show host/admin if role matches (no breakup for guest)
$__sess = $this->getRequest()->getSession();
$__eff = !empty($userProfile) ? $userProfile : $__sess->read('User');
$__role = strtolower((string)($__eff['role'] ?? ''));
$__isAdmin = $__role === 'admin';
$__isOwner = $__role === 'owner';

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
        'id' => 'host-dashboard',
        'url' => '/host/dashboard',
        'icon' => 'fa-solid fa-hotel',
        'label' => 'Host Dashboard',
    ],
    [
        'id' => 'host-listings',
        'url' => '/host/listings',
        'icon' => 'fa-solid fa-list',
        'label' => 'My Properties',
    ],
    [
        'id' => 'host-rooms',
        'url' => '/host/rooms',
        'icon' => 'fa-solid fa-bed',
        'label' => 'My Rooms',
    ],
    [
        'id' => 'host-onboarding',
        'url' => '/host/onboarding',
        'icon' => 'fa-solid fa-plus',
        'label' => 'Onboard Lodge',
    ],
    [
        'id' => 'host-profile',
        'url' => '/host/profile',
        'icon' => 'fa-regular fa-circle-user',
        'label' => 'Host Profile',
    ],
    [
        'id' => 'admin-dashboard',
        'url' => '/admin/dashboard',
        'icon' => 'fa-solid fa-gauge',
        'label' => 'Admin Dashboard',
    ],
    [
        'id' => 'admin-owners',
        'url' => '/admin/owners',
        'icon' => 'fa-solid fa-user-check',
        'label' => 'Verify Owners',
    ],
    [
        'id' => 'admin-lodges',
        'url' => '/admin/lodges',
        'icon' => 'fa-solid fa-house-circle-check',
        'label' => 'Verify Lodges',
    ],
    [
        'id' => 'admin-finance',
        'url' => '/admin/finance',
        'icon' => 'fa-solid fa-chart-line',
        'label' => 'Finance Overview',
    ],
    [
        'id' => 'admin-ledger',
        'url' => '/admin/finance/ledger',
        'icon' => 'fa-solid fa-file-invoice-dollar',
        'label' => 'Ledger',
    ],
    [
        'id' => 'admin-payouts',
        'url' => '/admin/finance/payouts',
        'icon' => 'fa-solid fa-money-bill-transfer',
        'label' => 'Payouts',
    ],
    [
        'id' => 'admin-bookings',
        'url' => '/admin/bookings',
        'icon' => 'fa-solid fa-calendar-check',
        'label' => 'Admin Bookings',
    ],
    [
        'id' => 'admin-staff',
        'url' => '/admin/staff',
        'icon' => 'fa-solid fa-users-gear',
        'label' => 'Staff',
    ],
    [
        'id' => 'admin-requests',
        'url' => '/admin/requests',
        'icon' => 'fa-solid fa-clipboard-question',
        'label' => 'Lodge Requests',
    ],
    [
        'id' => 'admin-support',
        'url' => '/admin/support',
        'icon' => 'fa-solid fa-headset',
        'label' => 'Support',
    ],
    [
        'id' => 'help-center',
        'url' => '/help-center',
        'icon' => 'fa-regular fa-circle-question',
        'label' => 'Help and support',
    ],
];
// Filter by role — no breakup for guest (hide host/admin)
$menuItems = array_values(array_filter($menuItems, function($it) use ($__isAdmin, $__isOwner) {
    if (str_starts_with($it['id'], 'host-')) return $__isOwner || $__isAdmin;
    if (str_starts_with($it['id'], 'admin-')) return $__isAdmin;
    return true;
}));
?>

<?= $this->Html->css('/assets/css/profile-sidebar.css') ?>

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



