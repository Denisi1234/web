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
<?= $this->Html->css('/assets/css/help-center.css') ?>

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
<?= $this->element('Help/help-center-script') ?>
