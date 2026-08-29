<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminOtp;
use App\Models\AdminSecurityCode;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            if ($user->status !== 'active') {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Your account is not active.',
                ])->withInput($request->only('email'));
            }

            if ($user->is_banned) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Your account has been banned.',
                ])->withInput($request->only('email'));
            }

            Auth::logout();

            if ($request->boolean('remember')) {
                $request->session()->put('remember_me', true);
            }

            $otpRecord = AdminOtp::generateFor($user, $request->ip());

            $request->session()->put('otp_user_id', $user->id);
            $request->session()->put('otp_expires_at', $otpRecord->expires_at->timestamp);

            try {
                Mail::to($user->email)->send(new \App\Mail\OtpMail($otpRecord->otp, $user->name));
            } catch (\Exception $e) {
                \Log::error('Failed to send OTP email: ' . $e->getMessage());
            }

            ActivityLogger::log('otp_sent', 'User', $user->id, 'OTP sent for admin login');

            return redirect()->route('admin.otp.verify');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('email'));
    }

    public function showOtpForm()
    {
        if (!session('otp_user_id')) {
            return redirect()->route('admin.login');
        }

        return view('auth.otp-verify');
    }

    public function verifyOtp(Request $request)
    {
        $validated = $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $userId = session('otp_user_id');
        if (!$userId) {
            return redirect()->route('admin.login');
        }

        $otpRecord = AdminOtp::where('user_id', $userId)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$otpRecord || !$otpRecord->verify($validated['otp'])) {
            return back()->withErrors([
                'otp' => 'Invalid or expired OTP. Please try again.',
            ])->withInput();
        }

        $user = \App\Models\User::find($userId);
        if (!$user) {
            return redirect()->route('admin.login');
        }

        $request->session()->put('otp_verified', true);
        $request->session()->put('otp_verified_at', now()->timestamp);

        $hasSecurityCode = AdminSecurityCode::where('user_id', $user->id)
            ->where('is_active', true)
            ->exists();

        if (!$hasSecurityCode) {
            $result = AdminSecurityCode::generateFor($user, 'Initial Security Code');
            $request->session()->put('show_security_code', $result['code']);
            $request->session()->put('show_security_code_id', $result['id']);
        }

        ActivityLogger::log('otp_verified', 'User', $user->id, 'OTP verified for admin login');

        return redirect()->route('admin.security-code.verify');
    }

    public function showSecurityCodeForm()
    {
        if (!session('otp_verified')) {
            return redirect()->route('admin.login');
        }

        $showCode = session('show_security_code');
        $securityCodeId = session('show_security_code_id');

        return view('auth.security-code-verify', compact('showCode', 'securityCodeId'));
    }

    public function verifySecurityCode(Request $request)
    {
        $validated = $request->validate([
            'security_code' => 'required|string|size:8',
        ]);

        $userId = session('otp_user_id');
        if (!$userId || !session('otp_verified')) {
            return redirect()->route('admin.login');
        }

        $user = \App\Models\User::find($userId);
        if (!$user) {
            return redirect()->route('admin.login');
        }

        $securityCode = AdminSecurityCode::where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        if (!$securityCode || !$securityCode->verify($validated['security_code'])) {
            return back()->withErrors([
                'security_code' => 'Invalid security code. Please try again.',
            ])->withInput();
        }

        Auth::login($user, session('remember_me', false));

        $request->session()->regenerate();
        $request->session()->forget([
            'otp_user_id', 'otp_expires_at', 'otp_verified',
            'otp_verified_at', 'show_security_code', 'show_security_code_id',
            'remember_me',
        ]);

        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        ActivityLogger::log('login', 'User', $user->id, 'Admin logged in (2FA complete)');

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request)
    {
        ActivityLogger::log('logout', 'User', Auth::id(), 'Admin logged out');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    public function resendOtp(Request $request)
    {
        $userId = session('otp_user_id');
        if (!$userId) {
            return redirect()->route('admin.login');
        }

        $user = \App\Models\User::find($userId);
        if (!$user) {
            return redirect()->route('admin.login');
        }

        AdminOtp::where('user_id', $user->id)
            ->where('is_used', false)
            ->update(['is_used' => true]);

        $otpRecord = AdminOtp::generateFor($user, $request->ip());

        $request->session()->put('otp_expires_at', $otpRecord->expires_at->timestamp);

        try {
            Mail::to($user->email)->send(new \App\Mail\OtpMail($otpRecord->otp, $user->name));
        } catch (\Exception $e) {
            \Log::error('Failed to resend OTP email: ' . $e->getMessage());
        }

        ActivityLogger::log('otp_sent', 'User', $user->id, 'OTP resent for admin login');

        return back()->with('success', 'New OTP has been sent to your email.');
    }
}
