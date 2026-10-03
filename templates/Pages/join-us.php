<?php
$this->assign('title', 'Become a Host | List Your Property on FastNet Stays');
$this->assign('description', 'List your hotel, lodge or apartment on FastNet Stays. Free listing, 90% payout, M-Pesa and Tigo Pesa settlements, 24/7 host support across Tanzania.');
$juLoggedIn = !empty($isLoggedIn);
$juRole = strtolower((string)($userRole ?? ''));
$isHost = in_array($juRole, ['owner', 'admin'], true);
$myList = (isset($myProperties) && is_array($myProperties)) ? $myProperties : [];

// A host whose account is still "Pending Verification" has no way forward
// without submitting documents, so surface the KYC form directly.
$juNeedsVerification = $juLoggedIn && $juRole === 'owner'
    && strtolower((string)($sessionUser['status'] ?? '')) === 'pending verification';
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
.ju-note {
  background: #fff;
  border-left: 3px solid #0f62fe;
  padding: 14px 16px;
  max-width: 720px;
  font-size: 14px;
  line-height: 1.6;
  color: #525252;
}
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

/* Square field corners to match the Carbon auth surfaces (/login, /signup).
   This page is not inside .cx-auth, so the rule there does not reach it and
   Bootstrap's .375rem radius would otherwise apply. */
#main-content .form-control,
#main-content .form-select,
#main-content .form-control:focus,
#main-content .form-control:hover,
#main-content .form-control:focus,
#main-content .form-control[readonly],
#main-content .form-control.is-invalid {
  border-radius: 0 !important;
}
#main-content .form-control:focus {
  outline: 2px solid var(--cds-focus, #0f62fe);
  outline-offset: -2px;
  border-color: #0f62fe;
}
#main-content .form-control.is-invalid {
  border-color: #da1e28;
}
#main-content input[type='file'].form-control { padding: 10px 14px; }

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
        <a href="<?= $this->Url->build('/signup?role=owner') ?>" class="btn-ju btn-ju-primary">Register as a host</a>
        <a href="<?= $this->Url->build('/login') ?>" class="btn-ju btn-ju-outline">Sign in</a>
      <?php elseif ($isHost): ?>
        <a href="<?= $this->Url->build('/host/onboarding') ?>" class="btn-ju btn-ju-primary">Add a new property</a>
        <a href="<?= $this->Url->build('/host/dashboard') ?>" class="btn-ju btn-ju-outline">Open host dashboard</a>
      <?php else: ?>
        <a href="<?= $this->Url->build('/signup?role=owner') ?>" class="btn-ju btn-ju-primary">Register as a host</a>
        <a href="<?= $this->Url->build('/login?redirect=/join-us') ?>" class="btn-ju btn-ju-outline">Sign in</a>
      <?php endif; ?>
      <a href="#how-it-works" class="btn-ju btn-ju-outline">How listing works</a>
    </div>

    <?php if ($juLoggedIn && !$isHost): ?>
      <!-- A guest account books and stays. It is deliberately NOT an owner
           account, and this page must not offer to convert it into one:
           hosting needs its own account with its own sign-in. -->
      <div class="ju-note mt-3" role="note">
        You are signed in with a <strong>guest account</strong>. That account is for booking
        and staying — it is <strong>not</strong> a host account, so it cannot list a property.
        To host, register a separate host account, or sign in to the host account you already use.
      </div>
    <?php endif; ?>
    <div class="ju-meta mt-4">
      <div><strong>90%</strong>Your share of each booking</div>
      <div><strong>Free</strong>No setup or listing fees</div>
      <div><strong>24 hrs</strong>Typical review turnaround</div>
    </div>
  </div>
</header>

<?php if ($juLoggedIn && $isHost): ?>
<!-- Owner verification (KYC). Shown only to a signed-in owner, and only while
     the account is still "Pending Verification". Without this there is no path
     for a host to submit the identity documents the admin queue reviews, so the
     account would stay stuck. -->
