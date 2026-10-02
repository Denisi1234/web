<?php
$this->assign('title', 'Add Property');
$this->assign('portal_title', 'Add property');
$this->assign('page_actions', '<a href="' . $this->Url->build('/host/listings') . '" class="p-btn ghost">Back to listings</a>');
?>
<?= $this->element('host_property_css') ?>
<div class="p-card" style="max-width:780px">
  <h3>New property</h3>
  <div class="sub">Creates a property and submits it for verification. Add rooms next.</div>
  <?= $this->Form->create(null, ['url' => ['action' => 'create'], 'class' => 'cds-pform', 'data-api' => 'POST /properties', 'data-api-build' => 'property', 'data-api-ok' => 'Property created and submitted for verification. Add rooms next.', 'data-opt' => 'go', 'data-api-go' => '/host/cache-bust?scope=properties&go=' . urlencode('/host/rooms')]) ?>
    <div class="cds-field">
      <label for="prop-name">Property name <span class="req">*</span></label>
      <input id="prop-name" name="name" class="cds-input" placeholder="Sunrise Lodge" required>
    </div>
    <div class="cds-row">
      <div class="cds-field">
        <label for="prop-city">City</label>
        <input id="prop-city" name="city" class="cds-input" placeholder="Dar es Salaam">
      </div>
      <div class="cds-field">
        <label for="prop-address">Address</label>
        <input id="prop-address" name="address" class="cds-input" placeholder="Plot 123, Njiro Road">
      </div>
    </div>
    <div class="cds-field">
      <label for="prop-price">Price per night (TSh) <span class="req">*</span></label>
      <input id="prop-price" name="price_per_night" class="cds-input" placeholder="150000" type="number" min="1" required>
    </div>
    <div class="cds-field">
      <label for="prop-desc">Description</label>
      <textarea id="prop-desc" name="description" class="cds-input" placeholder="Describe the property"></textarea>
    </div>
    <div class="cds-field">
      <label for="prop-img">Cover image URL</label>
      <input id="prop-img" name="image_url" class="cds-input" placeholder="https://…">
    </div>
    <div class="cds-actions">
      <button class="p-btn">Create listing</button>
    </div>
  <?= $this->Form->end() ?>
</div>
