<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('roles')->whereNull('deleted_at');

        if ($search = $request->input('search')) {
            $safeSearch = str_replace(['%', '_'], ['\%', '\_'], trim($search));
            if ($safeSearch !== '') {
                $query->where(function ($q) use ($safeSearch) {
                    $q->where('name', 'like', "%{$safeSearch}%")
                      ->orWhere('email', 'like', "%{$safeSearch}%")
                      ->orWhere('phone', 'like', "%{$safeSearch}%");
                });
            }
        }

        $allowedStatuses = ['active', 'inactive', 'suspended'];
        if ($status = $request->input('status')) {
            if (in_array($status, $allowedStatuses)) {
                $query->where('status', $status);
            }
        }

        if ($request->has('is_banned') && $request->input('is_banned') !== '' && $request->input('is_banned') !== null) {
            $isBanned = filter_var($request->input('is_banned'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($isBanned === true) {
                $query->where('is_banned', true);
            } elseif ($isBanned === false) {
                $query->where(function ($q) {
                    $q->where('is_banned', false)->orWhereNull('is_banned');
                });
            }
        }

        if ($request->has('is_premium') && $request->input('is_premium') !== '' && $request->input('is_premium') !== null) {
            $isPremium = filter_var($request->input('is_premium'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($isPremium === true) {
                $query->where('is_premium', true);
            } elseif ($isPremium === false) {
                $query->where(function ($q) {
                    $q->where('is_premium', false)->orWhereNull('is_premium');
                });
            }
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('slug', $request->input('role'));
            });
        }

        if ($request->filled('date_from') && $this->isValidDate($request->input('date_from'))) {
            $query->where('created_at', '>=', Carbon::parse($request->input('date_from'))->startOfDay());
        }

        if ($request->filled('date_to') && $this->isValidDate($request->input('date_to'))) {
            $query->where('created_at', '<=', Carbon::parse($request->input('date_to'))->endOfDay());
        }

        $sortBy = $request->input('sort', 'created_at');
        $sortDir = $request->input('dir', 'desc');
        $allowedSorts = ['id', 'name', 'email', 'status', 'created_at', 'last_login_at'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        } else {
            $query->latest();
        }

        $users = $query->paginate(20)->withQueryString();
        $stats = $this->getUserStats();
        $roles = Role::orderBy('name')->get();

        return view('admin.users.index', compact('users', 'stats', 'roles'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'status' => 'required|in:active,inactive,suspended',
            'is_verified' => 'boolean',
            'chat_badge' => 'nullable|string|max:50',
            'badge_color' => 'nullable|string|in:primary,secondary,success,danger,warning,info',
            'is_premium' => 'boolean',
            'premium_days' => 'nullable|integer|min:0|max:3650',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'status' => $validated['status'],
            'is_verified' => (bool) ($validated['is_verified'] ?? false),
            'chat_badge' => $validated['chat_badge'] ?? null,
            'badge_color' => $validated['badge_color'] ?? 'primary',
            'is_premium' => $validated['is_premium'] ?? false,
            'premium_expires_at' => ($validated['is_premium'] ?? false) && ($validated['premium_days'] ?? 0) > 0
                ? now()->addDays($validated['premium_days'])
                : null,
        ]);

        $user->assignRole('user');

        ActivityLogger::log(
            'create',
            'User',
            $user->id,
            "Created user: {$user->name} ({$user->email})"
        );

        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->name} created successfully.");
    }

    public function show(User $user)
    {
        if ($user->trashed()) {
            abort(404);
        }

        $user->load(['roles', 'activityLogs' => function ($q) {
            $q->with('user')->latest()->limit(50);
        }]);

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        if ($user->trashed()) {
            abort(404);
        }

        if ($user->isSuperAdmin() && $user->id !== auth()->id()) {
            abort(403, 'Cannot edit other Super Admin accounts.');
        }

        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        if ($user->trashed()) {
            abort(404);
        }

        if ($user->isSuperAdmin() && $user->id !== auth()->id()) {
            abort(403, 'Cannot modify other Super Admin accounts.');
        }

        if ($user->id === auth()->id() && $request->input('status') === 'suspended') {
            return back()->withErrors(['error' => 'Cannot suspend your own account.']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'status' => 'required|in:active,inactive,suspended',
            'is_verified' => 'boolean',
            'chat_badge' => 'nullable|string|max:50',
            'badge_color' => 'nullable|string|in:primary,secondary,success,danger,warning,info',
            'is_premium' => 'boolean',
            'premium_days' => 'nullable|integer|min:0|max:3650',
            'password' => ['nullable', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        $oldData = $user->only(['name', 'email', 'phone', 'status', 'is_verified', 'chat_badge', 'badge_color', 'is_premium']);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'status' => $validated['status'],
            'is_verified' => (bool) ($validated['is_verified'] ?? false),
            'chat_badge' => $validated['chat_badge'] ?? null,
            'badge_color' => $validated['badge_color'] ?? 'primary',
            'is_premium' => $validated['is_premium'] ?? false,
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        if (($validated['is_premium'] ?? false) && ($validated['premium_days'] ?? 0) > 0) {
            $updateData['premium_expires_at'] = $user->premium_expires_at && $user->premium_expires_at->isFuture()
                ? $user->premium_expires_at->addDays($validated['premium_days'])
                : now()->addDays($validated['premium_days']);
        } elseif (!($validated['is_premium'] ?? false)) {
            $updateData['premium_expires_at'] = null;
        }

        $user->update($updateData);

        ActivityLogger::log(
            'update',
            'User',
            $user->id,
            "Updated user: {$user->name}",
            $oldData,
            $user->only(['name', 'email', 'phone', 'status', 'is_premium'])
        );

        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->name} updated successfully.");
    }

    public function destroy(User $user)
    {
        if ($user->isSuperAdmin()) {
            return back()->withErrors(['error' => 'Cannot delete Super Admin account.']);
        }

        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'Cannot delete your own account.']);
        }

        $name = $user->name;

        $user->tokens()->delete();
        $user->delete();

        ActivityLogger::log(
            'delete',
            'User',
            $user->id,
            "Deleted user: {$name}"
        );

        return redirect()->route('admin.users.index')
            ->with('success', "User {$name} deleted successfully.");
    }

    public function toggleBan(User $user)
    {
        if ($user->isSuperAdmin()) {
            return back()->withErrors(['error' => 'Cannot ban Super Admin.']);
        }

        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'Cannot ban your own account.']);
        }

        $wasBanned = $user->is_banned;
        $newBanned = !$wasBanned;

        $user->update([
            'is_banned' => $newBanned,
            'status' => $newBanned ? 'suspended' : 'active',
        ]);

        $action = $newBanned ? 'banned' : 'unbanned';
        ActivityLogger::log(
            'update',
            'User',
            $user->id,
            "User {$action}: {$user->name}"
        );

        return back()->with('success', "User {$user->name} {$action} successfully.");
    }

    public function togglePremium(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'Cannot change your own premium status.']);
        }

        $newPremium = !$user->is_premium;
        $user->update([
            'is_premium' => $newPremium,
            'premium_expires_at' => $newPremium ? now()->addDays(30) : null,
        ]);

        $action = $newPremium ? 'activated' : 'deactivated';
        ActivityLogger::log(
            'update',
            'User',
            $user->id,
            "Premium {$action} for: {$user->name}"
        );

        return back()->with('success', "Premium {$action} for {$user->name}.");
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,suspend,delete,export',
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
        ]);

        $action = $request->input('action');
        $userIds = $request->input('user_ids');

        $users = User::whereIn('id', $userIds)->get();

        switch ($action) {
            case 'activate':
                User::whereIn('id', $userIds)
                    ->where('id', '!=', auth()->id())
                    ->update(['status' => 'active', 'is_banned' => false]);
                ActivityLogger::log('bulk_update', 'User', null, "Bulk activated " . $users->count() . " users");
                return back()->with('success', $users->count() . " users activated.");

            case 'suspend':
                User::whereIn('id', $userIds)
                    ->where('id', '!=', auth()->id())
                    ->where('is_banned', false)
                    ->update(['status' => 'suspended']);
                ActivityLogger::log('bulk_update', 'User', null, "Bulk suspended " . $users->count() . " users");
                return back()->with('success', $users->count() . " users suspended.");

            case 'delete':
                $safeUsers = $users->filter(fn ($u) => !$u->isSuperAdmin() && $u->id !== auth()->id());
                $safeUsers->each(function ($user) {
                    $user->tokens()->delete();
                    $user->delete();
                });
                ActivityLogger::log('bulk_delete', 'User', null, "Bulk deleted " . $safeUsers->count() . " users");
                return back()->with('success', $safeUsers->count() . " users deleted.");

            case 'export':
                return $this->exportUsers($users);

            default:
                return back()->withErrors(['error' => 'Invalid bulk action.']);
        }
    }

    private function exportUsers($users)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="users_export_' . date('Y-m-d_His') . '.csv"',
        ];

        $callback = function () use ($users) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Name', 'Email', 'Phone', 'Status', 'Premium', 'Banned', 'Created', 'Last Login']);

            foreach ($users as $user) {
                fputcsv($handle, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->phone ?? '',
                    $user->status,
                    $user->is_premium ? 'Yes' : 'No',
                    $user->is_banned ? 'Yes' : 'No',
                    $user->created_at->format('Y-m-d H:i:s'),
                    $user->last_login_at?->format('Y-m-d H:i:s') ?? 'Never',
                ]);
            }

            fclose($handle);
        };

        ActivityLogger::log('export', 'User', null, "Exported " . $users->count() . " users to CSV");

        return response()->stream($callback, 200, $headers);
    }

    private function getUserStats(): array
    {
        $today = now()->toDateString();

        $statusCounts = User::whereNull('deleted_at')
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $active = (int) ($statusCounts->get('active') ?? 0);
        $inactive = (int) ($statusCounts->get('inactive') ?? 0);
        $suspended = (int) ($statusCounts->get('suspended') ?? 0);

        return [
            'total' => User::whereNull('deleted_at')->count(),
            'active' => $active,
            'inactive' => $inactive,
            'suspended' => $suspended,
            'banned' => User::whereNull('deleted_at')->where('is_banned', true)->count(),
            'premium' => User::whereNull('deleted_at')->where('is_premium', true)->count(),
            'new_today' => User::whereNull('deleted_at')->whereDate('created_at', $today)->count(),
        ];
    }
}
