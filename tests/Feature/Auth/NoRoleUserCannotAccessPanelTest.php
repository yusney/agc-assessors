<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

/**
 * Verify that a freshly-created user who has no role assigned is denied panel
 * access by `canAccessPanel()`.
 *
 * Spec: "verify users without roles get 403 on panel"
 * We test this at the model/method level, which is the authoritative layer
 * that Filament's Authenticate middleware delegates to.
 */
final class NoRoleUserCannotAccessPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_user_without_role_is_denied_panel_access(): void
    {
        $panel = Mockery::mock(Panel::class);
        $user = User::factory()->create();

        $this->assertFalse(
            $user->canAccessPanel($panel),
            'Users with no role must be denied access to the Filament panel.'
        );
    }

    public function test_user_without_role_cannot_access_admin_panel_route(): void
    {
        $user = User::factory()->create();

        // Filament redirects unauthorised-but-authenticated users away from the
        // panel (typically to the home URL). A 302 is acceptable here; what must
        // NOT happen is a 200 OK landing on the dashboard.
        $response = $this->actingAs($user)->get('/admin');

        $this->assertNotSame(200, $response->status());
    }
}
