<?php
$this->assign('title', 'Become a Host | List Your Property on FastNet Stays');
$this->assign('description', 'List your hotel, lodge or apartment on FastNet Stays. Free listing, 90% payout, M-Pesa and Tigo Pesa settlements, 24/7 host support across Tanzania.');
$juLoggedIn = !empty($isLoggedIn);
$juRole = strtolower((string)($userRole ?? ''));
$isHost = in_array($juRole, ['owner', 'admin'], true);

// A pending host gets one pointer to the portal verification page.
// Portfolio listings never render here: they live inside the portals.
$juNeedsVerification = $juLoggedIn && $juRole === 'owner'
    && strtolower((string)($sessionUser['status'] ?? '')) === 'pending verification';
?>
<?= $this->Html->css('/assets/css/join-us.css?v=' . filemtime(WWW_ROOT . 'assets/css/join-us.css')) ?>

<?= $this->element('navbar') ?>
<div class="ju">
<main id="main-content" role="main">

<!-- Hero -->
<header class="ju-hero">
  <div class="container py-5" style="max-width:1140px">
    <span class="ju-eyebrow">Hosting on FastNet Stays</span>
    <h1 class="ju-h1 mt-3">List your property. <span class="accent">Earn on every booking.</span></h1>
    <p class="ju-lead mt-3">Put your hotel, lodge or apartment in front of millions of travellers searching Tanzania. Listing is free — you keep 90% of every paid stay, settled to M-Pesa, Tigo Pesa or bank.</p>
    <div class="ju-actions mt-4">
      <?php if (!$juLoggedIn): ?>
        <a href="<?= $this->Url->build('/signup?role=owner') ?>" class="btn-ju btn-ju-primary">Register as a host</a>
        <a href="<?= $this->Url->build('/login?role=owner') ?>" class="btn-ju btn-ju-outline">Sign in</a>
      <?php elseif ($isHost): ?>
        <a href="<?= $this->Url->build('/host/onboarding') ?>" class="btn-ju btn-ju-primary">Add a new property</a>
        <a href="<?= $this->Url->build('/host/dashboard') ?>" class="btn-ju btn-ju-outline">Open host dashboard</a>
      <?php else: ?>
        <a href="<?= $this->Url->build('/signup?role=owner') ?>" class="btn-ju btn-ju-primary">Register as a host</a>
        <a href="<?= $this->Url->build('/login?role=owner') ?>" class="btn-ju btn-ju-outline">Sign in with host account</a>
      <?php endif; ?>
      <a href="#how-it-works" class="btn-ju btn-ju-outline">How listing works</a>
    </div>

    <?php if ($juLoggedIn && !$isHost): ?>
      <div class="ju-note mt-3" role="note" style="background:#fffbeb;border-color:#fcd34d;color:#92400e">
        You are signed in as <strong><?= h($sessionUser['name'] ?? $sessionUser['email'] ?? 'User') ?></strong> with a <strong>guest account</strong>. Hosting needs its own account with its own sign-in — register a host account above (you may reuse the same email; guest and host stay separate logins), or sign out and sign back in with it. Your guest bookings stay untouched.
      </div>
    <?php endif; ?>
    <div class="ju-meta mt-4">
      <div><strong>90%</strong>Your share of each booking</div>
      <div><strong>Free</strong>No setup or listing fees</div>
      <div><strong>24 hrs</strong>Typical review turnaround</div>
    </div>
  </div>
</header>

<?php if ($juNeedsVerification): ?>

<!-- Identity verification lives in the owner portal now. Pending hosts get
     a pointer instead of a public form. -->
<section class="ju-section" style="padding-bottom:0"><div class="container" style="max-width:1140px">
  <div class="ju-tile" style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between">
    <div>
      <div class="ju-label">Verification</div>
      <h3 class="mt-1 mb-1" style="font-size:18px">Verify your identity</h3>
      <p class="mb-0" style="font-size:14px;color:#525252;max-width:560px">Required before your first property goes live. Submit your documents from your host dashboard — usually reviewed within one business day.</p>
    </div>
    <a href="<?= $this->Url->build('/host/verification') ?>" class="btn-ju btn-ju-primary">Continue verification</a>
  </div>
</div></section>
<?php endif; ?>

<!-- Steps -->
<section class="ju-section" id="how-it-works" style="scroll-margin-top:80px"><div class="container" style="max-width:1140px">
  <div class="ju-label">How it works</div>
  <h2 class="ju-h2">From sign-up to first payout in three steps</h2>
  <div class="row g-3 mt-3">
    <div class="col-md-4"><div class="ju-tile"><div class="num">Step 01</div><h3 class="mt-2">Create your host account</h3><p>Register a separate host account with its own sign-in. A guest login cannot list properties.</p>
      <?php if ($isHost): ?><?php if ($juNeedsVerification): ?><a href="<?= $this->Url->build('/host/verification') ?>">Submit verification documents</a><?php else: ?><strong>Done — you're a host.</strong><?php endif; ?><?php else: ?><a href="<?= $this->Url->build('/signup?role=owner') ?>">Register as a host</a><?php endif; ?>
    </div></div>
    <div class="col-md-4"><div class="ju-tile"><div class="num">Step 02</div><h3 class="mt-2">Describe your property and rooms</h3><p>Add photos, amenities, nightly rates and room inventory. Most hosts finish in under 20 minutes.</p><?php if ($isHost): ?><a href="<?= $this->Url->build('/host/onboarding') ?>">Start a listing</a><?php else: ?><a href="<?= $this->Url->build('/signup?role=owner') ?>">Sign up free to start</a><?php endif; ?></div></div>
    <div class="col-md-4"><div class="ju-tile"><div class="num">Step 03</div><h3 class="mt-2">Pass review and receive bookings</h3><p>Our team verifies quality and location, usually within 24 hours. Then you go live and payouts begin.</p><?php if ($isHost): ?><a href="<?= $this->Url->build('/host/dashboard') ?>">Open host dashboard</a><?php elseif (!$juLoggedIn): ?><a href="<?= $this->Url->build('/login?role=owner') ?>">Sign in to continue</a><?php else: ?><a href="<?= $this->Url->build('/signup?role=owner') ?>">Register as a host</a><?php endif; ?></div></div>
  </div>
