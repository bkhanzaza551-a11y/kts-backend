@extends('layouts.app')

@section('title', 'Signal Categories')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold"><i class="bi bi-tags me-2 text-primary"></i>Signal Categories</h4>
        <a href="{{ route('admin.signals.index') }}" class="text-secondary text-decoration-none small"><i class="bi bi-arrow-left me-1"></i>Back to Signals</a>
    </div>
    @if(auth()->user()->hasPermission('signal_categories_create'))
    <a href="{{ route('admin.signal-categories.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Add Category
    </a>
    @endif
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-3" style="width:50px;"><small class="text-secondary">Order</small></th>
                        <th><small class="text-secondary">Name</small></th>
                        <th><small class="text-secondary">Slug</small></th>
                        <th><small class="text-secondary">Signals</small></th>
                        <th><small class="text-secondary">Status</small></th>
                        <th class="pe-3"><small class="text-secondary">Actions</small></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $cat)
                    <tr>
                        <td class="ps-3"><span class="text-secondary">{{ $cat->sort_order }}</span></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="badge me-2" style="background-color: {{ $cat->color }}20; color: {{ $cat->color }}; border: 1px solid {{ $cat->color }}40; min-width: 120px;">
                                    @if($cat->icon)<i class="bi bi-{{ $cat->icon }} me-1"></i>@endif
                                    {{ $cat->name }}
                                </span>
                            </div>
                        </td>
                        <td><code class="text-secondary">{{ $cat->slug }}</code></td>
                        <td><span class="badge bg-secondary">{{ $cat->signals_count }} signals</span></td>
                        <td>
                            @if($cat->is_active)
                            <span class="badge bg-success">Active</span>
                            @else
                            <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td class="pe-3">
                            <div class="d-flex gap-1">
                                @if(auth()->user()->hasPermission('signal_categories_edit'))
                                <a href="{{ route('admin.signal-categories.edit', $cat) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></a>
                                @endif
                                @if(auth()->user()->hasPermission('signal_categories_delete'))
                                <form method="POST" action="{{ route('admin.signal-categories.destroy', $cat) }}" class="d-inline" onsubmit="return confirm('Delete this category? @if($cat->signals_count > 0) WARNING: This category has {{ $cat->signals_count }} signals! @endif')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-secondary py-4">
                            <i class="bi bi-tags fs-1 d-block mb-2 opacity-50"></i>
                            <p class="mb-0">No categories yet.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($categories->hasPages())
<div class="d-flex justify-content-between align-items-center mt-3">
    <small class="text-secondary">Showing {{ $categories->firstItem() }}-{{ $categories->lastItem() }} of {{ $categories->total() }}</small>
    {{ $categories->links() }}
</div>
@endif
@endsection
