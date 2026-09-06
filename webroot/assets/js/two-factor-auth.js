/** fastnetstays.com - OTP & Password Reset Controller */
let verifiedToken = '';
let targetEmail = '';

document.addEventListener("DOMContentLoaded", function() {
	const urlParams = new URLSearchParams(window.location.search);
	targetEmail = urlParams.get('email') || '';
	const urlToken = urlParams.get('token') || '';

	const emailDisplay = document.getElementById("2fa-email-display");
	if (emailDisplay && targetEmail) {
		emailDisplay.textContent = targetEmail;
	}

	// If token is already present in URL, show reset password form directly
	if (urlToken) {
		verifiedToken = urlToken;
		showNewPasswordSection();
	}

	// Step 1: Handle OTP Form Submission
	const otpForm = document.getElementById("web1-2fa-form");
	const alertBox = document.getElementById("auth-alert-box");
	const otpBtn = document.getElementById("2fa-submit-btn");

	if (otpForm) {
		otpForm.addEventListener("submit", async function(e) {
			e.preventDefault();
			alertBox.innerHTML = '';
			otpBtn.disabled = true;
			otpBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Verifying Code...';

			const otpVal = document.getElementById("2fa-otp-input").value.trim();

			try {
				const endpoint = (typeof window.API_URL === 'function') 
					? window.API_URL('/api/verify-otp') 
					: 'http://127.0.0.1:8000/api/verify-otp';

				const res = await fetch(endpoint, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'Accept': 'application/json'
					},
					body: JSON.stringify({ email: targetEmail, token: otpVal, otp: otpVal })
				});

				const data = await res.json();

				if (res.ok || res.status === 200 || data.success) {
					verifiedToken = otpVal;
					alertBox.innerHTML = '<div class="alert alert-success"><i class="fa-solid fa-circle-check me-2"></i>OTP verified! Now please set your new password below.</div>';
					
					setTimeout(() => {
						alertBox.innerHTML = '';
						showNewPasswordSection();
					}, 1200);

				} else {
					const errMsg = data.message || 'Invalid or expired OTP code. Please try again.';
					alertBox.innerHTML = '<div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i>' + errMsg + '</div>';
				}
			} catch(err) {
				console.error("OTP verification error:", err);
				alertBox.innerHTML = '<div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i>Unable to verify OTP code. Please check your connection.</div>';
			} finally {
				otpBtn.disabled = false;
				otpBtn.innerHTML = 'Verify OTP Code';
			}
		});
	}

	// Step 2: Handle Reset Password Form Submission
	const resetForm = document.getElementById("web1-reset-password-form");
	const resetBtn = document.getElementById("reset-submit-btn");

	if (resetForm) {
		resetForm.addEventListener("submit", async function(e) {
			e.preventDefault();
			alertBox.innerHTML = '';

			const newPass = document.getElementById("new-password").value;
			const confirmPass = document.getElementById("confirm-password").value;

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
						email: targetEmail,
						token: verifiedToken,
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
					const errMsg = data.message || 'Failed to reset password. Token may have expired.';
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

function showNewPasswordSection() {
	document.getElementById("otp-header").style.display = "none";
	document.getElementById("web1-2fa-form").style.display = "none";
	document.getElementById("reset-header").style.display = "block";
	document.getElementById("web1-reset-password-form").style.display = "block";
}

function togglePasswordVis(inputId, iconId) {
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

function evalPasswordStrength() {
	const val = document.getElementById("new-password").value;
	const bar = document.getElementById("strength-bar");
	const text = document.getElementById("strength-text");
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

	checkPasswordsMatch();
}

function checkPasswordsMatch() {
	const pass = document.getElementById("new-password").value;
	const confirmPass = document.getElementById("confirm-password").value;
	const matchText = document.getElementById("match-text");

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

async function resendOtpCode() {
	const alertBox = document.getElementById("auth-alert-box");
	if (!targetEmail) {
		if (alertBox) alertBox.innerHTML = '<div class="alert alert-warning py-2">Email address missing. Please restart forgot password process.</div>';
		return;
	}

	try {
		const endpoint = (typeof window.API_URL === 'function') 
			? window.API_URL('/api/resend-code') 
			: 'http://127.0.0.1:8000/api/resend-code';

		const res = await fetch(endpoint, {
			method: 'POST',
			headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
			body: JSON.stringify({ email: targetEmail })
		});
		const data = await res.json();

		if (res.ok || res.status === 200 || data.success) {
			if (alertBox) alertBox.innerHTML = '<div class="alert alert-success py-2"><i class="fa-solid fa-paper-plane me-2"></i>Resent verification code to your email!</div>';
		} else {
			if (alertBox) alertBox.innerHTML = '<div class="alert alert-warning py-2">' + (data.message || 'Failed to resend code.') + '</div>';
		}
	} catch(e) {
		if (alertBox) alertBox.innerHTML = '<div class="alert alert-danger py-2">Unable to resend code.</div>';
	}
}