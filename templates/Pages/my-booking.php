<?php
/**
 * FastNet Stays - My Bookings (live records only)
 */
$this->assign('title', 'My bookings - FastNet Stays');
$this->assign('description', 'Review upcoming stays, download receipts and track payments on FastNet Stays.');
$userBookings = is_array($userBookings ?? null) ? $userBookings : (is_array($bookings ?? null) ? $bookings : []);
$pendingPayments = is_array($pendingPayments ?? null) ? $pendingPayments : [];
?>
<?= $this->element('navbar'); ?>
<main id="main-content" role="main" style="background:var(--cds-gray-10);min-height:85vh">
<?= $this->Html->css('/assets/css/my-booking.css') ?>

<div class="container" style="max-width:960px">
  <div class="cds-bk-hero">
    <div class="cds-eyebrow">Account / Bookings</div>
    <h1 class="cds-h1">My bookings</h1>
    <p class="cds-lede">Upcoming and past stays, receipts, payments and cancellations.</p>
  </div>
</div>

<div class="container" style="max-width:1180px">
  <div class="row">
    <?= $this->element('profile_sidebar', ['active' => 'bookings']); ?>
    <div class="col-lg-9 ps-lg-4">
<div class="cds-bk-body" style="max-width:820px;padding-left:0;padding-right:0">
  <div id="bookingResultArea" style="display:none"></div>

  <?php if (!empty($pendingPayments)): ?>
  <section aria-label="Awaiting payment">
    <p class="cds-sec-label">Action needed</p>
    <h2 class="cds-sec-title">Awaiting payment</h2>
    <div class="cds-card cds-pend">
      <?php foreach ($pendingPayments as $pp):
        $ppCode = (string)($pp['booking_code'] ?? '');
        $ppAmt = (float)($pp['amount'] ?? 0);
        $ppMethod = (string)($pp['payment_method'] ?? '');
      ?>
      <div class="cds-bk-row">
        <div class="cds-bk-main">
          <span class="cds-tag cds-t-pending">Awaiting payment</span>
          <div class="cds-bk-prop">Ref #<?= h($ppCode !== '' ? $ppCode : $pp['payment_id']) ?></div>
          <div class="cds-bk-meta"><?= $ppMethod !== '' ? h(ucfirst($ppMethod)) . ' · ' : '' ?>Complete before the request expires</div>
        </div>
        <div class="cds-bk-side">
          <?php if ($ppAmt > 0): ?><div class="cds-bk-amt">TSh <?= number_format($ppAmt) ?></div><?php endif; ?>
          <div class="cds-bk-actions">
            <a href="<?= $this->Url->build('/booking-payment', ['?' => ['payment_id' => $pp['payment_id']]]) ?>" class="cds-btn cds-btn-pay">Complete payment</a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <section aria-label="Your stays">
    <p class="cds-sec-label">Reservations</p>
    <h2 class="cds-sec-title">Active &amp; past stays</h2>
    <?php if (!empty($userBookings)): ?>
    <div class="cds-card">
      <?php foreach ($userBookings as $b):
        $bCode = $b['booking_code'] ?? ($b['booking_number'] ?? ('BK' . ($b['id'] ?? '')));
        $propName = $b['room']['property']['name'] ?? ($b['property']['name'] ?? ($b['property_name'] ?? 'FastNet Stay'));
        $cityName = $b['room']['property']['city'] ?? ($b['property']['city'] ?? ($b['property']['address'] ?? 'Tanzania'));
        $roomTitle = $b['room']['room_number'] ?? ($b['room']['title'] ?? ($b['room_title'] ?? 'Standard room'));
        $checkIn = !empty($b['check_in']) ? date('d M Y', strtotime($b['check_in'])) : 'Flexible';
        $checkOut = !empty($b['check_out']) ? date('d M Y', strtotime($b['check_out'])) : 'Flexible';
        $price = isset($b['total_price']) ? 'TSh ' . number_format((float)$b['total_price']) : '';
        $rawStatus = strtolower($b['status'] ?? ($b['booking_status'] ?? 'confirmed'));
                                $tagCls = match(true) {
                                  $rawStatus === 'confirmed' => 'cds-t-confirmed',
                                  $rawStatus === 'pending' => 'cds-t-pending',
                                  $rawStatus === 'completed' => 'cds-t-completed',
                                  in_array($rawStatus, ['checked in', 'checked-in']) => 'cds-t-checkin',
                                  in_array($rawStatus, ['cancelled', 'canceled']) => 'cds-t-cancelled',
                                  default => 'cds-t-confirmed',
                                };
        $canCancel = !in_array($rawStatus, ['cancelled', 'canceled', 'completed'], true) && !empty($bCode);
        $cardGuestEmail = (string)(is_array($b['guest'] ?? null) ? ($b['guest']['email'] ?? '') : ($b['guest_email'] ?? ''));
        $detailsParams = ['booking_code' => $bCode];
        if ($cardGuestEmail !== '') $detailsParams['email'] = $cardGuestEmail;
      ?>
      <div class="cds-bk-row" id="bk_card_<?= h($bCode) ?>">
        <div class="cds-bk-main">
          <span class="cds-tag <?= $tagCls ?> bk-status-badge"><?= h(ucfirst($rawStatus)) ?></span>
          <div class="cds-bk-prop"><?= h($propName) ?></div>
          <div class="cds-bk-meta"><?= h($roomTitle) ?> · <?= h($cityName) ?></div>
          <div class="cds-bk-meta"><?= h($checkIn) ?> → <?= h($checkOut) ?> · Ref <strong>#<?= h($bCode) ?></strong></div>
        </div>
        <div class="cds-bk-side">
          <?php if ($price !== ''): ?><div class="cds-bk-amt"><?= h($price) ?></div><?php endif; ?>
          <div class="cds-bk-actions">
            <?php if ($bCode !== ''): ?>
            <a href="<?= $this->Url->build('/bookingpage-success', ['?' => $detailsParams]) ?>" class="cds-btn-ghost">View details</a>
            <?php endif; ?>
            <button type="button" class="cds-btn" onclick="downloadBookingReceipt('<?= h($bCode) ?>')">Receipt PDF</button>
            <?php if ($canCancel): ?><button type="button" class="cds-btn-danger" onclick="cancelBookingAction('<?= h($bCode) ?>')">Cancel stay</button><?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="cds-empty">
      <i class="fa-solid fa-suitcase" aria-hidden="true"></i>
      <b>No bookings yet</b>
      <span>Your confirmed stays will appear here.</span>
      <div style="margin-top:14px"><a href="<?= $this->Url->build('/hotel-list-01') ?>" class="cds-btn">Search stays</a></div>
    </div>
    <?php endif; ?>
  </section>

  <section aria-label="Find your booking">
    <p class="cds-sec-label">Can’t find it</p>
    <h2 class="cds-sec-title">Find your booking</h2>
    <div class="cds-find">
      <p class="sub">Look up any reservation with the guest email and booking reference.</p>
      <form id="findBookingForm" class="cds-form" onsubmit="handleFindBooking(event)">
        <div class="cds-grid2">
          <div class="cds-field">
            <label for="bookingEmail">Guest email</label>
            <input type="email" id="bookingEmail" placeholder="name@example.com" value="<?= h($userProfile['email'] ?? '') ?>" required>
          </div>
          <div class="cds-field">
            <label for="bookingNumber">Booking reference</label>
            <input type="text" id="bookingNumber" placeholder="e.g. BK12345" required>
          </div>
        </div>
        <div style="display:flex;justify-content:flex-end">
          <button type="submit" id="btnFindBooking" class="cds-btn">Find booking</button>
        </div>
      </form>
    </div>
  </section>
</div>
    </div>
  </div>
</div>

<div id="trivago-toast" style="position:fixed;bottom:24px;right:24px;background:#161616;color:#fff;padding:12px 20px;font-size:13.5px;z-index:9999;display:none;opacity:0;transition:opacity .25s ease"></div>

<?= $this->element('Booking/my-booking-script') ?>
</main>
<?= $this->element('footer', ['skin' => 'skin-light-footer']) ?>
