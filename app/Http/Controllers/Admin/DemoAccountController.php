<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DemoAccountRequest;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DemoAccountController extends Controller
{
    public function index(Request $request)
    {
        $query = DemoAccountRequest::with(['user', 'reviewer']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = str_replace(['%', '_'], ['\%', '\_'], trim($request->search));
            if ($search !== '') {
                $lower = strtolower($search);
                $query->where(function ($q) use ($lower) {
                    $q->whereHas('user', fn($u) => $u->whereRaw('LOWER(name) LIKE ?', ["%{$lower}%"])->orWhereRaw('LOWER(email) LIKE ?', ["%{$lower}%"]))
                        ->orWhereRaw('LOWER(COALESCE(demo_email, \'\')) LIKE ?', ["%{$lower}%"])
                        ->orWhereRaw('LOWER(COALESCE(demo_phone, \'\')) LIKE ?', ["%{$lower}%"])
                        ->orWhereRaw('LOWER(COALESCE(exness_account_number, \'\')) LIKE ?', ["%{$lower}%"]);
                });
            }
        }

        if ($request->filled('date_from') && $this->isValidDate($request->input('date_from'))) {
            $query->where('created_at', '>=', Carbon::parse($request->input('date_from'))->startOfDay());
        }

        if ($request->filled('date_to') && $this->isValidDate($request->input('date_to'))) {
            $query->where('created_at', '<=', Carbon::parse($request->input('date_to'))->endOfDay());
        }

        $requests = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'total' => DemoAccountRequest::count(),
            'pending' => DemoAccountRequest::where('status', 'pending')->count(),
            'approved' => DemoAccountRequest::where('status', 'approved')->count(),
            'rejected' => DemoAccountRequest::where('status', 'rejected')->count(),
            'linked' => DemoAccountRequest::where('status', 'linked')->count(),
        ];

        return view('admin.demo-accounts.index', compact('requests', 'stats'));
    }

    public function show(DemoAccountRequest $demoRequest)
    {
        $demoRequest->load(['user', 'reviewer']);

        return view('admin.demo-accounts.show', compact('demoRequest'));
    }

    public function approve(Request $request, DemoAccountRequest $demoRequest)
    {
        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $demoRequest->update([
            'status' => 'approved',
            'admin_notes' => $validated['admin_notes'] ?? null,
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        // Sync with user's profile trading account details
        $user = $demoRequest->user;
        if ($user) {
            $updateUser = [];
            if (empty($user->demo_account_id)) {
                $updateUser['demo_account_id'] = $demoRequest->exness_account_number;
            }
            if (empty($user->demo_account_server)) {
                $updateUser['demo_account_server'] = 'Exness-MT5Trial';
            }
            if (empty($user->broker_name)) {
                $updateUser['broker_name'] = 'Exness';
            }
            if (!empty($updateUser)) {
                $user->update($updateUser);
            }
        }

        ActivityLogger::log(
            'approve',
            'DemoAccountRequest',
            $demoRequest->id,
            "Approved demo account request for user #{$demoRequest->user_id}"
        );

        return redirect()->route('admin.demo-accounts.show', $demoRequest)
            ->with('success', 'Demo account request approved successfully! User now has access to download the Trading Bot.');
    }

    public function reject(Request $request, DemoAccountRequest $demoRequest)
    {
        $validated = $request->validate([
            'admin_notes' => 'required|string|max:1000',
        ]);

        $demoRequest->update([
            'status' => 'rejected',
            'admin_notes' => $validated['admin_notes'],
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        ActivityLogger::log(
            'reject',
            'DemoAccountRequest',
            $demoRequest->id,
            "Rejected demo account request for user #{$demoRequest->user_id}"
        );

        return redirect()->route('admin.demo-accounts.show', $demoRequest)
            ->with('success', 'Demo account request rejected.');
    }

    public function link(Request $request, DemoAccountRequest $demoRequest)
    {
        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $demoRequest->update([
            'status' => 'linked',
            'admin_notes' => $validated['admin_notes'] ?? $demoRequest->admin_notes,
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        ActivityLogger::log(
            'link',
            'DemoAccountRequest',
            $demoRequest->id,
            "Linked demo account for user #{$demoRequest->user_id}"
        );

        return redirect()->route('admin.demo-accounts.show', $demoRequest)
            ->with('success', 'Demo account marked as linked.');
    }

    public function destroy(DemoAccountRequest $demoRequest)
    {
        $demoRequest->delete();

        ActivityLogger::log(
            'delete',
            'DemoAccountRequest',
            $demoRequest->id,
            "Deleted demo account request #{$demoRequest->id}"
        );

        return redirect()->route('admin.demo-accounts.index')
            ->with('success', 'Demo account request deleted.');
    }
}
