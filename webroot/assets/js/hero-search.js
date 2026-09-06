/**
 * ============================================================
 * HERO SECTION SEARCH — hero-search.js
 * ============================================================
 * EXCLUSIVE to the Home Page Hero Section (#hero-search-form).
 * Do NOT include on any other page.
 * ============================================================
 */

(function HeroSearch() {
    'use strict';

    if (!document.getElementById('hero-search-form')) return;

    // ================================================================
    // A. DESTINATION DATA & HELPERS
    // ================================================================

    const ICONS = {
        City:       '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 21V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v17"/><path d="M12 9h7a1 1 0 0 1 1 1v11"/><path d="M7 7h1"/><path d="M7 11h1"/><path d="M7 15h1"/><path d="M16 13h1"/><path d="M16 17h1"/></svg>',
        Area:       '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21c4-4.5 7-8.5 7-12A7 7 0 0 0 5 9c0 3.5 3 7.5 7 12z"/><circle cx="12" cy="9" r="2.5"/></svg>',
        Island:     '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 18c2-2 4-2 6 0s4 2 6 0 4-2 6 0"/><path d="M12 14c-1-3 0-8 4-10"/><path d="M16 4c2 0 4 1 5 3-2 1-3 3-3 5"/></svg>',
        Attraction: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m8 3 4 8 5-5 5 15H2L8 3z"/></svg>',
        Airport:    '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.8.5 1.3.3l.5-.3c.4-.2.6-.6.5-1.1z"/><path d="M3 21h18"/></svg>',
        Property:   '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V6a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v15"/><path d="M9 8h6"/><path d="M10 21v-4a2 2 0 0 1 4 0v4"/></svg>'
    };
    const BADGE_CLASS = {
        City: 'hs-suggestion-badge--city', Island: 'hs-suggestion-badge--island',
        Attraction: 'hs-suggestion-badge--attraction', Airport: 'hs-suggestion-badge--airport',
        Property: 'hs-suggestion-badge--property'
    };
    const DESTINATIONS = [
        { name: 'Dar es Salaam',    sub: 'Commercial Capital, Coastal Region, Tanzania',              type: 'City',       kw: 'dar dsm coastal bongo city' },
        { name: 'Zanzibar',         sub: 'Unguja Island & Archipelago, Tanzania',                     type: 'Island',     kw: 'zanzibar unguja spice island beach' },
        { name: 'Arusha',           sub: 'Safari Capital & Mount Meru Hub, Northern Tanzania',        type: 'City',       kw: 'arusha safari mount meru northern' },
        { name: 'Dodoma',           sub: 'National Capital City, Central Tanzania',                   type: 'City',       kw: 'dodoma capital bunge central' },
        { name: 'Mwanza',           sub: 'The Rock City, Lake Victoria, Tanzania',                    type: 'City',       kw: 'mwanza rock lake victoria' },
        { name: 'Moshi',            sub: 'Kilimanjaro Foothills, Northern Tanzania',                  type: 'City',       kw: 'moshi kilimanjaro kili' },
        { name: 'Tanga',            sub: 'Historic Coastal Port City, Tanzania',                      type: 'City',       kw: 'tanga coastal pangani port' },
        { name: 'Pemba Island',     sub: 'The Green Island, Zanzibar Archipelago, Tanzania',          type: 'Island',     kw: 'pemba island cloves diving' },
        { name: 'Mafia Island',     sub: 'Marine Park & Whale Sharks, Coast, Tanzania',               type: 'Island',     kw: 'mafia island marine whale shark' },
        { name: 'Serengeti National Park', sub: 'Great Migration & Big Five Safari, Mara, Tanzania', type: 'Attraction', kw: 'serengeti migration safari big five' },
        { name: 'Ngorongoro Crater',      sub: 'UNESCO World Heritage Caldera, Tanzania',            type: 'Attraction', kw: 'ngorongoro caldera unesco conservation' },
        { name: 'Mount Kilimanjaro',      sub: 'Roof of Africa (5,895m), Tanzania',                  type: 'Attraction', kw: 'kilimanjaro kibo roof africa trekking' },
        { name: 'Tarangire National Park', sub: 'Elephants & Baobabs Sanctuary, Manyara, Tanzania', type: 'Attraction', kw: 'tarangire elephants baobab safari' },
        { name: 'Stone Town',  sub: 'UNESCO Historic Heart, Zanzibar',                               type: 'Area',       kw: 'stone town forodhani old fort zanzibar' },
        { name: 'Nungwi',      sub: 'Northern Coral Beaches & Sunset Dhows, Zanzibar',               type: 'Area',       kw: 'nungwi beach north zanzibar turtles' },
        { name: 'Kendwa',      sub: 'Non-tidal Turquoise Lagoon, Zanzibar',                          type: 'Area',       kw: 'kendwa beach zanzibar sunset' },
        { name: 'Paje',        sub: 'Kitesurfing Paradise, East Coast Zanzibar',                     type: 'Area',       kw: 'paje kitesurf beach zanzibar' },
        { name: 'Masaki',      sub: 'Peninsula, High-end Dining, Dar es Salaam',                     type: 'Area',       kw: 'masaki peninsula slipway dar' },
        { name: 'Mikocheni',   sub: 'Boutique Hotels & Shopping Hub, Dar es Salaam',                 type: 'Area',       kw: 'mikocheni palm village dar' },
        { name: 'Kariakoo',    sub: 'Commercial Epicenter, Dar es Salaam',                           type: 'Area',       kw: 'kariakoo market downtown dar' },
        { name: 'Mtumba',      sub: 'Government City, Dodoma, Tanzania',                             type: 'Area',       kw: 'mtumba government dodoma' },
        { name: 'Kisasa',      sub: 'Modern Residential & Lodge Hub, Dodoma',                        type: 'Area',       kw: 'kisasa dodoma prime lodges' },
        { name: 'Njiro',       sub: 'Upscale Suburb, Arusha',                                        type: 'Area',       kw: 'njiro arusha suburb cinema' },
        { name: 'Dar es Salaam (DAR - Julius Nyerere Intl.)', sub: 'Dar es Salaam, Tanzania (JNIA)', type: 'Airport',   kw: 'dar airport julius nyerere jnia' },
        { name: 'Kilimanjaro Intl. (JRO)', sub: 'Arusha / Moshi, Tanzania',                         type: 'Airport',    kw: 'kilimanjaro airport jro arusha moshi' },
        { name: 'Zanzibar (ZNZ)', sub: 'Abeid Amani Karume Intl., Zanzibar, Tanzania',               type: 'Airport',    kw: 'zanzibar airport znz kisauni' },
        { name: 'Sea Cliff Hotel',  sub: 'Masaki Peninsula, Dar es Salaam',                          type: 'Property',   kw: 'sea cliff hotel masaki dar luxury' },
        { name: 'Zanzibar Serena Hotel', sub: 'Stone Town Seafront, Zanzibar',                       type: 'Property',   kw: 'zanzibar serena stone town oceanfront' }
    ];

    function esc(s) {
        return String(s||'').replace(/[&<>"']/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c]);
    }
    function highlight(text, q) {
        if (!text||!q) return esc(text);
        try {
            const re = new RegExp('('+q.replace(/[.*+?^${}()|[\]\\]/g,'\\$&')+')','gi');
            return esc(text).replace(re,'<mark style="background:transparent;color:#1877f2;font-weight:700">$1</mark>');
        } catch(e) { return esc(text); }
    }
    function filterDest(q) {
        const n = (q||'').trim().toLowerCase().replace(/[-_,.]/g,' ');
        if (!n) return DESTINATIONS.slice(0,7);
        const ex=[],sw=[],co=[];
        DESTINATIONS.forEach(d => {
            const nm=d.name.toLowerCase(), all=[d.kw,d.name,d.sub,d.type].join(' ').toLowerCase();
            if(nm===n) ex.push(d); else if(nm.startsWith(n)) sw.push(d); else if(nm.includes(n)||all.includes(n)) co.push(d);
        });
        const seen=new Set();
        return [...ex,...sw,...co].filter(d=>{ const k=d.name.toLowerCase(); if(seen.has(k)) return false; seen.add(k); return true; }).slice(0,8);
    }

    // ================================================================
    // B. TABS (Hotels / Homes etc.)
    // ================================================================
    function initTabs() {
        document.querySelectorAll('#hero-search-form .hs-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('#hero-search-form .hs-tab').forEach(t => t.classList.remove('hs-tab--active'));
                tab.classList.add('hs-tab--active');
            });
        });
    }

    // ================================================================
    // C. DESTINATION AUTOCOMPLETE
    // ================================================================
    function initDestination() {
        const wrap     = document.getElementById('hs-dest-field');
        const input    = document.getElementById('hs-dest-input');
        const dropdown = document.getElementById('hs-dest-dropdown');
        const list     = document.getElementById('hs-dest-list');
        if (!wrap||!input||!dropdown||!list) return;

        let hiIdx=-1, filtered=[];

        function renderList(items) {
            filtered=items; hiIdx=-1;
            const q=input.value.trim();
            if (!items.length) {
                list.innerHTML=`<li class="hs-no-results"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>No destinations found for "<strong>${esc(q)}</strong>"</li>`;
            } else {
                list.innerHTML=items.map((d,i)=>`
                    <li class="hs-suggestion-item" data-idx="${i}" data-name="${esc(d.name)}">
                        <div class="hs-suggestion-icon">${ICONS[d.type]||ICONS.Area}</div>
                        <div style="flex:1;min-width:0">
                            <div class="hs-suggestion-name">${highlight(d.name,q)}</div>
                            <div class="hs-suggestion-sub">${highlight(d.sub,q)}</div>
                        </div>
                        <span class="hs-suggestion-badge ${BADGE_CLASS[d.type]||''}">${esc(d.type)}</span>
                    </li>`).join('');
                list.querySelectorAll('.hs-suggestion-item').forEach(li=>{
                    li.addEventListener('mousedown', e=>{ e.preventDefault(); selectDest(li.dataset.name); });
                });
            }
        }
        function openDD() { renderList(filterDest(input.value)); dropdown.classList.add('open'); }
        function closeDD() { dropdown.classList.remove('open'); }
        function selectDest(val) {
            input.value=val; closeDD();
            // Auto-advance to date picker
            openDateModal();
        }
        function updateHi() {
            list.querySelectorAll('.hs-suggestion-item').forEach((li,i)=>{
                li.classList.toggle('hs-active', i===hiIdx);
                if(i===hiIdx) li.scrollIntoView({block:'nearest'});
            });
        }
        input.addEventListener('focus', openDD);
        input.addEventListener('click', e=>{ e.stopPropagation(); openDD(); });
        input.addEventListener('input', ()=>{ renderList(filterDest(input.value)); dropdown.classList.add('open'); });
        input.addEventListener('keydown', e=>{
            if(!dropdown.classList.contains('open')){ if(e.key==='ArrowDown') openDD(); return; }
            if(e.key==='ArrowDown'){e.preventDefault();hiIdx=(hiIdx+1)%filtered.length;updateHi();}
            else if(e.key==='ArrowUp'){e.preventDefault();hiIdx=(hiIdx-1+filtered.length)%filtered.length;updateHi();}
            else if(e.key==='Enter'){e.preventDefault();if(hiIdx>=0&&filtered[hiIdx]) selectDest(filtered[hiIdx].name); else if(input.value.trim()) closeDD();}
            else if(e.key==='Escape'){closeDD();input.blur();}
        });
        document.addEventListener('click', e=>{ if(!wrap.contains(e.target)) closeDD(); });
    }

    // ================================================================
    // D. BOOKING.COM STYLE CALENDAR
    // ================================================================

    const MONTH_NAMES = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    const DAY_ABBR    = ['Mo','Tu','We','Th','Fr','Sa','Su']; // Monday-first

    // State
    let calStartDate  = null; // 'YYYY-MM-DD'
    let calEndDate    = null;
    let calClickState = 0;    // 0=none/both, 1=waiting for end

    // Which two months are shown (left month = offset 0)
    let calViewYear  = new Date().getFullYear();
    let calViewMonth = new Date().getMonth(); // 0-indexed

    function dateStr(y,m,d) {
        return `${y}-${String(m+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
    }
    function todayStr() {
        const t=new Date();
        return dateStr(t.getFullYear(), t.getMonth(), t.getDate());
    }
    function formatDisplay(str) {
        if(!str) return '';
        const [y,m,d]=str.split('-');
        const dt=new Date(parseInt(y),parseInt(m)-1,parseInt(d));
        const DAYS_FULL=['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        return `${parseInt(d)} ${MONTH_NAMES[dt.getMonth()].slice(0,3)} ${y}`;
    }
    function formatDayName(str) {
        if(!str) return '';
        const [y,m,d]=str.split('-');
        const dt=new Date(parseInt(y),parseInt(m)-1,parseInt(d));
        const DAYS=['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        return DAYS[dt.getDay()];
    }

    // Sync display labels in the search fields
    function syncDateLabels() {
        const ciDate = document.getElementById('hs-checkin-date');
        const ciDay  = document.getElementById('hs-checkin-day');
        const coDate = document.getElementById('hs-checkout-date');
        const coDay  = document.getElementById('hs-checkout-day');
        const hidCI  = document.getElementById('hs-hidden-checkin');
        const hidCO  = document.getElementById('hs-hidden-checkout');

        if(calStartDate) {
            if(ciDate) ciDate.textContent = formatDisplay(calStartDate);
            if(ciDay)  ciDay.textContent  = formatDayName(calStartDate);
            if(hidCI)  hidCI.value        = calStartDate;
        }
        if(calEndDate) {
            if(coDate) coDate.textContent = formatDisplay(calEndDate);
            if(coDay)  coDay.textContent  = formatDayName(calEndDate);
            if(hidCO)  hidCO.value        = calEndDate;
        } else {
            if(coDate) coDate.textContent = 'Select date';
            if(coDay)  coDay.textContent  = '';
        }

        // Update footer info
        const infoEl = document.getElementById('hs-cal-info');
        if(infoEl) {
            if(calStartDate && calEndDate) {
                const d1=new Date(calStartDate), d2=new Date(calEndDate);
                const nights=Math.round((d2-d1)/86400000);
                infoEl.innerHTML=`<strong>${formatDisplay(calStartDate)}</strong> → <strong>${formatDisplay(calEndDate)}</strong> &nbsp;·&nbsp; ${nights} night${nights!==1?'s':''}`;
            } else if(calStartDate) {
                infoEl.innerHTML=`<strong>${formatDisplay(calStartDate)}</strong> → select check-out`;
            } else {
                infoEl.textContent='Select your check-in date';
            }
        }
    }

    // Build one month's HTML
    function buildMonth(year, month) {
        const today = todayStr();
        const firstDay = new Date(year, month, 1).getDay(); // 0=Sun
        // Convert to Monday-first offset: Mon=0, Tue=1, ..., Sun=6
        const offset = (firstDay + 6) % 7;
        const daysInMonth = new Date(year, month+1, 0).getDate();

        let cells = '';
        // Empty cells before month start
        for(let i=0;i<offset;i++) cells += `<div class="hs-cal-day hs-cal-day--empty"><div class="hs-cal-day-inner"></div></div>`;

        for(let d=1;d<=daysInMonth;d++) {
            const ds = dateStr(year, month, d);
            let cls = 'hs-cal-day';
            if(ds < today) cls += ' hs-cal-day--past';
            else if(ds === today) cls += ' hs-cal-day--today';

            if(calStartDate && calEndDate) {
                if(ds===calStartDate) cls += ' hs-cal-day--range-start';
                else if(ds===calEndDate) cls += ' hs-cal-day--range-end';
                else if(ds>calStartDate && ds<calEndDate) cls += ' hs-cal-day--in-range';
            } else if(calStartDate && ds===calStartDate) {
                cls += ' hs-cal-day--single';
            }

            cells += `<div class="${cls}" data-date="${ds}"><div class="hs-cal-day-inner">${d}</div></div>`;
        }
        return cells;
    }

    function renderCalendar() {
        const grid = document.getElementById('hs-cal-months-grid');
        if(!grid) return;

        // Left month
        const ly=calViewYear, lm=calViewMonth;
        // Right month
        let ry=ly, rm=lm+1;
        if(rm>11){rm=0;ry++;}

        grid.innerHTML=`
            <div class="hs-cal-month">
                <div class="hs-cal-month-header">
                    <button type="button" class="hs-cal-nav-btn" id="hs-cal-prev" aria-label="Previous month">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                    </button>
                    <span class="hs-cal-month-title">${MONTH_NAMES[lm]} ${ly}</span>
                    <button type="button" class="hs-cal-nav-btn hs-invisible" aria-label=""></button>
                </div>
                <div class="hs-cal-weekdays">${DAY_ABBR.map(d=>`<div class="hs-cal-weekday">${d}</div>`).join('')}</div>
                <div class="hs-cal-days">${buildMonth(ly,lm)}</div>
            </div>
            <div class="hs-cal-month">
                <div class="hs-cal-month-header">
                    <button type="button" class="hs-cal-nav-btn hs-invisible" aria-label=""></button>
                    <span class="hs-cal-month-title">${MONTH_NAMES[rm]} ${ry}</span>
                    <button type="button" class="hs-cal-nav-btn" id="hs-cal-next" aria-label="Next month">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                    </button>
                </div>
                <div class="hs-cal-weekdays">${DAY_ABBR.map(d=>`<div class="hs-cal-weekday">${d}</div>`).join('')}</div>
                <div class="hs-cal-days">${buildMonth(ry,rm)}</div>
            </div>`;

        // Disable prev if left month is current month
        const now=new Date();
        const prevBtn=document.getElementById('hs-cal-prev');
        if(prevBtn && ly===now.getFullYear() && lm===now.getMonth()) {
            prevBtn.disabled=true; prevBtn.style.opacity='0.3'; prevBtn.style.cursor='default';
        }

        // Bind nav buttons
        document.getElementById('hs-cal-prev')?.addEventListener('click', e=>{
            e.stopPropagation();
            calViewMonth--; if(calViewMonth<0){calViewMonth=11;calViewYear--;}
            renderCalendar();
        });
        document.getElementById('hs-cal-next')?.addEventListener('click', e=>{
            e.stopPropagation();
            calViewMonth++; if(calViewMonth>11){calViewMonth=0;calViewYear++;}
            renderCalendar();
        });

        // Bind day clicks
        grid.querySelectorAll('.hs-cal-day:not(.hs-cal-day--past):not(.hs-cal-day--empty)').forEach(cell=>{
            cell.addEventListener('click', e=>{
                e.stopPropagation();
                const ds=cell.dataset.date;
                if(!ds) return;
                if(calClickState===0) {
                    // First click — set start
                    calStartDate=ds; calEndDate=null; calClickState=1;
                } else {
                    // Second click — set end (ensure order)
                    if(ds<calStartDate) { calEndDate=calStartDate; calStartDate=ds; }
                    else if(ds===calStartDate) { calEndDate=null; calClickState=0; }
                    else { calEndDate=ds; }
                    calClickState=0;
                }
                renderCalendar();
                syncDateLabels();
            });

            // Hover preview
            cell.addEventListener('mouseenter', ()=>{
                if(calClickState!==1) return;
                const ds=cell.dataset.date;
                if(!ds) return;
                // Light hover highlight — re-render with temp end
                const temp=calEndDate;
                calEndDate=ds>=calStartDate?ds:null;
                renderCalendar();
                calEndDate=temp;
            });
        });

        syncDateLabels();
    }

    function openDateModal() {
        const modal=document.getElementById('hs-date-modal');
        const backdrop=document.getElementById('hs-date-modal-backdrop');
        if(!modal) return;
        // Reset view to show current month pair
        const now=new Date();
        calViewYear=now.getFullYear(); calViewMonth=now.getMonth();
        // If start date exists, show that month pair
        if(calStartDate) {
            const [y,m]=calStartDate.split('-');
            calViewYear=parseInt(y); calViewMonth=parseInt(m)-1;
        }
        renderCalendar();
        modal.classList.add('open');
        if(backdrop) backdrop.classList.add('open');
    }
    function closeDateModal() {
        document.getElementById('hs-date-modal')?.classList.remove('open');
        document.getElementById('hs-date-modal-backdrop')?.classList.remove('open');
    }

    function initCalendar() {
        // Trigger from date fields
        document.getElementById('hs-checkin-field')?.addEventListener('click', e=>{ e.stopPropagation(); openDateModal(); });
        document.getElementById('hs-checkout-field')?.addEventListener('click', e=>{ e.stopPropagation(); openDateModal(); });

        // Calendar type tabs
        document.querySelectorAll('.hs-cal-type-tab').forEach(tab=>{
            tab.addEventListener('click', ()=>{
                document.querySelectorAll('.hs-cal-type-tab').forEach(t=>t.classList.remove('active'));
                tab.classList.add('active');
                const panel=document.getElementById('hs-cal-months-grid');
                const flex=document.getElementById('hs-flexible-panel');
                if(tab.dataset.tab==='calendar') {
                    if(panel) panel.style.display='grid';
                    if(flex)  flex.style.display='none';
                } else {
                    if(panel) panel.style.display='none';
                    if(flex)  flex.style.display='block';
                }
            });
        });

        // Clear button
        document.getElementById('hs-cal-clear')?.addEventListener('click', e=>{
            e.stopPropagation();
            calStartDate=null; calEndDate=null; calClickState=0;
            renderCalendar();
            syncDateLabels();
        });

        // Apply button
        document.getElementById('hs-cal-apply')?.addEventListener('click', e=>{
            e.stopPropagation();
            if(calStartDate && !calEndDate) { calEndDate=calStartDate; }
            syncDateLabels();
            closeDateModal();
            // Auto-advance to guests
            setTimeout(()=>document.getElementById('hs-guests-field')?.click(), 150);
        });

        // Backdrop click closes
        document.getElementById('hs-date-modal-backdrop')?.addEventListener('click', closeDateModal);

        // Close on outside click
        document.addEventListener('click', e=>{
            const modal=document.getElementById('hs-date-modal');
            if(modal && modal.classList.contains('open') && !modal.contains(e.target) &&
               !document.getElementById('hs-checkin-field')?.contains(e.target) &&
               !document.getElementById('hs-checkout-field')?.contains(e.target)) {
                closeDateModal();
            }
        });

        // Initialize with default dates
        const now=new Date();
        const tomorrow=new Date(now); tomorrow.setDate(now.getDate()+1);
        const checkout=new Date(now); checkout.setDate(now.getDate()+4);
        calStartDate = dateStr(tomorrow.getFullYear(), tomorrow.getMonth(), tomorrow.getDate());
        calEndDate   = dateStr(checkout.getFullYear(), checkout.getMonth(), checkout.getDate());
        syncDateLabels();
    }

    // ================================================================
    // E. GUESTS & ROOMS DROPDOWN
    // ================================================================
    function initGuests() {
        const trigger  = document.getElementById('hs-guests-field');
        const dropdown = document.getElementById('hs-guests-dropdown');
        if(!trigger||!dropdown) return;

        const state={adults:2,children:0,rooms:1};
        const LIMITS={adults:[1,16],children:[0,10],rooms:[1,10]};
        const hidA=document.getElementById('hs-hidden-adults');
        const hidC=document.getElementById('hs-hidden-children');
        const hidR=document.getElementById('hs-hidden-rooms');

        function updateUI() {
            const gLbl=document.getElementById('hs-guest-label');
            const rLbl=document.getElementById('hs-room-label');
            if(gLbl) gLbl.textContent=`${state.adults} adult${state.adults!==1?'s':''}${state.children>0?', '+state.children+' child'+(state.children!==1?'ren':''):''}`;
            if(rLbl) rLbl.textContent=`${state.rooms} room${state.rooms!==1?'s':''}`;
            if(hidA) hidA.value=state.adults;
            if(hidC) hidC.value=state.children;
            if(hidR) hidR.value=state.rooms;
            ['adults','children','rooms'].forEach(t=>{
                const dec=document.getElementById(`hs-${t}-dec`);
                const inc=document.getElementById(`hs-${t}-inc`);
                const val=document.getElementById(`hs-${t}-val`);
                if(val) val.textContent=state[t];
                if(dec) dec.disabled=(state[t]<=LIMITS[t][0]);
                if(inc) inc.disabled=(state[t]>=LIMITS[t][1]);
            });
        }

        trigger.addEventListener('click', e=>{ e.stopPropagation(); dropdown.classList.toggle('open'); });
        document.addEventListener('click', e=>{ if(!trigger.contains(e.target)&&!dropdown.contains(e.target)) dropdown.classList.remove('open'); });

        ['adults','children','rooms'].forEach(t=>{
            document.getElementById(`hs-${t}-dec`)?.addEventListener('click', e=>{ e.stopPropagation(); state[t]=Math.max(LIMITS[t][0],state[t]-1); updateUI(); });
            document.getElementById(`hs-${t}-inc`)?.addEventListener('click', e=>{ e.stopPropagation(); state[t]=Math.min(LIMITS[t][1],state[t]+1); updateUI(); });
        });

        document.getElementById('hs-guests-done')?.addEventListener('click', e=>{ e.stopPropagation(); dropdown.classList.remove('open'); });
        updateUI();
    }

    // ================================================================
    // F. FORM VALIDATION
    // ================================================================
    function initForm() {
        const form=document.getElementById('hero-search-form');
        const input=document.getElementById('hs-dest-input');
        if(!form) return;
        form.addEventListener('submit', e=>{
            if(!(input?.value||'').trim()) {
                e.preventDefault();
                input?.focus();
                document.getElementById('hs-dest-field')?.style.setProperty('outline','2px solid #ef4444');
                setTimeout(()=>document.getElementById('hs-dest-field')?.style.removeProperty('outline'), 1500);
                return false;
            }
        });
    }

    // ================================================================
    // G. BOOTSTRAP
    // ================================================================
    function init() {
        initTabs();
        initDestination();
        initCalendar();
        initGuests();
        initForm();
    }

    if(document.readyState==='loading') document.addEventListener('DOMContentLoaded', init);
    else init();

})();
