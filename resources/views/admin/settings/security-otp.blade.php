@extends('layouts.app')
@section('title', 'Verify OTP - Security Code Change')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold"><i class="bi bi-envelope-lock me-2 text-primary"></i>Verify OTP</h4>
    <a href="{{ route('admin.security.change-form') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width:60px;height:60px;">
                        <i class="bi bi-envelope text-primary fs-2"></i>
                    </div>
                    <h5 class=" mt-3 mb-1">Enter OTP</h5>
                    <p class="text-secondary small mb-0">Enter the 8-digit code sent to your email</p>
                </div>

                @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-1"></i>{{ $errors->first() }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <form method="POST" action="{{ route('admin.security.verify-otp.post') }}">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label text-secondary">OTP Code</label>
                        <input type="text" name="otp" class="form-control form-control-lg text-center fw-bold" maxlength="8" pattern="[0-9]{8}" inputmode="numeric" autofocus required placeholder="00000000" style="letter-spacing:8px;font-size:24px;">
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="bi bi-check-circle me-1"></i>Verify OTP
                    </button>
                </form>

                <div class="text-center mt-3">
                    <small class="text-secondary">OTP expires in 5 minutes</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
