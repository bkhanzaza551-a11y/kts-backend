@extends('layouts.app')

@section('title', 'Demo Account Settings')

@section('content')
<div class="container-fluid px-0">
    {{-- Header Section --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="p-2.5 rounded-3 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                <i class="bi bi-sliders2-vertical fs-4"></i>
            </div>
            <div>
                <h4 class="mb-1 fw-bold text-dark">Demo Account Settings</h4>
                <p class="text-secondary mb-0 small">Configure Exness broker link, onboarding guide steps, account tiers, and initial demo balances.</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.demo-accounts.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5" style="border-radius:8px;">
                <i class="bi bi-arrow-left"></i> Back to Requests
            </a>
            <button type="button" onclick="document.getElementById('demoSettingsForm').submit()" class="btn btn-primary btn-sm px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm" style="border-radius:8px;">
                <i class="bi bi-check2-circle"></i> Save Settings
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4 shadow-sm" role="alert" style="border-radius:10px;">
        <i class="bi bi-check-circle-fill fs-5"></i>
        <div class="flex-grow-1 fw-medium">{{ session('success') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-4 shadow-sm" role="alert" style="border-radius:10px;">
        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
        <div class="flex-grow-1">
            <strong>Please check the following errors:</strong>
            <ul class="mb-0 mt-1 ps-3">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.demo-settings.update') }}" id="demoSettingsForm">
        @csrf
        @method('PUT')

        {{-- 1. Basic & Broker Settings --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius:12px; overflow:hidden;">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <span class="p-1.5 rounded-2 bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center" style="width:32px;height:32px;">
                        <i class="bi bi-link-45deg fs-5"></i>
                    </span>
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">Basic Broker & Page Settings</h6>
                        <small class="text-muted">Primary referral affiliate link and guide introduction shown in the mobile app</small>
                    </div>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold text-dark small mb-1.5">
                            Exness Referral Link <span class="badge bg-primary-subtle text-primary ms-1" style="font-size:0.7rem;">Affiliate / Partner URL</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted px-3">
                                <i class="bi bi-globe2 text-primary"></i>
                            </span>
                            <input type="url" name="referral_link" class="form-control border-start-0 ps-1 @error('referral_link') is-invalid @enderror" value="{{ old('referral_link', $settings->referral_link) }}" placeholder="https://one.exnesstrack.net/a/... or https://www.exness.com/register/">
                        </div>
                        <div class="form-text d-flex align-items-center gap-1.5 mt-1.5 text-muted small">
                            <i class="bi bi-info-circle text-primary"></i> Users will be redirected to this link when they click <strong>"Open Exness"</strong> inside the mobile app.
                        </div>
                        @error('referral_link')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark small mb-1.5">
                            Page Title <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted px-3">
                                <i class="bi bi-type-h1"></i>
                            </span>
                            <input type="text" name="page_title" class="form-control border-start-0 ps-1 @error('page_title') is-invalid @enderror" value="{{ old('page_title', $settings->page_title) }}" placeholder="e.g. How to Create Exness Demo Account" required>
                        </div>
                        @error('page_title')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark small mb-1.5">
                            Page Subtitle / Tagline
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted px-3">
                                <i class="bi bi-chat-left-quote"></i>
                            </span>
                            <input type="text" name="page_description" class="form-control border-start-0 ps-1 @error('page_description') is-invalid @enderror" value="{{ old('page_description', $settings->page_description) }}" placeholder="e.g. Follow these simple steps to create your Exness demo account and start trading with KTS Markets.">
                        </div>
                        @error('page_description')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Instruction Steps --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius:12px; overflow:hidden;">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="p-1.5 rounded-2 bg-success-subtle text-success d-inline-flex align-items-center justify-content-center" style="width:32px;height:32px;">
                        <i class="bi bi-list-ol fs-5"></i>
                    </span>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h6 class="mb-0 fw-bold text-dark">Mobile Onboarding Steps</h6>
                            <span class="badge bg-light text-secondary border px-2 py-0.5 rounded-pill" id="stepsCountBadge" style="font-size:0.75rem;">0 Steps</span>
                        </div>
                        <small class="text-muted">Sequential step-by-step instructions shown in the mobile app onboarding card</small>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1.5 px-3 py-1.5 shadow-sm fw-semibold" onclick="addInstruction()" style="border-radius:8px;">
                    <i class="bi bi-plus-circle"></i> Add Step
                </button>
            </div>
            <div class="card-body p-4" id="instructionsContainer">
                @php $instructions = old('instructions', $settings->instructions ?? \App\Models\DemoAccountSetting::getDefaultInstructions()); @endphp
                @if(is_array($instructions))
                @foreach($instructions as $i => $inst)
                <div class="instruction-row border rounded-3 p-3 mb-3 bg-white shadow-sm position-relative" style="border-color:#e2e8f0 !important; transition:all 0.2s ease;">
                    <div class="d-flex justify-content-between align-items-center mb-2.5 pb-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary text-white rounded-pill px-2.5 py-1 fw-semibold d-inline-flex align-items-center gap-1" style="font-size:0.78rem;">
                                <i class="bi bi-check2-circle"></i> Step <span class="step-num">{{ $i + 1 }}</span>
                            </span>
                            <span class="text-muted small fw-medium">Step Configuration</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-light text-danger border-0 rounded-circle p-1 d-flex align-items-center justify-content-center hover-danger" onclick="removeInstruction(this)" title="Delete Step" style="width:30px;height:30px;">
                            <i class="bi bi-trash3 fs-6"></i>
                        </button>
                    </div>
                    <input type="hidden" name="instructions[{{ $i }}][step]" value="{{ $i + 1 }}">
                    <div class="row g-2.5">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary mb-1">Title <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-bookmark"></i></span>
                                <input type="text" name="instructions[{{ $i }}][title]" class="form-control form-control-sm border-start-0 ps-1" value="{{ $inst['title'] ?? '' }}" placeholder="e.g. Register on Exness" required>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-semibold text-secondary mb-1">Description <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-card-text"></i></span>
                                <input type="text" name="instructions[{{ $i }}][description]" class="form-control form-control-sm border-start-0 ps-1" value="{{ $inst['description'] ?? '' }}" placeholder="e.g. Go to exness.com/register and create a free account" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-secondary mb-1">Action URL (Optional)</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-box-arrow-up-right"></i></span>
                                <input type="url" name="instructions[{{ $i }}][url]" class="form-control form-control-sm border-start-0 ps-1" value="{{ $inst['url'] ?? '' }}" placeholder="https://...">
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
                @endif
            </div>
        </div>

        {{-- 3. Account Types --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius:12px; overflow:hidden;">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="p-1.5 rounded-2 bg-info-subtle text-info-emphasis d-inline-flex align-items-center justify-content-center" style="width:32px;height:32px;">
                        <i class="bi bi-layers fs-5"></i>
                    </span>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h6 class="mb-0 fw-bold text-dark">Supported Account Types</h6>
                            <span class="badge bg-light text-secondary border px-2 py-0.5 rounded-pill" id="typesCountBadge" style="font-size:0.75rem;">0 Types</span>
                        </div>
                        <small class="text-muted">Account tiers users can select when submitting their demo account request</small>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1.5 px-3 py-1.5 fw-semibold" onclick="addAccountType()" style="border-radius:8px;">
                    <i class="bi bi-plus-circle"></i> Add Account Type
                </button>
            </div>
            <div class="card-body p-4" id="accountTypesContainer">
                @php $types = old('account_types', $settings->account_types ?? \App\Models\DemoAccountSetting::getDefaultAccountTypes()); @endphp
                @if(is_array($types))
                @foreach($types as $i => $type)
                <div class="account-type-row border rounded-3 p-3 mb-3 bg-white shadow-sm position-relative" style="border-color:#e2e8f0 !important; transition:all 0.2s ease;">
                    <div class="d-flex justify-content-between align-items-center mb-2.5 pb-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill px-2.5 py-1 fw-semibold d-inline-flex align-items-center gap-1" style="font-size:0.78rem;">
                                <i class="bi bi-tag"></i> Tier <span class="type-num">{{ $i + 1 }}</span>
                            </span>
                            <span class="text-muted small fw-medium">Account Tier Definition</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-light text-danger border-0 rounded-circle p-1 d-flex align-items-center justify-content-center hover-danger" onclick="removeAccountType(this)" title="Delete Account Type" style="width:30px;height:30px;">
                            <i class="bi bi-trash3 fs-6"></i>
                        </button>
                    </div>
                    <div class="row g-2.5">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-secondary mb-1">Key / Value <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-code-slash"></i></span>
                                <input type="text" name="account_types[{{ $i }}][value]" class="form-control form-control-sm border-start-0 ps-1" value="{{ $type['value'] ?? '' }}" placeholder="e.g. standard" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-secondary mb-1">Display Label <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-card-heading"></i></span>
                                <input type="text" name="account_types[{{ $i }}][label]" class="form-control form-control-sm border-start-0 ps-1" value="{{ $type['label'] ?? '' }}" placeholder="e.g. Standard Account" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary mb-1">Description (Optional)</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-info-circle"></i></span>
                                <input type="text" name="account_types[{{ $i }}][description]" class="form-control form-control-sm border-start-0 ps-1" value="{{ $type['description'] ?? '' }}" placeholder="e.g. Most popular account type with 1:2000 leverage and 0.3 pips spread">
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
                @endif
            </div>
        </div>

        {{-- 4. Deposit Amounts --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius:12px; overflow:hidden;">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="p-1.5 rounded-2 bg-warning-subtle text-warning-emphasis d-inline-flex align-items-center justify-content-center" style="width:32px;height:32px;">
                        <i class="bi bi-cash-stack fs-5"></i>
                    </span>
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">Starting Demo Balances</h6>
                        <small class="text-muted">Preset virtual deposit options users can select for their practice balance</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-1.5">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addDepositAmount('1000')">+ $1K</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addDepositAmount('5000')">+ $5K</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addDepositAmount('10000')">+ $10K</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addDepositAmount('50000')">+ $50K</button>
                    <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" onclick="addDepositAmount('')">
                        <i class="bi bi-plus-circle"></i> Custom
                    </button>
                </div>
            </div>
            <div class="card-body p-4">
                @php $amounts = old('deposit_amounts', $settings->deposit_amounts ?? ['1000', '5000', '10000', '50000', '100000']); @endphp
                <div class="row g-2.5" id="depositAmountsContainer">
                    @if(is_array($amounts))
                    @foreach($amounts as $i => $amt)
                    <div class="col-xl-2 col-lg-3 col-md-4 col-6 deposit-amt-col">
                        <div class="input-group shadow-sm" style="border-radius:8px; overflow:hidden;">
                            <span class="input-group-text bg-light text-success fw-bold border-end-0 px-2.5">$</span>
                            <input type="text" name="deposit_amounts[]" class="form-control fw-semibold text-dark text-center px-1" value="{{ $amt }}" placeholder="1000" required>
                            @if(count($amounts) > 1)
                            <button type="button" class="btn btn-light text-danger border border-start-0 px-2" onclick="removeDepositAmount(this)" title="Remove">
                                <i class="bi bi-x-lg"></i>
                            </button>
                            @endif
                        </div>
                    </div>
                    @endforeach
                    @endif
                </div>
            </div>
        </div>

        {{-- Bottom Sticky Save Bar --}}
        <div class="card border-0 shadow-sm p-3 mb-5" style="border-radius:12px; background:linear-gradient(to right, #f8fafc, #ffffff); border:1px solid #e2e8f0 !important;">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-flex align-items-center gap-2 text-secondary small">
                    <i class="bi bi-shield-check text-success fs-5"></i>
                    <span>All changes update instantly across the KTS mobile app and demo onboarding flow.</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('admin.demo-accounts.index') }}" class="btn btn-light border px-3 py-2 fw-semibold" style="border-radius:9px;">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold shadow-sm d-inline-flex align-items-center gap-2" style="border-radius:9px;">
                        <i class="bi bi-check2-circle fs-5"></i> Save All Settings
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    .instruction-row:hover, .account-type-row:hover {
        border-color: #cbd5e1 !important;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05) !important;
    }
    .hover-danger:hover {
        background-color: #fee2e2 !important;
        color: #dc2626 !important;
    }
</style>
@endpush

@push('scripts')
<script>
let instructionIndex = {{ count($instructions ?? []) }};
let accountTypeIndex = {{ count($types ?? []) }};

function updateCounters() {
    const steps = document.querySelectorAll('.instruction-row').length;
    const types = document.querySelectorAll('.account-type-row').length;
    const stepBadge = document.getElementById('stepsCountBadge');
    const typeBadge = document.getElementById('typesCountBadge');
    if (stepBadge) stepBadge.textContent = `${steps} Step${steps === 1 ? '' : 's'}`;
    if (typeBadge) typeBadge.textContent = `${types} Type${types === 1 ? '' : 's'}`;
}
updateCounters();

function addInstruction() {
    const html = `
    <div class="instruction-row border rounded-3 p-3 mb-3 bg-white shadow-sm position-relative" style="border-color:#e2e8f0 !important; transition:all 0.2s ease;">
        <div class="d-flex justify-content-between align-items-center mb-2.5 pb-2 border-bottom">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary text-white rounded-pill px-2.5 py-1 fw-semibold d-inline-flex align-items-center gap-1" style="font-size:0.78rem;">
                    <i class="bi bi-check2-circle"></i> Step <span class="step-num">${instructionIndex + 1}</span>
                </span>
                <span class="text-muted small fw-medium">Step Configuration</span>
            </div>
            <button type="button" class="btn btn-sm btn-light text-danger border-0 rounded-circle p-1 d-flex align-items-center justify-content-center hover-danger" onclick="removeInstruction(this)" title="Delete Step" style="width:30px;height:30px;">
                <i class="bi bi-trash3 fs-6"></i>
            </button>
        </div>
        <input type="hidden" name="instructions[${instructionIndex}][step]" value="${instructionIndex + 1}">
        <div class="row g-2.5">
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-secondary mb-1">Title <span class="text-danger">*</span></label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-bookmark"></i></span>
                    <input type="text" name="instructions[${instructionIndex}][title]" class="form-control form-control-sm border-start-0 ps-1" placeholder="e.g. Register on Exness" required>
                </div>
            </div>
            <div class="col-md-5">
                <label class="form-label small fw-semibold text-secondary mb-1">Description <span class="text-danger">*</span></label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-card-text"></i></span>
                    <input type="text" name="instructions[${instructionIndex}][description]" class="form-control form-control-sm border-start-0 ps-1" placeholder="e.g. Enter details and verify email" required>
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-secondary mb-1">Action URL (Optional)</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-box-arrow-up-right"></i></span>
                    <input type="url" name="instructions[${instructionIndex}][url]" class="form-control form-control-sm border-start-0 ps-1" placeholder="https://...">
                </div>
            </div>
        </div>
    </div>`;
    document.getElementById('instructionsContainer').insertAdjacentHTML('beforeend', html);
    instructionIndex++;
    updateCounters();
}

function removeInstruction(btn) {
    const total = document.querySelectorAll('.instruction-row').length;
    if (total <= 1) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'warning', title: 'Cannot Delete', text: 'At least one instruction step is required.' });
        } else {
            alert('At least one instruction step is required.');
        }
        return;
    }
    btn.closest('.instruction-row').remove();
    reindexInstructions();
    updateCounters();
}

function reindexInstructions() {
    const rows = document.querySelectorAll('.instruction-row');
    rows.forEach((row, i) => {
        row.querySelector('.step-num').textContent = i + 1;
        row.querySelector('input[type="hidden"]').value = i + 1;
        row.querySelectorAll('input').forEach(inp => {
            if (inp.name) inp.name = inp.name.replace(/instructions\[\d+\]/, `instructions[${i}]`);
        });
    });
    instructionIndex = rows.length;
}

function addAccountType() {
    const html = `
    <div class="account-type-row border rounded-3 p-3 mb-3 bg-white shadow-sm position-relative" style="border-color:#e2e8f0 !important; transition:all 0.2s ease;">
        <div class="d-flex justify-content-between align-items-center mb-2.5 pb-2 border-bottom">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill px-2.5 py-1 fw-semibold d-inline-flex align-items-center gap-1" style="font-size:0.78rem;">
                    <i class="bi bi-tag"></i> Tier <span class="type-num">${accountTypeIndex + 1}</span>
                </span>
                <span class="text-muted small fw-medium">Account Tier Definition</span>
            </div>
            <button type="button" class="btn btn-sm btn-light text-danger border-0 rounded-circle p-1 d-flex align-items-center justify-content-center hover-danger" onclick="removeAccountType(this)" title="Delete Account Type" style="width:30px;height:30px;">
                <i class="bi bi-trash3 fs-6"></i>
            </button>
        </div>
        <div class="row g-2.5">
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-secondary mb-1">Key / Value <span class="text-danger">*</span></label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-code-slash"></i></span>
                    <input type="text" name="account_types[${accountTypeIndex}][value]" class="form-control form-control-sm border-start-0 ps-1" placeholder="e.g. pro" required>
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-secondary mb-1">Display Label <span class="text-danger">*</span></label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-card-heading"></i></span>
                    <input type="text" name="account_types[${accountTypeIndex}][label]" class="form-control form-control-sm border-start-0 ps-1" placeholder="e.g. Pro Account" required>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold text-secondary mb-1">Description (Optional)</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-info-circle"></i></span>
                    <input type="text" name="account_types[${accountTypeIndex}][description]" class="form-control form-control-sm border-start-0 ps-1" placeholder="e.g. Low floating spreads from 0.1 pips with zero commission">
                </div>
            </div>
        </div>
    </div>`;
    document.getElementById('accountTypesContainer').insertAdjacentHTML('beforeend', html);
    accountTypeIndex++;
    updateCounters();
}

function removeAccountType(btn) {
    const total = document.querySelectorAll('.account-type-row').length;
    if (total <= 1) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'warning', title: 'Cannot Delete', text: 'At least one account type is required.' });
        } else {
            alert('At least one account type is required.');
        }
        return;
    }
    btn.closest('.account-type-row').remove();
    reindexAccountTypes();
    updateCounters();
}

