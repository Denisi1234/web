<?php
$isOwnerSignup = (($requestedRole ?? 'customer') === 'owner');
if ($isOwnerSignup) {
    $this->assign('title', 'Become a Host | List Your Property on FastNet Stays');
    $this->assign('description', 'Create a free host account on FastNet Stays. List your lodge, keep 90% of every booking with M-Pesa payouts.');
} else {
    $this->assign('title', 'Create account | FastNet Stays');
    $this->assign('description', 'Create a FastNet Stays account to save stays, get member prices and book instantly across Tanzania.');
}
?>
<?= $this->Html->css('/assets/css/google-travel-layout.css') ?>
<?= $this->Html->css('/assets/css/google-travel-home.css') ?>
<style>
/* Scroll fix: home split-view CSS locks body scroll on desktop — auth pages must scroll */
html, body { height: auto !important; overflow-y: auto !important; }
#main-wrapper { height: auto !important; overflow: visible !important; display: block !important; }
/* ── IBM Carbon polish for signup (White theme): scoped, no layout change ── */
.cx-su{--cx-blue:#0f62fe;--cx-blue-h:#0353e9;--cx-ink:#161616;--cx-sec:#525252;
  --cx-line:#e0e0e0;--cx-bg:#f4f4f4;--cx-white:#fff;--cx-info-b:#d0e2ff;
  font-family:'IBM Plex Sans','Inter',Roboto,Arial,sans-serif;color:var(--cx-ink)}
