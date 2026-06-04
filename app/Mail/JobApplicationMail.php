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
            subject: 'Nueva candidatura: '.$this->application->name.' '.$this->application->last_name
                .' — '.$this->application->department,
            to: $this->resolveDestinationEmails(),
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

        if (! Storage::disk('private')->exists($this->application->cv_path)) {
            return [];
        }

        return [
            Attachment::fromStorageDisk('private', $this->application->cv_path)
                ->as('CV_'.$this->application->name.'_'.$this->application->last_name.'.'.pathinfo($this->application->cv_path, PATHINFO_EXTENSION))
                ->withMime($this->guessMime()),
        ];
    }

    /** @return list<string> */
    private function resolveDestinationEmails(): array
    {
        $settings = $this->settings ?: (SiteSetting::get('careers_page', []) ?? []);
        $raw = $settings['form_destination_email'] ?? null;

        $destinations = collect(explode(',', (string) $raw))
            ->map(fn (string $e) => trim($e))
            ->filter(fn (string $e) => filter_var($e, FILTER_VALIDATE_EMAIL))
            ->values()
            ->all();

        if ($destinations === []) {
            return [(string) config('mail.from.address', 'info@agcassessors.com')];
        }

        return $destinations;
    }

    private function guessMime(): string
    {
        $ext = strtolower(pathinfo((string) $this->application->cv_path, PATHINFO_EXTENSION));

        return match ($ext) {
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            default => 'application/octet-stream',
        };
    }
}
