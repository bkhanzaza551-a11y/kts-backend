@extends('layouts.app')
@section('title', 'System Settings')
@php use App\Models\SystemSetting; @endphp
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h4 class="mb-0 fw-bold"><i class="bi bi-gear me-2 text-primary"></i>System Settings</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.security.change-form') }}" class="btn btn-outline-warning btn-sm"><i class="bi bi-shield-lock me-1"></i>Security Code</a>
        @if(auth()->user()->hasPermission('settings_manage'))
        <a href="{{ route('admin.settings.backups') }}" class="btn btn-outline-info btn-sm"><i class="bi bi-cloud-download me-1"></i>Backups</a>
        <form method="POST" action="{{ route('admin.settings.toggle-maintenance') }}" class="d-inline" onsubmit="return confirm('{{ ($settings['system']['maintenance_mode']->value ?? '0') === '1' ? 'Disable maintenance mode?' : 'Enable maintenance mode? Users will not be able to access the app.' }}')">
            @csrf @method('PATCH')
            <button type="submit" class="btn btn-sm {{ ($settings['system']['maintenance_mode']->value ?? '0') === '1' ? 'btn-success' : 'btn-outline-warning' }}">
                <i class="bi bi-tools me-1"></i>{{ ($settings['system']['maintenance_mode']->value ?? '0') === '1' ? 'Maintenance ON' : 'Maintenance OFF' }}
            </button>
        </form>
        @endif
    </div>
</div>
@if(auth()->user()->hasPermission('settings_manage'))
<form method="POST" action="{{ route('admin.settings.update') }}">
    @csrf @method('PUT')
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header"><h6 class="mb-0">API Keys</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-secondary">Groq API Key</label>
                        <input type="password" name="groq_api_key" class="form-control" value="" placeholder="gsk_...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary">OpenAI API Key</label>
                        <input type="password" name="openai_api_key" class="form-control" value="" placeholder="sk-...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary">Firebase Key</label>
                        <textarea name="firebase_key" class="form-control" rows="3" placeholder="Firebase JSON key..."></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header"><h6 class="mb-0">Payment Gateway</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary">JazzCash Merchant ID</label>
                            <input type="text" name="jazzcash_merchant_id" class="form-control" value="">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">JazzCash Password</label>
                            <input type="password" name="jazzcash_password" class="form-control" value="">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">EasyPaisa Store ID</label>
                            <input type="text" name="easypaisa_store_id" class="form-control" value="">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">EasyPaisa Password</label>
                            <input type="password" name="easypaisa_password" class="form-control" value="">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><h6 class="mb-0">General</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-secondary">App Name</label>
                        <input type="text" name="app_name" class="form-control" value="{{ SystemSetting::getValue('app_name', 'KTS Markets') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary">Support Email</label>
                        <input type="email" name="support_email" class="form-control" value="{{ SystemSetting::getValue('support_email') }}">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="mt-4"><button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-lg me-1"></i>Save Settings</button></div>
</form>
@else
<div class="alert alert-info d-flex align-items-center" role="alert">
    <i class="bi bi-info-circle me-2"></i>
    <span>You have view-only access. Contact an administrator to make changes.</span>
</div>
@endif
@endsection
