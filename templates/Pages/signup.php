<?php
$this->assign('title', 'Create account | FastNet Stays');
$this->assign('description', 'Create a FastNet Stays account to save stays, get member prices and book instantly across Tanzania.');
?>
<?= $this->Html->css('/assets/css/google-travel-layout.css') ?>
<?= $this->Html->css('/assets/css/google-travel-home.css') ?>
<?= $this->element('navbar') ?>
<div class="gh-m-tabs" role="tablist" aria-label="Travel types">
  <a href="/?explore=1" role="tab">Explore</a>
  <a href="/?homes=1" role="tab">Homes</a>
  <a href="/" role="tab">Hotels</a>
  <a href="/?destination=Vacation" role="tab">Vacation rentals</a>
</div>
<nav aria-label="Breadcrumb" class="container-fluid px-2 px-lg-2" style="max-width:100%;margin:0 auto;background:#f8f9fa;">
  <ol class="breadcrumb mb-0 py-1" style="background:transparent;font-size:12px;line-height:1.2;--bs-breadcrumb-divider:'›';" itemscope itemtype="https://schema.org/BreadcrumbList">
    <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a href="/" itemprop="item" style="color:#5f6368;text-decoration:none;"><span itemprop="name">Home</span></a><meta itemprop="position" content="1"></li>
    <li class="breadcrumb-item active" aria-current="page" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><span itemprop="name">Create account</span><meta itemprop="position" content="2"></li>
  </ol>
</nav>
<main id="main-content" style="background:#f8f9fa;min-height:85vh;" role="main">
<!-- Login Section -->
<section class="py-5">
	<div class="container">

		<div class="row justify-content-center align-items-center m-auto">
			<div class="col-xl-11 col-lg-12 col-12">
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
							<div class="p-4 p-sm-7">
								<a href="<?= $this->Url->build('/'); ?>" class="d-inline-flex flex-column text-decoration-none mb-4">
									<div class="d-flex align-items-baseline lh-1">
										<span class="fw-bold tracking-tight font-sans fs-3 me-0" style="color: #003580 !important;">FASTNET</span>
										<span class="fw-bold tracking-tight font-sans fs-3 me-0" style="color: #d32f2f !important;">STAYS</span>
										<span class="fw-bold text-muted font-sans fs-6" style="font-size: 12px;">.com</span>
									</div>
									<div class="d-flex align-items-center gap-1 mt-1">
										<span class="rounded-circle bg-danger d-inline-block" style="width: 7px; height: 7px;"></span>
										<span class="rounded-circle d-inline-block" style="width: 7px; height: 7px; background-color: #f97316;"></span>
										<span class="rounded-circle d-inline-block" style="width: 7px; height: 7px; background-color: #eab308;"></span>
										<span class="rounded-circle bg-success d-inline-block" style="width: 7px; height: 7px;"></span>
										<span class="rounded-circle bg-primary d-inline-block" style="width: 7px; height: 7px;"></span>
									</div>
								</a>
								<!-- Title -->
								<h1 class="mb-2 fs-2">Create New Account</h1>
								<p class="mb-0">Already a Member?<a href="<?= $this->Url->build('/login'); ?>" class="fw-medium text-primary"> Signin</a></p>

								<!-- Form START -->
								<form id="web1-signup-form" class="mt-4 text-start">
									<div id="signup-alert-box"></div>
									<div class="form py-2">
										<div class="form-group">
											<label class="form-label">Full Name</label>
											<input type="text" id="signup-name" class="form-control" placeholder="John Doe" required>
										</div>
										<div class="form-group">
											<label class="form-label">Email Address</label>
											<input type="email" id="signup-email" class="form-control" placeholder="name@example.com" required>
										</div>
										<div class="form-group">
											<label class="form-label">Phone Number (Optional)</label>
											<input type="text" id="signup-phone" class="form-control" placeholder="+255 700 000 000">
										</div>
										<div class="form-group">
											<label class="form-label">Enter Password</label>
											<div class="position-relative">
												<input type="password" class="form-control" id="signup-password" name="password" placeholder="Min 8 characters" required>
												<span class="fa-solid fa-eye toggle-password position-absolute top-50 end-0 translate-middle-y me-3" onclick="let p=document.getElementById('signup-password'); p.type = p.type==='password'?'text':'password';"></span>
											</div>
										</div>

										<div class="form-group">
											<button type="submit" id="signup-submit-btn" class="btn btn-primary full-width font--bold btn-lg">Create An Account</button>
										</div>
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
										submitBtn.disabled = true;
										submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating Account...';

										const payload = {
											name: document.getElementById("signup-name").value.trim(),
											email: document.getElementById("signup-email").value.trim(),
											phone_number: document.getElementById("signup-phone").value.trim(),
											password: document.getElementById("signup-password").value,
											role: 'customer'
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

												alertBox.innerHTML = '<div class="alert alert-success">Registration successful! Updating header...</div>';
												setTimeout(() => {
													window.location.href = '<?= $this->Url->build('/'); ?>';
												}, 400);
											} else {
												const errMsg = data.message || (data.errors ? Object.values(data.errors).flat().join('<br>') : 'Registration failed.');
												alertBox.innerHTML = '<div class="alert alert-danger">' + errMsg + '</div>';
											}
										} catch(err) {
											console.error("Signup error:", err);
											alertBox.innerHTML = '<div class="alert alert-danger">Unable to connect to registration server.</div>';
										} finally {
											submitBtn.disabled = false;
											submitBtn.innerHTML = 'Create An Account';
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
<?= $this->element('footer', ['skin' => 'skin-light-footer']) ?>