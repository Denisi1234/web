/**
 * fastnetstays.com - Trivago Hero & Search Component Controller
 * Modular script handling destination popup, dual-month calendar datepicker,
 * and guests & rooms counter stepper modal.
 */

let adults = 2;
let children = 0;
let rooms = 1;

const DEFAULT_ADULTS = 2;
const DEFAULT_CHILDREN = 0;
const DEFAULT_ROOMS = 1;

let startDate = null;
let endDate = null;
let selectingEndDate = false;
let calBaseMonth = null;

function parseHeroDate(value) {
    if (value instanceof Date) return new Date(value.getFullYear(), value.getMonth(), value.getDate());
    const match = String(value || '').match(/^(\d{4})-(\d{2})-(\d{2})$/);
    if (match) return new Date(Number(match[1]), Number(match[2]) - 1, Number(match[3]));
    const parsed = new Date(value);
    return isNaN(parsed.getTime()) ? null : new Date(parsed.getFullYear(), parsed.getMonth(), parsed.getDate());
}

const monthNames = ["January","February","March","April","May","June","July","August","September","October","November","December"];
const monthShort = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];

window.initTrivagoHero = function(config) {
    if (config) {
        if (typeof config.adults !== 'undefined') adults = Number(config.adults);
        if (typeof config.children !== 'undefined') children = Number(config.children);
        if (typeof config.rooms !== 'undefined') rooms = Number(config.rooms);
        if (config.checkIn) startDate = parseHeroDate(config.checkIn);
        if (config.checkOut) endDate = parseHeroDate(config.checkOut);
    }
    if (!startDate || isNaN(startDate.getTime())) {
        startDate = new Date();
        startDate.setDate(startDate.getDate() + 7);
    }
    if (!endDate || isNaN(endDate.getTime())) {
        endDate = new Date(startDate);
        endDate.setDate(endDate.getDate() + 5);
    }
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    if (startDate < today) startDate = new Date(today);
    if (endDate <= startDate) {
        endDate = new Date(startDate);
        endDate.setDate(endDate.getDate() + 1);
    }
    selectingEndDate = false;
    calBaseMonth = new Date(startDate.getFullYear(), startDate.getMonth(), 1);

    renderHeroCalendars();
    updateResetBtnState();
};

function openHeroDatePicker(e) {
    if (e) e.stopPropagation();
    closeAllPopups();
    const modal = document.getElementById('hero_datepicker_modal');
    const pod = document.getElementById('trivago_hero_date_pod');
    if (modal) modal.classList.add('show');
    if (pod) pod.classList.add('pod-active');
    renderHeroCalendars();
}

function navHeroCal(dir) {
    if (!calBaseMonth) calBaseMonth = new Date();
    calBaseMonth.setMonth(calBaseMonth.getMonth() + dir);
    renderHeroCalendars();
}

function renderHeroCalendars() {
    if (!calBaseMonth) return;
    const m1Year = calBaseMonth.getFullYear();
    const m1Month = calBaseMonth.getMonth();
    
    const m2Date = new Date(m1Year, m1Month + 1, 1);
    const m2Year = m2Date.getFullYear();
    const m2Month = m2Date.getMonth();

    const m1Title = document.getElementById('hero_month1_title');
    if (m1Title) m1Title.innerText = monthNames[m1Month] + ' ' + m1Year;
    
    const m2Title = document.getElementById('hero_month2_title');
    if (m2Title) m2Title.innerText = monthNames[m2Month] + ' ' + m2Year;

    renderSingleMonth('hero_month1_grid', m1Year, m1Month);
    renderSingleMonth('hero_month2_grid', m2Year, m2Month);
}

