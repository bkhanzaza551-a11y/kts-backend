<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('roles')
            ->whereHas('roles', function ($q) {
                $q->where('slug', '!=', 'user');
            });

        if ($search = $request->input('search')) {
            $safeSearch = str_replace(['%', '_'], ['\%', '\_'], $search);
            $query->where(function ($q) use ($safeSearch) {
                $q->where('name', 'like', "%{$safeSearch}%")
                  ->orWhere('email', 'like', "%{$safeSearch}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $staff = $query->latest()->paginate(20);

        return view('admin.staff.index', compact('staff'));
    }

    public function create()
    {
        $roles = Role::where('slug', '!=', 'user')
            ->where('slug', '!=', 'super-admin')
            ->get();

        return view('admin.staff.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => ['required', 'confirmed', Password::min(8)],
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'status' => 'active',
        ]);

        $allowedRoleIds = Role::where('slug', '!=', 'user')
            ->where('slug', '!=', 'super-admin')
            ->pluck('id')
            ->toArray();

        $safeRoles = array_intersect($validated['roles'], $allowedRoleIds);
        $user->roles()->sync($safeRoles);

        ActivityLogger::log(
            'create',
            'User',
            $user->id,
            "Created staff member: {$user->name}"
        );

        return redirect()->route('admin.staff.index')
            ->with('success', 'Staff member created successfully.');
    }

    public function edit(User $staff)
    {
        if ($staff->isSuperAdmin() && $staff->id !== auth()->id()) {
            abort(403, 'Cannot edit other Super Admin accounts.');
        }

        $roles = Role::where('slug', '!=', 'user')
            ->where('slug', '!=', 'super-admin')
            ->get();
        $staff->load('roles');

        return view('admin.staff.edit', compact('staff', 'roles'));
    }

    public function update(Request $request, User $staff)
    {
        if ($staff->isSuperAdmin() && $staff->id !== auth()->id()) {
            abort(403, 'Cannot modify other Super Admin accounts.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $staff->id,
            'phone' => 'nullable|string|max:20',
            'status' => 'required|in:active,inactive,suspended',
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $oldData = $staff->only(['name', 'email', 'phone', 'status']);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'status' => $validated['status'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $staff->update($updateData);

        $allowedRoleIds = Role::where('slug', '!=', 'user')
            ->where('slug', '!=', 'super-admin')
            ->pluck('id')
            ->toArray();

        $safeRoles = array_intersect($validated['roles'], $allowedRoleIds);
        $staff->roles()->sync($safeRoles);

        ActivityLogger::log(
            'update',
            'User',
            $staff->id,
            "Updated staff member: {$staff->name}",
            $oldData,
            $staff->only(['name', 'email', 'phone', 'status'])
        );

        return redirect()->route('admin.staff.index')
            ->with('success', 'Staff member updated successfully.');
    }

    public function destroy(User $staff)
    {
        if ($staff->isSuperAdmin()) {
            return back()->withErrors(['error' => 'Cannot delete Super Admin.']);
        }

        if ($staff->id === auth()->id()) {
            return back()->withErrors(['error' => 'Cannot delete your own account.']);
        }

        $name = $staff->name;
        $staff->delete();

        ActivityLogger::log(
            'delete',
            'User',
            $staff->id,
            "Deleted staff member: {$name}"
        );

        return redirect()->route('admin.staff.index')
            ->with('success', 'Staff member deleted successfully.');
    }
}
