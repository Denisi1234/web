<?php
$reviewList = !empty($reviews) ? $reviews : [];
$overall = !empty($property['rating']) ? (float)$property['rating'] : (!empty($reviews) ? array_sum(array_map(fn($r)=>(float)($r['rating']??5), $reviews))/max(1,count($reviews)) : 4.6);
$overallFmt = number_format($overall,1);
$catScores = [
  'Cleanliness' => min(5, $overall + 0.2),
  'Service' => min(5, $overall + 0.1),
  'Location' => max(3.5, $overall - 0.1),
  'Value' => max(3.5, $overall - 0.2),
];
?>
<div class="card-body p-0 pt-2">
    <!-- Google Review Score Breakdown -->
    <div class="gh-review-breakdown" style="display:flex;gap:20px;align-items:center;padding:16px;border:1px solid #dadce0;border-radius:12px;background:#fff;margin-bottom:20px;">
        <div style="flex:0 0 auto;text-align:center;">
            <div style="width:64px;height:64px;border-radius:50%;background:#e8f0fe;border:2px solid #1a73e8;display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:700;color:#1a73e8;font-family:'Google Sans',sans-serif;"><?= h($overallFmt) ?></div>
            <div style="font-size:12px;color:#5f6368;margin-top:4px;"><?= count($reviewList) ?> verified reviews</div>
            <div style="color:#fbbc04;font-size:14px;"><?= str_repeat('★', (int)round($overall)) ?></div>
        </div>
        <div style="flex:1 1 auto;display:grid;gap:8px;">
            <?php foreach($catScores as $label=>$score): $pct = ($score/5)*100; ?>
            <div style="display:flex;align-items:center;gap:10px;">
                <span style="flex:0 0 90px;font-size:12px;color:#5f6368;"><?= h($label) ?></span>
                <div style="flex:1;height:6px;background:#f1f3f4;border-radius:3px;overflow:hidden;"><div style="width:<?= $pct ?>%;height:100%;background:#1a73e8;border-radius:3px;"></div></div>
                <span style="flex:0 0 24px;font-size:12px;font-weight:500;color:#202124;text-align:right;"><?= number_format($score,1) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <!-- Write a Review Accordion/Collapsible -->
    <div style="margin-bottom:16px;padding:14px;border:1px solid #dadce0;border-radius:12px;background:#fff;">
        <button class="btn btn-outline-primary btn-sm fw-bold rounded-pill px-3" type="button" data-bs-toggle="collapse" data-bs-target="#writeReviewCollapse">
            <i class="fa-solid fa-pen-to-square me-1"></i>Write a Review
        </button>

        <div class="collapse mt-3" id="writeReviewCollapse">
            <div id="web1-review-alert"></div>
            <form id="web1-write-review-form" class="row g-3">
                <input type="hidden" id="web1-rev-property-id" value="<?= h($propertyId ?? 12) ?>">
                <div class="col-md-6">
                    <label class="form-label text-sm fw-semibold">Your Name</label>
                    <input type="text" id="web1-rev-author" class="form-control form-control-sm" placeholder="John Doe" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-sm fw-semibold">Rating (1 to 5 Stars)</label>
                    <select id="web1-rev-rating" class="form-select form-select-sm">
                        <option value="5" selected>⭐⭐⭐⭐⭐ (5/5) Excellent</option>
                        <option value="4">⭐⭐⭐⭐ (4/5) Very Good</option>
                        <option value="3">⭐⭐⭐ (3/5) Average</option>
                        <option value="2">⭐⭐ (2/5) Poor</option>
                        <option value="1">⭐ (1/5) Terrible</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label text-sm fw-semibold">Your Experience & Feedback</label>
                    <textarea id="web1-rev-comment" class="form-control form-control-sm" rows="3" placeholder="What did you enjoy about your stay?" required></textarea>
                </div>
                <div class="col-12">
                    <button type="submit" id="web1-rev-submit-btn" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold">Submit Review</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Live Reviews List -->
    <?php if (!empty($reviewList)): ?>
        <div class="row g-4">
            <?php foreach ($reviewList as $rev): 
                $author = $rev['user_name'] ?? ($rev['guest_name'] ?? ($rev['name'] ?? 'Verified Guest'));
                $stars = max(1, min(5, (int)($rev['rating'] ?? 5)));
                $comment = $rev['comment'] ?? ($rev['review'] ?? 'Exceptional stay.');
                $date = !empty($rev['created_at']) ? (is_string($rev['created_at']) && strlen($rev['created_at']) > 10 ? date('d M Y', strtotime($rev['created_at'])) : $rev['created_at']) : 'Recently';
            ?>
                <div class="col-xl-6 col-lg-6 col-md-12">
                    <div style="padding:14px;border:1px solid #dadce0;border-radius:12px;background:#fff;box-shadow:0 1px 3px rgba(60,64,67,0.06);">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:35px;height:35px;border-radius:50%;background:#fef7e0;color:#b7791f;font-weight:700;font-size:13px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <?= strtoupper(substr($author, 0, 1)) ?>
                                </div>
                                <div>
                                    <h6 style="margin:0;font-weight:500;color:#202124;font-size:13px;font-family:'Google Sans',sans-serif;"><?= h($author) ?></h6>
                                    <span style="font-size:11px;color:#5f6368;"><?= h($date) ?></span>
                                </div>
                            </div>
                            <div class="text-warning text-xs">
                                <?= str_repeat('★', $stars) ?>
                            </div>
                        </div>
                        <p style="color:#3c4043;font-size:13px;line-height:1.5;font-style:italic;margin:0;font-family:Roboto,sans-serif;">"<?= h($comment) ?>"</p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div style="text-align:center;padding:20px 14px;border:1px solid #dadce0;border-radius:12px;background:#f8f9fa;">
            <i class="fa-regular fa-comment-dots fs-2 text-slate-400 mb-2"></i>
            <h6 style="font-weight:500;color:#202124;font-family:'Google Sans',sans-serif;margin-bottom:4px;">No Reviews Yet</h6>
            <p style="font-size:12px;color:#5f6368;margin:0;font-family:Roboto,sans-serif;">Be the first verified guest to share your experience for this stay.</p>
        </div>
    <?php endif; ?>
