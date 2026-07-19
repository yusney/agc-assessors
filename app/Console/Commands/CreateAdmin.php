<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

final class CreateAdmin extends Command
{
    protected $signature = 'app:create-admin
                            {email : Email address for the administrator}
                            {--name= : Display name for the administrator}';

    protected $description = 'Create a verified administrator with the super_admin role';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->argument('email')));

        $emailValidator = Validator::make(
            ['email' => $email],
            ['email' => ['required', 'email', 'max:255']],
        );

        if ($emailValidator->fails()) {
            foreach ($emailValidator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        if (User::query()->whereRaw('LOWER(email) = ?', [$email])->exists()) {
            $this->error('A user with this email already exists. No changes were made.');

            return self::FAILURE;
        }

        $superAdminRole = Role::query()
            ->where('name', 'super_admin')
            ->where('guard_name', 'web')
            ->first();

        if ($superAdminRole === null) {
            $this->error(
                'The super_admin role does not exist. Run RolesAndPermissionsSeeder first.',
            );

            return self::FAILURE;
        }

        $nameOption = $this->option('name');
        $name = $nameOption === null
            ? explode('@', $email, 2)[0]
            : trim((string) $nameOption);

        $nameValidator = Validator::make(
            ['name' => $name],
            ['name' => ['required', 'string', 'max:255']],
        );

        if ($nameValidator->fails()) {
            foreach ($nameValidator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $password = (string) $this->secret('Password');
        $passwordConfirmation = (string) $this->secret('Confirm password');

        if (mb_strlen($password) < 16) {
            $this->error('Password must be at least 16 characters long.');

            return self::FAILURE;
        }

        if (! hash_equals($password, $passwordConfirmation)) {
            $this->error('Password confirmation does not match.');

            return self::FAILURE;
        }

        try {
            $user = DB::transaction(function () use ($email, $name, $password, $superAdminRole): User {
                $user = User::query()->create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'email_verified_at' => now(),
                ]);

                $user->assignRole($superAdminRole);

                return $user;
            });
        } catch (UniqueConstraintViolationException) {
            $this->error('A user with this email already exists. No changes were made.');

            return self::FAILURE;
        }

        $this->info("Admin user {$user->email} created with the super_admin role.");

        return self::SUCCESS;
    }
}
