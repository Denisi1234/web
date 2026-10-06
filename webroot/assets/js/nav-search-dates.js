/* Nav search. Split for the 300-line cap — load dates, suggest, fetch, guests in order (shared globals). */
/**
 * fastnetstays.com - Navigation Header Hotel Search Controller
 * Handles destination clear, calendar datepicker, and guest counter modals.
 * Features timezone-safe date parsing, minimum 1-night validation, and range selection.
 */

var navAdults = 2;
var navChildren = 0;
var navRooms = 1;

var NAV_DEFAULT_ADULTS = 2;
var NAV_DEFAULT_CHILDREN = 0;
var NAV_DEFAULT_ROOMS = 1;

var navStartDate = null;
var navEndDate = null;
var navSelectingEndDate = false;
var navCalBaseMonth = null;

var navMonthNames = ["January","February","March","April","May","June","July","August","September","October","November","December"];
var navMonthShort = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];

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
