<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            HomeSectionSeeder::class,
            ServiceSeeder::class,
            NewsSeeder::class,
            TeamMemberSeeder::class,
            MenuItemSeeder::class,
            FooterSettingsSeeder::class,
        ]);
    }
}
