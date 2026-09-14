/** fastnetstays.com - Wishlists Controller */
const hotelIllustrationSvg = `
<svg width="130" height="110" viewBox="0 0 160 140" fill="none" xmlns="http://www.w3.org/2000/svg">
    <!-- Building drop shadow -->
    <rect x="42" y="32" width="76" height="96" rx="4" fill="#cbd5e1" opacity="0.6"/>
    <!-- Main Center Building -->
    <rect x="36" y="26" width="76" height="102" rx="4" fill="#ffffff" stroke="#334155" stroke-width="2.5"/>
    
    <!-- Top ornamental trim -->
    <rect x="48" y="16" width="52" height="10" rx="2" fill="#ffffff" stroke="#334155" stroke-width="2.5"/>
    <line x1="58" y1="21" x2="90" y2="21" stroke="#64748b" stroke-width="2"/>
    
    <!-- HOTEL Sign banner -->
    <rect x="44" y="36" width="60" height="20" rx="3" fill="#f8fafc" stroke="#334155" stroke-width="2"/>
    <text x="74" y="51" font-family="-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif" font-size="11" font-weight="900" fill="#334155" text-anchor="middle" letter-spacing="2.5">HOTEL</text>
    
    <!-- Windows Left Column -->
    <rect x="46" y="66" width="14" height="18" rx="2" fill="#f1f5f9" stroke="#334155" stroke-width="2"/>
    <line x1="46" y1="75" x2="60" y2="75" stroke="#334155" stroke-width="1.5"/>
    <line x1="53" y1="66" x2="53" y2="84" stroke="#334155" stroke-width="1.5"/>
    
    <!-- Windows Right Column -->
    <rect x="88" y="66" width="14" height="18" rx="2" fill="#f1f5f9" stroke="#334155" stroke-width="2"/>
    <line x1="88" y1="75" x2="102" y2="75" stroke="#334155" stroke-width="1.5"/>
    <line x1="95" y1="66" x2="95" y2="84" stroke="#334155" stroke-width="1.5"/>
    
    <!-- Center Entrance Canopy & Door -->
    <path d="M63 94H85V128H63V94Z" fill="#f8fafc" stroke="#334155" stroke-width="2"/>
    <!-- Canopy -->
    <path d="M59 94H89L85 88H63L59 94Z" fill="#334155"/>
    <!-- Door glass -->
    <line x1="74" y1="96" x2="74" y2="128" stroke="#334155" stroke-width="1.5"/>
    <circle cx="70" cy="112" r="1.5" fill="#334155"/>
    <circle cx="78" cy="112" r="1.5" fill="#334155"/>
    
    <!-- Decorative side plants -->
    <path d="M57 122Q54 117 56 112Q59 117 57 122Z" fill="#64748b"/>
    <path d="M91 122Q94 117 92 112Q89 117 91 122Z" fill="#64748b"/>
    
    <!-- Ground line -->
    <line x1="20" y1="128" x2="135" y2="128" stroke="#334155" stroke-width="2.5" stroke-linecap="round"/>
</svg>
`;

async function fetchRealLists() {
    try {
        const token = localStorage.getItem('auth_token') || '';
        const headers = { 'Accept': 'application/json' };
        if (token) headers['Authorization'] = 'Bearer ' + token;
        
        const r = await fetch('/wishlist-lists', { headers: headers, credentials: 'same-origin' });
        if (r.ok) {
            const j = await r.json();
            if (Array.isArray(j) && j.length > 0) {
                return j.map(x => ({ id: x.id, name: x.name, count: x.count || 0 }));
            }
        }
    } catch(e) {}

    // Fallback to direct backend API if available
    try {
        const token = localStorage.getItem('auth_token') || '';
        const apiHost = (typeof window.FASTNET_API_URL === 'string' && window.FASTNET_API_URL)
            ? window.FASTNET_API_URL
            : (window.location.protocol + '//' + window.location.hostname + ':8000');
        
        const headers = { 'Accept': 'application/json' };
        if (token) headers['Authorization'] = 'Bearer ' + token;

        const r = await fetch(apiHost + '/api/wishlist-lists', { headers: headers });
        if (r.ok) {
            const j = await r.json();
            if (Array.isArray(j) && j.length > 0) {
                return j.map(x => ({ id: x.id, name: x.name, count: x.count || 0 }));
            }
        }
    } catch(e) {}

    return null;
}

