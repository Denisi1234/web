<!-- Best Locations Design Start (fastnetstays.com) -->
<section class="py-5 bg-slate-50 position-relative overflow-hidden">
	<style>
	/* Horizontal Destinations Carousel Styling */
	.destinations-horizontal-carousel {
		display: flex !important;
		flex-direction: row !important;
		flex-wrap: nowrap !important;
		overflow-x: auto !important;
		overflow-y: hidden !important;
		scroll-behavior: smooth;
		-webkit-overflow-scrolling: touch;
		scroll-snap-type: x mandatory;
		scroll-padding-left: 10px;
		gap: 20px;
		padding: 4px 2px 16px 2px;
		-ms-overflow-style: none;
		scrollbar-width: none;
		width: 100%;
		max-width: 100%;
	}
	.destinations-horizontal-carousel::-webkit-scrollbar {
		display: none;
	}

	.destinations-carousel-card {
		flex: 0 0 calc(25% - 15px) !important;
		flex-shrink: 0 !important;
		min-width: calc(25% - 15px) !important;
		max-width: calc(25% - 15px) !important;
		scroll-snap-align: start;
		box-sizing: border-box;
	}

	@media (max-width: 1199px) and (min-width: 992px) {
		.destinations-carousel-card {
			flex: 0 0 calc(33.333% - 14px) !important;
			min-width: calc(33.333% - 14px) !important;
			max-width: calc(33.333% - 14px) !important;
		}
	}
	@media (max-width: 991px) and (min-width: 576px) {
		.destinations-carousel-card {
			flex: 0 0 280px !important;
			min-width: 280px !important;
			max-width: 280px !important;
		}
	}
	@media (max-width: 575px) {
		.destinations-horizontal-carousel {
			padding: 4px 16px 16px 16px !important;
			gap: 14px !important;
			scroll-padding-left: 16px !important;
		}
		.destinations-carousel-card {
			flex: 0 0 80% !important;
			min-width: 80% !important;
			max-width: 80% !important;
		}
	}
	@media (min-width: 992px) {
		.destinations-carousel-card .cardCities {
			transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s ease !important;
		}
		.destinations-carousel-card:hover .cardCities {
			transform: translateY(-4px) !important;
			box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12) !important;
		}
		#btn-destinations-prev:hover:not(:disabled),
		#btn-destinations-next:hover:not(:disabled) {
			background-color: #007fad !important;
			color: #ffffff !important;
			border-color: #007fad !important;
			box-shadow: 0 4px 12px rgba(0, 127, 173, 0.25) !important;
		}
	}
	</style>

	<div class="container max-w-[1440px]">
		<!-- Heading & Desktop Nav Arrows -->
		<div class="d-flex align-items-end justify-content-between mb-4">
			<div>
				<h2 class="fw-bold text-slate-900 mb-1" style="font-size: clamp(20px, 3.5vw, 26px); letter-spacing: -0.02em;">Explore Hot Destinations</h2>
				<p class="text-slate-600 mb-0 text-sm">Explore top rated travel spots from Zanzibar beaches to Serengeti safaris.</p>
			</div>
			<!-- Desktop Scroll Arrows -->
			<div id="destinations-arrows-group" class="d-none d-md-flex align-items-center gap-2">
				<button type="button" id="btn-destinations-prev" class="btn btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center p-0 shadow-xs" style="width: 36px; height: 36px; transition: opacity 0.2s ease, background-color 0.2s ease, color 0.2s ease;" aria-label="Previous destinations" disabled>
					<i class="fa-solid fa-chevron-left" style="font-size: 12px;"></i>
				</button>
				<button type="button" id="btn-destinations-next" class="btn btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center p-0 shadow-xs" style="width: 36px; height: 36px; transition: opacity 0.2s ease, background-color 0.2s ease, color 0.2s ease;" aria-label="Next destinations">
					<i class="fa-solid fa-chevron-right" style="font-size: 12px;"></i>
				</button>
			</div>
		</div>

		<!-- Single Row Horizontal Destinations Carousel Container -->
		<div id="destinations-carousel-container" class="destinations-horizontal-carousel">
			<?= $this->element('Home/home-5/destinations'); ?>
		</div>
	</div>
</section>
<!-- Best Locations Design End -->
