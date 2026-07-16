<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class ServicesControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_services_index_preserves_spanish_locale_in_service_links(): void
    {
        $slug = 'servei-idioma';
        $this->createService($slug);

        $response = $this->get('/es/serveis', ['Accept-Language' => 'es']);

        $response->assertOk()
            ->assertSee('href="/es/serveis/'.$slug.'"', false)
            ->assertDontSee('href="/en/serveis/'.$slug.'"', false);
    }

    public function test_service_detail_preserves_spanish_locale_in_breadcrumb_and_contact_cta(): void
    {
        $slug = 'servei-idioma-detall';
        $this->createService($slug);

        $response = $this->get('/es/serveis/'.$slug, ['Accept-Language' => 'es']);

        $response->assertOk()
            ->assertSee('href="/es/serveis"', false)
            ->assertSee('href="/es/contacte"', false)
            ->assertDontSee('href="/en/serveis"', false)
            ->assertDontSee('href="/en/contacte"', false);
    }

    private function createService(string $slug): void
    {
        DB::table('services')->insert([
            'slug' => $slug,
            'name' => json_encode([
                'ca' => 'Servei de prova',
                'es' => 'Servicio de prueba',
                'en' => 'Test service',
            ]),
            'description' => json_encode([
                'ca' => '<p>Descripció de prova.</p>',
                'es' => '<p>Descripción de prueba.</p>',
                'en' => '<p>Test description.</p>',
            ]),
            'seo_title' => json_encode([]),
            'seo_description' => json_encode([]),
            'active' => 1,
            'sort_order' => 1,
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]);
    }
}
