<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', 'like', '%' . $request->action . '%');
        }

        if ($request->filled('model')) {
            $query->where('model', $request->model);
        }

        if ($request->filled('date_from') && $this->isValidDate($request->input('date_from'))) {
            $query->where('created_at', '>=', Carbon::parse($request->input('date_from'))->startOfDay());
        }

        if ($request->filled('date_to') && $this->isValidDate($request->input('date_to'))) {
            $query->where('created_at', '<=', Carbon::parse($request->input('date_to'))->endOfDay());
        }

        if ($request->filled('search')) {
            $search = str_replace(['%', '_'], ['\%', '\_'], trim($request->search));
            if ($search !== '') {
                $lower = strtolower($search);
                $query->where(function ($q) use ($lower) {
                    $q->whereRaw('LOWER(COALESCE(description, \'\')) LIKE ?', ["%{$lower}%"])
                        ->orWhereRaw('LOWER(COALESCE(ip_address, \'\')) LIKE ?', ["%{$lower}%"])
                        ->orWhereRaw('LOWER(COALESCE(action, \'\')) LIKE ?', ["%{$lower}%"]);
                });
            }
        }

        $logs = $query->latest()->paginate(25)->withQueryString();
        $users = User::orderBy('name')->get();
        $models = ActivityLog::whereNotNull('model')->distinct()->pluck('model');
        $actions = ActivityLog::whereNotNull('action')->distinct()->pluck('action');

        return view('admin.audit-logs.index', compact('logs', 'users', 'models', 'actions'));
    }
}
