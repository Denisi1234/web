<?php
$loginRole = $loginRole ?? 'customer';
$isHostLogin = $loginRole === 'owner';
$isAdminLogin = $loginRole === 'admin';
if ($isHostLogin) {
    $this->assign('title', 'Host Sign In | FastNet Stays Owner Portal');
    $this->assign('description', 'Sign in to your FastNet Stays host account to manage properties, bookings and earnings.');
} elseif ($isAdminLogin) {
    $this->assign('title', 'Admin Sign In | FastNet Stays');
    $this->assign('description', 'Sign in to the FastNet Stays admin portal.');
} else {
    $this->assign('title', 'Sign in | FastNet Stays — Secure Login');
    $this->assign('description', 'Sign in to FastNet Stays to manage bookings, favourites and member prices across Tanzania.');
}
?>
<?= $this->Html->css('/assets/css/google-travel-layout.css') ?>
<?= $this->Html->css('/assets/css/google-travel-home.css') ?>
<style>
/* Scroll fix: home split-view CSS locks body scroll on desktop — auth pages must scroll */
html, body { height: auto !important; overflow-y: auto !important; }
#main-wrapper { height: auto !important; overflow: visible !important; display: block !important; }
/* ── IBM Carbon polish for login (White theme): scoped, mirrors signup ── */
.cx-li{--cx-blue:#0f62fe;--cx-blue-h:#0353e9;--cx-ink:#161616;--cx-sec:#525252;
  --cx-line:#e0e0e0;--cx-bg:#f4f4f4;--cx-white:#fff;
  font-family:'IBM Plex Sans','Inter',Roboto,Arial,sans-serif;color:var(--cx-ink)}
