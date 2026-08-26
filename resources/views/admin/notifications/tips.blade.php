@extends('layouts.app')
@section('title', 'AI Trading Tips')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold"><i class="bi bi-lightbulb me-2 text-success"></i>AI Trading Tips</h4>
    <div class="d-flex gap-2">
        <form method="POST" action="{{ route('admin.notifications.generate-tip') }}" class="d-inline" onsubmit="return confirm('Generate a new trading tip?')">
            @csrf
            <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-magic me-1"></i>Generate Tip</button>
        </form>
        <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h6 class="mb-0">Add Manual Tip</h6></div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.notifications.store-tip') }}" class="row g-3 align-items-end">
            @csrf
            <div class="col-md-6">
                <label class="form-label text-secondary">Tip <span class="text-danger">*</span></label>
                <textarea name="tip" class="form-control @error('tip') is-invalid @enderror" rows="2" maxlength="1000" required></textarea>
                @error('tip')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label text-secondary">Category</label>
                <select name="category" class="form-select">
                    <option value="general">General</option>
                    <option value="risk_management">Risk Management</option>
                    <option value="strategy">Strategy</option>
                    <option value="psychology">Psychology</option>
                    <option value="market_analysis">Market Analysis</option>
                    <option value="education">Education</option>
                </select>
            </div>
            <div class="col-md-3 d-grid"><button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Tip</button></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th class="ps-3">Tip</th><th>Category</th><th>Status</th><th>Date</th><th class="pe-3">Actions</th></tr></thead>
                <tbody>
                    @forelse($tips as $tip)
                    <tr>
                        <td class="ps-3" style="max-width:400px;">{{ Str::limit($tip->tip, 80) }}</td>
                        <td><span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $tip->category)) }}</span></td>
                        <td><span class="badge bg-{{ $tip->is_sent ? 'success' : 'warning' }}">{{ $tip->is_sent ? 'Sent' : 'Pending' }}</span></td>
                        <td class="text-secondary">{{ $tip->created_at->format('M d, Y') }}</td>
                        <td class="pe-3">
                            <form method="POST" action="{{ route('admin.notifications.destroy-tip', $tip) }}" class="d-inline" onsubmit="return confirm('Delete this tip?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-secondary py-4">No tips yet. Generate one!</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@if($tips->hasPages())
<div class="d-flex justify-content-between align-items-center mt-3">
    <small class="text-secondary">Showing {{ $tips->firstItem() }}-{{ $tips->lastItem() }} of {{ $tips->total() }}</small>
    {{ $tips->links() }}
</div>
@endif
@endsection
