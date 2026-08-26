@extends('layouts.app')

@section('title', 'Edit User - ' . $user->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold"><i class="bi bi-pencil me-2 text-warning"></i>Edit User</h4>
        <small class="text-secondary">Editing: {{ $user->name }} ({{ $user->email }})</small>
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf
            @method('PUT')
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-person me-2"></i>User Information</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small text-secondary">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') border-danger @enderror" value="{{ old('name', $user->name) }}" required>
                            @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-secondary">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') border-danger @enderror" value="{{ old('email', $user->email) }}" required>
                            @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-secondary">Phone</label>
                            <input type="text" name="phone" class="form-control @error('phone') border-danger @enderror" value="{{ old('phone', $user->phone) }}">
                            @error('phone') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-secondary">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') border-danger @enderror" required>
                                <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="suspended" {{ old('status', $user->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
                            </select>
                            @error('status') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-secondary">New Password <small class="text-muted">(leave blank to keep current)</small></label>
                            <input type="password" name="password" class="form-control @error('password') border-danger @enderror">
                            @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-secondary">Confirm New Password</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-star me-2"></i>Premium Subscription</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_premium" value="0">
                                <input type="checkbox" name="is_premium" value="1" class="form-check-input" id="isPremium" {{ old('is_premium', $user->is_premium) ? 'checked' : '' }}>
                                <label class="form-check-label" for="isPremium">Premium</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-secondary">Add Days</label>
                            <input type="number" name="premium_days" class="form-control" value="{{ old('premium_days', 30) }}" min="0" max="3650" id="premiumDays">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-secondary">Current Expiry</label>
                            <input type="text" class="form-control" value="{{ $user->premium_expires_at ? $user->premium_expires_at->format('M d, Y') : 'N/A' }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-secondary">New Expiry</label>
                            <input type="text" class="form-control" id="premiumExpiry" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-warning px-4">
                    <i class="bi bi-check-lg me-1"></i>Update User
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Account Info</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-secondary">User ID</small>
                    <div class="text-dark">#{{ $user->id }}</div>
                </div>
                <div class="mb-3">
                    <small class="text-secondary">Created</small>
                    <div class="text-dark">{{ $user->created_at->format('M d, Y h:i A') }}</div>
                </div>
                <div class="mb-3">
                    <small class="text-secondary">Last Login</small>
                    <div class="text-dark">{{ $user->last_login_at?->format('M d, Y h:i A') ?? 'Never' }}</div>
                </div>
                <div class="mb-3">
                    <small class="text-secondary">Last IP</small>
                    <div class="text-dark"><code>{{ $user->last_login_ip ?? 'N/A' }}</code></div>
                </div>
                <div>
                    <small class="text-secondary">Banned</small>
                    <div>
                        @if($user->is_banned)
                        <span class="badge bg-danger">Yes - Banned</span>
                        @else
                        <span class="badge bg-success">No</span>
                        @endif
                    </div>
                </div>
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
            premiumExpiry.value = 'No change';
        }
    }

    premiumCheck.addEventListener('change', updateExpiry);
    premiumDays.addEventListener('input', updateExpiry);
    updateExpiry();
});
</script>
@endpush
@endsection
