<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verify the viewer role's access matrix.
 *
 * Spec: viewer has ViewAny + View on ALL 7 resources (content + structural).
 * Must NOT have any write permissions (Create, Update, Delete, etc.).
 */
final class ViewerRoleAccessTest extends TestCase
{
    use RefreshDatabase;
    use RoleTestSetupTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRolesAndPermissions();
    }

    // ──────────────────────────────────────────────────────────
    // Read permissions — all 7 resources
    // ──────────────────────────────────────────────────────────

    public function test_viewer_can_read_all_content_resources(): void
    {
        $viewer = $this->userWithRole('viewer');

        foreach (['NewsModel', 'PageModel', 'ServiceModel', 'TeamMemberModel', 'EloquentOffice'] as $resource) {
            $this->assertTrue($viewer->can("ViewAny:{$resource}"), "viewer must ViewAny:{$resource}");
            $this->assertTrue($viewer->can("View:{$resource}"), "viewer must View:{$resource}");
        }
    }

    public function test_viewer_can_read_structural_resources(): void
    {
        $viewer = $this->userWithRole('viewer');

        foreach (['HomeSection', 'MenuItem'] as $resource) {
            $this->assertTrue($viewer->can("ViewAny:{$resource}"), "viewer must ViewAny:{$resource}");
            $this->assertTrue($viewer->can("View:{$resource}"), "viewer must View:{$resource}");
        }
    }

    // ──────────────────────────────────────────────────────────
    // Write permissions — must all be denied
    // ──────────────────────────────────────────────────────────

    public function test_viewer_cannot_create_any_resource(): void
    {
        $viewer = $this->userWithRole('viewer');

        $allResources = ['NewsModel', 'PageModel', 'ServiceModel', 'TeamMemberModel', 'EloquentOffice', 'HomeSection', 'MenuItem'];

        foreach ($allResources as $resource) {
            $this->assertFalse($viewer->can("Create:{$resource}"), "viewer must NOT Create:{$resource}");
        }
    }

    public function test_viewer_cannot_update_any_resource(): void
    {
        $viewer = $this->userWithRole('viewer');

        $allResources = ['NewsModel', 'PageModel', 'ServiceModel', 'TeamMemberModel', 'EloquentOffice', 'HomeSection', 'MenuItem'];

        foreach ($allResources as $resource) {
            $this->assertFalse($viewer->can("Update:{$resource}"), "viewer must NOT Update:{$resource}");
        }
    }

    public function test_viewer_cannot_delete_any_resource(): void
    {
        $viewer = $this->userWithRole('viewer');

        $allResources = ['NewsModel', 'PageModel', 'ServiceModel', 'TeamMemberModel', 'EloquentOffice', 'HomeSection', 'MenuItem'];

        foreach ($allResources as $resource) {
            $this->assertFalse($viewer->can("Delete:{$resource}"), "viewer must NOT Delete:{$resource}");
        }
    }

    // ──────────────────────────────────────────────────────────
    // Role CRUD — viewer has no role management access
    // ──────────────────────────────────────────────────────────

    public function test_viewer_cannot_manage_roles(): void
    {
        $viewer = $this->userWithRole('viewer');

        $this->assertFalse($viewer->can('ViewAny:Role'));
        $this->assertFalse($viewer->can('Create:Role'));
    }
}
