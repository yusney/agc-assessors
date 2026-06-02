# Design: User Roles and Permissions Management

## Technical Approach

Install `bezhansalleh/filament-shield` v4 (Filament 5) on `spatie/laravel-permission ^7.4`. Shield auto-discovers the 7 registered Resources and generates one policy + 12 permissions per Resource. Our Eloquent models live at `AGC\Infrastructure\Persistence\Eloquent\Models\*` (not `App\Models\*`), so Laravel's auto policy resolution fails — a `Gate::guessPolicyNamesUsing` shim in `AppServiceProvider` maps model FQCN → `App\Policies\{Model}Policy`. A `RolesAndPermissionsSeeder` creates `manager`/`editor`/`viewer` roles and assigns `super_admin` to the default admin; Shield's built-in `Gate::before` provides the bypass.

## Architecture Decisions

| # | Decision | Choice | Rationale |
|---|----------|--------|-----------|
| 1 | Shield version | Pin `^4.0` | Stable, Filament 5. |
| 2 | Policy subject | `subject => 'model'` (default) | Gate subject is the model. |
| 3 | Policy location | `app/Policies/` (Shield default) | Infrastructure adapter. |
| 4 | Model→Policy mapping | `Gate::guessPolicyNamesUsing` strips `AGC\…\Models\` prefix, appends `Policy` | Auto-extends to new Resources. |
| 5 | Super admin | Shield's built-in `Gate::before` via `super_admin.name` config | No hand-rolled bypass. |
| 6 | Role definition | Idempotent `RolesAndPermissionsSeeder` (not `shield:seeder --generate`) | One VCS source of truth; CI-runnable. |
| 7 | Editor scope | view/create/update/replicate on 5 content Resources; no destructive; NO access to HomeSection/MenuItem | "read+write content, no structural changes". |
| 8 | Viewer scope | `viewAny` + `view` on all 7 | Read-only. |
| 9 | Pages/widgets exclusion | Exclude `Dashboard`, `AccountWidget`, `FilamentInfoWidget` + 4 settings pages | Settings super-admin-only. |
| 10 | Shield commands | Non-interactive: `install --force`, `generate --all --force`, `super-admin --user=…` | Reproducible in CI. |

## Component Diagram

    AdminPanelProvider → plugins([..., FilamentShieldPlugin::make()])
        └──► registers Shield's RoleResource under nav
    shield:generate --all
        ├──► app/Policies/{NewsModel,PageModel,ServiceModel,TeamMemberModel,
        │   EloquentOffice,HomeSection,MenuItem}Policy.php
        └──► 84 records in `permissions` (7 Resources × 12 methods)
    AppServiceProvider::boot() → Gate::guessPolicyNamesUsing(...)
    Resource action → Gate::authorize('update', $newsModel)
        ├── super_admin → Shield Gate::before → true
        ├── editor → checks role permissions → true/false
        └── no role → false (403)
    RolesAndPermissionsSeeder
        ├── firstOrCreate('super_admin'/'manager'/'editor'/'viewer')
        ├── manager → syncPermissions(7 × all 12)
        ├── editor  → syncPermissions(5 × 4 non-destructive)
        ├── viewer  → syncPermissions(7 × view+viewAny)
        └── assignRole('super_admin') to admin@agcassessors.com

## File Changes

| File | Action | Description |
|------|--------|-------------|
| `composer.json`/`composer.lock` | Modify | Add `bezhansalleh/filament-shield: ^4.0` |
| `config/filament-shield.php` | Create | Publish; overrides below |
| `app/Models/User.php` | Modify | Add `use Spatie\Permission\Traits\HasRoles;` |
| `app/Providers/Filament/AdminPanelProvider.php` | Modify | Register `FilamentShieldPlugin::make()` |
| `app/Providers/AppServiceProvider.php` | Modify | Add `Gate::guessPolicyNamesUsing` closure |
| `app/Policies/*Policy.php` (×7) | Create | Auto-generated |
| `database/seeders/RolesAndPermissionsSeeder.php` | Create | Idempotent seeder |
| `database/seeders/DatabaseSeeder.php` | Modify | Call new seeder |
| `tests/Feature/Auth/*Test.php` (×8) | Create | Access-matrix tests |

## Configuration Changes

**`config/filament-shield.php`** — overrides only:

```php
return [
    'auth_provider_model' => 'App\\Models\\User',
    'super_admin' => ['enabled' => true, 'name' => 'super_admin',
                      'define_via_gate' => false, 'intercept_gate' => 'before'],
    'policies'   => ['path' => app_path('Policies'), 'merge' => true, 'generate' => true],
    'resources'  => ['subject' => 'model', 'exclude' => []],
    'pages'      => ['exclude' => [
        \Filament\Pages\Dashboard::class,
        \AGC\Filament\Pages\FooterSettingsPage::class,
        \AGC\Filament\Pages\SocialMediaSettingsPage::class,
        \AGC\Filament\Pages\TrustBarSettingsPage::class,
        \AGC\Filament\Pages\WorkWithUsSettingsPage::class,
    ]],
    'widgets'    => ['exclude' => [
        \Filament\Widgets\AccountWidget::class,
        \Filament\Widgets\FilamentInfoWidget::class,
    ]],
];
```

**`app/Providers/AppServiceProvider.php::boot()`** — append:

```php
use Illuminate\Support\Facades\Gate;

Gate::guessPolicyNamesUsing(function (string $modelClass): ?string {
    if (str_starts_with($modelClass, 'AGC\\Infrastructure\\Persistence\\Eloquent\\Models\\')) {
        return 'App\\Policies\\' . class_basename($modelClass) . 'Policy';
    }
    return null;
});
```

## Deployment Sequence

1. `composer require bezhansalleh/filament-shield:^4.0`
2. `php artisan vendor:publish --tag=filament-shield-config`; edit config per block above
3. `php artisan shield:install admin --force` (creates `super_admin` role)
4. Modify `User.php` (+`HasRoles`), `AppServiceProvider.php` (+guessPolicyNamesUsing), `AdminPanelProvider.php` (+`FilamentShieldPlugin::make()`)
5. `php artisan shield:generate --all --force` → 7 policies + 84 permission records
6. Create `RolesAndPermissionsSeeder`; add to `DatabaseSeeder::run()`
7. `php artisan db:seed --class=RolesAndPermissionsSeeder`
8. `php artisan shield:super-admin --user=admin@agcassessors.com --panel=admin` (defensive)
9. `php artisan test --filter=Auth`
10. `php artisan route:list | grep shield`; HTTP smoke `/admin/shield/roles` as super_admin → 200

## Testing Strategy

| Test | Asserts |
|------|---------|
| `SuperAdminAccessTest` | 200 on all 7 indexes + `/admin/shield/roles` |
| `ManagerAccessTest` | Full CRUD on all 7; 200 on `/admin/shield/roles` |
| `EditorAccessTest` | 200 on view/create/update for 5 content; 403 on delete/HomeSection/MenuItem |
| `ViewerAccessTest` | 200 on view/viewAny for all 7; 403 on writes |
| `NoRoleAccessTest` | 403 on indexes; 200 on Dashboard |
| `PolicyResolutionTest` | `Gate::getPolicyFor($newsModel)` → `App\Policies\NewsModelPolicy` |
| `SeederIdempotencyTest` | Run twice → 4 roles, 84 permissions, single super_admin |
| `CanAccessPanelTest` | Regression: `canAccessPanel()` returns `true` for default admin |

All use `RefreshDatabase` + `UserFactory::withRole(string $role)` state method.

## Migration / Rollback

**No new migrations** — spatie's tables already exist (`2026_05_29_205903_create_permission_tables.php`); Shield writes to them.

**Rollback (reverse, idempotent):**
1. `composer remove bezhansalleh/filament-shield`
2. Delete `app/Policies/*Policy.php` (7) + `config/filament-shield.php`
3. Revert `User.php`, `AppServiceProvider.php`, `AdminPanelProvider.php`, `DatabaseSeeder.php`; delete `RolesAndPermissionsSeeder.php`
4. Truncate (do not drop) `roles`, `permissions`, `model_has_permissions`, `model_has_roles`, `role_has_permissions`

## Open Questions

- **`editor` + `reorder` on News/Pages?** Currently excluded. If editors need sort-order control, grant `reorder:News`/`reorder:Page` only. Decide before sdd-tasks.
- **Localized permission labels** (`shield:translation ca/es/en`): defer to follow-up.
