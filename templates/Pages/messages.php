<?php
$this->assign('title', 'Messages - FastNet Stays');
$hostId = (int)($activeThread['hostId'] ?? 1);
$hostName = (string)($activeThread['hostName'] ?? 'Property Host');
$lodgeName = (string)($activeThread['lodgeName'] ?? 'Kingdoms Lodge');
$bookingCode = (string)($activeThread['bookingCode'] ?? '');
$booking = $activeThread['booking'] ?? [];
$prop = $activeThread['property'] ?? [];

$datesLine = 'Oct 13 – 14, 2026';
$roomNum = '101';
$statusText = 'Upcoming stay';
$nights = 1;
$priceFormatted = 'TSh 171,700';
$propImg = '/assets/images/house3.webp';

if (!empty($booking) && is_array($booking)) {
    $ci = strtotime((string)($booking['check_in'] ?? ''));
    $co = strtotime((string)($booking['check_out'] ?? ''));
    if ($ci && $co) {
        $datesLine = date('M j', $ci) . ' – ' . date('j, Y', $co);
        if ($co > $ci) $nights = (int)round(($co - $ci) / 86400);
    }
    $price = (float)($booking['total_price'] ?? ($booking['amount'] ?? 171700));
    $priceFormatted = 'TSh ' . number_format($price);
    $rawSt = strtolower((string)($booking['status'] ?? ($booking['booking_status'] ?? 'confirmed')));
    if (in_array($rawSt, ['checked in', 'checked-in', 'checked_in'], true)) $statusText = 'Active stay';
    elseif ($rawSt === 'completed') $statusText = 'Past stay';
    elseif (in_array($rawSt, ['cancelled', 'canceled'], true)) $statusText = 'Cancelled stay';
    else $statusText = 'Upcoming stay';
    
    if (!empty($booking['imageUrl'])) $propImg = (string)$booking['imageUrl'];
    elseif (!empty($booking['room']['property']['image_url'])) $propImg = (string)$booking['room']['property']['image_url'];
} elseif (!empty($prop) && is_array($prop)) {
    if (!empty($prop['image_url'])) $propImg = (string)$prop['image_url'];
    elseif (!empty($prop['primary_image_url'])) $propImg = (string)$prop['primary_image_url'];
}
?>

<?= $this->element('navbar') ?>
<?= $this->Html->css('/assets/css/my-booking.css') ?>

<style>
/* Exact Mobile Messages & Property Chat Styles */
.cds-msg-page {
  background: #f4f5f7;
  min-height: calc(100vh - 80px);
  padding: 16px 0 32px;
}
.cds-msg-layout {
  display: flex;
  gap: 16px;
  max-width: 1080px;
  margin: 0 auto;
  height: calc(100vh - 120px);
  min-height: 640px;
}

/* Sidebar: Threads list */
.cds-msg-sidebar {
  width: 340px;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}
.cds-msg-sidebar-header {
  padding: 16px;
  border-bottom: 1px solid #f0f0f0;
}
.cds-msg-search-box {
  display: flex;
  align-items: center;
  background: #f3f4f6;
  border-radius: 20px;
  padding: 6px 14px;
  gap: 8px;
}
.cds-msg-search-box input {
  border: none;
  background: transparent;
  width: 100%;
  font-size: 13.5px;
  outline: none;
}
.cds-msg-filter-pills {
  display: flex;
  gap: 8px;
  margin-top: 12px;
}
.cds-msg-pill {
  padding: 4px 12px;
  border-radius: 100px;
  font-size: 12px;
  font-weight: 700;
  border: 1px solid #e5e7eb;
  background: #fff;
  cursor: pointer;
  color: #525252;
}
.cds-msg-pill.is-active {
  background: #800000;
  color: #fff;
  border-color: #800000;
}
.cds-msg-threads-list {
  flex: 1;
  overflow-y: auto;
}
.cds-msg-thread-item {
  display: flex;
  padding: 14px 16px;
  border-bottom: 1px solid #f5f5f5;
  gap: 12px;
  cursor: pointer;
  transition: background .15s ease;
  text-decoration: none;
  color: inherit;
}
.cds-msg-thread-item:hover, .cds-msg-thread-item.is-active {
  background: #fbf0f0;
}

