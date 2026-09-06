<?php
$this->assign('title', 'Hotel Search & Deals | fastnetstays.com');
?>
<?= $this->Html->css('/assets/css/search-spacing.css') ?>

<!-- Include Top Navbar -->
<?= $this->element('navbar') ?>

<!-- Trivago Horizontal Filter Pills Strip -->
<?= $this->element('Listing/Hotel/hotel-list-01/search'); ?>

<!-- Trivago Main Content (Split Screen: List + Interactive Map) -->
<section class="py-3" style="background-color: #f3f4f6; min-height: 85vh;">
	<div class="container-fluid px-2 px-md-3 px-lg-4 max-w-[1440px] mx-auto">
		<?php if (!empty($searchErrors)): ?>
			<div class="alert alert-warning d-flex align-items-start gap-2 mb-3" role="alert">
				<i class="fa-solid fa-circle-info mt-1" aria-hidden="true"></i>
				<div>
					<strong>Search updated</strong>
					<ul class="mb-0 ps-3">
						<?php foreach ($searchErrors as $searchError): ?>
							<li><?= h($searchError) ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
		<?php endif; ?>

		<!-- Results Count Subheader -->
		<?= $this->element('Listing/Hotel/hotel-list-01/showing'); ?>

		<div class="row g-3 position-relative">
			
			<!-- Left: Hotel Deal Comparison Cards List -->
			<div class="col-xl-7 col-lg-7 col-md-12" id="hotel_list_col">
				<?= $this->element('Listing/Hotel/hotel-list-01/list'); ?>
				<?= $this->element('Listing/Hotel/hotel-list-01/pagination'); ?>
			</div>

			<!-- Right: Sticky Interactive Mapbox Map -->
			<div class="col-xl-5 col-lg-5 d-none d-lg-block" id="hotel_map_col">
				<div class="sticky-top" style="top: 130px; z-index: 10;">
					<div class="rounded-3 border overflow-hidden shadow-sm bg-white position-relative" style="height: calc(100vh - 160px); min-height: 540px;">
						
						<!-- Mapbox Map Container -->
						<div id="trivago-interactive-map" style="width: 100%; height: 100%;"></div>

						<!-- Map Controls Overlay (Fullscreen, Reset View) -->
						<div class="position-absolute top-0 start-0 m-3 d-flex flex-column gap-2" style="z-index: 10;">
							<button type="button" class="btn btn-white shadow-sm border rounded-2 p-2 square--36 bg-white d-flex align-items-center justify-content-center" onclick="toggleMapFullscreen()" title="Fullscreen">
								<i class="fa-solid fa-expand text-slate-700"></i>
							</button>
							<button type="button" class="btn btn-white shadow-sm border rounded-2 p-2 square--36 bg-white d-flex align-items-center justify-content-center" onclick="resetMapView()" title="Reset view">
								<i class="fa-solid fa-rotate-right text-slate-700"></i>
							</button>
						</div>

						<!-- Satellite Map Toggle Thumbnail Bottom Right -->
						<div class="position-absolute bottom-0 end-0 m-3" style="z-index: 10;">
							<div class="border border-2 border-white rounded-2 overflow-hidden shadow-sm cursor-pointer" style="width: 52px; height: 52px;" onclick="toggleSatelliteLayer()" title="Toggle satellite imagery">
                                <span id="map_style_thumb" class="w-100 h-100 d-flex align-items-center justify-content-center bg-light text-slate-700" aria-hidden="true"><i class="fa-solid fa-layer-group"></i></span>
							</div>
						</div>

					</div>
				</div>
			</div>

		</div>
	</div>
</section>

<!-- Mobile Floating Map / List View Switch Pill -->
<div class="d-lg-none position-fixed bottom-0 start-50 translate-middle-x mb-4" style="z-index: 1040;">
	<button type="button" id="mobile_view_toggle_btn" class="btn btn-dark shadow-lg rounded-pill px-4 py-2.5 fw-bold d-inline-flex align-items-center gap-2 border border-2 border-white" style="font-size: 14px; background: #0f172a;" onclick="toggleMobileListView()">
		<i class="fa-solid fa-map-location-dot text-warning" id="mobile_view_toggle_icon"></i>
		<span id="mobile_view_toggle_text">View Map</span>
	</button>
</div>

