/* Notification bell — live unread badge + dropdown inbox.
 * Direct to the backend notification API (same credentials as sign-in).
 * Polls while the tab is visible; panel opens on click, marks read.
 */
window.FastNotif = (function () {
  'use strict';

  var POLL_MS = 30000;
  var timer = null;
  var opened = false;

  function token() {
    try {
      return window.localStorage.getItem('auth_token') || window.localStorage.getItem('token') || '';
    } catch (e) {
      return '';
    }
  }

  function base() {
    var m = document.querySelector('meta[name="api-base"]');
    var b = m ? (m.getAttribute('content') || '') : '';
    if (b) return b.replace(/\/+$/, '');
    if (typeof window.API_URL === 'function') {
      try {
        var u = window.API_URL('/api/notifications');
        if (u && u.indexOf('/api/notifications') !== -1) return u.replace(/\/api\/notifications$/, '/api');
      } catch (e) {}
    }
    if (typeof window.FASTNET_API_URL === 'string' && window.FASTNET_API_URL) {
      return window.FASTNET_API_URL.replace(/\/+$/, '') + '/api';
    }
    return '';
  }

  async function call(path, opts) {
    var b = base();
    if (!b || !token()) return null;
    opts = opts || {};
    var res = await fetch(b + path, {
      method: opts.method || 'GET',
      credentials: 'omit',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        Authorization: 'Bearer ' + token()
      },
      body: opts.body ? JSON.stringify(opts.body) : undefined
    });
    if (res.status === 401) return { _unauth: true };
    var data = null;
    try { data = await res.json(); } catch (e) { data = null; }
    return { status: res.status, ok: res.ok, data: data || {} };
  }

  function iconFor(type) {
    type = String(type || '');
    if (type.indexOf('booking_cancel') === 0) return 'fa-calendar-xmark';
    if (type.indexOf('booking_confirm') === 0) return 'fa-calendar-check';
    if (type.indexOf('booking_complet') === 0) return 'fa-circle-check';
    if (type.indexOf('booking_resched') === 0) return 'fa-calendar-days';
    if (type.indexOf('payment') === 0 || type.indexOf('receipt') === 0) return 'fa-receipt';
    if (type.indexOf('message') === 0) return 'fa-comment-dots';
    if (type.indexOf('welcome') === 0) return 'fa-hand-sparkles';
    return 'fa-bell';
  }

  function safeUrl(url) {
    if (typeof url !== 'string' || url === '') return '';
    if (url.charAt(0) !== '/' || url.charAt(1) === '/') return '';
    if (url.indexOf('\\') !== -1 || /[\r\n\t<>"]/.test(url)) return '';
    return url;
  }

  function timeAgo(iso) {
    if (!iso) return '';
    var d = new Date(iso);
    if (isNaN(d.getTime())) return '';
    var s = Math.floor((Date.now() - d.getTime()) / 1000);
    if (s < 60) return 'Just now';
    if (s < 3600) return Math.floor(s / 60) + 'm ago';
    if (s < 86400) return Math.floor(s / 3600) + 'h ago';
    var days = Math.floor(s / 86400);
    if (days === 1) return 'Yesterday';
    if (days < 7) return days + 'd ago';
    return d.toLocaleDateString([], { day: 'numeric', month: 'short' });
  }

  function esc(s) {
    var d = document.createElement('div');
    d.textContent = s == null ? '' : String(s);
    return d.innerHTML;
  }

  function setBadge(n) {
    var badge = document.getElementById('fnBellBadge');
    if (!badge) return;
    n = Math.max(0, parseInt(n, 10) || 0);
    badge.style.display = n > 0 ? 'flex' : 'none';
    badge.textContent = n > 99 ? '99+' : String(n);
    var btn = document.getElementById('fnBellBtn');
    if (btn) btn.setAttribute('aria-label', n > 0 ? 'Notifications, ' + n + ' unread' : 'Notifications');
  }

  async function refreshBadge() {
    if (!token() || document.hidden) return;
    try {
      var res = await call('/notifications/unread-count');
      if (!res || res._unauth) return;
      setBadge(res.data.unread_count || 0);
    } catch (e) {}
  }

  function renderList(items) {
    var list = document.getElementById('fnBellList');
    if (!list) return;
    list.innerHTML = '';
    if (!items.length) {
      list.innerHTML = '<div class="fn-bell-empty">You are all caught up.<br>Booking updates and replies will appear here.</div>';
      return;
    }
    items.forEach(function (n) {
      var a = document.createElement('a');
      a.className = 'fn-bell-item' + (n.is_read ? '' : ' is-unread');
      var url = safeUrl(n.url);
      if (url) a.href = url;
      else { a.href = 'javascript:void(0)'; a.setAttribute('role', 'button'); }
      var ic = document.createElement('div');
      ic.className = 'fn-bell-ic';
      ic.innerHTML = '<i class="fa-solid ' + iconFor(n.type) + '"></i>';
      var tx = document.createElement('div');
      tx.style.flex = '1';
      tx.style.minWidth = '0';
      var title = n.title || 'Notification';
      var body = n.body || '';
      if (body.length > 140) body = body.slice(0, 137) + '...';
      tx.innerHTML = '<div class="t">' + esc(title) + '</div>' +
        (body ? '<div class="b">' + esc(body) + '</div>' : '') +
        '<div class="w">' + esc(timeAgo(n.created_at)) + '</div>';
      a.appendChild(ic);
      a.appendChild(tx);
      a.addEventListener('click', function (e) {
        if (!n.is_read && n.id) {
          call('/notifications/' + encodeURIComponent(n.id) + '/read', { method: 'PATCH' })
            .then(function () { refreshBadge(); });
        }
        if (!url) e.preventDefault();
      });
      list.appendChild(a);
    });
  }

  async function loadList() {
    var list = document.getElementById('fnBellList');
    if (list) list.innerHTML = '<div class="fn-bell-empty">Loading…</div>';
    try {
      var res = await call('/notifications?limit=20');
      if (!res || res._unauth) {
        if (list) list.innerHTML = '<div class="fn-bell-empty">Sign in to see notifications.</div>';
        return;
      }
      var items = (res.data && res.data.notifications) || [];
      renderList(Array.isArray(items) ? items : []);
      setBadge(res.data.unread_count || 0);
    } catch (e) {
      if (list) list.innerHTML = '<div class="fn-bell-empty">Could not load notifications.</div>';
    }
  }

  function close() {
    opened = false;
    var panel = document.getElementById('fnBellPanel');
    if (panel) panel.classList.remove('open');
    var btn = document.getElementById('fnBellBtn');
    if (btn) btn.setAttribute('aria-expanded', 'false');
  }

  function open() {
    if (!token()) return;
    opened = true;
    var panel = document.getElementById('fnBellPanel');
    if (panel) panel.classList.add('open');
    var btn = document.getElementById('fnBellBtn');
    if (btn) btn.setAttribute('aria-expanded', 'true');
    loadList();
  }

  function boot() {
    var wrap = document.getElementById('fnBell');
    if (!wrap || wrap.dataset.booted === '1') return;
    wrap.dataset.booted = '1';
    if (!token()) return;
    wrap.style.display = '';
    var btn = document.getElementById('fnBellBtn');
    if (btn) {
      btn.addEventListener('click', function (e) {
        e.stopPropagation();
        if (opened) close();
        else open();
      });
    }
    var all = document.getElementById('fnBellReadAll');
    if (all) {
      all.addEventListener('click', function () {
        call('/notifications/read-all', { method: 'POST' }).then(function () {
          setBadge(0);
          loadList();
        });
      });
    }
    document.addEventListener('click', function (e) {
      if (opened && !e.target.closest('#fnBell')) close();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') close();
    });
    refreshBadge();
    if (timer) clearInterval(timer);
    timer = setInterval(function () {
      if (!document.hidden) refreshBadge();
    }, POLL_MS);
    document.addEventListener('visibilitychange', function () {
      if (!document.hidden) refreshBadge();
    });
    window.addEventListener('focus', function () { refreshBadge(); });
  }

  document.addEventListener('DOMContentLoaded', function () { boot(); });
  boot();

  return { boot: boot, open: open, close: close, refresh: refreshBadge };
})();
