/* Nav search. Split for the 300-line cap — load dates, suggest, fetch, guests in order (shared globals). */
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

var NAV_POPULAR_DESTINATIONS = [
    { name: 'Dar es Salaam', subtitle: 'Coastal City · Commercial Hub & Lodges', icon: 'fa-solid fa-city', iconClass: 'icon-blue', lat: -6.7924, lng: 39.2083 },
    { name: 'Zanzibar City', subtitle: 'Stone Town & Beaches · Ocean Resorts', icon: 'fa-solid fa-umbrella-beach', iconClass: 'icon-blue', lat: -6.1659, lng: 39.2026 },
    { name: 'Arusha', subtitle: 'Safari Gateway · Mount Meru Lodges', icon: 'fa-solid fa-mountain', iconClass: 'icon-orange', lat: -3.3869, lng: 36.6830 },
    { name: 'Serengeti', subtitle: 'National Park · Wildlife Safari Camps', icon: 'fa-solid fa-paw', iconClass: 'icon-orange', lat: -2.3333, lng: 34.8333 },
    { name: 'Dodoma', subtitle: 'Capital City · Executive Hotels & Suites', icon: 'fa-solid fa-landmark', iconClass: 'icon-blue', lat: -6.1630, lng: 35.7516 },
    { name: 'Kilimanjaro', subtitle: 'Moshi · Alpine Foothills & Eco Stays', icon: 'fa-solid fa-volcano', iconClass: 'icon-orange', lat: -3.0674, lng: 37.3556 }
];

var navSuggestTimeout = null;
var navCurrentSuggestions = [];
var navActiveSuggestIdx = -1;

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
