<?php
/**
 * Shared real chat panel — guest inbox (/messages) and host inbox
 * (/host/messages). Everything rendered comes from the backend or the
 * booking/property in context: no canned messages, no reply bot, no fake
 * presence, avatars or phone numbers.
 *
 * Vars: $threads, $active[partner_id,partner_name,partner_role,lodge_name,
 * booking_code,booking,property,is_self], $initialMessages, $currentUserId,
 * $isHost, $backUrl, $sendEndpoint, $historyEndpoint, $threadBase,
 * $threadParam
 */
$threads = (isset($threads) && is_array($threads)) ? array_values(array_filter($threads, fn($t) => is_array($t) && !empty($t['partner_id']))) : [];
$active = (isset($active) && is_array($active)) ? $active : [];
$partnerId = (int)($active['partner_id'] ?? 0);
$partnerName = trim((string)($active['partner_name'] ?? ''));
$partnerRole = strtolower((string)($active['partner_role'] ?? ''));
$lodgeName = trim((string)($active['lodge_name'] ?? ''));
$bookingCode = trim((string)($active['booking_code'] ?? ''));
$booking = (isset($active['booking']) && is_array($active['booking'])) ? $active['booking'] : [];
$prop = (isset($active['property']) && is_array($active['property'])) ? $active['property'] : [];
$isSelf = !empty($active['is_self']);
$isHost = !empty($isHost);
$backUrl = (string)($backUrl ?? ($isHost ? '/host/dashboard' : '/my-booking'));
$sendEndpoint = (string)($sendEndpoint ?? ($isHost ? '/host/messages/send' : '/messages/send'));
$historyEndpoint = rtrim((string)($historyEndpoint ?? ($isHost ? '/host/messages/history' : '/messages/history')), '/') . '/';
$threadBase = (string)($threadBase ?? ($isHost ? '/host/messages' : '/messages'));
$threadParam = (string)($threadParam ?? ($isHost ? 'partner_id' : 'host_id'));
$initialMessages = (isset($initialMessages) && is_array($initialMessages)) ? array_values(array_filter($initialMessages, 'is_array')) : [];
$currentUserId = (int)($currentUserId ?? 0);

