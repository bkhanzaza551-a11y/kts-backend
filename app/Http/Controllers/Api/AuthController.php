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

        ActivityLogger::log('register', 'User', $user->id, 'New user registered via API - email verification pending');

        $emailSent = false;
        $maxRetries = 3;
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                Mail::raw("Your KTS Markets verification code is: {$otpRecord->otp}\n\nThis code expires in 5 minutes.\n\nIf you didn't register, ignore this email.", function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject("KTS Markets - Email Verification");
                });
                $emailSent = true;
                \Illuminate\Support\Facades\DB::table('email_logs')->insert([
                    'user_id' => $user->id, 'type' => 'confirmation', 'status' => 'sent',
                    'resent_by' => 'system', 'created_at' => now(),
                ]);
                break;
            } catch (\Exception $e) {
                \Log::warning("Email send attempt {$attempt} failed: " . $e->getMessage());
                if ($attempt < $maxRetries) {
                    sleep(2);
                }
            }
        }

        if (!$emailSent) {
            \Illuminate\Support\Facades\DB::table('email_logs')->insert([
                'user_id' => $user->id, 'type' => 'confirmation', 'status' => 'failed',
                'resent_by' => 'system', 'created_at' => now(),
            ]);
        }

        $response = [
            'success' => true,
            'message' => 'Registration successful. Please verify your email with the OTP sent.',
            'data' => [
                'user' => $user->load('roles'),
                'requires_email_verification' => true,
                'email' => $user->email,
                'otp' => $emailSent ? null : $otpRecord->otp,
            ],
        ];

        if (!$emailSent) {
            $response['message'] = 'Registration successful. Email could not be sent. Use this OTP to verify: ' . $otpRecord->otp;
        }

        return response()->json($response, 201);
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

        $sent = false;
        $maxRetries = 3;
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                Mail::raw("Your KTS Markets verification code is: {$otpRecord->otp}\n\nThis code expires in 5 minutes.\n\nIf you didn't register, ignore this email.", function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject("KTS Markets - Email Verification");
                });
                $sent = true;
                \Illuminate\Support\Facades\DB::table('email_logs')->insert([
                    'user_id' => $user->id, 'type' => 'confirmation', 'status' => 'sent',
                    'resent_by' => 'system', 'created_at' => now(),
                ]);
                break;
            } catch (\Exception $e) {
                \Log::warning("Email resend attempt {$attempt} failed: " . $e->getMessage());
                if ($attempt < $maxRetries) {
                    sleep(2);
                }
            }
        }

        if (!$sent) {
            \Illuminate\Support\Facades\DB::table('email_logs')->insert([
                'user_id' => $user->id, 'type' => 'confirmation', 'status' => 'failed',
                'resent_by' => 'system', 'created_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => $sent ? 'OTP resent to your email.' : 'Email could not be sent. Use this OTP to verify: ' . $otpRecord->otp,
            'data' => [
                'otp' => $sent ? null : $otpRecord->otp,
            ],
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

        if ($user->is_banned || $user->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been suspended.',
            ], 403);
        }

        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        $user->tokens()->delete();
        $token = $user->createToken('auth-token')->plainTextToken;

        ActivityLogger::log('login', 'User', $user->id, 'User logged in via API');

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => [
                'user' => $user->load('roles'),
                'token' => $token,
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
            'whatsapp' => 'nullable|string|max:20',
            'gender' => 'nullable|string|in:male,female,other',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'demo_account_id' => 'nullable|string|max:50',
            'demo_account_server' => 'nullable|string|max:100',
            'real_account_id' => 'nullable|string|max:50',
            'real_account_server' => 'nullable|string|max:100',
            'broker_name' => 'nullable|string|max:100',
            'is_profile_completed' => 'nullable|boolean',
            'avatar' => 'nullable',
        ]);

        $oldData = $user->only(['name', 'phone', 'whatsapp', 'gender', 'city', 'country', 'demo_account_id', 'demo_account_server', 'real_account_id', 'real_account_server', 'broker_name']);

        // Handle avatar upload (File or Base64 string)
        if ($request->hasFile('avatar')) {
            try {
                if ($user->avatar && \Storage::disk('public')->exists($user->avatar)) {
                    \Storage::disk('public')->delete($user->avatar);
                }
                $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
            } catch (\Throwable $e) {
                unset($validated['avatar']);
            }
        } elseif (!empty($validated['avatar']) && is_string($validated['avatar']) && str_starts_with($validated['avatar'], 'data:image') && str_contains($validated['avatar'], ';base64,')) {
            try {
                if ($user->avatar && \Storage::disk('public')->exists($user->avatar)) {
                    \Storage::disk('public')->delete($user->avatar);
                }
                $imageData = $validated['avatar'];
                $imageParts = explode(';base64,', $imageData);
                if (isset($imageParts[1]) && !empty($imageParts[1])) {
                    $imageTypeAux = explode('image/', $imageParts[0]);
                    $imageType = $imageTypeAux[1] ?? 'png';
                    $imageType = explode(';', $imageType)[0];
                    $imageBase64 = base64_decode($imageParts[1]);
                    $fileName = 'avatars/' . uniqid('avatar_') . '.' . $imageType;
                    \Storage::disk('public')->put($fileName, $imageBase64);
                    $validated['avatar'] = $fileName;
                } else {
                    unset($validated['avatar']);
                }
            } catch (\Throwable $e) {
                unset($validated['avatar']);
            }
        } elseif (!isset($validated['avatar']) || empty($validated['avatar']) || (is_string($validated['avatar']) && str_starts_with($validated['avatar'], 'http'))) {
            unset($validated['avatar']);
        }

        $user->update($validated);

        try {
            ActivityLogger::log(
                'update_profile',
                'User',
                (string)$user->id,
                'Profile updated via API',
                $oldData,
                $user->only(['name', 'phone', 'whatsapp', 'city', 'country', 'demo_account_id', 'real_account_id'])
            );
        } catch (\Throwable $e) {
            // Ignore activity log failure
        }

        $freshUser = $user->fresh()->load('roles');
        if ($freshUser->avatar && !str_starts_with($freshUser->avatar, 'http')) {
            $freshUser->avatar = \Storage::disk('public')->url($freshUser->avatar);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data' => [
                'user' => $freshUser,
            ],
        ]);
    }

    public function deleteAccount(Request $request)
    {
        $user = $request->user();

        if ($user->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Super Administrator accounts cannot be deleted directly from the app.',
            ], 403);
        }

        ActivityLogger::log('delete_account', 'User', $user->id, 'User requested permanent account deletion via mobile app');

        if ($user->avatar && \Storage::disk('public')->exists($user->avatar)) {
            \Storage::disk('public')->delete($user->avatar);
        }

        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Your account and all associated personal data have been permanently deleted.',
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
        \Illuminate\Support\Facades\DB::table('password_resets')->updateOrInsert(
            ['email' => $user->email],
            ['token' => Hash::make($resetToken), 'created_at' => now()]
        );

        ActivityLogger::log('forgot_password', 'User', $user->id, 'Password reset requested via API');

        // Return response FIRST, send email in background
        dispatch(function () use ($user, $resetToken) {
            try {
                Mail::raw("You have requested a password reset for your KTS Markets account.\n\nYour reset token is: {$resetToken}\n\nIf you did not request this, please ignore this email.", function ($message) use ($user) {
                    $message->to($user->email)->subject("KTS Markets - Password Reset");
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
        })->afterCommit();

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

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired reset token.'], 400);
        }

        $resetRecord = \Illuminate\Support\Facades\DB::table('password_resets')
            ->where('email', $validated['email'])
            ->latest()
            ->first();

        if (!$resetRecord || !\Illuminate\Support\Hash::check($validated['token'], $resetRecord->token)) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired reset token.'], 400);
        }

        if (\Carbon\Carbon::parse($resetRecord->created_at)->diffInMinutes(now()) > 60) {
            return response()->json(['success' => false, 'message' => 'Reset token has expired. Please request a new one.'], 400);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        $user->tokens()->delete();
        \Illuminate\Support\Facades\DB::table('password_resets')->where('email', $validated['email'])->delete();

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
