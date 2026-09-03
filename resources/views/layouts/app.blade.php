<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - KTS Markets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 250px;
            --primary: #0f172a;
            --primary-light: #334155;
            --primary-dark: #000000;
            --sidebar-bg: #ffffff;
            --sidebar-border: #e2e8f0;
            --sidebar-text: #475569;
            --sidebar-hover: #f8fafc;
            --sidebar-active: #f1f5f9;
            --body-bg: #f8fafc;
            --card-bg: #ffffff;
            --card-border: #e2e8f0;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #94a3b8;
            --border-color: #e2e8f0;
        }
        * { font-family: 'Inter', sans-serif; }
        body { 
            background-color: var(--body-bg); 
            color: var(--text-primary); 
            font-size: 0.875rem; 
            line-height: 1.5;
        }

        .sidebar {
            height: 100vh;
            width: var(--sidebar-width);
            transition: transform 0.2s ease;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1040;
            overflow-y: auto;
            overflow-x: hidden;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--sidebar-border);
        }
        .sidebar .nav-link {
            color: var(--sidebar-text);
            padding: 0.5rem 0.875rem;
            border-radius: 0.375rem;
            margin: 0.125rem 0.75rem;
            transition: all 0.15s ease;
            font-weight: 500;
            font-size: 0.85rem;
        }
        .sidebar .nav-link:hover {
            color: var(--text-primary);
            background-color: var(--sidebar-hover);
        }
        .sidebar .nav-link.active {
            color: var(--text-primary);
            background-color: var(--sidebar-active);
            font-weight: 600;
        }
        .sidebar .nav-link i {
            width: 20px;
            text-align: center;
            margin-right: 0.5rem;
            font-size: 0.95rem;
            color: var(--text-muted);
        }
        .sidebar .nav-link.active i {
            color: var(--text-primary);
        }

        .main-content {
            min-height: 100vh;
            background-color: var(--body-bg);
            margin-left: var(--sidebar-width);
            transition: margin-left 0.2s ease;
        }

        .stat-card {
            border: 1px solid var(--card-border);
            border-radius: 0.5rem;
            transition: all 0.2s ease;
            background: var(--card-bg);
            box-shadow: 0 1px 2px rgba(0,0,0,0.02);
        }
        .stat-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        }
        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 0.375rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f1f5f9 !important;
        }
        .stat-icon i {
            font-size: 1.1rem !important;
            color: var(--text-secondary) !important;
        }

        .card {
            border-radius: 0.5rem;
            border: 1px solid var(--card-border);
            background: var(--card-bg);
            box-shadow: 0 1px 2px rgba(0,0,0,0.02);
            overflow: hidden;
        }
        .card-header {
            background: var(--card-bg);
            border-bottom: 1px solid var(--border-color);
            padding: 0.875rem 1.25rem;
        }
        .card-header h6 {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .topbar {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 1030;
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15,23,42,0.4);
            z-index: 1035;
            backdrop-filter: blur(2px);
        }

        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0 !important; }
            .sidebar-overlay.show { display: block; }
        }

        .form-control, .form-select {
            border-radius: 0.375rem;
            padding: 0.45rem 0.75rem;
            border-color: #cbd5e1;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
            background-color: #fff;
            color: var(--text-primary);
            font-size: 0.875rem;
            box-shadow: 0 1px 2px rgba(0,0,0,0.01);
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 1px var(--primary-light);
            outline: none;
        }
        .form-control::placeholder { color: var(--text-muted); }
        .form-label { font-weight: 500; font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 0.35rem; }

        .btn {
            border-radius: 0.375rem;
            font-weight: 500;
            font-size: 0.85rem;
            padding: 0.45rem 0.875rem;
            transition: all 0.15s ease;
        }
        .btn-primary {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        .btn-primary:hover {
            background: var(--primary-light);
            border-color: var(--primary-light);
            color: #fff;
        }
        .btn-outline-primary {
            color: var(--text-primary);
            border-color: #cbd5e1;
            background: #fff;
            box-shadow: 0 1px 2px rgba(0,0,0,0.02);
        }
        .btn-outline-primary:hover {
            background: #f8fafc;
            border-color: #94a3b8;
            color: var(--text-primary);
        }
        .btn-outline-secondary {
            color: var(--text-secondary);
            border-color: #cbd5e1;
            background: #fff;
        }
        .btn-outline-secondary:hover {
            background: #f8fafc;
            color: var(--text-primary);
            border-color: #94a3b8;
        }

        .table {
            border-color: var(--border-color);
            color: var(--text-primary);
            margin-bottom: 0;
            font-size: 0.85rem;
        }
        .table thead th {
            border-bottom: 1px solid var(--border-color);
            font-weight: 500;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-secondary);
            background: #f8fafc;
            padding: 0.75rem;
        }
        .table tbody td {
            padding: 0.75rem;
            vertical-align: middle;
        }
        .table tbody tr {
            border-color: var(--border-color);
        }
        .table-hover tbody tr:hover {
            background-color: #f8fafc;
        }

        .badge {
            font-weight: 500;
            font-size: 0.75rem;
            padding: 0.35em 0.65em;
            border-radius: 0.25rem;
        }
        .badge.bg-success { background-color: #10b981 !important; color: #fff; }
        .badge.bg-danger { background-color: #ef4444 !important; color: #fff; }
        .badge.bg-warning { background-color: #f59e0b !important; color: #fff; }
        .badge.bg-secondary { background-color: #f1f5f9 !important; color: #475569 !important; border: 1px solid #e2e8f0; }

        .pagination .page-link {
            border-color: var(--border-color);
            color: var(--text-secondary);
            border-radius: 0.375rem;
            margin: 0 2px;
            font-size: 0.85rem;
        }
        .pagination .page-link:hover {
            background-color: var(--sidebar-hover);
            border-color: #cbd5e1;
            color: var(--text-primary);
        }
        .pagination .page-item.active .page-link {
            background-color: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }

        .input-group-text {
            border-radius: 0.375rem 0 0 0.375rem;
            border-color: #cbd5e1;
            background: #f8fafc;
            color: var(--text-secondary);
            font-size: 0.875rem;
        }
        .input-group .form-control {
            border-radius: 0 0.375rem 0.375rem 0;
        }

        .progress { border-radius: 0.25rem; height: 6px; }
        
        h1, h2, h3, h4, h5, h6 {
            color: var(--text-primary);
            font-weight: 600;
        }
        h4 { font-size: 1.25rem; }

        .alert {
            border-radius: 0.5rem;
            font-size: 0.875rem;
            border: 1px solid transparent;
        }
        .alert-success { border-color: #a7f3d0; background: #ecfdf5; color: #065f46; }
        .alert-danger { border-color: #fecaca; background: #fef2f2; color: #991b1b; }
        
        .fade-in { animation: fadeIn 0.3s ease-in; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }

        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 2px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
    @stack('styles')
</head>
<body>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    @auth
    <nav class="sidebar p-3 d-flex flex-column" id="sidebar">
        <div class="d-flex align-items-center justify-content-between mb-4 px-2">
            <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center text-decoration-none">
                <div class="rounded d-flex align-items-center justify-content-center me-2" style="width:36px;height:36px;background:var(--primary);">
                    <i class="bi bi-graph-up-arrow text-white fs-6"></i>
                </div>
                <div>
                    <span class="fw-bold d-block lh-1" style="color:var(--text-primary);font-size:0.95rem;">KTS Markets</span>
                    <small class="text-muted" style="font-size:0.65rem;">Super Admin Panel</small>
                </div>
            </a>
            <button class="btn btn-sm d-lg-none border-0" onclick="toggleSidebar()" style="color:var(--text-secondary);">
                <i class="bi bi-x-lg fs-5"></i>
            </button>
        </div>

        <div class="mb-2 px-3">
            <small class="text-uppercase fw-semibold" style="font-size:0.68rem;letter-spacing:0.06em;color:var(--text-muted);">Main Menu</small>
        </div>

        <div class="flex-grow-1 overflow-y-auto pe-1" style="min-height:0;">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2-fill"></i> Dashboard
                </a>
            </li>
            @if(auth()->user()->hasPermission('staff_view'))
            <li class="nav-item">
                <a href="{{ route('admin.staff.index') }}" class="nav-link {{ request()->routeIs('admin.staff.*') ? 'active' : '' }}">
                    <i class="bi bi-people-fill"></i> Staff Management
                </a>
            </li>
            @endif
            @if(auth()->user()->hasPermission('users_view'))
            <li class="nav-item">
                <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="bi bi-person-badge-fill"></i> User Management
                </a>
            </li>
            @endif
            @if(auth()->user()->hasPermission('roles_view'))
            <li class="nav-item">
                <a href="{{ route('admin.roles.index') }}" class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                    <i class="bi bi-shield-check"></i> Roles & Permissions
                </a>
            </li>
            @endif
            @if(auth()->user()->hasPermission('permissions_view'))
            <li class="nav-item">
                <a href="{{ route('admin.permissions.index') }}" class="nav-link {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}">
                    <i class="bi bi-key-fill"></i> Permissions
                </a>
            </li>
            @endif

            <div class="my-2 px-3">
                <small class="text-uppercase fw-semibold" style="font-size:0.68rem;letter-spacing:0.06em;color:var(--text-muted);">Trading</small>
            </div>

            @if(auth()->user()->hasPermission('signals_view'))
            <li class="nav-item">
                <a href="{{ route('admin.signals.index') }}" class="nav-link {{ request()->routeIs('admin.signals.*') ? 'active' : '' }}">
                    <i class="bi bi-broadcast"></i> Signals
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.analytics.signals') }}" class="nav-link {{ request()->routeIs('admin.analytics.signals') ? 'active' : '' }}">
                    <i class="bi bi-graph-up-arrow"></i> Signal Analytics
                </a>
            </li>
            @endif
            @if(auth()->user()->hasPermission('signal_categories_view'))
            <li class="nav-item">
                <a href="{{ route('admin.signal-categories.index') }}" class="nav-link {{ request()->routeIs('admin.signal-categories.*') ? 'active' : '' }}">
                    <i class="bi bi-tags-fill"></i> Signal Categories
                </a>
            </li>
            @endif
            @if(auth()->user()->hasPermission('mt5_bot_view'))
            <li class="nav-item">
                <a href="{{ route('admin.mt5-bot.index') }}" class="nav-link {{ request()->routeIs('admin.mt5-bot.*') ? 'active' : '' }}">
                    <i class="bi bi-robot"></i> MT5 Bot
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.analytics.mt5') }}" class="nav-link {{ request()->routeIs('admin.analytics.mt5') ? 'active' : '' }}">
                    <i class="bi bi-clipboard-data"></i> MT5 Analytics
                </a>
            </li>
            @endif

            <div class="my-2 px-3">
                <small class="text-uppercase fw-semibold" style="font-size:0.68rem;letter-spacing:0.06em;color:var(--text-muted);">Content</small>
            </div>

            @if(auth()->user()->hasPermission('education_view'))
            <li class="nav-item">
                <a href="{{ route('admin.courses.index') }}" class="nav-link {{ request()->routeIs('admin.courses.*') ? 'active' : '' }}">
                    <i class="bi bi-book-half"></i> Education
                </a>
            </li>
            @endif
            @if(auth()->user()->hasPermission('education_categories_view'))
            <li class="nav-item">
                <a href="{{ route('admin.education-categories.index') }}" class="nav-link {{ request()->routeIs('admin.education-categories.*') ? 'active' : '' }}">
                    <i class="bi bi-bookmark-star-fill"></i> Edu Categories
                </a>
            </li>
            @endif
            @if(auth()->user()->hasPermission('chat_view'))
            <li class="nav-item">
                <a href="{{ route('admin.chat.index') }}" class="nav-link {{ request()->routeIs('admin.chat.*') && !request()->routeIs('admin.chat.stickers*') ? 'active' : '' }}">
                    <i class="bi bi-chat-left-dots-fill"></i> Chat
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.chat.stickers.index') }}" class="nav-link {{ request()->routeIs('admin.chat.stickers*') ? 'active' : '' }}">
                    <i class="bi bi-emoji-smile-fill"></i> Stickers
                </a>
            </li>
            @endif
            @if(auth()->user()->hasPermission('ai_chatbot_view'))
            <li class="nav-item">
                <a href="{{ route('admin.ai-chatbot.settings') }}" class="nav-link {{ request()->routeIs('admin.ai-chatbot.*') ? 'active' : '' }}">
                    <i class="bi bi-cpu-fill"></i> AI Chatbot
                </a>
            </li>
            @endif
            @if(auth()->user()->hasPermission('chat_view'))
            <li class="nav-item">
                <a href="{{ route('admin.support-chat.index') }}" class="nav-link {{ request()->routeIs('admin.support-chat*') ? 'active' : '' }}">
                    <i class="bi bi-headset"></i> Support Chats
                </a>
            </li>
            @endif

            <div class="my-2 px-3">
                <small class="text-uppercase fw-semibold" style="font-size:0.68rem;letter-spacing:0.06em;color:var(--text-muted);">Finance</small>
            </div>

            @if(auth()->user()->hasPermission('notifications_view'))
            <li class="nav-item">
                <a href="{{ route('admin.notifications.index') }}" class="nav-link {{ request()->routeIs('admin.notifications.*') && !request()->routeIs('admin.notification-settings.*') ? 'active' : '' }}">
                    <i class="bi bi-bell-fill"></i> Notifications
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.notification-settings.index') }}" class="nav-link {{ request()->routeIs('admin.notification-settings.*') ? 'active' : '' }}">
                    <i class="bi bi-bell-slash-fill"></i> Notification Controller
                </a>
            </li>
            @endif
            @if(auth()->user()->hasPermission('transactions_view'))
            <li class="nav-item">
                <a href="{{ route('admin.payments.index') }}" class="nav-link {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
                    <i class="bi bi-credit-card-fill"></i> Payments
                </a>
            </li>
            @endif
            @if(auth()->user()->hasPermission('demo_accounts_view'))
            <li class="nav-item">
                <a href="{{ route('admin.demo-accounts.index') }}" class="nav-link {{ request()->routeIs('admin.demo-accounts.*') ? 'active' : '' }}">
                    <i class="bi bi-pc-display-horizontal"></i> Demo Accounts
                </a>
            </li>
            @endif
            @if(auth()->user()->hasPermission('demo_accounts_manage'))
            <li class="nav-item">
                <a href="{{ route('admin.demo-settings.index') }}" class="nav-link {{ request()->routeIs('admin.demo-settings.*') ? 'active' : '' }}">
                    <i class="bi bi-gear"></i> Demo Settings
                </a>
            </li>
            @endif

            <div class="my-2 px-3">
                <small class="text-uppercase fw-semibold" style="font-size:0.68rem;letter-spacing:0.06em;color:var(--text-muted);">System</small>
            </div>

            @if(auth()->user()->hasPermission('settings_view'))
            <li class="nav-item">
                <a href="{{ route('admin.legal-pages.index') }}" class="nav-link {{ request()->routeIs('admin.legal-pages.*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-text"></i> Legal Pages
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.audit-logs.index') }}" class="nav-link {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}">
                    <i class="bi bi-journal-arrow-down"></i> Audit Logs
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <i class="bi bi-gear-fill"></i> Settings
                </a>
            </li>
            @endif
        </ul>
        </div>

            <div class="mt-auto pt-3 border-top" style="border-color:var(--border-color)!important;">
            <div class="px-3 mb-2">
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle w-100 text-start" type="button" data-bs-toggle="dropdown" style="font-size:0.78rem;">
                        <i class="bi bi-currency-exchange me-2"></i>Currency: <strong>{{ \App\Services\CurrencyService::getCurrentCurrency() }}</strong>
                    </button>
                    <ul class="dropdown-menu shadow-sm" style="min-width:180px;">
                        <li><a class="dropdown-item currency-option {{ \App\Services\CurrencyService::getCurrentCurrency() === 'USD' ? 'active' : '' }}" href="#" data-currency="USD"><i class="bi bi-currency-dollar me-2"></i>USD - US Dollar</a></li>
                        <li><a class="dropdown-item currency-option {{ \App\Services\CurrencyService::getCurrentCurrency() === 'USDT' ? 'active' : '' }}" href="#" data-currency="USDT"><i class="bi bi-currency-bitcoin me-2"></i>USDT - Tether</a></li>
                        <li><a class="dropdown-item currency-option {{ \App\Services\CurrencyService::getCurrentCurrency() === 'PKR' ? 'active' : '' }}" href="#" data-currency="PKR"><i class="bi bi-cash me-2"></i>PKR - Pakistani Rupee</a></li>
                    </ul>
                </div>
            </div>
            <div class="d-flex align-items-center px-3 mb-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:36px;height:36px;background:var(--primary);">
                    <span class="text-white fw-bold" style="font-size: 0.85rem;">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                </div>
                <div class="ms-2 overflow-hidden">
                    <div class="fw-semibold text-truncate small" style="color:var(--text-primary);">{{ auth()->user()->name }}</div>
                    <div class="text-muted" style="font-size:0.7rem;">{{ auth()->user()->roles->pluck('name')->join(', ') }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="nav-link text-danger w-100 text-start border-0 bg-transparent py-2">
                    <i class="bi bi-box-arrow-left"></i> Logout
                </button>
            </form>
        </div>
    </nav>
    @endauth

    <div class="main-content" id="mainContent">
        <div class="topbar d-flex align-items-center justify-content-between px-4 py-2 d-lg-none">
            <button class="btn btn-sm border-0" onclick="toggleSidebar()" style="color:var(--text-secondary);">
                <i class="bi bi-list fs-4"></i>
            </button>
            <span class="fw-semibold small" style="color:var(--text-primary);">KTS Markets</span>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" style="font-size:0.75rem;">
                    <i class="bi bi-currency-exchange me-1"></i>{{ \App\Services\CurrencyService::getCurrentCurrency() }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="min-width:160px;">
                    <li><a class="dropdown-item currency-option {{ \App\Services\CurrencyService::getCurrentCurrency() === 'USD' ? 'active' : '' }}" href="#" data-currency="USD"><i class="bi bi-currency-dollar me-2"></i>USD</a></li>
                    <li><a class="dropdown-item currency-option {{ \App\Services\CurrencyService::getCurrentCurrency() === 'USDT' ? 'active' : '' }}" href="#" data-currency="USDT"><i class="bi bi-currency-bitcoin me-2"></i>USDT</a></li>
                    <li><a class="dropdown-item currency-option {{ \App\Services\CurrencyService::getCurrentCurrency() === 'PKR' ? 'active' : '' }}" href="#" data-currency="PKR"><i class="bi bi-cash me-2"></i>PKR</a></li>
                </ul>
            </div>
        </div>

        <div class="p-4">
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show fade-in" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show fade-in" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show fade-in" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        }
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.stat-card').forEach((card, i) => {
                card.style.animationDelay = (i * 0.05) + 's';
                card.classList.add('fade-in');
            });

            document.querySelectorAll('.currency-option').forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    const currency = this.dataset.currency;
                    fetch('{{ route("admin.currency.switch") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ currency: currency })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) location.reload();
                    })
                    .catch(err => console.error('Currency switch failed:', err));
                });
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