</div></section>

<!-- Benefits -->
<section class="ju-section" style="background:#fff"><div class="container" style="max-width:1140px">
  <div class="ju-label">Why hosts choose us</div>
  <h2 class="ju-h2">Everything you need to run your property</h2>
  <div class="row g-3 mt-3">
    <div class="col-md-4"><div class="ju-perk"><h3>Mobile-money payouts</h3><p>Settlements go straight to M-Pesa, Tigo Pesa or your bank. Track every shilling under Host → Earnings.</p></div></div>
    <div class="col-md-4"><div class="ju-perk"><h3>Transparent 90 / 10 split</h3><p>You keep 90% of every paid booking. The 10% covers secure payments, marketing and guest support.</p></div></div>
    <div class="col-md-4"><div class="ju-perk"><h3>Verified travellers</h3><p>Checked-in guest identities and confirmed mobile payments mean fewer no-shows and chargebacks.</p></div></div>
    <div class="col-md-4"><div class="ju-perk"><h3>Real-time calendar</h3><p>Control availability, nightly rates and room inventory from your dashboard or phone.</p></div></div>
    <div class="col-md-4"><div class="ju-perk"><h3>Local support, 7 days a week</h3><p>Swahili and English speaking host team in Dar es Salaam, Zanzibar and Arusha.</p></div></div>
    <div class="col-md-4"><div class="ju-perk"><h3>Built-in visibility</h3><p>Your rooms appear in FastNet search, destination guides and member deals automatically.</p></div></div>
  </div>
</div></section>

<!-- FAQ -->
<section class="ju-section"><div class="container ju-faq" style="max-width:820px">
  <div class="ju-label">FAQ</div>
  <h2 class="ju-h2 mb-3">Common host questions</h2>
  <details open><summary>What does it cost to list?</summary><p style="font-size:14px;color:#525252" class="mt-2">Nothing upfront. Listing, photos and support are free — FastNet retains 10% only when a guest completes a paid stay.</p></details>
  <details><summary>When and how do I get paid?</summary><p style="font-size:14px;color:#525252" class="mt-2">After check-in, the guest's payment settles to your M-Pesa, Tigo Pesa or bank account. Completed and pending balances are visible any time under Host → Earnings.</p></details>
  <details><summary>How long does verification take?</summary><p style="font-size:14px;color:#525252" class="mt-2">Most properties are reviewed within 24 hours. Your listing shows as Pending until approved, then flips to Active automatically.</p></details>
  <details><summary>I already have a guest account — must I register again?</summary><p style="font-size:14px;color:#525252" class="mt-2">Yes — hosting needs its own account with its own sign-in. You may reuse the same email address; guest and host stay two separate logins. Your guest bookings stay on the guest account.</p></details>
</div></section>

<!-- Final CTA -->
<section class="ju-section" style="padding-top:0"><div class="container" style="max-width:1140px">
  <div class="ju-cta p-4 p-md-5 d-flex flex-wrap gap-3 align-items-center">
    <div class="ju-cta-text"><h2 style="font-weight:600" class="mb-1">Ready when you are.</h2><p class="mb-0">Join the hosts earning across Tanzania — start your free listing today.</p></div>
    <?php if ($isHost): ?>
      <a href="<?= $this->Url->build('/host/onboarding') ?>" class="btn-ju btn-ju-light">Add a property</a>
    <?php elseif ($juLoggedIn): ?>
      <div class="d-flex flex-column gap-2 align-items-stretch">
        <a href="<?= $this->Url->build('/signup?role=owner') ?>" class="btn-ju btn-ju-light">Register as a host</a>
        <span style="color:#fff;font-size:13px;text-align:center">Already have a host account? <a href="<?= $this->Url->build('/logout') ?>" style="color:#fff;text-decoration:underline">Sign out</a>, then sign in with it</span>
      </div>
    <?php else: ?>
      <div class="d-flex flex-column gap-2 align-items-stretch">
        <a href="<?= $this->Url->build('/signup?role=owner') ?>" class="btn-ju btn-ju-light">Register as a host</a>
        <a href="<?= $this->Url->build('/login') ?>" style="color:#fff;font-size:13px;text-align:center">Already a host? Sign in</a>
      </div>
    <?php endif; ?>
  </div>
</div></section>

</main>
</div>
<?= $this->element('footer', ['skin' => 'skin-light-footer']) ?>