<style>
/* Live price marker bubble */
.trivago-price-marker {
    align-items: center;
    background: #ffffff;
    border: 1.5px solid #0f172a;
    border-radius: 20px;
    box-shadow: 0 3px 10px rgba(15, 23, 42, 0.22);
    color: #0f172a;
    display: inline-flex;
    font-size: 12px;
    font-weight: 800;
    justify-content: center;
    line-height: 1;
    min-height: 28px;
    padding: 6px 11px;
    position: relative;
    white-space: nowrap;
    cursor: pointer;
    transition: transform 0.18s cubic-bezier(0.34, 1.56, 0.64, 1), background-color 0.15s ease, color 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
    user-select: none;
    z-index: 10;
}
.trivago-price-marker::after {
    content: '';
    position: absolute;
    bottom: -6px;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 0;
    border-left: 6px solid transparent;
    border-right: 6px solid transparent;
    border-top: 6px solid #0f172a;
    transition: border-top-color 0.15s ease;
}
.trivago-price-marker::before {
    content: '';
    position: absolute;
    bottom: -4px;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 0;
    border-left: 5px solid transparent;
    border-right: 5px solid transparent;
    border-top: 5px solid #ffffff;
    transition: border-top-color 0.15s ease;
    z-index: 1;
}
.trivago-price-marker:hover,
.trivago-price-marker.hovered,
.trivago-price-marker:focus-visible,
.trivago-price-marker.active {
    background: #007fad !important;
    color: #ffffff !important;
    border-color: #005a7a !important;
    box-shadow: 0 6px 18px rgba(0, 127, 173, 0.45) !important;
    z-index: 9999 !important;
}
.trivago-price-marker:hover::after,
.trivago-price-marker.hovered::after,
.trivago-price-marker:focus-visible::after,
.trivago-price-marker.active::after {
    border-top-color: #005a7a !important;
}
.trivago-price-marker:hover::before,
.trivago-price-marker.hovered::before,
.trivago-price-marker:focus-visible::before,
.trivago-price-marker.active::before {
    border-top-color: #007fad !important;
}
.trivago-price-marker.visited {
    background: #f8fafc;
    color: #475569;
    border-color: #94a3b8;
}
.trivago-price-marker.visited::after {
    border-top-color: #94a3b8;
}
.trivago-price-marker.visited::before {
    border-top-color: #f8fafc;
}

/* Destination search pin */
.trivago-destination-marker {
    display: inline-flex;
    align-items: center;
    background: #ffffff;
    border: 1.5px solid #0284c7;
    border-radius: 20px;
    padding: 3px 8px;
    font-size: 11px;
    font-weight: 700;
    color: #0f172a;
    box-shadow: 0 2px 8px rgba(2, 132, 199, 0.25);
    white-space: nowrap;
    position: relative;
    user-select: none;
    cursor: default;
}
.trivago-destination-marker .dest-pulse {
    position: absolute;
    top: -4px;
    left: -4px;
    right: -4px;
    bottom: -4px;
    border-radius: 24px;
    border: 2px solid rgba(2, 132, 199, 0.45);
    animation: destMarkerPulse 2s infinite ease-in-out;
    pointer-events: none;
}
@keyframes destMarkerPulse {
    0% { transform: scale(0.95); opacity: 0.8; }
    50% { transform: scale(1.08); opacity: 0.2; }
    100% { transform: scale(0.95); opacity: 0.8; }
}

/* Custom Mapbox Popup */
.trivago-mapbox-popup .mapboxgl-popup-content {
    border-radius: 12px !important;
    padding: 0 !important;
    box-shadow: 0 8px 28px rgba(15, 23, 42, 0.20) !important;
    border: 1px solid #e2e8f0 !important;
    overflow: hidden !important;
    width: 260px !important;
}
.trivago-mapbox-popup .mapboxgl-popup-close-button {
    font-size: 16px;
    color: #ffffff;
    background: rgba(0,0,0,0.55);
    border-radius: 50%;
    width: 22px;
    height: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    top: 8px;
    right: 8px;
    z-index: 10;
    border: none;
    line-height: 1;
    padding: 0;
    transition: background 0.15s ease;
}
.trivago-mapbox-popup .mapboxgl-popup-close-button:hover {
    background: rgba(0,0,0,0.85);
    color: #ffffff;
}

