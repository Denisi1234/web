<?php
$this->assign('title', 'Create new password | FastNet Stays');
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
    <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a href="/login" itemprop="item" style="color:#5f6368;text-decoration:none;"><span itemprop="name">Sign in</span></a><meta itemprop="position" content="2"></li>
    <li class="breadcrumb-item active" aria-current="page" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><span itemprop="name">New password</span><meta itemprop="position" content="3"></li>
  </ol>
</nav>
<main id="main-content" style="background:#f8f9fa;min-height:85vh;" role="main">
<!-- Reset Password Section -->
<section class="py-5">
	<div class="container">

		<div class="row justify-content-center align-items-center m-auto">
			<div class="col-xl-11 col-lg-12 col-12">
				<div class="bg-mode card shadow-sm rounded-3 overflow-hidden">
					<div class="row g-0">
						<!-- Vector Image -->
						<div class="col-lg-6 d-flex align-items-center order-2 order-lg-1">
							<div class="p-3 p-lg-5 text-center w-100">
								<img src="<?= $this->Url->build('/assets/img/login.svg'); ?>" class="img-fluid max-h-400" alt="Reset Password Illustration">
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

								<h1 class="mb-2 fs-2">Create New Password</h1>
								<p class="mb-0 text-muted">Enter a strong new password for your account.</p>

								<!-- General Alert Box -->
								<div id="reset-alert-box" class="mt-3"></div>

								<!-- NEW PASSWORD FORM START -->
								<form id="standalone-reset-password-form" class="mt-4 text-start">
									<div class="form py-2">
										<!-- Email (if not prefilled, editable) -->
										<div class="form-floating mb-3">
											<input type="email" id="reset-email" class="form-control" placeholder="name@example.com" required>
											<label for="reset-email">Email Address</label>
										</div>

										<!-- Verification Code / Token -->
										<div class="form-floating mb-3">
											<input type="text" id="reset-token" class="form-control font-mono" placeholder="123456" maxlength="6" required>
											<label for="reset-token">6-Digit OTP Code</label>
										</div>

										<!-- New Password Input -->
										<div class="form-floating mb-3 position-relative">
											<input type="password" id="reset-new-password" class="form-control pe-5" placeholder="New Password" required minlength="8" oninput="evalStandalonePasswordStrength()">
											<label for="reset-new-password">New Password (min 8 chars)</label>
											<span class="position-absolute top-50 end-0 translate-middle-y me-3 cursor-pointer text-muted" onclick="toggleStandalonePasswordVis('reset-new-password', 'reset-eye-icon-1')">
												<i id="reset-eye-icon-1" class="fa-solid fa-eye fs-6"></i>
											</span>
										</div>

										<!-- Password Strength Meter -->
										<div class="mb-3">
											<div class="d-flex justify-content-between align-items-center mb-1">
												<small class="text-muted" style="font-size: 11px;">Password Strength:</small>
												<small id="reset-strength-text" class="fw-bold text-muted" style="font-size: 11px;">-</small>
											</div>
											<div class="progress" style="height: 5px;">
												<div id="reset-strength-bar" class="progress-bar bg-danger" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
											</div>
										</div>

										<!-- Confirm Password Input -->
										<div class="form-floating mb-3 position-relative">
											<input type="password" id="reset-confirm-password" class="form-control pe-5" placeholder="Confirm New Password" required minlength="8" oninput="checkStandalonePasswordsMatch()">
											<label for="reset-confirm-password">Confirm New Password</label>
											<span class="position-absolute top-50 end-0 translate-middle-y me-3 cursor-pointer text-muted" onclick="toggleStandalonePasswordVis('reset-confirm-password', 'reset-eye-icon-2')">
												<i id="reset-eye-icon-2" class="fa-solid fa-eye fs-6"></i>
											</span>
										</div>

										<div id="reset-match-text" class="mb-3 font-semibold" style="font-size: 12px; display: none;"></div>

										<div class="form-group mt-3">
											<button type="submit" id="standalone-reset-btn" class="btn btn-primary full-width font--bold btn-lg">Save New Password & Sign In</button>
										</div>
									</div>
								</form>
								<!-- NEW PASSWORD FORM END -->

								<div class="text-center mt-3">
									<a href="<?= $this->Url->build('/login'); ?>" class="text-muted text-sm text-decoration-none">Back to <span class="text-primary fw-medium">Sign in</span></a>
								</div>

								<!-- Copyright -->
								<div class="text-muted-2 mt-4 text-center"> © <?= date('Y') ?> fastnetstays.com. All rights reserved. </div>
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
<script>
document.addEventListener("DOMContentLoaded", function() {
	const urlParams = new URLSearchParams(window.location.search);
	const targetEmail = urlParams.get('email') || '';
	const urlToken = urlParams.get('token') || '';

	if (targetEmail) {
		const emailInput = document.getElementById("reset-email");
		if (emailInput) emailInput.value = targetEmail;
	}

	if (urlToken) {
		const tokenInput = document.getElementById("reset-token");
		if (tokenInput) tokenInput.value = urlToken;
	}

	const resetForm = document.getElementById("standalone-reset-password-form");
	const alertBox = document.getElementById("reset-alert-box");
	const resetBtn = document.getElementById("standalone-reset-btn");

	if (resetForm) {
		resetForm.addEventListener("submit", async function(e) {
			e.preventDefault();
			alertBox.innerHTML = '';

			const emailVal = document.getElementById("reset-email").value.trim();
			const tokenVal = document.getElementById("reset-token").value.trim();
			const newPass = document.getElementById("reset-new-password").value;
			const confirmPass = document.getElementById("reset-confirm-password").value;

			if (newPass.length < 8) {
				alertBox.innerHTML = '<div class="alert alert-warning">Password must be at least 8 characters long.</div>';
				return;
			}

			if (newPass !== confirmPass) {
				alertBox.innerHTML = '<div class="alert alert-danger">Passwords do not match. Please check again.</div>';
				return;
			}

			resetBtn.disabled = true;
			resetBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving New Password...';

			try {
				const endpoint = (typeof window.API_URL === 'function') 
					? window.API_URL('/api/reset-password') 
					: 'http://127.0.0.1:8000/api/reset-password';

				const res = await fetch(endpoint, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'Accept': 'application/json'
					},
					body: JSON.stringify({
						email: emailVal,
						token: tokenVal,
						password: newPass
					})
				});

				const data = await res.json();

				if (res.ok || res.status === 200 || data.success) {
					alertBox.innerHTML = '<div class="alert alert-success"><i class="fa-solid fa-circle-check me-2"></i>Your password has been reset successfully! Redirecting to sign in...</div>';
					resetForm.reset();
					setTimeout(() => {
						window.location.href = '<?= $this->Url->build('/login'); ?>';
					}, 1500);
				} else {
					const errMsg = data.message || 'Failed to reset password. OTP code may be invalid or expired.';
					alertBox.innerHTML = '<div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation me-2"></i>' + errMsg + '</div>';
					resetBtn.disabled = false;
					resetBtn.innerHTML = 'Save New Password & Sign In';
				}
			} catch(err) {
				console.error("Reset password error:", err);
				alertBox.innerHTML = '<div class="alert alert-danger">Unable to reach reset server. Please try again.</div>';
				resetBtn.disabled = false;
				resetBtn.innerHTML = 'Save New Password & Sign In';
			}
		});
	}
});

