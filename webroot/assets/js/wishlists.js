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

const defaultLists = [
    { id: 'next_stay', name: 'Your next stay', count: 0 },
    { id: 'demi', name: 'DEMI', count: 0 }
];

function getStoredLists() {
    try {
        const stored = localStorage.getItem('trivago_user_lists');
        if (stored) {
            const parsed = JSON.parse(stored);
            if (Array.isArray(parsed) && parsed.length > 0) return parsed;
        }
    } catch(e) {}
    return defaultLists;
}

function saveStoredLists(lists) {
    try {
        localStorage.setItem('trivago_user_lists', JSON.stringify(lists));
    } catch(e) {}
}

function renderFavouritesGrid() {
    const lists = getStoredLists();
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
                <button type="button" class="fav-card-share-btn" title="Share list" onclick="shareList(event, '${item.name.replace(/'/g, "\\'")}')">
                    <i class="fa-solid fa-share-nodes"></i>
                </button>
            </div>
            <div class="fav-card-title-row">
                <span class="fav-card-name">${item.name}</span>
                <span class="fav-card-stays">(${item.count} stays)</span>
            </div>
        `;
        // Navigate on card click (excluding share button)
        card.addEventListener('click', (e) => {
            if (!e.target.closest('.fav-card-share-btn')) {
                window.location.href = '<?= $this->Url->build('/hotel-list-01'); ?>';
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
            setTimeout(() => input.focus(), 50);
        }
    }
}

function closeCreateListModal() {
    const modal = document.getElementById('createListModal');
    if (modal) modal.classList.remove('open');
}

function submitCreateList() {
    const input = document.getElementById('newListInput');
    const val = input ? input.value.trim() : '';
    if (!val) {
        showFavToast('Please enter a list name');
        return;
    }
    const lists = getStoredLists();
    if (lists.length >= 20) {
        showFavToast('You have reached the limit of 20 lists');
        closeCreateListModal();
        return;
    }
    lists.push({
        id: 'list_' + Date.now(),
        name: val,
        count: 0
    });
    saveStoredLists(lists);
    renderFavouritesGrid();
    closeCreateListModal();
    showFavToast(`List "${val}" created`);
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
    const toast = document.getElementById('trivago-toast');
    if (!toast) return;
    toast.innerText = msg;
    toast.style.display = 'block';
    setTimeout(() => { toast.style.opacity = '1'; }, 10);
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => { toast.style.display = 'none'; }, 250);
    }, 2500);
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