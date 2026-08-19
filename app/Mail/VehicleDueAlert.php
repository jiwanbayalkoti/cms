<?php

namespace App\Mail;

use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class VehicleDueAlert extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Company $company, public Collection $alerts)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Vehicle service & document alerts - ' . $this->company->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.vehicle-due-alert',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
