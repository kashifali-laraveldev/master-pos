<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TenantWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $ownerName,
        public string $businessName,
        public string $email,
        public string $loginUrl,
        public string $apiDocsUrl
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to MasterPOS - ' . $this->businessName,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.tenants.welcome',
            with: [
                'ownerName' => $this->ownerName,
                'businessName' => $this->businessName,
                'email' => $this->email,
                'loginUrl' => $this->loginUrl,
                'apiDocsUrl' => $this->apiDocsUrl,
            ],
        );
    }
}
