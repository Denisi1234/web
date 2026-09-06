<!-- Call To Action Start -->
<div class="position-relative bg-cover bg-primary" style="background:url(<?= $this->Url->build('/assets/img/bg.jpg'); ?>)no-repeat;" data-overlay="5">
    <div class="container">
        <div class="row align-items-center justify-content-between">
            <div class="col-xl-12 col-lg-12 col-md-12">
                <div class="calltoAction-wraps position-relative py-5 px-4">
                    <div class="ht-40"></div>
                    <div class="row align-items-center justify-content-center">
                        <div class="col-xl-8 col-lg-9 col-md-10 col-sm-11 text-center">

                            <div class="calltoAction-title mb-5">
                                <h4 class="text-light fs-2 fw-bold lh-base m-0">Subscribe & Get<br>Special Discount with FastNetStays.com
                                </h4>
                            </div>
                            <div class="newsletter-forms mt-md-0 mt-4">
                                <div id="newsletter-alert-box"></div>
                                <form id="newsletter-subscription-form">
                                    <div class="row align-items-center justify-content-between bg-white rounded-3 p-2 gx-0">
                                        <div class="col-xl-9 col-lg-8 col-md-8">
                                            <div class="form-group m-0">
                                                <input type="email" id="newsletter-email-input" class="form-control bold ps-1 border-0" placeholder="Enter Your Email Address..." required>
                                            </div>
                                        </div>
                                        <div class="col-xl-3 col-lg-4 col-md-4">
                                            <div class="form-group m-0">
                                                <button type="submit" id="newsletter-submit-btn" class="btn btn-primary fw-medium full-width">Subscribe<i class="fa-solid fa-paper-plane ms-2"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <script>
                            document.addEventListener("DOMContentLoaded", function() {
                                const nForm = document.getElementById("newsletter-subscription-form");
                                const nAlert = document.getElementById("newsletter-alert-box");
                                const nBtn = document.getElementById("newsletter-submit-btn");

                                if (nForm) {
                                    nForm.addEventListener("submit", async function(e) {
                                        e.preventDefault();
                                        nAlert.innerHTML = '';
                                        nBtn.disabled = true;

                                        const emailVal = document.getElementById("newsletter-email-input").value.trim();
                                        const endpoint = (typeof window.API_URL === 'function') 
                                            ? window.API_URL('/api/subscribe') 
                                            : 'http://127.0.0.1:8000/api/subscribe';

                                        try {
                                            const res = await fetch(endpoint, {
                                                method: 'POST',
                                                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                                                body: JSON.stringify({ email: emailVal, type: 'general' })
                                            });
                                            const data = await res.json();

                                            if (res.ok || res.status === 201) {
                                                nAlert.innerHTML = '<div class="alert alert-success py-2 mb-3">' + (data.message || 'Subscribed successfully! Thank you for joining.') + '</div>';
                                                nForm.reset();
                                            } else {
                                                const errText = data.message || (data.errors ? Object.values(data.errors).flat().join(', ') : 'Subscription failed.');
                                                nAlert.innerHTML = '<div class="alert alert-warning py-2 mb-3">' + errText + '</div>';
                                            }
                                        } catch(err) {
                                            nAlert.innerHTML = '<div class="alert alert-danger py-2 mb-3">Unable to submit subscription right now.</div>';
                                        } finally {
                                            nBtn.disabled = false;
                                        }
                                    });
                                }
                            });
                            </script>

                        </div>
                    </div>
                    <div class="ht-40"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Call To Action Start -->