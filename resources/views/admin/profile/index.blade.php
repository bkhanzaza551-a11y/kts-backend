@extends('layouts.app')
@section('title', 'Admin Profile & Security')
@section('content')

{{-- Header --}}
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1 fw-bold text-dark"><i class="bi bi-person-circle me-2 text-primary"></i>My Admin Profile</h4>
        <p class="text-secondary small mb-0">Manage your administrative credentials, personal details, and account security.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.security.change-form') }}" class="btn btn-sm btn-outline-primary fw-semibold">
            <i class="bi bi-shield-lock me-1"></i>Change Security Code
        </a>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-light border fw-semibold">
            <i class="bi bi-arrow-left me-1"></i>Dashboard
        </a>
    </div>
</div>

<div class="row g-4">
    {{-- Left: Profile Overview & Meta --}}
    <div class="col-xl-4 col-lg-5">
        {{-- Profile Summary Card --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
            <div class="card-body p-4 text-center">
                <div class="position-relative d-inline-block mb-3">
                    @if($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="rounded-circle shadow-sm border" style="width: 90px; height: 90px; object-fit: cover;">
                    @else
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold shadow-sm mx-auto" style="width: 90px; height: 90px; font-size: 2rem;">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif
                    <span class="position-absolute bottom-0 end-0 bg-success border border-2 border-white rounded-circle p-2" title="Active Account"></span>
                </div>

                <h5 class="fw-bold text-dark mb-1">{{ $user->name }}</h5>
                <p class="text-secondary small mb-2">{{ $user->email }}</p>

                <div class="d-flex justify-content-center gap-1 flex-wrap mb-3">
                    @foreach($user->roles as $role)
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1" style="font-size: 0.72rem;">
                            <i class="bi bi-shield-check me-1"></i>{{ $role->name }}
                        </span>
                    @endforeach
                </div>

                <hr class="my-3 text-muted">

                <div class="d-flex flex-column gap-2 text-start small">
                    <div class="d-flex justify-content-between">
                        <span class="text-secondary"><i class="bi bi-telephone me-2"></i>Phone:</span>
                        <span class="fw-semibold text-dark">{{ $user->phone ?? 'Not set' }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-secondary"><i class="bi bi-clock-history me-2"></i>Last Login:</span>
                        <span class="text-dark">{{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->diffForHumans() : 'Never' }}</span>
                    </div>
                    @if($user->last_login_ip)
                    <div class="d-flex justify-content-between">
                        <span class="text-secondary"><i class="bi bi-geo me-2"></i>Last IP:</span>
                        <span class="font-monospace text-dark">{{ $user->last_login_ip }}</span>
                    </div>
                    @endif
                    <div class="d-flex justify-content-between">
                        <span class="text-secondary"><i class="bi bi-calendar-check me-2"></i>Joined:</span>
                        <span class="text-dark">{{ $user->created_at->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Security Quick Card --}}
        <div class="card border-0 shadow-sm" style="border-radius: 16px;">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-shield-lock-fill text-primary me-2"></i>Security Safeguards</h6>
            </div>
            <div class="card-body p-4 small">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <div class="fw-semibold text-dark">Email OTP Verification</div>
                        <div class="text-secondary" style="font-size: 0.75rem;">Required upon admin login</div>
                    </div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Enabled</span>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <div class="fw-semibold text-dark">6-Digit Security PIN</div>
                        <div class="text-secondary" style="font-size: 0.75rem;">Protects critical admin actions</div>
                    </div>
                    <a href="{{ route('admin.security.change-form') }}" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 0.75rem;">Update</a>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="fw-semibold text-dark">Super Admin Privileges</div>
                        <div class="text-secondary" style="font-size: 0.75rem;">Full system access & logs</div>
                    </div>
                    <span class="badge bg-dark text-white px-2 py-1">Active</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Right: Profile Edit Form & Password --}}
    <div class="col-xl-8 col-lg-7">
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-pencil-square text-primary me-2"></i>Update Profile Details</h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}" placeholder="+1234567890">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Profile Avatar (Optional)</label>
                            <input type="file" name="avatar" class="form-control @error('avatar') is-invalid @enderror" accept="image/*">
                            <small class="text-muted" style="font-size: 0.75rem;">Max 3MB (JPG, PNG, WebP)</small>
                            @error('avatar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <h6 class="fw-bold text-dark mb-3 pt-2 border-top"><i class="bi bi-key text-primary me-2"></i>Change Password (Optional)</h6>
                    <p class="text-secondary small mb-3">Leave password fields blank if you do not wish to change your current password.</p>

                    <div class="row g-3 mb-4">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Current Password</label>
                            <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" placeholder="Enter current password if changing password">
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">New Password</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Minimum 8 characters">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Confirm New Password</label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Re-type new password">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary px-4 fw-semibold" style="border-radius: 10px;">
                            <i class="bi bi-check2-circle me-1"></i>Save Profile Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
