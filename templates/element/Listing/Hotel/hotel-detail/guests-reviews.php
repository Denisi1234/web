<?php
$reviewList = !empty($reviews) ? $reviews : [];
?>
<div class="card-body p-0 pt-2">
    <!-- Write a Review Accordion/Collapsible -->
    <div class="mb-4 p-3 border border-slate-200 rounded-3 bg-white">
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
                    <div class="p-3 border rounded-3 bg-slate-50/50 h-100">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <div class="square--35 circle bg-orange-100 text-orange-600 fw-bold fs-6 d-flex align-items-center justify-content-center">
                                    <?= strtoupper(substr($author, 0, 1)) ?>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold text-slate-900 fs-sm"><?= h($author) ?></h6>
                                    <span class="text-xs text-muted"><?= h($date) ?></span>
                                </div>
                            </div>
                            <div class="text-warning text-xs">
                                <?= str_repeat('★', $stars) ?>
                            </div>
                        </div>
                        <p class="text-slate-600 text-sm mb-0 italic">"<?= h($comment) ?>"</p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-4 px-3 border rounded-3 bg-slate-50">
            <i class="fa-regular fa-comment-dots fs-2 text-slate-400 mb-2"></i>
            <h6 class="fw-bold text-slate-700 mb-1">No Reviews Yet</h6>
            <p class="text-sm text-slate-500 mb-0">Be the first verified guest to share your experience for this stay.</p>
        </div>
    <?php endif; ?>
</div>

<script>
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
</script>