<?php
/**
 * portal_sidebar — dark Carbon-style sidebar, role-based menu.
 * Expects $userProfile optionally; falls back to session User.
 */
$session = $this->getRequest()->getSession();
$sessionUser = $session->read('User');
$profile = $userProfile ?? $sessionUser ?? [];
$role = strtolower((string)($profile['role'] ?? $sessionUser['role'] ?? ''));
$isAdmin = $role === 'admin';
$path = $this->getRequest()->getPath() ?: '/';
$name = trim((string)($profile['name'] ?? $profile['full_name'] ?? $sessionUser['name'] ?? 'Portal user')) ?: 'Portal user';
$initial = strtoupper(substr($name, 0, 1));

$menu = $isAdmin ? [
  ['OVERVIEW', [
    ['url' => '/admin/dashboard', 'label' => 'Dashboard', 'icon' => 'fa-gauge', 'match' => ['/admin/dashboard', '/admin']],
  ]],
  ['MANAGEMENT', [
    ['url' => '/admin/owners', 'label' => 'Owners', 'icon' => 'fa-users', 'match' => ['/admin/owners', '/admin/verification']],
    ['url' => '/admin/lodges', 'label' => 'Lodges', 'icon' => 'fa-hotel', 'match' => ['/admin/lodges']],
    ['url' => '/admin/bookings', 'label' => 'Bookings', 'icon' => 'fa-calendar-check', 'match' => ['/admin/bookings']],
    ['url' => '/admin/requests', 'label' => 'Lodge requests', 'icon' => 'fa-clipboard-question', 'match' => ['/admin/requests']],
  ]],
  ['FINANCE', [
    ['url' => '/admin/finance', 'label' => 'Overview', 'icon' => 'fa-chart-line', 'match' => ['/admin/finance']],
    ['url' => '/admin/finance/ledger', 'label' => 'Ledger', 'icon' => 'fa-book', 'match' => ['/admin/finance/ledger']],
    ['url' => '/admin/finance/payouts', 'label' => 'Payouts', 'icon' => 'fa-wallet', 'match' => ['/admin/finance/payouts']],
  ]],
  ['WORKSPACE', [
    ['url' => '/admin/staff', 'label' => 'Staff', 'icon' => 'fa-users-gear', 'match' => ['/admin/staff']],
    ['url' => '/admin/support', 'label' => 'Support', 'icon' => 'fa-headset', 'match' => ['/admin/support']],
    ['url' => '/admin/reviews', 'label' => 'Reviews', 'icon' => 'fa-star', 'match' => ['/admin/reviews']],
  ]],
] : [
  ['OVERVIEW', [
    ['url' => '/host/dashboard', 'label' => 'Dashboard', 'icon' => 'fa-gauge', 'match' => ['/host/dashboard', '/host', '/owner']],
  ]],
  ['MANAGEMENT', [
    ['url' => '/host/listings', 'label' => 'My properties', 'icon' => 'fa-list', 'match' => ['/host/listings', '/host/lodge']],
    ['url' => '/host/rooms', 'label' => 'Rooms', 'icon' => 'fa-bed', 'match' => ['/host/rooms']],
    ['url' => '/host/bookings', 'label' => 'Bookings', 'icon' => 'fa-calendar-check', 'match' => ['/host/bookings']],
  ]],
  ['FINANCE', [
    ['url' => '/host/earnings', 'label' => 'Earnings', 'icon' => 'fa-wallet', 'match' => ['/host/earnings']],
  ]],
  ['WORKSPACE', [
    ['url' => '/host/onboarding', 'label' => 'Add property', 'icon' => 'fa-plus', 'match' => ['/host/onboarding']],
    ['url' => '/host/profile', 'label' => 'Profile', 'icon' => 'fa-circle-user', 'match' => ['/host/profile']],
  ]],
];
?>
<aside class="p-side" id="pSide" aria-label="Portal navigation">
  <a class="p-brand" href="<?= $this->Url->build($isAdmin ? '/admin/dashboard' : '/host/dashboard') ?>">
    <span class="p-brand-mark"><i class="fa-solid fa-hotel"></i></span><span>FastNet<span style="font-weight:400"> Portal</span></span>
  </a>
  <div class="p-role"><span class="p-role-dot"></span><?= $isAdmin ? 'Administrator' : 'Host' ?></div>
  <nav class="p-nav">
    <?php foreach ($menu as [$sec, $links]): ?>
      <div class="p-nav-sec"><?= h($sec) ?></div>
      <?php foreach ($links as $l):
        $active = false;
        foreach ((array)$l['match'] as $m) {
          if ($path === $m || ($m !== '/admin' && $m !== '/host' && str_starts_with($path, $m))) { $active = true; break; }
        }
      ?>
        <a href="<?= $this->Url->build($l['url']) ?>" class="p-link <?= $active ? 'active' : '' ?>"><i class="fa-solid <?= h($l['icon']) ?>"></i><span><?= h($l['label']) ?></span></a>
      <?php endforeach; ?>
    <?php endforeach; ?>
    <?php if ($isAdmin): ?>
      <div class="p-nav-sec">Switch view</div>
      <a href="<?= $this->Url->build('/host/dashboard') ?>" class="p-link"><i class="fa-solid fa-eye"></i><span>Host view</span></a>
    <?php endif; ?>
  </nav>
  <div class="p-side-foot">
    <div class="p-user"><span class="p-avatar"><?= h($initial) ?></span><div class="p-user-meta"><div class="p-user-name"><?= h($name) ?></div><div class="p-user-role"><?= h($role !== '' ? ucfirst($role) : 'Portal') ?></div></div></div>
    <a href="<?= $this->Url->build('/logout') ?>" class="p-logout"><i class="fa-solid fa-arrow-right-from-bracket"></i><span>Log out</span></a>
  </div>
</aside>
