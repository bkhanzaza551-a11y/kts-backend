@extends('layouts.app')
@section('title', 'Verify OTP')
@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center" style="margin-top:-60px;">
    <div class="w-100" style="max-width: 420px;">
        <div class="text-center mb-4">
            <div class="rounded-3 d-inline-flex align-items-center justify-content-center mb-3" style="width:60px;height:60px;background:linear-gradient(135deg,#4f46e5,#7c3aed);">
                <i class="bi bi-shield-lock text-white fs-2"></i>
            </div>
            <h4 class="fw-bold" style="color:#111827;">Verify OTP</h4>
            <p class="text-secondary small mb-0">Enter the 6-digit code sent to your email</p>
        </div>
        <div class="card" style="border-radius:1rem;border:none;box-shadow:0 10px 40px rgba(0,0,0,0.08);">
            <div class="card-body p-4">
                @if(!empty($latestOtp))
                <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-3" style="background:#fef3c7;border-left:4px solid #f59e0b;border-radius:0.75rem;">
                    <div class="me-3 fs-3 text-warning">
                        <i class="bi bi-key-fill"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-bold text-dark mb-0">Testing Mode OTP Code:</div>
                        <div class="fs-4 text-dark font-monospace fw-bold" style="letter-spacing:4px;">{{ $latestOtp }}</div>
                    </div>
                </div>
                @endif

                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-1"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $errors->first() }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <form method="POST" action="{{ route('admin.otp.verify.post') }}">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label fw-medium">OTP Code</label>
                        <input type="text" name="otp" value="{{ old('otp', $latestOtp ?? '') }}" class="form-control form-control-lg text-center fw-bold" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" autocomplete="one-time-code" autofocus required placeholder="000000" style="letter-spacing:8px;font-size:24px;">
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold" style="background:#4f46e5;border-color:#4f46e5;">
                        <i class="bi bi-check-circle me-1"></i>Verify OTP
                    </button>
                </form>

                <div class="text-center mt-3">
                    <small class="text-secondary d-block mb-2">OTP expires in 5 minutes</small>
                    <form method="POST" action="{{ route('admin.otp.resend') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-clockwise me-1"></i>Resend OTP
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="text-center mt-3">
            <a href="{{ route('admin.login') }}" class="text-secondary text-decoration-none small">
                <i class="bi bi-arrow-left me-1"></i>Back to Login
            </a>
        </div>
    </div>
</div>
@endsection
