<?php
/**
 * Trivago Language and Currency Profile Page
 */
$this->assign('title', 'Language and currency - FastNet Stays');
?>
<?= $this->element('navbar'); ?>

<?= $this->Html->css('/assets/css/profile.css'); ?>

<div class="trivago-profile-wrapper">
    <div class="container" style="max-width: 1120px;">
        <div class="row">
            
            <!-- Reusable Left Sidebar -->
            <?= $this->element('profile_sidebar', ['active' => 'language-currency']); ?>

            <!-- Right Content -->
            <div class="col-lg-9 ps-lg-4">
                <div class="trivago-profile-header">
                    <h1 class="trivago-profile-title">Language and currency</h1>
                    <p class="trivago-profile-sub">Choose your preferred language and currency</p>
                </div>

                <div class="trivago-lc-form">
                    <form id="langCurrencyForm" onsubmit="handleApply(event)">
                        <!-- Language Field -->
                        <div class="trivago-field-group">
                            <label class="trivago-field-label" for="prefLanguage">Language</label>
                            <div class="trivago-select-wrapper">
                                <select id="prefLanguage" class="trivago-select" name="language">
                                    <option value="en" selected>English</option>
                                    <option value="sw">Kiswahili</option>
                                    <option value="es">Español</option>
                                    <option value="fr">Français</option>
                                    <option value="de">Deutsch</option>
                                    <option value="it">Italiano</option>
                                    <option value="pt">Português</option>
                                    <option value="ar">العربية</option>
                                    <option value="zh">中文 (简体)</option>
                                </select>
                                <i class="fa-solid fa-chevron-down trivago-select-arrow"></i>
                            </div>
                        </div>

                        <!-- Currency Field -->
                        <div class="trivago-field-group">
                            <label class="trivago-field-label" for="prefCurrency">Currency</label>
                            <div class="trivago-select-wrapper">
                                <select id="prefCurrency" class="trivago-select" name="currency">
                                    <option value="USD" selected>USD - US Dollar</option>
                                    <option value="TZS">TZS - Tanzanian Shilling</option>
                                    <option value="EUR">EUR - Euro</option>
                                    <option value="GBP">GBP - British Pound</option>
                                    <option value="CAD">CAD - Canadian Dollar</option>
                                    <option value="AUD">AUD - Australian Dollar</option>
                                    <option value="KES">KES - Kenyan Shilling</option>
                                    <option value="UGX">UGX - Ugandan Shilling</option>
                                    <option value="ZAR">ZAR - South African Rand</option>
                                </select>
                                <i class="fa-solid fa-chevron-down trivago-select-arrow"></i>
                            </div>
                        </div>

                        <!-- Apply Button -->
                        <div class="trivago-actions">
                            <button type="submit" class="trivago-btn-apply" id="applyBtn">Apply</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<div id="trivago-toast">Preferences updated successfully</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load saved preferences if available
    const savedLang = localStorage.getItem('fastnet_lang') || 'en';
    const savedCurr = localStorage.getItem('fastnet_curr') || 'USD';

    const langSelect = document.getElementById('prefLanguage');
    const currSelect = document.getElementById('prefCurrency');

    if (langSelect) langSelect.value = savedLang;
    if (currSelect) currSelect.value = savedCurr;
});

function handleApply(e) {
    e.preventDefault();
    const langSelect = document.getElementById('prefLanguage');
    const currSelect = document.getElementById('prefCurrency');
    const applyBtn = document.getElementById('applyBtn');

    if (langSelect) localStorage.setItem('fastnet_lang', langSelect.value);
    if (currSelect) localStorage.setItem('fastnet_curr', currSelect.value);

    // Provide visual feedback
    applyBtn.innerText = 'Applying...';
    applyBtn.disabled = true;

    setTimeout(() => {
        applyBtn.innerText = 'Apply';
        applyBtn.disabled = false;
        showToast('Language and currency updated successfully');
    }, 400);
}

function showToast(msg) {
    const toast = document.getElementById('trivago-toast');
    if (!toast) return;
    toast.textContent = msg;
    toast.style.display = 'block';
    setTimeout(() => { toast.style.opacity = '1'; }, 10);
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => { toast.style.display = 'none'; }, 300);
    }, 3000);
}
</script>
