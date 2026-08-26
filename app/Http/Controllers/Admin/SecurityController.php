<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminOtp;
use App\Models\AdminSecurityCode;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SecurityController extends Controller
{
    public function showChangeForm()
    {
        $securityCodes = AdminSecurityCode::where('user_id', Auth::id())
            ->where('is_active', true)
            ->get();

        return view('admin.settings.security-code', compact('securityCodes'));
    }

    public function sendOtp(Request $request)
    {
        $user = Auth::user();

        $otpRecord = AdminOtp::generateFor($user, $request->ip());

        try {
            \Illuminate\Support\Facades\Mail::to($user->email)
                ->send(new \App\Mail\OtpMail($otpRecord->otp, $user->name, 'security_code_change'));
        } catch (\Exception $e) {
            \Log::error('Failed to send OTP for security code change: ' . $e->getMessage());
        }

        $request->session()->put('security_change_otp_expires_at', $otpRecord->expires_at->timestamp);

        ActivityLogger::log('otp_sent', 'User', $user->id, 'OTP sent for security code change');

        return redirect()->route('admin.security.verify-otp');
    }

    public function showOtpForm()
    {
        if (!session('security_change_otp_expires_at')) {
            return redirect()->route('admin.security.change-form');
        }

        return view('admin.settings.security-otp');
    }

    public function verifyOtp(Request $request)
    {
        $validated = $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $userId = Auth::id();

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

        $request->session()->put('security_change_otp_verified', true);

        ActivityLogger::log('otp_verified', 'User', $userId, 'OTP verified for security code change');

        return redirect()->route('admin.security.new-code-form');
    }

    public function showNewCodeForm()
    {
        if (!session('security_change_otp_verified')) {
            return redirect()->route('admin.security.change-form');
        }

        return view('admin.settings.security-new-code');
    }

    public function updateCode(Request $request)
    {
        $validated = $request->validate([
            'current_security_code' => 'required|string|size:8',
            'new_security_code' => 'required|string|size:8|alpha_num',
            'confirm_security_code' => 'required|string|size:8|alpha_num|same:new_security_code',
        ]);

        if ($validated['new_security_code'] !== $validated['confirm_security_code']) {
            return back()->withErrors([
                'confirm_security_code' => 'Security codes do not match.',
            ])->withInput();
        }

        $userId = Auth::id();

        $currentCode = AdminSecurityCode::where('user_id', $userId)
            ->where('is_active', true)
            ->first();

        if (!$currentCode || !$currentCode->verify($validated['current_security_code'])) {
            return back()->withErrors([
                'current_security_code' => 'Current security code is incorrect.',
            ])->withInput();
        }

        $currentCode->update(['is_active' => false]);

        $newCode = AdminSecurityCode::create([
            'user_id' => $userId,
            'code' => Hash::make(strtoupper($validated['new_security_code'])),
            'label' => 'Updated ' . now()->format('M d, Y'),
            'is_active' => true,
        ]);

        $request->session()->forget(['security_change_otp_expires_at', 'security_change_otp_verified']);

        ActivityLogger::log('security_code_changed', 'User', $userId, 'Admin security code changed');

        return redirect()->route('admin.security.change-form')
            ->with('success', 'Security code changed successfully. Your new code is: ' . strtoupper($validated['new_security_code']));
    }
}
