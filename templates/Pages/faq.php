<?php
$this->assign('title', 'Frequently Asked Questions | FastNet Stays');
$this->assign('description', 'Answers about booking stays, mobile-money payments, cancellations and hosting on FastNet Stays.');
$supportEmail = trim((string)($supportEmail ?? ''));
$faq = [
    ['cat' => 'Booking', 'q' => 'How do I book a stay on FastNet Stays?', 'a' => 'Search your destination (e.g. Zanzibar, Dar es Salaam, Arusha), pick your dates, choose a room and enter the lead guest details. On the payment step you pay with Vodacom M-Pesa, Tigo Pesa, Airtel Money or HaloPesa and approve the USSD push on your phone.'],
    ['cat' => 'Payment', 'q' => 'Which payment methods are accepted?', 'a' => 'Tanzanian mobile money through AzamPay only: Vodacom M-Pesa, Tigo Pesa, Airtel Money and HaloPesa (Halotel). All bookings are prepaid online in Tanzanian shillings — totals include a 1% mobile-money processing fee and no VAT.'],
    ['cat' => 'Payment', 'q' => 'Can I pay when I arrive at the property?', 'a' => 'No — every booking is confirmed by prepaid mobile-money payment at checkout. Your reservation is only guaranteed once the payment confirms, which is why the checkout shows a live price countdown.'],
    ['cat' => 'Cancellation', 'q' => 'What is the cancellation and refund policy?', 'a' => 'Each stay shows its own cancellation window at checkout — most include free cancellation before the stated date. Cancel an eligible stay from My bookings and approved refunds return to the mobile-money number you paid with.'],
    ['cat' => 'Booking', 'q' => 'Where is my receipt?', 'a' => 'Open My bookings, choose View Details on your reservation, then Download PDF. Receipts are generated for paid bookings only.'],
    ['cat' => 'Account', 'q' => 'I can’t find my booking. How do I look it up?', 'a' => 'On the My bookings page use “Find your booking” with the guest email and the booking reference from your confirmation email.'],
    ['cat' => 'Hosting', 'q' => 'How do I list my lodge or hotel?', 'a' => 'Create a separate host account from the Join us page, then add your property from the host dashboard — rooms, pricing, bookings and payouts are all managed there.'],
];
?>
<?= $this->element('navbar') ?>
<main id="main-content" role="main" style="background:var(--cds-gray-10);min-height:85vh">
<style>
.cds-faq-band{background:#161616;color:#fff;padding:32px 0 56px}
.cds-faq-eyebrow{font-size:12px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:#c6c6c6}
.cds-faq-h1{font-size:32px;font-weight:300;color:#fff;margin:8px 0 6px}
.cds-faq-lede{font-size:14px;color:#c6c6c6;margin:0;max-width:620px}
.cds-faq-searchwrap{margin-top:-28px;position:relative;z-index:2}
.cds-faq-search{display:flex;align-items:center;background:#fff;border:1px solid #8d8d8d;border-bottom:3px solid #8d8d8d;max-width:720px}
.cds-faq-search:focus-within{outline:2px solid var(--cds-focus);outline-offset:-2px;border-bottom-color:var(--cds-blue-60)}
.cds-faq-search>i{padding:0 4px 0 16px;color:#161616;font-size:16px}
.cds-faq-search input{border:none;background:transparent;flex:1;height:48px;font-size:14px;color:#161616;outline:none;min-width:0}
.cds-faq-search input::placeholder{color:#6f6f6f}
.cds-faq-body{max-width:960px;margin:0 auto;padding:16px 16px 40px;display:flex;flex-direction:column;gap:16px}
.cds-acc{background:#fff;border:1px solid #e0e0e0}
.cds-acc-item{border-top:1px solid #e0e0e0}
.cds-acc-item:first-child{border-top:none}
.cds-acc-q{width:100%;display:flex;align-items:center;gap:12px;background:none;border:none;text-align:left;padding:16px;font-size:14px;font-weight:600;color:#161616;cursor:pointer}
.cds-acc-q:hover{background:#f4f4f4}
.cds-acc-q .chev{margin-left:auto;font-size:14px;transition:transform 150ms ease;flex:0 0 auto}
.cds-acc-item.open .cds-acc-q .chev{transform:rotate(90deg)}
.cds-acctag{font-size:11px;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:#0043ce;background:#d0e2ff;padding:3px 8px;flex:0 0 auto}
.cds-acc-a{display:none;padding:0 16px 16px;font-size:14px;line-height:1.6;color:#393939;max-width:720px}
.cds-acc-item.open .cds-acc-a{display:block}
.cds-faq-empty{display:none;background:#fff;border:1px solid #e0e0e0;padding:24px 16px;text-align:center;font-size:14px;color:#525252}
.cds-faq-empty a{color:var(--cds-blue-60);font-weight:600}
.cds-count{font-size:12px;color:#6f6f6f;margin:0}
.cds-strip{display:flex;gap:12px;align-items:center;background:#fff;border:1px solid #e0e0e0;border-left:4px solid var(--cds-blue-60);padding:14px 16px;font-size:14px;color:#161616;flex-wrap:wrap}
.cds-strip a{color:var(--cds-blue-60);font-weight:600}
</style>

<div class="cds-faq-band">
  <div class="container" style="max-width:960px">
    <div class="cds-faq-eyebrow">Support / FAQ</div>
    <h1 class="cds-faq-h1">Frequently asked questions</h1>
    <p class="cds-faq-lede">Instant answers about booking stays, payments, cancellations and hosting.</p>
  </div>
</div>

<div class="container" style="max-width:960px">
  <div class="cds-faq-searchwrap">
    <form role="search" onsubmit="return false">
      <div class="cds-faq-search">
        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
        <input id="faqSearch" type="search" placeholder="Search answers — try “refund”, “M-Pesa”, “receipt”…" autocomplete="off" aria-label="Search answers">
      </div>
    </form>
  </div>
</div>

<div class="cds-faq-body">
  <p class="cds-count" id="faqCount"></p>
  <div class="cds-acc" id="faqList">
    <?php foreach ($faq as $f): ?>
      <div class="cds-acc-item" data-text="<?= h(strtolower($f['q'] . ' ' . $f['a'])) ?>">
        <button type="button" class="cds-acc-q" aria-expanded="false">
          <span class="cds-acctag"><?= h($f['cat']) ?></span>
          <span><?= h($f['q']) ?></span>
          <i class="fa-solid fa-chevron-right chev" aria-hidden="true"></i>
        </button>
        <div class="cds-acc-a"><?= h($f['a']) ?></div>
      </div>
    <?php endforeach; ?>
  </div>
  <div class="cds-faq-empty" id="faqEmpty">No answers match. Try the <a href="<?= $this->Url->build('/help-center') ?>">help center</a> or send a support ticket there.</div>
  <div class="cds-strip">
    <span>Still stuck?</span>
    <a href="<?= $this->Url->build('/help-center') ?>">Help center →</a>
    <?php if ($supportEmail !== ''): ?><a href="mailto:<?= h($supportEmail) ?>"><?= h($supportEmail) ?></a><?php endif; ?>
    <a href="<?= $this->Url->build('/contact-v1') ?>">Contact us →</a>
  </div>
</div>
</main>
<?= $this->element('footer', ['skin' => 'skin-light-footer']) ?>
<script>
(function(){
  const input = document.getElementById('faqSearch');
  const items = Array.from(document.querySelectorAll('.cds-acc-item'));
  const count = document.getElementById('faqCount');
  const empty = document.getElementById('faqEmpty');
  function apply(){
    const q = (input.value || '').trim().toLowerCase();
    let shown = 0;
    items.forEach(el => {
      const show = q === '' || el.dataset.text.includes(q);
      el.style.display = show ? '' : 'none';
      if (show) shown++;
      if (!show) { el.classList.remove('open'); el.querySelector('.cds-acc-q').setAttribute('aria-expanded','false'); }
    });
    count.textContent = shown + ' of ' + items.length + ' answers';
    empty.style.display = shown === 0 ? 'block' : 'none';
  }
  input.addEventListener('input', apply);
  document.querySelectorAll('.cds-acc-q').forEach(btn => btn.addEventListener('click', () => {
    const item = btn.closest('.cds-acc-item');
    const open = item.classList.toggle('open');
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
  }));
  apply();
})();
</script>
