<?php
/**
 * Professional refusal page for portal access control.
 *
 * Rendered directly by AppController::refusePortalAccess() with HTTP 403 —
 * NOT by the exception renderer, which maps every 4xx to error400.php and
 * would show the bare "was not found on this server" page instead.
 *
 * Self-contained styling on purpose: refusal pages must never depend on
 * portal layouts or assets that could themselves fail.
 *
 * @var \App\View\AppView $this
 * @var string $message A short hint from the gate (e.g. which role is needed)
 * @var string $url     The attempted address
 */
use Cake\Core\Configure;

$this->layout = 'error';

if (Configure::read('debug')) :
    $this->layout = 'dev_error';
    $this->assign('title', $message);
    $this->assign('templateName', 'portal403.php');
    $this->start('file');
    echo $this->element('auto_table_warning');
    $this->end();
endif;

$sessionUser = [];
try {
    $sessionUser = (array)$this->getRequest()->getSession()->read('User');
} catch (\Throwable $e) {
    $sessionUser = [];
}
$role = strtolower((string)($sessionUser['role'] ?? ''));
$signedIn = $sessionUser !== [] && !empty($sessionUser['email']);
$firstName = trim((string)($sessionUser['first_name'] ?? ''));
if ($firstName === '' && !empty($sessionUser['name'])) {
    $firstName = explode(' ', trim((string)$sessionUser['name']))[0];
}

$attempted = (string)($url ?? '');
$isHostArea = str_starts_with($attempted, '/host');
$isAdminArea = str_starts_with($attempted, '/admin');

if ($isAdminArea) {
    $title = 'Administrators only';
    $lede = 'This area is restricted to FastNet Stays administrators.';
} elseif ($isHostArea) {
    $title = 'Hosts only';
    $lede = 'This area is for property hosts — listings, rooms, bookings and payouts.';
} else {
    $title = 'Access denied';
    $lede = 'You do not have permission to open this page.';
}

$loginTarget = '/login?redirect=' . urlencode($attempted !== '' ? $attempted : '/');
?>
<style>
  .e403 { font-family: 'IBM Plex Sans', 'Inter', Roboto, Arial, sans-serif; color: #161616; background: #f4f4f4; min-height: 70vh; display: flex; align-items: center; justify-content: center; padding: 40px 16px; }
  .e403-card { background: #fff; border: 1px solid #e0e0e0; border-top: 4px solid #0f62fe; max-width: 560px; width: 100%; padding: 36px 36px 32px; }
  .e403-eyebrow { font-size: 12px; font-weight: 600; letter-spacing: 0.06em; text-transform: uppercase; color: #6f6f6f; margin-bottom: 10px; }
  .e403 h1 { font-size: 28px; font-weight: 600; letter-spacing: -0.01em; margin: 0 0 10px; }
  .e403 p { font-size: 15px; line-height: 1.6; color: #525252; margin: 0 0 12px; }
  .e403-note { background: #f4f4f4; border-left: 3px solid #0f62fe; padding: 12px 14px; font-size: 14px; line-height: 1.6; color: #393939; margin: 16px 0 22px; }
  .e403-actions { display: flex; gap: 10px; flex-wrap: wrap; }
  .e403-btn { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; padding: 0 24px; font-size: 14px; font-weight: 600; text-decoration: none; border: 1px solid transparent; border-radius: 0; cursor: pointer; }
  .e403-primary { background: #0f62fe; color: #fff; }
  .e403-primary:hover { background: #0050e6; color: #fff; }
  .e403-outline { background: #fff; color: #0f62fe; border-color: #0f62fe; }
  .e403-outline:hover { background: #edf5ff; }
  .e403-meta { margin-top: 20px; font-size: 12px; color: #8d8d8d; }
</style>

<div class="e403">
  <div class="e403-card">
    <div class="e403-eyebrow"><?= $isAdminArea ? 'Admin portal' : ($isHostArea ? 'Host portal' : 'FastNet Stays') ?></div>
    <h1><?= h($title) ?></h1>
    <p><?= h($lede) ?></p>

    <?php if ($signedIn && $role === 'customer' && $isHostArea): ?>
      <div class="e403-note">
        You are signed in<?= $firstName !== '' ? ' as <strong>' . h($firstName) . '</strong>' : '' ?>
        with a <strong>guest account</strong> — for booking and staying. It is
        <strong>not</strong> a host account, so it cannot open host tooling.
        Hosting needs its own account with its own sign-in.
      </div>
      <div class="e403-actions">
        <a class="e403-btn e403-primary" href="<?= $this->Url->build('/signup?role=owner') ?>">Register as a host</a>
        <a class="e403-btn e403-outline" href="<?= $this->Url->build('/') ?>">Back to browsing</a>
      </div>
      <div class="e403-meta">
        Already have a host account?
        <a href="<?= $this->Url->build('/logout') ?>">Sign out</a>
        and sign back in with it. Your guest bookings stay untouched.
      </div>
    <?php elseif ($signedIn && $isAdminArea): ?>
      <div class="e403-note">
        You are signed in<?= $firstName !== '' ? ' as <strong>' . h($firstName) . '</strong>' : '' ?>,
        but this area requires an administrator account.
      </div>
      <div class="e403-actions">
        <a class="e403-btn e403-primary" href="<?= $this->Url->build($role === 'owner' ? '/host/dashboard' : '/') ?>">Back to <?= $role === 'owner' ? 'host dashboard' : 'browsing' ?></a>
      </div>
    <?php elseif (!$signedIn): ?>
      <div class="e403-note">Sign in to continue.</div>
      <div class="e403-actions">
        <a class="e403-btn e403-primary" href="<?= $this->Url->build($loginTarget) ?>">Sign in</a>
        <?php if ($isHostArea): ?>
          <a class="e403-btn e403-outline" href="<?= $this->Url->build('/signup?role=owner') ?>">Register as a host</a>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div class="e403-actions">
        <a class="e403-btn e403-primary" href="<?= $this->Url->build('/') ?>">Back to home</a>
      </div>
    <?php endif; ?>
  </div>
</div>
