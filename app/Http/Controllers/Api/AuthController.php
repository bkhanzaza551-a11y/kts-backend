<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AdminOtp;
use App\Models\AdminSecurityCode;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'status' => 'inactive',
            'email_verified_at' => null,
        ]);

        $user->assignRole('user');

        $otpRecord = AdminOtp::generateFor($user, $request->ip());

        try {
            Mail::raw("Your KTS 10 Pips Bots verification code is: {$otpRecord->otp}\n\nThis code expires in 5 minutes.\n\nIf you didn't register, ignore this email.", function ($message) use ($user, $otpRecord) {
                $message->to($user->email)
                    ->subject("KTS 10 Pips Bots - Email Verification Code: {$otpRecord->otp}");
            });
            \Illuminate\Support\Facades\DB::table('email_logs')->insert([
                'user_id' => $user->id, 'type' => 'confirmation', 'status' => 'sent',
                'resent_by' => 'system', 'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::table('email_logs')->insert([
                'user_id' => $user->id, 'type' => 'confirmation', 'status' => 'failed',
                'resent_by' => 'system', 'created_at' => now(),
            ]);
        }

        ActivityLogger::log('register', 'User', $user->id, 'New user registered via API - email verification pending');

        return response()->json([
            'success' => true,
            'message' => 'Registration successful. Please verify your email with the OTP sent.',
            'data' => [
                'user' => $user->load('roles'),
                'requires_email_verification' => true,
                'email' => $user->email,
            ],
        ], 201);
    }

    public function verifyEmailOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        if ($user->email_verified_at) {
            return response()->json(['success' => false, 'message' => 'Email already verified. Please login.'], 422);
        }

        $otpRecord = AdminOtp::where('user_id', $user->id)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$otpRecord) {
            return response()->json(['success' => false, 'message' => 'OTP expired or not found. Please request a new one.'], 422);
        }

        if (!$otpRecord->verify($validated['otp'])) {
            return response()->json(['success' => false, 'message' => 'Invalid OTP code.'], 422);
        }

        $user->update([
            'email_verified_at' => now(),
            'status' => 'active',
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        ActivityLogger::log('verify_email', 'User', $user->id, 'Email verified via OTP');

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully.',
            'data' => [
                'user' => $user->fresh()->load('roles'),
                'token' => $token,
            ],
        ]);
    }

    public function resendEmailOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        if ($user->email_verified_at) {
            return response()->json(['success' => false, 'message' => 'Email already verified.'], 422);
        }

        $otpRecord = AdminOtp::generateFor($user, $request->ip());

        try {
            Mail::raw("Your KTS 10 Pips Bots verification code is: {$otpRecord->otp}\n\nThis code expires in 5 minutes.\n\nIf you didn't register, ignore this email.", function ($message) use ($user, $otpRecord) {
                $message->to($user->email)
                    ->subject("KTS 10 Pips Bots - Email Verification Code: {$otpRecord->otp}");
            });
            \Illuminate\Support\Facades\DB::table('email_logs')->insert([
                'user_id' => $user->id, 'type' => 'confirmation', 'status' => 'sent',
                'resent_by' => 'system', 'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::table('email_logs')->insert([
                'user_id' => $user->id, 'type' => 'confirmation', 'status' => 'failed',
                'resent_by' => 'system', 'created_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP resent to your email.',
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
            ], 401);
        }

        if ($user->is_banned) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been banned.',
            ], 403);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Your account is not active.',
            ], 403);
        }

        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        $user->tokens()->delete();
        $token = $user->createToken('auth-token')->plainTextToken;

        $otpRecord = AdminOtp::generateFor($user, $request->ip());

        try {
            Mail::raw("Your KTS 10 Pips Bots verification code is: {$otpRecord->otp}\n\nThis code expires in 5 minutes.\n\nIf you didn't attempt to login, please secure your account.", function ($message) use ($user, $otpRecord) {
                $message->to($user->email)->subject("KTS 10 Pips Bots - Login Verification Code: {$otpRecord->otp}");
            });
            \Illuminate\Support\Facades\DB::table('email_logs')->insert([
                'user_id' => $user->id, 'type' => 'otp', 'status' => 'sent',
                'resent_by' => 'system', 'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::table('email_logs')->insert([
                'user_id' => $user->id, 'type' => 'otp', 'status' => 'failed',
                'resent_by' => 'system', 'created_at' => now(),
            ]);
        }

        ActivityLogger::log('login', 'User', $user->id, 'User logged in via API');

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => [
                'user' => $user->load('roles'),
                'token' => $token,
                'requires_otp' => true,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        ActivityLogger::log('logout', 'User', $user->id, 'User logged out via API');

        $user->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    public function profile(Request $request)
    {
        $user = $request->user()->load('roles');
        if ($user->avatar && !str_starts_with($user->avatar, 'http')) {
            $user->avatar = \Storage::disk('public')->url($user->avatar);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
            ],
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'nullable|string|max:20',
            'demo_account_id' => 'nullable|string|max:50',
            'demo_account_server' => 'nullable|string|max:100',
            'real_account_id' => 'nullable|string|max:50',
            'real_account_server' => 'nullable|string|max:100',
            'broker_name' => 'nullable|string|max:100',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $oldData = $user->only(['name', 'phone', 'demo_account_id', 'demo_account_server', 'real_account_id', 'real_account_server', 'broker_name']);

        if ($request->hasFile('avatar')) {
            if ($user->avatar && \Storage::disk('public')->exists($user->avatar)) {
                \Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        unset($validated['avatar']);
        $user->update($validated);

        if ($request->hasFile('avatar')) {
            $user->update(['avatar' => $validated['avatar'] ?? $user->avatar]);
        }

        ActivityLogger::log(
            'update_profile',
            'User',
            $user->id,
            'Profile updated via API',
            $oldData,
            $user->only(['name', 'phone', 'demo_account_id', 'real_account_id'])
        );

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data' => [
                'user' => $user->fresh()->load('roles'),
            ],
        ]);
    }

    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', 'different:current_password', \Illuminate\Validation\Rules\Password::min(8)],
        ]);

        $user = $request->user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        ActivityLogger::log(
            'change_password',
            'User',
            $user->id,
            'Password changed via API'
        );

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully.',
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        $otpRecord = AdminOtp::where('user_id', $user->id)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$otpRecord) {
            return response()->json(['success' => false, 'message' => 'OTP expired or not found. Please login again.'], 422);
        }

        if (!$otpRecord->verify($validated['otp'])) {
            return response()->json(['success' => false, 'message' => 'Invalid OTP code.'], 422);
        }

        $hasSecurityCode = AdminSecurityCode::where('user_id', $user->id)->where('is_active', true)->exists();

        ActivityLogger::log('verify_otp', 'User', $user->id, 'OTP verified via API');

        if ($hasSecurityCode) {
            return response()->json([
                'success' => true,
                'message' => 'OTP verified. Security code required.',
                'data' => [
                    'user' => $user->load('roles'),
                    'requires_security_code' => true,
                ],
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'OTP verified successfully.',
            'data' => [
                'user' => $user->load('roles'),
                'token' => $token,
            ],
        ]);
    }

    public function verifySecurityCode(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'security_code' => 'required|string|size:8',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        $securityCode = AdminSecurityCode::where('user_id', $user->id)
            ->where('is_active', true)
            ->latest()
            ->first();

        if (!$securityCode) {
            return response()->json(['success' => false, 'message' => 'No active security code found.'], 422);
        }

        if (!$securityCode->verify($validated['security_code'])) {
            return response()->json(['success' => false, 'message' => 'Invalid security code.'], 422);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        ActivityLogger::log('verify_security_code', 'User', $user->id, 'Security code verified via API');

        return response()->json([
            'success' => true,
            'message' => 'Security code verified successfully.',
            'data' => [
                'user' => $user->load('roles'),
                'token' => $token,
            ],
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'No account found with this email.'], 404);
        }

        $resetToken = \Illuminate\Support\Str::random(60);
        $user->update([
            'remember_token' => $resetToken,
        ]);

        try {
            Mail::raw("You have requested a password reset for your KTS 10 Pips Bots account.\n\nYour reset token is: {$resetToken}\n\nIf you did not request this, please ignore this email.", function ($message) use ($user) {
                $message->to($user->email)->subject("KTS 10 Pips Bots - Password Reset");
            });
            \Illuminate\Support\Facades\DB::table('email_logs')->insert([
                'user_id' => $user->id, 'type' => 'reset_password', 'status' => 'sent',
                'resent_by' => 'system', 'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::table('email_logs')->insert([
                'user_id' => $user->id, 'type' => 'reset_password', 'status' => 'failed',
                'resent_by' => 'system', 'created_at' => now(),
            ]);
        }

        ActivityLogger::log('forgot_password', 'User', $user->id, 'Password reset requested via API');

        return response()->json([
            'success' => true,
            'message' => 'Password reset link has been sent to your email.',
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::min(8)],
        ]);

        $user = User::where('email', $validated['email'])
                    ->where('remember_token', $validated['token'])
                    ->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired reset token.'], 400);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
            'remember_token' => null,
        ]);

        ActivityLogger::log('reset_password', 'User', $user->id, 'Password successfully reset via API');

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully. You can now login.',
        ]);
    }

    public function googleAuth(Request $request)
    {
        $validated = $request->validate([
            'google_id' => 'required|string',
            'email' => 'required|email',
            'name' => 'required|string|max:255',
            'avatar' => 'nullable|string|max:500',
        ]);

        $user = User::where('google_id', $validated['google_id'])->first();

        if (!$user) {
            $user = User::where('email', $validated['email'])->first();

            if ($user) {
                $user->update([
                    'google_id' => $validated['google_id'],
                    'auth_provider' => 'google',
                    'avatar' => $user->avatar ?? $validated['avatar'] ?? null,
                    'email_verified_at' => now(),
                    'status' => 'active',
                ]);
            } else {
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'google_id' => $validated['google_id'],
                    'auth_provider' => 'google',
                    'avatar' => $validated['avatar'] ?? null,
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]);

                $user->assignRole('user');
            }
        }

        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        $user->tokens()->delete();
        $token = $user->createToken('mobile-app')->plainTextToken;

        ActivityLogger::log('google_login', 'User', $user->id, 'User logged in via Google');

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'user' => $user->fresh()->load('roles'),
            ],
        ]);
    }
}