.fn-map-card-popup {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}
.fn-map-popup-thumb {
    position: relative;
    width: 100%;
    height: 125px;
    overflow: hidden;
    background: #f1f5f9;
}
.fn-map-popup-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.2s ease;
}
.fn-map-popup-thumb:hover img {
    transform: scale(1.04);
}
.fn-map-popup-stars {
    position: absolute;
    bottom: 6px;
    left: 8px;
    background: rgba(15, 23, 42, 0.7);
    color: #ea580c;
    padding: 1px 5px;
    border-radius: 4px;
    font-size: 11px;
    letter-spacing: 1px;
    line-height: 1.2;
}
.fn-map-popup-info {
    padding: 10px 12px 12px;
}
.fn-map-popup-title {
    color: #0f172a;
    font-size: 13.5px;
    font-weight: 700;
    line-height: 1.25;
    margin-bottom: 4px;
    display: block;
    text-decoration: none;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.fn-map-popup-title:hover {
    color: #007fad;
}
.fn-map-popup-rating {
    display: flex;
    align-items: center;
    gap: 4px;
    margin-bottom: 5px;
}
.fn-map-popup-location {
    color: #64748b;
    font-size: 11px;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.fn-map-popup-bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 8px;
    border-top: 1px solid #f1f5f9;
}
.fn-map-popup-price {
    color: #0f172a;
    font-size: 14px;
    font-weight: 800;
}
.fn-map-popup-unit {
    color: #64748b;
    font-size: 10.5px;
}

/* Mobile full-screen map overlay when active */
@media (max-width: 991px) {
    #hotel_map_col.mobile-active {
        display: block !important;
        position: fixed !important;
        top: 125px !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        width: 100vw !important;
        height: calc(100vh - 125px) !important;
        z-index: 1030 !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    #hotel_map_col.mobile-active .sticky-top {
        position: static !important;
        height: 100% !important;
    }
    #hotel_map_col.mobile-active .rounded-3 {
        border-radius: 0 !important;
        border: none !important;
        height: 100% !important;
        min-height: 100% !important;
    }
}
</style>

