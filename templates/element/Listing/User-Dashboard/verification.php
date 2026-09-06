<!-- OTP Verification Modal -->
<div class="modal fade" id="otpmodal" tabindex="-1" role="dialog" aria-labelledby="otpmodalmodal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" id="otpmodalmodal">
            <span class="mod-close" data-bs-dismiss="modal" aria-hidden="true"><i class="fas fa-close"></i></span>
            <div class="modal-body py-5 px-md-5 px-4">
                <div class="verify-email-wrap mb-5">
                    <div class="icon-wrap text-center mb-4">
                        <img src="<?= $this->Url->build('/assets/img/mobile-verification.svg'); ?>" class="img-fluid mx-auto" width="140" alt="Image">
                    </div>
                    
                    <div class="message-wrap text-center d-block mb-4">
                        <h4 class="mb-1">OTP Verification</h4>
                        <p class="fw-normal">An 6-digit code has been send to <a href="#" class="text-dark fw-medium">+91 256 584 5236</a>.<a href="#" class="text-main">Change</a></p>
                    </div>
                    
                    <div class="button-wrap text-center d-flex flex-column gap-2">
                        <div class="form-group mb-3 d-flex align-items-center justify-content-center gap-3">
                            <input type="text" class="form-control fs-6 text-center" maxlength="1" oninput="moveToNext(this)" autofocus placeholder="-">
                            <input type="text" class="form-control fs-6 text-center" maxlength="1" oninput="moveToNext(this)" placeholder="-">
                            <input type="text" class="form-control fs-6 text-center" maxlength="1" oninput="moveToNext(this)" placeholder="-">
                            <input type="text" class="form-control fs-6 text-center" maxlength="1" oninput="moveToNext(this)" placeholder="-">
                            <input type="text" class="form-control fs-6 text-center" maxlength="1" oninput="moveToNext(this)" placeholder="-">
                            <input type="text" class="form-control fs-6 text-center" maxlength="1" oninput="moveToNext(this)" placeholder="-">
                        </div>
                        <button type="button" class="btn btn-primary px-5" data-bs-target="#successmodal" data-bs-toggle="modal">Verify & Proceed</button>
                        <p class="fw-normal">Don't recieve the OTP? <a href="#" class="text-main fw-medium text-uppercase">Resend OTP</a></p>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End Modal -->