<?php
$this->assign('title', 'Sign in | FastNet Stays — Secure Login');
$this->assign('description', 'Sign in to FastNet Stays to manage bookings, favourites and member prices across Tanzania.');
?>
<?= $this->Html->css('/assets/css/google-travel-layout.css') ?>
<?= $this->Html->css('/assets/css/google-travel-home.css') ?>
<?= $this->element('navbar') ?>
<main id="main-content" style="background:#f8f9fa;min-height:85vh;" role="main">
<!-- Login Section -->
<section class="py-5">
	<div class="container">

		<div class="row justify-content-center align-items-center m-auto">
			<div class="col-xl-10 col-lg-11 col-12">
				<div class="bg-mode card shadow-sm rounded-3 overflow-hidden">
					<div class="row g-0">
						<!-- Vector Image -->
						<div class="col-lg-6 d-flex align-items-center order-2 order-lg-1">
							<div class="p-3 p-lg-5">
								<img src="<?= $this->Url->build('/assets/img/login.svg'); ?>" class="img-fluid" alt="">
							</div>
							<!-- Divider -->
							<div class="vr opacity-1 d-none d-lg-block"></div>
						</div>

						<!-- Information -->
						<div class="col-lg-6 order-1">
							<div class="p-3 p-sm-4 p-md-5">
								<a href="<?= $this->Url->build('/'); ?>" class="d-inline-flex align-items-center text-decoration-none mb-4" title="fastnetstays.com" style="text-decoration:none!important;">
									<link href="https://fonts.googleapis.com/css2?family=Grand+Hotel&display=swap" rel="stylesheet">
									<span style="font-family:'Grand Hotel','Brush Script MT',cursive;font-size:34px;font-weight:400;letter-spacing:-.02em;line-height:1;background:linear-gradient(45deg,#feda75 0%,#fa7e1e 18%,#d62976 38%,#962fbf 68%,#4f5bd5 100%);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;filter:drop-shadow(0 1px 0 rgba(0,0,0,.04));white-space:nowrap;">FastNetStays</span>
									<span style="font-family:'Grand Hotel',cursive;font-size:13px;font-weight:400;color:#9ca3af;letter-spacing:.02em;margin-left:1px;align-self:flex-end;margin-bottom:4px;opacity:.9;">.com</span>
								</a>
								<!-- Title -->
								<h1 class="mb-2 fs-2">Welcome Back!</h1>
								<p class="mb-0">Are you new here?<a href="<?= $this->Url->build('/signup'); ?>" class="fw-medium text-primary"> Create an account</a></p>

								<!-- Form START -->
								<form id="web1-login-form" class="mt-4 text-start">
									<div id="login-alert-box"></div>
									<div class="form py-2">
										<div class="form-floating mb-3">
											<input type="email" id="login-email" class="form-control" placeholder=" " required>
											<label>Email Address</label>
										</div>
										<div class="form-floating mb-3 position-relative">
											<input type="password" id="login-password" class="form-control pe-5" placeholder=" " required>
											<label>Password</label>
											<span class="position-absolute top-50 end-0 translate-middle-y me-3 cursor-pointer text-slate-500 hover:text-slate-800" onclick="togglePasswordVisibility('login-password', this)">
												<i class="fa-solid fa-eye fs-6"></i>
											</span>
										</div>

										<div class="modal-flex-item d-flex align-items-center justify-content-between mb-3">
											<div class="modal-flex-last ms-auto">
												<a href="<?= $this->Url->build('/forgot-password'); ?>" class="text-primary fw-medium">Forgot Password?</a>
											</div>
										</div>
										
										<div class="form-group">
											<button type="submit" id="login-submit-btn" class="btn btn-primary full-width font--bold btn-lg">Log In</button>
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
													window.location.href = '<?= $this->Url->build('/'); ?>';
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
								<div class="text-muted-2 mt-4 text-center" style="font-size:12px;color:#70757a;font-family:Roboto,sans-serif;"> © <?= date('Y') ?> FastNet Stays Ltd. <a href="/privacy-policy" style="color:#1a73e8;text-decoration:none;">Privacy</a> · <a href="/terms-of-service" style="color:#1a73e8;text-decoration:none;">Terms</a> · <a href="/help-center" style="color:#1a73e8;text-decoration:none;">Help</a></div>
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
<div class="d-none d-md-block"><?= $this->element('footer', ['skin' => 'skin-light-footer']) ?></div>