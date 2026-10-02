<?php
/**
 * portal_nav — Real working Admin/Owner portal navigation.
 * Shows role-aware tabs for /admin/* and /host/* with active states.
 * Session-independent: uses controller-provided $userProfile (backend).
 */
$profile = $userProfile ?? [];
$role = strtolower((string)($profile['role'] ?? ''));
$path = $this->getRequest()->getPath() ?: '/';
$isAdminSection = str_starts_with($path, '/admin');
$isHostSection = str_starts_with($path, '/host') || str_starts_with($path, '/owner');

$adminLinks = [
    ['/admin/dashboard', 'Dashboard', 'fa-gauge'],
    ['/admin/owners', 'Owners', 'fa-users'],
    ['/admin/lodges', 'Lodges', 'fa-hotel'],
    ['/admin/bookings', 'Bookings', 'fa-calendar-check'],
    ['/admin/requests', 'Requests', 'fa-clipboard-question'],
    ['/admin/finance', 'Finance', 'fa-chart-line'],
    ['/admin/finance/ledger', 'Ledger', 'fa-book'],
    ['/admin/finance/payouts', 'Payouts', 'fa-wallet'],
    ['/admin/staff', 'Staff', 'fa-users-gear'],
    ['/admin/support', 'Support', 'fa-headset'],
    ['/admin/reviews', 'Reviews', 'fa-star'],
];
$hostLinks = [
    ['/host/dashboard', 'Dashboard', 'fa-gauge'],
    ['/host/listings', 'My Properties', 'fa-list'],
    ['/host/rooms', 'Rooms', 'fa-bed'],
    ['/host/bookings', 'Bookings', 'fa-calendar-check'],
    ['/host/earnings', 'Earnings', 'fa-wallet'],
    ['/host/onboarding', 'Onboard', 'fa-plus'],
    ['/host/profile', 'Profile', 'fa-circle-user'],
];
$links = $isAdminSection ? $adminLinks : $hostLinks;
// Admins browsing host section still get cross-link to admin
$showCrossAdmin = ($role === 'admin' && $isHostSection);
$showCrossHost = ($role === 'admin' || $role === 'owner') && $isAdminSection;
$name = $profile['name'] ?? $profile['full_name'] ?? 'Portal user';
$email = $profile['email'] ?? '';
?>
<style>.portal-nav{position:sticky;top:64px;z-index:50;background:#fff;border-bottom:1px solid #e8eaed}.portal-nav-scroll{display:flex;gap:8px;overflow-x:auto;padding:10px 0;scrollbar-width:none}.portal-nav-scroll::-webkit-scrollbar{display:none}.portal-pill{white-space:nowrap;border-radius:9999px;padding:8px 14px;font-size:12px;font-weight:700;border:1px solid #e8eaed;background:#fff;color:#5f6368;text-decoration:none}.portal-pill.active{background:#2563EB;border-color:#2563EB;color:#fff}.portal-pill:hover{border-color:#2563EB;color:#2563EB}.portal-pill.active:hover{color:#fff}</style>
<div class="portal-nav">
  <div class="container" style="max-width:1280px">
    <div class="d-flex align-items-center gap-3 flex-wrap py-2">
      <div class="d-flex align-items-center gap-2">
        <span style="width:32px;height:32px;border-radius:10px;background:#2563EB;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:13px"><?= h(strtoupper(substr(trim((string)$name), 0, 1) ?: 'P')) ?></span>
        <div>
          <div style="font-size:13px;font-weight:800;color:#1a1d25;line-height:1.1"><?= h($name) ?></div>
          <div style="font-size:11px;color:#5f6368"><?= h($role !== '' ? ucfirst($role) : 'Portal') ?><?= $email !== '' ? ' · ' . h($email) : '' ?></div>
        </div>
      </div>
      <div class="ms-auto d-flex gap-2">
        <?php if ($showCrossHost): ?>
          <a href="<?= $this->Url->build('/host/dashboard') ?>" class="portal-pill">Host portal</a>
        <?php endif; ?>
        <?php if ($showCrossAdmin): ?>
          <a href="<?= $this->Url->build('/admin/dashboard') ?>" class="portal-pill">Admin portal</a>
        <?php endif; ?>
        <a href="<?= $this->Url->build('/logout') ?>" class="portal-pill">Log out</a>
      </div>
    </div>
    <div class="portal-nav-scroll">
      <?php foreach ($links as [$url, $label, $icon]): $active = ($path === $url || ($url !== '/admin/dashboard' && $url !== '/host/dashboard' && str_starts_with($path, $url))); ?>
        <a href="<?= $this->Url->build($url) ?>" class="portal-pill <?= $active ? 'active' : '' ?>"><i class="fa-solid <?= h($icon) ?> me-1"></i><?= h($label) ?></a>
      <?php endforeach; ?>
    </div>
  </div>
</div>
