@extends('layouts.app')
@section('title', 'Verify Security Code')
@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center" style="margin-top:-60px;">
    <div class="w-100" style="max-width: 420px;">
        <div class="text-center mb-4">
            <div class="rounded-3 d-inline-flex align-items-center justify-content-center mb-3" style="width:60px;height:60px;background:linear-gradient(135deg,#f59e0b,#f97316);">
                <i class="bi bi-key text-white fs-2"></i>
            </div>
            <h4 class="fw-bold" style="color:#111827;">Security Code</h4>
            <p class="text-secondary small mb-0">Enter your 8-character security code</p>
        </div>
        <div class="card" style="border-radius:1rem;border:none;box-shadow:0 10px 40px rgba(0,0,0,0.08);">
            <div class="card-body p-4">
                @if($showCode)
                <div class="alert alert-success mb-3">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <strong>Your Security Code (save this!):</strong>
                    </div>
                    <div class="bg-light rounded p-3 text-center">
                        <code class="text-success fs-4 fw-bold" style="letter-spacing:4px;">{{ $showCode }}</code>
                    </div>
                    <small class="d-block mt-2 text-warning">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        This code will only be shown once. Save it securely!
                    </small>
                </div>
                @endif

                @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $errors->first() }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <form method="POST" action="{{ route('admin.security-code.verify.post') }}">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label fw-medium">Security Code</label>
                        <input type="text" name="security_code" class="form-control form-control-lg text-center fw-bold" maxlength="8" pattern="[A-Za-z0-9]{8}" autocomplete="off" autofocus required placeholder="XXXXXXXX" style="letter-spacing:4px;font-size:24px;text-transform:uppercase;">
                    </div>
                    <button type="submit" class="btn btn-warning w-100 py-2 fw-semibold text-dark">
                        <i class="bi bi-shield-check me-1"></i>Complete Login
                    </button>
                </form>
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
