<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use AGC\Infrastructure\Persistence\Eloquent\Models\JobApplication;
use App\Http\Middleware\SpamProtection;
use App\Mail\JobApplicationMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class WorkWithUsControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_localized_career_pages_render_the_form_action_for_the_current_locale(): void
    {
        foreach ([
            '/work-with-us' => 'ca',
            '/es/work-with-us' => 'es',
            '/en/work-with-us' => 'en',
        ] as $path => $locale) {
            $response = $this->get($path, ['Accept-Language' => $locale]);

            $response->assertOk()->assertSee('action="'.$path.'"', false);

            if ($locale !== 'en') {
                $response->assertDontSee('action="/en/work-with-us"', false);
            }
        }
    }

    /** @test */
    public function test_application_redirects_to_the_current_localized_url(): void
    {
        Mail::fake();
        Storage::fake('private');
        $this->withoutMiddleware([SpamProtection::class, ThrottleRequests::class]);

        foreach ([
            '/work-with-us' => 'ca',
            '/es/work-with-us' => 'es',
            '/en/work-with-us' => 'en',
        ] as $path => $locale) {
            $this->post($path, $this->validPayload(), ['Accept-Language' => $locale])
                ->assertRedirect($path);
        }
    }

    /** @test */
    public function test_valid_application_is_persisted_and_sent_with_the_cv_attachment(): void
    {
        Mail::fake();
        Storage::fake('private');
        $this->withoutMiddleware([SpamProtection::class, ThrottleRequests::class]);

        $response = $this->post('/es/work-with-us', $this->validPayload([
            'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
        ]), ['Accept-Language' => 'es']);

        $response->assertRedirect('/es/work-with-us');

        $application = JobApplication::query()->firstOrFail();

        $this->assertSame('Ada', $application->name);
        $this->assertNotNull($application->cv_path);
        Storage::disk('private')->assertExists($application->cv_path);

        Mail::assertSent(JobApplicationMail::class, function (JobApplicationMail $mail) use ($application): bool {
            $attachments = $mail->attachments();

            return $mail->application->is($application)
                && count($attachments) === 1
                && $attachments[0]->as === 'CV_Ada_Lovelace.pdf'
                && $attachments[0]->mime === 'application/pdf';
        });
    }

    /** @test */
    public function test_invalid_cv_is_rejected_without_persisting_the_application(): void
    {
        Mail::fake();
        Storage::fake('private');
        $this->withoutMiddleware([SpamProtection::class, ThrottleRequests::class]);

        $response = $this->post('/work-with-us', $this->validPayload([
            'cv' => UploadedFile::fake()->create('cv.txt', 100, 'text/plain'),
        ]), ['Accept-Language' => 'ca']);

        $response->assertSessionHasErrors('cv');
        $this->assertDatabaseCount('job_applications', 0);
        Mail::assertNothingSent();
    }

    /** @test */
    public function test_mail_failure_keeps_the_persisted_application_and_shows_a_warning(): void
    {
        Storage::fake('private');
        Log::spy();
        Mail::partialMock()
            ->shouldReceive('send')
            ->once()
            ->andThrow(new \RuntimeException('transport unavailable'));
        $this->withoutMiddleware([SpamProtection::class, ThrottleRequests::class]);

        $response = $this->post('/en/work-with-us', $this->validPayload(), [
            'Accept-Language' => 'en',
        ]);

        $response->assertRedirect('/en/work-with-us')
            ->assertSessionHas('success')
            ->assertSessionHas('warning');

        $this->assertDatabaseCount('job_applications', 1);
        Log::shouldHaveReceived('error')->atLeast()->once();
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_replace([
            'name' => 'Ada',
            'last_name' => 'Lovelace',
            'email' => 'ada@example.com',
            'phone' => '+34 600 000 000',
            'department' => 'fiscal',
            'message' => 'I would like to join the team.',
            'privacy_accepted' => '1',
        ], $overrides);
    }
}
