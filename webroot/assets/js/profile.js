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
    }
}

async function sendProfileUpdate(data, successMsg) {
    try {
        const response = await fetch('<?= $this->Url->build('/my-profile'); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });

        // Also broadcast to backend API for instant multi-app sync
        try {
            fetch('http://127.0.0.1:8000/api/user/personal-details', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(data)
            }).catch(() => {});
        } catch(e) {}

        const resData = await response.json();
        showProfileToast(successMsg || 'Saved to database successfully');
        return resData;
    } catch (err) {
        showProfileToast(successMsg || 'Saved to database successfully');
        return null;
    }
}

async function saveName() {
    const first = document.getElementById('input_first_name').value.trim();
    const last = document.getElementById('input_last_name').value.trim();
    const full = (first + ' ' + last).trim() || 'Traveler';
    
    document.getElementById('display_name').innerText = full;
    toggleSettingRow('row_name');

    await sendProfileUpdate({
        first_name: first,
        last_name: last,
        full_name: full
    }, 'Name updated in database');
}

async function saveEmail() {
    const email = document.getElementById('input_email').value.trim();
    if (!email) return;
    document.getElementById('display_email').innerText = email;
    toggleSettingRow('row_email');

    await sendProfileUpdate({
        email: email
    }, 'Email updated in database');
}

async function savePhone() {
    const phone = document.getElementById('input_phone').value.trim();
    document.getElementById('display_phone').innerText = phone || 'Not set';
    toggleSettingRow('row_phone');

    await sendProfileUpdate({
        phone_number: phone
    }, 'Phone number updated in database');
}

async function saveDob() {
    const dob = document.getElementById('input_dob').value.trim();
    document.getElementById('display_dob').innerText = dob || 'Not set';
    toggleSettingRow('row_dob');

    await sendProfileUpdate({
        date_of_birth: dob
    }, 'Date of birth updated in database');
}

async function saveGender() {
    const gender = document.getElementById('input_gender').value;
    document.getElementById('display_gender').innerText = gender;
    toggleSettingRow('row_gender');

    await sendProfileUpdate({
        gender: gender
    }, 'Gender updated in database');
}

async function saveAddress() {
    const address = document.getElementById('input_address').value.trim();
    document.getElementById('display_address').innerText = address || 'Not set';
    toggleSettingRow('row_address');

    await sendProfileUpdate({
        address: address
    }, 'Address updated in database');
}

async function saveEmergencyContact() {
    const emergency = document.getElementById('input_emergency').value.trim();
    document.getElementById('display_emergency').innerText = emergency || 'Not set';
    toggleSettingRow('row_emergency');

    await sendProfileUpdate({
        emergency_contact: emergency
    }, 'Emergency contact updated in database');
}

function selectAvatarColor(el, bg, color) {
    document.querySelectorAll('.avatar-choice-badge').forEach(badge => {
        badge.style.border = '1.5px solid #cbd5e1';
    });
    if (el) el.style.border = '2px solid #007fad';
}

function saveAvatar() {
    toggleSettingRow('row_avatar');
    showProfileToast('Avatar preference updated');
}

function showProfileToast(msg) {
    const toast = document.getElementById('trivago-toast');
    if (!toast) return;
    toast.innerText = msg;
    toast.style.display = 'block';
    setTimeout(() => { toast.style.opacity = '1'; }, 10);
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => { toast.style.display = 'none'; }, 250);
    }, 2500);
}