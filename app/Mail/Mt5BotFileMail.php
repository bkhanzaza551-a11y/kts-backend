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
        public ?string $adminNote = '',
    ) {
        $this->adminNote = $this->adminNote ?? '';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🤖 Your KTS Markets MT5 Bot File is Ready!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.mt5-bot-file',
            with: [
                'user' => $this->user,
                'bot' => $this->bot,
                'adminNote' => $this->adminNote,
            ],
        );
    }

    public function attachments(): array
    {
        $fileName = $this->bot->bot_file_name ?: 'KTS_Bot.ex5';

        if (!empty($this->bot->bot_file_data)) {
            return [
                Attachment::fromData(fn () => base64_decode($this->bot->bot_file_data), $fileName)
                    ->withMime('application/octet-stream'),
            ];
        }

        if ($this->bot->bot_file_path && Storage::disk('public')->exists($this->bot->bot_file_path)) {
            return [
                Attachment::fromStorageDisk('public', $this->bot->bot_file_path)
                    ->as($fileName)
                    ->withMime('application/octet-stream'),
            ];
        }
        return [];
    }
}
