<script>
(function(){
  const input = document.getElementById('helpSearch');
  const items = Array.from(document.querySelectorAll('.cds-acc-item'));
  const chips = Array.from(document.querySelectorAll('.cds-tag'));
  const count = document.getElementById('faqCount');
  const empty = document.getElementById('faqEmpty');
  let cat = 'All';
  function apply(){
    const q = (input.value || '').trim().toLowerCase();
    let shown = 0;
    items.forEach(el => {
      const okCat = cat === 'All' || el.dataset.cat === cat;
      const okQ = q === '' || el.dataset.text.includes(q);
      const show = okCat && okQ;
      el.style.display = show ? '' : 'none';
      if (show) shown++;
      if (!show) { el.classList.remove('open'); el.querySelector('.cds-acc-q').setAttribute('aria-expanded','false'); }
    });
    count.textContent = shown + ' of ' + items.length + ' answers';
    empty.style.display = shown === 0 ? 'block' : 'none';
  }
  input.addEventListener('input', apply);
  chips.forEach(ch => ch.addEventListener('click', () => {
    cat = ch.dataset.cat;
    chips.forEach(c => c.setAttribute('aria-pressed', c === ch ? 'true' : 'false'));
    apply();
  }));
  document.querySelectorAll('.cds-acc-q').forEach(btn => btn.addEventListener('click', () => {
    const item = btn.closest('.cds-acc-item');
    const open = item.classList.toggle('open');
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
  }));
  apply();

  // Real ticket submission — backend POST /api/tickets (auth:sanctum).
  const form = document.getElementById('ticketForm');
  if (form) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const alert = document.getElementById('ticketAlert');
      const btn = document.getElementById('ticketBtn');
      const issue = document.getElementById('ticketIssue').value.trim();
      const desc = document.getElementById('ticketDesc').value.trim();
      const email = document.getElementById('ticketEmail').value.trim();
      const ok = '<div class="cds-note success" role="status"><i class="fa-solid fa-check" aria-hidden="true"></i><div>';
      const err = '<div class="cds-note error" role="alert"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i><div>';
      const end = '</div></div>';
      alert.innerHTML = '';
      if (!issue) { alert.innerHTML = err + 'Please choose what your ticket is about.' + end; return; }
      if (desc.length < 10) { alert.innerHTML = err + 'Please describe the problem in at least 10 characters.' + end; return; }
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { alert.innerHTML = err + 'Please enter a valid reply-to email.' + end; return; }
      const token = (() => { try {
        return localStorage.getItem('auth_token') || localStorage.getItem('token') || '';
      } catch (e2) { return ''; } })();
      if (!token) {
        alert.innerHTML = err + 'Your session expired. Please <a href="<?= $this->Url->build('/login', ['?' => ['redirect' => '/help-center']]) ?>">sign in again</a>.' + end;
        return;
      }
      btn.disabled = true;
      btn.textContent = 'Sending…';
      try {
        const base = (typeof window.API_URL === 'function') ? window.API_URL('').replace(/\/$/, '') : '';
        const url = base ? base + '/tickets' : '/api/tickets';
        const res = await fetch(url, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': 'Bearer ' + token },
          body: JSON.stringify({ issue: issue, description: desc, email: email })
        });
        const data = await res.json().catch(() => ({}));
        if (res.status === 201 || res.ok) {
          const ref = data.id ? ' Reference #' + data.id + '.' : '';
          alert.innerHTML = ok + 'Ticket received!' + ref + ' We’ll reply by email.' + end;
          form.reset();
        } else if (res.status === 401) {
          alert.innerHTML = err + 'Your session expired. Please sign in again.' + end;
        } else if (res.status === 422) {
          const msg = data.message || (data.errors ? Object.values(data.errors).flat().join(' ') : 'Please check the form.');
          alert.innerHTML = err + String(msg).replace(/[<>&"]/g, '') + end;
        } else {
          alert.innerHTML = err + String(data.message || 'Could not send the ticket. Please try again.').replace(/[<>&"]/g, '') + end;
        }
      } catch (e2) {
        alert.innerHTML = err + 'Could not reach support. Please check your connection.' + end;
      } finally {
        btn.disabled = false;
        btn.textContent = 'Send ticket';
      }
    });
  }
})();
</script>