<section class="ju-section" id="verify-identity" style="scroll-margin-top:80px">
  <div class="container" style="max-width:1140px">
    <div class="ju-label">Verification</div>
    <h2 class="ju-h2">Verify your identity</h2>
    <p class="ju-lead mt-2" style="max-width:640px">
      Required before your first property goes live. Our team reviews documents
      manually — usually within one business day.
    </p>

    <form id="kycForm" enctype="multipart/form-data" novalidate style="max-width:860px">
    <div class="d-grid gap-3 mt-2">
     <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label" for="kyc_full_name">Full legal name</label>
        <input class="form-control" id="kyc_full_name" name="full_name" required maxlength="255" autocomplete="name">
      </div>
      <div class="col-md-6">
        <label class="form-label" for="kyc_phone">Mobile money number</label>
        <input class="form-control" id="kyc_phone" name="phone_number" required maxlength="255" autocomplete="tel" placeholder="0714 000 000">
      </div>
     </div>

     <div>
      <label class="form-label" for="kyc_id_number">National ID number</label>
      <input class="form-control" id="kyc_id_number" name="id_number" required maxlength="255">
     </div>

     <div>
      <label class="form-label" for="kyc_id_doc">Photo of your National ID</label>
      <input class="form-control" type="file" id="kyc_id_doc" name="id_document" accept="image/png,image/jpeg,image/webp" required>
      <div class="form-text">JPG, PNG or WebP. Max 10 MB.</div>
     </div>

     <fieldset style="border:1px solid #e8eaed;border-radius:8px;padding:14px">
      <legend style="font-size:13px;font-weight:600;padding:0 6px">Business registration <span class="text-muted" style="font-weight:400">(if registered)</span></legend>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label" for="kyc_brn">Registration number</label>
          <input class="form-control" id="kyc_brn" name="business_registration_number" maxlength="255">
        </div>
        <div class="col-md-6">
          <label class="form-label" for="kyc_bdoc">Business licence photo</label>
          <input class="form-control" type="file" id="kyc_bdoc" name="business_document" accept="image/png,image/jpeg,image/webp">
        </div>
      </div>
    </fieldset>

    <fieldset style="border:1px solid #e8eaed;border-radius:8px;padding:14px">
      <legend style="font-size:13px;font-weight:600;padding:0 6px">Payout account <span class="text-muted" style="font-weight:400">(optional now, needed to withdraw)</span></legend>
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label" for="kyc_bank">Bank name</label>
          <input class="form-control" id="kyc_bank" name="payout_bank_name" maxlength="255">
        </div>
        <div class="col-md-4">
          <label class="form-label" for="kyc_acct">Account number</label>
          <input class="form-control" id="kyc_acct" name="payout_account_number" maxlength="255">
        </div>
        <div class="col-md-4">
          <label class="form-label" for="kyc_acctname">Account holder</label>
          <input class="form-control" id="kyc_acctname" name="payout_account_name" maxlength="255">
        </div>
      </div>
    </fieldset>

    <div id="kycError" class="d-none" style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;padding:10px 12px;border-radius:8px;font-size:13px"></div>
   </div>
   <button type="submit" class="btn-ju btn-ju-primary mt-3" id="kycSubmitBtn" style="width:auto;padding:0 28px">Submit for review</button>
  </form>
  </div>
</section>
<script>
// Owner KYC submission -> POST /join-us/verify (PagesController::submitOwnerVerification)
(function () {
  var form   = document.getElementById('kycForm');
  var btn    = document.getElementById('kycSubmitBtn');
  var errBox = document.getElementById('kycError');
  if (!form || !btn) return;

  var fail = function (msg) {
    if (errBox) { errBox.textContent = msg; errBox.classList.remove('d-none'); }
    btn.disabled = false;
    btn.textContent = 'Submit for review';
  };

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    if (errBox) { errBox.classList.add('d-none'); errBox.textContent = ''; }

    btn.disabled = true;
    btn.textContent = 'Uploading documents...';

    fetch('/join-us/verify', {
      method: 'POST',
      body: new FormData(form),
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin'
    })
      .then(function (r) { return r.json().catch(function () { return {}; }); })
      .then(function (data) {
        if (data && data.status === 'success') {
          btn.textContent = 'Submitted';
          window.location.reload();
          return;
        }
        fail((data && data.message) || 'Submission failed. Please try again.');
      })
      .catch(function () { fail('Could not reach the server. Please try again.'); });
  });
})();
</script>
<script>
// KYC submit button loading state: instant feedback + no double submit.
(function () {
  var btn = document.getElementById('kycSubmitBtn');
  if (!btn) return;
  var form = document.getElementById('kycForm');
  if (!form) return;
  form.addEventListener('submit', function () {
    if (btn.disabled) return;
    if (window.FastAPI && FastAPI.btnDots) { FastAPI.btnDots(btn, true); return; }
    btn.disabled = true; btn.textContent = 'Submitting…';
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
      <?php if (!$juLoggedIn): ?><a href="<?= $this->Url->build('/signup?role=owner') ?>">Register as a host</a><?php elseif (!$isHost): ?><a href="<?= $this->Url->build('/signup?role=owner') ?>">Register a host account</a><?php elseif ($juNeedsVerification): ?><a href="#verify-identity">Submit verification documents</a><?php else: ?><strong>Done — you're a host.</strong><?php endif; ?>
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
  <details><summary>I already have a guest account — must I register again?</summary><p style="font-size:14px;color:#525252" class="mt-2">Yes. A guest account is for booking and staying only, and it is never turned into a host account. Register a separate host account with its own email and password, then sign in to it to list your property. Your guest bookings stay on your guest account.</p></details>
</div></section>

<!-- Final CTA -->
<section class="ju-section" style="padding-top:0"><div class="container" style="max-width:1140px">
  <div class="ju-cta p-4 p-md-5 d-flex flex-wrap gap-3 align-items-center">
    <div class="ju-cta-text"><h2 style="font-weight:600" class="mb-1">Ready when you are.</h2><p class="mb-0">Join the hosts earning across Tanzania — start your free listing today.</p></div>
    <?php if ($isHost): ?>
      <a href="<?= $this->Url->build('/host/onboarding') ?>" class="btn-ju btn-ju-light">Add a property</a>
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
