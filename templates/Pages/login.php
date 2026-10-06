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
<?= $this->Html->css('/assets/css/carbon-auth-01.css?v=' . filemtime(WWW_ROOT . 'assets/css/carbon-auth-01.css')) ?>
<?= $this->Html->css('/assets/css/carbon-auth-02.css?v=' . filemtime(WWW_ROOT . 'assets/css/carbon-auth-02.css')) ?>
<?= $this->element('navbar') ?>
<?= $this->element('breadcrumb-schema', ['label' => $isAdminLogin ? 'Admin sign in' : ($isHostLogin ? 'Host sign in' : 'Sign in')]) ?>
<main id="main-content" class="cx-auth cx-li" style="background:var(--cds-gray-10);min-height:85vh;" role="main">
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
								<p class="cx-lede mb-0">Access your properties, bookings and earnings. New to hosting?<a href="<?= \App\Service\HostIntent::signupUrl($this->Url, $this->getRequest(), $this->getRequest()->getSession()->read('signup_intent_role')) ?>" class="fw-medium text-primary"> Create a host account</a></p>
								<?php elseif ($isAdminLogin): ?>
								<p class="cx-lede mb-0">Restricted area — administrators only.</p>
								<?php else: ?>
								<p class="cx-lede mb-0">Are you new here?<a href="<?= \App\Service\HostIntent::signupUrl($this->Url, $this->getRequest(), $this->getRequest()->getSession()->read('signup_intent_role')) ?>" class="fw-medium text-primary"> Create an account</a></p>
								<?php endif; ?>

								<!-- Sign-in method: password or one-time code -->
								<div class="cx-switch mb-4" role="tablist" aria-label="Sign-in method">
									<button type="button" class="cx-switch-btn" role="tab" id="tab-password" aria-selected="true" aria-controls="pane-password" data-login-mode="password">Password</button>
									<button type="button" class="cx-switch-btn" role="tab" id="tab-code" aria-selected="false" aria-controls="pane-code" data-login-mode="code">Use a code</button>
								</div>

								<!-- Form START -->
								<form id="web1-login-form" class="mt-4 text-start" role="tabpanel" aria-labelledby="tab-password">
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
												<a href="<?= $this->Url->build('/forgot-password'); ?>" class="cx-link">Forgot Password?</a>
											</div>
										</div>

										<div class="form-group">
											<button type="submit" id="login-submit-btn" class="cx-btn">Log In</button>
										</div>
									</div>
								</form>

								<!-- Passwordless sign-in: contact, then the 6-digit code -->
								<form id="web1-otp-form" class="mt-4 text-start" role="tabpanel" aria-labelledby="tab-code" hidden>
									<div id="otp-alert-box"></div>

									<div id="otp-step-contact">
										<p class="cx-otp-sent">Sign in without a password. We'll text or email you a 6-digit code.</p>
										<div class="form-group mb-3">
											<label class="form-label" for="otp-contact">Mobile number or email</label>
											<input type="text" id="otp-contact" class="form-control" placeholder="0755 123 456 or you@example.com" required autocomplete="tel" inputmode="text">
										</div>
										<div class="form-group">
											<button type="submit" id="otp-send-btn" class="cx-btn">Send code</button>
										</div>
										<p class="cx-lede mb-0 mt-3" style="font-size:13px">By continuing you agree to our <a href="<?= $this->Url->build('/terms-of-service'); ?>">Terms</a> and <a href="<?= $this->Url->build('/privacy-policy'); ?>">Privacy Policy</a>.</p>
									</div>

									<div id="otp-step-code" hidden>
										<p class="cx-otp-sent">Enter the 6-digit code sent to <strong id="otp-target">your phone</strong>.</p>
										<div class="form-group mb-3">
											<label class="form-label" for="otp-code-input">Verification code</label>
											<input type="text" id="otp-code-input" class="form-control cx-code" placeholder="000000" maxlength="6" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" required>
										</div>
										<div class="form-group">
											<button type="submit" id="otp-verify-btn" class="cx-btn">Verify &amp; sign in</button>
										</div>
										<div class="d-flex align-items-center justify-content-between mt-3">
											<button type="button" class="cx-linkbtn" id="otp-back-btn">Use a different number</button>
											<button type="button" class="cx-linkbtn" id="otp-resend-btn" disabled>Resend code</button>
										</div>
									</div>
								</form>

								<?= $this->Html->script('/assets/js/auth.js') ?>
<?= $this->element('Auth/login-script') ?>

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