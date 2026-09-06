<?php
$this->assign('title', 'How We Work | fastnetstays.com');
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
        font-size: 24px;
        font-weight: 700;
        color: #1a1a1a;
        margin-top: 36px;
        margin-bottom: 16px;
    }
    .legal-content h3 {
        font-size: 18px;
        font-weight: 700;
        color: #1a1a1a;
        margin-top: 28px;
        margin-bottom: 12px;
    }
    .legal-content h4 {
        font-size: 15px;
        font-weight: 700;
        color: #1a1a1a;
        margin-top: 20px;
        margin-bottom: 8px;
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
</style>

<!-- Hero Header Banner Start -->
<section class="bg-cover position-relative" style="background:url(<?= $this->Url->build('/assets/img/bg-title.jpg'); ?>)no-repeat;" data-overlay="5">
    <div class="container">
        <div class="row align-items-center justify-content-center">
            <div class="col-xl-8 col-lg-10 col-md-12">
                <div class="fpc-capstion text-center my-4">
                    <div class="fpc-captions">
                        <h1 class="xl-heading text-light font-bold">How FastNetStays Works</h1>
                        <p class="text-light opacity-90">Discover how we rank stays, partner with local hosts, calculate pricing, and handle guest reviews across Tanzania.</p>
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
                            <h2 class="text-2xl font-bold text-[#1a1a1a] !mt-0 !mb-1">How We Work Overview</h2>
                            <p class="text-sm text-[#595959] !mb-0">Updated May 31, 2025</p>
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
                            <li><a href="#sec-1">Accommodations</a>
                                <ol class="list-[upper-alpha] pl-5 mt-1 space-y-1 font-normal">
                                    <li><a href="#sec-1A">Definitions and who we are</a></li>
                                    <li><a href="#sec-1B">How does our service work?</a></li>
                                    <li><a href="#sec-1C">Who do we work with?</a></li>
                                    <li><a href="#sec-1D">How do we make money?</a></li>
                                    <li><a href="#sec-1E">Our recommendation systems</a></li>
                                    <li><a href="#sec-1F">Reviews</a></li>
                                    <li><a href="#sec-1G">Prices</a></li>
                                    <li><a href="#sec-1H">Payments</a></li>
                                    <li><a href="#sec-1I">Host type</a></li>
                                    <li><a href="#sec-1J">Star ratings, review scores, and quality ratings</a></li>
                                    <li><a href="#sec-1K">Help and advice – if the unexpected happens</a></li>
                                    <li><a href="#sec-1L">Overbooking</a></li>
                                </ol>
                            </li>
                            <li><a href="#sec-2">Attractions & Experiences</a></li>
                            <li><a href="#sec-3">Private and public transportation</a></li>
                        </ol>
                    </div>

                    <!-- 1. Accommodations -->
                    <section id="sec-1">
                        <h2>1. Accommodations</h2>

                        <div id="sec-1A">
                            <h3>1A. Definitions and who we are</h3>
                            <p>Some of the words here have specific meanings, so check out the “Fastnetstays dictionary” in our <a href="<?= $this->Url->build('/terms-of-service'); ?>">Terms of Service</a>.</p>
                            <p>When you book an Accommodation, Fastnetstays.com provides and is responsible for the Platform but not the Travel Experience itself (section 1B). Fastnetstays.com is a company incorporated under the laws of the United Republic of Tanzania (registered address: Dar es Salaam, Tanzania).</p>
                        </div>

                        <div id="sec-1B">
                            <h3>1B. How does our service work?</h3>
                            <p>We make it easy for you to compare bookings from many hotels, safari lodges, beachfront villas, hosts, and other Service Providers across Tanzania.</p>
                            <p>When you make a booking on our Platform, you enter into a contract with the Service Provider (unless otherwise stated).</p>
                            <p>The information on our Platform is based on what Service Providers tell us. We do our best to keep things up to date at all times, but realistically, it can take a few hours to update, for example, text descriptions and lists of the facilities that Accommodations provide.</p>
                        </div>

                        <div id="sec-1C">
                            <h3>1C. Who do we work with?</h3>
                            <p>Only Service Providers with a contractual relationship with us will be displayed on our Platform. They may also offer Travel Experiences outside our Platform.</p>
                            <p>We don’t own any Accommodations ourselves – each Service Provider is a separate company or host that has agreed to work with us in a certain way.</p>
                            <p>Our Platform shows you the Accommodations you can book through us across Tanzania and East Africa, and our search results page tells you how many of them might be right for you based on what you’ve told us.</p>
                        </div>

                        <div id="sec-1D">
                            <h3>1D. How do we make money?</h3>
                            <p>We don’t buy or (re)sell any products or services. Once your stay is finished, the Service Provider just pays us a commission. A badge with a thumbs-up icon indicates that the property is part of our Preferred Partner Program – they pay us a higher commission if you make a booking.</p>
                            <p>If an Accommodation in your search results has a badge that says “Ad,” it means that the Service Provider has paid for it to appear there.</p>
                        </div>

                        <div id="sec-1E">
                            <h3>1E. Our recommendation systems</h3>
                            <h4>How Fastnetstays.com uses recommendation systems</h4>
                            <p>All great properties deserve to be discovered. That’s why we use “recommendation” systems to select, display, and/or rank information on our Platform in a way that’ll help you discover properties we think you’ll like. For example, on the “Stays” landing page, you’ll find several recommendation systems, including:</p>
                            <ul class="list-disc">
                                <li><strong>Trending destinations:</strong> Destinations you may want to travel to (such as Zanzibar, Serengeti, Arusha, Dar es Salaam) based on bookings made by other travelers whose searches were similar to yours.</li>
                                <li><strong>Homes guests love:</strong> Home properties, beachfront villas, and safari lodges with high review scores.</li>
                                <li><strong>Looking for the perfect stay?</strong> Properties (as opposed to destinations) that you may want to stay at based on bookings made by other guests whose searches were similar to yours.</li>
                            </ul>
                            <p>Our search results are also a recommendation system. In fact, they’re the recommendation system that our customers use the most, so be sure to check out “Our default ranking and sorting options” section.</p>
                            <p>All the recommendation systems we use provide recommendations based on one or more of the following factors:</p>
                            <ul class="list-disc">
                                <li>What you tell us when you are looking to book a Travel Experience, such as destination, dates, number of guests, etc.</li>
                                <li>The information we’ve gathered based on your previous interactions with our Platform, such as your past searches, existing reservations, etc., unless you opted out of the personalized recommendations.</li>
                                <li>Any other information on how you currently interact with our Platform, including the country or region where you are while browsing.</li>
                                <li>An Accommodation’s performance on our Platform:
                                    <ul class="list-circle pl-6 mt-1">
                                        <li>its click-through rate (how many people click on it);</li>
                                        <li>its gross bookings (how many bookings are made with that Accommodation);</li>
                                        <li>its net bookings (how many bookings are made with that Accommodation minus how many are canceled).</li>
                                    </ul>
                                </li>
                                <li>Information about an Accommodation’s availability, pricing scores, review scores, etc.</li>
                            </ul>
                            <p>To make it as easy as possible for you to find and book an Accommodation you like, each factor can be more or less important in different cases, depending on what we think is most likely to produce a list of properties you may want to book.</p>

                            <h4>Our default ranking and sorting options</h4>
                            <p>Our search results are also a recommendation system. They show all the Accommodations (hotels, apartments, safari lodges, etc.) that match your search. If you like, you can use filters to narrow down your results.</p>
                            <p>To check all the booking options an Accommodation offers, just select it.</p>
                            <p>When you first get your search results, they’ll be sorted (“ordered”) by “Our top picks” (called “Popularity” on our app):</p>
                            <p>To appear high up on the page, an Accommodation needs to do well in each of these three areas:</p>
                            <ul class="list-disc">
                                <li>Click-through rate: How many people click on it;</li>
                                <li>Gross bookings: How many bookings are made with that Accommodation;</li>
                                <li>Net bookings: How many bookings are made with that Accommodation minus how many are canceled.</li>
                            </ul>
                            <p>Those numbers depend on many factors, including review scores, availability, policies, pricing, quality of content (e.g. photos), and other features.</p>
                            <p>Other things can also influence an Accommodation’s ranking – for example, how much commission they pay us on bookings, how quickly they usually pay it, whether they’re part of our Preferred Partner Program, and whether we organize their payments.</p>
                            <p>Any information we've gathered based on how you interact with our Platform (including what you tell us) will also be a factor unless you opted out of personalized recommendations.</p>
                            <p>If an Accommodation in your search results has a badge that says “Ad,” it means that the Service Provider has paid for it to appear there.</p>
                            <p>If you would prefer us not to order your search results in our default way, you can sort them in other ways, such as:</p>
                            <ul class="list-disc">
                                <li><strong>Homes & villas first:</strong> Homes, private villas, and safari lodges appear higher than hotels and other types of Accommodation.</li>
                                <li><strong>Price (lowest first):</strong> Accommodations with lower prices (in TZS or USD) appear higher up.</li>
                                <li><strong>Property rating (high to low):</strong> Accommodations with more stars* and/or higher quality ratings* appear higher.</li>
                                <li><strong>Property rating (low to high):</strong> Accommodations with fewer stars and/or lower quality ratings appear higher.</li>
                                <li><strong>Top reviewed (called “Best reviewed” in our app):</strong> Accommodations with higher review scores* appear higher. If you find any instances where this isn’t the case, it’s because we also factor in reliability (i.e. number of reviews).</li>
                                <li><strong>Distance from (X):</strong> Accommodations that are closer to X (e.g. city center, airport, national park gate, beach) appear higher on the page.</li>
                            </ul>
                            <p class="text-xs text-[#595959] italic">* Check out “Star ratings, review scores, and quality ratings” (section 1J).</p>

                            <h4>Personalized recommendations</h4>
                            <p>Some of our recommendation systems go beyond your search parameters and filters and make personalized recommendations based on how you have interacted with Fastnetstays.com systems. You can manage your preferences at any time in your account settings or footer preferences.</p>
                        </div>

                        <div id="sec-1F">
                            <h3>1F. Reviews</h3>
                            <p>Each review score is from 1 to 10. We use a weighted review system, which means that the more recent the review, the bigger the impact on the total review score calculation.</p>
                            <p>In addition, guests can also give separate “subscores” for specific Travel Experience aspects such as: location, cleanliness, staff, comfort, facilities, value, and free Wifi. Guests submit their subscores and overall scores independently, so there’s no direct link between them.</p>
                            <p>You can review an Accommodation that you booked through our Platform if you stayed there or arrived at the Accommodation but didn’t actually stay there. To edit a review you already submitted, contact our Customer Service team.</p>
                            <p>We have automated systems and team verification that specialize in detecting fake reviews submitted to our Platform. If we find any, we delete them immediately.</p>
                            <p>To make sure reviews are relevant, we only accept reviews that are submitted within three months of checking out, and we may stop showing reviews once they’re 36 months old or if the Accommodation has a change of ownership.</p>
                            <p>An Accommodation host may choose to reply to a review.</p>
                            <p>If you would prefer us not to order reviews our default way, you can sort them based on other factors: <em>Newest first, Oldest first, Highest scores, or Lowest scores</em>.</p>
                        </div>

                        <div id="sec-1G">
                            <h3>1G. Prices</h3>
                            <p>The rates displayed on our Platform are set directly by the Service Providers in Tanzania. We may finance rewards or other benefits out of our own pocket.</p>
                            <p>When you make a booking, you agree to pay the cost of the Travel Experience itself and any other taxes, statutory tourism levies, and fees that may apply (e.g. conservation entry fees or extras). The price description indicates whether all taxes and fees are included or excluded.</p>
                            <p>You’ll be able to find complete information about the price before confirming your booking.</p>
                        </div>

                        <div id="sec-1H">
                            <h3>1H. Payments</h3>
                            <p>There are three ways you might pay for your Booking:</p>
                            <ol class="list-decimal">
                                <li>The Service Provider charges you directly at the Accommodation in Tanzanian Shillings (TZS) or USD.</li>
                                <li>The Service Provider charges you in advance via secure international card or local East African mobile money (M-Pesa, Airtel Money, Tigo Pesa).</li>
                                <li>Fastnetstays.com organizes your payment to the Service Provider in advance through our encrypted checkout gateway.</li>
                            </ol>
                            <p>If you cancel a booking or don’t show up, any cancellation/no-show fee and any refund will depend on the Service Provider’s cancellation/no-show policy.</p>
                        </div>

                        <div id="sec-1I">
                            <h3>1I. Host type</h3>
                            <p>We ask Service Providers across Tanzania to tell us if they’re acting as a “private host” (individual owner of a villa or apartment) or as a “professional host” (registered hotel, lodge, or hospitality enterprise).</p>
                        </div>

                        <div id="sec-1J">
                            <h3>1J. Star ratings, review scores, and quality ratings</h3>
                            <p><strong>Star ratings</strong> look like 1–5 yellow stars next to the property’s name. They are assigned by the Service Providers or national tourism classification authorities.</p>
                            <p><strong>Review scores</strong> look like a blue square with a white number from 1 to 10 submitted by verified guests.</p>
                            <p><strong>Quality ratings</strong> look like 1–5 yellow squares next to the property’s name, calculated automatically based on verified amenities, photos, cleanliness subscores, and guest ratings.</p>
                        </div>

                        <div id="sec-1K">
                            <h3>1K. Help and advice – if the unexpected happens</h3>
                            <p>If you have any questions or something doesn’t go according to plan, be sure to contact us. You can do this by accessing your booking, our mobile app, or our 24/7 Support Center. We handle complaints as soon as possible, treating urgent on-trip inquiries with top priority.</p>
                            <p>Please provide your booking reference number, contact details, and any supporting documents (photos, receipts) when reaching out.</p>
                        </div>

                        <div id="sec-1L">
                            <h3>1L. Overbooking</h3>
                            <p>Once your booking is confirmed, your Service Provider is required to honor it.</p>
                            <p>If the Service Provider is overbooked, they are responsible for finding a solution immediately with our assistance. If a suitable alternative cannot be arranged:</p>
                            <ul class="list-disc">
                                <li>You can cancel your booking at no cost with a full refund of anything you’ve paid.</li>
                                <li>We will help you find alternative Accommodation in a similar category in the same area.</li>
                            </ul>
                        </div>
                        <div class="mt-4"><a href="#sec-1" class="text-sm font-semibold">Back to top</a></div>
                    </section>

                    <!-- 2. Attractions & Experiences -->
                    <section id="sec-2" class="border-t border-[#e7e7e7] pt-8 mt-12">
                        <h2>2. Attractions & Experiences</h2>

                        <div id="sec-2A">
                            <h3>2A. Definitions and who we are</h3>
                            <p>When you book an Attraction or Guided Safari Tour, Fastnetstays.com provides and is responsible for the Platform connecting you to trusted local tour operators in Tanzania.</p>
                        </div>

                        <div id="sec-2B">
                            <h3>2B. How does our service work?</h3>
                            <p>We provide a marketplace for you to find and book excursions, national park day trips, spice tours, diving, and cultural experiences across Tanzania.</p>
                        </div>

                        <div id="sec-2C">
                            <h3>2C. Who do we work with?</h3>
                            <p>Every tour operator and guide on our Platform is a licensed, verified Tanzanian operator.</p>
                        </div>

                        <div id="sec-2D">
                            <h3>2D. How do we make money?</h3>
                            <p>We don’t buy or resell attraction tickets. When you make a booking, the operator pays us a platform commission. We don’t charge hidden guest fees.</p>
                        </div>

                        <div id="sec-2E">
                            <h3>2E. Payments & Support</h3>
                            <p>Payments are processed securely online in TZS or USD. If an unexpected cancellation occurs due to weather or park conditions, full refund or rescheduling options are provided in accordance with the operator’s policy.</p>
                        </div>
                        <div class="mt-4"><a href="#sec-1" class="text-sm font-semibold">Back to top</a></div>
                    </section>

                    <!-- 3. Private and public transportation -->
                    <section id="sec-3" class="border-t border-[#e7e7e7] pt-8 mt-12 mb-4">
                        <h2>3. Private and public transportation</h2>

                        <div id="sec-3A">
                            <h3>3A. Definitions and who we are</h3>
                            <p>When you arrange an airport transfer or intercity chauffeur across Tanzania (e.g. Kilimanjaro Airport to Arusha, Julius Nyerere Airport to Dar es Salaam, or Zanzibar transfers), Fastnetstays.com provides and manages the booking platform.</p>
                        </div>

                        <div id="sec-3B">
                            <h3>3B. How does our service work?</h3>
                            <p>We connect you with vetted, professional drivers and transfer services tailored to your party size, luggage count, and pickup schedule.</p>
                        </div>

                        <div id="sec-3C">
                            <h3>3C. Payments and Advice</h3>
                            <p>All transfer rates are transparent and agreed upon during booking, inclusive of tolls and driver fees. Our 24/7 support is available for any journey modifications or flight delay adjustments.</p>
                        </div>
                        <div class="mt-4"><a href="#sec-1" class="text-sm font-semibold">Back to top</a></div>
                    </section>

                </main>
            </div>
        </div>
    </div>
</section>

<!-- Include Footer -->
<?= $this->element('footer', ['skin' => 'skin-dark-footer']) ?>
