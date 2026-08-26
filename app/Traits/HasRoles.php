<?php

namespace App\Traits;

use App\Models\Permission;
use App\Models\Role;

trait HasRoles
{
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_role');
    }

    public function assignRole(string|array $roles): void
    {
        $roleModels = Role::whereIn('slug', (array) $roles)->get();
        $this->roles()->syncWithoutDetaching($roleModels->pluck('id'));
    }

    public function removeRole(string|array $roles): void
    {
        $roleModels = Role::whereIn('slug', (array) $roles)->get();
        $this->roles()->detach($roleModels->pluck('id'));
    }

    public function hasRole(string|array $roles): bool
    {
        if ($this->relationLoaded('roles')) {
            return $this->roles->contains(fn ($role) => in_array($role->slug, (array) $roles));
        }

        return $this->roles()->whereIn('slug', (array) $roles)->exists();
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->hasRole('super-admin')) {
            return true;
        }

        if ($this->relationLoaded('roles')) {
            $allPermissions = $this->roles->flatMap(function ($role) {
                if ($role->relationLoaded('permissions')) {
                    return $role->permissions;
                }
                return $role->permissions()->get();
            });

            return $allPermissions->contains('slug', $permission);
        }

        return $this->roles()
            ->with('permissions')
            ->get()
            ->flatMap->permissions
            ->contains('slug', $permission);
    }

    public function hasAnyPermission(array $permissions): bool
    {
        if ($this->hasRole('super-admin')) {
            return true;
        }

        if ($this->relationLoaded('roles')) {
            $allPermissions = $this->roles->flatMap(function ($role) {
                if ($role->relationLoaded('permissions')) {
                    return $role->permissions;
                }
                return $role->permissions()->get();
            });

            return $allPermissions->whereIn('slug', $permissions)->isNotEmpty();
        }

        return $this->roles()
            ->with('permissions')
            ->get()
            ->flatMap->permissions
            ->whereIn('slug', $permissions)
            ->isNotEmpty();
    }

    public function hasAllPermissions(array $permissions): bool
    {
        if ($this->hasRole('super-admin')) {
            return true;
        }

        $userPermissions = $this->getPermissions()->pluck('slug')->toArray();

        return empty(array_diff($permissions, $userPermissions));
    }

    public function getPermissions(): \Illuminate\Support\Collection
    {
        if ($this->hasRole('super-admin')) {
            return Permission::all();
        }

        if ($this->relationLoaded('roles')) {
            $allPermissions = $this->roles->flatMap(function ($role) {
                if ($role->relationLoaded('permissions')) {
                    return $role->permissions;
                }
                return $role->permissions()->get();
            });

            return $allPermissions->unique('id');
        }

        return $this->roles()
            ->with('permissions')
            ->get()
            ->flatMap->permissions
            ->unique('id');
    }
}
