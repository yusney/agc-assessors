<?php

namespace Database\Seeders;

use AGC\Infrastructure\Persistence\Eloquent\Models\EloquentOffice;
use Illuminate\Database\Seeder;

class OfficeSeeder extends Seeder
{
    public function run(): void
    {
        $offices = [
            [
                'name' => ['ca' => 'Caldes de Montbui', 'es' => 'Caldes de Montbui', 'en' => 'Caldes de Montbui'],
                'address' => ['ca' => 'Av. Pi i Margall, 114', 'es' => 'Av. Pi i Margall, 114', 'en' => 'Av. Pi i Margall, 114'],
                'city' => ['ca' => 'Caldes de Montbui', 'es' => 'Caldes de Montbui', 'en' => 'Caldes de Montbui'],
                'phone' => '+34 93 862 61 00',
                'email' => 'agcassessors@agc.cat',
                'lat' => 41.6277038,
                'lng' => 2.1674534,
                'is_active' => true,
            ],
            [
                'name' => ['ca' => 'Sant Celoni', 'es' => 'Sant Celoni', 'en' => 'Sant Celoni'],
                'address' => ['ca' => 'Crtra. Vella, 66, 1r', 'es' => 'Crtra. Vella, 66, 1º', 'en' => 'Crtra. Vella, 66, 1st floor'],
                'city' => ['ca' => 'Sant Celoni', 'es' => 'Sant Celoni', 'en' => 'Sant Celoni'],
                'phone' => '+34 93 867 05 12',
                'email' => 'info@agc.cat',
                'lat' => 41.6880663,
                'lng' => 2.4913484,
                'is_active' => true,
            ],
            [
                'name' => ['ca' => 'Mollet del Vallès', 'es' => 'Mollet del Vallès', 'en' => 'Mollet del Vallès'],
                'address' => ['ca' => 'C/ St. Roc, 35, 2n-8a', 'es' => 'C/ St. Roc, 35, 2º-8ª', 'en' => 'St. Roc St., 35, 2nd floor'],
                'city' => ['ca' => 'Mollet del Vallès', 'es' => 'Mollet del Vallès', 'en' => 'Mollet del Vallès'],
                'phone' => '+34 93 570 03 37',
                'email' => 'info@agc.cat',
                'lat' => 41.5447492,
                'lng' => 2.2192054,
                'is_active' => true,
            ],
            [
                'name' => ['ca' => 'Granollers', 'es' => 'Granollers', 'en' => 'Granollers'],
                'address' => ['ca' => 'C/ Josep Umbert i Ventura, 43 Bx', 'es' => 'C/ Josep Umbert i Ventura, 43 Bx', 'en' => 'Josep Umbert i Ventura St., 43'],
                'city' => ['ca' => 'Granollers', 'es' => 'Granollers', 'en' => 'Granollers'],
                'phone' => '+34 93 860 36 20',
                'email' => 'info@agc.cat',
                'lat' => 41.6079,
                'lng' => 2.2876,
                'is_active' => true,
            ],
            [
                'name' => ['ca' => 'Prats de Lluçanès', 'es' => 'Prats de Lluçanès', 'en' => 'Prats de Lluçanès'],
                'address' => ['ca' => 'Plaça Nova, 20', 'es' => 'Plaça Nova, 20', 'en' => 'Plaça Nova, 20'],
                'city' => ['ca' => 'Prats de Lluçanès', 'es' => 'Prats de Lluçanès', 'en' => 'Prats de Lluçanès'],
                'phone' => '+34 93 856 06 57',
                'email' => 'info@agc.cat',
                'lat' => 42.0029,
                'lng' => 2.0156,
                'is_active' => true,
            ],
            [
                'name' => ['ca' => 'Manlleu', 'es' => 'Manlleu', 'en' => 'Manlleu'],
                'address' => ['ca' => 'Plaça de Dalt de la Vila, 5', 'es' => 'Plaça de Dalt de la Vila, 5', 'en' => 'Plaça de Dalt de la Vila, 5'],
                'city' => ['ca' => 'Manlleu', 'es' => 'Manlleu', 'en' => 'Manlleu'],
                'phone' => null,
                'email' => 'info@agc.cat',
                'lat' => 42.0013284,
                'lng' => 2.2844067,
                'is_active' => true,
            ],
        ];

        foreach ($offices as $data) {
            EloquentOffice::create($data);
        }
    }
}
