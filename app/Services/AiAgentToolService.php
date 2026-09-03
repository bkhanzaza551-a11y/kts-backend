<?php

namespace App\Services;

use App\Models\AdminOtp;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class AiAgentToolService
{
    /**
     * Define all tools available to the AI agent.
     */
    public function getTools(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'check_email_log',
                    'description' => 'Check if an email was sent to a user. Returns email status (sent, delivered, failed) and details.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'user_id' => ['type' => 'integer', 'description' => 'The user ID to check email for'],
                            'email_type' => ['type' => 'string', 'description' => 'Type of email: confirmation, reset_password, otp, support, welcome'],
                        ],
                        'required' => ['user_id', 'email_type'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'resend_email',
                    'description' => 'Resend an email to a user. Use when email was not delivered or user did not receive it.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'user_id' => ['type' => 'integer', 'description' => 'The user ID to resend email to'],
                            'email_type' => ['type' => 'string', 'description' => 'Type of email: confirmation, reset_password, otp, support, welcome'],
                        ],
                        'required' => ['user_id', 'email_type'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'check_user_subscription',
                    'description' => 'Check user subscription/premium status, plan details, and expiry date.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'user_id' => ['type' => 'integer', 'description' => 'The user ID to check subscription for'],
                        ],
                        'required' => ['user_id'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'check_bot_status',
                    'description' => 'Check MT5 bot status, connection, and recent trades for a user.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'user_id' => ['type' => 'integer', 'description' => 'The user ID to check bot status for'],
                        ],
                        'required' => ['user_id'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'create_support_ticket',
                    'description' => 'Create a support ticket for admin to review. Use when issue needs human attention.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'user_id' => ['type' => 'integer', 'description' => 'The user ID who needs support'],
                            'subject' => ['type' => 'string', 'description' => 'Subject/title of the support ticket'],
                            'description' => ['type' => 'string', 'description' => 'Detailed description of the issue'],
                            'priority' => ['type' => 'string', 'enum' => ['low', 'medium', 'high', 'urgent'], 'description' => 'Priority level'],
                        ],
                        'required' => ['user_id', 'subject', 'description'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'send_notification',
                    'description' => 'Send an in-app notification to a user.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'user_id' => ['type' => 'integer', 'description' => 'The user ID to send notification to'],
                            'title' => ['type' => 'string', 'description' => 'Notification title'],
                            'body' => ['type' => 'string', 'description' => 'Notification body/message'],
                            'type' => ['type' => 'string', 'enum' => ['info', 'warning', 'success', 'error'], 'description' => 'Notification type'],
                        ],
                        'required' => ['user_id', 'title', 'body'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Execute a tool call from the AI.
     */
    public function executeTool(string $toolName, array $arguments, ?int $userId = null): array
    {
        $this->logAction($toolName, $arguments, $userId, 'started');

        try {
            $result = match ($toolName) {
                'check_email_log' => $this->checkEmailLog($arguments['user_id'], $arguments['email_type']),
                'resend_email' => $this->resendEmail($arguments['user_id'], $arguments['email_type']),
                'check_user_subscription' => $this->checkUserSubscription($arguments['user_id']),
                'check_bot_status' => $this->checkBotStatus($arguments['user_id']),
                'create_support_ticket' => $this->createSupportTicket($arguments, $userId),
                'send_notification' => $this->sendNotification($arguments),
                default => ['success' => false, 'error' => "Unknown tool: {$toolName}"],
            };

            $this->logAction($toolName, $arguments, $userId, 'completed', $result);

            return $result;
        } catch (\Exception $e) {
            $errorResult = ['success' => false, 'error' => $e->getMessage()];
            $this->logAction($toolName, $arguments, $userId, 'failed', $errorResult);
            return $errorResult;
        }
    }

    /**
     * Check email log for a user.
     * BUG FIX: Now checks both email_logs AND admin_otps tables.
     */
    private function checkEmailLog(int $userId, string $emailType): array
    {
        // Check email_logs table first
        $emailLog = DB::table('email_logs')
            ->where('user_id', $userId)
            ->where('type', $emailType)
            ->latest()
            ->first();

        if ($emailLog) {
            return [
                'success' => true,
                'found' => true,
                'status' => $emailLog->status ?? 'unknown',
                'sent_at' => $emailLog->created_at ?? null,
                'delivered' => ($emailLog->status ?? '') === 'delivered',
                'action_needed' => ($emailLog->status ?? '') !== 'delivered' ? 'resend_email' : 'none',
            ];
        }

        // Fallback: check admin_otps table for OTP-type emails
        if (in_array($emailType, ['otp', 'confirmation'])) {
            $otpRecord = AdminOtp::where('user_id', $userId)
                ->latest()
                ->first();

            if ($otpRecord) {
                return [
                    'success' => true,
                    'found' => true,
                    'status' => 'sent',
                    'sent_at' => $otpRecord->created_at ?? null,
                    'delivered' => false,
                    'action_needed' => 'resend_email',
                    'note' => 'OTP was generated but delivery status unknown',
                ];
            }
        }

        return [
            'success' => true,
            'found' => false,
            'message' => "No {$emailType} email found for user #{$userId}",
            'action_needed' => 'send_email',
        ];
    }

    /**
     * Resend email to a user.
     * BUG FIX: Now uses proper OTP system and logs to email_logs.
     */
    private function resendEmail(int $userId, string $emailType): array
    {
        $user = User::find($userId);
        if (!$user) {
            return ['success' => false, 'error' => 'User not found'];
        }

        try {
            match ($emailType) {
                'confirmation' => $this->sendConfirmationEmail($user),
                'reset_password' => $this->sendResetPasswordEmail($user),
                'otp' => $this->sendOtpEmail($user),
                'welcome' => $this->sendWelcomeEmail($user),
                'support' => $this->sendSupportEmail($user),
                default => null,
            };

            // Log to email_logs table
            DB::table('email_logs')->insert([
                'user_id' => $userId,
                'type' => $emailType,
                'status' => 'sent',
                'resent_by' => 'ai_agent',
                'created_at' => now(),
            ]);

            return [
                'success' => true,
                'message' => "Successfully resent {$emailType} email to {$user->email}",
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Failed to send email: ' . $e->getMessage()];
        }
    }

    /**
     * Check user subscription/premium status.
     * BUG FIX: User model has is_premium + premium_expires_at, NOT subscription() relationship.
     */
    private function checkUserSubscription(int $userId): array
    {
        $user = User::find($userId);
        if (!$user) {
            return ['success' => false, 'error' => 'User not found'];
        }

        $isPremium = $user->is_premium && $user->premium_expires_at && $user->premium_expires_at->isFuture();

        return [
            'success' => true,
            'has_subscription' => $isPremium,
            'is_premium' => $user->is_premium,
            'premium_expires_at' => $user->premium_expires_at ?? null,
            'is_active' => $isPremium,
            'days_remaining' => $isPremium ? max(0, $user->premium_expires_at->diffInDays(now())) : 0,
        ];
    }

    /**
     * Check MT5 bot status.
     * BUG FIX: Table names corrected: mt5_bot_settings→mt5_bot_configs, mt5_trades→mt5_bot_trades
     */
    private function checkBotStatus(int $userId): array
    {
        $user = User::find($userId);
        if (!$user) {
            return ['success' => false, 'error' => 'User not found'];
        }

        $botConfig = DB::table('mt5_bot_configs')
            ->where('created_by', $userId)
            ->latest()
            ->first();

        $recentTrades = DB::table('mt5_bot_trades')
            ->where('bot_config_id', $botConfig?->id)
            ->latest()
            ->limit(5)
            ->get();

        $botLogs = DB::table('mt5_bot_logs')
            ->where('bot_config_id', $botConfig?->id)
            ->latest()
            ->first();

        return [
            'success' => true,
            'bot_configured' => !!$botConfig,
            'bot_active' => ($botConfig?->status ?? '') === 'active',
            'connection_status' => $botConfig?->last_connected_at ? 'connected' : ($botConfig?->error_message ?? 'unknown'),
            'last_trade_at' => $recentTrades->first()?->created_at ?? null,
            'recent_trades_count' => $recentTrades->count(),
            'last_log' => $botLogs?->message ?? null,
            'last_log_at' => $botLogs?->created_at ?? null,
        ];
    }

    /**
     * Create support ticket.
     * BUG FIX: Added proper email error handling.
     */
    private function createSupportTicket(array $data, ?int $agentUserId): array
    {
        $ticketId = DB::table('support_tickets')->insertGetId([
            'ticket_number' => 'TKT-' . strtoupper(\Illuminate\Support\Str::random(8)),
            'user_id' => $data['user_id'],
            'subject' => $data['subject'],
            'description' => $data['description'],
            'priority' => $data['priority'] ?? 'medium',
            'status' => 'open',
            'source' => 'ai_chatbot',
            'created_by_agent' => true,
            'agent_user_id' => $agentUserId,
            'created_at' => now(),
        ]);

        // Send email to user (with error handling)
        $emailSent = false;
        try {
            $user = User::find($data['user_id']);
            if ($user) {
                Mail::raw(
                    "Hi {$user->name},\n\n" .
                    "A support ticket has been created for your issue.\n\n" .
                    "Ticket ID: #{$ticketId}\n" .
                    "Subject: {$data['subject']}\n" .
                    "Priority: " . ucfirst($data['priority'] ?? 'medium') . "\n\n" .
                    "Our team will review and get back to you shortly.\n\n" .
                    "KTS Markets Support",
                    function ($message) use ($user, $ticketId) {
                        $message->to($user->email)
                            ->subject("Support Ticket #{$ticketId} Created - KTS Markets");
                    }
                );
                $emailSent = true;
            }
        } catch (\Exception $e) {
            \Log::error("Failed to send support ticket email: " . $e->getMessage());
        }

        // Log to email_logs
        DB::table('email_logs')->insert([
            'user_id' => $data['user_id'],
            'type' => 'support',
            'status' => $emailSent ? 'sent' : 'failed',
            'resent_by' => 'ai_agent',
            'created_at' => now(),
        ]);

        return [
            'success' => true,
            'ticket_id' => $ticketId,
            'email_sent' => $emailSent,
            'message' => "Support ticket #{$ticketId} created successfully" .
                ($emailSent ? " and email sent to user" : " (email sending failed)"),
        ];
    }

    /**
     * Send notification to user.
     * BUG FIX: Now uses admin_notifications table correctly OR creates a simple in-app notification.
     */
    private function sendNotification(array $data): array
    {
        // Use admin_notifications table for user-targeted notifications
        $notificationId = DB::table('admin_notifications')->insertGetId([
            'title' => $data['title'],
            'body' => $data['body'],
            'type' => $data['type'] ?? 'info',
            'target' => 'specific',
            'target_user_id' => $data['user_id'],
            'is_sent' => true,
            'sent_count' => 1,
            'created_at' => now(),
        ]);

        return [
            'success' => true,
            'notification_id' => $notificationId,
            'message' => "Notification sent to user #{$data['user_id']}",
        ];
    }

    // === Email Sending Helpers ===

    private function sendConfirmationEmail(User $user): void
    {
        $otpRecord = AdminOtp::generateFor($user);

        Mail::raw(
            "Your KTS Markets verification code is: {$otpRecord->otp}\n\n" .
            "This code expires in 5 minutes.\n\n" .
            "If you didn't register, ignore this email.",
            function ($message) use ($user, $otpRecord) {
                $message->to($user->email)
                    ->subject("KTS Markets - Email Verification Code: {$otpRecord->otp}");
            }
        );
    }

    private function sendResetPasswordEmail(User $user): void
    {
        $token = bin2hex(random_bytes(32));
        $hashedToken = \Illuminate\Support\Hash::make($token);
        DB::table('password_reset_tokens')->where('email', $user->email)->delete();
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => $hashedToken,
            'created_at' => now(),
        ]);

        Mail::raw(
            "You have requested a password reset for your KTS Markets account.\n\n" .
            "Your reset token is: {$token}\n\n" .
            "If you did not request this, please ignore this email.",
            function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('KTS Markets - Password Reset');
            }
        );
    }

    private function sendOtpEmail(User $user): void
    {
        $otpRecord = AdminOtp::generateFor($user);

        Mail::raw(
            "Your login verification code is: {$otpRecord->otp}\n\n" .
            "This code expires in 5 minutes.\n\n" .
            "If you didn't attempt to login, please secure your account.",
            function ($message) use ($user, $otpRecord) {
                $message->to($user->email)
                    ->subject("KTS Markets - Login OTP: {$otpRecord->otp}");
            }
        );
    }

    private function sendWelcomeEmail(User $user): void
    {
        Mail::raw(
            "Welcome to KTS Markets!\n\n" .
            "Hi {$user->name},\n\n" .
            "Your account is ready. Start exploring our trading signals and MT5 bots.\n\n" .
            "Need help? Just chat with our AI assistant!\n\n" .
            "KTS Markets Team",
            function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Welcome to KTS Markets!');
            }
        );
    }

    private function sendSupportEmail(User $user): void
    {
        Mail::raw(
            "Hi {$user->name},\n\n" .
            "Our team has received your support request and will get back to you within 24 hours.\n\n" .
            "Thank you for your patience!\n\n" .
            "KTS Markets Support",
            function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Support Request Received - KTS Markets');
            }
        );
    }

    /**
     * Log agent action for audit trail.
     */
    private function logAction(string $tool, array $args, ?int $userId, string $status, array $result = []): void
    {
        try {
            DB::table('agent_action_logs')->insert([
                'tool_name' => $tool,
                'arguments' => json_encode($args),
                'agent_user_id' => $userId,
                'status' => $status,
                'result' => json_encode($result),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to log agent action: ' . $e->getMessage());
        }
    }
}
