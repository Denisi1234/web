/* Unified Shared Header Controller — Dynamic Currency, Wishlist Badge, A11y & Dropdowns */
(function() {
    'use strict';

    function setAriaExpanded(btn, isExpanded) {
        if (btn) btn.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
    }

    function closeAllPopups() {
        document.querySelectorAll('.trivago-user-dropdown').forEach(function(d) {
            d.classList.remove('show');
        });
        ['nav_user_btn', 'nav_lang_btn', 'nav_logged_out_lang_btn', 'nav_logged_out_menu_btn'].forEach(function(id) {
            var btn = document.getElementById(id);
            if (btn) {
                setAriaExpanded(btn, false);
                if (id === 'nav_user_btn') btn.style.outline = 'none';
            }
        });
    }

    function syncCurrencyUI() {
        var curr = 'TZS';
        try {
            curr = localStorage.getItem('fastnet_curr') || (window.FastNetCurrency && FastNetCurrency.get ? FastNetCurrency.get() : 'TZS');
        } catch (e) {}

        var label = 'EN · TSh';
        if (curr === 'USD') label = 'EN · $';
        else if (curr === 'EUR') label = 'EN · €';

        document.querySelectorAll('.nav-curr-label').forEach(function(el) {
            el.textContent = label;
        });

        document.querySelectorAll('[data-fx-option]').forEach(function(link) {
            var isSelected = link.getAttribute('data-fx-option') === curr;
            var icon = link.querySelector('.trivago-user-item-icon');
            if (isSelected) {
                link.classList.add('fw-bold', 'text-primary');
                if (icon && !icon.classList.contains('fa-check')) {
                    link._origClass = icon.className;
                    icon.className = 'fa-solid fa-check trivago-user-item-icon text-primary';
                }
            } else {
                link.classList.remove('fw-bold', 'text-primary');
                if (icon && link._origClass) {
                    icon.className = link._origClass;
                }
            }
        });
    }

    function syncWishlistBadge() {
        var badge = document.getElementById('nav_wishlist_count');
        if (!badge) return;
        var total = 0;
        try {
            var raw = localStorage.getItem('trivago_user_lists');
            if (raw) {
                var lists = JSON.parse(raw);
                if (Array.isArray(lists)) {
                    lists.forEach(function(item) { total += (parseInt(item.count, 10) || 0); });
                }
            }
            if (total === 0) {
                var idsRaw = localStorage.getItem('fn_wishlist_ids');
                if (idsRaw) {
                    var ids = JSON.parse(idsRaw);
                    if (Array.isArray(ids)) total = ids.length;
                }
            }
        } catch (e) {}

        if (total > 0) {
            badge.textContent = total > 99 ? '99+' : total;
            badge.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';
        }
    }

    function highlightActiveLinks() {
        var path = window.location.pathname.replace(/\/$/, '') || '/';
        document.querySelectorAll('.trivago-user-dropdown a[href]').forEach(function(a) {
            var href = a.getAttribute('href');
            if (href && href !== '#' && href.indexOf('javascript:') === -1) {
                var cleanHref = href.split('?')[0].replace(/\/$/, '') || '/';
                if (cleanHref === path && cleanHref !== '/') {
                    a.style.background = '#f0f4f9';
                    a.style.fontWeight = '700';
                    a.style.color = '#0f62fe';
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        var signinBtn = document.getElementById('nav_signin_btn');
        if (signinBtn) signinBtn.style.outline = 'none';

        var menuBtn = document.getElementById('nav_logged_out_menu_btn');
        var userBtn = document.getElementById('nav_user_btn');
        var langBtnIn = document.getElementById('nav_lang_btn');
        var langBtnOut = document.getElementById('nav_logged_out_lang_btn');

        if (menuBtn) {
            menuBtn.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                var drop = document.getElementById('nav_logged_out_menu_dropdown');
                if (!drop) return;
                var wasOpen = drop.classList.contains('show');
                closeAllPopups();
                if (!wasOpen) {
                    drop.classList.add('show');
                    setAriaExpanded(menuBtn, true);
                }
            };
        }

        if (userBtn) {
            userBtn.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                var drop = document.getElementById('nav_user_dropdown');
                if (!drop) return;
                var wasOpen = drop.classList.contains('show');
                closeAllPopups();
                if (!wasOpen) {
                    drop.classList.add('show');
                    setAriaExpanded(userBtn, true);
                    userBtn.style.outline = '2px solid #0f62fe';
                    userBtn.style.outlineOffset = '-2px';
                }
            };
        }

        [langBtnIn, langBtnOut].forEach(function(btn) {
            if (!btn) return;
            btn.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                var inWrap = document.getElementById('nav_logged_in_wrapper');
                var isLogged = inWrap && inWrap.style.display !== 'none';
                var dropId = isLogged ? 'nav_lang_dropdown' : 'nav_logged_out_lang_dropdown';
                var drop = document.getElementById(dropId) || document.getElementById('nav_lang_dropdown');
                if (!drop) return;
                var wasOpen = drop.classList.contains('show');
                closeAllPopups();
                if (!wasOpen) {
                    drop.classList.add('show');
                    setAriaExpanded(btn, true);
                }
            };
        });

        document.addEventListener('click', function(e) {
            if (!e.target.closest('#nav_logged_out_menu_wrapper') && 
                !e.target.closest('#nav_user_menu_wrapper') && 
                !e.target.closest('#nav_lang_menu_wrapper') && 
                !e.target.closest('#nav_logged_out_lang_wrapper')) {
                closeAllPopups();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeAllPopups();
        });

        syncCurrencyUI();
        syncWishlistBadge();
        highlightActiveLinks();

        window.addEventListener('fastnet:currency-change', syncCurrencyUI);
        window.addEventListener('fastnet:wishlist-updated', syncWishlistBadge);
        window.addEventListener('storage', function(e) {
            if (e.key === 'fastnet_curr') syncCurrencyUI();
            if (e.key === 'trivago_user_lists' || e.key === 'fn_wishlist_ids') syncWishlistBadge();
        });
    });
})();