$initial = fn($s) => strtoupper(substr(trim((string)$s), 0, 1) ?: '?');
// Partner listed among threads?
$activeInList = false;
foreach ($threads as $t) {
    if ((int)$t['partner_id'] === $partnerId && $partnerId > 0) { $activeInList = true; break; }
}
// Trip bar only from a real booking — never invented dates/prices.
$trip = null;
if (!empty($booking)) {
    $ci = strtotime((string)($booking['check_in'] ?? ''));
    $co = strtotime((string)($booking['check_out'] ?? ''));
    if ($ci && $co && $co > $ci) {
        $trip = [
            'dates' => date('M j', $ci) . ' – ' . date('j, Y', $co),
            'nights' => (int)round(($co - $ci) / 86400),
            'room' => trim((string)($booking['room_number'] ?? ($booking['room'] ?? ''))),
            'status' => trim((string)($booking['status'] ?? 'Confirmed')),
            'total' => (float)($booking['total_price'] ?? 0),
            'guests' => trim((string)($booking['guests'] ?? ($booking['guest_count'] ?? ''))),
            'img' => trim((string)($booking['imageUrl'] ?? ($booking['room']['property']['image_url'] ?? ($prop['image_url'] ?? ($prop['primary_image_url'] ?? ''))))),
        ];
    }
}
$propAddress = trim((string)($prop['address'] ?? ''));
$threadUrl = fn($pid) => $this->Url->build($threadBase . '?' . $threadParam . '=' . (int)$pid);
?>
<style>
.cds-msg-page{background:#f4f5f7;min-height:calc(100vh - 80px);padding:16px 0 32px}
.cds-msg-layout{display:flex;gap:16px;max-width:1080px;margin:0 auto;height:calc(100vh - 120px);min-height:640px}
.cds-msg-sidebar{width:340px;background:#fff;border:1px solid #e5e7eb;border-radius:8px;display:flex;flex-direction:column;overflow:hidden}
.cds-msg-sidebar-header{padding:16px;border-bottom:1px solid #f0f0f0}
.cds-msg-search-box{display:flex;align-items:center;background:#f3f4f6;border-radius:20px;padding:6px 14px;gap:8px}
.cds-msg-search-box input{border:none;background:transparent;width:100%;font-size:13.5px;outline:none}
.cds-msg-filter-pills{display:flex;gap:8px;margin-top:12px}
.cds-msg-pill{padding:4px 12px;border-radius:100px;font-size:12px;font-weight:700;border:1px solid #e5e7eb;background:#fff;cursor:pointer;color:#525252}
.cds-msg-pill.is-active{background:#0f62fe;color:#fff;border-color:#0f62fe}
.cds-msg-threads-list{flex:1;overflow-y:auto}
.cds-msg-thread-item{display:flex;padding:14px 16px;border-bottom:1px solid #f5f5f5;gap:12px;cursor:pointer;transition:background .15s ease;text-decoration:none;color:inherit}
.cds-msg-thread-item:hover,.cds-msg-thread-item.is-active{background:#edf5ff}
.cds-chat-avatar{width:42px;height:42px;border-radius:50%;position:relative;flex-shrink:0;background:#0f62fe;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:16px}
.cds-unread-dot{width:10px;height:10px;background:#0f62fe;border-radius:50%;flex-shrink:0;margin-top:6px}
.cds-msg-chat-pane{flex:1;background:#fff;border:1px solid #e5e7eb;border-radius:8px;display:flex;flex-direction:column;overflow:hidden;position:relative}
.cds-chat-header{padding:12px 18px;background:#fff;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between;z-index:10}
.cds-chat-header-user{display:flex;align-items:center;gap:12px}
.cds-chat-header-title{font-size:15px;font-weight:800;color:#161616;line-height:1.2}
.cds-chat-header-sub{font-size:12px;font-weight:600;color:#6e6e6e}
.cds-trip-bar{background:#fff;border-bottom:1px solid #e5e7eb}
.cds-trip-bar-header{padding:10px 18px;display:flex;align-items:center;justify-content:space-between;cursor:pointer;user-select:none;font-size:13px;font-weight:700;color:#393939}
.cds-trip-bar-header:hover{background:#f9f9f9}
.cds-trip-drawer{padding:14px 18px;border-top:1px solid #f0f0f0;background:#fafafa}
.cds-trip-card{display:flex;gap:14px}
.cds-trip-thumb{width:68px;height:68px;border-radius:6px;object-fit:cover;flex-shrink:0}
.cds-trip-actions{display:flex;gap:8px;margin-top:12px}
.cds-safety-banner{background:#fffbeb;border-bottom:1px solid #fef3c7;padding:8px 18px;display:flex;align-items:center;gap:10px;font-size:11.5px;color:#92400e}
.cds-chat-messages{flex:1;overflow-y:auto;padding:18px;background:#f9fafb;display:flex;flex-direction:column;gap:10px}
.cds-date-pill{align-self:center;background:#e5e7eb;color:#4b5563;padding:2px 10px;border-radius:100px;font-size:11px;font-weight:600;margin:6px 0}
.cds-msg-row{display:flex;margin-bottom:2px}
.cds-msg-row.is-guest{justify-content:flex-end}
.cds-msg-row.is-host{justify-content:flex-start}
.cds-bubble{max-width:75%;padding:10px 14px;border-radius:14px;font-size:13.5px;line-height:1.45;position:relative;word-break:break-word}
.cds-bubble.is-guest{background:linear-gradient(135deg,#0f62fe 0%,#0050e6 100%);color:#fff;border-bottom-right-radius:2px;box-shadow:0 1px 2px rgba(0,0,0,.08)}
.cds-bubble.is-host{background:#fff;color:#161616;border:1px solid #e5e7eb;border-bottom-left-radius:2px;box-shadow:0 1px 2px rgba(0,0,0,.03)}
.cds-bubble-meta{display:flex;align-items:center;justify-content:flex-end;gap:4px;margin-top:4px;font-size:10px;opacity:.85}
.cds-quick-replies{padding:8px 16px;background:#fff;border-top:1px solid #f0f0f0;display:flex;gap:8px;overflow-x:auto;scrollbar-width:none}
.cds-quick-chip{padding:5px 12px;background:#fff;border:1px solid #e5e7eb;border-radius:100px;font-size:12px;font-weight:700;color:#0f62fe;white-space:nowrap;cursor:pointer;transition:all .15s ease}
.cds-quick-chip:hover{background:#0f62fe;color:#fff;border-color:#0f62fe}
.cds-chat-input-bar{padding:12px 16px;background:#fff;border-top:1px solid #e5e7eb;display:flex;align-items:center;gap:10px}
.cds-chat-input-field{flex:1;background:#f3f4f6;border:1px solid transparent;border-radius:24px;padding:10px 16px;font-size:13.5px;outline:none;transition:border-color .15s ease}
.cds-chat-input-field:focus{border-color:#0f62fe;background:#fff}
.cds-chat-send-btn{width:40px;height:40px;border-radius:50%;background:#0f62fe;color:#fff;border:none;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:background .15s ease;flex-shrink:0}
.cds-chat-send-btn:hover{background:#0050e6}
.cds-chat-send-btn:disabled{opacity:.6;cursor:wait}
.cds-chat-error{display:none;margin:0 16px 8px;background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;padding:8px 12px;border-radius:8px;font-size:12.5px}
.cds-modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:3000;align-items:center;justify-content:center;padding:16px}
.cds-modal-box{background:#fff;border-radius:12px;padding:20px;width:100%}
.cds-modal-title{font-size:16px;font-weight:800;color:#161616}
.cds-empty{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;padding:40px 20px;text-align:center;color:#6b7280;font-size:14px}
@media (max-width:768px){
  .cds-msg-sidebar{display:none}
  .cds-msg-layout{height:calc(100vh - 70px);margin:0;border-radius:0}
  .cds-msg-chat-pane{border-radius:0;border:none}
}
</style>

<main class="cds-msg-page" role="main">
  <div class="container" style="max-width:1100px;padding:0 12px">
    <div class="cds-msg-layout">

      <aside class="cds-msg-sidebar">
        <div class="cds-msg-sidebar-header">
          <div style="font-size:18px;font-weight:800;color:#161616;margin-bottom:12px">Inbox</div>
          <div class="cds-msg-search-box">
            <i class="fa-solid fa-magnifying-glass" style="color:#9ca3af"></i>
            <input type="text" id="threadSearch" placeholder="Search messages...">
          </div>
          <div class="cds-msg-filter-pills">
            <button type="button" class="cds-msg-pill is-active" onclick="filterThreads('all', this)">All</button>
            <button type="button" class="cds-msg-pill" onclick="filterThreads('unread', this)">Unread</button>
          </div>
        </div>
        <div class="cds-msg-threads-list" id="threadsList">
          <?php if ($partnerId > 0 && !$activeInList && !$isSelf): ?>
          <a href="<?= h($threadUrl($partnerId)) ?>" class="cds-msg-thread-item is-active">
            <div class="cds-chat-avatar"><?= h($initial($partnerName)) ?></div>
            <div style="flex:1;min-width:0">
              <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2px">
                <span style="font-size:13.5px;font-weight:700;color:#161616;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= h($partnerName !== '' ? $partnerName : 'Conversation') ?></span>
              </div>
              <?php if ($lodgeName !== ''): ?><div style="font-size:12px;font-weight:600;color:#0f62fe;margin-bottom:2px"><?= h($lodgeName) ?></div><?php endif; ?>
              <div style="font-size:12px;color:#6b7280;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" id="sidebarLastMsg">New conversation</div>
            </div>
          </a>
          <?php endif; ?>
          <?php foreach ($threads as $t):
            $tp = (int)$t['partner_id'];
            $tActive = $tp === $partnerId;
            $tName = trim((string)($t['partner_name'] ?? 'Conversation'));
            $tLodge = trim((string)($t['lodge_name'] ?? ''));
            $tLast = trim((string)($t['last_message'] ?? ''));
            $tTime = trim((string)($t['time'] ?? ''));
            $tUnread = !empty($t['unread']);
          ?>
          <a href="<?= h($threadUrl($tp)) ?>" class="cds-msg-thread-item <?= $tActive ? 'is-active' : '' ?>" data-unread="<?= $tUnread ? '1' : '0' ?>" data-text="<?= h(strtolower($tName . ' ' . $tLodge . ' ' . $tLast)) ?>">
            <div class="cds-chat-avatar"><?= h($initial($tName)) ?></div>
            <div style="flex:1;min-width:0">
              <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2px">
                <span style="font-size:13.5px;font-weight:700;color:#161616;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= h($tName) ?></span>
                <?php if ($tTime !== ''): ?><span style="font-size:11px;color:#9ca3af"><?= h($tTime) ?></span><?php endif; ?>
              </div>
              <?php if ($tLodge !== ''): ?><div style="font-size:12px;font-weight:600;color:#0f62fe;margin-bottom:2px"><?= h($tLodge) ?></div><?php endif; ?>
              <div style="font-size:12px;color:#6b7280;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= h($tLast !== '' ? $tLast : '—') ?></div>
            </div>
            <?php if ($tUnread && !$tActive): ?><span class="cds-unread-dot"></span><?php endif; ?>
          </a>
          <?php endforeach; ?>
          <?php if (empty($threads) && ($partnerId <= 0 || $isSelf)): ?>
          <div class="cds-empty">
            <i class="fa-regular fa-comments" style="font-size:28px;color:#c6c6c6"></i>
            <div><strong>No conversations yet</strong></div>
            <div style="font-size:13px"><?= $isHost ? 'Messages from guests with bookings at your properties will appear here.' : 'After booking a stay you can message your host from the confirmation page.' ?></div>
          </div>
          <?php endif; ?>
        </div>
      </aside>

      <section class="cds-msg-chat-pane">
        <?php if ($partnerId <= 0 || $isSelf): ?>
        <div class="cds-empty">
          <i class="fa-regular fa-comments" style="font-size:32px;color:#c6c6c6"></i>
          <div><strong><?= $isSelf ? 'You cannot message yourself' : 'Select a conversation' ?></strong></div>
          <div style="font-size:13px"><?= $isSelf ? 'Hosts and guests each need their own sign-in — switch accounts to chat.' : 'Choose a thread on the left to start messaging.' ?></div>
        </div>
        <?php else: ?>
        <div class="cds-chat-header">
          <div class="cds-chat-header-user">
            <a href="<?= h($this->Url->build($backUrl)) ?>" style="color:#161616;font-size:16px;text-decoration:none;margin-right:4px" aria-label="Back">
              <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div class="cds-chat-avatar"><?= h($initial($partnerName)) ?></div>
            <div>
              <div class="cds-chat-header-title"><?= h($partnerName !== '' ? $partnerName : 'Conversation') ?></div>
              <div class="cds-chat-header-sub"><?= h($lodgeName !== '' ? $lodgeName : ucfirst($partnerRole !== '' ? $partnerRole : 'chat')) ?></div>
            </div>
          </div>
          <div style="display:flex;gap:10px">
            <button type="button" class="cds-btn cds-btn-ghost" style="height:36px;padding:0 12px;font-size:12px" onclick="pollMessages(true)" title="Refresh chat">
              <i class="fa-solid fa-arrows-rotate"></i>
            </button>
          </div>
        </div>

        <?php if ($trip !== null): ?>
        <div class="cds-trip-bar">
          <div class="cds-trip-bar-header" onclick="toggleTripDrawer()">
            <div style="display:flex;align-items:center;gap:8px">
              <i class="fa-regular fa-calendar-check" style="color:#0f62fe"></i>
              <span><?= h($trip['dates']) ?><?php if ($trip['room'] !== ''): ?> • Room <?= h($trip['room']) ?><?php endif; ?> • <?= h($trip['status']) ?></span>
            </div>
            <i class="fa-solid fa-chevron-down" id="tripDrawerChevron" style="transition:transform .2s ease"></i>
          </div>
          <div class="cds-trip-drawer" id="tripDrawer" style="display:none">
            <div class="cds-trip-card">
              <?php if ($trip['img'] !== ''): ?><img src="<?= h($trip['img']) ?>" class="cds-trip-thumb" alt="<?= h($lodgeName) ?>"><?php endif; ?>
              <div style="flex:1">
                <div style="font-size:14px;font-weight:800;color:#161616"><?= h($lodgeName !== '' ? $lodgeName : 'Stay') ?></div>
                <?php if ($bookingCode !== ''): ?><div style="font-size:11.5px;color:#6b7280;margin:2px 0">Reservation Code: <strong><?= h($bookingCode) ?></strong></div><?php endif; ?>
                <div style="font-size:11.5px;color:#6b7280"><?= (int)$trip['nights'] ?> Night<?= ((int)$trip['nights'] !== 1) ? 's' : '' ?><?php if ($trip['guests'] !== ''): ?> • <?= h($trip['guests']) ?> Guests<?php endif; ?><?php if ($trip['total'] > 0): ?> • TSh <?= number_format($trip['total']) ?> Total<?php endif; ?></div>
              </div>
            </div>
            <?php if ($bookingCode !== ''): ?>
            <div class="cds-trip-actions">
              <?php if ($propAddress !== ''): ?>
              <button type="button" class="cds-btn cds-btn-ghost" style="flex:1;height:34px;font-size:12px" onclick="openDirectionsModal()">
                <i class="fa-solid fa-map-location-dot" style="color:#0f62fe"></i>
                <span>Directions & Map</span>
              </button>
              <?php endif; ?>
              <button type="button" class="cds-btn cds-btn-primary" style="flex:1;height:34px;font-size:12px" onclick="shareVoucher('<?= h($bookingCode) ?>')">
                <i class="fa-solid fa-receipt"></i>
                <span>Share Voucher</span>
              </button>
            </div>
            <?php endif; ?>
          </div>
        </div>
        <?php endif; ?>

        <div class="cds-safety-banner" id="safetyBanner">
          <i class="fa-solid fa-shield-halved" style="font-size:14px"></i>
          <span style="flex:1">Keep payments inside FastNet. Never wire money directly to anyone.</span>
          <button type="button" onclick="document.getElementById('safetyBanner').style.display='none'" style="background:none;border:none;color:#92400e;cursor:pointer;font-size:13px">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <div class="cds-chat-messages" id="chatFeed"></div>

        <div class="cds-quick-replies">
          <button type="button" class="cds-quick-chip" onclick="sendQuickReply('What is the Wi-Fi passcode?')"><i class="fa-solid fa-wifi" style="margin-right:4px"></i> Wi-Fi Passcode</button>
          <button type="button" class="cds-quick-chip" onclick="sendQuickReply('Do you offer early check-in?')"><i class="fa-solid fa-key" style="margin-right:4px"></i> Early Check-in</button>
          <button type="button" class="cds-quick-chip" onclick="sendQuickReply('Is breakfast included in the booking?')"><i class="fa-solid fa-utensils" style="margin-right:4px"></i> Breakfast Options</button>
          <button type="button" class="cds-quick-chip" onclick="sendQuickReply('Is secure parking available?')"><i class="fa-solid fa-square-parking" style="margin-right:4px"></i> Parking Info</button>
          <button type="button" class="cds-quick-chip" onclick="sendQuickReply('Can you provide exact driving directions?')"><i class="fa-solid fa-map-location-dot" style="margin-right:4px"></i> Directions</button>
        </div>

        <div class="cds-chat-error" id="chatError" role="alert"></div>
        <form class="cds-chat-input-bar" id="chatForm">
          <input type="text" id="chatInput" class="cds-chat-input-field" placeholder="Type a message..." autocomplete="off" maxlength="2000">
          <button type="submit" class="cds-chat-send-btn" id="chatSendBtn" title="Send message">
            <i class="fa-solid fa-paper-plane" style="font-size:15px"></i>
          </button>
        </form>
        <?php endif; ?>
      </section>

    </div>
  </div>
</main>

<?php if ($propAddress !== '' && $partnerId > 0 && !$isSelf): ?>
<div id="directionsModal" class="cds-modal-overlay" role="dialog" aria-modal="true">
  <div class="cds-modal-box" style="max-width:440px;background:#fff;border-radius:12px;padding:20px;width:100%">
    <h3 class="cds-modal-title">Directions & Location</h3>
    <p style="font-size:13.5px;color:#374151;line-height:1.5;margin:12px 0 14px">
      Directions to <strong><?= h($lodgeName !== '' ? $lodgeName : 'the property') ?></strong>:<br>
      <?= h($propAddress) ?>
    </p>
    <div style="text-align:right">
      <button type="button" class="cds-btn cds-btn-primary" onclick="closeDirectionsModal()">Close</button>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
(function () {
  'use strict';
  var partnerId = <?= (int)$partnerId ?>;
  var currentUserId = <?= (int)$currentUserId ?>;
  var lodgeName = <?= json_encode($lodgeName !== '' ? $lodgeName : 'Property chat') ?>;
  var bookingCode = <?= json_encode($bookingCode) ?>;
  var sendEndpoint = <?= json_encode($sendEndpoint) ?>;
  var historyEndpoint = <?= json_encode($historyEndpoint) ?>;
  var initialMessages = <?= json_encode($initialMessages, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
  var seenIds = {};
  var pollTimer = null;

  function escapeHtml(s) {
    var d = document.createElement('div');
    d.textContent = (s === null || s === undefined) ? '' : String(s);
    return d.innerHTML;
  }

  function fmtTime(iso) {
    if (!iso) return '';
    var d = new Date(iso);
    if (isNaN(d.getTime())) return '';
    return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  }

  function fmtDay(iso) {
    if (!iso) return '';
    var d = new Date(iso);
    if (isNaN(d.getTime())) return '';
    var today = new Date();
    var sameDay = d.toDateString() === today.toDateString();
    if (sameDay) return 'Today';
    var y = new Date(today);
    y.setDate(y.getDate() - 1);
    if (d.toDateString() === y.toDateString()) return 'Yesterday';
    return d.toLocaleDateString([], { day: 'numeric', month: 'short', year: 'numeric' });
  }

  function showError(msg, loginUrl) {
    var box = document.getElementById('chatError');
    if (!box) return;
    box.innerHTML = '';
    box.appendChild(document.createTextNode(msg + ' '));
    if (loginUrl) {
      var a = document.createElement('a');
      a.href = loginUrl;
      a.textContent = 'Sign in again';
      a.style.fontWeight = '700';
      box.appendChild(a);
    }
    box.style.display = 'block';
  }

  function hideError() {
    var box = document.getElementById('chatError');
    if (box) {
      box.style.display = 'none';
      box.innerHTML = '';
    }
  }

  function renderMessage(msg) {
    var feed = document.getElementById('chatFeed');
    if (!feed || !msg || typeof msg !== 'object') return;
    var id = msg.id !== undefined && msg.id !== null ? String(msg.id) : '';
    if (id !== '' && seenIds[id]) return;
    if (id !== '') seenIds[id] = true;
    var mine = currentUserId > 0 && Number(msg.sender_id) === currentUserId;
    var row = document.createElement('div');
    row.className = 'cds-msg-row ' + (mine ? 'is-guest' : 'is-host');
    var check = mine ? '<i class="fa-solid fa-check-double" style="font-size:10px;margin-left:4px;color:#93c5fd"></i>' : '';
    row.innerHTML = '<div class="cds-bubble ' + (mine ? 'is-guest' : 'is-host') + '">' +
      escapeHtml(msg.text || '') +
      '<div class="cds-bubble-meta"><span>' + escapeHtml(fmtTime(msg.created_at) || 'Just now') + '</span>' + check + '</div></div>';
    feed.appendChild(row);
  }

  function renderAll(list) {
    var feed = document.getElementById('chatFeed');
    if (!feed) return;
    feed.innerHTML = '';
    seenIds = {};
    var lastDay = '';
    (Array.isArray(list) ? list : []).forEach(function (msg) {
      var day = fmtDay(msg.created_at);
      if (day !== '' && day !== lastDay) {
        lastDay = day;
        var pill = document.createElement('div');
        pill.className = 'cds-date-pill';
        pill.textContent = day;
        feed.appendChild(pill);
      }
      renderMessage(msg);
    });
    if (!feed.children.length) {
      feed.innerHTML = '<div class="cds-date-pill">No messages yet — say hello below.</div>';
    }
    scrollToBottom();
  }

  function scrollToBottom() {
    var feed = document.getElementById('chatFeed');
    if (feed) feed.scrollTop = feed.scrollHeight;
  }

  function bumpSidebar(text) {
    var prev = document.getElementById('sidebarLastMsg');
    if (prev) prev.textContent = text;
  }

  window.toggleTripDrawer = function () {
    var drawer = document.getElementById('tripDrawer');
    var chev = document.getElementById('tripDrawerChevron');
    if (!drawer) return;
    var open = drawer.style.display === 'none';
    drawer.style.display = open ? 'block' : 'none';
    if (chev) chev.style.transform = open ? 'rotate(180deg)' : 'rotate(0deg)';
  };

  window.openDirectionsModal = function () {
    var m = document.getElementById('directionsModal');
    if (m) m.style.display = 'flex';
  };
  window.closeDirectionsModal = function () {
    var m = document.getElementById('directionsModal');
    if (m) m.style.display = 'none';
  };

  window.filterThreads = function (mode, btn) {
    document.querySelectorAll('.cds-msg-pill').forEach(function (p) { p.classList.remove('is-active'); });
    if (btn) btn.classList.add('is-active');
    var q = (document.getElementById('threadSearch').value || '').toLowerCase();
    applyThreadFilter(q, mode);
  };

  function applyThreadFilter(q, mode) {
    var pills = document.querySelectorAll('.cds-msg-pill');
    var activeMode = 'all';
    pills.forEach(function (p) {
      if (p.classList.contains('is-active')) activeMode = p.textContent.trim().toLowerCase() === 'unread' ? 'unread' : 'all';
    });
    if (mode) activeMode = mode;
    document.querySelectorAll('#threadsList .cds-msg-thread-item').forEach(function (el) {
      var text = (el.getAttribute('data-text') || '').toLowerCase();
      var unread = el.getAttribute('data-unread') === '1';
      var ok = (q === '' || text.indexOf(q) !== -1) && (activeMode !== 'unread' || unread);
      el.style.display = ok ? '' : 'none';
    });
  }

  window.pollMessages = function (manual) {
    if (!partnerId) return Promise.resolve();
    return fetch(historyEndpoint + partnerId, { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
      .then(function (r) { return r.json().catch(function () { return {}; }); })
      .then(function (data) {
        if (data && Array.isArray(data.messages)) {
          if (typeof data.user_id !== 'undefined' && data.user_id) currentUserId = Number(data.user_id) || currentUserId;
          var fresh = false;
          data.messages.forEach(function (msg) {
            var id = msg && msg.id !== undefined && msg.id !== null ? String(msg.id) : '';
            if (id !== '' && !seenIds[id]) fresh = true;
            renderMessage(msg);
          });
          if (fresh) {
            var last = data.messages[data.messages.length - 1];
            if (last && last.text) bumpSidebar(String(last.text).slice(0, 80));
          }
          scrollToBottom();
        }
      })
      .catch(function () {});
  };

  window.sendQuickReply = function (text) {
    submitChatMessage(text);
  };

  window.shareVoucher = function (code) {
    if (!code) return;
    submitChatMessage('Booking voucher: ' + code);
  };

  function submitChatMessage(text) {
    var input = document.getElementById('chatInput');
    var btn = document.getElementById('chatSendBtn');
    var body = (typeof text === 'string' ? text : (input ? input.value : '')).trim();
    if (!body) return;
    if (!partnerId) {
      showError('Choose a conversation first.');
      return;
    }
    hideError();
    if (input) input.value = '';
    if (btn) btn.disabled = true;
    fetch(sendEndpoint, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ recipient_id: partnerId, lodge_name: lodgeName, text: body.slice(0, 2000) })
    })
      .then(function (r) {
        return r.json().catch(function () { return {}; }).then(function (data) {
          return { status: r.status, data: data };
        });
      })
      .then(function (res) {
        if (btn) btn.disabled = false;
        var data = res.data || {};
        if ((res.status === 200 || res.status === 201) && (data.status === 'success' || data.data)) {
          var echo = (data.data && typeof data.data === 'object') ? data.data : {};
          renderMessage({
            id: echo.id !== undefined ? echo.id : 'local-' + Date.now(),
            sender_id: currentUserId,
            text: echo.text || body,
            created_at: echo.created_at || new Date().toISOString()
          });
          bumpSidebar(body.slice(0, 80));
          scrollToBottom();
          pollMessages();
          return;
        }
        if (res.status === 401 && data.loginUrl) {
          window.location.href = data.loginUrl;
          return;
        }
        showError(data.message || data.error || 'Could not send your message. Please try again.', data.loginUrl);
      })
      .catch(function () {
        if (btn) btn.disabled = false;
        showError('Could not reach the server. Please try again.');
      });
  }
  window.submitChatMessage = submitChatMessage;

  document.addEventListener('DOMContentLoaded', function () {
    renderAll(initialMessages);
    var search = document.getElementById('threadSearch');
    if (search) search.addEventListener('input', function () { applyThreadFilter(search.value.toLowerCase()); });
    var form = document.getElementById('chatForm');
    if (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        submitChatMessage();
      });
    }
    pollMessages();
    pollTimer = setInterval(function () { pollMessages(); }, 5000);
  });
})();
</script>
