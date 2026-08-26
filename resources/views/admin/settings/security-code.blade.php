@extends('layouts.app')
@section('title', 'Security Code Settings')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold"><i class="bi bi-shield-lock me-2 text-warning"></i>Security Code Settings</h4>
    <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0">Current Security Code Status</h6></div>
            <div class="card-body">
                @if($securityCodes->count() > 0)
                <div class="alert alert-info mb-3">
                    <i class="bi bi-info-circle me-1"></i>You have <strong>{{ $securityCodes->count() }}</strong> active security code(s).
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Label</th><th>Last Used</th><th>Status</th></tr></thead>
                        <tbody>
                            @foreach($securityCodes as $code)
                            <tr>
                                <td class="text-white fw-semibold">{{ $code->label }}</td>
                                <td class="text-secondary">{{ $code->last_used_at ? $code->last_used_at->diffForHumans() : 'Never' }}</td>
                                <td><span class="badge bg-success">Active</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="alert alert-warning mb-0">
                    <i class="bi bi-exclamation-triangle me-1"></i>No active security code found. Please set one up.
                </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Change Security Code</h6></div>
            <div class="card-body">
                <p class="text-secondary small mb-3">To change your security code, you'll need to verify your identity via OTP sent to your email.</p>
                <form method="POST" action="{{ route('admin.security.send-otp') }}">
                    @csrf
                    <button type="submit" class="btn btn-warning w-100 text-dark fw-bold">
                        <i class="bi bi-envelope me-1"></i>Send OTP to Change Code
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