function getStoredLists() {
    try {
        const stored = localStorage.getItem('trivago_user_lists');
        if (stored) {
            const parsed = JSON.parse(stored);
            if (Array.isArray(parsed) && parsed.length > 0) return parsed;
        }
    } catch(e) {}
    return [
        { id: 'next_stay', name: 'Your next stay', count: 0 },
        { id: 'zanzibar', name: 'Zanzibar Stays', count: 0 }
    ];
}

function saveStoredLists(lists) {
    try {
        localStorage.setItem('trivago_user_lists', JSON.stringify(lists));
    } catch(e) {}
}

async function renderFavouritesGrid() {
    const loader = document.getElementById('favLoading');
    if (loader) loader.style.display = 'flex';
    
    let lists = await fetchRealLists();
    if (!Array.isArray(lists) || lists.length === 0) {
        lists = getStoredLists();
    }
    if (loader) loader.style.display = 'none';

    const container = document.getElementById('favouriteListsContainer');
    const counter = document.getElementById('listsCounter');
    
    if (counter) {
        counter.innerText = `${lists.length}/20 lists`;
    }
    
    if (!container) return;
    container.innerHTML = '';

    lists.forEach(item => {
        const card = document.createElement('div');
        card.className = 'fav-card-wrapper';
        card.innerHTML = `
            <div class="fav-card-visual">
                ${hotelIllustrationSvg}
                <button type="button" class="fav-card-share-btn" title="Share list" onclick="shareList(event, '${String(item.name).replace(/'/g, "\\'")}')">
                    <i class="fa-solid fa-share-nodes"></i>
                </button>
            </div>
            <div class="fav-card-title-row">
                <span class="fav-card-name">${item.name}</span>
                <span class="fav-card-stays">(${item.count || 0} stays)</span>
            </div>
        `;
        card.addEventListener('click', (e) => {
            if (!e.target.closest('.fav-card-share-btn')) {
                window.location.href = '/?destination=' + encodeURIComponent(item.name);
            }
        });
        container.appendChild(card);
    });
}

function openCreateListModal() {
    const modal = document.getElementById('createListModal');
    const input = document.getElementById('newListInput');
    if (modal) {
        modal.classList.add('open');
        if (input) {
            input.value = '';
            setTimeout(() => input.focus(), 60);
        }
    }
}

function closeCreateListModal() {
    const modal = document.getElementById('createListModal');
    if (modal) modal.classList.remove('open');
}

async function removeWishlist(pid) {
    if (window.FastnetLoader && window.FastnetLoader.bar) {
        window.FastnetLoader.bar.start();
    }
    const cardEl = document.getElementById('wishlist_card_' + pid);
    if (cardEl) {
        cardEl.style.opacity = '0.5';
        cardEl.style.pointerEvents = 'none';
    }

    const csrf = document.querySelector('meta[name="csrfToken"]')?.content || '';
    const token = localStorage.getItem('auth_token') || '';
    const headers = { 
        'X-CSRF-Token': csrf, 
        'Accept': 'application/json' 
    };
    if (token) headers['Authorization'] = 'Bearer ' + token;

    try {
        const r = await fetch('/wishlist/' + pid, {
            method: 'DELETE',
            headers: headers,
            credentials: 'same-origin'
        });

        // Also sync to direct backend API
        const apiHost = (typeof window.FASTNET_API_URL === 'string' && window.FASTNET_API_URL)
            ? window.FASTNET_API_URL
            : (window.location.protocol + '//' + window.location.hostname + ':8000');
        
        await fetch(apiHost + '/api/wishlist/' + pid, {
            method: 'DELETE',
            headers: headers
        }).catch(() => {});

        if (cardEl) {
            cardEl.style.transition = 'all 0.3s ease';
            cardEl.style.transform = 'scale(0.9)';
            cardEl.style.opacity = '0';
            setTimeout(() => {
                cardEl.remove();
                // Check if any cards left
                const grid = document.getElementById('savedStaysGrid');
                if (grid && grid.children.length === 0) {
                    const wrap = document.getElementById('savedStaysSection');
                    if (wrap) wrap.innerHTML = '<div class="alert alert-info py-2" style="font-size:13px">No favourites yet — tap ♡ on any stay to save.</div>';
                }
            }, 300);
        }

        if (window.FastnetLoader && window.FastnetLoader.bar) {
            window.FastnetLoader.bar.done();
        }
        showFavToast('Removed from favourites');
    } catch(e) {
        if (window.FastnetLoader && window.FastnetLoader.bar) {
            window.FastnetLoader.bar.done();
        }
        if (cardEl) {
            cardEl.style.opacity = '1';
            cardEl.style.pointerEvents = 'auto';
        }
        showFavToast('Could not remove stay');
    }
}

