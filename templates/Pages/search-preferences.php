<?php
/**
 * Trivago Search Preferences Profile Page
 */
$this->assign('title', 'Search preferences - FastNet Stays');
?>
<?= $this->element('navbar'); ?>

<?= $this->Html->css('/assets/css/profile.css'); ?>

<div class="trivago-profile-wrapper">
    <div class="container" style="max-width: 1120px;">
        <div class="row">
            
            <!-- Reusable Left Sidebar -->
            <?= $this->element('profile_sidebar', ['active' => 'search-preferences']); ?>

            <!-- Right Content -->
            <div class="col-lg-9 ps-lg-4">
                <div class="trivago-profile-header">
                    <h1 class="trivago-profile-title">Search preferences</h1>
                    <div class="trivago-profile-subtitle">Customise your hotel and search preferences</div>
                </div>

                <!-- Personalisation Options Accordion Card -->
                <div class="sp-card-accordion open" id="personalisationAccordion">
                    
                    <div class="sp-acc-header" onclick="togglePersonalisation()">
                        <div class="sp-acc-title-group">
                            <i class="fa-solid fa-pencil sp-acc-icon"></i>
                            <div>
                                <div class="sp-acc-title">Personalisation options</div>
                                <p class="sp-acc-sub">Get results and filters tailored to you</p>
                            </div>
                        </div>
                        <i class="fa-solid fa-chevron-down sp-acc-chevron"></i>
                    </div>

                    <div class="sp-acc-body">
                        <!-- Yellow Bellhop Banner -->
                        <div class="sp-banner-wrapper">
                            <img src="<?= $this->Url->build('/assets/img/search-pref-banner.png'); ?>" alt="Search preferences banner" class="sp-banner-img">
                        </div>

                        <!-- Question Prompt Content -->
                        <div class="sp-question-box">
                            <div class="sp-duration-badge">2 minutes</div>
                            <h2 class="sp-main-question">What matters to you when booking a hotel?</h2>
                            <p class="sp-question-desc">Share your preferences to see more of what you like.</p>

                            <button type="button" class="sp-btn-get-started" onclick="openPrefModal()">
                                Get started
                            </button>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </div>
</div>

<!-- Preferences Questionnaire Modal -->
<div class="sp-pref-modal-backdrop" id="prefModal">
    <div class="sp-pref-modal">
        <h4 class="fw-bold text-slate-900 mb-1">What matters most to you?</h4>
        <p class="text-xs text-slate-500 mb-3">Select the amenities and factors that matter during your trips.</p>

        <div class="mb-4">
            <div class="sp-pref-tag" onclick="toggleTag(this)"><i class="fa-solid fa-wifi"></i> Free High-speed WiFi</div>
            <div class="sp-pref-tag" onclick="toggleTag(this)"><i class="fa-solid fa-utensils"></i> Complimentary Breakfast</div>
            <div class="sp-pref-tag" onclick="toggleTag(this)"><i class="fa-solid fa-water-ladder"></i> Swimming Pool</div>
            <div class="sp-pref-tag" onclick="toggleTag(this)"><i class="fa-solid fa-square-parking"></i> Free Parking</div>
            <div class="sp-pref-tag" onclick="toggleTag(this)"><i class="fa-solid fa-spa"></i> Spa & Wellness</div>
            <div class="sp-pref-tag" onclick="toggleTag(this)"><i class="fa-solid fa-location-dot"></i> City Centre / Beachfront</div>
            <div class="sp-pref-tag" onclick="toggleTag(this)"><i class="fa-solid fa-snowflake"></i> Air Conditioning</div>
            <div class="sp-pref-tag" onclick="toggleTag(this)"><i class="fa-solid fa-ban-smoking"></i> Non-smoking rooms</div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-light rounded-2 fw-semibold px-3" onclick="closePrefModal()">Cancel</button>
            <button type="button" class="btn btn-primary rounded-2 fw-bold px-4" onclick="savePreferences()">Save preferences</button>
        </div>
    </div>
</div>

<!-- Floating Toast -->
<div id="trivago-toast"></div>


<script>
function togglePersonalisation() {
    const card = document.getElementById('personalisationAccordion');
    if (card) card.classList.toggle('open');
}

function openPrefModal() {
    const m = document.getElementById('prefModal');
    if (m) m.classList.add('open');
}

function closePrefModal() {
    const m = document.getElementById('prefModal');
    if (m) m.classList.remove('open');
}

function toggleTag(el) {
    el.classList.toggle('selected');
}

function savePreferences() {
    const selected = [];
    document.querySelectorAll('.sp-pref-tag.selected').forEach(tag => {
        selected.push(tag.innerText.trim());
    });
    try {
        localStorage.setItem('user_hotel_preferences', JSON.stringify(selected));
    } catch(e) {}
    closePrefModal();
    showSpToast('Search preferences saved');
}

function showSpToast(msg) {
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
</script>
