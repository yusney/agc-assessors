<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use AGC\Infrastructure\Persistence\Eloquent\Models\EloquentOffice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class OfficesControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<string, string> */
    private array $headers = ['Accept-Language' => 'ca'];

    public function test_offices_index_returns_200(): void
    {
        $this->get('/oficines', $this->headers)->assertOk();
    }

    public function test_offices_index_passes_offices_to_view(): void
    {
        EloquentOffice::create([
            'name' => ['ca' => 'Oficina A', 'es' => 'Oficina A', 'en' => 'Office A'],
            'address' => ['ca' => 'Carrer A 1', 'es' => 'Calle A 1', 'en' => 'A Street 1'],
            'city' => ['ca' => 'Barcelona', 'es' => 'Barcelona', 'en' => 'Barcelona'],
            'is_active' => true,
        ]);
        EloquentOffice::create([
            'name' => ['ca' => 'Oficina B', 'es' => 'Oficina B', 'en' => 'Office B'],
            'address' => ['ca' => 'Carrer B 2', 'es' => 'Calle B 2', 'en' => 'B Street 2'],
            'city' => ['ca' => 'Girona', 'es' => 'Gerona', 'en' => 'Girona'],
            'is_active' => true,
        ]);

        $response = $this->get('/oficines', $this->headers);

        $response->assertOk();
        $response->assertViewHas('offices');
        $this->assertCount(2, $response->viewData('offices'));
    }

    public function test_inactive_offices_not_shown(): void
    {
        EloquentOffice::create([
            'name' => ['ca' => 'Activa', 'es' => 'Activa', 'en' => 'Active'],
            'address' => ['ca' => 'Carrer X', 'es' => 'Calle X', 'en' => 'X Street'],
            'city' => ['ca' => 'Barcelona', 'es' => 'Barcelona', 'en' => 'Barcelona'],
            'is_active' => true,
        ]);
        EloquentOffice::create([
            'name' => ['ca' => 'Inactiva', 'es' => 'Inactiva', 'en' => 'Inactive'],
            'address' => ['ca' => 'Carrer Y', 'es' => 'Calle Y', 'en' => 'Y Street'],
            'city' => ['ca' => 'Tarragona', 'es' => 'Tarragona', 'en' => 'Tarragona'],
            'is_active' => false,
        ]);

        $response = $this->get('/oficines', $this->headers);

        $response->assertOk();
        $this->assertCount(1, $response->viewData('offices'));
    }

    public function test_view_receives_offices_geo_json(): void
    {
        EloquentOffice::create([
            'name' => ['ca' => 'Geo Oficina'],
            'address' => ['ca' => 'Carrer Geo 1'],
            'city' => ['ca' => 'Barcelona'],
            'lat' => 41.3879,
            'lng' => 2.16992,
            'is_active' => true,
        ]);

        $response = $this->get('/oficines', $this->headers);

        $response->assertOk();
        $response->assertViewHas('officesGeoJson');

        $geoJson = $response->viewData('officesGeoJson');
        $this->assertCount(1, $geoJson);
        $this->assertArrayHasKey('lat', $geoJson[0]);
        $this->assertArrayHasKey('lng', $geoJson[0]);
    }
}
