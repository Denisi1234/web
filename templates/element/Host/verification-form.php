<?php
/**
 * Owner KYC form — rendered inside the owner portal (/host/verification).
 * Submits via host-verification.js to POST /host/verification.
 */
?>
<form id="kycForm" enctype="multipart/form-data" novalidate>
  <div class="d-grid gap-3 mt-2">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label" for="kyc_full_name">Full legal name</label>
        <input class="form-control" id="kyc_full_name" name="full_name" required maxlength="255" autocomplete="name">
      </div>
      <div class="col-md-6">
        <label class="form-label" for="kyc_phone">Mobile money number</label>
        <input class="form-control" id="kyc_phone" name="phone_number" required maxlength="255" autocomplete="tel" placeholder="0714 000 000">
      </div>
    </div>

    <div>
      <label class="form-label" for="kyc_id_number">National ID number</label>
      <input class="form-control" id="kyc_id_number" name="id_number" required maxlength="255">
    </div>

    <div>
      <label class="form-label" for="kyc_id_doc">Photo of your National ID</label>
      <input class="form-control" type="file" id="kyc_id_doc" name="id_document" accept="image/jpeg,image/png,image/webp,application/pdf,.jpg,.jpeg,.png,.webp,.pdf" required>
      <div class="form-text">JPG, PNG, WebP or PDF. Max 10 MB.</div>
    </div>

    <fieldset style="border:1px solid #e8eaed;border-radius:8px;padding:14px">
      <legend style="font-size:13px;font-weight:600;padding:0 6px">Business registration <span class="text-muted" style="font-weight:400">(if registered)</span></legend>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label" for="kyc_brn">Registration number</label>
          <input class="form-control" id="kyc_brn" name="business_registration_number" maxlength="255">
        </div>
        <div class="col-md-6">
          <label class="form-label" for="kyc_bdoc">Business licence photo</label>
          <input class="form-control" type="file" id="kyc_bdoc" name="business_document" accept="image/jpeg,image/png,image/webp,application/pdf,.jpg,.jpeg,.png,.webp,.pdf">
        </div>
      </div>
    </fieldset>

    <fieldset style="border:1px solid #e8eaed;border-radius:8px;padding:14px">
      <legend style="font-size:13px;font-weight:600;padding:0 6px">Payout account <span class="text-muted" style="font-weight:400">(optional now, needed to withdraw)</span></legend>
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label" for="kyc_bank">Bank name</label>
          <input class="form-control" id="kyc_bank" name="payout_bank_name" maxlength="255">
        </div>
        <div class="col-md-4">
          <label class="form-label" for="kyc_acct">Account number</label>
          <input class="form-control" id="kyc_acct" name="payout_account_number" maxlength="255">
        </div>
        <div class="col-md-4">
          <label class="form-label" for="kyc_acctname">Account holder</label>
          <input class="form-control" id="kyc_acctname" name="payout_account_name" maxlength="255">
        </div>
      </div>
    </fieldset>

    <div id="kycError" class="d-none" style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;padding:10px 12px;border-radius:8px;font-size:13px"></div>
  </div>
  <button type="submit" class="p-btn mt-3" id="kycSubmitBtn">Submit for review</button>
</form>
