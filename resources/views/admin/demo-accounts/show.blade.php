@extends('layouts.app')

@section('title', 'Demo Account Request #' . $demoRequest->id)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold"><i class="bi bi-pc-display-horizontal me-2 text-primary"></i>Demo Account Request #{{ $demoRequest->id }}</h4>
        <small class="text-secondary">Submitted {{ $demoRequest->created_at->diffForHumans() }}</small>
    </div>
    <div class="d-flex gap-2">
        @if($demoRequest->status === 'pending')
        <form method="POST" action="{{ route('admin.demo-accounts.reject', $demoRequest) }}" onsubmit="return confirm('Reject this request?')">
            @csrf
            <input type="hidden" name="admin_notes" value="Rejected by admin">
            <button type="submit" class="btn btn-danger"><i class="bi bi-x-lg me-1"></i>Reject</button>
        </form>
        <form method="POST" action="{{ route('admin.demo-accounts.approve', $demoRequest) }}">
            @csrf
            <div class="input-group">
                <input type="text" name="admin_notes" class="form-control" placeholder="Notes (optional)" style="width:200px;">
                <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Approve</button>
            </div>
        </form>
        @endif
        @if($demoRequest->status === 'approved')
        <form method="POST" action="{{ route('admin.demo-accounts.link', $demoRequest) }}">
            @csrf
            <input type="hidden" name="admin_notes" value="Account linked to user">
            <button type="submit" class="btn btn-info"><i class="bi bi-link-45deg me-1"></i>Mark as Linked</button>
        </form>
        @endif
        <a href="{{ route('admin.demo-accounts.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        {{-- Request Details --}}
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0">Request Details</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-secondary small">Demo Email</label>
                        <div class="fw-semibold text-dark">{{ $demoRequest->demo_email ?? 'Not provided' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-secondary small">Demo Phone</label>
                        <div class="fw-semibold text-dark">{{ $demoRequest->demo_phone ?? 'Not provided' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-secondary small">Exness Account Number</label>
                        <div class="fw-semibold text-dark"><code>{{ $demoRequest->exness_account_number ?? 'Not provided' }}</code></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-secondary small">Account Type</label>
                        <div><span class="badge bg-light text-dark">{{ ucfirst($demoRequest->account_type) }}</span></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-secondary small">Deposit Amount</label>
                        <div class="fw-semibold text-dark">${{ number_format($demoRequest->deposit_amount) }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-secondary small">Status</label>
                        <div>
                            @switch($demoRequest->status)
                                @case('pending')
                                    <span class="badge bg-warning text-dark fs-6">Pending Review</span>
                                    @break
                                @case('approved')
                                    <span class="badge bg-success fs-6">Approved</span>
                                    @break
                                @case('rejected')
                                    <span class="badge bg-danger fs-6">Rejected</span>
                                    @break
                                @case('linked')
                                    <span class="badge bg-info fs-6">Linked & Active</span>
                                    @break
                            @endswitch
                        </div>
                    </div>
                    @if($demoRequest->user_notes)
                    <div class="col-12">
                        <label class="form-label text-secondary small">User Notes</label>
                        <div class="p-3 bg-light rounded">{{ $demoRequest->user_notes }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Admin Notes --}}
        @if($demoRequest->admin_notes)
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0">Admin Notes</h6></div>
            <div class="card-body">
                <div class="p-3 bg-light rounded">{{ $demoRequest->admin_notes }}</div>
                @if($demoRequest->reviewer)
                <small class="text-secondary mt-2 d-block">Reviewed by {{ $demoRequest->reviewer->name }} on {{ $demoRequest->reviewed_at->format('M d, Y H:i') }}</small>
                @endif
            </div>
        </div>
        @endif

        {{-- Exness Instructions --}}
        @php $demoSettings = \App\Models\DemoAccountSetting::getSettings(); @endphp
        <div class="card mb-4">
            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-info-circle me-1"></i>{{ $demoSettings->page_title }}</h6>
                @if($demoSettings->referral_link)
                <a href="{{ $demoSettings->referral_link }}" target="_blank" class="btn btn-sm btn-light"><i class="bi bi-box-arrow-up-right me-1"></i>Open Exness</a>
                @endif
            </div>
            <div class="card-body">
                @if($demoSettings->page_description)
                <p class="text-secondary">{{ $demoSettings->page_description }}</p>
                @endif
                @if(is_array($demoSettings->instructions))
                <ol class="mb-0">
                    @foreach($demoSettings->instructions as $step)
                    <li class="mb-2">
                        <strong>{{ $step['title'] ?? 'Step ' . $step['step'] }}</strong>
                        {{ $step['description'] ?? '' }}
                        @if(!empty($step['url']))
                        <a href="{{ $step['url'] }}" target="_blank" class="text-primary small ms-1"><i class="bi bi-link-45deg"></i></a>
                        @endif
                    </li>
                    @endforeach
                </ol>
                @endif
                <div class="mt-3">
                    <a href="{{ route('admin.demo-settings.index') }}" class="text-secondary small"><i class="bi bi-pencil-square me-1"></i>Edit Settings</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        {{-- User Info --}}
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0">User Info</h6></div>
            <div class="card-body text-center">
                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3" style="width:60px;height:60px;font-size:24px;">
                    {{ strtoupper(substr($demoRequest->user->name ?? 'U', 0, 1)) }}
                </div>
                <h6 class="mb-1">{{ $demoRequest->user->name ?? 'Deleted User' }}</h6>
                <small class="text-secondary">{{ $demoRequest->user->email ?? '' }}</small>
                <div class="mt-2">
                    @if($demoRequest->user)
                    <span class="badge bg-{{ $demoRequest->user->is_premium ? 'warning' : 'secondary' }}">
                        {{ $demoRequest->user->is_premium ? 'Premium' : 'Free' }}
                    </span>
                    @if($demoRequest->user->status !== 'active')
                    <span class="badge bg-danger">{{ ucfirst($demoRequest->user->status) }}</span>
                    @endif
                    @endif
                </div>
            </div>
        </div>

        {{-- Timeline --}}
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0">Timeline</h6></div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item mb-3">
                        <div class="d-flex">
                            <div class="me-3">
                                <div class="rounded-circle bg-primary" style="width:10px;height:10px;margin-top:5px;"></div>
                            </div>
                            <div>
                                <small class="fw-semibold text-dark">Request Submitted</small>
                                <div class="text-secondary small">{{ $demoRequest->created_at->format('M d, Y H:i') }}</div>
                            </div>
                        </div>
                    </div>
                    @if($demoRequest->reviewed_at)
                    <div class="timeline-item">
                        <div class="d-flex">
                            <div class="me-3">
                                <div class="rounded-circle bg-{{ $demoRequest->status === 'approved' ? 'success' : ($demoRequest->status === 'rejected' ? 'danger' : 'info') }}" style="width:10px;height:10px;margin-top:5px;"></div>
                            </div>
                            <div>
                                <small class="fw-semibold text-dark">Reviewed by {{ $demoRequest->reviewer->name ?? 'Admin' }}</small>
                                <div class="text-secondary small">{{ $demoRequest->reviewed_at->format('M d, Y H:i') }}</div>
                                <div class="badge bg-{{ $demoRequest->status === 'approved' ? 'success' : ($demoRequest->status === 'rejected' ? 'danger' : 'info') }} mt-1">{{ ucfirst($demoRequest->status) }}</div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Delete --}}
        <div class="card border-danger">
            <div class="card-body">
                <h6 class="text-danger mb-2">Danger Zone</h6>
                <form method="POST" action="{{ route('admin.demo-accounts.destroy', $demoRequest) }}" onsubmit="return confirm('Are you sure you want to delete this request permanently?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger w-100"><i class="bi bi-trash me-1"></i>Delete Request</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.timeline-item { position: relative; }
.timeline-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 4px;
    top: 20px;
    bottom: -12px;
    width: 2px;
    background: #e5e7eb;
}
</style>
@endsection
