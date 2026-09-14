<?php
$this->assign('title', 'Account security - FastNet Stays');
?>
<?= $this->Html->css('/assets/css/google-travel-layout.css') ?>
<?= $this->Html->css('/assets/css/google-travel-home.css') ?>
<?= $this->element('navbar'); ?>
<?= $this->Html->css('/assets/css/profile.css'); ?>
<div class="trivago-profile-wrapper">
    <div class="container" style="max-width: 1120px;">
        <div class="row">
            <?= $this->element('profile_sidebar', ['active' => 'security']); ?>
            <div class="col-lg-9 ps-lg-4">
                <div class="trivago-profile-header">
                    <h1 class="trivago-profile-title">Account security</h1>
                    <p class="trivago-profile-sub">Manage your password and account</p>
                </div>
                <div id="sec-alert"></div>
                <!-- Password -->
                <div class="trivago-setting-list">
                    <div class="trivago-setting-row open" id="row_password">
                        <div class="trivago-setting-header" onclick="toggleSettingRow('row_password')">
                            <div>
                                <div class="trivago-setting-label">Password</div>
                                <div class="trivago-setting-val">Last changed <?= h($userProfile['password_updated'] ?? '—') ?></div>
                            </div>
                            <i class="fa-solid fa-chevron-down trivago-setting-chevron"></i>
                        </div>
                        <div class="trivago-setting-body" style="display:block">
                            <form id="sec-pass-form" style="max-width:440px" onsubmit="return handlePasswordChange(event)">
                                <div class="mb-3">
                                    <label class="form-label text-xs fw-bold text-slate-600 mb-1">Current password</label>
                                    <input type="password" id="sec_cur" class="trivago-form-input" required autocomplete="current-password">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-xs fw-bold text-slate-600 mb-1">New password (min 8)</label>
                                    <input type="password" id="sec_new" class="trivago-form-input" required autocomplete="new-password">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-xs fw-bold text-slate-600 mb-1">Confirm new password</label>
                                    <input type="password" id="sec_cfm" class="trivago-form-input" required autocomplete="new-password">
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="trivago-btn-save">Update password</button>
                                    <button type="button" class="trivago-btn-cancel" onclick="toggleSettingRow('row_password')">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- Delete -->
                    <div class="trivago-setting-row" id="row_delete">
                        <div class="trivago-setting-header" onclick="toggleSettingRow('row_delete')">
                            <div>
                                <div class="trivago-setting-label" style="color:#dc2626">Delete account</div>
                                <div class="trivago-setting-val">Permanently delete your account</div>
                            </div>
                            <i class="fa-solid fa-chevron-down trivago-setting-chevron"></i>
                        </div>
                        <div class="trivago-setting-body">
                            <div class="trivago-loss-alert">
                                <div class="trivago-loss-title"><i class="fa-solid fa-triangle-exclamation"></i><span>You will permanently lose:</span></div>
                                <ul class="trivago-loss-list">
                                    <li>All stored payment methods</li>
                                    <li>Newsletter subscription</li>
                                    <li>Access to member deals & favourites</li>
                                </ul>
                            </div>
                            <div class="d-flex justify-content-end mt-3">
                                <button type="button" class="trivago-btn-delete" onclick="handleDeleteAccount()">Delete my account</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="trivago-toast"></div>
<script>
function toggleSettingRow(id){const r=document.getElementById(id);if(!r)return;const o=r.classList.contains('open');document.querySelectorAll('.trivago-setting-row').forEach(x=>{if(x.id!==id)x.classList.remove('open')});if(o)r.classList.remove('open');else r.classList.add('open');}
function showSecToast(m,err){const t=document.getElementById('trivago-toast')||document.getElementById('sec-alert');if(!t)return;const el=document.getElementById('sec-alert');if(el){el.innerHTML='<div class="alert '+(err?'alert-danger':'alert-success')+' py-2">'+m+'</div>'; setTimeout(()=>el.innerHTML='',3000);}const toast=document.getElementById('trivago-toast');if(toast){toast.innerText=m;toast.style.display='block';setTimeout(()=>toast.style.opacity='1',10);setTimeout(()=>{toast.style.opacity='0';setTimeout(()=>toast.style.display='none',250)},2500);}}
async function handlePasswordChange(e){
  e.preventDefault();
  const cur=document.getElementById('sec_cur').value, nw=document.getElementById('sec_new').value, cf=document.getElementById('sec_cfm').value;
  if(nw.length<8){showSecToast('New password min 8 characters',true);return false;}
  if(nw!==cf){showSecToast('New passwords do not match',true);return false;}
  const csrf=document.querySelector('meta[name="csrfToken"]')?.content||'';
  try{
    const r=await fetch('/security',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':csrf,'Accept':'application/json'},credentials:'same-origin',body:JSON.stringify({current_password:cur,new_password:nw,confirm_password:cf})});
    const d=await r.json().catch(()=>({}));
    if(!r.ok) throw new Error(d.message||'Failed');
    showSecToast(d.message||'Password updated');
    e.target.reset();
  }catch(err){showSecToast(err.message||'Failed',true);}
  return false;
}
async function handleDeleteAccount(){
  if(!confirm('Permanently delete your account? This cannot be undone.')) return;
  const csrf=document.querySelector('meta[name="csrfToken"]')?.content||'';
  try{
    const r=await fetch('/security',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':csrf},credentials:'same-origin',body:JSON.stringify({action:'delete'})});
    const d=await r.json().catch(()=>({}));
    if(!r.ok) throw new Error(d.message||'Delete failed');
  }catch(e){}
  try{localStorage.clear();}catch(e){}
  document.cookie='CAKEPHP=; Max-Age=0; path=/';
  showSecToast('Account deleted — signing out');
  setTimeout(()=>window.location.href='/',1500);
}
</script>
