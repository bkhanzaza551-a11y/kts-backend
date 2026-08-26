<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['user', 'approver']);

        if ($status = $request->input('status')) {
            if (in_array($status, ['pending', 'approved', 'rejected', 'completed'])) {
                $query->where('status', $status);
            }
        }

        if ($gateway = $request->input('gateway')) {
            if (in_array($gateway, ['jazzcash', 'easypaisa', 'bank_transfer', 'manual'])) {
                $query->where('gateway', $gateway);
            }
        }

        if ($search = trim($request->input('search', ''))) {
            $safeSearch = addcslashes($search, '%_\\');
            $query->where(function ($q) use ($safeSearch) {
                $q->where('transaction_id', 'like', "%{$safeSearch}%")
                  ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$safeSearch}%")->orWhere('email', 'like', "%{$safeSearch}%"));
            });
        }

        $transactions = $query->latest()->paginate(20)->withQueryString();

        $stats = Cache::remember('payment_stats', 60, function () {
            return [
                'total_revenue' => Transaction::where('status', 'approved')->sum('amount'),
                'pending' => Transaction::where('status', 'pending')->count(),
                'approved' => Transaction::where('status', 'approved')->count(),
                'today_revenue' => Transaction::where('status', 'approved')->whereDate('approved_at', today())->sum('amount'),
            ];
        });

        return view('admin.payments.index', compact('transactions', 'stats'));
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['user', 'approver']);
        return view('admin.payments.show', compact('transaction'));
    }

    public function approve(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        if ($transaction->user_id === auth()->id()) {
            return back()->with('error', 'You cannot approve your own transaction.');
        }

        try {
            DB::transaction(function () use ($transaction, $validated) {
                $fresh = $transaction->lockForUpdate()->fresh();

                if ($fresh->status !== 'pending') {
                    throw new \Exception('Transaction is no longer pending.');
                }

                $fresh->update([
                    'status' => 'approved',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'admin_notes' => $validated['admin_notes'] ?? $fresh->admin_notes,
                ]);

                $user = $fresh->user;
                if ($user && $fresh->plan_type && $fresh->plan_duration_days > 0) {
                    $expiry = $user->premium_expires_at && $user->premium_expires_at->isFuture()
                        ? $user->premium_expires_at->addDays($fresh->plan_duration_days)
                        : now()->addDays($fresh->plan_duration_days);

                    $user->update([
                        'is_premium' => true,
                        'premium_expires_at' => $expiry,
                    ]);
                }
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage() === 'Transaction is no longer pending.' ? $e->getMessage() : 'Failed to approve transaction. Please try again.');
        }

        ActivityLogger::log('approve_transaction', 'Transaction', $transaction->id, "Approved transaction {$transaction->transaction_id} for {$transaction->amount} {$transaction->currency}");
        Cache::forget('payment_stats');

        return back()->with('success', 'Transaction approved and premium activated.');
    }

    public function reject(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        if ($transaction->user_id === auth()->id()) {
            return back()->with('error', 'You cannot reject your own transaction.');
        }

        try {
            DB::transaction(function () use ($transaction, $validated) {
                $fresh = $transaction->lockForUpdate()->fresh();

                if ($fresh->status !== 'pending') {
                    throw new \Exception('Transaction is no longer pending.');
                }

                $fresh->update([
                    'status' => 'rejected',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'admin_notes' => $validated['admin_notes'] ?? $fresh->admin_notes,
                ]);
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage() === 'Transaction is no longer pending.' ? $e->getMessage() : 'Failed to reject transaction. Please try again.');
        }

        ActivityLogger::log('reject_transaction', 'Transaction', $transaction->id, "Rejected transaction {$transaction->transaction_id}");
        Cache::forget('payment_stats');

        return back()->with('success', 'Transaction rejected.');
    }
}
