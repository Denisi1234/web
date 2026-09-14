/** fastnetstays.com - Profile & Account Settings Controller */

function toggleSettingRow(rowId) {
    const row = document.getElementById(rowId);
    if (!row) return;
    const wasOpen = row.classList.contains('open');
    
    // Close other rows
    document.querySelectorAll('.trivago-setting-row').forEach(r => {
        if (r.id !== rowId) r.classList.remove('open');
    });

    if (wasOpen) {
        row.classList.remove('open');
    } else {
        row.classList.add('open');
        // Auto-focus first input
        const firstInput = row.querySelector('input, select, textarea');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 150);
        }
    }
}

async function sendProfileUpdate(data, successMsg) {
    try {
        if (window.FastnetLoader && window.FastnetLoader.bar) {
            window.FastnetLoader.bar.start();
        }

        const csrf = document.querySelector('meta[name="csrfToken"]')?.content || '';
        const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token') || '';

        const headers = {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-Token': csrf
        };
        if (token) {
            headers['Authorization'] = 'Bearer ' + token;
        }

        // 1. Post to CakePHP web frontend session & state
        const response = await fetch('/my-profile', {
            method: 'POST',
            headers: headers,
            credentials: 'same-origin',
            body: JSON.stringify(data)
        });
        const resData = await response.json().catch(() => ({}));

        // 2. Also direct-sync to backend REST API if token available
        if (token) {
            try {
                const apiHost = (typeof window.FASTNET_API_URL === 'string' && window.FASTNET_API_URL)
                    ? window.FASTNET_API_URL
                    : (window.location.protocol + '//' + window.location.hostname + ':8000');
                
                await fetch(apiHost + '/api/user/personal-details', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': 'Bearer ' + token
                    },
                    body: JSON.stringify(data)
                });
            } catch (err) {
                console.warn('Direct backend personal details sync note:', err);
            }
        }

        if (window.FastnetLoader && window.FastnetLoader.bar) {
            window.FastnetLoader.bar.done();
        }

        showProfileToast(successMsg || 'Saved successfully');
        return resData.details || resData;
    } catch (err) {
        if (window.FastnetLoader && window.FastnetLoader.bar) {
            window.FastnetLoader.bar.done();
        }
        showProfileToast(err.message || 'Save failed. Please check connection.');
        throw err;
    }
}

async function saveName() {
    const first = document.getElementById('input_first_name').value.trim();
    const last = document.getElementById('input_last_name').value.trim();
    const full = (first + ' ' + last).trim() || 'Traveler';
    
    document.getElementById('display_name').innerText = full;
    toggleSettingRow('row_name');

    // Update header name if present
    const navName = document.querySelector('.nav-user-name');
    if (navName) navName.innerText = full;

    await sendProfileUpdate({
        first_name: first,
        last_name: last,
        full_name: full,
        name: full
    }, 'Name updated successfully');
}

async function saveEmail() {
    const email = document.getElementById('input_email').value.trim();
    if (!email) {
        showProfileToast('Please enter a valid email address');
        return;
    }
    document.getElementById('display_email').innerText = email;
    toggleSettingRow('row_email');

    await sendProfileUpdate({
        email: email
    }, 'Email updated successfully');
}

async function savePhone() {
    const phone = document.getElementById('input_phone').value.trim();
    document.getElementById('display_phone').innerText = phone || 'Not set';
    toggleSettingRow('row_phone');

    await sendProfileUpdate({
        phone_number: phone,
        phone: phone
    }, 'Phone number updated successfully');
}

async function saveDob() {
    const dob = document.getElementById('input_dob').value.trim();
    document.getElementById('display_dob').innerText = dob || 'Not set';
    toggleSettingRow('row_dob');

    await sendProfileUpdate({
        date_of_birth: dob
    }, 'Date of birth updated successfully');
}

async function saveGender() {
    const gender = document.getElementById('input_gender').value;
    document.getElementById('display_gender').innerText = gender;
    toggleSettingRow('row_gender');

    await sendProfileUpdate({
        gender: gender
    }, 'Gender updated successfully');
}

async function saveAddress() {
    const address = document.getElementById('input_address').value.trim();
    document.getElementById('display_address').innerText = address || 'Not set';
    toggleSettingRow('row_address');

    await sendProfileUpdate({
        address: address
    }, 'Address updated successfully');
}

async function saveEmergencyContact() {
    const emergency = document.getElementById('input_emergency').value.trim();
    document.getElementById('display_emergency').innerText = emergency || 'Not set';
    toggleSettingRow('row_emergency');

    await sendProfileUpdate({
        emergency_contact: emergency
    }, 'Emergency contact updated successfully');
}

let _selectedAvatar = null;
function selectAvatarColor(el, bg, color) {
    document.querySelectorAll('.avatar-choice-badge').forEach(badge => {
        badge.style.border = '1.5px solid #cbd5e1';
    });
    if (el) el.style.border = '2px solid #007fad';
    _selectedAvatar = { bg, color };
}

async function saveAvatar() {
    if (_selectedAvatar) {
        await sendProfileUpdate({ 
            avatar_bg: _selectedAvatar.bg, 
            avatar_color: _selectedAvatar.color, 
            avatar: _selectedAvatar.bg 
        }, 'Avatar style updated');
        
        // instantly update header avatar without reload
        const hdrBtn = document.getElementById('nav_user_btn');
        if (hdrBtn) {
            hdrBtn.style.backgroundColor = _selectedAvatar.bg;
            hdrBtn.style.color = _selectedAvatar.color;
            hdrBtn.style.borderColor = _selectedAvatar.color;
        }
        try { 
            localStorage.setItem('nav_avatar_bg', _selectedAvatar.bg); 
            localStorage.setItem('nav_avatar_color', _selectedAvatar.color); 
        } catch(e){}
    }
    toggleSettingRow('row_avatar');
    showProfileToast('Avatar updated successfully');
}

// Restore header avatar from localStorage on load
try {
    const bg = localStorage.getItem('nav_avatar_bg'), col = localStorage.getItem('nav_avatar_color');
    if (bg && col) {
        const b = document.getElementById('nav_user_btn');
        if (b) { 
            b.style.backgroundColor = bg; 
            b.style.color = col; 
            b.style.borderColor = col; 
        }
    }
} catch(e){}

function showProfileToast(msg) {
    let toast = document.getElementById('trivago-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'trivago-toast';
        document.body.appendChild(toast);
    }
    toast.innerText = msg;
    toast.style.display = 'block';
    setTimeout(() => { toast.style.opacity = '1'; }, 10);
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => { toast.style.display = 'none'; }, 250);
    }, 2800);
}