function renderSingleMonth(elementId, year, month) {
    const grid = document.getElementById(elementId);
    if (!grid) return;
    grid.innerHTML = '';

    const firstDayIndex = new Date(year, month, 1).getDay();
    const totalDays = new Date(year, month + 1, 0).getDate();
    const today = new Date();
    today.setHours(0,0,0,0);

    for (let i = 0; i < firstDayIndex; i++) {
        const emptyCell = document.createElement('div');
        emptyCell.className = 'trivago-cal-day empty';
        grid.appendChild(emptyCell);
    }

    for (let d = 1; d <= totalDays; d++) {
        const thisDate = new Date(year, month, d);
        thisDate.setHours(0,0,0,0);
        
        const cell = document.createElement('div');
        cell.className = 'trivago-cal-day';
        
        if (thisDate < today) {
            cell.classList.add('disabled');
        }

        const isStart = startDate && thisDate.getTime() === startDate.getTime();
        const isEnd = endDate && thisDate.getTime() === endDate.getTime();
        const isInRange = startDate && endDate && thisDate > startDate && thisDate < endDate;

        if (isStart) cell.classList.add('range-start');
        if (isEnd) cell.classList.add('range-end');
        if (isInRange) cell.classList.add('in-range');

        const inner = document.createElement('div');
        inner.className = 'trivago-cal-day-inner';
        inner.innerText = d;

        cell.appendChild(inner);

        if (!cell.classList.contains('disabled')) {
            cell.onclick = function(ev) {
                ev.stopPropagation();
                handleDateClick(thisDate);
            };
        }

        grid.appendChild(cell);
    }
}

function handleDateClick(clickedDate) {
    if (!selectingEndDate || !startDate || clickedDate < startDate) {
        startDate = clickedDate;
        endDate = null;
        selectingEndDate = true;
    } else {
        endDate = clickedDate;
        selectingEndDate = false;
        applyDateSelection();
        closeAllPopups();
    }
    renderHeroCalendars();
}

function applyDateSelection() {
    if (!startDate || !endDate) return;
    
    const pad = n => String(n).padStart(2, '0');
    const startStr = startDate.getFullYear() + '-' + pad(startDate.getMonth() + 1) + '-' + pad(startDate.getDate());
    const endStr = endDate.getFullYear() + '-' + pad(endDate.getMonth() + 1) + '-' + pad(endDate.getDate());

    const inInput = document.getElementById('trivago_hidden_checkin');
    const outInput = document.getElementById('trivago_hidden_checkout');
    if (inInput) inInput.value = startStr;
    if (outInput) outInput.value = endStr;

    const display = document.getElementById('trivago_date_display');
    if (display) {
        display.innerText = startDate.getDate() + ' ' + monthShort[startDate.getMonth()] + ' - ' + endDate.getDate() + ' ' + monthShort[endDate.getMonth()];
    }
}

function applyCalShortcut(type) {
    const today = new Date();
    today.setHours(0,0,0,0);
    
    if (type === 'tonight') {
        startDate = new Date(today);
        endDate = new Date(today);
        endDate.setDate(endDate.getDate() + 1);
    } else if (type === 'tomorrow') {
        startDate = new Date(today);
        startDate.setDate(startDate.getDate() + 1);
        endDate = new Date(startDate);
        endDate.setDate(endDate.getDate() + 1);
    } else if (type === 'this_weekend') {
        const day = today.getDay();
        const diffToFri = (5 - day + 7) % 7;
        startDate = new Date(today);
        startDate.setDate(today.getDate() + diffToFri);
        endDate = new Date(startDate);
        endDate.setDate(startDate.getDate() + 2);
    } else if (type === 'next_weekend') {
        const day = today.getDay();
        const diffToFri = (5 - day + 7) % 7 + 7;
        startDate = new Date(today);
        startDate.setDate(today.getDate() + diffToFri);
        endDate = new Date(startDate);
        endDate.setDate(startDate.getDate() + 2);
    }

    calBaseMonth = new Date(startDate.getFullYear(), startDate.getMonth(), 1);
    applyDateSelection();
    closeAllPopups();
}

/* ══════════════════════════════════════════════════════════════════════════════
 * Home Hero - Mapbox Real Auto-Suggestion & Destination Dropdown Controller
 * ══════════════════════════════════════════════════════════════════════════════ */

