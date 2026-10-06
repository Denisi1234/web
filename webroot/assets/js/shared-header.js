/* Extracted for the 300-line cap. */
// Direct inline fail-safe event binding for instant click response
(function() {
    function closeAllPopups() {
        document.querySelectorAll('.trivago-user-dropdown').forEach(function(d) {
            d.classList.remove('show');
        });
        var btn = document.getElementById('nav_user_btn');
        if (btn) btn.style.outline = 'none';
    }

    document.addEventListener('DOMContentLoaded', function() {
        var signinBtn = document.getElementById('nav_signin_btn');
        if (signinBtn) {
            // The sign-in popup modal was removed: /login is the single
            // sign-in surface, so this is a plain link. Strip the focus ring
            // only; leave the href intact so it stays a real, middle-clickable
            // and keyboard-navigable link.
            signinBtn.style.outline = 'none';
        }

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
                if (!wasOpen) drop.classList.add('show');
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
                if (!wasOpen) drop.classList.add('show');
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
    });
})();