.cx-su .card{background:var(--cx-white);border:1px solid var(--cx-line);border-top:4px solid var(--cx-blue);border-radius:0;box-shadow:none}
.cx-su .card .row{gutter-x:0}
.cx-panel{padding:32px}
@media (min-width:992px){.cx-panel{padding:40px}}
.cx-media{background:#f4f4f4;display:flex;align-items:center}
.cx-media img{display:block;width:100%;height:100%;object-fit:cover}
.cx-su .vr{background-color:var(--cx-line);opacity:1;width:1px}
.cx-su h1{font-weight:600;letter-spacing:-.01em;color:var(--cx-ink)}
.cx-lede{font-size:15px;line-height:1.6;color:var(--cx-sec)}
.cx-note{display:flex;gap:10px;background:var(--cx-bg);border:1px solid var(--cx-line);border-left:3px solid var(--cx-blue);padding:12px 14px;font-size:13px;line-height:1.55;color:var(--cx-ink)}
.cx-note a{color:var(--cx-blue)}
.cx-su .form-label{display:block;font-size:12px;font-weight:600;letter-spacing:.02em;color:var(--cx-sec);margin-bottom:6px}
.cx-su .form-control,.cx-su .form-select{display:block;width:100%;min-height:48px;background:var(--cx-bg);border:1px solid transparent;border-bottom:1px solid #8d8d8d;border-radius:0;padding:0 14px;font-size:15px;color:var(--cx-ink)}
.cx-su .form-control::placeholder{color:rgba(22,22,22,.4)}
.cx-su .form-control:focus,.cx-su .form-select:focus{background:var(--cx-white);border:1px solid var(--cx-blue);box-shadow:inset 0 -2px 0 var(--cx-blue);outline:none}
.cx-su .text-muted{color:var(--cx-sec)!important;font-size:12px}
.cx-btn{display:inline-flex;align-items:center;justify-content:center;width:100%;min-height:48px;padding:0 24px;font-size:15px;font-weight:600;text-decoration:none;border:1px solid transparent;border-radius:0;cursor:pointer;background:var(--cx-blue);color:#fff}
.cx-btn:hover{background:var(--cx-blue-h);color:#fff}
.cx-btn:disabled{background:#c6c6c6;border-color:#c6c6c6;color:#525252;cursor:not-allowed}
.cx-btn:focus-visible,.cx-su a:focus-visible,.cx-su input:focus-visible{outline:2px solid var(--cx-blue);outline-offset:2px}
.cx-steps{display:grid;gap:0;margin-top:16px;border-top:1px solid var(--cx-line)}
.cx-steps div{display:flex;gap:12px;padding:12px 2px;border-bottom:1px solid var(--cx-line);font-size:13px;color:var(--cx-sec)}
.cx-steps b{color:var(--cx-ink)}
.cx-num{display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;flex:none;background:var(--cx-ink);color:#fff;font-size:12px;font-weight:600}
.cx-su .alert-danger{background:#fff1f1;border:1px solid var(--cx-line);border-left:3px solid #da1e28;border-radius:0;color:var(--cx-ink);font-size:13px}
.cx-su .alert-success{background:#defbe6;border:1px solid var(--cx-line);border-left:3px solid #24a148;border-radius:0;color:var(--cx-ink);font-size:13px}
.cx-copy{font-size:12px;color:var(--cx-sec)}
.cx-copy a{color:var(--cx-blue);text-decoration:none}
.cx-copy a:hover{text-decoration:underline}
@media (prefers-reduced-motion:reduce){.cx-su *{animation:none!important;transition:none!important}}
/* ── Mobile: focused single-column form, tighter spacing ── */
@media (max-width:991px){
  .cx-su section.py-5{padding-top:24px!important;padding-bottom:24px!important}
  .cx-panel{padding:20px}
  .cx-su h1{font-size:26px;line-height:1.2}
  .cx-lede{font-size:14px}
  .cx-steps div{padding:10px 2px;font-size:12px}
}
</style>
<?= $this->element('navbar') ?>
<nav aria-label="Breadcrumb" class="container-fluid px-2 px-lg-2" style="max-width:100%;margin:0 auto;background:#f4f4f4;">
  <ol class="breadcrumb mb-0 py-1" style="background:transparent;font-size:12px;line-height:1.2;--bs-breadcrumb-divider:'›';" itemscope itemtype="https://schema.org/BreadcrumbList">
    <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a href="/" itemprop="item" style="color:#5f6368;text-decoration:none;"><span itemprop="name">Home</span></a><meta itemprop="position" content="1"></li>
    <li class="breadcrumb-item active" aria-current="page" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><span itemprop="name"><?= $isOwnerSignup ? 'Become a host' : 'Create account' ?></span><meta itemprop="position" content="2"></li>
  </ol>
</nav>
<main id="main-content" class="cx-su" style="background:#f4f4f4;min-height:85vh;" role="main">
<!-- Signup Section -->
<section class="py-5">
	<div class="container">

		<div class="row justify-content-center align-items-center m-auto">
			<div class="col-xl-11 col-lg-12 col-12">
				<div class="bg-mode card overflow-hidden">
					<div class="row g-0">
						<!-- Vector Image : desktop only (decorative) -->
						<div class="col-lg-6 cx-media order-2 order-lg-1 d-none d-lg-flex">
							<div class="p-3 p-lg-5">
								<img src="<?= $this->Url->build('/assets/img/login.svg'); ?>" class="img-fluid" alt="" loading="lazy">
							</div>
							<!-- Divider -->
							<div class="vr opacity-1 d-none d-lg-block"></div>
						</div>

						<!-- Information -->
						<div class="col-lg-6 order-1">
							<div class="cx-panel">
																<?= $this->element('logo', ['size' => 22]) ?>
								<!-- Title -->
								<h1 class="mb-2 fs-2"><?= $isOwnerSignup ? 'Become a Host' : 'Create New Account' ?></h1>
								<?php if ($isOwnerSignup): ?>
								<p class="cx-lede mb-0">Here to list your property? Create your free host account below. Already a host?<a href="<?= $this->Url->build('/login'); ?>" class="fw-medium text-primary"> Sign in</a></p>
								<div class="cx-note mt-3" role="note">
									<span>Free listing — you keep <b>90%</b> of every paid stay, settled to M-Pesa. <a href="<?= $this->Url->build('/join-us') ?>">How hosting works</a></span>
								</div>
								<div class="cx-steps" aria-label="Hosting steps">
									<div><span class="cx-num">1</span><span><b>Create this account</b> — owner access, seconds.</span></div>
									<div><span class="cx-num">2</span><span><b>Add property &amp; rooms</b> — photos, rates, inventory.</span></div>
									<div><span class="cx-num">3</span><span><b>Pass review &amp; earn</b> — usually within 24 hrs.</span></div>
								</div>
								<?php else: ?>
								<p class="cx-lede mb-0">Join FastNet Stays as a guest. Already a Member?<a href="<?= $this->Url->build('/login'); ?>" class="fw-medium text-primary"> Signin</a></p>
								<?php endif; ?>

								<!-- Form START -->
								<form id="web1-signup-form" class="mt-4 text-start">
									<div id="signup-alert-box"></div>
									<div class="form py-2">
										<div class="form-group">
											<label class="form-label">Full Name</label>
											<input type="text" id="signup-name" class="form-control" placeholder="" required>
										</div>
										<div class="form-group">
											<label class="form-label">Email Address</label>
											<input type="email" id="signup-email" class="form-control" placeholder="" required>
										</div>
										<div class="form-group">
											<label class="form-label"><?= $isOwnerSignup ? 'M-Pesa Payout Number (Required)' : 'Phone Number (Optional)' ?></label>
											<input type="tel" id="signup-phone" class="form-control" placeholder="<?= $isOwnerSignup ? '+255...' : '' ?>" <?= $isOwnerSignup ? 'required' : '' ?> autocomplete="tel">
											<?php if ($isOwnerSignup): ?><small class="text-muted">Booking payouts are settled to this number.</small><?php endif; ?>
										</div>
										<div class="form-group">
											<label class="form-label">Enter Password</label>
											<div class="position-relative">
												<input type="password" class="form-control" id="signup-password" name="password" placeholder="" required minlength="8" autocomplete="new-password">
												<span class="fa-solid fa-eye toggle-password position-absolute top-50 end-0 translate-middle-y me-3" onclick="let p=document.getElementById('signup-password'); p.type = p.type==='password'?'text':'password';"></span>
											</div>
											<small class="text-muted">Minimum 8 characters.</small>
										</div>

										<div class="form-group">
											<button type="submit" id="signup-submit-btn" class="cx-btn"><?= $isOwnerSignup ? 'Create Host Account — Free' : 'Create An Account' ?></button>
										</div>
										<?php if ($isOwnerSignup): ?><p class="cx-copy mt-2">By continuing you agree to the <a href="<?= $this->Url->build('/terms-of-service') ?>">Host Terms</a> and <a href="<?= $this->Url->build('/privacy-policy') ?>">Privacy Policy</a>. No listing fees — 10% applies only on paid stays.</p><?php endif; ?>
									</div>
								</form>

								<script>
								document.addEventListener("DOMContentLoaded", function() {
									const form = document.getElementById("web1-signup-form");
									const alertBox = document.getElementById("signup-alert-box");
									const submitBtn = document.getElementById("signup-submit-btn");

									if (!form) return;

									form.addEventListener("submit", async function(e) {
										e.preventDefault();
										alertBox.innerHTML = '';

										const urlParams = new URLSearchParams(window.location.search);
										const requestedRole = (urlParams.get('role') === 'owner') ? 'owner' : 'customer';
										const isHostFlow = requestedRole === 'owner';
										const btnLabel = submitBtn.innerHTML;
										function fail(msg) {
											alertBox.innerHTML = '<div class="alert alert-danger">' + msg + '</div>';
											submitBtn.disabled = false;
											submitBtn.innerHTML = btnLabel;
										}

										const name = document.getElementById("signup-name").value.trim();
										const email = document.getElementById("signup-email").value.trim();
										const phone = document.getElementById("signup-phone").value.trim();
										const password = document.getElementById("signup-password").value;
										if (name.length < 2) return fail('Please enter your full name.');
										if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) return fail('Please enter a valid email address.');
										if (isHostFlow && phone.length < 9) return fail('Hosts need an M-Pesa payout number so we can settle your earnings.');
										if (password.length < 8) return fail('Password must be at least 8 characters.');

										submitBtn.disabled = true;
										submitBtn.innerHTML = '<span class="p-dots" aria-hidden="true"><span class="p-dot"></span><span class="p-dot"></span><span class="p-dot"></span></span>' + (isHostFlow ? 'Creating host account...' : 'Creating Account...');

										const payload = {
											name: name,
											email: email,
											phone_number: phone,
											password: password,
											role: requestedRole
										};

										try {
											const endpoint = (typeof window.API_URL === 'function') 
												? window.API_URL('/api/register') 
												: 'http://127.0.0.1:8000/api/register';

											const res = await fetch(endpoint, {
												method: 'POST',
												headers: {
													'Content-Type': 'application/json',
													'Accept': 'application/json'
												},
												body: JSON.stringify(payload)
											});

											const data = await res.json();

											if (res.ok || res.status === 201) {
												const authToken = data.access_token || data.token;
												localStorage.removeItem('is_logged_out');
												if (authToken) {
													localStorage.setItem('auth_token', authToken);
													localStorage.setItem('token', authToken);
												}
												if (data.user) {
													localStorage.setItem('user', JSON.stringify(data.user));
												}

												// Synchronize CakePHP session
												try {
													await fetch('<?= $this->Url->build('/login'); ?>', {
														method: 'POST',
														headers: {
															'Content-Type': 'application/json',
															'X-Requested-With': 'XMLHttpRequest'
														},
														body: JSON.stringify({
															action: 'login_sync',
															user: data.user,
															token: authToken
														})
													});
												} catch(errSync) {}

											alertBox.innerHTML = '<div class="alert alert-success">' + (isHostFlow ? 'Host account ready! Opening your dashboard...' : 'Registration successful! Updating header...') + '</div>';
											setTimeout(() => {
												const isHost = (data.user && data.user.role === 'owner') || requestedRole === 'owner';
													// Land on their own portal home (role dashboards handle the rest)
													window.location.href = isHost ? '<?= $this->Url->build('/host/dashboard'); ?>' : '<?= $this->Url->build('/'); ?>';
												}, 400);
											} else {
												const errMsg = data.message || (data.errors ? Object.values(data.errors).flat().join('<br>') : 'Registration failed.');
												alertBox.innerHTML = '<div class="alert alert-danger">' + errMsg + '</div>';
											}
										} catch(err) {
											console.error("Signup error:", err);
											alertBox.innerHTML = '<div class="alert alert-danger">Unable to connect to registration server.</div>';
										} finally {
											if (!alertBox.querySelector('.alert-success')) {
												submitBtn.disabled = false;
												submitBtn.innerHTML = btnLabel;
											}
										}
									});
								});
								</script>

									<!-- Copyright -->
									<div class="text-muted-2 mt-4 text-center" style="font-size:12px;color:#70757a;font-family:Roboto,sans-serif;"> © <?= date('Y') ?> FastNet Stays Ltd. <a href="/privacy-policy" style="color:#1a73e8;text-decoration:none;">Privacy</a> · <a href="/terms-of-service" style="color:#1a73e8;text-decoration:none;">Terms</a></div>
								</form>
								<!-- Form END -->
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

	</div>
</section>
</main>