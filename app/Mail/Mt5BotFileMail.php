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
use Illuminate\Support\Facades\URL;

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
        $downloadUrl = null;
        if (!empty($this->user->id)) {
            $downloadUrl = URL::temporarySignedRoute(
                'public.bot.email-download',
                now()->addDays(14),
                ['user' => $this->user->id]
            );
        } elseif (!empty($this->bot->bot_file_path)) {
            $downloadUrl = url('storage/' . $this->bot->bot_file_path);
        }

        return new Content(
            view: 'emails.mt5-bot-file',
            with: [
                'user' => $this->user,
                'bot' => $this->bot,
                'adminNote' => $this->adminNote,
                'downloadUrl' => $downloadUrl,
            ],
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
}
