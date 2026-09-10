<?php
/**
 * Trivago Personal Info Profile Page
 */
?>
<?= $this->Html->css('/assets/css/google-travel-layout.css') ?>
<?= $this->Html->css('/assets/css/google-travel-home.css') ?>
<?= $this->element('navbar'); ?>

<?= $this->Html->css('/assets/css/profile.css'); ?>

<div class="trivago-profile-wrapper">
    <div class="container" style="max-width: 1180px; padding-left: 20px; padding-right: 20px;">
        <div class="row">
            
            <!-- Reusable Left Sidebar -->
            <?= $this->element('profile_sidebar', ['active' => 'personal-info']); ?>

            <!-- Right Content -->
            <div class="col-lg-9 ps-lg-5 trivago-profile-content-col">
                <div class="trivago-profile-header">
                    <h1 class="trivago-profile-title">Personal info</h1>
                    <p class="trivago-profile-sub">Check or change your personal information</p>
                </div>

                <?php
                    $firstName = $userProfile['first_name'] ?? '';
                    $lastName = $userProfile['last_name'] ?? '';
                    $fullName = !empty($userProfile['full_name']) ? $userProfile['full_name'] : (trim($firstName . ' ' . $lastName) ?: ($userProfile['name'] ?? 'Traveler'));
                    $email = $userProfile['email'] ?? '';
                    $phoneNumber = $userProfile['phone_number'] ?? ($userProfile['phone'] ?? '');
                    $dateOfBirth = $userProfile['date_of_birth'] ?? '';
                    $gender = $userProfile['gender'] ?? 'Not set';
                    $address = $userProfile['address'] ?? '';
                    $emergencyContact = $userProfile['emergency_contact'] ?? '';
                    $bio = $userProfile['bio'] ?? '';
                ?>

                <!-- Settings Rows -->
                <div class="trivago-setting-list">
                    
                    <!-- 1. Name Row -->
                    <div class="trivago-setting-row" id="row_name">
                        <div class="trivago-setting-header" onclick="toggleSettingRow('row_name')">
                            <div>
                                <div class="trivago-setting-label">Name</div>
                                <div class="trivago-setting-val" id="display_name"><?= htmlspecialchars($fullName); ?></div>
                            </div>
                            <i class="fa-solid fa-chevron-down trivago-setting-chevron"></i>
                        </div>
                        <div class="trivago-setting-body">
                            <div class="d-flex flex-column gap-3 mb-3" style="max-width: 440px;">
                                <div>
                                    <label class="form-label text-xs fw-bold text-slate-600 mb-1">First name</label>
                                    <input type="text" id="input_first_name" class="trivago-form-input" value="<?= htmlspecialchars($firstName); ?>">
                                </div>
                                <div>
                                    <label class="form-label text-xs fw-bold text-slate-600 mb-1">Last name</label>
                                    <input type="text" id="input_last_name" class="trivago-form-input" value="<?= htmlspecialchars($lastName); ?>">
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="trivago-btn-save" id="btn_save_name" onclick="saveName()">Save</button>
                                <button type="button" class="trivago-btn-cancel" onclick="toggleSettingRow('row_name')">Cancel</button>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Email Address Row -->
                    <div class="trivago-setting-row" id="row_email">
                        <div class="trivago-setting-header" onclick="toggleSettingRow('row_email')">
                            <div>
                                <div class="trivago-setting-label">Email address</div>
                                <div class="trivago-setting-val" id="display_email"><?= htmlspecialchars($email); ?></div>
                            </div>
                            <i class="fa-solid fa-chevron-down trivago-setting-chevron"></i>
                        </div>
                        <div class="trivago-setting-body">
                            <div class="mb-3" style="max-width: 440px;">
                                <label class="form-label text-xs fw-bold text-slate-600 mb-1">Email address</label>
                                <input type="email" id="input_email" class="trivago-form-input" value="<?= htmlspecialchars($email); ?>">
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="trivago-btn-save" id="btn_save_email" onclick="saveEmail()">Save</button>
                                <button type="button" class="trivago-btn-cancel" onclick="toggleSettingRow('row_email')">Cancel</button>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Phone Number Row -->
                    <div class="trivago-setting-row" id="row_phone">
                        <div class="trivago-setting-header" onclick="toggleSettingRow('row_phone')">
                            <div>
                                <div class="trivago-setting-label">Phone number</div>
                                <div class="trivago-setting-val" id="display_phone"><?= htmlspecialchars($phoneNumber); ?></div>
                            </div>
                            <i class="fa-solid fa-chevron-down trivago-setting-chevron"></i>
                        </div>
                        <div class="trivago-setting-body">
                            <div class="mb-3" style="max-width: 440px;">
                                <label class="form-label text-xs fw-bold text-slate-600 mb-1">Phone number</label>
                                <input type="tel" id="input_phone" class="trivago-form-input" value="<?= htmlspecialchars($phoneNumber); ?>">
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="trivago-btn-save" id="btn_save_phone" onclick="savePhone()">Save</button>
                                <button type="button" class="trivago-btn-cancel" onclick="toggleSettingRow('row_phone')">Cancel</button>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Date of Birth Row -->
                    <div class="trivago-setting-row" id="row_dob">
                        <div class="trivago-setting-header" onclick="toggleSettingRow('row_dob')">
                            <div>
                                <div class="trivago-setting-label">Date of birth</div>
                                <div class="trivago-setting-val" id="display_dob"><?= htmlspecialchars($dateOfBirth); ?></div>
                            </div>
                            <i class="fa-solid fa-chevron-down trivago-setting-chevron"></i>
                        </div>
                        <div class="trivago-setting-body">
                            <div class="mb-3" style="max-width: 440px;">
                                <label class="form-label text-xs fw-bold text-slate-600 mb-1">Date of birth</label>
                                <input type="date" id="input_dob" class="trivago-form-input" value="<?= htmlspecialchars($dateOfBirth); ?>">
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="trivago-btn-save" id="btn_save_dob" onclick="saveDob()">Save</button>
                                <button type="button" class="trivago-btn-cancel" onclick="toggleSettingRow('row_dob')">Cancel</button>
                            </div>
                        </div>
                    </div>

                    <!-- 5. Gender Row -->
                    <div class="trivago-setting-row" id="row_gender">
                        <div class="trivago-setting-header" onclick="toggleSettingRow('row_gender')">
                            <div>
                                <div class="trivago-setting-label">Gender</div>
                                <div class="trivago-setting-val" id="display_gender"><?= htmlspecialchars($gender); ?></div>
                            </div>
                            <i class="fa-solid fa-chevron-down trivago-setting-chevron"></i>
                        </div>
                        <div class="trivago-setting-body">
                            <div class="mb-3" style="max-width: 440px;">
                                <label class="form-label text-xs fw-bold text-slate-600 mb-1">Gender</label>
                                <select id="input_gender" class="trivago-form-input" style="height: 48px;">
                                    <option value="Male" <?= $gender === 'Male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?= $gender === 'Female' ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other" <?= $gender === 'Other' ? 'selected' : ''; ?>>Other</option>
                                    <option value="Prefer not to say" <?= $gender === 'Prefer not to say' ? 'selected' : ''; ?>>Prefer not to say</option>
                                </select>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="trivago-btn-save" id="btn_save_gender" onclick="saveGender()">Save</button>
                                <button type="button" class="trivago-btn-cancel" onclick="toggleSettingRow('row_gender')">Cancel</button>
                            </div>
                        </div>
                    </div>

                    <!-- 6. Address Row -->
                    <div class="trivago-setting-row" id="row_address">
                        <div class="trivago-setting-header" onclick="toggleSettingRow('row_address')">
                            <div>
                                <div class="trivago-setting-label">Address</div>
                                <div class="trivago-setting-val" id="display_address"><?= htmlspecialchars($address); ?></div>
                            </div>
                            <i class="fa-solid fa-chevron-down trivago-setting-chevron"></i>
                        </div>
                        <div class="trivago-setting-body">
                            <div class="mb-3" style="max-width: 440px;">
                                <label class="form-label text-xs fw-bold text-slate-600 mb-1">Address / Location</label>
                                <input type="text" id="input_address" class="trivago-form-input" value="<?= htmlspecialchars($address); ?>" placeholder="e.g. Dar es Salaam, Tanzania">
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="trivago-btn-save" id="btn_save_address" onclick="saveAddress()">Save</button>
                                <button type="button" class="trivago-btn-cancel" onclick="toggleSettingRow('row_address')">Cancel</button>
                            </div>
                        </div>
                    </div>

                    <!-- 7. Emergency Contact Row -->
                    <div class="trivago-setting-row" id="row_emergency">
                        <div class="trivago-setting-header" onclick="toggleSettingRow('row_emergency')">
                            <div>
                                <div class="trivago-setting-label">Emergency contact</div>
                                <div class="trivago-setting-val" id="display_emergency"><?= htmlspecialchars($emergencyContact); ?></div>
                            </div>
                            <i class="fa-solid fa-chevron-down trivago-setting-chevron"></i>
                        </div>
                        <div class="trivago-setting-body">
                            <div class="mb-3" style="max-width: 440px;">
                                <label class="form-label text-xs fw-bold text-slate-600 mb-1">Emergency contact number</label>
                                <input type="tel" id="input_emergency" class="trivago-form-input" value="<?= htmlspecialchars($emergencyContact); ?>">
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="trivago-btn-save" id="btn_save_emergency" onclick="saveEmergencyContact()">Save</button>
                                <button type="button" class="trivago-btn-cancel" onclick="toggleSettingRow('row_emergency')">Cancel</button>
                            </div>
                        </div>
                    </div>

                    <!-- 8. Choose Avatar Row -->
                    <div class="trivago-setting-row" id="row_avatar">
                        <div class="trivago-setting-header" onclick="toggleSettingRow('row_avatar')">
                            <div>
                                <div class="trivago-setting-label">Choose avatar</div>
                                <div class="trivago-setting-val">Choose an avatar style that represents you</div>
                            </div>
                            <i class="fa-solid fa-chevron-down trivago-setting-chevron"></i>
                        </div>
                        <div class="trivago-setting-body">
                            <p class="text-sm text-slate-600 mb-3">Select your badge color or avatar style:</p>
                            <div class="d-flex align-items-center gap-3 mb-4">
                                <div class="d-flex align-items-center justify-content-center fw-bold avatar-choice-badge" style="width: 44px; height: 44px; border-radius: 8px; border: 2px solid #007fad; background: #f0f9ff; color: #0284c7; font-size: 18px; cursor: pointer;" onclick="selectAvatarColor(this, '#f0f9ff', '#0284c7')">
                                    <?= htmlspecialchars(strtoupper(substr($firstName, 0, 1))); ?>
                                </div>
                                <div class="d-flex align-items-center justify-content-center fw-bold avatar-choice-badge" style="width: 44px; height: 44px; border-radius: 8px; border: 1.5px solid #cbd5e1; background: #fef2f2; color: #dc2626; font-size: 18px; cursor: pointer;" onclick="selectAvatarColor(this, '#fef2f2', '#dc2626')">
                                    <?= htmlspecialchars(strtoupper(substr($firstName, 0, 1))); ?>
                                </div>
                                <div class="d-flex align-items-center justify-content-center fw-bold avatar-choice-badge" style="width: 44px; height: 44px; border-radius: 8px; border: 1.5px solid #cbd5e1; background: #f0fdf4; color: #16a34a; font-size: 18px; cursor: pointer;" onclick="selectAvatarColor(this, '#f0fdf4', '#16a34a')">
                                    <?= htmlspecialchars(strtoupper(substr($firstName, 0, 1))); ?>
                                </div>
                                <div class="d-flex align-items-center justify-content-center fw-bold avatar-choice-badge" style="width: 44px; height: 44px; border-radius: 8px; border: 1.5px solid #cbd5e1; background: #faf5ff; color: #9333ea; font-size: 18px; cursor: pointer;" onclick="selectAvatarColor(this, '#faf5ff', '#9333ea')">
                                    <?= htmlspecialchars(strtoupper(substr($firstName, 0, 1))); ?>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="trivago-btn-save" onclick="saveAvatar()">Save</button>
                                <button type="button" class="trivago-btn-cancel" onclick="toggleSettingRow('row_avatar')">Cancel</button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<!-- Floating Toast -->
<div id="trivago-toast"></div>

<?= $this->Html->script('/assets/js/profile.js'); ?>
</main>
<?= $this->element('footer', ['skin' => 'skin-light-footer']) ?>
