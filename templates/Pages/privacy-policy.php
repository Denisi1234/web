<?php
$this->assign('title', 'Privacy Notice for Travelers | fastnetstays.com');
?>

<!-- Include Navbar -->
<?= $this->element('navbar') ?>

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
<section class="bg-cover position-relative" style="background:url(<?= $this->Url->build('/assets/img/bg-title.jpg'); ?>)no-repeat;" data-overlay="5">
    <div class="container">
        <div class="row align-items-center justify-content-center">
            <div class="col-xl-8 col-lg-10 col-md-12">
                <div class="fpc-capstion text-center my-4">
                    <div class="fpc-captions">
                        <h1 class="xl-heading text-light font-bold">Privacy Notice for Travelers</h1>
                        <p class="text-light opacity-90">Read how we collect, process, and protect your personal data across Fastnetstays.com.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="fpc-banner"></div>
</section>
<!-- Hero Header Banner End -->

<!-- Main Legal Document Content -->
<section class="py-5 gray-simple">
    <div class="container px-3 px-md-4">
        <div class="row justify-content-center">
            <div class="col-xl-10 col-lg-11 col-md-12">
                <main class="bg-white p-4 p-sm-5 rounded-4 border border-slate-200 shadow-sm legal-content mx-auto">
                    
                    <!-- Document Sub-Header & Meta -->
                    <div class="d-flex align-items-center justify-content-between flex-wrap border-bottom border-slate-100 pb-3 mb-4">
                        <div>
                            <h2 class="text-2xl font-bold text-[#1a1a1a] !mt-0 !mb-1">Privacy & Data Protection Notice</h2>
                            <p class="text-sm text-[#595959] !mb-0">Updated August 2026</p>
                        </div>
                        <!-- Print Action Link -->
                        <div>
                            <button onclick="window.print()" class="inline-flex items-center gap-1.5 text-sm font-semibold text-[#ea580c] hover:underline cursor-pointer bg-orange-50 px-3 py-1.5 rounded-full border border-orange-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                Print Document
                            </button>
                        </div>
                    </div>

            <!-- Table of contents -->
            <div class="toc-box">
                <h3 class="text-base font-bold text-[#1a1a1a] !mt-0 !mb-3">Table of contents</h3>
                <ol class="list-decimal space-y-1">
                    <li><a href="#about-notice">About this privacy notice</a></li>
                    <li><a href="#terms-used">Terms we use in our privacy notices</a></li>
                    <li><a href="#data-we-collect">Personal data we collect and process</a>
                        <ol class="list-[upper-alpha] pl-5 mt-1 space-y-1 font-normal">
                            <li><a href="#data-you-give">Personal data you give to us</a></li>
                            <li><a href="#data-about-others">Personal data you give us about others</a></li>
                            <li><a href="#data-automatic">Personal data we collect automatically</a></li>
                            <li><a href="#data-other-sources">Personal data we receive from other sources</a></li>
                        </ol>
                    </li>
                    <li><a href="#purposes-processing">Purposes of collecting and processing your personal data</a></li>
                    <li><a href="#legal-bases">Legal bases for personal data processing</a></li>
                    <li><a href="#sharing-third-parties">Why we share personal data with third parties</a></li>
                    <li><a href="#ground-transport">Ground transportation & airport transfers</a></li>
                    <li><a href="#protect-data">How we protect personal data</a></li>
                    <li><a href="#cookies">How we use cookies & other tracking technologies</a></li>
                    <li><a href="#ai-automation">How we use artificial intelligence and make automated decisions</a></li>
                    <li><a href="#minors">How we treat personal data belonging to minors</a></li>
                    <li><a href="#your-rights">Your rights</a></li>
                    <li><a href="#our-company">Our company and how we comply with privacy laws</a></li>
                </ol>
            </div>

            <!-- 1. About this privacy notice -->
            <section id="about-notice">
                <h2>About this privacy notice</h2>
                <p>We are <strong>Fastnetstays.com</strong> and this privacy notice is intended for travelers using or considering using our products and services.</p>
                <p>You place your trust in us by using Fastnetstays.com services, and we value that trust.</p>
                <p>This privacy notice describes how we collect and process your personal data when you visit our website, use our mobile application, or book a stay or travel service through us. It details your rights related to your personal data and how you can contact us.</p>
                <p>Fastnetstays.com offers online travel-related services through its website and mobile app across Tanzania and East Africa. This privacy notice applies to all traveler information processed through our platform.</p>
                <div class="mt-2"><a href="#about-notice" class="text-sm font-semibold">Back to top</a></div>
            </section>

            <!-- 2. Terms we use in our privacy notices -->
            <section id="terms-used" class="border-t border-[#e7e7e7] pt-6 mt-8">
                <h2>Terms we use in our privacy notices</h2>
                <p>Throughout our privacy notices, Fastnetstays.com uses particular terms that have a specific meaning:</p>
                
                <table class="legal-table">
                    <thead>
                        <tr>
                            <th style="width: 25%;">Term</th>
                            <th>Meaning</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Fastnetstays.com</strong></td>
                            <td>When we refer to “we,” “us,” or “our,” we mean Fastnetstays.com incorporated in the United Republic of Tanzania.</td>
                        </tr>
                        <tr>
                            <td><strong>Platform</strong></td>
                            <td>Any websites, mobile apps, or other technologies that we provide to interact with travelers and partners.</td>
                        </tr>
                        <tr>
                            <td><strong>Traveler</strong></td>
                            <td>Anyone who uses or is considering using any of our travel products and services through our platform.</td>
                        </tr>
                        <tr>
                            <td><strong>Trip</strong></td>
                            <td>One or more travel products and services (stays, tours, transfers) a traveler can select via our platform.</td>
                        </tr>
                        <tr>
                            <td><strong>Trip provider</strong></td>
                            <td>The third-party provider of an accommodation (e.g. hotel, lodge, villa, apartment), an attraction/tour, or ground transportation.</td>
                        </tr>
                        <tr>
                            <td><strong>Trip reservation</strong></td>
                            <td>The online reservation, order, or purchase in connection with a stay or trip service.</td>
                        </tr>
                    </tbody>
                </table>
                <div class="mt-2"><a href="#about-notice" class="text-sm font-semibold">Back to top</a></div>
            </section>

            <!-- 3. Personal data we collect and process -->
            <section id="data-we-collect" class="border-t border-[#e7e7e7] pt-6 mt-8">
                <h2>Personal data we collect and process</h2>

                <div id="data-you-give">
                    <h3>Personal data you give to us</h3>
                    <p>When you make a reservation, we ask for your name and email address. Depending on the nature of your stay, we may also ask for your home address, telephone number, payment details (credit/debit card or mobile money details such as M-Pesa, Airtel Money, or Tigo Pesa), current location, and any preferences you specify (such as dietary or accessibility requests).</p>
                    <p>When you contact our customer support team via chat, email, or phone, we collect details of those interactions, including your name, booking reference number, and call/chat metadata.</p>
                    <p>If you create an account on Fastnetstays.com, we store your profile settings, saved properties, wishlists, travel preferences, and submitted guest reviews.</p>
                </div>

                <div id="data-about-others">
                    <h3>Personal data you give us about others</h3>
                    <p>You may provide information about other guests when booking a trip for family, companions, or colleagues. You are responsible for ensuring that they understand how their personal data is processed as detailed in this notice.</p>
                </div>

                <div id="data-automatic">
                    <h3>Personal data we collect automatically</h3>
                    <p>We automatically collect certain information when you browse our platform, including:</p>
                    <ul class="list-disc">
                        <li>IP address (from which we derive country/region)</li>
                        <li>Usage dates, times, search queries, and page clicks</li>
                        <li>Device details (operating system, browser type, language settings)</li>
                        <li>Mobile app diagnostic and performance data</li>
                    </ul>
                </div>

                <div id="data-other-sources">
                    <h3>Personal data and information we receive from other sources</h3>
                    <p>We integrate with secure payment gateways and local mobile network operators to confirm electronic transactions. Trip providers (hotel and lodge managers) may also share updates regarding booking confirmations, special guest requests, or dispute resolutions with us.</p>
                </div>
                <div class="mt-2"><a href="#about-notice" class="text-sm font-semibold">Back to top</a></div>
            </section>

            <!-- 4. Purposes of collecting and processing your personal data -->
            <section id="purposes-processing" class="border-t border-[#e7e7e7] pt-6 mt-8">
                <h2>Purposes of collecting and processing your personal data</h2>
                <p>We use your personal data for the following essential purposes:</p>
                <ul class="list-disc">
                    <li><strong>Trip & Payments Management:</strong> To finalize, administer, and confirm your reservations with hotels, safari lodges, and tour operators, and to process payments securely.</li>
                    <li><strong>Customer Support:</strong> To assist you 24/7 with inquiries, booking changes, cancellations, and on-trip assistance across Tanzania.</li>
                    <li><strong>Safety, Security & Fraud Prevention:</strong> To verify accounts, detect payment fraud, and protect guests and hosts from unauthorized activity.</li>
                    <li><strong>Platform Personalization:</strong> To optimize search results, display local currency (TZS / USD), and highlight stays matching your travel style.</li>
                    <li><strong>Marketing & Communications:</strong> To send booking confirmations, safety alerts, and optional promotional offers (which you can unsubscribe from at any time).</li>
                    <li><strong>Legal & Tax Compliance:</strong> To comply with Tanzanian statutory requirements, accounting rules, and tourism regulations.</li>
                </ul>
                <div class="mt-2"><a href="#about-notice" class="text-sm font-semibold">Back to top</a></div>
            </section>

            <!-- 5. Legal bases for personal data processing -->
            <section id="legal-bases" class="border-t border-[#e7e7e7] pt-6 mt-8">
                <h2>Legal bases for personal data processing</h2>
                <p>Fastnetstays.com processes your personal data under the following legal grounds:</p>
                <ul class="list-disc">
                    <li><strong>Performance of a Contract:</strong> Processing necessary to finalize and manage your accommodation and experience reservations.</li>
                    <li><strong>Legitimate Interests:</strong> Improving our platform, preventing online fraud, optimizing guest services, and maintaining cybersecurity.</li>
                    <li><strong>Legal Obligations:</strong> Compliance with applicable laws, tax reporting, and lawful government requests.</li>
                    <li><strong>Consent:</strong> Where explicitly requested, such as for direct marketing communications and optional tracking cookies.</li>
                </ul>
                <div class="mt-2"><a href="#about-notice" class="text-sm font-semibold">Back to top</a></div>
            </section>

            <!-- 6. Why we share personal data with third parties -->
            <section id="sharing-third-parties" class="border-t border-[#e7e7e7] pt-6 mt-8">
                <h2>Why we share personal data with third parties</h2>
                <p>We only share your personal data with third parties when strictly necessary:</p>
                <ul class="list-disc">
                    <li><strong>The Trip Provider:</strong> We share your name, guest count, contact details, and check-in specifications with the hotel, lodge, or tour host you booked with so they can prepare for your stay.</li>
                    <li><strong>Payment Service Providers:</strong> Encrypted card and mobile payment processors to handle secure checkout and refunds.</li>
                    <li><strong>Customer Support & Technical Infrastructure:</strong> Cloud hosting, SMS notification gateways, and customer support tooling.</li>
                    <li><strong>Competent Authorities:</strong> When required by Tanzanian law, court orders, or law enforcement investigations.</li>
                </ul>
                <p>We never sell your personal data to third parties.</p>
                <div class="mt-2"><a href="#about-notice" class="text-sm font-semibold">Back to top</a></div>
            </section>

            <!-- 7. Ground transportation -->
            <section id="ground-transport" class="border-t border-[#e7e7e7] pt-6 mt-8">
                <h2>Ground transportation & airport transfers</h2>
                <p>When you book airport transfers (e.g. from Julius Nyerere International Airport, Kilimanjaro International Airport, or Abeid Amani Karume International Airport in Zanzibar) or private chauffeur services through our platform, we share your name, flight details, pickup location, and passenger count with the verified transport provider.</p>
                <div class="mt-2"><a href="#about-notice" class="text-sm font-semibold">Back to top</a></div>
            </section>

            <!-- 8. How we protect personal data -->
            <section id="protect-data" class="border-t border-[#e7e7e7] pt-6 mt-8">
                <h2>How we protect personal data</h2>
                <p>We implement strict security practices to keep your data safe, including:</p>
                <ul class="list-disc">
                    <li>SSL/TLS encryption for all data in transit across our web and mobile applications.</li>
                    <li>Strict access controls and multi-factor authentication for authorized personnel.</li>
                    <li>Routine security audits, vulnerability monitoring, and firewall protections.</li>
                    <li>Data retention schedules to securely delete or anonymize data when no longer needed.</li>
                </ul>
                <div class="mt-2"><a href="#about-notice" class="text-sm font-semibold">Back to top</a></div>
            </section>

            <!-- 9. How we use cookies -->
            <section id="cookies" class="border-t border-[#e7e7e7] pt-6 mt-8">
                <h2>How we use cookies & other tracking technologies</h2>
                <p>We use cookies and local storage to ensure our platform operates smoothly:</p>
                <ul class="list-disc">
                    <li><strong>Functional Cookies:</strong> Essential for signing in, maintaining your active session, remembering your chosen currency (TZS/USD), and language settings.</li>
                    <li><strong>Analytical Cookies:</strong> Help us measure site traffic, page speed, and platform performance.</li>
                    <li><strong>Marketing Cookies:</strong> Allow relevant recommendations and promotions across digital channels. You can adjust your cookie settings at any time in your browser.</li>
                </ul>
                <div class="mt-2"><a href="#about-notice" class="text-sm font-semibold">Back to top</a></div>
            </section>

            <!-- 10. AI and automated decisions -->
            <section id="ai-automation" class="border-t border-[#e7e7e7] pt-6 mt-8">
                <h2>How we use artificial intelligence and make automated decisions</h2>
                <p>We utilize automated algorithms to power search result rankings, detect fraudulent transactions, and assist our customer support team in categorizing inquiries quickly. Critical decisions impacting bookings or user accounts always involve human review.</p>
                <div class="mt-2"><a href="#about-notice" class="text-sm font-semibold">Back to top</a></div>
            </section>

            <!-- 11. Minors -->
            <section id="minors" class="border-t border-[#e7e7e7] pt-6 mt-8">
                <h2>How we treat personal data belonging to minors</h2>
                <p>Our platform is not intended for individuals under 18 years of age. We only collect details of children when provided directly by a parent or legal guardian as part of a family accommodation reservation.</p>
                <div class="mt-2"><a href="#about-notice" class="text-sm font-semibold">Back to top</a></div>
            </section>

            <!-- 12. Your rights -->
            <section id="your-rights" class="border-t border-[#e7e7e7] pt-6 mt-8">
                <h2>Your rights</h2>
                <p>You have full control over your personal data:</p>
                <ul class="list-disc">
                    <li><strong>Access:</strong> You can request a copy of the personal data we hold about you.</li>
                    <li><strong>Correction:</strong> You can update or correct your profile details anytime via your account settings.</li>
                    <li><strong>Erasure:</strong> You can request the deletion of your account and personal data, subject to statutory retention obligations.</li>
                    <li><strong>Objection & Withdrawal:</strong> You can object to marketing communications or withdraw consent at any time.</li>
                </ul>
                <p>To exercise your rights, contact our Privacy Team at <a href="mailto:privacy@fastnetstays.com">privacy@fastnetstays.com</a>.</p>
                <div class="mt-2"><a href="#about-notice" class="text-sm font-semibold">Back to top</a></div>
            </section>

            <!-- 13. Our company and contact details -->
            <section id="our-company" class="border-t border-[#e7e7e7] pt-6 mt-8 mb-4">
                <h2>Our company and how we comply with privacy laws</h2>
                <p><strong>Data Controller:</strong> Fastnetstays.com</p>
                <p><strong>Registered Jurisdiction:</strong> United Republic of Tanzania (Dar es Salaam, Tanzania)</p>
                <p><strong>Privacy Inquiries:</strong> <a href="mailto:privacy@fastnetstays.com">privacy@fastnetstays.com</a></p>
                <p><strong>Customer Support:</strong> Available 24/7 via the in-app Help Center and <a href="<?= $this->Url->build('/contact'); ?>">Support Page</a>.</p>
                <div class="mt-4"><a href="#about-notice" class="text-sm font-semibold">Back to top</a></div>
            </section>

        </main>
            </div>
        </div>
    </div>
</section>

<!-- Include Footer -->
<?= $this->element('footer', ['skin' => 'skin-dark-footer']) ?>