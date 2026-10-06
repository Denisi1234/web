<script>
// Remove single item from recently viewed
async function removeRecentlyViewed(id) {
    const card = document.getElementById('rv_card_' + id);
    if (card) {
        card.style.opacity = '0';
        card.style.transform = 'scale(0.95)';
        setTimeout(() => {
            card.remove();
            checkEmptyState();
        }, 250);
    }
    // Remove from localStorage
    try {
        let recents = JSON.parse(localStorage.getItem('fastnet_recently_viewed') || '[]');
        if (Array.isArray(recents)) {
            recents = recents.filter(item => item && parseInt(item.id) !== parseInt(id));
            localStorage.setItem('fastnet_recently_viewed', JSON.stringify(recents));
        }
    } catch(e) {}
    // Remove from server session
    try {
        const csrf = document.querySelector('meta[name="csrfToken"]')?.content || '';
        await fetch('/recently-viewed/remove/' + id, {
            method: 'POST',
            headers: { 'X-CSRF-Token': csrf, 'Accept': 'application/json' }
        });
    } catch(e) {}
}

// Clear all recently viewed items
async function clearRecentlyViewed() {
    if (!confirm('Clear all recently viewed stays?')) return;
    const grid = document.getElementById('rv_grid_container');
    if (grid) grid.innerHTML = '';
    // Clear localStorage
    try {
        localStorage.removeItem('fastnet_recently_viewed');
    } catch(e) {}
    // Clear server session
    try {
        const csrf = document.querySelector('meta[name="csrfToken"]')?.content || '';
        await fetch('/recently-viewed/clear', {
            method: 'POST',
            headers: { 'X-CSRF-Token': csrf, 'Accept': 'application/json' }
        });
    } catch(e) {}
    checkEmptyState();
}

function checkEmptyState() {
    const grid = document.getElementById('rv_grid_container');
    const emptyBox = document.getElementById('rv_empty_container');
    const clearBtn = document.getElementById('btn_clear_all');
    const countSub = document.getElementById('rv_count_sub');
    const remainingCards = grid ? grid.querySelectorAll('.rv-card') : [];
    
    if (remainingCards.length === 0) {
        if (grid) grid.style.display = 'none';
        if (emptyBox) emptyBox.style.display = 'block';
        if (clearBtn) clearBtn.style.display = 'none';
        if (countSub) countSub.innerText = 'Stays and properties you recently browsed';
    } else {
        if (grid) grid.style.display = 'grid';
        if (emptyBox) emptyBox.style.display = 'none';
        if (clearBtn) clearBtn.style.display = 'inline-flex';
        if (countSub) countSub.innerText = remainingCards.length + ' stays saved from your browsing';
    }
}

// Client-side hydration from localStorage if session was clean
document.addEventListener('DOMContentLoaded', function() {
    if (typeof syncWishlistButtons === 'function') {
        syncWishlistButtons();
    }
});
</script>
