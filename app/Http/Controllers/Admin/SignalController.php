<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Signal;
use App\Models\SignalCategory;
use App\Services\ActivityLogger;
use App\Services\NotificationService;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SignalController extends Controller
{
    public function index(Request $request)
    {
        $query = Signal::with(['creator', 'categories']);

        if ($search = trim($request->input('search', ''))) {
            $safeSearch = addcslashes($search, '%_');
            $query->where(function ($q) use ($safeSearch) {
                $q->where('title', 'like', "%{$safeSearch}%")
                  ->orWhere('symbol', 'like', "%{$safeSearch}%")
                  ->orWhere('description', 'like', "%{$safeSearch}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($direction = $request->input('direction')) {
            $query->where('direction', $direction);
        }

        if ($result = $request->input('result')) {
            $query->where('result', $result);
        }

        if ($categoryId = $request->input('category_id')) {
            $query->whereHas('categories', fn($q) => $q->where('signal_categories.id', $categoryId));
        }

        if ($symbol = $request->input('symbol')) {
            $query->where('symbol', $symbol);
        }

        if ($request->filled('date_from') && $this->isValidDate($request->input('date_from'))) {
            $query->where('created_at', '>=', Carbon::parse($request->input('date_from'))->startOfDay());
        }

        if ($request->filled('date_to') && $this->isValidDate($request->input('date_to'))) {
            $query->where('created_at', '<=', Carbon::parse($request->input('date_to'))->endOfDay());
        }

        $sort = $request->input('sort', 'created_at');
        $dir = $request->input('dir', 'desc');
        $allowedSorts = ['id', 'title', 'symbol', 'direction', 'status', 'result', 'pips_result', 'created_at', 'published_at', 'views_count'];
        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $dir === 'asc' ? 'asc' : 'desc');
        }

        $signals = $query->paginate(15)->withQueryString();
        $categories = SignalCategory::where('is_active', true)->orderBy('name')->get();

        $stats = Cache::remember('signal_stats', 60, function () {
            $row = DB::table('signals')
                ->selectRaw("
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                    SUM(CASE WHEN result = 'win' THEN 1 ELSE 0 END) as wins,
                    SUM(CASE WHEN result = 'loss' THEN 1 ELSE 0 END) as losses,
                    COALESCE(SUM(CASE WHEN status = 'closed' THEN pips_result ELSE 0 END), 0) as total_pips
                ")->first();

            return [
                'total' => (int) $row->total,
                'draft' => (int) $row->draft,
                'active' => (int) $row->active,
                'closed' => (int) $row->closed,
                'cancelled' => (int) $row->cancelled,
                'wins' => (int) $row->wins,
                'losses' => (int) $row->losses,
                'total_pips' => (float) $row->total_pips,
            ];
        });

        $symbols = Signal::select('symbol')->distinct()->orderBy('symbol')->pluck('symbol');

        return view('admin.signals.index', compact('signals', 'categories', 'stats', 'symbols'));
    }

    public function create()
    {
        $categories = SignalCategory::where('is_active', true)->orderBy('name')->get();
        return view('admin.signals.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'symbol' => 'required|string|max:20',
            'direction' => 'required|in:buy,sell',
            'entry_price' => 'nullable|numeric|min:0',
            'take_profit' => 'nullable|numeric|min:0',
            'stop_loss' => 'nullable|numeric|min:0',
            'status' => 'required|in:draft,pending,active',
            'is_featured' => 'boolean',
            'expires_at' => 'nullable|date|after:now',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:signal_categories,id',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['expires_at'] = $request->filled('expires_at') ? $request->input('expires_at') : null;

        if ($validated['status'] === 'active') {
            $validated['published_at'] = now();
        }

        $categories = $validated['categories'] ?? [];
        unset($validated['categories']);

        $signal = Signal::create($validated);
        $signal->categories()->sync($categories);

        ActivityLogger::log('create', 'Signal', $signal->id, "Created signal: {$signal->title}");
        Cache::forget('signal_stats');

        if ($signal->status === 'active') {
            $dir = strtoupper($signal->direction);
            NotificationService::send('signal_new', [
                'title' => "New Signal: {$signal->symbol}",
                'body' => "{$dir} {$signal->symbol} @ {$signal->entry_price}. TP: {$signal->take_profit} | SL: {$signal->stop_loss}",
                'type' => 'info',
                'target' => 'all',
            ]);
            PushNotificationService::sendToAll(
                "New Signal: {$signal->symbol}",
                "{$dir} {$signal->symbol} @ {$signal->entry_price}",
                ['signal_id' => $signal->id, 'type' => 'signal_new']
            );
        }

        return redirect()->route('admin.signals.show', $signal)->with('success', 'Signal created successfully.');
    }

    public function show(Signal $signal)
    {
        $signal->load(['creator', 'categories']);

        return view('admin.signals.show', compact('signal'));
    }

    public function edit(Signal $signal)
    {
        $signal->load('categories');
        $categories = SignalCategory::where('is_active', true)->orderBy('name')->get();
        $selectedCategories = $signal->categories->pluck('id')->toArray();

        return view('admin.signals.edit', compact('signal', 'categories', 'selectedCategories'));
    }

    public function update(Request $request, Signal $signal)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'symbol' => 'required|string|max:20',
            'direction' => 'required|in:buy,sell',
            'entry_price' => 'nullable|numeric|min:0',
            'take_profit' => 'nullable|numeric|min:0',
            'stop_loss' => 'nullable|numeric|min:0',
            'status' => 'required|in:draft,pending,active,closed,cancelled',
            'result' => 'required_if:status,closed|nullable|in:win,loss,breakeven',
            'pips_result' => 'nullable|numeric',
            'close_price' => 'nullable|numeric|min:0',
            'is_featured' => 'boolean',
            'expires_at' => 'nullable|date',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:signal_categories,id',
        ]);

        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['expires_at'] = $request->filled('expires_at') ? $request->input('expires_at') : null;

        if ($validated['status'] === 'active' && $signal->status !== 'active') {
            $validated['published_at'] = now();
        }

        if ($validated['status'] === 'closed' && $signal->status !== 'closed') {
            $validated['closed_at'] = now();
            $resultEmoji = ($validated['result'] ?? 'closed') === 'win' ? '✅' : (($validated['result'] ?? 'closed') === 'loss' ? '❌' : '⏰');
            NotificationService::send('signal_closed', [
                'title' => "{$resultEmoji} Signal Closed: {$signal->symbol}",
                'body' => "Your {$signal->direction} signal for {$signal->symbol} has been closed.",
                'type' => ($validated['result'] ?? '') === 'win' ? 'success' : (($validated['result'] ?? '') === 'loss' ? 'danger' : 'info'),
                'target' => 'all',
            ]);
            PushNotificationService::sendToAll(
                "Signal Closed: {$signal->symbol}",
                "{$signal->symbol} {$signal->direction} signal has been closed",
                ['signal_id' => $signal->id, 'type' => 'signal_closed']
            );
        }

        $categories = $validated['categories'] ?? [];
        unset($validated['categories']);

        $signal->update($validated);
        $signal->categories()->sync($categories);

        ActivityLogger::log('update', 'Signal', $signal->id, "Updated signal: {$signal->title}");
        Cache::forget('signal_stats');

        return redirect()->route('admin.signals.show', $signal)->with('success', 'Signal updated successfully.');
    }

    public function destroy(Signal $signal)
    {
        $title = $signal->title;
        $signal->categories()->detach();
        $signal->delete();

        ActivityLogger::log('delete', 'Signal', $signal->id, "Deleted signal: {$title}");
        Cache::forget('signal_stats');

        return redirect()->route('admin.signals.index')->with('success', 'Signal deleted successfully.');
    }

    public function publish(Signal $signal)
    {
        if (in_array($signal->status, ['active', 'closed', 'cancelled'])) {
            return back()->with('error', 'Cannot publish a signal with status: ' . ucfirst($signal->status));
        }

        $signal->publish();

        ActivityLogger::log('publish', 'Signal', $signal->id, "Published signal: {$signal->title}");
        Cache::forget('signal_stats');

        $dir = strtoupper($signal->direction);
        NotificationService::send('signal_new', [
            'title' => "New Signal: {$signal->symbol}",
            'body' => "{$dir} {$signal->symbol} @ {$signal->entry_price}. TP: {$signal->take_profit} | SL: {$signal->stop_loss}",
            'type' => 'info',
            'target' => 'all',
        ]);
        PushNotificationService::sendToAll(
            "New Signal: {$signal->symbol}",
            "{$dir} {$signal->symbol} @ {$signal->entry_price}",
            ['signal_id' => $signal->id, 'type' => 'signal_new']
        );

        return back()->with('success', 'Signal published successfully.');
    }

    public function close(Request $request, Signal $signal)
    {
        if ($signal->status !== 'active') {
            return back()->with('error', 'Only active signals can be closed.');
        }

        $validated = $request->validate([
            'result' => 'required|in:win,loss,breakeven',
            'pips_result' => 'required|numeric',
            'close_price' => 'nullable|numeric|min:0',
        ]);

        $signal->close(
            $validated['result'],
            $validated['pips_result'],
            $validated['close_price'] ?? null
        );

        ActivityLogger::log('close', 'Signal', $signal->id, "Closed signal: {$signal->title} - {$validated['result']} ({$validated['pips_result']} pips)");
        Cache::forget('signal_stats');

        $resultEmoji = $validated['result'] === 'win' ? '✅' : ($validated['result'] === 'loss' ? '❌' : '⏰');
        NotificationService::send('signal_closed', [
            'title' => "{$resultEmoji} Signal Closed: {$signal->symbol}",
            'body' => "Your {$signal->direction} signal for {$signal->symbol} closed as " . strtoupper($validated['result']) . ". {$validated['pips_result']} pips.",
            'type' => $validated['result'] === 'win' ? 'success' : ($validated['result'] === 'loss' ? 'danger' : 'info'),
            'target' => 'all',
        ]);
        PushNotificationService::sendToAll(
            "Signal {$resultEmoji} " . strtoupper($validated['result']),
            "{$signal->symbol} {$signal->direction} signal closed. {$validated['pips_result']} pips",
            ['signal_id' => $signal->id, 'result' => $validated['result'], 'type' => 'signal_closed']
        );

        return back()->with('success', 'Signal closed successfully.');
    }

    public function symbols()
    {
        $symbols = Signal::select('symbol')
            ->distinct()
            ->orderBy('symbol')
            ->pluck('symbol');

        return response()->json($symbols);
    }

    private function isValidDate(string $date): bool
    {
        return strtotime($date) !== false;
    }
}
