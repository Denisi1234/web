<?php
$this->assign('title', 'Contact Us | FastNet Stays');
$this->assign('description', 'Contact FastNet Stays support about bookings, payments and hosting in Tanzania.');
$supportEmail = trim((string)($supportEmail ?? ''));
$sessionUser = $userProfile ?? [];
?>
<?= $this->element('navbar') ?>
<main id="main-content" role="main" style="background:var(--cds-gray-10);min-height:85vh">
<style>
.cds-contact-hero{padding:32px 0 8px}
.cds-contact-eyebrow{font-size:12px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:var(--cds-blue-60)}
.cds-contact-h1{font-size:32px;font-weight:300;color:#161616;margin:8px 0 6px;line-height:1.2}
.cds-contact-lede{font-size:14px;color:#525252;margin:0;max-width:620px}
.cds-contact-body{max-width:960px;margin:0 auto;padding:20px 16px 48px}
.cds-contact-grid{display:grid;grid-template-columns:1.4fr 1fr;gap:24px;align-items:start}
.cds-panel{background:#fff;border:1px solid #e0e0e0;padding:24px}
.cds-panel h2{font-size:20px;font-weight:400;color:#161616;margin:0 0 4px}
.cds-panel .sub{font-size:13.5px;color:#525252;margin:0 0 16px}
.cds-field{margin-bottom:16px}
.cds-field label{display:block;font-size:12px;color:#525252;margin-bottom:8px}
#contactForm.cds-form input,#contactForm.cds-form select,#contactForm.cds-form textarea{width:100%!important;background:#f4f4f4!important;border:none!important;border-bottom:1px solid #8d8d8d!important;border-radius:0!important;font-size:14px!important;color:#161616!important}
#contactForm.cds-form input,#contactForm.cds-form select{height:40px!important;padding:0 12px!important}
#contactForm.cds-form textarea{padding:10px 12px!important;min-height:120px;resize:vertical}
#contactForm.cds-form input:focus,#contactForm.cds-form select:focus,#contactForm.cds-form textarea:focus{outline:2px solid var(--cds-focus)!important;outline-offset:-2px!important;border-bottom-color:var(--cds-blue-60)!important}
.cds-btn{display:inline-block;background:var(--cds-blue-60);color:#fff;border:1px solid transparent;padding:12px 24px;font-size:14px;cursor:pointer;border-radius:0;min-height:48px}
.cds-btn:hover{background:#0353e9;color:#fff}
.cds-btn:disabled{opacity:.6;cursor:not-allowed}
.cds-btn-ghost{display:inline-block;background:transparent;color:var(--cds-blue-60);border:1px solid var(--cds-blue-60);padding:12px 24px;font-size:14px;border-radius:0;text-decoration:none!important;min-height:48px}
.cds-btn-ghost:hover{background:var(--cds-blue-60);color:#fff}
.cds-list{background:#fff;border:1px solid #e0e0e0}
.cds-list-row{display:flex;align-items:center;gap:12px;padding:13px 16px;border-top:1px solid #e0e0e0;font-size:14px}
.cds-list-row:first-child{border-top:none}
.cds-list-row .k{width:110px;flex:0 0 110px;font-size:12px;color:#6f6f6f}
.cds-list-row a{color:var(--cds-blue-60);font-weight:600;text-decoration:none}
.cds-list-row a:hover{text-decoration:underline}
.cds-note{display:flex;gap:12px;background:#fff;border:1px solid #e0e0e0;border-left:4px solid var(--cds-blue-60);padding:14px 16px;font-size:14px;color:#161616;margin-bottom:16px}
.cds-note.success{border-left-color:#24a148}
.cds-note.error{border-left-color:#da1e28}
.cds-note a{color:var(--cds-blue-60);font-weight:600}
@media(max-width:992px){.cds-contact-grid{grid-template-columns:1fr}}
</style>

<div class="container" style="max-width:960px">
  <div class="cds-contact-hero">
    <div class="cds-contact-eyebrow">Contact</div>
    <h1 class="cds-contact-h1">Contact us</h1>
    <p class="cds-contact-lede">Questions about a booking, a payment or listing your property? Send a message — we reply by email.</p>
  </div>
</div>

<div class="cds-contact-body">
  <div class="cds-contact-grid">
    <div class="cds-panel">
      <h2>Send us a message</h2>
      <p class="sub">We reply by email with a ticket reference.</p>
      <div id="contactAlert"></div>
      <?php if (!empty($isLoggedIn)): ?>
      <form id="contactForm" class="cds-form" novalidate>
        <div class="cds-field">
          <label for="cName">Your name</label>
          <input id="cName" type="text" autocomplete="name" value="<?= h($sessionUser['name'] ?? trim(($sessionUser['first_name'] ?? '') . ' ' . ($sessionUser['last_name'] ?? ''))) ?>" placeholder="Full name">
        </div>
        <div class="cds-field">
          <label for="cEmail">Email</label>
          <input id="cEmail" type="email" autocomplete="email" value="<?= h($sessionUser['email'] ?? '') ?>" placeholder="you@example.com">
        </div>
        <div class="cds-field">
          <label for="cSubject">Subject</label>
          <select id="cSubject">
            <option>Booking inquiry</option>
            <option>Payment issue</option>
            <option>Cancellation or refund</option>
            <option>Host partnership</option>
            <option>Something else</option>
          </select>
        </div>
        <div class="cds-field">
          <label for="cMsg">Message</label>
          <textarea id="cMsg" placeholder="Booking reference (if any) and how we can help…"></textarea>
        </div>
        <button type="submit" class="cds-btn" id="contactBtn">Send message</button>
      </form>
      <?php else: ?>
      <p style="font-size:14px;color:#525252">Messages need an account so we can reply with your ticket reference.</p>
      <p style="display:flex;gap:12px;flex-wrap:wrap">
        <a class="cds-btn-ghost" href="<?= $this->Url->build('/login', ['?' => ['redirect' => '/contact']]) ?>">Sign in to message us</a>
        <?php if ($supportEmail !== ''): ?>
        <a class="cds-btn" href="mailto:<?= h($supportEmail) ?>?subject=FastNet%20Stays%20inquiry">Email us directly</a>
        <?php endif; ?>
      </p>
      <?php endif; ?>
    </div>
    <div class="cds-list" aria-label="Other ways to reach us">
      <?php if ($supportEmail !== ''): ?>
      <div class="cds-list-row"><span class="k">Email</span><a href="mailto:<?= h($supportEmail) ?>"><?= h($supportEmail) ?></a></div>
      <?php endif; ?>
      <div class="cds-list-row"><span class="k">Help center</span><a href="<?= $this->Url->build('/help-center') ?>">Find answers →</a></div>
      <div class="cds-list-row"><span class="k">My bookings</span><a href="<?= $this->Url->build('/my-booking') ?>">Manage stays →</a></div>
      <div class="cds-list-row"><span class="k">Become a host</span><a href="<?= $this->Url->build('/join-us') ?>">List property →</a></div>
    </div>
  </div>
</div>
</main>
<?= $this->element('footer', ['skin' => 'skin-light-footer']) ?>
<script>
(function(){
  const form = document.getElementById('contactForm');
  if (!form) return;
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const alert = document.getElementById('contactAlert');
    const btn = document.getElementById('contactBtn');
    const name = document.getElementById('cName').value.trim();
    const email = document.getElementById('cEmail').value.trim();
    const subject = document.getElementById('cSubject').value;
    const msg = document.getElementById('cMsg').value.trim();
    const ok = '<div class="cds-note success" role="status"><div>';
    const err = '<div class="cds-note error" role="alert"><div>';
    const end = '</div></div>';
    alert.innerHTML = '';
    if (name.length < 2) { alert.innerHTML = err + 'Please enter your name.' + end; return; }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { alert.innerHTML = err + 'Please enter a valid email.' + end; return; }
    if (msg.length < 10) { alert.innerHTML = err + 'Please write your message in at least 10 characters.' + end; return; }
    let token = '';
    try { token = localStorage.getItem('auth_token') || localStorage.getItem('token') || ''; } catch (e2) {}
    if (!token) {
      alert.innerHTML = err + 'Your session expired. Please <a href="<?= $this->Url->build('/login', ['?' => ['redirect' => '/contact']]) ?>">sign in again</a>.' + end;
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
        body: JSON.stringify({ issue: subject, description: 'From: ' + name + ' (' + email + ')\n\n' + msg, email: email, name: name })
      });
      const data = await res.json().catch(() => ({}));
      if (res.status === 201 || res.ok) {
        alert.innerHTML = ok + 'Message sent!' + (data.id ? ' Reference #' + data.id + '.' : '') + ' We’ll reply by email.' + end;
        form.reset();
      } else if (res.status === 401) {
        alert.innerHTML = err + 'Your session expired. Please sign in again.' + end;
      } else {
        alert.innerHTML = err + String(data.message || 'Could not send your message. Please try again.').replace(/[<>&"]/g, '') + end;
      }
    } catch (e2) {
      alert.innerHTML = err + 'Could not reach support. Please check your connection.' + end;
    } finally {
      btn.disabled = false;
      btn.textContent = 'Send message';
    }
  });
})();
</script>
