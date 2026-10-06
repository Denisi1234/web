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
<?= $this->Html->css('/assets/css/carbon-auth-01.css?v=' . filemtime(WWW_ROOT . 'assets/css/carbon-auth-01.css')) ?>
<?= $this->Html->css('/assets/css/carbon-auth-02.css?v=' . filemtime(WWW_ROOT . 'assets/css/carbon-auth-02.css')) ?>
<?= $this->element('navbar') ?>
<?= $this->element('breadcrumb-schema', ['label' => $isOwnerSignup ? 'Become a host' : 'Create account']) ?>
<main id="main-content" class="cx-auth cx-su" style="background:var(--cds-gray-10);min-height:85vh;" role="main">
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
								<p class="cx-lede mb-0">Create a free host account. Already a host?<a href="<?= $this->Url->build('/login'); ?>" class="fw-medium text-primary"> Sign in</a></p>
								<div class="cx-note mt-3" role="note">
									<span>Keep <b>90%</b> of every paid stay, paid to your M-Pesa number.</span>
								</div>
								<?php else: ?>
								<p class="cx-lede mb-0">Save stays and book instantly. Already a member?<a href="<?= $this->Url->build('/login'); ?>" class="fw-medium text-primary"> Sign in</a></p>
								<?php endif; ?>

								<!-- Form START -->
								<form id="web1-signup-form" class="mt-4 text-start" data-role="<?= h($requestedRole) ?>">
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
										<?php if ($isOwnerSignup): ?><p class="cx-copy mt-2">By continuing you agree to the <a href="<?= $this->Url->build('/terms-of-service') ?>">Host Terms</a> and <a href="<?= $this->Url->build('/privacy-policy') ?>">Privacy Policy</a>. No listing fees.</p><?php endif; ?>
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

										// requestedRole is resolved server-side (query param,
										// remembered intent, or referring host page), so the
										// host journey is not lost just because ?role= is
										// missing from the URL.
										const requestedRole = <?= json_encode($requestedRole === 'owner' ? 'owner' : 'customer') ?>;
										const isHostFlow = requestedRole === 'owner';
										const btnLabel = submitBtn.innerHTML;
									function fail(msg) {
										alertBox.innerHTML = '<div class="alert alert-danger">' + msg + '</div>';
										try { if (window.FastnetLoader && typeof window.FastnetLoader.hide === 'function') window.FastnetLoader.hide(); } catch (e) {}
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

											// Synchronize CakePHP session. The sync endpoint verifies
											// the token against /me and echoes the VERIFIED user —
											// that verified role (never the requested intent) decides
											// where the account lands.
											let verifiedRole = (data.user && data.user.role ? String(data.user.role) : '').toLowerCase();
											try {
												const syncRes = await fetch('<?= $this->Url->build('/login'); ?>', {
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
												const syncData = await syncRes.json();
												if (syncData && syncData.user && syncData.user.role) {
													verifiedRole = String(syncData.user.role).toLowerCase();
												}
											} catch(errSync) {}

										alertBox.innerHTML = '<div class="alert alert-success">' + (verifiedRole === 'owner' ? 'Host account ready! Opening your dashboard...' : 'Registration successful! Updating header...') + '</div>';
										setTimeout(() => {
											// Land by backend-verified role only. A customer account —
											// even from the host form — never lands inside host tooling.
											if (verifiedRole === 'owner') {
												window.location.href = '<?= $this->Url->build('/host/dashboard'); ?>';
											} else if (verifiedRole === 'admin') {
												window.location.href = '<?= $this->Url->build('/admin/dashboard'); ?>';
											} else {
												window.location.href = '<?= $this->Url->build('/'); ?>';
											}
											}, 400);
											} else {
												const errMsg = data.message || (data.errors ? Object.values(data.errors).flat().join('<br>') : 'Registration failed.');
												const errLower = errMsg.toLowerCase();
												if (errLower.includes('already been taken') || errLower.includes('already taken') || errLower.includes('already exists')) {
													alertBox.innerHTML = `
														<div class="alert alert-primary p-3" style="border-radius:8px">
															<div class="fw-bold mb-1"><i class="fa-solid fa-circle-info me-1"></i> You already have an account!</div>
															<div style="font-size:13.5px;line-height:1.4">An account with <b>${email}</b> is already registered. Simply sign in to activate Host mode on your existing account.</div>
															<div class="mt-2">
																<a href="<?= $this->Url->build('/login?role=owner') ?>&email=${encodeURIComponent(email)}" class="btn btn-sm btn-primary" style="font-weight:600">Sign in to activate host mode →</a>
															</div>
														</div>
													`;
												} else {
													alertBox.innerHTML = '<div class="alert alert-danger">' + errMsg + '</div>';
												}
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