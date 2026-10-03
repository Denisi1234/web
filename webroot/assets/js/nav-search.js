/**
 * fastnetstays.com - Navigation Header Hotel Search Controller
 * Handles destination clear, calendar datepicker, and guest counter modals.
 * Features timezone-safe date parsing, minimum 1-night validation, and range selection.
 */

let navAdults = 2;
let navChildren = 0;
let navRooms = 1;

const NAV_DEFAULT_ADULTS = 2;
const NAV_DEFAULT_CHILDREN = 0;
const NAV_DEFAULT_ROOMS = 1;

let navStartDate = null;
let navEndDate = null;
let navSelectingEndDate = false;
let navCalBaseMonth = null;

const navMonthNames = ["January","February","March","April","May","June","July","August","September","October","November","December"];
const navMonthShort = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];

function parseLocalDate(val) {
    if (!val) return null;
    if (val instanceof Date) {
        const d = new Date(val.getTime());
        d.setHours(0, 0, 0, 0);
        return d;
    }
    if (typeof val === 'string') {
        const parts = val.trim().split('-');
        if (parts.length === 3) {
            const y = parseInt(parts[0], 10);
            const m = parseInt(parts[1], 10) - 1;
            const d = parseInt(parts[2], 10);
            if (!isNaN(y) && !isNaN(m) && !isNaN(d)) {
                return new Date(y, m, d);
            }
        }
    }
    const fallback = new Date(val);
    if (!isNaN(fallback.getTime())) {
        fallback.setHours(0, 0, 0, 0);
        return fallback;
    }
    return null;
}

function formatLocalDate(d) {
    if (!d) return '';
    const pad = n => String(n).padStart(2, '0');
    return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
}

window.initNavSearch = function(config) {
    if (config) {
        if (typeof config.adults !== 'undefined') navAdults = Number(config.adults);
        if (typeof config.children !== 'undefined') navChildren = Number(config.children);
        if (typeof config.rooms !== 'undefined') navRooms = Number(config.rooms);
        if (config.checkIn) navStartDate = parseLocalDate(config.checkIn);
        if (config.checkOut) navEndDate = parseLocalDate(config.checkOut);
    }
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    if (!navStartDate || isNaN(navStartDate.getTime()) || navStartDate < today) {
        navStartDate = new Date(today);
        navStartDate.setDate(navStartDate.getDate() + 7);
    }
    // Strict validation: checkOut must be at least 1 day after checkIn
    if (!navEndDate || isNaN(navEndDate.getTime()) || navEndDate <= navStartDate) {
        navEndDate = new Date(navStartDate);
        navEndDate.setDate(navEndDate.getDate() + 5);
    }

    navCalBaseMonth = new Date(navStartDate.getFullYear(), navStartDate.getMonth(), 1);
    applyNavDateSelection(false);
    renderNavCalendars();
    updateNavResetBtnState();
};

function openNavDatePicker(e) {
    if (e) e.stopPropagation();
    closeNavPopups();
    const modal = document.getElementById('nav_datepicker_modal');
    const pods = [document.getElementById('nav_date_pod_ci'), document.getElementById('nav_date_pod_co')];
    if (modal) modal.classList.add('show');
    pods.forEach(function(pod){
        if (pod) { pod.style.outline = '2px solid #0f62fe'; pod.style.outlineOffset = '-2px'; pod.style.borderRadius = '6px'; }
    });
    renderNavCalendars();
}

function navNavCal(dir) {
    if (!navCalBaseMonth) {
        navCalBaseMonth = new Date();
        navCalBaseMonth.setDate(1);
    }
    navCalBaseMonth.setMonth(navCalBaseMonth.getMonth() + dir);
    renderNavCalendars();
}

