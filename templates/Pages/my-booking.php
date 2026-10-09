<?php
/**
 * FastNet Stays - My Bookings (100% mobile app Carbon mirror)
 */
$this->assign('title', 'My bookings - FastNet Stays');
$this->assign('description', 'Review upcoming stays, download receipts and track payments on FastNet Stays.');
$userBookings = is_array($userBookings ?? null) ? $userBookings : (is_array($bookings ?? null) ? $bookings : []);
$pendingPayments = is_array($pendingPayments ?? null) ? $pendingPayments : [];
?>
<?= $this->element('navbar'); ?>
<main id="main-content" role="main" class="cds-bk-page">
<?= $this->Html->css('/assets/css/my-booking.css') ?>

<!-- App Header Bar -->
<div class="cds-bk-appbar">
  <div class="container" style="max-width:1180px">
    <div class="cds-bk-appbar-inner">
      <div class="cds-bk-title-wrap">
        <h1 class="cds-bk-title">My bookings</h1>
        <?php if (!empty($userBookings)): ?>
        <span class="cds-bk-count"><?= count($userBookings) ?></span>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<div class="container" style="max-width:1180px">
  <div class="row">
    <?= $this->element('profile_sidebar', ['active' => 'bookings']); ?>
    <div class="col-lg-9 ps-lg-4">
      <div class="cds-bk-body">

        <?php if (!empty($pendingPayments)): ?>
        <section aria-label="Awaiting payment" class="cds-bk-section-pending">
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
                <div class="cds-bk-actions" style="margin-top:6px">
                  <a href="<?= $this->Url->build('/booking-payment', ['?' => ['payment_id' => $pp['payment_id']]]) ?>" class="cds-btn cds-btn-pay">Complete payment</a>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </section>
        <?php endif; ?>

        <section aria-label="Your stays">
          <h2 class="visually-hidden">Active &amp; past stays</h2>
          <?php if (!empty($userBookings)):
            // Tab bucket per booking (mirrors mobile _tabOf).
            $bkTabOf = function (array $b): string {
              $s = strtolower(trim((string)($b['status'] ?? ($b['booking_status'] ?? 'confirmed'))));
              if (in_array($s, ['cancelled', 'canceled'], true)) return 'cancelled';
              if ($s === 'completed') return 'completed';
              return 'upcoming';
            };
            $bkCounts = ['upcoming' => 0, 'completed' => 0, 'cancelled' => 0];
            foreach ($userBookings as $cb) { $bkCounts[$bkTabOf((array)$cb)]++; }
          ?>
          
          <!-- Tab Bar -->
          <div class="cds-bk-tabs" role="tablist" aria-label="Filter stays">
            <?php foreach (['upcoming' => 'Upcoming', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $tk => $tl): ?>
            <button type="button" role="tab" class="cds-bk-tab<?= $tk === 'upcoming' ? ' is-active' : '' ?>" data-bk-tab="<?= $tk ?>" aria-selected="<?= $tk === 'upcoming' ? 'true' : 'false' ?>"><?= h($tl) ?> (<?= (int)$bkCounts[$tk] ?>)</button>
            <?php endforeach; ?>
          </div>

          <!-- Search & Sort Row -->
          <div class="cds-bk-tools">
            <div class="cds-bk-search">
              <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
              <input id="bkSearch" type="text" placeholder="Search by code, lodge, or city" aria-label="Search bookings" autocomplete="off">
              <button type="button" id="bkClearSearch" class="cds-bk-clear-btn" aria-label="Clear search" style="display:none">
                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
              </button>
            </div>
            <button type="button" id="bkSortBtn" class="cds-bk-sort-btn" title="Soonest first — tap for latest first" aria-label="Toggle sort order" data-sort="soon">
              <i id="bkSortIcon" class="fa-solid fa-arrow-up" aria-hidden="true"></i>
            </button>
          </div>

          <!-- Hint Row -->
          <div class="cds-bk-hint">
            <i class="fa-solid fa-hand-pointer" aria-hidden="true"></i>
            <span>Tap a booking for full details, check-in &amp; receipt</span>
          </div>

          <!-- Cards Stack -->
          <div id="bkCards">
            <?php foreach ($userBookings as $b):
              $b = (array)$b;
              $bCode = $b['booking_code'] ?? ($b['booking_number'] ?? ('BK' . ($b['id'] ?? '')));
              $bCode = (string)$bCode;
              $shortCode = str_contains($bCode, '-') ? (explode('-', $bCode)[1] ?? $bCode) : (strlen($bCode) > 12 ? substr($bCode, 0, 12) . '…' : ($bCode !== '' ? $bCode : '—'));
              $prop = is_array($b['room']['property'] ?? null) ? $b['room']['property'] : (is_array($b['property'] ?? null) ? $b['property'] : []);
              $propId = $b['property_id'] ?? ($prop['id'] ?? null);
              $propName = $prop['name'] ?? ($b['property_name'] ?? 'Lodge Stay');
              $cityName = $prop['city'] ?? ($b['property']['city'] ?? 'Dar es Salaam');
              $areaName = $prop['area'] ?? '';
              $place = trim(trim((string)$areaName, " \t\n\r\0\x0B,") . ', ' . trim((string)$cityName, " \t\n\r\0\x0B,"), " \t\n\r\0\x0B,");
              if ($place === '' || $place === ',') $place = 'Dar es Salaam';
              $img = $prop['image_url'] ?? ($prop['primary_image_url'] ?? ($b['imageUrl'] ?? ''));
              if (!is_string($img) || trim($img) === '') $img = '/assets/images/house3.webp';
              $checkInIso = (string)($b['check_in'] ?? '');
              $checkOutIso = (string)($b['check_out'] ?? '');
              $checkIn = $checkInIso !== '' ? date('d/m/Y', strtotime($checkInIso)) : '';
              $checkOut = $checkOutIso !== '' ? date('d/m/Y', strtotime($checkOutIso)) : '';
              $nights = null;
              if ($checkInIso !== '' && $checkOutIso !== '') {
                $diff = strtotime($checkOutIso) - strtotime($checkInIso);
                if ($diff > 0) $nights = (int)round($diff / 86400);
              }
              if ($nights === null || $nights < 1) {
                $nights = isset($b['nights']) ? (int)$b['nights'] : 1;
              }
              $datesFormatted = ($checkIn !== '' && $checkOut !== '') ? ($checkIn . ' - ' . $checkOut) : ($checkIn !== '' ? $checkIn : '');
              $datesLine = ($datesFormatted !== '' ? $datesFormatted . ' · ' : '') . $nights . ' night' . ($nights === 1 ? '' : 's');
              $rawPrice = $b['total_price'] ?? ($b['price'] ?? 0);
              $price = 'TSh ' . number_format((float)$rawPrice);
              $rawStatus = strtolower(trim((string)($b['status'] ?? ($b['booking_status'] ?? 'confirmed'))));
              $statusLabel = match(true) {
                in_array($rawStatus, ['cancelled', 'canceled'], true) => 'CANCELLED',
                $rawStatus === 'completed' => 'COMPLETED',
                in_array($rawStatus, ['checked in', 'checked_in', 'checked-in'], true) => 'CHECKED IN',
                $rawStatus === 'pending' => 'PENDING',
                default => 'CONFIRMED',
              };
              $tagCls = match(true) {
                $statusLabel === 'CONFIRMED' => 'cds-t-confirmed',
                $statusLabel === 'PENDING' => 'cds-t-pending',
                $statusLabel === 'COMPLETED' => 'cds-t-completed',
                $statusLabel === 'CHECKED IN' => 'cds-t-checkin',
                $statusLabel === 'CANCELLED' => 'cds-t-cancelled',
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
              <!-- Top Row: Thumbnail + Details -->
              <div class="cds-bk-card-body">
                <a class="cds-bk-photo" href="<?= $detailsUrl ?>" aria-label="Open stay details for <?= h($propName) ?>">
                  <img src="<?= h($img) ?>" alt="<?= h($propName) ?>" loading="lazy" onerror="this.onerror=null;this.src='/assets/images/house3.webp'">
                </a>
                <div class="cds-bk-info">
                  <div class="cds-bk-top-row">
                    <span class="cds-tag <?= $tagCls ?> bk-status-badge"><?= h($statusLabel) ?></span>
                    <span class="cds-bk-code"><?= h($shortCode) ?></span>
                  </div>
                  <a class="cds-bk-prop-row" href="<?= $detailsUrl ?>">
                    <span class="cds-bk-prop"><?= h($propName) ?></span>
                    <i class="fa-solid fa-chevron-right cds-bk-prop-arrow" aria-hidden="true"></i>
                  </a>
                  <div class="cds-bk-meta cds-bk-loc"><?= h($place) ?></div>
                  <div class="cds-bk-meta cds-bk-dates">
                    <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                    <span><?= h($datesLine) ?></span>
                  </div>
                  <div class="cds-bk-amt"><?= h($price) ?></div>
                </div>
              </div>

              <!-- Middle: Action Buttons (Per-Tab Exact Mobile Mirror) -->
              <div class="cds-bk-actions-bar">
                <div class="cds-bk-actions">
                  <?php if ($tab === 'cancelled'): ?>
                  <a href="<?= $this->Url->build('/hotel-list-01') ?>" class="cds-btn cds-btn-primary"><i class="fa-solid fa-rotate-right" aria-hidden="true"></i> Book again</a>
                  <?php elseif ($tab === 'completed'): ?>
                  <button type="button" class="cds-btn cds-btn-ghost" onclick="downloadBookingReceipt('<?= h($bCode) ?>')"><i class="fa-solid fa-receipt" aria-hidden="true"></i> Receipt</button>
                  <a href="<?= $propId ? $this->Url->build('/hotel-detail-01', ['?' => ['id' => $propId]]) . '#reviews' : $this->Url->build('/hotel-list-01') ?>" class="cds-btn cds-btn-primary"><i class="fa-regular fa-star" aria-hidden="true"></i> Review</a>
                  <?php else: ?>
                  <?php if ($canCancel): ?>
                  <button type="button" class="cds-btn cds-btn-danger" onclick="openCancelModal('<?= h($bCode) ?>')">Cancel stay</button>
                  <?php endif; ?>
                  <button type="button" class="cds-btn cds-btn-ghost" onclick="downloadBookingReceipt('<?= h($bCode) ?>')"><i class="fa-solid fa-receipt" aria-hidden="true"></i> Receipt</button>
                  <?php endif; ?>
                </div>
              </div>

              <!-- Bottom: View Stay Details Strip -->
              <a class="cds-bk-viewstrip" href="<?= $detailsUrl ?>">
                <span>View stay details</span>
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
              </a>
            </article>
            <?php endforeach; ?>
          </div>

          <!-- Dynamic Empty State -->
          <div class="cds-empty" id="bkEmpty" style="display:none">
            <div class="cds-empty-icon"><i id="bkEmptyIcon" class="fa-solid fa-suitcase" aria-hidden="true"></i></div>
            <b id="bkEmptyTitle">No upcoming stays</b>
            <span id="bkEmptySub">Your confirmed reservations will appear here.</span>
          </div>
          <?php else: ?>
          <!-- Initial Logged-In Empty State -->
          <div class="cds-empty">
            <div class="cds-empty-icon"><i class="fa-solid fa-suitcase" aria-hidden="true"></i></div>
            <b>No bookings yet</b>
            <span>Your confirmed stays will appear here.</span>
            <div style="margin-top:16px"><a href="<?= $this->Url->build('/hotel-list-01') ?>" class="cds-btn cds-btn-primary" style="display:inline-flex;padding:11px 24px">Search stays</a></div>
          </div>
          <?php endif; ?>
        </section>

      </div>
    </div>
  </div>
</div>

<!-- Mobile-Style Confirmation Dialog -->
<div id="bkCancelModal" class="cds-modal-overlay" style="display:none" role="dialog" aria-modal="true" aria-labelledby="bkCancelTitle">
  <div class="cds-modal-box">
    <h3 id="bkCancelTitle" class="cds-modal-title">Cancel this stay?</h3>
    <p id="bkCancelDesc" class="cds-modal-body">Booking will be cancelled. This action cannot be undone.</p>
    <div class="cds-modal-actions">
      <button type="button" class="cds-modal-btn-cancel" onclick="closeCancelModal()">Keep stay</button>
      <button type="button" id="bkConfirmCancelBtn" class="cds-modal-btn-confirm">Cancel stay</button>
    </div>
  </div>
</div>

<!-- Toast -->
<div id="trivago-toast" style="position:fixed;bottom:24px;right:24px;background:#161616;color:#fff;padding:12px 20px;font-size:13.5px;z-index:9999;display:none;opacity:0;transition:opacity .25s ease;border-radius:0"></div>

<?= $this->element('Booking/my-booking-script') ?>
</main>
<?= $this->element('footer', ['skin' => 'skin-light-footer']) ?>
