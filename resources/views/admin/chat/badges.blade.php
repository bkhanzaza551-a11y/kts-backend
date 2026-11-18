@extends('layouts.app')
@section('title', 'Chat Badges & Verification')

@section('content')
<div class="container-fluid px-0">
    {{-- Header Section --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="p-2.5 rounded-3 bg-warning-subtle text-warning-emphasis d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                <i class="bi bi-award-fill fs-4 text-warning"></i>
            </div>
            <div>
                <h4 class="mb-1 fw-bold text-dark">Trader Chat Badges & Verification</h4>
                <p class="text-secondary mb-0 small">Assign custom public titles, verified green ticks, and visual roles to traders in chat rooms.</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.chat.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5" style="border-radius:8px;">
                <i class="bi bi-chat-left-dots"></i> Chat Feed
            </a>
            <a href="{{ route('admin.chat.rooms') }}" class="btn btn-outline-secondary btn-sm px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5" style="border-radius:8px;">
                <i class="bi bi-door-open"></i> Rooms
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4 shadow-sm" role="alert" style="border-radius:10px;">
        <i class="bi bi-check-circle-fill fs-5"></i>
        <div class="flex-grow-1 fw-medium">{{ session('success') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- Assign Badge Card --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius:12px; overflow:visible;">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <span class="p-1.5 rounded-2 bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center" style="width:32px;height:32px;">
                    <i class="bi bi-shield-check fs-6"></i>
                </span>
                <div>
                    <h6 class="mb-0 fw-bold text-dark">Assign or Update Trader Badge</h6>
                    <small class="text-muted">Select a trader to assign custom chat title, badge color and verified status</small>
                </div>
            </div>
        </div>
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.chat.update-badge') }}" id="badgeForm" class="row g-3">
                @csrf

                {{-- Custom Searchable User Selector --}}
                <div class="col-lg-4 col-md-6">
                    <label class="form-label fw-semibold text-dark small mb-1.5">
                        Select Trader <span class="text-danger">*</span>
                    </label>

                    {{-- Hidden Form Input --}}
                    <input type="hidden" name="user_id" id="selectedUserId" value="" required>

                    {{-- Custom Combobox Trigger --}}
                    <div class="position-relative user-select-wrapper" id="userSelectWrapper">
                        <button type="button" class="form-select text-start d-flex align-items-center justify-content-between w-100 py-2 px-3 user-select-btn" id="userSelectBtn" style="border-radius:8px; height:42px;">
                            <div class="d-flex align-items-center gap-2 overflow-hidden" id="selectedUserPreview">
                                <div class="avatar-placeholder rounded-circle bg-light text-muted d-flex align-items-center justify-content-center flex-shrink-0" style="width:24px;height:24px;font-size:0.75rem;">
                                    <i class="bi bi-person"></i>
                                </div>
                                <span class="text-muted small text-truncate">Search by name or email...</span>
                            </div>
                            <i class="bi bi-chevron-down text-muted fs-6 flex-shrink-0"></i>
                        </button>

                        {{-- Floating Searchable Dropdown Panel --}}
                        <div class="user-dropdown-panel shadow-lg border rounded-3 p-2 bg-white" id="userDropdownPanel" style="display:none;">
                            {{-- Sticky Search Box --}}
                            <div class="p-1 mb-2 border-bottom pb-2">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                                    <input type="text" id="userSearchInput" class="form-control border-start-0 ps-1" placeholder="Type trader name or email..." autocomplete="off">
                                    <button type="button" class="btn btn-sm btn-light border border-start-0 text-muted" onclick="clearUserSearch()" title="Clear search">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- Scrollable Users List --}}
                            <div class="user-options-list" id="userOptionsList" style="max-height:280px; overflow-y:auto;">
                                @foreach($allUsers as $u)
                                <div class="user-option-item p-2 rounded-2 d-flex align-items-center justify-content-between"
                                     data-id="{{ $u->id }}"
                                     data-name="{{ $u->name }}"
                                     data-email="{{ $u->email }}"
                                     data-badge="{{ $u->chat_badge ?? '' }}"
                                     data-color="{{ $u->badge_color ?? 'primary' }}"
                                     data-verified="{{ $u->is_verified ? '1' : '0' }}"
                                     onclick="selectUser(this)">
                                    <div class="d-flex align-items-center gap-2 min-width-0">
                                        <div class="user-mini-avatar rounded-circle text-white fw-bold d-flex align-items-center justify-content-center flex-shrink-0" style="width:30px;height:30px;font-size:0.78rem;background:{{ $u->is_verified ? '#059669' : '#3b82f6' }};">
                                            {{ strtoupper(substr($u->name, 0, 1)) }}
                                        </div>
                                        <div class="text-truncate">
                                            <div class="fw-semibold text-dark small text-truncate d-flex align-items-center gap-1">
                                                <span>{{ $u->name }}</span>
                                                @if($u->is_verified)
                                                <i class="bi bi-patch-check-fill text-success" title="Verified Trader"></i>
                                                @endif
                                            </div>
                                            <div class="text-muted" style="font-size:0.73rem;">{{ $u->email }}</div>
                                        </div>
                                    </div>
                                    @if($u->chat_badge)
                                    <span class="badge bg-{{ $u->badge_color ?? 'primary' }} ms-2 flex-shrink-0" style="font-size:0.65rem;">
                                        {{ $u->chat_badge }}
                                    </span>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                            <div id="noUsersMatch" class="text-center py-3 text-muted small" style="display:none;">
                                <i class="bi bi-emoji-frown me-1"></i> No traders found matching search.
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Badge Title --}}
                <div class="col-lg-3 col-md-6">
                    <div class="d-flex justify-content-between align-items-center mb-1.5">
                        <label class="form-label fw-semibold text-dark small mb-0">Badge Title</label>
                        <small class="text-muted">Max 50 chars</small>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-tag"></i></span>
                        <input type="text" name="chat_badge" id="badgeTitleInput" class="form-control border-start-0 ps-1" placeholder="e.g. VIP TRADER" maxlength="50" style="height:42px;">
                    </div>
                    {{-- Quick Presets --}}
                    <div class="d-flex align-items-center gap-1 mt-1.5 flex-wrap">
                        <span class="text-muted" style="font-size:0.7rem;">Presets:</span>
                        <button type="button" class="btn btn-xs preset-chip" onclick="applyPreset('VIP', 'warning')">👑 VIP</button>
                        <button type="button" class="btn btn-xs preset-chip" onclick="applyPreset('PRO TRADER', 'success')">✅ PRO</button>
                        <button type="button" class="btn btn-xs preset-chip" onclick="applyPreset('OFFICIAL', 'primary')">🛡️ OFFICIAL</button>
                        <button type="button" class="btn btn-xs preset-chip" onclick="applyPreset('ELITE', 'danger')">🔥 ELITE</button>
                        <button type="button" class="btn btn-xs preset-chip" onclick="applyPreset('SCALPER', 'info')">⚡ SCALPER</button>
                    </div>
                </div>

                {{-- Badge Color --}}
                <div class="col-lg-2 col-md-4">
                    <label class="form-label fw-semibold text-dark small mb-1.5">Badge Color</label>
                    <select name="badge_color" id="badgeColorSelect" class="form-select" style="height:42px;">
                        <option value="warning">👑 Gold (VIP)</option>
                        <option value="success">✅ Green (Pro)</option>
                        <option value="primary">🛡️ Blue (Official)</option>
                        <option value="danger">🔥 Red (Elite)</option>
                        <option value="info">⚡ Cyan (Fast)</option>
                        <option value="secondary">⚙️ Gray (Standard)</option>
                    </select>
                </div>

                {{-- Verified Switch --}}
                <div class="col-lg-2 col-md-4 d-flex align-items-center pt-md-3">
                    <div class="form-check form-switch mt-2">
                        <input type="hidden" name="is_verified" value="0">
                        <input type="checkbox" name="is_verified" value="1" class="form-check-input" id="assignVerified" checked style="width:2.5em; height:1.3em;">
                        <label class="form-check-label fw-semibold text-dark ms-1 d-flex align-items-center gap-1" for="assignVerified" style="font-size:0.85rem;">
                            <i class="bi bi-patch-check-fill text-success fs-5"></i> Verified Tick
                        </label>
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="col-lg-1 col-md-4 d-grid pt-md-3">
                    <button type="submit" class="btn btn-warning text-dark fw-bold d-inline-flex align-items-center justify-content-center gap-1 shadow-sm mt-md-2" style="height:42px; border-radius:8px;">
                        <i class="bi bi-check2-circle fs-5"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Users With Badges Table --}}
    <div class="card border-0 shadow-sm" style="border-radius:12px; overflow:hidden;">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <span class="p-1.5 rounded-2 bg-success-subtle text-success d-inline-flex align-items-center justify-content-center" style="width:32px;height:32px;">
                    <i class="bi bi-people fs-6"></i>
                </span>
                <div>
                    <h6 class="mb-0 fw-bold text-dark">Verified Traders & Active Badges</h6>
                    <small class="text-muted">List of all users with assigned chat titles, verification ticks, or VIP status</small>
                </div>
            </div>
            <span class="badge bg-light text-secondary border px-2.5 py-1 rounded-pill fw-semibold" style="font-size:0.75rem;">
                {{ number_format($users->total()) }} Badged Users
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light border-bottom">
                        <tr class="text-secondary small fw-semibold">
                            <th class="ps-4 py-3">Trader</th>
                            <th class="py-3">Email Address</th>
                            <th class="py-3">Verification</th>
                            <th class="py-3">Chat Badge</th>
                            <th class="py-3">Plan Status</th>
                            <th class="pe-4 py-3 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td class="ps-4 py-3">
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center flex-shrink-0" style="width:34px;height:34px;font-size:0.85rem;background:{{ $user->is_verified ? '#059669' : '#3b82f6' }};">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.users.show', $user->id) }}" class="fw-bold text-dark text-decoration-none hover-primary d-inline-flex align-items-center gap-1">
                                            <span>{{ $user->name }}</span>
                                            @if($user->is_verified)
                                                <i class="bi bi-patch-check-fill text-success" title="Verified Trader"></i>
                                            @endif
                                        </a>
                                        <div class="text-muted small">ID: #{{ $user->id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-secondary py-3">{{ $user->email }}</td>
                            <td class="py-3">
                                @if($user->is_verified)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill">
                                        <i class="bi bi-check-circle-fill me-1"></i>Verified
                                    </span>
                                @else
                                    <span class="text-muted small">Not Verified</span>
                                @endif
                            </td>
                            <td class="py-3">
                                @if($user->chat_badge)
                                    <span class="badge bg-{{ $user->badge_color ?? 'primary' }} px-2.5 py-1 rounded-pill fw-semibold">
                                        {{ $user->chat_badge }}
                                    </span>
                                @else
                                    <span class="text-muted small fst-italic">No custom badge</span>
                                @endif
                            </td>
                            <td class="py-3">
                                @if($user->is_premium)
                                    <span class="badge bg-warning text-dark px-2 py-1 rounded-pill fw-semibold"><i class="bi bi-star-fill me-0.5"></i> VIP Trader</span>
                                @else
                                    <span class="badge bg-light text-secondary border px-2 py-1 rounded-pill">Standard</span>
                                @endif
                            </td>
                            <td class="pe-4 py-3 text-end">
                                <div class="d-inline-flex align-items-center gap-1">
                                    {{-- Quick Edit in Form --}}
                                    <button type="button" class="btn btn-sm btn-light border text-primary" onclick="loadUserForEdit({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}', '{{ addslashes($user->chat_badge ?? '') }}', '{{ $user->badge_color ?? 'primary' }}', {{ $user->is_verified ? 1 : 0 }})" title="Edit in form">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </button>

                                    {{-- Clear Badge --}}
                                    <form method="POST" action="{{ route('admin.chat.update-badge') }}" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                                        <input type="hidden" name="is_verified" value="0">
                                        <input type="hidden" name="chat_badge" value="">
                                        <input type="hidden" name="badge_color" value="primary">
                                        @if($user->chat_badge || $user->is_verified)
                                        <button type="submit" class="btn btn-sm btn-light border text-danger" onclick="return confirm('Remove badge and verification tick from {{ addslashes($user->name) }}?')">
                                            <i class="bi bi-trash3"></i> Clear
                                        </button>
                                        @endif
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-secondary py-5">
                                <div class="p-3 bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width:56px;height:56px;">
                                    <i class="bi bi-award fs-3 text-muted"></i>
                                </div>
                                <h6 class="fw-bold text-dark mb-1">No Badged Traders Found</h6>
                                <p class="text-muted small mb-0">Use the form above to assign custom titles and verification ticks to traders.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($users->hasPages())
        <div class="card-footer bg-white border-top py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <small class="text-muted fw-medium">Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ number_format($users->total()) }} traders</small>
            <div>{{ $users->links() }}</div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .user-select-btn {
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
        cursor: pointer;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .user-select-btn:focus, .user-select-btn.active {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
    }
    .user-dropdown-panel {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 1050;
        margin-top: 4px;
        border-color: #e2e8f0 !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
    }
    .user-option-item {
        cursor: pointer;
        transition: background-color 0.12s ease;
    }
    .user-option-item:hover, .user-option-item.selected {
        background-color: #eff6ff;
    }
    .preset-chip {
        font-size: 0.68rem;
        padding: 0.12rem 0.45rem;
        border-radius: 20px;
        background: #f1f5f9;
        color: #334155;
        border: 1px solid #e2e8f0;
        font-weight: 600;
        transition: all 0.12s ease;
    }
    .preset-chip:hover {
        background: #e2e8f0;
        color: #0f172a;
        transform: translateY(-1px);
    }
    .hover-primary:hover {
        color: #2563eb !important;
    }
</style>
@endpush

@push('scripts')
<script>
    const userSelectBtn = document.getElementById('userSelectBtn');
    const userDropdownPanel = document.getElementById('userDropdownPanel');
    const userSearchInput = document.getElementById('userSearchInput');
    const selectedUserId = document.getElementById('selectedUserId');
    const selectedUserPreview = document.getElementById('selectedUserPreview');
    const badgeTitleInput = document.getElementById('badgeTitleInput');
    const badgeColorSelect = document.getElementById('badgeColorSelect');
    const assignVerified = document.getElementById('assignVerified');
    const userOptionsList = document.getElementById('userOptionsList');
    const noUsersMatch = document.getElementById('noUsersMatch');

    // Toggle dropdown
    userSelectBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        const isOpen = userDropdownPanel.style.display === 'block';
        if (isOpen) {
            closeDropdown();
        } else {
            openDropdown();
        }
    });

    function openDropdown() {
        userDropdownPanel.style.display = 'block';
        userSelectBtn.classList.add('active');
        userSearchInput.value = '';
        filterUsers('');
        setTimeout(() => userSearchInput.focus(), 50);
    }

    function closeDropdown() {
        userDropdownPanel.style.display = 'none';
        userSelectBtn.classList.remove('active');
    }

    // Close when clicking outside
    document.addEventListener('click', function(e) {
        if (!document.getElementById('userSelectWrapper').contains(e.target)) {
            closeDropdown();
        }
    });

    // Close on Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && userDropdownPanel.style.display === 'block') {
            closeDropdown();
        }
    });

    // Search filter
    userSearchInput.addEventListener('input', function() {
        filterUsers(this.value.trim().toLowerCase());
    });

    function filterUsers(term) {
        const items = userOptionsList.querySelectorAll('.user-option-item');
        let visibleCount = 0;
        items.forEach(item => {
            const name = item.dataset.name.toLowerCase();
            const email = item.dataset.email.toLowerCase();
            const badge = (item.dataset.badge || '').toLowerCase();
            if (name.includes(term) || email.includes(term) || badge.includes(term)) {
                item.style.display = 'flex';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });
        noUsersMatch.style.display = visibleCount === 0 ? 'block' : 'none';
    }

    function clearUserSearch() {
        userSearchInput.value = '';
        filterUsers('');
        userSearchInput.focus();
    }

    // User selected
    function selectUser(el) {
        const id = el.dataset.id;
        const name = el.dataset.name;
        const email = el.dataset.email;
        const badge = el.dataset.badge;
        const color = el.dataset.color || 'primary';
        const verified = el.dataset.verified === '1';

        loadUserForEdit(id, name, email, badge, color, verified);
        closeDropdown();
    }

    function loadUserForEdit(id, name, email, badge, color, verified) {
        selectedUserId.value = id;
        const initial = (name || 'U').charAt(0).toUpperCase();

        selectedUserPreview.innerHTML = `
            <div class="user-mini-avatar rounded-circle text-white fw-bold d-flex align-items-center justify-content-center flex-shrink-0" style="width:24px;height:24px;font-size:0.75rem;background:#059669;">
                ${initial}
            </div>
            <div class="text-truncate">
                <span class="fw-bold text-dark small">${escapeHtml(name)}</span>
                <span class="text-muted" style="font-size:0.75rem;">(${escapeHtml(email)})</span>
            </div>
        `;

        if (badge) badgeTitleInput.value = badge;
        if (color) badgeColorSelect.value = color;
        assignVerified.checked = Boolean(verified);

        // Highlight selected in list
        document.querySelectorAll('.user-option-item').forEach(item => {
            if (item.dataset.id === String(id)) {
                item.classList.add('selected');
            } else {
                item.classList.remove('selected');
            }
        });

        // Scroll to form if clicked from table
        document.getElementById('badgeForm').scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function applyPreset(title, color) {
        badgeTitleInput.value = title;
        if (color) badgeColorSelect.value = color;
        assignVerified.checked = true;
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }
</script>
@endpush
