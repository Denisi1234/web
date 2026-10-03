/**
 * Shared sign-in completion for the web frontend.
 *
 * Both sign-in surfaces (the /login page and the header modal) end the same way:
 * the backend has just returned a token, and the PHP session still has to be
 * seeded. That second step used to be wrapped in `catch (err) {}` in both
 * templates, so a failed sync still showed "Login successful!" and redirected
 * into a page whose portal guards bounced straight back to /login. It is now
 * surfaced and retried instead of swallowed.
 */
window.FastAuth = (function () {
    'use strict';

    function config() {
        return window.FastAuthConfig || {};
    }

    function apiUrl(path) {
        if (typeof window.API_URL === 'function') {
            return window.API_URL(path);
        }
        if (typeof window.FASTNET_API_URL === 'string' && window.FASTNET_API_URL) {
            return window.FASTNET_API_URL.replace(/\/+$/, '') + path;
        }
        return path;
    }

    async function postJson(path, body) {
        const res = await fetch(apiUrl(path), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify(body)
        });
        let data = null;
        try { data = await res.json(); } catch (e) { data = null; }
        return { ok: res.ok, status: res.status, data: data };
    }

    function storeCredentials(data) {
        const token = (data && (data.access_token || data.token)) || '';
        const user = (data && data.user) || null;

        localStorage.removeItem('is_logged_out');
        if (token) {
            localStorage.setItem('auth_token', token);
            localStorage.setItem('token', token);
        }
        if (user) {
            localStorage.setItem('user', JSON.stringify(user));
        }
        return { token: token, user: user };
    }

    /**
     * Seed the PHP session from the token. The backend verifies it via /me and
     * only copies whitelisted fields, so the client is never trusted here.
     *
     * Resolves to true on success, false if the session could not be seeded.
     */
    async function syncSession(token, user, attempt) {
        attempt = attempt || 1;
        try {
            const res = await fetch(config().syncUrl || '/login', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    action: 'login_sync',
                    user: user,
                    token: token
                })
            });
            if (res.ok) {
                return true;
            }
            // 429 is the per-IP attempt budget: retrying immediately is pointless.
            if (res.status === 429 || attempt >= 2) {
                return false;
            }
        } catch (err) {
            if (attempt >= 2) {
                return false;
            }
        }
        return syncSession(token, user, attempt + 1);
    }

    /**
     * Where to send the user after a successful sign-in.
     * Mirrors PagesController::safeRedirect() so a crafted ?redirect= cannot
     * bounce anyone off-site.
     */
    function safeRedirect(url) {
        if (typeof url !== 'string' || url === '' || url.length > 500) return '';
        if (url.charAt(0) !== '/' || url.charAt(1) === '/') return '';
        if (url.indexOf('\\') !== -1 || /[\r\n\t<>"]/.test(url)) return '';
        if (!/^\/[A-Za-z0-9\/_\-.?=&%#+]*$/.test(url)) return '';
        return url;
    }

    function resolveDestination(user) {
        const params = new URLSearchParams(window.location.search);
        const redirect = safeRedirect(params.get('redirect') || '');
        const pageRole = (params.get('role') === 'owner' || params.get('role') === 'admin')
            ? params.get('role')
            : 'customer';
        const role = ((user && user.role) || '').toLowerCase();

        if (redirect) return redirect;
        if (role === 'owner') return config().hostDashboard || '/host/dashboard';
        if (role === 'admin') return config().adminDashboard || '/admin/dashboard';
        if (pageRole === 'owner') return config().joinUs || '/join-us';
        return config().home || '/';
    }

    /**
     * Store the credentials, seed the session and go to the right place.
     * onSyncFailure lets the caller explain the problem instead of silently
     * redirecting into a half-signed-in state.
     */
    async function finish(data, onSyncFailure) {
        const creds = storeCredentials(data);
        if (!creds.token) {
            return false;
        }

        const synced = await syncSession(creds.token, creds.user);
        if (!synced && typeof onSyncFailure === 'function') {
            onSyncFailure();
            return false;
        }

        if (typeof window.syncNavAuthState === 'function') {
            try { window.syncNavAuthState(); } catch (e) {}
        }

        window.location.href = resolveDestination(creds.user);
        return true;
    }

    return {
        apiUrl: apiUrl,
        postJson: postJson,
        storeCredentials: storeCredentials,
        syncSession: syncSession,
        resolveDestination: resolveDestination,
        finish: finish
    };
})();