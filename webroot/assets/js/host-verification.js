/* Owner KYC submission -> POST /host/verification (HostController::submitVerification) */
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

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    if (errBox) { errBox.classList.add('d-none'); errBox.textContent = ''; }

    btn.disabled = true;
    btn.textContent = 'Uploading documents...';

    fetch('/host/verification', {
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
