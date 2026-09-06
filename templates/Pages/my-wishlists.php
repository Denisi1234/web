<?php
/**
 * Trivago Favourites / Wishlists Profile Page
 */
$this->assign('title', 'Your favourites - FastNet Stays');
?>
<?= $this->element('navbar'); ?>

<?= $this->Html->css('/assets/css/wishlists.css'); ?>

<div class="trivago-profile-wrapper">
    <div class="container" style="max-width: 1120px;">
        <div class="row">
            
            <!-- Reusable Left Sidebar -->
            <?= $this->element('profile_sidebar', ['active' => 'favourites']); ?>

            <!-- Right Content -->
            <div class="col-lg-9 ps-lg-4">
                <div class="trivago-profile-header">
                    <h1 class="trivago-profile-title">Your favourites</h1>
                    <div class="trivago-lists-counter" id="listsCounter">2/20 lists</div>
                </div>

                <!-- Favourite Lists Container -->
                <div class="fav-lists-grid" id="favouriteListsContainer">
                    <!-- Cards will be populated dynamically via JavaScript -->
                </div>

                <!-- Create New List Button -->
                <button type="button" class="fav-create-btn" onclick="openCreateListModal()">
                    <i class="fa-solid fa-plus"></i>
                    <span>Create new list</span>
                </button>

            </div>

        </div>
    </div>
</div>

<!-- Modal: Create new list -->
<div class="trivago-modal-backdrop" id="createListModal">
    <div class="trivago-modal-card">
        <h4 class="fw-bold text-slate-900 mb-2">Create new list</h4>
        <p class="text-sm text-slate-500 mb-3">Name your list to organise your saved places.</p>
        <div>
            <label class="form-label text-xs fw-bold text-slate-600 mb-1">List name</label>
            <input type="text" id="newListInput" class="trivago-form-input" placeholder="e.g. Summer Vacation, Zanzibar Stays" maxlength="35">
        </div>
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="trivago-btn-ghost" onclick="closeCreateListModal()">Cancel</button>
            <button type="button" class="trivago-btn-primary" onclick="submitCreateList()">Create</button>
        </div>
    </div>
</div>

<!-- Floating Toast -->
<div id="trivago-toast"></div>


<?= $this->Html->script('/assets/js/wishlists.js'); ?>