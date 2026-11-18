@extends('layouts.app')

@section('title', 'Create User')

@section('content')
{{-- Top Header & Navigation --}}
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-secondary">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}" class="text-decoration-none text-secondary">Users</a></li>
                <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">Create User</li>
            </ol>
        </nav>
        <h4 class="mb-0 fw-bold text-dark"><i class="bi bi-person-plus-fill me-2 text-primary"></i>Create New User</h4>
        <p class="text-secondary small mb-0">Register a new client, configure MT5 trading accounts, and grant membership badges.</p>
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-2 fw-semibold" style="border-radius:10px;">
        <i class="bi bi-arrow-left me-1"></i>Back to Users
    </a>
</div>

{{-- Global Error Alerts --}}
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius:14px;">
        <div class="d-flex gap-2">
            <i class="bi bi-exclamation-triangle-fill fs-5 text-danger flex-shrink-0"></i>
            <div>
                <strong class="d-block mb-1">Please fix the following errors before submitting:</strong>
                <ul class="mb-0 ps-3 small">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<form method="POST" action="{{ route('admin.users.store') }}" id="createUserForm" autocomplete="off">
    @csrf

    <div class="row g-4">
        {{-- Main Form Container --}}
        <div class="col-12">

            {{-- 1. BASIC USER INFORMATION --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary" style="width:32px;height:32px;">
                        <i class="bi bi-person-vcard fs-6"></i>
                    </span>
                    <h6 class="mb-0 fw-bold text-dark">1. User Information</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        {{-- Full Name --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-dark">
                                Full Name <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-secondary border-end-0">
                                    <i class="bi bi-person"></i>
                                </span>
                                <input type="text" 
                                       name="name" 
                                       id="userNameInput"
                                       class="form-control border-start-0 ps-0 @error('name') is-invalid @enderror" 
                                       placeholder="e.g. John Doe"
                                       value="{{ old('name') }}" 
                                       autocomplete="name"
                                       required 
                                       autofocus>
                            </div>
                            @error('name') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                        </div>

                        {{-- Email Address --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-dark">
                                Email Address <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-secondary border-end-0">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input type="email" 
                                       name="email" 
                                       id="userEmailInput"
                                       class="form-control border-start-0 ps-0 @error('email') is-invalid @enderror" 
                                       placeholder="user@example.com"
                                       value="{{ old('email') }}" 
                                       autocomplete="off"
                                       required>
                            </div>
                            @error('email') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                        </div>

                        {{-- Phone Number (Explicit autocomplete to stop browser email autofill) --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-dark">
                                Phone Number <span class="text-muted fw-normal">(Optional)</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-secondary border-end-0">
                                    <i class="bi bi-telephone"></i>
                                </span>
                                <input type="tel" 
                                       name="phone" 
                                       id="userPhoneInput"
                                       class="form-control border-start-0 ps-0 @error('phone') is-invalid @enderror" 
                                       placeholder="+92 300 1234567"
                                       value="{{ old('phone') }}" 
                                       autocomplete="tel">
                            </div>
                            @error('phone') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                        </div>

                        {{-- Account Status --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-dark">
                                Initial Account Status <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-secondary border-end-0">
                                    <i class="bi bi-shield-check"></i>
                                </span>
                                <select name="status" id="userStatusSelect" class="form-select border-start-0 ps-0 @error('status') is-invalid @enderror" required>
                                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>🟢 Active (Can login & trade)</option>
                                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>⚪ Inactive (Requires activation)</option>
                                    <option value="suspended" {{ old('status') === 'suspended' ? 'selected' : '' }}>🔴 Suspended (Access blocked)</option>
                                </select>
                            </div>
                            @error('status') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. SECURITY & PASSWORD --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-warning-subtle text-warning" style="width:32px;height:32px;">
                            <i class="bi bi-key-fill fs-6 text-dark"></i>
                        </span>
                        <h6 class="mb-0 fw-bold text-dark">2. Security & Credentials</h6>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary fw-semibold" id="generatePasswordBtn" style="border-radius:8px;">
                        <i class="bi bi-shuffle me-1"></i>Generate Strong Password
                    </button>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        {{-- Password --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-dark">
                                Password <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-secondary border-end-0">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input type="password" 
                                       name="password" 
                                       id="passwordInput"
                                       class="form-control border-start-0 border-end-0 ps-0 @error('password') is-invalid @enderror" 
                                       placeholder="••••••••••••"
                                       autocomplete="new-password"
                                       required>
                                <button type="button" class="input-group-text bg-light text-secondary border-start-0 cursor-pointer toggle-pwd" data-target="passwordInput" title="Toggle password visibility">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            @error('password') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                        </div>

                        {{-- Confirm Password --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-dark">
                                Confirm Password <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-secondary border-end-0">
                                    <i class="bi bi-lock-fill"></i>
                                </span>
                                <input type="password" 
                                       name="password_confirmation" 
                                       id="confirmPasswordInput"
                                       class="form-control border-start-0 border-end-0 ps-0" 
                                       placeholder="••••••••••••"
                                       autocomplete="new-password"
                                       required>
                                <button type="button" class="input-group-text bg-light text-secondary border-start-0 cursor-pointer toggle-pwd" data-target="confirmPasswordInput" title="Toggle password visibility">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div id="passwordMatchStatus" class="small mt-1" style="display:none;"></div>
                        </div>

                        {{-- Real-time Password Rules Feedback --}}
                        <div class="col-12">
                            <div class="p-3 bg-light rounded-3 border">
                                <span class="fw-semibold small text-dark d-block mb-2">Password Requirements:</span>
                                <div class="d-flex flex-wrap gap-3 small">
                                    <span id="rule-length" class="text-secondary"><i class="bi bi-circle me-1"></i>At least 8 characters</span>
                                    <span id="rule-upper" class="text-secondary"><i class="bi bi-circle me-1"></i>Uppercase letter (A-Z)</span>
                                    <span id="rule-lower" class="text-secondary"><i class="bi bi-circle me-1"></i>Lowercase letter (a-z)</span>
                                    <span id="rule-number" class="text-secondary"><i class="bi bi-circle me-1"></i>At least one number (0-9)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. PREMIUM MEMBERSHIP SUBSCRIPTION --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-warning-subtle text-warning" style="width:32px;height:32px;">
                            <i class="bi bi-star-fill text-warning fs-6"></i>
                        </span>
                        <div>
                            <h6 class="mb-0 fw-bold text-dark">3. Premium Membership Access</h6>
                            <small class="text-secondary">Enable VIP features, pro signals, and high-frequency trading perks</small>
                        </div>
                    </div>
                    <div class="form-check form-switch m-0 fs-5">
                        <input type="hidden" name="is_premium" value="0">
                        <input type="checkbox" name="is_premium" value="1" class="form-check-input cursor-pointer" id="isPremium" {{ old('is_premium') ? 'checked' : '' }}>
                    </div>
                </div>
                <div class="card-body p-4" id="premiumCardBody">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-5">
                            <label class="form-label fw-semibold small text-dark">Duration (Number of Days)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-secondary"><i class="bi bi-calendar3"></i></span>
                                <input type="number" 
                                       name="premium_days" 
                                       class="form-control" 
                                       value="{{ old('premium_days', 30) }}" 
                                       min="1" 
                                       max="3650" 
                                       id="premiumDays">
                                <span class="input-group-text bg-light text-secondary">Days</span>
                            </div>
                        </div>

                        <div class="col-md-7">
                            <label class="form-label fw-semibold small text-dark d-block">Quick Presets</label>
                            <div class="btn-group w-100" role="group">
                                <button type="button" class="btn btn-outline-secondary btn-sm preset-btn" data-days="30">30 D</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm preset-btn" data-days="90">90 D (Quarter)</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm preset-btn" data-days="180">180 D</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm preset-btn" data-days="365">1 Year</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm preset-btn" data-days="3650">Lifetime</button>
                            </div>
                        </div>

                        <div class="col-12 mt-3">
                            <div class="p-3 rounded-3 d-flex align-items-center justify-content-between flex-wrap gap-2" id="expiryDisplayBox" style="background:#f8fafc; border:1px solid #e2e8f0;">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-clock-history text-secondary fs-5"></i>
                                    <div>
                                        <span class="small text-secondary d-block">Calculated Expiry Date:</span>
                                        <strong class="text-dark" id="premiumExpiryText">Not active</strong>
                                    </div>
                                </div>
                                <span class="badge bg-secondary px-3 py-2" id="premiumStatusBadge">Standard Account</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. VERIFIED STATUS & CHAT BADGES --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success-subtle text-success" style="width:32px;height:32px;">
                            <i class="bi bi-patch-check-fill fs-6"></i>
                        </span>
                        <div>
                            <h6 class="mb-0 fw-bold text-dark">4. Verified Trader Status & Custom Badges</h6>
                            <small class="text-secondary">Visual trust marks shown on Mobile Profile and Live Community Chat</small>
                        </div>
                    </div>
                    <div class="form-check form-switch m-0 fs-5">
                        <input type="hidden" name="is_verified" value="0">
                        <input type="checkbox" name="is_verified" value="1" class="form-check-input cursor-pointer" id="isVerified" {{ old('is_verified') ? 'checked' : '' }}>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 align-items-center">
                        {{-- Badge Label --}}
                        <div class="col-md-5">
                            <label class="form-label fw-semibold small text-dark">Custom Badge Label</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-secondary"><i class="bi bi-tag"></i></span>
                                <input type="text" 
                                       name="chat_badge" 
                                       id="chatBadgeInput" 
                                       class="form-control" 
                                       placeholder="e.g. VIP TRADER, PRO, MASTER" 
                                       value="{{ old('chat_badge') }}" 
                                       maxlength="30">
                            </div>
                            <small class="text-muted">Max 30 characters. Leave blank for default.</small>
                        </div>

                        {{-- Badge Color --}}
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-dark">Badge Accent Color</label>
                            <select name="badge_color" id="badgeColorSelect" class="form-select">
                                <option value="warning" {{ old('badge_color', 'warning') === 'warning' ? 'selected' : '' }}>🟡 Gold / Yellow (VIP / Pro)</option>
                                <option value="success" {{ old('badge_color') === 'success' ? 'selected' : '' }}>🟢 Emerald Green (Verified)</option>
                                <option value="primary" {{ old('badge_color') === 'primary' ? 'selected' : '' }}>🔵 Royal Blue (Official)</option>
                                <option value="danger" {{ old('badge_color') === 'danger' ? 'selected' : '' }}>🔴 Ruby Red (Elite)</option>
                                <option value="info" {{ old('badge_color') === 'info' ? 'selected' : '' }}>⚡ Cyan (Scalper)</option>
                                <option value="secondary" {{ old('badge_color') === 'secondary' ? 'selected' : '' }}>⚪ Slate Gray (Standard)</option>
                            </select>
                        </div>

                        {{-- Live Preview Chip --}}
                        <div class="col-md-3 text-center">
                            <label class="form-label fw-semibold small text-secondary d-block">Live Badge Preview</label>
                            <div class="p-2 rounded-3 bg-light border d-inline-flex align-items-center justify-content-center w-100" style="min-height:42px;">
                                <span id="badgeLivePreview" class="badge bg-warning text-dark px-3 py-2 fw-bold">
                                    <i class="bi bi-patch-check-fill me-1"></i><span id="badgeTextPreview">VIP TRADER</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 5. MT5 TRADING ACCOUNTS (REAL & DEMO) --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-info-subtle text-info" style="width:32px;height:32px;">
                        <i class="bi bi-cpu-fill text-dark fs-6"></i>
                    </span>
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">5. MT5 Trading Accounts (Real & Demo)</h6>
                        <small class="text-secondary">Link user's Exness/broker accounts for bot automated trading</small>
                    </div>
                </div>
                <div class="card-body p-4">
                    {{-- Broker Selector --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold small text-dark">Trading Broker Name</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-secondary"><i class="bi bi-bank"></i></span>
                            <input type="text" 
                                   name="broker_name" 
                                   class="form-control" 
                                   value="{{ old('broker_name', 'Exness') }}" 
                                   placeholder="e.g. Exness, IC Markets, XM">
                        </div>
                    </div>

                    <div class="row g-3">
                        {{-- Real MT5 Account Card --}}
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 h-100" style="background:#f0fdf4; border-color:#bbf7d0 !important;">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <span class="fw-bold text-success"><i class="bi bi-shield-check me-1"></i>Real MT5 Account</span>
                                    <span class="badge bg-success">Live Trading</span>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small text-secondary fw-semibold">Account ID / Number</label>
                                    <input type="text" 
                                           name="real_account_id" 
                                           class="form-control bg-white" 
                                           value="{{ old('real_account_id') }}" 
                                           placeholder="e.g. 47493059">
                                </div>
                                <div>
                                    <label class="form-label small text-secondary fw-semibold">Real MT5 Server</label>
                                    <input type="text" 
                                           name="real_account_server" 
                                           class="form-control bg-white" 
                                           value="{{ old('real_account_server', 'Exness-MT5Real') }}" 
                                           placeholder="e.g. Exness-MT5Real">
                                </div>
                            </div>
                        </div>

                        {{-- Demo MT5 Account Card --}}
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 h-100" style="background:#eff6ff; border-color:#bfdbfe !important;">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <span class="fw-bold text-primary"><i class="bi bi-pc-display-horizontal me-1"></i>Demo MT5 Account</span>
                                    <span class="badge bg-primary">Practice</span>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small text-secondary fw-semibold">Demo Account ID / Number</label>
                                    <input type="text" 
                                           name="demo_account_id" 
                                           class="form-control bg-white" 
                                           value="{{ old('demo_account_id') }}" 
                                           placeholder="e.g. 502354869">
                                </div>
                                <div>
                                    <label class="form-label small text-secondary fw-semibold">Demo MT5 Server</label>
                                    <input type="text" 
                                           name="demo_account_server" 
                                           class="form-control bg-white" 
                                           value="{{ old('demo_account_server', 'Exness-MT5Trial') }}" 
                                           placeholder="e.g. Exness-MT5Trial">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SUBMIT BAR --}}
            <div class="d-flex align-items-center gap-3 p-3 bg-white rounded-3 shadow-sm border mb-5">
                <button type="submit" class="btn btn-primary px-4 py-2 fw-bold" id="submitBtn" style="border-radius:10px;">
                    <i class="bi bi-check-circle-fill me-2"></i>Create User Account
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary px-4 py-2 fw-semibold" style="border-radius:10px;">
                    Cancel
                </a>
            </div>

        </div>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Password Generator
    const generateBtn = document.getElementById('generatePasswordBtn');
    const pwdInput = document.getElementById('passwordInput');
    const confirmInput = document.getElementById('confirmPasswordInput');
    const matchStatus = document.getElementById('passwordMatchStatus');

    function generateRandomPassword() {
        const uppers = "ABCDEFGHJKLMNPQRSTUVWXYZ";
        const lowers = "abcdefghjkmnpqrstuvwxyz";
        const nums = "23456789";
        const specials = "!@#$%&*";

        let pwd = "";
        pwd += uppers.charAt(Math.floor(Math.random() * uppers.length));
        pwd += lowers.charAt(Math.floor(Math.random() * lowers.length));
        pwd += nums.charAt(Math.floor(Math.random() * nums.length));
        pwd += specials.charAt(Math.floor(Math.random() * specials.length));

        const all = uppers + lowers + nums + specials;
        for (let i = 0; i < 8; i++) {
            pwd += all.charAt(Math.floor(Math.random() * all.length));
        }

        // Shuffle
        pwd = pwd.split('').sort(() => 0.5 - Math.random()).join('');
        return pwd;
    }

    if (generateBtn) {
        generateBtn.addEventListener('click', function () {
            const newPwd = generateRandomPassword();
            pwdInput.type = 'text';
            confirmInput.type = 'text';
            pwdInput.value = newPwd;
            confirmInput.value = newPwd;
            validatePassword();
            checkMatch();
        });
    }

    // 2. Toggle Password Visibility
    document.querySelectorAll('.toggle-pwd').forEach(btn => {
        btn.addEventListener('click', function () {
            const targetId = this.dataset.target;
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'bi bi-eye';
            }
        });
    });

    // 3. Password Validation Rules
    function validatePassword() {
        const val = pwdInput.value;
        const hasLength = val.length >= 8;
        const hasUpper = /[A-Z]/.test(val);
        const hasLower = /[a-z]/.test(val);
        const hasNumber = /[0-9]/.test(val);

        updateRule('rule-length', hasLength);
        updateRule('rule-upper', hasUpper);
        updateRule('rule-lower', hasLower);
        updateRule('rule-number', hasNumber);
    }

    function updateRule(elementId, isValid) {
        const el = document.getElementById(elementId);
        if (!el) return;
        if (isValid) {
            el.className = 'text-success fw-bold';
            el.querySelector('i').className = 'bi bi-check-circle-fill me-1';
        } else {
            el.className = 'text-secondary';
            el.querySelector('i').className = 'bi bi-circle me-1';
        }
    }

    function checkMatch() {
        if (!confirmInput.value) {
            matchStatus.style.display = 'none';
            return;
        }
        matchStatus.style.display = 'block';
        if (pwdInput.value === confirmInput.value) {
            matchStatus.className = 'small mt-1 text-success fw-bold';
            matchStatus.innerHTML = '<i class="bi bi-check2-circle me-1"></i>Passwords match perfectly';
        } else {
            matchStatus.className = 'small mt-1 text-danger fw-bold';
            matchStatus.innerHTML = '<i class="bi bi-x-circle me-1"></i>Passwords do not match';
        }
    }

    pwdInput.addEventListener('input', () => { validatePassword(); checkMatch(); });
    confirmInput.addEventListener('input', checkMatch);

    // 4. Premium Subscription Controls & Expiry
    const premiumCheck = document.getElementById('isPremium');
    const premiumDays = document.getElementById('premiumDays');
    const premiumExpiryText = document.getElementById('premiumExpiryText');
    const premiumStatusBadge = document.getElementById('premiumStatusBadge');
    const premiumCardBody = document.getElementById('premiumCardBody');

    function updatePremiumUI() {
        if (!premiumCheck || !premiumDays) return;
        if (premiumCheck.checked) {
            const days = parseInt(premiumDays.value) || 0;
            const targetDate = new Date();
            targetDate.setDate(targetDate.getDate() + days);

            const formattedDate = targetDate.toLocaleDateString('en-US', {
                day: 'numeric',
                month: 'short',
                year: 'numeric'
            });

            if (premiumExpiryText) premiumExpiryText.textContent = `${formattedDate} (${days} days remaining)`;
            if (premiumStatusBadge) {
                premiumStatusBadge.className = 'badge bg-warning text-dark px-3 py-2 fw-bold';
                premiumStatusBadge.innerHTML = '<i class="bi bi-star-fill me-1"></i>VIP Premium Active';
            }
            if (premiumCardBody) premiumCardBody.style.opacity = '1';
        } else {
            if (premiumExpiryText) premiumExpiryText.textContent = 'Not active (Standard client)';
            if (premiumStatusBadge) {
                premiumStatusBadge.className = 'badge bg-secondary px-3 py-2';
                premiumStatusBadge.textContent = 'Standard Account';
            }
        }
    }

    if (premiumCheck) premiumCheck.addEventListener('change', updatePremiumUI);
    if (premiumDays) premiumDays.addEventListener('input', updatePremiumUI);

    document.querySelectorAll('.preset-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            if (premiumDays) premiumDays.value = this.dataset.days;
            if (premiumCheck && !premiumCheck.checked) {
                premiumCheck.checked = true;
            }
            updatePremiumUI();
        });
    });

    // 5. Verified Badge & Chat Role Live Preview
    const isVerifiedCheck = document.getElementById('isVerified');
    const chatBadgeInput = document.getElementById('chatBadgeInput');
    const badgeColorSelect = document.getElementById('badgeColorSelect');
    const badgeLivePreview = document.getElementById('badgeLivePreview');
    const badgeTextPreview = document.getElementById('badgeTextPreview');

    function updateBadgePreview() {
        if (!chatBadgeInput || !badgeTextPreview || !badgeColorSelect || !badgeLivePreview) return;
        const text = chatBadgeInput.value.trim() || 'VERIFIED PRO';
        badgeTextPreview.textContent = text;

        const color = badgeColorSelect.value;
        const colorClasses = {
            'warning': 'badge bg-warning text-dark px-3 py-2 fw-bold',
            'success': 'badge bg-success px-3 py-2 fw-bold',
            'primary': 'badge bg-primary px-3 py-2 fw-bold',
            'danger': 'badge bg-danger px-3 py-2 fw-bold',
            'info': 'badge bg-info text-dark px-3 py-2 fw-bold',
            'secondary': 'badge bg-secondary px-3 py-2 fw-bold'
        };

        badgeLivePreview.className = colorClasses[color] || colorClasses['warning'];
    }

    if (isVerifiedCheck) isVerifiedCheck.addEventListener('change', updateBadgePreview);
    if (chatBadgeInput) chatBadgeInput.addEventListener('input', updateBadgePreview);
    if (badgeColorSelect) badgeColorSelect.addEventListener('change', updateBadgePreview);

    // Initial triggers
    updatePremiumUI();
    updateBadgePreview();
    validatePassword();
});
</script>
@endpush
@endsection
