<?php

namespace App\Mail;

use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CompanyLockStatusChanged extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Company $company,
        public string $status
    ) {
    }

    public function envelope(): Envelope
    {
        $statusText = $this->status === 'locked' ? 'Locked' : 'Unlocked';

        return new Envelope(
            subject: "Company {$statusText} - " . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.company-lock-status-changed',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
