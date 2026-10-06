/* Site chrome + navigation. Split for the 300-line cap — load custom-site.js then custom-nav.js. */
(function ($) {
	"use strict";

	/*---- Bottom To Top Scroll Script ---*/
	$(window).on('scroll', function () {
		var height = $(window).scrollTop();
		if (height > 100) {
			$('#back2Top').fadeIn();
		} else {
			$('#back2Top').fadeOut();
		}
	});



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

	$("#back2Top").on('click', function (event) {
		event.preventDefault();
		$("html, body").animate({ scrollTop: 0 }, "slow");
		return false;
	});
})(this.jQuery);
