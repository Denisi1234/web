/* Write-a-review for the hotel detail page.
 * Star picker + JSON submit to same-origin POST /hotel-detail/review.
 * The CakePHP action forwards with the session token and busts the
 * stays review cache, so the new review shows immediately. */
(function () {
  'use strict';

  var rating = 0;
  var propertyId = 0;

  function stars() {
    return Array.prototype.slice.call(document.querySelectorAll('#wr-stars [data-wr-star]'));
  }

  function paint() {
    stars().forEach(function (btn) {
      var v = parseInt(btn.getAttribute('data-wr-star'), 10);
      var icon = btn.querySelector('i');
      if (icon) icon.style.color = v <= rating ? '#f59e0b' : '#e0e0e0';
      btn.setAttribute('aria-checked', v === rating ? 'true' : 'false');
    });
  }

  function fail(msg) {
    var box = document.getElementById('wr-error');
    var btn = document.getElementById('wr-submit');
    if (box) {
      box.textContent = msg;
      box.classList.remove('d-none');
    }
    if (btn) {
      btn.disabled = false;
      btn.textContent = 'Submit review';
    }
  }

  window.openWriteReview = function (propId, loggedIn) {
    propertyId = parseInt(propId, 10) || 0;
    rating = 0;
    var box = document.getElementById('wr-error');
    if (box) {
      box.textContent = '';
      box.classList.add('d-none');
    }
    var comment = document.getElementById('wr-comment');
    if (comment) comment.value = '';
    var count = document.getElementById('wr-count');
    if (count) count.textContent = '0';
    paint();
    var modalEl = document.getElementById('writeReviewModal');
    if (!modalEl) return;
    if (!loggedIn) {
      var link = modalEl.querySelector('a[href*="/login"]');
      if (link) {
        window.location.href = link.getAttribute('href');
        return;
      }
    }
    if (window.bootstrap && bootstrap.Modal) {
      bootstrap.Modal.getOrCreateInstance(modalEl).show();
    } else {
      modalEl.style.display = 'block';
    }
  };

  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  function prependReview(review) {
    var scroll = document.getElementById('agoda_review_scroll');
    if (!scroll) return;
    // Drop the "no reviews" empty state once a real review exists.
    var card = scroll.closest('.agoda-rating-card');
    var author = (review.user_name || review.guest_name || '').trim();
    var text = (review.comment || '').trim();
    if (text.length > 110) text = text.slice(0, 107) + '...';
    var div = document.createElement('div');
    div.className = 'agoda-review-snippet';
    div.innerHTML = (text !== '' ? '&ldquo;' + escapeHtml(text) + '&rdquo;' : '') +
      '<div class="agoda-review-author">' +
      (author !== '' ? '<b>' + escapeHtml(author) + '</b>' : '') +
      '</div>';
    scroll.prepend(div);
    if (card) {
      var empty = card.querySelector('.agoda-rating-score');
      if (empty && empty.textContent.trim() === 'New') {
        window.location.reload();
      }
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    stars().forEach(function (btn) {
      btn.addEventListener('click', function () {
        rating = parseInt(btn.getAttribute('data-wr-star'), 10) || 0;
        var box = document.getElementById('wr-error');
        if (box) {
          box.textContent = '';
          box.classList.add('d-none');
        }
        paint();
      });
    });

    var comment = document.getElementById('wr-comment');
    if (comment) {
      comment.addEventListener('input', function () {
        var count = document.getElementById('wr-count');
        if (count) count.textContent = String(comment.value.length);
      });
    }

    var submit = document.getElementById('wr-submit');
    if (!submit) return;
    submit.addEventListener('click', function () {
      if (rating < 1 || rating > 5) {
        fail('Please select a star rating from 1 to 5.');
        return;
      }
      submit.disabled = true;
      submit.textContent = 'Submitting...';
      var box = document.getElementById('wr-error');
      if (box) {
        box.textContent = '';
        box.classList.add('d-none');
      }
      fetch('/hotel-detail/review', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({
          property_id: propertyId,
          rating: rating,
          comment: comment ? comment.value.trim() : ''
        })
      })
        .then(function (r) {
          return r.json().catch(function () { return {}; }).then(function (data) {
            return { status: r.status, data: data };
          });
        })
        .then(function (res) {
          var data = res.data || {};
          if ((res.status === 200 || res.status === 201) && data.success) {
            var modalEl = document.getElementById('writeReviewModal');
            if (window.bootstrap && bootstrap.Modal && modalEl) {
              bootstrap.Modal.getInstance(modalEl).hide();
            } else if (modalEl) {
              modalEl.style.display = 'none';
            }
            if (data.review) prependReview(data.review);
            submit.disabled = false;
            submit.textContent = 'Submit review';
            var target = document.getElementById('reviews');
            if (target && typeof target.scrollIntoView === 'function') {
              target.scrollIntoView({ behavior: 'smooth' });
            }
            return;
          }
          if (res.status === 401 && data.loginUrl) {
            window.location.href = data.loginUrl;
            return;
          }
          fail(data.error || 'Could not save your review. Please try again.');
        })
        .catch(function () {
          fail('Could not reach the server. Please try again.');
        });
    });
  });
})();
