<?php
// Real guarantee countdown: driven by the server-owned quote expiry.
// No hardcoded fallback — without a quote there is no guaranteed price to count down.
$quoteRemainingSrv = isset($quoteRemaining) ? max(0, (int)$quoteRemaining)
    : (isset($quote['expires_at']) ? max(0, (int)$quote['expires_at'] - time()) : 0);
$quoteHasGuarantee = !empty($quote['expires_at']) || isset($quoteRemaining);
$fmtRem = sprintf('%02d:%02d:%02d', (int)($quoteRemainingSrv / 3600), (int)(($quoteRemainingSrv % 3600) / 60), $quoteRemainingSrv % 60);
?>
<?php if ($quoteHasGuarantee): ?>
<div class="agoda-timer-bar" id="agodaTimerBar" data-remaining="<?= (int)$quoteRemainingSrv ?>" data-expires-at="<?= (int)($quote['expires_at'] ?? 0) ?>">
  <span id="agodaTimerLabel">This price is guaranteed for... <b><i class="fa-regular fa-clock"></i> <span id="agodaCountdown"><?= h($fmtRem) ?></span></b></span>
  <span id="agodaTimerExpired" style="display:none">Price guarantee expired — <a href="javascript:void(0)" id="agodaRefreshPrice" class="agoda-link" style="font-weight:800">refresh the live price</a></span>
</div>
<?php endif; ?>
<?php if (!empty($quoteRepriced)): ?>
<div style="max-width:1180px;margin:14px auto 0;padding:0 16px">
  <div style="background:#e6f4ea;border:1px solid #c8e6c9;color:#137333;padding:10px 14px;border-radius:8px;font-size:13px;display:flex;gap:8px;align-items:center">
    <i class="fa-solid fa-rotate"></i>
    <span>Your previous guarantee expired, so this is a fresh live price with a new countdown.</span>
  </div>
</div>
<?php endif; ?>

<?php if (!empty($quoteError) && empty($quote['is_fallback'] ?? false) || !empty($quote['_fallback'] ?? false) && !empty($quoteError)): ?>
  <div style="max-width:1180px;margin:14px auto 0;padding:0 16px">
    <div style="background:#fff3cd;border:1px solid #ffe69c;color:#664d03;padding:10px 14px;border-radius:8px;font-size:13px;display:flex;gap:8px;align-items:center">
      <i class="fa-solid fa-circle-info"></i>
      <span>
        <?php if (!empty($quote['_fallback'])): ?>
          Booking quote generated locally. Live price will be confirmed on payment.
        <?php else: ?>
          <?= h($quoteError) ?>
        <?php endif; ?>
      </span>
    </div>
  </div>
  <?php endif; ?>
