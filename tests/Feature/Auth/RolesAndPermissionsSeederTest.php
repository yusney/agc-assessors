<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class RolesAndPermissionsSeederTest extends TestCase
{
    use RefreshDatabase;

    // Bootstrap: create stub permissions matching Shield's generated format.
    // Shield v4 uses `Ability:ResourceName` naming (e.g. "ViewAny:NewsModel").
    protected function setUp(): void
    {
        parent::setUp();

        $resources = ['NewsModel', 'PageModel', 'ServiceModel', 'TeamMemberModel', 'EloquentOffice', 'HomeSection', 'MenuItem'];
        $abilities = ['ViewAny', 'View', 'Create', 'Update', 'Delete', 'DeleteAny', 'ForceDelete', 'ForceDeleteAny', 'Restore', 'RestoreAny', 'Replicate', 'Reorder'];

        foreach ($resources as $resource) {
            foreach ($abilities as $ability) {
                Permission::firstOrCreate(['name' => "{$ability}:{$resource}", 'guard_name' => 'web']);
            }
        }
    }

    public function test_seeder_creates_four_roles(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->assertDatabaseHas('roles', ['name' => 'super_admin']);
        $this->assertDatabaseHas('roles', ['name' => 'manager']);
        $this->assertDatabaseHas('roles', ['name' => 'editor']);
        $this->assertDatabaseHas('roles', ['name' => 'viewer']);
    }

    public function test_seeder_assigns_super_admin_role_to_admin_user(): void
    {
        User::factory()->create(['email' => 'admin@agcassessors.com']);

        $this->seed(RolesAndPermissionsSeeder::class);

        $admin = User::where('email', 'admin@agcassessors.com')->firstOrFail();
        $this->assertTrue($admin->hasRole('super_admin'));
    }

    public function test_seeder_is_idempotent_no_duplicate_roles(): void
    {
        User::factory()->create(['email' => 'admin@agcassessors.com']);

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->assertSame(1, Role::where('name', 'super_admin')->count());
        $this->assertSame(1, Role::where('name', 'manager')->count());
        $this->assertSame(1, Role::where('name', 'editor')->count());
        $this->assertSame(1, Role::where('name', 'viewer')->count());
    }

    public function test_seeder_is_idempotent_no_duplicate_role_assignments(): void
    {
        User::factory()->create(['email' => 'admin@agcassessors.com']);

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);

        $admin = User::where('email', 'admin@agcassessors.com')->firstOrFail();
        $this->assertSame(1, $admin->roles()->where('name', 'super_admin')->count());
    }

    public function test_editor_role_has_no_home_section_permissions(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $editor = Role::findByName('editor');
        $hasHomeSectionPerm = $editor->permissions()->where('name', 'like', '%:HomeSection')->exists();

        $this->assertFalse($hasHomeSectionPerm, 'editor must NOT have HomeSection permissions');
    }

    public function test_editor_role_has_no_menu_item_permissions(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $editor = Role::findByName('editor');
        $hasMenuItemPerm = $editor->permissions()->where('name', 'like', '%:MenuItem')->exists();

        $this->assertFalse($hasMenuItemPerm, 'editor must NOT have MenuItem permissions');
    }

    public function test_viewer_role_cannot_create_or_update(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $viewer = Role::findByName('viewer');
        $hasWrite = $viewer->permissions()
            ->whereIn('name', ['Create:NewsModel', 'Update:NewsModel', 'Delete:NewsModel'])
            ->exists();

        $this->assertFalse($hasWrite, 'viewer must NOT have write permissions');
    }
}
