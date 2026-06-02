<?php

namespace Database\Seeders;

use AGC\Infrastructure\Persistence\Eloquent\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'label' => ['ca' => 'Inici', 'es' => 'Inicio', 'en' => 'Home'],
                'url_path' => '/',
                'sort_order' => 1,
            ],
            [
                'label' => ['ca' => 'Serveis', 'es' => 'Servicios', 'en' => 'Services'],
                'url_path' => '/serveis',
                'sort_order' => 2,
            ],
            [
                'label' => ['ca' => 'Actualitat', 'es' => 'Actualidad', 'en' => 'News'],
                'url_path' => '/actualitat',
                'sort_order' => 3,
            ],
            [
                'label' => ['ca' => 'Equip', 'es' => 'Equipo', 'en' => 'Team'],
                'url_path' => '/equip',
                'sort_order' => 4,
            ],
            [
                'label' => ['ca' => 'Contacte', 'es' => 'Contacto', 'en' => 'Contact'],
                'url_path' => '/contacte',
                'sort_order' => 5,
            ],
            [
                'label' => ['ca' => 'Oficines', 'es' => 'Oficinas', 'en' => 'Offices'],
                'url_path' => '/oficines',
                'sort_order' => 6,
            ],
        ];

        foreach ($items as $item) {
            MenuItem::create(array_merge($item, [
                'is_active' => true,
                'target' => '_self',
            ]));
        }
    }
}
