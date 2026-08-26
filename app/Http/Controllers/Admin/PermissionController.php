<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::orderBy('module')->orderBy('action')->get();
        $grouped = $permissions->groupBy('module');

        return view('admin.permissions.index', compact('permissions', 'grouped'));
    }
}