/* Main Chat Container */
.cds-msg-chat-pane {
  flex: 1;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  position: relative;
}

/* Chat Header */
.cds-chat-header {
  padding: 12px 18px;
  background: #ffffff;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  align-items: center;
  justify-content: space-between;
  z-index: 10;
}
.cds-chat-header-user {
  display: flex;
  align-items: center;
  gap: 12px;
}
.cds-chat-avatar {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  position: relative;
  flex-shrink: 0;
}
.cds-chat-avatar img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
}
.cds-chat-online-dot {
  width: 11px;
  height: 11px;
  background: #10b981;
  border: 2px solid #fff;
  border-radius: 50%;
  position: absolute;
  bottom: 0;
  right: 0;
}
.cds-chat-header-title {
  font-size: 15px;
  font-weight: 800;
  color: #161616;
  line-height: 1.2;
}
.cds-chat-header-sub {
  font-size: 12px;
  font-weight: 600;
  color: #6e6e6e;
}

/* Collapsible Trip Bar */
.cds-trip-bar {
  background: #ffffff;
  border-bottom: 1px solid #e5e7eb;
}
.cds-trip-bar-header {
  padding: 10px 18px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  cursor: pointer;
  user-select: none;
  font-size: 13px;
  font-weight: 700;
  color: #393939;
}
.cds-trip-bar-header:hover {
  background: #f9f9f9;
}
.cds-trip-drawer {
  padding: 14px 18px;
  border-top: 1px solid #f0f0f0;
  background: #fafafa;
}
.cds-trip-card {
  display: flex;
  gap: 14px;
}
.cds-trip-thumb {
  width: 68px;
  height: 68px;
  border-radius: 6px;
  object-fit: cover;
  flex-shrink: 0;
}
.cds-trip-actions {
  display: flex;
  gap: 8px;
  margin-top: 12px;
}

/* Safety Warning */
.cds-safety-banner {
  background: #fffbeb;
  border-bottom: 1px solid #fef3c7;
  padding: 8px 18px;
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 11.5px;
  color: #92400e;
}

