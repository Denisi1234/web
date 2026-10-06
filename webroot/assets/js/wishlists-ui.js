/* Wishlists. Split for the 300-line cap — load wishlists-store.js first, then wishlists-ui.js. */
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
        btn.innerHTML = '<span class="p-dots" aria-hidden="true"><span class="p-dot"></span><span class="p-dot"></span><span class="p-dot"></span></span>Creating...';
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
