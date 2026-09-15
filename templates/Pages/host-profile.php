<?php $this->assign('title', 'Host Profile | FastNetStays'); ?>
<?= $this->element('navbar') ?>
<style>.host-input{width:100%;height:44px;border:1px solid #e8eaed;border-radius:12px;padding:0 12px}.host-card{border:1px solid #e8eaed;border-radius:16px;box-shadow:0 6px 16px rgba(0,0,0,.05);background:#fff}</style>
<div class="container py-4" style="max-width:720px">
  <h1 style="font-size:20px;font-weight:800">Host Profile</h1><p style="font-size:12px;color:#5f6368">Port of admin_owner_portal/app-profile.php — GET /me + PUT /user/profile (DataURL avatar)</p>

  <div class="host-card p-3 mb-3 d-flex gap-3 align-items-center flex-wrap">
    <img id="avatarPreview" src="<?= h($me['profile_photo_url'] ?? $me['avatar'] ?? 'https://images.unsplash.com/photo-1633332755192-727a05c4013d?w=120&h=120&fit=crop') ?>" style="width:72px;height:72px;border-radius:9999px;object-fit:cover;border:2px solid #e8eaed">
    <div><div style="font-weight:800"><?= h($me['name'] ?? '—') ?></div><div style="font-size:12px;color:#5f6368"><?= h($me['email'] ?? '') ?> · <?= h($me['phone'] ?? $me['phone_number'] ?? '') ?></div><div style="font-size:11px;color:#5f6368" class="mt-1"><?= (int)($stats['properties'] ?? 0) ?> properties · <?= (int)($stats['rooms'] ?? 0) ?> rooms</div></div>
  </div>

  <?= $this->Form->create(null, ['url'=>['action'=>'profile'], 'type'=>'file']) ?>
  <div class="host-card p-3 d-grid gap-3">
    <div class="row g-2">
      <div class="col-md-6"><label style="font-size:11px;font-weight:700;color:#5f6368">Name</label><input name="name" value="<?= h($me['name'] ?? '') ?>" class="host-input"></div>
      <div class="col-md-6"><label style="font-size:11px;font-weight:700;color:#5f6368">Email</label><input name="email" type="email" value="<?= h($me['email'] ?? '') ?>" class="host-input"></div>
    </div>
    <div class="row g-2">
      <div class="col-md-6"><label style="font-size:11px;font-weight:700;color:#5f6368">Phone</label><input name="phone" value="<?= h($me['phone'] ?? $me['phone_number'] ?? '') ?>" class="host-input"></div>
      <div class="col-md-6"><label style="font-size:11px;font-weight:700;color:#5f6368">Address</label><input name="address" value="<?= h($me['address'] ?? '') ?>" class="host-input"></div>
    </div>
    <div><label style="font-size:11px;font-weight:700;color:#5f6368">Bio</label><textarea name="bio" class="host-input" style="height:80px;padding:10px"><?= h($me['bio'] ?? '') ?></textarea></div>
    <div>
      <label style="font-size:11px;font-weight:700;color:#5f6368">Avatar (upload → DataURL)</label>
      <input type="file" id="avatarFile" accept="image/*" class="form-control" style="border-radius:12px;height:44px">
      <input type="hidden" name="profile_photo_url" id="avatarHidden" value="<?= h($me['profile_photo_url'] ?? '') ?>">
      <small style="font-size:11px;color:#9aa0a6">Preview via FileReader, sends DataURL to PUT /user/profile (app-profile.php parity).</small>
    </div>
    <button class="btn" style="background:#2563EB;color:#fff;border-radius:9999px;padding:12px;font-weight:800">Save Profile</button>
  </div>
  <?= $this->Form->end() ?>
</div>
<script>document.getElementById('avatarFile').addEventListener('change', function(e){ const f=e.target.files[0]; if(!f) return; const r=new FileReader(); r.onload=function(ev){ document.getElementById('avatarPreview').src=ev.target.result; document.getElementById('avatarHidden').value=ev.target.result; }; r.readAsDataURL(f); });</script>
<?= $this->element('footer', ['skin'=>'skin-light-footer']) ?>
