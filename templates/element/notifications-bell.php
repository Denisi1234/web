<?php
/**
 * Notification bell — professional inbox icon with live unread badge and
 * dropdown panel. Rendered for signed-in visitors (desktop + mobile).
 * Data comes straight from the backend notification API via
 * notifications.js; the badge polls while the tab is visible.
 */
?>
<style>
.fn-bell{position:relative}
.fn-bell-btn{position:relative;display:flex;align-items:center;gap:6px;background:none;border:none;color:#334155;font-size:14px;font-weight:600;padding:4px;cursor:pointer;text-decoration:none}
.fn-bell-btn:hover{color:#0f62fe}
.fn-bell-btn .fa-bell{font-size:17px}
.fn-bell-badge{position:absolute;top:-4px;right:-6px;min-width:18px;height:18px;border-radius:9999px;background:#dc2626;color:#fff;font-size:10.5px;font-weight:800;display:flex;align-items:center;justify-content:center;padding:0 5px;border:2px solid #fff}
.fn-bell-panel{position:absolute;top:calc(100% + 10px);right:-8px;width:min(380px,calc(100vw - 32px));max-height:min(480px,70vh);background:#fff;border:1px solid #e5e7eb;border-radius:12px;box-shadow:0 12px 32px rgba(0,0,0,.14);z-index:1200;display:none;flex-direction:column;overflow:hidden}
.fn-bell-panel.open{display:flex}
.fn-bell-head{display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #f1f5f9}
.fn-bell-head b{font-size:14px;color:#0f172a}
.fn-bell-head button{background:none;border:none;color:#0f62fe;font-size:12.5px;font-weight:700;cursor:pointer}
.fn-bell-list{overflow-y:auto;flex:1}
.fn-bell-item{display:flex;gap:12px;padding:12px 16px;border-bottom:1px solid #f8fafc;text-decoration:none;color:inherit;cursor:pointer}
.fn-bell-item:hover{background:#f8fafc}
.fn-bell-item.is-unread{background:#eff6ff}
.fn-bell-ic{width:36px;height:36px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:14px;background:#f1f5f9;color:#0f62fe}
.fn-bell-item.is-unread .fn-bell-ic{background:#dbeafe}
.fn-bell-item .t{font-size:13px;font-weight:700;color:#0f172a;line-height:1.35}
.fn-bell-item .b{font-size:12.5px;color:#475569;line-height:1.45;margin-top:2px}
.fn-bell-item .w{font-size:11px;color:#94a3b8;margin-top:4px}
.fn-bell-empty{padding:28px 20px;text-align:center;color:#64748b;font-size:13px}
</style>
<div class="fn-bell" id="fnBell" style="display:none">
  <button type="button" class="fn-bell-btn" id="fnBellBtn" aria-haspopup="true" aria-expanded="false" aria-label="Notifications">
    <i class="fa-regular fa-bell"></i>
    <span class="d-none d-xl-inline">Notifications</span>
    <span class="fn-bell-badge" id="fnBellBadge" style="display:none">0</span>
  </button>
  <div class="fn-bell-panel" id="fnBellPanel" role="menu" aria-label="Notifications">
    <div class="fn-bell-head">
      <b>Notifications</b>
      <button type="button" id="fnBellReadAll">Mark all read</button>
    </div>
    <div class="fn-bell-list" id="fnBellList">
      <div class="fn-bell-empty">Loading…</div>
    </div>
  </div>
</div>
