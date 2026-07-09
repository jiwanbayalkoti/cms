<?php

namespace App\Mail;

use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CompanyPaymentRequired extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Company $company)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment Required - ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.company-payment-required',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
