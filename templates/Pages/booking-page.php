<?php
/**
 * fastnetstays.com - Trivago Book & Go Checkout Experience
 * Pixel-perfect match to Trivago Book & Go Checkout Specification
 */

$propId = (int)($queryParams['property_id'] ?? ($property['id'] ?? 0));
$roomId = (int)($queryParams['room_id'] ?? ($room['id'] ?? 51));
$propTitle = $property['name'] ?? '';
$propCity = $property['city'] ?? '';
$propArea = $property['area'] ?? '';
$propCountry = $property['country'] ?? '';
$propStars = !empty($property['star_rating']) ? max(1, min(5, (int)$property['star_rating'])) : 0;

$roomTitle = $room['name'] ?? ($room['room_number'] ?? '');
$bed = $room['bed_configuration'] ?? '';
$adults = max(1, (int)($queryParams['adults'] ?? ($room['max_adults'] ?? 2)));
$children = (int)($queryParams['children'] ?? ($room['max_children'] ?? 0));
$guests = $adults + $children;
$rooms = max(1, (int)($queryParams['rooms'] ?? 1));

$checkIn = $queryParams['checkIn'] ?? date('Y-m-d', strtotime('+1 day'));
$checkOut = $queryParams['checkOut'] ?? date('Y-m-d', strtotime('+10 days'));
$nights = max(1, (int)round((strtotime($checkOut) - strtotime($checkIn)) / 86400));

$pricePerNight = (float)($room['price'] ?? ($room['customer_price'] ?? ($calculation['price_per_night'] ?? 0)));

// Authoritative price calculations — from backend POST /bookings/calculate via BookingQuoteService (no in-memory fallback)
if (!empty($calculation) && !empty($calculation['total_amount'])) {
    $grandTotal = (float)$calculation['total_amount'];
    $subtotal = !empty($calculation['subtotal']) ? (float)$calculation['subtotal'] : ($pricePerNight * $nights * $rooms);
    $taxFee = !empty($calculation['taxes']) ? (float)$calculation['taxes'] : ($grandTotal - $subtotal);
} else {
    // No authoritative quote — show unavailable state, do not estimate
    $subtotal = 0;
    $taxFee = 0;
    $grandTotal = 0;
}
$avgPerNight = $nights > 0 ? round($subtotal / ($nights * $rooms)) : $pricePerNight;

// Property Photo
$img = $property['image_url'] ?? '';
if (!empty($room['photos'])) {
    $photos = is_string($room['photos']) ? json_decode($room['photos'], true) : $room['photos'];
    if (is_array($photos) && !empty($photos[0])) {
        $img = $photos[0];
    }
}

// Autofill details
$prefillFirstName = $userProfile['first_name'] ?? '';
$prefillLastName = $userProfile['last_name'] ?? '';
if (empty($prefillFirstName) && !empty($userProfile['name'])) {
    $parts = explode(' ', trim($userProfile['name']), 2);
    $prefillFirstName = $parts[0] ?? '';
    $prefillLastName = $parts[1] ?? '';
}
$prefillEmail = $userProfile['email'] ?? '';
$prefillPhone = $userProfile['phone'] ?? '';

$this->assign('title', 'fastnetstays Book&Go - ' . $propTitle);
?>
<?= $this->Html->css('/assets/css/google-travel-layout.css') ?>
<?= $this->Html->css('/assets/css/google-travel-home.css') ?>
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
    <li class="breadcrumb-item active" aria-current="page" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><span itemprop="name">Booking</span><meta itemprop="position" content="2"></li>
  </ol>
</nav>
<main id="main-content" style="background:#f8f9fa;min-height:85vh;" role="main">

<!-- Trivago Checkout CSS -->
<link rel="stylesheet" href="<?= $this->Url->build('/assets/css/trivago-checkout.css') ?>?v=<?= time() ?>">

<!-- Focused Trivago Checkout Navbar -->
<header class="trivago-checkout-navbar">
    <div class="container d-flex align-items-center justify-content-between">
        <a href="<?= $this->Url->build('/') ?>" class="trivago-checkout-brand">
            <span class="trivago-checkout-logo-main">
                <span class="c-blue">fast</span><span class="c-red">net</span><span class="c-orange">stays</span>
            </span>
            <span class="trivago-checkout-bookgo">Book&amp;Go</span>
        </a>
    </div>
</header>

