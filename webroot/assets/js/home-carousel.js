/**
 * fastnetstays.com - Home Carousel & Loader Controller
 * Clean, lightweight, non-blocking navigation, arrow boundary handling,
 * and seamless loading/shimmer state transitions.
 */

function initHorizontalCarousel(containerId, prevBtnId, nextBtnId, cardSelector) {
	const container = document.getElementById(containerId);
	const prevBtn = document.getElementById(prevBtnId);
	const nextBtn = document.getElementById(nextBtnId);
	if (!container) return;

	function updateArrows() {
		if (!prevBtn || !nextBtn) return;
		const scrollLeft = Math.ceil(container.scrollLeft);
		const maxScrollLeft = container.scrollWidth - container.clientWidth;

		if (container.scrollWidth <= container.clientWidth + 5) {
			if (prevBtn.parentElement) prevBtn.parentElement.style.display = 'none';
			return;
		} else {
			if (prevBtn.parentElement) prevBtn.parentElement.style.display = 'flex';
		}

		if (scrollLeft <= 5) {
			prevBtn.disabled = true;
			prevBtn.style.opacity = '0.35';
			prevBtn.style.cursor = 'default';
		} else {
			prevBtn.disabled = false;
			prevBtn.style.opacity = '1';
			prevBtn.style.cursor = 'pointer';
		}

		if (scrollLeft >= maxScrollLeft - 5) {
			nextBtn.disabled = true;
			nextBtn.style.opacity = '0.35';
			nextBtn.style.cursor = 'default';
		} else {
			nextBtn.disabled = false;
			nextBtn.style.opacity = '1';
			nextBtn.style.cursor = 'pointer';
		}
	}

	function getScrollAmount() {
		const firstCard = container.querySelector(cardSelector);
		if (firstCard) {
			return firstCard.offsetWidth + 20;
		}
		return container.clientWidth * 0.75;
	}

	if (prevBtn) {
		prevBtn.onclick = function() {
			container.scrollBy({ left: -getScrollAmount(), behavior: 'smooth' });
		};
	}
	if (nextBtn) {
		nextBtn.onclick = function() {
			container.scrollBy({ left: getScrollAmount(), behavior: 'smooth' });
		};
	}

	// Smooth mouse wheel scroll conversion without event hijacking
	container.addEventListener('wheel', function(e) {
		if (e.deltaY !== 0) {
			container.scrollLeft += e.deltaY;
		}
	}, { passive: true });

	container.addEventListener('scroll', updateArrows);
	window.addEventListener('resize', updateArrows);
	updateArrows();
}

// ── Home loader ──────────────────────────────────────────────────────────
// The progress bar delegates to FastnetLoading so the homepage shows the same
// indicator as every other page. This used to be a second, independent bar
// with its own 35%→75%→100% timings, so home felt slower and behaved
// differently from the rest of the product.
//
// The mobile pill is a genuinely separate surface (a bottom sheet affordance,
// not a progress indicator) and keeps its own show/hide.
window.showHomeLoader = function (customText) {
	const pill = document.getElementById('home-mobile-loader-pill');
	const textEl = pill ? pill.querySelector('.mobile-loader-text') : null;

	if (textEl && customText) textEl.innerText = customText;

	FastnetLoading.bar.start();
	if (pill) pill.classList.remove('pill-hidden');
};

window.hideHomeLoader = function () {
	FastnetLoading.bar.done();

	const pill = document.getElementById('home-mobile-loader-pill');
	if (pill) pill.classList.add('pill-hidden');
};

// Reveal server-rendered homepage content immediately; loaders are reserved for navigation.
function revealHomeContent() {
	window.hideHomeLoader();

	// Re-initialize carousels now that real content is displayed
	initHorizontalCarousel('resorts-carousel-container', 'btn-resorts-prev', 'btn-resorts-next', '.resorts-carousel-card');
	initHorizontalCarousel('destinations-carousel-container', 'btn-destinations-prev', 'btn-destinations-next', '.destinations-carousel-card');
}

if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', revealHomeContent, { once: true });
} else {
	revealHomeContent();
}
