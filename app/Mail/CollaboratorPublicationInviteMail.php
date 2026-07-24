<?php

namespace App\Mail;

use App\Models\Publication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CollaboratorPublicationInviteMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Publication $publication,
        public string $accessUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Access your co-authored publication list',
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
