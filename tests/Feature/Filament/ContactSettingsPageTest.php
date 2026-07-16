<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use AGC\Filament\Pages\ContactSettingsPage;
use AGC\Infrastructure\Persistence\Eloquent\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ContactSettingsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_settings_page_saves_valid_destination_emails(): void
    {
        Livewire::test(ContactSettingsPage::class)
            ->fillForm([
                'title' => [
                    'ca' => 'Contacte',
                    'es' => 'Contacto',
                    'en' => 'Contact',
                ],
                'contact_destination_email' => 'info@example.com, office@example.com',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $settings = SiteSetting::get('contact_settings');

        $this->assertIsArray($settings);
        $this->assertSame(
            'info@example.com, office@example.com',
            $settings['contact_destination_email'] ?? null,
        );
    }
}
