<?php
/**
 * Trivago Notifications Profile Page
 */
$this->assign('title', 'Notifications - FastNet Stays');
?>
<?= $this->Html->css('/assets/css/google-travel-layout.css') ?>
<?= $this->Html->css('/assets/css/google-travel-home.css') ?>
<?= $this->element('navbar'); ?>

<?= $this->Html->css('/assets/css/profile.css'); ?>

<div class="trivago-profile-wrapper">
    <div class="container" style="max-width: 1120px;">
        <div class="row">
            
            <!-- Reusable Left Sidebar -->
            <?= $this->element('profile_sidebar', ['active' => 'notifications']); ?>

            <!-- Right Content -->
            <div class="col-lg-9 ps-lg-4">
                <div class="trivago-profile-header">
                    <h1 class="trivago-profile-title">Notifications</h1>
                    <div class="trivago-profile-subtitle">Customise your communication preferences</div>
                </div>

                <div class="notif-list">

                    <!-- 1. All promotional emails -->
                    <div class="notif-item notif-item-master">
                        <div class="notif-info">
                            <div class="notif-title">All promotional emails</div>
                            <p class="notif-desc">Turn on to get curated hotel deals, destination tips, all price drop alerts, and search reminders.</p>
                        </div>
                        <label class="trivago-toggle-switch">
                            <input type="checkbox" id="toggleAllPromos" checked onchange="toggleAll(this)">
                            <span class="trivago-slider"></span>
                        </label>
                    </div>

                    <!-- 2. Deals and trip ideas -->
                    <div class="notif-item">
                        <div class="notif-info">
                            <div class="notif-title">Deals and trip ideas</div>
                            <p class="notif-desc">Weekly newsletter with curated deals and seasonal travel ideas</p>
                        </div>
                        <label class="trivago-toggle-switch">
                            <input type="checkbox" class="sub-toggle" id="toggleDeals" checked onchange="onSubToggleChange()">
                            <span class="trivago-slider"></span>
                        </label>
                    </div>

                    <!-- 3. Top destination tips -->
                    <div class="notif-item">
                        <div class="notif-info">
                            <div class="notif-title">Top destination tips</div>
                            <p class="notif-desc">Monthly newsletter with hand-picked destination inspiration</p>
                        </div>
                        <label class="trivago-toggle-switch">
                            <input type="checkbox" class="sub-toggle" id="toggleTips" checked onchange="onSubToggleChange()">
                            <span class="trivago-slider"></span>
                        </label>
                    </div>

                    <!-- 4. Price drops on viewed stays -->
                    <div class="notif-item">
                        <div class="notif-info">
                            <div class="notif-title">Price drops on viewed stays</div>
                            <p class="notif-desc">Email alerts when prices drop for stays you've recently viewed</p>
                        </div>
                        <label class="trivago-toggle-switch">
                            <input type="checkbox" class="sub-toggle" id="toggleDrops" checked onchange="onSubToggleChange()">
                            <span class="trivago-slider"></span>
                        </label>
                    </div>

                    <!-- 5. Search reminders -->
                    <div class="notif-item">
                        <div class="notif-info">
                            <div class="notif-title">Search reminders</div>
                            <p class="notif-desc">Email reminders to pick up your search where you left off</p>
                        </div>
                        <label class="trivago-toggle-switch">
                            <input type="checkbox" class="sub-toggle" id="toggleReminders" checked onchange="onSubToggleChange()">
                            <span class="trivago-slider"></span>
                        </label>
                    </div>

                    <!-- 6. Your price alerts -->
                    <div class="notif-item">
                        <div class="notif-info">
                            <div class="notif-title">Your price alerts</div>
                            <p class="notif-desc">Email alerts when prices drop for stays you’re tracking</p>
                        </div>
                        <label class="trivago-toggle-switch">
                            <input type="checkbox" class="sub-toggle" id="toggleAlerts" checked onchange="onSubToggleChange()">
                            <span class="trivago-slider"></span>
                        </label>
                    </div>

                </div>

                <!-- Footer disclaimer -->
                <div class="notif-footer-note">
                    You can <a href="javascript:void(0);" onclick="unsubscribeAll()">unsubscribe</a> at any time. See our <a href="<?= $this->Url->build('/privacy-policy'); ?>">privacy policy</a> for more detail.
                </div>

            </div>

        </div>
    </div>
</div>

<!-- Floating Toast -->
<div id="trivago-toast"></div>


<script>
function toggleAll(masterCheckbox) {
    const isChecked = masterCheckbox.checked;
    document.querySelectorAll('.sub-toggle').forEach(chk => {
        chk.checked = isChecked;
    });
    saveNotifPreferences();
    showNotifToast(isChecked ? 'All promotional emails enabled' : 'All promotional emails disabled');
}

function onSubToggleChange() {
    const subToggles = document.querySelectorAll('.sub-toggle');
    const anyChecked = Array.from(subToggles).some(chk => chk.checked);
    const master = document.getElementById('toggleAllPromos');
    if (master) master.checked = anyChecked;
    saveNotifPreferences();
    showNotifToast('Notification preferences updated');
}

function unsubscribeAll() {
    const master = document.getElementById('toggleAllPromos');
    if (master) master.checked = false;
    document.querySelectorAll('.sub-toggle').forEach(chk => {
        chk.checked = false;
    });
    saveNotifPreferences();
    showNotifToast('You have unsubscribed from all emails');
}

function saveNotifPreferences() {
    const state = {
        all: document.getElementById('toggleAllPromos').checked,
        deals: document.getElementById('toggleDeals').checked,
        tips: document.getElementById('toggleTips').checked,
        drops: document.getElementById('toggleDrops').checked,
        reminders: document.getElementById('toggleReminders').checked,
        alerts: document.getElementById('toggleAlerts').checked,
    };
    try {
        localStorage.setItem('user_notification_prefs', JSON.stringify(state));
    } catch(e) {}
}

function loadNotifPreferences() {
    try {
        const stored = localStorage.getItem('user_notification_prefs');
        if (stored) {
            const state = JSON.parse(stored);
            if (state.all !== undefined) document.getElementById('toggleAllPromos').checked = state.all;
            if (state.deals !== undefined) document.getElementById('toggleDeals').checked = state.deals;
            if (state.tips !== undefined) document.getElementById('toggleTips').checked = state.tips;
            if (state.drops !== undefined) document.getElementById('toggleDrops').checked = state.drops;
            if (state.reminders !== undefined) document.getElementById('toggleReminders').checked = state.reminders;
            if (state.alerts !== undefined) document.getElementById('toggleAlerts').checked = state.alerts;
        }
    } catch(e) {}
}

function showNotifToast(msg) {
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

document.addEventListener('DOMContentLoaded', loadNotifPreferences);
</script>
</main>
<?= $this->element('footer', ['skin' => 'skin-light-footer']) ?>
