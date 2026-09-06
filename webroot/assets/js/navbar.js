/** fastnetstays.com - Navbar Navigation, Dropdowns & Wishlist Controller */
// ── Global Wishlist Management ─────────────────────────────────────────────

function getGlobalWishlistIds() {
    try {
        const stored = localStorage.getItem('fastnet_wishlist');
        if (stored) {
            const parsed = JSON.parse(stored);
            if (Array.isArray(parsed)) return parsed;
        }
    } catch(e) {}
    return [11, 12];
}

function saveGlobalWishlistIds(ids) {
    localStorage.setItem('fastnet_wishlist', JSON.stringify(ids));
    syncWishlistButtons();
}

async function toggleWishlist(propertyId, btnEl) {
    const id = parseInt(propertyId);
    let ids = getGlobalWishlistIds();
    const isSaved = ids.includes(id);
    const token = localStorage.getItem('auth_token') || localStorage.getItem('token');

    if (isSaved) {
        ids = ids.filter(item => item !== id);
        saveGlobalWishlistIds(ids);
        showWishlistToast('Removed from saved stays');

        if (token) {
            try {
                fetch('http://127.0.0.1:8000/api/wishlist/' + id, {
                    method: 'DELETE',
                    headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
                });
            } catch(e) {}
        }
    } else {
        ids.push(id);
        saveGlobalWishlistIds(ids);
        showWishlistToast('Saved to your Wishlist! ❤️');

        if (token) {
            try {
                fetch('http://127.0.0.1:8000/api/wishlist', {
                    method: 'POST',
                    headers: { 'Authorization': 'Bearer ' + token, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ property_id: id })
                });
            } catch(e) {}
        }
    }
}

function syncWishlistButtons() {
    const ids = getGlobalWishlistIds();
    document.querySelectorAll('[data-property-id]').forEach(btn => {
        const propId = parseInt(btn.getAttribute('data-property-id'));
        const icon = btn.querySelector('i');
        if (!icon) return;
        if (ids.includes(propId)) {
            icon.className = 'fa-solid fa-heart text-danger';
            btn.classList.add('active-wishlist');
        } else {
            icon.className = 'fa-regular fa-heart text-danger';
            btn.classList.remove('active-wishlist');
        }
    });
}

function showWishlistToast(msg) {
    let toast = document.getElementById('wishlist-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'wishlist-toast';
        toast.className = 'position-fixed bottom-0 end-0 m-4 p-3 bg-dark text-white rounded-3 shadow-lg text-sm';
        toast.style.zIndex = '9999';
        toast.style.transition = 'opacity 0.3s ease';
        document.body.appendChild(toast);
    }
    toast.innerHTML = '<i class="fa-solid fa-heart text-danger me-2"></i>' + msg;
    toast.style.display = 'block';
    toast.style.opacity = '1';
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => { toast.style.display = 'none'; }, 300);
    }, 2500);
}

// ── Global Trivago Navbar Dropdowns & Popups ──
function toggleNavUserDropdown(e) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    const drop = document.getElementById('nav_user_dropdown');
    const btn = document.getElementById('nav_user_btn');
    if (!drop) return;
    const wasOpen = drop.classList.contains('show');
    closeNavPopups();
    if (!wasOpen) {
        drop.classList.add('show');
        if (btn) {
            btn.style.outline = '2px solid #007fad';
            btn.style.outlineOffset = '-2px';
        }
    }
}

function toggleNavLangDropdown(e) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    const inWrapper = document.getElementById('nav_logged_in_wrapper');
    const isLoggedIn = inWrapper && inWrapper.style.display !== 'none';
    const drop = document.getElementById(isLoggedIn ? 'nav_lang_dropdown' : 'nav_logged_out_lang_dropdown') || document.getElementById('nav_lang_dropdown');
    if (!drop) return;
    const wasOpen = drop.classList.contains('show');
    closeNavPopups();
    if (!wasOpen) {
        drop.classList.add('show');
    }
}

function setLang(val) {
    document.querySelectorAll('#nav_lang_btn span, #nav_logged_out_lang_btn span').forEach(el => el.innerText = val);
    closeNavPopups();
}

