<?php
/**
 * Trivago Account Security Profile Page
 */
$this->assign('title', 'Account security - FastNet Stays');
?>
<?= $this->Html->css('/assets/css/google-travel-layout.css') ?>
<?= $this->Html->css('/assets/css/google-travel-home.css') ?>
<?= $this->element('navbar'); ?>

<?= $this->Html->css('/assets/css/profile.css'); ?>

<div class="trivago-profile-wrapper">
    <div class="container" style="max-width: 1120px;">
        <div class="row">
            
            <!-- Reusable Left Sidebar -->
            <?= $this->element('profile_sidebar', ['active' => 'security']); ?>

            <!-- Right Content -->
            <div class="col-lg-9 ps-lg-4">
                <div class="trivago-profile-header">
                    <h1 class="trivago-profile-title">Account security</h1>
                    <p class="trivago-profile-sub">Manage your trivago account</p>
                </div>

                <!-- Account Security Accordion List -->
                <div class="trivago-sec-box">

                    <!-- Delete account section -->
                    <div class="trivago-acc-item open" id="deleteAccountSection">
                        <div class="trivago-acc-header" onclick="toggleSecItem('deleteAccountSection')">
                            <div>
                                <div class="trivago-acc-title">Delete account</div>
                                <div class="trivago-acc-desc">Permanently delete your account</div>
                            </div>
                            <i class="fa-solid fa-chevron-down trivago-acc-chevron"></i>
                        </div>

                        <div class="trivago-acc-body">
                            <!-- Warning Loss Box -->
                            <div class="trivago-loss-alert">
                                <div class="trivago-loss-title">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                    <span>You will permanently lose:</span>
                                </div>
                                <ul class="trivago-loss-list">
                                    <li>All stored payment methods</li>
                                    <li>Newsletter subscription</li>
                                    <li>Access to member exclusive deals</li>
                                    <li>Access to trivago Business Studio</li>
                                    <li>Access to your favourite lists</li>
                                </ul>
                            </div>

                            <p class="trivago-warning-text mb-2">
                                We will no longer have access to any personal data or previous contact made through this account.
                            </p>
                            <p class="trivago-warning-text mb-2">
                                It may take up to 30 days to complete this process.
                            </p>
                            <p class="trivago-warning-text mb-4">
                                To know more about how trivago uses your information, please take a moment to review our <a href="<?= $this->Url->build('/privacy-policy'); ?>">Privacy Policy</a>.
                            </p>

                            <div class="d-flex justify-content-end">
                                <button type="button" class="trivago-btn-delete" onclick="handleDeleteAccount()">
                                    Delete my account
                                </button>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </div>
</div>

<!-- Floating Toast -->
<div id="trivago-toast"></div>


<script>
function toggleSecItem(id) {
    const item = document.getElementById(id);
    if (!item) return;
    item.classList.toggle('open');
}

function handleDeleteAccount() {
    if (confirm("Are you sure you want to permanently delete your trivago account? This action cannot be undone.")) {
        try {
            localStorage.removeItem('auth_token');
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            localStorage.removeItem('fastnet_wishlist');
        } catch (e) {}

        const toast = document.getElementById('trivago-toast');
        if (toast) {
            toast.innerText = "Account deletion initiated. Signing out...";
            toast.style.display = 'block';
            setTimeout(() => { toast.style.opacity = '1'; }, 10);
            setTimeout(() => {
                window.location.href = '<?= $this->Url->build('/'); ?>';
            }, 1500);
        } else {
            window.location.href = '<?= $this->Url->build('/'); ?>';
        }
    }
}
</script>
</main>
<?= $this->element('footer', ['skin' => 'skin-light-footer']) ?>