<script>
// Initialize Mapbox with live backend property markers.
let hotelMapInitialized = false;
function initializeHotelMap() {
    if (hotelMapInitialized) return;
    if (typeof mapboxgl === 'undefined') {
        console.warn("Mapbox GL JS is not loaded.");
        return;
    }

    const token = window.MAPBOX_TOKEN || window.DEFAULT_MAPBOX_TOKEN || '';
    if (!token) {
        return;
    }
    hotelMapInitialized = true;
    mapboxgl.accessToken = token;

    <?php
    $searchLat = !empty($queryParams['lat']) && is_numeric($queryParams['lat']) ? (float)$queryParams['lat'] : null;
    $searchLng = !empty($queryParams['lng']) && is_numeric($queryParams['lng']) ? (float)$queryParams['lng'] : null;
    $searchDest = !empty($queryParams['destination']) ? (string)$queryParams['destination'] : '';

    $firstProp = !empty($properties) ? $properties[0] : null;
    $firstLat = !empty($firstProp['latitude']) && is_numeric($firstProp['latitude']) ? (float)$firstProp['latitude'] : null;
    $firstLng = !empty($firstProp['longitude']) && is_numeric($firstProp['longitude']) ? (float)$firstProp['longitude'] : null;

    $centerLat = $firstLat ?? ($searchLat ?? -6.7924);
    $centerLng = $firstLng ?? ($searchLng ?? 39.2083);

    $destinationPin = ($searchLat !== null && $searchLng !== null) ? [
        'lat' => $searchLat,
        'lng' => $searchLng,
        'name' => !empty($searchDest) ? ucwords($searchDest) : 'Search Center'
    ] : null;

    $mapMarkers = array_map(function($p) use ($queryParams) {
        if (empty($p['latitude']) || empty($p['longitude']) || !is_numeric($p['latitude']) || !is_numeric($p['longitude'])) {
            return null;
        }
        $lat = (float)$p['latitude'];
        $lng = (float)$p['longitude'];
        $priceFormatted = !empty($p['customer_price_formatted'])
            ? $p['customer_price_formatted']
            : (!empty($p['price_per_night']) ? 'TSh ' . number_format((float)$p['price_per_night']) : '');
        $propertyId = (int)($p['id'] ?? 0);
        if ($propertyId < 1) {
            return null;
        }
        $area = $p['area'] ?? ($p['city'] ?? '');
        $city = $p['city'] ?? '';
        $locationText = trim(implode(', ', array_filter([ucwords($area), ucwords($city)])));

        $stars = !empty($p['star_rating']) ? max(1, min(5, (int)$p['star_rating'])) : 0;
        $rating = !empty($p['reviews_avg_rating']) ? number_format((float)$p['reviews_avg_rating'], 1) : (!empty($p['rating']) ? number_format((float)$p['rating'], 1) : null);
        $ratingWord = $rating !== null ? (($rating >= 9.0) ? 'Excellent' : (($rating >= 8.0) ? 'Very good' : 'Good')) : '';
        $reviewsCount = (int)($p['reviews_count'] ?? 0);

        $img = '';
        if (!empty($p['image_url'])) $img = $p['image_url'];
        elseif (!empty($p['primary_image_url'])) $img = $p['primary_image_url'];
        elseif (!empty($p['cover_image'])) $img = $p['cover_image'];
        if ($img !== '' && !str_starts_with($img, 'http')) {
            $img = $this->Url->build('/' . ltrim($img, '/'));
        }

        $queryString = !empty($queryParams) ? '?' . http_build_query($queryParams) : '';

        return [
            'id' => $propertyId,
            'name' => $p['name'] ?? 'Stay',
            'price' => $priceFormatted,
            'location' => $locationText ?: ucwords($area),
            'lat' => $lat,
            'lng' => $lng,
            'image' => $img,
            'rating' => $rating,
            'rating_word' => $ratingWord,
            'reviews_count' => $reviewsCount,
            'stars' => $stars,
            'url' => $this->Url->build('/hotel-detail/' . $propertyId . $queryString)
        ];
    }, $properties ?? []);
    $mapMarkers = array_values(array_filter($mapMarkers));
    ?>

    const defaultLat = <?= json_encode($centerLat) ?>;
    const defaultLng = <?= json_encode($centerLng) ?>;
    const hotelMarkersData = <?= json_encode($mapMarkers) ?>;
    const destinationPin = <?= json_encode($destinationPin) ?>;

    let isSatellite = false;
    const streetStyle = 'mapbox://styles/mapbox/streets-v12';
    const satelliteStyle = 'mapbox://styles/mapbox/satellite-streets-v12';
    const markerMap = {};
    const createdMarkers = [];

    const mapContainer = document.getElementById('trivago-interactive-map');
    if (!mapContainer) return;

    try {
        window.trivagoMap = new mapboxgl.Map({
            container: 'trivago-interactive-map',
            style: streetStyle,
            center: [defaultLng, defaultLat],
            zoom: hotelMarkersData.length === 1 ? 13.5 : 12,
            dragPan: true,
            scrollZoom: true,
            doubleClickZoom: true,
            touchZoomRotate: true,
            keyboard: true,
            boxZoom: true
        });

        window.trivagoMap.addControl(new mapboxgl.NavigationControl({ showCompass: true }), 'top-right');

        const escapeHtml = function (value) {
            return String(value ?? '').replace(/[&<>'"]/g, function (character) {
                return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;' })[character];
            });
        };

        // Render dynamic price marker bubbles for hotels
        hotelMarkersData.forEach(function(item) {
            const el = document.createElement('div');
            el.className = 'trivago-price-marker';
            el.id = 'map-marker-' + item.id;
            el.setAttribute('role', 'button');
            el.setAttribute('tabindex', '0');
            el.setAttribute('aria-label', (item.name || 'Stay') + ': ' + (item.price || ''));
            el.innerHTML = `<span>${escapeHtml(item.price || 'View')}</span>`;

            const popupHtml = `
                <div class="fn-map-card-popup">
                    ${item.image ? `
                        <div class="fn-map-popup-thumb">
                            <a href="${escapeHtml(item.url)}">
                                <img src="${escapeHtml(item.image)}" alt="${escapeHtml(item.name || 'Stay')}" />
                            </a>
                            ${item.stars > 0 ? `<span class="fn-map-popup-stars">${'★'.repeat(item.stars)}</span>` : ''}
                        </div>
                    ` : ''}
                    <div class="fn-map-popup-info">
                        <a href="${escapeHtml(item.url)}" class="fn-map-popup-title">${escapeHtml(item.name || 'Stay')}</a>
                        ${item.rating ? `
                            <div class="fn-map-popup-rating">
                                <span class="badge bg-success text-white px-1.5 py-0.5 rounded text-2xs fw-bold">${escapeHtml(item.rating)}</span>
                                <span class="text-slate-700 fw-semibold text-2xs">${escapeHtml(item.rating_word || 'Good')}</span>
                                ${item.reviews_count > 0 ? `<span class="text-slate-400 text-2xs">(${escapeHtml(item.reviews_count)})</span>` : ''}
                            </div>
                        ` : ''}
                        <div class="fn-map-popup-location">
                            <i class="fa-solid fa-location-dot text-slate-400 me-1"></i>
                            <span>${escapeHtml(item.location || 'Tanzania')}</span>
                        </div>
                        <div class="fn-map-popup-bottom">
                            <div class="fn-map-popup-price-wrap">
                                <span class="fn-map-popup-price">${escapeHtml(item.price)}</span>
                                <span class="fn-map-popup-unit">/ night</span>
                            </div>
                            <a href="${escapeHtml(item.url)}" class="btn btn-sm btn-primary py-1 px-2.5 rounded-2 fw-bold text-xs">View Deal</a>
                        </div>
                    </div>
                </div>
            `;

            const popup = new mapboxgl.Popup({
                offset: [0, -12],
                closeButton: true,
                closeOnClick: false,
                maxWidth: '280px',
                className: 'trivago-mapbox-popup'
            }).setHTML(popupHtml);

            const marker = new mapboxgl.Marker({ element: el, anchor: 'bottom' })
                .setLngLat([item.lng, item.lat])
                .setPopup(popup)
                .addTo(window.trivagoMap);

            markerMap[item.id] = {
                marker: marker,
                element: el,
                popup: popup,
                lat: item.lat,
                lng: item.lng
            };

            popup.on('open', () => {
                el.classList.add('active');
                el.style.zIndex = '9999';
                highlightCardInList(item.id, true, false);
            });

            popup.on('close', () => {
                el.classList.remove('active');
                el.style.zIndex = '';
                el.classList.add('visited');
                highlightCardInList(item.id, false, false);
            });

            el.addEventListener('mouseenter', () => {
                el.classList.add('hovered');
                highlightCardInList(item.id, true, false);
            });

            el.addEventListener('mouseleave', () => {
                el.classList.remove('hovered');
                if (!popup.isOpen()) {
                    highlightCardInList(item.id, false, false);
                }
            });

            el.addEventListener('click', () => {
                window.trivagoMap.easeTo({
                    center: [item.lng, item.lat],
                    duration: 350
                });
                highlightCardInList(item.id, true, true);
            });

            el.addEventListener('keydown', function(event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    marker.togglePopup();
                }
            });

            createdMarkers.push(marker);
        });

        // Render destination center pin if search coords were provided
        if (destinationPin && destinationPin.lat && destinationPin.lng) {
            const destEl = document.createElement('div');
            destEl.className = 'trivago-destination-marker';
            destEl.setAttribute('title', destinationPin.name);
            destEl.innerHTML = `
                <span class="dest-pulse"></span>
                <i class="fa-solid fa-location-crosshairs text-primary me-1"></i>
                <span>${escapeHtml(destinationPin.name)}</span>
            `;
            const destMarker = new mapboxgl.Marker({ element: destEl, anchor: 'center' })
                .setLngLat([destinationPin.lng, destinationPin.lat])
                .addTo(window.trivagoMap);
            createdMarkers.push(destMarker);
        }

        // Fit map view to encompass all markers if multiple
        if (hotelMarkersData.length > 1) {
            const bounds = new mapboxgl.LngLatBounds();
            hotelMarkersData.forEach(function(m) {
                bounds.extend([m.lng, m.lat]);
            });
            if (destinationPin && destinationPin.lat) {
                bounds.extend([destinationPin.lng, destinationPin.lat]);
            }
            window.trivagoMap.fitBounds(bounds, { padding: 45, maxZoom: 14 });
        } else if (hotelMarkersData.length === 1 && destinationPin && destinationPin.lat) {
            const bounds = new mapboxgl.LngLatBounds();
            bounds.extend([hotelMarkersData[0].lng, hotelMarkersData[0].lat]);
            bounds.extend([destinationPin.lng, destinationPin.lat]);
            window.trivagoMap.fitBounds(bounds, { padding: 65, maxZoom: 13.5 });
        }

        function highlightCardInList(propId, isHighlight, scrollTo = false) {
            const card = document.getElementById('hotel-card-' + propId);
            if (!card) return;
            if (isHighlight) {
                card.classList.add('map-highlight-card');
                if (scrollTo) {
                    card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            } else {
                card.classList.remove('map-highlight-card');
            }
        }

        window.focusMapMarker = function(propId) {
            const obj = markerMap[propId];
            if (!obj || !window.trivagoMap) return;

            // Switch to mobile map view if on mobile screen
            const mapCol = document.getElementById('hotel_map_col');
            if (mapCol && window.innerWidth < 992 && !mapCol.classList.contains('mobile-active')) {
                window.toggleMobileListView();
            }

            window.trivagoMap.flyTo({
                center: [obj.lng, obj.lat],
                zoom: 14.5,
                essential: true
            });
            if (!obj.popup.isOpen()) {
                obj.marker.togglePopup();
            }
        };

        window.highlightMapMarker = function(propId, isHighlight) {
            const obj = markerMap[propId];
            if (!obj) return;
            if (isHighlight) {
                obj.element.classList.add('active');
                obj.element.style.zIndex = '9999';
            } else if (!obj.popup.isOpen()) {
                obj.element.classList.remove('active');
                obj.element.style.zIndex = '';
            }
        };

        window.resetMapView = function() {
            if (!window.trivagoMap) return;
            if (hotelMarkersData.length > 1) {
                const bounds = new mapboxgl.LngLatBounds();
                hotelMarkersData.forEach(m => bounds.extend([m.lng, m.lat]));
                if (destinationPin && destinationPin.lat) bounds.extend([destinationPin.lng, destinationPin.lat]);
                window.trivagoMap.fitBounds(bounds, { padding: 45, maxZoom: 14 });
            } else {
                window.trivagoMap.flyTo({
                    center: [defaultLng, defaultLat],
                    zoom: hotelMarkersData.length === 1 ? 13.5 : 12,
                    essential: true
                });
            }
        };

        window.toggleMapFullscreen = function() {
            const container = document.getElementById('trivago-interactive-map');
            if (!container) return;
            if (!document.fullscreenElement) {
                container.requestFullscreen().catch(err => alert(err.message));
            } else {
                document.exitFullscreen();
            }
        };

        window.toggleSatelliteLayer = function() {
            if (!window.trivagoMap) return;
            isSatellite = !isSatellite;
            const newStyle = isSatellite ? satelliteStyle : streetStyle;
            window.trivagoMap.setStyle(newStyle);
            
            // Re-add markers after style reloads
            window.trivagoMap.once('style.load', function() {
                createdMarkers.forEach(m => m.addTo(window.trivagoMap));
            });
        };

        // Mobile list / map view switcher
        window.toggleMobileListView = function() {
            const listCol = document.getElementById('hotel_list_col');
            const mapCol = document.getElementById('hotel_map_col');
            const btnText = document.getElementById('mobile_view_toggle_text');
            const btnIcon = document.getElementById('mobile_view_toggle_icon');
            if (!listCol || !mapCol) return;

            const isMapActive = mapCol.classList.contains('mobile-active');
            if (isMapActive) {
                // Switch back to list view
                mapCol.classList.remove('mobile-active');
                mapCol.classList.add('d-none');
                mapCol.classList.add('d-lg-block');
                listCol.style.display = 'block';
                if (btnText) btnText.innerText = 'View Map';
                if (btnIcon) btnIcon.className = 'fa-solid fa-map-location-dot text-warning';
            } else {
                // Switch to map view
                listCol.style.display = 'none';
                mapCol.classList.remove('d-none');
                mapCol.classList.add('mobile-active');
                if (btnText) btnText.innerText = 'View List';
                if (btnIcon) btnIcon.className = 'fa-solid fa-list-ul text-warning';
                if (window.trivagoMap) {
                    setTimeout(() => window.trivagoMap.resize(), 150);
                }
            }
        };

        setTimeout(function() {
            if (window.trivagoMap) window.trivagoMap.resize();
        }, 350);

    } catch (err) {
        console.error("Mapbox initialization error:", err);
    }
}
document.addEventListener('DOMContentLoaded', initializeHotelMap);
window.addEventListener('fastnet:mapbox-ready', initializeHotelMap);
</script>

<!-- Include Footer -->
<?= $this->element('footer', ['skin' => 'skin-dark-footer']) ?>