function handleMenuClick(e) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    const drop = document.getElementById('nav_logged_out_menu_dropdown');
    if (!drop) {
        window.location.href = '/menu';
        return;
    }
    const wasOpen = drop.classList.contains('show');
    closeNavPopups();
    if (!wasOpen) {
        drop.classList.add('show');
    }
}

function handleNavLogout(e) {
    try {
        localStorage.removeItem('auth_token');
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        localStorage.setItem('is_logged_out', '1');
    } catch(err) {}
}

function syncNavAuthState() {
    try {
        const inGroup = document.getElementById('nav_logged_in_wrapper');
        const outGroup = document.getElementById('nav_logged_out_wrapper');
        if (!inGroup || !outGroup) return;

        const token = localStorage.getItem('auth_token') || localStorage.getItem('token');
        let user = null;
        try {
            user = JSON.parse(localStorage.getItem('user') || 'null');
        } catch(e) {}

        const hasValidUser = Boolean(token) || Boolean(user && (user.id || user.email));
        if (hasValidUser) {
            localStorage.removeItem('is_logged_out');
        }

        const isLoggedOutClient = localStorage.getItem('is_logged_out') === '1';
        const isAuthenticated = !isLoggedOutClient && hasValidUser;

        if (!isAuthenticated) {
            inGroup.style.setProperty('display', 'none', 'important');
            outGroup.style.setProperty('display', 'flex', 'important');
        } else {
            // Signed in! Switch immediately to logged-in header controls
            inGroup.style.setProperty('display', 'flex', 'important');
            outGroup.style.setProperty('display', 'none', 'important');

            if (user) {
                const userBtn = document.getElementById('nav_user_btn');
                const welcomeHeader = document.querySelector('#nav_user_dropdown .trivago-user-head-row span');
                const displayName = user.first_name || (user.name ? user.name.split(' ')[0] : 'Traveler');
                if (displayName) {
                    if (welcomeHeader) welcomeHeader.innerText = 'Welcome back, ' + displayName;
                    if (userBtn) userBtn.innerText = displayName.trim().charAt(0).toUpperCase();
                }
            }
        }
    } catch(e) {}
}

function closeNavPopups() {
    const drops = ['nav_recent_dropdown', 'nav_datepicker_modal', 'nav_guest_modal', 'nav_user_dropdown', 'nav_lang_dropdown', 'nav_logged_out_menu_dropdown'];
    drops.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.classList.remove('show');
    });
    const pods = ['nav_dest_pod', 'nav_date_pod', 'nav_guest_pod', 'nav_user_btn'];
    pods.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.style.outline = 'none';
    });
}

document.addEventListener('click', function(e) {
    const form = document.getElementById('nav_search_form');
    const userWrapper = document.getElementById('nav_user_menu_wrapper');
    const langWrapper = document.getElementById('nav_lang_menu_wrapper');
    const outLangWrapper = document.getElementById('nav_logged_out_lang_wrapper');
    const outMenuWrapper = document.getElementById('nav_logged_out_menu_wrapper');

    const clickedInsideSearch = form && form.contains(e.target);
    const clickedInsideUser = userWrapper && userWrapper.contains(e.target);
    const clickedInsideLang = (langWrapper && langWrapper.contains(e.target)) || (outLangWrapper && outLangWrapper.contains(e.target));
    const clickedInsideOutMenu = outMenuWrapper && outMenuWrapper.contains(e.target);
    
    if (!clickedInsideSearch && !clickedInsideUser && !clickedInsideLang && !clickedInsideOutMenu) {
        closeNavPopups();
    }
});

document.addEventListener('DOMContentLoaded', function() {
    syncWishlistButtons();
    syncNavAuthState();
    
    // Sync User Profile info in Navbar
    try {
        const user = JSON.parse(localStorage.getItem('user') || '{}');
        const userBtn = document.getElementById('nav_user_btn');
        const welcomeHeader = document.querySelector('#nav_user_dropdown .trivago-user-head-row span');
        
        let displayName = user.name || user.firstName || user.first_name || (user.email ? user.email.split('@')[0] : '');
        if (welcomeHeader && displayName) {
            welcomeHeader.innerText = 'Welcome back, ' + displayName;
        }
        if (userBtn && displayName) {
            userBtn.innerText = displayName.trim().charAt(0).toUpperCase();
        }
    } catch(e) {}
});
