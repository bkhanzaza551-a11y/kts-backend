<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Payment;
use App\Models\Signal;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    public function search(Request $request)
    {
        $q = trim($request->input('q', ''));
        if (strlen($q) < 1) {
            return response()->json([
                'results' => [
                    [
                        'category' => 'Quick Navigation',
                        'icon' => 'bi-lightning-charge-fill',
                        'items' => $this->getNavigationItems(''),
                    ]
                ]
            ]);
        }

        $needle = strtolower($q);
        $likePattern = "%{$needle}%";
        $results = [];

        // 1. Navigation / Menu items match
        $navMatches = $this->getNavigationItems($q);
        if (!empty($navMatches)) {
            $results[] = [
                'category' => 'Navigation & Modules',
                'icon' => 'bi-compass',
                'items' => array_slice($navMatches, 0, 5),
            ];
        }

        // 2. Users & Traders (Clients & Accounts)
        if ($request->user()->hasPermission('users_view')) {
            $users = User::with('roles')
                ->where(function($query) use ($likePattern) {
                    $query->whereRaw('LOWER(name) LIKE ?', [$likePattern])
                          ->orWhereRaw('LOWER(email) LIKE ?', [$likePattern])
                          ->orWhereRaw('LOWER(COALESCE(phone, \'\')) LIKE ?', [$likePattern])
                          ->orWhereRaw('LOWER(COALESCE(real_account_id, \'\')) LIKE ?', [$likePattern])
                          ->orWhereRaw('LOWER(COALESCE(demo_account_id, \'\')) LIKE ?', [$likePattern])
                          ->orWhereRaw('LOWER(COALESCE(broker_name, \'\')) LIKE ?', [$likePattern]);
                })
                ->limit(6)
                ->get();

            if ($users->isNotEmpty()) {
                $userItems = [];
                foreach ($users as $u) {
                    $roleName = $u->roles->pluck('name')->first() ?? ($u->isSuperAdmin() ? 'Super Admin' : 'Trader');
                    $subtitle = $u->email . ($u->real_account_id ? " • Real: {$u->real_account_id}" : ($u->demo_account_id ? " • Demo: {$u->demo_account_id}" : " • {$roleName}"));
                    
                    $badge = $u->is_banned ? 'Banned' : ($u->is_premium ? 'VIP' : ($u->isSuperAdmin() ? 'Admin' : 'User'));
                    $badgeClass = $u->is_banned ? 'bg-danger text-white' : ($u->is_premium ? 'bg-warning text-dark' : ($u->isSuperAdmin() ? 'bg-dark text-white' : 'bg-primary text-white'));
                    
                    $userItems[] = [
                        'title' => $u->name,
                        'subtitle' => $subtitle,
                        'badge' => $badge,
                        'badge_class' => $badgeClass,
                        'url' => route('admin.users.show', $u->id),
                        'icon' => 'bi-person-circle',
                    ];
                }
                $results[] = [
                    'category' => 'Users & Accounts',
                    'icon' => 'bi-people',
                    'items' => $userItems,
                ];
            }
        }

        // 3. Trading Signals
        if ($request->user()->hasPermission('signals_view')) {
            $signals = Signal::where(function($query) use ($likePattern) {
                    $query->whereRaw('LOWER(symbol) LIKE ?', [$likePattern])
                          ->orWhereRaw('LOWER(type) LIKE ?', [$likePattern]);
                })
                ->limit(4)
                ->latest()
                ->get();

            if ($signals->isNotEmpty()) {
                $signalItems = [];
                foreach ($signals as $s) {
                    $signalItems[] = [
                        'title' => "{$s->symbol} (" . strtoupper($s->type) . ")",
                        'subtitle' => "Status: {$s->status} • TP1: {$s->tp1} • SL: {$s->sl}",
                        'badge' => strtoupper($s->type),
                        'badge_class' => strtoupper($s->type) === 'BUY' ? 'bg-success text-white' : 'bg-danger text-white',
                        'url' => route('admin.signals.index', ['search' => $s->symbol]),
                        'icon' => 'bi-broadcast',
                    ];
                }
                $results[] = [
                    'category' => 'Trading Signals',
                    'icon' => 'bi-broadcast',
                    'items' => $signalItems,
                ];
            }
        }

        // 4. Support Tickets
        if ($request->user()->hasPermission('chat_view')) {
            $tickets = SupportTicket::where(function($query) use ($likePattern) {
                    $query->whereRaw('LOWER(ticket_number) LIKE ?', [$likePattern])
                          ->orWhereRaw('LOWER(subject) LIKE ?', [$likePattern]);
                })
                ->limit(4)
                ->latest()
                ->get();

            if ($tickets->isNotEmpty()) {
                $ticketItems = [];
                foreach ($tickets as $t) {
                    $ticketItems[] = [
                        'title' => "{$t->ticket_number}: {$t->subject}",
                        'subtitle' => "Status: " . ucfirst($t->status) . " • Priority: " . ucfirst($t->priority ?? 'medium'),
                        'badge' => ucfirst($t->status),
                        'badge_class' => $t->status === 'open' ? 'bg-success text-white' : 'bg-secondary text-white',
                        'url' => route('admin.support-chat.show', $t->id),
                        'icon' => 'bi-headset',
                    ];
                }
                $results[] = [
                    'category' => 'Support Tickets',
                    'icon' => 'bi-headset',
                    'items' => $ticketItems,
                ];
            }
        }

        // 5. Education Courses & Academy
        if ($request->user()->hasPermission('education_view')) {
            $courses = Course::where(function($query) use ($likePattern) {
                    $query->whereRaw('LOWER(title) LIKE ?', [$likePattern])
                          ->orWhereRaw('LOWER(COALESCE(description, \'\')) LIKE ?', [$likePattern]);
                })
                ->limit(4)
                ->get();

            if ($courses->isNotEmpty()) {
                $courseItems = [];
                foreach ($courses as $c) {
                    $courseItems[] = [
                        'title' => $c->title,
                        'subtitle' => "Difficulty: " . ucfirst($c->difficulty ?? 'All'),
                        'badge' => 'Course',
                        'badge_class' => 'bg-info text-dark',
                        'url' => route('admin.courses.show', $c->id),
                        'icon' => 'bi-book-half',
                    ];
                }
                $results[] = [
                    'category' => 'Education & Courses',
                    'icon' => 'bi-mortarboard',
                    'items' => $courseItems,
                ];
            }
        }

        // 6. Payments & Invoices
        if ($request->user()->hasPermission('transactions_view')) {
            $payments = Payment::where(function($query) use ($likePattern) {
                    $query->whereRaw('LOWER(COALESCE(transaction_id, \'\')) LIKE ?', [$likePattern])
                          ->orWhereRaw('LOWER(COALESCE(tx_hash, \'\')) LIKE ?', [$likePattern]);
                })
                ->limit(4)
                ->latest()
                ->get();

            if ($payments->isNotEmpty()) {
                $paymentItems = [];
                foreach ($payments as $p) {
                    $paymentItems[] = [
                        'title' => "Tx: " . ($p->transaction_id ?: substr($p->tx_hash, 0, 16)),
                        'subtitle' => "Amount: \${$p->amount} • Status: " . ucfirst($p->status),
                        'badge' => ucfirst($p->status),
                        'badge_class' => $p->status === 'approved' ? 'bg-success text-white' : ($p->status === 'pending' ? 'bg-warning text-dark' : 'bg-danger text-white'),
                        'url' => route('admin.payments.index', ['search' => $p->transaction_id ?: $p->tx_hash]),
                        'icon' => 'bi-credit-card-2-front',
                    ];
                }
                $results[] = [
                    'category' => 'Payments & Transactions',
                    'icon' => 'bi-credit-card',
                    'items' => $paymentItems,
                ];
            }
        }

        return response()->json(['results' => $results]);
    }

    private function getNavigationItems(string $q): array
    {
        $allNav = [
            ['title' => 'Dashboard', 'subtitle' => 'System overview and core statistics', 'url' => route('admin.dashboard'), 'icon' => 'bi-grid-1x2-fill', 'badge' => 'Main', 'keywords' => ['dashboard', 'home', 'stats', 'analytics', 'overview']],
            ['title' => 'User Management', 'subtitle' => 'Manage registered mobile app clients and traders', 'url' => route('admin.users.index'), 'icon' => 'bi-person-badge-fill', 'badge' => 'Users', 'keywords' => ['users', 'clients', 'traders', 'accounts', 'banned', 'vip', 'premium']],
            ['title' => 'Staff Management', 'subtitle' => 'Manage admin team and staff credentials', 'url' => route('admin.staff.index'), 'icon' => 'bi-people-fill', 'badge' => 'Staff', 'keywords' => ['staff', 'admins', 'team', 'moderators']],
            ['title' => 'Roles & Permissions', 'subtitle' => 'Configure security roles and capabilities', 'url' => route('admin.roles.index'), 'icon' => 'bi-shield-check', 'badge' => 'Security', 'keywords' => ['roles', 'permissions', 'access', 'security', 'guard']],
            ['title' => 'Signals Management', 'subtitle' => 'Create and publish live trading signals', 'url' => route('admin.signals.index'), 'icon' => 'bi-broadcast', 'badge' => 'Trading', 'keywords' => ['signals', 'forex', 'crypto', 'buy', 'sell', 'trades']],
            ['title' => 'Signal Analytics', 'subtitle' => 'Win rate and trade performance stats', 'url' => route('admin.analytics.signals'), 'icon' => 'bi-graph-up-arrow', 'badge' => 'Analytics', 'keywords' => ['signals analytics', 'win rate', 'pnl', 'performance']],
            ['title' => 'MT5 Bot Management', 'subtitle' => 'Automated trading bot settings and status', 'url' => route('admin.mt5-bot.index'), 'icon' => 'bi-robot', 'badge' => 'Bot', 'keywords' => ['mt5 bot', 'automated trading', 'bot', 'expert advisor', 'ea', 'terminal']],
            ['title' => 'MT5 Bot Analytics', 'subtitle' => 'Equity curve, monthly P&L, bot performance', 'url' => route('admin.analytics.mt5'), 'icon' => 'bi-clipboard-data', 'badge' => 'Analytics', 'keywords' => ['mt5 analytics', 'equity curve', 'profit', 'bot stats']],
            ['title' => 'Education Courses', 'subtitle' => 'Trading academy, video courses and lessons', 'url' => route('admin.courses.index'), 'icon' => 'bi-book-half', 'badge' => 'Academy', 'keywords' => ['education', 'courses', 'lessons', 'videos', 'academy', 'learn']],
            ['title' => 'Chat Rooms & Moderation', 'subtitle' => 'Live community chat rooms and banned words', 'url' => route('admin.chat.index'), 'icon' => 'bi-chat-left-dots-fill', 'badge' => 'Community', 'keywords' => ['chat', 'community', 'messages', 'moderation', 'stickers', 'rooms']],
            ['title' => 'AI Chatbot Settings', 'subtitle' => 'Groq AI prompt, personality and knowledge base', 'url' => route('admin.ai-chatbot.settings'), 'icon' => 'bi-cpu-fill', 'badge' => 'AI', 'keywords' => ['ai chatbot', 'groq', 'assistant', 'bot settings', 'ai prompt']],
            ['title' => 'Support Chats & Helpdesk', 'subtitle' => 'Customer inquiries and escalated tickets', 'url' => route('admin.support-chat.index'), 'icon' => 'bi-headset', 'badge' => 'Support', 'keywords' => ['support', 'helpdesk', 'tickets', 'customer care', 'inquiries']],
            ['title' => 'Push Notifications', 'subtitle' => 'Broadcast push alerts and trading tips', 'url' => route('admin.notifications.index'), 'icon' => 'bi-bell-fill', 'badge' => 'Alerts', 'keywords' => ['notifications', 'push', 'broadcast', 'fcm', 'messages']],
            ['title' => 'Notification Controller', 'subtitle' => 'Toggle specific notification channels on/off', 'url' => route('admin.notification-settings.index'), 'icon' => 'bi-bell-slash-fill', 'badge' => 'Settings', 'keywords' => ['notification controller', 'notification settings', 'toggle alerts']],
            ['title' => 'Payments & Invoices', 'subtitle' => 'Approve deposits, crypto payments and subscriptions', 'url' => route('admin.payments.index'), 'icon' => 'bi-credit-card-fill', 'badge' => 'Finance', 'keywords' => ['payments', 'transactions', 'crypto', 'usdt', 'deposits', 'billing']],
            ['title' => 'Demo Account Requests', 'subtitle' => 'Review and approve MT5 demo accounts', 'url' => route('admin.demo-accounts.index'), 'icon' => 'bi-pc-display-horizontal', 'badge' => 'Demo', 'keywords' => ['demo accounts', 'demo requests', 'practice account', 'broker link']],
            ['title' => 'Demo Settings & Instructions', 'subtitle' => 'Configure instructions, brokers and servers', 'url' => route('admin.demo-settings.index'), 'icon' => 'bi-gear', 'badge' => 'Settings', 'keywords' => ['demo settings', 'broker setup', 'video tutorial', 'demo instructions']],
            ['title' => 'Legal Pages', 'subtitle' => 'Manage Privacy Policy and Terms of Service', 'url' => route('admin.legal-pages.index'), 'icon' => 'bi-file-earmark-text', 'badge' => 'Legal', 'keywords' => ['privacy policy', 'terms and conditions', 'legal pages', 'compliance']],
            ['title' => 'Audit Logs', 'subtitle' => 'Track all superadmin and staff activities', 'url' => route('admin.audit-logs.index'), 'icon' => 'bi-journal-arrow-down', 'badge' => 'Logs', 'keywords' => ['audit logs', 'activity log', 'history', 'security logs', 'actions']],
            ['title' => 'System Settings', 'subtitle' => 'Maintenance mode, database backups, brand config', 'url' => route('admin.settings.index'), 'icon' => 'bi-gear-fill', 'badge' => 'System', 'keywords' => ['system settings', 'backups', 'maintenance', 'logo', 'security code']],
            ['title' => 'Admin Profile & Security', 'subtitle' => 'Manage your admin credentials, email, password', 'url' => route('admin.profile.index'), 'icon' => 'bi-person-circle', 'badge' => 'Account', 'keywords' => ['profile', 'account', 'password', 'change password', 'security', 'my account']],
        ];

        if (empty($q)) {
            return array_map(function($item) {
                return [
                    'title' => $item['title'],
                    'subtitle' => $item['subtitle'],
                    'badge' => $item['badge'],
                    'badge_class' => 'bg-light text-secondary border',
                    'url' => $item['url'],
                    'icon' => $item['icon'],
                ];
            }, array_slice($allNav, 0, 6));
        }

        $needle = strtolower($q);
        $matches = [];
        foreach ($allNav as $item) {
            $matched = false;
            if (str_contains(strtolower($item['title']), $needle) || str_contains(strtolower($item['subtitle']), $needle)) {
                $matched = true;
            } else {
                foreach ($item['keywords'] as $kw) {
                    if (str_contains($kw, $needle)) {
                        $matched = true;
                        break;
                    }
                }
            }

            if ($matched) {
                $matches[] = [
                    'title' => $item['title'],
                    'subtitle' => $item['subtitle'],
                    'badge' => $item['badge'],
                    'badge_class' => 'bg-primary-subtle text-primary border border-primary-subtle',
                    'url' => $item['url'],
                    'icon' => $item['icon'],
                ];
            }
        }

        return $matches;
    }
}