</div>

<script>
(function(){
if (window._web1ReviewBound) return; window._web1ReviewBound = true;
document.addEventListener("DOMContentLoaded", function() {
    const revForm = document.getElementById("web1-write-review-form");
    const revAlert = document.getElementById("web1-review-alert");
    const revBtn = document.getElementById("web1-rev-submit-btn");

    if (revForm) {
        revForm.addEventListener("submit", async function(e) {
            e.preventDefault();
            revAlert.innerHTML = '';
            revBtn.disabled = true;

            const payload = {
                property_id: document.getElementById("web1-rev-property-id").value,
                user_name: document.getElementById("web1-rev-author").value.trim(),
                rating: parseInt(document.getElementById("web1-rev-rating").value),
                comment: document.getElementById("web1-rev-comment").value.trim()
            };

            try {
                const endpoint = (typeof window.API_URL === 'function') 
                    ? window.API_URL('/api/reviews') 
                    : 'http://127.0.0.1:8000/api/reviews';

                const token = localStorage.getItem('auth_token');
                const headers = {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                };
                if (token) {
                    headers['Authorization'] = 'Bearer ' + token;
                }

                const res = await fetch(endpoint, {
                    method: 'POST',
                    headers: headers,
                    body: JSON.stringify(payload)
                });
                const data = await res.json();

                if (res.ok || res.status === 201) {
                    revAlert.innerHTML = '<div class="alert alert-success py-2">Thank you! Your review has been submitted successfully.</div>';
                    revForm.reset();
                    setTimeout(() => { window.location.reload(); }, 1200);
                } else {
                    const errMsg = data.message || 'Failed to submit review.';
                    revAlert.innerHTML = '<div class="alert alert-danger py-2">' + errMsg + '</div>';
                }
            } catch(err) {
                console.error("Review error:", err);
                revAlert.innerHTML = '<div class="alert alert-danger py-2">Unable to connect to reviews server.</div>';
            } finally {
                revBtn.disabled = false;
            }
        });
    }
});
})();
</script>