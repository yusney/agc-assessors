<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Offices;

use AGC\Domain\Offices\Entities\Office;
use AGC\Domain\Shared\ValueObjects\TranslatableString;
use AGC\Infrastructure\Persistence\Eloquent\Models\EloquentOffice;
use AGC\Infrastructure\Persistence\Eloquent\Repositories\EloquentOfficeRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class EloquentOfficeRepositoryMapperTest extends TestCase
{
    use RefreshDatabase;

    public function test_maps_eloquent_office_to_domain_entity(): void
    {
        EloquentOffice::create([
            'name'      => ['ca' => 'Oficina Test', 'es' => 'Oficina Test ES', 'en' => 'Test Office'],
            'address'   => ['ca' => 'Carrer Test 1', 'es' => 'Calle Test 1', 'en' => 'Test Street 1'],
            'city'      => ['ca' => 'Barcelona', 'es' => 'Barcelona', 'en' => 'Barcelona'],
            'phone'     => '+34 93 000 00 01',
            'email'     => 'test@test.com',
            'lat'       => 41.3879,
            'lng'       => 2.16992,
            'is_active' => true,
        ]);

        $repository = new EloquentOfficeRepository();
        $offices    = $repository->findAllActive();

        $this->assertCount(1, $offices);
        $this->assertInstanceOf(Office::class, $offices[0]);

        $office = $offices[0];
        $this->assertSame('Oficina Test', $office->name()->get('ca'));
        $this->assertSame('Oficina Test ES', $office->name()->get('es'));
        $this->assertSame('Test Office', $office->name()->get('en'));
        $this->assertSame('Carrer Test 1', $office->address()->get('ca'));
        $this->assertSame('Barcelona', $office->city()->get('ca'));
        $this->assertSame('+34 93 000 00 01', $office->phone());
        $this->assertSame('test@test.com', $office->email());
        $this->assertEqualsWithDelta(41.3879, $office->lat(), 0.0001);
        $this->assertEqualsWithDelta(2.16992, $office->lng(), 0.0001);
        $this->assertTrue($office->isActive());
    }

    public function test_maps_with_null_optional_fields(): void
    {
        EloquentOffice::create([
            'name'      => ['ca' => 'Oficina Nul·la', 'es' => 'Oficina Nula', 'en' => 'Null Office'],
            'address'   => ['ca' => 'Carrer Sense Num', 'es' => 'Calle Sin Num', 'en' => 'No Number Street'],
            'city'      => ['ca' => 'Girona', 'es' => 'Gerona', 'en' => 'Girona'],
            'phone'     => null,
            'email'     => null,
            'lat'       => null,
            'lng'       => null,
            'is_active' => true,
        ]);

        $repository = new EloquentOfficeRepository();
        $offices    = $repository->findAllActive();

        $this->assertCount(1, $offices);
        $office = $offices[0];
        $this->assertNull($office->phone());
        $this->assertNull($office->email());
        $this->assertNull($office->lat());
        $this->assertNull($office->lng());
    }

    public function test_translatable_string_type_returned(): void
    {
        EloquentOffice::create([
            'name'      => ['ca' => 'TS Test'],
            'address'   => ['ca' => 'Addr'],
            'city'      => ['ca' => 'City'],
            'is_active' => true,
        ]);

        $repository = new EloquentOfficeRepository();
        $offices    = $repository->findAllActive();

        $this->assertInstanceOf(TranslatableString::class, $offices[0]->name());
        $this->assertInstanceOf(TranslatableString::class, $offices[0]->address());
        $this->assertInstanceOf(TranslatableString::class, $offices[0]->city());
    }

    public function test_inactive_offices_excluded_from_find_all_active(): void
    {
        EloquentOffice::create([
            'name'      => ['ca' => 'Activa'],
            'address'   => ['ca' => 'Addr Active'],
            'city'      => ['ca' => 'Barcelona'],
            'is_active' => true,
        ]);
        EloquentOffice::create([
            'name'      => ['ca' => 'Inactiva'],
            'address'   => ['ca' => 'Addr Inactive'],
            'city'      => ['ca' => 'Girona'],
            'is_active' => false,
        ]);

        $repository = new EloquentOfficeRepository();
        $offices    = $repository->findAllActive();

        $this->assertCount(1, $offices);
        $this->assertSame('Activa', $offices[0]->name()->get('ca'));
    }
}
