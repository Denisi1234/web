								<script>
								window.FastAuthConfig = {
									syncUrl: '<?= $this->Url->build('/login'); ?>',
									home: '<?= $this->Url->build('/'); ?>',
									hostDashboard: '<?= $this->Url->build('/host/dashboard'); ?>',
									adminDashboard: '<?= $this->Url->build('/admin/dashboard'); ?>',
									joinUs: '<?= $this->Url->build('/join-us'); ?>'
								};

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
									const passwordForm = document.getElementById("web1-login-form");
									const otpForm = document.getElementById("web1-otp-form");
									const loginAlert = document.getElementById("login-alert-box");
									const otpAlert = document.getElementById("otp-alert-box");

									function swapMode(mode) {
										document.querySelectorAll("[data-login-mode]").forEach(function(btn) {
											btn.setAttribute("aria-selected", btn.getAttribute("data-login-mode") === mode ? "true" : "false");
										});
										const onCode = mode === "code";
										passwordForm.hidden = onCode;
										otpForm.hidden = !onCode;
										if (!onCode) { otpAlert.innerHTML = ''; }
									}

									document.querySelectorAll("[data-login-mode]").forEach(function(btn) {
										btn.addEventListener("click", function() { swapMode(btn.getAttribute("data-login-mode")); });
									});

								// The PHP session is what the portal guards read. If it cannot be
								// seeded, say so instead of redirecting into a signed-out page.
								function syncFailed(alertBox) {
									alertBox.innerHTML = '<div class="alert alert-danger">We could not finish signing you in on this device. Please check your connection and try again.</div>';
								}

								// Belt-and-braces: make sure no global "Loading…" pill or bar is
								// left stranded by a failed AJAX sign-in (e.g. an older cached
								// app-loader.js that still starts it on submit).
								function hideGlobalLoader() {
									try {
										if (window.FastnetLoader && typeof window.FastnetLoader.hide === 'function') {
											window.FastnetLoader.hide();
										}
									} catch (e) {}
								}

									// ── Password sign-in ──
									if (passwordForm) {
										const submitBtn = document.getElementById("login-submit-btn");
										passwordForm.addEventListener("submit", async function(e) {
											e.preventDefault();
											loginAlert.innerHTML = '';
											submitBtn.disabled = true;
											submitBtn.innerHTML = '<span class="p-dots" aria-hidden="true"><span class="p-dot"></span><span class="p-dot"></span><span class="p-dot"></span></span>Logging in...';

											try {
												const res = await window.FastAuth.postJson('/api/login', {
													email: document.getElementById("login-email").value.trim(),
													password: document.getElementById("login-password").value
												});
												const data = res.data || {};

												if (res.ok && (data.access_token || data.token)) {
													loginAlert.innerHTML = '<div class="alert alert-success">Login successful! Taking you there...</div>';
													submitBtn.disabled = true;
													await window.FastAuth.finish(data, function() {
														syncFailed(loginAlert);
														submitBtn.disabled = false;
														submitBtn.innerHTML = 'Log In';
													});
											} else {
												loginAlert.innerHTML = '<div class="alert alert-danger">' + (data.message || 'Invalid email or password.') + '</div>';
												hideGlobalLoader();
												submitBtn.disabled = false;
												submitBtn.innerHTML = 'Log In';
											}
										} catch(err) {
											console.error("Login error:", err);
											loginAlert.innerHTML = '<div class="alert alert-danger">Unable to connect to authentication server.</div>';
											hideGlobalLoader();
											submitBtn.disabled = false;
											submitBtn.innerHTML = 'Log In';
										} finally {
											// Safety net: never leave the button stuck on the spinner
											// while it is still clickable (success path keeps it
											// disabled on purpose until the redirect happens).
											if (!submitBtn.disabled && submitBtn.innerHTML !== 'Log In') { submitBtn.innerHTML = 'Log In'; }
										}
										});
									}

									// ── Passwordless sign-in ──
									if (!otpForm) return;

									const stepContact = document.getElementById("otp-step-contact");
									const stepCode = document.getElementById("otp-step-code");
									const contactInput = document.getElementById("otp-contact");
									const codeInput = document.getElementById("otp-code-input");
									const targetLabel = document.getElementById("otp-target");
									const sendBtn = document.getElementById("otp-send-btn");
									const verifyBtn = document.getElementById("otp-verify-btn");
									const resendBtn = document.getElementById("otp-resend-btn");
									const backBtn = document.getElementById("otp-back-btn");

									let contact = '';
									let resendTimer = null;

									function startResendCountdown(seconds) {
										let left = seconds;
										resendBtn.disabled = true;
										resendBtn.textContent = 'Resend code in ' + left + 's';
										if (resendTimer) { clearInterval(resendTimer); }
										resendTimer = setInterval(function() {
											left -= 1;
											if (left <= 0) {
												clearInterval(resendTimer);
												resendTimer = null;
												resendBtn.disabled = false;
												resendBtn.textContent = 'Resend code';
											} else {
												resendBtn.textContent = 'Resend code in ' + left + 's';
											}
										}, 1000);
									}

									function showCodeStep(channel, maskedContact) {
										stepContact.hidden = true;
										stepCode.hidden = false;
										targetLabel.textContent = maskedContact;
										otpAlert.innerHTML = '<div class="alert alert-success">We sent a 6-digit code to ' + maskedContact + '.</div>';
										codeInput.value = '';
										codeInput.focus();
										startResendCountdown(45);
									}

									function backToContact() {
										stepCode.hidden = true;
										stepContact.hidden = false;
										otpAlert.innerHTML = '';
										if (resendTimer) { clearInterval(resendTimer); resendTimer = null; }
										resendBtn.disabled = true;
										resendBtn.textContent = 'Resend code';
										contactInput.focus();
									}

									function mask(contact, channel) {
										if (channel === 'sms') {
											const tail = contact.replace(/\D/g, '').slice(-4);
											return tail ? '••••••' + tail : 'your phone';
										}
										const at = contact.indexOf('@');
										if (at < 1) return 'your email';
										return contact.charAt(0) + '•••' + contact.slice(at);
									}

									async function sendCode() {
										otpAlert.innerHTML = '';
										sendBtn.disabled = true;
										sendBtn.innerHTML = '<span class="p-dots" aria-hidden="true"><span class="p-dot"></span><span class="p-dot"></span><span class="p-dot"></span></span>Sending...';
										try {
											const res = await window.FastAuth.postJson('/api/login/otp/request', { contact: contact });
											const data = res.data || {};
											if (res.ok && data.success) {
												contact = contactInput.value.trim();
												showCodeStep(data.channel || 'email', mask(contact, data.channel || 'email'));
										} else {
											otpAlert.innerHTML = '<div class="alert alert-danger">' + (data.message || 'We could not send a code. Please try again.') + '</div>';
											hideGlobalLoader();
										}
									} catch(err) {
										otpAlert.innerHTML = '<div class="alert alert-danger">Unable to reach the authentication server.</div>';
										hideGlobalLoader();
									} finally {
											sendBtn.disabled = false;
											sendBtn.innerHTML = 'Send code';
										}
									}

									otpForm.addEventListener("submit", async function(e) {
										e.preventDefault();
										const code = codeInput.value.replace(/\D/g, '');

										if (!stepContact.hidden) {
											contact = contactInput.value.trim();
											if (!contact) return;
											await sendCode();
											return;
										}

										if (code.length !== 6) {
											otpAlert.innerHTML = '<div class="alert alert-danger">Enter all 6 digits of the code.</div>';
											return;
										}

										otpAlert.innerHTML = '';
										verifyBtn.disabled = true;
										verifyBtn.innerHTML = '<span class="p-dots" aria-hidden="true"><span class="p-dot"></span><span class="p-dot"></span><span class="p-dot"></span></span>Verifying...';

										try {
											const res = await window.FastAuth.postJson('/api/login/otp/verify', { contact: contact, code: code });
											const data = res.data || {};

											if (res.ok && data.success && (data.access_token || data.token)) {
												otpAlert.innerHTML = '<div class="alert alert-success">Signed in! Taking you there...</div>';
												await window.FastAuth.finish(data, function() {
													otpAlert.innerHTML = '<div class="alert alert-danger">We could not finish signing you in on this device. Please check your connection and try again.</div>';
													verifyBtn.disabled = false;
													verifyBtn.innerHTML = 'Verify &amp; sign in';
												});
										} else {
											otpAlert.innerHTML = '<div class="alert alert-danger">' + (data.message || 'That code is not correct. Please try again.') + '</div>';
											hideGlobalLoader();
											verifyBtn.disabled = false;
											verifyBtn.innerHTML = 'Verify &amp; sign in';
											codeInput.value = '';
											codeInput.focus();
										}
									} catch(err) {
										otpAlert.innerHTML = '<div class="alert alert-danger">Unable to reach the authentication server.</div>';
										hideGlobalLoader();
										verifyBtn.disabled = false;
										verifyBtn.innerHTML = 'Verify &amp; sign in';
									}
									});

									resendBtn.addEventListener("click", async function() {
										if (resendBtn.disabled) return;
										resendBtn.disabled = true;
										await sendCode();
										if (stepContact.hidden) { startResendCountdown(45); }
									});

									backBtn.addEventListener("click", backToContact);

									codeInput.addEventListener("input", function() {
										codeInput.value = codeInput.value.replace(/\D/g, '').slice(0, 6);
									});
								});
								</script>