<!-- Main Checkout Body -->
<main class="trivago-checkout-body">
    <div class="container">
		<?php if (!empty($quoteError)): ?>
			<div class="alert alert-danger" role="alert">
				<strong>Room unavailable.</strong> <?= h($quoteError) ?> Please return to the property and choose another room.
			</div>
		<?php endif; ?>

        <!-- Stepper Progress Bar -->
        <div class="trivago-checkout-stepper-row">
            <a href="<?= $this->Url->build('/hotel-detail/' . $propId . '?' . http_build_query($queryParams)) ?>" class="trivago-stepper-back-btn">
                <i class="fa-solid fa-arrow-left"></i> Back
            </a>

            <div class="trivago-stepper-track">
                <!-- Step 1: Search (Completed) -->
                <div class="trivago-step-item completed">
                    <div class="trivago-step-circle">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <span>Search</span>
                </div>

                <div class="trivago-step-line completed"></div>

                <!-- Step 2: Book (Active) -->
                <div class="trivago-step-item active">
                    <div class="trivago-step-circle">2</div>
                    <span>Book</span>
                </div>

                <div class="trivago-step-line"></div>

                <!-- Step 3: Done (Upcoming) -->
                <div class="trivago-step-item upcoming">
                    <div class="trivago-step-circle">3</div>
                    <span>Done</span>
                </div>
            </div>
        </div>

        <!-- Checkout Form Wrap -->
        <form id="trivagoCheckoutForm" action="<?= $this->Url->build('/bookingpage-02') ?>" method="POST">
            <input type="hidden" name="_csrfToken" value="<?= $this->request->getAttribute('csrfToken') ?>">
            <input type="hidden" name="property_id" value="<?= h($propId) ?>">
            <input type="hidden" name="room_id" value="<?= h($roomId) ?>">
            <input type="hidden" name="check_in" value="<?= h($checkIn) ?>">
            <input type="hidden" name="check_out" value="<?= h($checkOut) ?>">
            <input type="hidden" name="guests" value="<?= h($guests) ?>">
            <input type="hidden" name="rooms" value="<?= h($rooms) ?>">
            <input type="hidden" name="quote_id" value="<?= h($quote['quote_id'] ?? '') ?>">

            <!-- Mobile Property & Price Summary Card (Visible on Mobile & Tablet only < 992px) -->
            <div class="trivago-mobile-summary d-lg-none mb-3">
                <div class="trivago-card p-3 mb-0">
                    <div class="d-flex align-items-center gap-3">
                            <?php if ($img !== ''): ?><img src="<?= (str_starts_with($img, 'http')) ? h($img) : $this->Url->build('/' . h($img)) ?>" alt="<?= h($propTitle) ?>" class="trivago-mobile-summary-thumb"><?php else: ?><div class="trivago-mobile-summary-thumb trivago-summary-image-empty" role="status">No photo</div><?php endif; ?>
                        <div class="flex-grow-1 min-w-0">
                            <h5 class="fw-bold text-dark text-truncate mb-0" style="font-size: 15px;"><?= h($propTitle) ?></h5>
                            <div class="text-warning text-xs mb-1">
                                <?= str_repeat('<i class="fa fa-star"></i>', $propStars) ?>
                                <span class="text-muted ms-1"><?= h($propCity) ?></span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-1">
                                <span class="text-muted text-xs"><?= date('D, d M', strtotime($checkIn)) ?> &rarr; <?= date('D, d M', strtotime($checkOut)) ?> (<?= h($nights) ?>n)</span>
                                <span class="fw-bold text-success" style="font-size: 14px;">TZS <?= number_format($grandTotal) ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Collapsible Trigger for Breakdown -->
                    <button type="button" class="btn btn-sm btn-light w-100 mt-2 py-1 text-xs fw-semibold text-secondary d-flex align-items-center justify-content-center gap-1 border" id="btnToggleMobileDetails" onclick="toggleMobileSummaryDetails()">
                        <span>Show details &amp; price breakdown</span>
                        <i id="mobileSummaryChevron" class="fa-solid fa-chevron-down ms-1" style="transition: transform 0.2s;"></i>
                    </button>

                    <!-- Collapsible Content -->
                    <div id="mobileSummaryDetails" class="trivago-mobile-details mt-3 pt-3 border-top" style="display: none;">
                        <div class="text-xs text-muted mb-2">
                            <span class="fw-bold text-dark d-block mb-1"><?= h($roomTitle) ?></span>
                            <?php if (!empty($room['breakfast_included'])): ?><span class="d-block text-success mb-1"><i class="fa-solid fa-mug-saucer me-1"></i>Breakfast included</span><?php endif; ?>
                            <?php if (!empty($room['non_refundable'])): ?><span class="d-block text-secondary"><i class="fa-solid fa-clock-rotate-left me-1"></i>Non-refundable</span><?php endif; ?>
                        </div>
                        <div class="bg-light rounded p-2 text-xs mb-2">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted"><?= h($rooms) ?> room x <?= h($nights) ?> night<?= $nights > 1 ? 's' : '' ?></span>
                                <span class="fw-semibold text-dark">TZS <?= number_format($subtotal) ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Taxes &amp; fees (18% VAT)</span>
                                <span class="fw-semibold text-dark">TZS <?= number_format($taxFee) ?></span>
                            </div>
                            <div class="d-flex justify-content-between pt-1 border-top fw-bold text-dark">
                                <span>Total amount</span>
                                <span class="text-success">TZS <?= number_format($grandTotal) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row align-items-start">
                
                <!-- Left Column: Guest info, Payment details, Review & Book -->
                <div class="col-lg-7 col-xl-7 col-12 mb-4">
                    
                    <!-- 1. Guest details -->
                    <div class="trivago-card">
                        <h3 class="trivago-card-title">
                            <span>Guest details <span class="text-muted fw-normal fs-6">(Required)</span></span>
                        </h3>
                        <p class="trivago-card-subtitle">Enter the details of the guest who will check in.</p>

                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="trivago-field">
                                    <label class="trivago-label">First name <span class="text-danger">*</span></label>
                                    <input type="text" name="first_name" id="trivago_first_name" class="trivago-input" placeholder="e.g. John" value="<?= h($prefillFirstName) ?>" required>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="trivago-field">
                                    <label class="trivago-label">Last name <span class="text-danger">*</span></label>
                                    <input type="text" name="last_name" id="trivago_last_name" class="trivago-input" placeholder="e.g. Mwangi" value="<?= h($prefillLastName) ?>" required>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="trivago-field">
                                    <label class="trivago-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" id="trivago_email" class="trivago-input" placeholder="e.g. guest@example.com" value="<?= h($prefillEmail) ?>" required>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="trivago-field">
                                    <label class="trivago-label">Phone number <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <select class="trivago-select border-end-0" id="trivago_dial_code" style="max-width: 95px; border-top-right-radius: 0; border-bottom-right-radius: 0; font-size: 13px;">
                                            <option value="+255" selected>🇹🇿 +255</option>
                                            <option value="+1">🇺🇸 +1</option>
                                            <option value="+254">🇰🇪 +254</option>
                                            <option value="+256">🇺🇬 +256</option>
                                            <option value="+250">🇷🇼 +250</option>
                                            <option value="+27">🇿🇦 +27</option>
                                            <option value="+44">🇬🇧 +44</option>
                                            <option value="+49">🇩🇪 +49</option>
                                            <option value="+971">🇦🇪 +971</option>
                                        </select>
                                        <input type="tel" name="phone" id="trivago_phone" class="trivago-input" style="border-top-left-radius: 0; border-bottom-left-radius: 0;" placeholder="712 345 678" value="<?= h($prefillPhone) ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="text-muted text-xs mt-3 mb-0">We'll send your booking confirmation and contact you if needed.</p>
                    </div>

                    <!-- 2. Payment details (Required - Tanzania Local Mobile Money) -->
                    <div class="trivago-card">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <h3 class="trivago-card-title mb-0">
                                <span>Payment details <span class="text-muted fw-normal fs-6">(Required)</span></span>
                            </h3>
                            <span class="d-inline-flex align-items-center text-muted gap-1" style="font-size: 11px;">
                                <i class="fa-solid fa-shield-halved text-success"></i> Secure mobile payment
                            </span>
                        </div>
                        <p class="trivago-card-subtitle">Pay securely via your Tanzanian mobile money account.</p>

                        <!-- Clean Horizontal Mobile Money Strip -->
                        <div class="momo-strip">
                            <!-- Vodacom M-Pesa -->
                            <label class="momo-strip-item active">
                                <input type="radio" name="payment_method" value="vodacom" class="momo-strip-radio" checked>
                                <img src="<?= $this->Url->build('/assets/img/vodacom-logo.png') ?>" alt="Vodacom M-Pesa" class="momo-strip-logo">
                                <span class="momo-strip-name">M-Pesa</span>
                            </label>

                            <!-- Tigo Pesa -->
                            <label class="momo-strip-item">
                                <input type="radio" name="payment_method" value="tigo" class="momo-strip-radio">
                                <img src="<?= $this->Url->build('/assets/img/tigo-pesa-logo.jpg') ?>" alt="Tigo Pesa" class="momo-strip-logo">
                                <span class="momo-strip-name">Tigo Pesa</span>
                            </label>

                            <!-- Airtel Money -->
                            <label class="momo-strip-item">
                                <input type="radio" name="payment_method" value="airtel" class="momo-strip-radio">
                                <img src="<?= $this->Url->build('/assets/img/airtel-logo.png') ?>" alt="Airtel Money" class="momo-strip-logo">
                                <span class="momo-strip-name">Airtel Money</span>
                            </label>

                            <!-- Halotel HaloPesa -->
                            <label class="momo-strip-item">
                                <input type="radio" name="payment_method" value="halotel" class="momo-strip-radio">
                                <img src="<?= $this->Url->build('/assets/img/halotel-logo.jpg') ?>" alt="HaloPesa" class="momo-strip-logo">
                                <span class="momo-strip-name">HaloPesa</span>
                            </label>
                        </div>

                        <!-- Mobile Payment Input -->
                        <div class="trivago-field">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label class="trivago-label mb-0">Mobile number for payment <span class="text-danger">*</span></label>
                                <button type="button" id="btnSyncPhone" class="btn btn-link p-0 text-decoration-none text-xs fw-semibold text-primary">
                                    <i class="fa-solid fa-arrow-down me-1"></i>Same as guest phone
                                </button>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="d-inline-flex align-items-center justify-content-center bg-light border border-end-0 rounded-start px-3 text-muted fw-semibold momo-phone-prefix">
                                    +255
                                </span>
                                <input type="tel" id="mobile_payment_phone" name="payment_phone" class="trivago-input" style="border-top-left-radius: 0; border-bottom-left-radius: 0;" placeholder="712 345 678" value="<?= h($prefillPhone) ?>" required>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Review and book -->
                    <div class="trivago-card">
                        <h3 class="trivago-card-title mb-3">Review and book</h3>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="termsAgreeCheck" name="terms_agree" value="1" required checked>
                            <label class="form-check-label text-xs text-secondary ms-1" for="termsAgreeCheck" style="line-height: 1.5;">
                                By booking, you confirm that you've read and accept the <a href="<?= $this->Url->build('/terms-of-service') ?>" target="_blank" class="text-primary text-decoration-underline">terms and conditions</a> and <a href="<?= $this->Url->build('/privacy-policy') ?>" target="_blank" class="text-primary text-decoration-underline">privacy policy</a> from fastnet DEALS - a fully owned subsidiary of fastnetstays. You confirm you are 21 or older. <span class="text-danger">*</span>
                            </label>
                        </div>

                        <button type="submit" id="btnConfirmPay" class="trivago-confirm-pay-btn" <?= empty($quote) ? 'disabled' : '' ?> >
                            Confirm and pay TZS <?= number_format($grandTotal) ?>
                        </button>
                        <p class="text-center text-xs text-muted mt-2 mb-0">You've found a great deal — confirm now to make it yours!</p>
                    </div>

                </div>

                <!-- Right Column: Sticky Summary Sidebar (Desktop >= 992px) -->
                <div class="col-lg-5 col-xl-5 d-none d-lg-block">
                    <div class="trivago-sidebar-sticky">

                        <!-- Trust partner note -->
                        <div class="trivago-trust-banner d-flex align-items-start gap-2">
                            <span>You're booking directly with our trusted partner <strong>fastnet DEALS</strong> - a fully owned subsidiary of fastnetstays.</span>
                            <i class="fa-solid fa-circle-info text-muted ms-auto mt-1 cursor-pointer"></i>
                        </div>

                        <!-- Booking Details Card -->
                        <div class="trivago-summary-card">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h4 class="fw-bold text-dark fs-6 mb-0">Booking details</h4>
                                <a href="<?= $this->Url->build('/hotel-detail/' . $propId . '?' . http_build_query($queryParams)) ?>" class="text-primary text-sm fw-semibold text-decoration-underline">Change</a>
                            </div>

                            <!-- Hotel photo -->
                            <?php if ($img !== ''): ?><img src="<?= (str_starts_with($img, 'http')) ? h($img) : $this->Url->build('/' . h($img)) ?>" alt="<?= h($propTitle) ?>" class="trivago-summary-img"><?php else: ?><div class="trivago-summary-img trivago-summary-image-empty" role="status">No photo</div><?php endif; ?>

                            <!-- Hotel name & rating -->
                            <h5 class="fw-bold text-dark fs-6 mb-1"><?= h($propTitle) ?></h5>
                            <div class="d-flex align-items-center gap-1 text-warning text-xs mb-1">
                                <?= str_repeat('<i class="fa fa-star"></i>', $propStars) ?>
                                <span class="text-muted ms-1">Hotel</span>
                            </div>
                            <p class="text-muted text-xs mb-3">
                                <i class="fa-solid fa-location-dot me-1 text-danger"></i><?= h($propCountry) ?>, <?= h($propCity) ?>, <?= h($propArea) ?>
                            </p>

                            <!-- Check-in / Check-out / Nights 3-column row -->
                            <div class="row g-2 text-xs py-2 border-top border-bottom mb-2">
                                <div class="col-4">
                                    <span class="text-muted d-block">Check-in</span>
                                    <strong class="text-dark d-block"><?= date('D, d M Y', strtotime($checkIn)) ?></strong>
                                    <span class="text-muted">2:00 PM*</span>
                                </div>
                                <div class="col-4">
                                    <span class="text-muted d-block">Check-out</span>
                                    <strong class="text-dark d-block"><?= date('D, d M Y', strtotime($checkOut)) ?></strong>
                                    <span class="text-muted">12:00 PM*</span>
                                </div>
                                <div class="col-4 text-end">
                                    <span class="text-muted d-block">Nights</span>
                                    <strong class="text-dark d-block fs-6"><?= h($nights) ?></strong>
                                </div>
                                <div class="col-12 mt-1">
                                    <span class="text-muted" style="font-size: 10.5px;">*All times shown are in the property's local time.</span>
                                </div>
                            </div>

                            <!-- Blue Alert: Check your dates before booking -->
                            <div class="trivago-blue-alert gap-2">
                                <i class="fa-solid fa-circle-info text-primary"></i>
                                <span>Check your dates before booking.</span>
                            </div>

                            <!-- Room details -->
                            <div class="pt-2">
                                <h6 class="fw-bold text-dark text-sm mb-1"><?= h($roomTitle) ?></h6>
                                <?php if (!empty($room['breakfast_included'])): ?><div class="d-flex align-items-center gap-2 text-xs text-success fw-medium mb-1">
                                    <i class="fa-solid fa-mug-saucer"></i>
                                    <span>Breakfast included</span>
                                </div><?php endif; ?>
                                <?php if (!empty($room['non_refundable'])): ?><div class="d-flex align-items-center gap-2 text-xs text-secondary mb-2">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                    <span>Non-refundable</span>
                                    <i class="fa-solid fa-circle-info text-muted"></i>
                                </div><?php endif; ?>
                                <div class="text-xs text-muted">
                                    <span class="fw-medium text-dark">Room details</span>
                                    <div class="d-flex align-items-center gap-1 mt-1">
                                        <i class="fa-solid fa-bed text-muted"></i>
                                        <span><?= h($roomTitle) ?> (<?= h($adults) ?> adult<?= $adults > 1 ? 's' : '' ?><?= $children > 0 ? ', ' . h($children) . ' child' . ($children > 1 ? 'ren' : '') : '' ?>)</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Price Summary Card -->
                        <div class="trivago-summary-card">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h4 class="fw-bold text-dark fs-6 mb-0">Price summary</h4>
                                <span class="text-xs text-primary fw-medium">All prices in TZS</span>
                            </div>

                            <div class="trivago-price-row">
                                <div>
                                    <span class="d-block text-dark"><?= h($rooms) ?> room x <?= h($nights) ?> night<?= $nights > 1 ? 's' : '' ?></span>
                                    <span class="text-xs text-muted">TZS <?= number_format($avgPerNight) ?> average per night per room</span>
                                </div>
                                <span class="fw-bold text-dark">TZS <?= number_format($subtotal) ?></span>
                            </div>

                            <div class="trivago-price-row">
                                <div class="d-flex align-items-center gap-1">
                                    <span class="text-dark">Taxes and fees</span>
                                    <i class="fa-solid fa-circle-info text-muted cursor-pointer" title="Includes 18% Tanzania VAT and municipal tourism taxes."></i>
                                </div>
                                <span class="fw-bold text-dark">TZS <?= number_format($taxFee) ?></span>
                            </div>

                            <hr class="my-3 border-slate-200">

                            <div class="trivago-total-row">
                                <span class="trivago-total-label">Total</span>
                                <span class="trivago-total-amount">TZS <?= number_format($grandTotal) ?></span>
                            </div>
                            <span class="text-xs text-muted d-block mt-1">Total price includes all taxes and fees.</span>
                        </div>

                    </div>
                </div>

            </div>
        </form>

    </div>
