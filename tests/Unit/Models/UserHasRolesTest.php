<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\User;
use PHPUnit\Framework\TestCase;
use Spatie\Permission\Traits\HasRoles;

final class UserHasRolesTest extends TestCase
{
    public function test_user_model_uses_has_roles_trait(): void
    {
        $this->assertContains(
            HasRoles::class,
            class_uses_recursive(User::class),
            'User model must use the HasRoles trait from spatie/laravel-permission',
        );
    }

    public function test_user_instance_has_has_role_method(): void
    {
        $user = new User;

        $this->assertTrue(
            method_exists($user, 'hasRole'),
            'User must expose hasRole() via HasRoles trait',
        );
    }
}
