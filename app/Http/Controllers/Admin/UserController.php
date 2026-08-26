<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
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

        if ($request->filled('is_banned')) {
            $query->where('is_banned', $request->boolean('is_banned'));
        }

        if ($request->filled('is_premium')) {
            $query->where('is_premium', $request->boolean('is_premium'));
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('slug', $request->input('role'));
            });
        }

        if ($request->filled('date_from') && $this->isValidDate($request->input('date_from'))) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to') && $this->isValidDate($request->input('date_to'))) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
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

        return view('admin.users.index', compact('users', 'stats'));
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
            'is_premium' => 'boolean',
            'premium_days' => 'nullable|integer|min:0|max:3650',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'status' => $validated['status'],
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
            'is_premium' => 'boolean',
            'premium_days' => 'nullable|integer|min:0|max:3650',
            'password' => ['nullable', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        $oldData = $user->only(['name', 'email', 'phone', 'status', 'is_premium']);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'status' => $validated['status'],
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
        return User::selectRaw("
            status,
            SUM(CASE WHEN is_banned = 1 THEN 1 ELSE 0 END) as banned_count,
            SUM(CASE WHEN is_premium = 1 THEN 1 ELSE 0 END) as premium_count,
            SUM(CASE WHEN DATE(created_at) = DATE('now') THEN 1 ELSE 0 END) as new_today
        ")
        ->groupBy('status')
        ->get()
        ->pipe(function ($results) {
            $active = $results->where('status', 'active')->first();
            $inactive = $results->where('status', 'inactive')->first();
            $suspended = $results->where('status', 'suspended')->first();

            return [
                'total' => $results->sum(fn ($r) => $r->status === 'active' ? $active?->count ?? 0 : 0)
                    + $results->sum(fn ($r) => $r->status === 'inactive' ? $inactive?->count ?? 0 : 0)
                    + $results->sum(fn ($r) => $r->status === 'suspended' ? $suspended?->count ?? 0 : 0),
                'active' => $active?->count ?? 0,
                'inactive' => $inactive?->count ?? 0,
                'suspended' => $suspended?->count ?? 0,
                'banned' => $results->sum('banned_count'),
                'premium' => $results->sum('premium_count'),
                'new_today' => $results->sum('new_today'),
            ];
        });
    }

    private function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}