const HERO_POPULAR_DESTINATIONS = [
    { name: 'Dar es Salaam', subtitle: 'Coastal City · Commercial Hub & Lodges', icon: 'fa-solid fa-city', iconClass: 'icon-blue', lat: -6.7924, lng: 39.2083 },
    { name: 'Zanzibar City', subtitle: 'Stone Town & Beaches · Ocean Resorts', icon: 'fa-solid fa-umbrella-beach', iconClass: 'icon-blue', lat: -6.1659, lng: 39.2026 },
    { name: 'Arusha', subtitle: 'Safari Gateway · Mount Meru Lodges', icon: 'fa-solid fa-mountain', iconClass: 'icon-orange', lat: -3.3869, lng: 36.6830 },
    { name: 'Serengeti', subtitle: 'National Park · Wildlife Safari Camps', icon: 'fa-solid fa-paw', iconClass: 'icon-orange', lat: -2.3333, lng: 34.8333 },
    { name: 'Dodoma', subtitle: 'Capital City · Executive Hotels & Suites', icon: 'fa-solid fa-landmark', iconClass: 'icon-blue', lat: -6.1630, lng: 35.7516 },
    { name: 'Kilimanjaro', subtitle: 'Moshi · Alpine Foothills & Eco Stays', icon: 'fa-solid fa-volcano', iconClass: 'icon-orange', lat: -3.0674, lng: 37.3556 }
];

let heroSuggestTimeout = null;
let heroCurrentSuggestions = [];
let heroActiveSuggestIdx = -1;

