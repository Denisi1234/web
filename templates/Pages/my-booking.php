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
    <?php if (!empty($userBookings)):
      // Tab bucket per booking (mirrors the mobile app).
      $bkTabOf = function (array $b): string {
        $s = strtolower(trim((string)($b['status'] ?? ($b['booking_status'] ?? 'confirmed'))));
        if (in_array($s, ['cancelled', 'canceled'], true)) return 'cancelled';
        if ($s === 'completed') return 'completed';
        return 'upcoming';
      };
      $bkCounts = ['upcoming' => 0, 'completed' => 0, 'cancelled' => 0];
      foreach ($userBookings as $cb) { $bkCounts[$bkTabOf((array)$cb)]++; }
    ?>
    <div class="cds-bk-tabs" role="tablist" aria-label="Filter stays">
      <?php foreach (['upcoming' => 'Upcoming', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $tk => $tl): ?>
      <button type="button" role="tab" class="cds-bk-tab<?= $tk === 'upcoming' ? ' is-active' : '' ?>" data-bk-tab="<?= $tk ?>" aria-selected="<?= $tk === 'upcoming' ? 'true' : 'false' ?>"><?= h($tl) ?> (<?= (int)$bkCounts[$tk] ?>)</button>
      <?php endforeach; ?>
    </div>
    <div class="cds-bk-tools">
      <div class="cds-bk-search">
        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
        <input id="bkSearch" type="search" placeholder="Search by code, lodge, or city" aria-label="Search bookings">
      </div>
      <select id="bkSort" aria-label="Sort bookings">
        <option value="soon">Soonest first</option>
        <option value="late">Latest first</option>
      </select>
    </div>
    <p class="cds-bk-hint"><i class="fa-regular fa-hand-pointer" aria-hidden="true"></i> Open a stay for full details, check-in &amp; receipt.</p>
    <div id="bkCards">
      <?php foreach ($userBookings as $b):
        $b = (array)$b;
        $bCode = $b['booking_code'] ?? ($b['booking_number'] ?? ('BK' . ($b['id'] ?? '')));
        $bCode = (string)$bCode;
        $shortCode = str_contains($bCode, '-') ? (explode('-', $bCode)[1] ?? $bCode) : substr($bCode, 0, 12);
        $prop = is_array($b['room']['property'] ?? null) ? $b['room']['property'] : (is_array($b['property'] ?? null) ? $b['property'] : []);
        $propName = $prop['name'] ?? ($b['property_name'] ?? 'FastNet Stay');
        $cityName = $prop['city'] ?? ($b['property']['city'] ?? 'Tanzania');
        $areaName = $prop['area'] ?? '';
        $place = trim(trim((string)$areaName, " \t\n\r\0\x0B,") . ', ' . trim((string)$cityName, " \t\n\r\0\x0B,"), " \t\n\r\0\x0B,");
        $img = $prop['image_url'] ?? ($prop['primary_image_url'] ?? '');
        if (!is_string($img) || trim($img) === '') $img = '/assets/img/hotel/hotel-1.jpg';
        $checkInIso = (string)($b['check_in'] ?? '');
        $checkOutIso = (string)($b['check_out'] ?? '');
        $checkIn = $checkInIso !== '' ? date('d M Y', strtotime($checkInIso)) : 'Flexible';
        $checkOut = $checkOutIso !== '' ? date('d M Y', strtotime($checkOutIso)) : 'Flexible';
        $nights = null;
        if ($checkInIso !== '' && $checkOutIso !== '') {
          $diff = strtotime($checkOutIso) - strtotime($checkInIso);
          if ($diff > 0) $nights = (int)round($diff / 86400);
        }
        $datesLine = $checkIn . ' → ' . $checkOut . ($nights !== null ? ' · ' . $nights . ' night' . ($nights === 1 ? '' : 's') : '');
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
        $tab = $bkTabOf($b);
        $canCancel = !in_array($rawStatus, ['cancelled', 'canceled', 'completed'], true) && $bCode !== '';
        $cardGuestEmail = (string)(is_array($b['guest'] ?? null) ? ($b['guest']['email'] ?? '') : ($b['guest_email'] ?? ''));
        $detailsParams = ['booking_code' => $bCode];
        if ($cardGuestEmail !== '') $detailsParams['email'] = $cardGuestEmail;
        $detailsUrl = $this->Url->build('/bookingpage-success', ['?' => $detailsParams]);
        $searchHay = strtolower($bCode . ' ' . $propName . ' ' . $cityName . ' ' . $areaName);
      ?>
      <article class="cds-bk-card" id="bk_card_<?= h($bCode) ?>" data-tab="<?= h($tab) ?>" data-search="<?= h($searchHay) ?>" data-checkin="<?= h($checkInIso) ?>"<?= $tab !== 'upcoming' ? ' style="display:none"' : '' ?>>
        <a class="cds-bk-photo" href="<?= $detailsUrl ?>" aria-label="Open stay details for <?= h($propName) ?>">
          <img src="<?= h($img) ?>" alt="<?= h($propName) ?>" loading="lazy" onerror="this.onerror=null;this.src='/assets/img/hotel/hotel-1.jpg'">
        </a>
        <div class="cds-bk-info">
          <div class="cds-bk-top">
            <span class="cds-tag <?= $tagCls ?> bk-status-badge"><?= h(ucfirst($rawStatus)) ?></span>
            <span class="cds-bk-code"><?= h($shortCode) ?></span>
          </div>
          <div class="cds-bk-prop"><?= h($propName) ?></div>
          <?php if ($place !== ''): ?><div class="cds-bk-meta"><?= h($place) ?></div><?php endif; ?>
          <div class="cds-bk-meta"><i class="fa-regular fa-calendar" aria-hidden="true"></i> <?= h($datesLine) ?></div>
          <?php if ($price !== ''): ?><div class="cds-bk-amt"><?= h($price) ?></div><?php endif; ?>
        </div>
        <div class="cds-bk-foot">
          <div class="cds-bk-actions">
            <?php if ($bCode !== ''): ?>
            <a href="<?= $detailsUrl ?>" class="cds-btn-ghost">View details</a>
            <?php endif; ?>
            <button type="button" class="cds-btn" onclick="downloadBookingReceipt('<?= h($bCode) ?>')">Receipt PDF</button>
            <?php if ($canCancel): ?><button type="button" class="cds-btn-danger" onclick="cancelBookingAction('<?= h($bCode) ?>')">Cancel stay</button><?php endif; ?>
          </div>
          <a class="cds-bk-viewstrip" href="<?= $detailsUrl ?>">View stay details <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <div class="cds-empty" id="bkEmpty" style="display:none">
      <i class="fa-solid fa-suitcase" aria-hidden="true"></i>
      <b id="bkEmptyTitle">No stays here yet</b>
      <span id="bkEmptySub">Bookings in this tab will appear here.</span>
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
