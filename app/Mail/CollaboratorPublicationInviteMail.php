<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CollaboratorPublicationInviteMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public string $accessUrl) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Research Collaboration Progress',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.collaborator-publications-invite',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
