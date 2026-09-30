<?php
/**
 * portal_topbar — slim portal header: menu toggle, page title, actions, account menu.
 * Slots: portal_title (assign in template), page_actions (optional HTML).
 */
$session = $this->getRequest()->getSession();
$sessionUser = $session->read('User');
$profile = $userProfile ?? $sessionUser ?? [];
$role = strtolower((string)($profile['role'] ?? $sessionUser['role'] ?? ''));
$isAdmin = $role === 'admin';
$name = trim((string)($profile['name'] ?? $profile['full_name'] ?? $sessionUser['name'] ?? 'Portal user')) ?: 'Portal user';
$initial = strtoupper(substr($name, 0, 1));
$title = $this->fetch('portal_title');
if ($title === '') $title = $this->fetch('title');
$profileUrl = $isAdmin ? '/admin/dashboard' : '/host/profile';
?>
<header class="p-top">
  <button class="p-menu-btn" id="pMenuBtn" aria-label="Toggle navigation"><i class="fa-solid fa-bars"></i></button>
  <h1 class="p-title"><?= h($title !== '' ? $title : 'Portal') ?></h1>
  <div class="p-top-actions">
    <?= $this->fetch('page_actions') ?>
    <a class="p-site-link" href="<?= $this->Url->build('/') ?>"><i class="fa-solid fa-arrow-up-right-from-square me-1"></i>View site</a>
    <div class="dropdown">
      <button class="p-top-avatar" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Account menu"><?= h($initial) ?></button>
      <ul class="dropdown-menu dropdown-menu-end">
        <li><h6 class="dropdown-header"><?= h($name) ?><br><small class="text-muted"><?= h($role !== '' ? ucfirst($role) : 'Portal') ?></small></h6></li>
        <li><a class="dropdown-item" href="<?= $this->Url->build($profileUrl) ?>">My profile</a></li>
        <li><a class="dropdown-item" href="<?= $this->Url->build('/') ?>">View site</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item text-danger" href="<?= $this->Url->build('/logout') ?>">Log out</a></li>
      </ul>
    </div>
  </div>
</header>