</main>

<script>
// Mobile Summary Breakdown Toggle
function toggleMobileSummaryDetails() {
    const details = document.getElementById('mobileSummaryDetails');
    const chevron = document.getElementById('mobileSummaryChevron');
    const btn = document.getElementById('btnToggleMobileDetails');
    if (!details) return;
    if (details.style.display === 'none' || !details.style.display) {
        details.style.display = 'block';
        if (chevron) chevron.style.transform = 'rotate(180deg)';
        if (btn) btn.querySelector('span').textContent = 'Hide details';
    } else {
        details.style.display = 'none';
        if (chevron) chevron.style.transform = 'rotate(0deg)';
        if (btn) btn.querySelector('span').textContent = 'Show details & price breakdown';
    }
}

document.addEventListener("DOMContentLoaded", function() {
    // 1. Autofill from localStorage if user data is stored
    try {
        const userStr = localStorage.getItem('user') || localStorage.getItem('fastnet_user');
        if (userStr) {
            const user = JSON.parse(userStr);
            const fnInput = document.getElementById('trivago_first_name');
            const lnInput = document.getElementById('trivago_last_name');
            const emInput = document.getElementById('trivago_email');
            const phInput = document.getElementById('trivago_phone');
            const payPhoneInput = document.getElementById('mobile_payment_phone');

            if (user.name) {
                const parts = user.name.split(' ');
                if (fnInput && !fnInput.value) fnInput.value = parts[0] || '';
                if (lnInput && !lnInput.value) lnInput.value = parts.slice(1).join(' ') || '';
            }
            if (user.first_name && fnInput && !fnInput.value) fnInput.value = user.first_name;
            if (user.last_name && lnInput && !lnInput.value) lnInput.value = user.last_name;
            if (user.email && emInput && !emInput.value) emInput.value = user.email;
            if (user.phone) {
                if (phInput && !phInput.value) phInput.value = user.phone;
                if (payPhoneInput && !payPhoneInput.value) payPhoneInput.value = user.phone;
            }
        }
    } catch (e) {
        console.warn('Autofill parsing failed', e);
    }

    // 2. Mobile Money Provider Strip Switching
    const momoRadios = document.querySelectorAll('.momo-strip-radio');
    momoRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.momo-strip-item').forEach(item => item.classList.remove('active'));
            if (this.checked) {
                const item = this.closest('.momo-strip-item');
                if (item) item.classList.add('active');
            }
        });
    });

    // 3. Sync Guest Phone to Mobile Money Phone
    const btnSyncPhone = document.getElementById('btnSyncPhone');
    if (btnSyncPhone) {
        btnSyncPhone.addEventListener('click', function() {
            const guestPhone = document.getElementById('trivago_phone');
            const payPhone = document.getElementById('mobile_payment_phone');
            if (guestPhone && payPhone && guestPhone.value.trim()) {
                payPhone.value = guestPhone.value.trim();
                payPhone.classList.add('bg-light-success');
                setTimeout(() => payPhone.classList.remove('bg-light-success'), 1200);
            }
        });
    }

    // 4. Form Submission & Validation
    const form = document.getElementById('trivagoCheckoutForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const termsCheck = document.getElementById('termsAgreeCheck');
            if (termsCheck && !termsCheck.checked) {
                e.preventDefault();
                alert('Please accept the terms and conditions before confirming your booking.');
                termsCheck.focus();
                return false;
            }

            const payPhone = document.getElementById('mobile_payment_phone');
            if (payPhone && !payPhone.value.trim()) {
                e.preventDefault();
                alert('Please enter your mobile money phone number for payment.');
                payPhone.focus();
                return false;
            }

            const btn = document.getElementById('btnConfirmPay');
            if (btn) {
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Sending Mobile Money USSD prompt...';
                btn.disabled = true;
                btn.setAttribute('aria-busy', 'true');
                btn.style.opacity = '0.85';
            }
        });
    }
});
</script>
