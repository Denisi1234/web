<?php
/**
 * Host Profile — IBM Carbon v11 Host Interface
 * @var \App\View\AppView $this
 * @var array $me
 * @var array $userProfile
 * @var array $stats
 * @var string|null $verificationStatus Real owner-verification state from
 *      GET /verification/owner (null = never submitted). Anything the page
 *      claims about verification must come from this, not from decoration.
 */
$this->assign('title', 'Host Profile & Identity · FastNet Portal');
$this->assign('portal_title', 'Host Profile');

$name = trim((string)($me['name'] ?? $me['full_name'] ?? 'FastNet Host'));
$email = trim((string)($me['email'] ?? ''));
$phone = trim((string)($me['phone'] ?? $me['phone_number'] ?? ''));
$address = trim((string)($me['address'] ?? $me['city'] ?? 'Dar es Salaam, Tanzania'));
$avatarUrl = trim((string)($me['profile_photo_url'] ?? ''));
if ($avatarUrl === '') {
    // The session-cached profile can carry avatar: '' while /me returns
    // profile_photo_url: null. An empty string defeats ??, so check plainly.
    $avatarUrl = trim((string)($me['avatar'] ?? ''));
}
// No stock-photo fallback: showing a stranger's Unsplash portrait as the
// host's own face is worse than showing nothing. Initials it is.
$avatarInitial = mb_strtoupper(mb_substr($name !== '' ? $name : '?', 0, 1, 'UTF-8'), 'UTF-8');
$userId = (int)($me['id'] ?? 0);
$displayId = $userId > 0 ? sprintf('HOST-%05d', $userId) : '';
$propertiesCount = (int)($stats['properties'] ?? 0);
$roomsCount = (int)($stats['rooms'] ?? 0);
$verificationStatus = isset($verificationStatus) && is_string($verificationStatus) ? $verificationStatus : null;
$isVerifiedOwner = $verificationStatus === 'approved';

