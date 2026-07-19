<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    /**
     * Keep the default entrypoint explicit and side-effect free.
     *
     * Authentication and permission setup must be invoked manually in the
     * documented order. In particular, this seeder never creates or changes
     * users or roles.
     */
    public function run(): void
    {
        $this->command?->info(
            'No default seeders run. Invoke RolesAndPermissionsSeeder and app:create-admin explicitly.',
        );
    }
}
