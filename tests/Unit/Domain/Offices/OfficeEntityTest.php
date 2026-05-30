<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Offices;

use AGC\Domain\Offices\Entities\Office;
use AGC\Domain\Shared\ValueObjects\TranslatableString;
use PHPUnit\Framework\TestCase;

final class OfficeEntityTest extends TestCase
{
    public function test_constructs_with_all_fields(): void
    {
        $office = $this->makeOffice();

        $this->assertSame(1, $office->id());
        $this->assertSame('Oficina Central', $office->name()->get('ca'));
        $this->assertSame('Carrer Major 1', $office->address()->get('ca'));
        $this->assertSame('Barcelona', $office->city()->get('ca'));
        $this->assertSame('+34 93 000 00 00', $office->phone());
        $this->assertSame('info@test.com', $office->email());
        $this->assertSame(41.3879, $office->lat());
        $this->assertSame(2.16992, $office->lng());
        $this->assertTrue($office->isActive());
    }

    public function test_getters_return_correct_translatable_values(): void
    {
        $office = $this->makeOffice(name: [
            'ca' => 'Oficina',
            'es' => 'Oficina ES',
            'en' => 'Office EN',
        ]);

        $this->assertSame('Oficina', $office->name()->get('ca'));
        $this->assertSame('Oficina ES', $office->name()->get('es'));
        $this->assertSame('Office EN', $office->name()->get('en'));
    }

    public function test_nullable_phone_email_lat_lng(): void
    {
        $office = $this->makeOffice(phone: null, email: null, lat: null, lng: null);

        $this->assertNull($office->phone());
        $this->assertNull($office->email());
        $this->assertNull($office->lat());
        $this->assertNull($office->lng());
    }

    public function test_is_inactive(): void
    {
        $office = $this->makeOffice(isActive: false);

        $this->assertFalse($office->isActive());
    }

    public function test_name_address_city_return_translatable_string_instances(): void
    {
        $office = $this->makeOffice();

        $this->assertInstanceOf(TranslatableString::class, $office->name());
        $this->assertInstanceOf(TranslatableString::class, $office->address());
        $this->assertInstanceOf(TranslatableString::class, $office->city());
    }

    public function test_translatable_string_fallback_chain(): void
    {
        $office = $this->makeOffice(name: ['es' => 'Solo Español']);

        // ca is empty, fallback to es
        $this->assertSame('Solo Español', $office->name()->get('ca'));
    }

    private function makeOffice(
        int $id = 1,
        array $name = ['ca' => 'Oficina Central', 'es' => 'Oficina Central ES', 'en' => 'Central Office'],
        array $address = ['ca' => 'Carrer Major 1', 'es' => 'Calle Mayor 1', 'en' => 'High Street 1'],
        array $city = ['ca' => 'Barcelona', 'es' => 'Barcelona', 'en' => 'Barcelona'],
        ?string $phone = '+34 93 000 00 00',
        ?string $email = 'info@test.com',
        ?float $lat = 41.3879,
        ?float $lng = 2.16992,
        bool $isActive = true,
    ): Office {
        return new Office(
            id: $id,
            name: new TranslatableString($name),
            address: new TranslatableString($address),
            city: new TranslatableString($city),
            phone: $phone,
            email: $email,
            lat: $lat,
            lng: $lng,
            isActive: $isActive,
        );
    }
}