function reindexAccountTypes() {
    const rows = document.querySelectorAll('.account-type-row');
    rows.forEach((row, i) => {
        const num = row.querySelector('.type-num');
        if (num) num.textContent = i + 1;
        row.querySelectorAll('input').forEach(inp => {
            if (inp.name) inp.name = inp.name.replace(/account_types\[\d+\]/, `account_types[${i}]`);
        });
    });
    accountTypeIndex = rows.length;
}

function addDepositAmount(amt = '') {
    const container = document.getElementById('depositAmountsContainer');
    const html = `
    <div class="col-xl-2 col-lg-3 col-md-4 col-6 deposit-amt-col">
        <div class="input-group shadow-sm" style="border-radius:8px; overflow:hidden;">
            <span class="input-group-text bg-light text-success fw-bold border-end-0 px-2.5">$</span>
            <input type="text" name="deposit_amounts[]" class="form-control fw-semibold text-dark text-center px-1" value="${amt}" placeholder="1000" required>
            <button type="button" class="btn btn-light text-danger border border-start-0 px-2" onclick="removeDepositAmount(this)" title="Remove">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>`;
    container.insertAdjacentHTML('beforeend', html);
}

function removeDepositAmount(btn) {
    const total = document.querySelectorAll('.deposit-amt-col').length;
    if (total <= 1) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'warning', title: 'Cannot Delete', text: 'At least one deposit balance option is required.' });
        } else {
            alert('At least one deposit balance option is required.');
        }
        return;
    }
    btn.closest('.deposit-amt-col').remove();
}
</script>
@endpush
