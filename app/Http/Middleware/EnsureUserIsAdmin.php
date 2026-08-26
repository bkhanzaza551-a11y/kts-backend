<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    private const STAFF_ROLE_SLUGS = [
        'super-admin',
        'admin',
        'signal-manager',
        'chat-moderator',
        'support-manager',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return redirect()->route('admin.login');
        }

        $staffSlugs = Cache::remember('staff_role_slugs', 3600, function () {
            return \App\Models\Role::where('is_system', false)
                ->orWhereIn('slug', self::STAFF_ROLE_SLUGS)
                ->pluck('slug')
                ->toArray();
        });

        if (!$request->user()->hasRole($staffSlugs)) {
            abort(403, 'Unauthorized. Staff access required.');
        }

        return $next($request);
    }
}
