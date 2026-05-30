# Tasks: Offices Feature

Ordered by dependency: **domain → infra → filament → routes → frontend → tests**.

| ID | Title | Layer | Files to create/modify | Est. lines changed |
|---|---|---|---|---:|
| TASK-03 | Office domain entity (immutable) | Domain | `src/Domain/Offices/Entities/Office.php` | ~38 |
| TASK-04 | OfficeRepositoryInterface contract | Domain | `src/Domain/Offices/Repositories/OfficeRepositoryInterface.php` | ~16 |
| TASK-01 | Migration: create offices table + index | Infrastructure | `database/migrations/*_create_offices_table.php` | ~42 |
| TASK-02 | EloquentOffice translatable model | Infrastructure | `src/Infrastructure/Persistence/Eloquent/Models/EloquentOffice.php` | ~34 |
| TASK-05 | EloquentOfficeRepository implementation + mapper | Infrastructure | `src/Infrastructure/Persistence/Eloquent/Repositories/EloquentOfficeRepository.php` | ~48 |
| TASK-06 | OfficesServiceProvider bindings + registration | Infrastructure | `app/Providers/OfficesServiceProvider.php`, `config/app.php` | ~18 |
| TASK-09 | Google Maps key config | Infrastructure | `config/services.php` | ~4 |
| TASK-07 | Filament OfficeResource (strict Filament 5 APIs) | Filament | `src/Filament/Resources/OfficeResource.php` | ~92 |
| TASK-08 | OfficesController + localized routes | Routes/HTTP | `app/Http/Controllers/Public/OfficesController.php`, `routes/web.php` | ~40 |
| TASK-10 | Translation strings for offices nav/page copy | Frontend i18n | `resources/lang/ca/messages.php`, `resources/lang/es/messages.php`, `resources/lang/en/messages.php` | ~30 |
| TASK-11 | Home section `offices_map` blade | Frontend | `resources/views/public/home-sections/offices_map.blade.php` | ~56 |
| TASK-12 | Public offices index page blade | Frontend | `resources/views/public/pages/offices/index.blade.php` | ~74 |
| TASK-13 | Menu item seeding for offices URLs | Infrastructure | `database/seeders/MenuItemSeeder.php` | ~14 |
| TASK-14 | Unit tests (Office entity + repository mapper) | Tests (Unit) | `tests/Unit/Domain/Offices/OfficeTest.php`, `tests/Unit/Domain/Offices/EloquentOfficeRepositoryMapperTest.php` | ~40 |
| TASK-15 | Feature test for localized offices route | Tests (Feature) | `tests/Feature/Http/OfficesControllerTest.php` | ~24 |

## TDD Execution Notes

- RED first for TASK-14 and TASK-15, then GREEN on TASK-03/04/05/08/11/12.
- Verification command: `docker compose exec php php artisan test`.

## Suggested Work Slices

- **Slice 1 (backend, TASK-01..10)**: migration, domain contracts/entities, repositories, provider, config, Filament resource, controller/routes, i18n keys (~320 lines).
- **Slice 2 (frontend + tests, TASK-11..15)**: public blades, menu seeder, unit+feature tests (~260 lines).

## Review Workload Forecast

- Total estimated lines changed: **~580**
- Chained PRs recommended: **Yes**
- Decision needed before apply: **Yes**
