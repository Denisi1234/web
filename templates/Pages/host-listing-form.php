<?php
$this->assign('title', 'Add Property');
$this->assign('portal_title', 'Add property');
$this->assign('page_actions', '<a href="' . $this->Url->build('/host/listings') . '" class="p-btn ghost">Back to listings</a>');
?>
<div class="p-card" style="max-width:720px">
  <h3>New property</h3>
  <div class="sub">Creates a property and submits it for verification. Add rooms next.</div>
  <?= $this->Form->create(null, ['url' => ['action' => 'create'], 'style' => 'display:grid;gap:12px;margin-top:16px']) ?>
    <div>
      <label for="prop-name" style="font-size:12px;font-weight:600;color:var(--p-text-2)">Property name *</label>
      <input id="prop-name" name="name" class="form-control" style="min-height:40px" placeholder="Sunrise Lodge" required>
    </div>
    <div class="row g-2">
      <div class="col-md-6">
        <label for="prop-city" style="font-size:12px;font-weight:600;color:var(--p-text-2)">City</label>
        <input id="prop-city" name="city" class="form-control" style="min-height:40px" placeholder="Dar es Salaam">
      </div>
      <div class="col-md-6">
        <label for="prop-address" style="font-size:12px;font-weight:600;color:var(--p-text-2)">Address</label>
        <input id="prop-address" name="address" class="form-control" style="min-height:40px" placeholder="Plot 123, Njiro Road">
      </div>
    </div>
    <div>
      <label for="prop-price" style="font-size:12px;font-weight:600;color:var(--p-text-2)">Price per night (TSh) *</label>
      <input id="prop-price" name="price_per_night" class="form-control" style="min-height:40px" placeholder="150000" type="number" min="1" required>
    </div>
    <div>
      <label for="prop-desc" style="font-size:12px;font-weight:600;color:var(--p-text-2)">Description</label>
      <textarea id="prop-desc" name="description" class="form-control" style="min-height:80px" placeholder="Describe the property"></textarea>
    </div>
    <div>
      <label for="prop-img" style="font-size:12px;font-weight:600;color:var(--p-text-2)">Cover image URL</label>
      <input id="prop-img" name="image_url" class="form-control" style="min-height:40px" placeholder="https://…">
    </div>
    <button class="p-btn" style="justify-content:center">Create listing</button>
  <?= $this->Form->end() ?>
</div>
