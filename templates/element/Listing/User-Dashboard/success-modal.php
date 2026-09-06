<!-- Success Modal -->
<div class="modal fade" id="successmodal" tabindex="-1" role="dialog" aria-labelledby="successmodalmodal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" id="successmodalmodal">
            <span class="mod-close" data-bs-dismiss="modal" aria-hidden="true"><i class="fas fa-close"></i></span>
            <div class="modal-body py-5 px-md-5 px-4">
                <div class="verify-email-wrap mb-5">
                    <div class="icon-wrap text-center mb-4">
                        <img src="<?= $this->Url->build('/assets/img/done.png'); ?>" class="img-fluid mx-auto" width="110" alt="Image">
                    </div>
                    
                    <div class="message-wrap text-center d-block mb-4">
                        <h4 class="mb-1">Mobile Number Verified Successfully</h4>
                        <p class="fw-normal">Now go to your profile and search & apply jobs.</p>
                    </div>
                    
                    <div class="button-wrap text-center d-flex flex-column">
                        <a href="<?= $this->Url->build('/'); ?>" class="btn btn-primary px-5">Go To Home</a>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End Modal -->