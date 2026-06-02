<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Policies\RolePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Verify that the super_admin role is protected from deletion at the policy level.
 *
 * Spec: "Super Admin Role Cannot Be Deleted via UI"
 * The RolePolicy::delete() must return false for the super_admin role,
 * regardless of the user's permissions. Other roles may be deleted as normal.
 */
final class SuperAdminDeleteGuardTest extends TestCase
{
    use RefreshDatabase;
    use RoleTestSetupTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRolesAndPermissions();
    }

    // ──────────────────────────────────────────────────────────
    // Policy-level guard
    // ──────────────────────────────────────────────────────────

    public function test_policy_denies_deletion_of_super_admin_role(): void
    {
        $admin = $this->userWithRole('super_admin');
        $superAdminRole = Role::where('name', 'super_admin')->firstOrFail();

        $policy = new RolePolicy;

        $this->assertFalse(
            $policy->delete($admin, $superAdminRole),
            'RolePolicy::delete() must return false for the super_admin role.',
        );
    }

    public function test_policy_allows_deletion_of_non_system_roles(): void
    {
        $admin = $this->userWithRole('super_admin');
        $editorRole = Role::where('name', 'editor')->firstOrFail();

        $policy = new RolePolicy;

        // super_admin has Delete:Role permission, so the policy should allow it.
        $this->assertTrue(
            $policy->delete($admin, $editorRole),
            'RolePolicy::delete() must allow deletion of non-system roles for super_admin.',
        );
    }

    public function test_gate_denies_deletion_of_super_admin_role(): void
    {
        $admin = $this->userWithRole('super_admin');
        $superAdminRole = Role::where('name', 'super_admin')->firstOrFail();

        // Gate::allows goes through the full authorization stack (Gate::before + policy).
        $this->actingAs($admin);

        $this->assertFalse(
            $admin->can('delete', $superAdminRole),
            'Gate check for delete on super_admin role must be denied.',
        );
    }

    public function test_gate_allows_deletion_of_other_roles_by_super_admin(): void
    {
        $admin = $this->userWithRole('super_admin');
        $viewerRole = Role::where('name', 'viewer')->firstOrFail();

        $this->actingAs($admin);

        $this->assertTrue(
            $admin->can('delete', $viewerRole),
            'super_admin must be able to delete non-system roles.',
        );
    }
}
