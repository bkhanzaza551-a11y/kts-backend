<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - KTS Markets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.min.css" rel="stylesheet">
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

        /* Custom SweetAlert2 Theme */
        div.swal2-container {
            backdrop-filter: blur(5px) !important;
            -webkit-backdrop-filter: blur(5px) !important;
            background: rgba(15, 23, 42, 0.5) !important;
            z-index: 99999 !important;
        }
        div.swal2-popup.custom-swal-modal {
            border-radius: 20px !important;
            padding: 2rem 1.75rem !important;
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.35) !important;
            font-family: 'Inter', sans-serif !important;
            border: 1px solid rgba(226, 232, 240, 0.9) !important;
            background: #ffffff !important;
        }
        div.swal2-icon {
            margin: 0.5rem auto 1.25rem !important;
            scale: 0.95;
            border-width: 3px !important;
        }
        .swal2-title.custom-swal-title {
            font-size: 1.3rem !important;
            font-weight: 700 !important;
            color: #0f172a !important;
            padding: 0 0 0.5rem 0 !important;
            line-height: 1.4 !important;
        }
        .swal2-html-container.custom-swal-content {
            font-size: 0.92rem !important;
            color: #64748b !important;
            margin: 0 0 1.5rem 0 !important;
            line-height: 1.55 !important;
        }
        .custom-swal-confirm {
            border-radius: 10px !important;
            padding: 0.65rem 1.5rem !important;
            font-weight: 600 !important;
            font-size: 0.9rem !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.12) !important;
            transition: all 0.15s ease !important;
        }
        .custom-swal-confirm:hover {
            transform: translateY(-1px);
        }
        .custom-swal-cancel {
            border-radius: 10px !important;
            padding: 0.65rem 1.4rem !important;
            font-weight: 600 !important;
            font-size: 0.9rem !important;
            color: #475569 !important;
            background-color: #f1f5f9 !important;
            border: 1px solid #e2e8f0 !important;
            margin-right: 0.75rem !important;
            transition: all 0.15s ease !important;
        }
        .custom-swal-cancel:hover {
            background-color: #e2e8f0 !important;
            color: #0f172a !important;
        }
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

        .admin-topbar {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 1030;
            height: 64px;
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        }

        .global-search-wrapper {
            position: relative;
            flex: 1;
            max-width: 520px;
        }
        .global-search-input-group {
            position: relative;
            display: flex;
            align-items: center;
        }
        .global-search-input-group .search-icon {
            position: absolute;
            left: 14px;
            color: #94a3b8;
            font-size: 0.95rem;
            pointer-events: none;
            z-index: 2;
        }
        .global-search-input {
            width: 100%;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.52rem 4.2rem 0.52rem 2.4rem;
            font-size: 0.85rem;
            color: #0f172a;
            transition: all 0.15s ease;
        }
        .global-search-input:focus {
            background: #ffffff;
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.12);
            outline: none;
        }
        .search-shortcut-badge {
            position: absolute;
            right: 12px;
            background: #e2e8f0;
            color: #64748b;
            font-size: 0.68rem;
            font-family: inherit;
            font-weight: 600;
            padding: 2px 7px;
            border-radius: 5px;
            pointer-events: none;
        }
        .search-spinner {
            position: absolute;
            right: 12px;
            width: 1rem;
            height: 1rem;
        }
        .search-results-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            right: 0;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            box-shadow: 0 16px 36px rgba(15, 23, 42, 0.14), 0 2px 6px rgba(15, 23, 42, 0.04);
            max-height: 440px;
            overflow-y: auto;
            z-index: 1050;
            display: none;
        }
        .search-results-dropdown.show {
            display: block;
            animation: searchFadeIn 0.16s ease-out;
        }
        @keyframes searchFadeIn {
            from { opacity: 0; transform: translateY(-4px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .search-category-header {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #94a3b8;
            padding: 0.6rem 0.95rem 0.35rem;
            background: #f8fafc;
            border-top: 1px solid #f1f5f9;
        }
        .search-category-header:first-child {
            border-top: none;
        }
        .search-result-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.65rem 0.95rem;
            text-decoration: none;
            color: #0f172a;
            transition: background 0.12s ease;
            cursor: pointer;
            border-bottom: 1px solid #f8fafc;
        }
        .search-result-item:last-child {
            border-bottom: none;
        }
        .search-result-item:hover, .search-result-item.is-selected {
            background: #f1f5f9;
        }
        .search-item-left {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            min-width: 0;
        }
        .search-item-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: #f1f5f9;
            color: #0f172a;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            flex-shrink: 0;
        }
        .search-item-title {
            font-size: 0.84rem;
            font-weight: 600;
            color: #0f172a;
            line-height: 1.25;
            margin-bottom: 2px;
        }
        .search-item-subtitle {
            font-size: 0.74rem;
            color: #64748b;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 320px;
        }
        .avatar-online-dot {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 9px;
            height: 9px;
            background-color: #10b981;
            border: 2px solid #ffffff;
            border-radius: 50%;
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
    </nav>
    @endauth

    <div class="main-content" id="mainContent">
        @auth
        <header class="admin-topbar">
            {{-- Left: Mobile Sidebar Toggle + Global Search Bar --}}
            <div class="d-flex align-items-center gap-2 gap-md-3 flex-grow-1" style="max-width: 580px;">
                <button class="btn btn-sm btn-light border d-lg-none py-1 px-2" onclick="toggleSidebar()" type="button" aria-label="Toggle sidebar">
                    <i class="bi bi-list fs-5"></i>
                </button>

                {{-- Global Search Bar with Live Suggestions --}}
                <div class="global-search-wrapper" id="globalSearchWrapper">
                    <div class="global-search-input-group">
                        <i class="bi bi-search search-icon"></i>
                        <input type="text" id="globalSearchInput" class="global-search-input" placeholder="Search users, signals, bots, tickets, pages..." autocomplete="off" spellcheck="false">
                        <span class="search-shortcut-badge d-none d-md-inline" title="Press Ctrl+K or / to search">Ctrl K</span>
                        <div class="spinner-border spinner-border-sm text-primary search-spinner d-none" id="searchSpinner" role="status"></div>
                    </div>

                    {{-- Live Suggestions Floating Dropdown --}}
                    <div class="search-results-dropdown" id="searchResultsDropdown">
                        <div id="searchResultsContent"></div>
                    </div>
                </div>
            </div>

            {{-- Right: Currency Switcher & Super Admin Profile Dropdown --}}
            <div class="d-flex align-items-center gap-2">
                {{-- Currency Switcher Dropdown --}}
                <div class="dropdown">
                    <button class="btn btn-sm btn-light border dropdown-toggle d-flex align-items-center gap-1 gap-md-2 fw-semibold px-2 py-1" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius: 9px; font-size: 0.82rem;">
                        <i class="bi bi-currency-exchange text-primary"></i>
                        <span class="d-none d-sm-inline">{{ \App\Services\CurrencyService::getCurrentCurrency() }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border py-1" style="min-width: 190px; border-radius: 12px;">
                        <li class="dropdown-header text-uppercase text-secondary fw-bold" style="font-size: 0.68rem; letter-spacing: 0.5px;">Display Currency</li>
                        <li>
                            <a class="dropdown-item currency-option d-flex align-items-center justify-content-between py-2 {{ \App\Services\CurrencyService::getCurrentCurrency() === 'USD' ? 'active fw-bold' : '' }}" href="#" data-currency="USD">
                                <span><i class="bi bi-currency-dollar me-2 text-success"></i>USD (US Dollar)</span>
                                @if(\App\Services\CurrencyService::getCurrentCurrency() === 'USD')
                                    <i class="bi bi-check2 text-primary"></i>
                                @endif
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item currency-option d-flex align-items-center justify-content-between py-2 {{ \App\Services\CurrencyService::getCurrentCurrency() === 'USDT' ? 'active fw-bold' : '' }}" href="#" data-currency="USDT">
                                <span><i class="bi bi-currency-bitcoin me-2 text-warning"></i>USDT (Tether)</span>
                                @if(\App\Services\CurrencyService::getCurrentCurrency() === 'USDT')
                                    <i class="bi bi-check2 text-primary"></i>
                                @endif
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item currency-option d-flex align-items-center justify-content-between py-2 {{ \App\Services\CurrencyService::getCurrentCurrency() === 'PKR' ? 'active fw-bold' : '' }}" href="#" data-currency="PKR">
                                <span><i class="bi bi-cash me-2 text-info"></i>PKR (Pak Rupee)</span>
                                @if(\App\Services\CurrencyService::getCurrentCurrency() === 'PKR')
                                    <i class="bi bi-check2 text-primary"></i>
                                @endif
                            </a>
                        </li>
                    </ul>
                </div>

                {{-- Super Admin Profile Dropdown --}}
                <div class="dropdown">
                    <button class="btn btn-sm btn-light border dropdown-toggle d-flex align-items-center gap-2 p-1 pe-2" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius: 25px;">
                        <div class="position-relative">
                            @if(auth()->user()->avatar)
                                <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                            @endif
                            <span class="avatar-online-dot"></span>
                        </div>
                        <div class="text-start d-none d-md-block pe-1">
                            <div class="fw-bold text-dark lh-1" style="font-size: 0.82rem;">{{ auth()->user()->name }}</div>
                            <small class="text-secondary" style="font-size: 0.68rem;">{{ auth()->user()->roles->pluck('name')->first() ?? 'Admin' }}</small>
                        </div>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border p-2" style="min-width: 240px; border-radius: 14px;">
                        <li class="px-3 py-2 bg-light rounded-3 mb-2">
                            <div class="fw-bold text-dark text-truncate">{{ auth()->user()->name }}</div>
                            <div class="text-secondary small text-truncate">{{ auth()->user()->email }}</div>
                            <div class="mt-1">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.68rem;">
                                    <i class="bi bi-shield-check me-1"></i>{{ auth()->user()->roles->pluck('name')->join(', ') }}
                                </span>
                            </div>
                        </li>
                        <li>
                            <a class="dropdown-item rounded-2 py-2 d-flex align-items-center" href="{{ route('admin.profile.index') }}">
                                <i class="bi bi-person-circle text-primary me-2 fs-6"></i>
                                <span>My Admin Profile</span>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item rounded-2 py-2 d-flex align-items-center" href="{{ route('admin.security.change-form') }}">
                                <i class="bi bi-shield-lock text-warning me-2 fs-6"></i>
                                <span>Security PIN & OTP</span>
                            </a>
                        </li>
                        @if(auth()->user()->hasPermission('settings_view'))
                        <li>
                            <a class="dropdown-item rounded-2 py-2 d-flex align-items-center" href="{{ route('admin.settings.index') }}">
                                <i class="bi bi-gear text-secondary me-2 fs-6"></i>
                                <span>System Settings</span>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item rounded-2 py-2 d-flex align-items-center" href="{{ route('admin.audit-logs.index') }}">
                                <i class="bi bi-journal-text text-info me-2 fs-6"></i>
                                <span>Activity & Audit Logs</span>
                            </a>
                        </li>
                        @endif
                        <li><hr class="dropdown-divider my-2"></li>
                        <li>
                            <form method="POST" action="{{ route('admin.logout') }}" id="topbarLogoutForm">
                                @csrf
                                <button type="submit" class="dropdown-item rounded-2 py-2 text-danger fw-semibold d-flex align-items-center">
                                    <i class="bi bi-box-arrow-left me-2 fs-6"></i>
                                    <span>Sign Out</span>
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>
        @endauth

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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        }

        // Global Beautiful SweetAlert2 Confirm Handler
        (function() {
            function showStyledConfirm(message, onConfirm) {
                const lowerMsg = (message || '').toLowerCase();
                let icon = 'question';
                let iconColor = '#0d6efd';
                let confirmBtnClass = 'btn btn-primary custom-swal-confirm';
                let confirmBtnText = 'Yes, Confirm';
                let title = message || 'Are you sure?';
                let subtitle = 'Please confirm if you would like to proceed.';

                if (lowerMsg.includes('approve') || lowerMsg.includes('activate') || lowerMsg.includes('restore') || lowerMsg.includes('unban') || lowerMsg.includes('resume') || lowerMsg.includes('publish') || lowerMsg.includes('link')) {
                    icon = 'success';
                    iconColor = '#10b981';
                    confirmBtnClass = 'btn btn-success custom-swal-confirm';
                    confirmBtnText = lowerMsg.includes('approve') ? 'Yes, Approve' : (lowerMsg.includes('publish') ? 'Yes, Publish' : (lowerMsg.includes('link') ? 'Yes, Link Account' : 'Yes, Confirm'));
                    subtitle = 'This action will approve the request and update the status immediately.';
                } else if (lowerMsg.includes('delete') || lowerMsg.includes('permanently') || lowerMsg.includes('ban') || lowerMsg.includes('destroy') || lowerMsg.includes('clear') || lowerMsg.includes('remove')) {
                    icon = 'warning';
                    iconColor = '#ef4444';
                    confirmBtnClass = 'btn btn-danger custom-swal-confirm';
                    confirmBtnText = lowerMsg.includes('delete') ? 'Yes, Delete' : (lowerMsg.includes('ban') ? 'Yes, Ban User' : 'Yes, Remove');
                    subtitle = lowerMsg.includes('permanently') ? 'Warning: This action is permanent and cannot be undone.' : 'Are you sure you want to proceed with this deletion?';
                } else if (lowerMsg.includes('reject') || lowerMsg.includes('pause') || lowerMsg.includes('disable') || lowerMsg.includes('unpublish') || lowerMsg.includes('close') || lowerMsg.includes('stop')) {
                    icon = 'warning';
                    iconColor = '#f59e0b';
                    confirmBtnClass = 'btn btn-warning text-dark custom-swal-confirm';
                    confirmBtnText = lowerMsg.includes('reject') ? 'Yes, Reject' : (lowerMsg.includes('pause') ? 'Yes, Pause' : 'Yes, Proceed');
                    subtitle = 'This will update the record and apply changes immediately.';
                }

                Swal.fire({
                    title: title,
                    text: subtitle,
                    icon: icon,
                    iconColor: iconColor,
                    showCancelButton: true,
                    confirmButtonText: confirmBtnText,
                    cancelButtonText: 'Cancel',
                    reverseButtons: true,
                    focusCancel: false,
                    customClass: {
                        popup: 'custom-swal-modal',
                        title: 'custom-swal-title',
                        htmlContainer: 'custom-swal-content',
                        confirmButton: confirmBtnClass,
                        cancelButton: 'custom-swal-cancel'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed && typeof onConfirm === 'function') {
                        onConfirm();
                    }
                });
            }

            // Expose globally for manual calls
            window.showStyledConfirm = showStyledConfirm;

            // Intercept form submissions
            document.addEventListener('submit', function(e) {
                const form = e.target;
                if (form.dataset.swalConfirmed === 'true') {
                    return;
                }

                const onsubmitAttr = form.getAttribute('onsubmit') || '';
                const match = onsubmitAttr.match(/confirm\(['"`](.*?)['"`]\)/i);
                let message = null;

                if (match && match[1]) {
                    message = match[1];
                } else if (form.dataset.confirm) {
                    message = form.dataset.confirm;
                }

                if (message) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();

                    showStyledConfirm(message, function() {
                        form.dataset.swalConfirmed = 'true';
                        form.submit();
                    });
                    return false;
                }
            }, true);

            // Intercept button/link clicks
            document.addEventListener('click', function(e) {
                const target = e.target.closest('button, a, input[type="submit"]');
                if (!target) return;

                if (target.dataset.swalConfirmed === 'true') {
                    return;
                }

                const onclickAttr = target.getAttribute('onclick') || '';
                const match = onclickAttr.match(/confirm\(['"`](.*?)['"`]\)/i);
                let message = null;

                if (match && match[1]) {
                    message = match[1];
                } else if (target.dataset.confirm) {
                    message = target.dataset.confirm;
                }

                if (message) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();

                    showStyledConfirm(message, function() {
                        target.dataset.swalConfirmed = 'true';
                        if (target.type === 'submit' && target.form) {
                            target.form.dataset.swalConfirmed = 'true';
                            target.form.submit();
                        } else if (target.tagName === 'A' && target.href) {
                            window.location.href = target.href;
                        } else {
                            target.click();
                        }
                    });
                    return false;
                }
            }, true);
        })();

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

            // Global Search Suggestions & Keyboard Navigation
            (function() {
                const searchInput = document.getElementById('globalSearchInput');
                const searchWrapper = document.getElementById('globalSearchWrapper');
                const resultsDropdown = document.getElementById('searchResultsDropdown');
                const resultsContent = document.getElementById('searchResultsContent');
                const searchSpinner = document.getElementById('searchSpinner');

                if (!searchInput || !resultsDropdown || !resultsContent) return;

                let debounceTimer = null;
                let selectedIndex = -1;
                let currentItems = [];

                // Keyboard shortcut: Ctrl+K, Cmd+K, or / to focus search
                document.addEventListener('keydown', function(e) {
                    if ((e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === 'K')) {
                        e.preventDefault();
                        searchInput.focus();
                        searchInput.select();
                    } else if (e.key === '/' && document.activeElement !== searchInput && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) {
                        e.preventDefault();
                        searchInput.focus();
                        searchInput.select();
                    }
                });

                function escapeHtml(str) {
                    if (!str) return '';
                    return String(str)
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#039;');
                }

                function showResults(data) {
                    if (!data || !data.results || data.results.length === 0) {
                        resultsContent.innerHTML = `
                            <div class="text-center py-4 px-3 text-secondary">
                                <i class="bi bi-search fs-3 text-muted mb-2 d-block"></i>
                                <div class="fw-semibold text-dark small">No matching results found</div>
                                <div class="text-muted" style="font-size: 0.75rem;">Try searching for user name, signal symbol, ticket #, or module.</div>
                            </div>
                        `;
                        resultsDropdown.classList.add('show');
                        currentItems = [];
                        selectedIndex = -1;
                        return;
                    }

                    let html = '';
                    currentItems = [];

                    data.results.forEach(category => {
                        html += `<div class="search-category-header"><i class="bi ${category.icon || 'bi-folder'} me-1"></i>${category.category}</div>`;
                        if (category.items && category.items.length > 0) {
                            category.items.forEach(item => {
                                const itemIndex = currentItems.length;
                                currentItems.push(item);
                                html += `
                                    <a href="${item.url}" class="search-result-item" data-index="${itemIndex}">
                                        <div class="search-item-left">
                                            <div class="search-item-icon">
                                                <i class="bi ${item.icon || 'bi-arrow-right'}"></i>
                                            </div>
                                            <div style="min-width: 0;">
                                                <div class="search-item-title">${escapeHtml(item.title)}</div>
                                                <div class="search-item-subtitle">${escapeHtml(item.subtitle || '')}</div>
                                            </div>
                                        </div>
                                        ${item.badge ? `<span class="badge ${item.badge_class || 'bg-light text-secondary border'} px-2 py-1 ms-2 flex-shrink-0" style="font-size:0.68rem;">${escapeHtml(item.badge)}</span>` : ''}
                                    </a>
                                `;
                            });
                        }
                    });

                    resultsContent.innerHTML = html;
                    resultsDropdown.classList.add('show');
                    selectedIndex = -1;
                }

                function fetchSuggestions(query) {
                    if (searchSpinner) searchSpinner.classList.remove('d-none');
                    fetch(`{{ route('admin.global-search') }}?q=${encodeURIComponent(query)}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (searchSpinner) searchSpinner.classList.add('d-none');
                        showResults(data);
                    })
                    .catch(err => {
                        if (searchSpinner) searchSpinner.classList.add('d-none');
                        console.error('Search error:', err);
                    });
                }

                searchInput.addEventListener('focus', function() {
                    fetchSuggestions(this.value.trim());
                });

                searchInput.addEventListener('input', function() {
                    clearTimeout(debounceTimer);
                    const q = this.value.trim();
                    debounceTimer = setTimeout(() => {
                        fetchSuggestions(q);
                    }, 160);
                });

                searchInput.addEventListener('keydown', function(e) {
                    if (!resultsDropdown.classList.contains('show')) return;
                    const domItems = resultsContent.querySelectorAll('.search-result-item');
                    if (domItems.length === 0) return;

                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        selectedIndex = (selectedIndex + 1) % domItems.length;
                        updateSelection(domItems);
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        selectedIndex = (selectedIndex - 1 + domItems.length) % domItems.length;
                        updateSelection(domItems);
                    } else if (e.key === 'Enter') {
                        e.preventDefault();
                        if (selectedIndex >= 0 && selectedIndex < domItems.length) {
                            domItems[selectedIndex].click();
                        } else if (domItems.length > 0) {
                            domItems[0].click();
                        }
                    } else if (e.key === 'Escape') {
                        resultsDropdown.classList.remove('show');
                        searchInput.blur();
                    }
                });

                function updateSelection(domItems) {
                    domItems.forEach((el, idx) => {
                        if (idx === selectedIndex) {
                            el.classList.add('is-selected');
                            el.scrollIntoView({ block: 'nearest' });
                        } else {
                            el.classList.remove('is-selected');
                        }
                    });
                }

                document.addEventListener('click', function(e) {
                    if (!searchWrapper.contains(e.target)) {
                        resultsDropdown.classList.remove('show');
                    }
                });
            })();
        });
    </script>
    @stack('scripts')
</body>
</html>