function getHeroMapboxToken() {
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

function getStoredHeroRecentSearches() {
    try {
        const raw = localStorage.getItem('fastnet_recent_destinations');
        return raw ? JSON.parse(raw) : [];
    } catch(e) {
        return [];
    }
}

function showDefaultHeroDestinations() {
    const recents = getStoredHeroRecentSearches();
    const titleEl = document.getElementById('hero_recent_header_title');
    const listEl = document.getElementById('hero_dest_suggestions_list');
    if (!listEl) return;

    heroCurrentSuggestions = [];
    heroActiveSuggestIdx = -1;

    let items = [];
    if (recents && recents.length > 0) {
        if (titleEl) titleEl.innerText = 'Recent & Popular Destinations';
        recents.forEach(r => {
            items.push({
                name: r.name,
                subtitle: r.subtitle || 'Recent search',
                icon: 'fa-solid fa-clock-rotate-left',
                iconClass: 'icon-purple',
                lat: r.lat,
                lng: r.lng,
                isRecent: true
            });
        });
        HERO_POPULAR_DESTINATIONS.forEach(p => {
            if (!items.some(it => it.name.toLowerCase() === p.name.toLowerCase())) {
                items.push(p);
            }
        });
    } else {
        if (titleEl) titleEl.innerText = 'Popular Destinations in Tanzania';
        items = [...HERO_POPULAR_DESTINATIONS];
    }

    heroCurrentSuggestions = items;
    renderHeroSuggestionList(items, '');
}

function renderHeroSuggestionList(items, query) {
    const listEl = document.getElementById('hero_dest_suggestions_list');
    if (!listEl) return;

    if (!items || items.length === 0) {
        listEl.innerHTML = `
            <div class="p-3 text-center text-slate-500" style="font-size: 13px;">
                <i class="fa-solid fa-map-location-dot text-slate-400 d-block fs-5 mb-2"></i>
                No locations found. Press enter to search stays by keyword.
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

        html += `
            <div class="trivago-recent-item" id="hero_suggest_item_${idx}" data-idx="${idx}" onclick="selectHeroDestination('${safeName}', '${safeSub}', ${latVal}, ${lngVal})">
                <div class="trivago-recent-icon ${item.iconClass || 'icon-blue'}">
                    <i class="${item.icon || 'fa-solid fa-location-dot'}"></i>
                </div>
                <div class="trivago-recent-info">
                    <div class="trivago-recent-name">${highlightedTitle}</div>
                    <div class="trivago-recent-meta">${escapeHtml(item.subtitle || '')}</div>
                </div>
            </div>
        `;
    });

    listEl.innerHTML = html;
}

function openHeroRecentDropdown(e) {
    if (e) e.stopPropagation();
    closeAllPopups();

    const drop = document.getElementById('hero_recent_dropdown');
    const pod = document.getElementById('trivago_hero_dest_pod');
    const input = document.getElementById('trivago_home_dest');

    if (drop) drop.classList.add('show');
    if (pod) pod.classList.add('pod-active');

    const curVal = input ? input.value.trim() : '';
    if (curVal.length >= 2) {
        fetchHeroMapboxSuggestions(curVal);
    } else {
        showDefaultHeroDestinations();
    }
}

function handleHeroDestInput(e) {
    const val = (e.target.value || '').trim();
    clearTimeout(heroSuggestTimeout);

    if (val.length < 2) {
        showDefaultHeroDestinations();
        return;
    }

    const titleEl = document.getElementById('hero_recent_header_title');
    if (titleEl) {
        titleEl.innerHTML = `<i class="fa-solid fa-spinner fa-spin text-primary me-1"></i> Searching Mapbox...`;
    }

    heroSuggestTimeout = setTimeout(() => {
        fetchHeroMapboxSuggestions(val);
    }, 250);
}

function handleHeroDestKeydown(e) {
    const drop = document.getElementById('hero_recent_dropdown');
    const isOpen = drop && drop.classList.contains('show');

    if (!isOpen) {
        if (e.key === 'ArrowDown' || e.key === 'Enter') {
            openHeroRecentDropdown(e);
            return;
        }
        return;
    }

    if (e.key === 'ArrowDown') {
        e.preventDefault();
        if (heroCurrentSuggestions.length > 0) {
            heroActiveSuggestIdx = (heroActiveSuggestIdx + 1) % heroCurrentSuggestions.length;
            updateSelectedHeroSuggestionUi();
        }
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        if (heroCurrentSuggestions.length > 0) {
            heroActiveSuggestIdx = (heroActiveSuggestIdx - 1 + heroCurrentSuggestions.length) % heroCurrentSuggestions.length;
            updateSelectedHeroSuggestionUi();
        }
    } else if (e.key === 'Enter') {
        if (heroActiveSuggestIdx >= 0 && heroCurrentSuggestions[heroActiveSuggestIdx]) {
            e.preventDefault();
            const chosen = heroCurrentSuggestions[heroActiveSuggestIdx];
            selectHeroDestination(chosen.name, chosen.subtitle, chosen.lat, chosen.lng);
        } else if (heroCurrentSuggestions.length > 0) {
            e.preventDefault();
            const first = heroCurrentSuggestions[0];
            selectHeroDestination(first.name, first.subtitle, first.lat, first.lng);
        } else {
            closeAllPopups();
            const input = document.getElementById('trivago_home_dest');
            if (input && input.closest('form')) input.closest('form').submit();
        }
    } else if (e.key === 'Escape') {
        closeAllPopups();
    }
}

function updateSelectedHeroSuggestionUi() {
    const items = document.querySelectorAll('#hero_dest_suggestions_list .trivago-recent-item');
    items.forEach((it, idx) => {
        if (idx === heroActiveSuggestIdx) {
            it.classList.add('selected');
            it.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        } else {
            it.classList.remove('selected');
        }
    });
}

async function fetchHeroMapboxSuggestions(query) {
    const token = getHeroMapboxToken();
    const titleEl = document.getElementById('hero_recent_header_title');

    try {
        const endpoint = `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(query)}.json?access_token=${token}&autocomplete=true&types=place,locality,neighborhood,poi,district,region,address,country&language=en,sw&proximity=39.2083,-6.7924&limit=7`;

        const res = await fetch(endpoint);
        if (!res.ok) throw new Error('Mapbox API HTTP ' + res.status);
        const data = await res.json();

        if (data && data.features && data.features.length > 0) {
            heroCurrentSuggestions = data.features.map(f => {
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
                } else if (placeType === 'region' || placeType === 'country') {
                    icon = 'fa-solid fa-earth-africa';
                    iconClass = 'icon-purple';
                }

                let subtitle = '';
                if (f.context && f.context.length > 0) {
                    subtitle = f.context.map(c => c.text).join(', ');
                } else {
                    subtitle = f.place_name || 'Tanzania';
                }

                return {
                    name: f.text || f.place_name,
                    subtitle: subtitle,
                    lat: f.center ? f.center[1] : null,
                    lng: f.center ? f.center[0] : null,
                    icon: icon,
                    iconClass: iconClass
                };
            });

            heroActiveSuggestIdx = -1;
            if (titleEl) titleEl.innerText = `Locations matching "${query}"`;
            renderHeroSuggestionList(heroCurrentSuggestions, query);
        } else {
            heroCurrentSuggestions = [];
            heroActiveSuggestIdx = -1;
            if (titleEl) titleEl.innerText = `No locations found for "${query}"`;
            renderHeroSuggestionList([], query);
        }
    } catch (err) {
        console.warn('Mapbox hero auto-suggestion fallback:', err);
        const filtered = HERO_POPULAR_DESTINATIONS.filter(p =>
            p.name.toLowerCase().includes(query.toLowerCase()) ||
            p.subtitle.toLowerCase().includes(query.toLowerCase())
        );
        heroCurrentSuggestions = filtered;
        heroActiveSuggestIdx = -1;
        if (titleEl) titleEl.innerText = `Destinations matching "${query}"`;
        renderHeroSuggestionList(filtered, query);
    }
}

function selectHeroDestination(name, subtitle, lat, lng) {
    const input = document.getElementById('trivago_home_dest');
    const hiddenLat = document.getElementById('hero_hidden_lat');
    const hiddenLng = document.getElementById('hero_hidden_lng');

    if (input) input.value = name;
    if (hiddenLat && lat !== null && lat !== undefined) hiddenLat.value = lat;
    if (hiddenLng && lng !== null && lng !== undefined) hiddenLng.value = lng;

    try {
        let recents = getStoredHeroRecentSearches();
        recents = recents.filter(r => r.name.toLowerCase() !== name.toLowerCase());
        recents.unshift({ name, subtitle, lat, lng, date: new Date().toISOString() });
        if (recents.length > 6) recents = recents.slice(0, 6);
        localStorage.setItem('fastnet_recent_destinations', JSON.stringify(recents));
    } catch (e) {}

    closeAllPopups();

    // Automatically transition to datepicker modal for smooth hotel booking flow
    const datePod = document.getElementById('trivago_hero_date_pod');
    if (datePod && typeof openHeroDatePicker === 'function') {
        setTimeout(() => {
            openHeroDatePicker();
        }, 80);
    } else {
        if (input && input.closest('form')) input.closest('form').submit();
    }
}

function clearHeroDest(e) {
    if (e) e.stopPropagation();
    const input = document.getElementById('trivago_home_dest');
    const hiddenLat = document.getElementById('hero_hidden_lat');
    const hiddenLng = document.getElementById('hero_hidden_lng');

    if (input) {
        input.value = '';
        input.focus();
    }
    if (hiddenLat) hiddenLat.value = '';
    if (hiddenLng) hiddenLng.value = '';

    showDefaultHeroDestinations();
}

function selectHeroRecent(name, cin, cout, ad, rm) {
    selectHeroDestination(name, 'Destination', null, null);
}

function toggleTrivagoGuestsDropdown(e) {
    if (e) e.stopPropagation();
    const drop = document.getElementById('trivago_guest_modal');
    const pod = document.getElementById('trivago_hero_guest_pod');
    const wasOpen = drop && drop.classList.contains('show');
    closeAllPopups();
    if (drop && !wasOpen) {
        drop.classList.add('show');
        if (pod) pod.classList.add('pod-active');
    }
}

function updateGuestCount(type, delta) {
    if (type === 'adults') {
        adults = Math.max(1, adults + delta);
        document.getElementById('trivago_adults_val').innerText = adults;
        document.getElementById('trivago_hidden_adults').value = adults;
        const minusBtn = document.getElementById('hero_adults_minus');
        if (minusBtn) {
            if (adults <= 1) minusBtn.classList.add('disabled');
            else minusBtn.classList.remove('disabled');
        }
    } else if (type === 'children') {
        children = Math.max(0, children + delta);
        document.getElementById('trivago_children_val').innerText = children;
        document.getElementById('trivago_hidden_children').value = children;
        const minusBtn = document.getElementById('hero_children_minus');
        if (minusBtn) {
            if (children <= 0) minusBtn.classList.add('disabled');
            else minusBtn.classList.remove('disabled');
        }
    } else if (type === 'rooms') {
        rooms = Math.max(1, rooms + delta);
        document.getElementById('trivago_rooms_val').innerText = rooms;
        document.getElementById('trivago_hidden_rooms').value = rooms;
        const minusBtn = document.getElementById('hero_rooms_minus');
        if (minusBtn) {
            if (rooms <= 1) minusBtn.classList.add('disabled');
            else minusBtn.classList.remove('disabled');
        }
    }
    const totalGuests = adults + children;
    const disp = document.getElementById('trivago_guest_display');
    if (disp) {
        disp.innerText = totalGuests + ' Guests, ' + rooms + ' Room' + (rooms > 1 ? 's' : '');
    }
    updateResetBtnState();
}

function updateResetBtnState() {
    const petCheck = document.getElementById('hero_pet_friendly');
    const isPetChecked = petCheck && petCheck.checked;
    const isModified = adults !== DEFAULT_ADULTS || children !== DEFAULT_CHILDREN || rooms !== DEFAULT_ROOMS || isPetChecked;
    const resetBtn = document.getElementById('hero_guest_reset_btn');
    if (resetBtn) {
        if (isModified) resetBtn.classList.add('active');
        else resetBtn.classList.remove('active');
    }
}

function resetGuestCounts() {
    adults = DEFAULT_ADULTS;
    children = DEFAULT_CHILDREN;
    rooms = DEFAULT_ROOMS;
    
    document.getElementById('trivago_adults_val').innerText = adults;
    document.getElementById('trivago_children_val').innerText = children;
    document.getElementById('trivago_rooms_val').innerText = rooms;
    
    document.getElementById('trivago_hidden_adults').value = adults;
    document.getElementById('trivago_hidden_children').value = children;
    document.getElementById('trivago_hidden_rooms').value = rooms;

    const petCheck = document.getElementById('hero_pet_friendly');
    if (petCheck) petCheck.checked = false;

    document.getElementById('hero_adults_minus').classList.remove('disabled');
    document.getElementById('hero_children_minus').classList.add('disabled');
    document.getElementById('hero_rooms_minus').classList.add('disabled');

    const totalGuests = adults + children;
    const disp = document.getElementById('trivago_guest_display');
    if (disp) {
        disp.innerText = totalGuests + ' Guests, ' + rooms + ' Room';
    }
    updateResetBtnState();
}

function applyGuestSelection() {
    closeAllPopups();
}

function closeAllPopups() {
    const drops = ['hero_recent_dropdown', 'hero_datepicker_modal', 'trivago_guest_modal'];
    drops.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.classList.remove('show');
    });

    const pods = ['trivago_hero_dest_pod', 'trivago_hero_date_pod', 'trivago_hero_guest_pod'];
    pods.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.classList.remove('pod-active');
    });
}

document.addEventListener('click', function() {
    closeAllPopups();
});