.cx-li .card{background:var(--cx-white);border:1px solid var(--cx-line);border-top:4px solid var(--cx-blue);border-radius:0;box-shadow:none}
.cx-panel{padding:32px}
@media (min-width:992px){.cx-panel{padding:40px}}
.cx-media{background:#f4f4f4;display:flex;align-items:center}
.cx-media img{display:block;width:100%;height:100%;object-fit:cover}
.cx-li .vr{background-color:var(--cx-line);opacity:1;width:1px}
.cx-li h1{font-weight:600;letter-spacing:-.01em;color:var(--cx-ink)}
.cx-lede{font-size:15px;line-height:1.6;color:var(--cx-sec)}
.cx-li .form-label{display:block;font-size:12px;font-weight:600;letter-spacing:.02em;color:var(--cx-sec);margin-bottom:6px}
.cx-li .form-control{display:block;width:100%;min-height:48px;background:var(--cx-bg);border:1px solid transparent;border-bottom:1px solid #8d8d8d;border-radius:0;padding:0 14px;font-size:15px;color:var(--cx-ink)}
.cx-li .form-control::placeholder{color:rgba(22,22,22,.4)}
.cx-li .form-control:focus{background:var(--cx-white);border:1px solid var(--cx-blue);box-shadow:inset 0 -2px 0 var(--cx-blue);outline:none}
.cx-btn{display:inline-flex;align-items:center;justify-content:center;width:100%;min-height:48px;padding:0 24px;font-size:15px;font-weight:600;text-decoration:none;border:1px solid transparent;border-radius:0;cursor:pointer;background:var(--cx-blue);color:#fff}
.cx-btn:hover{background:var(--cx-blue-h);color:#fff}
.cx-btn:disabled{background:#c6c6c6;border-color:#c6c6c6;color:#525252;cursor:not-allowed}
.cx-btn:focus-visible,.cx-li a:focus-visible,.cx-li input:focus-visible{outline:2px solid var(--cx-blue);outline-offset:2px}
.cx-li .alert-danger{background:#fff1f1;border:1px solid var(--cx-line);border-left:3px solid #da1e28;border-radius:0;color:var(--cx-ink);font-size:13px}
.cx-li .alert-success{background:#defbe6;border:1px solid var(--cx-line);border-left:3px solid #24a148;border-radius:0;color:var(--cx-ink);font-size:13px}
.cx-copy{font-size:12px;color:var(--cx-sec)}
.cx-copy a{color:var(--cx-blue);text-decoration:none}
.cx-copy a:hover{text-decoration:underline}
.cx-pw-toggle{cursor:pointer;color:var(--cx-sec)}
.cx-pw-toggle:hover{color:var(--cx-ink)}
@media (max-width:991px){
  .cx-li section.py-5{padding-top:24px!important;padding-bottom:24px!important}
  .cx-panel{padding:20px}
  .cx-li h1{font-size:26px;line-height:1.2}
  .cx-lede{font-size:14px}
}
@media (prefers-reduced-motion:reduce){.cx-li *{animation:none!important;transition:none!important}}
</style>
<?= $this->element('navbar') ?>
<nav aria-label="Breadcrumb" class="container-fluid px-2 px-lg-2" style="max-width:100%;margin:0 auto;background:#f4f4f4;">
  <ol class="breadcrumb mb-0 py-1" style="background:transparent;font-size:12px;line-height:1.2;--bs-breadcrumb-divider:'›';" itemscope itemtype="https://schema.org/BreadcrumbList">
    <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a href="/" itemprop="item" style="color:#5f6368;text-decoration:none;"><span itemprop="name">Home</span></a><meta itemprop="position" content="1"></li>
    <li class="breadcrumb-item active" aria-current="page" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><span itemprop="name">Sign in</span><meta itemprop="position" content="2"></li>
  </ol>
</nav>
<main id="main-content" class="cx-li" style="background:#f4f4f4;min-height:85vh;" role="main">
<!-- Login Section -->
<section class="py-5">
	<div class="container">

		<div class="row justify-content-center align-items-center m-auto">
			<div class="col-xl-10 col-lg-11 col-12">
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
								<h1 class="mb-2 fs-2"><?= $isHostLogin ? 'Welcome Back, Host!' : 'Welcome Back!' ?></h1>
								<?php if ($isHostLogin): ?>
								<p class="cx-lede mb-0">Access your properties, bookings and earnings. New to hosting?<a href="<?= $this->Url->build('/signup?role=owner'); ?>" class="fw-medium text-primary"> Create a host account</a></p>
								<?php elseif ($isAdminLogin): ?>
								<p class="cx-lede mb-0">Restricted area — administrators only.</p>
								<?php else: ?>
								<p class="cx-lede mb-0">Are you new here?<a href="<?= $this->Url->build('/signup'); ?>" class="fw-medium text-primary"> Create an account</a></p>
								<?php endif; ?>

								<!-- Form START -->
								<form id="web1-login-form" class="mt-4 text-start">
									<div id="login-alert-box"></div>
									<div class="form py-2">
										<div class="form-group mb-3">
											<label class="form-label" for="login-email">Email Address</label>
											<input type="email" id="login-email" class="form-control" placeholder="you@example.com" required autocomplete="email">
										</div>
										<div class="form-group mb-3">
											<label class="form-label" for="login-password">Password</label>
											<div class="position-relative">
												<input type="password" id="login-password" class="form-control pe-5" placeholder="••••••••" required autocomplete="current-password">
												<span class="position-absolute top-50 end-0 translate-middle-y me-3 cx-pw-toggle" onclick="togglePasswordVisibility('login-password', this)">
													<i class="fa-solid fa-eye fs-6"></i>
												</span>
											</div>
										</div>

										<div class="d-flex align-items-center justify-content-between mb-3">
											<div class="ms-auto">
												<a href="<?= $this->Url->build('/forgot-password'); ?>" class="fw-medium" style="font-size:13px">Forgot Password?</a>
											</div>
										</div>

										<div class="form-group">
											<button type="submit" id="login-submit-btn" class="cx-btn">Log In</button>
										</div>
									</div>
								</form>

								<script>
								function togglePasswordVisibility(inputId, containerEl) {
									const input = document.getElementById(inputId);
									if (!input) return;
									const icon = (containerEl && containerEl.querySelector) ? (containerEl.querySelector('i') || containerEl) : document.getElementById(containerEl);
									if (input.type === 'password') {
										input.type = 'text';
										if (icon) icon.className = 'fa-solid fa-eye-slash fs-6';
									} else {
										input.type = 'password';
										if (icon) icon.className = 'fa-solid fa-eye fs-6';
									}
								}

								document.addEventListener("DOMContentLoaded", function() {
									const form = document.getElementById("web1-login-form");
									const alertBox = document.getElementById("login-alert-box");
									const submitBtn = document.getElementById("login-submit-btn");

									if (!form) return;

									form.addEventListener("submit", async function(e) {
										e.preventDefault();
										alertBox.innerHTML = '';
										submitBtn.disabled = true;
										submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Logging in...';

										const payload = {
											email: document.getElementById("login-email").value.trim(),
											password: document.getElementById("login-password").value
										};

										try {
											const endpoint = (typeof window.API_URL === 'function') 
												? window.API_URL('/api/login') 
												: 'http://127.0.0.1:8000/api/login';

											const res = await fetch(endpoint, {
												method: 'POST',
												headers: {
													'Content-Type': 'application/json',
													'Accept': 'application/json'
												},
												body: JSON.stringify(payload)
											});

											const data = await res.json();

											if (res.ok || res.status === 200) {
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
														credentials: 'same-origin',
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

												alertBox.innerHTML = '<div class="alert alert-success">Login successful! Updating header...</div>';
												setTimeout(() => {
													function safeRedirect(u) {
														if (typeof u !== 'string' || u === '' || u.length > 500) return '';
														if (u.charAt(0) !== '/' || u.charAt(1) === '/') return '';
														if (u.indexOf('\\') !== -1 || /[\r\n\t<>"]/.test(u)) return '';
														if (!/^\/[A-Za-z0-9\/_\-.?=&%#+]*$/.test(u)) return '';
														return u;
													}
													const params = new URLSearchParams(window.location.search);
													const redirect = safeRedirect(params.get('redirect') || '');
													const pageRole = (params.get('role') === 'owner' || params.get('role') === 'admin') ? params.get('role') : 'customer';
													const role = (data.user && data.user.role || '').toLowerCase();
													let dest = '<?= $this->Url->build('/'); ?>';
													if (redirect) {
														dest = redirect;
													} else if (role === 'owner') {
														dest = '<?= $this->Url->build('/host/dashboard'); ?>';
													} else if (role === 'admin') {
														dest = '<?= $this->Url->build('/admin/dashboard'); ?>';
													} else if (pageRole === 'owner') {
														// Host-intent sign-in, plain customer account → convert
														dest = '<?= $this->Url->build('/join-us'); ?>';
													}
													window.location.href = dest;
												}, 400);
											} else {
												const errMsg = data.message || (data.errors ? Object.values(data.errors).flat().join('<br>') : 'Invalid email or password.');
												alertBox.innerHTML = '<div class="alert alert-danger">' + errMsg + '</div>';
											}
										} catch(err) {
											console.error("Login error:", err);
											alertBox.innerHTML = '<div class="alert alert-danger">Unable to connect to authentication server.</div>';
										} finally {
											submitBtn.disabled = false;
											submitBtn.innerHTML = 'Log In';
										}
									});
								});
								</script>

								<!-- Copyright -->
								<div class="cx-copy mt-4 text-center"> © <?= date('Y') ?> FastNet Stays Ltd. <a href="/privacy-policy">Privacy</a> · <a href="/terms-of-service">Terms</a> · <a href="/help-center">Help</a></div>
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