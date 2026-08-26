<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $otp,
        public string $userName,
        public string $purpose = 'login',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your KTS Markets Admin OTP',
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: $this->buildHtml(),
        );
    }

    private function buildHtml(): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <style>
                body { font-family: Arial, sans-serif; background: #1a1a2e; color: #eee; margin: 0; padding: 20px; }
                .container { max-width: 500px; margin: 0 auto; background: #16213e; border-radius: 12px; padding: 30px; border: 1px solid #0f3460; }
                .header { text-align: center; margin-bottom: 25px; }
                .logo { font-size: 24px; font-weight: bold; color: #0ea5e9; }
                .otp-box { background: #0f3460; border-radius: 8px; padding: 20px; text-align: center; margin: 20px 0; }
                .otp-code { font-size: 36px; font-weight: bold; color: #0ea5e9; letter-spacing: 8px; font-family: monospace; }
                .warning { background: #7c2d12; border-radius: 8px; padding: 15px; margin: 20px 0; font-size: 13px; color: #fbbf24; }
                .footer { text-align: center; font-size: 12px; color: #666; margin-top: 25px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <div class='logo'>KTS Markets</div>
                    <p style='color:#94a3b8;'>Admin Panel Verification</p>
                </div>
                <p>Hello {$this->userName},</p>
                <p>Your one-time password (OTP) for admin login is:</p>
                <div class='otp-box'>
                    <div class='otp-code'>{$this->otp}</div>
                </div>
                <div class='warning'>
                    <strong>⚠ Security Notice:</strong><br>
                    This OTP expires in 5 minutes. Do not share this code with anyone. If you did not request this login, please secure your account immediately.
                </div>
                <div class='footer'>
                    <p>KTS Markets &copy; " . date('Y') . " | Codex Aura Solutions</p>
                </div>
            </div>
        </body>
        </html>";
    }
}
