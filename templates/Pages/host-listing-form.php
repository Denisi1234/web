<?php $this->assign('title','Add Property | FastNetStays'); ?>
<?= $this->element('navbar') ?>
<style>.host-input{width:100%;height:44px;border:1px solid #e8eaed;border-radius:12px;padding:0 12px}.host-card{border:1px solid #e8eaed;border-radius:16px;box-shadow:0 6px 16px rgba(0,0,0,.05)}</style>
<div class="container py-4" style="max-width:720px">
  <h1 style="font-size:20px;font-weight:800">Add New Property</h1><p style="font-size:12px;color:#5f6368">Adapted from admin_owner_portal/add-room.php + mobile add_property.dart — POST /api/properties.</p>
  <?= $this->Form->create(null,['url'=>['action'=>'create']]) ?>
  <div class="host-card p-3 d-grid gap-3">
    <input name="name" class="host-input" placeholder="Property name *" required>
    <input name="city" class="host-input" placeholder="City (e.g. Dar es Salaam)" required>
    <input name="address" class="host-input" placeholder="Address">
    <input name="price_per_night" class="host-input" placeholder="Price per night TSh" type="number" required>
    <textarea name="description" class="host-input" style="height:80px;padding:10px" placeholder="Description"></textarea>
    <input name="image_url" class="host-input" placeholder="Cover image URL (or upload via portal)">
    <button class="btn" style="background:#2563EB;color:#fff;border-radius:30px;padding:12px;font-weight:800">Create Listing</button>
  </div>
  <?= $this->Form->end() ?>
</div>
<?= $this->element('footer',['skin'=>'skin-light-footer']) ?>