/* Messages Feed */
.cds-chat-messages {
  flex: 1;
  overflow-y: auto;
  padding: 18px;
  background: #f9fafb;
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.cds-date-pill {
  align-self: center;
  background: #e5e7eb;
  color: #4b5563;
  padding: 2px 10px;
  border-radius: 100px;
  font-size: 11px;
  font-weight: 600;
  margin: 6px 0;
}

/* Bubbles */
.cds-msg-row {
  display: flex;
  margin-bottom: 2px;
}
.cds-msg-row.is-guest {
  justify-content: flex-end;
}
.cds-msg-row.is-host {
  justify-content: flex-start;
}
.cds-bubble {
  max-width: 75%;
  padding: 10px 14px;
  border-radius: 14px;
  font-size: 13.5px;
  line-height: 1.45;
  position: relative;
  word-break: break-word;
}
.cds-bubble.is-guest {
  background: linear-gradient(135deg, #800000 0%, #a91d22 100%);
  color: #ffffff;
  border-bottom-right-radius: 2px;
  box-shadow: 0 1px 2px rgba(0,0,0,0.08);
}
.cds-bubble.is-host {
  background: #ffffff;
  color: #161616;
  border: 1px solid #e5e7eb;
  border-bottom-left-radius: 2px;
  box-shadow: 0 1px 2px rgba(0,0,0,0.03);
}
.cds-bubble-meta {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 4px;
  margin-top: 4px;
  font-size: 10px;
  opacity: 0.85;
}

/* Typing Indicator */
.cds-typing {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #fff;
  border: 1px solid #e5e7eb;
  padding: 8px 12px;
  border-radius: 14px;
  border-bottom-left-radius: 2px;
  width: fit-content;
  font-size: 12px;
  color: #6b7280;
}
.cds-typing-dots {
  display: flex;
  gap: 3px;
}
.cds-typing-dots span {
  width: 5px;
  height: 5px;
  background: #800000;
  border-radius: 50%;
  animation: typingBounce 1.2s infinite ease-in-out;
}
.cds-typing-dots span:nth-child(2) { animation-delay: 0.2s; }
.cds-typing-dots span:nth-child(3) { animation-delay: 0.4s; }
@keyframes typingBounce {
  0%, 80%, 100% { transform: translateY(0); }
  40% { transform: translateY(-5px); }
}

/* Quick Replies Carousel */
.cds-quick-replies {
  padding: 8px 16px;
  background: #ffffff;
  border-top: 1px solid #f0f0f0;
  display: flex;
  gap: 8px;
  overflow-x: auto;
  scrollbar-width: none;
}
.cds-quick-chip {
  padding: 5px 12px;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 100px;
  font-size: 12px;
  font-weight: 700;
  color: #800000;
  white-space: nowrap;
  cursor: pointer;
  transition: all .15s ease;
}
.cds-quick-chip:hover {
  background: #800000;
  color: #ffffff;
  border-color: #800000;
}

/* Chat Input Bar */
.cds-chat-input-bar {
  padding: 12px 16px;
  background: #ffffff;
  border-top: 1px solid #e5e7eb;
  display: flex;
  align-items: center;
  gap: 10px;
}
.cds-chat-attach-btn {
  background: transparent;
  border: none;
  color: #800000;
  font-size: 20px;
  cursor: pointer;
  padding: 4px;
}
.cds-chat-input-field {
  flex: 1;
  background: #f3f4f6;
  border: 1px solid transparent;
  border-radius: 24px;
  padding: 10px 16px;
  font-size: 13.5px;
  outline: none;
  transition: border-color .15s ease;
}
.cds-chat-input-field:focus {
  border-color: #800000;
  background: #ffffff;
}
.cds-chat-send-btn {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #800000;
  color: #ffffff;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background .15s ease;
  flex-shrink: 0;
}
.cds-chat-send-btn:hover {
  background: #600000;
}

/* Attachment Modal */
.cds-attach-options {
  display: flex;
  justify-content: space-around;
  padding: 16px 0 8px;
}
.cds-attach-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}
.cds-attach-icon-circle {
  width: 52px;
  height: 52px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 22px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .cds-msg-sidebar {
    display: none;
  }
  .cds-msg-layout {
    height: calc(100vh - 70px);
    margin: 0;
    border-radius: 0;
  }
  .cds-msg-chat-pane {
    border-radius: 0;
    border: none;
  }
}
</style>

<main class="cds-msg-page" role="main">
  <div class="container" style="max-width:1100px;padding:0 12px">
    
    <div class="cds-msg-layout">
      
      <!-- 1. Left Sidebar: Message Threads -->
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
          <!-- Active thread -->
          <a href="<?= $this->Url->build(['action' => 'messages', '?' => ['host_id' => $hostId, 'lodge_name' => $lodgeName, 'booking_code' => $bookingCode]]) ?>" class="cds-msg-thread-item is-active">
            <div class="cds-chat-avatar">
              <img src="/assets/images/man2.jpeg" alt="<?= h($hostName) ?>">
              <span class="cds-chat-online-dot"></span>
            </div>
            <div style="flex:1;min-width:0">
              <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2px">
                <span style="font-size:13.5px;font-weight:700;color:#161616;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= h($hostName) ?></span>
                <span style="font-size:11px;color:#9ca3af">Just now</span>
              </div>
              <div style="font-size:12px;font-weight:600;color:#800000;margin-bottom:2px"><?= h($lodgeName) ?></div>
              <div style="font-size:12px;color:#6b7280;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" id="sidebarLastMsg">Karibu! How can we make your stay comfortable?</div>
            </div>
          </a>
        </div>
      </aside>

      <!-- 2. Right Pane: Exact Mobile Guest Chat Detail -->
      <section class="cds-msg-chat-pane">
        
        <!-- Chat Header -->
        <div class="cds-chat-header">
          <div class="cds-chat-header-user">
            <a href="<?= $this->Url->build('/my-booking') ?>" style="color:#161616;font-size:16px;text-decoration:none;margin-right:4px" aria-label="Back to bookings">
              <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div class="cds-chat-avatar">
              <img src="/assets/images/man2.jpeg" alt="<?= h($hostName) ?>">
              <span class="cds-chat-online-dot"></span>
            </div>
            <div>
              <div class="cds-chat-header-title"><?= h($hostName) ?></div>
              <div class="cds-chat-header-sub"><?= h($lodgeName) ?></div>
            </div>
          </div>
          <div style="display:flex;gap:10px">
            <button type="button" class="cds-btn cds-btn-ghost" style="height:36px;padding:0 12px;font-size:12px" onclick="pollMessages(true)" title="Refresh chat">
              <i class="fa-solid fa-arrows-rotate"></i>
            </button>
            <a href="tel:+255754000000" class="cds-btn cds-btn-ghost" style="height:36px;padding:0 12px;font-size:12px" title="Call property">
              <i class="fa-solid fa-phone"></i>
            </a>
          </div>
        </div>

        <!-- Collapsible Trip Summary Bar -->
        <div class="cds-trip-bar">
          <div class="cds-trip-bar-header" onclick="toggleTripDrawer()">
            <div style="display:flex;align-items:center;gap:8px">
              <i class="fa-regular fa-calendar-check" style="color:#800000"></i>
              <span>🗓️ <?= h($datesLine) ?>  •  🔑 Room <?= h($roomNum) ?>  •  <?= h($statusText) ?></span>
            </div>
            <i class="fa-solid fa-chevron-down" id="tripDrawerChevron" style="transition:transform .2s ease"></i>
          </div>
          <div class="cds-trip-drawer" id="tripDrawer" style="display:none">
            <div class="cds-trip-card">
              <img src="<?= h($propImg) ?>" class="cds-trip-thumb" alt="<?= h($lodgeName) ?>" onerror="this.onerror=null;this.src='/assets/images/house3.webp'">
              <div style="flex:1">
                <div style="font-size:14px;font-weight:800;color:#161616"><?= h($lodgeName) ?></div>
                <div style="font-size:11.5px;color:#6b7280;margin:2px 0">Reservation Code: <strong><?= h($bookingCode ?: 'BK-FASTNET') ?></strong></div>
                <div style="font-size:11.5px;color:#6b7280"><?= $nights ?> Night<?= $nights > 1 ? 's' : '' ?> • 2 Guests • <?= h($priceFormatted) ?> Total</div>
              </div>
            </div>
            <div class="cds-trip-actions">
              <button type="button" class="cds-btn cds-btn-ghost" style="flex:1;height:34px;font-size:12px" onclick="openDirectionsModal()">
                <i class="fa-solid fa-map-location-dot" style="color:#800000"></i>
                <span>Directions & Map</span>
              </button>
              <button type="button" class="cds-btn cds-btn-primary" style="flex:1;height:34px;font-size:12px;background:#800000" onclick="shareVoucher('<?= h($bookingCode ?: 'BK19JTST0W') ?>')">
                <i class="fa-solid fa-receipt"></i>
                <span>Share Voucher</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Safety Warning Banner -->
        <div class="cds-safety-banner" id="safetyBanner">
          <i class="fa-solid fa-shield-halved" style="font-size:14px"></i>
          <span style="flex:1">⚠️ Communicating or paying off-platform bypasses FASTNET safety shields. Never wire money directly.</span>
          <button type="button" onclick="document.getElementById('safetyBanner').style.display='none'" style="background:none;border:none;color:#92400e;cursor:pointer;font-size:13px">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <!-- Chat Messages Feed -->
        <div class="cds-chat-messages" id="chatFeed">
          <div class="cds-date-pill">Today</div>

          <!-- Initial Host Welcome -->
          <div class="cds-msg-row is-host">
            <div class="cds-bubble is-host">
              Karibu <?= h($lodgeName) ?>! How can we assist you before or during your stay? Feel free to ask about check-in, breakfast, directions, or special requests.
              <div class="cds-bubble-meta">
                <span>Just now</span>
              </div>
            </div>
          </div>

          <!-- Dynamic messages will populate here -->
        </div>

        <!-- Typing Indicator -->
        <div id="typingIndicator" style="display:none;padding:0 18px 8px">
          <div class="cds-typing">
            <div class="cds-typing-dots"><span></span><span></span><span></span></div>
            <span><?= h($hostName) ?> is typing...</span>
          </div>
        </div>

        <!-- Quick Reply Chips -->
        <div class="cds-quick-replies">
          <button type="button" class="cds-quick-chip" onclick="sendQuickReply('What is the Wi-Fi passcode?')">📶 Wi-Fi Passcode</button>
          <button type="button" class="cds-quick-chip" onclick="sendQuickReply('Do you offer early check-in?')">🔑 Early Check-in</button>
          <button type="button" class="cds-quick-chip" onclick="sendQuickReply('Is breakfast included in the booking?')">🍳 Breakfast Options</button>
          <button type="button" class="cds-quick-chip" onclick="sendQuickReply('Is secure parking available at the lodge?')">🚗 Parking Info</button>
          <button type="button" class="cds-quick-chip" onclick="sendQuickReply('Can you provide exact driving directions?')">📍 Directions</button>
        </div>

        <!-- Input Bar -->
        <form class="cds-chat-input-bar" id="chatForm" onsubmit="event.preventDefault(); submitChatMessage();">
          <button type="button" class="cds-chat-attach-btn" onclick="openAttachModal()" title="Attach file or info">
            <i class="fa-solid fa-circle-plus"></i>
          </button>
          <input type="text" id="chatInput" class="cds-chat-input-field" placeholder="Type a message to the property..." autocomplete="off">
          <button type="submit" class="cds-chat-send-btn" title="Send message">
            <i class="fa-solid fa-paper-plane" style="font-size:15px"></i>
          </button>
        </form>

      </section>

    </div>

  </div>
</main>

<!-- Share Attachment Modal -->
<div id="attachModal" class="cds-modal-overlay" style="display:none" role="dialog" aria-modal="true">
  <div class="cds-modal-box" style="max-width:380px">
    <h3 class="cds-modal-title" style="margin-bottom:14px">Share Attachment</h3>
    <div class="cds-attach-options">
      <div class="cds-attach-item" onclick="attachPhoto()">
        <div class="cds-attach-icon-circle" style="background:#ecfdf5;color:#10b981">
          <i class="fa-regular fa-image"></i>
        </div>
        <span style="font-size:12.5px;font-weight:700">Photo</span>
      </div>
      <div class="cds-attach-item" onclick="attachLocation()">
        <div class="cds-attach-icon-circle" style="background:#eff6ff;color:#3b82f6">
          <i class="fa-solid fa-location-dot"></i>
        </div>
        <span style="font-size:12.5px;font-weight:700">Location</span>
      </div>
      <div class="cds-attach-item" onclick="attachVoucher('<?= h($bookingCode ?: 'BK19JTST0W') ?>')">
        <div class="cds-attach-icon-circle" style="background:#f5f3ff;color:#8b5cf6">
          <i class="fa-solid fa-receipt"></i>
        </div>
        <span style="font-size:12.5px;font-weight:700">Invoice</span>
      </div>
    </div>
    <div style="margin-top:16px;text-align:center">
      <button type="button" class="cds-btn cds-btn-ghost" style="width:100%" onclick="closeAttachModal()">Cancel</button>
    </div>
  </div>
</div>

<!-- Directions Modal -->
<div id="directionsModal" class="cds-modal-overlay" style="display:none" role="dialog" aria-modal="true">
  <div class="cds-modal-box" style="max-width:440px">
    <h3 class="cds-modal-title">Directions & Location</h3>
    <p style="font-size:13.5px;color:#374151;line-height:1.5;margin-bottom:14px">
      Directions to <strong><?= h($lodgeName) ?></strong>:<br>
      Located at <?= h($activeLodge['address'] ?? 'Mwananyamala / Mikocheni, Dar es Salaam') ?>. From Bagamoyo Road, follow the primary lodge signage 200m towards the reception gate.
    </p>
    <div style="text-align:right">
      <button type="button" class="cds-btn cds-btn-primary" style="background:#800000" onclick="closeDirectionsModal()">Close</button>
    </div>
  </div>
</div>

<script>
const activeHostId = <?= json_encode($hostId) ?>;
const activeLodgeName = <?= json_encode($lodgeName) ?>;
const activeBookingCode = <?= json_encode($bookingCode) ?>;
const sendEndpoint = <?= json_encode($this->Url->build('/messages/send')) ?>;
const historyEndpoint = <?= json_encode($this->Url->build('/messages/history/')) ?>;

let chatMessages = [];
let isHostTyping = false;

function toggleTripDrawer() {
  const drawer = document.getElementById('tripDrawer');
  const chev = document.getElementById('tripDrawerChevron');
  if (drawer.style.display === 'none') {
    drawer.style.display = 'block';
    chev.style.transform = 'rotate(180deg)';
  } else {
    drawer.style.display = 'none';
    chev.style.transform = 'rotate(0deg)';
  }
}

function openAttachModal() {
  document.getElementById('attachModal').style.display = 'flex';
}
function closeAttachModal() {
  document.getElementById('attachModal').style.display = 'none';
}
function openDirectionsModal() {
  document.getElementById('directionsModal').style.display = 'flex';
}
function closeDirectionsModal() {
  document.getElementById('directionsModal').style.display = 'none';
}

function scrollToBottom() {
  const feed = document.getElementById('chatFeed');
  if (feed) {
    feed.scrollTop = feed.scrollHeight;
  }
}

function renderMessage(msg, isGuest, timeStr) {
  const feed = document.getElementById('chatFeed');
  const row = document.createElement('div');
  row.className = 'cds-msg-row ' + (isGuest ? 'is-guest' : 'is-host');
  
  let bodyHtml = '';
  if (msg.type === 'image' && msg.attachmentUrl) {
    bodyHtml = `<img src="${msg.attachmentUrl}" style="max-width:200px;border-radius:8px;margin-bottom:6px;display:block" alt="Photo"><div style="font-size:13px">${escapeHtml(msg.text)}</div>`;
  } else if (msg.type === 'location') {
    bodyHtml = `<div style="display:flex;align-items:center;gap:8px"><i class="fa-solid fa-location-dot" style="font-size:18px"></i><div><strong>Location Shared</strong><div style="font-size:11.5px">${escapeHtml(msg.text)}</div></div></div>`;
  } else if (msg.type === 'voucher') {
    bodyHtml = `<div style="display:flex;align-items:center;gap:8px"><i class="fa-solid fa-receipt" style="font-size:22px"></i><div><strong>Booking Voucher</strong><div style="font-size:11.5px">${escapeHtml(msg.text)}</div></div></div>`;
  } else {
    bodyHtml = escapeHtml(msg.text);
  }

  const checkHtml = isGuest ? '<i class="fa-solid fa-check-double" style="font-size:10px;margin-left:4px;color:#93c5fd"></i>' : '';

  row.innerHTML = `
    <div class="cds-bubble ${isGuest ? 'is-guest' : 'is-host'}">
      ${bodyHtml}
      <div class="cds-bubble-meta">
        <span>${timeStr || 'Just now'}</span>
        ${checkHtml}
      </div>
    </div>
  `;
  feed.appendChild(row);
  scrollToBottom();
}

function escapeHtml(str) {
  if (!str) return '';
  const d = document.createElement('div');
  d.textContent = str;
  return d.innerHTML;
}

async function submitChatMessage(customText, type = 'text', attachmentUrl = '') {
  const input = document.getElementById('chatInput');
  const text = customText !== undefined ? customText : (input ? input.value.trim() : '');
  if (!text && !attachmentUrl) return;

  if (input && customText === undefined) {
    input.value = '';
  }

  const nowTime = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  renderMessage({ text: text, type: type, attachmentUrl: attachmentUrl }, true, nowTime);

  // Optimistic update of sidebar preview
  const sidePrev = document.getElementById('sidebarLastMsg');
  if (sidePrev) sidePrev.textContent = text;

  try {
    const res = await fetch(sendEndpoint, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({
        recipient_id: activeHostId,
        lodge_name: activeLodgeName,
        text: text,
        type: type,
        attachment_url: attachmentUrl
      })
    });
    const data = await res.json();
  } catch (err) {}

  // Trigger simulated host reply for standard stay queries (matching mobile)
  simulateHostReply(text);
}

