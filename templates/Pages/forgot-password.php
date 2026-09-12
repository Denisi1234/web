<?php
$this->assign('title', 'Forgot password | FastNet Stays');
$this->assign('description', 'Reset your FastNet Stays password securely.');
?>
<?= $this->Html->css('/assets/css/google-travel-layout.css') ?>
<?= $this->Html->css('/assets/css/google-travel-home.css') ?>
<?= $this->element('navbar') ?>
<nav aria-label="Breadcrumb" class="container-fluid px-2 px-lg-2" style="max-width:100%;margin:0 auto;background:#f8f9fa;">
  <ol class="breadcrumb mb-0 py-1" style="background:transparent;font-size:12px;line-height:1.2;--bs-breadcrumb-divider:'›';" itemscope itemtype="https://schema.org/BreadcrumbList">
    <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a href="/" itemprop="item" style="color:#5f6368;text-decoration:none;"><span itemprop="name">Home</span></a><meta itemprop="position" content="1"></li>
    <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a href="/login" itemprop="item" style="color:#5f6368;text-decoration:none;"><span itemprop="name">Sign in</span></a><meta itemprop="position" content="2"></li>
    <li class="breadcrumb-item active" aria-current="page" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><span itemprop="name">Forgot password</span><meta itemprop="position" content="3"></li>
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
																<a href="<?= $this->Url->build('/'); ?>" class="d-inline-flex align-items-center text-decoration-none mb-4" title="fastnetstays.com" style="text-decoration:none!important;">
									<link href="https://fonts.googleapis.com/css2?family=Grand+Hotel&display=swap" rel="stylesheet">
									<span style="font-family:'Grand Hotel','Brush Script MT',cursive;font-size:34px;font-weight:400;letter-spacing:-.02em;line-height:1;background:linear-gradient(45deg,#feda75 0%,#fa7e1e 18%,#d62976 38%,#962fbf 68%,#4f5bd5 100%);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;filter:drop-shadow(0 1px 0 rgba(0,0,0,.04));white-space:nowrap;">FastNetStays</span>
									<span style="font-family:'Grand Hotel',cursive;font-size:13px;font-weight:400;color:#9ca3af;letter-spacing:.02em;margin-left:1px;align-self:flex-end;margin-bottom:4px;opacity:.9;">.com</span>
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
</main>
<?= $this->element('footer', ['skin' => 'skin-light-footer']) ?>