$this->assign('page_actions', '
  <a href="' . $this->Url->build('/host/cache-bust?scope=profile&go=' . urlencode('/host/profile')) . '" class="cp-btn cp-btn-ghost cp-btn-sm" title="Bust session profile cache and fetch fresh record from FastNet API">
    <i class="fa-solid fa-arrows-rotate"></i> Re-sync
  </a>
');
?>
<?= $this->Html->css('/assets/css/carbon-profile.css') ?>

<div class="cp-wrap">
  <!-- IBM Carbon Page Header -->
  <header class="cp-header">
    <nav class="cp-breadcrumb" aria-label="Breadcrumb">
      <a href="<?= $this->Url->build('/host/dashboard') ?>">FastNet Portal</a>
      <span class="sep">/</span>
      <a href="<?= $this->Url->build('/host/listings') ?>">Workspace</a>
      <span class="sep">/</span>
      <span style="color:var(--cds-gray-100);font-weight:500">Host Profile</span>
    </nav>
    <div class="cp-title-row">
      <div class="cp-title-area">
        <h1>Host Profile</h1>
        <p class="cp-subtitle">Manage your public hosting identity and contact details.</p>
      </div>
      <?php if ($isVerifiedOwner): ?>
      <div class="cp-header-actions">
        <span class="cp-tag cp-tag-green"><span class="cp-status-dot"></span> Verified host</span>
      </div>
      <?php endif; ?>
    </div>
  </header>

  <!-- Hero Tile -->
  <section class="cp-hero-tile">
    <div class="cp-avatar-box">
      <?php if ($avatarUrl !== ''): ?>
        <img id="avatarPreview" src="<?= h($avatarUrl) ?>" alt="<?= h($name) ?>" class="cp-avatar-img">
      <?php else: ?>
        <span class="cp-avatar-initial" aria-hidden="true"><?= h($avatarInitial) ?></span>
      <?php endif; ?>
    </div>

    <div class="cp-identity-info">
      <div class="cp-host-name-row">
        <span class="cp-host-name"><?= h($name !== '' ? $name : 'Unnamed Host') ?></span>
        <?php if ($displayId !== ''): ?>
          <span class="cp-host-id"><?= h($displayId) ?></span>
        <?php endif; ?>
      </div>

      <div class="cp-meta-row">
        <?php if ($email !== ''): ?>
          <span class="cp-meta-item"><i class="fa-regular fa-envelope"></i> <?= h($email) ?></span>
        <?php endif; ?>
        <?php if ($phone !== ''): ?>
          <span class="cp-meta-item"><i class="fa-solid fa-phone"></i> <?= h($phone) ?></span>
        <?php endif; ?>
        <span class="cp-meta-item"><i class="fa-solid fa-location-dot"></i> <?= h($address !== '' ? $address : 'Tanzania') ?></span>
      </div>
    </div>

    <div class="cp-stats-grid">
      <div class="cp-stat-box">
        <div class="cp-stat-val"><?= $propertiesCount ?></div>
        <div class="cp-stat-lbl">Properties</div>
      </div>
      <div class="cp-stat-box">
        <div class="cp-stat-val"><?= $roomsCount ?></div>
        <div class="cp-stat-lbl">Rooms Total</div>
      </div>
    </div>
  </section>

  <!-- IBM Carbon Tabs / Content Switcher -->
  <div class="cp-tabs-nav" role="tablist">
    <button type="button" class="cp-tab-btn active" data-tab="tab-profile" role="tab" aria-selected="true">
      <i class="fa-regular fa-id-card"></i> Profile &amp; Contact Details
    </button>
    <button type="button" class="cp-tab-btn" data-tab="tab-credentials" role="tab" aria-selected="false">
      <i class="fa-solid fa-hotel"></i> Hosting Portfolio &amp; Verification
    </button>
  </div>

  <!-- TAB 1: Profile & Contact Details (Form) -->
  <div id="tab-profile" class="cp-tab-pane active" role="tabpanel">
    <!-- Carbon Inline Notification -->
    <div class="cp-notification cp-notification-info">
      <i class="fa-solid fa-circle-info"></i>
      <div>
        <div class="cp-notification-title">FastNet Profile Synchronization</div>
        <div>Your contact details and public bio appear on your lodge listings, booking confirmations, and direct guest communication channels.</div>
      </div>
    </div>

    <?= $this->Form->create(null, [
      'url' => ['action' => 'profile'],
      'type' => 'file',
      'id' => 'hostProfileForm',
      'data-api' => 'PATCH /profile',
      'data-api-build' => 'profile',
      'data-api-ok' => 'Profile updated.',
      'data-opt' => 'refresh',
      'data-opt-bust' => 'profile',
      'data-api-go' => '/host/cache-bust?scope=profile&go=' . urlencode('/host/profile')
    ]) ?>

      <!-- Personal & Contact Information Card -->
      <div class="cp-card">
        <div class="cp-section-head">
          <div>
            <h2>Personal &amp; Contact Details</h2>
            <div class="cp-section-sub">Official identity used for host verification and guest correspondence</div>
          </div>
          <span class="cp-tag cp-tag-gray">Mandatory fields</span>
        </div>

        <div class="row g-3">
          <div class="col-md-6">
            <div class="cp-form-item">
              <label class="cp-label" for="profileName">Full legal name <span class="req">*</span></label>
              <div class="cp-input-group">
                <i class="fa-regular fa-user cp-input-icon"></i>
                <input id="profileName" name="name" type="text" value="<?= h($name) ?>" class="cp-input" required placeholder="e.g. Amina Rashid" autocomplete="name">
              </div>
              <div class="cp-helper-text">Displayed on guest vouchers and invoices</div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="cp-form-item">
              <label class="cp-label" for="profileEmail">Primary contact email <span class="req">*</span></label>
              <div class="cp-input-group">
                <i class="fa-regular fa-envelope cp-input-icon"></i>
                <input id="profileEmail" name="email" type="email" value="<?= h($email) ?>" class="cp-input" required placeholder="host@example.com" autocomplete="email">
              </div>
              <div class="cp-helper-text">Used for reservation alerts and security notifications</div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="cp-form-item">
              <label class="cp-label" for="profilePhone">Phone number (M-Pesa / WhatsApp) <span class="req">*</span></label>
              <div class="cp-input-group">
                <i class="fa-solid fa-phone cp-input-icon"></i>
                <input id="profilePhone" name="phone" type="tel" value="<?= h($phone) ?>" class="cp-input" required placeholder="+255 7XX XXX XXX" autocomplete="tel">
              </div>
              <div class="cp-helper-text">Used for instant SMS booking notifications &amp; payout confirmations</div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="cp-form-item">
              <label class="cp-label" for="profileAddress">Operating address / Base city</label>
              <div class="cp-input-group">
                <i class="fa-solid fa-location-dot cp-input-icon"></i>
                <input id="profileAddress" name="address" type="text" value="<?= h($address) ?>" class="cp-input" placeholder="e.g. Masaki, Dar es Salaam">
              </div>
              <div class="cp-helper-text">City, district or physical headquarters address</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Profile Avatar Uploader Card -->
      <div class="cp-card">
        <div class="cp-section-head">
          <div>
            <h2>Profile Photo &amp; Avatar</h2>
            <div class="cp-section-sub">A personal portrait or company logo, shown next to your listings</div>
          </div>
          <span class="cp-tag cp-tag-gray">Max 5 MB</span>
        </div>

        <div class="cp-dropzone" id="avatarDropzone">
          <?php if ($avatarUrl !== ''): ?>
            <img id="dropzonePreview" src="<?= h($avatarUrl) ?>" alt="Avatar Preview" class="cp-dropzone-preview">
          <?php else: ?>
            <span class="cp-avatar-initial cp-avatar-initial-lg" aria-hidden="true"><?= h($avatarInitial) ?></span>
          <?php endif; ?>
          <div class="cp-dropzone-content">
            <div class="cp-dropzone-title">Select new profile photograph</div>
            <div class="cp-dropzone-desc" id="fileInfoText">Drag &amp; drop an image here or click Browse. Supported formats: JPG, PNG, WebP (maximum 5 MB).</div>
          </div>
          <div class="cp-dropzone-actions">
            <input type="file" id="avatarFile" name="avatarFile" accept="image/jpeg,image/png,image/webp" class="cp-file-input">
            <button type="button" class="cp-btn cp-btn-secondary cp-btn-sm" id="btnChooseFile">
              <i class="fa-solid fa-arrow-up-from-bracket"></i> Browse file
            </button>
            <button type="button" class="cp-btn cp-btn-ghost cp-btn-sm d-none" id="btnResetFile" title="Reset image">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- IBM Carbon Form Actions Bar -->
      <div class="cp-actions-bar">
        <div class="cp-sync-status">
          <i class="fa-solid fa-cloud-arrow-up text-primary"></i>
          <span id="syncStatusMsg">Changes are saved when you click “Save profile changes”</span>
        </div>
        <div style="display:flex;gap:12px;align-items:center">
          <button type="reset" class="cp-btn cp-btn-ghost" id="btnResetForm">Discard changes</button>
          <button type="submit" class="cp-btn cp-btn-primary" id="btnSaveProfile">
            <i class="fa-solid fa-floppy-disk"></i> Save profile changes
          </button>
        </div>
      </div>

    <?= $this->Form->end() ?>
  </div>

  <!-- TAB 2: Hosting Portfolio & Verification -->
  <div id="tab-credentials" class="cp-tab-pane" role="tabpanel">
    <div class="cp-card">
      <div class="cp-section-head">
        <div>
          <h2>Hosting Portfolio Overview</h2>
          <div class="cp-section-sub">Active lodges and listings associated with your host credentials</div>
        </div>
        <a href="<?= $this->Url->build('/host/listings') ?>" class="cp-btn cp-btn-tertiary cp-btn-sm">
          <i class="fa-solid fa-list"></i> Manage properties
        </a>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <div class="cp-stat-box" style="padding:16px">
            <div class="cp-stat-val"><?= $propertiesCount ?></div>
            <div class="cp-stat-lbl">Managed Properties</div>
            <div style="font-size:12px;color:var(--cds-gray-70);margin-top:6px">Published on FastNet search</div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="cp-stat-box" style="padding:16px">
            <div class="cp-stat-val"><?= $roomsCount ?></div>
            <div class="cp-stat-lbl">Available Rooms / Units</div>
            <div style="font-size:12px;color:var(--cds-gray-70);margin-top:6px">Configured with rate plans</div>
          </div>
        </div>
      </div>

      <!-- Real verification state. The table that used to sit here asserted a
           fabricated "100% compliant" with tier rows that map to no backend
           state at all. This shows the actual GET /verification/owner status,
           and null (never submitted) is a first-class answer, not an error. -->
      <h3 style="font-size:14px;font-weight:600;margin-bottom:12px">Identity verification</h3>
      <?php if ($verificationStatus === 'approved'): ?>
        <div class="cp-notification" style="border-left:3px solid var(--cds-green-50)">
          <i class="fa-solid fa-circle-check" style="color:var(--cds-green-50)"></i>
          <div>
            <div class="cp-notification-title">Verified</div>
            <div>Your identity documents were approved. Your listings are eligible to go live.</div>
          </div>
        </div>
      <?php elseif ($verificationStatus !== null): ?>
        <div class="cp-notification cp-notification-info">
          <i class="fa-solid fa-circle-info"></i>
          <div>
            <div class="cp-notification-title">Status: <?= h(ucfirst(str_replace('_', ' ', $verificationStatus))) ?></div>
            <div>Your documents are with our review team. Most reviews complete within one business day.</div>
          </div>
        </div>
      <?php else: ?>
        <div class="cp-notification cp-notification-info">
          <i class="fa-solid fa-circle-info"></i>
          <div>
            <div class="cp-notification-title">Not submitted</div>
            <div>Your properties cannot go live until your identity is verified. It takes a few minutes.</div>
            <div style="margin-top:10px"><a href="<?= $this->Url->build('/join-us#verify-identity') ?>" class="cp-btn cp-btn-primary cp-btn-sm">Verify your identity</a></div>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
(function() {
  // ── Tab Switcher with URL hash support ──
  var tabButtons = document.querySelectorAll('.cp-tab-btn');
  var tabPanes = document.querySelectorAll('.cp-tab-pane');

  function switchTab(targetId) {
    tabButtons.forEach(function(btn) {
      var isTarget = btn.getAttribute('data-tab') === targetId;
      btn.classList.toggle('active', isTarget);
      btn.setAttribute('aria-selected', isTarget ? 'true' : 'false');
    });
    tabPanes.forEach(function(pane) {
      pane.classList.toggle('active', pane.id === targetId);
    });
  }

  tabButtons.forEach(function(btn) {
    btn.addEventListener('click', function() {
      var targetId = this.getAttribute('data-tab');
      switchTab(targetId);
      if (history.replaceState) {
        history.replaceState(null, null, '#' + targetId.replace('tab-', ''));
      }
    });
  });

  // Activate tab from URL hash if provided
  var hash = window.location.hash.replace('#', '');
  if (hash && document.getElementById('tab-' + hash)) {
    switchTab('tab-' + hash);
  }

  // ── Avatar Dropzone & Instant Preview ──
  var avatarFile = document.getElementById('avatarFile');
  var btnChoose = document.getElementById('btnChooseFile');
  var btnReset = document.getElementById('btnResetFile');
  var dropzone = document.getElementById('avatarDropzone');
  var fileInfo = document.getElementById('fileInfoText');
  var preview1 = document.getElementById('avatarPreview');
  var preview2 = document.getElementById('dropzonePreview');
  var initialAvatarSrc = preview1 ? preview1.src : '';

  if (btnChoose && avatarFile) {
    btnChoose.addEventListener('click', function() {
      avatarFile.click();
    });
  }

  function previewSlot(rootSel, cls, alt) {
    // When the host has no photo yet the slot holds an initials span instead
    // of an <img>. Swap it for a live image so the new file previews.
    var slot = document.querySelector(rootSel + ' img, ' + rootSel + ' .cp-avatar-initial');
    if (slot && slot.tagName !== 'IMG') {
      var img = document.createElement('img');
      img.className = cls;
      img.alt = alt;
      slot.replaceWith(img);
      return img;
    }
    return slot;
  }

  function handleFile(file) {
    if (!file) return;
    if (file.size > 5 * 1024 * 1024) {
      window.alert('Profile photograph exceeds 5 MB. Please select a smaller image.');
      avatarFile.value = '';
      return;
    }
    if (!/^image\/(jpeg|png|webp)$/i.test(file.type)) {
      window.alert('Invalid file format. Please upload JPG, PNG or WebP.');
      avatarFile.value = '';
      return;
    }

    var reader = new FileReader();
    reader.onload = function(e) {
      preview1 = previewSlot('.cp-avatar-box', 'cp-avatar-img', 'Host photo');
      preview2 = previewSlot('#avatarDropzone', 'cp-dropzone-preview', 'Avatar Preview');
      if (preview1) preview1.src = e.target.result;
      if (preview2) preview2.src = e.target.result;
      if (fileInfo) fileInfo.textContent = 'Selected: ' + file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
      if (btnReset) btnReset.classList.remove('d-none');
    };
    reader.readAsDataURL(file);
  }

  if (avatarFile) {
    avatarFile.addEventListener('change', function(e) {
      handleFile(e.target.files[0]);
    });
  }

  if (btnReset) {
    btnReset.addEventListener('click', function() {
      if (avatarFile) avatarFile.value = '';
      if (preview1) preview1.src = initialAvatarSrc;
      if (preview2) preview2.src = initialAvatarSrc;
      if (fileInfo) fileInfo.textContent = 'Drag & drop an image here or click Browse. Supported formats: JPG, PNG, WebP (maximum 5 MB).';
      btnReset.classList.add('d-none');
    });
  }

  // Drag and drop events
  if (dropzone) {
    ['dragenter', 'dragover'].forEach(function(evt) {
      dropzone.addEventListener(evt, function(e) {
        e.preventDefault();
        e.stopPropagation();
        dropzone.classList.add('dragover');
      });
    });
    ['dragleave', 'drop'].forEach(function(evt) {
      dropzone.addEventListener(evt, function(e) {
        e.preventDefault();
        e.stopPropagation();
        dropzone.classList.remove('dragover');
      });
    });
    dropzone.addEventListener('drop', function(e) {
      if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0]) {
        if (avatarFile) avatarFile.files = e.dataTransfer.files;
        handleFile(e.dataTransfer.files[0]);
      }
    });
  }
});

  // ── Form dirty state & shortcut (Ctrl/Cmd + S) ──
  var profileForm = document.getElementById('hostProfileForm');
  var btnSave = document.getElementById('btnSaveProfile');
  if (profileForm && btnSave) {
    profileForm.addEventListener('input', function() {
      btnSave.classList.add('pulse');
    });
    window.addEventListener('keydown', function(e) {
      if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        var activeTab = document.querySelector('.cp-tab-pane.active');
        if (activeTab && activeTab.id === 'tab-profile') {
          e.preventDefault();
          profileForm.requestSubmit();
        }
      }
    });
  }
})();
</script>
