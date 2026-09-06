<!-- Popular Hotels & Resorts Section Start (fastnetstays.com) -->
<section class="py-5 bg-white position-relative">
	<style>
	/* Horizontal Property Carousel Styling (Agoda OTA Reference) */
	.resorts-horizontal-carousel {
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
	}
	.resorts-horizontal-carousel::-webkit-scrollbar {
		display: none;
	}

	.resorts-carousel-card {
		flex: 0 0 285px !important;
		flex-shrink: 0 !important;
		min-width: 285px !important;
		max-width: 285px !important;
		scroll-snap-align: start;
		box-sizing: border-box;
	}

	@media (min-width: 1200px) {
		.resorts-carousel-card {
			flex: 0 0 calc(25% - 15px) !important;
			min-width: calc(25% - 15px) !important;
			max-width: calc(25% - 15px) !important;
		}
	}
	@media (max-width: 1199px) and (min-width: 992px) {
		.resorts-carousel-card {
			flex: 0 0 290px !important;
			min-width: 290px !important;
			max-width: 290px !important;
		}
	}
	@media (max-width: 991px) and (min-width: 576px) {
		.resorts-carousel-card {
			flex: 0 0 280px !important;
			min-width: 280px !important;
			max-width: 280px !important;
		}
	}
	@media (max-width: 575px) {
		.resorts-horizontal-carousel {
			padding: 4px 16px 16px 16px !important;
			gap: 14px !important;
			scroll-padding-left: 16px !important;
		}
		.resorts-carousel-card {
			flex: 0 0 84% !important;
			min-width: 84% !important;
			max-width: 84% !important;
		}
	}
	@media (min-width: 992px) {
		#btn-resorts-prev:hover:not(:disabled),
		#btn-resorts-next:hover:not(:disabled) {
			background-color: #007fad !important;
			color: #ffffff !important;
			border-color: #007fad !important;
			box-shadow: 0 4px 12px rgba(0, 127, 173, 0.25) !important;
		}
		.resorts-carousel-card .card {
			transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s ease !important;
		}
		.resorts-carousel-card:hover .card {
			transform: translateY(-4px) !important;
			box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08) !important;
		}
		.resorts-carousel-card .popFlights-item-overHidden img {
			transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1) !important;
		}
		.resorts-carousel-card:hover .popFlights-item-overHidden img {
			transform: scale(1.05) !important;
		}
	}
	</style>

	<div class="container max-w-[1440px]">
		<!-- Heading & Desktop Nav Arrows -->
		<div class="d-flex align-items-end justify-content-between mb-4">
			<div>
				<h2 class="fw-bold text-slate-900 mb-1" style="font-size: clamp(20px, 3.5vw, 26px); letter-spacing: -0.02em;">Popular Stays & Resorts</h2>
				<p class="text-slate-600 mb-0 text-sm">Discover hand-picked lodges, luxury resorts, and beach escapes across Tanzania.</p>
			</div>
			<!-- Desktop Scroll Arrows -->
			<div id="resorts-arrows-group" class="d-none d-md-flex align-items-center gap-2">
				<button type="button" id="btn-resorts-prev" class="btn btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center p-0 shadow-xs" style="width: 36px; height: 36px; transition: opacity 0.2s ease, background-color 0.2s ease, color 0.2s ease;" aria-label="Previous stays" disabled>
					<i class="fa-solid fa-chevron-left" style="font-size: 12px;"></i>
				</button>
				<button type="button" id="btn-resorts-next" class="btn btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center p-0 shadow-xs" style="width: 36px; height: 36px; transition: opacity 0.2s ease, background-color 0.2s ease, color 0.2s ease;" aria-label="Next stays">
					<i class="fa-solid fa-chevron-right" style="font-size: 12px;"></i>
				</button>
			</div>
		</div>

		<!-- Resorts Shimmer Skeleton Carousel -->
		<div id="resorts-shimmer-container" class="resorts-horizontal-carousel home-shimmer-container">
			<?php for ($s = 0; $s < 4; $s++): ?>
			<div class="resorts-carousel-card" style="scroll-snap-align: start; box-sizing: border-box;">
				<div class="card rounded-3 border h-100 shadow-sm overflow-hidden" style="pointer-events: none; border-color: #e2e8f0; background: #ffffff;">
					<div class="fastnet-shimmer" style="height: clamp(175px, 28vw, 200px); width: 100%; background-color: #e2e8f0;"></div>
					<div class="p-3 d-flex flex-column justify-content-between" style="min-height: 160px;">
						<div>
							<div class="d-flex align-items-center gap-2 mb-2">
								<div class="fastnet-shimmer shimmer-rounded-xs" style="width: 65px; height: 12px; background-color: #e2e8f0;"></div>
								<div class="fastnet-shimmer shimmer-rounded-xs" style="width: 80px; height: 12px; background-color: #e2e8f0;"></div>
							</div>
							<div class="fastnet-shimmer shimmer-rounded-xs mb-2" style="width: 85%; height: 18px; background-color: #e2e8f0;"></div>
							<div class="fastnet-shimmer shimmer-rounded-xs mb-2" style="width: 60%; height: 13px; background-color: #e2e8f0;"></div>
						</div>
						<div class="d-flex align-items-center justify-content-between pt-3 mt-2 border-top">
							<div class="fastnet-shimmer shimmer-rounded-xs" style="width: 45px; height: 20px; background-color: #e2e8f0;"></div>
							<div class="fastnet-shimmer shimmer-rounded-xs" style="width: 75px; height: 20px; background-color: #e2e8f0;"></div>
						</div>
					</div>
				</div>
			</div>
			<?php endfor; ?>
		</div>

		<!-- Single Row Horizontal Carousel Container (Real Database Content) -->
		<div id="resorts-carousel-container" class="resorts-horizontal-carousel home-real-container">
			<?= $this->element('Home/home-5/resorts'); ?>
		</div>
	</div>
</section>
<!-- Popular Hotels End -->
