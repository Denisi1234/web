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
// Single-source: thin top bar via FastnetLoading only. The dark floating
// capsule pill was removed (clashed with Carbon white theme, tripled with
// bar + skeletons). These two functions stay as bar-only aliases so existing
// callers (index.php, hero.php, hotel-detail.php) keep working unchanged.
window.showHomeLoader = function () {
	if (window.FastnetLoading && FastnetLoading.bar) FastnetLoading.bar.start();
};

window.hideHomeLoader = function () {
	if (window.FastnetLoading && FastnetLoading.bar) FastnetLoading.bar.done();
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
