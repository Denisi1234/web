<?php
$this->assign('title', 'Customer Terms of Service | fastnetstays.com');
?>

<?= $this->Html->css('/assets/css/google-travel-layout.css') ?>
<?= $this->Html->css('/assets/css/google-travel-home.css') ?>
<?= $this->Html->css('/assets/css/google-travel-cards.css') ?>

<?= $this->element('navbar') ?>
<div class="gh-m-tabs" role="tablist" aria-label="Travel types">
  <a href="/?explore=1" role="tab">Explore</a>
  <a href="/?homes=1" role="tab">Homes</a>
  <a href="/" role="tab" class="active" aria-selected="true">Hotels</a>
  <a href="/?destination=Vacation" role="tab">Vacation rentals</a>
</div>
<nav aria-label="Breadcrumb" class="container-fluid px-2 px-lg-2" style="max-width:100%;margin:0 auto;background:#f8f9fa;">
  <ol class="breadcrumb mb-0 py-1" style="background:transparent;font-size:12px;line-height:1.2;--bs-breadcrumb-divider:'›';" itemscope itemtype="https://schema.org/BreadcrumbList">
    <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a href="/" itemprop="item" style="color:#5f6368;text-decoration:none;"><span itemprop="name">Home</span></a><meta itemprop="position" content="1"></li>
    <li class="breadcrumb-item active" aria-current="page" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><span itemprop="name">Terms Of Service</span><meta itemprop="position" content="2"></li>
  </ol>
</nav>
<main id="main-content" style="background:#f8f9fa;min-height:85vh;" role="main">

<style>
    .legal-content h1 {
        font-size: 32px;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 8px;
    }
    .legal-content h2 {
        font-size: 22px;
        font-weight: 700;
        color: #1a1a1a;
        margin-top: 36px;
        margin-bottom: 14px;
    }
    .legal-content h3 {
        font-size: 16px;
        font-weight: 700;
        color: #1a1a1a;
        margin-top: 24px;
        margin-bottom: 10px;
    }
    .legal-content p {
        font-size: 14.5px;
        line-height: 1.65;
        color: #262626;
        margin-bottom: 14px;
    }
    .legal-content ul, .legal-content ol {
        font-size: 14.5px;
        line-height: 1.65;
        color: #262626;
        margin-bottom: 14px;
        padding-left: 24px;
    }
    .legal-content li {
        margin-bottom: 6px;
    }
    .legal-content a {
        color: #ea580c;
        text-decoration: underline;
    }
    .legal-content a:hover {
        color: #c2410c;
    }
    .toc-box {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px 24px;
        margin-bottom: 32px;
    }
    .toc-box ol {
        padding-left: 20px;
        margin-bottom: 0;
    }
    .toc-box li {
        font-size: 14px;
        margin-bottom: 6px;
        font-weight: 600;
    }
    .toc-box a {
        color: #ea580c;
        text-decoration: none;
    }
    .toc-box a:hover {
        text-decoration: underline;
    }
    .legal-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
        font-size: 13.5px;
    }
    .legal-table th, .legal-table td {
        border: 1px solid #e2e8f0;
        padding: 10px 14px;
        text-align: left;
        vertical-align: top;
    }
    .legal-table th {
        background-color: #f8fafc;
        font-weight: 700;
        color: #0f172a;
    }
</style>

<!-- Hero Header Banner Start -->
<section class="position-relative" style="background:#fff;border-bottom:1px solid #e8eaed;padding:28px 0 22px;">
    <div class="container">
        <div class="row align-items-center justify-content-center">
            <div class="col-xl-8 col-lg-10 col-md-12">
                <div class="fpc-capstion text-center my-4">
                    <div class="fpc-captions">
                        <h1 class="xl-heading fw-bold" style="color:#202124;font-family:'Google Sans',Roboto,sans-serif;font-weight:400;"">Customer Terms of Service</h1>
                        <p class="text-muted" style="color:#5f6368;font-family:Roboto,sans-serif;"">Official Terms governing accommodation, experience, and transportation bookings across Tanzania.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="fpc-banner"></div>
