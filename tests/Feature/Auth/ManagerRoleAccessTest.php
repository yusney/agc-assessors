<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verify the manager role's access matrix.
 *
 * Spec: manager has full CRUD on all 5 content resources (News, Page, Service,
 * TeamMember, Office) but MUST NOT access structural resources (HomeSection,
 * MenuItem) or the Shield Roles resource.
 */
final class ManagerRoleAccessTest extends TestCase
{
    use RefreshDatabase;
    use RoleTestSetupTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRolesAndPermissions();
    }

    // ──────────────────────────────────────────────────────────
    // Content resources — full CRUD allowed
    // ──────────────────────────────────────────────────────────

    public function test_manager_can_view_any_content_resources(): void
    {
        $manager = $this->userWithRole('manager');

        foreach (['NewsModel', 'PageModel', 'ServiceModel', 'TeamMemberModel', 'EloquentOffice'] as $resource) {
            $this->assertTrue(
                $manager->can("ViewAny:{$resource}"),
                "manager must ViewAny:{$resource}"
            );
        }
    }

    public function test_manager_can_create_content_resources(): void
    {
        $manager = $this->userWithRole('manager');

        foreach (['NewsModel', 'PageModel', 'ServiceModel', 'TeamMemberModel', 'EloquentOffice'] as $resource) {
            $this->assertTrue(
                $manager->can("Create:{$resource}"),
                "manager must Create:{$resource}"
            );
        }
    }

    public function test_manager_can_delete_content_resources(): void
    {
        $manager = $this->userWithRole('manager');

        foreach (['NewsModel', 'PageModel', 'ServiceModel', 'TeamMemberModel', 'EloquentOffice'] as $resource) {
            $this->assertTrue(
                $manager->can("Delete:{$resource}"),
                "manager must Delete:{$resource}"
            );
        }
    }

    // ──────────────────────────────────────────────────────────
    // Structural resources — must be blocked
    // ──────────────────────────────────────────────────────────

    public function test_manager_cannot_view_home_section(): void
    {
        $manager = $this->userWithRole('manager');

        $this->assertFalse($manager->can('ViewAny:HomeSection'));
        $this->assertFalse($manager->can('Create:HomeSection'));
    }

    public function test_manager_cannot_view_menu_item(): void
    {
        $manager = $this->userWithRole('manager');

        $this->assertFalse($manager->can('ViewAny:MenuItem'));
        $this->assertFalse($manager->can('Create:MenuItem'));
    }

    // ──────────────────────────────────────────────────────────
    // Role CRUD — must be blocked
    // ──────────────────────────────────────────────────────────

    public function test_manager_cannot_manage_roles(): void
    {
        $manager = $this->userWithRole('manager');

        $this->assertFalse($manager->can('ViewAny:Role'));
        $this->assertFalse($manager->can('Create:Role'));
        $this->assertFalse($manager->can('Delete:Role'));
    }
}