async function submitCreateList() {
    const input = document.getElementById('newListInput');
    const val = input ? input.value.trim() : '';
    if (!val) {
        showFavToast('Please enter a list name');
        return;
    }
    const btn = document.querySelector('#createListModal .trivago-btn-primary');
    const origText = btn ? btn.innerText : 'Create';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Creating...';
    }
    if (window.FastnetLoader && window.FastnetLoader.bar) {
        window.FastnetLoader.bar.start();
    }

    const csrf = document.querySelector('meta[name="csrfToken"]')?.content || '';
    const token = localStorage.getItem('auth_token') || '';
    const headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-Token': csrf
    };
    if (token) headers['Authorization'] = 'Bearer ' + token;

    try {
        // 1. Post to CakePHP endpoint
        const r = await fetch('/wishlist-lists', {
            method: 'POST',
            headers: headers,
            credentials: 'same-origin',
            body: JSON.stringify({ name: val })
        });
        const j = await r.json().catch(() => ({}));
        if (!r.ok) throw new Error(j.message || 'Failed');

        // 2. Direct backend sync
        const apiHost = (typeof window.FASTNET_API_URL === 'string' && window.FASTNET_API_URL)
            ? window.FASTNET_API_URL
            : (window.location.protocol + '//' + window.location.hostname + ':8000');
        
        await fetch(apiHost + '/api/wishlist-lists', {
            method: 'POST',
            headers: headers,
            body: JSON.stringify({ name: val })
        }).catch(() => {});

        if (window.FastnetLoader && window.FastnetLoader.bar) {
            window.FastnetLoader.bar.done();
        }
        showFavToast(`List "${val}" created in database`);
        closeCreateListModal();
        await renderFavouritesGrid();
    } catch(e) {
        // Local fallback if offline
        const lists = getStoredLists();
        if (lists.length >= 20) {
            showFavToast('You have reached the limit of 20 lists');
            closeCreateListModal();
            return;
        }
        lists.unshift({ id: 'list_' + Date.now(), name: val, count: 0 });
        saveStoredLists(lists);
        await renderFavouritesGrid();
        closeCreateListModal();
        showFavToast(`List "${val}" created`);
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerText = origText;
        }
        if (window.FastnetLoader && window.FastnetLoader.bar) {
            window.FastnetLoader.bar.done();
        }
    }
}

function shareList(e, listName) {
    e.stopPropagation();
    if (navigator.clipboard) {
        navigator.clipboard.writeText(window.location.href);
        showFavToast(`Link copied for "${listName}"`);
    } else {
        showFavToast(`Share "${listName}" link`);
    }
}

function showFavToast(msg) {
    let toast = document.getElementById('trivago-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'trivago-toast';
        document.body.appendChild(toast);
    }
    toast.innerText = msg;
    toast.style.display = 'block';
    setTimeout(() => { toast.style.opacity = '1'; }, 10);
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => { toast.style.display = 'none'; }, 250);
    }, 2800);
}

document.addEventListener('DOMContentLoaded', function() {
    renderFavouritesGrid();

    // Close modal if clicked backdrop
    const modal = document.getElementById('createListModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeCreateListModal();
        });
    }

    // Support enter key in modal input
    const input = document.getElementById('newListInput');
    if (input) {
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') submitCreateList();
        });
    }
});