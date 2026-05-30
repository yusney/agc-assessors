# Design: Offices Feature

## Technical Approach

We will build the offices directory as a full vertical slice using Clean Architecture. The domain will remain completely decoupled from the framework, containing only pure PHP 8.4 classes (Entities, Value Objects, and Repository Interfaces). The infrastructure layer will implement the repository using a translatable Eloquent model. The admin interface will be built with Filament 5 schemas, strictly bound to the Eloquent model. The frontend will use a lightweight Alpine.js integration to render a full-width Google Map alongside a distinctive grid of office cards using the project's bespoke Tailwind 4 design system.

## Architecture Decisions

### Decision: Domain Layer Decoupling
**Choice**: Create a pure PHP `Office` entity and `OfficeRepository` interface in `AGC\Domain\Offices\Entities\`.
**Alternatives considered**: Use the Eloquent model directly throughout the application.
**Rationale**: Adhering to the established Clean Architecture conventions ensures domain logic is insulated from framework upgrades (like future Filament or Laravel changes).

### Decision: Translatable Properties
**Choice**: Use `spatie/laravel-translatable` on `EloquentOffice` with JSON columns for `name`, `address`, and `city`.
**Alternatives considered**: Separate translation tables or locale columns (`name_en`, `name_es`).
**Rationale**: Matches the existing pattern used by News and Services, simplifying Filament Tabs schema and maintaining consistent Blade rendering `$model->field()->get(app()->getLocale())`.

### Decision: Google Maps Integration
**Choice**: Pure JS API loaded async via Blade section + Alpine.js `x-data` / `x-init` for map initialization and marker placement.
**Alternatives considered**: Heavy Vue/React wrapper or third-party Laravel map package.
**Rationale**: Avoids unnecessary dependencies. Alpine.js can easily parse a JSON array injected by the Controller to render native Maps API markers with custom styling.

### Decision: Distinctive Office Card Frontend Design
**Choice**: Clean, architectural cards (`rounded-xl`) with a sharp top accent bar (`border-t-[3px] border-[#00346f]`). Uses the `Outfit` font for headlines, `Inter` for data, and integrates `Material Symbols Outlined`.
**Alternatives considered**: Generic shadow boxes or simple list views.
**Rationale**: The brand's "Refined Advisory" aesthetic demands high visual quality. The accent bar adds a premium touch while keeping the design stark and professional, complementing the #f9f9ff background.

## Data Flow

    [Filament Admin] ──→ EloquentOffice ──→ Database (offices table)
                                                 │
    [Public Controller] ←─ OfficeRepository ─────┘
            │
            ▼ (JSON Markers + Domain Entities)
    [Blade View] ──→ [Alpine.js Component] ──→ Google Maps Embed

## File Changes

| File | Action | Description |
|------|--------|-------------|
| `src/Domain/Offices/Entities/Office.php` | Create | Immutable final class with `TranslatableString` and lat/lng. |
| `src/Domain/Offices/Repositories/OfficeRepository.php` | Create | Interface for fetching active offices. |
| `src/Infrastructure/Persistence/Eloquent/Models/EloquentOffice.php` | Create | Eloquent model with `$translatable` and float casts. |
| `src/Infrastructure/Persistence/Eloquent/Repositories/EloquentOfficeRepository.php` | Create | Implements interface, maps Eloquent models to Domain entities. |
| `app/Providers/OfficesServiceProvider.php` | Create | Binds the Repository Interface to the Eloquent Implementation. |
| `database/migrations/*_create_offices_table.php` | Create | Migration with JSON columns, lat/lng floats, and `is_active` index. |
| `database/seeders/OfficeSeeder.php` | Create | Seeds 6 offices (ca/es/en). |
| `src/Filament/Resources/OfficeResource.php` | Create | Filament 5 CRUD resource using Tabs for translations and Grid for layout. |
| `app/Http/Controllers/Public/OfficesController.php` | Create | Controller fetching offices, converting to GeoJSON-like array. |
| `resources/views/public/pages/offices/index.blade.php` | Create | The main public view (Hero, Map, Grid). |
| `resources/views/public/home-sections/offices_map.blade.php` | Create | Home section view (Header, Map, Grid). |
| `routes/web.php` | Modify | Add `LaravelLocalization` routes for `/oficines`, `/es/oficinas`, `/en/offices`. |
| `resources/lang/{ca,es,en}/messages.php` | Modify | Add translation keys for navigation and page copy. |
| `database/seeders/MenuItemSeeder.php` | Modify | Add offices to navigation seeding. |
| `config/services.php` | Modify | Add `google_maps.key`. |

## Interfaces / Contracts

```php
namespace AGC\Domain\Offices\Repositories;

use AGC\Domain\Offices\Entities\Office;

interface OfficeRepository
{
    /**
     * @return array<Office>
     */
    public function getActiveOffices(): array;
    
    public function findById(int $id): ?Office;
}
```

```php
namespace AGC\Domain\Offices\Entities;

use AGC\Domain\Shared\ValueObjects\TranslatableString;

final class Office
{
    public function __construct(
        public readonly int $id,
        public readonly TranslatableString $name,
        public readonly TranslatableString $address,
        public readonly TranslatableString $city,
        public readonly string $phone,
        public readonly string $email,
        public readonly float $latitude,
        public readonly float $longitude,
        public readonly bool $isActive
    ) {}
}
```

## Testing Strategy

| Layer | What to Test | Approach |
|-------|-------------|----------|
| Unit | `EloquentOfficeRepository` mapping | Verify mapper correctly converts Eloquent to Domain `Office` entity with `TranslatableString`. |
| Integration | `OfficeResource` (Filament) | Test creation/editing of an office and verify translatable data saves correctly to the database. |
| E2E | Public `/oficines` Route | Test HTTP 200, verify map script is loaded, and all active office cards are rendered on the page. |

## Migration / Rollout

No complex data migration required as this is a new module. 
Rollout involves:
1. Running the `offices` table migration.
2. Executing `OfficeSeeder` and `MenuItemSeeder`.
3. Validating the Google Maps API key in `.env`.

## Open Questions

- [ ] Will we need custom Google Maps JSON styling (e.g. grayscale) to better match the #f9f9ff / #00346f aesthetic, or standard maps?
- [ ] Is there a preferred "Directions" icon in `Material Symbols Outlined`? We plan to use `arrow_outward`.
