<?php
$this->assign('title', 'Become a Host | List Your Property on FastNet Stays');
$this->assign('description', 'List your hotel, lodge or apartment on FastNet Stays. Free listing, 90% payout, M-Pesa and Tigo Pesa settlements, 24/7 host support across Tanzania.');
$juLoggedIn = !empty($isLoggedIn);
$juRole = strtolower((string)($userRole ?? ''));
$isHost = in_array($juRole, ['owner', 'admin'], true);
$myList = (isset($myProperties) && is_array($myProperties)) ? $myProperties : [];
?>
<style>
/* Scroll fix: home split-view CSS locks body scroll on desktop — this page must scroll */
html, body { height: auto !important; overflow-y: auto !important; }
#main-wrapper { height: auto !important; overflow: visible !important; display: block !important; }
/* ── Carbon-inspired host page ── */
.ju { background: #f4f4f4; color: #161616; font-family: 'IBM Plex Sans', 'Inter', Roboto, sans-serif; }
.ju-hero { background: #161616; color: #fff; }
.ju-eyebrow { display: inline-block; font-size: 12px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; color: #a6c8ff; border-bottom: 2px solid #0f62fe; padding-bottom: 6px; }
.ju-h1 { font-size: clamp(32px, 4.6vw, 52px); font-weight: 600; line-height: 1.1; letter-spacing: -.01em; color: #f4f4f4; }
.ju-h1 .accent { color: #ffd292; }
@supports ((-webkit-background-clip: text) and (-webkit-text-fill-color: transparent)) {
  .ju-h1 .accent { background: linear-gradient(92deg, #ffcf87 0%, #ffb3ab 100%); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; }
}
.ju-lead { font-size: 17px; line-height: 1.6; color: #c6c6c6; max-width: 620px; }
.ju-meta { display: flex; gap: 24px; flex-wrap: wrap; font-size: 14px; color: #c6c6c6; }
.ju-meta strong { display: block; font-size: 20px; color: #fff; }
.ju-actions { display: flex; gap: 12px; flex-wrap: wrap; }
.btn-ju { display: inline-flex; align-items: center; gap: 8px; min-height: 48px; padding: 0 24px; font-size: 15px; font-weight: 600; text-decoration: none; border: 1px solid transparent; cursor: pointer; }
.btn-ju-primary { background: #0f62fe; color: #fff; }
.btn-ju-primary:hover { background: #0353e9; color: #fff; }
.btn-ju-outline { background: transparent; color: #fff; border-color: #8d8d8d; }
.btn-ju-outline:hover { border-color: #fff; color: #fff; }
.btn-ju-light { background: #fff; color: #161616; }
.btn-ju-light:hover { background: #e0e0e0; color: #161616; }
.ju-section { padding: 56px 0; }
.ju-label { font-size: 12px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; color: #0f62fe; }
.ju-h2 { font-size: clamp(24px, 3vw, 32px); font-weight: 600; letter-spacing: -.01em; }
.ju-tile { background: #fff; border-top: 3px solid #0f62fe; padding: 24px; height: 100%; }
.ju-tile h3 { font-size: 18px; font-weight: 600; }
.ju-tile p { font-size: 14px; line-height: 1.6; color: #525252; }
.ju-tile .num { font-size: 13px; font-weight: 600; color: #6f6f6f; }
.ju-perk { background: #fff; border-left: 3px solid #e0e0e0; padding: 20px; height: 100%; }
.ju-perk h3 { font-size: 16px; font-weight: 600; }
.ju-perk p { font-size: 14px; line-height: 1.6; color: #525252; }
.ju-prop { background: #fff; border: 1px solid #e0e0e0; height: 100%; }
.ju-prop img { height: 150px; width: 100%; object-fit: cover; display: block; }
.ju-status { display: inline-block; font-size: 12px; font-weight: 600; padding: 4px 10px; }
.ju-faq details { background: #fff; border-bottom: 1px solid #e0e0e0; padding: 18px 4px; }
.ju-faq details:first-of-type { border-top: 1px solid #e0e0e0; }
.ju-faq summary { font-size: 16px; font-weight: 600; cursor: pointer; }
.ju-cta { background: #0f62fe; color: #fff; }
.ju-cta p { color: #d0e2ff; }
.ju-cta-text { flex: 1; min-width: 240px; }
.ju img { max-width: 100%; }
.btn-ju { justify-content: center; }
.btn-ju:focus-visible, .ju-faq summary:focus-visible, a:focus-visible { outline: 2px solid #0f62fe; outline-offset: 2px; }
.ju-hero .btn-ju-outline:focus-visible { outline-color: #fff; }
/* ── Responsive ── */
@media (max-width: 767px) {
  .ju-section { padding: 36px 0; }
  .ju-hero .container { padding-top: 2rem !important; padding-bottom: 2rem !important; }
  .ju-lead { font-size: 16px; }
  .ju-meta { gap: 12px 20px; }
  .ju-meta strong { font-size: 18px; }
  .ju-tile, .ju-perk { padding: 20px; }
  .modal-dialog { margin: 12px auto; max-width: calc(100% - 24px); }
}
@media (max-width: 575px) {
  .ju-actions { flex-direction: column; align-items: stretch; }
  .ju-actions .btn-ju { width: 100%; }
  .ju-meta { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; font-size: 12px; }
  .ju-meta strong { font-size: 17px; }
  .ju-cta { flex-direction: column; align-items: stretch !important; }
  .ju-cta-text { min-width: 0; }
  .ju-cta .btn-ju { width: 100%; }
  .ju-cta form { width: 100%; }
  .ju-h1 { font-size: 32px; }
  .ju-prop img { height: 170px; }
}
@media (prefers-reduced-motion: reduce) {
  .ju * { animation: none !important; transition: none !important; }
}
</style>

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
        <a href="<?= $this->Url->build('/signup?role=owner') ?>" class="btn-ju btn-ju-primary">List your property — it's free</a>
        <a href="<?= $this->Url->build('/login') ?>" class="btn-ju btn-ju-outline">Sign in to add a property</a>
      <?php elseif ($isHost): ?>
        <a href="<?= $this->Url->build('/host/onboarding') ?>" class="btn-ju btn-ju-primary">Add a new property</a>
        <a href="<?= $this->Url->build('/host/dashboard') ?>" class="btn-ju btn-ju-outline">Open host dashboard</a>
      <?php else: ?>
        <button type="button" class="btn-ju btn-ju-primary" data-bs-toggle="modal" data-bs-target="#becomeHostModal">Become a host — it's free</button>
        <a href="#how-it-works" class="btn-ju btn-ju-outline">How listing works</a>
      <?php endif; ?>
    </div>
    <div class="ju-meta mt-4">
      <div><strong>90%</strong>Your share of each booking</div>
      <div><strong>Free</strong>No setup or listing fees</div>
      <div><strong>24 hrs</strong>Typical review turnaround</div>
    </div>
  </div>
</header>

<?php if ($juLoggedIn && !$isHost): ?>
<div class="modal fade" id="becomeHostModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content" style="border-radius:0">
  <div class="modal-header"><h5 style="font-weight:600">Become a host</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <?= $this->Form->create(null, ['url' => '/join-us', 'id' => 'becomeHostForm']) ?>
  <div class="modal-body d-grid gap-2">
    <?= $this->Form->hidden('action', ['value' => 'become_host']) ?>
    <input name="phone_number" class="form-control" style="height:48px" placeholder="M-Pesa phone number (optional)" autocomplete="tel">
    <input name="business_name" class="form-control" style="height:48px" placeholder="Business or property name (optional)" autocomplete="organization">
    <div style="font-size:13px;color:#525252">This upgrades your account to <strong>owner</strong> so you can list properties. Free, takes seconds.</div>
  </div>
  <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" id="becomeHostBtn">Become a host</button></div>
  <?= $this->Form->end() ?>
</div></div></div>
<script>
// Become-a-host loading state: instant feedback + no double submit (inline, no deps)
(function () {
  var f = document.getElementById('becomeHostForm');
  if (f) f.addEventListener('submit', function () {
    var b = document.getElementById('becomeHostBtn');
    if (b && !b.disabled) { b.disabled = true; b.textContent = 'Upgrading…'; }
  });
  var cta = document.getElementById('becomeHostCtaForm');
  if (cta) cta.addEventListener('submit', function () {
    var b = document.getElementById('becomeHostCtaBtn');
    if (b && !b.disabled) { b.disabled = true; b.textContent = 'Upgrading…'; }
  });
})();
</script>
<?php endif; ?>

<!-- Owner listings -->
<?php if ($juLoggedIn && $isHost && !empty($myList)): ?>
<section class="ju-section" style="padding-bottom:0"><div class="container" style="max-width:1140px">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div><div class="ju-label">Your portfolio</div><h2 class="ju-h2 mb-0">Your listings</h2></div>
    <a href="<?= $this->Url->build('/host/listings') ?>" class="btn-ju btn-ju-light" style="border:1px solid #8d8d8d">Manage listings</a>
  </div>
  <div class="row g-3">
    <?php foreach (array_slice($myList, 0, 3) as $p): $st = strtolower((string)($p['status'] ?? 'active')); $img = $p['image_url'] ?? $p['primary_image_url'] ?? 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=600&h=400&fit=crop'; ?>
    <div class="col-md-4"><div class="ju-prop">
      <img src="<?= h($img) ?>" alt="">
      <div class="p-3">
        <div style="font-weight:600"><?= h($p['name'] ?? 'Property') ?></div>
        <div style="font-size:13px;color:#525252"><?= h($p['city'] ?? '') ?> · TSh <?= number_format((float)($p['price_per_night'] ?? 0)) ?> / night</div>
        <span class="ju-status mt-2" style="background:<?= $st==='active'?'#defbe6;color:#0e6027':($st==='pending'?'#fcf4d6;color:#8e6a00':'#e5f0ff;color:#0f62fe') ?>"><?= h($p['status'] ?? 'Active') ?></span>
      </div>
    </div></div>
    <?php endforeach; ?>
  </div>
</div></section>
<?php endif; ?>

<!-- Steps -->
<section class="ju-section" id="how-it-works" style="scroll-margin-top:80px"><div class="container" style="max-width:1140px">
  <div class="ju-label">How it works</div>
  <h2 class="ju-h2">From sign-up to first payout in three steps</h2>
  <div class="row g-3 mt-3">
    <div class="col-md-4"><div class="ju-tile"><div class="num">Step 01</div><h3 class="mt-2">Create your host account</h3><p>Register as an owner, or upgrade your guest account with one click. No paperwork to start.</p>
      <?php if (!$juLoggedIn): ?><a href="<?= $this->Url->build('/signup?role=owner') ?>">Sign up as owner</a><?php elseif (!$isHost): ?><a href="#" data-bs-toggle="modal" data-bs-target="#becomeHostModal">Upgrade my account</a><?php else: ?><strong>Done — you're a host.</strong><?php endif; ?>
    </div></div>
    <div class="col-md-4"><div class="ju-tile"><div class="num">Step 02</div><h3 class="mt-2">Describe your property and rooms</h3><p>Add photos, amenities, nightly rates and room inventory. Most hosts finish in under 20 minutes.</p><?php if (!$juLoggedIn): ?><a href="<?= $this->Url->build('/signup?role=owner') ?>">Sign up free to start</a><?php else: ?><a href="<?= $this->Url->build('/host/onboarding') ?>">Start a listing</a><?php endif; ?></div></div>
    <div class="col-md-4"><div class="ju-tile"><div class="num">Step 03</div><h3 class="mt-2">Pass review and receive bookings</h3><p>Our team verifies quality and location, usually within 24 hours. Then you go live and payouts begin.</p><?php if (!$juLoggedIn): ?><a href="<?= $this->Url->build('/login') ?>">Sign in to continue</a><?php else: ?><a href="<?= $this->Url->build('/host/dashboard') ?>">Open host dashboard</a><?php endif; ?></div></div>
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
  <details><summary>I already have a guest account — must I register again?</summary><p style="font-size:14px;color:#525252" class="mt-2">No. Choose “Become a host” and your existing account is upgraded to owner instantly, keeping your bookings and profile.</p></details>
</div></section>

<!-- Final CTA -->
<section class="ju-section" style="padding-top:0"><div class="container" style="max-width:1140px">
  <div class="ju-cta p-4 p-md-5 d-flex flex-wrap gap-3 align-items-center">
    <div class="ju-cta-text"><h2 style="font-weight:600" class="mb-1">Ready when you are.</h2><p class="mb-0">Join the hosts earning across Tanzania — start your free listing today.</p></div>
    <?php if (!$juLoggedIn): ?>
      <div class="d-flex flex-column gap-2 align-items-stretch">
        <a href="<?= $this->Url->build('/login') ?>" class="btn-ju btn-ju-light">Add a property</a>
        <a href="<?= $this->Url->build('/signup?role=owner') ?>" style="color:#fff;font-size:13px;text-align:center">New here? Create a free host account</a>
      </div>
    <?php elseif ($isHost): ?>
      <a href="<?= $this->Url->build('/host/onboarding') ?>" class="btn-ju btn-ju-light">Add a property</a>
    <?php else: ?>
      <?= $this->Form->create(null, ['url' => '/join-us', 'id' => 'becomeHostCtaForm']) ?><?= $this->Form->hidden('action', ['value' => 'become_host']) ?><button class="btn-ju btn-ju-light" id="becomeHostCtaBtn" style="border:none">Become a host</button><?= $this->Form->end() ?>
    <?php endif; ?>
  </div>
</div></section>

</main>
</div>
<?= $this->element('footer', ['skin' => 'skin-light-footer']) ?>
