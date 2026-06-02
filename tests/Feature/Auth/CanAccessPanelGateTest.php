<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Verify that `User::canAccessPanel()` correctly gates panel entry.
 *
 * Tests the method directly (no HTTP stack) so we prove the authz contract
 * at the model layer before layering on middleware behaviour.
 */
final class CanAccessPanelGateTest extends TestCase
{
    use RefreshDatabase;

    private Panel $panel;

    protected function setUp(): void
    {
        parent::setUp();

        // canAccessPanel() never reads the Panel argument — mock for type safety.
        $this->panel = Mockery::mock(Panel::class);
    }

    public function test_user_without_any_role_cannot_access_panel(): void
    {
        $user = User::factory()->create();

        $this->assertFalse(
            $user->canAccessPanel($this->panel),
            'A user with no role must be denied panel access.'
        );
    }

    public function test_user_with_super_admin_role_can_access_panel(): void
    {
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        $this->assertTrue($user->canAccessPanel($this->panel));
    }

    public function test_user_with_editor_role_can_access_panel(): void
    {
        Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole('editor');

        $this->assertTrue($user->canAccessPanel($this->panel));
    }

    public function test_user_with_viewer_role_can_access_panel(): void
    {
        Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole('viewer');

        $this->assertTrue($user->canAccessPanel($this->panel));
    }

    public function test_with_role_factory_state_creates_user_with_assigned_role(): void
    {
        Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $user = User::factory()->withRole('manager')->create();

        $this->assertTrue($user->hasRole('manager'));
        $this->assertTrue($user->canAccessPanel($this->panel));
    }
}
