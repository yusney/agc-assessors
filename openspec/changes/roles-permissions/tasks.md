# Tasks: User Roles and Permissions Management (Filament Shield v4)

## Review Workload Forecast

| Field | Value |
|-------|-------|
| Estimated changed lines | ~310 human-authored + 175 auto-generated policies = ~485 total |
| 400-line budget risk | High |
| Chained PRs recommended | Yes |
| Suggested split | PR1 Foundation (~55 lines) → PR2 Seed+Generate (~85 lines) → PR3 Tests (~365 lines) |
| Delivery strategy | ask-on-risk |
| Chain strategy | pending |

Decision needed before apply: Yes
Chained PRs recommended: Yes
Chain strategy: pending
400-line budget risk: High

### Suggested Work Units

| Unit | Goal | Likely PR | Notes |
|------|------|-----------|-------|
| 1 | Composer + config + HasRoles + Gate shim + Plugin registration | PR 1 | Independently verifiable; Shield plugin shows in panel but no roles exist yet |
| 2 | shield:install, shield:generate, RolesAndPermissionsSeeder, DatabaseSeeder | PR 2 | Depends on PR1; 7 policies auto-generated; seeder is VCS source-of-truth |
| 3 | UserFactory withRole() + 8 feature tests + QA | PR 3 | Depends on PR2; verify full role matrix |

---

## Phase 1: Foundation (Dependency + Wiring)

- [ ] 1.1 `composer require bezhansalleh/filament-shield:^4.0` — add to composer.json
- [ ] 1.2 `php artisan vendor:publish --tag=filament-shield-config` — creates `config/filament-shield.php`
- [ ] 1.3 Edit `config/filament-shield.php`: set `auth_provider_model` → `App\Models\User`, super_admin name → `super_admin`, intercept_gate → `before`, exclude 4 settings pages + Dashboard + AccountWidget + FilamentInfoWidget, `resources.subject` → `model`
- [ ] 1.4 `php artisan shield:install admin --force` — creates `super_admin` role in DB
- [ ] 1.5 Add `use Spatie\Permission\Traits\HasRoles;` trait to `app/Models/User.php`
- [ ] 1.6 Register `FilamentShieldPlugin::make()` in `AdminPanelProvider.php` plugins array
- [ ] 1.7 Add `Gate::guessPolicyNamesUsing(...)` in `AppServiceProvider.php::boot()` mapping `AGC\Infrastructure\Persistence\Eloquent\Models\*` → `App\Policies\*Policy`

## Phase 2: Policies + Seeding

- [ ] 2.1 `php artisan shield:generate --all --force` — generates 7× `app/Policies/*Policy.php` + 84 permission records
- [ ] 2.2 Create `database/seeders/RolesAndPermissionsSeeder.php`: idempotent seeder using `firstOrCreate` for `super_admin`/`manager`/`editor`/`viewer`; sync permissions per role matrix; assign `super_admin` to `admin@agcassessors.com`
- [ ] 2.3 Add `RolesAndPermissionsSeeder::class` to `DatabaseSeeder::run()` call array
- [ ] 2.4 `php artisan db:seed --class=RolesAndPermissionsSeeder` — verify roles + permissions in DB
- [ ] 2.5 `php artisan shield:super-admin --user=admin@agcassessors.com --panel=admin` (defensive)

## Phase 3: Testing

- [ ] 3.1 Add `withRole(string $role)` state method to `UserFactory` using `assignRole()`
- [ ] 3.2 Write `SuperAdminAccessTest` — 200 on all 7 resource indexes + `/admin/shield/roles`
- [ ] 3.3 Write `ManagerAccessTest` — full CRUD on all 7 resources; 200 on Roles page
- [ ] 3.4 Write `EditorAccessTest` — 200 on view/create/update for 5 content resources; 403 on delete / HomeSection / MenuItem
- [ ] 3.5 Write `ViewerAccessTest` — 200 on viewAny+view for all 7; 403 on writes
- [ ] 3.6 Write `NoRoleAccessTest` — 403 on resource indexes; 200 on Dashboard
- [ ] 3.7 Write `PolicyResolutionTest` — `Gate::getPolicyFor($newsModel)` resolves to `App\Policies\NewsModelPolicy`
- [ ] 3.8 Write `SeederIdempotencyTest` — run twice → 4 roles, 84 permissions, single super_admin assignment
- [ ] 3.9 Write `CanAccessPanelTest` — regression: default admin still passes `canAccessPanel()`

## Phase 4: Verification

- [ ] 4.1 `php artisan test --filter=Auth` — all 8 tests pass
- [ ] 4.2 `php artisan route:list | grep shield` — confirm Shield routes registered
- [ ] 4.3 HTTP smoke: `curl -s -o /dev/null -w "%{http_code}" http://localhost:8080/admin/shield/roles` → 200
- [ ] 4.4 `./vendor/bin/pint --test` — PSR-12 compliance
- [ ] 4.5 `phpstan analyse` — no new errors

---

**Dependencies**: Phase 2 depends on Phase 1. Phase 3 depends on Phase 2. Phase 4 runs after all implementation.
**Verification**: Each test in Phase 3 maps to a spec scenario. Phase 4 confirms end-to-end.
