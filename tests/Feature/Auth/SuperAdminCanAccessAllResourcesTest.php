<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

/**
 * Verify that the `super_admin` role bypasses all Gate / policy checks via the
 * `Gate::before` hook registered in AppServiceProvider.
 *
 * Strategy: create a super_admin user WITHOUT running the seeder (so they carry
 * zero Spatie permissions). They should still pass every ability check because
 * `Gate::before` fires first and returns `true`.
 */
final class SuperAdminCanAccessAllResourcesTest extends TestCase
{
    use RefreshDatabase;
    use RoleTestSetupTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRolesAndPermissions();
    }

    // ──────────────────────────────────────────────────────────
    // Gate::before bypass (no seeder permissions needed)
    // ──────────────────────────────────────────────────────────

    public function test_super_admin_bypasses_gate_for_any_ability(): void
    {
        $admin = $this->userWithRole('super_admin');

        // Intentionally test with an ability that exists in no permission record.
        $this->assertTrue(
            Gate::forUser($admin)->allows('edit-settings'),
            'Gate::before must return true for super_admin on any ability.'
        );
    }

    // ──────────────────────────────────────────────────────────
    // Spatie permission checks (super_admin has all 108 perms)
    // ──────────────────────────────────────────────────────────

    public function test_super_admin_can_view_any_news(): void
    {
        $admin = $this->userWithRole('super_admin');

        $this->assertTrue($admin->can('ViewAny:NewsModel'));
    }

    public function test_super_admin_can_delete_home_section(): void
    {
        $admin = $this->userWithRole('super_admin');

        $this->assertTrue($admin->can('Delete:HomeSection'));
    }

    public function test_super_admin_can_manage_roles(): void
    {
        $admin = $this->userWithRole('super_admin');

        $this->assertTrue($admin->can('ViewAny:Role'));
        $this->assertTrue($admin->can('Create:Role'));
        $this->assertTrue($admin->can('Delete:Role'));
    }

    public function test_super_admin_can_access_all_content_resources(): void
    {
        $admin = $this->userWithRole('super_admin');

        $contentResources = ['NewsModel', 'PageModel', 'ServiceModel', 'TeamMemberModel', 'EloquentOffice'];

        foreach ($contentResources as $resource) {
            $this->assertTrue(
                $admin->can("ViewAny:{$resource}"),
                "super_admin must be able to ViewAny:{$resource}"
            );
            $this->assertTrue(
                $admin->can("Create:{$resource}"),
                "super_admin must be able to Create:{$resource}"
            );
            $this->assertTrue(
                $admin->can("Delete:{$resource}"),
                "super_admin must be able to Delete:{$resource}"
            );
        }
    }

    public function test_super_admin_can_access_structural_resources(): void
    {
        $admin = $this->userWithRole('super_admin');

        foreach (['HomeSection', 'MenuItem'] as $resource) {
            $this->assertTrue(
                $admin->can("ViewAny:{$resource}"),
                "super_admin must be able to ViewAny:{$resource}"
            );
            $this->assertTrue(
                $admin->can("Delete:{$resource}"),
                "super_admin must be able to Delete:{$resource}"
            );
        }
    }
}
