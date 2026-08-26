@extends('layouts.app')
@section('title', 'Set New Security Code')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold"><i class="bi bi-key me-2 text-warning"></i>Set New Security Code</h4>
    <a href="{{ route('admin.security.change-form') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width:60px;height:60px;">
                        <i class="bi bi-key text-warning fs-2"></i>
                    </div>
                    <h5 class=" mt-3 mb-1">Change Security Code</h5>
                    <p class="text-secondary small mb-0">Enter your current and new security codes</p>
                </div>

                @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-1"></i>{{ $errors->first() }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <form method="POST" action="{{ route('admin.security.update-code') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label text-secondary">Current Security Code</label>
                        <input type="text" name="current_security_code" class="form-control text-center fw-bold" maxlength="8" pattern="[A-Za-z0-9]{8}" required placeholder="XXXXXXXX" style="letter-spacing:4px;text-transform:uppercase;">
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label class="form-label text-secondary">New Security Code</label>
                        <input type="text" name="new_security_code" class="form-control text-center fw-bold" maxlength="8" pattern="[A-Za-z0-9]{8}" required placeholder="XXXXXXXX" style="letter-spacing:4px;text-transform:uppercase;">
                        <small class="text-secondary">8 characters (letters and numbers only)</small>
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-secondary">Confirm New Security Code</label>
                        <input type="text" name="confirm_security_code" class="form-control text-center fw-bold" maxlength="8" pattern="[A-Za-z0-9]{8}" required placeholder="XXXXXXXX" style="letter-spacing:4px;text-transform:uppercase;">
                    </div>
                    <button type="submit" class="btn btn-warning btn-lg w-100 text-dark fw-bold" onclick="return confirm('Are you sure you want to change your security code?')">
                        <i class="bi bi-shield-check me-1"></i>Update Security Code
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
