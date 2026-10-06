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
<?= $this->Html->css('/assets/css/carbon-profile-01.css?v=' . filemtime(WWW_ROOT . 'assets/css/carbon-profile-01.css')) ?>
<?= $this->Html->css('/assets/css/carbon-profile-02.css?v=' . filemtime(WWW_ROOT . 'assets/css/carbon-profile-02.css')) ?>
<?= $this->Html->css('/assets/css/carbon-profile-03.css?v=' . filemtime(WWW_ROOT . 'assets/css/carbon-profile-03.css')) ?>

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
<?= $this->element('Host/host-profile-form', ['name' => $name ?? '', 'email' => $email ?? '', 'phone' => $phone ?? '', 'address' => $address ?? '', 'avatarUrl' => $avatarUrl ?? null, 'avatarInitial' => $avatarInitial ?? '']) ?>

<?= $this->element('Host/host-profile-portfolio', ['propertiesCount' => $propertiesCount ?? 0, 'roomsCount' => $roomsCount ?? 0, 'verificationStatus' => $verificationStatus ?? null]) ?>

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