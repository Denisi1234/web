<?php
/**
 * Trivago Language and Currency Profile Page
 */
$this->assign('title', 'Language and currency - FastNet Stays');
?>
<?= $this->Html->css('/assets/css/google-travel-layout.css') ?>
<?= $this->Html->css('/assets/css/google-travel-home.css') ?>
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
                                    <?php if (!empty($currencies) && is_array($currencies)): ?>
                                        <?php foreach ($currencies as $c): $code = $c['code'] ?? ''; ?>
                                            <option value="<?= h($code) ?>" <?= $code === 'TZS' ? 'selected' : '' ?>>
                                                <?= h($code) ?> - <?= h($c['name'] ?? $code) ?> <?= !empty($c['flag']) ? h($c['flag']) : '' ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="TZS" selected>TZS - Tanzanian Shilling 🇹🇿</option>
                                        <option value="USD">USD - US Dollar 🇺🇸</option>
                                        <option value="EUR">EUR - Euro 🇪🇺</option>
                                        <option value="GBP">GBP - British Pound 🇬🇧</option>
                                        <option value="KES">KES - Kenyan Shilling 🇰🇪</option>
                                    <?php endif; ?>
                                </select>
                                <div id="currencyRateNote" style="font-size:12px;color:#6b7280;margin-top:4px"></div>
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
    if (currSelect && currSelect.value !== savedCurr) currSelect.dataset.restore = savedCurr;

    loadCurrencies();
});

function handleApply(e) {
    e.preventDefault();
    const langSelect = document.getElementById('prefLanguage');
    const currSelect = document.getElementById('prefCurrency');
    const applyBtn = document.getElementById('applyBtn');

    if (langSelect) localStorage.setItem('fastnet_lang', langSelect.value);
    if (currSelect) localStorage.setItem('fastnet_curr', currSelect.value);

    applyBtn.innerText = 'Applying...';
    applyBtn.disabled = true;

    // Nothing to persist server-side for display preference yet, so restore the
    // button as soon as the local write is done. This used to hold the button
    // for a flat 400ms to simulate work.
    try {
        localStorage.setItem('fastnet_curr_rate', String(document.getElementById('currencyRateNote').dataset.rate || ''));
    } catch (err) {}

    applyBtn.innerText = 'Apply';
    applyBtn.disabled = false;
    showToast('Language and currency updated');
}

/* Load the real currency list and live TZS rates from GET /api/currencies. */
function loadCurrencies() {
    var select = document.getElementById('prefCurrency');
    var note   = document.getElementById('currencyRateNote');
    if (!select) return;

    fetch('/api/currencies', { headers: { 'Accept': 'application/json' } })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (!data || !Array.isArray(data.currencies) || !data.currencies.length) return;

            var current = localStorage.getItem('fastnet_curr') || 'TZS';
            var seen = {};
            var html = '';

            data.currencies.forEach(function (c) {
                var code = c.code || c.currency_code || c.symbol;
                if (!code || seen[code]) return;
                seen[code] = true;
                var name = c.name || code;
                html += '<option value="' + code + '">' + code + ' - ' + name + '</option>';
            });

            select.innerHTML = html;
            if (seen[current]) select.value = current;

            var updateNote = function () {
                var chosen = data.currencies.find(function (c) {
                    return (c.code || c.currency_code) === select.value;
                });
                if (!chosen || !note) return;
                var rate = chosen.tzs_per_unit;
                note.dataset.rate = rate != null ? rate : '';
                note.textContent = rate
                    ? '1 ' + select.value + ' = ' + Number(rate).toLocaleString() + ' TZS'
                      + (data.rates_stale ? ' (approx. — live rates unavailable)' : '')
                    : '';
            };
            updateNote();
            select.addEventListener('change', updateNote);
        })
        .catch(function () { /* keep the default TZS option */ });
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
</main>
<?= $this->element('footer', ['skin' => 'skin-light-footer']) ?>
