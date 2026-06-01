<?php

declare(strict_types=1);

namespace App\Mail;

use AGC\Infrastructure\Persistence\Eloquent\Models\JobApplication;
use AGC\Infrastructure\Persistence\Eloquent\Models\SiteSetting;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Facades\Storage;

final class JobApplicationMail extends Mailable
{

    /** @param array<string, mixed> $settings */
    public function __construct(
        public readonly JobApplication $application,
        private readonly array $settings = [],
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nueva candidatura: ' . $this->application->name . ' ' . $this->application->last_name
                . ' — ' . $this->application->department,
            to: [$this->resolveDestinationEmail()],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.job-application',
            with: ['application' => $this->application],
        );
    }

    /** @return list<Attachment> */
    public function attachments(): array
    {
        if ($this->application->cv_path === null) {
            return [];
        }

        if (!Storage::disk('private')->exists($this->application->cv_path)) {
            return [];
        }

        return [
            Attachment::fromStorageDisk('private', $this->application->cv_path)
                ->as('CV_' . $this->application->name . '_' . $this->application->last_name . '.' . pathinfo($this->application->cv_path, PATHINFO_EXTENSION))
                ->withMime($this->guessMime()),
        ];
    }

    private function resolveDestinationEmail(): string
    {
        $settings = $this->settings ?: (SiteSetting::get('careers_page', []) ?? []);
        $email    = $settings['form_destination_email'] ?? null;

        if (is_string($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $email;
        }

        return (string) config('mail.from.address', 'info@agcassessors.com');
    }

    private function guessMime(): string
    {
        $ext = strtolower(pathinfo((string) $this->application->cv_path, PATHINFO_EXTENSION));

        return match ($ext) {
            'pdf'  => 'application/pdf',
            'doc'  => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            default => 'application/octet-stream',
        };
    }
}
