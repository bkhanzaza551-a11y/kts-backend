@extends('layouts.app')

@section('title', 'Create User')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold"><i class="bi bi-person-plus me-2 text-primary"></i>Create User</h4>
        <small class="text-secondary">Add a new user to the platform</small>
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-person me-2"></i>User Information</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small text-secondary">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') border-danger @enderror" value="{{ old('name') }}" required autofocus>
                            @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-secondary">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') border-danger @enderror" value="{{ old('email') }}" required>
                            @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-secondary">Phone</label>
                            <input type="text" name="phone" class="form-control @error('phone') border-danger @enderror" value="{{ old('phone') }}">
                            @error('phone') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-secondary">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') border-danger @enderror" required>
                                <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="suspended" {{ old('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                            </select>
                            @error('status') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-secondary">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control @error('password') border-danger @enderror" required>
                            @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-secondary">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-star me-2"></i>Premium Subscription</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_premium" value="0">
                                <input type="checkbox" name="is_premium" value="1" class="form-check-input" id="isPremium" {{ old('is_premium') ? 'checked' : '' }}>
                                <label class="form-check-label" for="isPremium">Enable Premium</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-secondary">Premium Duration (days)</label>
                            <input type="number" name="premium_days" class="form-control" value="{{ old('premium_days', 30) }}" min="1" max="3650" id="premiumDays">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-secondary">Expires On</label>
                            <input type="text" class="form-control" id="premiumExpiry" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-check-lg me-1"></i>Create User
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h6 class="text-dark mb-3"><i class="bi bi-info-circle me-2"></i>Password Requirements</h6>
                <ul class="text-secondary small mb-0">
                    <li>Minimum 8 characters</li>
                    <li>At least one uppercase letter</li>
                    <li>At least one lowercase letter</li>
                    <li>At least one number</li>
                </ul>
            </div>
        </div>
    </div>
</div>

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
        } else {
            premiumExpiry.value = 'Not premium';
        }
    }

    premiumCheck.addEventListener('change', updateExpiry);
    premiumDays.addEventListener('input', updateExpiry);
    updateExpiry();
});
</script>
@endpush
@endsection