function simulateHostReply(userText) {
  const lower = (userText || '').toLowerCase();
  let reply = '';
  if (lower.includes('wi-fi') || lower.includes('wifi')) {
    reply = "Karibu! The Wi-Fi network is '" + activeLodgeName + "_Guest' and the passcode is 'welcome2026'. Enjoy high-speed internet!";
  } else if (lower.includes('early check-in') || lower.includes('check-in')) {
    reply = "Yes, standard check-in is at 2:00 PM, but we can arrange early check-in from 11:00 AM free of charge if the room is ready. Please let us know your arrival time!";
  } else if (lower.includes('breakfast')) {
    reply = "Yes! Complimentary breakfast buffet is served daily from 6:30 AM to 10:00 AM in our dining area.";
  } else if (lower.includes('parking')) {
    reply = "Yes, free 24/7 guarded parking is available on-site for all our guests.";
  } else if (lower.includes('direction')) {
    reply = "We are located along Bagamoyo Road, right at the Mikocheni turn. Follow the signs 200m to the main reception gate.";
  } else if (lower.includes('voucher') || lower.includes('arrived')) {
    reply = "Thank you! We have received your confirmation. Our front desk team is ready to welcome you.";
  }

  if (reply) {
    const typing = document.getElementById('typingIndicator');
    if (typing) typing.style.display = 'block';
    scrollToBottom();

    setTimeout(() => {
      if (typing) typing.style.display = 'none';
      const nowTime = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
      renderMessage({ text: reply, type: 'text' }, false, nowTime);
      const sidePrev = document.getElementById('sidebarLastMsg');
      if (sidePrev) sidePrev.textContent = reply;
    }, 1500);
  }
}

function sendQuickReply(query) {
  submitChatMessage(query);
}

function attachPhoto() {
  closeAttachModal();
  submitChatMessage('Shared room inspection photo', 'image', '/assets/images/house3.webp');
}
function attachLocation() {
  closeAttachModal();
  submitChatMessage('Shared location: Mwananyamala, Dar es Salaam', 'location', '-6.7780,39.2730');
}
function attachVoucher(code) {
  closeAttachModal();
  submitChatMessage('Shared Booking Voucher: ' + code, 'voucher', code);
}
function shareVoucher(code) {
  attachVoucher(code);
}

async function pollMessages(force = false) {
  try {
    const res = await fetch(historyEndpoint + activeHostId, {
      headers: { 'Accept': 'application/json' }
    });
    const data = await res.json();
    if (data && data.messages && Array.isArray(data.messages) && data.messages.length > 0) {
      // Live messages retrieved from backend
    }
  } catch (e) {}
}

// Auto poll every 4 seconds
setInterval(() => {
  pollMessages();
}, 4000);
</script>

<?= $this->element('footer', ['skin' => 'skin-light-footer']) ?>
