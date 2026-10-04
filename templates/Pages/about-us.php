<?php
$this->assign('title', 'About Us | FastNet Stays');
$this->assign('description', 'FastNet Stays is Tanzania’s accommodation marketplace — verified lodges, transparent pricing and mobile-money payments.');
?>
<?= $this->element('navbar') ?>
<main id="main-content" role="main" style="background:var(--cds-gray-10);min-height:85vh">
<style>
.cds-about-band{background:#161616;color:#fff;padding:40px 0 32px}
.cds-about-eyebrow{font-size:12px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:#c6c6c6}
.cds-about-h1{font-size:40px;font-weight:300;color:#fff;margin:10px 0 8px;line-height:1.15;max-width:720px}
.cds-about-lede{font-size:16px;color:#c6c6c6;margin:0;max-width:640px}
.cds-about-body{max-width:1100px;margin:0 auto;padding:28px 16px 56px;display:flex;flex-direction:column;gap:28px}
.cds-sec-label{font-size:12px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:#525252;margin:0 0 4px}
.cds-sec-title{font-size:26px;font-weight:300;color:#161616;margin:0 0 6px;max-width:720px}
.cds-sec-sub{font-size:14px;color:#525252;margin:0;max-width:720px}
.cds-split{display:grid;grid-template-columns:1.1fr 1fr;gap:32px;align-items:center;background:#fff;border:1px solid #e0e0e0;padding:32px}
.cds-split p{font-size:14.5px;line-height:1.65;color:#393939;margin:0 0 12px}
.cds-split img{width:100%;height:100%;min-height:280px;object-fit:cover;display:block}
.cds-feat{display:flex;gap:12px;margin-top:16px}
.cds-feat i{font-size:18px;color:var(--cds-blue-60);margin-top:2px}
.cds-feat b{display:block;font-size:14px;color:#161616}
.cds-feat span{font-size:13px;color:#525252}
.cds-tiles{display:grid;grid-template-columns:repeat(3,1fr);gap:1px;background:#e0e0e0;border:1px solid #e0e0e0;margin-top:16px}
.cds-tile{background:#fff;padding:24px;min-height:190px}
.cds-tile i{font-size:22px;color:var(--cds-blue-60);margin-bottom:40px}
.cds-tile b{display:block;font-size:15px;font-weight:600;color:#161616;margin-bottom:6px}
.cds-tile p{font-size:13.5px;line-height:1.6;color:#525252;margin:0}
.cds-cta{display:flex;gap:12px;flex-wrap:wrap;background:#161616;padding:28px;color:#fff;align-items:center;justify-content:space-between}
.cds-cta h2{font-size:22px;font-weight:300;margin:0 0 4px}
.cds-cta p{font-size:13.5px;color:#c6c6c6;margin:0}
.cds-cta .row-btns{display:flex;gap:12px;flex-wrap:wrap}
.cds-btn{display:inline-block;background:var(--cds-blue-60);color:#fff;border:1px solid transparent;padding:12px 22px;font-size:14px;border-radius:0;text-decoration:none!important;min-height:48px}
.cds-btn:hover{background:#0353e9;color:#fff}
.cds-btn-ghost{display:inline-block;background:transparent;color:#fff;border:1px solid #8d8d8d;padding:12px 22px;font-size:14px;border-radius:0;text-decoration:none!important;min-height:48px}
.cds-btn-ghost:hover{border-color:#fff}
@media(max-width:992px){.cds-split{grid-template-columns:1fr;padding:24px}.cds-tiles{grid-template-columns:1fr}.cds-cta{flex-direction:column;align-items:flex-start}}
@media(max-width:560px){.cds-about-h1{font-size:30px}.cds-tile i{margin-bottom:20px}}
</style>

<div class="cds-about-band">
  <div class="container" style="max-width:1100px">
    <div class="cds-about-eyebrow">About / FastNet Stays</div>
    <h1 class="cds-about-h1">Tanzania’s accommodation marketplace</h1>
    <p class="cds-about-lede">Verified safari lodges, beach resorts and city stays — with transparent pricing and mobile-money payments.</p>
  </div>
</div>

<div class="cds-about-body">
  <section aria-label="Who we are">
    <div class="cds-split">
      <div>
        <p class="cds-sec-label">Who we are</p>
        <h2 class="cds-sec-title">Built for Tanzanian travel</h2>
        <p>FastNetStays connects travelers with verified lodges, coastal beach resorts, boutique hotels and guest lodges across Zanzibar, Dar es Salaam, Arusha, Serengeti, Kilimanjaro and Dodoma — with instant availability, live pricing in Tanzanian shillings and no hidden fees.</p>
        <div class="cds-feat">
          <i class="fa-solid fa-bolt" aria-hidden="true"></i>
          <div><b>Live availability &amp; locked prices</b><span>Real room inventory with a 15-minute price guarantee at checkout.</span></div>
        </div>
        <div class="cds-feat">
          <i class="fa-solid fa-mobile-screen-button" aria-hidden="true"></i>
          <div><b>Local mobile money</b><span>Pay with M-Pesa, Tigo Pesa, Airtel Money or HaloPesa through AzamPay.</span></div>
        </div>
      </div>
      <div><img src="<?= $this->Url->build('/assets/img/side-3.png') ?>" alt="Travelers booking Tanzanian stays on FastNet Stays" loading="lazy"></div>
    </div>
  </section>

  <section aria-label="Why trust us">
    <p class="cds-sec-label">Our pillars</p>
    <h2 class="cds-sec-title">Why travelers and hosts trust us</h2>
    <p class="cds-sec-sub">Reliability, local partnership and guest-first technology.</p>
    <div class="cds-tiles">
      <div class="cds-tile">
        <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
        <b>Verified stays</b>
        <p>Listed properties pass host verification, so photos, amenities and service match what you book.</p>
      </div>
      <div class="cds-tile">
        <i class="fa-solid fa-handshake-angle" aria-hidden="true"></i>
        <b>Local hosts empowered</b>
        <p>Tanzanian owners manage listings, pricing and bookings directly from the host dashboard.</p>
      </div>
      <div class="cds-tile">
        <i class="fa-solid fa-headset" aria-hidden="true"></i>
        <b>Human support</b>
        <p>Booking changes, special requests and travel questions — answered by our support team.</p>
      </div>
    </div>
  </section>

  <section class="cds-cta" aria-label="Get started">
    <div>
      <h2>Ready when you are</h2>
      <p>Find your next stay — or list your property and start receiving bookings.</p>
    </div>
    <div class="row-btns">
      <a class="cds-btn" href="<?= $this->Url->build('/hotel-list-01') ?>">Search stays</a>
      <a class="cds-btn-ghost" href="<?= $this->Url->build('/join-us') ?>">Become a host</a>
    </div>
  </section>
</div>
</main>
<?= $this->element('footer', ['skin' => 'skin-light-footer']) ?>
