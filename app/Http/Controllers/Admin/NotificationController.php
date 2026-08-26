<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\AiTradingTip;
use App\Models\NotificationTemplate;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = AdminNotification::with('sender')->latest()->paginate(20);
        $stats = Cache::remember('notification_stats', 60, function () {
            return [
                'total_sent' => AdminNotification::where('is_sent', true)->sum('sent_count'),
                'pending' => AdminNotification::where('is_sent', false)->count(),
                'today_sent' => AdminNotification::where('is_sent', true)->whereDate('created_at', today())->sum('sent_count'),
                'templates' => NotificationTemplate::where('is_active', true)->count(),
            ];
        });
        return view('admin.notifications.index', compact('notifications', 'stats'));
    }

    public function create()
    {
        $templates = NotificationTemplate::where('is_active', true)->get();
        $roles = \App\Models\Role::all();
        return view('admin.notifications.create', compact('templates', 'roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:2000',
            'type' => 'required|in:info,warning,success,danger',
            'target' => 'required|in:all,premium,free,role,user',
            'target_user_id' => 'nullable|required_if:target,user|exists:users,id',
            'target_role_id' => 'nullable|required_if:target,role|exists:roles,id',
        ]);

        $query = User::whereNull('deleted_at');
        if ($validated['target'] === 'premium') {
            $query->where('is_premium', true);
        } elseif ($validated['target'] === 'free') {
            $query->where('is_premium', false);
        } elseif ($validated['target'] === 'role') {
            $query->whereHas('roles', fn($q) => $q->where('id', $validated['target_role_id']));
        } elseif ($validated['target'] === 'user') {
            $query->where('id', $validated['target_user_id']);
        }

        $sentCount = $query->count();

        $notification = NotificationService::send('system_announcement', [
            'title' => $validated['title'],
            'body' => $validated['body'],
            'type' => $validated['type'],
            'target' => $validated['target'],
            'target_user_id' => $validated['target_user_id'] ?? null,
            'target_role_id' => $validated['target_role_id'] ?? null,
            'sent_count' => $sentCount,
            'sent_by' => auth()->id(),
        ]);

        if (!$notification) {
            return redirect()->route('admin.notifications.index')
                ->with('error', 'System announcements are currently disabled in Notification Controller.');
        }

        ActivityLogger::log('create', 'AdminNotification', $notification->id, "Sent notification: {$notification->title} to {$sentCount} users");
        Cache::forget('notification_stats');

        return redirect()->route('admin.notifications.index')->with('success', "Notification sent to {$sentCount} users.");
    }

    public function templates()
    {
        $templates = NotificationTemplate::latest()->paginate(20);
        return view('admin.notifications.templates', compact('templates'));
    }

    public function storeTemplate(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:notification_templates,name',
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:2000',
            'type' => 'required|in:info,warning,success,danger',
        ]);

        $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
        NotificationTemplate::create($validated);

        return back()->with('success', 'Template created.');
    }

    public function destroyTemplate(NotificationTemplate $template)
    {
        $template->delete();
        return back()->with('success', 'Template deleted.');
    }

    public function tips()
    {
        $tips = AiTradingTip::latest()->paginate(20);
        return view('admin.notifications.tips', compact('tips'));
    }

    public function storeTip(Request $request)
    {
        $validated = $request->validate([
            'tip' => 'required|string|max:1000',
            'category' => 'required|string|max:100',
        ]);

        AiTradingTip::create($validated);
        return back()->with('success', 'Tip added.');
    }

    public function destroyTip(AiTradingTip $tip)
    {
        $tip->delete();
        return back()->with('success', 'Tip deleted.');
    }

    public function generateTip()
    {
        $tips = [
            ['tip' => 'Always use proper risk management. Never risk more than 2% of your account on a single trade.', 'category' => 'risk_management'],
            ['tip' => 'The trend is your friend. Trade in the direction of the higher timeframe trend for better results.', 'category' => 'strategy'],
            ['tip' => 'Keep a trading journal. Reviewing your trades helps identify patterns and improve performance.', 'category' => 'psychology'],
            ['tip' => 'Avoid overtrading. Quality setups are more important than quantity of trades.', 'category' => 'psychology'],
            ['tip' => 'Use stop losses on every trade. Protecting your capital is the first priority.', 'category' => 'risk_management'],
            ['tip' => 'Major economic news can cause high volatility. Consider reducing position sizes during news events.', 'category' => 'market_analysis'],
            ['tip' => 'Diversify your trading. Don\'t put all your capital in one currency pair.', 'category' => 'strategy'],
            ['tip' => 'Demo trade new strategies before going live. Practice makes perfect.', 'category' => 'education'],
            ['tip' => 'Compound your gains. Increase lot size gradually as your account grows.', 'category' => 'strategy'],
            ['tip' => 'Don\'t revenge trade. If you take a loss, step away and wait for the next setup.', 'category' => 'psychology'],
            ['tip' => 'Understand support and resistance levels before placing any trade.', 'category' => 'market_analysis'],
            ['tip' => 'Backtest your strategy on at least 100 trades before going live.', 'category' => 'education'],
            ['tip' => 'Risk-reward ratio should be at least 1:2 for every trade you take.', 'category' => 'risk_management'],
            ['tip' => 'Trading during London and New York sessions gives the best volatility for major pairs.', 'category' => 'market_analysis'],
            ['tip' => 'Your trading plan should include entry, exit, and position size before you enter any trade.', 'category' => 'strategy'],
            ['tip' => 'Don\'t add to a losing position. Accept the loss and move on.', 'category' => 'risk_management'],
            ['tip' => 'Moving averages can help identify trend direction. Use 50 EMA and 200 EMA for confluence.', 'category' => 'market_analysis'],
            ['tip' => 'Emotional trading leads to losses. Stick to your strategy regardless of feelings.', 'category' => 'psychology'],
            ['tip' => 'Gold (XAUUSD) is most volatile during London session open. Plan your entries accordingly.', 'category' => 'market_analysis'],
            ['tip' => 'The 10-pips strategy works best in ranging markets. Avoid during strong trends.', 'category' => 'strategy'],
        ];

        $existingTips = AiTradingTip::pluck('tip')->toArray();
        $availableTips = array_filter($tips, fn($t) => !in_array($t['tip'], $existingTips));

        if (empty($availableTips)) {
            return back()->with('error', 'All tips have been generated. Add custom tips or reset the pool.');
        }

        $tip = $availableTips[array_rand($availableTips)];
        AiTradingTip::create($tip);

        return back()->with('success', 'New trading tip generated.');
    }
}
