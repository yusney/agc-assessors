<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
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
            from: new Address(
                config('mail.from.address', 'info@agcassessors.com'),
                'AGC Assessors'
            ),
            subject: 'Consulta web: ' . $this->data['subject'] . ' — ' . $this->data['name'],
            replyTo: [
                new Address(
                    $this->data['email'],
                    $this->data['name']
                ),
            ],
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
