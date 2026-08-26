@extends('layouts.app')
@section('title', 'Restricted Words')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold"><i class="bi bi-file-word me-2 text-warning"></i>Restricted Words</h4>
    <a href="{{ route('admin.chat.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="card mb-4">
    <div class="card-header"><h6 class="mb-0">Add Restricted Word</h6></div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.chat.store-restricted-word') }}" class="row g-3 align-items-end">
            @csrf
            <div class="col-md-4">
                <label class="form-label text-secondary">Word <span class="text-danger">*</span></label>
                <input type="text" name="word" class="form-control @error('word') is-invalid @enderror" required maxlength="100" placeholder="Enter word to filter">
                @error('word')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label text-secondary">Replacement</label>
                <input type="text" name="replacement" class="form-control" maxlength="10" placeholder="***" value="***">
            </div>
            <div class="col-md-2 d-grid"><button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add</button></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th class="ps-3">Word</th><th>Replacement</th><th>Status</th><th>Added By</th><th class="pe-3">Actions</th></tr></thead>
                <tbody>
                    @forelse($words as $word)
                    <tr class="{{ !$word->is_active ? 'opacity-50' : '' }}">
                        <td class="ps-3 text-dark fw-semibold">{{ $word->word }}</td>
                        <td class="text-secondary">{{ $word->replacement }}</td>
                        <td><span class="badge bg-{{ $word->is_active ? 'success' : 'secondary' }}">{{ $word->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td class="text-secondary">{{ $word->creator->name ?? '-' }}</td>
                        <td class="pe-3">
                            <div class="d-flex gap-1">
                                <form method="POST" action="{{ route('admin.chat.toggle-restricted-word', $word) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $word->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"><i class="bi bi-{{ $word->is_active ? 'pause' : 'play' }}"></i></button>
                                </form>
                                <form method="POST" action="{{ route('admin.chat.destroy-restricted-word', $word) }}" class="d-inline" onsubmit="return confirm('Delete this word?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-secondary py-4">No restricted words.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@if($words->hasPages())
<div class="d-flex justify-content-between align-items-center mt-3">
    <small class="text-secondary">Showing {{ $words->firstItem() }}-{{ $words->lastItem() }} of {{ $words->total() }}</small>
    {{ $words->links() }}
</div>
@endif
@endsection
