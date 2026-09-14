<?php
/**
 * Trivago Favourites / Wishlists Profile Page
 */
$this->assign('title', 'Your favourites - FastNet Stays');
?>
<?= $this->Html->css('/assets/css/google-travel-layout.css') ?>
<?= $this->Html->css('/assets/css/google-travel-home.css') ?>
<?= $this->element('navbar'); ?>

<?= $this->Html->css('/assets/css/wishlists.css'); ?>

<main id="main-content" role="main">
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

                <div id="savedStaysSection" class="mb-4">
                <?php if (!empty($wishlists)): ?>
                    <h3 style="font-size:16px;font-weight:700;color:#0f172a;margin-bottom:12px;font-family:'Google Sans',sans-serif;">Saved stays</h3>
                    <div class="row g-3" id="savedStaysGrid">
                        <?php foreach ($wishlists as $w): 
                            $img = $w['image_url'] ?? $w['primary_image_url'] ?? 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=400&h=300&fit=crop';
                            $propTitle = \App\Utility\TextFormatter::formatTitle((string)($w['name'] ?? 'Stay'));
                            $propCity = \App\Utility\TextFormatter::formatTitle((string)($w['city'] ?? 'Tanzania'));
                            $priceVal = (float)($w['price_per_night'] ?? ($w['price'] ?? 0));
                            $wId = (int)($w['id'] ?? 0);
                        ?>
                        <div class="col-md-6" id="wishlist_card_<?= $wId ?>">
                            <div class="card h-100 shadow-sm border-0" style="border:1px solid #e8eaed !important;border-radius:12px;overflow:hidden;background:#fff;">
                                <div style="position:relative;height:160px;background:#e5e7eb;overflow:hidden;">
                                    <img src="<?= h($img) ?>" style="height:100%;object-fit:cover;width:100%;display:block;" alt="<?= h($propTitle) ?>" onerror="this.src='https://images.unsplash.com/photo-1566073771259-6a8506099945?w=400&h=300&fit=crop'">
                                    <span style="position:absolute;top:10px;right:10px;background:rgba(255,255,255,0.92);border-radius:50%;width:32px;height:32px;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(0,0,0,0.15);"><i class="fa-solid fa-heart text-danger" style="font-size:14px;"></i></span>
                                </div>
                                <div class="p-3 d-flex flex-column justify-content-between" style="flex:1;">
                                    <div>
                                        <div style="font-weight:700;color:#202124;font-size:15px;font-family:'Google Sans',sans-serif;"><?= h($propTitle) ?></div>
                                        <div style="font-size:12.5px;color:#5f6368;margin-top:2px;"><i class="fa-solid fa-location-dot" style="color:#1a73e8;font-size:11px;"></i> <?= h($propCity) ?></div>
                                        <?php if ($priceVal > 0): ?>
                                        <div style="font-size:13px;font-weight:700;color:#202124;margin-top:6px;">TSh <?= number_format($priceVal) ?> <span style="font-size:11px;font-weight:400;color:#5f6368;">/ night</span></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 mt-3 pt-2 border-top">
                                        <a href="<?= $this->Url->build('/hotel-detail/' . $wId) ?>" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold" style="font-size:12px;">View Stay</a>
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold" style="font-size:12px;" onclick="removeWishlist(<?= $wId ?>)"><i class="fa-solid fa-trash-can me-1"></i>Remove</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info py-2" style="font-size:13px;border-radius:8px;">No favourites yet — tap ♡ on any stay to save.</div>
                <?php endif; ?>
                </div>

                <!-- Loading indicator — visible during silent fetch, not silent waiting -->
                <div id="favLoading" style="display:none;align-items:center;gap:10px;padding:14px 0;color:#64748b;font-size:13px">
                    <span class="spinner-border spinner-border-sm" style="width:16px;height:16px;border-width:2px;color:#007fad"></span>
                    <span>Loading your favourites...</span>
                </div>
                <!-- Favourite Lists Container (local custom lists) -->
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
</main>
<?= $this->element('footer', ['skin' => 'skin-light-footer']) ?>