function renderNavCalendars(hoverDate = null) {
    if (!document.getElementById('nav_month1_grid')) return;
    if (!navCalBaseMonth || isNaN(navCalBaseMonth.getTime())) {
        const base = (navStartDate && !isNaN(navStartDate.getTime())) ? navStartDate : new Date();
        navCalBaseMonth = new Date(base.getFullYear(), base.getMonth(), 1);
    }
    const m1Year = navCalBaseMonth.getFullYear();
    const m1Month = navCalBaseMonth.getMonth();
    
    const m2Date = new Date(m1Year, m1Month + 1, 1);
    const m2Year = m2Date.getFullYear();
    const m2Month = m2Date.getMonth();

    const t1 = document.getElementById('nav_month1_title');
    const t2 = document.getElementById('nav_month2_title');
    if (t1) t1.innerText = navMonthNames[m1Month] + ' ' + m1Year;
    if (t2) t2.innerText = navMonthNames[m2Month] + ' ' + m2Year;

    renderNavSingleMonth('nav_month1_grid', m1Year, m1Month, hoverDate);
    renderNavSingleMonth('nav_month2_grid', m2Year, m2Month, hoverDate);
}

function renderNavSingleMonth(elementId, year, month, hoverDate = null) {
    const grid = document.getElementById(elementId);
    if (!grid) return;
    grid.innerHTML = '';

    const firstDayIndex = new Date(year, month, 1).getDay();
    const totalDays = new Date(year, month + 1, 0).getDate();
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    for (let i = 0; i < firstDayIndex; i++) {
        const emptyCell = document.createElement('div');
        emptyCell.className = 'trivago-cal-day empty';
        grid.appendChild(emptyCell);
    }

    for (let d = 1; d <= totalDays; d++) {
        const thisDate = new Date(year, month, d);
        thisDate.setHours(0, 0, 0, 0);
        
        const cell = document.createElement('div');
        cell.className = 'trivago-cal-day';
        if (thisDate < today) cell.classList.add('disabled');

        const isStart = navStartDate && thisDate.getTime() === navStartDate.getTime();
        const effectiveEnd = navEndDate || (navSelectingEndDate ? hoverDate : null);
        const isEnd = effectiveEnd && thisDate.getTime() === effectiveEnd.getTime();
        const isInRange = navStartDate && effectiveEnd && thisDate > navStartDate && thisDate < effectiveEnd;

        if (isStart) cell.classList.add('range-start');
        if (isEnd) cell.classList.add('range-end');
        if (isInRange) cell.classList.add(navEndDate ? 'in-range' : 'hover-range');

        const inner = document.createElement('div');
        inner.className = 'trivago-cal-day-inner';
        inner.innerText = d;
        cell.appendChild(inner);

        if (!cell.classList.contains('disabled')) {
            cell.onclick = function(ev) {
                ev.stopPropagation();
                handleNavDateClick(thisDate);
            };
            cell.onmouseenter = function() {
                if (navSelectingEndDate && navStartDate && thisDate >= navStartDate) {
                    renderNavCalendars(thisDate);
                }
            };
        }
        grid.appendChild(cell);
    }
}

function handleNavDateClick(clickedDate) {
    clickedDate.setHours(0, 0, 0, 0);

    if (!navSelectingEndDate || !navStartDate) {
        // First click: Select check-in date
        navStartDate = clickedDate;
        navEndDate = null;
        navSelectingEndDate = true;
        
        paintNavDates(`${navStartDate.getDate()} ${navMonthShort[navStartDate.getMonth()]}`, 'Select dates', '');
    } else {
        // Second click: Select check-out date
        if (clickedDate.getTime() === navStartDate.getTime()) {
            // Same day clicked: auto-select 1 night stay
            navEndDate = new Date(navStartDate);
            navEndDate.setDate(navEndDate.getDate() + 1);
            navSelectingEndDate = false;
            applyNavDateSelection(true);
            closeNavPopups();
            submitNavSearchForm();
        } else if (clickedDate < navStartDate) {
            // Earlier date clicked: update check-in to this earlier date
            navStartDate = clickedDate;
            navEndDate = null;
            navSelectingEndDate = true;
            
            paintNavDates(`${navStartDate.getDate()} ${navMonthShort[navStartDate.getMonth()]}`, 'Select dates', '');
        } else {
            // Future check-out date selected (> navStartDate)
            navEndDate = clickedDate;
            navSelectingEndDate = false;
            applyNavDateSelection(true);
            closeNavPopups();
            submitNavSearchForm();
        }
    }
    renderNavCalendars();
}

