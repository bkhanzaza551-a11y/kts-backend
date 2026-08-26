@extends('layouts.app')
@section('title', 'Logs: ' . $bot->name)
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold"><i class="bi bi-journal-text me-2 text-primary"></i>Logs: {{ $bot->name }}</h4>
    <a href="{{ route('admin.mt5-bot.show', $bot) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Bot</a>
</div>
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th class="ps-3">Time</th><th>Level</th><th>Action</th><th>Message</th><th>Metadata</th></tr></thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td class="ps-3 text-secondary">{{ $log->created_at->format('M d, Y H:i:s') }}</td>
                        <td><span class="badge bg-{{ $log->level_color }}">{{ strtoupper($log->level) }}</span></td>
                        <td class="text-dark">{{ $log->action }}</td>
                        <td class="text-secondary" style="max-width:300px;">{{ $log->message }}</td>
                        <td class="text-secondary">
                            @if($log->metadata)
                            <button class="btn btn-sm btn-outline-secondary py-0" type="button" data-bs-toggle="collapse" data-bs-target="#meta-{{ $log->id }}">
                                <i class="bi bi-code-slash"></i>
                            </button>
                            <div class="collapse mt-1" id="meta-{{ $log->id }}">
                                <pre class="text-secondary small mb-0" style="white-space:pre-wrap;">{{ json_encode($log->metadata, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                            @else - @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-secondary py-4">No logs found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@if($logs->hasPages())
<div class="d-flex justify-content-between align-items-center mt-3">
    <small class="text-secondary">Showing {{ $logs->firstItem() }}-{{ $logs->lastItem() }} of {{ $logs->total() }}</small>
    {{ $logs->withQueryString()->links() }}
</div>
@endif
@endsection
