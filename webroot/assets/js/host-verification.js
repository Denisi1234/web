/* Owner KYC submission -> POST /host/verification/submit (HostController::submitVerification) */
(function () {
  var form   = document.getElementById('kycForm');
  var btn    = document.getElementById('kycSubmitBtn');
  var errBox = document.getElementById('kycError');
  if (!form || !btn) return;

  var fail = function (msg) {
    if (errBox) { errBox.textContent = msg; errBox.classList.remove('d-none'); }
    btn.disabled = false;
    btn.textContent = 'Submit for review';
  };

  // Returns an error message or '' when the file input is acceptable.
  // required=false allows an empty optional input.
  var checkFile = function (input, required, label) {
    if (!input || !input.files || input.files.length === 0) {
      return required ? ('Please choose your ' + label + ' (JPG, PNG or WebP).') : '';
    }
    var f = input.files[0];
    var okTypes = ['image/jpeg', 'image/png', 'image/webp'];
    var name = (f.name || '').toLowerCase();
    var okExt = /\.(jpe?g|png|webp)$/.test(name);
    if (okTypes.indexOf(f.type) === -1 && !okExt) {
      return label + ' must be a JPG, PNG or WebP photo. iPhone HEIC photos are not accepted — please retake or convert it first.';
    }
    if (f.size > 10 * 1024 * 1024) {
      return label + ' is too large. Please choose a file under 10 MB.';
    }
    return '';
  };

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    if (errBox) { errBox.classList.add('d-none'); errBox.textContent = ''; }

    // Validate before uploading: the ID photo is required and phone photos
    // must be real images under 10 MB (HEIC and huge files are rejected).
    var name = document.getElementById('kyc_full_name');
    var phone = document.getElementById('kyc_phone');
    var idNum = document.getElementById('kyc_id_number');
    var idDoc = document.getElementById('kyc_id_doc');
    var bizDoc = document.getElementById('kyc_bdoc');
    if (!name || name.value.trim() === '') return fail('Please enter your full legal name.');
    if (!phone || phone.value.trim().length < 9) return fail('Please enter your mobile money number.');
    if (!idNum || idNum.value.trim() === '') return fail('Please enter your National ID number.');
    var fileErr = checkFile(idDoc, true, 'National ID photo');
    if (fileErr) return fail(fileErr);
    fileErr = checkFile(bizDoc, false, 'Business licence photo');
    if (fileErr) return fail(fileErr);

    btn.disabled = true;
    btn.textContent = 'Uploading documents...';

    fetch('/host/verification/submit', {
      method: 'POST',
      body: new FormData(form),
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin'
    })
      .then(function (r) { return r.json().catch(function () { return {}; }); })
      .then(function (data) {
        if (data && data.status === 'success') {
          btn.textContent = 'Submitted';
          window.location.reload();
          return;
        }
        fail((data && data.message) || 'Submission failed. Please try again.');
      })
      .catch(function () { fail('Could not reach the server. Please try again.'); });
  });
})();
