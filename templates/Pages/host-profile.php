<?php
$this->assign('title', 'Host Profile');
$this->assign('portal_title', 'Profile');
?>
<div class="p-card mb-3" style="max-width:720px">
  <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
    <img id="avatarPreview" src="<?= h($me['profile_photo_url'] ?? $me['avatar'] ?? 'https://images.unsplash.com/photo-1633332755192-727a05c4013d?w=120&h=120&fit=crop') ?>" alt="Avatar" style="width:64px;height:64px;border-radius:50%;object-fit:cover;border:1px solid var(--p-border)">
    <div>
      <div style="font-size:16px;font-weight:600"><?= h($me['name'] ?? '—') ?></div>
      <div style="font-size:13px;color:var(--p-text-2)"><?= h($me['email'] ?? '') ?> · <?= h($me['phone'] ?? $me['phone_number'] ?? '') ?></div>
      <div style="font-size:12px;color:var(--p-text-2);margin-top:2px"><?= (int)($stats['properties'] ?? 0) ?> properties · <?= (int)($stats['rooms'] ?? 0) ?> rooms</div>
    </div>
  </div>
</div>

<div class="p-card" style="max-width:720px">
  <h3>Edit profile</h3>
  <div class="sub">Syncs via PUT /user/profile.</div>
  <?= $this->Form->create(null, ['url' => ['action' => 'profile'], 'type' => 'file', 'style' => 'display:grid;gap:12px;margin-top:16px']) ?>
    <div class="row g-2">
      <div class="col-md-6"><label style="font-size:12px;font-weight:600;color:var(--p-text-2)">Name</label><input name="name" value="<?= h($me['name'] ?? '') ?>" class="form-control" style="min-height:40px"></div>
      <div class="col-md-6"><label style="font-size:12px;font-weight:600;color:var(--p-text-2)">Email</label><input name="email" type="email" value="<?= h($me['email'] ?? '') ?>" class="form-control" style="min-height:40px"></div>
    </div>
    <div class="row g-2">
      <div class="col-md-6"><label style="font-size:12px;font-weight:600;color:var(--p-text-2)">Phone</label><input name="phone" value="<?= h($me['phone'] ?? $me['phone_number'] ?? '') ?>" class="form-control" style="min-height:40px"></div>
      <div class="col-md-6"><label style="font-size:12px;font-weight:600;color:var(--p-text-2)">Address</label><input name="address" value="<?= h($me['address'] ?? '') ?>" class="form-control" style="min-height:40px"></div>
    </div>
    <div><label style="font-size:12px;font-weight:600;color:var(--p-text-2)">Bio</label><textarea name="bio" class="form-control" style="min-height:80px"><?= h($me['bio'] ?? '') ?></textarea></div>
    <div>
      <label style="font-size:12px;font-weight:600;color:var(--p-text-2)">Avatar (upload → preview)</label>
      <input type="file" id="avatarFile" accept="image/*" class="form-control" style="min-height:40px">
      <input type="hidden" name="profile_photo_url" id="avatarHidden" value="<?= h($me['profile_photo_url'] ?? '') ?>">
    </div>
    <button class="p-btn" style="justify-content:center">Save profile</button>
  <?= $this->Form->end() ?>
</div>
<script>document.getElementById('avatarFile').addEventListener('change', function(e){ const f=e.target.files[0]; if(!f) return; const r=new FileReader(); r.onload=function(ev){ document.getElementById('avatarPreview').src=ev.target.result; document.getElementById('avatarHidden').value=ev.target.result; }; r.readAsDataURL(f); });</script>
