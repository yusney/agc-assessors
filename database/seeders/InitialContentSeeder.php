<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * One-time content seed for initial production deploy.
 *
 * DO NOT run this on every container boot — it would overwrite
 * manual edits made via the Filament admin panel.
 *
 * Run manually once after the first deploy:
 *   docker compose exec php php artisan db:seed --class=InitialContentSeeder
 */
final class InitialContentSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            HomeSectionSeeder::class,
            ServiceSeeder::class,
            NewsSeeder::class,
            TeamMemberSeeder::class,
            MenuItemSeeder::class,
            FooterSettingsSeeder::class,
        ]);
    }
}
