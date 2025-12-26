<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class UserAccountCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $password;
    public $loginUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $password)
    {
        $this->user = $user;
        $this->password = $password;
        $this->loginUrl = route('admin.login');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Account Has Been Created - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.user-account-created',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
    
    /**
     * Increment email notification count for tracking
     */
    public static function incrementEmailCount(): void
    {
        $monthKey = 'email_notification_count_' . now()->format('Y-m');
        $count = Cache::get($monthKey, 0);
        $expiresAt = now()->endOfMonth();
        Cache::put($monthKey, $count + 1, $expiresAt);
    }
    
    /**
     * Get current month's email notification count
     */
    public static function getEmailCount(): int
    {
        $monthKey = 'email_notification_count_' . now()->format('Y-m');
        return Cache::get($monthKey, 0);
    }
}
