<?php
$this->assign('title', 'Password Reset & OTP Verification');
?>

<!-- Password Reset & Verification Section -->
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

								<!-- Dynamic Headers -->
								<div id="otp-header">
									<h1 class="mb-2 fs-2">OTP Verification</h1>
									<p class="mb-0 text-muted">We have sent a 6-digit verification code to <span id="2fa-email-display" class="fw-bold text-primary">your email</span>.</p>
								</div>

								<div id="reset-header" style="display: none;">
									<h1 class="mb-2 fs-2">Create New Password</h1>
									<p class="mb-0 text-muted">Please choose a strong new password for your account.</p>
								</div>

								<!-- General Alert Box -->
								<div id="auth-alert-box" class="mt-3"></div>

								<!-- STEP 1: OTP FORM START -->
								<form id="web1-2fa-form" class="mt-4 text-start">
									<div class="form py-2">
										<div class="form-group mb-3">
											<label class="form-label font-semibold">Enter 6-Digit OTP Code</label>
											<input type="text" id="2fa-otp-input" class="form-control text-center fs-4 font-mono tracking-widest" placeholder="123456" maxlength="6" required>
										</div>

										<div class="modal-flex-item d-flex align-items-center justify-content-between mb-3">
											<div class="modal-flex-first">
												<span class="text-sm text-muted">Didn't get a code?</span>
											</div>
											<div class="modal-flex-last">
												<a href="javascript:void(0);" onclick="resendOtpCode()" class="text-primary fw-medium text-sm text-decoration-underline">Click to Resend</a>
											</div>
										</div>

										<div class="form-group">
											<button type="submit" id="2fa-submit-btn" class="btn btn-primary full-width font--bold btn-lg">Verify OTP Code</button>
										</div>
									</div>
								</form>
								<!-- STEP 1: OTP FORM END -->

								<!-- STEP 2: NEW PASSWORD FORM START -->
								<form id="web1-reset-password-form" class="mt-4 text-start" style="display: none;">
									<div class="form py-2">
										<!-- New Password Input -->
										<div class="form-floating mb-3 position-relative">
											<input type="password" id="new-password" class="form-control pe-5" placeholder="New Password" required minlength="8" oninput="evalPasswordStrength()">
											<label for="new-password">New Password (min 8 chars)</label>
											<span class="position-absolute top-50 end-0 translate-middle-y me-3 cursor-pointer text-muted" onclick="togglePasswordVis('new-password', 'eye-icon-1')">
												<i id="eye-icon-1" class="fa-solid fa-eye fs-6"></i>
											</span>
										</div>

										<!-- Password Strength Meter -->
										<div class="mb-3">
											<div class="d-flex justify-content-between align-items-center mb-1">
												<small class="text-muted" style="font-size: 11px;">Password Strength:</small>
												<small id="strength-text" class="fw-bold text-muted" style="font-size: 11px;">-</small>
											</div>
											<div class="progress" style="height: 5px;">
												<div id="strength-bar" class="progress-bar bg-danger" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
											</div>
										</div>

										<!-- Confirm Password Input -->
										<div class="form-floating mb-3 position-relative">
											<input type="password" id="confirm-password" class="form-control pe-5" placeholder="Confirm New Password" required minlength="8" oninput="checkPasswordsMatch()">
											<label for="confirm-password">Confirm New Password</label>
											<span class="position-absolute top-50 end-0 translate-middle-y me-3 cursor-pointer text-muted" onclick="togglePasswordVis('confirm-password', 'eye-icon-2')">
												<i id="eye-icon-2" class="fa-solid fa-eye fs-6"></i>
											</span>
										</div>

										<div id="match-text" class="mb-3 font-semibold" style="font-size: 12px; display: none;"></div>

										<div class="form-group mt-3">
											<button type="submit" id="reset-submit-btn" class="btn btn-primary full-width font--bold btn-lg">Save New Password & Sign In</button>
										</div>
									</div>
								</form>
								<!-- STEP 2: NEW PASSWORD FORM END -->

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

<?= $this->Html->script('/assets/js/two-factor-auth.js'); ?>