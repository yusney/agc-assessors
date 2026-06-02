<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verify the editor role's access matrix.
 *
 * Spec: editor has ViewAny/View/Create/Update/Delete on the 5 content resources
 * (News, Page, Service, TeamMember, Office).  Must NOT have access to
 * HomeSection, MenuItem, or Role CRUD.  Must NOT have destructive permissions
 * (DeleteAny, ForceDelete).
 */
final class EditorRoleAccessTest extends TestCase
{
    use RefreshDatabase;
    use RoleTestSetupTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRolesAndPermissions();
    }

    // ──────────────────────────────────────────────────────────
    // Content resources — CRUD allowed (no DeleteAny/ForceDelete)
    // ──────────────────────────────────────────────────────────

    public function test_editor_can_read_and_write_content_resources(): void
    {
        $editor = $this->userWithRole('editor');

        foreach (['NewsModel', 'PageModel', 'ServiceModel', 'TeamMemberModel', 'EloquentOffice'] as $resource) {
            foreach (['ViewAny', 'View', 'Create', 'Update'] as $ability) {
                $this->assertTrue(
                    $editor->can("{$ability}:{$resource}"),
                    "editor must {$ability}:{$resource}"
                );
            }
        }
    }

    public function test_editor_can_delete_single_content_records(): void
    {
        $editor = $this->userWithRole('editor');

        foreach (['NewsModel', 'PageModel', 'ServiceModel', 'TeamMemberModel', 'EloquentOffice'] as $resource) {
            $this->assertTrue(
                $editor->can("Delete:{$resource}"),
                "editor must Delete:{$resource}"
            );
        }
    }

    public function test_editor_cannot_bulk_delete_content_resources(): void
    {
        $editor = $this->userWithRole('editor');

        foreach (['NewsModel', 'PageModel', 'ServiceModel', 'TeamMemberModel', 'EloquentOffice'] as $resource) {
            $this->assertFalse(
                $editor->can("DeleteAny:{$resource}"),
                "editor must NOT DeleteAny:{$resource}"
            );
        }
    }

    public function test_editor_cannot_force_delete_content_resources(): void
    {
        $editor = $this->userWithRole('editor');

        foreach (['NewsModel', 'PageModel', 'ServiceModel', 'TeamMemberModel', 'EloquentOffice'] as $resource) {
            $this->assertFalse(
                $editor->can("ForceDelete:{$resource}"),
                "editor must NOT ForceDelete:{$resource}"
            );
        }
    }

    // ──────────────────────────────────────────────────────────
    // Structural resources — must be blocked
    // ──────────────────────────────────────────────────────────

    public function test_editor_cannot_access_home_section(): void
    {
        $editor = $this->userWithRole('editor');

        $this->assertFalse($editor->can('ViewAny:HomeSection'));
        $this->assertFalse($editor->can('Create:HomeSection'));
        $this->assertFalse($editor->can('Delete:HomeSection'));
    }

    public function test_editor_cannot_access_menu_item(): void
    {
        $editor = $this->userWithRole('editor');

        $this->assertFalse($editor->can('ViewAny:MenuItem'));
        $this->assertFalse($editor->can('Create:MenuItem'));
    }

    // ──────────────────────────────────────────────────────────
    // Role CRUD — must be blocked
    // ──────────────────────────────────────────────────────────

    public function test_editor_cannot_manage_roles(): void
    {
        $editor = $this->userWithRole('editor');

        $this->assertFalse($editor->can('ViewAny:Role'));
        $this->assertFalse($editor->can('Create:Role'));
    }
}
