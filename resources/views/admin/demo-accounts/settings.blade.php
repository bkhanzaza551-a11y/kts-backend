@extends('layouts.app')

@section('title', 'Demo Account Settings')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold"><i class="bi bi-gear me-2 text-primary"></i>Demo Account Settings</h4>
    <a href="{{ route('admin.demo-accounts.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Requests</a>
</div>

<form method="POST" action="{{ route('admin.demo-settings.update') }}">
    @csrf
    @method('PUT')

    {{-- Basic Settings --}}
    <div class="card mb-4">
        <div class="card-header"><h6 class="mb-0">Basic Settings</h6></div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label text-secondary">Exness Referral Link</label>
                <input type="url" name="referral_link" class="form-control @error('referral_link') is-invalid @enderror" value="{{ old('referral_link', $settings->referral_link) }}" placeholder="https://www.exness.com/register/">
                <small class="text-secondary">This link will be shown in the mobile app. Users will be redirected here when they click "Open Exness".</small>
                @error('referral_link')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label text-secondary">Page Title <span class="text-danger">*</span></label>
                <input type="text" name="page_title" class="form-control @error('page_title') is-invalid @enderror" value="{{ old('page_title', $settings->page_title) }}" required>
                @error('page_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-0">
                <label class="form-label text-secondary">Page Description</label>
                <textarea name="page_description" class="form-control @error('page_description') is-invalid @enderror" rows="2">{{ old('page_description', $settings->page_description) }}</textarea>
                @error('page_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    {{-- Instructions --}}
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Instructions Steps</h6>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addInstruction()"><i class="bi bi-plus me-1"></i>Add Step</button>
        </div>
        <div class="card-body" id="instructionsContainer">
            @php $instructions = old('instructions', $settings->instructions ?? DemoAccountSetting::getDefaultInstructions()); @endphp
            @if(is_array($instructions))
            @foreach($instructions as $i => $inst)
            <div class="instruction-row border rounded p-3 mb-3 bg-light">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong class="text-primary">Step {{ $i + 1 }}</strong>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeInstruction(this)"><i class="bi bi-trash"></i></button>
                </div>
                <input type="hidden" name="instructions[{{ $i }}][step]" value="{{ $i + 1 }}">
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label small text-secondary">Title <span class="text-danger">*</span></label>
                        <input type="text" name="instructions[{{ $i }}][title]" class="form-control form-control-sm" value="{{ $inst['title'] ?? '' }}" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label small text-secondary">Description <span class="text-danger">*</span></label>
                        <input type="text" name="instructions[{{ $i }}][description]" class="form-control form-control-sm" value="{{ $inst['description'] ?? '' }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-secondary">URL (optional)</label>
                        <input type="url" name="instructions[{{ $i }}][url]" class="form-control form-control-sm" value="{{ $inst['url'] ?? '' }}" placeholder="https://...">
                    </div>
                </div>
            </div>
            @endforeach
            @endif
        </div>
    </div>

    {{-- Account Types --}}
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Account Types</h6>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addAccountType()"><i class="bi bi-plus me-1"></i>Add Type</button>
        </div>
        <div class="card-body" id="accountTypesContainer">
            @php $types = old('account_types', $settings->account_types ?? DemoAccountSetting::getDefaultAccountTypes()); @endphp
            @if(is_array($types))
            @foreach($types as $i => $type)
            <div class="account-type-row border rounded p-3 mb-3 bg-light">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong class="text-primary">Type {{ $i + 1 }}</strong>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeAccountType(this)"><i class="bi bi-trash"></i></button>
                </div>
                <div class="row g-2">
                    <div class="col-md-3">
                        <label class="form-label small text-secondary">Value <span class="text-danger">*</span></label>
                        <input type="text" name="account_types[{{ $i }}][value]" class="form-control form-control-sm" value="{{ $type['value'] ?? '' }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-secondary">Label <span class="text-danger">*</span></label>
                        <input type="text" name="account_types[{{ $i }}][label]" class="form-control form-control-sm" value="{{ $type['label'] ?? '' }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-secondary">Description</label>
                        <input type="text" name="account_types[{{ $i }}][description]" class="form-control form-control-sm" value="{{ $type['description'] ?? '' }}">
                    </div>
                </div>
            </div>
            @endforeach
            @endif
        </div>
    </div>

    {{-- Deposit Amounts --}}
    <div class="card mb-4">
        <div class="card-header"><h6 class="mb-0">Deposit Amount Options</h6></div>
        <div class="card-body">
            @php $amounts = old('deposit_amounts', $settings->deposit_amounts ?? ['1000', '5000', '10000', '50000', '100000']); @endphp
            <div class="row g-2">
                @if(is_array($amounts))
                @foreach($amounts as $i => $amt)
                <div class="col-md-2 col-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">$</span>
                        <input type="text" name="deposit_amounts[]" class="form-control" value="{{ $amt }}">
                        @if($i >= 1)<button type="button" class="btn btn-outline-danger btn-sm" onclick="this.closest('.col-md-2').remove()"><i class="bi bi-x"></i></button>@endif
                    </div>
                </div>
                @endforeach
                @endif
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addDepositAmount()"><i class="bi bi-plus me-1"></i>Add Amount</button>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-lg me-1"></i>Save Settings</button>
    </div>
</form>
@endsection

@push('scripts')
<script>
let instructionIndex = {{ count($instructions ?? []) }};
let accountTypeIndex = {{ count($types ?? []) }};

function addInstruction() {
    const html = `
    <div class="instruction-row border rounded p-3 mb-3 bg-light">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <strong class="text-primary">Step <span class="step-num">${instructionIndex + 1}</span></strong>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeInstruction(this)"><i class="bi bi-trash"></i></button>
        </div>
        <input type="hidden" name="instructions[${instructionIndex}][step]" value="${instructionIndex + 1}">
        <div class="row g-2">
            <div class="col-md-4">
                <label class="form-label small text-secondary">Title <span class="text-danger">*</span></label>
                <input type="text" name="instructions[${instructionIndex}][title]" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-5">
                <label class="form-label small text-secondary">Description <span class="text-danger">*</span></label>
                <input type="text" name="instructions[${instructionIndex}][description]" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-secondary">URL (optional)</label>
                <input type="url" name="instructions[${instructionIndex}][url]" class="form-control form-control-sm" placeholder="https://...">
            </div>
        </div>
    </div>`;
    document.getElementById('instructionsContainer').insertAdjacentHTML('beforeend', html);
    instructionIndex++;
}

function removeInstruction(btn) {
    btn.closest('.instruction-row').remove();
    reindexInstructions();
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
    <div class="account-type-row border rounded p-3 mb-3 bg-light">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <strong class="text-primary">Type ${accountTypeIndex + 1}</strong>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeAccountType(this)"><i class="bi bi-trash"></i></button>
        </div>
        <div class="row g-2">
            <div class="col-md-3">
                <label class="form-label small text-secondary">Value <span class="text-danger">*</span></label>
                <input type="text" name="account_types[${accountTypeIndex}][value]" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-secondary">Label <span class="text-danger">*</span></label>
                <input type="text" name="account_types[${accountTypeIndex}][label]" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small text-secondary">Description</label>
                <input type="text" name="account_types[${accountTypeIndex}][description]" class="form-control form-control-sm">
            </div>
        </div>
    </div>`;
    document.getElementById('accountTypesContainer').insertAdjacentHTML('beforeend', html);
    accountTypeIndex++;
}

function removeAccountType(btn) {
    btn.closest('.account-type-row').remove();
}

function addDepositAmount() {
    const html = `<div class="col-md-2 col-4"><div class="input-group input-group-sm"><span class="input-group-text">$</span><input type="text" name="deposit_amounts[]" class="form-control"><button type="button" class="btn btn-outline-danger btn-sm" onclick="this.closest('.col-md-2').remove()"><i class="bi bi-x"></i></button></div></div>`;
    event.target.closest('.card-body').querySelector('.row').insertAdjacentHTML('beforeend', html);
}
</script>
@endpush