function paintNavDates(ciText, coText, nightsText) {
    const ci = document.getElementById('nav_ci_display');
    const co = document.getElementById('nav_date_display');
    const badge = document.getElementById('nav_nights_badge');
    if (ci && ciText) ci.innerText = ciText;
    if (co && coText) co.innerText = coText;
    if (badge && nightsText) badge.innerText = nightsText;
    if (co) co.title = ciText && coText ? `${ciText} - ${coText}${nightsText ? ` (${nightsText})` : ''}` : '';
}
function applyNavDateSelection(syncInputs = true) {
    if (!navStartDate || !navEndDate) return;
    const startStr = formatLocalDate(navStartDate);
    const endStr = formatLocalDate(navEndDate);

    const checkInInput = document.getElementById('nav_hidden_checkin');
    const checkOutInput = document.getElementById('nav_hidden_checkout');
    if (checkInInput) checkInInput.value = startStr;
    if (checkOutInput) checkOutInput.value = endStr;

    const nights = Math.max(1, Math.round((navEndDate.getTime() - navStartDate.getTime()) / 86400000));
    const nightsText = nights === 1 ? '1 night' : `${nights} nights`;
    const displayStr = `${navStartDate.getDate()} ${navMonthShort[navStartDate.getMonth()]} - ${navEndDate.getDate()} ${navMonthShort[navEndDate.getMonth()]}`;

    paintNavDates(
        `${navStartDate.getDate()} ${navMonthShort[navStartDate.getMonth()]}`,
        `${navEndDate.getDate()} ${navMonthShort[navEndDate.getMonth()]}`,
        nightsText
    );
}

function submitNavSearchForm() {
    const form = document.getElementById('nav_search_form');
    if (form) form.submit();
}

function applyNavCalShortcut(type) {
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    if (type === 'tonight') {
        navStartDate = new Date(today);
        navEndDate = new Date(today);
        navEndDate.setDate(navEndDate.getDate() + 1);
    } else if (type === 'tomorrow') {
        navStartDate = new Date(today);
        navStartDate.setDate(navStartDate.getDate() + 1);
        navEndDate = new Date(navStartDate);
        navEndDate.setDate(navEndDate.getDate() + 1);
    } else if (type === 'this_weekend') {
        const day = today.getDay();
        const diffToFri = (5 - day + 7) % 7;
        navStartDate = new Date(today);
        navStartDate.setDate(today.getDate() + diffToFri);
        navEndDate = new Date(navStartDate);
        navEndDate.setDate(navEndDate.getDate() + 2);
    } else if (type === 'next_weekend') {
        const day = today.getDay();
        const diffToFri = (5 - day + 7) % 7 + 7;
        navStartDate = new Date(today);
        navStartDate.setDate(today.getDate() + diffToFri);
        navEndDate = new Date(navStartDate);
        navEndDate.setDate(navEndDate.getDate() + 2);
    }
    navSelectingEndDate = false;
    applyNavDateSelection(true);
    closeNavPopups();
    submitNavSearchForm();
}

/* ══════════════════════════════════════════════════════════════════════════════
 * Mapbox Real Auto-Suggestion & Destination Dropdown Controller
 * ══════════════════════════════════════════════════════════════════════════════ */

const NAV_POPULAR_DESTINATIONS = [
    { name: 'Dar es Salaam', subtitle: 'Coastal City · Commercial Hub & Lodges', icon: 'fa-solid fa-city', iconClass: 'icon-blue', lat: -6.7924, lng: 39.2083 },
    { name: 'Zanzibar City', subtitle: 'Stone Town & Beaches · Ocean Resorts', icon: 'fa-solid fa-umbrella-beach', iconClass: 'icon-blue', lat: -6.1659, lng: 39.2026 },
    { name: 'Arusha', subtitle: 'Safari Gateway · Mount Meru Lodges', icon: 'fa-solid fa-mountain', iconClass: 'icon-orange', lat: -3.3869, lng: 36.6830 },
    { name: 'Serengeti', subtitle: 'National Park · Wildlife Safari Camps', icon: 'fa-solid fa-paw', iconClass: 'icon-orange', lat: -2.3333, lng: 34.8333 },
    { name: 'Dodoma', subtitle: 'Capital City · Executive Hotels & Suites', icon: 'fa-solid fa-landmark', iconClass: 'icon-blue', lat: -6.1630, lng: 35.7516 },
    { name: 'Kilimanjaro', subtitle: 'Moshi · Alpine Foothills & Eco Stays', icon: 'fa-solid fa-volcano', iconClass: 'icon-orange', lat: -3.0674, lng: 37.3556 }
];

