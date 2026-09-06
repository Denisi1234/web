<!-- Log In Modal -->
<div class="modal fade" id="login" tabindex="-1" role="dialog" aria-labelledby="loginmodal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered login-pop-form" role="document">
        <div class="modal-content" id="loginmodal">
            <div class="modal-header">
                <h4 class="modal-title fs-6 fw-bold">Sign In to FastNet Stays</h4>
                <a href="#" class="text-muted fs-4" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-square-xmark"></i></a>
            </div>
            <div class="modal-body">
                <div class="modal-login-form py-3 px-md-3 px-0">
                    <div id="modal-login-alert"></div>
                    <form id="modal-login-form">
                        <div class="form-floating mb-3">
                            <input type="email" id="modal-login-email" class="form-control" placeholder="name@example.com" required>
                            <label>Email Address</label>
                        </div>
                        <div class="form-floating mb-3 position-relative">
                            <input type="password" id="modal-login-password" class="form-control pe-5" placeholder="Password" required>
                            <label>Password</label>
                            <span class="position-absolute top-50 end-0 translate-middle-y me-3 cursor-pointer text-slate-500 hover:text-slate-800" style="z-index: 10;" onclick="togglePasswordVisibility('modal-login-password', this)">
                                <i class="fa-solid fa-eye fs-6"></i>
                            </span>
                        </div>

                        <div class="form-group mb-3">
                            <button type="submit" id="modal-login-btn" class="btn btn-primary full-width font--bold btn-lg">Log In</button>
                        </div>

                        <div class="modal-flex-item d-flex align-items-center justify-content-between mb-2">
                            <div class="modal-flex-first">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" id="modal-savepassword" checked>
                                    <label class="form-check-label" for="modal-savepassword">Remember me</label>
                                </div>
                            </div>
                            <div class="modal-flex-last">
                                <a href="<?= $this->Url->build('/forgot-password'); ?>" class="text-primary fw-medium text-sm">Forgot Password?</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer align-items-center justify-content-center">
                <p class="mb-0">Don't have an account yet?<a href="<?= $this->Url->build('/signup'); ?>" class="text-primary fw-bold ms-1">Create Account</a></p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const modalForm = document.getElementById("modal-login-form");
    const modalAlert = document.getElementById("modal-login-alert");
    const modalBtn = document.getElementById("modal-login-btn");

    if (modalForm) {
        modalForm.addEventListener("submit", async function(e) {
            e.preventDefault();
            modalAlert.innerHTML = '';
            modalBtn.disabled = true;
            modalBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Signing in...';

            const payload = {
                email: document.getElementById("modal-login-email").value.trim(),
                password: document.getElementById("modal-login-password").value
            };

            try {
                const endpoint = (typeof window.API_URL === 'function')
                    ? window.API_URL('/api/login')
                    : 'http://127.0.0.1:8000/api/login';
                const res = await fetch(endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
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

                    if (typeof syncNavAuthState === 'function') {
                        syncNavAuthState();
                    }

                    modalAlert.innerHTML = '<div class="alert alert-success py-2">Login successful! Updating header...</div>';
                    setTimeout(() => { window.location.reload(); }, 400);
                } else {
                    const errMsg = data.message || 'Invalid email or password.';
                    modalAlert.innerHTML = '<div class="alert alert-danger py-2">' + errMsg + '</div>';
                }
            } catch(err) {
                modalAlert.innerHTML = '<div class="alert alert-danger py-2">Authentication server unavailable.</div>';
            } finally {
                modalBtn.disabled = false;
                modalBtn.innerHTML = 'Log In';
            }
        });
    }
});
</script>
<!-- End Modal -->