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
