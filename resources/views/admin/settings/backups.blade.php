@extends('layouts.app')
@section('title', 'System Backups')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold"><i class="bi bi-cloud-download me-2 text-info"></i>System Backups</h4>
    <div class="d-flex gap-2">
        <form method="POST" action="{{ route('admin.settings.create-backup') }}" class="d-inline" onsubmit="return confirm('Create a new backup now?')">
            @csrf
            <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Create Backup</button>
        </form>
        <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th class="ps-3">Filename</th><th>Size</th><th>Date</th><th class="pe-3">Actions</th></tr></thead>
                <tbody>
                    @forelse($backups as $backup)
                    <tr>
                        <td class="ps-3"><i class="bi bi-file-earmark-zip me-2 text-info"></i><span class="fw-semibold text-dark">{{ $backup['name'] }}</span></td>
                        <td class="text-secondary">{{ number_format($backup['size'] / 1024, 1) }} KB</td>
                        <td class="text-secondary">{{ \Carbon\Carbon::createFromTimestamp($backup['date'])->format('M d, Y H:i') }}</td>
                        <td class="pe-3">
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.settings.download-backup', $backup['name']) }}" class="btn btn-sm btn-outline-success"><i class="bi bi-download"></i></a>
                                <form method="POST" action="{{ route('admin.settings.delete-backup', $backup['name']) }}" class="d-inline" onsubmit="return confirm('Delete this backup?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-secondary py-4"><i class="bi bi-cloud-download fs-1 d-block mb-2 opacity-50"></i><p class="mb-0">No backups yet. Create your first backup!</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
