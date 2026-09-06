<?php
$this->assign('title', 'Forgot-Password Page');
?>

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
								<h1 class="mb-2 fs-2">Forgot Password?</h1>
								<p class="mb-0">Enter the email address associated with an account.</p>

								<!-- Form START -->
								<form id="web1-forgot-password-form" class="mt-4 text-start">
									<div id="forgot-alert-box"></div>
									<div class="form py-2">
										<div class="form-group">
											<label class="form-label">Enter your email ID</label>
											<input type="email" id="forgot-email" class="form-control" placeholder="name@example.com" required>
										</div>

										<div class="form-group text-center mb-3">
											<p class="mb-0">Back to <a href="<?= $this->Url->build('/login'); ?>" class="fw-medium text-primary">Sign in</a></p>
										</div>

										<div class="form-group">
											<button type="submit" id="forgot-submit-btn" class="btn btn-primary full-width font--bold btn-lg">Send Verification OTP</button>
										</div>
									</div>

									<!-- Copyright -->
									<div class="text-muted-2 mt-4 text-center"> © <?= date('Y') ?> fastnetstays.com. All rights reserved. </div>
								</form>
								<!-- Form END -->

								<script>
								document.addEventListener("DOMContentLoaded", function() {
									const form = document.getElementById("web1-forgot-password-form");
									const alertBox = document.getElementById("forgot-alert-box");
									const submitBtn = document.getElementById("forgot-submit-btn");

									if (!form) return;

									form.addEventListener("submit", async function(e) {
										e.preventDefault();
										alertBox.innerHTML = '';
										submitBtn.disabled = true;
										submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending Code...';

										const emailVal = document.getElementById("forgot-email").value.trim();

										try {
											const endpoint = (typeof window.API_URL === 'function') 
												? window.API_URL('/api/send-otp') 
												: 'http://127.0.0.1:8000/api/send-otp';

											const res = await fetch(endpoint, {
												method: 'POST',
												headers: {
													'Content-Type': 'application/json',
													'Accept': 'application/json'
												},
												body: JSON.stringify({ email: emailVal })
											});

											const data = await res.json();

											if (res.ok || res.status === 200) {
												alertBox.innerHTML = '<div class="alert alert-success">Verification code sent! Please check your email inbox and enter code below.</div>';
												setTimeout(() => {
													window.location.href = '<?= $this->Url->build('/two-factor-auth?email='); ?>' + encodeURIComponent(emailVal);
												}, 1500);
											} else {
												const errMsg = data.message || 'Unable to send reset code for this email.';
												alertBox.innerHTML = '<div class="alert alert-danger">' + errMsg + '</div>';
											}
										} catch(err) {
											console.error("Forgot password error:", err);
											alertBox.innerHTML = '<div class="alert alert-danger">Unable to reach password reset server.</div>';
										} finally {
											submitBtn.disabled = false;
											submitBtn.innerHTML = 'Send Verification OTP';
										}
									});
								});
								</script>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

	</div>
</section>
<!-- Login Section End -->