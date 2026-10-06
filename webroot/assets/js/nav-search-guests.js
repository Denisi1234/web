/* Nav search. Split for the 300-line cap — load dates, suggest, fetch, guests in order (shared globals). */
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
