@extends('layouts.app')

@section('title', 'Notification Controller')

@push('styles')
<style>
    .notif-category-card {
        background: #1a1a2e;
        border: 1px solid #2a2a4a;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .notif-category-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid #2a2a4a;
    }
    .notif-category-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 16px;
        font-weight: 600;
        color: #fff;
    }
    .notif-category-icon {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }
    .notif-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        background: #0d0d1a;
        border-radius: 8px;
        margin-bottom: 8px;
        transition: all 0.2s;
    }
    .notif-item:hover {
        background: #151530;
    }
    .notif-item-info {
        display: flex;
        align-items: center;
        gap: 12px;
        flex: 1;
    }
    .notif-item-icon {
        font-size: 18px;
        opacity: 0.7;
    }
    .notif-item-details h6 {
        margin: 0;
        font-size: 14px;
        color: #e0e0e0;
    }
    .notif-item-details p {
        margin: 2px 0 0;
        font-size: 12px;
        color: #888;
    }
    .notif-toggle {
        position: relative;
        width: 48px;
        height: 26px;
    }
    .notif-toggle input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .notif-toggle .slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background: #333;
        border-radius: 26px;
        transition: 0.3s;
    }
    .notif-toggle .slider:before {
        content: '';
        position: absolute;
        height: 20px;
        width: 20px;
        left: 3px;
        bottom: 3px;
        background: #fff;
        border-radius: 50%;
        transition: 0.3s;
    }
    .notif-toggle input:checked + .slider {
        background: #D4A843;
    }
    .notif-toggle input:checked + .slider:before {
        transform: translateX(22px);
    }
    .stats-bar {
        display: flex;
        gap: 16px;
        margin-bottom: 24px;
    }
    .stat-card {
        background: #1a1a2e;
        border: 1px solid #2a2a4a;
        border-radius: 10px;
        padding: 16px 24px;
        flex: 1;
        text-align: center;
    }
    .stat-card h3 {
        margin: 0;
        font-size: 28px;
        font-weight: 700;
    }
    .stat-card p {
        margin: 4px 0 0;
        font-size: 13px;
        color: #888;
    }
    .btn-category-toggle {
        font-size: 12px;
        padding: 4px 12px;
        border-radius: 20px;
        border: 1px solid #444;
        background: transparent;
        color: #aaa;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-category-toggle:hover {
        border-color: #D4A843;
        color: #D4A843;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1" style="color: #D4A843;">
            <i class="bi bi-bell-slash-fill me-2"></i>Notification Controller
        </h4>
        <p class="text-muted mb-0">Control which notifications are sent to users</p>
    </div>
</div>

<!-- Stats -->
<div class="stats-bar" id="statsBar">
    <div class="stat-card">
        <h3 style="color: #D4A843;" id="statTotal">0</h3>
        <p>Total Types</p>
    </div>
    <div class="stat-card">
        <h3 style="color: #4CAF50;" id="statEnabled">0</h3>
        <p>Enabled</p>
    </div>
    <div class="stat-card">
        <h3 style="color: #f44336;" id="statDisabled">0</h3>
        <p>Disabled</p>
    </div>
</div>

<!-- Categories -->
@foreach($grouped as $category => $settings)
    @php $catInfo = $categories[$category] ?? ['name' => $category, 'icon' => 'bi-bell', 'color' => '#666']; @endphp
    <div class="notif-category-card">
        <div class="notif-category-header">
            <div class="notif-category-title">
                <div class="notif-category-icon" style="background: {{ $catInfo['color'] }}20; color: {{ $catInfo['color'] }};">
                    <i class="bi {{ $catInfo['icon'] }}"></i>
                </div>
                {{ $catInfo['name'] }}
                <span class="badge bg-secondary ms-2" style="font-size: 11px;">{{ count($settings) }} types</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button class="btn-category-toggle" onclick="toggleCategory('{{ $category }}', true)">
                    <i class="bi bi-check-all"></i> Enable All
                </button>
                <button class="btn-category-toggle" onclick="toggleCategory('{{ $category }}', false)">
                    <i class="bi bi-x-lg"></i> Disable All
                </button>
            </div>
        </div>

        @foreach($settings as $setting)
        <div class="notif-item" id="item-{{ $setting['slug'] }}">
            <div class="notif-item-info">
                <div class="notif-item-icon" style="color: {{ $catInfo['color'] }};">
                    <i class="bi {{ $setting['icon'] ?? 'bi-bell' }}"></i>
                </div>
                <div class="notif-item-details">
                    <h6>{{ $setting['name'] }}</h6>
                    <p>{{ $setting['description'] }}</p>
                </div>
            </div>
            <label class="notif-toggle">
                <input type="checkbox" {{ $setting['is_enabled'] ? 'checked' : '' }}
                    onchange="toggleSetting('{{ $setting['slug'] }}', this.checked)">
                <span class="slider"></span>
            </label>
        </div>
        @endforeach
    </div>
    @endforeach
@endsection

@push('scripts')
<script>
function loadStats() {
    fetch('{{ route("notification-settings.stats") }}')
        .then(r => r.json())
        .then(data => {
            document.getElementById('statTotal').textContent = data.total;
            document.getElementById('statEnabled').textContent = data.enabled;
            document.getElementById('statDisabled').textContent = data.disabled;
        });
}

function toggleSetting(slug, enabled) {
    fetch(`/admin/notification-settings/${slug}/toggle`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            loadStats();
        }
    })
    .catch(err => {
        showToast('Failed to update setting', 'error');
        loadStats();
    });
}

function toggleCategory(category, isEnabled) {
    fetch('{{ route("notification-settings.toggle-all") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ category, is_enabled: isEnabled })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            // Update all toggles in this category visually
            document.querySelectorAll(`.notif-category-card`).forEach(card => {
                const header = card.querySelector('.notif-category-title');
                if (header && header.textContent.includes(category.charAt(0).toUpperCase() + category.slice(1))) {
                    card.querySelectorAll('.notif-toggle input').forEach(input => {
                        input.checked = isEnabled;
                    });
                }
            });
            // Reload to sync properly
            setTimeout(() => location.reload(), 500);
        }
    })
    .catch(err => {
        showToast('Failed to update category', 'error');
    });
}

function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; animation: slideIn 0.3s ease;';
    toast.innerHTML = `<i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>${message}`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// Load stats on page load
loadStats();
</script>
@endpush
