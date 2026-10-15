/* Owner KYC submission -> POST /host/verification/submit (HostController::submitVerification)
 *
 * Delegated listeners (no DOMContentLoaded binding): portal sidebar
 * navigation swaps #main-content via PJAX without refiring load events,
 * so direct bindings would be lost and the submit would fall through to
 * a native POST that just re-renders the page. Delegation survives swaps.
 */
(function () {
  'use strict';

  function fail(form, msg) {
    var box = form.querySelector('#kycError');
    var btn = form.querySelector('#kycSubmitBtn');
    if (box) {
      box.textContent = msg;
      box.classList.remove('d-none');
    }
    if (btn) {
      btn.disabled = false;
      btn.textContent = 'Submit for review';
    }
  }

  // Returns an error message or '' when the file input is acceptable.
  // required=false allows an empty optional input.
  function checkFile(input, required, label) {
    if (!input || !input.files || input.files.length === 0) {
      return required ? ('Please choose your ' + label + ' (JPG, PNG, WebP or PDF).') : '';
    }
    var f = input.files[0];
    var okTypes = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
    var name = (f.name || '').toLowerCase();
    var okExt = /\.(jpe?g|png|webp|pdf)$/.test(name);
    if (okTypes.indexOf(f.type) === -1 && !okExt) {
      return label + ' must be a JPG, PNG, WebP or PDF file. iPhone HEIC photos are not accepted — please retake or convert it first.';
    }
    if (f.size > 10 * 1024 * 1024) {
      return label + ' is too large. Please choose a file under 10 MB.';
    }
    return '';
  }

  // KYC submit (delegation — works on first load and after PJAX swaps).
  document.addEventListener('submit', function (e) {
    var form = e.target;
    if (!form || form.id !== 'kycForm') return;
    e.preventDefault();

    var errBox = form.querySelector('#kycError');
    var btn = form.querySelector('#kycSubmitBtn');
    if (errBox) {
      errBox.classList.add('d-none');
      errBox.textContent = '';
    }

    // Validate before uploading: the ID photo is required and phone photos
    // must be real images under 10 MB (HEIC and huge files are rejected).
    var name = form.querySelector('#kyc_full_name');
    var phone = form.querySelector('#kyc_phone');
    var idNum = form.querySelector('#kyc_id_number');
    var idDoc = form.querySelector('#kyc_id_doc');
    var bizDoc = form.querySelector('#kyc_bdoc');
    if (!name || name.value.trim() === '') return fail(form, 'Please enter your full legal name.');
    if (!phone || phone.value.trim().length < 9) return fail(form, 'Please enter your mobile money number.');
    if (!idNum || idNum.value.trim() === '') return fail(form, 'Please enter your National ID number.');
    var fileErr = checkFile(idDoc, true, 'National ID photo');
    if (fileErr) return fail(form, fileErr);
    fileErr = checkFile(bizDoc, false, 'Business licence photo');
    if (fileErr) return fail(form, fileErr);

    if (btn) {
      btn.disabled = true;
      btn.textContent = 'Uploading documents...';
    }

    fetch('/host/verification/submit', {
      method: 'POST',
      body: new FormData(form),
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin'
    })
      .then(function (r) { return r.json().catch(function () { return {}; }); })
      .then(function (data) {
        if (data && data.status === 'success') {
          if (btn) btn.textContent = 'Submitted';
          window.location.reload();
          return;
        }
        fail(form, (data && data.message) || 'Submission failed. Please try again.');
      })
      .catch(function () { fail(form, 'Could not reach the server. Please try again.'); });
  });
})();
