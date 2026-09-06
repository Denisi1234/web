/**
 * Advanced Mapbox & OpenStreetMap Real Geocoding Autocomplete Engine for Tanzania
 * Features Mapbox Places Geocoding API, popular region previews, dynamic backend lodge integration, and keyboard navigation
 */
(function ($) {
	"use strict";

	let debounceTimer = null;
	let backendProperties = [];
	const MAPBOX_ACCESS_TOKEN = window.MAPBOX_TOKEN || window.DEFAULT_MAPBOX_TOKEN || '';

	// Popular Tanzanian Destinations shown on focus
	const popularTanzaniaHubs = [
		{ title: "Dodoma", subTitle: "Capital City Region, Central Zone", category: "Capital City", lat: -6.1630, lng: 35.7516, icon: "fa-city" },
		{ title: "Dar es Salaam", subTitle: "Coastal Metropolis (Mbezi Beach, Kinondoni, Masaki)", category: "Major Hub", lat: -6.7924, lng: 39.2083, icon: "fa-city" },
		{ title: "Zanzibar", subTitle: "Island Resorts (Nungwi Beach, Paje, Stone Town)", category: "Beach Resorts", lat: -6.1659, lng: 39.2026, icon: "fa-umbrella-beach" },
		{ title: "Arusha", subTitle: "Safari Gateway & Lodge District (Sekei)", category: "Safari Gateway", lat: -3.3869, lng: 36.6830, icon: "fa-mountain-sun" },
		{ title: "Serengeti", subTitle: "National Safari Park & Luxury Camps", category: "National Park", lat: -2.3333, lng: 34.8333, icon: "fa-hippo" },
		{ title: "Kilimanjaro / Moshi", subTitle: "Mountain Region & Trekking Hub", category: "Mountain Region", lat: -3.3396, lng: 37.3400, icon: "fa-mountain" }
	];

	// Fetch backend live properties dynamically (environment aware: localhost & production)
	try {
		const endpoint = (typeof window.API_URL === 'function')
			? window.API_URL('/api/properties')
			: ((window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1' || window.location.hostname === '')
				? 'http://127.0.0.1:8000/api/properties'
				: 'https://api.fastnetstays.com/api/properties');

		fetch(endpoint)
			.then(res => res.json())
			.then(data => {
				const props = data.data || data.items || data;
				if (Array.isArray(props)) {
					backendProperties = props;
				}
			}).catch(() => {});
	} catch (e) {}

	function highlightMatch(text, query) {
		if (!query) return text;
		const idx = text.toLowerCase().indexOf(query.toLowerCase());
		if (idx === -1) return text;
		return text.substring(0, idx) + '<strong class="text-primary">' + text.substring(idx, idx + query.length) + '</strong>' + text.substring(idx + query.length);
	}

	function getPlaceIcon(type) {
		switch ((type || '').toLowerCase()) {
			case 'place': case 'city': case 'town': return 'fa-city';
			case 'locality': case 'suburb': case 'neighborhood': return 'fa-location-dot';
			case 'hotel': case 'lodging': case 'guest_house': return 'fa-hotel';
			case 'beach': return 'fa-umbrella-beach';
			case 'region': case 'district': return 'fa-map-pin';
			case 'poi': case 'attraction': return 'fa-mountain-sun';
			default: return 'fa-location-dot';
		}
	}

	// Geocode location search via Mapbox Places API with OpenStreetMap fallback
	function geocodeMapLocations(query, callback) {
		if (!query || query.trim().length === 0) {
			callback([]);
			return;
		}

		const mapboxUrl = `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(query)}.json?country=tz&types=place,locality,neighborhood,district,region,poi&access_token=${MAPBOX_ACCESS_TOKEN}`;

		fetch(mapboxUrl)
			.then(res => {
				if (!res.ok) throw new Error("Mapbox response error");
				return res.json();
			})
			.then(data => {
				if (data && Array.isArray(data.features) && data.features.length > 0) {
					const mapboxResults = data.features.map(f => {
						const name = f.text || (f.place_name ? f.place_name.split(',')[0] : 'Location');
						const full = f.place_name || name;
						const parts = full.split(',');
						const subTitle = parts.slice(1, 3).join(',').trim() || 'Tanzania';
						const type = f.place_type && f.place_type[0] ? f.place_type[0] : 'Location';
						const category = type.charAt(0).toUpperCase() + type.slice(1);
						const lng = f.center ? f.center[0] : 35.7516;
						const lat = f.center ? f.center[1] : -6.1630;

						return {
							title: name.trim(),
							fullTitle: full,
							subTitle: subTitle,
							category: category,
							lat: lat,
							lng: lng,
							icon: getPlaceIcon(type)
						};
					});

					// Combine matching backend properties
					const matchingBackend = filterBackendProperties(query);
					callback([...matchingBackend, ...mapboxResults]);
				} else {
					fallbackOsmGeocoding(query, callback);
				}
			})
			.catch(() => {
				fallbackOsmGeocoding(query, callback);
			});
	}

	function fallbackOsmGeocoding(query, callback) {
		const nominatimUrl = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&countrycodes=tz&addressdetails=1&limit=6`;
		fetch(nominatimUrl, {
			headers: { 'Accept': 'application/json', 'User-Agent': 'FastnetStays-TravelApp/1.0' }
		})
		.then(res => res.json())
		.then(data => {
			const osmResults = Array.isArray(data) ? data.map(place => {
				const name = place.name || (place.display_name ? place.display_name.split(',')[0] : 'Location');
				const parts = place.display_name ? place.display_name.split(',') : [name];
				const subTitle = parts.slice(1, 3).join(',').trim() || 'Tanzania';
				const category = place.type ? place.type.charAt(0).toUpperCase() + place.type.slice(1) : (place.class || 'Location');
				return {
					title: name.trim(),
					fullTitle: place.display_name || name,
					subTitle: subTitle,
					category: category,
					lat: parseFloat(place.lat),
					lng: parseFloat(place.lon),
					icon: getPlaceIcon(place.type)
				};
			}) : [];

			const matchingBackend = filterBackendProperties(query);
			callback([...matchingBackend, ...osmResults]);
		})
		.catch(() => {
			const matchingBackend = filterBackendProperties(query);
			const matchingPopular = popularTanzaniaHubs.filter(h => h.title.toLowerCase().includes(query.toLowerCase()));
			callback([...matchingBackend, ...matchingPopular]);
		});
	}

	function filterBackendProperties(query) {
		return backendProperties.filter(p => {
			const pName = (p.name || '').toLowerCase();
			const pCity = (p.city || p.area || '').toLowerCase();
			const q = query.toLowerCase();
			return pName.includes(q) || pCity.includes(q);
		}).map(p => ({
			title: p.name,
			fullTitle: `${p.name}, ${p.city || p.area || 'Tanzania'}`,
			subTitle: `${p.area || p.city || 'Tanzania'} (Verified Stay)`,
			category: 'Verified Stay',
			lat: parseFloat(p.latitude || -6.7924),
			lng: parseFloat(p.longitude || 39.2083),
			icon: 'fa-hotel'
		}));
	}

	document.querySelectorAll(".flightInput").forEach(input => {
		const container = input.closest(".autocomplete-container");
		if (!container) return;
		let suggestionsBox = container.querySelector(".suggestions");

		if (!suggestionsBox) {
			suggestionsBox = document.createElement("div");
			suggestionsBox.className = "suggestions";
			container.appendChild(suggestionsBox);
		}

		let selectedIndex = -1;

		const renderSuggestions = (items, query, isPopularHeader = false) => {
			suggestionsBox.innerHTML = "";
			selectedIndex = -1;

			if (!items || items.length === 0) {
				if (query && query.length >= 2) {
					suggestionsBox.innerHTML = `<div class="p-3 text-xs text-muted text-center"><i class="fa-solid fa-spinner fa-spin me-1"></i> Searching Mapbox places...</div>`;
					suggestionsBox.style.display = 'block';
				} else {
					suggestionsBox.style.display = 'none';
				}
				return;
			}

			suggestionsBox.style.display = 'block';

			if (isPopularHeader) {
				const header = document.createElement("div");
				header.className = "px-3 py-2 bg-slate-100 text-slate-700 text-xs fw-bold text-uppercase border-bottom tracking-wider d-flex align-items-center justify-content-between";
				header.innerHTML = `<span>Popular Destinations in Tanzania</span> <i class="fa-solid fa-fire text-orange-500"></i>`;
				suggestionsBox.appendChild(header);
			}

			items.forEach((place, index) => {
				const item = document.createElement("div");
				item.className = "suggestion-item d-flex align-items-center justify-content-between p-2.5 border-bottom cursor-pointer hover:bg-slate-50 transition";
				item.dataset.index = index;
				item.innerHTML = `
					<div class="d-flex align-items-center gap-2.5 overflow-hidden">
						<div class="rounded-circle bg-light-primary text-primary d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; flex-shrink: 0;">
							<i class="fa-solid ${place.icon || 'fa-location-dot'}" style="font-size: 13px;"></i>
						</div>
						<div class="overflow-hidden">
							<div class="fw-bold text-dark text-sm mb-0 text-truncate">${highlightMatch(place.title, query)}</div>
							<div class="text-xs text-slate-500 text-truncate" style="max-width: 240px;">${place.subTitle}</div>
						</div>
					</div>
					<span class="badge bg-slate-100 text-slate-600 text-xs fw-normal px-2 py-1 ms-2 flex-shrink-0">${place.category}</span>
				`;
				item.onclick = (e) => {
					e.stopPropagation();
					selectPlace(place);
				};
				suggestionsBox.appendChild(item);
			});
		};

		function selectPlace(place) {
			input.value = place.title;
			input.dataset.lat = place.lat;
			input.dataset.lng = place.lng;
			input.dataset.placeType = place.category;
			suggestionsBox.innerHTML = "";
			suggestionsBox.style.display = 'none';

			const event = new CustomEvent('locationSelected', { detail: place });
			document.dispatchEvent(event);
		}

		const handleInput = () => {
			const query = input.value.trim();
			clearTimeout(debounceTimer);

			if (query.length === 0) {
				renderSuggestions(popularTanzaniaHubs, "", true);
				return;
			}

			suggestionsBox.innerHTML = `<div class="p-3 text-xs text-muted text-center"><i class="fa-solid fa-spinner fa-spin me-1 text-primary"></i> Geocoding place...</div>`;
			suggestionsBox.style.display = 'block';

			debounceTimer = setTimeout(() => {
				geocodeMapLocations(query, (results) => {
					renderSuggestions(results, query, false);
				});
			}, 200);
		};

		input.addEventListener("focus", handleInput);
		input.addEventListener("input", handleInput);

		// Keyboard Navigation (Arrow Keys + Enter)
		input.addEventListener("keydown", (e) => {
			const items = suggestionsBox.querySelectorAll('.suggestion-item');
			if (!items.length) return;

			if (e.key === 'ArrowDown') {
				e.preventDefault();
				selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
				highlightItem(items);
			} else if (e.key === 'ArrowUp') {
				e.preventDefault();
				selectedIndex = Math.max(selectedIndex - 1, 0);
				highlightItem(items);
			} else if (e.key === 'Enter' && selectedIndex >= 0) {
				e.preventDefault();
				items[selectedIndex].click();
			}
		});

		function highlightItem(items) {
			items.forEach((it, idx) => {
				if (idx === selectedIndex) {
					it.classList.add('bg-slate-100');
					it.scrollIntoView({ block: 'nearest' });
				} else {
					it.classList.remove('bg-slate-100');
				}
			});
		}
	});

	document.addEventListener("click", e => {
		document.querySelectorAll(".suggestions").forEach(box => {
			if (!e.target.closest(".autocomplete-container")) {
				box.style.display = 'none';
			}
		});
	});

})(window.jQuery || {});