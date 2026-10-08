<?php
$this->assign('title', 'Verify booking | FastNet Stays');
$this->assign('description', 'Check that a FastNet Stays booking confirmation is genuine. Scan the QR on your receipt to verify the stay instantly.');
$valid = !empty($valid);
$booking = is_array($booking ?? null) ? $booking : null;
$code = trim((string)($code ?? ''));
$error = trim((string)($error ?? ''));
$status = trim((string)($booking['status'] ?? ''));
$statusLower = strtolower($status);
$pill = 'blue';
if (in_array($statusLower, ['confirmed', 'checked in', 'checked_in'], true)) $pill = 'green';
elseif (in_array($statusLower, ['cancelled', 'canceled'], true)) $pill = 'red';
elseif ($statusLower === 'completed') $pill = 'grey';
$checkIn = trim((string)($booking['check_in'] ?? ''));
$checkOut = trim((string)($booking['check_out'] ?? ''));
$nights = null;
if ($checkIn !== '' && $checkOut !== '') {
    $diff = strtotime($checkOut) - strtotime($checkIn);
    if ($diff > 0) $nights = (int)round($diff / 86400);
}
$place = trim(trim((string)($booking['property_area'] ?? ''), " \t\n\r\0\x0B,") . ', ' . trim((string)($booking['property_city'] ?? ''), " \t\n\r\0\x0B,"), " \t\n\r\0\x0B,");
$stayLine = $checkIn;
if ($checkOut !== '') $stayLine .= ' → ' . $checkOut;
if ($nights !== null) $stayLine .= ' · ' . $nights . ' night' . ($nights === 1 ? '' : 's');
$pay = trim((string)($booking['payment_status'] ?? ''));
?>
<?= $this->element('navbar') ?>
<main id="main-content" role="main" style="background:var(--cds-gray-10);min-height:85vh">
<?= $this->Html->css('/assets/css/verify-booking.css') ?>

<div class="cds-verify-band">
  <div class="container" style="max-width:960px">
    <div class="cds-verify-eyebrow">Booking verification</div>
    <h1 class="cds-verify-h1"><?= $valid ? 'This booking is verified' : 'Check this confirmation' ?></h1>
    <p class="cds-verify-lede">Scanned from a printed FastNet Stays receipt. This page proves the confirmation is genuine — no account needed.</p>
  </div>
</div>

<div class="cds-verify-body">

  <?php if ($valid): ?>
  <div class="cds-verify-verdict ok" role="status">
    <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
    <div>
      <h2>Verified booking — this confirmation is genuine</h2>
      <p>Issued by FastNet Stays for booking <code><?= h($code) ?></code>. Details below come live from our system, so they always match the real reservation.</p>
    </div>
  </div>
  <?php else: ?>
  <div class="cds-verify-verdict bad" role="alert">
    <i class="fa-solid fa-circle-xmark" aria-hidden="true"></i>
    <div>
      <h2>Could not verify this confirmation</h2>
      <p><?= $error !== '' ? h($error) : 'This confirmation is not recognized. It may be forged, altered, or mistyped.' ?></p>
    </div>
  </div>
  <?php endif; ?>

  <?php if ($booking !== null): ?>
  <section class="cds-verify-card" aria-label="Booking details">
    <p class="cds-verify-sec">Stay details</p>
    <div class="cds-list">
      <div class="cds-list-row"><span class="k">Property</span><strong><?= h((string)($booking['property_name'] ?? 'Lodge stay')) ?></strong></div>
      <?php if ($place !== ''): ?>
      <div class="cds-list-row"><span class="k">Location</span><?= h($place) ?></div>
      <?php endif; ?>
      <?php if (trim((string)($booking['room_number'] ?? '')) !== '' || trim((string)($booking['room_type'] ?? '')) !== ''): ?>
      <div class="cds-list-row"><span class="k">Room</span><?= h(trim(trim((string)($booking['room_type'] ?? '')) . ' · Room ' . trim((string)($booking['room_number'] ?? '')), ' ·')) ?></div>
      <?php endif; ?>
      <?php if ($stayLine !== ''): ?>
      <div class="cds-list-row"><span class="k">Dates</span><?= h($stayLine) ?></div>
      <?php endif; ?>
      <?php if (trim((string)($booking['guest_name'] ?? '')) !== ''): ?>
      <div class="cds-list-row"><span class="k">Lead guest</span><?= h((string)$booking['guest_name']) ?></div>
      <?php endif; ?>
      <?php if ($status !== ''): ?>
      <div class="cds-list-row"><span class="k">Status</span><span class="cds-verify-pill <?= h($pill) ?>"><?= h($status) ?></span></div>
      <?php endif; ?>
      <?php if ($pay !== ''): ?>
      <div class="cds-list-row"><span class="k">Payment</span><?= h(ucfirst($pay)) ?></div>
      <?php endif; ?>
      <div class="cds-list-row"><span class="k">Booking code</span><code><?= h($code) ?></code></div>
    </div>
  </section>
  <?php endif; ?>

  <?php if ($valid): ?>
  <div class="cds-verify-next">
    <strong>At check-in</strong>
    Show this page or your printed confirmation, plus a valid photo ID matching the lead guest name. For bookings paid by card, the property may ask to see the card used.
  </div>
  <?php else: ?>
  <div class="cds-verify-next">
    <strong>For staff</strong>
    Do not check in on this confirmation alone — verify the guest's photo ID and contact support. If a guest handed you this receipt, ask them to re-scan the QR on their original confirmation.
  </div>
  <?php endif; ?>

  <div class="cds-verify-actions">
    <a class="cds-btn-ghost" href="<?= $this->Url->build('/help-center') ?>">Help center</a>
    <a class="cds-btn-ghost" href="<?= $this->Url->build('/contact-v1') ?>">Contact support</a>
  </div>

</div>
</main>
<?= $this->element('footer', ['skin' => 'skin-light-footer']) ?>
