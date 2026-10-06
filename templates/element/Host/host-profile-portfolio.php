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
