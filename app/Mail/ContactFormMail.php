<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

final class ContactFormMail extends Mailable
{
    /** @param array<string, mixed> $data */
    public function __construct(
        public readonly array $data,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[AGC Web] ' . $this->data['subject'],
            replyTo: [$this->data['email']],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-form',
            with: ['data' => $this->data],
        );
    }
}
