<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

final class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Crear usuario admin solo si no existe
        User::firstOrCreate(
            ['email' => 'admin@agcassessors.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('Admin*123'),
                'email_verified_at' => now(),
            ]
        );
    }
}
