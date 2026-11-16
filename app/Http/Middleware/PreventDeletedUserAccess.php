<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PreventDeletedUserAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            $user = $request->user();

            if ($user->trashed()) {
                if ($user->currentAccessToken()) {
                    $user->currentAccessToken()->delete();
                }

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Your account has been deleted.',
                    ], 401);
                }

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('admin.login')
                    ->withErrors(['email' => 'Your account has been deleted.']);
            }

            if ($user->is_banned || $user->status === 'suspended') {
                if ($user->currentAccessToken()) {
                    $user->currentAccessToken()->delete();
                }

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Your account has been suspended.',
                    ], 403);
                }

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('admin.login')
                    ->withErrors(['email' => 'Your account has been suspended.']);
            }

            // Track last login / last active timestamp automatically for authenticated users
            if (!$user->last_login_at || $user->last_login_at->diffInMinutes(now()) >= 2) {
                $user->update([
                    'last_login_at' => now(),
                    'last_login_ip' => $request->ip(),
                ]);
            }
        }

        return $next($request);
    }
}
