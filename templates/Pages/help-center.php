<?php
$this->assign('title', 'Help Center | FastNet Stays');
$this->assign('description', 'Get help with bookings, mobile-money payments, cancellations and hosting on FastNet Stays Tanzania.');
$helpCentre = is_array($helpCentre ?? null) ? $helpCentre : null;
$topics = $helpCentre['popular_topics'] ?? [];
if (!is_array($topics)) $topics = [];
// Only relative in-app action URLs are honoured — never off-site redirects.
$topics = array_values(array_filter($topics, fn($t) => is_array($t) && !empty($t['title']) && isset($t['action_url']) && str_starts_with((string)$t['action_url'], '/') && !str_starts_with((string)$t['action_url'], '//')));
$topicIcons = ['calendar' => 'fa-regular fa-calendar-check', 'phone' => 'fa-solid fa-phone', 'card' => 'fa-regular fa-credit-card', 'luggage' => 'fa-solid fa-suitcase-rolling'];
$upcoming = $helpCentre['upcoming_stay'] ?? null;
if (!is_array($upcoming) || empty($upcoming)) $upcoming = null;
$support = $helpCentre['support_contact'] ?? [];
$supportPhone = trim((string)($support['phone'] ?? ''));
$supportEmail = trim((string)($support['email'] ?? ''));
$available247 = !empty($support['available_247']);
$sessionUser = $userProfile ?? [];
$faq = [
    ['cat' => 'Booking', 'q' => 'How do I book a stay?', 'a' => 'Search for a destination, pick your dates and room, then enter the lead guest details. Your price is guaranteed while the checkout countdown runs. On the payment step choose M-Pesa, Tigo Pesa, Airtel Money or HaloPesa and approve the USSD push on your phone.'],
    ['cat' => 'Payment', 'q' => 'Which payment methods can I use?', 'a' => 'We accept Tanzanian mobile money through AzamPay: M-Pesa (Vodacom), Tigo Pesa, Airtel Money and HaloPesa. All payment data is encrypted. Totals are charged in Tanzanian shillings and include a 1% mobile-money processing fee — no VAT is added.'],
    ['cat' => 'Payment', 'q' => 'What does “this price is guaranteed” mean?', 'a' => 'When you start checkout we lock a live price quote for 15 minutes and count it down on screen. If the countdown expires before you pay, the guarantee lapses and you refresh for the current live price — you are never charged a stale amount.'],
    ['cat' => 'Payment', 'q' => 'I approved the payment on my phone but the page still says waiting. What now?', 'a' => 'Keep the payment page open — it checks with the payment gateway automatically and confirms the moment approval lands. If it expires, do not pay twice: find your booking on the My bookings page with your email and reference, or send us a support ticket below.'],
    ['cat' => 'Cancellation', 'q' => 'Can I cancel or change my booking?', 'a' => 'Most stays include free cancellation before the date shown at checkout. Open My bookings, find your reservation and choose Cancel stay where eligible. Approved refunds go back to the mobile-money number you paid with.'],
    ['cat' => 'Booking', 'q' => 'Where is my receipt or invoice?', 'a' => 'Open My bookings, choose View Details on your reservation, then Download PDF. Receipts are only generated for paid bookings.'],
    ['cat' => 'Account', 'q' => 'I can’t find my booking. How do I look it up?', 'a' => 'On the My bookings page use “Find your booking” with the guest email and the booking reference from your confirmation. Guest bookings made without an account are found the same way.'],
    ['cat' => 'Hosting', 'q' => 'How do I list my property and become a host?', 'a' => 'Hosting uses a separate host account: create one from the Join us page, then add your property from the host dashboard where you manage rooms, prices, bookings and payouts. A normal guest account cannot access host tooling.'],
    ['cat' => 'Booking', 'q' => 'Can I request a non-smoking room or a specific bed setup?', 'a' => 'Yes — choose your room and bed preferences at checkout and add anything else in writing. Preferences are sent to the property with your booking and are subject to availability on arrival.'],
];
$cats = ['All', 'Booking', 'Payment', 'Cancellation', 'Account', 'Hosting'];
?>
<?= $this->element('navbar') ?>
<main id="main-content" role="main" style="background:var(--cds-gray-10);min-height:85vh">
<style>
/* IBM Carbon v11 idiom — White/Gray-10 theme, productive type */
.cds-help-band{background:#161616;color:#fff;padding:32px 0 56px}
.cds-help-eyebrow{font-size:12px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:#c6c6c6}
.cds-help-h1{font-size:42px;font-weight:300;letter-spacing:0;color:#fff;margin:12px 0 8px;line-height:1.15}
.cds-help-lede{font-size:16px;font-weight:400;color:#c6c6c6;margin:0;max-width:640px}
.cds-help-searchwrap{margin-top:-28px;position:relative;z-index:2}
.cds-help-search{display:flex;align-items:center;background:#fff;border:1px solid #8d8d8d;border-bottom:3px solid #8d8d8d;max-width:720px}
.cds-help-search:focus-within{outline:2px solid var(--cds-focus);outline-offset:-2px;border-bottom-color:var(--cds-blue-60)}
.cds-help-search>i{padding:0 4px 0 16px;color:#161616;font-size:16px}
.cds-help-search input{border:none;background:transparent;flex:1;height:48px;font-size:14px;color:#161616;outline:none;min-width:0}
.cds-help-search input::placeholder{color:#6f6f6f}
.cds-help-body{max-width:960px;margin:0 auto;padding:16px 16px 40px;display:flex;flex-direction:column;gap:16px}
.cds-sec-label{font-size:12px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:#525252;margin:0 0 4px}
.cds-sec-title{font-size:22px;font-weight:400;color:#161616;margin:0 0 2px}
.cds-sec-sub{font-size:14px;color:#525252;margin:0 0 10px}
/* Clickable tiles */
.cds-topic-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1px;background:#e0e0e0;border:1px solid #e0e0e0}
.cds-topic{background:#fff;padding:14px 16px;min-height:112px;display:flex;flex-direction:column;text-decoration:none!important;position:relative;transition:background 120ms ease}
.cds-topic:hover{background:#e8e8e8}
.cds-topic i{font-size:20px;color:var(--cds-blue-60);margin-bottom:12px}
.cds-topic b{display:block;font-size:14px;font-weight:600;color:#161616}
.cds-topic span.go{margin-top:auto;padding-top:8px;font-size:13px;color:var(--cds-blue-60);font-weight:600;display:flex;align-items:center;gap:6px}
/* Filter tags */
.cds-tagrow{display:flex;gap:8px;flex-wrap:wrap;margin:0 0 12px}
.cds-tag{border:1px solid #8d8d8d;background:#fff;color:#161616;font-size:12px;font-weight:400;padding:5px 12px;cursor:pointer;border-radius:0}
.cds-tag[aria-pressed="true"]{background:#161616;border-color:#161616;color:#fff}
.cds-help-count{font-size:12px;color:#6f6f6f;margin:0 0 0}
/* Accordion — border rows, chevron right */
.cds-acc{background:#fff;border:1px solid #e0e0e0}
.cds-acc-item{border-top:1px solid #e0e0e0}
.cds-acc-item:first-child{border-top:none}
.cds-acc-q{width:100%;display:flex;align-items:center;gap:12px;background:none;border:none;text-align:left;padding:16px;font-size:14px;font-weight:600;color:#161616;cursor:pointer}
.cds-acc-q:hover{background:#f4f4f4}
.cds-acc-q .chev{margin-left:auto;font-size:14px;color:#161616;transition:transform 150ms ease;flex:0 0 auto}
.cds-acc-item.open .cds-acc-q .chev{transform:rotate(90deg)}
.cds-acctag{font-size:11px;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:#0043ce;background:#d0e2ff;padding:3px 8px;flex:0 0 auto}
.cds-acc-a{display:none;padding:0 16px 16px 16px;font-size:14px;line-height:1.6;color:#393939;max-width:720px}
.cds-acc-item.open .cds-acc-a{display:block}
.cds-help-empty{display:none;background:#fff;border:1px solid #e0e0e0;padding:28px 16px;text-align:center;font-size:14px;color:#525252}
.cds-help-empty a{color:var(--cds-blue-60);font-weight:600}
/* Notification + structured list */
.cds-note{display:flex;gap:12px;background:#fff;border:1px solid #e0e0e0;border-left:4px solid var(--cds-blue-60);padding:14px 16px;font-size:14px;color:#161616}
.cds-note.success{border-left-color:#24a148}
.cds-note.error{border-left-color:#da1e28}
.cds-note i{font-size:18px;margin-top:1px}
.cds-note a{color:var(--cds-blue-60);font-weight:600}
.cds-list{background:#fff;border:1px solid #e0e0e0}
.cds-list-row{display:flex;align-items:center;gap:12px;padding:13px 16px;border-top:1px solid #e0e0e0;font-size:14px}
.cds-list-row:first-child{border-top:none}
.cds-list-row .k{width:130px;flex:0 0 130px;font-size:12px;color:#6f6f6f}
.cds-list-row a{color:var(--cds-blue-60);font-weight:600;text-decoration:none}
.cds-list-row a:hover{text-decoration:underline}
/* Form */
.cds-field{margin-bottom:16px}
.cds-field label{display:block;font-size:12px;color:#525252;margin-bottom:8px}
/* !important wins over the global rounded-input theme — Carbon square fields */
#helpTicket .cds-field input,#helpTicket .cds-field select,#helpTicket .cds-field textarea{width:100%!important;background:#f4f4f4!important;border:none!important;border-bottom:1px solid #8d8d8d!important;border-radius:0!important;font-size:14px!important;color:#161616!important}
#helpTicket .cds-field input,#helpTicket .cds-field select{height:40px!important;padding:0 12px!important}
#helpTicket .cds-field textarea{padding:10px 12px!important;min-height:96px;resize:vertical}
#helpTicket .cds-field input:focus,#helpTicket .cds-field select:focus,#helpTicket .cds-field textarea:focus{outline:2px solid var(--cds-focus)!important;outline-offset:-2px!important;border-bottom-color:var(--cds-blue-60)!important}
.cds-btn{display:inline-block;background:var(--cds-blue-60);color:#fff;border:1px solid transparent;padding:12px 20px;font-size:14px;font-weight:400;cursor:pointer;border-radius:0;text-decoration:none!important;min-height:48px}
.cds-btn:hover{background:#0353e9;color:#fff}
.cds-btn:disabled{opacity:.6;cursor:not-allowed}
.cds-btn-ghost{display:inline-block;background:transparent;color:var(--cds-blue-60);border:1px solid var(--cds-blue-60);padding:12px 20px;font-size:14px;cursor:pointer;border-radius:0;text-decoration:none!important;min-height:48px}
.cds-btn-ghost:hover{background:var(--cds-blue-60);color:#fff}
.cds-cols{display:grid;grid-template-columns:1fr 1fr;gap:24px}
.cds-panel{background:#fff;border:1px solid #e0e0e0;padding:24px}
@media(max-width:992px){.cds-topic-grid{grid-template-columns:1fr 1fr}.cds-cols{grid-template-columns:1fr}}
@media(max-width:560px){.cds-help-h1{font-size:32px}.cds-topic-grid{grid-template-columns:1fr}.cds-list-row .k{width:96px;flex-basis:96px}}
</style>

<div class="cds-help-band">
  <div class="container" style="max-width:960px">
    <div class="cds-help-eyebrow">Support / Help center</div>
    <h1 class="cds-help-h1">How can we help?</h1>
    <p class="cds-help-lede">Answers about bookings, mobile-money payments, cancellations and hosting across Tanzania.</p>
  </div>
</div>

<div class="container" style="max-width:960px">
  <div class="cds-help-searchwrap">
    <form id="helpSearchForm" role="search" onsubmit="return false">
      <div class="cds-help-search">
        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
        <input id="helpSearch" type="search" placeholder="Search — try “refund”, “M-Pesa”, “receipt”…" autocomplete="off" aria-label="Search help articles">
      </div>
    </form>
  </div>
</div>

<div class="cds-help-body">

  <?php if ($upcoming !== null): ?>
  <div class="cds-note" role="status">
    <i class="fa-solid fa-suitcase-rolling" style="color:var(--cds-blue-60)" aria-hidden="true"></i>
    <div>
      <strong>Upcoming stay<?= !empty($upcoming['property_name']) ? ' — ' . h($upcoming['property_name']) : '' ?></strong>
      <span style="color:#525252"><?= !empty($upcoming['check_in']) ? h($upcoming['check_in']) : '' ?><?= !empty($upcoming['check_out']) ? ' → ' . h($upcoming['check_out']) : '' ?><?= !empty($upcoming['status']) ? ' · ' . h($upcoming['status']) : '' ?></span>
      <div><a href="<?= $this->Url->build('/my-booking') ?>">Manage in My bookings →</a></div>
    </div>
  </div>
  <?php endif; ?>

  <?php if (!empty($topics)): ?>
  <section aria-label="Popular topics">
    <p class="cds-sec-label">Start here</p>
    <h2 class="cds-sec-title">Popular topics</h2>
    <p class="cds-sec-sub">Jump straight to the right place.</p>
    <div class="cds-topic-grid">
      <?php foreach (array_slice($topics, 0, 4) as $t): ?>
        <a class="cds-topic" href="<?= h($t['action_url']) ?>">
          <i class="<?= h($topicIcons[$t['icon'] ?? ''] ?? 'fa-regular fa-circle-question') ?>" aria-hidden="true"></i>
          <b><?= h($t['title']) ?></b>
          <span class="go">Open <i class="fa-solid fa-arrow-right" style="font-size:12px;margin:0"></i></span>
        </a>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <section aria-label="Frequently asked questions">
    <p class="cds-sec-label">Knowledge base</p>
    <h2 class="cds-sec-title">Frequently asked questions</h2>
    <p class="cds-sec-sub">Real answers for guests and hosts.</p>
    <div class="cds-tagrow" role="group" aria-label="Filter by topic">
      <?php foreach ($cats as $i => $c): ?>
        <button type="button" class="cds-tag" data-cat="<?= h($c) ?>" aria-pressed="<?= $i === 0 ? 'true' : 'false' ?>"><?= h($c) ?></button>
      <?php endforeach; ?>
    </div>
    <p class="cds-help-count" id="faqCount"></p>
    <div class="cds-acc" id="faqList" style="margin-top:8px">
      <?php foreach ($faq as $f): ?>
        <div class="cds-acc-item" data-cat="<?= h($f['cat']) ?>" data-text="<?= h(strtolower($f['q'] . ' ' . $f['a'])) ?>">
          <button type="button" class="cds-acc-q" aria-expanded="false">
            <span class="cds-acctag"><?= h($f['cat']) ?></span>
            <span><?= h($f['q']) ?></span>
            <i class="fa-solid fa-chevron-right chev" aria-hidden="true"></i>
          </button>
          <div class="cds-acc-a"><?= h($f['a']) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="cds-help-empty" id="faqEmpty">
      No answers match your search. <a href="#helpTicket">Send us a ticket</a> and we’ll help directly.
    </div>
  </section>

  <section aria-label="Contact support">
    <p class="cds-sec-label">Still stuck</p>
    <h2 class="cds-sec-title">Talk to a human</h2>
    <p class="cds-sec-sub"><?= $available247 ? 'Support is available around the clock.' : 'We reply as fast as we can.' ?></p>
    <div class="cds-cols">
      <div class="cds-list">
        <?php if ($supportPhone !== ''): ?>
          <div class="cds-list-row"><span class="k">Phone</span><a href="tel:<?= h(preg_replace('/[^+\d]/', '', $supportPhone)) ?>"><?= h($supportPhone) ?></a></div>
        <?php endif; ?>
        <?php if ($supportEmail !== ''): ?>
          <div class="cds-list-row"><span class="k">Email</span><a href="mailto:<?= h($supportEmail) ?>"><?= h($supportEmail) ?></a></div>
        <?php endif; ?>
        <div class="cds-list-row"><span class="k">Contact page</span><a href="<?= $this->Url->build('/contact-v1') ?>">Open contact form →</a></div>
        <?php if ($supportPhone === '' && $supportEmail === ''): ?>
          <div class="cds-list-row"><span class="k">Note</span><span style="color:#525252">Reach us any time through the contact page.</span></div>
        <?php endif; ?>
      </div>
      <div class="cds-panel" id="helpTicket">
        <p class="cds-sec-label">Support ticket</p>
        <h3 class="cds-sec-title" style="font-size:20px">Submit a ticket</h3>
        <p class="cds-sec-sub">We reply by email with your ticket reference.</p>
        <div id="ticketAlert"></div>
        <?php if (!empty($isLoggedIn)): ?>
        <form id="ticketForm" novalidate>
          <div class="cds-field">
            <label for="ticketIssue">What is it about?</label>
            <select id="ticketIssue" required>
              <option value="">Choose a topic…</option>
              <option>My booking</option>
              <option>Payment</option>
              <option>Cancellation or refund</option>
              <option>My account</option>
              <option>Hosting</option>
              <option>Something else</option>
            </select>
          </div>
          <div class="cds-field">
            <label for="ticketDesc">Describe the problem</label>
            <textarea id="ticketDesc" required minlength="10" placeholder="Booking reference (if any), what happened, what you expected…"></textarea>
          </div>
          <div class="cds-field">
            <label for="ticketEmail">Reply-to email</label>
            <input id="ticketEmail" type="email" required value="<?= h($sessionUser['email'] ?? '') ?>" placeholder="you@example.com">
          </div>
          <button type="submit" class="cds-btn" id="ticketBtn">Send ticket</button>
        </form>
        <?php else: ?>
        <p style="font-size:14px;color:#525252">Tickets need an account so we can follow up with you.</p>
        <a class="cds-btn-ghost" href="<?= $this->Url->build('/login', ['?' => ['redirect' => '/help-center']]) ?>">Sign in to send a ticket</a>
        <?php endif; ?>
      </div>
    </div>
  </section>

</div>
</main>
<?= $this->element('footer', ['skin' => 'skin-light-footer']) ?>
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
