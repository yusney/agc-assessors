<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class CreateAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_creates_verified_user_with_only_super_admin_role(): void
    {
        Role::create(['name' => 'super_admin', 'guard_name' => 'web']);

        $this->artisan('app:create-admin', [
            'email' => 'new-admin@example.com',
            '--name' => 'New Administrator',
        ])
            ->expectsQuestion('Password', 'a-very-long-test-password')
            ->expectsQuestion('Confirm password', 'a-very-long-test-password')
            ->assertExitCode(0);

        $user = User::query()->where('email', 'new-admin@example.com')->firstOrFail();

        $this->assertSame('New Administrator', $user->name);
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue($user->hasRole('super_admin'));
        $this->assertSame(['super_admin'], $user->roles()->pluck('name')->all());
    }

    public function test_command_refuses_to_overwrite_an_existing_user(): void
    {
        $existingUser = User::factory()->create([
            'email' => 'existing@example.com',
            'name' => 'Existing User',
        ]);

        $this->artisan('app:create-admin', [
            'email' => 'existing@example.com',
            '--name' => 'Replacement User',
        ])->assertExitCode(1);

        $this->assertDatabaseCount('users', 1);
        $this->assertSame('Existing User', $existingUser->fresh()->name);
    }

    public function test_command_handles_unique_email_constraint_without_partial_user(): void
    {
        Role::create(['name' => 'super_admin', 'guard_name' => 'web']);

        // Force the database constraint failure deterministically; this does not model concurrency.
        User::creating(static function (User $user): void {
            DB::table('users')->insert([
                'name' => 'Competing User',
                'email' => $user->email,
                'password' => 'not-used',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            User::flushEventListeners();
        });

        try {
            $this->artisan('app:create-admin', [
                'email' => 'race@example.com',
                '--name' => 'Race Administrator',
            ])
                ->expectsQuestion('Password', 'a-very-long-test-password')
                ->expectsQuestion('Confirm password', 'a-very-long-test-password')
                ->expectsOutput('A user with this email already exists. No changes were made.')
                ->assertExitCode(1);
        } finally {
            User::flushEventListeners();
        }

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseMissing('users', ['email' => 'race@example.com']);
    }

    public function test_command_fails_when_super_admin_role_does_not_exist(): void
    {
        $this->artisan('app:create-admin', [
            'email' => 'new-admin@example.com',
            '--name' => 'New Administrator',
        ])->assertExitCode(1);

        $this->assertDatabaseMissing('users', ['email' => 'new-admin@example.com']);
    }

    public function test_command_hashes_the_password(): void
    {
        Role::create(['name' => 'super_admin', 'guard_name' => 'web']);

        $password = 'another-very-long-test-password';

        $this->artisan('app:create-admin', [
            'email' => 'hashed-admin@example.com',
            '--name' => 'Hashed Administrator',
        ])
            ->expectsQuestion('Password', $password)
            ->expectsQuestion('Confirm password', $password)
            ->assertExitCode(0);

        $user = User::query()->where('email', 'hashed-admin@example.com')->firstOrFail();

        $this->assertNotSame($password, $user->password);
        $this->assertTrue(Hash::check($password, $user->password));
    }

    public function test_command_rejects_passwords_shorter_than_sixteen_characters(): void
    {
        Role::create(['name' => 'super_admin', 'guard_name' => 'web']);

        $this->artisan('app:create-admin', [
            'email' => 'short-password@example.com',
            '--name' => 'Short Password Test',
        ])
            ->expectsQuestion('Password', 'too-short')
            ->expectsQuestion('Confirm password', 'too-short')
            ->assertExitCode(1);

        $this->assertDatabaseMissing('users', ['email' => 'short-password@example.com']);
    }
}
