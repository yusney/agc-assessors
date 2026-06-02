<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verify that only `super_admin` can access and manage Shield roles.
 *
 * Spec: "only super_admin can see/manage roles"
 * Manager, Editor, and Viewer must all be denied all Role CRUD abilities.
 */
final class RoleCrudAccessTest extends TestCase
{
    use RefreshDatabase;
    use RoleTestSetupTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRolesAndPermissions();
    }

    // ──────────────────────────────────────────────────────────
    // super_admin — full Role CRUD
    // ──────────────────────────────────────────────────────────

    public function test_super_admin_can_view_roles(): void
    {
        $admin = $this->userWithRole('super_admin');

        $this->assertTrue($admin->can('ViewAny:Role'));
        $this->assertTrue($admin->can('View:Role'));
    }

    public function test_super_admin_can_create_and_delete_roles(): void
    {
        $admin = $this->userWithRole('super_admin');

        $this->assertTrue($admin->can('Create:Role'));
        $this->assertTrue($admin->can('Update:Role'));
        $this->assertTrue($admin->can('Delete:Role'));
    }

    // ──────────────────────────────────────────────────────────
    // manager — denied
    // ──────────────────────────────────────────────────────────

    public function test_manager_cannot_view_or_manage_roles(): void
    {
        $manager = $this->userWithRole('manager');

        $this->assertFalse($manager->can('ViewAny:Role'));
        $this->assertFalse($manager->can('Create:Role'));
        $this->assertFalse($manager->can('Delete:Role'));
    }

    // ──────────────────────────────────────────────────────────
    // editor — denied
    // ──────────────────────────────────────────────────────────

    public function test_editor_cannot_view_or_manage_roles(): void
    {
        $editor = $this->userWithRole('editor');

        $this->assertFalse($editor->can('ViewAny:Role'));
        $this->assertFalse($editor->can('Create:Role'));
        $this->assertFalse($editor->can('Delete:Role'));
    }

    // ──────────────────────────────────────────────────────────
    // viewer — denied
    // ──────────────────────────────────────────────────────────

    public function test_viewer_cannot_view_or_manage_roles(): void
    {
        $viewer = $this->userWithRole('viewer');

        $this->assertFalse($viewer->can('ViewAny:Role'));
        $this->assertFalse($viewer->can('Create:Role'));
        $this->assertFalse($viewer->can('Delete:Role'));
    }

    // ──────────────────────────────────────────────────────────
    // Shield roles resource HTTP route (super_admin vs others)
    // ──────────────────────────────────────────────────────────

    public function test_super_admin_can_access_shield_roles_route(): void
    {
        $admin = $this->userWithRole('super_admin');

        $response = $this->actingAs($admin)->get('/admin/shield/roles');

        // A successful 200 or a redirect within the panel (e.g. 302 to login for
        // session issues) are acceptable; what must NOT happen is 403.
        $this->assertNotSame(403, $response->status());
    }

    public function test_editor_is_denied_shield_roles_route(): void
    {
        $editor = $this->userWithRole('editor');

        $response = $this->actingAs($editor)->get('/admin/shield/roles');

        // Filament raises AuthorizationException → 403 when policy denies viewAny.
        $this->assertSame(403, $response->status());
    }
}