function toggleStandalonePasswordVis(inputId, iconId) {
	const input = document.getElementById(inputId);
	const icon = document.getElementById(iconId);
	if (!input) return;
	if (input.type === 'password') {
		input.type = 'text';
		if (icon) icon.className = 'fa-solid fa-eye-slash fs-6';
	} else {
		input.type = 'password';
		if (icon) icon.className = 'fa-solid fa-eye fs-6';
	}
}

function evalStandalonePasswordStrength() {
	const val = document.getElementById("reset-new-password").value;
	const bar = document.getElementById("reset-strength-bar");
	const text = document.getElementById("reset-strength-text");
	if (!bar || !text) return;

	let score = 0;
	if (val.length >= 8) score += 25;
	if (/[A-Z]/.test(val)) score += 25;
	if (/[0-9]/.test(val)) score += 25;
	if (/[^A-Za-z0-9]/.test(val)) score += 25;

	bar.style.width = score + '%';

	if (score <= 25) {
		bar.className = 'progress-bar bg-danger';
		text.textContent = 'Weak';
		text.className = 'fw-bold text-danger';
	} else if (score <= 50) {
		bar.className = 'progress-bar bg-warning';
		text.textContent = 'Medium';
		text.className = 'fw-bold text-warning';
	} else if (score <= 75) {
		bar.className = 'progress-bar bg-info';
		text.textContent = 'Good';
		text.className = 'fw-bold text-info';
	} else {
		bar.className = 'progress-bar bg-success';
		text.textContent = 'Strong';
		text.className = 'fw-bold text-success';
	}

	checkStandalonePasswordsMatch();
}

function checkStandalonePasswordsMatch() {
	const pass = document.getElementById("reset-new-password").value;
	const confirmPass = document.getElementById("reset-confirm-password").value;
	const matchText = document.getElementById("reset-match-text");

	if (!matchText || !confirmPass) {
		if (matchText) matchText.style.display = 'none';
		return;
	}

	matchText.style.display = 'block';
	if (pass === confirmPass) {
		matchText.textContent = '✓ Passwords match';
		matchText.className = 'mb-3 fw-semibold text-success';
	} else {
		matchText.textContent = '✕ Passwords do not match';
		matchText.className = 'mb-3 fw-semibold text-danger';
	}
}
</script>
