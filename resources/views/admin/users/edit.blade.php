@extends('layouts.app')

@section('title', 'Edit User - ' . $user->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 44px; height: 44px; font-size: 1.1rem;">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
        <div>
            <div class="d-flex align-items-center gap-2">
                <h4 class="mb-0 fw-bold text-dark">{{ $user->name }}</h4>
                <span class="badge bg-light text-secondary border">#{{ $user->id }}</span>
                @if($user->is_verified)
                    <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-patch-check-fill me-1"></i>Verified</span>
                @endif
            </div>
            <small class="text-secondary">{{ $user->email }} &middot; Registered {{ $user->created_at->format('M d, Y') }}</small>
        </div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline-primary btn-sm fw-semibold">
            <i class="bi bi-eye me-1"></i>View Profile
        </a>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm fw-semibold">
            <i class="bi bi-arrow-left me-1"></i>Back to Users
        </a>
    </div>
</div>

<form method="POST" action="{{ route('admin.users.update', $user) }}" autocomplete="off">
    @csrf
    @method('PUT')

    <div class="row g-4">
        {{-- Left Column: Editable Form Fields (8 Cols) --}}
        <div class="col-lg-8">
            {{-- Card 1: User Information --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-person-vcard text-primary me-2"></i>Account & Personal Details</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Phone Number</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}" placeholder="+1 234 567 8900" autocomplete="off">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Account Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>Active (Normal Access)</option>
                                <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>Inactive (Pending Activation)</option>
                                <option value="suspended" {{ old('status', $user->status) === 'suspended' ? 'selected' : '' }}>Suspended (Access Blocked)</option>
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12"><hr class="my-2 border-light"></div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">New Password <small class="text-muted fw-normal">(leave blank to keep current)</small></label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="••••••••" autocomplete="new-password">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Confirm New Password</label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="••••••••" autocomplete="new-password">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card 2: Trading Accounts (Real & Demo MT5) --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-graph-up text-primary me-2"></i>MT5 Trading Accounts Configuration</h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-secondary">Broker Name</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-bank"></i></span>
                            <input type="text" name="broker_name" class="form-control @error('broker_name') is-invalid @enderror" value="{{ old('broker_name', $user->broker_name ?: 'Exness') }}" placeholder="e.g. Exness, IC Markets">
                        </div>
                        @error('broker_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 bg-light-subtle h-100">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <div class="rounded p-1 bg-success text-white" style="line-height:1;"><i class="bi bi-shield-check"></i></div>
                                    <h6 class="mb-0 fw-bold text-success">Real MT5 Account</h6>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-secondary">Real Account ID / Login</label>
                                    <input type="text" name="real_account_id" class="form-control @error('real_account_id') is-invalid @enderror" value="{{ old('real_account_id', $user->real_account_id) }}" placeholder="e.g. 98451203">
                                    @error('real_account_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div>
                                    <label class="form-label small fw-semibold text-secondary">Real Server Name</label>
                                    <input type="text" name="real_account_server" class="form-control @error('real_account_server') is-invalid @enderror" value="{{ old('real_account_server', $user->real_account_server ?: 'Exness-MT5Real') }}" placeholder="e.g. Exness-MT5Real">
                                    @error('real_account_server')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 bg-light-subtle h-100">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <div class="rounded p-1 bg-primary text-white" style="line-height:1;"><i class="bi bi-pc-display"></i></div>
                                    <h6 class="mb-0 fw-bold text-primary">Demo MT5 Account</h6>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-secondary">Demo Account ID / Login</label>
                                    <input type="text" name="demo_account_id" class="form-control @error('demo_account_id') is-invalid @enderror" value="{{ old('demo_account_id', $user->demo_account_id) }}" placeholder="e.g. 502354869">
                                    @error('demo_account_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div>
                                    <label class="form-label small fw-semibold text-secondary">Demo Server Name</label>
                                    <input type="text" name="demo_account_server" class="form-control @error('demo_account_server') is-invalid @enderror" value="{{ old('demo_account_server', $user->demo_account_server ?: 'Exness-MT5Trial') }}" placeholder="e.g. Exness-MT5Trial">
                                    @error('demo_account_server')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card 3: Verified Badge & Profile Roles --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-patch-check text-primary me-2"></i>Verification & Chat Badges</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <div class="form-check form-switch mb-1">
                                <input type="hidden" name="is_verified" value="0">
                                <input type="checkbox" name="is_verified" value="1" class="form-check-input" id="isVerified" {{ old('is_verified', $user->is_verified) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold text-dark" for="isVerified">
                                    <i class="bi bi-patch-check-fill text-success me-1"></i>Verified Badge
                                </label>
                            </div>
                            <small class="text-secondary d-block">Shows verified green checkmark on mobile app & chat.</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Custom Badge Title</label>
                            <input type="text" name="chat_badge" class="form-control" placeholder="e.g. VIP TRADER, PRO" value="{{ old('chat_badge', $user->chat_badge) }}" maxlength="50">
                            <small class="text-muted">Displays next to user name in chat rooms.</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Badge Accent Color</label>
                            <select name="badge_color" class="form-select">
                                <option value="warning" {{ old('badge_color', $user->badge_color) === 'warning' ? 'selected' : '' }}>👑 Gold / VIP (Warning)</option>
                                <option value="success" {{ old('badge_color', $user->badge_color) === 'success' ? 'selected' : '' }}>✅ Green / Pro (Success)</option>
                                <option value="primary" {{ old('badge_color', $user->badge_color) === 'primary' ? 'selected' : '' }}>🛡️ Blue / Official (Primary)</option>
                                <option value="danger" {{ old('badge_color', $user->badge_color) === 'danger' ? 'selected' : '' }}>🔥 Red / Elite (Danger)</option>
                                <option value="info" {{ old('badge_color', $user->badge_color) === 'info' ? 'selected' : '' }}>⚡ Cyan / Fast (Info)</option>
                                <option value="secondary" {{ old('badge_color', $user->badge_color) === 'secondary' ? 'selected' : '' }}>Gray (Secondary)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card 4: Premium Subscription Tier --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-star-fill text-warning me-2"></i>Premium Membership Tier</h6>
                    @if($user->is_premium && $user->premium_expires_at && $user->premium_expires_at->isFuture())
                        <span class="badge bg-warning text-dark"><i class="bi bi-clock-history me-1"></i>Active until {{ $user->premium_expires_at->format('M d, Y') }}</span>
                    @endif
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <div class="form-check form-switch mb-1">
                                <input type="hidden" name="is_premium" value="0">
                                <input type="checkbox" name="is_premium" value="1" class="form-check-input" id="isPremium" {{ old('is_premium', $user->is_premium) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold text-dark" for="isPremium">Premium User</label>
                            </div>
                            <small class="text-secondary">Unlocks VIP signals & bots</small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-secondary">Add Days</label>
                            <input type="number" name="premium_days" class="form-control" value="{{ old('premium_days', 30) }}" min="0" max="3650" id="premiumDays">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-secondary">Current Expiry</label>
                            <input type="text" class="form-control bg-light" value="{{ $user->premium_expires_at ? $user->premium_expires_at->format('M d, Y') : 'N/A' }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-secondary">Calculated Expiry</label>
                            <input type="text" class="form-control bg-light fw-bold text-primary" id="premiumExpiry" readonly>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Form Submit Bar --}}
            <div class="d-flex gap-2 justify-content-start align-items-center mb-5">
                <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold" style="border-radius: 10px;">
                    <i class="bi bi-check-lg me-1"></i>Save & Update User
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary px-3 py-2 fw-semibold" style="border-radius: 10px;">
                    Cancel
                </a>
            </div>
        </div>

        {{-- Right Column: User Overview & Timeline Card (4 Cols) --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-info-circle text-primary me-2"></i>User Profile Overview</h6>
                </div>
                <div class="card-body p-4 text-center">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold mx-auto mb-3 shadow-sm" style="width: 72px; height: 72px; font-size: 1.8rem;">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <h5 class="fw-bold text-dark mb-1">{{ $user->name }}</h5>
                    <p class="text-secondary small mb-2">{{ $user->email }}</p>
                    
                    <div class="d-flex justify-content-center gap-2 flex-wrap mb-4">
                        @if($user->is_banned)
                            <span class="badge bg-danger">Banned</span>
                        @elseif($user->status === 'active')
                            <span class="badge bg-success">Active</span>
                        @elseif($user->status === 'suspended')
                            <span class="badge bg-warning text-dark">Suspended</span>
                        @else
                            <span class="badge bg-secondary">{{ ucfirst($user->status) }}</span>
                        @endif

                        @if($user->is_premium)
                            <span class="badge bg-warning text-dark"><i class="bi bi-star-fill me-1"></i>Premium</span>
                        @else
                            <span class="badge bg-light text-secondary border">Free Plan</span>
                        @endif
                    </div>

                    <div class="text-start border-top pt-3">
                        <div class="row g-3">
                            <div class="col-6">
                                <small class="text-secondary d-block fw-semibold" style="font-size: 0.72rem;">USER ID</small>
                                <span class="fw-bold text-dark">#{{ $user->id }}</span>
                            </div>
                            <div class="col-6">
                                <small class="text-secondary d-block fw-semibold" style="font-size: 0.72rem;">ROLES</small>
                                <span class="fw-bold text-dark">{{ $user->roles->pluck('name')->first() ?? 'User' }}</span>
                            </div>
                            <div class="col-12">
                                <small class="text-secondary d-block fw-semibold" style="font-size: 0.72rem;">JOINED DATE</small>
                                <span class="text-dark">{{ $user->created_at->format('M d, Y h:i A') }}</span>
                            </div>
                            <div class="col-12">
                                <small class="text-secondary d-block fw-semibold" style="font-size: 0.72rem;">LAST LOGIN</small>
                                <span class="text-dark">
                                    @if($user->last_login_at)
                                        <i class="bi bi-circle-fill text-success me-1" style="font-size: 0.5rem;"></i>{{ $user->last_login_at->diffForHumans() }}
                                    @else
                                        <span class="text-muted fst-italic">Never logged in</span>
                                    @endif
                                </span>
                            </div>
                            <div class="col-12">
                                <small class="text-secondary d-block fw-semibold" style="font-size: 0.72rem;">LAST IP ADDRESS</small>
                                <code class="text-dark px-2 py-1 bg-light rounded border small">{{ $user->last_login_ip ?? '127.0.0.1' }}</code>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick Links Card --}}
            <div class="card border-0 shadow-sm" style="border-radius: 14px;">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-lightning-charge text-primary me-2"></i>Quick Actions</h6>
                </div>
                <div class="card-body p-3">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline-primary btn-sm py-2 text-start">
                            <i class="bi bi-person-bounding-box me-2"></i>View Full User Profile
                        </a>
                        <a href="{{ route('admin.audit-logs.index', ['user_id' => $user->id]) }}" class="btn btn-outline-secondary btn-sm py-2 text-start">
                            <i class="bi bi-journal-text me-2"></i>View User Activity Logs
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const premiumCheck = document.getElementById('isPremium');
    const premiumDays = document.getElementById('premiumDays');
    const premiumExpiry = document.getElementById('premiumExpiry');

    function updateExpiry() {
        if (premiumCheck.checked && premiumDays.value > 0) {
            const d = new Date();
            d.setDate(d.getDate() + parseInt(premiumDays.value));
            premiumExpiry.value = d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        } else if (!premiumCheck.checked) {
            premiumExpiry.value = 'Removed (Free)';
        } else {
            premiumExpiry.value = 'No change';
        }
    }

    premiumCheck?.addEventListener('change', updateExpiry);
    premiumDays?.addEventListener('input', updateExpiry);
    updateExpiry();
});
</script>
@endpush
@endsection

