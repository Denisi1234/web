/* Nav search. Split for the 300-line cap — load dates, suggest, fetch, guests in order (shared globals). */
async function fetchLiveSuggestions(query) {
    const token = getNavMapboxToken();
    const titleEl = document.getElementById('nav_recent_header_title');

    try {
        // Concurrently query backend suggestions and Mapbox places
        const backendPromise = fetch('/api/search/suggestions?q=' + encodeURIComponent(query), {
            headers: { 'Accept': 'application/json' }
        }).then(r => r.ok ? r.json() : null).catch(() => null);

        let mapboxPromise = Promise.resolve(null);
        if (token && token.length > 5) {
            const endpoint = `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(query)}.json?access_token=${token}&autocomplete=true&types=place,locality,neighborhood,poi,district,region,address,country&language=en,sw&proximity=39.2083,-6.7924&limit=5`;
            mapboxPromise = fetch(endpoint).then(r => r.ok ? r.json() : null).catch(() => null);
        }

        const [backendData, mapboxData] = await Promise.all([backendPromise, mapboxPromise]);

        const combined = [];

        // 0. Backend destinations (cities/areas) - previously ignored entirely,
        // so a city match never appeared even though the endpoint returns it.
        if (backendData && Array.isArray(backendData.destinations)) {
            backendData.destinations.forEach(d => {
                if (!d || !d.city) return;
                combined.push({
                    name: d.city,
                    subtitle: (d.propertiesCount ? d.propertiesCount + ' stays · ' : '')
                        + (d.starting_price ? 'from TSh ' + Number(d.starting_price).toLocaleString() : '')
                        || 'Destination',
                    lat: null,
                    lng: null,
                    icon: 'fa-solid fa-city',
                    iconClass: 'icon-blue'
                });
            });
        }

        // 1. Add Backend Properties
        if (backendData && Array.isArray(backendData.properties)) {
            backendData.properties.forEach(p => {
                combined.push({
                    name: p.name,
                    subtitle: [p.district, p.city].filter(Boolean).join(', ') + ' · Lodge',
                    lat: null,
                    lng: null,
                    icon: 'fa-solid fa-hotel',
                    iconClass: 'icon-orange',
                    image: p.image || null,
                    score: (p.score === null || p.score === undefined) ? null : Number(p.score),
                    propertyId: p.id
                });
            });
        }

        // 2. Add Backend Destinations
        if (backendData && Array.isArray(backendData.destinations)) {
            backendData.destinations.forEach(d => {
                const cityName = d.city || d;
                const count = d.properties_count ? `${d.properties_count} properties` : 'Popular destination';
                const price = d.min_price ? ` · from TSh ${Number(d.min_price).toLocaleString()}` : '';
                combined.push({
                    name: cityName,
                    subtitle: `${count}${price} · Tanzania`,
                    lat: null,
                    lng: null,
                    icon: 'fa-solid fa-city',
                    iconClass: 'icon-blue'
                });
            });
        }

        // 3. Add Mapbox places (deduplicate)
        if (mapboxData && Array.isArray(mapboxData.features)) {
            mapboxData.features.forEach(f => {
                const name = f.text || f.place_name;
                if (combined.some(c => c.name.toLowerCase() === name.toLowerCase())) {
                    return;
                }
                let icon = 'fa-solid fa-location-dot';
                let iconClass = 'icon-blue';
                const placeType = f.place_type ? f.place_type[0] : '';
                if (placeType === 'place' || placeType === 'locality') {
                    icon = 'fa-solid fa-city';
                    iconClass = 'icon-blue';
                } else if (placeType === 'poi') {
                    icon = 'fa-solid fa-hotel';
                    iconClass = 'icon-orange';
                } else if (placeType === 'neighborhood' || placeType === 'district') {
                    icon = 'fa-solid fa-map-pin';
                    iconClass = 'icon-green';
                }

                let subtitle = '';
                if (f.context && f.context.length > 0) {
                    subtitle = f.context.map(c => c.text).join(', ');
                } else {
                    subtitle = f.place_name || 'Tanzania';
                }

                combined.push({
                    name: name,
                    subtitle: subtitle,
                    lat: f.center ? f.center[1] : null,
                    lng: f.center ? f.center[0] : null,
                    icon: icon,
                    iconClass: iconClass
                });
            });
        }

        navCurrentSuggestions = combined.slice(0, 8);
        navActiveSuggestIdx = -1;

        if (titleEl) {
            titleEl.innerText = navCurrentSuggestions.length > 0 ? `Suggestions for "${query}"` : `No stays or locations found for "${query}"`;
        }
        renderSuggestionList(navCurrentSuggestions, query);
    } catch (err) {
        console.warn('Auto-suggestion fallback:', err);
        const filtered = NAV_POPULAR_DESTINATIONS.filter(p =>
            p.name.toLowerCase().includes(query.toLowerCase()) ||
            p.subtitle.toLowerCase().includes(query.toLowerCase())
        );
        navCurrentSuggestions = filtered;
        navActiveSuggestIdx = -1;
        if (titleEl) titleEl.innerText = `Destinations matching "${query}"`;
        renderSuggestionList(filtered, query);
    }
}

function selectNavDestination(name, subtitle, lat, lng, propertyId) {
    if (propertyId) {
        window.location.href = '/hotel-detail?id=' + encodeURIComponent(propertyId);
        return;
    }

    const input = document.getElementById('nav_dest_input');
    const hiddenLat = document.getElementById('nav_hidden_lat');
    const hiddenLng = document.getElementById('nav_hidden_lng');

    if (input) input.value = name;
    if (hiddenLat && lat !== null && lat !== undefined) hiddenLat.value = lat;
    if (hiddenLng && lng !== null && lng !== undefined) hiddenLng.value = lng;

    closeNavPopups();

    if (window.trivagoMap && typeof window.trivagoMap.flyTo === 'function' && lat && lng) {
        window.trivagoMap.flyTo({
            center: [lng, lat],
            zoom: 12.5,
            essential: true
        });
    }

    const datePod = document.getElementById('nav_date_pod_ci') || document.getElementById('nav_date_pod_co');
    if (datePod && typeof openNavDatePicker === 'function') {
        setTimeout(() => {
            openNavDatePicker();
        }, 80);
    } else {
        submitNavSearchForm();
    }
}
