<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('permissions', 'users')->latest()->paginate(20);

        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::orderBy('module')->orderBy('action')->get();
        $grouped = $permissions->groupBy('module');

        return view('admin.roles.create', compact('permissions', 'grouped'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                'unique:roles,slug',
            ],
            'description' => 'nullable|string|max:500',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $systemSlugs = ['super-admin', 'admin', 'user'];
        if (in_array($validated['slug'], $systemSlugs)) {
            return back()->withErrors(['slug' => 'This slug is reserved.'])->withInput();
        }

        $role = Role::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
        ]);

        $role->permissions()->sync($validated['permissions']);

        Cache::forget('staff_role_slugs');

        ActivityLogger::log(
            'create',
            'Role',
            $role->id,
            "Created role: {$role->name}"
        );

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role created successfully.');
    }

    public function edit(Role $role)
    {
        $permissions = Permission::orderBy('module')->orderBy('action')->get();
        $grouped = $permissions->groupBy('module');
        $role->load('permissions');

        return view('admin.roles.edit', compact('role', 'permissions', 'grouped'));
    }

    public function update(Request $request, Role $role)
    {
        $rules = [
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'description' => 'nullable|string|max:500',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ];

        if (!$role->is_system) {
            $rules['slug'] = [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                'unique:roles,slug,' . $role->id,
            ];
        }

        $validated = $request->validate($rules);

        $oldData = $role->only(['name', 'slug', 'description']);

        $updateData = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ];

        if (!$role->is_system && isset($validated['slug'])) {
            $systemSlugs = ['super-admin', 'admin', 'user'];
            if (in_array($validated['slug'], $systemSlugs)) {
                return back()->withErrors(['slug' => 'This slug is reserved.'])->withInput();
            }
            $updateData['slug'] = $validated['slug'];
        }

        $role->update($updateData);

        $role->permissions()->sync($validated['permissions']);

        Cache::forget('staff_role_slugs');

        ActivityLogger::log(
            'update',
            'Role',
            $role->id,
            "Updated role: {$role->name}",
            $oldData,
            $role->only(['name', 'slug', 'description'])
        );

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        if ($role->is_system) {
            return back()->withErrors(['error' => 'Cannot delete system roles.']);
        }

        if ($role->users()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete role with assigned users.']);
        }

        $name = $role->name;
        $role->delete();

        Cache::forget('staff_role_slugs');

        ActivityLogger::log(
            'delete',
            'Role',
            $role->id,
            "Deleted role: {$name}"
        );

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role deleted successfully.');
    }
}
