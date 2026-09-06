<!-- Sleek fastnetstays.com Top Loading Progress Bar with Safe Area & Glow -->
<div id="home-top-loader-bar" class="home-top-loader-bar"></div>

<!-- Mobile Floating Capsule Pill Indicator (fastnetstays.com App Style) -->
<div id="home-mobile-loader-pill" class="d-md-none pill-hidden">
	<div class="mobile-loader-pill-inner">
		<div class="mobile-loader-spinner"></div>
		<span class="mobile-loader-text">Finding best stays...</span>
	</div>
</div>

<style>
/* ── Top Progress Loader Bar ── */
.home-top-loader-bar {
	position: fixed;
	top: 0;
	top: env(safe-area-inset-top, 0px);
	left: 0;
	width: 35%;
	height: 3px;
	background: linear-gradient(90deg, #d93025 0%, #f29900 35%, #1a73e8 70%, #007fad 100%);
	display: none;
	opacity: 0;
	z-index: 999999;
	transition: width 0.35s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.3s ease;
	box-shadow: 0 0 12px rgba(26, 115, 232, 0.8), 0 0 4px rgba(242, 153, 0, 0.6);
	pointer-events: none;
}

/* ── Mobile Floating Pill Indicator ── */
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

/* ── Strict Mutual Exclusivity: NEVER display shimmer and products at the same time ── */
#home-app-root.is-shimmer-loading .home-real-container {
	display: none !important;
}
#home-app-root.is-shimmer-loading .home-shimmer-container {
	display: flex !important;
}

#home-app-root:not(.is-shimmer-loading) .home-shimmer-container {
	display: none !important;
}
#home-app-root:not(.is-shimmer-loading) .home-real-container {
	display: flex !important;
}
</style>