let navSuggestTimeout = null;
let navCurrentSuggestions = [];
let navActiveSuggestIdx = -1;

function getNavMapboxToken() {
    return window.MAPBOX_TOKEN || 
           window.DEFAULT_MAPBOX_TOKEN || 
           (typeof mapboxgl !== 'undefined' ? mapboxgl.accessToken : '');
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function highlightMatch(text, query) {
    if (!query || !text) return escapeHtml(text);
    const escapedQ = query.trim().replace(/[-[\]{}()*+?.,\\^$|#\s]/g, '\\$&');
    if (!escapedQ) return escapeHtml(text);
    const regex = new RegExp(`(${escapedQ})`, 'gi');
    return escapeHtml(text).replace(regex, '<mark class="fw-bold text-primary bg-transparent p-0">$1</mark>');
}

function getStoredRecentSearches() {
    return [];
}


function showDefaultDestinations() {
    // Previously called an undefined RecentSearches(), which threw before the
    // spinner was ever cleared — leaving the destination dots spinning forever
    // as soon as the user deleted back below two characters.
    const titleEl = document.getElementById('nav_recent_header_title');
    const listEl = document.getElementById('nav_dest_suggestions_list');
    if (!listEl) return;

    navCurrentSuggestions = [];
    navActiveSuggestIdx = -1;

    if (titleEl) titleEl.innerText = 'Popular Destinations in Tanzania';
    navCurrentSuggestions = [...NAV_POPULAR_DESTINATIONS];
    renderSuggestionList(navCurrentSuggestions, '');
}

function renderSuggestionList(items, query) {
    const listEl = document.getElementById('nav_dest_suggestions_list');
    if (!listEl) return;

    if (!items || items.length === 0) {
        listEl.innerHTML = `
            <div class="p-3 text-center text-slate-500" style="font-size: 13px;">
                <i class="fa-solid fa-map-location-dot text-slate-400 d-block fs-5 mb-2"></i>
                No stays or locations found. Press enter to search stays by keyword.
            </div>
        `;
        return;
    }

    let html = '';
    items.forEach((item, idx) => {
        const highlightedTitle = highlightMatch(item.name, query);
        const safeName = (item.name || '').replace(/'/g, "\\'");
        const safeSub = (item.subtitle || '').replace(/'/g, "\\'");
        const latVal = item.lat !== null && item.lat !== undefined ? item.lat : 'null';
        const lngVal = item.lng !== null && item.lng !== undefined ? item.lng : 'null';
        const propIdVal = item.propertyId ? item.propertyId : 'null';

        const iconMarkup = item.image
            ? `<img src="${escapeHtml(item.image)}" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:6px;">`
            : `<i class="${item.icon || 'fa-solid fa-location-dot'}"></i>`;

        const scoreMarkup = item.score
            ? `<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0" style="font-size:11px">★ ${item.score}</span>`
            : '';

        html += `
            <div class="trivago-recent-item" id="nav_suggest_item_${idx}" data-idx="${idx}" onclick="selectNavDestination('${safeName}', '${safeSub}', ${latVal}, ${lngVal}, ${propIdVal})">
                <div class="trivago-recent-icon ${item.iconClass || 'icon-blue'}">
                    ${iconMarkup}
                </div>
                <div class="trivago-recent-info" style="flex:1">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="trivago-recent-name">${highlightedTitle}</div>
                        ${scoreMarkup}
                    </div>
                    <div class="trivago-recent-meta">${escapeHtml(item.subtitle || '')}</div>
                </div>
            </div>
        `;
    });

    listEl.innerHTML = html;
}

function openNavRecentDropdown(e) {
    if (e) e.stopPropagation();
    closeNavPopups();

    const drop = document.getElementById('nav_recent_dropdown');
    const pod = document.getElementById('nav_dest_pod');
    const input = document.getElementById('nav_dest_input');

    if (drop) drop.classList.add('show');
    if (pod) {
        pod.style.outline = '2px solid #0f62fe';
        pod.style.outlineOffset = '-2px';
        pod.style.borderRadius = '6px';
    }

    const curVal = input ? input.value.trim() : '';
    if (curVal.length >= 2) {
        fetchLiveSuggestions(curVal);
    } else {
        showDefaultDestinations();
    }
}

function handleNavDestInput(e) {
    const val = (e.target.value || '').trim();
    clearTimeout(navSuggestTimeout);

    if (val.length < 2) {
        showDefaultDestinations();
        return;
    }

    const titleEl = document.getElementById('nav_recent_header_title');
    if (titleEl) {
        titleEl.innerHTML = `<span class="p-dots" aria-hidden="true"><span class="p-dot"></span><span class="p-dot"></span><span class="p-dot"></span></span> Searching stays & destinations...`;
    }

    navSuggestTimeout = setTimeout(() => {
        fetchLiveSuggestions(val);
    }, 200);
}

function handleNavDestKeydown(e) {
    const drop = document.getElementById('nav_recent_dropdown');
    const isOpen = drop && drop.classList.contains('show');

    if (!isOpen) {
        if (e.key === 'ArrowDown' || e.key === 'Enter') {
            openNavRecentDropdown(e);
            return;
        }
        return;
    }

    if (e.key === 'ArrowDown') {
        e.preventDefault();
        if (navCurrentSuggestions.length > 0) {
            navActiveSuggestIdx = (navActiveSuggestIdx + 1) % navCurrentSuggestions.length;
            updateSelectedSuggestionUi();
        }
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        if (navCurrentSuggestions.length > 0) {
            navActiveSuggestIdx = (navActiveSuggestIdx - 1 + navCurrentSuggestions.length) % navCurrentSuggestions.length;
            updateSelectedSuggestionUi();
        }
    } else if (e.key === 'Enter') {
        if (navActiveSuggestIdx >= 0 && navCurrentSuggestions[navActiveSuggestIdx]) {
            e.preventDefault();
            const chosen = navCurrentSuggestions[navActiveSuggestIdx];
            selectNavDestination(chosen.name, chosen.subtitle, chosen.lat, chosen.lng, chosen.propertyId);
        } else if (navCurrentSuggestions.length > 0) {
            e.preventDefault();
            const first = navCurrentSuggestions[0];
            selectNavDestination(first.name, first.subtitle, first.lat, first.lng, first.propertyId);
        } else {
            closeNavPopups();
            submitNavSearchForm();
        }
    } else if (e.key === 'Escape') {
        closeNavPopups();
    }
}

function updateSelectedSuggestionUi() {
    const items = document.querySelectorAll('#nav_dest_suggestions_list .trivago-recent-item');
    items.forEach((it, idx) => {
        if (idx === navActiveSuggestIdx) {
            it.classList.add('selected');
            it.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        } else {
            it.classList.remove('selected');
        }
    });
}

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

function clearNavDest(e) {
    if (e) e.stopPropagation();
    const input = document.getElementById('nav_dest_input');
    const hiddenLat = document.getElementById('nav_hidden_lat');
    const hiddenLng = document.getElementById('nav_hidden_lng');

    if (input) {
        input.value = '';
        input.focus();
    }
    if (hiddenLat) hiddenLat.value = '';
    if (hiddenLng) hiddenLng.value = '';

    showDefaultDestinations();
}

function selectNavRecent(name, cin, cout, ad, rm) {
    selectNavDestination(name, 'Destination', null, null);
}

function openNavGuestModal(e) {
    if (e) e.stopPropagation();
    closeNavPopups();
    const modal = document.getElementById('nav_guest_modal');
    const pod = document.getElementById('nav_guest_pod');
    if (modal) modal.classList.add('show');
    if (pod) {
        pod.style.outline = '2px solid #0f62fe';
        pod.style.outlineOffset = '-2px';
        pod.style.borderRadius = '6px';
    }
}

function updateNavGuestCount(type, delta) {
    if (type === 'adults') {
        navAdults = Math.max(1, navAdults + delta);
        document.getElementById('nav_adults_val').innerText = navAdults;
        document.getElementById('nav_hidden_adults').value = navAdults;
        const minus = document.getElementById('nav_adults_minus');
        if (minus) {
            if (navAdults <= 1) minus.classList.add('disabled');
            else minus.classList.remove('disabled');
        }
    } else if (type === 'children') {
        navChildren = Math.max(0, navChildren + delta);
        document.getElementById('nav_children_val').innerText = navChildren;
        document.getElementById('nav_hidden_children').value = navChildren;
        const minus = document.getElementById('nav_children_minus');
        if (minus) {
            if (navChildren <= 0) minus.classList.add('disabled');
            else minus.classList.remove('disabled');
        }
    } else if (type === 'rooms') {
        navRooms = Math.max(1, navRooms + delta);
        document.getElementById('nav_rooms_val').innerText = navRooms;
        document.getElementById('nav_hidden_rooms').value = navRooms;
        const minus = document.getElementById('nav_rooms_minus');
        if (minus) {
            if (navRooms <= 1) minus.classList.add('disabled');
            else minus.classList.remove('disabled');
        }
    }
    const total = navAdults + navChildren;
    document.getElementById('nav_guest_display').innerText = total + ' Guests, ' + navRooms + ' Room' + (navRooms > 1 ? 's' : '');
    updateNavResetBtnState();
}

function updateNavResetBtnState() {
    const petCheck = document.getElementById('nav_pet_friendly');
    const isPetChecked = petCheck && petCheck.checked;
    const isModified = navAdults !== NAV_DEFAULT_ADULTS || navChildren !== NAV_DEFAULT_CHILDREN || navRooms !== NAV_DEFAULT_ROOMS || isPetChecked;
    const resetBtn = document.getElementById('nav_guest_reset_btn');
    if (resetBtn) {
        if (isModified) resetBtn.classList.add('active');
        else resetBtn.classList.remove('active');
    }
}

function resetNavGuestCounts() {
    navAdults = NAV_DEFAULT_ADULTS;
    navChildren = NAV_DEFAULT_CHILDREN;
    navRooms = NAV_DEFAULT_ROOMS;
    
    document.getElementById('nav_adults_val').innerText = navAdults;
    document.getElementById('nav_children_val').innerText = navChildren;
    document.getElementById('nav_rooms_val').innerText = navRooms;
    
    document.getElementById('nav_hidden_adults').value = navAdults;
    document.getElementById('nav_hidden_children').value = navChildren;
    document.getElementById('nav_hidden_rooms').value = navRooms;

    const petCheck = document.getElementById('nav_pet_friendly');
    if (petCheck) petCheck.checked = false;

    document.getElementById('nav_adults_minus').classList.remove('disabled');
    document.getElementById('nav_children_minus').classList.add('disabled');
    document.getElementById('nav_rooms_minus').classList.add('disabled');

    const total = navAdults + navChildren;
    document.getElementById('nav_guest_display').innerText = total + ' Guests, ' + navRooms + ' Room';
    updateNavResetBtnState();
}

function applyNavGuestSelection() {
    closeNavPopups();
    submitNavSearchForm();
}

function closeNavPopups() {
    const drops = ['nav_recent_dropdown', 'nav_datepicker_modal', 'nav_guest_modal', 'nav_user_dropdown'];
    drops.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.classList.remove('show');
    });
    const pods = ['nav_dest_pod', 'nav_date_pod_ci', 'nav_date_pod_co', 'nav_guest_pod', 'nav_user_btn'];
    pods.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.style.outline = 'none';
    });
}

document.addEventListener('click', function(e) {
    const form = document.getElementById('nav_search_form');
    const userWrapper = document.getElementById('nav_user_menu_wrapper');
    const clickedInsideSearch = form && form.contains(e.target);
    const clickedInsideUser = userWrapper && userWrapper.contains(e.target);
    
    if (!clickedInsideSearch && !clickedInsideUser) {
        closeNavPopups();
    }
});

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('nav_search_form')) {
        renderNavCalendars();
        updateNavResetBtnState();
    }
});