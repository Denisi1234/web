<!-- Add Mobile Payment Modal -->
<div class="modal fade" id="addcard" tabindex="-1" role="dialog" aria-labelledby="addcardmodal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered addcard-pop-form" role="document">
        <div class="modal-content" id="addcardmodal">
            <div class="modal-header">
                <h4 class="modal-title fs-6">Add Mobile Payment</h4>
                <a href="#" class="text-muted fs-4" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-square-xmark"></i></a>
            </div>
            <div class="modal-body">
                <div class="modal-addcard-form pb-4 pt-0">
                    <form class="row align-items-start g-3">

                        <div class="col-xl-12 col-lg-12 col-md-12">
                            <div class="form-group">
                                <label class="form-label">Payment Network (Tanzania)</label>
                                <select class="form-control">
                                    <option value="vodacom" selected>Vodacom M-Pesa (074, 075, 076)</option>
                                    <option value="tigo">Tigo Pesa (065, 067, 071)</option>
                                    <option value="airtel">Airtel Money (068, 069, 078)</option>
                                    <option value="halotel">HaloPesa (Halotel) (061, 062)</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-xl-12 col-lg-12 col-md-12">
                            <div class="form-group">
                                <label class="form-label">Mobile Money Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 fw-bold">🇹🇿 +255</span>
                                    <input type="text" class="form-control" placeholder="0712 345 678" required>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-12 col-lg-12 col-md-12">
                            <div class="form-group">
                                <label class="form-label">Registered Name on SIM</label>
                                <input type="text" class="form-control" placeholder="Full name registered on SIM" required>
                            </div>
                        </div>

                        <div class="col-xl-12 col-lg-12 col-md-12">
                            <button type="button" class="btn btn-md btn-primary full-width fw-medium">Save Mobile Payment</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End Modal -->