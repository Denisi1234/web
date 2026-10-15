/* Site chrome + navigation. Split for the 300-line cap — load custom-site.js then custom-nav.js. */
(function ($) {
	"use strict";

	// Back-to-top floating button removed: pages behave like mature
	// mobile apps with no floating chrome over content.



	// Carousel — guard if flickity not loaded (defer order)
	if ($.fn.flickity) {
		$('.main-carousel').flickity({
			// options
			cellAlign: 'center',
			contain: true
		});
	}


	// Tooltip
	var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
	var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
		return new bootstrap.Tooltip(tooltipTriggerEl)
	});
})(this.jQuery);