</section>
<!-- Hero Header Banner End -->

<!-- Main Legal Document Content -->
<section class="py-5 " style="background:#f8f9fa;">
    <div class="container px-3 px-md-4">
        <div class="row justify-content-center">
            <div class="col-xl-10 col-lg-11 col-md-12">
                <main class="bg-white p-4 p-sm-5 rounded-4 border border-slate-200 shadow-sm legal-content mx-auto">
                    
                    <!-- Document Sub-Header & Meta -->
                    <div class="d-flex align-items-center justify-content-between flex-wrap border-bottom border-slate-100 pb-3 mb-4">
                        <div>
                            <h2 class="text-2xl font-bold text-[#1a1a1a] !mt-0 !mb-1">Customer Terms of Service</h2>
                            <p class="text-sm text-[#595959] !mb-0">Updated September 15, 2025</p>
                        </div>
                        <!-- Print Action Link -->
                        <div>
                            <button onclick="window.print()" class="inline-flex items-center gap-1.5 text-sm font-semibold text-[#ea580c] hover:underline cursor-pointer bg-orange-50 px-3 py-1.5 rounded-full border border-orange-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                Print Document
                            </button>
                        </div>
                    </div>

                    <!-- Summary of these Terms -->
                    <div class="bg-orange-50/60 border border-orange-200 rounded-3 p-4 mb-4">
                        <h3 class="text-base font-bold text-[#c2410c] !mt-0 !mb-2">Summary of these Terms</h3>
                        <p class="text-sm text-[#1e293b] mb-2">Along with the Terms on this page, our <a href="<?= $this->Url->build('/about-us'); ?>" class="font-semibold">How Fastnetstays Works</a> page and our <a href="<?= $this->Url->build('/privacy-policy'); ?>" class="font-semibold">Privacy Notice for Travelers</a> form part of our contract with you.</p>
                        <p class="text-sm text-[#1e293b] mb-0">By completing a booking or creating an account, you agree to these terms. Fastnetstays.com operates as an online marketplace connecting travelers with trusted accommodation providers, tour operators, and transportation drivers in Tanzania.</p>
                    </div>

                    <!-- Table of contents -->
                    <div class="toc-box">
                        <h3 class="text-base font-bold text-[#1a1a1a] !mt-0 !mb-3">Table of contents</h3>
                        <ol class="list-decimal space-y-1 font-semibold">
                            <li><a href="#section-a">A. All Travel Experiences (General Terms)</a>
                                <ol class="list-[upper-alpha] pl-5 mt-1 space-y-1 font-normal">
                                    <li><a href="#a1">A1. Definitions</a></li>
                                    <li><a href="#a2">A2. About these terms</a></li>
                                    <li><a href="#a3">A3. About Fastnetstays.com</a></li>
                                    <li><a href="#a4">A4. Our Platform</a></li>
                                    <li><a href="#a5">A5. Our values and user obligations</a></li>
                                    <li><a href="#a6">A6. Prices and currency display</a></li>
                                    <li><a href="#a7">A7. Payment & local mobile money</a></li>
                                    <li><a href="#a8">A8. Booking policies, cancellations & no-shows</a></li>
                                    <li><a href="#a9">A9. Privacy and cookies</a></li>
                                    <li><a href="#a10">A10. Intellectual property rights & content</a></li>
                                    <li><a href="#a11">A11. What if something goes wrong? (Complaints & support)</a></li>
                                    <li><a href="#a12">A12. Limitation of liability</a></li>
                                    <li><a href="#a13">A13. Applicable law and jurisdiction</a></li>
                                    <li><a href="#a14">A14. Modifications to these terms</a></li>
                                </ol>
                            </li>
                            <li><a href="#section-b">B. Accommodations (Hotels, Lodges, Villas & Stays)</a></li>
                            <li><a href="#section-c">C. Attractions & Safari Experiences</a></li>
                            <li><a href="#section-d">D. Private & Public Transportation (Airport Transfers)</a></li>
                            <li><a href="#dictionary">Fastnetstays.com Dictionary</a></li>
                        </ol>
                    </div>

                    <!-- Section A -->
                    <section id="section-a">
                        <h2>A. All Travel Experiences (General Terms)</h2>

                        <div id="a1">
                            <h3>A1. Definitions</h3>
                            <p>Some words here have specific meanings. Please refer to the <strong>Fastnetstays.com Dictionary</strong> at the end of these Terms.</p>
                        </div>

                        <div id="a2">
                            <h3>A2. About these terms</h3>
                            <p>1. When you complete your booking, you accept these Terms and any other conditions provided during the reservation process.</p>
                            <p>2. If any court or authority decides that part of these terms is unenforceable, the remaining terms will continue in full force.</p>
                            <p>3. Section A contains general terms applicable to all bookings. Sections B, C, and D contain specific terms for Accommodations, Attractions, and Transportation respectively. If there is a direct conflict, the specific section terms will govern.</p>
                        </div>

                        <div id="a3">
                            <h3>A3. About Fastnetstays.com</h3>
                            <p>1. Fastnetstays.com is incorporated in the United Republic of Tanzania (Dar es Salaam). We provide and operate the digital Platform that connects travelers with third-party Service Providers.</p>
                            <p>2. Fastnetstays.com facilitates the search, booking, and payment processing for travel services, but is not the creator, owner, or operator of the Travel Experience itself (such as the actual hotel room, safari tour, or vehicle).</p>
                        </div>

                        <div id="a4">
                            <h3>A4. Our Platform</h3>
                            <p>1. We receive information from Service Providers (photos, amenities, prices, availability). While we exercise professional diligence, we cannot warrant that all supplier data is uninterrupted or error-free. We will correct any identified inaccuracies promptly.</p>
                            <p>2. Displaying a stay or experience on our platform does not constitute an endorsement of quality or safety.</p>
                            <p>3. You must be at least 18 years old to create an account and make bookings on our platform. You are responsible for keeping your login credentials confidential.</p>
                        </div>

                        <div id="a5">
                            <h3>A5. Our values and user obligations</h3>
                            <p>1. You agree to use Fastnetstays.com for lawful, non-commercial personal bookings. You agree not to make speculative or fraudulent bookings, and to treat accommodation hosts, safari guides, and drivers with respect and courtesy.</p>
                        </div>

                        <div id="a6">
                            <h3>A6. Prices and currency display</h3>
                            <p>1. Prices are displayed clearly before you confirm your booking and include applicable statutory taxes unless specifically marked otherwise.</p>
                            <p>2. We offer transparent pricing in <strong>Tanzanian Shillings (TZS)</strong> as well as major international currencies (e.g. USD). The exact charge will reflect the currency selected during checkout.</p>
                            <p>3. Obvious pricing errors (such as an unintentional technical glitch listing a luxury villa for 1 TZS) are non-binding. We reserve the right to cancel such reservations and issue a full refund immediately upon discovery.</p>
                        </div>

                        <div id="a7">
                            <h3>A7. Payment & local mobile money</h3>
                            <p>1. <strong>Payment Processing:</strong> Fastnetstays.com facilitates payments securely through authorized payment gateways, accepting leading Tanzanian Mobile Money networks (<strong>Vodacom M-Pesa, Tigo Pesa, Airtel Money, HaloPesa</strong>). Card payments are not supported.</p>
                            <p>2. <strong>Pay Now vs. Pay at Property:</strong> Depending on the accommodation policy, you may pay in full online via Mobile Money at the time of booking, or pay directly to the property upon arrival.</p>
                            <p>3. <strong>Security:</strong> All payment transactions are encrypted and authenticated via your mobile network's secure USSD / SIM PIN prompt. If you suspect unauthorized use of your mobile wallet, notify your telecom provider and our support team immediately.</p>
                        </div>

                        <div id="a8">
                            <h3>A8. Booking policies, cancellations & no-shows</h3>
                            <p>1. Each Service Provider sets their own cancellation policies (e.g., Free Cancellation up to 48 hours before check-in, Non-Refundable, or Flexible). These rules are prominently displayed before checkout and in your booking confirmation email.</p>
                            <p>2. If you cancel within an eligible free cancellation period, your refund will be processed back to your original mobile money wallet. In case of a no-show or late cancellation, the provider's stated fees will apply.</p>
                        </div>

                        <div id="a9">
                            <h3>A9. Privacy and cookies</h3>
                            <p>1. Your privacy is paramount. Please read our <a href="<?= $this->Url->build('/privacy-policy'); ?>">Privacy Notice for Travelers</a> to understand how we collect, store, and safeguard your personal information.</p>
                        </div>

                        <div id="a10">
                            <h3>A10. Intellectual property rights & content</h3>
                            <p>1. All content, trademarks, logos, interface design, and software on Fastnetstays.com are the exclusive property of Fastnetstays.com or its licensors.</p>
                            <p>2. You may not scrape, copy, replicate, or use automated bots/AI crawlers on our platform without our prior written consent.</p>
                            <p>3. When you submit guest reviews and photos, you grant Fastnetstays.com a non-exclusive license to display them across our platform to assist other travelers.</p>
                        </div>

                        <div id="a11">
                            <h3>A11. What if something goes wrong? (Complaints & support)</h3>
                            <p>1. Our Customer Support team is available 24/7 to assist you. If you encounter any issue with your booking or stay:</p>
                            <ul class="list-disc">
                                <li>Contact our support team via the <a href="<?= $this->Url->build('/contact'); ?>">Support Page</a> or in-app live chat.</li>
                                <li>Provide your booking reference number and relevant details/photos so we can mediate with the host or provider promptly.</li>
                            </ul>
                        </div>

                        <div id="a12">
                            <h3>A12. Limitation of liability</h3>
                            <p>1. Nothing in these Terms limits our liability for gross negligence, willful misconduct, or matters that cannot be lawfully excluded under the laws of Tanzania.</p>
                            <p>2. To the maximum extent permitted by law, Fastnetstays.com is not liable for indirect losses, unexpected events beyond our control (force majeure), or acts and omissions of third-party service providers.</p>
                        </div>

                        <div id="a13">
                            <h3>A13. Applicable law and jurisdiction</h3>
                            <p>1. These Terms are governed by and construed in accordance with the <strong>laws of the United Republic of Tanzania</strong>. Any legal claim or dispute shall be submitted to the competent courts of Dar es Salaam, Tanzania.</p>
                        </div>

                        <div id="a14">
                            <h3>A14. Modifications to these terms</h3>
                            <p>1. We may update these Terms periodically. Updates take effect upon posting. Your continued use of Fastnetstays.com after updates constitutes acceptance of the modified Terms.</p>
                        </div>
                        <div class="mt-2"><a href="#section-a" class="text-sm font-semibold">Back to top</a></div>
                    </section>

                    <!-- Section B: Accommodations -->
                    <section id="section-b" class="border-t border-[#e7e7e7] pt-6 mt-8">
                        <h2>B. Accommodations (Hotels, Safari Lodges & Stays)</h2>
                        <p>1. <strong>Scope:</strong> This section governs reservations of hotel rooms, safari tented camps, beachfront villas, and holiday apartments.</p>
                        <p>2. <strong>Direct Contract:</strong> When you book an accommodation, you enter into a direct agreement with the accommodation provider.</p>
                        <p>3. <strong>Guest Responsibilities:</strong> Guests must present valid identification (National ID or Passport) upon check-in, respect property house rules, maintain good conduct, and treat the property with care.</p>
                        <p>4. <strong>Damage Policies:</strong> If any damage or loss occurs during your stay, the property provider may request reimbursement directly or through our dispute resolution channel with verified proof.</p>
                        <div class="mt-2"><a href="#section-a" class="text-sm font-semibold">Back to top</a></div>
                    </section>

                    <!-- Section C: Attractions -->
                    <section id="section-c" class="border-t border-[#e7e7e7] pt-6 mt-8">
                        <h2>C. Attractions & Safari Experiences</h2>
                        <p>1. <strong>Scope:</strong> This section covers guided wildlife safaris (e.g. Serengeti, Ngorongoro, Tarangire), Zanzibar spice and island tours, mountain trekking, and cultural day trips.</p>
                        <p>2. <strong>Operator Terms:</strong> Safari and tour operators provide licensed guides, park permit arrangements, and specified itineraries. Travelers must comply with national park safety rules and environmental guidelines.</p>
                        <p>3. <strong>Weather & Park Regulations:</strong> Safari itineraries may occasionally require adjustment due to extreme weather or park authority notices for guest safety.</p>
                        <div class="mt-2"><a href="#section-a" class="text-sm font-semibold">Back to top</a></div>
                    </section>

                    <!-- Section D: Transportation -->
                    <section id="section-d" class="border-t border-[#e7e7e7] pt-6 mt-8">
                        <h2>D. Private & Public Transportation (Airport Transfers)</h2>
                        <p>1. <strong>Scope:</strong> Covers pre-booked airport transfers (JNIA Dar es Salaam, KIA Kilimanjaro, Abeid Amani Karume Zanzibar) and intercity private driver bookings.</p>
                        <p>2. <strong>Pickup & Flight Monitoring:</strong> When booking an airport pickup, provide accurate flight details so your driver can accommodate schedule adjustments for delayed flights.</p>
                        <p>3. <strong>Cancellation:</strong> Pre-booked private transfers can typically be canceled free of charge up to 24 hours prior to pickup time.</p>
                        <div class="mt-2"><a href="#section-a" class="text-sm font-semibold">Back to top</a></div>
                    </section>

                    <!-- Dictionary -->
                    <section id="dictionary" class="border-t border-[#e7e7e7] pt-6 mt-8 mb-4">
                        <h2>Fastnetstays.com Dictionary</h2>
                        <table class="legal-table">
                            <thead>
                                <tr>
                                    <th style="width: 25%;">Term</th>
                                    <th>Definition</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Fastnetstays.com</strong></td>
                                    <td>Fastnetstays.com, an online travel and accommodations marketplace registered in Tanzania.</td>
                                </tr>
                                <tr>
                                    <td><strong>Account</strong></td>
                                    <td>The user profile created on our platform to manage bookings, wishlists, and payments.</td>
                                </tr>
                                <tr>
                                    <td><strong>Accommodation</strong></td>
                                    <td>Hotels, safari lodges, resorts, bed & breakfasts, serviced apartments, and vacation rentals listed on our Platform.</td>
                                </tr>
                                <tr>
                                    <td><strong>Attraction / Tour</strong></td>
                                    <td>Guided safaris, excursions, boat cruises, cultural tours, and experiential activities.</td>
                                </tr>
                                <tr>
                                    <td><strong>Booking</strong></td>
                                    <td>The confirmed reservation of an Accommodation, Attraction, or Transportation service.</td>
                                </tr>
                                <tr>
                                    <td><strong>Service Provider</strong></td>
                                    <td>The independent third-party owner, hotel operator, safari guide, or transport driver providing the booked service.</td>
                                </tr>
                                <tr>
                                    <td><strong>Traveler</strong></td>
                                    <td>Any individual using our platform to search for, reserve, or experience travel services.</td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="mt-4"><a href="#section-a" class="text-sm font-semibold">Back to top</a></div>
                    </section>

                </main>
            </div>
        </div>
    </div>
</section>

<!-- Include Footer -->
<?= $this->element('footer', ['skin' => 'skin-dark-footer']) ?>
