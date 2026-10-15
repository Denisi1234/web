<?php
/**
 * Write-a-review modal for the hotel detail page.
 * Minimal professional dialog matching the property details modal language.
 * Expects: $detailPropertyId (int), $propTitle (string), $isLoggedIn (bool).
 */
$wrPropId = (int)($detailPropertyId ?? 0);
$wrLoggedIn = !empty($isLoggedIn);
$wrLoginUrl = '/login?redirect=' . urlencode('/hotel-detail/' . $wrPropId . '#reviews');
?>
<div class="modal fade" id="writeReviewModal" tabindex="-1" aria-labelledby="writeReviewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="border:1px solid #dadce0;">
      <div class="modal-header px-4 py-3 bg-white" style="border-bottom:1px solid #e8eaed;">
        <h5 class="modal-title fw-bold" id="writeReviewModalLabel" style="color:#1a1d25;font-size:17px;">Write a review</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body px-4 py-3" style="background:#fff;">
        <div style="font-size:13.5px;color:#5f6368;margin-bottom:12px;"><?= h($propTitle ?? 'This stay') ?></div>
        <?php if (!$wrLoggedIn): ?>
          <div style="background:#f8f9fa;border:1px solid #e8eaed;border-radius:10px;padding:14px;font-size:13.5px;color:#3c4043;line-height:1.6;">
            Sign in to write a review. Only guests with a paid stay can review.
          </div>
          <a href="<?= $this->Url->build($wrLoginUrl) ?>" class="btn fw-bold w-100 mt-3 py-2 rounded-pill" style="background:#0f62fe;color:#fff;border:none;">Sign in</a>
        <?php else: ?>
          <div id="wr-error" class="d-none" style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;padding:10px 12px;border-radius:8px;font-size:13px;margin-bottom:12px"></div>
          <label style="font-size:13px;font-weight:700;color:#1a1d25;display:block;margin-bottom:8px;">Your rating</label>
          <div id="wr-stars" role="radiogroup" aria-label="Star rating" style="display:flex;gap:6px;margin-bottom:14px">
            <?php for ($i = 1; $i <= 5; $i++): ?>
              <button type="button" data-wr-star="<?= $i ?>" role="radio" aria-checked="false" aria-label="<?= $i ?> star<?= $i > 1 ? 's' : '' ?>" style="background:none;border:none;padding:2px;cursor:pointer;line-height:1"><i class="fa-solid fa-star" style="font-size:26px;color:#e0e0e0"></i></button>
            <?php endfor; ?>
          </div>
          <label for="wr-comment" style="font-size:13px;font-weight:700;color:#1a1d25;display:block;margin-bottom:8px;">Your review <span style="font-weight:400;color:#5f6368">(optional)</span></label>
          <textarea id="wr-comment" rows="4" maxlength="2000" placeholder="How was your stay?" style="width:100%;border:1px solid #dadce0;border-radius:10px;padding:10px 12px;font-size:13.5px;color:#1a1d25;resize:vertical"></textarea>
          <div style="font-size:12px;color:#9aa0a6;text-align:right;margin-top:4px"><span id="wr-count">0</span>/2000</div>
          <button type="button" id="wr-submit" class="btn fw-bold w-100 mt-2 py-2 rounded-pill" style="background:#0f62fe;color:#fff;border:none;">Submit review</button>
          <div style="font-size:12px;color:#5f6368;margin-top:10px;line-height:1.5;">One review per stay. Only guests with a paid stay at this property can review.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
