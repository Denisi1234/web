<!-- Floating Capsule Pill Indicator (all screens — desktop included)

     The top progress bar that used to live here was a fifth, independent
     loading implementation (its own #home-top-loader-bar with a red/amber/blue
     gradient that clashed with the product's single blue). Progress is now
     owned solely by FastnetLoading.bar, which renders
     #fastnet-top-progress from the app-loader element. -->
<div id="home-mobile-loader-pill" class="pill-hidden">
	<div class="mobile-loader-pill-inner">
		<div class="mobile-loader-spinner"></div>
		<span class="mobile-loader-text">Finding best stays...</span>
	</div>
</div>

<style>
#home-mobile-loader-pill {
	position: fixed;
	top: calc(env(safe-area-inset-top, 0px) + 64px);
	left: 50%;
	transform: translateX(-50%) translateY(0);
	z-index: 999990;
	pointer-events: none;
	transition: opacity 0.28s ease, transform 0.32s cubic-bezier(0.34, 1.56, 0.64, 1);
	opacity: 0;
}
#home-mobile-loader-pill.pill-hidden {
	opacity: 0 !important;
	transform: translateX(-50%) translateY(-14px) scale(0.92) !important;
}
.mobile-loader-pill-inner {
	display: inline-flex;
	align-items: center;
	gap: 10px;
	background: rgba(15, 23, 42, 0.92);
	backdrop-filter: blur(16px);
	-webkit-backdrop-filter: blur(16px);
	color: #ffffff;
	padding: 8px 18px 8px 14px;
	border-radius: 9999px;
	box-shadow: 0 10px 30px rgba(0, 0, 0, 0.28), 0 0 0 1px rgba(255, 255, 255, 0.12);
}
.mobile-loader-spinner {
	width: 15px;
	height: 15px;
	border: 2px solid rgba(255, 255, 255, 0.25);
	border-top-color: #38bdf8;
	border-right-color: #f29900;
	border-radius: 50%;
	animation: mobileSpin 0.75s linear infinite;
}
@keyframes mobileSpin {
	to { transform: rotate(360deg); }
}
.mobile-loader-text {
	font-size: 13px;
	font-weight: 700;
	letter-spacing: -0.01em;
	color: #f8fafc;
	white-space: nowrap;
}

/* ── Universal Shimmer Wave & Skeleton Animation ── */
@keyframes fastnetShimmerWave {
	0% {
		transform: translateX(-100%);
	}
	100% {
		transform: translateX(100%);
	}
}
.fastnet-shimmer {
	position: relative;
	overflow: hidden;
	background-color: #f1f5f9;
}
.fastnet-shimmer::after {
	position: absolute;
	top: 0;
	right: 0;
	bottom: 0;
	left: 0;
	transform: translateX(-100%);
	background-image: linear-gradient(
		90deg,
		rgba(255, 255, 255, 0) 0,
		rgba(255, 255, 255, 0.45) 20%,
		rgba(255, 255, 255, 0.8) 60%,
		rgba(255, 255, 255, 0) 100%
	);
	animation: fastnetShimmerWave 1.4s infinite;
	content: '';
}
.shimmer-rounded-xs { border-radius: 4px !important; }
.shimmer-rounded-sm { border-radius: 6px !important; }
.shimmer-rounded { border-radius: 10px !important; }
.shimmer-circle { border-radius: 50% !important; }

<!-- The mutual-exclusivity rules that used to live here all targeted
     #home-app-root, an element no template renders. Content/shimmer swapping
     is handled by FastnetLoading.skeleton() against #gh-cards-container and
     #gh-shimmer-container, which do exist. -->
</style>
