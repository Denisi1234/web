<!-- Verify Mobile Modal -->
<div class="modal fade" id="verifyphone" tabindex="-1" role="dialog" aria-labelledby="verifyphonemodal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" id="verifyphonemodal">
            <span class="mod-close" data-bs-dismiss="modal" aria-hidden="true"><i class="fas fa-close"></i></span>
            <div class="modal-body py-5 px-md-5 px-4">
                <div class="verify-email-wrap mb-5">
                    <div class="icon-wrap text-center mb-4">
                        <img src="<?= $this->Url->build('/assets/img/mobile-verification.svg'); ?>" class="img-fluid mx-auto" width="140" alt="Image">
                    </div>
                    
                    <div class="message-wrap text-center d-block mb-4">
                        <h4 class="mb-1">Verify your Mobile Number</h4>
                        <p class="fw-normal">Please enter your mobile number to recieve a verification code.</p>
                    </div>
                    
                    <div class="button-wrap text-center d-flex flex-column gap-4">
                        <div class="form-group position-relative m-0">
                            <input type="tel" class="form-control ps-5 fs-6" placeholder="586 875 9523">
                            <div class="mobile-prefix position-absolute top-50 start-0 translate-middle-y ms-3"><span class="fw-medium">+91</span></div>
                        </div>
                        <button type="button" class="btn btn-primary px-5" data-bs-target="#otpmodal" data-bs-toggle="modal">Continue<i class="bi bi-arrow-right ms-2"></i></button>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End Modal -->