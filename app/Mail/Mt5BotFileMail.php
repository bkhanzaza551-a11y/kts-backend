<?php

namespace App\Mail;

use App\Models\Mt5BotConfig;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class Mt5BotFileMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public Mt5BotConfig $bot,
        public string $adminNote = '',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🤖 Your KTS Markets MT5 Bot File is Ready!',
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: $this->buildHtml(),
        );
    }

    public function attachments(): array
    {
        if ($this->bot->bot_file_path && Storage::disk('public')->exists($this->bot->bot_file_path)) {
            return [
                Attachment::fromStorageDisk('public', $this->bot->bot_file_path)
                    ->as($this->bot->bot_file_name ?: basename($this->bot->bot_file_path))
                    ->withMime('application/octet-stream'),
            ];
        }
        return [];
    }

    private function buildHtml(): string
    {
        $userName   = e($this->user->name);
        $botName    = e($this->bot->name ?? 'KTS10 Pips Bot');
        $fileName   = e($this->bot->bot_file_name ?? 'KTS_Bot.ex5');
        $fileSize   = e($this->bot->bot_file_size ?? '');
        $fileType   = strtoupper($this->bot->bot_file_type ?? 'EX5');
        $adminNote  = $this->adminNote ? e($this->adminNote) : '';
        $year       = date('Y');

        $noteBadge = $adminNote
            ? "<div style='background:#1e3a5f;border-left:4px solid #f5a623;border-radius:6px;padding:14px 18px;margin:18px 0;font-size:14px;color:#fcd34d;'><strong>📝 Note from Admin:</strong><br>{$adminNote}</div>"
            : '';

        return "<!DOCTYPE html>
<html>
<head>
<meta charset='utf-8'>
<meta name='viewport' content='width=device-width,initial-scale=1'>
<style>
  body{margin:0;padding:0;background:#0a0a14;font-family:Arial,Helvetica,sans-serif;}
  a{color:#f5a623;}
</style>
</head>
<body style='background:#0a0a14;padding:30px 10px;'>
  <div style='max-width:560px;margin:0 auto;'>

    <!-- Header -->
    <div style='text-align:center;margin-bottom:28px;'>
      <div style='display:inline-block;background:linear-gradient(135deg,#f5a623,#ffc84a);border-radius:14px;padding:12px 24px;'>
        <span style='font-size:22px;font-weight:900;color:#000;letter-spacing:1px;'>KTS</span>
        <span style='font-size:16px;font-weight:700;color:#000;'> Markets</span>
      </div>
      <div style='color:#6b7280;font-size:12px;margin-top:8px;'>Trading Bot Distribution</div>
    </div>

    <!-- Main Card -->
    <div style='background:#12121a;border:1px solid #2a2a3a;border-radius:16px;overflow:hidden;'>

      <!-- Gold top bar -->
      <div style='height:4px;background:linear-gradient(90deg,#f5a623,#ffc84a,#f5a623);'></div>

      <div style='padding:32px;'>
        <h2 style='color:#f5f5f8;font-size:20px;margin:0 0 8px;'>🤖 Your MT5 Bot File is Ready!</h2>
        <p style='color:#94a3b8;font-size:14px;margin:0 0 24px;'>Hello <strong style='color:#e8e8f0;'>{$userName}</strong>, your bot software file has been approved and is ready to install.</p>

        {$noteBadge}

        <!-- File Info Box -->
        <div style='background:#1a1a26;border:1px solid #2a2a3a;border-radius:10px;padding:20px;margin-bottom:24px;'>
          <div style='display:flex;align-items:center;gap:14px;'>
            <div style='width:48px;height:48px;background:linear-gradient(135deg,#f5a623,#e08000);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0;text-align:center;line-height:48px;'>📦</div>
            <div>
              <div style='color:#f5a623;font-weight:700;font-size:15px;'>{$fileName}</div>
              <div style='color:#6b7280;font-size:12px;margin-top:3px;'>{$botName} &nbsp;•&nbsp; {$fileType} File {$fileSize}</div>
            </div>
          </div>
        </div>

        <!-- Instructions -->
        <div style='margin-bottom:24px;'>
          <h3 style='color:#e8e8f0;font-size:15px;margin:0 0 14px;'>📋 Installation Steps</h3>
          <div style='counter-reset:step;'>
            " . $this->step('1', 'Download the attached <strong style="color:#f5a623;">' . $fileName . '</strong> file from this email.') . "
            " . $this->step('2', 'Open <strong style="color:#f5a623;">MetaTrader 5</strong> on your computer.') . "
            " . $this->step('3', 'Go to <strong style="color:#f5a623;">File → Open Data Folder</strong> → MQL5 → Experts') . "
            " . $this->step('4', 'Copy the bot file into the <strong style="color:#f5a623;">Experts</strong> folder.') . "
            " . $this->step('5', 'Restart MT5 and find the bot in the <strong style="color:#f5a623;">Navigator → Expert Advisors</strong> panel.') . "
            " . $this->step('6', 'Drag the bot onto your chart and enable <strong style="color:#f5a623;">Auto Trading</strong>.') . "
          </div>
        </div>

        <!-- Warning -->
        <div style='background:#2d1810;border:1px solid #7c2d12;border-radius:8px;padding:14px 18px;margin-bottom:24px;font-size:13px;color:#fbbf24;'>
          <strong>⚠️ Important:</strong> Only install on a trusted device. Do not share this file with others. This bot file is provided exclusively for your account.
        </div>

        <!-- Support -->
        <p style='color:#94a3b8;font-size:13px;margin:0;'>Need help? Contact our support team through the <strong style='color:#e8e8f0;'>KTS Markets App</strong> → Support section.</p>
      </div>
    </div>

    <!-- Footer -->
    <div style='text-align:center;margin-top:24px;'>
      <p style='color:#374151;font-size:12px;margin:0;'>KTS Markets © {$year} &nbsp;|&nbsp; This email was sent by an admin. Do not reply.</p>
    </div>

  </div>
</body>
</html>";
    }

    private function step(string $num, string $text): string
    {
        return "<div style='display:flex;gap:12px;margin-bottom:12px;align-items:flex-start;'>
            <div style='width:26px;height:26px;background:#f5a623;border-radius:50%;color:#000;font-weight:700;font-size:12px;text-align:center;line-height:26px;flex-shrink:0;'>{$num}</div>
            <div style='color:#94a3b8;font-size:13px;padding-top:4px;'>{$text}</div>
          </div>";
    }
}
